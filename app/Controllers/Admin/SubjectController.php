<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SubjectController extends BaseController
{
    public function index(): void
    {
        $this->authorize('subjects.view');

        $institutionId = session('institution_id');
        $where = "s.institution_id = ?";
        $params = [$institutionId];

        $search = $this->input('search');
        if ($search) {
            $where .= " AND (s.name LIKE ? OR s.code LIKE ?)";
            $s = '%' . $search . '%';
            $params = array_merge($params, [$s, $s]);
        }

        $page = (int)($this->input('page') ?: 1);
        $sql = "SELECT s.*, d.name as department_name 
                FROM subjects s 
                LEFT JOIN departments d ON d.id = s.department_id 
                WHERE {$where} 
                ORDER BY s.name ASC";

        $subjects = db()->paginate($sql, $params, $page, config('app.per_page', 15));

        $this->view('subjects/index', compact('subjects', 'search'));
    }

    public function create(): void
    {
        $this->authorize('subjects.manage');

        $institutionId = session('institution_id');
        $departments = db()->query("SELECT id, name FROM departments WHERE institution_id = ? AND status = 'active' ORDER BY name", [$institutionId])->fetchAll();

        $this->view('subjects/create', compact('departments'));
    }

    public function store(): void
    {
        $this->authorize('subjects.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'department_id' => 'required',
            'code' => 'required',
            'name' => 'required',
            'type' => 'required'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $id = db()->insert('subjects', [
            'institution_id' => session('institution_id'),
            'department_id'  => $data['department_id'],
            'code'           => sanitize($data['code']),
            'name'           => sanitize($data['name']),
            'type'           => $data['type'],
            'credits'        => (float)($data['credits'] ?? 0),
            'status'         => $data['status'] ?? 'active'
        ]);

        $this->logAudit('subject_created', 'subject', $id);
        $this->redirectWith('subjects', 'Subject created successfully.', 'success');
    }

    public function edit(int $id): void
    {
        $this->authorize('subjects.manage');

        $subject = db()->query("SELECT * FROM subjects WHERE id = ? AND institution_id = ?", [$id, session('institution_id')])->fetch();
        if (!$subject) {
            $this->redirectWith('subjects', 'Subject not found.', 'error');
            return;
        }

        $departments = db()->query("SELECT id, name FROM departments WHERE institution_id = ? AND status = 'active' ORDER BY name", [session('institution_id')])->fetchAll();

        $this->view('subjects/edit', compact('subject', 'departments'));
    }

    public function update(int $id): void
    {
        $this->authorize('subjects.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'department_id' => 'required',
            'code' => 'required',
            'name' => 'required',
            'type' => 'required'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        db()->update('subjects', [
            'department_id'  => $data['department_id'],
            'code'           => sanitize($data['code']),
            'name'           => sanitize($data['name']),
            'type'           => $data['type'],
            'credits'        => (float)($data['credits'] ?? 0),
            'status'         => $data['status'] ?? 'active'
        ], '`id` = ?', [$id]);

        $this->logAudit('subject_updated', 'subject', $id);
        $this->redirectWith('subjects', 'Subject updated successfully.', 'success');
    }

    public function destroy(int $id): void
    {
        $this->authorize('subjects.manage');
        
        // Soft delete not supported by schema, check if used in timetable?
        // We'll just hard delete or we could do a status check
        db()->query("DELETE FROM subjects WHERE id = ? AND institution_id = ?", [$id, session('institution_id')]);
        
        $this->logAudit('subject_deleted', 'subject', $id);
        $this->redirectWith('subjects', 'Subject deleted successfully.', 'success');
    }
}
