<?php
namespace App\Models;

class Lead extends BaseModel
{
    protected string $table = 'leads';
    protected bool $softDeletes = true;

    /**
     * Paginated list with filters and joins
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where = "l.deleted_at IS NULL";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND l.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where .= " AND (l.first_name LIKE ? OR l.last_name LIKE ? OR l.email LIKE ? OR l.phone LIKE ? OR l.lead_number LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filters['status_id'])) {
            $where .= " AND l.lead_status_id = ?";
            $params[] = $filters['status_id'];
        }

        if (!empty($filters['source_id'])) {
            $where .= " AND l.lead_source_id = ?";
            $params[] = $filters['source_id'];
        }

        if (!empty($filters['assigned_to'])) {
            $where .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['priority'])) {
            $where .= " AND l.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['course_id'])) {
            $where .= " AND l.course_interested_id = ?";
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        // Counselor can only see their own leads unless they have view_all
        if (!empty($filters['only_mine'])) {
            $where .= " AND l.assigned_to = ?";
            $params[] = $filters['only_mine'];
        }

        $sql = "SELECT l.*, ls.name as status_name, ls.color as status_color,
                       lsrc.name as source_name,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_name,
                       c.name as course_name,
                       i.name as institution_name
                FROM leads l
                LEFT JOIN lead_statuses ls ON ls.id = l.lead_status_id
                LEFT JOIN lead_sources lsrc ON lsrc.id = l.lead_source_id
                LEFT JOIN users u ON u.id = l.assigned_to
                LEFT JOIN courses c ON c.id = l.course_interested_id
                LEFT JOIN institutions i ON i.id = l.institution_id
                WHERE {$where}
                ORDER BY l.created_at DESC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Get single lead with all related data
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT l.*, ls.name as status_name, ls.color as status_color,
                       ls.is_won, ls.is_lost,
                       lsrc.name as source_name,
                       CONCAT(ua.first_name, ' ', ua.last_name) as assigned_name,
                       CONCAT(uc.first_name, ' ', uc.last_name) as created_by_name,
                       c.name as course_name, c.code as course_code,
                       i.name as institution_name
                FROM leads l
                LEFT JOIN lead_statuses ls ON ls.id = l.lead_status_id
                LEFT JOIN lead_sources lsrc ON lsrc.id = l.lead_source_id
                LEFT JOIN users ua ON ua.id = l.assigned_to
                LEFT JOIN users uc ON uc.id = l.created_by
                LEFT JOIN courses c ON c.id = l.course_interested_id
                LEFT JOIN institutions i ON i.id = l.institution_id
                WHERE l.id = ? AND l.deleted_at IS NULL";
        $this->db->query($sql, [$id]);
        $lead = $this->db->fetch();

        if (!$lead) return null;

        // Get activities
        $this->db->query(
            "SELECT la.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM lead_activities la
             LEFT JOIN users u ON u.id = la.user_id
             WHERE la.lead_id = ?
             ORDER BY la.created_at DESC",
            [$id]
        );
        $lead['activities'] = $this->db->fetchAll();

        // Get followups
        $this->db->query(
            "SELECT f.*, CONCAT(u.first_name, ' ', u.last_name) as assigned_name
             FROM followups f
             LEFT JOIN users u ON u.id = f.assigned_to
             WHERE f.lead_id = ?
             ORDER BY f.scheduled_at DESC",
            [$id]
        );
        $lead['followups'] = $this->db->fetchAll();

        // Get documents
        $this->db->query(
            "SELECT * FROM documents WHERE documentable_type = 'lead' AND documentable_id = ? ORDER BY created_at DESC",
            [$id]
        );
        $lead['documents'] = $this->db->fetchAll();

        return $lead;
    }

    /**
     * Generate unique lead number
     */
    public function generateLeadNumber(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst = $this->db->fetch();
        $instCode = $inst ? $inst['code'] : 'GEN';
        return generateNumber('LD', $instCode);
    }

    /**
     * Check for duplicates based on phone and email
     */
    public function checkDuplicate(string $phone, ?string $email = null, ?int $excludeId = null): ?array
    {
        $where = "deleted_at IS NULL AND (phone = ?";
        $params = [$phone];

        if ($email) {
            $where .= " OR email = ?";
            $params[] = $email;
        }
        $where .= ")";

        if ($excludeId) {
            $where .= " AND id != ?";
            $params[] = $excludeId;
        }

        if ($this->institutionScope) {
            $where .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $this->db->query("SELECT id, first_name, last_name, phone, email, lead_number FROM leads WHERE {$where} LIMIT 1", $params);
        return $this->db->fetch();
    }

    /**
     * Add activity to timeline
     */
    public function addActivity(int $leadId, string $type, string $title, ?string $description = null, ?int $userId = null, ?array $metadata = null): int
    {
        return (int)$this->db->insert('lead_activities', [
            'lead_id'     => $leadId,
            'user_id'     => $userId,
            'type'        => $type,
            'title'       => $title,
            'description' => $description,
            'metadata'    => $metadata ? json_encode($metadata) : null,
        ]);
    }

    /**
     * Update lead status with activity log
     */
    public function updateStatus(int $leadId, int $newStatusId, ?int $userId = null): void
    {
        $lead = $this->find($leadId);
        $oldStatusId = $lead['lead_status_id'] ?? null;

        $this->db->query("SELECT name FROM lead_statuses WHERE id = ?", [$oldStatusId]);
        $oldStatus = $this->db->fetch();
        $this->db->query("SELECT name, is_won FROM lead_statuses WHERE id = ?", [$newStatusId]);
        $newStatus = $this->db->fetch();

        $updateData = ['lead_status_id' => $newStatusId];

        // If converted, set converted_at
        if ($newStatus && $newStatus['is_won']) {
            $updateData['converted_at'] = date('Y-m-d H:i:s');
        }

        // If first contact
        if (empty($lead['first_contacted_at']) && $newStatusId != $oldStatusId) {
            $updateData['first_contacted_at'] = date('Y-m-d H:i:s');
        }

        $updateData['last_contacted_at'] = date('Y-m-d H:i:s');

        $this->update($leadId, $updateData);

        $this->addActivity($leadId, 'status_change',
            'Status changed from ' . ($oldStatus['name'] ?? 'Unknown') . ' to ' . ($newStatus['name'] ?? 'Unknown'),
            null, $userId,
            ['old_status' => $oldStatus['name'] ?? '', 'new_status' => $newStatus['name'] ?? '']
        );
    }

    /**
     * Assign lead to counselor
     */
    public function assignTo(int $leadId, int $counselorId, ?int $userId = null): void
    {
        $this->db->query("SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE id = ?", [$counselorId]);
        $counselor = $this->db->fetch();

        $this->update($leadId, ['assigned_to' => $counselorId]);

        $this->addActivity($leadId, 'assignment',
            'Lead assigned to ' . ($counselor['name'] ?? 'Unknown'),
            null, $userId,
            ['assigned_to' => $counselorId]
        );

        // Create notification for counselor
        $lead = $this->find($leadId);
        $this->db->insert('notifications', [
            'user_id'        => $counselorId,
            'institution_id' => $lead['institution_id'] ?? null,
            'type'           => 'lead_assigned',
            'title'          => 'New Lead Assigned',
            'message'        => 'Lead ' . ($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '') . ' has been assigned to you.',
            'action_url'     => '/leads/' . $leadId,
        ]);
    }

    /**
     * Get lead statuses
     */
    public function getStatuses(): array
    {
        $this->db->query("SELECT * FROM lead_statuses ORDER BY sort_order");
        return $this->db->fetchAll();
    }

    /**
     * Get lead sources
     */
    public function getSources(): array
    {
        $this->db->query("SELECT * FROM lead_sources WHERE is_active = 1 ORDER BY name");
        return $this->db->fetchAll();
    }

    /**
     * Get default status ID
     */
    public function getDefaultStatusId(): int
    {
        $this->db->query("SELECT id FROM lead_statuses WHERE is_default = 1 LIMIT 1");
        $row = $this->db->fetch();
        return $row ? (int)$row['id'] : 1;
    }

    /**
     * Export leads as array
     */
    public function getExportData(array $filters = []): array
    {
        $where = "l.deleted_at IS NULL";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND l.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['status_id'])) {
            $where .= " AND l.lead_status_id = ?";
            $params[] = $filters['status_id'];
        }
        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql = "SELECT l.lead_number, l.first_name, l.last_name, l.email, l.phone,
                       l.city, l.state, l.qualification, l.percentage, l.passing_year,
                       l.school_college, l.priority, l.notes, l.created_at,
                       ls.name as status, lsrc.name as source,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_to,
                       c.name as course_interested
                FROM leads l
                LEFT JOIN lead_statuses ls ON ls.id = l.lead_status_id
                LEFT JOIN lead_sources lsrc ON lsrc.id = l.lead_source_id
                LEFT JOIN users u ON u.id = l.assigned_to
                LEFT JOIN courses c ON c.id = l.course_interested_id
                WHERE {$where}
                ORDER BY l.created_at DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }
}
