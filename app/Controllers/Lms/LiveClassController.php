<?php
namespace App\Controllers\Lms;

class LiveClassController extends LmsBaseController
{
    // ── Index ─────────────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('live.view');

        $tab      = $this->input('tab', 'upcoming');   // upcoming | past
        $courseId = (int)$this->input('course', 0);
        $page     = max(1, (int)$this->input('page', 1));
        $perPage  = 12;
        $offset   = ($page - 1) * $perPage;

        [$classes, $total] = $this->isLearner()
            ? $this->_learnerList($tab, $courseId, $perPage, $offset)
            : $this->_instructorList($tab, $courseId, $perPage, $offset);

        $totalPages = (int)ceil($total / $perPage) ?: 1;
        $myCourses  = $this->_getMyCourses();
        $pageTitle  = 'Live Classes';
        $this->view('lms/live/index', compact(
            'classes', 'total', 'page', 'totalPages',
            'tab', 'courseId', 'myCourses', 'pageTitle'
        ), 'main');
    }

    // ── Create form ───────────────────────────────────────────
    public function create(): void
    {
        $this->authorize('live.manage');
        $courses   = $this->_getMyCourses();
        $courseId  = (int)$this->input('course_id', 0);
        $pageTitle = 'Schedule Live Class';
        $this->view('lms/live/form', compact('courses', 'courseId', 'pageTitle'), 'main');
    }

    // ── Store ─────────────────────────────────────────────────
    public function store(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('live.manage');
        $data   = $_POST;
        $errors = $this->_validate($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $id = (int)$this->db->insert('lms_live_classes', [
                'course_id'          => (int)$data['course_id'],
                'institution_id'     => $this->institutionId,
                'created_by'         => $this->lmsUserId,
                'title'              => trim($data['title']),
                'description'        => trim($data['description'] ?? ''),
                'platform'           => $data['platform'] ?? 'zoom',
                'meeting_url'        => trim($data['meeting_url']),
                'meeting_id'         => trim($data['meeting_id'] ?? ''),
                'meeting_password'   => trim($data['meeting_password'] ?? ''),
                'scheduled_at'       => $data['scheduled_at'],
                'duration_mins'      => max(1, (int)($data['duration_mins'] ?: 60)),
                'max_participants'   => !empty($data['max_participants']) ? (int)$data['max_participants'] : null,
                'is_recorded'        => isset($data['is_recorded']) ? 1 : 0,
                'is_published'       => isset($data['is_published']) ? 1 : 0,
                'host_notes'         => trim($data['host_notes'] ?? ''),
            ]);

            // Auto-register all active enrollments
            $this->_autoRegister($id, (int)$data['course_id']);

            // Add to lms_deadlines so it appears in calendar/dashboard
            $this->_syncDeadline($id, (int)$data['course_id'], $data['scheduled_at'], trim($data['title']));

            $this->audit('live.scheduled', 'live_class', $id, ['title' => $data['title']]);
            flash('success', 'Live class scheduled.');
            redirect(url("elms/live/{$id}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to schedule live class.']);
            back();
        }
    }

    // ── Show ─────────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->authorize('live.view');
        $class = $this->_find($id);

        // Registration record for learner
        $myReg = null;
        if ($this->isLearner()) {
            try {
                $this->db->query(
                    "SELECT * FROM lms_live_registrations WHERE live_class_id=? AND lms_user_id=?",
                    [$id, $this->lmsUserId]
                );
                $myReg = $this->db->fetch() ?: null;
            } catch (\Throwable $e) {}
        }

        // Participants (instructor only)
        $participants = [];
        if ($this->isInstructor()) {
            try {
                $this->db->query(
                    "SELECT r.*, CONCAT(u.first_name,' ',u.last_name) AS student_name, u.email
                     FROM lms_live_registrations r
                     JOIN lms_users u ON u.id=r.lms_user_id
                     WHERE r.live_class_id=?
                     ORDER BY r.joined_at IS NULL, r.joined_at",
                    [$id]
                );
                $participants = $this->db->fetchAll();
            } catch (\Throwable $e) {}
        }

        $now        = new \DateTime();
        $scheduledAt = new \DateTime($class['scheduled_at']);
        $endTime    = (clone $scheduledAt)->modify("+{$class['duration_mins']} minutes");
        $isLive     = $class['status'] === 'live';
        $isEnded    = in_array($class['status'], ['ended','cancelled']);
        $canJoin    = !$isEnded && $scheduledAt <= (clone $now)->modify('+15 minutes') && $now <= $endTime;
        $countdown  = max(0, $scheduledAt->getTimestamp() - $now->getTimestamp());

        $pageTitle = $class['title'];
        $this->view('lms/live/show', compact(
            'class', 'myReg', 'participants', 'isLive', 'isEnded', 'canJoin', 'countdown', 'pageTitle'
        ), 'main');
    }

    // ── Edit ─────────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('live.manage');
        $class     = $this->_find($id, true);
        $courses   = $this->_getMyCourses();
        $pageTitle = 'Edit Live Class';
        $this->view('lms/live/form', compact('class', 'courses', 'pageTitle'), 'main');
    }

    // ── Update ────────────────────────────────────────────────
    public function update(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('live.manage');
        $this->_find($id, true);
        $data   = $_POST;
        $errors = $this->_validate($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $this->db->query(
                "UPDATE lms_live_classes SET
                    title=?,description=?,platform=?,meeting_url=?,meeting_id=?,meeting_password=?,
                    scheduled_at=?,duration_mins=?,max_participants=?,is_recorded=?,is_published=?,host_notes=?
                 WHERE id=? AND institution_id=?",
                [
                    trim($data['title']),
                    trim($data['description'] ?? ''),
                    $data['platform'] ?? 'zoom',
                    trim($data['meeting_url']),
                    trim($data['meeting_id'] ?? ''),
                    trim($data['meeting_password'] ?? ''),
                    $data['scheduled_at'],
                    max(1, (int)($data['duration_mins'] ?: 60)),
                    !empty($data['max_participants']) ? (int)$data['max_participants'] : null,
                    isset($data['is_recorded']) ? 1 : 0,
                    isset($data['is_published']) ? 1 : 0,
                    trim($data['host_notes'] ?? ''),
                    $id, $this->institutionId,
                ]
            );
            $this->_syncDeadline($id, (int)$data['course_id'], $data['scheduled_at'], trim($data['title']));
            flash('success', 'Live class updated.');
            redirect(url("elms/live/{$id}"));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to update.']);
            back();
        }
    }

    // ── Delete ────────────────────────────────────────────────
    public function destroy(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('live.manage');
        $this->_find($id, true);
        try {
            $this->db->query("DELETE FROM lms_live_classes WHERE id=? AND institution_id=?", [$id, $this->institutionId]);
            $this->db->query("DELETE FROM lms_deadlines WHERE entity_id=? AND type='live'", [$id]);
            flash('success', 'Live class deleted.');
        } catch (\Throwable $e) { flash('errors', ['Failed.']); }
        redirect(url('elms/live'));
    }

    // ── Join (record join & redirect to meeting) ─────────────
    public function join(int $id): void
    {
        $this->authorize('live.view');
        $class = $this->_find($id);

        try {
            $this->db->query(
                "INSERT INTO lms_live_registrations (live_class_id, lms_user_id, joined_at, attended)
                 VALUES (?,?,NOW(),1)
                 ON DUPLICATE KEY UPDATE joined_at=IFNULL(joined_at,NOW()), attended=1",
                [$id, $this->lmsUserId]
            );
            // XP: award 5 XP for joining
            $this->db->query("UPDATE lms_users SET xp_points=xp_points+5 WHERE id=?", [$this->lmsUserId]);
            $this->db->query(
                "INSERT INTO lms_activity_feed
                    (lms_user_id, institution_id, event, entity_type, entity_id, entity_title, xp_earned)
                 VALUES (?,?,'live_joined','live_class',?,?,5)",
                [$this->lmsUserId, $this->institutionId, $id, $class['title']]
            );
        } catch (\Throwable $e) {}

        // Redirect to meeting URL
        header('Location: ' . $class['meeting_url']);
        exit;
    }

    // ── Set status: start / end ───────────────────────────────
    public function setStatus(int $id): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('live.manage');
        $this->_find($id, true);
        $status = $this->input('status', '');
        if (!in_array($status, ['live','ended','cancelled','scheduled'])) {
            $this->json(['error' => 'Invalid status'], 400); return;
        }

        $extra = '';
        if ($status === 'live')  $extra = ', started_at=NOW()';
        if ($status === 'ended') $extra = ', ended_at=NOW()';

        try {
            $this->db->query(
                "UPDATE lms_live_classes SET status=? {$extra} WHERE id=? AND institution_id=?",
                [$status, $id, $this->institutionId]
            );
            $this->json(['status' => 'ok', 'new_status' => $status]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Save recording URL (AJAX) ─────────────────────────────
    public function saveRecording(int $id): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->authorize('live.manage');
        $this->_find($id, true);
        $url      = trim($this->input('recording_url', ''));
        $password = trim($this->input('recording_password', ''));
        try {
            $this->db->query(
                "UPDATE lms_live_classes SET recording_url=?, recording_password=?, status='ended'
                 WHERE id=? AND institution_id=?",
                [$url ?: null, $password ?: null, $id, $this->institutionId]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Cancel ────────────────────────────────────────────────
    public function cancel(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->authorize('live.manage');
        $this->_find($id, true);
        try {
            $this->db->query(
                "UPDATE lms_live_classes SET status='cancelled' WHERE id=? AND institution_id=?",
                [$id, $this->institutionId]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Helpers ───────────────────────────────────────────────

    private function _find(int $id, bool $instructorOnly = false): array
    {
        try {
            $this->db->query(
                "SELECT lc.*, c.title AS course_title,
                        CONCAT(u.first_name,' ',u.last_name) AS instructor_name
                 FROM lms_live_classes lc
                 JOIN lms_courses c ON c.id=lc.course_id
                 JOIN lms_users u   ON u.id=lc.created_by
                 WHERE lc.id=? AND lc.institution_id=?",
                [$id, $this->institutionId]
            );
            $c = $this->db->fetch();
        } catch (\Throwable $e) { $c = null; }

        if (!$c) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Live Class Not Found'], 'main');
            exit;
        }
        if ($instructorOnly && $this->isLearner()) {
            http_response_code(403);
            $this->view('lms/errors/403', ['pageTitle' => 'Access Denied'], 'main');
            exit;
        }
        return $c;
    }

    private function _autoRegister(int $classId, int $courseId): void
    {
        try {
            $this->db->query(
                "SELECT lms_user_id FROM lms_enrollments WHERE course_id=? AND status='active'",
                [$courseId]
            );
            foreach ($this->db->fetchAll() as $e) {
                $this->db->query(
                    "INSERT IGNORE INTO lms_live_registrations (live_class_id, lms_user_id) VALUES (?,?)",
                    [$classId, $e['lms_user_id']]
                );
            }
        } catch (\Throwable $e) {}
    }

    private function _syncDeadline(int $classId, int $courseId, string $scheduledAt, string $title): void
    {
        try {
            $this->db->query("DELETE FROM lms_deadlines WHERE entity_id=? AND type='live'", [$classId]);
            $this->db->query("SELECT lms_user_id FROM lms_enrollments WHERE course_id=? AND status='active'", [$courseId]);
            foreach ($this->db->fetchAll() as $e) {
                $this->db->insert('lms_deadlines', [
                    'institution_id' => $this->institutionId,
                    'course_id'      => $courseId,
                    'lms_user_id'    => $e['lms_user_id'],
                    'type'           => 'live',
                    'entity_id'      => $classId,
                    'title'          => $title,
                    'due_at'         => $scheduledAt,
                ]);
            }
        } catch (\Throwable $e) {}
    }

    private function _getMyCourses(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "institution_id={$this->institutionId}"
                : "instructor_id={$this->lmsUserId} AND institution_id={$this->institutionId}";
            $this->db->query("SELECT id,title FROM lms_courses WHERE {$scope} AND deleted_at IS NULL ORDER BY title");
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _instructorList(string $tab, int $courseId, int $perPage, int $offset): array
    {
        $cond   = $tab === 'upcoming'
            ? "lc.status IN('scheduled','live')"
            : "lc.status IN('ended','cancelled')";
        $where  = ["lc.institution_id=?", $cond, "lc.deleted_at IS NULL"];
        $params = [$this->institutionId];
        if (!$this->isAdmin()) { $where[] = 'lc.created_by=?'; $params[] = $this->lmsUserId; }
        if ($courseId)         { $where[] = 'lc.course_id=?';  $params[] = $courseId; }
        $w    = implode(' AND ', $where);
        $ord  = $tab === 'upcoming' ? 'lc.scheduled_at ASC' : 'lc.scheduled_at DESC';
        return $this->_query($w, $params, $ord, $perPage, $offset);
    }

    private function _learnerList(string $tab, int $courseId, int $perPage, int $offset): array
    {
        $cond   = $tab === 'upcoming'
            ? "lc.status IN('scheduled','live')"
            : "lc.status IN('ended','cancelled')";
        $where  = ["lc.institution_id=?","lc.is_published=1","e.lms_user_id=?", $cond];
        $params = [$this->institutionId, $this->lmsUserId];
        if ($courseId) { $where[] = 'lc.course_id=?'; $params[] = $courseId; }
        $w   = implode(' AND ', $where);
        $ord = $tab === 'upcoming' ? 'lc.scheduled_at ASC' : 'lc.scheduled_at DESC';

        try {
            $this->db->query(
                "SELECT COUNT(DISTINCT lc.id) AS cnt
                 FROM lms_live_classes lc
                 JOIN lms_enrollments e ON e.course_id=lc.course_id
                 WHERE {$w}", $params
            );
            $total = (int)($this->db->fetch()['cnt'] ?? 0);
            $this->db->query(
                "SELECT lc.*, c.title AS course_title,
                        CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
                        r.attended AS i_attended, r.joined_at
                 FROM lms_live_classes lc
                 JOIN lms_courses c ON c.id=lc.course_id
                 JOIN lms_users u   ON u.id=lc.created_by
                 JOIN lms_enrollments e ON e.course_id=lc.course_id
                 LEFT JOIN lms_live_registrations r ON r.live_class_id=lc.id AND r.lms_user_id=?
                 WHERE {$w} ORDER BY {$ord} LIMIT ? OFFSET ?",
                array_merge([$this->lmsUserId], $params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _query(string $where, array $params, string $order, int $perPage, int $offset): array
    {
        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_live_classes lc WHERE {$where}",
                $params
            );
            $total = (int)($this->db->fetch()['cnt'] ?? 0);
            $this->db->query(
                "SELECT lc.*, c.title AS course_title,
                        CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
                        (SELECT COUNT(*) FROM lms_live_registrations WHERE live_class_id=lc.id) AS reg_count,
                        (SELECT COUNT(*) FROM lms_live_registrations WHERE live_class_id=lc.id AND attended=1) AS attended_count
                 FROM lms_live_classes lc
                 JOIN lms_courses c ON c.id=lc.course_id
                 JOIN lms_users u   ON u.id=lc.created_by
                 WHERE {$where} ORDER BY {$order} LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            return [$this->db->fetchAll(), $total];
        } catch (\Throwable $e) { return [[], 0]; }
    }

    private function _validate(array $d): array
    {
        $errors = [];
        if (empty(trim($d['title'] ?? '')))      $errors['title']        = 'Title required.';
        if (empty($d['course_id'] ?? ''))         $errors['course_id']    = 'Please select a course.';
        if (empty(trim($d['meeting_url'] ?? ''))) $errors['meeting_url']  = 'Meeting URL required.';
        if (empty($d['scheduled_at'] ?? ''))      $errors['scheduled_at'] = 'Scheduled date & time required.';
        return $errors;
    }
}
