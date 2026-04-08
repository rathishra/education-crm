<?php
namespace App\Controllers\Lms;

class CourseController extends LmsBaseController
{
    // ── Course Listing ────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('courses.view');

        $page    = max(1, (int)($this->input('page', 1)));
        $perPage = 12;
        $offset  = ($page - 1) * $perPage;

        $search   = trim($this->input('search', ''));
        $status   = $this->input('status', '');
        $catId    = (int)$this->input('category', 0);
        $level    = $this->input('level', '');

        $where  = ['c.institution_id = ?'];
        $params = [$this->institutionId];

        if (!$this->isAdmin()) {
            $where[]  = 'c.instructor_id = ?';
            $params[] = $this->lmsUserId;
        }
        if ($search) {
            $where[]  = '(c.title LIKE ? OR c.code LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where[]  = 'c.status = ?';
            $params[] = $status;
        }
        if ($catId) {
            $where[]  = 'c.category_id = ?';
            $params[] = $catId;
        }
        if ($level) {
            $where[]  = 'c.level = ?';
            $params[] = $level;
        }
        $where[] = 'c.deleted_at IS NULL';

        $whereSQL = implode(' AND ', $where);

        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM lms_courses c WHERE {$whereSQL}", $params);
            $total = (int)($this->db->fetch()['cnt'] ?? 0);

            $listParams = array_merge($params, [$perPage, $offset]);
            $this->db->query(
                "SELECT c.*,
                        CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
                        cat.name AS category_name, cat.color AS cat_color,
                        (SELECT COUNT(*) FROM lms_enrollments e WHERE e.course_id = c.id AND e.status != 'dropped') AS enroll_cnt,
                        (SELECT COUNT(*) FROM lms_lessons l WHERE l.course_id = c.id AND l.deleted_at IS NULL) AS lesson_cnt
                 FROM lms_courses c
                 JOIN lms_users u     ON u.id = c.instructor_id
                 LEFT JOIN lms_categories cat ON cat.id = c.category_id
                 WHERE {$whereSQL}
                 ORDER BY c.created_at DESC
                 LIMIT ? OFFSET ?",
                $listParams
            );
            $courses = $this->db->fetchAll();
        } catch (\Throwable $e) {
            $courses = [];
            $total   = 0;
        }

        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        $categories = $this->_getCategories();

        $this->view('lms/courses/index', compact(
            'courses', 'total', 'page', 'totalPages', 'perPage',
            'search', 'status', 'catId', 'level', 'categories'
        ), 'main');
    }

    // ── Create Form ───────────────────────────────────────────
    public function create(): void
    {
        $this->authorize('courses.create');
        $categories  = $this->_getCategories();
        $instructors = $this->_getInstructors();
        $subjects    = $this->_getSubjects();
        $pageTitle   = 'Create Course';
        $this->view('lms/courses/form', compact('categories', 'instructors', 'subjects', 'pageTitle'), 'main');
    }

    // ── Store ─────────────────────────────────────────────────
    public function store(): void
    {
        $this->authorize('courses.create');

        $data   = $this->_validate();
        $errors = $data['_errors'] ?? [];
        if (!empty($errors)) {
            flash('errors', $errors);
            back();
            return;
        }

        $slug = $this->_uniqueSlug($data['title']);
        $code = strtoupper(substr(preg_replace('/[^A-Z0-9]/i', '', $data['title']), 0, 6) . '-' . rand(100, 999));

        $thumbPath = null;
        if (!empty($_FILES['thumbnail']['name'])) {
            $up = $this->uploadFile('thumbnail', 'lms/thumbnails');
            if ($up) $thumbPath = $up['file_path'];
        }

        try {
            $courseId = $this->db->insert('lms_courses', [
                'institution_id'    => $this->institutionId,
                'instructor_id'     => $this->isAdmin() ? (int)$data['instructor_id'] : $this->lmsUserId,
                'category_id'       => $data['category_id'] ?: null,
                'subject_id'        => !empty($data['subject_id']) ? (int)$data['subject_id'] : null,
                'code'              => $code,
                'title'             => $data['title'],
                'slug'              => $slug,
                'short_description' => $data['short_description'],
                'description'       => $data['description'],
                'thumbnail'         => $thumbPath,
                'level'             => $data['level'],
                'language'          => $data['language'] ?: 'English',
                'duration_hours'    => $data['duration_hours'] ?: null,
                'status'            => $data['status'],
                'visibility'        => $data['visibility'],
                'allow_self_enroll' => isset($data['allow_self_enroll']) ? 1 : 0,
                'pass_percentage'   => (int)($data['pass_percentage'] ?: 60),
                'certificate_enabled' => isset($data['certificate_enabled']) ? 1 : 0,
                'start_date'        => $data['start_date'] ?: null,
                'end_date'          => $data['end_date'] ?: null,
                'max_students'      => $data['max_students'] ?: null,
            ]);

            $this->_saveTags((int)$courseId, $data['tags'] ?? '');
            $this->audit('course.created', 'course', (int)$courseId, ['title' => $data['title']]);

            flash('success', "Course \"{$data['title']}\" created successfully.");
            redirect(url("elms/courses/{$courseId}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to create course. Please try again.']);
            back();
        }
    }

    // ── Show (manage course) ──────────────────────────────────
    public function show(int $id): void
    {
        $this->authorize('courses.view');
        $course = $this->_findCourse($id);

        try {
            // Sections with lessons
            $this->db->query(
                "SELECT * FROM lms_course_sections WHERE course_id = ? ORDER BY sort_order, id",
                [$id]
            );
            $sections = $this->db->fetchAll();

            foreach ($sections as &$sec) {
                $this->db->query(
                    "SELECT * FROM lms_lessons WHERE section_id = ? AND deleted_at IS NULL ORDER BY sort_order, id",
                    [$sec['id']]
                );
                $sec['lessons'] = $this->db->fetchAll();
            }
            unset($sec);

            // Enrollment stats
            $this->db->query(
                "SELECT
                    COUNT(*) AS total,
                    SUM(status='active') AS active_cnt,
                    SUM(status='completed') AS completed_cnt,
                    ROUND(AVG(progress),1) AS avg_progress
                 FROM lms_enrollments WHERE course_id = ? AND status != 'dropped'",
                [$id]
            );
            $enrollStats = $this->db->fetch() ?: ['total'=>0,'active_cnt'=>0,'completed_cnt'=>0,'avg_progress'=>0];

            // Recent enrollments
            $this->db->query(
                "SELECT e.*, CONCAT(u.first_name,' ',u.last_name) AS learner_name, u.email
                 FROM lms_enrollments e
                 JOIN lms_users u ON u.id = e.lms_user_id
                 WHERE e.course_id = ? AND e.status != 'dropped'
                 ORDER BY e.enrolled_at DESC LIMIT 10",
                [$id]
            );
            $recentEnrollments = $this->db->fetchAll();

            // Tags
            $this->db->query(
                "SELECT t.name FROM lms_tags t
                 JOIN lms_course_tags ct ON ct.tag_id = t.id
                 WHERE ct.course_id = ?",
                [$id]
            );
            $tags = array_column($this->db->fetchAll(), 'name');

        } catch (\Throwable $e) {
            $sections = $enrollStats = [];
            $recentEnrollments = $tags = [];
        }

        $pageTitle = $course['title'];
        $this->view('lms/courses/show', compact(
            'course', 'sections', 'enrollStats', 'recentEnrollments', 'tags', 'pageTitle'
        ), 'main');
    }

    // ── Edit Form ─────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('courses.edit');
        $course      = $this->_findCourse($id);
        $categories  = $this->_getCategories();
        $instructors = $this->_getInstructors();
        $subjects    = $this->_getSubjects();

        try {
            $this->db->query(
                "SELECT t.name FROM lms_tags t
                 JOIN lms_course_tags ct ON ct.tag_id = t.id
                 WHERE ct.course_id = ?", [$id]
            );
            $tagNames = implode(', ', array_column($this->db->fetchAll(), 'name'));
        } catch (\Throwable $e) { $tagNames = ''; }

        $pageTitle = 'Edit Course';
        $this->view('lms/courses/form', compact('course', 'categories', 'instructors', 'subjects', 'tagNames', 'pageTitle'), 'main');
    }

    // ── Update ────────────────────────────────────────────────
    public function update(int $id): void
    {
        $this->authorize('courses.edit');
        $course = $this->_findCourse($id);

        $data   = $this->_validate();
        $errors = $data['_errors'] ?? [];
        if (!empty($errors)) {
            flash('errors', $errors);
            back();
            return;
        }

        $updateData = [
            'instructor_id'     => $this->isAdmin() ? (int)$data['instructor_id'] : $course['instructor_id'],
            'category_id'       => $data['category_id'] ?: null,
            'subject_id'        => !empty($data['subject_id']) ? (int)$data['subject_id'] : null,
            'title'             => $data['title'],
            'short_description' => $data['short_description'],
            'description'       => $data['description'],
            'level'             => $data['level'],
            'language'          => $data['language'] ?: 'English',
            'duration_hours'    => $data['duration_hours'] ?: null,
            'status'            => $data['status'],
            'visibility'        => $data['visibility'],
            'allow_self_enroll' => isset($data['allow_self_enroll']) ? 1 : 0,
            'pass_percentage'   => (int)($data['pass_percentage'] ?: 60),
            'certificate_enabled' => isset($data['certificate_enabled']) ? 1 : 0,
            'start_date'        => $data['start_date'] ?: null,
            'end_date'          => $data['end_date'] ?: null,
            'max_students'      => $data['max_students'] ?: null,
        ];

        if (!empty($_FILES['thumbnail']['name'])) {
            $up = $this->uploadFile('thumbnail', 'lms/thumbnails');
            if ($up) $updateData['thumbnail'] = $up['file_path'];
        }

        try {
            $sets   = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($updateData)));
            $vals   = array_merge(array_values($updateData), [$id, $this->institutionId]);
            $this->db->query("UPDATE lms_courses SET {$sets} WHERE id = ? AND institution_id = ?", $vals);

            $this->_saveTags($id, $data['tags'] ?? '');
            $this->audit('course.updated', 'course', $id, ['title' => $data['title']]);

            flash('success', "Course updated successfully.");
            redirect(url("elms/courses/{$id}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to update course.']);
            back();
        }
    }

    // ── Publish / Archive toggle ──────────────────────────────
    public function toggleStatus(int $id): void
    {
        $this->authorize('courses.publish');
        $course = $this->_findCourse($id);

        $newStatus = $course['status'] === 'published' ? 'draft' : 'published';
        try {
            $this->db->query(
                "UPDATE lms_courses SET status = ? WHERE id = ? AND institution_id = ?",
                [$newStatus, $id, $this->institutionId]
            );
            $this->audit('course.status_changed', 'course', $id, ['status' => $newStatus]);

            if ($this->isAjax()) {
                $this->json(['status' => 'ok', 'new_status' => $newStatus]);
                return;
            }
            flash('success', "Course " . ($newStatus === 'published' ? 'published' : 'unpublished') . " successfully.");
        } catch (\Throwable $e) {
            if ($this->isAjax()) { $this->json(['error' => 'Failed'], 500); return; }
        }
        redirect(url("elms/courses/{$id}"));
    }

    // ── Soft delete ───────────────────────────────────────────
    public function destroy(int $id): void
    {
        $this->authorize('courses.delete');
        $this->_findCourse($id);

        try {
            $this->db->query(
                "UPDATE lms_courses SET deleted_at = NOW() WHERE id = ? AND institution_id = ?",
                [$id, $this->institutionId]
            );
            $this->audit('course.deleted', 'course', $id);
            flash('success', 'Course deleted.');
        } catch (\Throwable $e) {
            flash('errors', ['Failed to delete course.']);
        }
        redirect(url('elms/courses'));
    }

    // ── Lessons list (AJAX for assignment form) ───────────────
    public function lessonsList(int $id): void
    {
        try {
            $this->db->query(
                "SELECT l.id, l.title, s.title AS section_title
                 FROM lms_lessons l
                 JOIN lms_course_sections s ON s.id = l.section_id
                 WHERE l.course_id = ? AND l.deleted_at IS NULL
                 ORDER BY s.sort_order, l.sort_order",
                [$id]
            );
            $this->json($this->db->fetchAll());
        } catch (\Throwable $e) {
            $this->json([]);
        }
    }

    // ── Enroll students (bulk) ────────────────────────────────
    public function enrollStudents(int $id): void
    {
        $this->authorize('courses.enroll');
        $this->_findCourse($id);

        $userIds = array_filter(array_map('intval', (array)($this->input('user_ids', []))));
        if (empty($userIds)) {
            $this->json(['error' => 'No students selected'], 400);
            return;
        }

        $enrolled = 0;
        foreach ($userIds as $uid) {
            try {
                $this->db->query(
                    "INSERT IGNORE INTO lms_enrollments
                        (course_id, lms_user_id, institution_id, enrolled_by, status)
                     VALUES (?, ?, ?, ?, 'active')",
                    [$id, $uid, $this->institutionId, $this->lmsUserId]
                );
                // Update denormalized count
                $this->db->query(
                    "UPDATE lms_courses SET enrolled_count = enrolled_count + 1 WHERE id = ?", [$id]
                );
                $enrolled++;
            } catch (\Throwable $e) {}
        }

        $this->json(['status' => 'ok', 'enrolled' => $enrolled]);
    }

    // ── Sections CRUD (AJAX) ──────────────────────────────────
    public function storeSectionAjax(int $courseId): void
    {
        $this->authorize('courses.edit');
        $this->_findCourse($courseId);

        $title = trim($this->input('title', ''));
        if (!$title) { $this->json(['error' => 'Title required'], 400); return; }

        try {
            $this->db->query(
                "SELECT COALESCE(MAX(sort_order),0)+1 AS next_order FROM lms_course_sections WHERE course_id = ?",
                [$courseId]
            );
            $nextOrder = (int)($this->db->fetch()['next_order'] ?? 1);

            $secId = $this->db->insert('lms_course_sections', [
                'course_id'  => $courseId,
                'title'      => $title,
                'sort_order' => $nextOrder,
            ]);
            $this->json(['status' => 'ok', 'id' => $secId, 'title' => $title, 'sort_order' => $nextOrder]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed to create section'], 500);
        }
    }

    public function updateSectionAjax(int $courseId, int $secId): void
    {
        $this->authorize('courses.edit');
        $title = trim($this->input('title', ''));
        if (!$title) { $this->json(['error' => 'Title required'], 400); return; }

        try {
            $this->db->query(
                "UPDATE lms_course_sections SET title = ? WHERE id = ? AND course_id = ?",
                [$title, $secId, $courseId]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed'], 500);
        }
    }

    public function deleteSectionAjax(int $courseId, int $secId): void
    {
        $this->authorize('courses.edit');
        try {
            $this->db->query(
                "DELETE FROM lms_course_sections WHERE id = ? AND course_id = ?",
                [$secId, $courseId]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed'], 500);
        }
    }

    public function reorderSectionsAjax(int $courseId): void
    {
        $this->authorize('courses.edit');
        $order = (array)($this->input('order', []));
        foreach ($order as $idx => $secId) {
            try {
                $this->db->query(
                    "UPDATE lms_course_sections SET sort_order = ? WHERE id = ? AND course_id = ?",
                    [(int)$idx, (int)$secId, $courseId]
                );
            } catch (\Throwable $e) {}
        }
        $this->json(['status' => 'ok']);
    }

    // ── Helpers ───────────────────────────────────────────────

    private function _findCourse(int $id): array
    {
        try {
            $scope = $this->isAdmin()
                ? "institution_id = {$this->institutionId}"
                : "institution_id = {$this->institutionId} AND instructor_id = {$this->lmsUserId}";

            $this->db->query(
                "SELECT c.*, CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
                        cat.name AS category_name, cat.color AS cat_color,
                        sub.subject_name, sub.subject_code, sub.subject_type, sub.credits, sub.semester AS subject_semester
                 FROM lms_courses c
                 JOIN lms_users u ON u.id = c.instructor_id
                 LEFT JOIN lms_categories cat ON cat.id = c.category_id
                 LEFT JOIN subjects sub ON sub.id = c.subject_id
                 WHERE c.id = ? AND {$scope} AND c.deleted_at IS NULL",
                [$id]
            );
            $course = $this->db->fetch();
        } catch (\Throwable $e) { $course = null; }

        if (!$course) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Course Not Found'], 'main');
            exit;
        }
        return $course;
    }

    private function _getCategories(): array
    {
        try {
            $this->db->query(
                "SELECT * FROM lms_categories WHERE institution_id = ? ORDER BY name",
                [$this->institutionId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _getInstructors(): array
    {
        try {
            $this->db->query(
                "SELECT id, CONCAT(first_name,' ',last_name) AS name, email
                 FROM lms_users
                 WHERE institution_id = ? AND role IN ('instructor','lms_admin') AND status = 'active'
                 ORDER BY first_name",
                [$this->institutionId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _getSubjects(): array
    {
        try {
            $this->db->query(
                "SELECT s.id, s.subject_code, s.subject_name, s.subject_type,
                        s.semester, s.credits, c.name AS program_name
                 FROM subjects s
                 LEFT JOIN courses c ON c.id = s.course_id
                 WHERE s.institution_id = ? AND s.status = 'active' AND s.deleted_at IS NULL
                 ORDER BY s.subject_name",
                [$this->institutionId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _validate(): array
    {
        $d = $_POST;
        $errors = [];
        if (empty(trim($d['title'] ?? ''))) $errors['title'] = 'Course title is required.';
        if (empty($d['level'] ?? ''))       $errors['level'] = 'Level is required.';
        if (empty($d['status'] ?? ''))      $errors['status'] = 'Status is required.';
        if (!empty($errors)) $d['_errors'] = $errors;
        return $d;
    }

    private function _uniqueSlug(string $title): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $slug = $base;
        $i    = 1;
        while (true) {
            try {
                $this->db->query(
                    "SELECT id FROM lms_courses WHERE slug = ? AND institution_id = ?",
                    [$slug, $this->institutionId]
                );
                if (!$this->db->fetch()) break;
            } catch (\Throwable $e) { break; }
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function _saveTags(int $courseId, string $tagInput): void
    {
        try {
            $this->db->query("DELETE FROM lms_course_tags WHERE course_id = ?", [$courseId]);
            $names = array_filter(array_map('trim', explode(',', $tagInput)));
            foreach ($names as $name) {
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
                $this->db->query(
                    "INSERT IGNORE INTO lms_tags (institution_id, name, slug) VALUES (?, ?, ?)",
                    [$this->institutionId, $name, $slug]
                );
                $this->db->query(
                    "SELECT id FROM lms_tags WHERE slug = ? AND institution_id = ?",
                    [$slug, $this->institutionId]
                );
                $tag = $this->db->fetch();
                if ($tag) {
                    $this->db->query(
                        "INSERT IGNORE INTO lms_course_tags (course_id, tag_id) VALUES (?, ?)",
                        [$courseId, $tag['id']]
                    );
                }
            }
        } catch (\Throwable $e) {}
    }
}
