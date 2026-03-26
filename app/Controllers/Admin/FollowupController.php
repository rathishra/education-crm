<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Followup;
use App\Models\User;

class FollowupController extends BaseController
{
    private Followup $followupModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->followupModel = new Followup();
        $this->userModel = new User();
    }

    /**
     * List followups with filters and summary stats
     */
    public function index(): void
    {
        $this->authorize('followups.view');

        $page = (int)($this->input('page') ?? 1);
        $filters = [
            'search'      => $this->input('search'),
            'status'      => $this->input('status'),
            'type'        => $this->input('type'),
            'lead_id'     => $this->input('lead_id'),
            'assigned_to' => $this->input('assigned_to'),
            'date_from'   => $this->input('date_from'),
            'date_to'     => $this->input('date_to'),
            'priority'    => $this->input('priority'),
        ];

        // Scope to own followups if user lacks view_all
        $statsUserId = null;
        if (!hasPermission('followups.view_all')) {
            $filters['only_mine'] = $this->user['id'];
            $statsUserId = $this->user['id'];
        }

        $followups = $this->followupModel->getListPaginated($page, 15, $filters);
        $stats = $this->followupModel->getStats($statsUserId);
        $counselors = $this->userModel->getCounselors($this->institutionId);

        // Build summary card data
        $totalPending = $stats['by_status']['pending'] ?? 0;
        $overdue = $stats['overdue'];

        // Today's followups
        $todayCount = $this->followupModel->getTodayCount(
            $statsUserId ?? $this->user['id']
        );

        // Completed this week
        $weekWhere = "status = 'completed' AND completed_at >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
        $weekParams = [];
        if ($this->institutionId) {
            $weekWhere .= " AND institution_id = ?";
            $weekParams[] = $this->institutionId;
        }
        if ($statsUserId) {
            $weekWhere .= " AND assigned_to = ?";
            $weekParams[] = $statsUserId;
        }
        $this->db->query("SELECT COUNT(*) AS total FROM followups WHERE {$weekWhere}", $weekParams);
        $completedThisWeek = (int)($this->db->fetch()['total'] ?? 0);

        $this->view('followups.index', [
            'pageTitle'         => 'Follow-ups',
            'followups'         => $followups,
            'filters'           => $filters,
            'counselors'        => $counselors,
            'stats'             => [
                'total_pending'      => $totalPending,
                'overdue'            => $overdue,
                'today'              => $todayCount,
                'completed_this_week' => $completedThisWeek,
            ],
        ]);
    }

    /**
     * Calendar view for followups
     */
    public function calendar(): void
    {
        $this->authorize('followups.view');

        $this->view('followups.calendar', [
            'pageTitle' => 'Follow-up Calendar',
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->authorize('followups.create');

        $leads = $this->getLeadOptions();
        $counselors = $this->userModel->getCounselors($this->institutionId);
        $selectedLeadId = $this->input('lead_id');

        $this->view('followups.create', [
            'pageTitle'      => 'Schedule Follow-up',
            'leads'          => $leads,
            'counselors'     => $counselors,
            'selectedLeadId' => $selectedLeadId,
        ]);
    }

    /**
     * Validate and store a new followup
     */
    public function store(): void
    {
        $this->authorize('followups.create');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $data = $this->postData([
            'lead_id', 'subject', 'type', 'scheduled_at',
            'description', 'priority', 'assigned_to',
        ]);

        $errors = $this->validate($data, [
            'lead_id'      => 'required|numeric',
            'subject'      => 'required|max:255',
            'type'         => 'required|in:call,email,sms,whatsapp,meeting,visit,other',
            'scheduled_at' => 'required|date',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        // Defaults
        $data['institution_id'] = $this->institutionId;
        $data['created_by'] = $this->user['id'];
        $data['status'] = 'pending';
        $data['assigned_to'] = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : $this->user['id'];
        $data['priority'] = !empty($data['priority']) ? $data['priority'] : 'medium';

        // Clean empty optional fields
        if (empty($data['description'])) $data['description'] = null;

        try {
            $followupId = $this->followupModel->create($data);

            $this->logAudit('create', 'followup', $followupId);
            $this->redirectWith(url('followups'), 'success', 'Follow-up scheduled successfully.');
        } catch (\Exception $e) {
            appLog("Followup create failed: " . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to schedule follow-up.'], $data);
        }
    }

    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        $this->authorize('followups.edit');

        $followup = $this->followupModel->findWithDetails($id);
        if (!$followup) {
            $this->redirectWith(url('followups'), 'error', 'Follow-up not found.');
            return;
        }

        $leads = $this->getLeadOptions();
        $counselors = $this->userModel->getCounselors($this->institutionId);

        $this->view('followups.edit', [
            'pageTitle'  => 'Edit Follow-up',
            'followup'   => $followup,
            'leads'      => $leads,
            'counselors' => $counselors,
        ]);
    }

    /**
     * Validate and update an existing followup
     */
    public function update(int $id): void
    {
        $this->authorize('followups.edit');
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $followup = $this->followupModel->find($id);
        if (!$followup) {
            $this->redirectWith(url('followups'), 'error', 'Follow-up not found.');
            return;
        }

        $data = $this->postData([
            'lead_id', 'subject', 'type', 'scheduled_at',
            'description', 'priority', 'assigned_to',
        ]);

        $errors = $this->validate($data, [
            'lead_id'      => 'required|numeric',
            'subject'      => 'required|max:255',
            'type'         => 'required|in:call,email,sms,whatsapp,meeting,visit,other',
            'scheduled_at' => 'required|date',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $data['assigned_to'] = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : $this->user['id'];
        $data['priority'] = !empty($data['priority']) ? $data['priority'] : 'medium';
        if (empty($data['description'])) $data['description'] = null;
        $data['updated_by'] = $this->user['id'];

        try {
            $this->followupModel->update($id, $data);

            $this->logAudit('update', 'followup', $id, $followup, $data);
            $this->redirectWith(url('followups'), 'success', 'Follow-up updated successfully.');
        } catch (\Exception $e) {
            appLog("Followup update failed: " . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to update follow-up.'], $data);
        }
    }

    /**
     * Mark followup as completed (AJAX)
     */
    public function complete(int $id): void
    {
        $this->authorize('followups.edit');

        $followup = $this->followupModel->find($id);
        if (!$followup) {
            $this->error('Follow-up not found.', 404);
            return;
        }

        $outcome = trim($_POST['outcome'] ?? '');
        if (empty($outcome)) {
            $this->error('Outcome is required.');
            return;
        }

        $affected = $this->followupModel->complete($id, $outcome, $this->user['id']);

        if ($affected) {
            $this->logAudit('complete', 'followup', $id);
            $this->success('Follow-up marked as completed.');
        } else {
            $this->error('Failed to complete follow-up.');
        }
    }

    /**
     * Delete a followup
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

    /**
     * AJAX endpoint for FullCalendar events
     */
    public function events(): void
    {
        $this->authorize('followups.view');

        $start = $this->input('start');
        $end = $this->input('end');

        if (!$start || !$end) {
            $this->error('Start and end dates are required.');
            return;
        }

        // Scope to own followups if user lacks view_all
        $userId = null;
        if (!hasPermission('followups.view_all')) {
            $userId = $this->user['id'];
        }

        $events = $this->followupModel->getCalendarEvents($start, $end, $userId);

        $this->json($events);
    }

    /**
     * Get leads as select options for the current institution
     */
    private function getLeadOptions(): array
    {
        $this->db->query(
            "SELECT id, CONCAT(first_name, ' ', last_name) AS name, lead_number, phone
             FROM leads
             WHERE institution_id = ? AND deleted_at IS NULL
             ORDER BY first_name, last_name",
            [$this->institutionId]
        );
        return $this->db->fetchAll();
    }
}
