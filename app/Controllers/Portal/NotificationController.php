<?php
namespace App\Controllers\Portal;

class NotificationController extends PortalBaseController
{
    public function index(): void
    {
        $sid = $this->studentId;
        $db  = $this->db;

        $db->query(
            "SELECT * FROM notifications
             WHERE student_id = ?
             ORDER BY created_at DESC
             LIMIT 50",
            [$sid]
        );
        $notifications = $db->fetchAll();

        // Mark all as read on view (batch update)
        if (!empty($notifications)) {
            $db->query(
                "UPDATE notifications SET is_read = 1, read_at = NOW()
                 WHERE student_id = ? AND is_read = 0",
                [$sid]
            );
        }

        $pageTitle = 'Notifications';
        $this->view('portal/notifications/index', compact('notifications', 'pageTitle'));
    }

    public function markRead(int $id): void
    {
        $this->db->query(
            "UPDATE notifications SET is_read = 1, read_at = NOW()
             WHERE id = ? AND student_id = ?",
            [$id, $this->studentId]
        );

        if (isAjax()) {
            $this->json(['status' => 'ok']);
            return;
        }

        redirect(url('portal/student/notifications'));
    }
}
