<?php
namespace App\Models;

class Followup extends BaseModel
{
    protected string $table = 'followups';

    /**
     * Paginated list with filters and joins
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where .= " AND (f.subject LIKE ? OR f.description LIKE ? OR CONCAT(l.first_name, ' ', l.last_name) LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s]);
        }

        if (!empty($filters['status'])) {
            $where .= " AND f.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $where .= " AND f.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['lead_id'])) {
            $where .= " AND f.lead_id = ?";
            $params[] = $filters['lead_id'];
        }

        if (!empty($filters['assigned_to'])) {
            $where .= " AND f.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(f.scheduled_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(f.scheduled_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['only_mine'])) {
            $where .= " AND f.assigned_to = ?";
            $params[] = $filters['only_mine'];
        }

        if (!empty($filters['priority'])) {
            $where .= " AND f.priority = ?";
            $params[] = $filters['priority'];
        }

        // Order by scheduled_at ASC for pending, DESC for completed
        $statusFilter = $filters['status'] ?? '';
        if ($statusFilter === 'completed') {
            $orderBy = "f.scheduled_at DESC";
        } elseif ($statusFilter === 'pending') {
            $orderBy = "f.scheduled_at ASC";
        } else {
            $orderBy = "FIELD(f.status, 'pending', 'missed', 'rescheduled', 'cancelled', 'completed'), f.scheduled_at ASC";
        }

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name) AS lead_name,
                       l.lead_number,
                       l.phone AS lead_phone,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN leads l ON l.id = f.lead_id
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY {$orderBy}";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Get single followup with lead info, assigned user, and created-by user
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name) AS lead_name,
                       l.lead_number, l.phone AS lead_phone, l.email AS lead_email,
                       CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_name,
                       CONCAT(uc.first_name, ' ', uc.last_name) AS created_by_name
                FROM followups f
                LEFT JOIN leads l ON l.id = f.lead_id
                LEFT JOIN users ua ON ua.id = f.assigned_to
                LEFT JOIN users uc ON uc.id = f.created_by
                WHERE f.id = ?";
        $params = [$id];

        if ($this->institutionScope) {
            $sql .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $sql .= " LIMIT 1";

        $this->db->query($sql, $params);
        return $this->db->fetch();
    }

    /**
     * Get next N upcoming followups (pending, scheduled_at >= now)
     */
    public function getUpcoming(int $limit = 10, ?int $userId = null): array
    {
        $where = "f.status = 'pending' AND f.scheduled_at >= NOW()";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name) AS lead_name,
                       l.lead_number,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN leads l ON l.id = f.lead_id
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.scheduled_at ASC
                LIMIT ?";
        $params[] = $limit;

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Get overdue followups (pending and scheduled_at < now)
     */
    public function getOverdue(?int $userId = null): array
    {
        $where = "f.status = 'pending' AND f.scheduled_at < NOW()";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.*,
                       CONCAT(l.first_name, ' ', l.last_name) AS lead_name,
                       l.lead_number,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN leads l ON l.id = f.lead_id
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.scheduled_at ASC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Get followups in a date range formatted for FullCalendar JSON
     */
    public function getCalendarEvents(string $start, string $end, ?int $userId = null): array
    {
        $where = "f.scheduled_at >= ? AND f.scheduled_at <= ?";
        $params = [$start, $end];

        if ($this->institutionScope) {
            $where .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where .= " AND f.assigned_to = ?";
            $params[] = $userId;
        }

        $sql = "SELECT f.id, f.subject, f.type, f.status, f.priority,
                       f.scheduled_at, f.completed_at,
                       CONCAT(l.first_name, ' ', l.last_name) AS lead_name
                FROM followups f
                LEFT JOIN leads l ON l.id = f.lead_id
                WHERE {$where}
                ORDER BY f.scheduled_at ASC";

        $this->db->query($sql, $params);
        $rows = $this->db->fetchAll();

        $typeColors = [
            'call'     => '#3B82F6', // blue
            'email'    => '#8B5CF6', // purple
            'sms'      => '#10B981', // green
            'whatsapp' => '#22C55E', // emerald
            'meeting'  => '#F59E0B', // amber
            'visit'    => '#EF4444', // red
            'other'    => '#6B7280', // gray
        ];

        $events = [];
        foreach ($rows as $row) {
            $title = $row['subject'];
            if (!empty($row['lead_name'])) {
                $title .= ' - ' . $row['lead_name'];
            }

            $events[] = [
                'id'    => (int)$row['id'],
                'title' => $title,
                'start' => $row['scheduled_at'],
                'end'   => $row['completed_at'] ?? $row['scheduled_at'],
                'color' => $typeColors[$row['type']] ?? $typeColors['other'],
                'url'   => '/followups/' . $row['id'],
                'extendedProps' => [
                    'type'     => $row['type'],
                    'status'   => $row['status'],
                    'priority' => $row['priority'],
                ],
            ];
        }

        return $events;
    }

    /**
     * Get all followups for a specific lead
     */
    public function getByLead(int $leadId): array
    {
        $where = "f.lead_id = ?";
        $params = [$leadId];

        if ($this->institutionScope) {
            $where .= " AND f.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $sql = "SELECT f.*,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name
                FROM followups f
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE {$where}
                ORDER BY f.scheduled_at DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Mark followup as completed with outcome notes
     */
    public function complete(int $id, string $outcome, ?int $userId = null): int
    {
        $data = [
            'status'       => 'completed',
            'outcome'      => $outcome,
            'completed_at' => date('Y-m-d H:i:s'),
        ];

        $affected = $this->update($id, $data);

        // Log activity on the associated lead
        if ($affected && $userId) {
            $followup = $this->find($id);
            if ($followup && $followup['lead_id']) {
                $leadModel = new Lead();
                $leadModel->addActivity(
                    (int)$followup['lead_id'],
                    'followup_completed',
                    ucfirst($followup['type']) . ' followup completed: ' . $followup['subject'],
                    $outcome,
                    $userId,
                    ['followup_id' => $id]
                );
            }
        }

        return $affected;
    }

    /**
     * Get stats: counts by status, type, and overdue count
     */
    public function getStats(?int $userId = null): array
    {
        $where = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if ($userId) {
            $where .= " AND assigned_to = ?";
            $params[] = $userId;
        }

        // Counts by status
        $sql = "SELECT status, COUNT(*) AS total FROM followups WHERE {$where} GROUP BY status";
        $this->db->query($sql, $params);
        $statusRows = $this->db->fetchAll();
        $byStatus = [];
        foreach ($statusRows as $row) {
            $byStatus[$row['status']] = (int)$row['total'];
        }

        // Counts by type
        $sql = "SELECT type, COUNT(*) AS total FROM followups WHERE {$where} GROUP BY type";
        $this->db->query($sql, $params);
        $typeRows = $this->db->fetchAll();
        $byType = [];
        foreach ($typeRows as $row) {
            $byType[$row['type']] = (int)$row['total'];
        }

        // Overdue count
        $sql = "SELECT COUNT(*) AS total FROM followups WHERE {$where} AND status = 'pending' AND scheduled_at < NOW()";
        $this->db->query($sql, $params);
        $overdueRow = $this->db->fetch();
        $overdue = $overdueRow ? (int)$overdueRow['total'] : 0;

        return [
            'by_status' => $byStatus,
            'by_type'   => $byType,
            'overdue'   => $overdue,
        ];
    }

    /**
     * Count of today's followups for a user
     */
    public function getTodayCount(int $userId): int
    {
        $where = "DATE(scheduled_at) = CURDATE() AND assigned_to = ?";
        $params = [$userId];

        if ($this->institutionScope) {
            $where .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        return $this->count($where, $params);
    }
}
