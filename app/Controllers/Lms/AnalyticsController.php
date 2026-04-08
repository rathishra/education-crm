<?php
namespace App\Controllers\Lms;

class AnalyticsController extends LmsBaseController
{
    // ── Institution-wide overview ─────────────────────────────
    public function index(): void
    {
        $this->authorize('analytics.view');

        $range     = $this->input('range', '30');  // 7 | 30 | 90 | 365
        $rangeInt  = in_array($range, ['7','30','90','365']) ? (int)$range : 30;
        $dateFrom  = date('Y-m-d', strtotime("-{$rangeInt} days"));

        // ── KPI cards ──
        $kpis = $this->_getKpis($dateFrom);

        // ── Enrollment trend (daily new enrollments) ──
        $enrollTrend = $this->_enrollTrend($dateFrom);

        // ── Activity feed counts by day ──
        $activityTrend = $this->_activityTrend($dateFrom);

        // ── Top courses by enrollment & completion ──
        $topCourses = $this->_topCourses();

        // ── Quiz pass-rate by course ──
        $quizPassRates = $this->_quizPassRates();

        // ── Learner progress distribution (0-25, 26-50, 51-75, 76-100%) ──
        $progressBuckets = $this->_progressBuckets();

        // ── Top active learners (XP leaderboard) ──
        $leaderboard = $this->_leaderboard(10);

        // ── Content type breakdown (lesson types) ──
        $contentTypes = $this->_contentTypes();

        $pageTitle = 'LMS Analytics';
        $this->view('lms/analytics/index', compact(
            'kpis', 'enrollTrend', 'activityTrend',
            'topCourses', 'quizPassRates', 'progressBuckets',
            'leaderboard', 'contentTypes',
            'range', 'rangeInt', 'dateFrom', 'pageTitle'
        ), 'main');
    }

    // ── Per-course deep-dive ──────────────────────────────────
    public function course(int $id): void
    {
        $this->authorize('analytics.view');

        // Verify course belongs to institution
        try {
            $this->db->query(
                "SELECT id, title, thumbnail, status, created_at FROM lms_courses WHERE id=? AND institution_id=? AND deleted_at IS NULL",
                [$id, $this->institutionId]
            );
            $course = $this->db->fetch();
        } catch (\Throwable $e) { $course = null; }

        if (!$course) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Course Not Found'], 'main');
            return;
        }

        $range    = $this->input('range', '30');
        $rangeInt = in_array($range, ['7','30','90','365']) ? (int)$range : 30;
        $dateFrom = date('Y-m-d', strtotime("-{$rangeInt} days"));

        // Enrollment over time
        $enrollTrend = $this->_courseEnrollTrend($id, $dateFrom);

        // Lesson completion rates
        $lessonStats = $this->_lessonStats($id);

        // Assignment score distribution
        $assignStats = $this->_assignStats($id);

        // Quiz performance
        $quizStats = $this->_quizStats($id);

        // Attendance summary
        $attStats = $this->_courseAttStats($id);

        // Student engagement table
        $students = $this->_courseStudents($id);

        // Forum activity
        $forumStats = $this->_forumStats($id);

        // Drop-off: enrolled but 0% progress
        $dropOff = count(array_filter($students, fn($s) => (float)$s['progress_pct'] == 0));

        $pageTitle = 'Analytics — ' . $course['title'];
        $this->view('lms/analytics/course', compact(
            'course', 'enrollTrend', 'lessonStats',
            'assignStats', 'quizStats', 'attStats',
            'students', 'forumStats', 'dropOff',
            'range', 'rangeInt', 'pageTitle'
        ), 'main');
    }

    // ── Per-student analytics ─────────────────────────────────
    public function student(int $userId): void
    {
        $this->authorize('analytics.view');

        try {
            $this->db->query(
                "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, u.email,
                        u.xp_points, u.level, u.role, u.created_at AS joined_at
                 FROM lms_users u
                 WHERE u.id=? AND u.institution_id=?",
                [$userId, $this->institutionId]
            );
            $student = $this->db->fetch();
        } catch (\Throwable $e) { $student = null; }

        if (!$student) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Student Not Found'], 'main');
            return;
        }

        // Enrollments + progress
        try {
            $this->db->query(
                "SELECT e.course_id, c.title, e.progress_pct, e.enrolled_at,
                        (SELECT COUNT(*) FROM lms_lesson_progress lp JOIN lms_lessons l ON l.id=lp.lesson_id WHERE l.course_id=e.course_id AND lp.lms_user_id=? AND lp.completed=1) AS completed_lessons,
                        (SELECT COUNT(*) FROM lms_lessons l WHERE l.course_id=e.course_id AND l.deleted_at IS NULL) AS total_lessons
                 FROM lms_enrollments e
                 JOIN lms_courses c ON c.id=e.course_id
                 WHERE e.lms_user_id=? AND e.status='active'
                 ORDER BY e.enrolled_at DESC",
                [$userId, $userId]
            );
            $enrollments = $this->db->fetchAll();
        } catch (\Throwable $e) { $enrollments = []; }

        // Recent activity (last 30 events)
        try {
            $this->db->query(
                "SELECT * FROM lms_activity_feed WHERE lms_user_id=? ORDER BY created_at DESC LIMIT 30",
                [$userId]
            );
            $activity = $this->db->fetchAll();
        } catch (\Throwable $e) { $activity = []; }

        // XP over time (last 30 days)
        try {
            $this->db->query(
                "SELECT DATE(created_at) AS day, SUM(xp_earned) AS xp
                 FROM lms_activity_feed
                 WHERE lms_user_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(created_at) ORDER BY day",
                [$userId]
            );
            $xpTrend = $this->db->fetchAll();
        } catch (\Throwable $e) { $xpTrend = []; }

        // Quiz performance
        try {
            $this->db->query(
                "SELECT q.title, MAX(qa.percentage) AS best_pct, COUNT(qa.id) AS attempts,
                        MAX(qa.passed) AS ever_passed
                 FROM lms_quiz_attempts qa
                 JOIN lms_quizzes q ON q.id=qa.quiz_id
                 WHERE qa.lms_user_id=? AND qa.status='submitted'
                   AND q.institution_id=?
                 GROUP BY q.id ORDER BY qa.submitted_at DESC LIMIT 20",
                [$userId, $this->institutionId]
            );
            $quizPerf = $this->db->fetchAll();
        } catch (\Throwable $e) { $quizPerf = []; }

        // Assignment scores
        try {
            $this->db->query(
                "SELECT a.title, sub.score, a.max_score, sub.graded_at, sub.is_late
                 FROM lms_assignment_submissions sub
                 JOIN lms_assignments a ON a.id=sub.assignment_id
                 WHERE sub.lms_user_id=? AND a.institution_id=? AND sub.score IS NOT NULL
                 ORDER BY sub.submitted_at DESC LIMIT 20",
                [$userId, $this->institutionId]
            );
            $assignPerf = $this->db->fetchAll();
        } catch (\Throwable $e) { $assignPerf = []; }

        $pageTitle = 'Student Analytics — ' . $student['name'];
        $this->view('lms/analytics/student', compact(
            'student', 'enrollments', 'activity',
            'xpTrend', 'quizPerf', 'assignPerf', 'pageTitle'
        ), 'main');
    }

    // ── Course list for analytics ─────────────────────────────
    public function courses(): void
    {
        $this->authorize('analytics.view');

        $scope = $this->isAdmin()
            ? "c.institution_id={$this->institutionId}"
            : "c.instructor_id={$this->lmsUserId} AND c.institution_id={$this->institutionId}";

        try {
            $this->db->query(
                "SELECT c.id, c.title, c.status, c.created_at,
                        (SELECT COUNT(*) FROM lms_enrollments WHERE course_id=c.id AND status='active')        AS enrollments,
                        (SELECT COUNT(*) FROM lms_enrollments WHERE course_id=c.id AND progress_pct=100)       AS completions,
                        (SELECT COUNT(*) FROM lms_lessons     WHERE course_id=c.id AND deleted_at IS NULL)     AS lessons,
                        (SELECT COUNT(*) FROM lms_assignments WHERE course_id=c.id AND deleted_at IS NULL)     AS assignments,
                        (SELECT COUNT(*) FROM lms_quizzes     WHERE course_id=c.id AND deleted_at IS NULL)     AS quizzes,
                        (SELECT ROUND(AVG(progress_pct),1) FROM lms_enrollments WHERE course_id=c.id AND status='active') AS avg_progress
                 FROM lms_courses c
                 WHERE {$scope} AND c.deleted_at IS NULL
                 ORDER BY enrollments DESC"
            );
            $courses = $this->db->fetchAll();
        } catch (\Throwable $e) { $courses = []; }

        $pageTitle = 'Course Analytics';
        $this->view('lms/analytics/courses', compact('courses', 'pageTitle'), 'main');
    }

    // ── Private data methods ──────────────────────────────────

    private function _getKpis(string $dateFrom): array
    {
        $inst = $this->institutionId;
        $kpis = [];
        try {
            // Total learners
            $this->db->query("SELECT COUNT(*) AS cnt FROM lms_users WHERE institution_id=? AND role='learner'", [$inst]);
            $kpis['total_learners'] = (int)($this->db->fetch()['cnt'] ?? 0);

            // Active learners (activity in range)
            $this->db->query(
                "SELECT COUNT(DISTINCT lms_user_id) AS cnt FROM lms_activity_feed WHERE institution_id=? AND created_at>=?",
                [$inst, $dateFrom]
            );
            $kpis['active_learners'] = (int)($this->db->fetch()['cnt'] ?? 0);

            // Total enrollments
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_enrollments e JOIN lms_courses c ON c.id=e.course_id WHERE c.institution_id=? AND e.status='active'",
                [$inst]
            );
            $kpis['total_enrollments'] = (int)($this->db->fetch()['cnt'] ?? 0);

            // Completion rate
            $this->db->query(
                "SELECT
                    COUNT(*) AS total,
                    SUM(progress_pct=100) AS completed
                 FROM lms_enrollments e JOIN lms_courses c ON c.id=e.course_id
                 WHERE c.institution_id=? AND e.status='active'",
                [$inst]
            );
            $row = $this->db->fetch();
            $kpis['completion_rate'] = $row['total'] > 0
                ? round($row['completed'] / $row['total'] * 100, 1) : 0;

            // Quiz pass rate
            $this->db->query(
                "SELECT COUNT(*) AS total, SUM(passed) AS passed
                 FROM lms_quiz_attempts qa
                 JOIN lms_quizzes q ON q.id=qa.quiz_id
                 WHERE q.institution_id=? AND qa.status='submitted'",
                [$inst]
            );
            $row = $this->db->fetch();
            $kpis['quiz_pass_rate'] = $row['total'] > 0
                ? round($row['passed'] / $row['total'] * 100, 1) : 0;

            // Avg course progress
            $this->db->query(
                "SELECT ROUND(AVG(e.progress_pct),1) AS avg FROM lms_enrollments e
                 JOIN lms_courses c ON c.id=e.course_id WHERE c.institution_id=? AND e.status='active'",
                [$inst]
            );
            $kpis['avg_progress'] = (float)($this->db->fetch()['avg'] ?? 0);

            // Forum posts in range
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_forum_posts fp
                 JOIN lms_forum_threads ft ON ft.id=fp.thread_id
                 WHERE ft.institution_id=? AND fp.created_at>=? AND fp.deleted_at IS NULL",
                [$inst, $dateFrom]
            );
            $kpis['forum_posts'] = (int)($this->db->fetch()['cnt'] ?? 0);

            // New enrollments in range
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_enrollments e
                 JOIN lms_courses c ON c.id=e.course_id
                 WHERE c.institution_id=? AND e.enrolled_at>=?",
                [$inst, $dateFrom]
            );
            $kpis['new_enrollments'] = (int)($this->db->fetch()['cnt'] ?? 0);

        } catch (\Throwable $e) {}
        return $kpis;
    }

    private function _enrollTrend(string $dateFrom): array
    {
        try {
            $this->db->query(
                "SELECT DATE(e.enrolled_at) AS day, COUNT(*) AS cnt
                 FROM lms_enrollments e JOIN lms_courses c ON c.id=e.course_id
                 WHERE c.institution_id=? AND e.enrolled_at>=?
                 GROUP BY DATE(e.enrolled_at) ORDER BY day",
                [$this->institutionId, $dateFrom]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _activityTrend(string $dateFrom): array
    {
        try {
            $this->db->query(
                "SELECT DATE(created_at) AS day, event, COUNT(*) AS cnt
                 FROM lms_activity_feed
                 WHERE institution_id=? AND created_at>=?
                 GROUP BY DATE(created_at), event ORDER BY day",
                [$this->institutionId, $dateFrom]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _topCourses(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "c.institution_id={$this->institutionId}"
                : "c.instructor_id={$this->lmsUserId} AND c.institution_id={$this->institutionId}";
            $this->db->query(
                "SELECT c.id, c.title,
                        COUNT(DISTINCT e.lms_user_id) AS enrollments,
                        ROUND(AVG(e.progress_pct),1) AS avg_progress,
                        SUM(e.progress_pct=100) AS completions,
                        ROUND(100*SUM(e.progress_pct=100)/NULLIF(COUNT(*),0),1) AS completion_rate
                 FROM lms_courses c
                 LEFT JOIN lms_enrollments e ON e.course_id=c.id AND e.status='active'
                 WHERE {$scope} AND c.deleted_at IS NULL
                 GROUP BY c.id ORDER BY enrollments DESC LIMIT 8"
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _quizPassRates(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "q.institution_id={$this->institutionId}"
                : "q.created_by={$this->lmsUserId} AND q.institution_id={$this->institutionId}";
            $this->db->query(
                "SELECT q.title, c.title AS course_title,
                        COUNT(qa.id) AS attempts,
                        ROUND(100*SUM(qa.passed)/NULLIF(COUNT(qa.id),0),1) AS pass_rate,
                        ROUND(AVG(qa.percentage),1) AS avg_score
                 FROM lms_quizzes q
                 JOIN lms_courses c ON c.id=q.course_id
                 LEFT JOIN lms_quiz_attempts qa ON qa.quiz_id=q.id AND qa.status='submitted'
                 WHERE {$scope} AND q.deleted_at IS NULL
                 GROUP BY q.id HAVING attempts > 0
                 ORDER BY attempts DESC LIMIT 8"
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _progressBuckets(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "c.institution_id={$this->institutionId}"
                : "c.instructor_id={$this->lmsUserId} AND c.institution_id={$this->institutionId}";
            $this->db->query(
                "SELECT
                    SUM(e.progress_pct = 0)                           AS not_started,
                    SUM(e.progress_pct > 0  AND e.progress_pct <= 25) AS bucket_25,
                    SUM(e.progress_pct > 25 AND e.progress_pct <= 50) AS bucket_50,
                    SUM(e.progress_pct > 50 AND e.progress_pct <= 75) AS bucket_75,
                    SUM(e.progress_pct > 75 AND e.progress_pct < 100) AS bucket_99,
                    SUM(e.progress_pct = 100)                         AS completed
                 FROM lms_enrollments e
                 JOIN lms_courses c ON c.id=e.course_id
                 WHERE {$scope} AND e.status='active'"
            );
            return (array)($this->db->fetch() ?: []);
        } catch (\Throwable $e) { return []; }
    }

    private function _leaderboard(int $limit): array
    {
        try {
            $this->db->query(
                "SELECT id, CONCAT(first_name,' ',last_name) AS name, email, xp_points, level,
                        (SELECT COUNT(*) FROM lms_enrollments WHERE lms_user_id=u.id AND status='active') AS courses
                 FROM lms_users u
                 WHERE institution_id=? AND role='learner'
                 ORDER BY xp_points DESC LIMIT ?",
                [$this->institutionId, $limit]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _contentTypes(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "c.institution_id={$this->institutionId}"
                : "c.instructor_id={$this->lmsUserId} AND c.institution_id={$this->institutionId}";
            $this->db->query(
                "SELECT l.type, COUNT(*) AS cnt
                 FROM lms_lessons l JOIN lms_courses c ON c.id=l.course_id
                 WHERE {$scope} AND l.deleted_at IS NULL
                 GROUP BY l.type ORDER BY cnt DESC"
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _courseEnrollTrend(int $courseId, string $dateFrom): array
    {
        try {
            $this->db->query(
                "SELECT DATE(enrolled_at) AS day, COUNT(*) AS cnt
                 FROM lms_enrollments WHERE course_id=? AND enrolled_at>=?
                 GROUP BY DATE(enrolled_at) ORDER BY day",
                [$courseId, $dateFrom]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _lessonStats(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT l.id, l.title, l.type, l.sort_order, s.title AS section_title,
                        COUNT(DISTINCT lp.lms_user_id) AS completions,
                        (SELECT COUNT(*) FROM lms_enrollments WHERE course_id=? AND status='active') AS enrolled
                 FROM lms_lessons l
                 JOIN lms_course_sections s ON s.id=l.section_id
                 LEFT JOIN lms_lesson_progress lp ON lp.lesson_id=l.id AND lp.completed=1
                 WHERE l.course_id=? AND l.deleted_at IS NULL
                 GROUP BY l.id ORDER BY s.sort_order, l.sort_order",
                [$courseId, $courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _assignStats(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT a.title, a.max_score,
                        COUNT(sub.id)      AS submissions,
                        ROUND(AVG(sub.score / a.max_score * 100),1) AS avg_pct,
                        MIN(sub.score / a.max_score * 100)           AS min_pct,
                        MAX(sub.score / a.max_score * 100)           AS max_pct,
                        SUM(sub.is_late)                             AS late_count
                 FROM lms_assignments a
                 LEFT JOIN lms_assignment_submissions sub ON sub.assignment_id=a.id AND sub.score IS NOT NULL
                 WHERE a.course_id=? AND a.deleted_at IS NULL AND a.max_score > 0
                 GROUP BY a.id ORDER BY a.due_at",
                [$courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _quizStats(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT q.title, q.pass_percentage,
                        COUNT(qa.id) AS attempts,
                        COUNT(DISTINCT qa.lms_user_id) AS unique_takers,
                        ROUND(AVG(qa.percentage),1)    AS avg_pct,
                        ROUND(100*SUM(qa.passed)/NULLIF(COUNT(qa.id),0),1) AS pass_rate,
                        ROUND(AVG(qa.time_taken_s)/60, 1) AS avg_mins
                 FROM lms_quizzes q
                 LEFT JOIN lms_quiz_attempts qa ON qa.quiz_id=q.id AND qa.status='submitted'
                 WHERE q.course_id=? AND q.deleted_at IS NULL
                 GROUP BY q.id ORDER BY q.created_at",
                [$courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _courseAttStats(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT COUNT(DISTINCT s.id) AS sessions,
                        ROUND(AVG(CASE WHEN r.status IN('present','late','excused') THEN 100 ELSE 0 END),1) AS avg_attendance_pct
                 FROM lms_attendance_sessions s
                 LEFT JOIN lms_attendance_records r ON r.session_id=s.id
                 WHERE s.course_id=? AND s.institution_id=?",
                [$courseId, $this->institutionId]
            );
            return (array)($this->db->fetch() ?: ['sessions' => 0, 'avg_attendance_pct' => 0]);
        } catch (\Throwable $e) { return ['sessions' => 0, 'avg_attendance_pct' => 0]; }
    }

    private function _courseStudents(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, u.email,
                        e.progress_pct, e.enrolled_at, u.xp_points,
                        (SELECT MAX(qa.percentage) FROM lms_quiz_attempts qa
                         JOIN lms_quizzes q ON q.id=qa.quiz_id
                         WHERE qa.lms_user_id=u.id AND q.course_id=? AND qa.status='submitted') AS best_quiz,
                        (SELECT COUNT(*) FROM lms_assignment_submissions sub
                         JOIN lms_assignments a ON a.id=sub.assignment_id
                         WHERE sub.lms_user_id=u.id AND a.course_id=?) AS submissions
                 FROM lms_enrollments e
                 JOIN lms_users u ON u.id=e.lms_user_id
                 WHERE e.course_id=? AND e.status='active'
                 ORDER BY e.progress_pct DESC",
                [$courseId, $courseId, $courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _forumStats(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT COUNT(DISTINCT t.id) AS threads,
                        COUNT(DISTINCT p.id) AS posts,
                        SUM(t.is_solved)     AS solved
                 FROM lms_forum_threads t
                 LEFT JOIN lms_forum_posts p ON p.thread_id=t.id AND p.deleted_at IS NULL
                 WHERE t.course_id=? AND t.deleted_at IS NULL",
                [$courseId]
            );
            return (array)($this->db->fetch() ?: ['threads'=>0,'posts'=>0,'solved'=>0]);
        } catch (\Throwable $e) { return ['threads'=>0,'posts'=>0,'solved'=>0]; }
    }
}
