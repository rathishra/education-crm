<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AcademicYear;
use App\Models\Institution;

class AcademicYearController extends BaseController
{
    private AcademicYear $ayModel;
    private Institution $instModel;

    public function __construct()
    {
        parent::__construct();
        $this->ayModel = new AcademicYear();
        $this->instModel = new Institution();
    }

    public function index(): void
    {
        $this->authorize('academic_years.view');

        $page = (int)($this->input('page') ?? 1);
        $filters = [
            'status'         => $this->input('status'),
            'institution_id' => $this->input('institution_id') ?: $this->institutionId,
        ];

        $academicYears = $this->ayModel->getListPaginated($page, 15, $filters);
        $institutions = session('user_institutions', []);

        $this->view('academic_years.index', [
            'pageTitle'     => 'Academic Years',
            'academicYears' => $academicYears,
            'institutions'  => $institutions,
            'filters'       => $filters,
        ]);
    }

    public function create(): void
    {
        $this->authorize('academic_years.create');
        $institutions = session('user_institutions', []);

        $this->view('academic_years.create', [
            'pageTitle'    => 'Add Academic Year',
            'institutions' => $institutions,
        ]);
    }

    public function store(): void
    {
        $this->authorize('academic_years.create');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data = $this->postData(['institution_id', 'name', 'start_date', 'end_date', 'status', 'is_current']);
        
        $instId = (int)($data['institution_id'] ?? $this->institutionId);
        $data['institution_id'] = $instId;

        $errors = $this->validate($data, [
            'institution_id' => 'required|numeric',
            'name'           => 'required|max:50',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after:start_date',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $data['is_current'] = isset($data['is_current']) ? 1 : 0;

        try {
            $id = $this->ayModel->withoutScope()->create($data);
            
            if ($data['is_current']) {
                $this->ayModel->setCurrent($id, $instId);
            }

            $this->logAudit('create', 'academic_year', $id);
            $this->redirectWith(url('academic-years'), 'success', 'Academic Year created successfully.');
        } catch (\Exception $e) {
            appLog("AcademicYear create failed: " . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create academic year.'], $data);
        }
    }

    public function edit(string $id): void
    {
        $this->authorize('academic_years.edit');
        $ay = $this->ayModel->find((int)$id);

        if (!$ay) {
            $this->redirectWith(url('academic-years'), 'error', 'Academic Year not found.');
            return;
        }

        $institutions = session('user_institutions', []);

        $this->view('academic_years.edit', [
            'pageTitle'    => 'Edit Academic Year',
            'ay'           => $ay,
            'institutions' => $institutions,
        ]);
    }

    public function update(string $id): void
    {
        $this->authorize('academic_years.edit');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $ay = $this->ayModel->find((int)$id);
        if (!$ay) {
            $this->redirectWith(url('academic-years'), 'error', 'Academic Year not found.');
            return;
        }

        $data = $this->postData(['name', 'start_date', 'end_date', 'status', 'is_current']);
        
        $errors = $this->validate($data, [
            'name'       => 'required|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'status'     => 'required|in:active,inactive',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $data['is_current'] = isset($data['is_current']) ? 1 : 0;

        $this->ayModel->update((int)$id, $data);
        
        if ($data['is_current']) {
            $this->ayModel->setCurrent((int)$id, $ay['institution_id']);
        }

        $this->logAudit('update', 'academic_year', (int)$id, $ay, $data);
        $this->redirectWith(url('academic-years'), 'success', 'Academic Year updated successfully.');
    }

    public function destroy(string $id): void
    {
        $this->authorize('academic_years.delete');
        $ay = $this->ayModel->find((int)$id);

        if (!$ay) {
            $this->redirectWith(url('academic-years'), 'error', 'Academic Year not found.');
            return;
        }

        // Check dependencies
        if ($this->db->count('students', 'academic_year_id = ?', [(int)$id]) > 0) {
            $this->redirectWith(url('academic-years'), 'error', 'Cannot delete academic year with linked students.');
            return;
        }

        $this->ayModel->delete((int)$id);
        $this->logAudit('delete', 'academic_year', (int)$id);
        $this->redirectWith(url('academic-years'), 'success', 'Academic Year deleted successfully.');
    }
}
