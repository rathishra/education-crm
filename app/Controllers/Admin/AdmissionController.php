<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admission;

class AdmissionController extends BaseController
{
    private Admission $admission;

    public function __construct()
    {
        parent::__construct();
        $this->admission = new Admission();
    }

    // ================================================================
    // INDEX
    // ================================================================

    public function index(): void
    {
        $this->authorize('admissions.view');

        $filters = [
            'search'          => $this->input('search'),
            'status'          => $this->input('status'),
            'course_id'       => $this->input('course_id'),
            'batch_id'        => $this->input('batch_id'),
            'academic_year_id'=> $this->input('academic_year_id'),
            'department_id'   => $this->input('department_id'),
            'counselor_id'    => $this->input('counselor_id'),
            'payment_status'  => $this->input('payment_status'),
            'date_from'       => $this->input('date_from'),
            'date_to'         => $this->input('date_to'),
        ];

        $page       = max(1, (int)($this->input('page') ?: 1));
        $admissions = $this->admission->getListPaginated($page, config('app.per_page', 15), $filters);
        $stats      = $this->admission->getStats();

        $db = $this->db;
        $db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $db->fetchAll();
        $db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $db->fetchAll();
        $db->query(
            "SELECT DISTINCT u.id, u.first_name, u.last_name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $counselors = $db->fetchAll();

        $this->view('admissions/index', compact('admissions', 'filters', 'stats', 'courses', 'departments', 'academicYears', 'counselors'));
    }

    // ================================================================
    // CREATE
    // ================================================================

    public function create(): void
    {
        $this->authorize('admissions.create');

        $leadId  = (int)$this->input('lead_id');
        $prefill = [];
        if ($leadId) {
            $prefill = $this->admission->createFromLead($leadId) ?? [];
        }

        $db = $this->db;
        $db->query("SELECT id, name, code FROM courses WHERE institution_id = ? AND deleted_at IS NULL AND status = 'active' ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $db->fetchAll();
        $db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $db->fetchAll();
        $db->query(
            "SELECT DISTINCT u.id, u.first_name, u.last_name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $counselors = $db->fetchAll();

        $this->view('admissions/create', compact('prefill', 'courses', 'departments', 'academicYears', 'counselors', 'leadId'));
    }

    // ================================================================
    // STORE
    // ================================================================

    public function store(): void
    {
        $this->authorize('admissions.create');

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'first_name' => 'required',
            'phone'      => 'required|phone',
            'course_id'  => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        // Duplicate check (non-blocking)
        $duplicate = $this->admission->checkDuplicate(
            sanitize($data['phone']),
            sanitize($data['email'] ?? ''),
            $this->institutionId
        );

        $insertData = [
            'institution_id'           => $this->institutionId,
            'admission_number'         => $this->admission->generateAdmissionNumber($this->institutionId),
            'lead_id'                  => $data['lead_id'] ?: null,
            'enquiry_id'               => $data['enquiry_id'] ?: null,
            // Personal
            'first_name'               => sanitize($data['first_name']),
            'last_name'                => sanitize($data['last_name'] ?? ''),
            'email'                    => sanitize($data['email'] ?? ''),
            'phone'                    => sanitize($data['phone']),
            'date_of_birth'            => $data['date_of_birth'] ?: null,
            'gender'                   => $data['gender'] ?? null,
            'category'                 => $data['category'] ?? null,
            'nationality'              => sanitize($data['nationality'] ?? 'Indian'),
            // Address
            'address_line1'            => sanitize($data['address_line1'] ?? ''),
            'address_line2'            => sanitize($data['address_line2'] ?? ''),
            'city'                     => sanitize($data['city'] ?? ''),
            'state'                    => sanitize($data['state'] ?? ''),
            'pincode'                  => sanitize($data['pincode'] ?? ''),
            // Parents
            'father_name'              => sanitize($data['father_name'] ?? ''),
            'father_phone'             => sanitize($data['father_phone'] ?? ''),
            'mother_name'              => sanitize($data['mother_name'] ?? ''),
            'guardian_name'            => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'           => sanitize($data['guardian_phone'] ?? ''),
            // Previous education
            'previous_qualification'   => sanitize($data['previous_qualification'] ?? ''),
            'previous_percentage'      => $data['previous_percentage'] ?: null,
            'previous_institution'     => sanitize($data['previous_institution'] ?? ''),
            'previous_year_of_passing' => $data['previous_year_of_passing'] ?: null,
            // Academic
            'department_id'            => $data['department_id'] ?: null,
            'course_id'                => (int)$data['course_id'],
            'batch_id'                 => $data['batch_id'] ?: null,
            'academic_year_id'         => $data['academic_year_id'] ?: null,
            'section_id'               => $data['section_id'] ?: null,
            'specialization'           => sanitize($data['specialization'] ?? ''),
            'current_semester'         => max(1, (int)($data['current_semester'] ?? 1)),
            'admission_type'           => $data['admission_type'] ?? 'regular',
            'quota'                    => $data['quota'] ?? 'general',
            'application_date'         => date('Y-m-d'),
            'application_source'       => sanitize($data['application_source'] ?? ''),
            // Counselor
            'counselor_id'             => $data['counselor_id'] ?: null,
            // Fees
            'fee_structure_id'         => $data['fee_structure_id'] ?: null,
            'total_fee'                => (float)($data['total_fee'] ?? 0),
            'discount_amount'          => (float)($data['discount_amount'] ?? 0),
            'scholarship_amount'       => (float)($data['scholarship_amount'] ?? 0),
            'initial_payment'          => (float)($data['initial_payment'] ?? 0),
            'payment_due_date'         => $data['payment_due_date'] ?: null,
            // Status
            'status'                   => 'pending',
            'payment_status'           => 'pending',
            // Misc
            'remarks'                  => sanitize($data['remarks'] ?? ''),
            'created_by'               => $this->user['id'],
        ];

        // Recalculate final_fee
        $insertData['final_fee']    = max(0, $insertData['total_fee'] - $insertData['discount_amount'] - $insertData['scholarship_amount']);
        $insertData['balance_amount'] = $insertData['final_fee'];

        $id = (int)$this->admission->create($insertData);

        // Init document checklist
        $this->admission->initDocumentChecklist($id, $this->institutionId);

        // Timeline: created
        $this->admission->addTimeline($id, 'created', 'Application submitted', null, null, 'pending', $this->user['id']);

        $this->logAudit('admission_created', 'admission', $id);

        if ($duplicate) {
            $this->redirectWith(url('admissions/' . $id), 'warning', 'Admission created. Note: A similar application already exists (' . e($duplicate['admission_number']) . ').');
        } else {
            $this->redirectWith(url('admissions/' . $id), 'success', 'Admission application created successfully.');
        }
    }

    // ================================================================
    // SHOW
    // ================================================================

    public function show(int $id): void
    {
        $this->authorize('admissions.view');

        $admission = $this->admission->findWithDetails($id);
        if (!$admission) {
            $this->redirectWith(url('admissions'), 'error', 'Admission not found.');
            return;
        }

        $canConfirm = $this->admission->canConfirm($id);

        $this->db->query("SELECT id, name FROM batches WHERE course_id = ? AND deleted_at IS NULL ORDER BY start_date DESC", [$admission['course_id']]);
        $batches = $this->db->fetchAll();

        $this->view('admissions/show', compact('admission', 'batches', 'canConfirm'));
    }

    // ================================================================
    // EDIT / UPDATE
    // ================================================================

    public function edit(int $id): void
    {
        $this->authorize('admissions.edit');

        $admission = $this->admission->find($id);
        if (!$admission) {
            $this->redirectWith(url('admissions'), 'error', 'Admission not found.');
            return;
        }

        $db = $this->db;
        $db->query("SELECT id, name, code FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $db->fetchAll();
        $db->query("SELECT id, name FROM batches WHERE course_id = ? AND deleted_at IS NULL ORDER BY start_date DESC", [$admission['course_id']]);
        $batches = $db->fetchAll();
        $db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $db->fetchAll();
        $db->query(
            "SELECT DISTINCT u.id, u.first_name, u.last_name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $counselors = $db->fetchAll();

        $this->view('admissions/edit', compact('admission', 'courses', 'departments', 'batches', 'academicYears', 'counselors'));
    }

    public function update(int $id): void
    {
        $this->authorize('admissions.edit');

        $admission = $this->admission->find($id);
        if (!$admission) {
            $this->redirectWith(url('admissions'), 'error', 'Admission not found.');
            return;
        }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'first_name' => 'required',
            'phone'      => 'required|phone',
            'course_id'  => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $updateData = [
            // Personal
            'first_name'               => sanitize($data['first_name']),
            'last_name'                => sanitize($data['last_name'] ?? ''),
            'email'                    => sanitize($data['email'] ?? ''),
            'phone'                    => sanitize($data['phone']),
            'date_of_birth'            => $data['date_of_birth'] ?: null,
            'gender'                   => $data['gender'] ?? null,
            'category'                 => $data['category'] ?? null,
            'nationality'              => sanitize($data['nationality'] ?? 'Indian'),
            // Address
            'address_line1'            => sanitize($data['address_line1'] ?? ''),
            'address_line2'            => sanitize($data['address_line2'] ?? ''),
            'city'                     => sanitize($data['city'] ?? ''),
            'state'                    => sanitize($data['state'] ?? ''),
            'pincode'                  => sanitize($data['pincode'] ?? ''),
            // Parents
            'father_name'              => sanitize($data['father_name'] ?? ''),
            'father_phone'             => sanitize($data['father_phone'] ?? ''),
            'mother_name'              => sanitize($data['mother_name'] ?? ''),
            'guardian_name'            => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'           => sanitize($data['guardian_phone'] ?? ''),
            // Previous education
            'previous_qualification'   => sanitize($data['previous_qualification'] ?? ''),
            'previous_percentage'      => $data['previous_percentage'] ?: null,
            'previous_institution'     => sanitize($data['previous_institution'] ?? ''),
            'previous_year_of_passing' => $data['previous_year_of_passing'] ?: null,
            // Academic
            'department_id'            => $data['department_id'] ?: null,
            'course_id'                => (int)$data['course_id'],
            'batch_id'                 => $data['batch_id'] ?: null,
            'academic_year_id'         => $data['academic_year_id'] ?: null,
            'section_id'               => $data['section_id'] ?: null,
            'specialization'           => sanitize($data['specialization'] ?? ''),
            'current_semester'         => max(1, (int)($data['current_semester'] ?? 1)),
            'admission_type'           => $data['admission_type'] ?? $admission['admission_type'],
            'quota'                    => $data['quota'] ?? $admission['quota'],
            'application_source'       => sanitize($data['application_source'] ?? ''),
            'counselor_id'             => $data['counselor_id'] ?: null,
            // Fees
            'fee_structure_id'         => $data['fee_structure_id'] ?: null,
            'total_fee'                => (float)($data['total_fee'] ?? 0),
            'discount_amount'          => (float)($data['discount_amount'] ?? 0),
            'scholarship_amount'       => (float)($data['scholarship_amount'] ?? 0),
            'initial_payment'          => (float)($data['initial_payment'] ?? 0),
            'payment_due_date'         => $data['payment_due_date'] ?: null,
            'remarks'                  => sanitize($data['remarks'] ?? ''),
        ];

        // Recalculate fees
        $updateData['final_fee']    = max(0, $updateData['total_fee'] - $updateData['discount_amount'] - $updateData['scholarship_amount']);
        $paid                       = (float)($admission['paid_amount'] ?? 0);
        $updateData['balance_amount'] = max(0, $updateData['final_fee'] - $paid);

        $this->admission->update($id, $updateData);

        $this->admission->addTimeline($id, 'note_added', 'Application details updated', null, null, null, $this->user['id']);
        $this->logAudit('admission_updated', 'admission', $id);
        $this->redirectWith(url('admissions/' . $id), 'success', 'Admission updated successfully.');
    }

    // ================================================================
    // WORKFLOW ACTIONS
    // ================================================================

    public function approve(int $id): void
    {
        $this->authorize('admissions.approve');

        $result = $this->admission->approve($id, $this->user['id']);
        $msg    = $result ? 'Admission confirmed successfully.' : 'Cannot confirm this admission.';
        $type   = $result ? 'success' : 'error';

        if ($result) {
            $this->logAudit('admission_approved', 'admission', $id);
        }

        $this->redirectWith(url('admissions/' . $id), $type, $msg);
    }

    public function reject(int $id): void
    {
        $this->authorize('admissions.approve');

        $reason = sanitize($this->postData()['reason'] ?? '');
        $result = $this->admission->reject($id, $this->user['id'], $reason);
        $msg    = $result ? 'Admission rejected.' : 'Cannot reject this admission.';
        $type   = $result ? 'success' : 'error';

        if ($result) {
            $this->logAudit('admission_rejected', 'admission', $id, ['reason' => $reason]);
        }

        $this->redirectWith(url('admissions/' . $id), $type, $msg);
    }

    public function cancel(int $id): void
    {
        $this->authorize('admissions.approve');

        $reason = sanitize($this->postData()['reason'] ?? '');
        $result = $this->admission->cancel($id, $this->user['id'], $reason);
        $msg    = $result ? 'Admission cancelled.' : 'Cannot cancel this admission.';
        $type   = $result ? 'success' : 'error';

        if ($result) {
            $this->logAudit('admission_cancelled', 'admission', $id, ['reason' => $reason]);
        }

        $this->redirectWith(url('admissions/' . $id), $type, $msg);
    }

    public function reopen(int $id): void
    {
        $this->authorize('admissions.approve');

        $result = $this->admission->reopen($id, $this->user['id']);
        $msg    = $result ? 'Admission reopened.' : 'Cannot reopen this admission.';
        $type   = $result ? 'success' : 'error';

        if ($result) {
            $this->logAudit('admission_reopened', 'admission', $id);
        }

        $this->redirectWith(url('admissions/' . $id), $type, $msg);
    }

    public function markDocumentPending(int $id): void
    {
        $this->authorize('admissions.approve');

        $this->admission->markDocumentPending($id, $this->user['id']);
        $this->redirectWith(url('admissions/' . $id), 'success', 'Status updated to Document Pending.');
    }

    public function markPaymentPending(int $id): void
    {
        $this->authorize('admissions.approve');

        $this->admission->markPaymentPending($id, $this->user['id']);
        $this->redirectWith(url('admissions/' . $id), 'success', 'Status updated to Payment Pending.');
    }

    public function enroll(int $id): void
    {
        $this->authorize('admissions.enroll');

        $studentId = $this->admission->enroll($id);
        if ($studentId) {
            $admissionData = $this->admission->findWithDetails($id);
            if ($admissionData) {
                $this->assignInitialFees($studentId, $admissionData);
            }
            $this->logAudit('admission_enrolled', 'admission', $id, ['student_id' => $studentId]);
            $this->redirectWith(url('students/' . $studentId), 'success', 'Student enrolled successfully.');
        } else {
            $this->redirectWith(url('admissions/' . $id), 'error', 'Cannot enroll. Admission must be confirmed first.');
        }
    }

    // ================================================================
    // DOCUMENT MANAGEMENT
    // ================================================================

    public function storeDocument(int $id): void
    {
        $this->authorize('admissions.edit');

        $admission = $this->admission->find($id);
        if (!$admission) {
            $this->redirectWith(url('admissions'), 'error', 'Admission not found.');
            return;
        }

        $docType = $this->postData()['document_type'] ?? '';
        if (!$docType) {
            $this->redirectWith(url('admissions/' . $id), 'error', 'Document type is required.');
            return;
        }

        // Handle file upload
        if (empty($_FILES['document_file']['name'])) {
            $this->redirectWith(url('admissions/' . $id), 'error', 'Please select a file to upload.');
            return;
        }

        $file         = $_FILES['document_file'];
        $uploadDir    = 'uploads/admissions/' . $id . '/';
        $fullUploadDir = BASE_PATH . '/public/' . $uploadDir;

        if (!is_dir($fullUploadDir)) {
            mkdir($fullUploadDir, 0755, true);
        }

        $ext          = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName     = $docType . '_' . time() . '.' . $ext;
        $destPath     = $fullUploadDir . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->redirectWith(url('admissions/' . $id), 'error', 'File upload failed.');
            return;
        }

        $this->admission->submitDocument($id, $docType, [
            'original_filename' => $file['name'],
            'file_path'         => $uploadDir . $safeName,
            'file_size'         => $file['size'],
            'file_type'         => $file['type'],
        ], $this->user['id']);

        $this->redirectWith(url('admissions/' . $id), 'success', 'Document uploaded successfully.');
    }

    public function verifyDocument(int $id): void
    {
        $this->authorize('admissions.approve');

        $data   = $this->postData();
        $docId  = (int)($data['document_id'] ?? 0);
        $status = $data['status'] ?? 'verified';
        $notes  = sanitize($data['notes'] ?? '');

        if (!in_array($status, ['verified', 'rejected'], true)) {
            $this->redirectWith(url('admissions/' . $id), 'error', 'Invalid status.');
            return;
        }

        $this->admission->verifyDocument($docId, $status, $notes, $this->user['id']);
        $this->logAudit('document_' . $status, 'admission_document', $docId);
        $this->redirectWith(url('admissions/' . $id), 'success', 'Document ' . $status . '.');
    }

    // ================================================================
    // PAYMENT MANAGEMENT
    // ================================================================

    public function storePayment(int $id): void
    {
        $this->authorize('admissions.edit');

        $admission = $this->admission->find($id);
        if (!$admission) {
            $this->redirectWith(url('admissions'), 'error', 'Admission not found.');
            return;
        }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'amount'       => 'required',
            'payment_mode' => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        try {
            $paymentId = $this->admission->recordPayment($id, [
                'payment_date'          => $data['payment_date'] ?? date('Y-m-d'),
                'amount'                => (float)$data['amount'],
                'payment_mode'          => $data['payment_mode'],
                'transaction_reference' => sanitize($data['transaction_reference'] ?? ''),
                'receipt_number'        => sanitize($data['receipt_number'] ?? ''),
                'fee_head'              => sanitize($data['fee_head'] ?? ''),
                'academic_year_id'      => $data['academic_year_id'] ?: null,
                'remarks'               => sanitize($data['remarks'] ?? ''),
            ], $this->user['id']);

            $this->logAudit('payment_recorded', 'admission', $id, ['payment_id' => $paymentId]);
            $this->redirectWith(url('admissions/' . $id), 'success', 'Payment recorded successfully.');
        } catch (\Exception $e) {
            $this->redirectWith(url('admissions/' . $id), 'error', 'Payment recording failed.');
        }
    }

    // ================================================================
    // NOTES
    // ================================================================

    public function addNote(int $id): void
    {
        $this->authorize('admissions.view');

        $note = sanitize($this->postData()['note'] ?? '');
        if (!$note) {
            $this->redirectWith(url('admissions/' . $id), 'error', 'Note cannot be empty.');
            return;
        }

        $this->admission->addTimeline($id, 'note_added', 'Note added', $note, null, null, $this->user['id']);
        $this->redirectWith(url('admissions/' . $id), 'success', 'Note added.');
    }

    // ================================================================
    // AJAX — CASCADING DROPDOWNS
    // ================================================================

    public function ajaxDepartments(): void
    {
        header('Content-Type: application/json');
        $institutionId = (int)$this->input('institution_id') ?: $this->institutionId;

        $this->db->query(
            "SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name",
            [$institutionId]
        );
        echo json_encode($this->db->fetchAll());
        exit;
    }

    public function ajaxCourses(): void
    {
        header('Content-Type: application/json');
        $institutionId = (int)$this->input('institution_id') ?: $this->institutionId;
        $departmentId  = (int)$this->input('department_id');

        $sql    = "SELECT id, name, code FROM courses WHERE institution_id = ? AND deleted_at IS NULL AND status = 'active'";
        $params = [$institutionId];

        if ($departmentId) {
            $sql    .= " AND department_id = ?";
            $params[] = $departmentId;
        }

        $sql .= " ORDER BY name";
        $this->db->query($sql, $params);
        echo json_encode($this->db->fetchAll());
        exit;
    }

    public function ajaxBatches(): void
    {
        header('Content-Type: application/json');
        $courseId = (int)$this->input('course_id');

        if (!$courseId) {
            echo json_encode([]);
            exit;
        }

        $this->db->query(
            "SELECT id, name, start_date, end_date FROM batches WHERE course_id = ? AND deleted_at IS NULL ORDER BY start_date DESC",
            [$courseId]
        );
        echo json_encode($this->db->fetchAll());
        exit;
    }

    // ================================================================
    // AJAX — FROM LEAD
    // ================================================================

    public function fromLead(): void
    {
        header('Content-Type: application/json');
        $leadId = (int)$this->input('lead_id');

        if (!$leadId) {
            echo json_encode(['error' => 'lead_id required']);
            exit;
        }

        $prefill = $this->admission->createFromLead($leadId);
        echo json_encode($prefill ?? []);
        exit;
    }

    // ================================================================
    // AJAX — DUPLICATE CHECK
    // ================================================================

    public function checkDuplicate(): void
    {
        header('Content-Type: application/json');
        $phone      = sanitize($this->input('phone') ?? '');
        $email      = sanitize($this->input('email') ?? '');
        $excludeId  = (int)$this->input('exclude_id');

        if (!$phone && !$email) {
            echo json_encode(['duplicate' => false]);
            exit;
        }

        $dup = $this->admission->checkDuplicate($phone, $email, $this->institutionId, $excludeId);

        echo json_encode([
            'duplicate'        => (bool)$dup,
            'admission_number' => $dup['admission_number'] ?? null,
            'name'             => $dup ? trim(($dup['first_name'] ?? '') . ' ' . ($dup['last_name'] ?? '')) : null,
            'id'               => $dup['id'] ?? null,
        ]);
        exit;
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    private function assignInitialFees(int $studentId, array $admission): void
    {
        $this->db->query(
            "SELECT id FROM fee_structures
             WHERE course_id = ? AND academic_year_id = ? AND status = 'active'
             LIMIT 1",
            [$admission['course_id'], $admission['academic_year_id']]
        );
        $structure = $this->db->fetch();

        if ($structure) {
            $feeModel = new \App\Models\Fee();
            $feeModel->assignStructure($studentId, $structure['id']);
        }
    }
}
