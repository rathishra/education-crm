<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Task;
use App\Models\User;

class TaskController extends BaseController
{
    private Task $taskModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->taskModel = new Task();
        $this->userModel = new User();
    }

    /**
     * List tasks with filters, overdue count, and stats
     */
    public function index(): void
    {
        $this->authorize('tasks.view');

        $page = (int)($this->input('page') ?? 1);
        $filters = [
            'search'       => $this->input('search'),
            'status'       => $this->input('status'),
            'priority'     => $this->input('priority'),
            'assigned_to'  => $this->input('assigned_to'),
            'due_from'     => $this->input('due_from'),
            'due_to'       => $this->input('due_to'),
            'related_type' => $this->input('related_type'),
        ];

        // Scope to own tasks if user lacks tasks.view_all
        if (!hasPermission('tasks.view_all')) {
            $filters['only_mine'] = $this->user['id'];
        }

        $tasks = $this->taskModel->getListPaginated($page, 15, $filters);

        // Overdue count and stats (scoped to own if no view_all)
        $statsUserId = !hasPermission('tasks.view_all') ? $this->user['id'] : null;
        $overdueCount = count($this->taskModel->getOverdue($statsUserId));
        $stats = $this->taskModel->getStats($statsUserId);

        $counselors = $this->userModel->getCounselors($this->institutionId);

        $this->view('tasks.index', [
            'pageTitle'    => 'Task Management',
            'tasks'        => $tasks,
            'filters'      => $filters,
            'overdueCount' => $overdueCount,
            'stats'        => $stats,
            'counselors'   => $counselors,
        ]);
    }

    /**
     * Create a new task
     */
    public function store(): void
    {
        $this->authorize('tasks.create');
        if (!verifyCsrf()) {
            if (isAjax()) {
                $this->error('Session expired.', 419);
                return;
            }
            $this->backWithErrors(['Session expired.']);
            return;
        }

        $data = $this->postData([
            'title', 'description', 'due_date', 'priority',
            'assigned_to', 'related_type', 'related_id',
        ]);

        $errors = $this->validate($data, [
            'title'    => 'required|max:255',
            'due_date' => 'required|date',
            'priority' => 'in:low,medium,high,urgent',
        ]);

        if (!empty($errors)) {
            if (isAjax()) {
                $this->error('Validation failed.', 422, $errors);
                return;
            }
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        // Set defaults
        $data['institution_id'] = $this->institutionId;
        $data['created_by'] = $this->user['id'];
        $data['assigned_to'] = !empty($data['assigned_to']) ? (int)$data['assigned_to'] : $this->user['id'];
        $data['priority'] = !empty($data['priority']) ? $data['priority'] : 'medium';
        $data['status'] = 'pending';

        // Clean empty optional values
        foreach (['description', 'related_type', 'related_id'] as $field) {
            if (empty($data[$field])) {
                $data[$field] = null;
            }
        }

        try {
            $taskId = $this->taskModel->create($data);
            $this->logAudit('create', 'task', $taskId);

            if (isAjax()) {
                $this->success('Task created successfully.', ['task_id' => $taskId]);
                return;
            }
            $this->redirectWith(url('tasks'), 'success', 'Task created successfully.');
        } catch (\Exception $e) {
            appLog("Task create failed: " . $e->getMessage(), 'error');
            if (isAjax()) {
                $this->error('Failed to create task.');
                return;
            }
            $this->backWithErrors(['Failed to create task.'], $data);
        }
    }

    /**
     * Update an existing task
     */
    public function update(int $id): void
    {
        $this->authorize('tasks.edit');
        if (!verifyCsrf()) {
            if (isAjax()) {
                $this->error('Session expired.', 419);
                return;
            }
            $this->backWithErrors(['Session expired.']);
            return;
        }

        $task = $this->taskModel->find($id);
        if (!$task) {
            if (isAjax()) {
                $this->error('Task not found.', 404);
                return;
            }
            $this->redirectWith(url('tasks'), 'error', 'Task not found.');
            return;
        }

        $data = $this->postData([
            'title', 'description', 'due_date', 'priority',
            'assigned_to', 'related_type', 'related_id', 'status',
        ]);

        $errors = $this->validate($data, [
            'title'    => 'required|max:255',
            'due_date' => 'required|date',
            'priority' => 'in:low,medium,high,urgent',
            'status'   => 'in:pending,in_progress,completed,cancelled',
        ]);

        if (!empty($errors)) {
            if (isAjax()) {
                $this->error('Validation failed.', 422, $errors);
                return;
            }
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $data['updated_by'] = $this->user['id'];

        // Clean empty optional values
        foreach (['description', 'related_type', 'related_id', 'assigned_to'] as $field) {
            if (empty($data[$field])) {
                $data[$field] = null;
            }
        }

        try {
            $this->taskModel->update($id, $data);
            $this->logAudit('update', 'task', $id, $task, $data);

            if (isAjax()) {
                $this->success('Task updated successfully.');
                return;
            }
            $this->redirectWith(url('tasks'), 'success', 'Task updated successfully.');
        } catch (\Exception $e) {
            appLog("Task update failed: " . $e->getMessage(), 'error');
            if (isAjax()) {
                $this->error('Failed to update task.');
                return;
            }
            $this->backWithErrors(['Failed to update task.'], $data);
        }
    }

    /**
     * AJAX endpoint to update task status
     */
    public function updateStatus(int $id): void
    {
        $this->authorize('tasks.edit');

        $status = trim($_POST['status'] ?? '');
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        if (empty($status) || !in_array($status, $validStatuses)) {
            $this->error('Invalid status.');
            return;
        }

        $task = $this->taskModel->find($id);
        if (!$task) {
            $this->error('Task not found.', 404);
            return;
        }

        $this->taskModel->updateStatus($id, $status);
        $this->logAudit('update_status', 'task', $id, ['status' => $task['status']], ['status' => $status]);

        $this->success('Task status updated successfully.');
    }

    /**
     * Delete a task
     */
    public function destroy(int $id): void
    {
        $this->authorize('tasks.delete');

        $task = $this->taskModel->find($id);
        if (!$task) {
            if (isAjax()) {
                $this->error('Task not found.', 404);
                return;
            }
            $this->redirectWith(url('tasks'), 'error', 'Task not found.');
            return;
        }

        $this->taskModel->delete($id);
        $this->logAudit('delete', 'task', $id);

        if (isAjax()) {
            $this->success('Task deleted successfully.');
            return;
        }
        $this->redirectWith(url('tasks'), 'success', 'Task deleted successfully.');
    }
}
