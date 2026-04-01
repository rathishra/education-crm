<?php $pageTitle = 'Task Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-tasks me-2"></i>Task Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Tasks</li>
            </ol>
        </nav>
    </div>
    <div>
        <div class="btn-group btn-group-sm me-2" role="group">
            <button type="button" class="btn btn-outline-secondary" id="btnListView" title="List View">
                <i class="fas fa-list"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnBoardView" title="Board View">
                <i class="fas fa-columns"></i>
            </button>
        </div>
        <?php if (hasPermission('tasks.create')): ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#taskModal" id="btnNewTask">
            <i class="fas fa-plus me-1"></i>New Task
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-start border-primary border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Total Pending</div>
                        <div class="fs-3 fw-bold text-primary"><?= number_format($stats['status']['pending'] ?? 0) ?></div>
                    </div>
                    <div class="text-primary opacity-50"><i class="fas fa-clock fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-info border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">In Progress</div>
                        <div class="fs-3 fw-bold text-info"><?= number_format($stats['status']['in_progress'] ?? 0) ?></div>
                    </div>
                    <div class="text-info opacity-50"><i class="fas fa-spinner fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-danger border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Overdue</div>
                        <div class="fs-3 fw-bold text-danger"><?= number_format($stats['overdue'] ?? 0) ?></div>
                    </div>
                    <div class="text-danger opacity-50"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-success border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Completed</div>
                        <div class="fs-3 fw-bold text-success"><?= number_format($stats['status']['completed'] ?? 0) ?></div>
                    </div>
                    <div class="text-success opacity-50"><i class="fas fa-check-circle fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search tasks..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="priority">
                    <option value="">All Priorities</option>
                    <?php foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($filters['priority'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="due_from"
                       value="<?= e($filters['due_from'] ?? '') ?>" title="Due from">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="due_to"
                       value="<?= e($filters['due_to'] ?? '') ?>" title="Due to">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Kanban Board View (hidden by default, toggle with JS) -->
<div id="kanbanView" style="display:none;">
    <div class="row g-3">
        <?php
        $kanbanCols = [
            'pending'    => ['label'=>'Pending',    'color'=>'secondary', 'icon'=>'fa-clock'],
            'in_progress'=> ['label'=>'In Progress','color'=>'primary',   'icon'=>'fa-spinner'],
            'completed'  => ['label'=>'Completed',  'color'=>'success',   'icon'=>'fa-check-circle'],
            'cancelled'  => ['label'=>'Cancelled',  'color'=>'dark',      'icon'=>'fa-ban'],
        ];
        $tasksByStatus = [];
        foreach (($tasks['data'] ?? []) as $t) {
            $tasksByStatus[$t['status']][] = $t;
        }
        foreach ($kanbanCols as $colStatus => $col):
            $colTasks = $tasksByStatus[$colStatus] ?? [];
        ?>
        <div class="col-md-3">
            <div class="card border-top border-<?= $col['color'] ?> border-3 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold small">
                        <i class="fas <?= $col['icon'] ?> me-1 text-<?= $col['color'] ?>"></i>
                        <?= $col['label'] ?>
                    </span>
                    <span class="badge bg-<?= $col['color'] ?>"><?= count($colTasks) ?></span>
                </div>
                <div class="card-body p-2" style="min-height:200px; max-height:65vh; overflow-y:auto;">
                    <?php if (empty($colTasks)): ?>
                    <div class="text-center text-muted py-3" style="font-size:.8rem;">No tasks</div>
                    <?php else: ?>
                    <?php foreach ($colTasks as $task):
                        $isOverdue = !empty($task['due_date'])
                            && $task['status'] !== 'completed'
                            && $task['status'] !== 'cancelled'
                            && strtotime($task['due_date']) < strtotime(date('Y-m-d'));
                        $priorityColors = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'];
                    ?>
                    <div class="card mb-2 border shadow-none <?= $isOverdue ? 'border-danger' : '' ?>"
                         style="cursor:default;">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="fw-semibold" style="font-size:.8rem; line-height:1.3;">
                                    <?= e($task['title']) ?>
                                </div>
                                <span class="badge bg-<?= $priorityColors[$task['priority']] ?? 'secondary' ?> ms-1" style="font-size:.6rem;">
                                    <?= ucfirst(e($task['priority'])) ?>
                                </span>
                            </div>
                            <?php if (!empty($task['description'])): ?>
                            <p class="text-muted mb-1" style="font-size:.72rem; line-height:1.3;">
                                <?= e(mb_strlen($task['description']) > 60 ? mb_substr($task['description'],0,60).'...' : $task['description']) ?>
                            </p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted" style="font-size:.7rem;">
                                    <?php if (!empty($task['assigned_name'])): ?>
                                    <i class="fas fa-user me-1"></i><?= e($task['assigned_name']) ?>
                                    <?php endif; ?>
                                </small>
                                <small class="<?= $isOverdue ? 'text-danger fw-semibold' : 'text-muted' ?>" style="font-size:.7rem;">
                                    <?php if (!empty($task['due_date'])): ?>
                                    <i class="fas fa-calendar-alt me-1"></i><?= formatDate($task['due_date'], 'd M') ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                            <?php if (hasPermission('tasks.edit')): ?>
                            <div class="mt-1 pt-1 border-top d-flex gap-1">
                                <button type="button" class="btn btn-xs btn-outline-secondary btn-edit-task flex-fill"
                                        style="font-size:.65rem; padding:1px 4px;"
                                        data-id="<?= $task['id'] ?>"
                                        data-title="<?= e($task['title']) ?>"
                                        data-description="<?= e($task['description'] ?? '') ?>"
                                        data-priority="<?= e($task['priority']) ?>"
                                        data-due_date="<?= e($task['due_date'] ?? '') ?>"
                                        data-assigned_to="<?= e($task['assigned_to'] ?? '') ?>"
                                        data-related_type="<?= e($task['related_type'] ?? '') ?>"
                                        data-related_id="<?= e($task['related_id'] ?? '') ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-outline-info dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown"
                                            style="font-size:.65rem; padding:1px 4px;">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:.8rem;">
                                        <?php foreach (['pending'=>'Pending','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $sVal=>$sLabel): ?>
                                        <?php if ($sVal !== $task['status']): ?>
                                        <li>
                                            <a class="dropdown-item btn-change-status" href="#"
                                               data-id="<?= $task['id'] ?>" data-status="<?= $sVal ?>">
                                                <?= $sLabel ?>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Tasks Table -->
<div class="card" id="tasksListView">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total: <strong><?= number_format($tasks['total'] ?? 0) ?></strong> tasks</span>
        <a href="<?= url('tasks') ?>" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Related To</th>
                        <th>Assigned To</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tasks['data'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No tasks found</td></tr>
                    <?php else: ?>
                    <?php foreach ($tasks['data'] as $task): ?>
                    <?php
                        $isOverdue = !empty($task['due_date'])
                            && $task['status'] !== 'completed'
                            && $task['status'] !== 'cancelled'
                            && strtotime($task['due_date']) < strtotime(date('Y-m-d'));
                        $priorityColors = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
                        $statusColors = ['pending' => 'secondary', 'in_progress' => 'primary', 'completed' => 'success', 'cancelled' => 'dark'];
                        $statusLabels = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
                    ?>
                    <tr class="<?= $isOverdue ? 'table-warning' : '' ?>">
                        <td class="fw-semibold"><?= e($task['title']) ?></td>
                        <td>
                            <small class="text-muted">
                                <?= e(mb_strlen($task['description'] ?? '') > 50 ? mb_substr($task['description'], 0, 50) . '...' : ($task['description'] ?? '-')) ?>
                            </small>
                        </td>
                        <td>
                            <?php if (!empty($task['related_type']) && !empty($task['related_id'])): ?>
                                <?php
                                    $relatedUrls = [
                                        'lead' => 'leads',
                                        'student' => 'students',
                                        'admission' => 'admissions',
                                        'enquiry' => 'enquiries',
                                    ];
                                    $relatedRoute = $relatedUrls[$task['related_type']] ?? null;
                                ?>
                                <?php if ($relatedRoute): ?>
                                <a href="<?= url($relatedRoute . '/' . $task['related_id']) ?>" class="text-decoration-none">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-link me-1"></i><?= ucfirst(e($task['related_type'])) ?> #<?= e($task['related_id']) ?>
                                    </span>
                                </a>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><small><?= e($task['assigned_name'] ?? '-') ?></small></td>
                        <td>
                            <small><?= !empty($task['due_date']) ? formatDate($task['due_date']) : '-' ?></small>
                            <?php if ($isOverdue): ?>
                                <br><span class="badge bg-danger">Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $priorityColors[$task['priority']] ?? 'secondary' ?>">
                                <?= ucfirst(e($task['priority'])) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $statusColors[$task['status']] ?? 'secondary' ?>" id="statusBadge-<?= $task['id'] ?>">
                                <?= $statusLabels[$task['status']] ?? ucfirst(e($task['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (hasPermission('tasks.edit')): ?>
                                <button type="button" class="btn btn-outline-primary btn-edit-task"
                                        data-id="<?= $task['id'] ?>"
                                        data-title="<?= e($task['title']) ?>"
                                        data-description="<?= e($task['description'] ?? '') ?>"
                                        data-priority="<?= e($task['priority']) ?>"
                                        data-due_date="<?= e($task['due_date'] ?? '') ?>"
                                        data-assigned_to="<?= e($task['assigned_to'] ?? '') ?>"
                                        data-related_type="<?= e($task['related_type'] ?? '') ?>"
                                        data-related_id="<?= e($task['related_id'] ?? '') ?>"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>

                                <?php if (hasPermission('tasks.edit')): ?>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Change Status">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php foreach ($statusLabels as $sVal => $sLabel): ?>
                                        <?php if ($sVal !== $task['status']): ?>
                                        <li>
                                            <a class="dropdown-item btn-change-status" href="#"
                                               data-id="<?= $task['id'] ?>"
                                               data-status="<?= $sVal ?>">
                                                <span class="badge bg-<?= $statusColors[$sVal] ?> me-1">&nbsp;</span>
                                                <?= $sLabel ?>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>

                                <?php if (hasPermission('tasks.delete')): ?>
                                <form method="POST" action="<?= url('tasks/' . $task['id'] . '/delete') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-delete" data-name="<?= e($task['title']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($tasks['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $tasks; $baseUrl = url('tasks') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Create/Edit Task Modal -->
<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel"><i class="fas fa-tasks me-2"></i>New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="taskForm">
                <?= csrfField() ?>
                <input type="hidden" name="task_id" id="taskId" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="taskTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="taskTitle" name="title" required>
                        </div>
                        <div class="col-md-4">
                            <label for="taskPriority" class="form-label">Priority</label>
                            <select class="form-select" id="taskPriority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="taskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="taskDueDate" class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="taskDueDate" name="due_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="taskAssignedTo" class="form-label">Assigned To</label>
                            <select class="form-select" id="taskAssignedTo" name="assigned_to">
                                <option value="">-- Select User --</option>
                                <?php foreach ($users ?? [] as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= e($user['name'] ?? ($user['first_name'] . ' ' . ($user['last_name'] ?? ''))) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="taskRelatedType" class="form-label">Related Type</label>
                            <select class="form-select" id="taskRelatedType" name="related_type">
                                <option value="">-- None --</option>
                                <option value="lead">Lead</option>
                                <option value="student">Student</option>
                                <option value="admission">Admission</option>
                                <option value="enquiry">Enquiry</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="taskRelatedId" class="form-label">Related ID</label>
                            <input type="text" class="form-control" id="taskRelatedId" name="related_id" placeholder="Enter related record ID">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="taskSubmitBtn">
                        <i class="fas fa-save me-1"></i>Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // New Task button - reset form
    $('#btnNewTask').on('click', function() {
        $('#taskForm')[0].reset();
        $('#taskId').val('');
        $('#taskModalLabel').html('<i class="fas fa-tasks me-2"></i>New Task');
        $('#taskSubmitBtn').html('<i class="fas fa-save me-1"></i>Save Task');
        $('#taskPriority').val('medium');
    });

    // Edit Task button - populate form
    $(document).on('click', '.btn-edit-task', function() {
        var btn = $(this);
        $('#taskId').val(btn.data('id'));
        $('#taskTitle').val(btn.data('title'));
        $('#taskDescription').val(btn.data('description'));
        $('#taskPriority').val(btn.data('priority'));
        $('#taskDueDate').val(btn.data('due_date'));
        $('#taskAssignedTo').val(btn.data('assigned_to'));
        $('#taskRelatedType').val(btn.data('related_type'));
        $('#taskRelatedId').val(btn.data('related_id'));
        $('#taskModalLabel').html('<i class="fas fa-edit me-2"></i>Edit Task');
        $('#taskSubmitBtn').html('<i class="fas fa-save me-1"></i>Update Task');
        taskModal.show();
    });

    // Submit Task Form (Create or Edit)
    $('#taskForm').on('submit', function(e) {
        e.preventDefault();

        var taskId = $('#taskId').val();
        var actionUrl = taskId
            ? '<?= url('tasks') ?>/' + taskId
            : '<?= url('tasks') ?>';

        var formData = $(this).serialize();

        $('#taskSubmitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');

        $.ajax({
            url: actionUrl,
            type: 'POST',
            data: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    taskModal.hide();
                    toastr.success(response.message || 'Task saved successfully.');
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    toastr.error(response.message || 'Failed to save task.');
                    $('#taskSubmitBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Task');
                }
            },
            error: function(xhr) {
                var msg = 'An error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    msg = Object.values(xhr.responseJSON.errors).join('<br>');
                }
                toastr.error(msg);
                $('#taskSubmitBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Task');
            }
        });
    });

    // Status Change via AJAX
    $(document).on('click', '.btn-change-status', function(e) {
        e.preventDefault();

        var link = $(this);
        var taskId = link.data('id');
        var newStatus = link.data('status');

        var statusLabels = {
            'pending': 'Pending',
            'in_progress': 'In Progress',
            'completed': 'Completed',
            'cancelled': 'Cancelled'
        };
        var statusColors = {
            'pending': 'secondary',
            'in_progress': 'primary',
            'completed': 'success',
            'cancelled': 'dark'
        };

        $.ajax({
            url: '<?= url('tasks') ?>/' + taskId + '/status',
            type: 'POST',
            data: { status: newStatus, csrf_token: csrfToken },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var badge = $('#statusBadge-' + taskId);
                    badge.removeClass().addClass('badge bg-' + statusColors[newStatus]);
                    badge.text(statusLabels[newStatus]);
                    toastr.success(response.message || 'Status updated successfully.');
                    // Reload after short delay to refresh overdue styling and stats
                    setTimeout(function() { location.reload(); }, 1200);
                } else {
                    toastr.error(response.message || 'Failed to update status.');
                }
            },
            error: function(xhr) {
                var msg = 'An error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            }
        });
    });

    // Delete confirmation
    $(document).on('click', '.btn-delete', function(e) {
        var name = $(this).data('name');
        if (!confirm('Are you sure you want to delete the task "' + name + '"? This action cannot be undone.')) {
            e.preventDefault();
        }
    });

    // Board/List view toggle
    var viewMode = localStorage.getItem('tasksViewMode') || 'list';
    function applyViewMode(mode) {
        viewMode = mode;
        localStorage.setItem('tasksViewMode', mode);
        if (mode === 'board') {
            document.getElementById('kanbanView').style.display = 'block';
            var listView = document.getElementById('tasksListView');
            if (listView) listView.style.display = 'none';
            document.getElementById('btnBoardView').classList.add('active');
            document.getElementById('btnListView').classList.remove('active');
        } else {
            document.getElementById('kanbanView').style.display = 'none';
            var listView = document.getElementById('tasksListView');
            if (listView) listView.style.display = '';
            document.getElementById('btnListView').classList.add('active');
            document.getElementById('btnBoardView').classList.remove('active');
        }
    }
    document.getElementById('btnListView').addEventListener('click', function() { applyViewMode('list'); });
    document.getElementById('btnBoardView').addEventListener('click', function() { applyViewMode('board'); });
    applyViewMode(viewMode);
});
</script>
