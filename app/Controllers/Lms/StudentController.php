<?php
namespace App\Controllers\Lms;

class StudentController extends LmsBaseController
{
    // ── Student list ───────────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('users.view');

        $search   = trim($this->input('q', ''));
        $courseId = (int)$this->input('course', 0);
        $status   = $this->input('status', 'active');
        $page     = max(1, (int)$this->input('page', 1));
        $perPage  = 25;
        $offset   = ($page - 1) * $perPage;

        // Build WHERE clause
        $where  = ['lu.institution_id=?', "lu.role='learner'", 'lu.deleted_at IS NULL'];
        $params = [$this->institutionId];

        if ($status === 'active')   { $where[] = "lu.status='active'"; }
        if ($status === 'inactive') { $where[] = "lu.status IN ('inactive','suspended')"; }

        if ($search !== '') {
            $where[]  = "(lu.first_name LIKE ? OR lu.last_name LIKE ? OR lu.email LIKE ? OR s.student_id_number LIKE ? OR s.roll_number LIKE ?)";
            $like     = "%{$search}%";
            $params   = array_merge($params, [$like, $like, $like, $like, $like]);
        }

        // Instructor: only students enrolled in their own courses
        if ($this->isLearner()) {
            // Learners should not access this list
            redirect(url('elms/dashboard')); return;
        }
        if (!$this->isAdmin()) {
            $where[]  = "lu.id IN (SELECT le.lms_user_id FROM lms_enrollments le
                                   JOIN lms_courses lc ON lc.id=le.course_id
                                   WHERE lc.instructor_id=? AND le.status='active')";
            $params[] = $this->lmsUserId;
        }

        if ($courseId) {
            $where[]  = "lu.id IN (SELECT lms_user_id FROM lms_enrollments WHERE course_id=? AND status='active')";
            $params[] = $courseId;
        }

        $w = implode(' AND ', $where);

        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_users lu
                 LEFT JOIN students s ON s.id = lu.student_id
                 WHERE {$w}",
                $params
            );
            $total = (int)($this->db->fetch()['cnt'] ?? 0);

            $this->db->query(
                "SELECT lu.id, lu.first_name, lu.last_name, lu.email, lu.avatar,
                        lu.xp_points, lu.level, lu.status, lu.last_active_at, lu.created_at,
                        lu.student_id,
                        s.student_id_number, s.roll_number, s.photo,
                        c.name AS program_name, b.name AS batch_name,
                        s.current_semester,
                        (SELECT COUNT(*) FROM lms_enrollments le
                         WHERE le.lms_user_id=lu.id AND le.status='active') AS enrolled_courses,
                        (SELECT ROUND(AVG(le.progress))
                         FROM lms_enrollments le
                         WHERE le.lms_user_id=lu.id AND le.status='active') AS avg_progress,
                        (SELECT COUNT(*) FROM lms_enrollments le
                         WHERE le.lms_user_id=lu.id AND le.status='completed') AS completed_courses
                 FROM lms_users lu
                 LEFT JOIN students s  ON s.id  = lu.student_id
                 LEFT JOIN courses  c  ON c.id  = s.course_id
                 LEFT JOIN batches  b  ON b.id  = s.batch_id
                 WHERE {$w}
                 ORDER BY lu.first_name, lu.last_name
                 LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            $students = $this->db->fetchAll();
        } catch (\Throwable $e) {
            $students = [];
            $total    = 0;
        }

        // Course filter dropdown
        $courses = $this->_getCourses();

        $totalPages = (int)ceil($total / $perPage) ?: 1;
        $pageTitle  = 'LMS Students';

        $this->view('lms/students/index', compact(
            'students', 'total', 'page', 'totalPages',
            'search', 'courseId', 'status', 'courses', 'pageTitle'
        ), 'main');
    }

    // ── Student profile ────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->authorize('users.view');

        // Load LMS user
        try {
            $this->db->query(
                "SELECT lu.*,
                        s.student_id_number, s.roll_number, s.gender, s.date_of_birth,
                        s.address_line1, s.city, s.state, s.phone AS acad_phone,
                        s.father_name, s.father_phone,
                        s.current_semester, s.admission_date, s.admission_type,
                        c.name  AS program_name,
                        b.name  AS batch_name,
                        b.code  AS batch_code,
                        d.name  AS dept_name,
                        ay.name AS academic_year
                 FROM lms_users lu
                 LEFT JOIN students    s  ON s.id  = lu.student_id
                 LEFT JOIN courses     c  ON c.id  = s.course_id
                 LEFT JOIN batches     b  ON b.id  = s.batch_id
                 LEFT JOIN departments d  ON d.id  = s.department_id
                 LEFT JOIN academic_years ay ON ay.id = s.academic_year_id
                 WHERE lu.id=? AND lu.institution_id=? AND lu.role='learner' AND lu.deleted_at IS NULL",
                [$id, $this->institutionId]
            );
            $student = $this->db->fetch();
        } catch (\Throwable $e) {
            $student = null;
        }

        if (!$student) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Student Not Found'], 'main');
            exit;
        }

        // Instructor scope check
        if (!$this->isAdmin()) {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_enrollments le
                 JOIN lms_courses lc ON lc.id=le.course_id
                 WHERE le.lms_user_id=? AND lc.instructor_id=?",
                [$id, $this->lmsUserId]
            );
            if (!(int)($this->db->fetch()['cnt'] ?? 0)) {
                redirect(url('elms/students')); return;
            }
        }

        // Enrolled courses
        try {
            $this->db->query(
                "SELECT le.*, lc.title, lc.code, lc.thumbnail, lc.status AS course_status,
                        lc.subject_id,
                        sub.subject_name, sub.subject_type, sub.subject_code, sub.semester,
                        sub.credits,
                        COALESCE(
                          (SELECT ROUND(AVG(CASE WHEN lp.status='completed' THEN 100 ELSE 0 END))
                           FROM lms_lesson_progress lp
                           JOIN lms_lessons ll ON ll.id=lp.lesson_id
                           WHERE lp.lms_user_id=le.lms_user_id AND ll.course_id=le.course_id),
                          le.progress
                        ) AS real_progress
                 FROM lms_enrollments le
                 JOIN lms_courses lc ON lc.id=le.course_id
                 LEFT JOIN subjects sub ON sub.id=lc.subject_id
                 WHERE le.lms_user_id=? AND le.institution_id=?
                 ORDER BY le.enrolled_at DESC",
                [$id, $this->institutionId]
            );
            $enrollments = $this->db->fetchAll();
        } catch (\Throwable $e) { $enrollments = []; }

        // Recent activity
        try {
            $this->db->query(
                "SELECT * FROM lms_activity_feed
                 WHERE lms_user_id=? AND institution_id=?
                 ORDER BY created_at DESC LIMIT 10",
                [$id, $this->institutionId]
            );
            $activity = $this->db->fetchAll();
        } catch (\Throwable $e) { $activity = []; }

        // Assignment submissions summary
        try {
            $this->db->query(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status='graded' THEN 1 ELSE 0 END) AS graded,
                        ROUND(AVG(CASE WHEN score IS NOT NULL THEN score ELSE NULL END),1) AS avg_score
                 FROM lms_assignment_submissions
                 WHERE lms_user_id=?",
                [$id]
            );
            $assignStats = $this->db->fetch() ?: [];
        } catch (\Throwable $e) { $assignStats = []; }

        // Quiz attempts summary
        try {
            $this->db->query(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN passed=1 THEN 1 ELSE 0 END) AS passed,
                        ROUND(AVG(percentage),1) AS avg_pct
                 FROM lms_quiz_attempts
                 WHERE lms_user_id=? AND status='submitted'",
                [$id]
            );
            $quizStats = $this->db->fetch() ?: [];
        } catch (\Throwable $e) { $quizStats = []; }

        $pageTitle = trim($student['first_name'].' '.$student['last_name']);
        $this->view('lms/students/show', compact(
            'student', 'enrollments', 'activity', 'assignStats', 'quizStats', 'pageTitle'
        ), 'main');
    }

    // ── Toggle status (AJAX) ───────────────────────────────────────
    public function toggleStatus(int $id): void
    {
        $this->authorize('users.manage');
        try {
            $this->db->query(
                "SELECT status FROM lms_users WHERE id=? AND institution_id=? AND role='learner'",
                [$id, $this->institutionId]
            );
            $u = $this->db->fetch();
            if (!$u) { $this->json(['error' => 'Not found'], 404); return; }

            $new = $u['status'] === 'active' ? 'inactive' : 'active';
            $this->db->query(
                "UPDATE lms_users SET status=?, updated_at=NOW() WHERE id=?",
                [$new, $id]
            );
            $this->json(['status' => 'ok', 'new_status' => $new]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Helpers ────────────────────────────────────────────────────
    private function _getCourses(): array
    {
        try {
            $where  = ['lc.institution_id=?', 'lc.deleted_at IS NULL'];
            $params = [$this->institutionId];
            if (!$this->isAdmin()) {
                $where[]  = 'lc.instructor_id=?';
                $params[] = $this->lmsUserId;
            }
            $this->db->query(
                "SELECT lc.id, lc.title, lc.code,
                        sub.subject_name, sub.subject_code
                 FROM lms_courses lc
                 LEFT JOIN subjects sub ON sub.id=lc.subject_id
                 WHERE ".implode(' AND ', $where)." ORDER BY lc.title",
                $params
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }
}
