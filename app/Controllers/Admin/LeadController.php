<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Lead;
use App\Models\User;

class LeadController extends BaseController
{
    private Lead $leadModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->leadModel = new Lead();
        $this->userModel = new User();
        $this->leadModel->setInstitutionScope($this->institutionId);
    }

    // =========================================================
    // INDEX
    // =========================================================

    public function index(): void
    {
        $this->authorize('leads.view');

        $page = max(1, (int)($this->input('page') ?? 1));

        $filters = [
            'search'              => $this->input('search'),
            'status_id'           => $this->input('status_id'),
            'source_id'           => $this->input('source_id'),
            'assigned_to'         => $this->input('assigned_to'),
            'priority'            => $this->input('priority'),
            'course_id'           => $this->input('course_id'),
            'department_id'       => $this->input('department_id'),
            'date_from'           => $this->input('date_from'),
            'date_to'             => $this->input('date_to'),
            'next_followup_overdue' => $this->input('followup_overdue'),
            'converted'           => $this->input('converted'),
        ];

        // Counselors see only their leads unless they have view_all
        if (!hasPermission('leads.view_all') && hasRole('counselor')) {
            $filters['only_mine'] = $this->user['id'];
        }

        $statuses = $this->leadModel->getStatuses();
        $sources  = $this->leadModel->getSources();
        $stats    = $this->leadModel->getStats($this->institutionId);

        // Counselors list via raw query
        $this->db->query(
            "SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $counselors = $this->db->fetchAll();

        // Courses (scoped to institution)
        $this->db->query(
            "SELECT id, name FROM courses WHERE institution_id = ? AND status = 'active' ORDER BY name",
            [$this->institutionId]
        );
        $courses = $this->db->fetchAll();

        // Departments
        $this->db->query(
            "SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name",
            [$this->institutionId]
        );
        $departments = $this->db->fetchAll();

        $leads = $this->leadModel->getListPaginated($page, 15, $filters);

        $this->view('leads/index', [
            'pageTitle'   => 'Lead Management',
            'leads'       => $leads,
            'statuses'    => $statuses,
            'sources'     => $sources,
            'stats'       => $stats,
            'counselors'  => $counselors,
            'courses'     => $courses,
            'departments' => $departments,
            'filters'     => $filters,
        ]);
    }

    // =========================================================
    // CREATE
    // =========================================================

    public function create(): void
    {
        $this->authorize('leads.create');

        [$institutions, $departments, $courses, $counselors, $sources, $statuses] =
            $this->loadFormDropdowns();

        $this->view('leads/create', [
            'pageTitle'    => 'Add Lead',
            'institutions' => $institutions,
            'departments'  => $departments,
            'courses'      => $courses,
            'counselors'   => $counselors,
            'sources'      => $sources,
            'statuses'     => $statuses,
        ]);
    }

    // =========================================================
    // STORE
    // =========================================================

    public function store(): void
    {
        $this->authorize('leads.create');

        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please try again.']);
            return;
        }

        $errors = $this->validate($_POST, [
            'first_name' => 'required|max:100',
            'phone'      => 'required',
            'email'      => 'email',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $_POST);
            return;
        }

        $phone = sanitize(trim($_POST['phone'] ?? ''));
        $email = sanitize(trim($_POST['email'] ?? ''));

        // Non-blocking duplicate check
        $duplicate = $this->leadModel->checkDuplicateAjax(
            $phone,
            $email,
            $this->institutionId
        );
        if ($duplicate) {
            flash('warning', 'Possible duplicate: ' . $duplicate['first_name'] . ' '
                . $duplicate['last_name'] . ' (' . $duplicate['lead_number'] . ')');
        }

        // Generate lead number
        $leadNumber = $this->leadModel->generateLeadNumber($this->institutionId);

        // Resolve default status
        $leadStatusId = (int)($_POST['lead_status_id'] ?? 0)
            ?: $this->leadModel->getDefaultStatusId();

        // Assigned counselor (form field is counselor_id)
        $assignedTo = (int)($_POST['counselor_id'] ?? $_POST['assigned_to'] ?? 0) ?: null;

        $insertData = [
            // Auto fields
            'institution_id'   => $this->institutionId,
            'lead_number'      => $leadNumber,
            'lead_status_id'   => $leadStatusId,
            'created_by'       => $this->user['id'],

            // Personal
            'first_name'       => sanitize(trim($_POST['first_name'] ?? '')),
            'last_name'        => sanitize(trim($_POST['last_name'] ?? '')) ?: null,
            'email'            => $email ?: null,
            'phone'            => $phone,
            'gender'           => sanitize($_POST['gender'] ?? '') ?: null,
            'date_of_birth'    => ($_POST['date_of_birth'] ?? '') ?: null,

            // Academic interest
            'course_interested_id' => (int)($_POST['course_interested_id'] ?? 0) ?: null,
            'department_id'        => (int)($_POST['department_id'] ?? 0) ?: null,
            'academic_year'        => sanitize(trim($_POST['academic_year'] ?? '')) ?: null,
            'preferred_mode'       => sanitize($_POST['preferred_mode'] ?? '') ?: null,

            // Lead meta
            'priority'         => sanitize($_POST['priority'] ?? 'warm') ?: 'warm',
            'lead_score'       => isset($_POST['lead_score']) && $_POST['lead_score'] !== ''
                                    ? (int)$_POST['lead_score'] : null,
            'expected_join_date' => ($_POST['expected_join_date'] ?? '') ?: null,
            'budget'           => isset($_POST['budget']) && $_POST['budget'] !== ''
                                    ? (float)$_POST['budget'] : null,

            // Source tracking
            'lead_source_id'   => (int)($_POST['lead_source_id'] ?? 0) ?: null,
            'campaign_name'    => sanitize(trim($_POST['campaign_name'] ?? '')) ?: null,
            'reference_name'   => sanitize(trim($_POST['reference_name'] ?? '')) ?: null,

            // Assignment
            'assigned_to'      => $assignedTo,

            // Notes & followup
            'notes'            => sanitize(trim($_POST['notes'] ?? '')) ?: null,
            'next_followup_date' => ($_POST['next_followup_date'] ?? '') ?: null,
            'followup_mode'    => sanitize($_POST['followup_mode'] ?? '') ?: null,

            // Preferences
            'hostel_required'      => isset($_POST['hostel_required']) ? 1 : 0,
            'transport_required'   => isset($_POST['transport_required']) ? 1 : 0,
            'scholarship_required' => isset($_POST['scholarship_required']) ? 1 : 0,

            // Alternate contact
            'alternate_phone'  => sanitize(trim($_POST['alternate_phone'] ?? '')) ?: null,

            // Link to enquiry if converted
            'enquiry_id'       => (int)($_POST['enquiry_id'] ?? 0) ?: null,
        ];

        try {
            $leadId = $this->leadModel->withoutScope()->create($insertData);

            $this->leadModel->addActivity(
                $leadId,
                'system',
                'Lead created',
                null,
                $this->user['id']
            );

            if ($assignedTo) {
                $this->leadModel->assignTo($leadId, $assignedTo, $this->user['id']);
            }

            $this->logAudit('create', 'lead', $leadId);
            $this->redirectWith(
                url('leads/' . $leadId),
                'success',
                'Lead created. Number: ' . $leadNumber
            );
        } catch (\Exception $e) {
            appLog('Lead create failed: ' . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create lead. Please try again.'], $_POST);
        }
    }

    // =========================================================
    // SHOW
    // =========================================================

    public function show(int $id): void
    {
        $this->authorize('leads.view');

        $lead = $this->leadModel->findWithDetails($id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $statuses   = $this->leadModel->getStatuses();
        $sources    = $this->leadModel->getSources();

        $this->db->query(
            "SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $counselors = $this->db->fetchAll();

        $this->view('leads/show', compact('lead', 'statuses', 'sources', 'counselors'));
    }

    // =========================================================
    // EDIT
    // =========================================================

    public function edit(int $id): void
    {
        $this->authorize('leads.edit');

        $lead = $this->leadModel->findWithDetails($id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        [$institutions, $departments, $courses, $counselors, $sources, $statuses] =
            $this->loadFormDropdowns();

        $this->view('leads/edit', [
            'pageTitle'    => 'Edit Lead — ' . $lead['lead_number'],
            'lead'         => $lead,
            'institutions' => $institutions,
            'departments'  => $departments,
            'courses'      => $courses,
            'counselors'   => $counselors,
            'sources'      => $sources,
            'statuses'     => $statuses,
        ]);
    }

    // =========================================================
    // UPDATE
    // =========================================================

    public function update(int $id): void
    {
        $this->authorize('leads.edit');

        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please try again.']);
            return;
        }

        $lead = $this->leadModel->findWithDetails($id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $errors = $this->validate($_POST, [
            'first_name' => 'required|max:100',
            'phone'      => 'required',
            'email'      => 'email',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $_POST);
            return;
        }

        $newStatusId  = (int)($_POST['lead_status_id'] ?? 0) ?: null;
        $newAssigned  = (int)($_POST['counselor_id'] ?? $_POST['assigned_to'] ?? 0) ?: null;

        // Detect status change
        if ($newStatusId && $newStatusId !== (int)$lead['lead_status_id']) {
            $this->leadModel->updateStatus($id, $newStatusId, $this->user['id']);
        }

        // Detect assignment change
        if ($newAssigned && $newAssigned !== (int)($lead['assigned_to'] ?? 0)) {
            $this->leadModel->assignTo($id, $newAssigned, $this->user['id']);
        }

        $updateData = [
            // Personal
            'first_name'       => sanitize(trim($_POST['first_name'] ?? '')),
            'last_name'        => sanitize(trim($_POST['last_name'] ?? '')) ?: null,
            'email'            => sanitize(trim($_POST['email'] ?? '')) ?: null,
            'phone'            => sanitize(trim($_POST['phone'] ?? '')),
            'gender'           => sanitize($_POST['gender'] ?? '') ?: null,
            'date_of_birth'    => ($_POST['date_of_birth'] ?? '') ?: null,

            // Academic interest
            'course_interested_id' => (int)($_POST['course_interested_id'] ?? 0) ?: null,
            'department_id'        => (int)($_POST['department_id'] ?? 0) ?: null,
            'academic_year'        => sanitize(trim($_POST['academic_year'] ?? '')) ?: null,
            'preferred_mode'       => sanitize($_POST['preferred_mode'] ?? '') ?: null,

            // Lead meta
            'priority'         => sanitize($_POST['priority'] ?? 'warm') ?: 'warm',
            'lead_score'       => isset($_POST['lead_score']) && $_POST['lead_score'] !== ''
                                    ? (int)$_POST['lead_score'] : null,
            'expected_join_date' => ($_POST['expected_join_date'] ?? '') ?: null,
            'budget'           => isset($_POST['budget']) && $_POST['budget'] !== ''
                                    ? (float)$_POST['budget'] : null,

            // Source tracking
            'lead_source_id'   => (int)($_POST['lead_source_id'] ?? 0) ?: null,
            'campaign_name'    => sanitize(trim($_POST['campaign_name'] ?? '')) ?: null,
            'reference_name'   => sanitize(trim($_POST['reference_name'] ?? '')) ?: null,

            // Notes & followup
            'notes'            => sanitize(trim($_POST['notes'] ?? '')) ?: null,
            'next_followup_date' => ($_POST['next_followup_date'] ?? '') ?: null,
            'followup_mode'    => sanitize($_POST['followup_mode'] ?? '') ?: null,

            // Preferences
            'hostel_required'      => isset($_POST['hostel_required']) ? 1 : 0,
            'transport_required'   => isset($_POST['transport_required']) ? 1 : 0,
            'scholarship_required' => isset($_POST['scholarship_required']) ? 1 : 0,

            // Alternate contact
            'alternate_phone'  => sanitize(trim($_POST['alternate_phone'] ?? '')) ?: null,

            'updated_by' => $this->user['id'],
        ];

        $this->leadModel->update($id, $updateData);
        $this->leadModel->addActivity(
            $id,
            'system',
            'Lead details updated',
            null,
            $this->user['id']
        );
        $this->logAudit('update', 'lead', $id, $lead, $updateData);
        $this->redirectWith(url('leads/' . $id), 'success', 'Lead updated.');
    }

    // =========================================================
    // DESTROY
    // =========================================================

    public function destroy(int $id): void
    {
        $this->authorize('leads.delete');

        $lead = $this->leadModel->find($id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $this->leadModel->delete($id); // soft delete
        $this->logAudit('delete', 'lead', $id);
        $this->redirectWith(url('leads'), 'success', 'Lead deleted successfully.');
    }

    // =========================================================
    // UPDATE STATUS (AJAX-aware)
    // =========================================================

    public function updateStatus(int $id): void
    {
        $this->authorize('leads.edit');

        $statusId = (int)($_POST['status_id'] ?? 0);

        if (!$statusId) {
            if (isAjax()) {
                $this->error('Invalid status ID.');
                return;
            }
            $this->redirectWith(url('leads/' . $id), 'error', 'Invalid status.');
            return;
        }

        $lead = $this->leadModel->find($id);
        if (!$lead) {
            if (isAjax()) {
                $this->error('Lead not found.', 404);
                return;
            }
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $this->leadModel->updateStatus($id, $statusId, $this->user['id']);
        $this->logAudit('update_status', 'lead', $id);

        if (isAjax()) {
            $this->success('Status updated successfully.');
            return;
        }
        $this->redirectWith(url('leads/' . $id), 'success', 'Status updated.');
    }

    // =========================================================
    // ASSIGN (AJAX-aware)
    // =========================================================

    public function assign(int $id): void
    {
        $this->authorize('leads.edit');

        $counselorId = (int)($_POST['assigned_to'] ?? 0);

        if (!$counselorId) {
            if (isAjax()) {
                $this->error('Please select a counselor.');
                return;
            }
            $this->redirectWith(url('leads/' . $id), 'error', 'Please select a counselor.');
            return;
        }

        $lead = $this->leadModel->find($id);
        if (!$lead) {
            if (isAjax()) {
                $this->error('Lead not found.', 404);
                return;
            }
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $this->leadModel->assignTo($id, $counselorId, $this->user['id']);
        $this->logAudit('assign', 'lead', $id);

        if (isAjax()) {
            $this->success('Lead assigned successfully.');
            return;
        }
        $this->redirectWith(url('leads/' . $id), 'success', 'Lead assigned.');
    }

    // =========================================================
    // ADD ACTIVITY (AJAX-aware)
    // =========================================================

    public function addActivity(int $id): void
    {
        $this->authorize('leads.edit');

        $type        = sanitize($_POST['type'] ?? 'note');
        $title       = sanitize(trim($_POST['title'] ?? ''));
        $description = sanitize(trim($_POST['description'] ?? '')) ?: null;

        if (empty($title)) {
            if (isAjax()) {
                $this->error('Activity title is required.');
                return;
            }
            $this->backWithErrors(['Activity title is required.']);
            return;
        }

        $lead = $this->leadModel->find($id);
        if (!$lead) {
            if (isAjax()) {
                $this->error('Lead not found.', 404);
                return;
            }
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $this->leadModel->addActivity($id, $type, $title, $description, $this->user['id']);
        $this->leadModel->update($id, ['last_contacted_at' => date('Y-m-d H:i:s')]);

        if (isAjax()) {
            $this->success('Activity added.');
            return;
        }
        $this->redirectWith(url('leads/' . $id), 'success', 'Activity added.');
    }

    // =========================================================
    // STORE FOLLOWUP
    // =========================================================

    public function storeFollowup(int $id): void
    {
        $this->authorize('leads.edit');

        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please try again.']);
            return;
        }

        $lead = $this->leadModel->find($id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $errors = $this->validate($_POST, [
            'followup_date' => 'required',
            'followup_mode' => 'required',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $_POST);
            return;
        }

        $followupData = [
            'followup_date'      => sanitize($_POST['followup_date']),
            'followup_mode'      => sanitize($_POST['followup_mode']),
            'status'             => sanitize($_POST['status'] ?? 'completed'),
            'outcome'            => sanitize($_POST['outcome'] ?? '') ?: null,
            'notes'              => sanitize(trim($_POST['notes'] ?? '')) ?: null,
            'next_followup_date' => ($_POST['next_followup_date'] ?? '') ?: null,
            'next_followup_mode' => sanitize($_POST['next_followup_mode'] ?? '') ?: null,
            'duration_minutes'   => isset($_POST['duration_minutes']) && $_POST['duration_minutes'] !== ''
                                    ? (int)$_POST['duration_minutes'] : null,
            'counselor_id'       => (int)($_POST['counselor_id'] ?? $this->user['id']),
        ];

        $this->leadModel->addFollowup($id, $followupData, $this->institutionId, $this->user['id']);

        $this->redirectWith(url('leads/' . $id), 'success', 'Follow-up recorded successfully.');
    }

    // =========================================================
    // CONVERT LEAD TO ADMISSION
    // =========================================================

    public function convert(int $id): void
    {
        // Use leads.convert if it exists, fall back to leads.edit
        if (hasPermission('leads.convert')) {
            $this->authorize('leads.convert');
        } else {
            $this->authorize('leads.edit');
        }

        $lead = $this->leadModel->findWithDetails($id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        // Look up source name for the admission record
        $sourceName = null;
        if (!empty($lead['lead_source_id'])) {
            $this->db->query(
                "SELECT name FROM lead_sources WHERE id = ?",
                [$lead['lead_source_id']]
            );
            $src = $this->db->fetch();
            $sourceName = $src['source_name'] ?? $src['name'] ?? null;
        }

        // Require a course before conversion
        if (empty($lead['course_interested_id'])) {
            $this->redirectWith(url('leads/' . $id), 'error', 'Please assign a course of interest to the lead before converting.');
            return;
        }

        try {
            $admissionModel = new \App\Models\Admission();
            $admNumber = $admissionModel->generateAdmissionNumber($this->institutionId);

            // Resolve academic_year_id from name if needed
            $academicYearId = null;
            if (!empty($lead['academic_year_id'])) {
                $academicYearId = (int)$lead['academic_year_id'];
            } elseif (!empty($lead['academic_year'])) {
                $this->db->query(
                    "SELECT id FROM academic_years WHERE institution_id = ? AND (name = ? OR YEAR(start_date) = ?) LIMIT 1",
                    [$this->institutionId, $lead['academic_year'], $lead['academic_year']]
                );
                $ay = $this->db->fetch();
                $academicYearId = $ay ? (int)$ay['id'] : null;
            }
            // Fallback: use current academic year
            if (!$academicYearId) {
                $this->db->query(
                    "SELECT id FROM academic_years WHERE institution_id = ? AND is_current = 1 LIMIT 1",
                    [$this->institutionId]
                );
                $ay = $this->db->fetch();
                $academicYearId = $ay ? (int)$ay['id'] : null;
            }

            $admId = $admissionModel->create([
                'institution_id'     => $this->institutionId,
                'admission_number'   => $admNumber,
                'first_name'         => $lead['first_name'],
                'last_name'          => $lead['last_name'] ?? null,
                'email'              => $lead['email'] ?? null,
                'phone'              => $lead['phone'],
                'gender'             => $lead['gender'] ?? null,
                'date_of_birth'      => $lead['date_of_birth'] ?? null,
                'course_id'          => (int)$lead['course_interested_id'],
                'academic_year_id'   => $academicYearId,
                'application_date'   => date('Y-m-d'),
                'status'             => 'applied',
                'application_source' => $sourceName,
                'remarks'            => $lead['notes'] ?? null,
                'lead_id'            => $lead['id'],
                'created_by'         => $this->user['id'],
            ]);

            // Mark lead as won/converted
            $this->db->query(
                "SELECT id FROM lead_statuses WHERE is_won = 1 LIMIT 1"
            );
            $wonStatus = $this->db->fetch();
            if ($wonStatus) {
                $this->leadModel->updateStatus($id, (int)$wonStatus['id'], $this->user['id']);
            }

            // Stamp converted_at
            $this->leadModel->update($id, ['converted_at' => date('Y-m-d H:i:s')]);

            $this->leadModel->addActivity(
                $id,
                'system',
                'Converted to Admission #' . $admNumber,
                null,
                $this->user['id']
            );

            $this->logAudit('convert', 'lead', $id);

            $this->redirectWith(
                url('admissions/' . $admId),
                'success',
                'Lead converted to admission.'
            );
        } catch (\Exception $e) {
            appLog('Lead convert failed [lead_id=' . $id . ']: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine(), 'error');
            $this->redirectWith(url('leads/' . $id), 'error', 'Conversion failed: ' . $e->getMessage());
        }
    }

    // =========================================================
    // CHECK DUPLICATE (JSON)
    // =========================================================

    public function checkDuplicate(): void
    {
        header('Content-Type: application/json');

        $phone         = sanitize(trim($this->input('phone') ?? ''));
        $email         = sanitize(trim($this->input('email') ?? ''));
        $institutionId = (int)($this->input('institution_id') ?? $this->institutionId);
        $excludeId     = (int)($this->input('exclude_id') ?? 0);

        if (empty($phone) && empty($email)) {
            echo json_encode(['duplicate' => false]);
            exit;
        }

        $found = $this->leadModel->checkDuplicateAjax(
            $phone,
            $email,
            $institutionId,
            $excludeId
        );

        if ($found) {
            echo json_encode([
                'duplicate'   => true,
                'field'       => ($found['phone'] === $phone) ? 'phone' : 'email',
                'lead_number' => $found['lead_number'],
                'name'        => trim($found['first_name'] . ' ' . ($found['last_name'] ?? '')),
                'id'          => (int)$found['id'],
            ]);
        } else {
            echo json_encode(['duplicate' => false]);
        }
        exit;
    }

    // =========================================================
    // AJAX DEPARTMENTS
    // =========================================================

    public function ajaxDepartments(): void
    {
        header('Content-Type: application/json');

        $institutionId = (int)($this->input('institution_id') ?? $this->institutionId);

        $this->db->query(
            "SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name",
            [$institutionId]
        );
        $departments = $this->db->fetchAll();

        echo json_encode($departments);
        exit;
    }

    // =========================================================
    // AJAX COURSES
    // =========================================================

    public function ajaxCourses(): void
    {
        header('Content-Type: application/json');

        $departmentId  = (int)($this->input('department_id') ?? 0);
        $institutionId = (int)($this->input('institution_id') ?? $this->institutionId);

        if ($departmentId) {
            $this->db->query(
                "SELECT id, name, code FROM courses
                 WHERE department_id = ? AND status = 'active'
                 ORDER BY name",
                [$departmentId]
            );
        } else {
            $this->db->query(
                "SELECT id, name, code FROM courses
                 WHERE institution_id = ? AND status = 'active'
                 ORDER BY name",
                [$institutionId]
            );
        }
        $courses = $this->db->fetchAll();

        echo json_encode($courses);
        exit;
    }

    // =========================================================
    // EXPORT
    // =========================================================

    public function export(): void
    {
        $this->authorize('leads.export');

        $filters = [
            'status_id'   => $this->input('status_id'),
            'source_id'   => $this->input('source_id'),
            'assigned_to' => $this->input('assigned_to'),
            'priority'    => $this->input('priority'),
            'course_id'   => $this->input('course_id'),
            'department_id' => $this->input('department_id'),
            'date_from'   => $this->input('date_from'),
            'date_to'     => $this->input('date_to'),
        ];

        $leads    = $this->leadModel->getExportData($filters);
        $filename = 'leads_export_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8 compatibility
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Lead Number', 'First Name', 'Last Name', 'Email', 'Phone',
            'City', 'State', 'Qualification', 'Percentage', 'Passing Year',
            'School / College', 'Course Interested', 'Department', 'Source',
            'Status', 'Assigned To', 'Priority', 'Lead Score', 'Budget',
            'Expected Join Date', 'Campaign', 'Reference', 'Notes',
            'Next Follow-up', 'Hostel', 'Transport', 'Scholarship', 'Created Date',
        ]);

        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['lead_number'],
                $lead['first_name'],
                $lead['last_name'] ?? '',
                $lead['email'] ?? '',
                $lead['phone'],
                $lead['city'] ?? '',
                $lead['state'] ?? '',
                $lead['qualification'] ?? '',
                $lead['percentage'] ?? '',
                $lead['passing_year'] ?? '',
                $lead['school_college'] ?? '',
                $lead['course_interested'] ?? '',
                $lead['department'] ?? '',
                $lead['source'] ?? '',
                $lead['status'] ?? '',
                $lead['assigned_to'] ?? '',
                $lead['priority'] ?? '',
                $lead['lead_score'] ?? '',
                $lead['budget'] ?? '',
                $lead['expected_join_date'] ?? '',
                $lead['campaign_name'] ?? '',
                $lead['reference_name'] ?? '',
                $lead['notes'] ?? '',
                $lead['next_followup_date'] ?? '',
                isset($lead['hostel_required']) ? ($lead['hostel_required'] ? 'Yes' : 'No') : '',
                isset($lead['transport_required']) ? ($lead['transport_required'] ? 'Yes' : 'No') : '',
                isset($lead['scholarship_required']) ? ($lead['scholarship_required'] ? 'Yes' : 'No') : '',
                $lead['created_at'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    // =========================================================
    // IMPORT — SHOW FORM
    // =========================================================

    public function showImport(): void
    {
        $this->authorize('leads.import');
        $this->view('leads/import', ['pageTitle' => 'Import Leads']);
    }

    // =========================================================
    // IMPORT — PROCESS CSV
    // =========================================================

    public function import(): void
    {
        $this->authorize('leads.import');

        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please try again.']);
            return;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->backWithErrors(['Please upload a valid CSV file.']);
            return;
        }

        $file   = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->backWithErrors(['Could not read the uploaded file.']);
            return;
        }

        // Skip header row
        fgetcsv($handle);

        $defaultStatusId = $this->leadModel->getDefaultStatusId();
        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                $skipped++;
                continue;
            }

            $firstName = trim($row[0] ?? '');
            // Column layout: first_name, last_name, email, phone, city, qualification
            $phone     = trim($row[3] ?? $row[1] ?? '');

            if (empty($firstName) || empty($phone)) {
                $skipped++;
                continue;
            }

            // Skip duplicates
            if ($this->leadModel->checkDuplicate($phone, null)) {
                $skipped++;
                continue;
            }

            $data = [
                'institution_id' => $this->institutionId,
                'lead_number'    => $this->leadModel->generateLeadNumber($this->institutionId),
                'first_name'     => sanitize($firstName),
                'last_name'      => sanitize(trim($row[1] ?? '')) ?: null,
                'email'          => sanitize(trim($row[2] ?? '')) ?: null,
                'phone'          => sanitize($phone),
                'city'           => sanitize(trim($row[4] ?? '')) ?: null,
                'qualification'  => sanitize(trim($row[5] ?? '')) ?: null,
                'lead_status_id' => $defaultStatusId,
                'priority'       => 'warm',
                'created_by'     => $this->user['id'],
            ];

            try {
                $this->leadModel->withoutScope()->create($data);
                $imported++;
            } catch (\Exception $e) {
                appLog('Lead import row failed: ' . $e->getMessage(), 'warning');
                $skipped++;
            }
        }

        fclose($handle);

        $this->redirectWith(
            url('leads'),
            'success',
            "Import complete: {$imported} imported, {$skipped} skipped."
        );
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Load all dropdown data needed for create/edit forms.
     * Returns array: [institutions, departments, courses, counselors, sources, statuses]
     */
    private function loadFormDropdowns(): array
    {
        // Active institutions
        $this->db->query(
            "SELECT id, name, code FROM institutions
             WHERE status = 'active' AND deleted_at IS NULL
             ORDER BY name"
        );
        $institutions = $this->db->fetchAll();

        // Departments for current institution
        $this->db->query(
            "SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name",
            [$this->institutionId]
        );
        $departments = $this->db->fetchAll();

        // Active courses
        $this->db->query(
            "SELECT id, name FROM courses WHERE status = 'active' ORDER BY name"
        );
        $courses = $this->db->fetchAll();

        // Counselors (users with a role in this institution)
        $this->db->query(
            "SELECT DISTINCT u.id, CONCAT(u.first_name, ' ', u.last_name) AS name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $counselors = $this->db->fetchAll();

        // Lead sources
        $this->db->query(
            "SELECT id, name FROM lead_sources WHERE is_active = 1 ORDER BY name"
        );
        $sources = $this->db->fetchAll();

        // Lead statuses
        $statuses = $this->leadModel->getStatuses();

        return [$institutions, $departments, $courses, $counselors, $sources, $statuses];
    }

    // =========================================================
    // IMPORT LEADS (CSV)
    // =========================================================

    public function showImport(): void
    {
        $this->authorize('leads.create');
        $pageTitle = 'Import Leads';
        $this->view('leads/import', compact('pageTitle'));
    }

    public function importTemplate(): void
    {
        $this->authorize('leads.create');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads_import_template.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['first_name','last_name','phone','alternate_phone','email','gender','course_interested_id','priority','notes']);
        fputcsv($out, ['John','Doe','9876543210','','john@example.com','male','','warm','Interested in BCA']);
        fclose($out);
        exit;
    }

    public function import(): void
    {
        $this->authorize('leads.create');

        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please try again.']);
            return;
        }

        if (empty($_FILES['csv_file']['tmp_name'])) {
            $this->backWithErrors(['Please upload a CSV file.']);
            return;
        }

        $file = $_FILES['csv_file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $this->backWithErrors(['Only CSV files are supported.']);
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $this->backWithErrors(['Could not read the uploaded file.']);
            return;
        }

        // Expected CSV columns (header row)
        $headers = array_map('trim', fgetcsv($handle) ?: []);
        $required = ['first_name', 'phone'];
        foreach ($required as $req) {
            if (!in_array($req, $headers)) {
                fclose($handle);
                $this->backWithErrors(["CSV must contain column: {$req}"]);
                return;
            }
        }

        $defaultStatusId = $this->leadModel->getDefaultStatusId();
        $imported = 0;
        $skipped  = 0;
        $errors   = [];
        $row      = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $row++;
            if (empty(array_filter($line))) continue;

            $data = array_combine($headers, array_pad($line, count($headers), ''));

            $phone = trim($data['phone'] ?? '');
            $firstName = trim($data['first_name'] ?? '');

            if (empty($phone) || empty($firstName)) {
                $skipped++;
                $errors[] = "Row {$row}: missing first_name or phone — skipped.";
                continue;
            }

            // Skip duplicates
            $this->db->query(
                "SELECT id FROM leads WHERE institution_id = ? AND phone = ? LIMIT 1",
                [$this->institutionId, $phone]
            );
            if ($this->db->fetch()) {
                $skipped++;
                $errors[] = "Row {$row}: phone {$phone} already exists — skipped.";
                continue;
            }

            try {
                $insertData = [
                    'institution_id'       => $this->institutionId,
                    'lead_number'          => $this->leadModel->generateLeadNumber($this->institutionId),
                    'lead_status_id'       => $defaultStatusId,
                    'first_name'           => sanitize($firstName),
                    'last_name'            => sanitize(trim($data['last_name'] ?? '')) ?: null,
                    'email'                => sanitize(trim($data['email'] ?? '')) ?: null,
                    'phone'                => sanitize($phone),
                    'alternate_phone'      => sanitize(trim($data['alternate_phone'] ?? '')) ?: null,
                    'gender'               => in_array($data['gender'] ?? '', ['male','female','other']) ? $data['gender'] : null,
                    'course_interested_id' => !empty($data['course_interested_id']) ? (int)$data['course_interested_id'] : null,
                    'priority'             => in_array($data['priority'] ?? '', ['hot','warm','cold']) ? $data['priority'] : 'warm',
                    'notes'                => sanitize(trim($data['notes'] ?? '')) ?: null,
                    'created_by'           => $this->user['id'],
                ];

                $leadId = $this->leadModel->withoutScope()->create($insertData);
                $this->leadModel->addActivity($leadId, 'system', 'Imported via CSV', null, $this->user['id']);
                $imported++;
            } catch (\Exception $e) {
                $skipped++;
                $errors[] = "Row {$row}: " . $e->getMessage();
            }
        }

        fclose($handle);

        $this->logAudit('import', 'leads', 0, ['imported' => $imported, 'skipped' => $skipped]);

        $msg = "{$imported} lead(s) imported successfully.";
        if ($skipped) $msg .= " {$skipped} row(s) skipped.";

        if ($imported > 0) {
            flash('success', $msg);
        } else {
            flash('warning', $msg);
        }

        if (!empty($errors)) {
            $_SESSION['import_errors'] = array_slice($errors, 0, 20);
        }

        redirect('leads/import');
    }
}
