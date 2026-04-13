<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DepartmentController extends BaseController
{
    // ──────────────────────────────────────────────
    // LIST
    // ──────────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('departments.view');

        $search  = trim($this->input('search', ''));
        $instId  = (int)$this->input('institution_id', 0);
        $status  = $this->input('status', '');
        $type    = $this->input('type', '');
        $page    = max(1, (int)$this->input('page', 1));
        $perPage = 15;
        $offset  = ($page - 1) * $perPage;

        $where  = ['d.deleted_at IS NULL'];
        $params = [];

        if ($this->institutionId) {
            $where[]  = 'd.institution_id = ?';
            $params[] = $this->institutionId;
        } elseif ($instId) {
            $where[]  = 'd.institution_id = ?';
            $params[] = $instId;
        }
        if ($search) {
            $where[]  = '(d.name LIKE ? OR d.code LIKE ? OR d.hod_name LIKE ?)';
            $like     = "%{$search}%";
            $params   = array_merge($params, [$like, $like, $like]);
        }
        if ($status) { $where[] = 'd.status = ?';           $params[] = $status; }
        if ($type)   { $where[] = 'd.department_type = ?';  $params[] = $type; }

        $whereSQL = implode(' AND ', $where);

        $this->db->query("SELECT COUNT(*) AS total FROM departments d WHERE {$whereSQL}", $params);
        $total = (int)($this->db->fetch()['total'] ?? 0);
        $pages = max(1, (int)ceil($total / $perPage));

        $this->db->query("
            SELECT d.*,
                   i.name AS institution_name,
                   pd.name AS parent_name,
                   (SELECT COUNT(*) FROM courses c    WHERE c.department_id = d.id AND c.deleted_at IS NULL)  AS course_count,
                   (SELECT COUNT(*) FROM students s   WHERE s.department_id = d.id AND s.deleted_at IS NULL)  AS live_student_count,
                   (SELECT COUNT(*) FROM department_staff ds WHERE ds.department_id = d.id AND ds.staff_type = 'teaching' AND ds.status = 'active') AS live_faculty_count
            FROM departments d
            LEFT JOIN institutions i  ON i.id = d.institution_id
            LEFT JOIN departments pd  ON pd.id = d.parent_department_id
            WHERE {$whereSQL}
            ORDER BY d.department_type, d.name
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);
        $departments = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM institutions WHERE deleted_at IS NULL ORDER BY name");
        $institutions = $this->db->fetchAll();

        $this->view('departments/index', compact(
            'departments', 'institutions', 'search', 'instId',
            'status', 'type', 'page', 'pages', 'total', 'perPage'
        ));
    }

    // ──────────────────────────────────────────────
    // CREATE FORM
    // ──────────────────────────────────────────────
    public function create(): void
    {
        $this->authorize('departments.create');
        [$institutions, $parentDepts, $users, $courseTypes] = $this->_formData();
        $this->view('departments/form', [
            'department'   => null,
            'institutions' => $institutions,
            'parentDepts'  => $parentDepts,
            'users'        => $users,
            'courseTypes'  => $courseTypes,
            'programs'     => [],
            'rooms'        => [],
            'finance'      => null,
            'staff'        => [],
            'isEdit'       => false,
        ]);
    }

    // ──────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────
    public function store(): void
    {
        $this->authorize('departments.create');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data   = $_POST;
        $errors = $this->_validate($data);
        if ($errors) { $this->backWithErrors($errors, $data); return; }

        $instId = (int)($data['institution_id'] ?? $this->institutionId);
        if ($this->db->exists('departments', 'institution_id = ? AND code = ? AND deleted_at IS NULL', [$instId, strtoupper($data['code'])])) {
            $this->backWithErrors(['Department code already exists in this institution.'], $data);
            return;
        }

        $this->db->beginTransaction();
        try {
            $id = $this->db->insert('departments', $this->_buildRow($data, $instId));
            $this->_savePrograms($id, $data['programs'] ?? []);
            $this->_saveRooms($id, $data['rooms'] ?? []);
            $this->_saveFinance($id, $data);
            $this->_syncStaff($id, $data['staff_ids'] ?? [], $data['staff_types'] ?? []);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            appLog('Department store failed: ' . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create department. Please try again.'], $data);
            return;
        }

        $this->logAudit('create', 'department', $id);
        $this->redirectWith(url("departments/{$id}"), 'success', 'Department created successfully.');
    }

    // ──────────────────────────────────────────────
    // SHOW / DASHBOARD
    // ──────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->authorize('departments.view');
        $department = $this->_find($id);
        if (!$department) { $this->redirectWith(url('departments'), 'error', 'Department not found.'); return; }

        $this->db->query("SELECT * FROM department_programs WHERE department_id = ? ORDER BY program_level, program_name", [$id]);
        $programs = $this->db->fetchAll();

        $this->db->query("
            SELECT ds.*, u.first_name, u.last_name, u.email AS user_email, u.phone AS user_phone
            FROM department_staff ds
            LEFT JOIN users u ON u.id = ds.user_id
            WHERE ds.department_id = ?
            ORDER BY ds.staff_type, u.first_name
        ", [$id]);
        $staff = $this->db->fetchAll();

        $this->db->query("SELECT * FROM department_rooms WHERE department_id = ? ORDER BY room_type, room_name", [$id]);
        $rooms = $this->db->fetchAll();

        $this->db->query("SELECT * FROM department_finance WHERE department_id = ? ORDER BY financial_year DESC LIMIT 1", [$id]);
        $finance = $this->db->fetch() ?: [];

        $this->db->query("SELECT COUNT(*) AS cnt FROM courses  WHERE department_id = ? AND deleted_at IS NULL", [$id]);
        $courseCount = (int)($this->db->fetch()['cnt'] ?? 0);

        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM students WHERE department_id = ? AND deleted_at IS NULL", [$id]);
            $studentCount = (int)($this->db->fetch()['cnt'] ?? 0);
        } catch (\Exception $e) {
            $studentCount = 0;
        }

        $facultyCount  = count(array_filter($staff, fn($s) => $s['staff_type'] === 'teaching'));
        $nonTeachCount = count(array_filter($staff, fn($s) => $s['staff_type'] === 'non_teaching'));

        // Keep stored counts fresh
        $this->db->query("UPDATE departments SET faculty_count = ?, student_count = ? WHERE id = ?",
            [$facultyCount, $studentCount, $id]);

        // Sub-departments
        $this->db->query("SELECT id, name, code, status FROM departments WHERE parent_department_id = ? AND deleted_at IS NULL ORDER BY name", [$id]);
        $subDepts = $this->db->fetchAll();

        // Recent audit log
        $this->db->query("
            SELECT al.*, u.first_name, u.last_name FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            WHERE al.model_type = 'department' AND al.model_id = ?
            ORDER BY al.created_at DESC LIMIT 10
        ", [$id]);
        $auditLogs = $this->db->fetchAll();

        $this->view('departments/show', compact(
            'department', 'programs', 'staff', 'rooms', 'finance',
            'courseCount', 'studentCount', 'facultyCount', 'nonTeachCount',
            'subDepts', 'auditLogs'
        ));
    }

    // ──────────────────────────────────────────────
    // EDIT FORM
    // ──────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('departments.edit');
        $department = $this->_find($id);
        if (!$department) { $this->redirectWith(url('departments'), 'error', 'Department not found.'); return; }

        [$institutions, $parentDepts, $users, $courseTypes] = $this->_formData($id);

        $this->db->query("SELECT * FROM department_programs WHERE department_id = ? ORDER BY program_level", [$id]);
        $programs = $this->db->fetchAll();

        $this->db->query("SELECT * FROM department_rooms WHERE department_id = ? ORDER BY room_type, room_name", [$id]);
        $rooms = $this->db->fetchAll();

        $this->db->query("SELECT * FROM department_finance WHERE department_id = ? ORDER BY financial_year DESC LIMIT 1", [$id]);
        $finance = $this->db->fetch() ?: [];

        $this->db->query("SELECT ds.*, u.first_name, u.last_name FROM department_staff ds LEFT JOIN users u ON u.id = ds.user_id WHERE ds.department_id = ?", [$id]);
        $staff = $this->db->fetchAll();

        $this->view('departments/form', compact('department', 'institutions', 'parentDepts', 'users', 'courseTypes', 'programs', 'rooms', 'finance', 'staff') + ['isEdit' => true]);
    }

    // ──────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────
    public function update(int $id): void
    {
        $this->authorize('departments.edit');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $department = $this->_find($id);
        if (!$department) { $this->redirectWith(url('departments'), 'error', 'Department not found.'); return; }

        $data = $_POST;
        // Preserve institution + code
        $data['institution_id'] = $department['institution_id'];
        $data['code']           = $department['code'];

        $errors = $this->_validate($data, $id);
        if ($errors) { $this->backWithErrors($errors, $data); return; }

        $this->db->beginTransaction();
        try {
            $row = $this->_buildRow($data, $department['institution_id']);
            unset($row['institution_id'], $row['code'], $row['created_by']);
            $row['updated_by'] = $this->user['id'] ?? null;

            $this->db->update('departments', $row, 'id = ?', [$id]);
            $this->_savePrograms($id, $data['programs'] ?? []);
            $this->_saveRooms($id, $data['rooms'] ?? []);
            $this->_saveFinance($id, $data);
            $this->_syncStaff($id, $data['staff_ids'] ?? [], $data['staff_types'] ?? []);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            appLog('Department update failed: ' . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to update department.'], $data);
            return;
        }

        $this->logAudit('update', 'department', $id);
        $this->redirectWith(url("departments/{$id}"), 'success', 'Department updated successfully.');
    }

    // ──────────────────────────────────────────────
    // SOFT DELETE
    // ──────────────────────────────────────────────
    public function destroy(int $id): void
    {
        $this->authorize('departments.delete');
        if (!verifyCsrf()) { $this->redirectWith(url('departments'), 'error', 'Session expired.'); return; }

        if ($this->db->exists('courses', 'department_id = ? AND deleted_at IS NULL', [$id])) {
            $this->redirectWith(url("departments/{$id}"), 'error', 'Cannot delete — department has active courses.');
            return;
        }

        $this->db->update('departments', [
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->user['id'] ?? null,
        ], 'id = ?', [$id]);

        $this->logAudit('delete', 'department', $id);
        $this->redirectWith(url('departments'), 'success', 'Department deleted.');
    }

    // ──────────────────────────────────────────────
    // TOGGLE STATUS (AJAX)
    // ──────────────────────────────────────────────
    public function toggleStatus(int $id): void
    {
        $this->authorize('departments.edit');
        if (!verifyCsrf()) { $this->_json(['status' => 'error', 'message' => 'CSRF failed']); return; }

        $dept = $this->_find($id);
        if (!$dept) { $this->_json(['status' => 'error']); return; }

        $new = $dept['status'] === 'active' ? 'inactive' : 'active';
        $this->db->update('departments', ['status' => $new, 'updated_by' => $this->user['id'] ?? null], 'id = ?', [$id]);
        $this->_json(['status' => 'success', 'new_status' => $new]);
    }

    // ──────────────────────────────────────────────
    // AJAX — dropdown list for other modules
    // ──────────────────────────────────────────────
    public function ajaxList(): void
    {
        $instId = (int)$this->input('institution_id', $this->institutionId);
        $this->db->query(
            "SELECT id, name, code FROM departments WHERE institution_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name",
            [$instId]
        );
        $this->_json(['data' => $this->db->fetchAll()]);
    }

    // ══════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════

    private function _find(int $id): ?array
    {
        $this->db->query("
            SELECT d.*, i.name AS institution_name, pd.name AS parent_name
            FROM departments d
            LEFT JOIN institutions i  ON i.id = d.institution_id
            LEFT JOIN departments pd  ON pd.id = d.parent_department_id
            WHERE d.id = ? AND d.deleted_at IS NULL
        ", [$id]);
        return $this->db->fetch() ?: null;
    }

    private function _formData(int $excludeId = 0): array
    {
        $this->db->query("SELECT id, name FROM institutions WHERE deleted_at IS NULL ORDER BY name");
        $institutions = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, name, code FROM departments WHERE institution_id = ? AND deleted_at IS NULL AND id != ? ORDER BY name",
            [$this->institutionId, $excludeId]
        );
        $parentDepts = $this->db->fetchAll();

        if ($this->institutionId) {
            $this->db->query(
                "SELECT DISTINCT u.id, u.first_name, u.last_name, u.email
                 FROM users u
                 INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
                 WHERE u.is_active = 1
                 ORDER BY u.first_name, u.last_name",
                [$this->institutionId]
            );
        } else {
            $this->db->query(
                "SELECT id, first_name, last_name, email FROM users WHERE is_active = 1 ORDER BY first_name, last_name"
            );
        }
        $users = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, code, description, degree_type FROM course_types WHERE institution_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY code",
            [$this->institutionId]
        );
        $courseTypes = $this->db->fetchAll();

        return [$institutions, $parentDepts, $users, $courseTypes];
    }

    private function _validate(array $data, int $excludeId = 0): array
    {
        $errors = [];
        if (empty($data['institution_id'])) $errors[] = 'Institution is required.';
        if (empty(trim($data['name'] ?? '')))  $errors[] = 'Department name is required.';
        if (empty(trim($data['code'] ?? '')))  $errors[] = 'Department code is required.';
        if (strlen($data['name'] ?? '') > 255) $errors[] = 'Department name must not exceed 255 characters.';
        if (strlen($data['code'] ?? '') > 50)  $errors[] = 'Department code must not exceed 50 characters.';
        if (!in_array($data['department_type'] ?? '', ['academic', 'administrative', 'research'])) {
            $errors[] = 'Invalid department type selected.';
        }
        if (!empty($data['dept_email']) && !filter_var($data['dept_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid department email address.';
        }
        if (!empty($data['established_year'])) {
            $yr = (int)$data['established_year'];
            if ($yr < 1800 || $yr > ((int)date('Y') + 1)) $errors[] = 'Invalid established year.';
        }
        return $errors;
    }

    private function _buildRow(array $data, int $instId): array
    {
        return [
            'institution_id'            => $instId,
            'name'                      => sanitize($data['name']),
            'code'                      => strtoupper(sanitize($data['code'])),
            'department_type'           => $data['department_type'] ?? 'academic',
            'parent_department_id'      => $data['parent_department_id'] ?: null,
            'established_year'          => $data['established_year'] ?: null,
            'status'                    => $data['status'] ?? 'active',
            'description'               => sanitize($data['description'] ?? ''),
            // HOD & Contact
            'hod_name'                  => sanitize($data['hod_name'] ?? ''),
            'hod_employee_id'           => $data['hod_employee_id'] ?: null,
            'dept_email'                => sanitize($data['dept_email'] ?? ''),
            'dept_phone'                => sanitize($data['dept_phone'] ?? ''),
            'extension_number'          => sanitize($data['extension_number'] ?? ''),
            'office_block'              => sanitize($data['office_block'] ?? ''),
            'office_floor'              => sanitize($data['office_floor'] ?? ''),
            'office_room'               => sanitize($data['office_room'] ?? ''),
            'alt_contact_name'          => sanitize($data['alt_contact_name'] ?? ''),
            'alt_contact_phone'         => sanitize($data['alt_contact_phone'] ?? ''),
            // Academic
            'semester_pattern'          => $data['semester_pattern'] ?? 'semester',
            'credit_system'             => !empty($data['credit_system'])          ? 1 : 0,
            'grading_scheme'            => sanitize($data['grading_scheme'] ?? ''),
            'intake_capacity'           => $data['intake_capacity'] ? (int)$data['intake_capacity'] : null,
            'admission_quota'           => sanitize($data['admission_quota'] ?? ''),
            'counselling_code'          => sanitize($data['counselling_code'] ?? ''),
            // Staff roles
            'coordinator_id'            => $data['coordinator_id']          ?: null,
            'exam_coordinator_id'       => $data['exam_coordinator_id']     ?: null,
            'timetable_incharge_id'     => $data['timetable_incharge_id']   ?: null,
            // LMS
            'lms_allow_course_creation' => !empty($data['lms_allow_course_creation']) ? 1 : 0,
            'lms_attendance_required'   => !empty($data['lms_attendance_required'])   ? 1 : 0,
            'lms_internal_marks'        => !empty($data['lms_internal_marks'])         ? 1 : 0,
            'lms_lab_courses'           => !empty($data['lms_lab_courses'])            ? 1 : 0,
            'lms_project_dissertation'  => !empty($data['lms_project_dissertation'])   ? 1 : 0,
            'lms_hod_approval'          => !empty($data['lms_hod_approval'])           ? 1 : 0,
            // Workflow
            'allow_hod_login'           => !empty($data['allow_hod_login'])    ? 1 : 0,
            'approval_required'         => !empty($data['approval_required'])  ? 1 : 0,
            'created_by'                => $this->user['id'] ?? null,
        ];
    }

    private function _savePrograms(int $deptId, array $programs): void
    {
        $this->db->query("DELETE FROM department_programs WHERE department_id = ?", [$deptId]);
        foreach ($programs as $p) {
            if (empty(trim($p['program_name'] ?? ''))) continue;

            // Lookup course_type_id and program_level from master if code provided
            $ctId    = null;
            $level   = 'ug';
            $code    = sanitize($p['program_name']);
            $this->db->query(
                "SELECT id, course_category, degree_type FROM course_types WHERE institution_id = ? AND code = ? LIMIT 1",
                [$this->institutionId, $code]
            );
            $ct = $this->db->fetch();
            if ($ct) {
                $ctId  = $ct['id'];
                $level = $ct['course_category']; // certificate/ug/pg/school/research_scholar/mphil/phd
            }

            $this->db->insert('department_programs', [
                'department_id' => $deptId,
                'program_level' => $level,
                'degree_type'   => $p['degree_type'] ?? 'full_time',
                'program_name'  => $code,
                'intake_seats'  => $p['intake_seats'] ? (int)$p['intake_seats'] : null,
                'status'        => 'active',
            ]);
        }
    }

    private function _saveRooms(int $deptId, array $rooms): void
    {
        $this->db->query("DELETE FROM department_rooms WHERE department_id = ?", [$deptId]);
        foreach ($rooms as $r) {
            if (empty(trim($r['room_name'] ?? ''))) continue;
            $this->db->insert('department_rooms', [
                'department_id' => $deptId,
                'room_name'     => sanitize($r['room_name']),
                'room_type'     => $r['room_type']    ?? 'classroom',
                'block'         => sanitize($r['block']       ?? ''),
                'floor'         => sanitize($r['floor']       ?? ''),
                'room_number'   => sanitize($r['room_number'] ?? ''),
                'capacity'      => $r['capacity'] ? (int)$r['capacity'] : null,
                'has_projector' => !empty($r['has_projector']) ? 1 : 0,
                'has_ac'        => !empty($r['has_ac'])        ? 1 : 0,
                'status'        => 'active',
            ]);
        }
    }

    private function _saveFinance(int $deptId, array $data): void
    {
        if (empty($data['cost_center']) && empty($data['budget_allocation']) && empty($data['expense_head'])) return;
        $fy  = sanitize($data['financial_year'] ?? (date('Y') . '-' . substr((string)(date('Y') + 1), -2)));
        $row = [
            'cost_center'       => sanitize($data['cost_center']    ?? ''),
            'budget_allocation' => $data['budget_allocation'] ? (float)$data['budget_allocation'] : null,
            'expense_head'      => sanitize($data['expense_head']   ?? ''),
            'revenue_account'   => sanitize($data['revenue_account'] ?? ''),
            'fee_category'      => sanitize($data['fee_category']   ?? ''),
            'financial_year'    => $fy,
            'notes'             => sanitize($data['finance_notes']  ?? ''),
        ];
        $this->db->query("SELECT id FROM department_finance WHERE department_id = ? AND financial_year = ?", [$deptId, $fy]);
        $existing = $this->db->fetch();
        if ($existing) {
            $this->db->update('department_finance', $row, 'id = ?', [$existing['id']]);
        } else {
            $row['department_id'] = $deptId;
            $this->db->insert('department_finance', $row);
        }
    }

    private function _syncStaff(int $deptId, array $userIds, array $types): void
    {
        $this->db->query("DELETE FROM department_staff WHERE department_id = ?", [$deptId]);
        foreach ($userIds as $i => $uid) {
            $uid = (int)$uid;
            if (!$uid) continue;
            $this->db->query(
                "INSERT IGNORE INTO department_staff (department_id, user_id, staff_type) VALUES (?, ?, ?)",
                [$deptId, $uid, $types[$i] ?? 'teaching']
            );
        }
    }

    private function _json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
