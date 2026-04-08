<?php
namespace App\Controllers\Lms;

class DashboardController extends LmsBaseController
{
    public function index(): void
    {
        $uid  = $this->lmsUserId;
        $inst = $this->institutionId;
        $db   = $this->db;

        $stats       = [];
        $courses      = [];
        $deadlines    = [];
        $activity     = [];
        $announcements = [];
        $leaderboard  = [];
        $instructorStats = [];

        // ── Announcements (all roles) ─────────────────────────
        try {
            $db->query(
                "SELECT a.*, CONCAT(u.first_name,' ',u.last_name) AS author_name,
                        c.title AS course_title
                 FROM lms_announcements a
                 JOIN lms_users u ON u.id = a.author_id
                 LEFT JOIN lms_courses c ON c.id = a.course_id
                 WHERE a.institution_id = ?
                   AND a.deleted_at IS NULL
                   AND (a.expire_at IS NULL OR a.expire_at > NOW())
                   AND (
                       a.course_id IS NULL
                       OR a.course_id IN (
                           SELECT course_id FROM lms_enrollments
                           WHERE lms_user_id = ? AND status = 'active'
                       )
                   )
                 ORDER BY a.is_pinned DESC, a.publish_at DESC
                 LIMIT 5",
                [$inst, $uid]
            );
            $announcements = $db->fetchAll();
        } catch (\Throwable $e) {}

        // ── Role-based data ───────────────────────────────────
        if ($this->isLearner()) {
            $stats       = $this->_learnerStats($uid, $inst);
            $courses     = $this->_learnerCourses($uid);
            $deadlines   = $this->_upcomingDeadlines($uid, $inst);
            $activity    = $this->_recentActivity($uid, $inst);
            $leaderboard = $this->_leaderboard($inst, $uid);
        } else {
            // Instructor / Admin
            $instructorStats = $this->_instructorStats($uid, $inst);
            $courses         = $this->_instructorCourses($uid, $inst);
            $deadlines       = $this->_pendingGrading($uid, $inst);
            $activity        = $this->_recentActivity($uid, $inst);
        }

        $pageTitle   = 'LMS Dashboard';
        $greetingHour = (int)date('H');

        $this->view('lms/dashboard/index', compact(
            'stats', 'courses', 'deadlines', 'activity',
            'announcements', 'leaderboard', 'instructorStats',
            'pageTitle', 'greetingHour'
        ), 'main');
    }

    // ── LEARNER queries ───────────────────────────────────────

    private function _learnerStats(int $uid, int $inst): array
    {
        $stats = [
            'enrolled'    => 0, 'completed'   => 0,
            'in_progress' => 0, 'certificates' => 0,
            'xp_points'   => 0, 'level'        => 1,
            'assignments_due' => 0, 'quizzes_due' => 0,
            'avg_score'   => 0, 'streak_days'  => 0,
        ];
        try {
            $this->db->query(
                "SELECT
                    COUNT(*)                                 AS enrolled,
                    SUM(status='completed')                  AS completed,
                    SUM(status='active' AND progress > 0)   AS in_progress,
                    SUM(certificate_issued_at IS NOT NULL)   AS certificates,
                    ROUND(AVG(NULLIF(score,0)),1)             AS avg_score
                 FROM lms_enrollments
                 WHERE lms_user_id = ? AND status != 'dropped'",
                [$uid]
            );
            $row = $this->db->fetch();
            if ($row) {
                $stats['enrolled']     = (int)$row['enrolled'];
                $stats['completed']    = (int)$row['completed'];
                $stats['in_progress']  = (int)$row['in_progress'];
                $stats['certificates'] = (int)$row['certificates'];
                $stats['avg_score']    = (float)$row['avg_score'];
            }

            // XP & level from lms_users
            $this->db->query("SELECT xp_points, level FROM lms_users WHERE id = ?", [$uid]);
            $u = $this->db->fetch();
            if ($u) {
                $stats['xp_points'] = (int)$u['xp_points'];
                $stats['level']     = (int)$u['level'];
            }

            // Upcoming deadlines count
            $this->db->query(
                "SELECT type, COUNT(*) AS cnt
                 FROM lms_deadlines
                 WHERE lms_user_id = ? AND is_submitted = 0 AND due_at > NOW() AND due_at <= DATE_ADD(NOW(), INTERVAL 7 DAY)
                 GROUP BY type",
                [$uid]
            );
            foreach ($this->db->fetchAll() as $row) {
                if ($row['type'] === 'assignment') $stats['assignments_due'] = (int)$row['cnt'];
                if ($row['type'] === 'quiz')        $stats['quizzes_due']    = (int)$row['cnt'];
            }

            // Login streak (consecutive days with activity)
            $this->db->query(
                "SELECT COUNT(DISTINCT DATE(created_at)) AS streak
                 FROM lms_activity_feed
                 WHERE lms_user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
                [$uid]
            );
            $s = $this->db->fetch();
            $stats['streak_days'] = (int)($s['streak'] ?? 0);

        } catch (\Throwable $e) {}
        return $stats;
    }

    private function _learnerCourses(int $uid): array
    {
        try {
            $this->db->query(
                "SELECT c.id, c.title, c.slug, c.thumbnail, c.level, c.total_lessons,
                        c.duration_hours,
                        e.progress, e.lessons_completed, e.status AS enroll_status,
                        e.last_accessed_at, e.score,
                        CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
                        cat.name AS category_name, cat.color AS cat_color
                 FROM lms_enrollments e
                 JOIN lms_courses c   ON c.id = e.course_id
                 JOIN lms_users u     ON u.id = c.instructor_id
                 LEFT JOIN lms_categories cat ON cat.id = c.category_id
                 WHERE e.lms_user_id = ? AND e.status IN ('active','completed')
                 ORDER BY e.last_accessed_at DESC, e.enrolled_at DESC
                 LIMIT 6",
                [$uid]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function _upcomingDeadlines(int $uid, int $inst): array
    {
        try {
            $this->db->query(
                "SELECT d.*, c.title AS course_title
                 FROM lms_deadlines d
                 JOIN lms_courses c ON c.id = d.course_id
                 WHERE d.lms_user_id = ? AND d.is_submitted = 0
                   AND d.due_at > NOW()
                 ORDER BY d.due_at ASC
                 LIMIT 8",
                [$uid]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function _leaderboard(int $inst, int $currentUid): array
    {
        try {
            $this->db->query(
                "SELECT id, first_name, last_name, xp_points, level, avatar
                 FROM lms_users
                 WHERE institution_id = ? AND role = 'learner' AND status = 'active'
                 ORDER BY xp_points DESC
                 LIMIT 5",
                [$inst]
            );
            $rows = $this->db->fetchAll();
            foreach ($rows as &$r) {
                $r['is_me'] = ((int)$r['id'] === $currentUid);
            }
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ── INSTRUCTOR / ADMIN queries ────────────────────────────

    private function _instructorStats(int $uid, int $inst): array
    {
        $stats = [
            'my_courses'       => 0, 'total_learners'   => 0,
            'pending_grading'  => 0, 'avg_completion'   => 0,
            'live_upcoming'    => 0, 'forum_pending'    => 0,
            'total_courses'    => 0, 'active_learners'  => 0,
        ];
        try {
            // Instructor's own course stats
            $this->db->query(
                "SELECT COUNT(*) AS cnt,
                        COALESCE(SUM(enrolled_count),0) AS total_learners
                 FROM lms_courses
                 WHERE instructor_id = ? AND deleted_at IS NULL",
                [$uid]
            );
            $r = $this->db->fetch();
            $stats['my_courses']     = (int)($r['cnt'] ?? 0);
            $stats['total_learners'] = (int)($r['total_learners'] ?? 0);

            // Average completion rate across my courses
            $this->db->query(
                "SELECT ROUND(AVG(e.progress),1) AS avg_pct
                 FROM lms_enrollments e
                 JOIN lms_courses c ON c.id = e.course_id
                 WHERE c.instructor_id = ? AND e.status = 'active'",
                [$uid]
            );
            $r2 = $this->db->fetch();
            $stats['avg_completion'] = (float)($r2['avg_pct'] ?? 0);

            if ($this->isAdmin()) {
                // Platform totals
                $this->db->query(
                    "SELECT COUNT(*) AS cnt FROM lms_courses WHERE institution_id = ? AND deleted_at IS NULL",
                    [$inst]
                );
                $stats['total_courses'] = (int)($this->db->fetch()['cnt'] ?? 0);

                $this->db->query(
                    "SELECT COUNT(*) AS cnt FROM lms_users WHERE institution_id = ? AND status = 'active' AND role = 'learner'",
                    [$inst]
                );
                $stats['active_learners'] = (int)($this->db->fetch()['cnt'] ?? 0);
            }
        } catch (\Throwable $e) {}
        return $stats;
    }

    private function _instructorCourses(int $uid, int $inst): array
    {
        try {
            $scope = $this->isAdmin()
                ? "c.institution_id = {$inst}"
                : "c.instructor_id = {$uid}";

            $this->db->query(
                "SELECT c.id, c.title, c.slug, c.thumbnail, c.status,
                        c.enrolled_count, c.total_lessons, c.rating_avg,
                        cat.name AS category_name, cat.color AS cat_color,
                        (SELECT COUNT(*) FROM lms_enrollments e WHERE e.course_id = c.id AND e.status = 'completed') AS completed_count
                 FROM lms_courses c
                 LEFT JOIN lms_categories cat ON cat.id = c.category_id
                 WHERE {$scope} AND c.deleted_at IS NULL
                 ORDER BY c.enrolled_count DESC, c.created_at DESC
                 LIMIT 6"
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function _pendingGrading(int $uid, int $inst): array
    {
        $items = [];
        $scope = $this->isAdmin()
            ? "lc.institution_id = {$inst}"
            : "lc.institution_id = {$inst} AND lc.instructor_id = {$uid}";

        try {
            // Ungraded assignment submissions
            $this->db->query(
                "SELECT 'assignment' AS type, a.id, a.title,
                        lc.title AS course_title, lc.id AS course_id,
                        CONCAT(lu.first_name,' ',lu.last_name) AS student_name,
                        s.submitted_at
                 FROM lms_assignment_submissions s
                 JOIN lms_assignments a  ON a.id = s.assignment_id
                 JOIN lms_courses lc     ON lc.id = a.course_id
                 JOIN lms_users lu       ON lu.id = s.lms_user_id
                 WHERE s.status = 'submitted' AND {$scope} AND lc.deleted_at IS NULL
                 ORDER BY s.submitted_at ASC LIMIT 10"
            );
            $items = array_merge($items, $this->db->fetchAll());

            // Quiz attempts needing manual review (status='submitted' not yet graded)
            $this->db->query(
                "SELECT 'quiz' AS type, q.id, q.title,
                        lc.title AS course_title, lc.id AS course_id,
                        CONCAT(lu.first_name,' ',lu.last_name) AS student_name,
                        qa.submitted_at
                 FROM lms_quiz_attempts qa
                 JOIN lms_quizzes q  ON q.id = qa.quiz_id
                 JOIN lms_courses lc ON lc.id = q.course_id
                 JOIN lms_users lu   ON lu.id = qa.lms_user_id
                 WHERE qa.status = 'submitted' AND {$scope} AND lc.deleted_at IS NULL
                 ORDER BY qa.submitted_at ASC LIMIT 5"
            );
            $items = array_merge($items, $this->db->fetchAll());

            // Sort combined by submitted_at
            usort($items, fn($a, $b) => strtotime($a['submitted_at']) - strtotime($b['submitted_at']));
            return array_slice($items, 0, 15);
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ── Shared queries ────────────────────────────────────────

    private function _recentActivity(int $uid, int $inst): array
    {
        try {
            $this->db->query(
                "SELECT a.event, a.entity_title, a.xp_earned, a.created_at, a.meta
                 FROM lms_activity_feed a
                 WHERE a.lms_user_id = ?
                 ORDER BY a.created_at DESC
                 LIMIT 10",
                [$uid]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ── Widget: mark announcement read (AJAX) ─────────────────
    public function dismissAnnouncement(int $id): void
    {
        // Store in session to avoid re-showing on page reload
        $dismissed = $this->session->get('lms_dismissed_ann', []);
        $dismissed[] = $id;
        $this->session->set('lms_dismissed_ann', array_unique($dismissed));
        $this->json(['status' => 'ok']);
    }
}
