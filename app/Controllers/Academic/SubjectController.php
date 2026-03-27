<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class SubjectController extends BaseController
{
    public function index(): void
    {
        $search  = trim($_GET['search']  ?? '');
        $type    = trim($_GET['type']    ?? '');
        $sem     = trim($_GET['semester'] ?? '');
        $status  = $_GET['status'] ?? '';

        $where  = "s.deleted_at IS NULL AND s.institution_id = ?";
        $params = [$this->institutionId];

        if ($search !== '') {
            $where   .= " AND (s.subject_name LIKE ? OR s.subject_code LIKE ?)";
            $like     = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        if ($type !== '') {
            $where   .= " AND s.subject_type = ?";
            $params[] = $type;
        }
        if ($sem !== '') {
            $where   .= " AND s.semester = ?";
            $params[] = $sem;
        }
        if ($status !== '') {
            $where   .= " AND s.status = ?";
            $params[] = $status;
        }

        $this->db->query(
            "SELECT s.*, d.name AS dept_name, c.name AS course_name
             FROM subjects s
             LEFT JOIN departments d ON d.id = s.department_id
             LEFT JOIN courses     c ON c.id = s.course_id
             WHERE {$where}
             ORDER BY s.semester ASC, s.subject_code ASC",
            $params
        );
        $subjects = $this->db->fetchAll();

        // Stats
        $this->db->query(
            "SELECT
                COUNT(*)                     AS total,
                SUM(status='active')         AS active,
                SUM(subject_type='theory')   AS theory,
                SUM(subject_type='lab')      AS lab,
                SUM(is_elective=1)           AS elective
             FROM subjects WHERE deleted_at IS NULL AND institution_id = ?",
            [$this->institutionId]
        );
        $stats = $this->db->fetch();

        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('academic/subjects/index', compact('subjects', 'stats', 'departments', 'search', 'type', 'sem', 'status'));
    }

    public function create(): void
    {
        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM courses WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->view('academic/subjects/create', compact('departments', 'courses'));
    }

    public function store(): void
    {
        verifyCsrf();

        $data = [
            'institution_id'  => $this->institutionId,
            'department_id'   => (int)$this->input('department_id') ?: null,
            'course_id'       => (int)$this->input('course_id') ?: null,
            'subject_code'    => strtoupper(trim($this->input('subject_code', ''))),
            'subject_name'    => trim($this->input('subject_name', '')),
            'short_name'      => trim($this->input('short_name', '')) ?: null,
            'subject_type'    => $this->input('subject_type', 'theory'),
            'is_elective'     => (int)(bool)$this->input('is_elective'),
            'credits'         => (float)$this->input('credits', 3),
            'hours_per_week'  => (int)$this->input('hours_per_week', 3),
            'theory_hours'    => (int)$this->input('theory_hours', 3),
            'lab_hours'       => (int)$this->input('lab_hours', 0),
            'tutorial_hours'  => (int)$this->input('tutorial_hours', 0),
            'semester'        => (int)$this->input('semester') ?: null,
            'regulation'      => trim($this->input('regulation', '')) ?: null,
            'description'     => trim($this->input('description', '')) ?: null,
            'status'          => 'active',
            'created_by'      => $this->user['id'],
        ];

        $errors = [];
        if (empty($data['subject_code'])) $errors['subject_code'] = 'Subject code is required.';
        if (empty($data['subject_name'])) $errors['subject_name'] = 'Subject name is required.';

        if (empty($errors)) {
            $this->db->query(
                "SELECT id FROM subjects WHERE institution_id = ? AND subject_code = ? AND deleted_at IS NULL",
                [$this->institutionId, $data['subject_code']]
            );
            if ($this->db->fetch()) {
                $errors['subject_code'] = 'Subject code already exists.';
            }
        }

        if (!empty($errors)) {
            return $this->backWithErrors($errors);
        }

        $this->db->insert('subjects', $data);
        $this->logAudit('subject_create', 'subjects', $this->db->lastInsertId());
        $this->redirectWith(url('academic/subjects'), 'success', 'Subject created successfully.');
    }

    public function edit(int $id): void
    {
        $this->db->query(
            "SELECT * FROM subjects WHERE id = ? AND institution_id = ? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        $subject = $this->db->fetch();
        if (!$subject) {
            $this->redirectWith(url('academic/subjects'), 'error', 'Subject not found.');
            return;
        }

        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM courses WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->view('academic/subjects/edit', compact('subject', 'departments', 'courses'));
    }

    public function update(int $id): void
    {
        verifyCsrf();

        $this->db->query(
            "SELECT id FROM subjects WHERE id = ? AND institution_id = ? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            $this->redirectWith(url('academic/subjects'), 'error', 'Subject not found.');
            return;
        }

        $code = strtoupper(trim($this->input('subject_code', '')));

        $errors = [];
        if (empty($code)) $errors['subject_code'] = 'Subject code is required.';
        if (empty(trim($this->input('subject_name', '')))) $errors['subject_name'] = 'Subject name is required.';

        if (empty($errors)) {
            $this->db->query(
                "SELECT id FROM subjects WHERE institution_id = ? AND subject_code = ? AND deleted_at IS NULL AND id != ?",
                [$this->institutionId, $code, $id]
            );
            if ($this->db->fetch()) $errors['subject_code'] = 'Subject code already in use.';
        }

        if (!empty($errors)) {
            return $this->backWithErrors($errors);
        }

        $this->db->update('subjects', [
            'department_id'  => (int)$this->input('department_id') ?: null,
            'course_id'      => (int)$this->input('course_id') ?: null,
            'subject_code'   => $code,
            'subject_name'   => trim($this->input('subject_name', '')),
            'short_name'     => trim($this->input('short_name', '')) ?: null,
            'subject_type'   => $this->input('subject_type', 'theory'),
            'is_elective'    => (int)(bool)$this->input('is_elective'),
            'credits'        => (float)$this->input('credits', 3),
            'hours_per_week' => (int)$this->input('hours_per_week', 3),
            'theory_hours'   => (int)$this->input('theory_hours', 3),
            'lab_hours'      => (int)$this->input('lab_hours', 0),
            'tutorial_hours' => (int)$this->input('tutorial_hours', 0),
            'semester'       => (int)$this->input('semester') ?: null,
            'regulation'     => trim($this->input('regulation', '')) ?: null,
            'description'    => trim($this->input('description', '')) ?: null,
            'status'         => $this->input('status', 'active'),
        ], 'id = ?', [$id]);

        $this->logAudit('subject_update', 'subjects', $id);
        $this->redirectWith(url('academic/subjects'), 'success', 'Subject updated successfully.');
    }

    public function delete(int $id): void
    {
        verifyCsrf();
        $this->db->query(
            "UPDATE subjects SET deleted_at = NOW() WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->logAudit('subject_delete', 'subjects', $id);
        $this->redirectWith(url('academic/subjects'), 'success', 'Subject deleted.');
    }

    // AJAX: get subjects by course
    public function ajaxByCourse(): void
    {
        $courseId = (int)($_GET['course_id'] ?? 0);
        $semester = (int)($_GET['semester'] ?? 0);
        $w = "institution_id = ? AND status = 'active' AND deleted_at IS NULL";
        $p = [$this->institutionId];
        if ($courseId) { $w .= " AND course_id = ?"; $p[] = $courseId; }
        if ($semester) { $w .= " AND semester = ?";  $p[] = $semester; }
        $this->db->query("SELECT id, subject_code, subject_name, credits FROM subjects WHERE {$w} ORDER BY semester, subject_code", $p);
        header('Content-Type: application/json');
        echo json_encode($this->db->fetchAll());
        exit;
    }
}
