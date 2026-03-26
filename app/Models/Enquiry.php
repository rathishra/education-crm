<?php
namespace App\Models;

class Enquiry extends BaseModel
{
    protected string $table = 'enquiries';

    /**
     * Paginated list with filters and JOINs
     *
     * Supported filters:
     *   search         – enquiry_number, name, phone, email
     *   status         – ENUM value
     *   priority       – hot | warm | cold
     *   course_id      – course_interested_id
     *   department_id  – department_id
     *   counselor_id   – counselor_id
     *   source         – source string
     *   date_from      – DATE(created_at) >=
     *   date_to        – DATE(created_at) <=
     *   only_mine      – assigned_to = user_id
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where  = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND e.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where   .= " AND (e.enquiry_number LIKE ? OR CONCAT(e.first_name,' ',COALESCE(e.last_name,'')) LIKE ? OR e.phone LIKE ? OR e.email LIKE ?)";
            $s        = '%' . $filters['search'] . '%';
            $params   = array_merge($params, [$s, $s, $s, $s]);
        }

        if (!empty($filters['status'])) {
            $where   .= " AND e.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['priority'])) {
            $where   .= " AND e.priority = ?";
            $params[] = $filters['priority'];
        }

        if (!empty($filters['course_id'])) {
            $where   .= " AND e.course_interested_id = ?";
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['department_id'])) {
            $where   .= " AND e.department_id = ?";
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['counselor_id'])) {
            $where   .= " AND e.counselor_id = ?";
            $params[] = $filters['counselor_id'];
        }

        if (!empty($filters['source'])) {
            $where   .= " AND e.source = ?";
            $params[] = $filters['source'];
        }

        if (!empty($filters['date_from'])) {
            $where   .= " AND DATE(e.created_at) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where   .= " AND DATE(e.created_at) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['only_mine'])) {
            $where   .= " AND e.assigned_to = ?";
            $params[] = $filters['only_mine'];
        }

        $sql = "SELECT e.*,
                       c.name  AS course_name,
                       CONCAT(au.first_name, ' ', au.last_name) AS assigned_to_name,
                       i.name  AS institution_name
                FROM enquiries e
                LEFT JOIN courses      c  ON c.id  = e.course_interested_id
                LEFT JOIN users        au ON au.id = e.assigned_to
                LEFT JOIN institutions i  ON i.id  = e.institution_id
                WHERE {$where}
                ORDER BY e.created_at DESC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Aggregate statistics for the dashboard stat cards
     */
    public function getStats(int $institutionId): array
    {
        $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'new')       AS new_count,
                SUM(status = 'contacted') AS contacted,
                SUM(status = 'converted') AS converted,
                SUM(DATE(created_at) >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS this_month
             FROM enquiries
             WHERE institution_id = ?",
            [$institutionId]
        );
        $row = $this->db->fetch();
        return array_merge([
            'total'      => 0,
            'new_count'  => 0,
            'contacted'  => 0,
            'interested' => 0,
            'hot'        => 0,
            'warm'       => 0,
            'cold'       => 0,
            'converted'  => 0,
            'this_month' => 0,
        ], $row ?: []);
    }

    /**
     * Fetch a single enquiry with all related details
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT e.*,
                       c.name  AS course_name,  c.code AS course_code,
                       CONCAT(au.first_name, ' ', au.last_name) AS assigned_to_name,
                       i.name  AS institution_name,
                       i.code  AS institution_code
                FROM enquiries e
                LEFT JOIN courses      c  ON c.id  = e.course_interested_id
                LEFT JOIN users        au ON au.id = e.assigned_to
                LEFT JOIN institutions i  ON i.id  = e.institution_id
                WHERE e.id = ?";
        $this->db->query($sql, [$id]);
        return $this->db->fetch() ?: null;
    }

    /**
     * Check whether migration 15_enquiries_enhanced.sql has been applied.
     * Looks for the 'priority' column as a proxy for the full migration.
     * Result is cached in a static so the SHOW COLUMNS query runs only once per request.
     */
    public function hasExtendedColumns(): bool
    {
        static $result = null;
        if ($result === null) {
            try {
                $this->db->query("SELECT priority FROM enquiries LIMIT 0");
                $result = true;
            } catch (\Throwable $e) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Auto-generate enquiry number: ENQ-{INST}-{YYYYMMDD}-{XXXX}
     */
    public function generateEnquiryNumber(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst     = $this->db->fetch();
        $instCode = $inst ? $inst['code'] : 'GEN';
        return generateNumber('ENQ', $instCode);
    }

    /**
     * Check for duplicate enquiry by phone or email within the same institution.
     *
     * Returns an array with keys: field, enquiry_number, name, id — or null if no duplicate.
     */
    public function checkDuplicate(string $phone, string $email, int $institutionId, int $excludeId = 0): ?array
    {
        $conditions = [];
        $params     = [];

        if ($phone !== '') {
            $conditions[] = "phone = ?";
            $params[]     = $phone;
        }
        if ($email !== '') {
            $conditions[] = "email = ?";
            $params[]     = $email;
        }

        if (empty($conditions)) {
            return null;
        }

        $orClause = '(' . implode(' OR ', $conditions) . ')';
        $sql      = "SELECT id, enquiry_number, first_name, last_name, phone, email
                     FROM enquiries
                     WHERE {$orClause}
                       AND institution_id = ?";
        $params[] = $institutionId;

        if ($excludeId > 0) {
            $sql     .= " AND id != ?";
            $params[] = $excludeId;
        }

        $sql .= " LIMIT 1";
        $this->db->query($sql, $params);
        $row = $this->db->fetch();

        if (!$row) {
            return null;
        }

        // Determine which field matched
        $field = 'phone';
        if ($phone !== '' && $row['phone'] === $phone) {
            $field = 'phone';
        } elseif ($email !== '' && strtolower($row['email'] ?? '') === strtolower($email)) {
            $field = 'email';
        }

        return [
            'field'          => $field,
            'enquiry_number' => $row['enquiry_number'],
            'name'           => trim($row['first_name'] . ' ' . ($row['last_name'] ?? '')),
            'id'             => (int)$row['id'],
        ];
    }

    /**
     * Convert enquiry to a lead record.
     * Maps extended fields: source, priority, campaign_name, counselor_id→assigned_to,
     * department_id, remarks→notes.
     */
    public function convertToLead(int $enquiryId): ?int
    {
        $enquiry = $this->findWithDetails($enquiryId);
        if (!$enquiry) {
            return null;
        }

        $leadModel = new Lead();
        $leadModel->setInstitutionScope($enquiry['institution_id']);

        // Map enquiry priority to lead priority
        $priorityMap = ['hot' => 'high', 'warm' => 'medium', 'cold' => 'low'];
        $leadPriority = $priorityMap[$enquiry['priority'] ?? 'warm'] ?? 'medium';

        $leadData = [
            'institution_id'       => $enquiry['institution_id'],
            'lead_number'          => $leadModel->generateLeadNumber($enquiry['institution_id']),
            'first_name'           => $enquiry['first_name'],
            'last_name'            => $enquiry['last_name'] ?? null,
            'phone'                => $enquiry['phone'],
            'email'                => $enquiry['email'] ?? null,
            'gender'               => $enquiry['gender'] ?? null,
            'date_of_birth'        => $enquiry['date_of_birth'] ?? null,
            'course_interested_id' => $enquiry['course_interested_id'] ?? null,
            'lead_status_id'       => $leadModel->getDefaultStatusId(),
            'priority'             => $leadPriority,
            'source'               => $enquiry['source'] ?? null,
            'notes'                => $enquiry['remarks'] ?? $enquiry['message'] ?? null,
            'assigned_to'          => $enquiry['counselor_id'] ?? $enquiry['assigned_to'] ?? null,
            'enquiry_id'           => $enquiryId,
        ];

        $leadId = (int)$this->db->insert('leads', $leadData);
        if (!$leadId) {
            return null;
        }

        // Mark enquiry as converted
        $this->update($enquiryId, [
            'status'  => 'converted',
            'lead_id' => $leadId,
        ]);

        return $leadId;
    }

    /**
     * Delete an enquiry record.
     * Once migration 15_enquiries_enhanced.sql is run, this can be changed
     * to soft-delete by setting deleted_at instead.
     */
    public function softDelete(int $id): bool
    {
        $this->db->query("DELETE FROM enquiries WHERE id = ?", [$id]);
        return true;
    }
}
