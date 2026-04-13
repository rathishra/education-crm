<?php
namespace App\Controllers\Lms;

class AssignmentController extends LmsBaseController
{
    // ── Index — instructor: all assignments; learner: their submissions ──
    public function index(): void
    {
        $this->authorize('assignments.view');

        $page    = max(1, (int)$this->input('page', 1));
        $perPage = 15;
        $offset  = ($page - 1) * $perPage;
        $search  = trim($this->input('search', ''));
        $status  = $this->input('status', '');
        $courseId = (int)$this->input('course', 0);

        if ($this->isInstructor()) {
            [$assignments, $total] = $this->_instructorList($search, $status, $courseId, $perPage, $offset);
        } else {
            [$assignments, $total] = $this->_learnerList($search, $status, $courseId, $perPage, $offset);
        }

        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        $myCourses  = $this->_getMyCourses();
        $pageTitle  = 'Assignments';

        $this->view('lms/assignments/index', compact(
            'assignments', 'total', 'page', 'totalPages',
            'search', 'status', 'courseId', 'myCourses', 'pageTitle'
        ), 'main');
    }

    // ── Create form ───────────────────────────────────────────
    public function create(): void
    {
        $this->authorize('assignments.create');
        $courses   = $this->_getMyCourses();
        $courseId  = (int)$this->input('course_id', 0);
        $lessons   = $courseId ? $this->_getLessonsForCourse($courseId) : [];
        $pageTitle = 'Create Assignment';
        $this->view('lms/assignments/form', compact('courses', 'courseId', 'lessons', 'pageTitle'), 'main');
    }

    // ── Store ─────────────────────────────────────────────────
    public function store(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('assignments.create');
        $data = $_POST;

        $errors = $this->_validate($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $id = $this->db->insert('lms_assignments', [
                'course_id'          => (int)$data['course_id'],
                'lesson_id'          => !empty($data['lesson_id']) ? (int)$data['lesson_id'] : null,
                'institution_id'     => $this->institutionId,
                'created_by'         => $this->lmsUserId,
                'title'              => trim($data['title']),
                'instructions'       => trim($data['instructions']),
                'max_score'          => (int)($data['max_score'] ?: 100),
                'pass_score'         => (int)($data['pass_score'] ?: 50),
                'submission_type'    => $data['submission_type'] ?: 'any',
                'allowed_file_types' => trim($data['allowed_file_types'] ?? '') ?: null,
                'max_file_size_mb'   => max(1, (int)($data['max_file_size_mb'] ?: 10)),
                'due_at'             => !empty($data['due_at']) ? $data['due_at'] : null,
                'allow_late'         => isset($data['allow_late']) ? 1 : 0,
                'late_penalty_pct'   => (int)($data['late_penalty_pct'] ?? 0),
                'attempts_allowed'   => max(0, (int)($data['attempts_allowed'] ?: 1)),
                'is_published'       => isset($data['is_published']) ? 1 : 0,
                'rubric'             => !empty($data['rubric']) ? $data['rubric'] : null,
            ]);

            // Sync deadline records for enrolled learners
            $this->_syncDeadlines((int)$id, (int)$data['course_id'], $data['due_at'] ?? null, trim($data['title']));

            $this->audit('assignment.created', 'assignment', (int)$id, ['title' => $data['title']]);
            flash('success', "Assignment \"{$data['title']}\" created.");
            redirect(url("elms/assignments/{$id}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to create assignment.']);
            back();
        }
    }

    // ── Show (instructor detail / learner submission form) ────
    public function show(int $id): void
    {
        $this->authorize('assignments.view');
        $assignment = $this->_findAssignment($id);

        if ($this->isInstructor()) {
            $this->_showInstructor($assignment);
        } else {
            $this->_showLearner($assignment);
        }
    }

    private function _showInstructor(array $a): void
    {
        $id = (int)$a['id'];
        try {
            $this->db->query(
                "SELECT s.*,
                        CONCAT(u.first_name,' ',u.last_name) AS learner_name,
                        u.email AS learner_email,
                        CONCAT(g.first_name,' ',g.last_name) AS grader_name
                 FROM lms_assignment_submissions s
                 JOIN lms_users u ON u.id = s.lms_user_id
                 LEFT JOIN lms_users g ON g.id = s.graded_by
                 WHERE s.assignment_id = ?
                 ORDER BY s.submitted_at DESC",
                [$id]
            );
            $submissions = $this->db->fetchAll();

            $this->db->query(
                "SELECT COUNT(*) AS enrolled,
                        SUM(status='completed') AS completed
                 FROM lms_enrollments WHERE course_id = ?",
                [$a['course_id']]
            );
            $enrollStats = $this->db->fetch();
        } catch (\Throwable $e) {
            $submissions = [];
            $enrollStats = ['enrolled' => 0, 'completed' => 0];
        }

        $stats = [
            'submitted' => count(array_filter($submissions, fn($s) => $s['status'] !== 'graded')),
            'graded'    => count(array_filter($submissions, fn($s) => $s['status'] === 'graded')),
            'avg_score' => count($submissions) ? round(array_sum(array_column(
                array_filter($submissions, fn($s) => $s['score'] !== null), 'score'
            )) / max(1, count(array_filter($submissions, fn($s) => $s['score'] !== null))), 1) : 0,
            'enrolled'  => (int)($enrollStats['enrolled'] ?? 0),
        ];

        $pageTitle = $a['title'];
        $this->view('lms/assignments/show', compact('assignment', 'submissions', 'stats', 'pageTitle'), 'main');
    }

    private function _showLearner(array $a): void
    {
        // Check enrollment
        try {
            $this->db->query(
                "SELECT * FROM lms_enrollments WHERE course_id = ? AND lms_user_id = ?",
                [$a['course_id'], $this->lmsUserId]
            );
            $enrollment = $this->db->fetch();
        } catch (\Throwable $e) { $enrollment = null; }

        // Previous submissions
        try {
            $this->db->query(
                "SELECT * FROM lms_assignment_submissions
                 WHERE assignment_id = ? AND lms_user_id = ?
                 ORDER BY attempt DESC",
                [$a['id'], $this->lmsUserId]
            );
            $submissions = $this->db->fetchAll();
        } catch (\Throwable $e) { $submissions = []; }

        $attemptCount    = count($submissions);
        $attemptsAllowed = (int)$a['attempts_allowed'];
        $canSubmit       = $enrollment && ($attemptsAllowed === 0 || $attemptCount < $attemptsAllowed);
        $isOverdue       = $a['due_at'] && strtotime($a['due_at']) < time() && !$a['allow_late'];
        if ($isOverdue) $canSubmit = false;

        $pageTitle = $a['title'];
        $this->view('lms/assignments/submit', compact(
            'assignment', 'submissions', 'enrollment', 'canSubmit', 'isOverdue', 'attemptCount', 'pageTitle'
        ), 'main');
    }

    // ── Edit ──────────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('assignments.create');
        $assignment = $this->_findAssignment($id, true);
        $courses    = $this->_getMyCourses();
        $lessons    = $this->_getLessonsForCourse((int)$assignment['course_id']);
        $pageTitle  = 'Edit Assignment';
        $this->view('lms/assignments/form', compact('assignment', 'courses', 'lessons', 'pageTitle'), 'main');
    }

    // ── Update ────────────────────────────────────────────────
    public function update(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('assignments.create');
        $assignment = $this->_findAssignment($id, true);
        $data = $_POST;

        $errors = $this->_validate($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $sets = implode(', ', array_map(fn($k) => "`{$k}` = ?", [
                'title','instructions','max_score','pass_score','submission_type',
                'allowed_file_types','max_file_size_mb','due_at','allow_late',
                'late_penalty_pct','attempts_allowed','is_published','lesson_id'
            ]));
            $this->db->query("UPDATE lms_assignments SET {$sets} WHERE id = ?", [
                trim($data['title']),
                trim($data['instructions']),
                (int)($data['max_score'] ?: 100),
                (int)($data['pass_score'] ?: 50),
                $data['submission_type'] ?: 'any',
                trim($data['allowed_file_types'] ?? '') ?: null,
                max(1, (int)($data['max_file_size_mb'] ?: 10)),
                !empty($data['due_at']) ? $data['due_at'] : null,
                isset($data['allow_late']) ? 1 : 0,
                (int)($data['late_penalty_pct'] ?? 0),
                max(0, (int)($data['attempts_allowed'] ?: 1)),
                isset($data['is_published']) ? 1 : 0,
                !empty($data['lesson_id']) ? (int)$data['lesson_id'] : null,
                $id,
            ]);

            $this->_syncDeadlines($id, (int)$assignment['course_id'], $data['due_at'] ?? null, trim($data['title']));
            $this->audit('assignment.updated', 'assignment', $id);
            flash('success', 'Assignment updated.');
            redirect(url("elms/assignments/{$id}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to update assignment.']);
            back();
        }
    }

    // ── Delete ────────────────────────────────────────────────
    public function destroy(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('assignments.create');
        $this->_findAssignment($id, true);
        try {
            $this->db->query("UPDATE lms_assignments SET deleted_at = NOW() WHERE id = ?", [$id]);
            flash('success', 'Assignment deleted.');
        } catch (\Throwable $e) { flash('errors', ['Failed.']); }
        redirect(url('elms/assignments'));
    }

    // ── Submit (learner) ──────────────────────────────────────
    public function submit(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('assignments.view');
        $assignment = $this->_findAssignment($id);

        if ($this->isInstructor()) { redirect(url("elms/assignments/{$id}")); return; }

        // Verify enrollment
        try {
            $this->db->query(
                "SELECT id FROM lms_enrollments WHERE course_id = ? AND lms_user_id = ?",
                [$assignment['course_id'], $this->lmsUserId]
            );
            if (!$this->db->fetch()) { flash('errors', ['You are not enrolled in this course.']); back(); return; }
        } catch (\Throwable $e) { flash('errors', ['Enrollment check failed.']); back(); return; }

        // Count previous attempts
        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_assignment_submissions WHERE assignment_id = ? AND lms_user_id = ?",
                [$id, $this->lmsUserId]
            );
            $attemptCount = (int)($this->db->fetch()['cnt'] ?? 0);
        } catch (\Throwable $e) { $attemptCount = 0; }

        $attemptsAllowed = (int)$assignment['attempts_allowed'];
        if ($attemptsAllowed > 0 && $attemptCount >= $attemptsAllowed) {
            flash('errors', ['Maximum attempts reached.']); back(); return;
        }

        $isLate = $assignment['due_at'] && strtotime($assignment['due_at']) < time();
        if ($isLate && !$assignment['allow_late']) {
            flash('errors', ['The submission deadline has passed.']); back(); return;
        }

        $subType = $this->input('submission_type', 'text');
        $text    = trim($this->input('text_content', ''));
        $url     = trim($this->input('url_content', ''));

        $filePath = $fileOrig = null;
        if ($subType === 'file' && !empty($_FILES['submission_file']['name'])) {
            $allowed = array_filter(array_map('trim', explode(',', $assignment['allowed_file_types'] ?? '')));
            $up = $this->uploadFile('submission_file', 'lms/submissions/' . $assignment['course_id']);
            if ($up) {
                $filePath = $up['file_path'];
                $fileOrig = $up['original_name'];
            } else {
                flash('errors', ['File upload failed. Check file type and size.']); back(); return;
            }
        }

        if ($subType === 'text' && !$text) { flash('errors', ['Submission text cannot be empty.']); back(); return; }
        if ($subType === 'url'  && !$url)  { flash('errors', ['Please provide a URL.']); back(); return; }
        if ($subType === 'file' && !$filePath) { flash('errors', ['Please upload a file.']); back(); return; }

        try {
            $this->db->insert('lms_assignment_submissions', [
                'assignment_id'   => $id,
                'lms_user_id'     => $this->lmsUserId,
                'attempt'         => $attemptCount + 1,
                'submission_type' => $subType,
                'text_content'    => $text ?: null,
                'file_path'       => $filePath,
                'file_original'   => $fileOrig,
                'url_content'     => $url ?: null,
                'status'          => $isLate ? 'late' : 'submitted',
                'is_late'         => $isLate ? 1 : 0,
            ]);

            // Mark deadline submitted
            try {
                $this->db->query(
                    "UPDATE lms_deadlines SET is_submitted = 1 WHERE entity_id = ? AND lms_user_id = ? AND type = 'assignment'",
                    [$id, $this->lmsUserId]
                );
            } catch (\Throwable $e) {}

            // Activity feed
            try {
                $this->db->query(
                    "INSERT INTO lms_activity_feed (lms_user_id, institution_id, event, entity_type, entity_id, entity_title, xp_earned)
                     VALUES (?, ?, 'assignment_submitted', 'assignment', ?, ?, 5)",
                    [$this->lmsUserId, $this->institutionId, $id, $assignment['title']]
                );
            } catch (\Throwable $e) {}

            flash('success', 'Assignment submitted successfully!');
            redirect(url("elms/assignments/{$id}"));
        } catch (\Throwable $e) {
            flash('errors', ['Submission failed. Please try again.']);
            back();
        }
    }

    // ── Grade submission ──────────────────────────────────────
    public function grade(int $id, int $submissionId): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('assignments.grade');
        $assignment = $this->_findAssignment($id, true);

        try {
            $this->db->query("SELECT * FROM lms_assignment_submissions WHERE id = ? AND assignment_id = ?", [$submissionId, $id]);
            $sub = $this->db->fetch();
        } catch (\Throwable $e) { $sub = null; }
        if (!$sub) { $this->json(['error' => 'Submission not found'], 404); return; }

        $score    = $this->input('score');
        $feedback = trim($this->input('feedback', ''));

        if ($score === null || !is_numeric($score)) { $this->json(['error' => 'Valid score required'], 400); return; }
        $score = min((float)$score, (float)$assignment['max_score']);

        // Apply late penalty
        if ($sub['is_late'] && $assignment['late_penalty_pct'] > 0) {
            $score = round($score * (1 - $assignment['late_penalty_pct'] / 100), 2);
        }

        try {
            $this->db->query(
                "UPDATE lms_assignment_submissions
                 SET score = ?, feedback = ?, status = 'graded', graded_by = ?, graded_at = NOW()
                 WHERE id = ?",
                [$score, $feedback ?: null, $this->lmsUserId, $submissionId]
            );
            $this->audit('assignment.graded', 'submission', $submissionId, ['score' => $score]);
            $this->json(['status' => 'ok', 'score' => $score, 'feedback' => $feedback]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Grading failed'], 500);
        }
    }

    // ── Download submission file ──────────────────────────────
    public function download(int $id, int $submissionId): void
    {
        $this->authorize('assignments.grade');
        try {
            $this->db->query(
                "SELECT s.file_path, s.file_original FROM lms_assignment_submissions s WHERE s.id = ? AND s.assignment_id = ?",
                [$submissionId, $id]
            );
            $sub = $this->db->fetch();
        } catch (\Throwable $e) { $sub = null; }

        if (!$sub || !$sub['file_path']) { http_response_code(404); echo 'File not found'; exit; }

        $fullPath = BASE_PATH . '/public/' . $sub['file_path'];
        if (!file_exists($fullPath)) { http_response_code(404); echo 'File not found'; exit; }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . ($sub['file_original'] ?: basename($sub['file_path'])) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    // ── Helpers ───────────────────────────────────────────────

    private function _findAssignment(int $id, bool $instructorOnly = false): array
    {
        try {
            $this->db->query(
                "SELECT a.*, c.title AS course_title,
                        CONCAT(u.first_name,' ',u.last_name) AS creator_name
                 FROM lms_assignments a
                 JOIN lms_courses c ON c.id = a.course_id
                 JOIN lms_users u   ON u.id = a.created_by
                 WHERE a.id = ? AND a.institution_id = ? AND a.deleted_at IS NULL",
                [$id, $this->institutionId]
            );
            $a = $this->db->fetch();
        } catch (\Throwable $e) { $a = null; }

        if (!$a) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Not Found'], 'main');
            exit;
        }
        if ($instructorOnly && !$this->isInstructor() && (int)$a['created_by'] !== $this->lmsUserId) {
            http_response_code(403);
            $this->view('lms/errors/403', ['pageTitle' => 'Access Denied'], 'main');
            exit;
        }
        return $a;
    }

    private function _instructorList(string $search, string $status, int $courseId, int $perPage, int $offset): array
    {
        $where  = ['a.institution_id = ?', 'a.deleted_at IS NULL'];
        $params = [$this->institutionId];

        if (!$this->isAdmin()) {
            $where[]  = 'a.created_by = ?';
            $params[] = $this->lmsUserId;
        }
        if ($search) {
            $where[]  = 'a.title LIKE ?';
            $params[] = "%{$search}%";
        }
        if ($courseId) {
            $where[]  = 'a.course_id = ?';
            $params[] = $courseId;
        }
        $whereSQL = implode(' AND ', $where);

        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM lms_assignments a WHERE {$whereSQL}", $params);
            $total = (int)($this->db->fetch()['cnt'] ?? 0);

            $this->db->query(
                "SELECT a.*, c.title AS course_title,
                        (SELECT COUNT(*) FROM lms_assignment_submissions s WHERE s.assignment_id = a.id) AS sub_count,
                        (SELECT COUNT(*) FROM lms_assignment_submissions s WHERE s.assignment_id = a.id AND s.status='graded') AS graded_count
                 FROM lms_assignments a
                 JOIN lms_courses c ON c.id = a.course_id
                 WHERE {$whereSQL}
                 ORDER BY a.created_at DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _learnerList(string $search, string $status, int $courseId, int $perPage, int $offset): array
    {
        $where  = [
            'a.institution_id = ?',
            'a.deleted_at IS NULL',
            'a.is_published = 1',
            'e.lms_user_id = ?',
            "e.status != 'dropped'"
        ];
        $params = [$this->institutionId, $this->lmsUserId];

        if ($search) {
            $where[]  = 'a.title LIKE ?';
            $params[] = "%{$search}%";
        }
        if ($courseId) {
            $where[]  = 'a.course_id = ?';
            $params[] = $courseId;
        }
        $whereSQL = implode(' AND ', $where);

        try {
            $this->db->query(
                "SELECT COUNT(DISTINCT a.id) AS cnt
                 FROM lms_assignments a
                 JOIN lms_enrollments e ON e.course_id = a.course_id
                 WHERE {$whereSQL}",
                $params
            );
            $total = (int)($this->db->fetch()['cnt'] ?? 0);

            $this->db->query(
                "SELECT a.*, c.title AS course_title,
                        s.status AS sub_status, s.score, s.graded_at, s.attempt
                 FROM lms_assignments a
                 JOIN lms_courses c ON c.id = a.course_id
                 JOIN lms_enrollments e ON e.course_id = a.course_id
                 LEFT JOIN lms_assignment_submissions s
                    ON s.assignment_id = a.id AND s.lms_user_id = ? AND s.attempt = (
                        SELECT MAX(attempt) FROM lms_assignment_submissions
                        WHERE assignment_id = a.id AND lms_user_id = ?
                    )
                 WHERE {$whereSQL}
                 ORDER BY ISNULL(a.due_at), a.due_at ASC
                 LIMIT ? OFFSET ?",
                array_merge([$this->lmsUserId, $this->lmsUserId], $params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _getMyCourses(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "institution_id = {$this->institutionId}"
                : "instructor_id = {$this->lmsUserId} AND institution_id = {$this->institutionId}";
            $this->db->query("SELECT id, title FROM lms_courses WHERE {$scope} AND deleted_at IS NULL ORDER BY title");
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _getLessonsForCourse(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT l.id, l.title, s.title AS section_title
                 FROM lms_lessons l
                 JOIN lms_course_sections s ON s.id = l.section_id
                 WHERE l.course_id = ? AND l.deleted_at IS NULL
                 ORDER BY s.sort_order, l.sort_order",
                [$courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _syncDeadlines(int $assignmentId, int $courseId, ?string $dueAt, string $title): void
    {
        if (!$dueAt) return;
        try {
            // Delete old deadline records for this assignment
            $this->db->query(
                "DELETE FROM lms_deadlines WHERE entity_id = ? AND type = 'assignment'",
                [$assignmentId]
            );
            // Insert for all active enrollments
            $this->db->query(
                "SELECT lms_user_id FROM lms_enrollments WHERE course_id = ? AND status = 'active'",
                [$courseId]
            );
            $enrollments = $this->db->fetchAll();
            foreach ($enrollments as $enr) {
                $this->db->insert('lms_deadlines', [
                    'institution_id' => $this->institutionId,
                    'course_id'      => $courseId,
                    'lms_user_id'    => $enr['lms_user_id'],
                    'type'           => 'assignment',
                    'entity_id'      => $assignmentId,
                    'title'          => $title,
                    'due_at'         => $dueAt,
                ]);
            }
        } catch (\Throwable $e) {}
    }

    private function _validate(array $d): array
    {
        $errors = [];
        if (empty(trim($d['title'] ?? '')))        $errors['title']        = 'Title is required.';
        if (empty(trim($d['instructions'] ?? '')))  $errors['instructions'] = 'Instructions are required.';
        if (empty($d['course_id'] ?? ''))           $errors['course_id']    = 'Please select a course.';
        return $errors;
    }
}
