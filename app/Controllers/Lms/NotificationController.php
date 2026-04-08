<?php
namespace App\Controllers\Lms;

class NotificationController extends LmsBaseController
{
    // ── Notification type config ──────────────────────────────
    private const TYPES = [
        'assignment_submitted' => ['icon'=>'fas fa-tasks',          'color'=>'#6366f1'],
        'assignment_graded'    => ['icon'=>'fas fa-star',           'color'=>'#f59e0b'],
        'quiz_result'          => ['icon'=>'fas fa-question-circle','color'=>'#0284c7'],
        'new_reply'            => ['icon'=>'fas fa-reply',          'color'=>'#10b981'],
        'thread_solved'        => ['icon'=>'fas fa-check-circle',   'color'=>'#22c55e'],
        'live_reminder'        => ['icon'=>'fas fa-broadcast-tower','color'=>'#ef4444'],
        'enrollment'           => ['icon'=>'fas fa-user-check',     'color'=>'#8b5cf6'],
        'announcement'         => ['icon'=>'fas fa-bullhorn',       'color'=>'#d97706'],
        'deadline_reminder'    => ['icon'=>'fas fa-clock',          'color'=>'#dc2626'],
        'badge_earned'         => ['icon'=>'fas fa-medal',          'color'=>'#f59e0b'],
        'system'               => ['icon'=>'fas fa-cog',            'color'=>'#64748b'],
    ];

    // ── Notification list ─────────────────────────────────────
    public function index(): void
    {
        $this->authorize('notifications.view');

        $filter  = $this->input('filter', 'all');   // all | unread
        $page    = max(1, (int)$this->input('page', 1));
        $perPage = 25;
        $offset  = ($page - 1) * $perPage;

        $where  = ['lms_user_id=?'];
        $params = [$this->lmsUserId];
        if ($filter === 'unread') { $where[] = 'is_read=0'; }

        $w = implode(' AND ', $where);

        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM lms_notifications WHERE {$w}", $params);
            $total = (int)($this->db->fetch()['cnt'] ?? 0);

            $this->db->query(
                "SELECT * FROM lms_notifications WHERE {$w}
                 ORDER BY is_read ASC, created_at DESC LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );
            $notifications = $this->db->fetchAll();
        } catch (\Throwable $e) {
            $notifications = [];
            $total = 0;
        }

        $unreadCount = $this->_unreadCount();
        $totalPages  = (int)ceil($total / $perPage) ?: 1;
        $pageTitle   = 'Notifications';

        $this->view('lms/notifications/index', compact(
            'notifications', 'total', 'unreadCount',
            'page', 'totalPages', 'filter', 'pageTitle'
        ), 'main');
    }

    // ── Mark single as read (AJAX) ────────────────────────────
    public function markRead(int $id): void
    {
        $this->authorize('notifications.view');
        try {
            $this->db->query(
                "UPDATE lms_notifications SET is_read=1, read_at=NOW()
                 WHERE id=? AND lms_user_id=?",
                [$id, $this->lmsUserId]
            );
            $this->json(['status' => 'ok', 'unread' => $this->_unreadCount()]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Mark all as read (AJAX) ───────────────────────────────
    public function markAllRead(): void
    {
        $this->authorize('notifications.view');
        try {
            $this->db->query(
                "UPDATE lms_notifications SET is_read=1, read_at=NOW()
                 WHERE lms_user_id=? AND is_read=0",
                [$this->lmsUserId]
            );
            $this->json(['status' => 'ok', 'unread' => 0]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Delete single (AJAX) ─────────────────────────────────
    public function delete(int $id): void
    {
        $this->authorize('notifications.view');
        try {
            $this->db->query(
                "DELETE FROM lms_notifications WHERE id=? AND lms_user_id=?",
                [$id, $this->lmsUserId]
            );
            $this->json(['status' => 'ok', 'unread' => $this->_unreadCount()]);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Clear all read notifications (AJAX) ──────────────────
    public function clearRead(): void
    {
        $this->authorize('notifications.view');
        try {
            $this->db->query(
                "DELETE FROM lms_notifications WHERE lms_user_id=? AND is_read=1",
                [$this->lmsUserId]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Unread count (AJAX, for header badge polling) ─────────
    public function unreadCount(): void
    {
        // No auth check — light endpoint, already behind auth middleware
        $this->json(['count' => $this->_unreadCount()]);
    }

    // ── Announcements list ────────────────────────────────────
    public function announcements(): void
    {
        $this->authorize('notifications.view');

        $courseId = (int)$this->input('course', 0);
        $page     = max(1, (int)$this->input('page', 1));
        $perPage  = 15;
        $offset   = ($page - 1) * $perPage;

        $where  = ['a.institution_id=?','a.is_published=1',
                   '(a.publish_at IS NULL OR a.publish_at <= NOW())',
                   '(a.expires_at IS NULL OR a.expires_at > NOW())',
                   'dis.id IS NULL'];
        $params = [$this->institutionId];

        // Learner: only institution-wide + enrolled courses
        if ($this->isLearner()) {
            $where[] = "(a.course_id IS NULL OR a.course_id IN (SELECT course_id FROM lms_enrollments WHERE lms_user_id=? AND status='active'))";
            $params[] = $this->lmsUserId;
        }
        if ($courseId) { $where[] = 'a.course_id=?'; $params[] = $courseId; }

        $w = implode(' AND ', $where);

        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt
                 FROM lms_announcements a
                 LEFT JOIN lms_announcement_dismissals dis
                    ON dis.announcement_id=a.id AND dis.lms_user_id=?
                 WHERE {$w}",
                array_merge([$this->lmsUserId], $params)
            );
            $total = (int)($this->db->fetch()['cnt'] ?? 0);

            $this->db->query(
                "SELECT a.*, c.title AS course_title,
                        CONCAT(u.first_name,' ',u.last_name) AS author_name
                 FROM lms_announcements a
                 LEFT JOIN lms_courses c ON c.id=a.course_id
                 JOIN lms_users u ON u.id=a.created_by
                 LEFT JOIN lms_announcement_dismissals dis
                    ON dis.announcement_id=a.id AND dis.lms_user_id=?
                 WHERE {$w} ORDER BY a.created_at DESC LIMIT ? OFFSET ?",
                array_merge([$this->lmsUserId], $params, [$perPage, $offset])
            );
            $announcements = $this->db->fetchAll();
        } catch (\Throwable $e) {
            $announcements = [];
            $total = 0;
        }

        $myCourses  = $this->_getMyCourses();
        $totalPages = (int)ceil($total / $perPage) ?: 1;
        $pageTitle  = 'Announcements';

        $this->view('lms/notifications/announcements', compact(
            'announcements', 'total', 'page', 'totalPages',
            'courseId', 'myCourses', 'pageTitle'
        ), 'main');
    }

    // ── Create announcement ───────────────────────────────────
    public function createAnnouncement(): void
    {
        $this->authorize('announcements.manage');
        $courses   = $this->_getMyCourses();
        $pageTitle = 'New Announcement';
        $this->view('lms/notifications/announcement_form', compact('courses', 'pageTitle'), 'main');
    }

    // ── Store announcement ────────────────────────────────────
    public function storeAnnouncement(): void
    {
        $this->authorize('announcements.manage');
        $data   = $_POST;
        $errors = $this->_validateAnn($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $annId = (int)$this->db->insert('lms_announcements', [
                'institution_id' => $this->institutionId,
                'course_id'      => !empty($data['course_id']) ? (int)$data['course_id'] : null,
                'created_by'     => $this->lmsUserId,
                'title'          => trim($data['title']),
                'body'           => trim($data['body']),
                'type'           => $data['type'] ?? 'info',
                'is_published'   => isset($data['is_published']) ? 1 : 0,
                'publish_at'     => !empty($data['publish_at'])  ? $data['publish_at']  : null,
                'expires_at'     => !empty($data['expires_at'])  ? $data['expires_at']  : null,
            ]);

            // Fan out notifications to recipients
            if (!empty($data['is_published'])) {
                $this->_fanOutAnnouncement($annId, (int)($data['course_id'] ?? 0), trim($data['title']));
            }

            $this->audit('announcement.created', 'announcement', $annId, ['title' => $data['title']]);
            flash('success', 'Announcement published.');
            redirect(url('elms/announcements'));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to create announcement.']);
            back();
        }
    }

    // ── Edit announcement ─────────────────────────────────────
    public function editAnnouncement(int $id): void
    {
        $this->authorize('announcements.manage');
        $ann       = $this->_findAnn($id);
        $courses   = $this->_getMyCourses();
        $pageTitle = 'Edit Announcement';
        $this->view('lms/notifications/announcement_form', compact('ann', 'courses', 'pageTitle'), 'main');
    }

    // ── Update announcement ───────────────────────────────────
    public function updateAnnouncement(int $id): void
    {
        $this->authorize('announcements.manage');
        $this->_findAnn($id);
        $data   = $_POST;
        $errors = $this->_validateAnn($data);
        if (!empty($errors)) { flash('errors', $errors); back(); return; }

        try {
            $this->db->query(
                "UPDATE lms_announcements
                 SET title=?,body=?,type=?,course_id=?,is_published=?,publish_at=?,expires_at=?
                 WHERE id=? AND institution_id=?",
                [
                    trim($data['title']),
                    trim($data['body']),
                    $data['type'] ?? 'info',
                    !empty($data['course_id']) ? (int)$data['course_id'] : null,
                    isset($data['is_published']) ? 1 : 0,
                    !empty($data['publish_at']) ? $data['publish_at'] : null,
                    !empty($data['expires_at']) ? $data['expires_at'] : null,
                    $id, $this->institutionId,
                ]
            );
            flash('success', 'Announcement updated.');
            redirect(url('elms/announcements'));
        } catch (\Throwable $e) {
            flash('errors', ['Failed to update.']);
            back();
        }
    }

    // ── Delete announcement ───────────────────────────────────
    public function deleteAnnouncement(int $id): void
    {
        $this->authorize('announcements.manage');
        $this->_findAnn($id);
        try {
            $this->db->query("DELETE FROM lms_announcements WHERE id=? AND institution_id=?", [$id, $this->institutionId]);
            flash('success', 'Announcement deleted.');
        } catch (\Throwable $e) { flash('errors', ['Failed.']); }
        redirect(url('elms/announcements'));
    }

    // ── Dismiss announcement (AJAX) ───────────────────────────
    public function dismissAnnouncement(int $id): void
    {
        try {
            $this->db->query(
                "INSERT IGNORE INTO lms_announcement_dismissals (announcement_id, lms_user_id) VALUES (?,?)",
                [$id, $this->lmsUserId]
            );
            $this->json(['status' => 'ok']);
        } catch (\Throwable $e) { $this->json(['error' => 'Failed'], 500); }
    }

    // ── Static helper: send a notification (called by other controllers) ──
    public static function send(
        \Core\Database\DB $db,
        int $institutionId,
        int $recipientId,
        string $type,
        string $title,
        string $body = '',
        string $link = '',
        ?int $senderId = null
    ): void {
        $types = self::TYPES;
        $meta  = $types[$type] ?? $types['system'];
        try {
            $db->insert('lms_notifications', [
                'institution_id' => $institutionId,
                'lms_user_id'    => $recipientId,
                'sender_id'      => $senderId,
                'type'           => $type,
                'title'          => $title,
                'body'           => $body,
                'link'           => $link ?: null,
                'icon'           => $meta['icon'],
                'color'          => $meta['color'],
            ]);
        } catch (\Throwable $e) {}
    }

    // ── Helpers ───────────────────────────────────────────────

    private function _unreadCount(): int
    {
        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM lms_notifications WHERE lms_user_id=? AND is_read=0",
                [$this->lmsUserId]
            );
            return (int)($this->db->fetch()['cnt'] ?? 0);
        } catch (\Throwable $e) { return 0; }
    }

    private function _fanOutAnnouncement(int $annId, int $courseId, string $title): void
    {
        try {
            if ($courseId) {
                $this->db->query(
                    "SELECT lms_user_id FROM lms_enrollments WHERE course_id=? AND status='active'",
                    [$courseId]
                );
            } else {
                $this->db->query(
                    "SELECT id AS lms_user_id FROM lms_users WHERE institution_id=?",
                    [$this->institutionId]
                );
            }
            $recipients = $this->db->fetchAll();
            foreach ($recipients as $r) {
                if ((int)$r['lms_user_id'] === $this->lmsUserId) continue;
                self::send(
                    $this->db, $this->institutionId,
                    (int)$r['lms_user_id'],
                    'announcement', $title,
                    '', url("elms/announcements"),
                    $this->lmsUserId
                );
            }
        } catch (\Throwable $e) {}
    }

    private function _findAnn(int $id): array
    {
        try {
            $this->db->query(
                "SELECT * FROM lms_announcements WHERE id=? AND institution_id=?",
                [$id, $this->institutionId]
            );
            $a = $this->db->fetch();
        } catch (\Throwable $e) { $a = null; }
        if (!$a) {
            http_response_code(404);
            $this->view('lms/errors/404', ['pageTitle' => 'Not Found'], 'main');
            exit;
        }
        return $a;
    }

    private function _getMyCourses(): array
    {
        try {
            $scope = $this->isAdmin()
                ? "institution_id={$this->institutionId}"
                : ($this->isLearner()
                    ? "id IN (SELECT course_id FROM lms_enrollments WHERE lms_user_id={$this->lmsUserId} AND status='active')"
                    : "instructor_id={$this->lmsUserId} AND institution_id={$this->institutionId}");
            $this->db->query("SELECT id,title FROM lms_courses WHERE {$scope} AND deleted_at IS NULL ORDER BY title");
            return $this->db->fetchAll();
        } catch (\Throwable $e) { return []; }
    }

    private function _validateAnn(array $d): array
    {
        $errors = [];
        if (empty(trim($d['title'] ?? ''))) $errors['title'] = 'Title required.';
        if (empty(trim($d['body']  ?? ''))) $errors['body']  = 'Body required.';
        return $errors;
    }
}
