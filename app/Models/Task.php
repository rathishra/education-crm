<?php
namespace App\Models;

class Task extends BaseModel
{
    protected string $table = 'tasks';

    /**
     * Paginated list with filters and joins
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND t.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where .= " AND (t.title LIKE ? OR t.description LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($filters['status'])) {
            $where .= " AND t.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['assigned_to'])) {
            $where .= " AND t.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['due_from'])) {
            $where .= " AND DATE(t.due_date) >= ?";
            $params[] = $filters['due_from'];
        }

        if (!empty($filters['due_to'])) {
            $where .= " AND DATE(t.due_date) <= ?";
            $params[] = $filters['due_to'];
        }

        if (!empty($filters['only_mine'])) {
            $where .= " AND t.assigned_to = ?";
            $params[] = $filters['only_mine'];
        }

        if (!empty($filters['related_type'])) {
            $where .= " AND t.related_type = ?";
            $params[] = $filters['related_type'];
        }

        if (!empty($filters['related_id'])) {
            $where .= " AND t.related_id = ?";
            $params[] = $filters['related_id'];
        }

        $sql = "SELECT t.*,
                       CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_to_name,
                       CONCAT(uc.first_name, ' ', uc.last_name) AS created_by_name
                FROM tasks t
                LEFT JOIN users ua ON ua.id = t.assigned_to
                LEFT JOIN users uc ON uc.id = t.created_by
                WHERE {$where}
                ORDER BY FIELD(t.priority, 'urgent', 'high', 'medium', 'low'),
                         t.due_date ASC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Get single task with assigned user, creator, and related entity info
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT t.*,
                       CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_to_name,
                       ua.email AS assigned_to_email,
                       CONCAT(uc.first_name, ' ', uc.last_name) AS created_by_name
                FROM tasks t
                LEFT JOIN users ua ON ua.id = t.assigned_to
                LEFT JOIN users uc ON uc.id = t.created_by
                WHERE t.id = ?";
        $params = [$id];

        if ($this->institutionScope) {
            $sql .= " AND t.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $sql .= " LIMIT 1";
        $this->db->query($sql, $params);
        $task = $this->db->fetch();

        if ($task && $task['related_type'] && $task['related_id']) {
            $task['related_entity'] = $this->fetchRelatedEntity(
                $task['related_type'],
                (int)$task['related_id']
            );
        }

        return $task;
    }

    /**
     * Get tasks linked to a specific entity (lead, student, etc.)
     */
    public function getByRelated(string $relatedType, int $relatedId, ?string $status = null): array
    {
        $where = "t.related_type = ? AND t.related_id = ?";
        $params = [$relatedType, $relatedId];

        if ($this->institutionScope) {
            $where .= " AND t.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($status) {
            $where .= " AND t.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT t.*,
                       CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_to_name,
                       CONCAT(uc.first_name, ' ', uc.last_name) AS created_by_name
                FROM tasks t
                LEFT JOIN users ua ON ua.id = t.assigned_to
                LEFT JOIN users uc ON uc.id = t.created_by
                WHERE {$where}
                ORDER BY FIELD(t.priority, 'urgent', 'high', 'medium', 'low'),
                         t.due_date ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Change status with timestamp tracking
     */
    public function updateStatus(int $id, string $status): int
    {
        $data = ['status' => $status];

        switch ($status) {
            case 'in_progress':
                $data['started_at'] = date('Y-m-d H:i:s');
                break;
            case 'completed':
                $data['completed_at'] = date('Y-m-d H:i:s');
                break;
            case 'cancelled':
                $data['completed_at'] = date('Y-m-d H:i:s');
                break;
        }

        return $this->update($id, $data);
    }

    /**
     * Get pending/in_progress tasks past due_date
     */
    public function getOverdue(?int $assignedTo = null): array
    {
        $where = "t.status IN ('pending', 'in_progress') AND t.due_date IS NOT NULL AND t.due_date < NOW()";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND t.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($assignedTo) {
            $where .= " AND t.assigned_to = ?";
            $params[] = $assignedTo;
        }

        $sql = "SELECT t.*,
                       CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_to_name,
                       CONCAT(uc.first_name, ' ', uc.last_name) AS created_by_name
                FROM tasks t
                LEFT JOIN users ua ON ua.id = t.assigned_to
                LEFT JOIN users uc ON uc.id = t.created_by
                WHERE {$where}
                ORDER BY t.due_date ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Get task statistics: counts by status, priority, and overdue
     */
    public function getStats(?int $assignedTo = null): array
    {
        $where = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($assignedTo) {
            $where .= " AND assigned_to = ?";
            $params[] = $assignedTo;
        }

        // Counts by status
        $sql = "SELECT status, COUNT(*) AS total FROM tasks WHERE {$where} GROUP BY status";
        $this->db->query($sql, $params);
        $statusRows = $this->db->fetchAll();
        $byStatus = [];
        foreach ($statusRows as $row) {
            $byStatus[$row['status']] = (int)$row['total'];
        }

        // Counts by priority
        $sql = "SELECT priority, COUNT(*) AS total FROM tasks WHERE {$where} GROUP BY priority";
        $this->db->query($sql, $params);
        $priorityRows = $this->db->fetchAll();
        $byPriority = [];
        foreach ($priorityRows as $row) {
            $byPriority[$row['priority']] = (int)$row['total'];
        }

        // Overdue count
        $overdueWhere = $where . " AND status IN ('pending', 'in_progress') AND due_date IS NOT NULL AND due_date < NOW()";
        $sql = "SELECT COUNT(*) AS total FROM tasks WHERE {$overdueWhere}";
        $this->db->query($sql, $params);
        $overdueRow = $this->db->fetch();

        return [
            'by_status'   => $byStatus,
            'by_priority' => $byPriority,
            'overdue'     => (int)($overdueRow['total'] ?? 0),
        ];
    }

    /**
     * Get tasks assigned to a specific user with optional status filter
     */
    public function getMyTasks(int $userId, ?string $status = null, int $page = 1, int $perPage = 15): array
    {
        $where = "t.assigned_to = ?";
        $params = [$userId];

        if ($this->institutionScope) {
            $where .= " AND t.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($status) {
            $where .= " AND t.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT t.*,
                       CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_to_name,
                       CONCAT(uc.first_name, ' ', uc.last_name) AS created_by_name
                FROM tasks t
                LEFT JOIN users ua ON ua.id = t.assigned_to
                LEFT JOIN users uc ON uc.id = t.created_by
                WHERE {$where}
                ORDER BY FIELD(t.priority, 'urgent', 'high', 'medium', 'low'),
                         t.due_date ASC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Fetch related entity name based on type
     */
    protected function fetchRelatedEntity(string $type, int $id): ?array
    {
        $tableMap = [
            'lead'      => ['table' => 'leads',      'name' => "CONCAT(first_name, ' ', COALESCE(last_name, ''))"],
            'student'   => ['table' => 'students',    'name' => "CONCAT(first_name, ' ', COALESCE(last_name, ''))"],
            'admission' => ['table' => 'admissions',  'name' => 'admission_number'],
            'enquiry'   => ['table' => 'enquiries',   'name' => 'name'],
        ];

        if (!isset($tableMap[$type])) {
            return null;
        }

        $config = $tableMap[$type];
        $sql = "SELECT id, {$config['name']} AS name FROM `{$config['table']}` WHERE id = ? LIMIT 1";
        $this->db->query($sql, [$id]);
        $entity = $this->db->fetch();

        if ($entity) {
            $entity['type'] = $type;
        }

        return $entity;
    }
}
