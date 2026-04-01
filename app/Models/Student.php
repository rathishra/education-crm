<?php
namespace App\Models;

use Core\Database\Database;

class Student extends BaseModel
{
    protected string $table = 'students';
    protected bool $softDeletes = true;

    const STATUSES = ['active', 'inactive', 'alumni', 'dropout', 'suspended'];
    const GENDERS = ['male', 'female', 'other'];
    const BLOOD_GROUPS = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
    const CATEGORIES = ['OC', 'BC', 'MBC', 'SC', 'ST', 'OBC'];
    const STUDENT_TYPES = ['hosteller', 'day_scholar'];
    const DOC_TYPES = [
        'marksheet'            => 'Marksheet',
        'transfer_certificate' => 'Transfer Certificate',
        'conduct_certificate'  => 'Conduct Certificate',
        'id_proof'             => 'ID Proof',
        'community_certificate'=> 'Community Certificate',
        'income_certificate'   => 'Income Certificate',
        'passport_photo'       => 'Passport Photo',
        'medical_record'       => 'Medical Record',
        'other'                => 'Other',
    ];

    /**
     * Generate unique admission number: ADM-YYYY-NNNNN
     */
    public function generateAdmissionNumber(int $institutionId): string
    {
        $year   = date('Y');
        $prefix = "ADM-{$year}-";
        $this->db->query(
            "SELECT admission_number FROM students WHERE institution_id = ? AND admission_number LIKE ? ORDER BY id DESC LIMIT 1",
            [$institutionId, $prefix . '%']
        );
        $last = $this->db->fetch();
        if ($last) {
            $lastNum = (int)substr($last['admission_number'], strlen($prefix));
            $newNum  = str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNum = '00001';
        }
        return $prefix . $newNum;
    }

    /**
     * Generate unique student ID: STU-{INSTCODE}-{YEAR}-{SEQ}
     */
    public function generateStudentId(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst     = $this->db->fetch();
        $instCode = $inst ? $inst['code'] : 'GEN';
        $year     = date('Y');

        $this->db->query(
            "SELECT COUNT(*) as cnt FROM students WHERE institution_id = ? AND YEAR(created_at) = ?",
            [$institutionId, $year]
        );
        $row = $this->db->fetch();
        $seq = str_pad(($row['cnt'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        return "STU-{$instCode}-{$year}-{$seq}";
    }

    /**
     * Get paginated student list with filters (joins student_academics)
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where  = ['s.deleted_at IS NULL'];
        $params = [];

        if ($this->institutionScope) {
            $where[]  = 's.institution_id = ?';
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_number LIKE ? OR s.mobile_number LIKE ? OR s.roll_number LIKE ? OR s.student_id_number LIKE ? OR s.phone LIKE ? OR s.email LIKE ?)";
            $term    = '%' . $filters['search'] . '%';
            array_push($params, $term, $term, $term, $term, $term, $term, $term, $term);
        }

        if (!empty($filters['status'])) {
            $where[]  = 's.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['department_id'])) {
            $where[]  = '(sa.department_id = ? OR s.department_id = ?)';
            $params[] = $filters['department_id'];
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['course_id'])) {
            $where[]  = '(sa.course_id = ? OR s.course_id = ?)';
            $params[] = $filters['course_id'];
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['batch_id'])) {
            $where[]  = '(sa.batch_id = ? OR s.batch_id = ?)';
            $params[] = $filters['batch_id'];
            $params[] = $filters['batch_id'];
        }

        if (!empty($filters['gender'])) {
            $where[]  = 's.gender = ?';
            $params[] = $filters['gender'];
        }

        if (!empty($filters['admission_year'])) {
            $where[]  = 'YEAR(s.admission_date) = ?';
            $params[] = $filters['admission_year'];
        }

        $whereClause = implode(' AND ', $where);
        $sql = "
            SELECT s.*,
                   CONCAT(s.first_name, ' ', COALESCE(s.last_name,'')) AS full_name,
                   sa.department_id AS sa_department_id,
                   sa.course_id     AS sa_course_id,
                   sa.batch_id      AS sa_batch_id,
                   sa.section_id, sa.semester,
                   sa.admission_date AS sa_admission_date, sa.academic_status,
                   COALESCE(d2.name, d.name) AS department_name,
                   COALESCE(c2.name, c.name) AS course_name,
                   COALESCE(b2.name, b.name) AS batch_name
            FROM students s
            LEFT JOIN student_academics sa ON sa.student_id = s.id
            LEFT JOIN departments d  ON d.id  = s.department_id
            LEFT JOIN courses     c  ON c.id  = s.course_id
            LEFT JOIN batches      b  ON b.id  = s.batch_id
            LEFT JOIN departments d2 ON d2.id = sa.department_id
            LEFT JOIN courses     c2 ON c2.id = sa.course_id
            LEFT JOIN batches      b2 ON b2.id = sa.batch_id
            WHERE {$whereClause}
            ORDER BY s.first_name, s.last_name
        ";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Get single student with legacy-compatible detail joins
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT s.*, c.name as course_name, c.code as course_code,
                       b.name as batch_name, b.start_date as batch_start, b.end_date as batch_end,
                       sec.name as section_name, sec.code as section_code,
                       d.name as department_name,
                       i.name as institution_name, i.code as institution_code,
                       CONCAT(uc.first_name, ' ', uc.last_name) as created_by_name
                FROM students s
                LEFT JOIN courses      c   ON c.id   = s.course_id
                LEFT JOIN batches       b   ON b.id   = s.batch_id
                LEFT JOIN sections     sec ON sec.id  = s.section_id
                LEFT JOIN departments  d   ON d.id   = s.department_id
                LEFT JOIN institutions i   ON i.id   = s.institution_id
                LEFT JOIN users        uc  ON uc.id  = s.created_by
                WHERE s.id = ? AND s.deleted_at IS NULL";
        $this->db->query($sql, [$id]);
        $student = $this->db->fetch();

        if (!$student) return null;

        $student['fee_summary'] = $this->getFeeSummary($id);

        // Documents (legacy table + new dedicated table)
        $this->db->query(
            "SELECT * FROM student_documents WHERE student_id = ? AND deleted_at IS NULL ORDER BY created_at DESC",
            [$id]
        );
        $student['documents'] = $this->db->fetchAll();

        // Activities
        $this->db->query(
            "SELECT sa.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM student_activities sa
             LEFT JOIN users u ON u.id = sa.user_id
             WHERE sa.student_id = ?
             ORDER BY sa.created_at DESC",
            [$id]
        );
        $student['activities'] = $this->db->fetchAll();

        return $student;
    }

    /**
     * Get full student with all relations (360 view)
     */
    public function findWith360View(int $id): ?array
    {
        $this->db->query(
            "SELECT s.*,
                    CONCAT(s.first_name,' ',COALESCE(s.middle_name,''),' ',COALESCE(s.last_name,'')) AS full_name,
                    sa.department_id AS sa_department_id,
                    sa.course_id     AS sa_course_id,
                    sa.batch_id      AS sa_batch_id,
                    sa.section_id, sa.semester,
                    sa.admission_date AS sa_admission_date,
                    sa.admission_type, sa.quota, sa.academic_status,
                    sa.previous_school, sa.previous_marks,
                    COALESCE(d2.name, d.name)  AS department_name,
                    COALESCE(c2.name, c.name)  AS course_name,
                    COALESCE(c2.code, c.code)  AS course_code,
                    COALESCE(b2.name, b.name)  AS batch_name,
                    sec.name AS section_name,
                    i.name   AS institution_name
             FROM students s
             LEFT JOIN student_academics sa ON sa.student_id = s.id
             LEFT JOIN departments  d   ON d.id   = s.department_id
             LEFT JOIN courses      c   ON c.id   = s.course_id
             LEFT JOIN batches       b   ON b.id   = s.batch_id
             LEFT JOIN departments  d2  ON d2.id  = sa.department_id
             LEFT JOIN courses      c2  ON c2.id  = sa.course_id
             LEFT JOIN batches       b2  ON b2.id  = sa.batch_id
             LEFT JOIN sections     sec ON sec.id = sa.section_id
             LEFT JOIN institutions i   ON i.id   = s.institution_id
             WHERE s.id = ? AND s.deleted_at IS NULL",
            [$id]
        );
        $student = $this->db->fetch();
        if (!$student) return null;

        // Parents (new dedicated table first, fall back to inline fields)
        $this->db->query("SELECT * FROM student_parents WHERE student_id = ?", [$id]);
        $student['parents'] = $this->db->fetch() ?: [];

        // Documents
        $this->db->query(
            "SELECT * FROM student_documents WHERE student_id = ? AND deleted_at IS NULL ORDER BY created_at DESC",
            [$id]
        );
        $student['documents'] = $this->db->fetchAll();

        // Behaviour records
        $this->db->query(
            "SELECT sb.*, u.first_name AS added_by_name FROM student_behaviour sb
             LEFT JOIN users u ON u.id = sb.added_by
             WHERE sb.student_id = ? ORDER BY sb.incident_date DESC LIMIT 10",
            [$id]
        );
        $student['behaviour'] = $this->db->fetchAll();

        // Timeline (last 30 events)
        $this->db->query(
            "SELECT st.*, u.first_name AS actor_name FROM student_timeline st
             LEFT JOIN users u ON u.id = st.added_by
             WHERE st.student_id = ? ORDER BY st.event_date DESC LIMIT 30",
            [$id]
        );
        $student['timeline'] = $this->db->fetchAll();

        // Fee summary
        $student['fee_summary'] = $this->getFeeSummary($id);

        // Recent payments
        $this->db->query(
            "SELECT p.*, u.first_name AS received_by_name FROM payments p
             LEFT JOIN users u ON u.id = p.received_by
             WHERE p.student_id = ? ORDER BY p.payment_date DESC LIMIT 5",
            [$id]
        );
        $student['recent_payments'] = $this->db->fetchAll();

        // Tags
        $this->db->query("SELECT * FROM student_tags WHERE student_id = ?", [$id]);
        $student['tags'] = $this->db->fetchAll();

        return $student;
    }

    /**
     * Comprehensive 360-degree student profile (legacy alias)
     */
    public function getProfile360(int $id): ?array
    {
        $student = $this->findWithDetails($id);
        if (!$student) return null;

        // Installments
        $this->db->query(
            "SELECT si.*, sf.total_amount as fee_total
             FROM student_installments si
             JOIN student_fees sf ON sf.id = si.student_fee_id
             WHERE sf.student_id = ?
             ORDER BY si.due_date ASC",
            [$id]
        );
        $student['installments'] = $this->db->fetchAll();

        // Payment history
        $this->db->query(
            "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) as collected_by_name
             FROM payments p
             LEFT JOIN users u ON u.id = p.collected_by
             WHERE p.student_id = ? AND p.status = 'success'
             ORDER BY p.payment_date DESC",
            [$id]
        );
        $student['payments'] = $this->db->fetchAll();

        $student['attendance_summary'] = [
            'total_classes' => 0,
            'attended'      => 0,
            'percentage'    => 0,
        ];

        return $student;
    }

    /**
     * Dashboard stats for student module
     */
    public function getDashboardStats(int $institutionId): array
    {
        $stats = [];

        $this->db->query(
            "SELECT status, COUNT(*) as cnt FROM students WHERE institution_id = ? AND deleted_at IS NULL GROUP BY status",
            [$institutionId]
        );
        $byStatus          = $this->db->fetchAll();
        $stats['total']    = 0;
        $stats['by_status'] = [];
        foreach ($byStatus as $row) {
            $stats['by_status'][$row['status']] = (int)$row['cnt'];
            $stats['total'] += (int)$row['cnt'];
        }
        $stats['active']   = $stats['by_status']['active'] ?? 0;
        $stats['alumni']   = $stats['by_status']['alumni'] ?? 0;
        $stats['inactive'] = $stats['by_status']['inactive'] ?? 0;

        // New admissions this month
        $this->db->query(
            "SELECT COUNT(*) as cnt FROM student_academics WHERE institution_id = ? AND admission_date >= DATE_FORMAT(NOW(),'%Y-%m-01')",
            [$institutionId]
        );
        $row = $this->db->fetch();
        $stats['new_this_month'] = (int)($row['cnt'] ?? 0);

        // By department
        $this->db->query(
            "SELECT dep.name, COUNT(s.id) as cnt FROM students s
             JOIN student_academics sa ON sa.student_id = s.id
             JOIN departments dep ON dep.id = sa.department_id
             WHERE s.institution_id = ? AND s.deleted_at IS NULL AND s.status = 'active'
             GROUP BY dep.id, dep.name ORDER BY cnt DESC",
            [$institutionId]
        );
        $stats['by_department'] = $this->db->fetchAll();

        // By course
        $this->db->query(
            "SELECT co.name, COUNT(s.id) as cnt FROM students s
             JOIN student_academics sa ON sa.student_id = s.id
             JOIN courses co ON co.id = sa.course_id
             WHERE s.institution_id = ? AND s.deleted_at IS NULL AND s.status = 'active'
             GROUP BY co.id, co.name ORDER BY cnt DESC LIMIT 10",
            [$institutionId]
        );
        $stats['by_course'] = $this->db->fetchAll();

        // Gender breakdown
        $this->db->query(
            "SELECT gender, COUNT(*) as cnt FROM students WHERE institution_id = ? AND deleted_at IS NULL AND status='active' GROUP BY gender",
            [$institutionId]
        );
        $stats['by_gender'] = [];
        foreach ($this->db->fetchAll() as $row) {
            $stats['by_gender'][$row['gender']] = (int)$row['cnt'];
        }

        return $stats;
    }

    /**
     * Get statistics: counts by status, gender distribution, course-wise count (legacy)
     */
    public function getStats(): array
    {
        $instWhere = '';
        $params    = [];

        if ($this->institutionScope) {
            $instWhere = ' AND institution_id = ?';
            $params[]  = $this->institutionScope;
        }

        // Status counts
        $this->db->query(
            "SELECT status, COUNT(*) as cnt FROM students WHERE deleted_at IS NULL {$instWhere} GROUP BY status",
            $params
        );
        $statusCounts = [];
        foreach ($this->db->fetchAll() as $row) {
            $statusCounts[$row['status']] = (int)$row['cnt'];
        }

        // Gender distribution
        $this->db->query(
            "SELECT gender, COUNT(*) as cnt FROM students WHERE deleted_at IS NULL {$instWhere} GROUP BY gender",
            $params
        );
        $genderCounts = [];
        foreach ($this->db->fetchAll() as $row) {
            $genderCounts[$row['gender'] ?? 'unknown'] = (int)$row['cnt'];
        }

        // Course-wise count
        $this->db->query(
            "SELECT c.name as course_name, COUNT(s.id) as cnt
             FROM students s
             LEFT JOIN courses c ON c.id = s.course_id
             WHERE s.deleted_at IS NULL {$instWhere}
             GROUP BY s.course_id, c.name
             ORDER BY cnt DESC",
            $params
        );
        $courseWise = $this->db->fetchAll();

        // New this month
        $this->db->query(
            "SELECT COUNT(*) as cnt FROM students WHERE deleted_at IS NULL AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) {$instWhere}",
            $params
        );
        $newThisMonth = $this->db->fetch();

        $totalActive    = $statusCounts['active'] ?? 0;
        $totalGraduated = $statusCounts['alumni'] ?? $statusCounts['passed_out'] ?? 0;
        $totalInactive  = ($statusCounts['inactive'] ?? 0) + ($statusCounts['dropout'] ?? 0) + ($statusCounts['dropped'] ?? 0) + ($statusCounts['suspended'] ?? 0) + ($statusCounts['transferred'] ?? 0);
        $totalAll       = array_sum($statusCounts);

        return [
            'total'           => $totalAll,
            'total_active'    => $totalActive,
            'total_graduated' => $totalGraduated,
            'total_inactive'  => $totalInactive,
            'new_this_month'  => (int)($newThisMonth['cnt'] ?? 0),
            'status_counts'   => $statusCounts,
            'gender_counts'   => $genderCounts,
            'course_wise'     => $courseWise,
        ];
    }

    /**
     * Add timeline event
     */
    public function addTimelineEvent(int $studentId, int $institutionId, string $type, string $title, array $data = [], ?int $userId = null): void
    {
        $this->db->insert('student_timeline', [
            'student_id'     => $studentId,
            'institution_id' => $institutionId,
            'event_type'     => $type,
            'event_title'    => $title,
            'event_data'     => !empty($data) ? json_encode($data) : null,
            'event_date'     => date('Y-m-d H:i:s'),
            'added_by'       => $userId,
        ]);
    }

    /**
     * Add activity to student timeline (legacy compatibility)
     */
    public function addActivity(int $studentId, string $type, string $description, ?int $userId = null, ?array $metadata = null): int
    {
        return (int)$this->db->insert('student_activities', [
            'student_id'  => $studentId,
            'user_id'     => $userId,
            'type'        => $type,
            'title'       => $description,
            'description' => $description,
            'metadata'    => $metadata ? json_encode($metadata) : null,
        ]);
    }

    /**
     * Update student status with activity log
     */
    public function updateStatus(int $id, string $status, ?string $reason = null, ?int $userId = null): void
    {
        $student   = $this->find($id);
        $oldStatus = $student['status'] ?? 'unknown';

        $this->update($id, ['status' => $status]);

        $description = "Status changed from {$oldStatus} to {$status}";
        if ($reason) {
            $description .= ". Reason: {$reason}";
        }

        $this->addActivity($id, 'status_change', $description, $userId, [
            'old_status' => $oldStatus,
            'new_status' => $status,
            'reason'     => $reason,
        ]);
    }

    /**
     * Bulk promote students to next semester
     */
    public function bulkPromote(array $studentIds, int $newSemester, int $institutionId): int
    {
        if (empty($studentIds)) return 0;
        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $params       = array_merge([$newSemester], $studentIds, [$institutionId]);
        $this->db->query(
            "UPDATE student_academics SET semester = ? WHERE student_id IN ({$placeholders}) AND institution_id = ?",
            $params
        );
        return $this->db->rowCount();
    }

    /**
     * Export data with filters
     */
    public function getExportData(array $filters = []): array
    {
        $where  = 's.deleted_at IS NULL';
        $params = [];

        if ($this->institutionScope) {
            $where   .= ' AND s.institution_id = ?';
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['status'])) {
            $where   .= ' AND s.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['course_id'])) {
            $where   .= ' AND s.course_id = ?';
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['batch_id'])) {
            $where   .= ' AND s.batch_id = ?';
            $params[] = $filters['batch_id'];
        }

        if (!empty($filters['department_id'])) {
            $where   .= ' AND s.department_id = ?';
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['gender'])) {
            $where   .= ' AND s.gender = ?';
            $params[] = $filters['gender'];
        }

        if (!empty($filters['search'])) {
            $where   .= ' AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id_number LIKE ? OR s.admission_number LIKE ? OR s.phone LIKE ?)';
            $s        = '%' . $filters['search'] . '%';
            $params   = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        $sql = "SELECT s.student_id_number, s.admission_number, s.first_name, s.middle_name, s.last_name,
                       s.email, s.phone, s.mobile_number,
                       s.date_of_birth, s.gender, s.category, s.aadhar_number, s.aadhaar_number,
                       s.address_line1, s.city, s.state, s.pincode,
                       s.father_name, s.father_phone, s.mother_name,
                       s.guardian_name, s.guardian_phone,
                       s.admission_date, s.status, s.student_type, s.created_at,
                       c.name as course_name, b.name as batch_name, d.name as department_name
                FROM students s
                LEFT JOIN courses     c ON c.id = s.course_id
                LEFT JOIN batches      b ON b.id = s.batch_id
                LEFT JOIN departments d ON d.id = s.department_id
                WHERE {$where}
                ORDER BY s.created_at DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Get fee summary for a student
     */
    public function getFeeSummary(int $studentId): array
    {
        $this->db->query(
            "SELECT
                COALESCE(SUM(net_amount), 0)         as total_fees,
                COALESCE(SUM(paid_amount), 0)        as total_paid,
                COALESCE(SUM(balance_amount), 0)     as total_balance,
                COALESCE(SUM(discount_amount), 0)    as total_discount,
                COALESCE(SUM(scholarship_amount), 0) as total_scholarship
             FROM student_fees
             WHERE student_id = ?",
            [$studentId]
        );
        $summary = $this->db->fetch() ?: [
            'total_fees'        => 0,
            'total_paid'        => 0,
            'total_balance'     => 0,
            'total_discount'    => 0,
            'total_scholarship' => 0,
        ];

        $this->db->query(
            "SELECT payment_date FROM payments WHERE student_id = ? AND status = 'success' ORDER BY payment_date DESC LIMIT 1",
            [$studentId]
        );
        $lastPayment                    = $this->db->fetch();
        $summary['last_payment_date']   = $lastPayment['payment_date'] ?? null;

        return $summary;
    }
}
