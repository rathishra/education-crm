<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class NotificationController extends BaseController
{
    public function index(): void
    {
        $page = (int)($this->input('page') ?? 1);
        $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
        $notifications = $this->db->paginate($sql, [$this->user['id']], $page, 20);

        $this->view('notifications.index', [
            'pageTitle'     => 'Notifications',
            'notifications' => $notifications,
        ]);
    }

    public function unreadCount(): void
    {
        $count = $this->db->count('notifications', 'user_id = ? AND is_read = 0', [$this->user['id']]);
        $this->json(['count' => $count]);
    }

    public function markRead(string $id): void
    {
        $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
        ], 'id = ? AND user_id = ?', [(int)$id, $this->user['id']]);

        if (isAjax()) {
            $this->success('Marked as read.');
            return;
        }
        back();
    }

    public function markAllRead(): void
    {
        $this->db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
        ], 'user_id = ? AND is_read = 0', [$this->user['id']]);

        if (isAjax()) {
            $this->success('All notifications marked as read.');
            return;
        }
        $this->redirectWith(url('notifications'), 'success', 'All marked as read.');
    }
}
