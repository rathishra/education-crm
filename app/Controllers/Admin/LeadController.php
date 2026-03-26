<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Lead;
use App\Models\User;
use App\Models\Student;

class LeadController extends BaseController
{
    private Lead $leadModel;
    private User $userModel;
    private Student $studentModel;

    public function __construct()
    {
        parent::__construct();
        $this->leadModel = new Lead();
        $this->userModel = new User();
        $this->studentModel = new Student();
    }

    public function index(): void
    {
        $this->authorize('leads.view');

        $page = (int)($this->input('page') ?? 1);
        $filters = [
            'search'      => $this->input('search'),
            'status_id'   => $this->input('status_id'),
            'source_id'   => $this->input('source_id'),
            'assigned_to' => $this->input('assigned_to'),
            'priority'    => $this->input('priority'),
            'course_id'   => $this->input('course_id'),
            'date_from'   => $this->input('date_from'),
            'date_to'     => $this->input('date_to'),
        ];

        // Counselors see only their leads unless they have view_all
        if (!hasPermission('leads.view_all') && hasRole('counselor')) {
            $filters['only_mine'] = $this->user['id'];
        }

        $leads = $this->leadModel->getListPaginated($page, 15, $filters);
        $statuses = $this->leadModel->getStatuses();
        $sources = $this->leadModel->getSources();
        $counselors = $this->userModel->getCounselors($this->institutionId);

        $this->view('leads.index', [
            'pageTitle'  => 'Lead Management',
            'leads'      => $leads,
            'statuses'   => $statuses,
            'sources'    => $sources,
            'counselors' => $counselors,
            'filters'    => $filters,
        ]);
    }

    public function create(): void
    {
        $this->authorize('leads.create');

        $statuses = $this->leadModel->getStatuses();
        $sources = $this->leadModel->getSources();
        $counselors = $this->userModel->getCounselors($this->institutionId);

        // Get courses for current institution
        $this->db->query(
            "SELECT id, name, code FROM courses WHERE institution_id = ? AND status = 'active' ORDER BY name",
            [$this->institutionId]
        );
        $courses = $this->db->fetchAll();

        $this->view('leads.create', [
            'pageTitle'  => 'Add Lead',
            'statuses'   => $statuses,
            'sources'    => $sources,
            'counselors' => $counselors,
            'courses'    => $courses,
        ]);
    }

    public function store(): void
    {
        $this->authorize('leads.create');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data = $this->postData([
            'first_name', 'last_name', 'email', 'phone', 'alternate_phone',
            'date_of_birth', 'gender', 'address_line1', 'address_line2',
            'city', 'state', 'pincode', 'country',
            'qualification', 'percentage', 'passing_year', 'school_college',
            'lead_source_id', 'lead_status_id', 'assigned_to',
            'course_interested_id', 'priority', 'notes'
        ]);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'phone'      => 'required|phone',
            'email'      => 'email',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        // Check duplicates
        $duplicate = $this->leadModel->checkDuplicate($data['phone'], $data['email'] ?: null);
        if ($duplicate) {
            $dupMsg = "Possible duplicate found: {$duplicate['first_name']} {$duplicate['last_name']} ({$duplicate['lead_number']})";
            flash('warning', $dupMsg);
            // Mark but still allow creation
            $data['is_duplicate'] = 1;
            $data['duplicate_of'] = $duplicate['id'];
        }

        // Set defaults
        $data['institution_id'] = $this->institutionId;
        $data['lead_number'] = $this->leadModel->generateLeadNumber($this->institutionId);
        $data['lead_status_id'] = ($data['lead_status_id'] ?? null) ?: $this->leadModel->getDefaultStatusId();
        $data['created_by'] = $this->user['id'];

        // Clean empty values
        foreach (['lead_source_id', 'assigned_to', 'course_interested_id', 'percentage', 'passing_year', 'duplicate_of'] as $field) {
            if (empty($data[$field])) $data[$field] = null;
        }
        if (empty($data['date_of_birth'])) $data['date_of_birth'] = null;
        if (empty($data['country'])) $data['country'] = 'India';

        try {
            $leadId = $this->leadModel->withoutScope()->create($data);

            // Log activity
            $this->leadModel->addActivity($leadId, 'system', 'Lead created', null, $this->user['id']);

            // If assigned, log and notify
            if (!empty($data['assigned_to'])) {
                $this->leadModel->assignTo($leadId, (int)$data['assigned_to'], $this->user['id']);
            }

            $this->logAudit('create', 'lead', $leadId);
            $this->redirectWith(url('leads/' . $leadId), 'success', 'Lead created successfully. Number: ' . $data['lead_number']);
        } catch (\Exception $e) {
            appLog("Lead create failed: " . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create lead.'], $data);
        }
    }

    public function show(string $id): void
    {
        $this->authorize('leads.view');

        $lead = $this->leadModel->findWithDetails((int)$id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $statuses = $this->leadModel->getStatuses();
        $sources = $this->leadModel->getSources();
        $counselors = $this->userModel->getCounselors($this->institutionId);

        $this->view('leads.show', [
            'pageTitle'  => 'Lead Details - ' . $lead['lead_number'],
            'lead'       => $lead,
            'statuses'   => $statuses,
            'sources'    => $sources,
            'counselors' => $counselors,
        ]);
    }

    public function edit(string $id): void
    {
        $this->authorize('leads.edit');

        $lead = $this->leadModel->find((int)$id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $statuses = $this->leadModel->getStatuses();
        $sources = $this->leadModel->getSources();
        $counselors = $this->userModel->getCounselors($this->institutionId);
        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND status = 'active' ORDER BY name", [$lead['institution_id']]);
        $courses = $this->db->fetchAll();

        $this->view('leads.edit', [
            'pageTitle'  => 'Edit Lead',
            'lead'       => $lead,
            'statuses'   => $statuses,
            'sources'    => $sources,
            'counselors' => $counselors,
            'courses'    => $courses,
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('leads.edit');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $lead = $this->leadModel->find((int)$id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $data = $this->postData([
            'first_name', 'last_name', 'email', 'phone', 'alternate_phone',
            'date_of_birth', 'gender', 'address_line1', 'address_line2',
            'city', 'state', 'pincode', 'country',
            'qualification', 'percentage', 'passing_year', 'school_college',
            'lead_source_id', 'lead_status_id', 'assigned_to',
            'course_interested_id', 'priority', 'notes'
        ]);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'phone'      => 'required|phone',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $data['updated_by'] = $this->user['id'];
        foreach (['lead_source_id', 'assigned_to', 'course_interested_id', 'percentage', 'passing_year'] as $field) {
            if (empty($data[$field])) $data[$field] = null;
        }
        if (empty($data['date_of_birth'])) $data['date_of_birth'] = null;

        // Detect status change
        if ($data['lead_status_id'] != $lead['lead_status_id']) {
            $this->leadModel->updateStatus((int)$id, (int)$data['lead_status_id'], $this->user['id']);
            unset($data['lead_status_id']); // already updated
        }

        // Detect assignment change
        if (($data['assigned_to'] ?? null) != $lead['assigned_to'] && !empty($data['assigned_to'])) {
            $this->leadModel->assignTo((int)$id, (int)$data['assigned_to'], $this->user['id']);
            unset($data['assigned_to']);
        }

        $this->leadModel->update((int)$id, $data);
        $this->leadModel->addActivity((int)$id, 'system', 'Lead details updated', null, $this->user['id']);
        $this->logAudit('update', 'lead', (int)$id, $lead, $data);
        $this->redirectWith(url('leads/' . $id), 'success', 'Lead updated successfully.');
    }

    public function destroy(string $id): void
    {
        $this->authorize('leads.delete');

        $lead = $this->leadModel->find((int)$id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        $this->leadModel->delete((int)$id); // soft delete
        $this->logAudit('delete', 'lead', (int)$id);
        $this->redirectWith(url('leads'), 'success', 'Lead deleted successfully.');
    }

    /**
     * Quick status update (AJAX)
     */
    public function updateStatus(string $id): void
    {
        $this->authorize('leads.edit');
        $statusId = (int)($_POST['status_id'] ?? 0);

        if (!$statusId) {
            $this->error('Invalid status.');
            return;
        }

        $lead = $this->leadModel->find((int)$id);
        if (!$lead) {
            $this->error('Lead not found.', 404);
            return;
        }

        $this->leadModel->updateStatus((int)$id, $statusId, $this->user['id']);
        $this->logAudit('update_status', 'lead', (int)$id);

        if (isAjax()) {
            $this->success('Status updated successfully.');
            return;
        }
        $this->redirectWith(url('leads/' . $id), 'success', 'Status updated.');
    }

    /**
     * Assign lead (AJAX)
     */
    public function assign(string $id): void
    {
        $this->authorize('leads.assign');
        $counselorId = (int)($_POST['assigned_to'] ?? 0);

        if (!$counselorId) {
            $this->error('Select a counselor.');
            return;
        }

        $lead = $this->leadModel->find((int)$id);
        if (!$lead) {
            $this->error('Lead not found.', 404);
            return;
        }

        $this->leadModel->assignTo((int)$id, $counselorId, $this->user['id']);
        $this->logAudit('assign', 'lead', (int)$id);

        if (isAjax()) {
            $this->success('Lead assigned successfully.');
            return;
        }
        $this->redirectWith(url('leads/' . $id), 'success', 'Lead assigned.');
    }

    /**
     * Add activity note (AJAX)
     */
    public function addActivity(string $id): void
    {
        $this->authorize('leads.edit');

        $type = $_POST['type'] ?? 'note';
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($title)) {
            $this->error('Title is required.');
            return;
        }

        $lead = $this->leadModel->find((int)$id);
        if (!$lead) {
            $this->error('Lead not found.', 404);
            return;
        }

        $this->leadModel->addActivity((int)$id, $type, $title, $description, $this->user['id']);

        // Update last contacted
        $this->leadModel->update((int)$id, ['last_contacted_at' => date('Y-m-d H:i:s')]);

        if (isAjax()) {
            $this->success('Activity added.');
            return;
        }
        $this->redirectWith(url('leads/' . $id), 'success', 'Activity added.');
    }

    /**
     * Convert lead to student
     */
    public function convert(string $id): void
    {
        $this->authorize('leads.edit');

        $lead = $this->leadModel->find((int)$id);
        if (!$lead) {
            $this->redirectWith(url('leads'), 'error', 'Lead not found.');
            return;
        }

        // Mark as converted
        $convertedStatus = $this->db->query(
            "SELECT id FROM lead_statuses WHERE is_won = 1 LIMIT 1"
        )->fetch();

        if ($convertedStatus) {
            $this->leadModel->updateStatus((int)$id, (int)$convertedStatus['id'], $this->user['id']);
        }

        // Create student record from lead data
        $studentIdNumber = generateNumber('STD', (string)$this->institutionId);
        $studentId = $this->studentModel->create([
            'institution_id' => $lead['institution_id'],
            'lead_id'        => $lead['id'],
            'student_id_number' => $studentIdNumber,
            'first_name'     => $lead['first_name'],
            'last_name'      => $lead['last_name'],
            'email'          => $lead['email'],
            'phone'          => $lead['phone'],
            'alternate_phone'=> $lead['alternate_phone'],
            'date_of_birth'  => $lead['date_of_birth'],
            'gender'         => $lead['gender'],
            'address_line1'  => $lead['address_line1'],
            'address_line2'  => $lead['address_line2'],
            'city'           => $lead['city'],
            'state'          => $lead['state'],
            'pincode'        => $lead['pincode'],
            'country'        => $lead['country'],
            'course_id'      => $lead['course_interested_id'],
            'admission_date' => date('Y-m-d'),
            'created_by'     => $this->user['id'],
            'status'         => 'active',
        ]);

        // Student timeline entry
        $this->studentModel->addActivity($studentId, 'conversion', 'Converted from lead ' . $lead['lead_number'], $this->user['id'], [
            'lead_id' => $lead['id'],
            'source'  => $lead['lead_source_id'],
        ]);

        // Update lead converted_at timestamp
        $this->leadModel->update((int)$id, ['converted_at' => date('Y-m-d H:i:s')]);

        $this->logAudit('convert', 'lead', (int)$id);

        // Redirect to student profile
        redirect(url('students/' . $studentId));
    }

    /**
     * Export leads to CSV
     */
    public function export(): void
    {
        $this->authorize('leads.export');

        $filters = [
            'status_id' => $this->input('status_id'),
            'date_from' => $this->input('date_from'),
            'date_to'   => $this->input('date_to'),
        ];

        $leads = $this->leadModel->getExportData($filters);

        $filename = 'leads_export_' . date('Y-m-d_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header row
        fputcsv($output, [
            'Lead Number', 'First Name', 'Last Name', 'Email', 'Phone',
            'City', 'State', 'Qualification', 'Percentage', 'Passing Year',
            'School/College', 'Course Interested', 'Source', 'Status',
            'Assigned To', 'Priority', 'Notes', 'Created Date'
        ]);

        foreach ($leads as $lead) {
            fputcsv($output, [
                $lead['lead_number'], $lead['first_name'], $lead['last_name'],
                $lead['email'], $lead['phone'], $lead['city'], $lead['state'],
                $lead['qualification'], $lead['percentage'], $lead['passing_year'],
                $lead['school_college'], $lead['course_interested'], $lead['source'],
                $lead['status'], $lead['assigned_to'], $lead['priority'],
                $lead['notes'], $lead['created_at']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Show import form
     */
    public function showImport(): void
    {
        $this->authorize('leads.import');
        $this->view('leads.import', ['pageTitle' => 'Import Leads']);
    }

    /**
     * Handle CSV import
     */
    public function import(): void
    {
        $this->authorize('leads.import');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->backWithErrors(['Please upload a valid CSV file.']);
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->backWithErrors(['Could not read the file.']);
            return;
        }

        $header = fgetcsv($handle);
        $imported = 0;
        $skipped = 0;
        $defaultStatusId = $this->leadModel->getDefaultStatusId();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) { $skipped++; continue; }

            $firstName = trim($row[0] ?? '');
            $phone = trim($row[3] ?? $row[1] ?? '');

            if (empty($firstName) || empty($phone)) { $skipped++; continue; }

            // Check duplicate
            if ($this->leadModel->checkDuplicate($phone)) { $skipped++; continue; }

            $data = [
                'institution_id' => $this->institutionId,
                'lead_number'    => $this->leadModel->generateLeadNumber($this->institutionId),
                'first_name'     => $firstName,
                'last_name'      => trim($row[1] ?? ''),
                'email'          => trim($row[2] ?? '') ?: null,
                'phone'          => $phone,
                'city'           => trim($row[4] ?? '') ?: null,
                'qualification'  => trim($row[5] ?? '') ?: null,
                'lead_status_id' => $defaultStatusId,
                'priority'       => 'medium',
                'created_by'     => $this->user['id'],
            ];

            try {
                $this->leadModel->withoutScope()->create($data);
                $imported++;
            } catch (\Exception $e) {
                $skipped++;
            }
        }

        fclose($handle);
        $this->redirectWith(url('leads'), 'success', "Import complete: {$imported} imported, {$skipped} skipped.");
    }
}
