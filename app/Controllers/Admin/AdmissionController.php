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

    public function index(): void
    {
        $this->authorize('admissions.view');

        $filters = [
            'search'          => $this->input('search'),
            'status'          => $this->input('status'),
            'course_id'       => $this->input('course_id'),
            'batch_id'        => $this->input('batch_id'),
            'academic_year_id'=> $this->input('academic_year_id'),
            'date_from'       => $this->input('date_from'),
            'date_to'         => $this->input('date_to'),
        ];

        $page       = max(1, (int)($this->input('page') ?: 1));
        $admissions = $this->admission->getListPaginated($page, config('app.per_page', 15), $filters);
        $stats      = $this->admission->getStats();

        $db = $this->db;
        $db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $db->fetchAll();

        $this->view('admissions/index', compact('admissions', 'filters', 'stats', 'courses', 'academicYears'));
    }

    public function create(): void
    {
        $this->authorize('admissions.create');

        $leadId  = (int)$this->input('lead_id');
        $prefill = [];
        if ($leadId) {
            $prefill = $this->admission->createFromLead($leadId);
        }

        $db = $this->db;
        $db->query("SELECT id, name, code FROM courses WHERE institution_id = ? AND deleted_at IS NULL AND status = 'active' ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $db->fetchAll();

        $this->view('admissions/create', compact('prefill', 'courses', 'academicYears', 'leadId'));
    }

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

        $insertData = [
            'institution_id'          => $this->institutionId,
            'admission_number'        => $this->admission->generateAdmissionNumber($this->institutionId),
            'first_name'              => sanitize($data['first_name']),
            'last_name'               => sanitize($data['last_name'] ?? ''),
            'email'                   => sanitize($data['email'] ?? ''),
            'phone'                   => sanitize($data['phone']),
            'date_of_birth'           => $data['date_of_birth'] ?: null,
            'gender'                  => $data['gender'] ?? null,
            'address_line1'           => sanitize($data['address_line1'] ?? ''),
            'city'                    => sanitize($data['city'] ?? ''),
            'state'                   => sanitize($data['state'] ?? ''),
            'pincode'                 => sanitize($data['pincode'] ?? ''),
            'nationality'             => sanitize($data['nationality'] ?? 'Indian'),
            'category'                => $data['category'] ?? null,
            'father_name'             => sanitize($data['father_name'] ?? ''),
            'father_phone'            => sanitize($data['father_phone'] ?? ''),
            'mother_name'             => sanitize($data['mother_name'] ?? ''),
            'guardian_name'           => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'          => sanitize($data['guardian_phone'] ?? ''),
            'previous_qualification'  => sanitize($data['previous_qualification'] ?? ''),
            'previous_percentage'     => $data['previous_percentage'] ?: null,
            'previous_institution'    => sanitize($data['previous_institution'] ?? ''),
            'previous_year_of_passing'=> $data['previous_year_of_passing'] ?: null,
            'course_id'               => (int)$data['course_id'],
            'batch_id'                => $data['batch_id'] ?: null,
            'academic_year_id'        => $data['academic_year_id'] ?: null,
            'admission_type'          => $data['admission_type'] ?? 'regular',
            'application_date'        => date('Y-m-d'),
            'status'                  => 'applied',
            'lead_id'                 => $data['lead_id'] ?: null,
            'remarks'                 => sanitize($data['remarks'] ?? ''),
            'created_by'              => $this->user['id'],
        ];

        $id = $this->admission->create($insertData);
        $this->logAudit('admission_created', 'admission', $id);
        $this->redirectWith(url('admissions/' . $id), 'success', 'Admission application created successfully.');
    }

    public function show(int $id): void
    {
        $this->authorize('admissions.view');

        $admission = $this->admission->findWithDetails($id);
        if (!$admission) {
            $this->redirectWith(url('admissions'), 'error', 'Admission not found.');
            return;
        }

        $this->db->query("SELECT id, name FROM batches WHERE course_id = ? AND deleted_at IS NULL ORDER BY start_date DESC", [$admission['course_id']]);
        $batches = $this->db->fetchAll();

        $this->view('admissions/show', compact('admission', 'batches'));
    }

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
        $db->query("SELECT id, name FROM batches WHERE course_id = ? AND deleted_at IS NULL ORDER BY start_date DESC", [$admission['course_id']]);
        $batches = $db->fetchAll();
        $db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $db->fetchAll();

        $this->view('admissions/edit', compact('admission', 'courses', 'batches', 'academicYears'));
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

        $this->admission->update($id, [
            'first_name'              => sanitize($data['first_name']),
            'last_name'               => sanitize($data['last_name'] ?? ''),
            'email'                   => sanitize($data['email'] ?? ''),
            'phone'                   => sanitize($data['phone']),
            'date_of_birth'           => $data['date_of_birth'] ?: null,
            'gender'                  => $data['gender'] ?? null,
            'address_line1'           => sanitize($data['address_line1'] ?? ''),
            'city'                    => sanitize($data['city'] ?? ''),
            'state'                   => sanitize($data['state'] ?? ''),
            'pincode'                 => sanitize($data['pincode'] ?? ''),
            'category'                => $data['category'] ?? null,
            'father_name'             => sanitize($data['father_name'] ?? ''),
            'father_phone'            => sanitize($data['father_phone'] ?? ''),
            'mother_name'             => sanitize($data['mother_name'] ?? ''),
            'guardian_name'           => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'          => sanitize($data['guardian_phone'] ?? ''),
            'previous_qualification'  => sanitize($data['previous_qualification'] ?? ''),
            'previous_percentage'     => $data['previous_percentage'] ?: null,
            'previous_institution'    => sanitize($data['previous_institution'] ?? ''),
            'course_id'               => (int)$data['course_id'],
            'batch_id'                => $data['batch_id'] ?: null,
            'academic_year_id'        => $data['academic_year_id'] ?: null,
            'admission_type'          => $data['admission_type'] ?? $admission['admission_type'],
            'remarks'                 => sanitize($data['remarks'] ?? ''),
        ]);

        $this->logAudit('admission_updated', 'admission', $id);
        $this->redirectWith(url('admissions/' . $id), 'success', 'Admission updated successfully.');
    }

    public function approve(int $id): void
    {
        $this->authorize('admissions.approve');

        $result = $this->admission->approve($id, $this->user['id']);
        $msg    = $result ? 'Admission approved successfully.' : 'Cannot approve this admission.';
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
            $this->redirectWith(url('admissions/' . $id), 'error', 'Cannot enroll. Admission must be approved first.');
        }
    }

    /**
     * Auto-assign fee structure based on course and academic year
     */
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
