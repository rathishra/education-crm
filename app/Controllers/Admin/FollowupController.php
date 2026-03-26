<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Followup;
use App\Models\User;

class FollowupController extends BaseController
{
    private Followup $followupModel;
    private User     $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->followupModel = new Followup();
        $this->userModel     = new User();
        $this->followupModel->setInstitutionScope($this->institutionId);
    }

    // =========================================================
    // INDEX
    // =========================================================

    /**
     * List follow-ups with tab navigation, filters, stats and counselor breakdown.
     */
    public function index(): void
    {
        $this->authorize('followups.view');

        $page = max(1, (int)($this->input('page') ?? 1));
        $tab  = $this->input('tab') ?? 'all';

        $filters = [
            'tab'          => $tab,
            'search'       => $this->input('search'),
            'status'       => $this->input('status'),
            'followup_mode'=> $this->input('followup_mode'),
            'priority'     => $this->input('priority'),
            'assigned_to'  => $this->input('assigned_to'),
            'enquiry_id'   => $this->input('enquiry_id'),
            'lead_id'      => $this->input('lead_id'),
            'date_from'    => $this->input('date_from'),
            'date_to'      => $this->input('date_to'),
        ];

        // Scope to own followups if the user cannot view all
        $statsUserId = null;
        if (!hasPermission('followups.view_all')) {
            $filters['only_mine'] = $this->user['id'];
            $statsUserId          = $this->user['id'];
        }

        $followups    = $this->followupModel->getListPaginated($page, 20, $filters);
        $stats        = $this->followupModel->getStats($statsUserId);
        $counselors   = $this->userModel->getCounselors($this->institutionId);

        $counselorWise = [];
        if (hasPermission('followups.view_all')) {
            $counselorWise = $this->followupModel->getCounselorWise($this->institutionId);
        }

        $this->view('followups/index', [
            'pageTitle'     => 'Follow-up Management',
            'followups'     => $followups,
            'filters'       => $filters,
            'tab'           => $tab,
            'stats'         => $stats,
            'counselors'    => $counselors,
            'counselorWise' => $counselorWise,
        ]);
    }

    // =========================================================
    // SHOW
    // =========================================================

    /**
     * Display a single follow-up with full entity details and history.
     */
    public function show(int $id): void
    {
        $this->authorize('followups.view');

        $followup = $this->followupModel->findWithDetails($id);
        if (!$followup) {
            $this->redirectWith(url('followups'), 'error', 'Follow-up not found.');
            return;
        }

        $this->view('followups/show', [
            'pageTitle' => 'Follow-up Details',
            'followup'  => $followup,
        ]);
    }

    // =========================================================
    // CREATE
    // =========================================================

    /**
     * Display the create form, pre-filling entity from GET params.
     */
    public function create(): void
    {
        $this->authorize('followups.create');

        $enquiries  = $this->getEnquiryOptions();
        $leads      = $this->getLeadOptions();
        $students   = $this->getStudentOptions();
        $counselors = $this->userModel->getCounselors($this->institutionId);

        // Pre-fill from query string
        $selectedEnquiryId = $this->input('enquiry_id');
        $selectedLeadId    = $this->input('lead_id');
        $selectedStudentId = $this->input('student_id');

        $this->view('followups/create', [
            'pageTitle'         => 'Schedule Follow-up',
            'enquiries'         => $enquiries,
            'leads'             => $leads,
            'students'          => $students,
            'counselors'        => $counselors,
            'selectedEnquiryId' => $selectedEnquiryId,
            'selectedLeadId'    => $selectedLeadId,
            'selectedStudentId' => $selectedStudentId,
        ]);
    }

    // =========================================================
    // STORE
    // =========================================================

    /**
     * Validate and persist a new follow-up.
     */
    public function store(): void
    {
        $this->authorize('followups.create');
        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please try again.']);
            return;
        }

        $data = $this->postData([
            'enquiry_id', 'lead_id', 'student_id',
            'subject', 'followup_mode', 'followup_date', 'followup_time',
            'description', 'priority', 'assigned_to', 'remarks',
            'next_followup_date', 'next_followup_time',
            'reminder_at',
        ]);

        $errors = $this->validate($data, [
            'followup_date' => 'required|date',
            'followup_mode' => 'required|in:call,whatsapp,email,visit,meeting',
        ]);

        // At least one entity must be linked
        if (
            empty($data['enquiry_id']) &&
            empty($data['lead_id'])    &&
            empty($data['student_id'])
        ) {
            $errors['entity'] = 'Please link this follow-up to an enquiry, lead, or student.';
        }

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        // Defaults
        $data['institution_id'] = $this->institutionId;
        $data['created_by']     = $this->user['id'];
        $data['status']         = 'pending';
        $data['assigned_to']    = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : $this->user['id'];
        $data['priority']       = !empty($data['priority']) ? $data['priority'] : 'medium';

        // Map followup_mode to legacy type column for backward compat
        $data['type'] = $data['followup_mode'];

        // Build scheduled_at from date + time
        $data['scheduled_at'] = $data['followup_date'] . ' ' . ($data['followup_time'] ?: '09:00:00');

        // Normalise optional fields to NULL
        foreach (['enquiry_id', 'lead_id', 'student_id', 'followup_time',
                  'next_followup_date', 'next_followup_time', 'reminder_at',
                  'description', 'remarks'] as $col) {
            if (isset($data[$col]) && $data[$col] === '') {
                $data[$col] = null;
            }
        }

        // Cast IDs to int or null
        foreach (['enquiry_id', 'lead_id', 'student_id'] as $col) {
            if (!empty($data[$col])) {
                $data[$col] = (int)$data[$col];
            } else {
                $data[$col] = null;
            }
        }

        try {
            $followupId = $this->followupModel->create($data);
            $this->logAudit('create', 'followup', $followupId);
            $this->redirectWith(url('followups/' . $followupId), 'success', 'Follow-up scheduled successfully.');
        } catch (\Exception $e) {
            appLog('Followup store failed: ' . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to schedule follow-up. Please try again.'], $data);
        }
    }

    // =========================================================
    // EDIT
    // =========================================================

    /**
     * Display the edit form.
     */
    public function edit(int $id): void
    {
        $this->authorize('followups.edit');

        $followup = $this->followupModel->findWithDetails($id);
        if (!$followup) {
            $this->redirectWith(url('followups'), 'error', 'Follow-up not found.');
            return;
        }

        $enquiries  = $this->getEnquiryOptions();
        $leads      = $this->getLeadOptions();
        $students   = $this->getStudentOptions();
        $counselors = $this->userModel->getCounselors($this->institutionId);

        $this->view('followups/edit', [
            'pageTitle'  => 'Edit Follow-up',
            'followup'   => $followup,
            'enquiries'  => $enquiries,
            'leads'      => $leads,
            'students'   => $students,
            'counselors' => $counselors,
        ]);
    }

    // =========================================================
    // UPDATE
    // =========================================================

    /**
     * Validate and update an existing follow-up.
     */
    public function update(int $id): void
    {
        $this->authorize('followups.edit');
        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please try again.']);
            return;
        }

        $followup = $this->followupModel->find($id);
        if (!$followup) {
            $this->redirectWith(url('followups'), 'error', 'Follow-up not found.');
            return;
        }

        $data = $this->postData([
            'enquiry_id', 'lead_id', 'student_id',
            'subject', 'followup_mode', 'followup_date', 'followup_time',
            'description', 'priority', 'assigned_to', 'remarks',
            'next_followup_date', 'next_followup_time',
            'reminder_at', 'status',
        ]);

        $errors = $this->validate($data, [
            'followup_date' => 'required|date',
            'followup_mode' => 'required|in:call,whatsapp,email,visit,meeting',
        ]);

        if (
            empty($data['enquiry_id']) &&
            empty($data['lead_id'])    &&
            empty($data['student_id'])
        ) {
            $errors['entity'] = 'Please link this follow-up to an enquiry, lead, or student.';
        }

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $data['assigned_to'] = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : (int)$followup['assigned_to'];
        $data['priority']    = !empty($data['priority']) ? $data['priority'] : 'medium';
        $data['type']        = $data['followup_mode'];
        $data['updated_by']  = $this->user['id'];

        $data['scheduled_at'] = $data['followup_date'] . ' ' . ($data['followup_time'] ?: '09:00:00');

        foreach (['enquiry_id', 'lead_id', 'student_id', 'followup_time',
                  'next_followup_date', 'next_followup_time', 'reminder_at',
                  'description', 'remarks'] as $col) {
            if (isset($data[$col]) && $data[$col] === '') {
                $data[$col] = null;
            }
        }

        foreach (['enquiry_id', 'lead_id', 'student_id'] as $col) {
            if (!empty($data[$col])) {
                $data[$col] = (int)$data[$col];
            } else {
                $data[$col] = null;
            }
        }

        try {
            $this->followupModel->update($id, $data);
            $this->logAudit('update', 'followup', $id, $followup, $data);
            $this->redirectWith(url('followups/' . $id), 'success', 'Follow-up updated successfully.');
        } catch (\Exception $e) {
            appLog('Followup update failed: ' . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to update follow-up. Please try again.'], $data);
        }
    }

    // =========================================================
    // COMPLETE
    // =========================================================

    /**
     * Mark a follow-up as completed.
     * Supports AJAX (JSON) and standard form POST.
     * Optionally creates the next follow-up when next_followup_date is provided.
     */
    public function complete(int $id): void
    {
        $this->authorize('followups.edit');

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $followup = $this->followupModel->find($id);
        if (!$followup) {
            if ($isAjax) {
                $this->error('Follow-up not found.', 404);
            } else {
                $this->redirectWith(url('followups'), 'error', 'Follow-up not found.');
            }
            return;
        }

        $response        = sanitize($this->postData(['response'])['response'] ?? '');
        $remarks         = sanitize($this->postData(['remarks'])['remarks']   ?? '');
        $nextFollowupDate= $this->postData(['next_followup_date'])['next_followup_date'] ?? null;
        $nextFollowupMode= $this->postData(['next_followup_mode'])['next_followup_mode'] ?? null;

        if (empty($response)) {
            if ($isAjax) {
                $this->error('Response is required.');
            } else {
                $this->backWithErrors(['Response is required.']);
            }
            return;
        }

        $ok = $this->followupModel->complete($id, $response, $remarks, $this->user['id']);

        if (!$ok) {
            if ($isAjax) {
                $this->error('Failed to complete follow-up.');
            } else {
                $this->redirectWith(url('followups/' . $id), 'error', 'Failed to complete follow-up.');
            }
            return;
        }

        $this->logAudit('complete', 'followup', $id);

        // Schedule a next follow-up if requested
        $newId = null;
        if (!empty($nextFollowupDate)) {
            $mode = $nextFollowupMode ?: ($followup['followup_mode'] ?: $followup['type']);

            $newFollowupData = [
                'institution_id' => $followup['institution_id'],
                'enquiry_id'     => $followup['enquiry_id']  ?: null,
                'lead_id'        => $followup['lead_id']     ?: null,
                'student_id'     => $followup['student_id']  ?: null,
                'assigned_to'    => (int)$followup['assigned_to'],
                'subject'        => $followup['subject'],
                'type'           => $mode,
                'followup_mode'  => $mode,
                'followup_date'  => $nextFollowupDate,
                'scheduled_at'   => $nextFollowupDate . ' 09:00:00',
                'priority'       => $followup['priority'],
                'status'         => 'pending',
                'created_by'     => $this->user['id'],
            ];

            try {
                $newId = $this->followupModel->create($newFollowupData);
                $this->logAudit('create', 'followup', $newId);
            } catch (\Exception $e) {
                appLog('Next followup create failed: ' . $e->getMessage(), 'error');
            }
        }

        if ($isAjax) {
            $payload = ['message' => 'Follow-up completed successfully.'];
            if ($newId) {
                $payload['next_followup_id']  = $newId;
                $payload['next_followup_url'] = url('followups/' . $newId);
            }
            $this->success($payload);
        } else {
            $this->redirectWith(url('followups/' . $id), 'success', 'Follow-up completed successfully.');
        }
    }

    // =========================================================
    // RESCHEDULE
    // =========================================================

    /**
     * Reschedule a follow-up: marks original as rescheduled, creates a new one.
     * Supports AJAX (JSON) and standard form POST.
     */
    public function reschedule(int $id): void
    {
        $this->authorize('followups.edit');

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $followup = $this->followupModel->find($id);
        if (!$followup) {
            if ($isAjax) {
                $this->error('Follow-up not found.', 404);
            } else {
                $this->redirectWith(url('followups'), 'error', 'Follow-up not found.');
            }
            return;
        }

        $data = $this->postData([
            'followup_date', 'followup_time', 'followup_mode',
            'assigned_to', 'remarks', 'priority',
        ]);

        if (empty($data['followup_date'])) {
            if ($isAjax) {
                $this->error('New follow-up date is required.');
            } else {
                $this->backWithErrors(['New follow-up date is required.']);
            }
            return;
        }

        try {
            $newId = $this->followupModel->reschedule($id, $data, $this->user['id']);
            $this->logAudit('reschedule', 'followup', $id, null, ['new_id' => $newId]);

            if ($isAjax) {
                $this->success([
                    'message'       => 'Follow-up rescheduled successfully.',
                    'new_id'        => $newId,
                    'new_url'       => url('followups/' . $newId),
                ]);
            } else {
                $this->redirectWith(url('followups/' . $newId), 'success', 'Follow-up rescheduled successfully.');
            }
        } catch (\Exception $e) {
            appLog('Followup reschedule failed: ' . $e->getMessage(), 'error');
            if ($isAjax) {
                $this->error('Failed to reschedule follow-up.');
            } else {
                $this->redirectWith(url('followups/' . $id), 'error', 'Failed to reschedule follow-up.');
            }
        }
    }

    // =========================================================
    // DESTROY
    // =========================================================

    /**
     * Hard-delete a follow-up.
     */
    public function destroy(int $id): void
    {
        $this->authorize('followups.delete');

        $followup = $this->followupModel->find($id);
        if (!$followup) {
            $this->redirectWith(url('followups'), 'error', 'Follow-up not found.');
            return;
        }

        $this->followupModel->delete($id);
        $this->logAudit('delete', 'followup', $id);
        $this->redirectWith(url('followups'), 'success', 'Follow-up deleted successfully.');
    }

    // =========================================================
    // CALENDAR EVENTS (AJAX)
    // =========================================================

    /**
     * AJAX endpoint that returns FullCalendar-compatible JSON events.
     */
    public function events(): void
    {
        $this->authorize('followups.view');

        $start = $this->input('start');
        $end   = $this->input('end');

        if (!$start || !$end) {
            $this->error('Start and end dates are required.');
            return;
        }

        $userId = null;
        if (!hasPermission('followups.view_all')) {
            $userId = $this->user['id'];
        }

        $events = $this->followupModel->getCalendarEvents($start, $end, $userId);
        $this->json($events);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Active leads for the current institution as select options.
     */
    private function getLeadOptions(): array
    {
        $this->db->query(
            "SELECT id,
                    CONCAT(first_name, ' ', last_name) AS name,
                    lead_number,
                    phone
             FROM leads
             WHERE institution_id = ? AND deleted_at IS NULL
             ORDER BY first_name, last_name",
            [$this->institutionId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Open enquiries for the current institution as select options.
     */
    private function getEnquiryOptions(): array
    {
        $this->db->query(
            "SELECT id,
                    enquiry_number,
                    CONCAT(first_name, ' ', last_name) AS name,
                    phone
             FROM enquiries
             WHERE institution_id = ?
               AND status NOT IN ('converted', 'closed')
             ORDER BY created_at DESC",
            [$this->institutionId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Active students for the current institution as select options.
     */
    private function getStudentOptions(): array
    {
        $this->db->query(
            "SELECT id,
                    student_id_number,
                    CONCAT(first_name, ' ', last_name) AS name,
                    phone
             FROM students
             WHERE institution_id = ?
               AND status = 'active'
               AND deleted_at IS NULL
             ORDER BY first_name, last_name",
            [$this->institutionId]
        );
        return $this->db->fetchAll();
    }
}
