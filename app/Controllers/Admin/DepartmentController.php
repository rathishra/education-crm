<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Department;
use App\Models\Institution;

class DepartmentController extends BaseController
{
    private Department $deptModel;
    private Institution $instModel;

    public function __construct()
    {
        parent::__construct();
        $this->deptModel = new Department();
        $this->instModel = new Institution();
    }

    public function index(): void
    {
        $this->authorize('departments.view');

        $page = (int)($this->input('page') ?? 1);
        $filters = [
            'search'         => $this->input('search'),
            'status'         => $this->input('status'),
            'institution_id' => $this->input('institution_id') ?: $this->institutionId,
        ];

        $departments = $this->deptModel->getListPaginated($page, 15, $filters);
        $institutions = session('user_institutions', []);

        $this->view('departments.index', [
            'pageTitle'    => 'Departments',
            'departments'  => $departments,
            'institutions' => $institutions,
            'filters'      => $filters,
        ]);
    }

    public function create(): void
    {
        $this->authorize('departments.create');

        $institutions = session('user_institutions', []);

        $this->view('departments.create', [
            'pageTitle'    => 'Add Department',
            'institutions' => $institutions,
        ]);
    }

    public function store(): void
    {
        $this->authorize('departments.create');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data = $this->postData([
            'institution_id', 'name', 'code', 'hod_name', 'email', 'phone', 'description'
        ]);

        $instId = (int)($data['institution_id'] ?? $this->institutionId);
        $data['institution_id'] = $instId;

        $errors = $this->validate($data, [
            'institution_id' => 'required|numeric',
            'name'           => 'required|max:255',
            'code'           => 'required|max:50',
        ]);

        // Check unique code within institution
        if (empty($errors) && $this->db->exists('departments', 'institution_id = ? AND code = ?', [$instId, $data['code']])) {
            $errors['code'] = 'This department code already exists in this institution.';
        }

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $data['status'] = 'active';

        try {
            $id = $this->deptModel->withoutScope()->create($data);
            $this->logAudit('create', 'department', $id);
            $this->redirectWith(url('departments'), 'success', 'Department created successfully.');
        } catch (\Exception $e) {
            appLog("Department create failed: " . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create department.'], $data);
        }
    }

    public function edit(string $id): void
    {
        $this->authorize('departments.edit');

        $this->db->query("SELECT d.*, i.name as institution_name FROM departments d JOIN institutions i ON i.id = d.institution_id WHERE d.id = ?", [(int)$id]);
        $dept = $this->db->fetch();

        if (!$dept) {
            $this->redirectWith(url('departments'), 'error', 'Department not found.');
            return;
        }

        $institutions = session('user_institutions', []);

        $this->view('departments.edit', [
            'pageTitle'    => 'Edit Department',
            'dept'         => $dept,
            'institutions' => $institutions,
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('departments.edit');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $dept = $this->deptModel->find((int)$id);
        if (!$dept) {
            $this->redirectWith(url('departments'), 'error', 'Department not found.');
            return;
        }

        $data = $this->postData(['name', 'hod_name', 'email', 'phone', 'description', 'status']);

        $errors = $this->validate($data, [
            'name'   => 'required|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $this->deptModel->update((int)$id, $data);
        $this->logAudit('update', 'department', (int)$id, $dept, $data);
        $this->redirectWith(url('departments'), 'success', 'Department updated successfully.');
    }

    public function destroy(string $id): void
    {
        $this->authorize('departments.delete');

        $dept = $this->deptModel->find((int)$id);
        if (!$dept) {
            $this->redirectWith(url('departments'), 'error', 'Department not found.');
            return;
        }

        if ($this->db->count('courses', 'department_id = ?', [(int)$id]) > 0) {
            $this->redirectWith(url('departments'), 'error', 'Cannot delete department with linked courses.');
            return;
        }

        $this->deptModel->delete((int)$id);
        $this->logAudit('delete', 'department', (int)$id);
        $this->redirectWith(url('departments'), 'success', 'Department deleted successfully.');
    }
}
