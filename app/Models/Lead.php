<?php
namespace App\Models;

class Lead extends BaseModel
{
    protected string $table   = 'leads';
    protected bool $softDeletes = true;

    /** Cached result: TRUE once migration 16 has been applied. */
    private ?bool $enhanced = null;

    /**
     * Check once whether the migration-16 columns exist.
     * Uses a zero-row LIMIT query — no data read, no side effects.
     */
    private function isEnhanced(): bool
    {
        if ($this->enhanced === null) {
            try {
                $this->db->query("SELECT next_followup_date FROM leads LIMIT 0");
                $this->enhanced = true;
            } catch (\Throwable $e) {
                $this->enhanced = false;
            }
        }
        return $this->enhanced;
    }

    // =========================================================
    // LIST & SEARCH
    // =========================================================

    /**
     * Paginated list with filters and joins.
     *
     * Supported filters:
     *   search, status_id, source_id, assigned_to, priority,
     *   course_id, department_id, date_from, date_to,
     *   only_mine (user_id), next_followup_overdue (bool)
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where  = "l.deleted_at IS NULL";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND l.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where   .= " AND (l.first_name LIKE ? OR l.last_name LIKE ? OR l.email LIKE ?"
                      . " OR l.phone LIKE ? OR l.lead_number LIKE ?)";
            $s        = '%' . $filters['search'] . '%';
            $params   = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filters['status_id'])) {
            $where   .= " AND l.lead_status_id = ?";
            $params[] = $filters['status_id'];
        }

        if (!empty($filters['source_id'])) {
            $where   .= " AND l.lead_source_id = ?";
            $params[] = $filters['source_id'];
        }

        if (!empty($filters['assigned_to'])) {
            $where   .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['priority'])) {
            $where   .= " AND l.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['course_id'])) {
            $where   .= " AND l.course_interested_id = ?";
            $params[] = $filters['course_id'];
        }

        // department filter — only available after migration 16
        if ($this->isEnhanced() && !empty($filters['department_id'])) {
            $where   .= " AND l.department_id = ?";
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['date_from'])) {
            $where   .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where   .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        // Counselor can only see their own leads unless they have view_all
        if (!empty($filters['only_mine'])) {
            $where   .= " AND l.assigned_to = ?";
            $params[] = $filters['only_mine'];
        }

        // overdue follow-up filter — only available after migration 16
        if ($this->isEnhanced() && !empty($filters['next_followup_overdue'])) {
            $where .= " AND l.next_followup_date IS NOT NULL AND l.next_followup_date <= CURDATE()";
        }

        // Converted filter — leads whose status is a "won" status
        if (!empty($filters['converted'])) {
            $where .= " AND EXISTS (SELECT 1 FROM lead_statuses ls2 WHERE ls2.id = l.lead_status_id AND ls2.is_won = 1)";
        }

        // Extra SELECT columns and JOINs only available after migration 16
        $extraSelect = $this->isEnhanced()
            ? ",\n                       d.name AS department_name"
            : '';
        $deptJoin = $this->isEnhanced()
            ? "LEFT JOIN departments    d    ON d.id    = l.department_id\n                "
            : '';

        $sql = "SELECT l.*,
                       ls.name          AS status_name,
                       ls.color         AS status_color,
                       ls.is_won        AS is_converted,
                       lsrc.name        AS source_name,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_name,
                       c.name           AS course_name,
                       i.name           AS institution_name{$extraSelect}
                FROM leads l
                LEFT JOIN lead_statuses  ls   ON ls.id   = l.lead_status_id
                LEFT JOIN lead_sources   lsrc ON lsrc.id = l.lead_source_id
                LEFT JOIN users          u    ON u.id    = l.assigned_to
                LEFT JOIN courses        c    ON c.id    = l.course_interested_id
                {$deptJoin}LEFT JOIN institutions   i    ON i.id    = l.institution_id
                WHERE {$where}
                ORDER BY l.created_at DESC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    // =========================================================
    // DETAIL VIEW
    // =========================================================

    /**
     * Get single lead with all related data including followups, activities, documents.
     */
    public function findWithDetails(int $id): ?array
    {
        $deptSelect = $this->isEnhanced() ? ",\n                       d.name AS department_name" : '';
        $deptJoin   = $this->isEnhanced()
            ? "LEFT JOIN departments d ON d.id = l.department_id\n                " : '';

        $sql = "SELECT l.*,
                       ls.name   AS status_name,
                       ls.color  AS status_color,
                       ls.is_won,
                       ls.is_lost,
                       lsrc.name AS source_name,
                       CONCAT(ua.first_name, ' ', ua.last_name) AS assigned_name,
                       CONCAT(uc.first_name, ' ', uc.last_name) AS created_by_name,
                       c.name    AS course_name,
                       c.code    AS course_code,
                       i.name    AS institution_name{$deptSelect}
                FROM leads l
                LEFT JOIN lead_statuses  ls   ON ls.id   = l.lead_status_id
                LEFT JOIN lead_sources   lsrc ON lsrc.id = l.lead_source_id
                LEFT JOIN users          ua   ON ua.id   = l.assigned_to
                LEFT JOIN users          uc   ON uc.id   = l.created_by
                LEFT JOIN courses        c    ON c.id    = l.course_interested_id
                {$deptJoin}LEFT JOIN institutions   i    ON i.id    = l.institution_id
                WHERE l.id = ? AND l.deleted_at IS NULL";

        $this->db->query($sql, [$id]);
        $lead = $this->db->fetch();

        if (!$lead) {
            return null;
        }

        // Activities timeline
        $this->db->query(
            "SELECT la.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name
             FROM lead_activities la
             LEFT JOIN users u ON u.id = la.user_id
             WHERE la.lead_id = ?
             ORDER BY la.created_at DESC",
            [$id]
        );
        $lead['activities'] = $this->db->fetchAll();

        // Dedicated lead followups — only available after migration 16
        if ($this->isEnhanced()) {
            $this->db->query(
                "SELECT lf.*, CONCAT(u.first_name, ' ', u.last_name) AS counselor_name
                 FROM lead_followups lf
                 LEFT JOIN users u ON u.id = lf.counselor_id
                 WHERE lf.lead_id = ?
                 ORDER BY lf.followup_date DESC",
                [$id]
            );
            $lead['followups'] = $this->db->fetchAll();
        } else {
            $lead['followups'] = [];
        }

        // Documents
        $this->db->query(
            "SELECT * FROM documents
             WHERE documentable_type = 'lead' AND documentable_id = ?
             ORDER BY created_at DESC",
            [$id]
        );
        $lead['documents'] = $this->db->fetchAll();

        return $lead;
    }

    // =========================================================
    // LEAD NUMBER
    // =========================================================

    /**
     * Generate unique lead number like LD-INST-YYYYMMDD-XXXX.
     */
    public function generateLeadNumber(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst     = $this->db->fetch();
        $instCode = $inst ? $inst['code'] : 'GEN';
        return generateNumber('LD', $instCode);
    }

    // =========================================================
    // DUPLICATE DETECTION
    // =========================================================

    /**
     * Check for duplicate lead by phone and/or email within institution scope.
     */
    public function checkDuplicate(string $phone, ?string $email = null, ?int $excludeId = null): ?array
    {
        $where    = "deleted_at IS NULL AND (phone = ?";
        $params   = [$phone];

        if ($email) {
            $where   .= " OR email = ?";
            $params[] = $email;
        }
        $where .= ")";

        if ($excludeId) {
            $where   .= " AND id != ?";
            $params[] = $excludeId;
        }

        if ($this->institutionScope) {
            $where   .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $this->db->query(
            "SELECT id, first_name, last_name, phone, email, lead_number
             FROM leads
             WHERE {$where}
             LIMIT 1",
            $params
        );
        return $this->db->fetch();
    }

    /**
     * AJAX duplicate check — explicitly scoped to institution.
     * Returns the conflicting lead row or null if no duplicate exists.
     */
    public function checkDuplicateAjax(string $phone, string $email, int $institutionId, int $excludeId = 0): ?array
    {
        $where  = "deleted_at IS NULL AND institution_id = ? AND (phone = ?";
        $params = [$institutionId, $phone];

        if ($email !== '') {
            $where   .= " OR email = ?";
            $params[] = $email;
        }
        $where .= ")";

        if ($excludeId > 0) {
            $where   .= " AND id != ?";
            $params[] = $excludeId;
        }

        $this->db->query(
            "SELECT id, first_name, last_name, phone, email, lead_number
             FROM leads
             WHERE {$where}
             LIMIT 1",
            $params
        );
        return $this->db->fetch();
    }

    // =========================================================
    // ACTIVITIES
    // =========================================================

    /**
     * Append an entry to the lead activity timeline.
     */
    public function addActivity(
        int     $leadId,
        string  $type,
        string  $title,
        ?string $description = null,
        ?int    $userId      = null,
        ?array  $metadata    = null
    ): int {
        return (int)$this->db->insert('lead_activities', [
            'lead_id'     => $leadId,
            'user_id'     => $userId,
            'type'        => $type,
            'title'       => $title,
            'description' => $description,
            'metadata'    => $metadata ? json_encode($metadata) : null,
        ]);
    }

    // =========================================================
    // STATUS & ASSIGNMENT
    // =========================================================

    /**
     * Change lead status and record activity.
     */
    public function updateStatus(int $leadId, int $newStatusId, ?int $userId = null): void
    {
        $lead        = $this->find($leadId);
        $oldStatusId = $lead['lead_status_id'] ?? null;

        $this->db->query("SELECT name FROM lead_statuses WHERE id = ?", [$oldStatusId]);
        $oldStatus = $this->db->fetch();

        $this->db->query("SELECT name, is_won FROM lead_statuses WHERE id = ?", [$newStatusId]);
        $newStatus = $this->db->fetch();

        $updateData = ['lead_status_id' => $newStatusId];

        if ($newStatus && $newStatus['is_won']) {
            $updateData['converted_at'] = date('Y-m-d H:i:s');
        }

        if (empty($lead['first_contacted_at']) && $newStatusId != $oldStatusId) {
            $updateData['first_contacted_at'] = date('Y-m-d H:i:s');
        }

        $updateData['last_contacted_at'] = date('Y-m-d H:i:s');

        $this->update($leadId, $updateData);

        $this->addActivity(
            $leadId,
            'status_change',
            'Status changed from ' . ($oldStatus['name'] ?? 'Unknown') . ' to ' . ($newStatus['name'] ?? 'Unknown'),
            null,
            $userId,
            ['old_status' => $oldStatus['name'] ?? '', 'new_status' => $newStatus['name'] ?? '']
        );
    }

    /**
     * Assign lead to a counselor and notify them.
     */
    public function assignTo(int $leadId, int $counselorId, ?int $userId = null): void
    {
        $this->db->query(
            "SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE id = ?",
            [$counselorId]
        );
        $counselor = $this->db->fetch();

        $this->update($leadId, ['assigned_to' => $counselorId]);

        $this->addActivity(
            $leadId,
            'assignment',
            'Lead assigned to ' . ($counselor['name'] ?? 'Unknown'),
            null,
            $userId,
            ['assigned_to' => $counselorId]
        );

        $lead = $this->find($leadId);
        $this->db->insert('notifications', [
            'user_id'        => $counselorId,
            'institution_id' => $lead['institution_id'] ?? null,
            'type'           => 'lead_assigned',
            'title'          => 'New Lead Assigned',
            'message'        => 'Lead ' . ($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')
                              . ' has been assigned to you.',
            'action_url'     => '/leads/' . $leadId,
        ]);
    }

    // =========================================================
    // LOOKUPS
    // =========================================================

    /**
     * All lead statuses ordered by sort_order.
     */
    public function getStatuses(): array
    {
        $this->db->query("SELECT * FROM lead_statuses ORDER BY sort_order");
        return $this->db->fetchAll();
    }

    /**
     * All active lead sources ordered by name.
     */
    public function getSources(): array
    {
        $this->db->query("SELECT * FROM lead_sources WHERE is_active = 1 ORDER BY name");
        return $this->db->fetchAll();
    }

    /**
     * ID of the default (is_default = 1) lead status.
     */
    public function getDefaultStatusId(): int
    {
        $this->db->query("SELECT id FROM lead_statuses WHERE is_default = 1 LIMIT 1");
        $row = $this->db->fetch();
        return $row ? (int)$row['id'] : 1;
    }

    // =========================================================
    // EXPORT
    // =========================================================

    /**
     * Flat export-ready array with human-readable column values.
     * Supports filters: status_id, date_from, date_to.
     */
    public function getExportData(array $filters = []): array
    {
        $where  = "l.deleted_at IS NULL";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND l.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['status_id'])) {
            $where   .= " AND l.lead_status_id = ?";
            $params[] = $filters['status_id'];
        }

        if (!empty($filters['date_from'])) {
            $where   .= " AND DATE(l.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where   .= " AND DATE(l.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql = "SELECT l.lead_number,
                       l.first_name,
                       l.last_name,
                       l.email,
                       l.phone,
                       l.city,
                       l.state,
                       l.qualification,
                       l.percentage,
                       l.passing_year,
                       l.school_college,
                       l.priority,
                       l.lead_score,
                       l.notes,
                       l.campaign_name,
                       l.reference_name,
                       l.budget,
                       l.expected_join_date,
                       l.next_followup_date,
                       l.hostel_required,
                       l.transport_required,
                       l.scholarship_required,
                       l.created_at,
                       ls.name   AS status,
                       lsrc.name AS source,
                       CONCAT(u.first_name, ' ', u.last_name) AS assigned_to,
                       c.name    AS course_interested,
                       d.name    AS department
                FROM leads l
                LEFT JOIN lead_statuses  ls   ON ls.id   = l.lead_status_id
                LEFT JOIN lead_sources   lsrc ON lsrc.id = l.lead_source_id
                LEFT JOIN users          u    ON u.id    = l.assigned_to
                LEFT JOIN courses        c    ON c.id    = l.course_interested_id
                LEFT JOIN departments    d    ON d.id    = l.department_id
                WHERE {$where}
                ORDER BY l.created_at DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    // =========================================================
    // STATS DASHBOARD
    // =========================================================

    /**
     * Single-query aggregate stats for the leads dashboard.
     *
     * Returns:
     *   total, hot, warm, cold, followup_due, converted, this_month
     */
    public function getStats(int $institutionId): array
    {
        $sql = "SELECT
                    COUNT(*)                                             AS total,
                    SUM(converted_at IS NOT NULL)                        AS converted,
                    SUM(DATE(created_at) >= DATE_FORMAT(NOW(),'%Y-%m-01')) AS this_month
                FROM leads
                WHERE institution_id = ? AND deleted_at IS NULL";

        $this->db->query($sql, [$institutionId]);
        $row = $this->db->fetch();

        $stats = [
            'total'        => (int)($row['total']     ?? 0),
            'hot'          => 0,
            'warm'         => 0,
            'cold'         => 0,
            'followup_due' => 0,
            'converted'    => (int)($row['converted'] ?? 0),
            'this_month'   => (int)($row['this_month'] ?? 0),
        ];

        // Extended stats only available after migration 16
        if ($this->isEnhanced()) {
            $this->db->query(
                "SELECT
                    SUM(priority = 'hot')  AS hot,
                    SUM(priority = 'warm') AS warm,
                    SUM(priority = 'cold') AS cold,
                    SUM(next_followup_date IS NOT NULL AND next_followup_date <= CURDATE()) AS followup_due
                 FROM leads
                 WHERE institution_id = ? AND deleted_at IS NULL",
                [$institutionId]
            );
            $ext = $this->db->fetch();
            $stats['hot']          = (int)($ext['hot']          ?? 0);
            $stats['warm']         = (int)($ext['warm']         ?? 0);
            $stats['cold']         = (int)($ext['cold']         ?? 0);
            $stats['followup_due'] = (int)($ext['followup_due'] ?? 0);
        }

        return $stats;
    }

    // =========================================================
    // FOLLOWUPS
    // =========================================================

    /**
     * Insert a new followup record, update lead's followup fields,
     * and append an activity log entry.
     *
     * $data keys expected:
     *   followup_date, followup_mode, status, outcome (optional),
     *   notes (optional), duration_minutes (optional),
     *   next_followup_date (optional), next_followup_mode (optional)
     *
     * Returns the new lead_followups.id.
     */
    public function addFollowup(int $leadId, array $data, int $institutionId, int $userId): int
    {
        // Normalise and guard required fields
        $followupDate = $data['followup_date'] ?? date('Y-m-d');
        $mode         = $data['followup_mode']  ?? 'call';
        $status       = $data['status']         ?? 'completed';
        $notes        = $data['notes']          ?? null;

        // 1. Insert into lead_followups
        $newId = (int)$this->db->insert('lead_followups', [
            'lead_id'            => $leadId,
            'institution_id'     => $institutionId,
            'counselor_id'       => $userId,
            'followup_date'      => $followupDate,
            'followup_mode'      => $mode,
            'duration_minutes'   => isset($data['duration_minutes']) && $data['duration_minutes'] !== ''
                                    ? (int)$data['duration_minutes'] : null,
            'status'             => $status,
            'outcome'            => $data['outcome']           ?? null,
            'notes'              => $notes,
            'next_followup_date' => $data['next_followup_date'] ?? null,
            'next_followup_mode' => $data['next_followup_mode'] ?? null,
            'created_by'         => $userId,
        ]);

        // 2. Map followup mode to lead_activities type ENUM
        //    lead_activities.type allows: note, call, email, sms, whatsapp, meeting,
        //    status_change, assignment, document, system
        $activityTypeMap = [
            'call'      => 'call',
            'whatsapp'  => 'whatsapp',
            'email'     => 'email',
            'meeting'   => 'meeting',
            'visit'     => 'note',  // 'visit' has no direct enum value; log as note
        ];
        $activityType = $activityTypeMap[$mode] ?? 'note';

        // Build a human-readable title
        $modeLabel = ucfirst($mode);
        $title     = $modeLabel . ' follow-up — ' . ucfirst($status);
        if (!empty($data['outcome'])) {
            $title .= ' (' . str_replace('_', ' ', $data['outcome']) . ')';
        }

        $this->addActivity(
            $leadId,
            $activityType,
            $title,
            $notes,
            $userId,
            [
                'followup_id'   => $newId,
                'mode'          => $mode,
                'status'        => $status,
                'outcome'       => $data['outcome'] ?? null,
                'followup_date' => $followupDate,
            ]
        );

        // 3. Update lead's follow-up tracking fields
        $leadUpdate = [
            'last_followup_date'  => $followupDate,
            'last_contacted_at'   => date('Y-m-d H:i:s'),
        ];

        if (!empty($data['next_followup_date'])) {
            $leadUpdate['next_followup_date'] = $data['next_followup_date'];
            $leadUpdate['followup_mode']      = $data['next_followup_mode'] ?? null;
        }

        $this->db->update(
            'leads',
            $leadUpdate,
            'id = ?',
            [$leadId]
        );

        return $newId;
    }

    /**
     * All followup records for a lead, newest first.
     */
    public function getFollowups(int $leadId): array
    {
        $this->db->query(
            "SELECT lf.*, CONCAT(u.first_name, ' ', u.last_name) AS counselor_name
             FROM lead_followups lf
             LEFT JOIN users u ON u.id = lf.counselor_id
             WHERE lf.lead_id = ?
             ORDER BY lf.followup_date DESC",
            [$leadId]
        );
        return $this->db->fetchAll();
    }
}
