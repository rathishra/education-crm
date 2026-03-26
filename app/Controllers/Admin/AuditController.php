<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuditController extends BaseController
{
    public function index(): void
    {
        $this->authorize('audit.view');

        $where = "1=1";
        $params = [];

        $search = $this->input('search');
        $action = $this->input('action');
        $userId = $this->input('user_id');
        $dateFrom = $this->input('date_from');
        $dateTo   = $this->input('date_to');

        if ($search)   { $where .= " AND (al.action LIKE ? OR al.entity_type LIKE ? OR CONCAT(u.first_name,' ',u.last_name) LIKE ?)"; $s = '%'.$search.'%'; $params = array_merge($params, [$s, $s, $s]); }
        if ($action)   { $where .= " AND al.action = ?"; $params[] = $action; }
        if ($userId)   { $where .= " AND al.user_id = ?"; $params[] = $userId; }
        if ($dateFrom) { $where .= " AND DATE(al.created_at) >= ?"; $params[] = $dateFrom; }
        if ($dateTo)   { $where .= " AND DATE(al.created_at) <= ?"; $params[] = $dateTo; }

        $page = (int)($this->input('page') ?: 1);
        $sql = "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM audit_logs al
                LEFT JOIN users u ON u.id = al.user_id
                WHERE {$where}
                ORDER BY al.created_at DESC";

        $logs = db()->paginate($sql, $params, $page, config('app.per_page', 15));

        db()->query("SELECT DISTINCT action FROM audit_logs ORDER BY action");
        $actions = db()->fetchAll();

        $this->view('settings/audit', compact('logs', 'actions', 'search', 'action', 'userId', 'dateFrom', 'dateTo'));
    }
}
