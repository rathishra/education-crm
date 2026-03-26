<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Enquiry;
use App\Models\Lead;

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

    public function index(): void
    {
        $this->authorize('enquiries.view');

        $filters = [
            'search'   => $this->input('search'),
            'status'   => $this->input('status'),
            'course_id'=> $this->input('course_id'),
            'date_from'=> $this->input('date_from'),
            'date_to'  => $this->input('date_to'),
        ];

        if (!hasPermission('enquiries.view_all')) {
            $filters['only_mine'] = $this->user['id'];
        }

        $page     = max(1, (int)($this->input('page') ?: 1));
        $perPage  = (int)config('app.pagination.per_page', 15);
        $enquiries = $this->enquiry->getListPaginated($page, $perPage, $filters);

        $this->db->query("SELECT id, name FROM courses WHERE status = 'active' ORDER BY name");
        $courses = $this->db->fetchAll();

        $this->view('enquiries/index', [
            'enquiries' => $enquiries,
            'filters'   => $filters,
            'courses'   => $courses,
        ]);
    }

    public function create(): void
    {
        $this->authorize('enquiries.create');

        $this->db->query("SELECT id, name FROM courses WHERE status = 'active' ORDER BY name");
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM lead_sources WHERE is_active = 1 ORDER BY name");
        $sources = $this->db->fetchAll();

        $this->view('enquiries/create', [
            'courses' => $courses,
            'sources' => $sources,
        ]);
    }

    public function store(): void
    {
        $this->authorize('enquiries.create');

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'first_name' => 'required',
            'phone'      => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $institutionId = $this->institutionId;

        // Resolve source name from source_id
        $sourceName = null;
        if (!empty($data['source_id'])) {
            $this->db->query("SELECT name FROM lead_sources WHERE id = ?", [(int)$data['source_id']]);
            $src = $this->db->fetch();
            $sourceName = $src ? $src['name'] : null;
        }

        $insertData = [
            'institution_id'       => $institutionId,
            'enquiry_number'       => $this->enquiry->generateEnquiryNumber($institutionId),
            'first_name'           => sanitize($data['first_name']),
            'last_name'            => sanitize($data['last_name'] ?? ''),
            'phone'                => sanitize($data['phone']),
            'email'                => sanitize($data['email'] ?? ''),
            'course_interested_id' => !empty($data['course_interested_id']) ? (int)$data['course_interested_id'] : null,
            'source'               => $sourceName,
            'message'              => sanitize($data['message'] ?? ''),
            'status'               => 'new',
            'assigned_to'          => $this->user['id'],
        ];

        $id = $this->enquiry->create($insertData);
        $this->logAudit('enquiry_created', 'enquiry', $id);

        $this->redirectWith(url('enquiries/' . $id), 'success', 'Enquiry created successfully.');
    }

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

    public function edit(int $id): void
    {
        $this->authorize('enquiries.edit');

        $enquiry = $this->enquiry->find($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        $this->db->query("SELECT id, name FROM courses WHERE status = 'active' ORDER BY name");
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM lead_sources WHERE is_active = 1 ORDER BY name");
        $sources = $this->db->fetchAll();

        $this->view('enquiries/edit', [
            'enquiry' => $enquiry,
            'courses' => $courses,
            'sources' => $sources,
        ]);
    }

    public function update(int $id): void
    {
        $this->authorize('enquiries.edit');

        $enquiry = $this->enquiry->find($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'first_name' => 'required',
            'phone'      => 'required',
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        // Resolve source name
        $sourceName = $enquiry['source'];
        if (isset($data['source_id'])) {
            if (!empty($data['source_id'])) {
                $this->db->query("SELECT name FROM lead_sources WHERE id = ?", [(int)$data['source_id']]);
                $src = $this->db->fetch();
                $sourceName = $src ? $src['name'] : null;
            } else {
                $sourceName = null;
            }
        }

        $updateData = [
            'first_name'           => sanitize($data['first_name']),
            'last_name'            => sanitize($data['last_name'] ?? ''),
            'phone'                => sanitize($data['phone']),
            'email'                => sanitize($data['email'] ?? ''),
            'course_interested_id' => !empty($data['course_interested_id']) ? (int)$data['course_interested_id'] : null,
            'source'               => $sourceName,
            'message'              => sanitize($data['message'] ?? ''),
            'status'               => $data['status'] ?? $enquiry['status'],
        ];

        $this->enquiry->update($id, $updateData);
        $this->logAudit('enquiry_updated', 'enquiry', $id);

        $this->redirectWith(url('enquiries/' . $id), 'success', 'Enquiry updated successfully.');
    }

    public function delete(int $id): void
    {
        $this->authorize('enquiries.delete');

        $enquiry = $this->enquiry->find($id);
        if (!$enquiry) {
            $this->redirectWith(url('enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        // Hard delete (no deleted_at column in enquiries table)
        $this->db->query("DELETE FROM enquiries WHERE id = ?", [$id]);
        $this->logAudit('enquiry_deleted', 'enquiry', $id);

        $this->redirectWith(url('enquiries'), 'success', 'Enquiry deleted.');
    }

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
            $this->redirectWith(url('leads/' . $leadId), 'success', 'Enquiry converted to lead successfully.');
        } else {
            $this->redirectWith(url('enquiries/' . $id), 'error', 'Failed to convert enquiry.');
        }
    }
}
