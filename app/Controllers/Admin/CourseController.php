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

        $where  = "c.deleted_at IS NULL";
        $params = [];

        if ($this->institutionId) {
            $where   .= " AND c.institution_id = ?";
            $params[] = $this->institutionId;
        }
        if ($search) {
            $where   .= " AND (c.name LIKE ? OR c.code LIKE ?)";
            $s        = '%' . $search . '%';
            $params   = array_merge($params, [$s, $s]);
        }
        if ($deptId) { $where .= " AND c.department_id = ?"; $params[] = $deptId; }
        if ($status) { $where .= " AND c.status = ?";        $params[] = $status; }

        $page    = max(1, (int)($this->input('page') ?: 1));
        $perPage = config('app.per_page', 15);

        $sql = "SELECT c.*, d.name AS dept_name,
                       COUNT(DISTINCT b.id) AS batch_count,
                       COUNT(DISTINCT s.id) AS student_count
                FROM courses c
                LEFT JOIN departments d  ON d.id  = c.department_id
                LEFT JOIN batches     b  ON b.course_id = c.id AND b.deleted_at IS NULL
                LEFT JOIN students    s  ON s.course_id = c.id AND s.deleted_at IS NULL
                WHERE {$where}
                GROUP BY c.id
                ORDER BY c.name";

        $courses = $this->db->paginate($sql, $params, $page, $perPage);

        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('courses/index', compact('courses', 'departments', 'search', 'deptId', 'status'));
    }

    public function create(): void
    {
        $this->authorize('courses.create');

        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('courses/create', compact('departments'));
    }

    public function store(): void
    {
        $this->authorize('courses.create');

        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'name' => 'required|max:255',
            'code' => 'required|max:50',
        ]);
        if ($errors) { $this->backWithErrors(array_values($errors), $data); return; }

        // Unique code within institution
        if ($this->db->exists('courses', 'institution_id = ? AND code = ? AND deleted_at IS NULL', [$this->institutionId, strtoupper($data['code'])])) {
            $this->backWithErrors(['Course code already exists in this institution.'], $data);
            return;
        }

        $id = $this->db->insert('courses', [
            'institution_id'  => $this->institutionId,
            'department_id'   => $data['department_id'] ?: null,
            'name'            => sanitize($data['name']),
            'code'            => strtoupper(sanitize($data['code'])),
            'short_name'      => sanitize($data['short_name'] ?? ''),
            'description'     => sanitize($data['description'] ?? ''),
            'degree_type'     => $data['degree_type'] ?? 'ug',
            'duration_years'  => (float)($data['duration_years'] ?? 1),
            'total_semesters' => $data['total_semesters'] ? (int)$data['total_semesters'] : null,
            'total_seats'     => (int)($data['total_seats'] ?? 0),
            'eligibility'     => sanitize($data['eligibility'] ?? ''),
            'status'          => $data['status'] ?? 'active',
        ]);

        $this->logAudit('create', 'course', $id);
        $this->redirectWith(url('courses'), 'success', 'Course created successfully.');
    }

    public function edit(int $id): void
    {
        $this->authorize('courses.edit');

        $this->db->query("SELECT * FROM courses WHERE id = ? AND deleted_at IS NULL", [$id]);
        $course = $this->db->fetch();
        if (!$course) { $this->redirectWith(url('courses'), 'error', 'Course not found.'); return; }

        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('courses/edit', compact('course', 'departments'));
    }

    public function update(int $id): void
    {
        $this->authorize('courses.edit');

        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data   = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'code' => 'required']);
        if ($errors) { $this->backWithErrors(array_values($errors), $data); return; }

        $this->db->update('courses', [
            'department_id'   => $data['department_id'] ?: null,
            'name'            => sanitize($data['name']),
            'code'            => strtoupper(sanitize($data['code'])),
            'short_name'      => sanitize($data['short_name'] ?? ''),
            'description'     => sanitize($data['description'] ?? ''),
            'degree_type'     => $data['degree_type'] ?? 'ug',
            'duration_years'  => (float)($data['duration_years'] ?? 1),
            'total_semesters' => $data['total_semesters'] ? (int)$data['total_semesters'] : null,
            'total_seats'     => (int)($data['total_seats'] ?? 0),
            'eligibility'     => sanitize($data['eligibility'] ?? ''),
            'status'          => $data['status'] ?? 'active',
        ], '`id` = ?', [$id]);

        $this->logAudit('update', 'course', $id);
        $this->redirectWith(url('courses'), 'success', 'Course updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('courses.delete');

        if (!verifyCsrf()) { $this->redirectWith(url('courses'), 'error', 'Session expired.'); return; }

        $this->db->update('courses', ['deleted_at' => date('Y-m-d H:i:s')], '`id` = ?', [$id]);
        $this->logAudit('delete', 'course', $id);
        $this->redirectWith(url('courses'), 'success', 'Course deleted successfully.');
    }
}
