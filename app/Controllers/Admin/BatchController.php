<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class BatchController extends BaseController
{
    public function index(): void
    {
        $this->authorize('batches.view');

        $courseId = $this->input('course_id');
        $status   = $this->input('status');
        $search   = $this->input('search');

        $where  = "b.deleted_at IS NULL";
        $params = [];

        if ($this->institutionId) {
            $where   .= " AND b.institution_id = ?";
            $params[] = $this->institutionId;
        }
        if ($courseId) { $where .= " AND b.course_id = ?"; $params[] = $courseId; }
        if ($status)   { $where .= " AND b.status = ?";   $params[] = $status; }
        if ($search)   {
            $where  .= " AND (b.name LIKE ? OR b.code LIKE ?)";
            $s       = '%' . $search . '%';
            $params  = array_merge($params, [$s, $s]);
        }

        $page    = max(1, (int)($this->input('page') ?: 1));
        $perPage = config('app.per_page', 15);

        $sql = "SELECT b.*, c.name AS course_name, ay.name AS academic_year_name,
                       COUNT(s.id) AS student_count
                FROM batches b
                LEFT JOIN courses       c  ON c.id  = b.course_id
                LEFT JOIN academic_years ay ON ay.id = b.academic_year_id
                LEFT JOIN students      s  ON s.batch_id = b.id AND s.deleted_at IS NULL
                WHERE {$where}
                GROUP BY b.id
                ORDER BY b.start_date DESC, b.name";

        $batches = $this->db->paginate($sql, $params, $page, $perPage);

        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->view('batches/index', compact('batches', 'courses', 'courseId', 'status', 'search'));
    }

    public function create(): void
    {
        $this->authorize('batches.create');

        $this->db->query("SELECT id, name, code, degree_type FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY is_current DESC, start_date DESC LIMIT 5", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->view('batches/create', compact('courses', 'academicYears'));
    }

    public function store(): void
    {
        $this->authorize('batches.create');

        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'name'      => 'required|max:100',
            'course_id' => 'required|numeric',
        ]);
        if ($errors) { $this->backWithErrors(array_values($errors), $data); return; }

        $id = $this->db->insert('batches', [
            'institution_id'  => $this->institutionId,
            'course_id'       => (int)$data['course_id'],
            'academic_year_id'=> $data['academic_year_id'] ? (int)$data['academic_year_id'] : null,
            'name'            => sanitize($data['name']),
            'code'            => strtoupper(sanitize($data['code'] ?? '')),
            'semester'        => $data['semester'] ? (int)$data['semester'] : null,
            'max_students'    => (int)($data['max_students'] ?? $data['capacity'] ?? 0),
            'start_date'      => $data['start_date'] ?: null,
            'end_date'        => $data['end_date'] ?: null,
            'class_timing'    => sanitize($data['class_timing'] ?? $data['timing'] ?? ''),
            'status'          => $data['status'] ?? 'active',
        ]);

        $this->logAudit('create', 'batch', $id);
        $this->redirectWith(url('batches'), 'success', 'Batch created successfully.');
    }

    public function edit(int $id): void
    {
        $this->authorize('batches.edit');

        $this->db->query(
            "SELECT b.*, c.name AS course_name FROM batches b
             LEFT JOIN courses c ON c.id = b.course_id
             WHERE b.id = ? AND b.deleted_at IS NULL",
            [$id]
        );
        $batch = $this->db->fetch();
        if (!$batch) { $this->redirectWith(url('batches'), 'error', 'Batch not found.'); return; }

        $this->db->query("SELECT id, name, code FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY is_current DESC, start_date DESC LIMIT 5", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->view('batches/edit', compact('batch', 'courses', 'academicYears'));
    }

    public function update(int $id): void
    {
        $this->authorize('batches.edit');

        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data   = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'course_id' => 'required']);
        if ($errors) { $this->backWithErrors(array_values($errors), $data); return; }

        $this->db->update('batches', [
            'course_id'       => (int)$data['course_id'],
            'academic_year_id'=> $data['academic_year_id'] ? (int)$data['academic_year_id'] : null,
            'name'            => sanitize($data['name']),
            'code'            => strtoupper(sanitize($data['code'] ?? '')),
            'semester'        => $data['semester'] ? (int)$data['semester'] : null,
            'max_students'    => (int)($data['max_students'] ?? $data['capacity'] ?? 0),
            'start_date'      => $data['start_date'] ?: null,
            'end_date'        => $data['end_date'] ?: null,
            'class_timing'    => sanitize($data['class_timing'] ?? $data['timing'] ?? ''),
            'status'          => $data['status'] ?? 'active',
        ], '`id` = ?', [$id]);

        $this->logAudit('update', 'batch', $id);
        $this->redirectWith(url('batches'), 'success', 'Batch updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('batches.delete');

        if (!verifyCsrf()) { $this->redirectWith(url('batches'), 'error', 'Session expired.'); return; }

        $this->db->update('batches', ['deleted_at' => date('Y-m-d H:i:s')], '`id` = ?', [$id]);
        $this->logAudit('delete', 'batch', $id);
        $this->redirectWith(url('batches'), 'success', 'Batch deleted successfully.');
    }
}
