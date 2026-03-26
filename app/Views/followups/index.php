<?php $pageTitle = 'Follow-up Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-phone-alt me-2"></i>Follow-up Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Follow-ups</li>
            </ol>
        </nav>
    </div>
    <div>
        <a href="<?= url('followups/calendar') ?>" class="btn btn-outline-info me-1">
            <i class="fas fa-calendar-alt me-1"></i>Calendar View
        </a>
        <?php if (hasPermission('followups.create')): ?>
        <a href="<?= url('followups/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Follow-up
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-start border-primary border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">Pending</div>
                        <div class="fs-4 fw-bold text-primary"><?= number_format($stats['pending'] ?? 0) ?></div>
                    </div>
                    <div class="text-primary opacity-50"><i class="fas fa-clock fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-danger border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">Overdue</div>
                        <div class="fs-4 fw-bold text-danger"><?= number_format($stats['overdue'] ?? 0) ?></div>
                    </div>
                    <div class="text-danger opacity-50"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-warning border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">Today</div>
                        <div class="fs-4 fw-bold text-warning"><?= number_format($stats['today'] ?? 0) ?></div>
                    </div>
                    <div class="text-warning opacity-50"><i class="fas fa-calendar-day fa-2x"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-success border-4">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase">Completed This Week</div>
                        <div class="fs-4 fw-bold text-success"><?= number_format($stats['completed_week'] ?? 0) ?></div>
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
            <div class="col-md-2">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search subject, lead..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="type">
                    <option value="">All Types</option>
                    <?php foreach (['call','email','sms','whatsapp','meeting','visit','other'] as $t): ?>
                    <option value="<?= $t ?>" <?= ($filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <select class="form-select form-select-sm" name="priority">
                    <option value="">Priority</option>
                    <?php foreach (['low','medium','high','urgent'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       value="<?= e($filters['date_from'] ?? '') ?>" title="From date">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       value="<?= e($filters['date_to'] ?? '') ?>" title="To date">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Follow-ups Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total: <strong><?= number_format($followups['total'] ?? 0) ?></strong> follow-ups</span>
        <a href="<?= url('followups') ?>" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Lead</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Assigned To</th>
                        <th>Scheduled</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($followups['data'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No follow-ups found</td></tr>
                    <?php else: ?>
                    <?php foreach ($followups['data'] as $fu): ?>
                    <?php
                        $isOverdue = $fu['status'] === 'pending' && strtotime($fu['scheduled_at']) < time();
                    ?>
                    <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                        <td>
                            <a href="<?= url('leads/' . $fu['lead_id']) ?>" class="fw-semibold text-decoration-none">
                                <?= e($fu['lead_name'] ?? '-') ?>
                            </a>
                            <?php if (!empty($fu['lead_number'])): ?>
                            <br><small class="text-muted"><code><?= e($fu['lead_number']) ?></code></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($fu['subject']) ?></td>
                        <td>
                            <?php
                                $typeIcons = [
                                    'call' => 'fas fa-phone',
                                    'email' => 'fas fa-envelope',
                                    'sms' => 'fas fa-comment-dots',
                                    'whatsapp' => 'fab fa-whatsapp',
                                    'meeting' => 'fas fa-users',
                                    'visit' => 'fas fa-building',
                                    'other' => 'fas fa-ellipsis-h',
                                ];
                                $typeColors = [
                                    'call' => 'success',
                                    'email' => 'info',
                                    'sms' => 'primary',
                                    'whatsapp' => 'success',
                                    'meeting' => 'warning',
                                    'visit' => 'secondary',
                                    'other' => 'dark',
                                ];
                                $icon = $typeIcons[$fu['type']] ?? 'fas fa-ellipsis-h';
                                $color = $typeColors[$fu['type']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $color ?>">
                                <i class="<?= $icon ?> me-1"></i><?= ucfirst($fu['type']) ?>
                            </span>
                        </td>
                        <td><small><?= e($fu['assigned_name'] ?? '-') ?></small></td>
                        <td>
                            <small><?= formatDateTime($fu['scheduled_at']) ?></small>
                            <?php if ($isOverdue): ?>
                            <br><span class="badge bg-danger">Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                                $priorityColors = [
                                    'low' => 'secondary',
                                    'medium' => 'info',
                                    'high' => 'warning',
                                    'urgent' => 'danger',
                                ];
                            ?>
                            <span class="badge bg-<?= $priorityColors[$fu['priority']] ?? 'secondary' ?>">
                                <?= ucfirst($fu['priority']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= match($fu['status']) {
                                'completed' => 'success',
                                'cancelled' => 'secondary',
                                'pending' => 'warning',
                                default => 'secondary'
                            } ?>">
                                <?= ucfirst($fu['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('leads/' . $fu['lead_id']) ?>" class="btn btn-outline-info" title="View Lead">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (hasPermission('followups.edit')): ?>
                                <a href="<?= url('followups/' . $fu['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('followups.edit') && $fu['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-outline-success btn-complete"
                                        data-id="<?= $fu['id'] ?>"
                                        data-subject="<?= e($fu['subject']) ?>"
                                        title="Mark Complete">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php endif; ?>
                                <?php if (hasPermission('followups.delete')): ?>
                                <form method="POST" action="<?= url('followups/' . $fu['id'] . '/delete') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-delete" data-name="<?= e($fu['subject']) ?>" title="Delete">
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
    <?php if (($followups['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $followups; $baseUrl = url('followups') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Complete Follow-up Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Complete Follow-up</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Completing: <strong id="completeSubject"></strong></p>
                <div class="mb-3">
                    <label class="form-label required">Outcome / Notes</label>
                    <textarea class="form-control" id="completeOutcome" name="outcome" rows="4"
                              placeholder="Describe the outcome of this follow-up..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="completeSubmit">
                    <i class="fas fa-check me-1"></i>Mark as Completed
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    var completeId = null;
    var completeModal = new bootstrap.Modal(document.getElementById('completeModal'));

    // Open complete modal
    $(document).on('click', '.btn-complete', function() {
        completeId = $(this).data('id');
        $('#completeSubject').text($(this).data('subject'));
        $('#completeOutcome').val('');
        completeModal.show();
    });

    // Submit complete
    $('#completeSubmit').on('click', function() {
        var outcome = $('#completeOutcome').val().trim();
        if (!outcome) {
            toastr.error('Please enter the outcome.');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');

        $.ajax({
            url: '<?= url('followups') ?>/' + completeId + '/complete',
            method: 'POST',
            data: {
                csrf_token: $('meta[name="csrf-token"]').attr('content'),
                outcome: outcome
            },
            success: function(response) {
                completeModal.hide();
                toastr.success('Follow-up marked as completed.');
                location.reload();
            },
            error: function(xhr) {
                var msg = xhr.responseJSON?.message || 'Failed to complete follow-up.';
                toastr.error(msg);
                $btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Mark as Completed');
            }
        });
    });

    // Confirm delete
    $(document).on('click', '.btn-delete', function(e) {
        var name = $(this).data('name');
        if (!confirm('Are you sure you want to delete follow-up "' + name + '"?')) {
            e.preventDefault();
        }
    });
});
</script>
