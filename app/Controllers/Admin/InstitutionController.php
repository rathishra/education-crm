<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class InstitutionController extends BaseController
{
    // ─────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('institutions.view');

        $search  = trim($this->input('search', ''));
        $type    = $this->input('type', '');
        $status  = $this->input('status', '');
        $orgId   = (int)$this->input('organization_id', 0);
        $page    = max(1, (int)$this->input('page', 1));
        $perPage = 15;
        $offset  = ($page - 1) * $perPage;

        $where  = ['i.deleted_at IS NULL'];
        $params = [];

        if ($search) {
            $where[]  = '(i.name LIKE ? OR i.code LIKE ? OR i.city LIKE ? OR i.short_name LIKE ?)';
            $like     = "%{$search}%";
            $params   = array_merge($params, [$like, $like, $like, $like]);
        }
        if ($type)   { $where[] = 'i.institution_type = ?'; $params[] = $type; }
        if ($status) { $where[] = 'i.status = ?';           $params[] = $status; }
        if ($orgId)  { $where[] = 'i.organization_id = ?';  $params[] = $orgId; }

        $sql = implode(' AND ', $where);

        $this->db->query("SELECT COUNT(*) AS total FROM institutions i WHERE {$sql}", $params);
        $total = (int)($this->db->fetch()['total'] ?? 0);
        $pages = max(1, (int)ceil($total / $perPage));

        $this->db->query("
            SELECT i.*,
                   o.organization_name AS org_name,
                   (SELECT COUNT(*) FROM departments d  WHERE d.institution_id = i.id AND d.deleted_at IS NULL) AS dept_count,
                   (SELECT COUNT(*) FROM courses c      WHERE c.institution_id = i.id AND c.deleted_at IS NULL) AS course_count,
                   (SELECT COUNT(*) FROM students s     WHERE s.institution_id = i.id AND s.deleted_at IS NULL) AS student_count,
                   (SELECT COUNT(*) FROM users u
                    INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = i.id
                    WHERE u.is_active = 1) AS user_count
            FROM institutions i
            LEFT JOIN organizations o ON o.id = i.organization_id
            WHERE {$sql}
            ORDER BY i.name
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);
        $institutions = $this->db->fetchAll();

        $this->db->query("SELECT id, organization_name FROM organizations WHERE deleted_at IS NULL ORDER BY organization_name");
        $organizations = $this->db->fetchAll();

        $this->view('institutions/index', compact(
            'institutions', 'organizations', 'search', 'type', 'status',
            'orgId', 'page', 'pages', 'total', 'perPage'
        ));
    }

    // ─────────────────────────────────────────────
    // CREATE FORM
    // ─────────────────────────────────────────────
    public function create(): void
    {
        $this->authorize('institutions.create');
        [$organizations] = $this->_formData();
        $this->view('institutions/form', [
            'inst'          => null,
            'organizations' => $organizations,
            'academic'      => [],
            'modules'       => [],
            'finance'       => [],
            'branding'      => [],
            'infra'         => [],
            'perms'         => [],
            'isEdit'        => false,
        ]);
    }

    // ─────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────
    public function store(): void
    {
        $this->authorize('institutions.create');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data   = $_POST;
        $errors = $this->_validate($data);
        if ($errors) { $this->backWithErrors($errors, $data); return; }

        // Unique code check
        $this->db->query("SELECT id FROM institutions WHERE code = ? AND deleted_at IS NULL", [strtoupper(trim($data['code']))]);
        if ($this->db->fetch()) {
            $this->backWithErrors(['Institution code already exists.'], $data);
            return;
        }

        $this->db->beginTransaction();
        try {
            // Logo upload
            $logo = $this->uploadFile('logo', 'logos');

            $instId = $this->db->insert('institutions', $this->_buildCoreRow($data, $logo));
            $this->_saveAcademic($instId, $data);
            $this->_saveModules($instId, $data);
            $this->_saveFinance($instId, $data);
            $this->_saveBranding($instId, $data);
            $this->_saveInfra($instId, $data);
            $this->_savePerms($instId, $data);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            appLog('Institution store failed: ' . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create institution. ' . $e->getMessage()], $data);
            return;
        }

        $this->logAudit('create', 'institution', $instId);
        $this->redirectWith(url("institutions/{$instId}"), 'success', 'Institution created successfully.');
    }

    // ─────────────────────────────────────────────
    // SHOW / DASHBOARD
    // ─────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->authorize('institutions.view');
        $inst = $this->_find($id);
        if (!$inst) { $this->redirectWith(url('institutions'), 'error', 'Institution not found.'); return; }

        // Load all related config
        $academic = $this->_loadRelated('institution_academic_config', $id);
        $modules  = $this->_loadRelated('institution_modules', $id);
        $finance  = $this->_loadRelated('institution_finance', $id);
        $branding = $this->_loadRelated('institution_branding', $id);
        $infra    = $this->_loadRelated('institution_infrastructure', $id);
        $perms    = $this->_loadRelated('institution_permissions', $id);

        // Live stats
        $this->db->query("SELECT COUNT(*) AS cnt FROM departments WHERE institution_id = ? AND deleted_at IS NULL", [$id]);
        $deptCount = (int)($this->db->fetch()['cnt'] ?? 0);

        $this->db->query("SELECT COUNT(*) AS cnt FROM courses WHERE institution_id = ? AND deleted_at IS NULL", [$id]);
        $courseCount = (int)($this->db->fetch()['cnt'] ?? 0);

        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM students WHERE institution_id = ? AND deleted_at IS NULL", [$id]);
            $studentCount = (int)($this->db->fetch()['cnt'] ?? 0);
        } catch (\Exception $e) { $studentCount = 0; }

        try {
            $this->db->query("SELECT COUNT(*) AS cnt FROM leads WHERE institution_id = ? AND deleted_at IS NULL", [$id]);
            $leadCount = (int)($this->db->fetch()['cnt'] ?? 0);
        } catch (\Exception $e) { $leadCount = 0; }

        $this->db->query("
            SELECT COUNT(DISTINCT ur.user_id) AS cnt FROM user_roles ur
            WHERE ur.institution_id = ?", [$id]);
        $userCount = (int)($this->db->fetch()['cnt'] ?? 0);

        // Departments list
        $this->db->query("SELECT id, name, code, department_type, status, faculty_count, student_count FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name LIMIT 20", [$id]);
        $departments = $this->db->fetchAll();

        // Recent audit
        $this->db->query("
            SELECT al.*, u.first_name, u.last_name FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            WHERE al.model_type = 'institution' AND al.model_id = ?
            ORDER BY al.created_at DESC LIMIT 10
        ", [$id]);
        $auditLogs = $this->db->fetchAll();

        $this->view('institutions/show', compact(
            'inst', 'academic', 'modules', 'finance', 'branding', 'infra', 'perms',
            'deptCount', 'courseCount', 'studentCount', 'leadCount', 'userCount',
            'departments', 'auditLogs'
        ));
    }

    // ─────────────────────────────────────────────
    // EDIT FORM
    // ─────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('institutions.edit');
        $inst = $this->_find($id);
        if (!$inst) { $this->redirectWith(url('institutions'), 'error', 'Institution not found.'); return; }

        [$organizations] = $this->_formData();
        $academic = $this->_loadRelated('institution_academic_config', $id);
        $modules  = $this->_loadRelated('institution_modules', $id);
        $finance  = $this->_loadRelated('institution_finance', $id);
        $branding = $this->_loadRelated('institution_branding', $id);
        $infra    = $this->_loadRelated('institution_infrastructure', $id);
        $perms    = $this->_loadRelated('institution_permissions', $id);

        $this->view('institutions/form', compact(
            'inst', 'organizations', 'academic', 'modules', 'finance',
            'branding', 'infra', 'perms'
        ) + ['isEdit' => true]);
    }

    // ─────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────
    public function update(int $id): void
    {
        $this->authorize('institutions.edit');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $inst = $this->_find($id);
        if (!$inst) { $this->redirectWith(url('institutions'), 'error', 'Institution not found.'); return; }

        $data = $_POST;
        $data['code'] = $inst['code']; // Code is immutable after creation

        $errors = $this->_validate($data, $id);
        if ($errors) { $this->backWithErrors($errors, $data); return; }

        $this->db->beginTransaction();
        try {
            $logo    = $this->uploadFile('logo', 'logos');
            $row     = $this->_buildCoreRow($data, $logo);
            unset($row['code'], $row['organization_id'], $row['created_by']);
            $row['updated_by'] = $this->user['id'] ?? null;
            if (!$logo) unset($row['logo']); // Keep existing logo if none uploaded

            $this->db->update('institutions', $row, 'id = ?', [$id]);
            $this->_saveAcademic($id, $data);
            $this->_saveModules($id, $data);
            $this->_saveFinance($id, $data);
            $this->_saveBranding($id, $data);
            $this->_saveInfra($id, $data);
            $this->_savePerms($id, $data);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            appLog('Institution update failed: ' . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to update institution.'], $data);
            return;
        }

        $this->logAudit('update', 'institution', $id);
        $this->redirectWith(url("institutions/{$id}"), 'success', 'Institution updated successfully.');
    }

    // ─────────────────────────────────────────────
    // SOFT DELETE
    // ─────────────────────────────────────────────
    public function destroy(int $id): void
    {
        $this->authorize('institutions.delete');
        if (!verifyCsrf()) { $this->redirectWith(url('institutions'), 'error', 'Session expired.'); return; }

        $deps = [];
        $this->db->query("SELECT COUNT(*) AS c FROM departments WHERE institution_id = ? AND deleted_at IS NULL", [$id]);
        if ((int)($this->db->fetch()['c'] ?? 0)) $deps[] = 'departments';
        try {
            $this->db->query("SELECT COUNT(*) AS c FROM students WHERE institution_id = ? AND deleted_at IS NULL", [$id]);
            if ((int)($this->db->fetch()['c'] ?? 0)) $deps[] = 'students';
        } catch (\Exception $e) {}

        if ($deps) {
            $this->redirectWith(url("institutions/{$id}"), 'error',
                'Cannot delete — institution has linked: ' . implode(', ', $deps) . '. Deactivate instead.');
            return;
        }

        $this->db->update('institutions', [
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->user['id'] ?? null,
        ], 'id = ?', [$id]);

        $this->logAudit('delete', 'institution', $id);
        $this->redirectWith(url('institutions'), 'success', 'Institution deleted.');
    }

    // ─────────────────────────────────────────────
    // TOGGLE STATUS (AJAX)
    // ─────────────────────────────────────────────
    public function toggleStatus(int $id): void
    {
        $this->authorize('institutions.edit');
        if (!verifyCsrf()) { $this->_json(['status' => 'error']); return; }
        $inst = $this->_find($id);
        if (!$inst) { $this->_json(['status' => 'error']); return; }
        $new = $inst['status'] === 'active' ? 'inactive' : 'active';
        $this->db->update('institutions', ['status' => $new], 'id = ?', [$id]);
        $this->_json(['status' => 'success', 'new_status' => $new]);
    }

    // ─────────────────────────────────────────────
    // AJAX – switcher list
    // ─────────────────────────────────────────────
    public function ajaxList(): void
    {
        $this->db->query(
            "SELECT i.id, i.name, i.short_name, i.code, i.logo FROM institutions i
             INNER JOIN user_roles ur ON ur.institution_id = i.id AND ur.user_id = ?
             WHERE i.status = 'active' AND i.deleted_at IS NULL
             GROUP BY i.id ORDER BY i.name",
            [$this->user['id'] ?? 0]
        );
        $this->_json(['data' => $this->db->fetchAll()]);
    }

    // ══════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════

    private function _find(int $id): ?array
    {
        $this->db->query("
            SELECT i.*, o.organization_name AS org_name
            FROM institutions i
            LEFT JOIN organizations o ON o.id = i.organization_id
            WHERE i.id = ? AND i.deleted_at IS NULL
        ", [$id]);
        return $this->db->fetch() ?: null;
    }

    private function _formData(): array
    {
        $this->db->query("SELECT id, organization_name FROM organizations WHERE deleted_at IS NULL ORDER BY organization_name");
        return [$this->db->fetchAll()];
    }

    private function _loadRelated(string $table, int $instId): array
    {
        try {
            $this->db->query("SELECT * FROM {$table} WHERE institution_id = ? LIMIT 1", [$instId]);
            return $this->db->fetch() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function _validate(array $data, int $excludeId = 0): array
    {
        $errors = [];
        if (empty($data['organization_id'])) $errors[] = 'Organization / Trust is required.';
        if (empty(trim($data['name'] ?? '')))  $errors[] = 'Institution name is required.';
        if (empty(trim($data['code'] ?? '')))  $errors[] = 'Institution code is required.';
        if (strlen($data['name'] ?? '') > 255) $errors[] = 'Institution name is too long.';
        if (!in_array($data['institution_type'] ?? '', ['college','school','university','training_institute','polytechnic','deemed_university','autonomous','other'])) {
            $errors[] = 'Invalid institution type.';
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }
        if (!empty($data['support_email']) && !filter_var($data['support_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid support email address.';
        }
        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid website URL.';
        }
        if (!empty($data['established_year'])) {
            $yr = (int)$data['established_year'];
            if ($yr < 1800 || $yr > (int)date('Y') + 1) $errors[] = 'Invalid established year.';
        }
        return $errors;
    }

    private function _buildCoreRow(array $d, ?array $logo): array
    {
        $row = [
            'organization_id'  => (int)($d['organization_id'] ?? 0),
            'name'             => sanitize($d['name']),
            'short_name'       => sanitize($d['short_name'] ?? ''),
            'code'             => strtoupper(sanitize($d['code'])),
            'institution_type' => $d['institution_type'] ?? 'college',
            'parent_org_name'  => sanitize($d['parent_org_name'] ?? ''),
            'established_year' => $d['established_year'] ?: null,
            'status'           => $d['status'] ?? 'active',
            'description'      => sanitize($d['description'] ?? ''),
            // Contact
            'email'            => sanitize($d['email'] ?? ''),
            'support_email'    => sanitize($d['support_email'] ?? ''),
            'phone'            => sanitize($d['phone'] ?? ''),
            'alt_phone'        => sanitize($d['alt_phone'] ?? ''),
            'fax'              => sanitize($d['fax'] ?? ''),
            'admission_phone'  => sanitize($d['admission_phone'] ?? ''),
            'website'          => sanitize($d['website'] ?? ''),
            // Address
            'address_line1'    => sanitize($d['address_line1'] ?? ''),
            'address_line2'    => sanitize($d['address_line2'] ?? ''),
            'city'             => sanitize($d['city'] ?? ''),
            'state'            => sanitize($d['state'] ?? ''),
            'country'          => sanitize($d['country'] ?? 'India'),
            'pincode'          => sanitize($d['pincode'] ?? ''),
            'latitude'         => $d['latitude']  ? (float)$d['latitude']  : null,
            'longitude'        => $d['longitude'] ? (float)$d['longitude'] : null,
            // Administration
            'principal_name'   => sanitize($d['principal_name'] ?? ''),
            'director_name'    => sanitize($d['director_name'] ?? ''),
            'registrar_name'   => sanitize($d['registrar_name'] ?? ''),
            'coe_name'         => sanitize($d['coe_name'] ?? ''),
            'admission_head'   => sanitize($d['admission_head'] ?? ''),
            'finance_officer'  => sanitize($d['finance_officer'] ?? ''),
            'affiliation_number' => sanitize($d['affiliation_number'] ?? ''),
            'affiliation_body'   => sanitize($d['affiliation_body'] ?? ''),
            'created_by'       => $this->user['id'] ?? null,
        ];
        if ($logo) $row['logo'] = $logo['file_path'];
        return $row;
    }

    private function _upsert(string $table, int $instId, array $row): void
    {
        $this->db->query("SELECT id FROM {$table} WHERE institution_id = ? LIMIT 1", [$instId]);
        $existing = $this->db->fetch();
        if ($existing) {
            $this->db->update($table, $row, 'institution_id = ?', [$instId]);
        } else {
            $row['institution_id'] = $instId;
            $this->db->insert($table, $row);
        }
    }

    private function _saveAcademic(int $id, array $d): void
    {
        $this->_upsert('institution_academic_config', $id, [
            'academic_year_start_month' => (int)($d['academic_year_start_month'] ?? 6),
            'academic_pattern'          => $d['academic_pattern'] ?? 'semester',
            'credit_system'             => !empty($d['credit_system'])       ? 1 : 0,
            'grading_system'            => sanitize($d['grading_system'] ?? ''),
            'max_credits_per_semester'  => $d['max_credits_per_semester'] ? (int)$d['max_credits_per_semester'] : null,
            'attendance_policy'         => (int)($d['attendance_policy'] ?? 75),
            'internal_assessment'       => !empty($d['internal_assessment']) ? 1 : 0,
            'internal_marks_percentage' => (int)($d['internal_marks_percentage'] ?? 30),
            'pass_marks_percentage'     => (int)($d['pass_marks_percentage']     ?? 50),
            'arrear_policy'             => sanitize($d['arrear_policy'] ?? ''),
        ]);
    }

    private function _saveModules(int $id, array $d): void
    {
        $this->_upsert('institution_modules', $id, [
            'erp_departments'      => !empty($d['erp_departments'])      ? 1 : 0,
            'erp_programs'         => !empty($d['erp_programs'])         ? 1 : 0,
            'erp_courses'          => !empty($d['erp_courses'])          ? 1 : 0,
            'erp_admissions'       => !empty($d['erp_admissions'])       ? 1 : 0,
            'erp_fees'             => !empty($d['erp_fees'])             ? 1 : 0,
            'erp_exams'            => !empty($d['erp_exams'])            ? 1 : 0,
            'erp_hr'               => !empty($d['erp_hr'])               ? 1 : 0,
            'erp_hostel'           => !empty($d['erp_hostel'])           ? 1 : 0,
            'erp_transport'        => !empty($d['erp_transport'])        ? 1 : 0,
            'erp_library'          => !empty($d['erp_library'])          ? 1 : 0,
            'erp_placement'        => !empty($d['erp_placement'])        ? 1 : 0,
            'lms_enabled'          => !empty($d['lms_enabled'])          ? 1 : 0,
            'lms_online_classes'   => !empty($d['lms_online_classes'])   ? 1 : 0,
            'lms_assignments'      => !empty($d['lms_assignments'])      ? 1 : 0,
            'lms_quiz'             => !empty($d['lms_quiz'])             ? 1 : 0,
            'lms_discussion_forum' => !empty($d['lms_discussion_forum']) ? 1 : 0,
            'lms_attendance'       => !empty($d['lms_attendance'])       ? 1 : 0,
            'lms_gradebook'        => !empty($d['lms_gradebook'])        ? 1 : 0,
        ]);
    }

    private function _saveFinance(int $id, array $d): void
    {
        $this->_upsert('institution_finance', $id, [
            'base_currency'        => sanitize($d['base_currency']        ?? 'INR'),
            'currency_symbol'      => sanitize($d['currency_symbol']      ?? '₹'),
            'fee_collection_mode'  => $d['fee_collection_mode']           ?? 'both',
            'tax_enabled'          => !empty($d['tax_enabled'])            ? 1 : 0,
            'tax_percentage'       => $d['tax_percentage']  ? (float)$d['tax_percentage']  : null,
            'default_fee_template' => sanitize($d['default_fee_template'] ?? ''),
            'finance_start_month'  => (int)($d['finance_start_month']     ?? 4),
            'cost_center_code'     => sanitize($d['cost_center_code']     ?? ''),
            'bank_name'            => sanitize($d['bank_name']            ?? ''),
            'bank_account'         => sanitize($d['bank_account']         ?? ''),
            'bank_ifsc'            => sanitize($d['bank_ifsc']            ?? ''),
            'payment_gateway'      => sanitize($d['payment_gateway']      ?? ''),
        ]);
    }

    private function _saveBranding(int $id, array $d): void
    {
        $row = [
            'primary_color'      => sanitize($d['primary_color']      ?? '#2c3e8c'),
            'secondary_color'    => sanitize($d['secondary_color']     ?? '#e74c3c'),
            'theme'              => $d['theme']                        ?? 'light',
            'report_header_name' => sanitize($d['report_header_name'] ?? ''),
            'footer_text'        => sanitize($d['footer_text']        ?? ''),
        ];
        $banner = $this->uploadFile('login_banner', 'banners');
        if ($banner) $row['login_banner'] = $banner['file_path'];
        $favicon = $this->uploadFile('favicon', 'logos');
        if ($favicon) $row['favicon'] = $favicon['file_path'];
        $this->_upsert('institution_branding', $id, $row);
    }

    private function _saveInfra(int $id, array $d): void
    {
        $this->_upsert('institution_infrastructure', $id, [
            'campus_type'         => $d['campus_type']       ?? 'single',
            'total_buildings'     => $d['total_buildings']   ? (int)$d['total_buildings']   : null,
            'total_classrooms'    => $d['total_classrooms']  ? (int)$d['total_classrooms']  : null,
            'total_labs'          => $d['total_labs']        ? (int)$d['total_labs']        : null,
            'total_departments'   => $d['total_departments'] ? (int)$d['total_departments'] : null,
            'total_area_sqft'     => $d['total_area_sqft']   ? (int)$d['total_area_sqft']   : null,
            'library_available'   => !empty($d['library_available'])   ? 1 : 0,
            'hostel_available'    => !empty($d['hostel_available'])    ? 1 : 0,
            'hostel_boys_seats'   => $d['hostel_boys_seats']  ? (int)$d['hostel_boys_seats']  : null,
            'hostel_girls_seats'  => $d['hostel_girls_seats'] ? (int)$d['hostel_girls_seats'] : null,
            'transport_available' => !empty($d['transport_available']) ? 1 : 0,
            'canteen_available'   => !empty($d['canteen_available'])   ? 1 : 0,
            'sports_available'    => !empty($d['sports_available'])    ? 1 : 0,
        ]);
    }

    private function _savePerms(int $id, array $d): void
    {
        $this->_upsert('institution_permissions', $id, [
            'allow_multi_campus'        => !empty($d['allow_multi_campus'])        ? 1 : 0,
            'allow_multi_department'    => !empty($d['allow_multi_department'])    ? 1 : 0,
            'allow_multi_academic_year' => !empty($d['allow_multi_academic_year']) ? 1 : 0,
            'data_isolation'            => !empty($d['data_isolation'])            ? 1 : 0,
            'allow_hod_login'           => !empty($d['allow_hod_login'])           ? 1 : 0,
            'allow_student_portal'      => !empty($d['allow_student_portal'])      ? 1 : 0,
            'allow_parent_portal'       => !empty($d['allow_parent_portal'])       ? 1 : 0,
            'allow_faculty_portal'      => !empty($d['allow_faculty_portal'])      ? 1 : 0,
        ]);
    }

    private function _json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
