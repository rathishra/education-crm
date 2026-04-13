<?php
namespace App\Controllers\Front;

use App\Controllers\BaseController;

/**
 * Public-facing Enquiry Controller
 * No authentication required — guest accessible
 */
class EnquiryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    // ─────────────────────────────────────────────
    // SHOW ENQUIRY FORM
    // ─────────────────────────────────────────────
    public function index(): void
    {
        $instId = (int)$this->input('institution_id', 0);

        // Active institutions
        $this->db->query(
            "SELECT id, name, short_name, code, logo, city, state, phone, email
             FROM institutions
             WHERE status = 'active' AND deleted_at IS NULL
             ORDER BY name"
        );
        $institutions = $this->db->fetchAll();

        // If a specific institution is selected, pre-load its departments & courses
        $departments = [];
        $courses     = [];
        if ($instId) {
            $this->db->query(
                "SELECT id, name, code FROM departments
                 WHERE institution_id = ? AND status = 'active' AND deleted_at IS NULL
                 ORDER BY name",
                [$instId]
            );
            $departments = $this->db->fetchAll();

            $courses = [];
            foreach ([
                "SELECT c.id, c.name, c.code, c.degree_type, c.duration_years, c.total_seats, c.fees_per_year, c.department_id, d.name AS dept_name FROM courses c LEFT JOIN departments d ON d.id = c.department_id WHERE c.institution_id = ? AND c.status = 'active' AND c.deleted_at IS NULL ORDER BY d.name, c.name",
                "SELECT c.id, c.name, c.code, c.degree_type, c.duration_years, c.total_seats, c.fees_per_year, c.department_id, d.name AS dept_name FROM courses c LEFT JOIN departments d ON d.id = c.department_id WHERE c.institution_id = ? AND c.status = 'active' ORDER BY d.name, c.name",
                "SELECT c.id, c.name, c.code, c.degree_type, c.duration_years, c.total_seats, c.fees_per_year, c.department_id, d.name AS dept_name FROM courses c LEFT JOIN departments d ON d.id = c.department_id WHERE c.institution_id = ? ORDER BY d.name, c.name",
            ] as $sql) {
                try {
                    $this->db->query($sql, [$instId]);
                    $courses = $this->db->fetchAll();
                    break;
                } catch (\Exception $e) { continue; }
            }
        }

        // Lead sources (for "How did you hear about us")
        try {
            $this->db->query("SELECT id, name FROM lead_sources WHERE is_active = 1 ORDER BY name");
            $sources = $this->db->fetchAll();
        } catch (\Exception $e) {
            $sources = [];
        }

        $pageTitle  = 'Student Enquiry';
        $selectedId = $instId;

        $this->view('front.enquiry', compact(
            'institutions', 'departments', 'courses',
            'sources', 'pageTitle', 'selectedId'
        ), 'public_form');
    }

    // ─────────────────────────────────────────────
    // AJAX — load departments by institution
    // ─────────────────────────────────────────────
    public function ajaxDepartments(): void
    {
        $instId = (int)$this->input('institution_id', 0);
        if (!$instId) { $this->_json([]); return; }

        $this->db->query(
            "SELECT id, name, code FROM departments
             WHERE institution_id = ? AND status = 'active' AND deleted_at IS NULL
             ORDER BY name",
            [$instId]
        );
        $this->_json($this->db->fetchAll());
    }

    // ─────────────────────────────────────────────
    // AJAX — load courses by institution (optionally filtered by dept)
    // ─────────────────────────────────────────────
    public function ajaxCourses(): void
    {
        $instId = (int)$this->input('institution_id', 0);
        $deptId = (int)$this->input('department_id', 0);
        if (!$instId) { $this->_json([]); return; }

        $params = [$instId];
        // When a dept filter is given: match that dept OR unassigned (NULL dept) courses
        $deptClause = '';
        if ($deptId) {
            $deptClause = ' AND (c.department_id = ? OR c.department_id IS NULL)';
            $params[]   = $deptId;
        }

        // Try with deleted_at filter first; fall back without it if column doesn't exist
        $rows = [];
        $queries = [
            "SELECT c.id, c.name, c.code, c.degree_type, c.duration_years,
                    c.total_seats, c.fees_per_year, c.department_id,
                    d.name AS dept_name
             FROM courses c
             LEFT JOIN departments d ON d.id = c.department_id
             WHERE c.institution_id = ? AND c.status = 'active' AND c.deleted_at IS NULL{$deptClause}
             ORDER BY c.name",
            // Fallback 1: no deleted_at filter
            "SELECT c.id, c.name, c.code, c.degree_type, c.duration_years,
                    c.total_seats, c.fees_per_year, c.department_id,
                    d.name AS dept_name
             FROM courses c
             LEFT JOIN departments d ON d.id = c.department_id
             WHERE c.institution_id = ? AND c.status = 'active'{$deptClause}
             ORDER BY c.name",
            // Fallback 2: any status, no deleted_at filter
            "SELECT c.id, c.name, c.code, c.degree_type, c.duration_years,
                    c.total_seats, c.fees_per_year, c.department_id,
                    d.name AS dept_name
             FROM courses c
             LEFT JOIN departments d ON d.id = c.department_id
             WHERE c.institution_id = ?{$deptClause}
             ORDER BY c.name",
        ];

        foreach ($queries as $sql) {
            try {
                $this->db->query($sql, $params);
                $rows = $this->db->fetchAll();
                break; // Use first successful non-empty result
            } catch (\Exception $e) {
                // Column may not exist — try next query
                continue;
            }
        }

        $this->_json($rows);
    }

    // ─────────────────────────────────────────────
    // HANDLE FORM SUBMISSION
    // ─────────────────────────────────────────────
    public function submit(): void
    {
        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please refresh and try again.']);
            return;
        }

        $data   = $_POST;
        $errors = [];

        // Validate required fields
        if (empty(trim($data['first_name'] ?? '')))        $errors[] = 'First name is required.';
        if (empty(trim($data['phone'] ?? '')))             $errors[] = 'Phone number is required.';
        if (empty($data['institution_id']))                $errors[] = 'Please select an institution.';
        if (empty($data['course_interested_id']))          $errors[] = 'Please select a course you are interested in.';
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (!empty($data['phone']) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $data['phone'])) {
            $errors[] = 'Please enter a valid phone number.';
        }

        if ($errors) {
            $this->backWithErrors($errors, $data);
            return;
        }

        $instId = (int)$data['institution_id'];

        // Validate institution exists
        $this->db->query("SELECT id, code FROM institutions WHERE id = ? AND status = 'active' AND deleted_at IS NULL", [$instId]);
        if (!$this->db->fetch()) {
            $this->backWithErrors(['Invalid institution selected.'], $data);
            return;
        }

        // Validate course belongs to institution
        $this->db->query(
            "SELECT id FROM courses WHERE id = ? AND institution_id = ? AND status = 'active' AND deleted_at IS NULL",
            [(int)$data['course_interested_id'], $instId]
        );
        if (!$this->db->fetch()) {
            $this->backWithErrors(['Selected course is not available for this institution.'], $data);
            return;
        }

        // Resolve source name from source_id
        $sourceName = 'Website';
        if (!empty($data['source_id'])) {
            try {
                $this->db->query("SELECT name FROM lead_sources WHERE id = ?", [(int)$data['source_id']]);
                $src = $this->db->fetch();
                if ($src) $sourceName = $src['name'];
            } catch (\Exception $e) {}
        }

        // Generate enquiry number
        $enqNumber = $this->_generateEnquiryNumber($instId);

        // Base insert data
        $insertData = [
            'institution_id'       => $instId,
            'enquiry_number'       => $enqNumber,
            'first_name'           => sanitize($data['first_name']),
            'last_name'            => sanitize($data['last_name'] ?? ''),
            'phone'                => sanitize($data['phone']),
            'email'                => !empty($data['email']) ? sanitize($data['email']) : null,
            'course_interested_id' => (int)$data['course_interested_id'],
            'source'               => $sourceName,
            'message'              => sanitize($data['message'] ?? ''),
            'status'               => 'new',
            'assigned_to'          => null,
        ];

        // Extended fields (migration 15 columns)
        $extended = [
            'gender'               => in_array($data['gender'] ?? '', ['male','female','other']) ? $data['gender'] : null,
            'date_of_birth'        => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            'department_id'        => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'academic_year'        => sanitize($data['academic_year'] ?? ''),
            'preferred_mode'       => in_array($data['preferred_mode'] ?? '', ['online','offline','hybrid']) ? $data['preferred_mode'] : 'offline',
            'campaign_name'        => sanitize($data['utm_campaign'] ?? ''),
            'reference_name'       => sanitize($data['reference_name'] ?? ''),
            'remarks'              => sanitize($data['remarks'] ?? $data['message'] ?? ''),
            'priority'             => 'warm',
            'hostel_required'      => !empty($data['hostel_required'])      ? 1 : 0,
            'transport_required'   => !empty($data['transport_required'])   ? 1 : 0,
            'scholarship_required' => !empty($data['scholarship_required']) ? 1 : 0,
            'pref2_department_id'  => !empty($data['pref2_department_id'])  ? (int)$data['pref2_department_id']  : null,
            'pref2_course_id'      => !empty($data['pref2_course_id'])      ? (int)$data['pref2_course_id']      : null,
            'pref2_academic_year'  => sanitize($data['pref2_academic_year'] ?? ''),
            'created_by'           => null,
        ];

        // Try inserting with extended fields; fall back to base-only on column error
        try {
            $this->db->insert('enquiries', array_merge($insertData, $extended));
        } catch (\Exception $e) {
            // Extended columns may not exist — insert base only
            try {
                $this->db->insert('enquiries', $insertData);
            } catch (\Exception $e2) {
                appLog('Public enquiry insert failed: ' . $e2->getMessage(), 'error');
                $this->backWithErrors(['Sorry, we could not process your enquiry. Please try again.'], $data);
                return;
            }
        }

        // Redirect to thank-you page
        flash('success', 'Your enquiry has been submitted! Our team will contact you within 24 hours.');
        flash('enq_name', sanitize($data['first_name'] . ' ' . ($data['last_name'] ?? '')));
        flash('enq_number', $enqNumber);
        flash('enq_phone', sanitize($data['phone']));

        header('Location: ' . url('enquire/thank-you'));
        exit;
    }

    // ─────────────────────────────────────────────
    // THANK YOU PAGE
    // ─────────────────────────────────────────────
    public function thankYou(): void
    {
        $name      = getFlash('enq_name')   ?? '';
        $number    = getFlash('enq_number') ?? '';
        $phone     = getFlash('enq_phone')  ?? '';
        $pageTitle = 'Enquiry Submitted';

        $this->view('front.enquiry_thankyou', compact('name', 'number', 'phone', 'pageTitle'), 'public_form');
    }

    // ─────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────
    private function _generateEnquiryNumber(int $instId): string
    {
        try {
            $this->db->query("SELECT code FROM institutions WHERE id = ?", [$instId]);
            $inst = $this->db->fetch();
            $code = strtoupper(substr(preg_replace('/[^A-Z0-9]/', '', $inst['code'] ?? 'GEN'), 0, 6));
        } catch (\Exception $e) {
            $code = 'GEN';
        }

        $date   = date('Ymd');
        $prefix = "ENQ-{$code}-{$date}-";

        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM enquiries WHERE enquiry_number LIKE ?",
            [$prefix . '%']
        );
        $count = (int)($this->db->fetch()['cnt'] ?? 0);
        return $prefix . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    private function _json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
