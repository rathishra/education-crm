<?php
namespace App\Controllers\Portal;

class LmsController extends PortalBaseController
{
    // ── My Courses (enrolled via LMS) ─────────────────────────────
    public function index(): void
    {
        $lmsUserId = $this->_getLmsUserId();

        $enrolledCourses = [];
        $deadlines       = [];
        $materials        = []; // legacy academic materials
        $batchId          = $this->getStudentBatchId();

        if ($lmsUserId) {
            try {
                // Enrolled courses with progress
                $this->db->query(
                    "SELECT le.id AS enrollment_id, le.progress, le.status AS enroll_status,
                            le.enrolled_at, le.completed_at,
                            lc.id AS course_id, lc.title, lc.code, lc.thumbnail,
                            lc.short_description, lc.total_lessons,
                            lc.subject_id,
                            sub.subject_name, sub.subject_code, sub.subject_type,
                            sub.credits, sub.semester,
                            CONCAT(lu.first_name,' ',lu.last_name) AS instructor_name,
                            (SELECT COUNT(*) FROM lms_lessons ll WHERE ll.course_id=lc.id AND ll.is_published=1 AND ll.deleted_at IS NULL) AS lesson_count,
                            (SELECT COUNT(*) FROM lms_lesson_progress lp
                             WHERE lp.lms_user_id=? AND lp.lesson_id IN (SELECT id FROM lms_lessons WHERE course_id=lc.id) AND lp.status='completed') AS completed_lessons
                     FROM lms_enrollments le
                     JOIN lms_courses lc ON lc.id=le.course_id AND lc.deleted_at IS NULL
                     JOIN lms_users lu ON lu.id=lc.instructor_id
                     LEFT JOIN subjects sub ON sub.id=lc.subject_id
                     WHERE le.lms_user_id=? AND le.institution_id=? AND le.status IN ('active','completed')
                     ORDER BY le.status ASC, lc.title ASC",
                    [$lmsUserId, $lmsUserId, $this->institutionId]
                );
                $enrolledCourses = $this->db->fetchAll();

                // Upcoming deadlines
                $this->db->query(
                    "SELECT d.title, d.type, d.due_at, d.is_submitted,
                            lc.title AS course_title, lc.id AS course_id
                     FROM lms_deadlines d
                     JOIN lms_courses lc ON lc.id=d.course_id
                     WHERE d.lms_user_id=? AND d.institution_id=? AND d.is_submitted=0
                       AND d.due_at >= NOW()
                     ORDER BY d.due_at ASC LIMIT 8",
                    [$lmsUserId, $this->institutionId]
                );
                $deadlines = $this->db->fetchAll();
            } catch (\Throwable $e) {}
        }

        // Also load legacy academic materials (backward compat)
        if ($batchId) {
            try {
                $this->db->query(
                    "SELECT lm.*, sub.subject_name, sub.subject_code,
                            CONCAT(u.first_name,' ',COALESCE(u.last_name,'')) AS faculty_name
                     FROM lms_materials lm
                     JOIN subjects sub ON sub.id = lm.subject_id
                     JOIN users u ON u.id = lm.faculty_id
                     WHERE lm.batch_id=? AND lm.institution_id=? AND lm.is_published=1 AND lm.deleted_at IS NULL
                     ORDER BY lm.created_at DESC",
                    [$batchId, $this->institutionId]
                );
                $materials = $this->db->fetchAll();
            } catch (\Throwable $e) {}
        }

        $pageTitle = 'My Learning';
        $this->view('portal/lms/index', compact(
            'enrolledCourses', 'deadlines', 'materials', 'lmsUserId', 'pageTitle'
        ));
    }

    // ── Course Detail ──────────────────────────────────────────────
    public function course(int $id): void
    {
        $lmsUserId = $this->_getLmsUserId();
        if (!$lmsUserId) {
            flash('errors', ['Your LMS account has not been provisioned.']);
            redirect(url('portal/student/lms'));
            return;
        }

        // Verify enrollment
        try {
            $this->db->query(
                "SELECT le.*, lc.title, lc.code, lc.description, lc.thumbnail,
                        lc.pass_percentage, lc.total_lessons,
                        sub.subject_name, sub.subject_code,
                        CONCAT(lu.first_name,' ',lu.last_name) AS instructor_name
                 FROM lms_enrollments le
                 JOIN lms_courses lc ON lc.id=le.course_id
                 JOIN lms_users lu ON lu.id=lc.instructor_id
                 LEFT JOIN subjects sub ON sub.id=lc.subject_id
                 WHERE le.course_id=? AND le.lms_user_id=? AND le.status IN ('active','completed')",
                [$id, $lmsUserId]
            );
            $enrollment = $this->db->fetch();
        } catch (\Throwable $e) { $enrollment = null; }

        if (!$enrollment) {
            flash('errors', ['You are not enrolled in this course.']);
            redirect(url('portal/student/lms'));
            return;
        }

        // Sections + lessons
        try {
            $this->db->query(
                "SELECT cs.id, cs.title AS section_title, cs.sort_order
                 FROM lms_course_sections cs
                 WHERE cs.course_id=? ORDER BY cs.sort_order",
                [$id]
            );
            $sections = $this->db->fetchAll();

            foreach ($sections as &$sec) {
                $this->db->query(
                    "SELECT l.id, l.title, l.type, l.is_free, l.xp_reward,
                            COALESCE(lp.status,'not_started') AS progress_status
                     FROM lms_lessons l
                     LEFT JOIN lms_lesson_progress lp ON lp.lesson_id=l.id AND lp.lms_user_id=?
                     WHERE l.section_id=? AND l.is_published=1 AND l.deleted_at IS NULL
                     ORDER BY l.sort_order",
                    [$lmsUserId, $sec['id']]
                );
                $sec['lessons'] = $this->db->fetchAll();
            }
            unset($sec);
        } catch (\Throwable $e) { $sections = []; }

        // Assignments
        try {
            $this->db->query(
                "SELECT a.id, a.title, a.due_at, a.max_score,
                        s.status AS sub_status, s.score, s.submitted_at
                 FROM lms_assignments a
                 LEFT JOIN lms_assignment_submissions s ON s.assignment_id=a.id AND s.lms_user_id=?
                 WHERE a.course_id=? AND a.is_published=1 AND a.deleted_at IS NULL
                 ORDER BY a.due_at",
                [$lmsUserId, $id]
            );
            $assignments = $this->db->fetchAll();
        } catch (\Throwable $e) { $assignments = []; }

        // Quizzes
        try {
            $this->db->query(
                "SELECT q.id, q.title, q.due_at, q.time_limit_mins, q.attempts_allowed, q.pass_percentage,
                        (SELECT MAX(qa.percentage) FROM lms_quiz_attempts qa WHERE qa.quiz_id=q.id AND qa.lms_user_id=?) AS best_score,
                        (SELECT COUNT(*) FROM lms_quiz_attempts qa2 WHERE qa2.quiz_id=q.id AND qa2.lms_user_id=?) AS attempt_count
                 FROM lms_quizzes q
                 WHERE q.course_id=? AND q.is_published=1 AND q.deleted_at IS NULL
                 ORDER BY q.due_at",
                [$lmsUserId, $lmsUserId, $id]
            );
            $quizzes = $this->db->fetchAll();
        } catch (\Throwable $e) { $quizzes = []; }

        $pageTitle = $enrollment['title'];
        $this->view('portal/lms/course', compact(
            'enrollment', 'sections', 'assignments', 'quizzes', 'lmsUserId', 'pageTitle'
        ));
    }

    // ── Legacy download (for lms_materials) ───────────────────────
    public function download(int $id): void
    {
        $batchId = $this->getStudentBatchId();
        if (!$batchId) {
            flash('errors', ['Access denied.']);
            redirect(url('portal/student/lms'));
            return;
        }

        $this->db->query(
            "SELECT * FROM lms_materials
             WHERE id = ? AND batch_id = ? AND is_published = 1 AND deleted_at IS NULL LIMIT 1",
            [$id, $batchId]
        );
        $material = $this->db->fetch();

        if (!$material) {
            flash('errors', ['Material not found or not accessible.']);
            redirect(url('portal/student/lms'));
            return;
        }

        $this->db->query("UPDATE lms_materials SET download_count = download_count + 1 WHERE id = ?", [$id]);

        $filePath = BASE_PATH . '/public/' . ltrim($material['file_path'], '/');
        if (!file_exists($filePath)) {
            flash('errors', ['File not found on server.']);
            redirect(url('portal/student/lms'));
            return;
        }

        $fileName = $material['file_name'] ?? basename($filePath);
        $mimeType = $material['mime_type'] ?? 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }

    // ── Helper: resolve LMS user for current student ─────────────
    private function _getLmsUserId(): ?int
    {
        if (!$this->studentId) return null;
        try {
            $this->db->query(
                "SELECT id FROM lms_users
                 WHERE student_id=? AND institution_id=? AND deleted_at IS NULL AND status='active'",
                [$this->studentId, $this->institutionId]
            );
            $r = $this->db->fetch();
            return $r ? (int)$r['id'] : null;
        } catch (\Throwable $e) { return null; }
    }
}
