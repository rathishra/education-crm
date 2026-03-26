<?php
namespace App\Models;

class Admission extends BaseModel
{
    protected string $table = 'admissions';
    protected bool $softDeletes = false;

    /**
     * Paginated list with filters and joins
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND a.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where .= " AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.admission_number LIKE ? OR a.phone LIKE ? OR a.email LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filters['status'])) {
            $where .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['course_id'])) {
            $where .= " AND a.course_id = ?";
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['batch_id'])) {
            $where .= " AND a.batch_id = ?";
            $params[] = $filters['batch_id'];
        }

        if (!empty($filters['academic_year_id'])) {
            $where .= " AND a.academic_year_id = ?";
            $params[] = $filters['academic_year_id'];
        }

        if (!empty($filters['date_from'])) {
            $where .= " AND DATE(a.application_date) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where .= " AND DATE(a.application_date) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql = "SELECT a.*,
                       c.name as course_name, c.code as course_code,
                       b.name as batch_name,
                       ay.name as academic_year_name,
                       s.student_id_number,
                       CONCAT(uc.first_name, ' ', uc.last_name) as created_by_name,
                       CONCAT(ua.first_name, ' ', ua.last_name) as approved_by_name
                FROM admissions a
                LEFT JOIN courses c ON c.id = a.course_id
                LEFT JOIN batches b ON b.id = a.batch_id
                LEFT JOIN academic_years ay ON ay.id = a.academic_year_id
                LEFT JOIN students s ON s.id = a.student_id
                LEFT JOIN users uc ON uc.id = a.created_by
                LEFT JOIN users ua ON ua.id = a.approved_by
                WHERE {$where}
                ORDER BY a.created_at DESC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Get single admission with all related data
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT a.*,
                       c.name as course_name, c.code as course_code,
                       b.name as batch_name, b.start_date as batch_start, b.end_date as batch_end,
                       ay.name as academic_year_name,
                       s.student_id_number, s.status as student_status,
                       l.lead_number, l.first_name as lead_first_name, l.last_name as lead_last_name,
                       CONCAT(uc.first_name, ' ', uc.last_name) as created_by_name,
                       CONCAT(ua.first_name, ' ', ua.last_name) as approved_by_name,
                       i.name as institution_name, i.code as institution_code
                FROM admissions a
                LEFT JOIN courses c ON c.id = a.course_id
                LEFT JOIN batches b ON b.id = a.batch_id
                LEFT JOIN academic_years ay ON ay.id = a.academic_year_id
                LEFT JOIN students s ON s.id = a.student_id
                LEFT JOIN leads l ON l.id = a.lead_id
                LEFT JOIN users uc ON uc.id = a.created_by
                LEFT JOIN users ua ON ua.id = a.approved_by
                LEFT JOIN institutions i ON i.id = a.institution_id
                WHERE a.id = ?";

        if ($this->institutionScope) {
            $sql .= " AND a.institution_id = ?";
            $this->db->query($sql, [$id, $this->institutionScope]);
        } else {
            $this->db->query($sql, [$id]);
        }

        $admission = $this->db->fetch();

        if (!$admission) return null;

        // Get documents
        $this->db->query(
            "SELECT * FROM documents WHERE documentable_type = 'admission' AND documentable_id = ? ORDER BY created_at DESC",
            [$id]
        );
        $admission['documents'] = $this->db->fetchAll();

        // Get student record if linked
        if ($admission['student_id']) {
            $this->db->query("SELECT * FROM students WHERE id = ?", [$admission['student_id']]);
            $admission['student'] = $this->db->fetch();
        } else {
            $admission['student'] = null;
        }

        return $admission;
    }

    /**
     * Generate unique admission number
     * Format: ADM-{INSTCODE}-{YYMMDD}-{SEQ}
     */
    public function generateAdmissionNumber(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst = $this->db->fetch();
        $instCode = $inst ? $inst['code'] : 'GEN';

        $dateCode = date('ymd');
        $prefix = "ADM-{$instCode}-{$dateCode}";

        // Get today's count for sequential numbering
        $this->db->query(
            "SELECT COUNT(*) as cnt FROM admissions WHERE admission_number LIKE ? AND institution_id = ?",
            [$prefix . '%', $institutionId]
        );
        $row = $this->db->fetch();
        $seq = str_pad(($row['cnt'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$seq}";
    }

    /**
     * Approve admission
     */
    public function approve(int $id, int $approvedBy): bool
    {
        $admission = $this->find($id);
        if (!$admission || !in_array($admission['status'], ['applied', 'under_review', 'documents_pending'])) {
            return false;
        }

        $this->update($id, [
            'status'      => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /**
     * Reject admission
     */
    public function reject(int $id, int $rejectedBy, string $reason): bool
    {
        $admission = $this->find($id);
        if (!$admission || !in_array($admission['status'], ['applied', 'under_review', 'documents_pending'])) {
            return false;
        }

        $this->update($id, [
            'status'      => 'rejected',
            'approved_by' => $rejectedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'remarks'     => $reason,
        ]);

        return true;
    }

    /**
     * Enroll student - create or activate student record
     */
    public function enroll(int $id): ?int
    {
        $admission = $this->findWithDetails($id);
        if (!$admission || $admission['status'] !== 'approved') {
            return null;
        }

        $studentId = $admission['student_id'];

        if (!$studentId) {
            // Generate student ID number
            $studentIdNumber = $this->generateStudentIdNumber($admission['institution_id']);

            // Create student record from admission data
            $studentData = [
                'institution_id'       => $admission['institution_id'],
                'admission_id'         => $id,
                'student_id_number'    => $studentIdNumber,
                'first_name'           => $admission['first_name'],
                'last_name'            => $admission['last_name'],
                'email'                => $admission['email'],
                'phone'                => $admission['phone'],
                'date_of_birth'        => $admission['date_of_birth'],
                'gender'               => $admission['gender'],
                'course_id'            => $admission['course_id'],
                'batch_id'             => $admission['batch_id'],
                'academic_year_id'     => $admission['academic_year_id'],
                'admission_date'       => $admission['admission_date'] ?: date('Y-m-d'),
                'admission_type'       => $admission['admission_type'],
                'lead_id'              => $admission['lead_id'],
                'status'               => 'active',
                'created_by'           => $admission['created_by'],
            ];

            $studentId = (int)$this->db->insert('students', $studentData);
        } else {
            // Activate existing student
            $this->db->update('students', ['status' => 'active'], '`id` = ?', [$studentId]);
        }

        // Update admission
        $this->update($id, [
            'status'         => 'enrolled',
            'student_id'     => $studentId,
            'admission_date' => $admission['admission_date'] ?: date('Y-m-d'),
        ]);

        return $studentId;
    }

    /**
     * Generate student ID number
     */
    private function generateStudentIdNumber(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst = $this->db->fetch();
        $instCode = $inst ? $inst['code'] : 'GEN';

        $year = date('Y');
        $prefix = "{$instCode}-{$year}";

        $this->db->query(
            "SELECT COUNT(*) as cnt FROM students WHERE student_id_number LIKE ? AND institution_id = ?",
            [$prefix . '%', $institutionId]
        );
        $row = $this->db->fetch();
        $seq = str_pad(($row['cnt'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$seq}";
    }

    /**
     * Create admission from a converted lead
     */
    public function createFromLead(int $leadId, array $data = []): ?array
    {
        $this->db->query("SELECT * FROM leads WHERE id = ? AND deleted_at IS NULL", [$leadId]);
        $lead = $this->db->fetch();

        if (!$lead) return null;

        return [
            'lead_id'                => $lead['id'],
            'first_name'             => $lead['first_name'],
            'last_name'              => $lead['last_name'] ?? '',
            'email'                  => $lead['email'] ?? '',
            'phone'                  => $lead['phone'],
            'date_of_birth'          => $lead['date_of_birth'] ?? '',
            'gender'                 => $lead['gender'] ?? '',
            'course_id'              => $lead['course_interested_id'] ?? '',
            'previous_qualification' => $lead['qualification'] ?? '',
            'previous_percentage'    => $lead['percentage'] ?? '',
            'address_line1'          => $lead['address_line1'] ?? '',
            'address_line2'          => $lead['address_line2'] ?? '',
            'city'                   => $lead['city'] ?? '',
            'state'                  => $lead['state'] ?? '',
            'pincode'                => $lead['pincode'] ?? '',
            'guardian_name'          => '',
            'guardian_phone'         => $lead['alternate_phone'] ?? '',
        ];
    }

    /**
     * Get admission statistics
     */
    public function getStats(): array
    {
        $where = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        // Counts by status
        $this->db->query(
            "SELECT status, COUNT(*) as cnt FROM admissions WHERE {$where} GROUP BY status",
            $params
        );
        $rows = $this->db->fetchAll();
        $statusCounts = [];
        foreach ($rows as $row) {
            $statusCounts[$row['status']] = (int)$row['cnt'];
        }

        // This month admissions
        $monthWhere = $where . " AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
        $monthParams = array_merge($params, [date('m'), date('Y')]);
        $this->db->query(
            "SELECT COUNT(*) as cnt FROM admissions WHERE {$monthWhere}",
            $monthParams
        );
        $monthRow = $this->db->fetch();

        // This month enrolled
        $enrolledWhere = $where . " AND status = 'enrolled' AND MONTH(created_at) = ? AND YEAR(created_at) = ?";
        $enrolledParams = array_merge($params, [date('m'), date('Y')]);
        $this->db->query(
            "SELECT COUNT(*) as cnt FROM admissions WHERE {$enrolledWhere}",
            $enrolledParams
        );
        $enrolledRow = $this->db->fetch();

        return [
            'total'               => array_sum($statusCounts),
            'applied'             => $statusCounts['applied'] ?? 0,
            'under_review'        => $statusCounts['under_review'] ?? 0,
            'documents_pending'   => $statusCounts['documents_pending'] ?? 0,
            'approved'            => $statusCounts['approved'] ?? 0,
            'rejected'            => $statusCounts['rejected'] ?? 0,
            'enrolled'            => $statusCounts['enrolled'] ?? 0,
            'cancelled'           => $statusCounts['cancelled'] ?? 0,
            'pending_count'       => ($statusCounts['applied'] ?? 0) + ($statusCounts['under_review'] ?? 0) + ($statusCounts['documents_pending'] ?? 0),
            'this_month'          => (int)($monthRow['cnt'] ?? 0),
            'enrolled_this_month' => (int)($enrolledRow['cnt'] ?? 0),
        ];
    }
}
