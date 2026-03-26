<?php
namespace App\Models;

class Admission extends BaseModel
{
    protected string $table    = 'admissions';
    protected bool $softDeletes = false;

    // ----------------------------------------------------------------
    // DOCUMENT TYPE CONSTANTS
    // ----------------------------------------------------------------

    const REQUIRED_DOCS = [
        'marksheet',
        'transfer_certificate',
        'id_proof',
        'photo',
    ];

    const OPTIONAL_DOCS = [
        'community_certificate',
        'income_certificate',
        'migration_certificate',
        'medical_certificate',
    ];

    // ----------------------------------------------------------------
    // STATUS CONSTANTS
    // ----------------------------------------------------------------

    const STATUS_DRAFT            = 'draft';
    const STATUS_PENDING          = 'pending';
    const STATUS_DOCUMENT_PENDING = 'document_pending';
    const STATUS_PAYMENT_PENDING  = 'payment_pending';
    const STATUS_CONFIRMED        = 'confirmed';
    const STATUS_ENROLLED         = 'enrolled';
    const STATUS_REJECTED         = 'rejected';
    const STATUS_CANCELLED        = 'cancelled';

    // ----------------------------------------------------------------
    // STATUS LABELS  [status => [label, badge_class]]
    // ----------------------------------------------------------------

    const STATUS_LABELS = [
        'draft'            => ['Draft',            'bg-light text-dark'],
        'pending'          => ['Pending',           'bg-warning text-dark'],
        'document_pending' => ['Documents Pending', 'bg-info'],
        'payment_pending'  => ['Payment Pending',   'bg-primary'],
        'confirmed'        => ['Confirmed',         'bg-success'],
        'enrolled'         => ['Enrolled',          'bg-success'],
        'rejected'         => ['Rejected',          'bg-danger'],
        'cancelled'        => ['Cancelled',         'bg-secondary'],
    ];

    // ================================================================
    // LIST
    // ================================================================

    /**
     * Paginated list with filters and joins.
     *
     * Supported filters:
     *   search (admission_number, name, phone, email)
     *   status, course_id, batch_id, academic_year_id, counselor_id,
     *   department_id, payment_status, date_from, date_to
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where  = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND a.institution_id = ?";
            $params[] = $this->institutionScope;
        }

        if (!empty($filters['search'])) {
            $where   .= " AND (a.admission_number LIKE ? OR a.first_name LIKE ? OR a.last_name LIKE ?"
                      . " OR a.phone LIKE ? OR a.email LIKE ?)";
            $s        = '%' . $filters['search'] . '%';
            $params   = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filters['status'])) {
            $where   .= " AND a.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['course_id'])) {
            $where   .= " AND a.course_id = ?";
            $params[] = $filters['course_id'];
        }

        if (!empty($filters['batch_id'])) {
            $where   .= " AND a.batch_id = ?";
            $params[] = $filters['batch_id'];
        }

        if (!empty($filters['academic_year_id'])) {
            $where   .= " AND a.academic_year_id = ?";
            $params[] = $filters['academic_year_id'];
        }

        if (!empty($filters['counselor_id'])) {
            $where   .= " AND a.counselor_id = ?";
            $params[] = $filters['counselor_id'];
        }

        if (!empty($filters['department_id'])) {
            $where   .= " AND a.department_id = ?";
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['payment_status'])) {
            $where   .= " AND a.payment_status = ?";
            $params[] = $filters['payment_status'];
        }

        if (!empty($filters['date_from'])) {
            $where   .= " AND DATE(a.application_date) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where   .= " AND DATE(a.application_date) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql = "SELECT
                    a.*,
                    c.name  AS course_name,
                    c.code  AS course_code,
                    b.name  AS batch_name,
                    ay.name AS academic_year_name,
                    d.name  AS department_name,
                    s.student_id_number,
                    i.name  AS institution_name,
                    CONCAT(uc.first_name, ' ', uc.last_name)     AS created_by_name,
                    CONCAT(ua.first_name, ' ', ua.last_name)     AS approved_by_name,
                    CONCAT(counsel.first_name, ' ', counsel.last_name) AS counselor_name
                FROM admissions a
                LEFT JOIN courses       c      ON c.id      = a.course_id
                LEFT JOIN batches       b      ON b.id      = a.batch_id
                LEFT JOIN academic_years ay    ON ay.id     = a.academic_year_id
                LEFT JOIN departments   d      ON d.id      = a.department_id
                LEFT JOIN students      s      ON s.id      = a.student_id
                LEFT JOIN users         uc     ON uc.id     = a.created_by
                LEFT JOIN users         ua     ON ua.id     = a.approved_by
                LEFT JOIN users         counsel ON counsel.id = a.counselor_id
                LEFT JOIN institutions  i      ON i.id      = a.institution_id
                WHERE {$where}
                ORDER BY a.created_at DESC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    // ================================================================
    // DETAIL
    // ================================================================

    /**
     * Get a single admission with full JOINs plus documents, payments,
     * and timeline arrays embedded.
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT
                    a.*,
                    c.name       AS course_name,
                    c.code       AS course_code,
                    b.name       AS batch_name,
                    b.start_date AS batch_start,
                    b.end_date   AS batch_end,
                    ay.name      AS academic_year_name,
                    d.name       AS department_name,
                    sec.name     AS section_name,
                    s.student_id_number,
                    s.status     AS student_status,
                    l.lead_number,
                    l.first_name AS lead_first_name,
                    l.last_name  AS lead_last_name,
                    enq.id       AS enquiry_ref_id,
                    fs.name      AS fee_structure_name,
                    i.name       AS institution_name,
                    i.code       AS institution_code,
                    CONCAT(uc.first_name, ' ', uc.last_name)      AS created_by_name,
                    CONCAT(ua.first_name, ' ', ua.last_name)      AS approved_by_name,
                    CONCAT(counsel.first_name, ' ', counsel.last_name) AS counselor_name
                FROM admissions a
                LEFT JOIN courses        c      ON c.id      = a.course_id
                LEFT JOIN batches        b      ON b.id      = a.batch_id
                LEFT JOIN academic_years ay     ON ay.id     = a.academic_year_id
                LEFT JOIN departments    d      ON d.id      = a.department_id
                LEFT JOIN sections       sec    ON sec.id    = a.section_id
                LEFT JOIN students       s      ON s.id      = a.student_id
                LEFT JOIN leads          l      ON l.id      = a.lead_id
                LEFT JOIN enquiries      enq    ON enq.id    = a.enquiry_id
                LEFT JOIN fee_structures fs     ON fs.id     = a.fee_structure_id
                LEFT JOIN users          uc     ON uc.id     = a.created_by
                LEFT JOIN users          ua     ON ua.id     = a.approved_by
                LEFT JOIN users          counsel ON counsel.id = a.counselor_id
                LEFT JOIN institutions   i      ON i.id      = a.institution_id
                WHERE a.id = ?";

        if ($this->institutionScope) {
            $sql .= " AND a.institution_id = ?";
            $this->db->query($sql, [$id, $this->institutionScope]);
        } else {
            $this->db->query($sql, [$id]);
        }

        $admission = $this->db->fetch();
        if (!$admission) {
            return null;
        }

        $admission['documents'] = $this->getDocuments($id);
        $admission['payments']  = $this->getPayments($id);
        $admission['timeline']  = $this->getTimeline($id);

        return $admission;
    }

    // ================================================================
    // STATS
    // ================================================================

    /**
     * Single-query aggregate stats for the current institution scope.
     *
     * Returns:
     *   total, draft, pending, document_pending, payment_pending,
     *   confirmed, enrolled, rejected, cancelled,
     *   this_month, payment_pending_amount
     */
    public function getStats(): array
    {
        $where  = "1=1";
        $params = [];

        if ($this->institutionScope) {
            $where   .= " AND institution_id = ?";
            $params[] = $this->institutionScope;
        }

        $monthCond = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";

        $sql = "SELECT
                    COUNT(*)                                                          AS total,
                    SUM(status = 'draft')                                             AS draft,
                    SUM(status = 'pending')                                           AS pending,
                    SUM(status = 'document_pending')                                  AS document_pending,
                    SUM(status = 'payment_pending')                                   AS payment_pending,
                    SUM(status = 'confirmed')                                         AS confirmed,
                    SUM(status = 'enrolled')                                          AS enrolled,
                    SUM(status = 'rejected')                                          AS rejected,
                    SUM(status = 'cancelled')                                         AS cancelled,
                    SUM({$monthCond})                                                 AS this_month,
                    COALESCE(SUM(CASE WHEN status = 'payment_pending'
                                      THEN balance_amount ELSE 0 END), 0)            AS payment_pending_amount
                FROM admissions
                WHERE {$where}";

        $this->db->query($sql, $params);
        $row = $this->db->fetch();

        return [
            'total'                  => (int)($row['total']                  ?? 0),
            'draft'                  => (int)($row['draft']                  ?? 0),
            'pending'                => (int)($row['pending']                ?? 0),
            'document_pending'       => (int)($row['document_pending']       ?? 0),
            'payment_pending'        => (int)($row['payment_pending']        ?? 0),
            'confirmed'              => (int)($row['confirmed']              ?? 0),
            'enrolled'               => (int)($row['enrolled']               ?? 0),
            'rejected'               => (int)($row['rejected']               ?? 0),
            'cancelled'              => (int)($row['cancelled']              ?? 0),
            'this_month'             => (int)($row['this_month']             ?? 0),
            'payment_pending_amount' => (float)($row['payment_pending_amount'] ?? 0),
        ];
    }

    // ================================================================
    // GENERATE NUMBERS
    // ================================================================

    /**
     * Generate unique admission number.
     * Format: ADM-{INSTCODE}-{YYMMDD}-{SEQ4}
     */
    public function generateAdmissionNumber(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst     = $this->db->fetch();
        $instCode = $inst ? strtoupper($inst['code']) : 'GEN';

        $dateCode = date('ymd');
        $prefix   = "ADM-{$instCode}-{$dateCode}";

        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM admissions WHERE admission_number LIKE ? AND institution_id = ?",
            [$prefix . '%', $institutionId]
        );
        $row = $this->db->fetch();
        $seq = str_pad(($row['cnt'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$seq}";
    }

    /**
     * Generate unique student ID number.
     * Format: {INSTCODE}-{YYYY}-{SEQ4}
     */
    public function generateStudentIdNumber(int $institutionId): string
    {
        $this->db->query("SELECT code FROM institutions WHERE id = ?", [$institutionId]);
        $inst     = $this->db->fetch();
        $instCode = $inst ? strtoupper($inst['code']) : 'GEN';

        $year   = date('Y');
        $prefix = "{$instCode}-{$year}";

        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM students WHERE student_id_number LIKE ? AND institution_id = ?",
            [$prefix . '%', $institutionId]
        );
        $row = $this->db->fetch();
        $seq = str_pad(($row['cnt'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$seq}";
    }

    // ================================================================
    // WORKFLOW
    // ================================================================

    /**
     * Approve an admission (pending → confirmed).
     * Allowed from: pending, document_pending.
     */
    public function approve(int $id, int $userId): bool
    {
        $admission = $this->find($id);
        if (!$admission || !in_array($admission['status'], [
            self::STATUS_PENDING,
            self::STATUS_DOCUMENT_PENDING,
        ], true)) {
            return false;
        }

        $this->update($id, [
            'status'      => self::STATUS_CONFIRMED,
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->addTimeline(
            $id,
            'approved',
            'Admission confirmed',
            null,
            $admission['status'],
            self::STATUS_CONFIRMED,
            $userId
        );

        return true;
    }

    /**
     * Reject an admission.
     * Allowed from: pending, document_pending, payment_pending, confirmed.
     */
    public function reject(int $id, int $userId, string $reason): bool
    {
        $admission = $this->find($id);
        if (!$admission || !in_array($admission['status'], [
            self::STATUS_PENDING,
            self::STATUS_DOCUMENT_PENDING,
            self::STATUS_PAYMENT_PENDING,
            self::STATUS_CONFIRMED,
        ], true)) {
            return false;
        }

        $this->update($id, [
            'status'           => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);

        $this->addTimeline(
            $id,
            'rejected',
            'Admission rejected',
            $reason,
            $admission['status'],
            self::STATUS_REJECTED,
            $userId
        );

        return true;
    }

    /**
     * Cancel an admission.
     * Allowed from: pending, document_pending, payment_pending, confirmed.
     */
    public function cancel(int $id, int $userId, string $reason): bool
    {
        $admission = $this->find($id);
        if (!$admission || !in_array($admission['status'], [
            self::STATUS_PENDING,
            self::STATUS_DOCUMENT_PENDING,
            self::STATUS_PAYMENT_PENDING,
            self::STATUS_CONFIRMED,
        ], true)) {
            return false;
        }

        $this->update($id, [
            'status'           => self::STATUS_CANCELLED,
            'rejection_reason' => $reason,
        ]);

        $this->addTimeline(
            $id,
            'cancelled',
            'Admission cancelled',
            $reason,
            $admission['status'],
            self::STATUS_CANCELLED,
            $userId
        );

        return true;
    }

    /**
     * Reopen a cancelled or rejected admission (back to pending).
     * Allowed from: cancelled, rejected.
     */
    public function reopen(int $id, int $userId): bool
    {
        $admission = $this->find($id);
        if (!$admission || !in_array($admission['status'], [
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
        ], true)) {
            return false;
        }

        $this->update($id, [
            'status'           => self::STATUS_PENDING,
            'rejection_reason' => null,
        ]);

        $this->addTimeline(
            $id,
            'reopened',
            'Admission reopened',
            null,
            $admission['status'],
            self::STATUS_PENDING,
            $userId
        );

        return true;
    }

    /**
     * Enroll a confirmed admission — creates a student record.
     * Allowed only from: confirmed (or 'approved' for backward compatibility).
     * Returns the student_id on success, null otherwise.
     */
    public function enroll(int $id): ?int
    {
        $admission = $this->findWithDetails($id);
        if (!$admission || !in_array($admission['status'], [
            self::STATUS_CONFIRMED,
            'approved', // backward compat
        ], true)) {
            return null;
        }

        $studentId = $admission['student_id'] ? (int)$admission['student_id'] : null;

        if (!$studentId) {
            $studentIdNumber = $this->generateStudentIdNumber((int)$admission['institution_id']);

            $studentData = [
                'institution_id'    => $admission['institution_id'],
                'admission_id'      => $id,
                'student_id_number' => $studentIdNumber,
                'first_name'        => $admission['first_name'],
                'last_name'         => $admission['last_name'],
                'email'             => $admission['email'],
                'phone'             => $admission['phone'],
                'date_of_birth'     => $admission['date_of_birth'],
                'gender'            => $admission['gender'],
                'course_id'         => $admission['course_id'],
                'batch_id'          => $admission['batch_id'],
                'academic_year_id'  => $admission['academic_year_id'],
                'section_id'        => $admission['section_id'] ?? null,
                'department_id'     => $admission['department_id'] ?? null,
                'admission_date'    => $admission['admission_date'] ?: date('Y-m-d'),
                'admission_type'    => $admission['admission_type'],
                'category'          => $admission['category'] ?? null,
                'nationality'       => $admission['nationality'] ?? 'Indian',
                'address_line1'     => $admission['address_line1'] ?? null,
                'address_line2'     => $admission['address_line2'] ?? null,
                'city'              => $admission['city'] ?? null,
                'state'             => $admission['state'] ?? null,
                'pincode'           => $admission['pincode'] ?? null,
                'father_name'       => $admission['father_name'] ?? null,
                'father_phone'      => $admission['father_phone'] ?? null,
                'mother_name'       => $admission['mother_name'] ?? null,
                'guardian_name'     => $admission['guardian_name'] ?? null,
                'guardian_phone'    => $admission['guardian_phone'] ?? null,
                'lead_id'           => $admission['lead_id'],
                'enquiry_id'        => $admission['enquiry_id'] ?? null,
                'status'            => 'active',
                'created_by'        => $admission['created_by'],
            ];

            $studentId = (int)$this->db->insert('students', $studentData);
        } else {
            $this->db->update('students', ['status' => 'active'], '`id` = ?', [$studentId]);
        }

        $this->update($id, [
            'status'         => self::STATUS_ENROLLED,
            'student_id'     => $studentId,
            'admission_date' => $admission['admission_date'] ?: date('Y-m-d'),
        ]);

        $this->addTimeline(
            $id,
            'enrolled',
            'Student enrolled',
            "Student ID: {$studentId}",
            $admission['status'],
            self::STATUS_ENROLLED,
            (int)($admission['created_by'] ?? 0)
        );

        return $studentId;
    }

    /**
     * Mark admission as document_pending.
     */
    public function markDocumentPending(int $id, int $userId): void
    {
        $admission = $this->find($id);
        if (!$admission) {
            return;
        }

        $this->update($id, ['status' => self::STATUS_DOCUMENT_PENDING]);

        $this->addTimeline(
            $id,
            'status_change',
            'Status changed to Document Pending',
            null,
            $admission['status'],
            self::STATUS_DOCUMENT_PENDING,
            $userId
        );
    }

    /**
     * Mark admission as payment_pending.
     */
    public function markPaymentPending(int $id, int $userId): void
    {
        $admission = $this->find($id);
        if (!$admission) {
            return;
        }

        $this->update($id, ['status' => self::STATUS_PAYMENT_PENDING]);

        $this->addTimeline(
            $id,
            'status_change',
            'Status changed to Payment Pending',
            null,
            $admission['status'],
            self::STATUS_PAYMENT_PENDING,
            $userId
        );
    }

    // ================================================================
    // DOCUMENT MANAGEMENT
    // ================================================================

    /**
     * Create one checklist row per document type (required + optional)
     * with is_submitted = 0, so the UI can render the full checklist.
     */
    public function initDocumentChecklist(int $admissionId, int $institutionId): void
    {
        $allTypes = array_merge(self::REQUIRED_DOCS, self::OPTIONAL_DOCS);

        foreach ($allTypes as $docType) {
            // Only insert if no row already exists for this type
            $this->db->query(
                "SELECT id FROM admission_documents
                 WHERE admission_id = ? AND document_type = ?
                 LIMIT 1",
                [$admissionId, $docType]
            );
            if ($this->db->fetch()) {
                continue;
            }

            $this->db->insert('admission_documents', [
                'admission_id'        => $admissionId,
                'institution_id'      => $institutionId,
                'document_type'       => $docType,
                'document_name'       => ucwords(str_replace('_', ' ', $docType)),
                'is_required'         => in_array($docType, self::REQUIRED_DOCS, true) ? 1 : 0,
                'is_submitted'        => 0,
                'verification_status' => 'pending',
            ]);
        }
    }

    /**
     * Fetch all documents for an admission, required first then by type name.
     */
    public function getDocuments(int $admissionId): array
    {
        $this->db->query(
            "SELECT * FROM admission_documents
             WHERE admission_id = ?
             ORDER BY is_required DESC, document_type ASC",
            [$admissionId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Insert or update a submitted document row.
     * $fileData keys: original_filename, file_path, file_size, file_type, document_name (optional).
     * Returns the admission_documents.id.
     */
    public function submitDocument(int $admissionId, string $docType, array $fileData, int $userId): int
    {
        // Fetch existing checklist row if present
        $this->db->query(
            "SELECT id, admission_id FROM admission_documents
             WHERE admission_id = ? AND document_type = ?
             LIMIT 1",
            [$admissionId, $docType]
        );
        $existing = $this->db->fetch();

        $record = [
            'original_filename'   => $fileData['original_filename'] ?? null,
            'file_path'           => $fileData['file_path']         ?? null,
            'file_size'           => $fileData['file_size']         ?? null,
            'file_type'           => $fileData['file_type']         ?? null,
            'document_name'       => $fileData['document_name']     ?? ucwords(str_replace('_', ' ', $docType)),
            'is_submitted'        => 1,
            'submitted_at'        => date('Y-m-d H:i:s'),
            'is_verified'         => 0,
            'verification_status' => 'pending',
            'uploaded_by'         => $userId,
        ];

        if ($existing) {
            $docId = (int)$existing['id'];
            $this->db->update('admission_documents', $record, '`id` = ?', [$docId]);
        } else {
            // Fetch institution_id from the admission
            $adm   = $this->find($admissionId);
            $docId = (int)$this->db->insert('admission_documents', array_merge($record, [
                'admission_id'   => $admissionId,
                'institution_id' => $adm['institution_id'] ?? 0,
                'document_type'  => $docType,
                'is_required'    => in_array($docType, self::REQUIRED_DOCS, true) ? 1 : 0,
            ]));
        }

        $this->addTimeline(
            $admissionId,
            'document_uploaded',
            'Document uploaded: ' . ucwords(str_replace('_', ' ', $docType)),
            null,
            null,
            $docType,
            $userId
        );

        return $docId;
    }

    /**
     * Verify or reject a document.
     * $status: 'verified' | 'rejected'
     */
    public function verifyDocument(int $docId, string $status, string $notes, int $userId): void
    {
        // Retrieve the document to know its admission_id and type
        $this->db->query(
            "SELECT admission_id, document_type FROM admission_documents WHERE id = ? LIMIT 1",
            [$docId]
        );
        $doc = $this->db->fetch();
        if (!$doc) {
            return;
        }

        $isVerified = ($status === 'verified') ? 1 : 0;
        $eventType  = ($status === 'verified') ? 'document_verified' : 'document_rejected';

        $this->db->update('admission_documents', [
            'verification_status' => $status,
            'verified_by'         => $userId,
            'verified_at'         => date('Y-m-d H:i:s'),
            'verification_notes'  => $notes,
            'is_verified'         => $isVerified,
        ], '`id` = ?', [$docId]);

        $docLabel = ucwords(str_replace('_', ' ', $doc['document_type']));
        $this->addTimeline(
            (int)$doc['admission_id'],
            $eventType,
            "Document {$status}: {$docLabel}",
            $notes ?: null,
            null,
            $status,
            $userId
        );
    }

    /**
     * Check whether all required documents have been verified.
     */
    public function checkAllDocsVerified(int $admissionId): bool
    {
        $this->db->query(
            "SELECT COUNT(*) AS cnt
             FROM admission_documents
             WHERE admission_id = ?
               AND is_required = 1
               AND verification_status != 'verified'",
            [$admissionId]
        );
        $row = $this->db->fetch();
        return ((int)($row['cnt'] ?? 1)) === 0;
    }

    // ================================================================
    // PAYMENT MANAGEMENT
    // ================================================================

    /**
     * Record a payment against an admission.
     * $data keys: payment_date, amount, payment_mode, transaction_reference,
     *             receipt_number, fee_head, academic_year_id, remarks.
     * Returns the new admission_payments.id.
     */
    public function recordPayment(int $admissionId, array $data, int $userId): int
    {
        $admission = $this->find($admissionId);
        if (!$admission) {
            throw new \InvalidArgumentException("Admission {$admissionId} not found.");
        }

        $paymentId = (int)$this->db->insert('admission_payments', [
            'admission_id'          => $admissionId,
            'institution_id'        => $admission['institution_id'],
            'payment_date'          => $data['payment_date']          ?? date('Y-m-d'),
            'amount'                => $data['amount'],
            'payment_mode'          => $data['payment_mode']          ?? 'cash',
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'receipt_number'        => $data['receipt_number']        ?? null,
            'fee_head'              => $data['fee_head']              ?? null,
            'academic_year_id'      => $data['academic_year_id']      ?? null,
            'remarks'               => $data['remarks']               ?? null,
            'collected_by'          => $userId,
        ]);

        // Recalculate running totals
        $amount      = (float)$data['amount'];
        $newPaid     = (float)$admission['paid_amount'] + $amount;
        $finalFee    = (float)$admission['final_fee'];
        $balance     = $finalFee - $newPaid;
        $balance     = max(0, $balance);

        if ($balance <= 0) {
            $payStatus = 'paid';
        } elseif ($newPaid > 0) {
            $payStatus = 'partial';
        } else {
            $payStatus = 'pending';
        }

        $this->update($admissionId, [
            'paid_amount'    => $newPaid,
            'balance_amount' => $balance,
            'payment_status' => $payStatus,
        ]);

        $this->addTimeline(
            $admissionId,
            'payment_recorded',
            'Payment recorded: ₹' . number_format($amount, 2),
            isset($data['receipt_number']) ? 'Receipt: ' . $data['receipt_number'] : null,
            null,
            (string)$amount,
            $userId
        );

        return $paymentId;
    }

    /**
     * Fetch all payments for an admission with collector name.
     */
    public function getPayments(int $admissionId): array
    {
        $this->db->query(
            "SELECT ap.*,
                    CONCAT(u.first_name, ' ', u.last_name) AS collector_name
             FROM admission_payments ap
             LEFT JOIN users u ON u.id = ap.collected_by
             WHERE ap.admission_id = ?
             ORDER BY ap.payment_date DESC",
            [$admissionId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Recalculate final_fee, balance_amount and payment_status from
     * the stored total_fee, discount_amount, scholarship_amount and paid_amount.
     */
    public function recalculateFees(int $admissionId): void
    {
        $admission = $this->find($admissionId);
        if (!$admission) {
            return;
        }

        $finalFee = (float)$admission['total_fee']
                  - (float)$admission['discount_amount']
                  - (float)$admission['scholarship_amount'];
        $finalFee = max(0, $finalFee);

        $paid    = (float)$admission['paid_amount'];
        $balance = max(0, $finalFee - $paid);

        if ($balance <= 0 && $finalFee > 0) {
            $payStatus = 'paid';
        } elseif ($paid > 0) {
            $payStatus = 'partial';
        } else {
            $payStatus = 'pending';
        }

        $this->update($admissionId, [
            'final_fee'      => $finalFee,
            'balance_amount' => $balance,
            'payment_status' => $payStatus,
        ]);
    }

    // ================================================================
    // TIMELINE
    // ================================================================

    /**
     * Insert a timeline event for an admission.
     */
    public function addTimeline(
        int     $admissionId,
        string  $eventType,
        string  $title,
        ?string $desc    = null,
        ?string $oldVal  = null,
        ?string $newVal  = null,
        int     $userId  = 0
    ): void {
        $this->db->insert('admission_timeline', [
            'admission_id' => $admissionId,
            'event_type'   => $eventType,
            'title'        => $title,
            'description'  => $desc,
            'old_value'    => $oldVal,
            'new_value'    => $newVal,
            'performed_by' => $userId > 0 ? $userId : null,
        ]);
    }

    /**
     * Fetch timeline for an admission, newest first, with performer name.
     */
    public function getTimeline(int $admissionId): array
    {
        $this->db->query(
            "SELECT at.*,
                    CONCAT(u.first_name, ' ', u.last_name) AS performed_by_name
             FROM admission_timeline at
             LEFT JOIN users u ON u.id = at.performed_by
             WHERE at.admission_id = ?
             ORDER BY at.created_at DESC",
            [$admissionId]
        );
        return $this->db->fetchAll();
    }

    // ================================================================
    // HELPERS
    // ================================================================

    /**
     * Pre-fill admission data from a lead record.
     * Returns an array of field values (not saved) or null if lead not found.
     */
    public function createFromLead(int $leadId): ?array
    {
        $this->db->query(
            "SELECT * FROM leads WHERE id = ? AND deleted_at IS NULL",
            [$leadId]
        );
        $lead = $this->db->fetch();
        if (!$lead) {
            return null;
        }

        return [
            'lead_id'                => $lead['id'],
            'enquiry_id'             => $lead['enquiry_id']          ?? null,
            'first_name'             => $lead['first_name'],
            'last_name'              => $lead['last_name']            ?? '',
            'email'                  => $lead['email']                ?? '',
            'phone'                  => $lead['phone'],
            'date_of_birth'          => $lead['date_of_birth']        ?? null,
            'gender'                 => $lead['gender']               ?? null,
            'course_id'              => $lead['course_interested_id'] ?? null,
            'department_id'          => $lead['department_id']        ?? null,
            'previous_qualification' => $lead['qualification']        ?? null,
            'previous_percentage'    => $lead['percentage']           ?? null,
            'address_line1'          => $lead['address_line1']        ?? null,
            'address_line2'          => $lead['address_line2']        ?? null,
            'city'                   => $lead['city']                 ?? null,
            'state'                  => $lead['state']                ?? null,
            'pincode'                => $lead['pincode']              ?? null,
            'nationality'            => $lead['nationality']          ?? 'Indian',
            'guardian_name'          => null,
            'guardian_phone'         => $lead['alternate_phone']      ?? null,
            'counselor_id'           => $lead['assigned_to']          ?? null,
            'application_source'     => $lead['source']               ?? null,
        ];
    }

    /**
     * Check whether an admission can be confirmed (enroll-ready).
     * Conditions:
     *   1. All required documents must be verified.
     *   2. paid_amount >= initial_payment (if initial_payment > 0).
     *
     * Returns ['can' => bool, 'reason' => string]
     */
    public function canConfirm(int $admissionId): array
    {
        $admission = $this->find($admissionId);
        if (!$admission) {
            return ['can' => false, 'reason' => 'Admission not found.'];
        }

        // Document check
        if (!$this->checkAllDocsVerified($admissionId)) {
            return [
                'can'    => false,
                'reason' => 'One or more required documents have not been verified.',
            ];
        }

        // Payment check
        $initialPayment = (float)($admission['initial_payment'] ?? 0);
        if ($initialPayment > 0) {
            $paidAmount = (float)($admission['paid_amount'] ?? 0);
            if ($paidAmount < $initialPayment) {
                return [
                    'can'    => false,
                    'reason' => sprintf(
                        'Initial payment of ₹%s is required; ₹%s collected so far.',
                        number_format($initialPayment, 2),
                        number_format($paidAmount, 2)
                    ),
                ];
            }
        }

        return ['can' => true, 'reason' => ''];
    }

    /**
     * Check for duplicate applications by phone or email within the same institution.
     * Excludes the record with $excludeId (useful during edits).
     * Returns the first matching admission row, or null if no duplicate exists.
     */
    public function checkDuplicate(
        string $phone,
        string $email,
        int    $institutionId,
        int    $excludeId = 0
    ): ?array {
        $whereParts = ["institution_id = ?", "(phone = ? OR email = ?)"];
        $params     = [$institutionId, $phone, $email];

        if ($excludeId > 0) {
            $whereParts[] = "id != ?";
            $params[]     = $excludeId;
        }

        $where = implode(' AND ', $whereParts);

        $this->db->query(
            "SELECT * FROM admissions WHERE {$where} LIMIT 1",
            $params
        );
        $row = $this->db->fetch();

        return $row ?: null;
    }
}
