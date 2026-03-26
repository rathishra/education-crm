<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\Lead;

class LeadApiController extends BaseController
{
    private Lead $lead;

    public function __construct()
    {
        $this->lead = new Lead();
    }

    /**
     * GET /api/leads
     */
    public function index(): void
    {
        $filters = [
            'search'      => $this->input('search'),
            'status_id'   => $this->input('status_id'),
            'source_id'   => $this->input('source_id'),
            'assigned_to' => $this->input('assigned_to'),
            'priority'    => $this->input('priority'),
            'course_id'   => $this->input('course_id'),
            'date_from'   => $this->input('date_from'),
            'date_to'     => $this->input('date_to'),
        ];

        $page = (int)($this->input('page') ?: 1);
        $perPage = min((int)($this->input('per_page') ?: 15), 100);

        $leads = $this->lead->getListPaginated($page, $perPage, $filters);

        jsonResponse(['success' => true, 'data' => $leads]);
    }

    /**
     * GET /api/leads/:id
     */
    public function show(int $id): void
    {
        $lead = $this->lead->findWithDetails($id);
        if (!$lead) {
            jsonResponse(['success' => false, 'message' => 'Lead not found'], 404);
            return;
        }
        jsonResponse(['success' => true, 'data' => $lead]);
    }

    /**
     * POST /api/leads
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $this->postData();

        $errors = $this->validate($data, [
            'first_name' => 'required',
            'phone'      => 'required|phone',
        ]);

        if ($errors) {
            jsonResponse(['success' => false, 'errors' => $errors], 422);
            return;
        }

        $institutionId = $data['institution_id'] ?? session('institution_id');

        // Check duplicate
        $duplicate = $this->lead->checkDuplicate($data['phone'], $data['email'] ?? null);
        if ($duplicate) {
            jsonResponse([
                'success' => false,
                'message' => 'Duplicate lead found',
                'duplicate' => $duplicate,
            ], 409);
            return;
        }

        $insertData = [
            'institution_id'       => $institutionId,
            'lead_number'          => $this->lead->generateLeadNumber($institutionId),
            'first_name'           => sanitize($data['first_name']),
            'last_name'            => sanitize($data['last_name'] ?? ''),
            'phone'                => sanitize($data['phone']),
            'alternate_phone'      => sanitize($data['alternate_phone'] ?? ''),
            'email'                => sanitize($data['email'] ?? ''),
            'date_of_birth'        => $data['date_of_birth'] ?? null,
            'gender'               => $data['gender'] ?? null,
            'address_line1'        => sanitize($data['address_line1'] ?? ''),
            'address_line2'        => sanitize($data['address_line2'] ?? ''),
            'city'                 => sanitize($data['city'] ?? ''),
            'state'                => sanitize($data['state'] ?? ''),
            'pincode'              => sanitize($data['pincode'] ?? ''),
            'country'              => sanitize($data['country'] ?? 'India'),
            'qualification'        => sanitize($data['qualification'] ?? ''),
            'percentage'           => $data['percentage'] ?? null,
            'passing_year'         => $data['passing_year'] ?? null,
            'school_college'       => sanitize($data['school_college'] ?? ''),
            'course_interested_id' => $data['course_interested_id'] ?? null,
            'lead_source_id'       => $data['lead_source_id'] ?? null,
            'lead_status_id'       => $data['lead_status_id'] ?? $this->lead->getDefaultStatusId(),
            'assigned_to'          => $data['assigned_to'] ?? null,
            'priority'             => $data['priority'] ?? 'medium',
            'notes'                => sanitize($data['notes'] ?? ''),
            'created_by'           => auth()['id'] ?? null,
        ];

        $id = $this->lead->create($insertData);
        $lead = $this->lead->find($id);

        jsonResponse(['success' => true, 'data' => $lead, 'message' => 'Lead created'], 201);
    }

    /**
     * PUT /api/leads/:id
     */
    public function update(int $id): void
    {
        $lead = $this->lead->find($id);
        if (!$lead) {
            jsonResponse(['success' => false, 'message' => 'Lead not found'], 404);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: $this->postData();

        $updateData = [];
        $allowed = [
            'first_name', 'last_name', 'phone', 'alternate_phone', 'email',
            'date_of_birth', 'gender', 'address_line1', 'address_line2',
            'city', 'state', 'pincode', 'country', 'qualification',
            'percentage', 'passing_year', 'school_college',
            'course_interested_id', 'lead_source_id', 'lead_status_id',
            'assigned_to', 'priority', 'notes',
        ];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = is_string($data[$field]) ? sanitize($data[$field]) : $data[$field];
            }
        }

        if (empty($updateData)) {
            jsonResponse(['success' => false, 'message' => 'No fields to update'], 422);
            return;
        }

        $this->lead->update($id, $updateData);
        $updated = $this->lead->find($id);

        jsonResponse(['success' => true, 'data' => $updated, 'message' => 'Lead updated']);
    }

    /**
     * DELETE /api/leads/:id
     */
    public function destroy(int $id): void
    {
        $lead = $this->lead->find($id);
        if (!$lead) {
            jsonResponse(['success' => false, 'message' => 'Lead not found'], 404);
            return;
        }

        $this->lead->softDelete($id);
        jsonResponse(['success' => true, 'message' => 'Lead deleted']);
    }

    /**
     * GET /api/leads/statuses
     */
    public function statuses(): void
    {
        jsonResponse(['success' => true, 'data' => $this->lead->getStatuses()]);
    }

    /**
     * GET /api/leads/sources
     */
    public function sources(): void
    {
        jsonResponse(['success' => true, 'data' => $this->lead->getSources()]);
    }

    /**
     * POST /api/leads/:id/status
     */
    public function updateStatus(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $this->postData();

        if (empty($data['status_id'])) {
            jsonResponse(['success' => false, 'message' => 'status_id required'], 422);
            return;
        }

        $lead = $this->lead->find($id);
        if (!$lead) {
            jsonResponse(['success' => false, 'message' => 'Lead not found'], 404);
            return;
        }

        $this->lead->updateStatus($id, (int)$data['status_id'], auth()['id'] ?? null);
        jsonResponse(['success' => true, 'message' => 'Status updated']);
    }

    /**
     * POST /api/leads/:id/assign
     */
    public function assign(int $id): void
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $this->postData();

        if (empty($data['assigned_to'])) {
            jsonResponse(['success' => false, 'message' => 'assigned_to required'], 422);
            return;
        }

        $lead = $this->lead->find($id);
        if (!$lead) {
            jsonResponse(['success' => false, 'message' => 'Lead not found'], 404);
            return;
        }

        $this->lead->assignTo($id, (int)$data['assigned_to'], auth()['id'] ?? null);
        jsonResponse(['success' => true, 'message' => 'Lead assigned']);
    }
}
