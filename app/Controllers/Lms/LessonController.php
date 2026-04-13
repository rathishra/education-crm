<?php
namespace App\Controllers\Lms;

class LessonController extends LmsBaseController
{
    // ── Create lesson form ────────────────────────────────────
    public function create(int $courseId): void
    {
        $this->authorize('content.create');
        $course    = $this->_findCourse($courseId);
        $sections  = $this->_getSections($courseId);
        $sectionId = (int)$this->input('section_id', 0);
        $pageTitle = 'Add Lesson';
        $this->view('lms/lessons/form', compact('course', 'sections', 'sectionId', 'pageTitle'), 'main');
    }

    // ── Store lesson ──────────────────────────────────────────
    public function store(int $courseId): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('content.create');
        $course = $this->_findCourse($courseId);

        $data   = $this->_collectInput();
        $errors = $this->_validateLesson($data);
        if (!empty($errors)) {
            flash('errors', $errors);
            back();
            return;
        }

        $filePath = null;
        if (!empty($_FILES['lesson_file']['name'])) {
            $up = $this->uploadFile('lesson_file', 'lms/lessons/' . $courseId);
            if ($up) $filePath = $up['file_path'];
        }

        $slug  = $this->_uniqueSlug($courseId, $data['title']);
        $order = $this->_nextSortOrder((int)$data['section_id']);

        try {
            $lessonId = $this->db->insert('lms_lessons', [
                'course_id'       => $courseId,
                'section_id'      => (int)$data['section_id'],
                'title'           => $data['title'],
                'slug'            => $slug,
                'type'            => $data['type'],
                'content'         => $data['content'] ?? null,
                'video_url'       => $this->_normaliseVideoUrl($data['video_url'] ?? ''),
                'video_duration'  => $this->_parseDuration($data['video_duration'] ?? ''),
                'file_path'       => $filePath,
                'sort_order'      => $order,
                'is_free'         => isset($data['is_free']) ? 1 : 0,
                'is_published'    => isset($data['is_published']) ? 1 : 0,
                'xp_reward'       => max(0, (int)($data['xp_reward'] ?: 10)),
            ]);

            // Keep lms_courses.total_lessons in sync
            $this->db->query(
                "UPDATE lms_courses SET total_lessons = (
                    SELECT COUNT(*) FROM lms_lessons WHERE course_id = ? AND deleted_at IS NULL
                 ) WHERE id = ?",
                [$courseId, $courseId]
            );

            $this->audit('lesson.created', 'lesson', (int)$lessonId, ['title' => $data['title'], 'course_id' => $courseId]);
            flash('success', "Lesson \"{$data['title']}\" added successfully.");
            redirect(url("elms/courses/{$courseId}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to create lesson. Please try again.']);
            back();
        }
    }

    // ── Edit form ─────────────────────────────────────────────
    public function edit(int $courseId, int $id): void
    {
        $this->authorize('content.edit');
        $course   = $this->_findCourse($courseId);
        $lesson   = $this->_findLesson($courseId, $id);
        $sections = $this->_getSections($courseId);
        $pageTitle = 'Edit Lesson';
        $this->view('lms/lessons/form', compact('course', 'lesson', 'sections', 'pageTitle'), 'main');
    }

    // ── Update ────────────────────────────────────────────────
    public function update(int $courseId, int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('content.edit');
        $this->_findCourse($courseId);
        $lesson = $this->_findLesson($courseId, $id);

        $data   = $this->_collectInput();
        $errors = $this->_validateLesson($data);
        if (!empty($errors)) {
            flash('errors', $errors);
            back();
            return;
        }

        $updateData = [
            'section_id'     => (int)$data['section_id'],
            'title'          => $data['title'],
            'type'           => $data['type'],
            'content'        => $data['content'] ?? null,
            'video_url'      => $this->_normaliseVideoUrl($data['video_url'] ?? ''),
            'video_duration' => $this->_parseDuration($data['video_duration'] ?? ''),
            'sort_order'     => (int)($data['sort_order'] ?: $lesson['sort_order']),
            'is_free'        => isset($data['is_free']) ? 1 : 0,
            'is_published'   => isset($data['is_published']) ? 1 : 0,
            'xp_reward'      => max(0, (int)($data['xp_reward'] ?: 10)),
        ];

        if (!empty($_FILES['lesson_file']['name'])) {
            $up = $this->uploadFile('lesson_file', 'lms/lessons/' . $courseId);
            if ($up) $updateData['file_path'] = $up['file_path'];
        }

        try {
            $sets = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($updateData)));
            $vals = array_merge(array_values($updateData), [$id, $courseId]);
            $this->db->query("UPDATE lms_lessons SET {$sets} WHERE id = ? AND course_id = ?", $vals);

            $this->audit('lesson.updated', 'lesson', $id, ['title' => $data['title']]);
            flash('success', 'Lesson updated successfully.');
            redirect(url("elms/courses/{$courseId}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to update lesson.']);
            back();
        }
    }

    // ── Soft delete ───────────────────────────────────────────
    public function destroy(int $courseId, int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('content.delete');
        $this->_findLesson($courseId, $id);

        try {
            $this->db->query(
                "UPDATE lms_lessons SET deleted_at = NOW() WHERE id = ? AND course_id = ?",
                [$id, $courseId]
            );
            $this->db->query(
                "UPDATE lms_courses SET total_lessons = (
                    SELECT COUNT(*) FROM lms_lessons WHERE course_id = ? AND deleted_at IS NULL
                 ) WHERE id = ?",
                [$courseId, $courseId]
            );
            $this->audit('lesson.deleted', 'lesson', $id);
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed to delete'], 500);
        }
    }

    // ── Reorder lessons (AJAX) ────────────────────────────────
    public function reorder(int $courseId): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('content.edit');
        $order = (array)($this->input('order', []));
        foreach ($order as $idx => $lessonId) {
            try {
                $this->db->query(
                    "UPDATE lms_lessons SET sort_order = ? WHERE id = ? AND course_id = ?",
                    [(int)$idx, (int)$lessonId, $courseId]
                );
            } catch (\Throwable $e) {}
        }
        $this->json(['status' => 'ok']);
    }

    // ── Viewer (learner reads lesson) ─────────────────────────
    public function view_lesson(int $courseId, int $id): void
    {
        $this->authorize('content.view');
        $course = $this->_findCourse($courseId);
        $lesson = $this->_findLesson($courseId, $id);

        // Get enrollment for progress tracking
        $enrollment = null;
        try {
            $this->db->query(
                "SELECT * FROM lms_enrollments WHERE course_id = ? AND lms_user_id = ?",
                [$courseId, $this->lmsUserId]
            );
            $enrollment = $this->db->fetch();
        } catch (\Throwable $e) {}

        // Get all sections with lessons for sidebar nav
        $sections = [];
        try {
            $this->db->query(
                "SELECT * FROM lms_course_sections WHERE course_id = ? ORDER BY sort_order, id",
                [$courseId]
            );
            $sections = $this->db->fetchAll();
            foreach ($sections as &$sec) {
                $this->db->query(
                    "SELECT l.id, l.title, l.type, l.sort_order, l.is_free, l.video_duration,
                            lp.status AS progress_status
                     FROM lms_lessons l
                     LEFT JOIN lms_lesson_progress lp ON lp.lesson_id = l.id AND lp.lms_user_id = ?
                     WHERE l.section_id = ? AND l.deleted_at IS NULL AND l.is_published = 1
                     ORDER BY l.sort_order, l.id",
                    [$this->lmsUserId, $sec['id']]
                );
                $sec['lessons'] = $this->db->fetchAll();
            }
            unset($sec);
        } catch (\Throwable $e) {}

        // Get progress for this specific lesson
        $progress = null;
        if ($enrollment) {
            try {
                $this->db->query(
                    "SELECT * FROM lms_lesson_progress WHERE lesson_id = ? AND lms_user_id = ?",
                    [$id, $this->lmsUserId]
                );
                $progress = $this->db->fetch();
            } catch (\Throwable $e) {}
        }

        // Prev/Next lessons
        [$prevLesson, $nextLesson] = $this->_getAdjacentLessons($courseId, $id, $lesson['section_id']);

        $pageTitle = $lesson['title'];
        $this->view('lms/lessons/viewer', compact(
            'course', 'lesson', 'sections', 'enrollment', 'progress', 'prevLesson', 'nextLesson', 'pageTitle'
        ), 'main');
    }

    // ── Mark progress (AJAX) ──────────────────────────────────
    public function markProgress(int $courseId, int $id): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        if (!$this->lmsUserId) { $this->json(['error' => 'Not authenticated'], 401); return; }

        try {
            $this->db->query(
                "SELECT * FROM lms_enrollments WHERE course_id = ? AND lms_user_id = ?",
                [$courseId, $this->lmsUserId]
            );
            $enrollment = $this->db->fetch();
            if (!$enrollment) { $this->json(['error' => 'Not enrolled'], 403); return; }

            $status       = $this->input('status', 'completed');
            $watchSeconds = (int)$this->input('watch_seconds', 0);

            // Upsert lesson progress
            $this->db->query(
                "INSERT INTO lms_lesson_progress (enrollment_id, lesson_id, lms_user_id, status, watch_seconds, completed_at)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    status        = IF(status='completed', 'completed', VALUES(status)),
                    watch_seconds = GREATEST(watch_seconds, VALUES(watch_seconds)),
                    completed_at  = IF(VALUES(status)='completed' AND completed_at IS NULL, NOW(), completed_at)",
                [
                    $enrollment['id'], $id, $this->lmsUserId,
                    $status, $watchSeconds,
                    $status === 'completed' ? date('Y-m-d H:i:s') : null,
                ]
            );

            // Recalculate enrollment progress
            $this->db->query(
                "SELECT COUNT(*) AS total,
                        SUM(lp.status='completed') AS done
                 FROM lms_lessons l
                 LEFT JOIN lms_lesson_progress lp ON lp.lesson_id = l.id AND lp.lms_user_id = ?
                 WHERE l.course_id = ? AND l.deleted_at IS NULL AND l.is_published = 1",
                [$this->lmsUserId, $courseId]
            );
            $row     = $this->db->fetch();
            $total   = (int)($row['total'] ?? 0);
            $done    = (int)($row['done']  ?? 0);
            $pct     = $total > 0 ? min(100, (int)round($done / $total * 100)) : 0;
            $isComplete = ($pct >= 100);

            $this->db->query(
                "UPDATE lms_enrollments
                 SET progress = ?, lessons_completed = ?,
                     status   = IF(? >= 100, 'completed', status),
                     completed_at = IF(? >= 100 AND completed_at IS NULL, NOW(), completed_at),
                     last_accessed_at = NOW()
                 WHERE id = ?",
                [$pct, $done, $pct, $pct, $enrollment['id']]
            );

            // Award XP on first completion
            if ($status === 'completed') {
                $this->db->query("SELECT xp_reward FROM lms_lessons WHERE id = ?", [$id]);
                $xp = (int)($this->db->fetch()['xp_reward'] ?? 0);
                if ($xp > 0) {
                    $this->db->query(
                        "UPDATE lms_users SET xp_points = xp_points + ? WHERE id = ?",
                        [$xp, $this->lmsUserId]
                    );
                    // Log activity
                    $this->db->query("SELECT title FROM lms_lessons WHERE id = ?", [$id]);
                    $title = $this->db->fetch()['title'] ?? '';
                    $this->db->query(
                        "INSERT INTO lms_activity_feed
                            (lms_user_id, institution_id, event, entity_type, entity_id, entity_title, xp_earned)
                         VALUES (?, ?, 'lesson_completed', 'lesson', ?, ?, ?)",
                        [$this->lmsUserId, $this->institutionId, $id, $title, $xp]
                    );
                }
            }

            $this->json(['status' => 'ok', 'progress' => $pct, 'completed' => $isComplete]);
        } catch (\Throwable $e) {
            $this->json(['error' => 'Failed'], 500);
        }
    }

    // ── Helpers ───────────────────────────────────────────────

    private function _findCourse(int $id): array
    {
        try {
            $scope = $this->isAdmin()
                ? "institution_id = {$this->institutionId}"
                : "institution_id = {$this->institutionId} AND (instructor_id = {$this->lmsUserId} OR {$this->institutionId} > 0)";

            $this->db->query(
                "SELECT * FROM lms_courses WHERE id = ? AND institution_id = ? AND deleted_at IS NULL",
                [$id, $this->institutionId]
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

    private function _findLesson(int $courseId, int $id): array
    {
        try {
            $this->db->query(
                "SELECT l.*, s.title AS section_title
                 FROM lms_lessons l
                 JOIN lms_course_sections s ON s.id = l.section_id
                 WHERE l.id = ? AND l.course_id = ? AND l.deleted_at IS NULL",
                [$id, $courseId]
            );
            $lesson = $this->db->fetch();
        } catch (\Throwable $e) { $lesson = null; }

        if (!$lesson) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Lesson Not Found'], 'main');
            exit;
        }
        return $lesson;
    }

    private function _getSections(int $courseId): array
    {
        try {
            $this->db->query(
                "SELECT * FROM lms_course_sections WHERE course_id = ? ORDER BY sort_order, id",
                [$courseId]
            );
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _nextSortOrder(int $sectionId): int
    {
        try {
            $this->db->query(
                "SELECT COALESCE(MAX(sort_order),0)+1 AS next FROM lms_lessons WHERE section_id = ? AND deleted_at IS NULL",
                [$sectionId]
            );
            return (int)($this->db->fetch()['next'] ?? 1);
        } catch (\Throwable $e) { return 1; }
    }

    private function _getAdjacentLessons(int $courseId, int $currentId, int $sectionId): array
    {
        $prev = $next = null;
        try {
            // All published lessons in course ordered
            $this->db->query(
                "SELECT l.id, l.title, l.type, cs.id AS section_id
                 FROM lms_lessons l
                 JOIN lms_course_sections cs ON cs.id = l.section_id
                 WHERE l.course_id = ? AND l.deleted_at IS NULL AND l.is_published = 1
                 ORDER BY cs.sort_order, cs.id, l.sort_order, l.id",
                [$courseId]
            );
            $all = $this->db->fetchAll();
            $ids = array_column($all, 'id');
            $pos = array_search($currentId, $ids);
            if ($pos !== false) {
                if ($pos > 0)              $prev = $all[$pos - 1];
                if ($pos < count($all)-1)  $next = $all[$pos + 1];
            }
        } catch (\Throwable $e) {}
        return [$prev, $next];
    }

    private function _uniqueSlug(int $courseId, string $title): string
    {
        $base = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $slug = $base; $i = 1;
        while (true) {
            try {
                $this->db->query(
                    "SELECT id FROM lms_lessons WHERE slug = ? AND course_id = ?",
                    [$slug, $courseId]
                );
                if (!$this->db->fetch()) break;
            } catch (\Throwable $e) { break; }
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private function _normaliseVideoUrl(string $url): ?string
    {
        $url = trim($url);
        if (!$url) return null;
        // Convert YouTube watch URLs to embed
        if (preg_match('/youtube\.com\/watch\?v=([A-Za-z0-9_\-]+)/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        if (preg_match('/youtu\.be\/([A-Za-z0-9_\-]+)/', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        // Convert Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        return $url;
    }

    private function _parseDuration(string $input): ?int
    {
        $input = trim($input);
        if (!$input) return null;
        // mm:ss format
        if (preg_match('/^(\d+):(\d{2})$/', $input, $m)) {
            return (int)$m[1] * 60 + (int)$m[2];
        }
        // pure seconds
        if (is_numeric($input)) return (int)$input;
        return null;
    }

    private function _collectInput(): array
    {
        return [
            'section_id'     => $this->input('section_id'),
            'title'          => trim($this->input('title', '')),
            'type'           => $this->input('type', 'video'),
            'content'        => $this->input('content'),
            'video_url'      => $this->input('video_url'),
            'video_duration' => $this->input('video_duration'),
            'sort_order'     => $this->input('sort_order'),
            'is_free'        => $this->input('is_free'),
            'is_published'   => $this->input('is_published'),
            'xp_reward'      => $this->input('xp_reward', 10),
        ];
    }

    private function _validateLesson(array $d): array
    {
        $errors = [];
        if (empty($d['title']))      $errors['title']      = 'Lesson title is required.';
        if (empty($d['section_id'])) $errors['section_id'] = 'Please select a section.';
        if (empty($d['type']))       $errors['type']       = 'Lesson type is required.';
        return $errors;
    }
}
