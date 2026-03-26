<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Institution;
use App\Models\Organization;

class InstitutionController extends BaseController
{
    private Institution $instModel;
    private Organization $orgModel;

    public function __construct()
    {
        parent::__construct();
        $this->instModel = new Institution();
        $this->orgModel = new Organization();
    }

    public function index(): void
    {
        $this->authorize('institutions.view');

        $page = (int)($this->input('page') ?? 1);
        $filters = [
            'search'          => $this->input('search'),
            'type'            => $this->input('type'),
            'status'          => $this->input('status'),
            'organization_id' => $this->input('organization_id'),
        ];

        $institutions = $this->instModel->getListPaginated($page, 15, $filters);
        $organizations = $this->orgModel->getSelectOptions();

        $this->view('institutions.index', [
            'pageTitle'     => 'Institutions',
            'institutions'  => $institutions,
            'organizations' => $organizations,
            'types'         => Institution::TYPES,
            'filters'       => $filters,
        ]);
    }

    public function create(): void
    {
        $this->authorize('institutions.create');

        $organizations = $this->orgModel->getSelectOptions();

        $this->view('institutions.create', [
            'pageTitle'     => 'Add Institution',
            'organizations' => $organizations,
            'types'         => Institution::TYPES,
        ]);
    }

    public function store(): void
    {
        $this->authorize('institutions.create');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data = $this->postData([
            'organization_id', 'name', 'code', 'type', 'email', 'phone', 'website',
            'address_line1', 'address_line2', 'city', 'state', 'country', 'pincode',
            'affiliation_number', 'affiliation_body', 'established_year', 'principal_name'
        ]);

        $errors = $this->validate($data, [
            'organization_id' => 'required|numeric',
            'name'            => 'required|max:255',
            'code'            => 'required|max:50|unique:institutions,code',
            'type'            => 'required|in:engineering,arts_science,medical,nursing,polytechnic,other',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $logo = $this->uploadFile('logo', 'logos');
        if ($logo) $data['logo'] = $logo['file_path'];
        $data['status'] = 'active';

        try {
            $id = $this->instModel->withoutScope()->create($data);
            $this->logAudit('create', 'institution', $id);
            $this->redirectWith(url('institutions'), 'success', 'Institution created successfully.');
        } catch (\Exception $e) {
            appLog("Institution create failed: " . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create institution.'], $data);
        }
    }

    public function show(string $id): void
    {
        $this->authorize('institutions.view');

        $inst = $this->instModel->findWithDetails((int)$id);
        if (!$inst) {
            $this->redirectWith(url('institutions'), 'error', 'Institution not found.');
            return;
        }

        $this->view('institutions.show', [
            'pageTitle' => 'Institution Details',
            'inst'      => $inst,
            'types'     => Institution::TYPES,
        ]);
    }

    public function edit(string $id): void
    {
        $this->authorize('institutions.edit');

        $inst = $this->instModel->find((int)$id);
        if (!$inst) {
            $this->redirectWith(url('institutions'), 'error', 'Institution not found.');
            return;
        }

        $organizations = $this->orgModel->getSelectOptions();

        $this->view('institutions.edit', [
            'pageTitle'     => 'Edit Institution',
            'inst'          => $inst,
            'organizations' => $organizations,
            'types'         => Institution::TYPES,
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('institutions.edit');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $inst = $this->instModel->find((int)$id);
        if (!$inst) {
            $this->redirectWith(url('institutions'), 'error', 'Institution not found.');
            return;
        }

        $data = $this->postData([
            'name', 'type', 'email', 'phone', 'website',
            'address_line1', 'address_line2', 'city', 'state', 'country', 'pincode',
            'affiliation_number', 'affiliation_body', 'established_year', 'principal_name', 'status'
        ]);

        $errors = $this->validate($data, [
            'name'   => 'required|max:255',
            'type'   => 'required|in:engineering,arts_science,medical,nursing,polytechnic,other',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $logo = $this->uploadFile('logo', 'logos');
        if ($logo) $data['logo'] = $logo['file_path'];

        $this->instModel->update((int)$id, $data);
        $this->logAudit('update', 'institution', (int)$id, $inst, $data);
        $this->redirectWith(url('institutions'), 'success', 'Institution updated successfully.');
    }

    public function destroy(string $id): void
    {
        $this->authorize('institutions.delete');

        $inst = $this->instModel->find((int)$id);
        if (!$inst) {
            $this->redirectWith(url('institutions'), 'error', 'Institution not found.');
            return;
        }

        // Check dependencies
        $deps = [];
        if ($this->db->count('departments', 'institution_id = ?', [(int)$id]) > 0) $deps[] = 'departments';
        if ($this->db->count('courses', 'institution_id = ?', [(int)$id]) > 0) $deps[] = 'courses';
        if ($this->db->count('students', 'institution_id = ? AND deleted_at IS NULL', [(int)$id]) > 0) $deps[] = 'students';
        if ($this->db->count('leads', 'institution_id = ? AND deleted_at IS NULL', [(int)$id]) > 0) $deps[] = 'leads';

        if (!empty($deps)) {
            $this->redirectWith(url('institutions'), 'error',
                'Cannot delete institution. It has linked: ' . implode(', ', $deps) . '. Remove them first or deactivate instead.');
            return;
        }

        $this->instModel->delete((int)$id);
        $this->logAudit('delete', 'institution', (int)$id);
        $this->redirectWith(url('institutions'), 'success', 'Institution deleted successfully.');
    }

    public function toggleStatus(string $id): void
    {
        $this->authorize('institutions.edit');

        $inst = $this->instModel->find((int)$id);
        if (!$inst) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not found']);
            return;
        }

        $newStatus = $inst['status'] === 'active' ? 'inactive' : 'active';
        $this->instModel->update((int)$id, ['status' => $newStatus]);
        $this->logAudit('status_toggle', 'institution', (int)$id, [], ['status' => $newStatus]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'status' => $newStatus]);
    }
}
