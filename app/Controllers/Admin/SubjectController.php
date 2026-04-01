<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SubjectController extends BaseController
{
    public function index(): void
    {
        $this->authorize('subjects.view');

        $where  = "s.institution_id = ?";
        $params = [$this->institutionId];

        $search = $this->input('search');
        $type   = $this->input('type');

        if ($search) {
            $where .= " AND (s.name LIKE ? OR s.code LIKE ?)";
            $s = '%' . $search . '%';
            $params = array_merge($params, [$s, $s]);
        }
        if ($type) {
            $where .= " AND s.type = ?";
            $params[] = $type;
        }

        $page = max(1, (int)($this->input('page') ?: 1));
        $sql  = "SELECT s.*, d.name as department_name
                 FROM subjects s
                 LEFT JOIN departments d ON d.id = s.department_id
                 WHERE {$where}
                 ORDER BY s.name ASC";

        $subjects = $this->db->paginate($sql, $params, $page, config('app.per_page', 15));

        $this->view('subjects/index', compact('subjects', 'search', 'type'));
    }

    public function create(): void
    {
        $this->authorize('subjects.manage');

        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? AND status = 'active' ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('subjects/create', compact('departments'));
    }

    public function store(): void
    {
        $this->authorize('subjects.manage');

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'department_id' => 'required',
            'code'          => 'required',
            'name'          => 'required',
            'type'          => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        // Check for duplicate code within institution
        $this->db->query("SELECT id FROM subjects WHERE institution_id = ? AND code = ?", [$this->institutionId, strtoupper(sanitize($data['code']))]);
        if ($this->db->fetch()) {
            $this->backWithErrors(['Subject code already exists for this institution.']);
            return;
        }

        $id = $this->db->insert('subjects', [
            'institution_id' => $this->institutionId,
            'department_id'  => (int)$data['department_id'],
            'code'           => strtoupper(sanitize($data['code'])),
            'name'           => sanitize($data['name']),
            'type'           => $data['type'],
            'credits'        => (float)($data['credits'] ?? 0),
            'status'         => $data['status'] ?? 'active',
        ]);

        $this->logAudit('subject_created', 'subject', $id);
        $this->redirectWith(url('subjects'), 'success', 'Subject created successfully.');
    }

    public function edit(int $id): void
    {
        $this->authorize('subjects.manage');

        $this->db->query("SELECT * FROM subjects WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $subject = $this->db->fetch();
        if (!$subject) {
            $this->redirectWith(url('subjects'), 'error', 'Subject not found.');
            return;
        }

        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? AND status = 'active' ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('subjects/edit', compact('subject', 'departments'));
    }

    public function update(int $id): void
    {
        $this->authorize('subjects.manage');

        $this->db->query("SELECT id FROM subjects WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        if (!$this->db->fetch()) {
            $this->redirectWith(url('subjects'), 'error', 'Subject not found.');
            return;
        }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'department_id' => 'required',
            'code'          => 'required',
            'name'          => 'required',
            'type'          => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $this->db->update('subjects', [
            'department_id' => (int)$data['department_id'],
            'code'          => strtoupper(sanitize($data['code'])),
            'name'          => sanitize($data['name']),
            'type'          => $data['type'],
            'credits'       => (float)($data['credits'] ?? 0),
            'status'        => $data['status'] ?? 'active',
        ], '`id` = ?', [$id]);

        $this->logAudit('subject_updated', 'subject', $id);
        $this->redirectWith(url('subjects'), 'success', 'Subject updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('subjects.manage');

        // Check if used in timetables or batch_subjects
        $this->db->query("SELECT COUNT(*) as cnt FROM timetables WHERE subject_id = ?", [$id]);
        $timetableUse = $this->db->fetch();
        if (($timetableUse['cnt'] ?? 0) > 0) {
            $this->redirectWith(url('subjects'), 'error', 'Cannot delete: subject is used in timetables.');
            return;
        }

        $this->db->query("DELETE FROM batch_subjects WHERE subject_id = ?", [$id]);
        $this->db->query("DELETE FROM subjects WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);

        $this->logAudit('subject_deleted', 'subject', $id);
        $this->redirectWith(url('subjects'), 'success', 'Subject deleted successfully.');
    }
}
