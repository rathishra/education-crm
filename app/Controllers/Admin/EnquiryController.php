<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Enquiry;
use App\Models\Lead;
use App\Models\Admission;

class EnquiryController extends BaseController
{
    private Enquiry $enquiry;

    public function __construct()
    {
        parent::__construct();
        $this->enquiry = new Enquiry();
        if ($this->institutionId) {
            $this->enquiry->setInstitutionScope($this->institutionId);
        }
    }

    // -------------------------------------------------------------------------
    // 1. index — paginated list + stats
    // -------------------------------------------------------------------------
    public function index(): void
    {
        $this->authorize('enquiries.view');

        $filters = [
            'search'       => $this->input('search'),
            'status'       => $this->input('status'),
            'priority'     => $this->input('priority'),
            'course_id'    => $this->input('course_id'),
            'department_id'=> $this->input('department_id'),
            'counselor_id' => $this->input('counselor_id'),
            'source'       => $this->input('source'),
            'date_from'    => $this->input('date_from'),
            'date_to'      => $this->input('date_to'),
        ];

        if (!hasPermission('enquiries.view_all')) {
            $filters['only_mine'] = $this->user['id'];
        }

        $page      = max(1, (int)($this->input('page') ?: 1));
        $perPage   = (int)config('app.pagination.per_page', 15);
        $enquiries = $this->enquiry->getListPaginated($page, $perPage, $filters);
        $stats     = $this->enquiry->getStats($this->institutionId);

        // Counselors for filter dropdown (users with a role in this institution)
        $this->db->query(
            "SELECT DISTINCT u.id, CONCAT(u.first_name,' ',u.last_name) AS name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $counselors = $this->db->fetchAll();

        // Distinct sources used by this institution
        $this->db->query(
            "SELECT DISTINCT source FROM enquiries
             WHERE institution_id = ? AND source IS NOT NULL AND source <> ''
             ORDER BY source",
            [$this->institutionId]
        );
        $sources = array_column($this->db->fetchAll(), 'source');

        $this->view('enquiries/index', [
            'enquiries'  => $enquiries,
            'filters'    => $filters,
            'stats'      => $stats,
            'counselors' => $counselors,
            'sources'    => $sources,
        ]);
    }

    // -------------------------------------------------------------------------
    // 2. create — show form
    // -------------------------------------------------------------------------
    public function create(): void
    {
        $this->authorize('enquiries.create');

        $this->db->query(
            "SELECT id, name FROM institutions WHERE status = 'active' AND deleted_at IS NULL ORDER BY name"
        );
        $institutions = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name",
            [$this->institutionId]
        );
        $departments = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, name FROM courses WHERE status = 'active' ORDER BY name"
        );
        $courses = $this->db->fetchAll();

        $this->db->query(
            "SELECT DISTINCT u.id, CONCAT(u.first_name,' ',u.last_name) AS name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $counselors = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, name FROM lead_sources WHERE is_active = 1 ORDER BY name"
        );
        $sources = $this->db->fetchAll();

        $this->view('enquiries/create', [
            'institutions' => $institutions,
            'departments'  => $departments,
            'courses'      => $courses,
            'counselors'   => $counselors,
            'sources'      => $sources,
            'institutionId'=> $this->institutionId,
        ]);
    }

    // -------------------------------------------------------------------------
    // 3. store — validate and insert
    // -------------------------------------------------------------------------
    public function store(): void
    {
        $this->authorize('enquiries.create');

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'first_name'           => 'required',
            'phone'                => 'required',
            'course_interested_id' => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $institutionId = !empty($data['institution_id'])
            ? (int)$data['institution_id']
            : $this->institutionId;

        // Resolve source name from source_id
        $sourceName = null;
        if (!empty($data['source_id'])) {
            $this->db->query(
                "SELECT name FROM lead_sources WHERE id = ?",
                [(int)$data['source_id']]
            );
            $src        = $this->db->fetch();
            $sourceName = $src ? $src['name'] : null;
        }

        // Duplicate check (non-blocking — flash warning but continue)
        $duplicate = null;
        $phone     = sanitize($data['phone']);
        $email     = sanitize($data['email'] ?? '');
        if ($phone || $email) {
            $duplicate = $this->enquiry->checkDuplicate($phone, $email, $institutionId);
        }

        // Base columns — always exist
        $insertData = [
            'institution_id'       => $institutionId,
            'enquiry_number'       => $this->enquiry->generateEnquiryNumber($institutionId),
            'first_name'           => sanitize($data['first_name']),
            'last_name'            => sanitize($data['last_name'] ?? ''),
            'phone'                => $phone,
            'email'                => $email ?: null,
            'course_interested_id' => !empty($data['course_interested_id']) ? (int)$data['course_interested_id'] : null,
            'source'               => $sourceName,
            'message'              => sanitize($data['message'] ?? $data['remarks'] ?? ''),
            'status'               => 'new',
            'assigned_to'          => $this->user['id'],
        ];

        // Extended columns — added by migration 15_enquiries_enhanced.sql
        // These are merged in only if the columns exist; INSERT will fail gracefully
        // if migration has not yet been run (columns silently skipped is not possible
        // in MySQL, so we detect existence via a schema check).
        if ($this->enquiry->hasExtendedColumns()) {
            $insertData += [
                'gender'               => !empty($data['gender']) ? $data['gender'] : null,
                'date_of_birth'        => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                'department_id'        => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                'academic_year'        => sanitize($data['academic_year'] ?? ''),
                'preferred_mode'       => in_array($data['preferred_mode'] ?? '', ['online','offline','hybrid'])
                                            ? $data['preferred_mode'] : 'offline',
                'campaign_name'        => sanitize($data['campaign_name'] ?? ''),
                'reference_name'       => sanitize($data['reference_name'] ?? ''),
                'counselor_id'         => !empty($data['counselor_id']) ? (int)$data['counselor_id'] : null,
                'remarks'              => sanitize($data['remarks'] ?? ''),
                'priority'             => in_array($data['priority'] ?? '', ['hot','warm','cold'])
                                            ? $data['priority'] : 'warm',
                'next_followup_date'   => !empty($data['next_followup_date']) ? $data['next_followup_date'] : null,
                'followup_mode'        => in_array($data['followup_mode'] ?? '', ['call','whatsapp','visit','email'])
                                            ? $data['followup_mode'] : null,
                'hostel_required'      => !empty($data['hostel_required']) ? 1 : 0,
                'transport_required'   => !empty($data['transport_required']) ? 1 : 0,
                'scholarship_required' => !empty($data['scholarship_required']) ? 1 : 0,
                'pref2_department_id'  => !empty($data['pref2_department_id']) ? (int)$data['pref2_department_id'] : null,
                'pref2_course_id'      => !empty($data['pref2_course_id']) ? (int)$data['pref2_course_id'] : null,
                'pref2_academic_year'  => sanitize($data['pref2_academic_year'] ?? ''),
                'created_by'           => $this->user['id'],
            ];
        }

        $id = $this->enquiry->create($insertData);
        $this->logAudit('enquiry_created', 'enquiry', $id);

        if ($duplicate) {
            $this->redirectWith(
                url('enquiries/' . $id),
                'warning',
                'Enquiry saved, but a possible duplicate was found: '
                    . e($duplicate['name']) . ' (' . e($duplicate['enquiry_number']) . ')'
            );
        } else {
            $this->redirectWith(url('enquiries/' . $id), 'success', 'Enquiry created successfully.');
        }
    }

    // -------------------------------------------------------------------------
    // 4. show — view details
    // -------------------------------------------------------------------------
    public function show(int $id): void
    {
        $this->authorize('enquiries.view');

        $enquiry = $this->enquiry->findWithDetails($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        $this->view('enquiries/show', ['enquiry' => $enquiry]);
    }

    // -------------------------------------------------------------------------
    // 5. edit — show edit form
    // -------------------------------------------------------------------------
    public function edit(int $id): void
    {
        $this->authorize('enquiries.edit');

        $enquiry = $this->enquiry->findWithDetails($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        $this->db->query(
            "SELECT id, name FROM institutions WHERE status = 'active' AND deleted_at IS NULL ORDER BY name"
        );
        $institutions = $this->db->fetchAll();

        $instId = $enquiry['institution_id'] ?? $this->institutionId;

        $this->db->query(
            "SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name",
            [$instId]
        );
        $departments = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, name FROM courses WHERE status = 'active' ORDER BY name"
        );
        $courses = $this->db->fetchAll();

        $this->db->query(
            "SELECT DISTINCT u.id, CONCAT(u.first_name,' ',u.last_name) AS name
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$instId]
        );
        $counselors = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, name FROM lead_sources WHERE is_active = 1 ORDER BY name"
        );
        $sources = $this->db->fetchAll();

        $this->view('enquiries/edit', [
            'enquiry'      => $enquiry,
            'institutions' => $institutions,
            'departments'  => $departments,
            'courses'      => $courses,
            'counselors'   => $counselors,
            'sources'      => $sources,
            'institutionId'=> $instId,
        ]);
    }

    // -------------------------------------------------------------------------
    // 6. update — process edit form
    // -------------------------------------------------------------------------
    public function update(int $id): void
    {
        $this->authorize('enquiries.edit');

        $enquiry = $this->enquiry->findWithDetails($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'first_name'           => 'required',
            'phone'                => 'required',
            'course_interested_id' => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        // Resolve source name from source_id
        $sourceName = $enquiry['source'];
        if (array_key_exists('source_id', $data)) {
            if (!empty($data['source_id'])) {
                $this->db->query(
                    "SELECT name FROM lead_sources WHERE id = ?",
                    [(int)$data['source_id']]
                );
                $src        = $this->db->fetch();
                $sourceName = $src ? $src['name'] : null;
            } else {
                $sourceName = null;
            }
        }

        // Base columns — always exist
        $updateData = [
            'first_name'           => sanitize($data['first_name']),
            'last_name'            => sanitize($data['last_name'] ?? ''),
            'phone'                => sanitize($data['phone']),
            'email'                => sanitize($data['email'] ?? '') ?: null,
            'course_interested_id' => !empty($data['course_interested_id']) ? (int)$data['course_interested_id'] : null,
            'source'               => $sourceName,
            'message'              => sanitize($data['message'] ?? $data['remarks'] ?? ''),
            'status'               => in_array($data['status'] ?? '', ['new','contacted','interested','not_interested','converted','closed'])
                                        ? $data['status'] : $enquiry['status'],
            'assigned_to'          => !empty($data['assigned_to'])
                                        ? (int)$data['assigned_to']
                                        : $enquiry['assigned_to'],
        ];

        // Extended columns — only if migration has been applied
        if ($this->enquiry->hasExtendedColumns()) {
            $updateData += [
                'gender'               => !empty($data['gender']) ? $data['gender'] : null,
                'date_of_birth'        => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
                'department_id'        => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                'academic_year'        => sanitize($data['academic_year'] ?? ''),
                'preferred_mode'       => in_array($data['preferred_mode'] ?? '', ['online','offline','hybrid'])
                                            ? $data['preferred_mode'] : 'offline',
                'campaign_name'        => sanitize($data['campaign_name'] ?? ''),
                'reference_name'       => sanitize($data['reference_name'] ?? ''),
                'counselor_id'         => !empty($data['counselor_id']) ? (int)$data['counselor_id'] : null,
                'remarks'              => sanitize($data['remarks'] ?? ''),
                'priority'             => in_array($data['priority'] ?? '', ['hot','warm','cold'])
                                            ? $data['priority'] : ($enquiry['priority'] ?? 'warm'),
                'next_followup_date'   => !empty($data['next_followup_date']) ? $data['next_followup_date'] : null,
                'followup_mode'        => in_array($data['followup_mode'] ?? '', ['call','whatsapp','visit','email'])
                                            ? $data['followup_mode'] : null,
                'hostel_required'      => !empty($data['hostel_required']) ? 1 : 0,
                'transport_required'   => !empty($data['transport_required']) ? 1 : 0,
                'scholarship_required' => !empty($data['scholarship_required']) ? 1 : 0,
                'pref2_department_id'  => !empty($data['pref2_department_id']) ? (int)$data['pref2_department_id'] : null,
                'pref2_course_id'      => !empty($data['pref2_course_id']) ? (int)$data['pref2_course_id'] : null,
                'pref2_academic_year'  => sanitize($data['pref2_academic_year'] ?? ''),
            ];
        }

        $this->enquiry->update($id, $updateData);
        $this->logAudit('enquiry_updated', 'enquiry', $id);

        $this->redirectWith(url('enquiries/' . $id), 'success', 'Enquiry updated successfully.');
    }

    // -------------------------------------------------------------------------
    // 7. destroy — soft delete
    // -------------------------------------------------------------------------
    public function destroy(int $id): void
    {
        $this->authorize('enquiries.delete');

        $enquiry = $this->enquiry->find($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        $this->enquiry->softDelete($id);
        $this->logAudit('enquiry_deleted', 'enquiry', $id);

        $this->redirectWith(url('enquiries'), 'success', 'Enquiry deleted.');
    }

    // -------------------------------------------------------------------------
    // 8. convertToLead
    // -------------------------------------------------------------------------
    public function convertToLead(int $id): void
    {
        $this->authorize('enquiries.convert');

        $enquiry = $this->enquiry->find($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        if ($enquiry['status'] === 'converted') {
            $this->redirectWith(url('enquiries/' . $id), 'warning', 'Enquiry already converted.');
            return;
        }

        $leadId = $this->enquiry->convertToLead($id);
        if ($leadId) {
            $this->logAudit('enquiry_converted', 'enquiry', $id, ['lead_id' => $leadId]);
            $this->redirectWith(
                url('leads/' . $leadId),
                'success',
                'Enquiry converted to lead successfully.'
            );
        } else {
            $this->redirectWith(url('enquiries/' . $id), 'error', 'Failed to convert enquiry.');
        }
    }

    // -------------------------------------------------------------------------
    // 9. convertToAdmission
    // -------------------------------------------------------------------------
    public function convertToAdmission(int $id): void
    {
        $this->authorize('admissions.create');

        $enquiry = $this->enquiry->findWithDetails($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        if ($enquiry['status'] === 'converted') {
            $this->redirectWith(url('enquiries/' . $id), 'warning', 'Enquiry already converted.');
            return;
        }

        $admissionModel  = new Admission();
        $admissionNumber = $admissionModel->generateAdmissionNumber($this->institutionId);

        $admissionId = $this->db->insert('admissions', [
            'institution_id'   => $this->institutionId,
            'admission_number' => $admissionNumber,
            'first_name'       => $enquiry['first_name'],
            'last_name'        => $enquiry['last_name'] ?? '',
            'email'            => $enquiry['email'] ?? '',
            'phone'            => $enquiry['phone'],
            'gender'           => $enquiry['gender'] ?? null,
            'date_of_birth'    => $enquiry['date_of_birth'] ?? null,
            'course_id'        => $enquiry['course_interested_id'] ?? null,
            'department_id'    => $enquiry['department_id'] ?? null,
            'nationality'      => 'Indian',
            'admission_type'   => 'regular',
            'application_date' => date('Y-m-d'),
            'status'           => 'applied',
            'source'           => $enquiry['source'] ?? null,
            'remarks'          => $enquiry['remarks'] ?: ($enquiry['message'] ?? ''),
            'created_by'       => $this->user['id'],
        ]);

        if ($admissionId) {
            $this->db->query(
                "UPDATE enquiries SET status = 'converted', updated_at = NOW() WHERE id = ?",
                [$id]
            );
            $this->logAudit('enquiry_to_admission', 'enquiry', $id, ['admission_id' => $admissionId]);
            $this->redirectWith(
                url('admissions/' . $admissionId),
                'success',
                'Enquiry converted to admission successfully. Admission #' . $admissionNumber
            );
        } else {
            $this->redirectWith(url('enquiries/' . $id), 'error', 'Failed to create admission from enquiry.');
        }
    }

    // -------------------------------------------------------------------------
    // 10. export — CSV download
    // -------------------------------------------------------------------------
    public function export(): void
    {
        $this->authorize('enquiries.view');

        $filters = [
            'search'        => $this->input('search'),
            'status'        => $this->input('status'),
            'priority'      => $this->input('priority'),
            'counselor_id'  => $this->input('counselor_id'),
            'source'        => $this->input('source'),
            'date_from'     => $this->input('date_from'),
            'date_to'       => $this->input('date_to'),
        ];
        if (!hasPermission('enquiries.view_all')) {
            $filters['only_mine'] = $this->user['id'];
        }

        $result = $this->enquiry->getListPaginated(1, 10000, $filters);
        $rows   = $result['data'] ?? [];

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="enquiries_' . date('Y-m-d') . '.csv"');
        $fh = fopen('php://output', 'w');
        fputcsv($fh, ['Enquiry #', 'Name', 'Phone', 'Email', 'Course', 'Source', 'Priority', 'Status', 'Counselor', 'Date']);
        foreach ($rows as $r) {
            fputcsv($fh, [
                $r['enquiry_number'] ?? '',
                trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                $r['phone'] ?? '',
                $r['email'] ?? '',
                $r['course_name'] ?? '',
                $r['source'] ?? '',
                $r['priority'] ?? '',
                $r['status'] ?? '',
                $r['counselor_name'] ?? $r['assigned_to_name'] ?? '',
                $r['created_at'] ?? '',
            ]);
        }
        fclose($fh);
        exit;
    }

    // -------------------------------------------------------------------------
    // 11. bulk — AJAX bulk actions (POST JSON)
    // -------------------------------------------------------------------------
    public function bulk(): void
    {
        header('Content-Type: application/json');
        $this->authorize('enquiries.view');

        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = $body['action'] ?? '';
        $ids    = array_filter(array_map('intval', $body['ids'] ?? []));
        $value  = $body['value'] ?? '';

        if (empty($ids)) {
            echo json_encode(['status' => 'error', 'message' => 'No records selected.']); exit;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'status':
                $allowed = ['new','contacted','interested','not_interested','closed'];
                if (!in_array($value, $allowed)) {
                    echo json_encode(['status'=>'error','message'=>'Invalid status.']); exit;
                }
                $this->authorize('enquiries.edit');
                $this->db->query(
                    "UPDATE enquiries SET status=?, updated_at=NOW() WHERE id IN ($placeholders) AND institution_id=?",
                    array_merge([$value], $ids, [$this->institutionId])
                );
                echo json_encode(['status'=>'success','message'=>count($ids).' enquiry(s) updated.']);
                break;

            case 'convert_to_lead':
                $this->authorize('enquiries.convert');
                $converted = 0;
                foreach ($ids as $eid) {
                    $this->db->query("SELECT * FROM enquiries WHERE id=? AND institution_id=? AND status!='converted' AND deleted_at IS NULL", [$eid, $this->institutionId]);
                    $enq = $this->db->fetch();
                    if ($enq && $this->enquiry->convertToLead($eid)) {
                        $converted++;
                    }
                }
                echo json_encode(['status'=>'success','message'=>"$converted enquiry(s) converted to leads."]);
                break;

            case 'delete':
                $this->authorize('enquiries.delete');
                $this->db->query(
                    "UPDATE enquiries SET deleted_at=NOW() WHERE id IN ($placeholders) AND institution_id=?",
                    array_merge($ids, [$this->institutionId])
                );
                echo json_encode(['status'=>'success','message'=>count($ids).' enquiry(s) deleted.']);
                break;

            default:
                echo json_encode(['status'=>'error','message'=>'Unknown action.']);
        }
        exit;
    }

    // -------------------------------------------------------------------------
    // 12. quickStatus — inline AJAX status change (POST JSON)
    // -------------------------------------------------------------------------
    public function quickStatus(int $id): void
    {
        header('Content-Type: application/json');
        $this->authorize('enquiries.edit');

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $status  = $body['status'] ?? '';
        $allowed = ['new','contacted','interested','not_interested','closed'];

        if (!in_array($status, $allowed)) {
            echo json_encode(['status'=>'error','message'=>'Invalid status.']); exit;
        }

        $this->db->query(
            "UPDATE enquiries SET status=?, updated_at=NOW() WHERE id=? AND institution_id=?",
            [$status, $id, $this->institutionId]
        );
        echo json_encode(['status'=>'success','message'=>'Status updated.']);
        exit;
    }

    // -------------------------------------------------------------------------
    // 13. checkDuplicate — AJAX endpoint
    // GET /enquiries/check-duplicate?phone=X&email=Y&institution_id=Z&exclude_id=N
    // -------------------------------------------------------------------------
    public function checkDuplicate(): void
    {
        header('Content-Type: application/json');

        $phone         = trim($this->input('phone') ?? '');
        $email         = trim($this->input('email') ?? '');
        $institutionId = !empty($this->input('institution_id'))
            ? (int)$this->input('institution_id')
            : $this->institutionId;
        $excludeId     = (int)($this->input('exclude_id') ?? 0);

        if ($phone === '' && $email === '') {
            echo json_encode(['duplicate' => false]);
            return;
        }

        $duplicate = $this->enquiry->checkDuplicate($phone, $email, $institutionId, $excludeId);

        if ($duplicate) {
            echo json_encode([
                'duplicate'      => true,
                'field'          => $duplicate['field'],
                'enquiry_number' => $duplicate['enquiry_number'],
                'name'           => $duplicate['name'],
                'id'             => $duplicate['id'],
            ]);
        } else {
            echo json_encode(['duplicate' => false]);
        }
    }

    // -------------------------------------------------------------------------
    // 11. ajaxDepartments — AJAX: GET ?institution_id=X
    // -------------------------------------------------------------------------
    public function ajaxDepartments(): void
    {
        header('Content-Type: application/json');

        $institutionId = (int)($this->input('institution_id') ?? $this->institutionId);
        if (!$institutionId) {
            echo json_encode([]);
            return;
        }

        $this->db->query(
            "SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name",
            [$institutionId]
        );
        echo json_encode($this->db->fetchAll());
    }

    // -------------------------------------------------------------------------
    // 12. ajaxCourses — AJAX: GET ?department_id=X OR ?institution_id=X
    // -------------------------------------------------------------------------
    public function ajaxCourses(): void
    {
        header('Content-Type: application/json');

        $departmentId  = (int)($this->input('department_id') ?? 0);
        $institutionId = (int)($this->input('institution_id') ?? 0);

        if ($departmentId) {
            $this->db->query(
                "SELECT c.id, c.name
                 FROM courses c
                 INNER JOIN department_courses dc ON dc.course_id = c.id
                 WHERE dc.department_id = ? AND c.status = 'active'
                 ORDER BY c.name",
                [$departmentId]
            );
        } elseif ($institutionId) {
            $this->db->query(
                "SELECT id, name FROM courses WHERE status = 'active' ORDER BY name"
            );
        } else {
            echo json_encode([]);
            return;
        }

        echo json_encode($this->db->fetchAll());
    }
}
