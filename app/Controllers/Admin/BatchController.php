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

        $where = "b.deleted_at IS NULL";
        $params = [];

        $institutionId = session('institution_id');
        if ($institutionId) { $where .= " AND b.institution_id = ?"; $params[] = $institutionId; }
        if ($courseId)      { $where .= " AND b.course_id = ?"; $params[] = $courseId; }
        if ($status)        { $where .= " AND b.status = ?"; $params[] = $status; }
        if ($search)        {
            $where .= " AND (b.name LIKE ? OR b.code LIKE ?)";
            $s = '%' . $search . '%';
            $params = array_merge($params, [$s, $s]);
        }

        $page = (int)($this->input('page') ?: 1);
        $perPage = config('app.per_page', 15);

        $sql = "SELECT b.*, c.name as course_name,
                       COUNT(s.id) as student_count
                FROM batches b
                LEFT JOIN courses c ON c.id = b.course_id
                LEFT JOIN students s ON s.batch_id = b.id AND s.deleted_at IS NULL
                WHERE {$where}
                GROUP BY b.id
                ORDER BY b.start_date DESC";

        $batches = db()->paginate($sql, $params, $page, $perPage);

        db()->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = db()->fetchAll();

        $this->view('batches/index', compact('batches', 'courses', 'courseId', 'status', 'search'));
    }

    public function create(): void
    {
        $this->authorize('batches.create');

        db()->query("SELECT id, name, code FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = db()->fetchAll();

        $this->view('batches/create', compact('courses'));
    }

    public function store(): void
    {
        $this->authorize('batches.create');

        $data = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'course_id' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $institutionId = session('institution_id');

        $id = db()->insert('batches', [
            'institution_id' => $institutionId,
            'course_id'      => $data['course_id'],
            'name'           => sanitize($data['name']),
            'code'           => strtoupper(sanitize($data['code'] ?? '')),
            'start_date'     => $data['start_date'] ?: null,
            'end_date'       => $data['end_date'] ?: null,
            'capacity'       => (int)($data['capacity'] ?? 0),
            'timing'         => sanitize($data['timing'] ?? ''),
            'status'         => $data['status'] ?? 'upcoming',
            'description'    => sanitize($data['description'] ?? ''),
            'created_by'     => auth()['id'],
        ]);

        $this->logAudit('batch_created', 'batch', $id);
        $this->redirectWith('batches', 'Batch created.', 'success');
    }

    public function edit(int $id): void
    {
        $this->authorize('batches.edit');

        db()->query("SELECT b.*, c.name as course_name FROM batches b LEFT JOIN courses c ON c.id = b.course_id WHERE b.id = ? AND b.deleted_at IS NULL", [$id]);
        $batch = db()->fetch();
        if (!$batch) { $this->redirectWith('batches', 'Batch not found.', 'error'); return; }

        db()->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = db()->fetchAll();

        $this->view('batches/edit', compact('batch', 'courses'));
    }

    public function update(int $id): void
    {
        $this->authorize('batches.edit');

        $data = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'course_id' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        db()->update('batches', [
            'course_id'  => $data['course_id'],
            'name'       => sanitize($data['name']),
            'code'       => strtoupper(sanitize($data['code'] ?? '')),
            'start_date' => $data['start_date'] ?: null,
            'end_date'   => $data['end_date'] ?: null,
            'capacity'   => (int)($data['capacity'] ?? 0),
            'timing'     => sanitize($data['timing'] ?? ''),
            'status'     => $data['status'] ?? 'upcoming',
            'description'=> sanitize($data['description'] ?? ''),
        ], '`id` = ?', [$id]);

        $this->logAudit('batch_updated', 'batch', $id);
        $this->redirectWith('batches', 'Batch updated.', 'success');
    }

    public function destroy(int $id): void
    {
        $this->authorize('batches.delete');
        db()->update('batches', ['deleted_at' => date('Y-m-d H:i:s')], '`id` = ?', [$id]);
        $this->logAudit('batch_deleted', 'batch', $id);
        $this->redirectWith('batches', 'Batch deleted.', 'success');
    }
}
