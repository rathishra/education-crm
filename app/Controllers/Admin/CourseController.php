<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CourseController extends BaseController
{
    public function index(): void
    {
        $this->authorize('courses.view');

        $search = $this->input('search');
        $deptId = $this->input('dept_id');
        $status = $this->input('status');

        $where = "c.deleted_at IS NULL";
        $params = [];

        $institutionId = session('institution_id');
        if ($institutionId) {
            $where .= " AND c.institution_id = ?";
            $params[] = $institutionId;
        }

        if ($search) {
            $where .= " AND (c.name LIKE ? OR c.code LIKE ?)";
            $s = '%' . $search . '%';
            $params = array_merge($params, [$s, $s]);
        }
        if ($deptId) { $where .= " AND c.department_id = ?"; $params[] = $deptId; }
        if ($status) { $where .= " AND c.status = ?"; $params[] = $status; }

        $db = db();
        $page = (int)($this->input('page') ?: 1);
        $perPage = config('app.per_page', 15);

        $sql = "SELECT c.*, d.name as dept_name,
                       COUNT(DISTINCT b.id) as batch_count,
                       COUNT(DISTINCT s.id) as student_count
                FROM courses c
                LEFT JOIN departments d ON d.id = c.department_id
                LEFT JOIN batches b ON b.course_id = c.id AND b.deleted_at IS NULL
                LEFT JOIN students s ON s.course_id = c.id AND s.deleted_at IS NULL
                WHERE {$where}
                GROUP BY c.id
                ORDER BY c.name";

        $courses = $db->paginate($sql, $params, $page, $perPage);

        $db->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name");
        $departments = $db->fetchAll();

        $this->view('courses/index', compact('courses', 'departments', 'search', 'deptId', 'status'));
    }

    public function create(): void
    {
        $this->authorize('courses.create');

        $db = db();
        $db->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name");
        $departments = $db->fetchAll();

        $this->view('courses/create', compact('departments'));
    }

    public function store(): void
    {
        $this->authorize('courses.create');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'name' => 'required',
            'code' => 'required',
        ]);

        if ($errors) { $this->backWithErrors($errors); return; }

        $institutionId = session('institution_id');

        $id = db()->insert('courses', [
            'institution_id' => $institutionId,
            'department_id'  => $data['department_id'] ?: null,
            'name'           => sanitize($data['name']),
            'code'           => strtoupper(sanitize($data['code'])),
            'short_name'     => sanitize($data['short_name'] ?? ''),
            'description'    => sanitize($data['description'] ?? ''),
            'duration_years' => (int)($data['duration_years'] ?? 1),
            'seats'          => (int)($data['seats'] ?? 0),
            'fees_per_year'  => (float)($data['fees_per_year'] ?? 0),
            'course_type'    => $data['course_type'] ?? 'ug',
            'status'         => $data['status'] ?? 'active',
            'created_by'     => auth()['id'],
        ]);

        $this->logAudit('course_created', 'course', $id);
        $this->redirectWith('courses', 'Course created successfully.', 'success');
    }

    public function edit(int $id): void
    {
        $this->authorize('courses.edit');

        db()->query("SELECT * FROM courses WHERE id = ? AND deleted_at IS NULL", [$id]);
        $course = db()->fetch();
        if (!$course) { $this->redirectWith('courses', 'Course not found.', 'error'); return; }

        db()->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name");
        $departments = db()->fetchAll();

        $this->view('courses/edit', compact('course', 'departments'));
    }

    public function update(int $id): void
    {
        $this->authorize('courses.edit');

        $data = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'code' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        db()->update('courses', [
            'department_id'  => $data['department_id'] ?: null,
            'name'           => sanitize($data['name']),
            'code'           => strtoupper(sanitize($data['code'])),
            'short_name'     => sanitize($data['short_name'] ?? ''),
            'description'    => sanitize($data['description'] ?? ''),
            'duration_years' => (int)($data['duration_years'] ?? 1),
            'seats'          => (int)($data['seats'] ?? 0),
            'fees_per_year'  => (float)($data['fees_per_year'] ?? 0),
            'course_type'    => $data['course_type'] ?? 'ug',
            'status'         => $data['status'] ?? 'active',
        ], '`id` = ?', [$id]);

        $this->logAudit('course_updated', 'course', $id);
        $this->redirectWith('courses', 'Course updated.', 'success');
    }

    public function destroy(int $id): void
    {
        $this->authorize('courses.delete');
        db()->update('courses', ['deleted_at' => date('Y-m-d H:i:s')], '`id` = ?', [$id]);
        $this->logAudit('course_deleted', 'course', $id);
        $this->redirectWith('courses', 'Course deleted.', 'success');
    }
}
