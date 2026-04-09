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

        try {
            $db->query(
                "SELECT ts.id, ts.name AS stop_name, tr.name AS route_name
                 FROM transport_stops ts
                 INNER JOIN transport_routes tr ON tr.id = ts.route_id AND tr.institution_id = ? AND tr.status = 'active'
                 ORDER BY tr.name, ts.sort_order, ts.name",
                [$this->institutionId]
            );
            $busStops = $db->fetchAll();
        } catch (\Exception $e) {
            $busStops = [];
        }

        $this->view('admissions/create', compact('prefill', 'courses', 'departments', 'academicYears', 'counselors', 'leadId', 'busStops'));
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
            'whatsapp_number'          => sanitize($data['whatsapp_number'] ?? ''),
            'country_code'             => sanitize($data['country_code'] ?? '+91'),
            'aadhaar_number'           => sanitize($data['aadhaar_number'] ?? ''),
            'date_of_birth'            => $data['date_of_birth'] ?: null,
            'gender'                   => $data['gender'] ?? null,
            'category'                 => $data['category'] ?? null,
            'programme_level'          => $data['programme_level'] ?? null,
            'domain'                   => sanitize($data['domain'] ?? ''),
            'nationality'              => sanitize($data['nationality'] ?? 'Indian'),
            'hostel_required'          => !empty($data['hostel_required']) && $data['hostel_required'] !== 'No' ? 1 : 0,
            'transport_required'       => !empty($data['transport_required']) && $data['transport_required'] !== 'No' ? 1 : 0,
            'nearest_bus_stop'         => sanitize($data['nearest_bus_stop'] ?? ''),
            // Address
            'address_line1'            => sanitize($data['address_line1'] ?? ''),
            'address_line2'            => sanitize($data['address_line2'] ?? ''),
            'city'                     => sanitize($data['city'] ?? ''),
            'state'                    => sanitize($data['state'] ?? ''),
            'pincode'                  => sanitize($data['pincode'] ?? ''),
            'country'                  => sanitize($data['country'] ?? 'India'),
            'permanent_same_as_comm'   => !empty($data['permanent_same_as_comm']) ? 1 : 0,
            'permanent_address_line1'  => sanitize($data['permanent_address_line1'] ?? ''),
            'permanent_address_line2'  => sanitize($data['permanent_address_line2'] ?? ''),
            'permanent_city'           => sanitize($data['permanent_city'] ?? ''),
            'permanent_state'          => sanitize($data['permanent_state'] ?? ''),
            'permanent_pincode'        => sanitize($data['permanent_pincode'] ?? ''),
            'permanent_country'        => sanitize($data['permanent_country'] ?? 'India'),
            // Parents
            'father_name'              => sanitize($data['father_name'] ?? ''),
            'father_phone'             => sanitize($data['father_phone'] ?? ''),
            'father_occupation'        => sanitize($data['father_occupation'] ?? ''),
            'father_annual_income'     => sanitize($data['father_annual_income'] ?? ''),
            'mother_name'              => sanitize($data['mother_name'] ?? ''),
            'mother_phone'             => sanitize($data['mother_phone'] ?? ''),
            'mother_occupation'        => sanitize($data['mother_occupation'] ?? ''),
            'mother_annual_income'     => sanitize($data['mother_annual_income'] ?? ''),
            'guardian_name'            => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'           => sanitize($data['guardian_phone'] ?? ''),
            // Personal details
            'place_of_birth'           => sanitize($data['place_of_birth'] ?? ''),
            'blood_group'              => sanitize($data['blood_group'] ?? ''),
            'mother_tongue'            => sanitize($data['mother_tongue'] ?? ''),
            'religion'                 => sanitize($data['religion'] ?? ''),
            'community'                => sanitize($data['community'] ?? ''),
            'sibling_in_college'       => sanitize($data['sibling_in_college'] ?? ''),
            // Course preferences
            'course_preference_1'      => $data['course_preference_1'] ?: null,
            'course_preference_2'      => $data['course_preference_2'] ?: null,
            'course_preference_3'      => $data['course_preference_3'] ?: null,
            // SSLC / 10th
            'sslc_school_name'         => sanitize($data['sslc_school_name'] ?? ''),
            'sslc_state'               => sanitize($data['sslc_state'] ?? ''),
            'sslc_city'                => sanitize($data['sslc_city'] ?? ''),
            'sslc_board'               => sanitize($data['sslc_board'] ?? ''),
            'sslc_medium'              => sanitize($data['sslc_medium'] ?? ''),
            'sslc_year_of_passing'     => sanitize($data['sslc_year_of_passing'] ?? ''),
            'sslc_max_marks'           => $data['sslc_max_marks'] ?: null,
            'sslc_marks_obtained'      => $data['sslc_marks_obtained'] ?: null,
            'sslc_percentage'          => $data['sslc_marks_obtained'] && $data['sslc_max_marks']
                                            ? round((float)$data['sslc_marks_obtained'] / (float)$data['sslc_max_marks'] * 100, 2)
                                            : ($data['sslc_percentage'] ?: null),
            // HSC / 12th
            'hsc_school_name'          => sanitize($data['hsc_school_name'] ?? ''),
            'hsc_state'                => sanitize($data['hsc_state'] ?? ''),
            'hsc_district'             => sanitize($data['hsc_district'] ?? ''),
            'hsc_board'                => sanitize($data['hsc_board'] ?? ''),
            'hsc_medium'               => sanitize($data['hsc_medium'] ?? ''),
            'hsc_group'                => sanitize($data['hsc_group'] ?? ''),
            'hsc_result_status'        => sanitize($data['hsc_result_status'] ?? ''),
            'hsc_registration_no'      => sanitize($data['hsc_registration_no'] ?? ''),
            'hsc_max_marks'            => $data['hsc_max_marks'] ?: null,
            'hsc_marks_obtained'       => $data['hsc_marks_obtained'] ?: null,
            'hsc_percentage'           => $data['hsc_marks_obtained'] && $data['hsc_max_marks']
                                            ? round((float)$data['hsc_marks_obtained'] / (float)$data['hsc_max_marks'] * 100, 2)
                                            : ($data['hsc_percentage'] ?: null),
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

        try {
            $db->query(
                "SELECT ts.id, ts.name AS stop_name, tr.name AS route_name
                 FROM transport_stops ts
                 INNER JOIN transport_routes tr ON tr.id = ts.route_id AND tr.institution_id = ? AND tr.status = 'active'
                 ORDER BY tr.name, ts.sort_order, ts.name",
                [$this->institutionId]
            );
            $busStops = $db->fetchAll();
        } catch (\Exception $e) {
            $busStops = [];
        }

        $this->view('admissions/edit', compact('admission', 'courses', 'departments', 'batches', 'academicYears', 'counselors', 'busStops'));
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
            'whatsapp_number'          => sanitize($data['whatsapp_number'] ?? ''),
            'country_code'             => sanitize($data['country_code'] ?? '+91'),
            'aadhaar_number'           => sanitize($data['aadhaar_number'] ?? ''),
            'date_of_birth'            => $data['date_of_birth'] ?: null,
            'gender'                   => $data['gender'] ?? null,
            'category'                 => $data['category'] ?? null,
            'programme_level'          => $data['programme_level'] ?? null,
            'domain'                   => sanitize($data['domain'] ?? ''),
            'nationality'              => sanitize($data['nationality'] ?? 'Indian'),
            'hostel_required'          => !empty($data['hostel_required']) && $data['hostel_required'] !== 'No' ? 1 : 0,
            'transport_required'       => !empty($data['transport_required']) && $data['transport_required'] !== 'No' ? 1 : 0,
            'nearest_bus_stop'         => sanitize($data['nearest_bus_stop'] ?? ''),
            // Address
            'address_line1'            => sanitize($data['address_line1'] ?? ''),
            'address_line2'            => sanitize($data['address_line2'] ?? ''),
            'city'                     => sanitize($data['city'] ?? ''),
            'state'                    => sanitize($data['state'] ?? ''),
            'pincode'                  => sanitize($data['pincode'] ?? ''),
            'country'                  => sanitize($data['country'] ?? 'India'),
            'permanent_same_as_comm'   => !empty($data['permanent_same_as_comm']) ? 1 : 0,
            'permanent_address_line1'  => sanitize($data['permanent_address_line1'] ?? ''),
            'permanent_address_line2'  => sanitize($data['permanent_address_line2'] ?? ''),
            'permanent_city'           => sanitize($data['permanent_city'] ?? ''),
            'permanent_state'          => sanitize($data['permanent_state'] ?? ''),
            'permanent_pincode'        => sanitize($data['permanent_pincode'] ?? ''),
            'permanent_country'        => sanitize($data['permanent_country'] ?? 'India'),
            // Parents
            'father_name'              => sanitize($data['father_name'] ?? ''),
            'father_phone'             => sanitize($data['father_phone'] ?? ''),
            'father_occupation'        => sanitize($data['father_occupation'] ?? ''),
            'father_annual_income'     => sanitize($data['father_annual_income'] ?? ''),
            'mother_name'              => sanitize($data['mother_name'] ?? ''),
            'mother_phone'             => sanitize($data['mother_phone'] ?? ''),
            'mother_occupation'        => sanitize($data['mother_occupation'] ?? ''),
            'mother_annual_income'     => sanitize($data['mother_annual_income'] ?? ''),
            'guardian_name'            => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'           => sanitize($data['guardian_phone'] ?? ''),
            // Personal details
            'place_of_birth'           => sanitize($data['place_of_birth'] ?? ''),
            'blood_group'              => sanitize($data['blood_group'] ?? ''),
            'mother_tongue'            => sanitize($data['mother_tongue'] ?? ''),
            'religion'                 => sanitize($data['religion'] ?? ''),
            'community'                => sanitize($data['community'] ?? ''),
            'sibling_in_college'       => sanitize($data['sibling_in_college'] ?? ''),
            // Course preferences
            'course_preference_1'      => $data['course_preference_1'] ?: null,
            'course_preference_2'      => $data['course_preference_2'] ?: null,
            'course_preference_3'      => $data['course_preference_3'] ?: null,
            // SSLC / 10th
            'sslc_school_name'         => sanitize($data['sslc_school_name'] ?? ''),
            'sslc_state'               => sanitize($data['sslc_state'] ?? ''),
            'sslc_city'                => sanitize($data['sslc_city'] ?? ''),
            'sslc_board'               => sanitize($data['sslc_board'] ?? ''),
            'sslc_medium'              => sanitize($data['sslc_medium'] ?? ''),
            'sslc_year_of_passing'     => sanitize($data['sslc_year_of_passing'] ?? ''),
            'sslc_max_marks'           => $data['sslc_max_marks'] ?: null,
            'sslc_marks_obtained'      => $data['sslc_marks_obtained'] ?: null,
            'sslc_percentage'          => $data['sslc_marks_obtained'] && $data['sslc_max_marks']
                                            ? round((float)$data['sslc_marks_obtained'] / (float)$data['sslc_max_marks'] * 100, 2)
                                            : ($data['sslc_percentage'] ?: null),
            // HSC / 12th
            'hsc_school_name'          => sanitize($data['hsc_school_name'] ?? ''),
            'hsc_state'                => sanitize($data['hsc_state'] ?? ''),
            'hsc_district'             => sanitize($data['hsc_district'] ?? ''),
            'hsc_board'                => sanitize($data['hsc_board'] ?? ''),
            'hsc_medium'               => sanitize($data['hsc_medium'] ?? ''),
            'hsc_group'                => sanitize($data['hsc_group'] ?? ''),
            'hsc_result_status'        => sanitize($data['hsc_result_status'] ?? ''),
            'hsc_registration_no'      => sanitize($data['hsc_registration_no'] ?? ''),
            'hsc_max_marks'            => $data['hsc_max_marks'] ?: null,
            'hsc_marks_obtained'       => $data['hsc_marks_obtained'] ?: null,
            'hsc_percentage'           => $data['hsc_marks_obtained'] && $data['hsc_max_marks']
                                            ? round((float)$data['hsc_marks_obtained'] / (float)$data['hsc_max_marks'] * 100, 2)
                                            : ($data['hsc_percentage'] ?: null),
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
                $this->_provisionLmsUser($studentId, $admissionData);
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
    // EXPORT CSV
    // ================================================================

    public function export(): void
    {
        $this->authorize('admissions.view');

        $filters = [
            'search'           => $this->input('search'),
            'status'           => $this->input('status'),
            'course_id'        => $this->input('course_id'),
            'department_id'    => $this->input('department_id'),
            'counselor_id'     => $this->input('counselor_id'),
            'payment_status'   => $this->input('payment_status'),
            'date_from'        => $this->input('date_from'),
            'date_to'          => $this->input('date_to'),
        ];

        // Support exporting specific IDs from bulk-export
        $idsParam = $this->input('ids');
        if ($idsParam) {
            $ids  = array_filter(array_map('intval', explode(',', $idsParam)));
            $rows = [];
            if (!empty($ids)) {
                $ph = implode(',', array_fill(0, count($ids), '?'));
                $this->db->query(
                    "SELECT a.*, c.name AS course_name, d.name AS department_name,
                            ay.name AS academic_year_name,
                            CONCAT(counsel.first_name,' ',counsel.last_name) AS counselor_name
                     FROM admissions a
                     LEFT JOIN courses c ON c.id = a.course_id
                     LEFT JOIN departments d ON d.id = a.department_id
                     LEFT JOIN academic_years ay ON ay.id = a.academic_year_id
                     LEFT JOIN users counsel ON counsel.id = a.counselor_id
                     WHERE a.id IN ($ph) AND a.institution_id = ?",
                    array_merge($ids, [$this->institutionId])
                );
                $rows = $this->db->fetchAll();
            }
        } else {
            $all = $this->admission->getListPaginated(1, 5000, $filters);
            $rows = $all['data'] ?? [];
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="admissions_' . date('Ymd_His') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Admission #','First Name','Last Name','Phone','Email','Course','Department','Academic Year','Status','Payment Status','Counselor','Applied Date']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['admission_number'],
                $r['first_name'],
                $r['last_name'] ?? '',
                $r['phone'],
                $r['email'] ?? '',
                $r['course_name'] ?? '',
                $r['department_name'] ?? '',
                $r['academic_year_name'] ?? '',
                $r['status'],
                $r['payment_status'] ?? '',
                $r['counselor_name'] ?? '',
                $r['application_date'] ?? $r['created_at'],
            ]);
        }
        fclose($out);
        exit;
    }

    // ================================================================
    // BULK ACTIONS
    // ================================================================

    public function bulkAction(): void
    {
        $this->authorize('admissions.approve');
        header('Content-Type: application/json');

        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids    = array_map('intval', $input['ids'] ?? []);
        $action = $input['action'] ?? '';

        if (empty($ids)) {
            echo json_encode(['status' => 'error', 'message' => 'No records selected']); exit;
        }

        $validStatuses = ['pending','document_pending','payment_pending','confirmed','cancelled','rejected'];

        if ($action === 'delete') {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $this->db->query(
                "DELETE FROM admissions WHERE id IN ($placeholders) AND institution_id = ?",
                array_merge($ids, [$this->institutionId])
            );
            echo json_encode(['status' => 'success', 'message' => count($ids) . ' application(s) deleted.']); exit;
        }

        if (in_array($action, $validStatuses, true)) {
            foreach ($ids as $id) {
                $this->db->query(
                    "SELECT id, status FROM admissions WHERE id = ? AND institution_id = ?",
                    [$id, $this->institutionId]
                );
                $adm = $this->db->fetch();
                if ($adm && $adm['status'] !== 'enrolled') {
                    $this->db->query(
                        "UPDATE admissions SET status = ?, updated_at = NOW() WHERE id = ?",
                        [$action, $id]
                    );
                }
            }
            $label = ucfirst(str_replace('_', ' ', $action));
            echo json_encode(['status' => 'success', 'message' => count($ids) . " application(s) marked as {$label}."]); exit;
        }

        echo json_encode(['status' => 'error', 'message' => 'Unknown action']); exit;
    }

    // ================================================================
    // QUICK STATUS  (AJAX inline change)
    // ================================================================

    public function quickStatus(int $id): void
    {
        $this->authorize('admissions.approve');
        header('Content-Type: application/json');

        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = $input['status'] ?? ($_POST['status'] ?? '');

        $allowed = ['pending','document_pending','payment_pending','confirmed','cancelled','rejected'];
        if (!in_array($status, $allowed, true)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status']); exit;
        }

        $this->db->query(
            "SELECT id FROM admissions WHERE id = ? AND institution_id = ? AND status != 'enrolled'",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Not found or already enrolled']); exit;
        }

        $this->db->query(
            "UPDATE admissions SET status = ?, updated_at = NOW() WHERE id = ?",
            [$status, $id]
        );
        $this->admission->addTimeline($id, 'note_added', 'Status changed to ' . ucfirst(str_replace('_', ' ', $status)), null, null, $status, $this->user['id'] ?? 1);

        $labels = \App\Models\Admission::STATUS_LABELS;
        [$label, $badgeClass] = $labels[$status] ?? [ucfirst($status), 'bg-secondary'];
        echo json_encode(['status' => 'success', 'label' => $label, 'badge_class' => $badgeClass]); exit;
    }

    // ================================================================
    // OFFER LETTER & ADMISSION LETTER (printable)
    // ================================================================

    public function offerLetter(int $id): void
    {
        $this->authorize('admissions.view');
        $admission = $this->admission->findWithDetails($id);
        if (!$admission) {
            $this->redirectWith(url('admissions'), 'error', 'Admission not found.');
            return;
        }
        // Mark offer letter as sent
        $this->db->query(
            "UPDATE admissions SET offer_letter_sent_at = COALESCE(offer_letter_sent_at, NOW()) WHERE id = ?",
            [$id]
        );
        $this->db->query("SELECT * FROM institutions WHERE id = ?", [$this->institutionId]);
        $institution = $this->db->fetch();
        $this->view('admissions/offer-letter', compact('admission', 'institution'), 'blank');
    }

    public function admissionLetter(int $id): void
    {
        $this->authorize('admissions.view');
        $admission = $this->admission->findWithDetails($id);
        if (!$admission || !in_array($admission['status'], ['confirmed', 'enrolled'])) {
            $this->redirectWith(url('admissions'), 'error', 'Admission must be confirmed first.');
            return;
        }
        $this->db->query(
            "UPDATE admissions SET admission_letter_sent_at = COALESCE(admission_letter_sent_at, NOW()) WHERE id = ?",
            [$id]
        );
        $this->db->query("SELECT * FROM institutions WHERE id = ?", [$this->institutionId]);
        $institution = $this->db->fetch();
        $this->view('admissions/admission-letter', compact('admission', 'institution'), 'blank');
    }

    // ================================================================
    // INTERVIEW SCHEDULING
    // ================================================================

    public function scheduleInterview(int $id): void
    {
        $this->authorize('admissions.approve');

        $admission = $this->admission->find($id);
        if (!$admission) {
            $this->json(['status' => 'error', 'message' => 'Not found.'], 404);
            return;
        }

        $date  = $this->input('interview_date', '');
        $time  = $this->input('interview_time', '');
        $mode  = $this->input('interview_mode', 'in_person');
        $venue = trim($this->input('interview_venue', ''));
        $panel = trim($this->input('interview_panel', ''));
        $notes = trim($this->input('interview_notes', ''));

        if (!$date) {
            $this->redirectWith(url('admissions/' . $id), 'error', 'Interview date is required.');
            return;
        }

        $this->db->query(
            "UPDATE admissions SET
                interview_date   = ?,
                interview_time   = ?,
                interview_mode   = ?,
                interview_venue  = ?,
                interview_panel  = ?,
                interview_notes  = ?,
                interview_result = 'pending',
                updated_at       = NOW()
             WHERE id = ? AND institution_id = ?",
            [$date, $time ?: null, $mode, $venue ?: null, $panel ?: null, $notes ?: null, $id, $this->institutionId]
        );

        $detail = "Interview scheduled for " . date('d M Y', strtotime($date)) . ($time ? ' at ' . date('H:i', strtotime($time)) : '') . ($venue ? ' — ' . $venue : '');
        $this->admission->addTimeline($id, 'note_added', $detail, null, null, null, $this->user['id']);
        $this->logAudit('interview_scheduled', 'admission', $id);
        $this->redirectWith(url('admissions/' . $id . '#tab-interview'), 'success', 'Interview scheduled successfully.');
    }

    public function recordInterviewResult(int $id): void
    {
        $this->authorize('admissions.approve');

        $result = $this->input('interview_result', 'pending');
        $score  = $this->input('interview_score', null);
        $notes  = trim($this->input('interview_notes', ''));

        if (!in_array($result, ['pending', 'passed', 'failed', 'on_hold'])) {
            $this->redirectWith(url('admissions/' . $id), 'error', 'Invalid result.');
            return;
        }

        $this->db->query(
            "UPDATE admissions SET interview_result = ?, interview_score = ?, interview_notes = ?, updated_at = NOW()
             WHERE id = ? AND institution_id = ?",
            [$result, $score ?: null, $notes ?: null, $id, $this->institutionId]
        );

        $this->admission->addTimeline($id, 'note_added', 'Interview result recorded: ' . ucfirst($result), null, null, null, $this->user['id']);
        $this->logAudit('interview_result_recorded', 'admission', $id);
        $this->redirectWith(url('admissions/' . $id . '#tab-interview'), 'success', 'Interview result saved.');
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

            // Also create enterprise fee_student_assignments per fee head
            $this->_assignEnterpriseFees($studentId, (int)$structure['id'], $admission);
        }
    }

    /**
     * Create fee_student_assignments from fee_structure_details (enterprise fee schema).
     */
    private function _assignEnterpriseFees(int $studentId, int $structureId, array $admission): void
    {
        try {
            $this->db->query(
                "SELECT fsd.*, fh.head_name, fh.head_code, fh.fee_type
                 FROM fee_structure_details fsd
                 JOIN fee_heads fh ON fh.id = fsd.fee_head_id
                 WHERE fsd.structure_id = ? ORDER BY fsd.sort_order",
                [$structureId]
            );
            $details = $this->db->fetchAll();
            if (empty($details)) return;

            $instId = (int)$admission['institution_id'];
            $ayId   = $admission['academic_year_id'] ?? null;

            foreach ($details as $d) {
                // Skip if already assigned
                $this->db->query(
                    "SELECT id FROM fee_student_assignments
                     WHERE student_id = ? AND structure_id = ? AND fee_head_id = ?
                     LIMIT 1",
                    [$studentId, $structureId, $d['fee_head_id']]
                );
                if ($this->db->fetch()) continue;

                $amount = (float)$d['amount'];
                $this->db->insert('fee_student_assignments', [
                    'institution_id'    => $instId,
                    'student_id'        => $studentId,
                    'academic_year_id'  => $ayId,
                    'structure_id'      => $structureId,
                    'fee_head_id'       => (int)$d['fee_head_id'],
                    'gross_amount'      => $amount,
                    'concession_amount' => 0,
                    'net_amount'        => $amount,
                    'paid_amount'       => 0,
                    'fine_amount'       => 0,
                    'balance_amount'    => $amount,
                    'due_date'          => $d['due_date'] ?? date('Y-m-d', strtotime('+30 days')),
                    'status'            => 'pending',
                    'created_by'        => auth()['id'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            // Non-fatal — basic fee assignment already done
        }
    }

    /**
     * Auto-provision an LMS user (learner) and enroll them in matching LMS courses.
     */
    private function _provisionLmsUser(int $studentId, array $admission): void
    {
        try {
            $instId = (int)$admission['institution_id'];

            // Check if LMS user already exists
            $this->db->query(
                "SELECT id FROM lms_users WHERE student_id = ? AND institution_id = ? AND deleted_at IS NULL LIMIT 1",
                [$studentId, $instId]
            );
            $existing = $this->db->fetch();
            if ($existing) {
                $lmsUserId = (int)$existing['id'];
            } else {
                $lmsUserId = (int)$this->db->insert('lms_users', [
                    'institution_id' => $instId,
                    'student_id'     => $studentId,
                    'first_name'     => $admission['first_name'],
                    'last_name'      => $admission['last_name'] ?? '',
                    'email'          => $admission['email'],
                    'role'           => 'learner',
                    'status'         => 'active',
                    'xp_points'      => 0,
                    'level'          => 1,
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            }

            if (!$lmsUserId) return;

            // Find LMS courses linked to subjects in the student's batch
            $batchId  = $admission['batch_id'] ?? null;
            $courseId  = $admission['course_id'] ?? null; // academic program

            // Get subjects from faculty_subject_allocations for this batch
            $this->db->query(
                "SELECT DISTINCT lc.id AS lms_course_id
                 FROM lms_courses lc
                 JOIN subjects s ON s.id = lc.subject_id
                 JOIN faculty_subject_allocations fsa ON fsa.subject_id = s.id
                 WHERE fsa.batch_id = ? AND fsa.institution_id = ?
                   AND lc.institution_id = ? AND lc.deleted_at IS NULL
                   AND lc.status IN ('published','active')",
                [$batchId, $instId, $instId]
            );
            $lmsCourses = $this->db->fetchAll();

            // Also include courses linked to subjects in the student's program (course_id)
            if ($courseId) {
                $this->db->query(
                    "SELECT DISTINCT lc.id AS lms_course_id
                     FROM lms_courses lc
                     JOIN subjects s ON s.id = lc.subject_id
                     WHERE s.course_id = ? AND s.institution_id = ?
                       AND lc.institution_id = ? AND lc.deleted_at IS NULL
                       AND lc.status IN ('published','active')",
                    [$courseId, $instId, $instId]
                );
                $programCourses = $this->db->fetchAll();
                $existingIds = array_column($lmsCourses, 'lms_course_id');
                foreach ($programCourses as $pc) {
                    if (!in_array($pc['lms_course_id'], $existingIds)) {
                        $lmsCourses[] = $pc;
                    }
                }
            }

            // Create enrollments
            foreach ($lmsCourses as $lc) {
                $this->db->query(
                    "SELECT id FROM lms_enrollments WHERE lms_user_id = ? AND course_id = ? LIMIT 1",
                    [$lmsUserId, $lc['lms_course_id']]
                );
                if ($this->db->fetch()) continue;

                $this->db->insert('lms_enrollments', [
                    'institution_id' => $instId,
                    'lms_user_id'    => $lmsUserId,
                    'course_id'      => (int)$lc['lms_course_id'],
                    'status'         => 'active',
                    'progress'       => 0,
                    'enrolled_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        } catch (\Throwable $e) {
            // Non-fatal — student record is already created
        }
    }
}
