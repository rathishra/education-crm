<?php $pageTitle = 'Follow-up Management'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-phone-alt me-2 text-primary"></i>Follow-up Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Follow-ups</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('followups/calendar') ?>" class="btn btn-outline-info">
            <i class="fas fa-calendar-alt me-1"></i>Calendar View
        </a>
        <?php if (hasPermission('followups.create')): ?>
        <a href="<?= url('followups/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Follow-up
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Stat Cards (5 cols) ────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <!-- Today -->
    <div class="col-6 col-sm-4 col-xl">
        <div class="stat-card stat-sky py-3">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['today'] ?? 0) ?></div>
                <div class="stat-label">Today</div>
            </div>
        </div>
    </div>
    <!-- Pending -->
    <div class="col-6 col-sm-4 col-xl">
        <div class="stat-card stat-amber py-3">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['pending'] ?? 0) ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    <!-- Overdue -->
    <div class="col-6 col-sm-4 col-xl">
        <div class="stat-card stat-rose py-3">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['overdue'] ?? 0) ?></div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>
    </div>
    <!-- Completed Today -->
    <div class="col-6 col-sm-4 col-xl">
        <div class="stat-card stat-emerald py-3">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['completed_today'] ?? 0) ?></div>
                <div class="stat-label">Completed Today</div>
            </div>
        </div>
    </div>
    <!-- Rescheduled -->
    <div class="col-6 col-sm-4 col-xl">
        <div class="stat-card stat-violet py-3">
            <div class="stat-icon"><i class="fas fa-redo"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['rescheduled'] ?? 0) ?></div>
                <div class="stat-label">Rescheduled</div>
            </div>
        </div>
    </div>
</div>

<!-- ── Tab Bar ────────────────────────────────────────────────────────────── -->
<?php $activeTab = $activeTab ?? 'all'; ?>
<ul class="nav nav-pills gap-1 mb-3">
    <li class="nav-item">
        <a href="<?= url('followups') ?>?tab=all"
           class="nav-link <?= $activeTab === 'all' ? 'active' : '' ?>">
            All
        </a>
    </li>
    <li class="nav-item">
        <a href="<?= url('followups') ?>?tab=today"
           class="nav-link <?= $activeTab === 'today' ? 'active' : '' ?>">
            Today
            <span class="badge bg-info text-white ms-1"><?= (int)($stats['today'] ?? 0) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a href="<?= url('followups') ?>?tab=overdue"
           class="nav-link <?= $activeTab === 'overdue' ? 'active' : 'text-danger' ?>">
            Overdue
            <span class="badge bg-danger ms-1"><?= (int)($stats['overdue'] ?? 0) ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a href="<?= url('followups') ?>?tab=upcoming"
           class="nav-link <?= $activeTab === 'upcoming' ? 'active' : '' ?>">
            Upcoming
        </a>
    </li>
</ul>

<!-- ── Filters ────────────────────────────────────────────────────────────── -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold small"><i class="fas fa-filter me-1 text-muted"></i>Filters</span>
        <button class="btn btn-sm btn-link text-muted p-0 d-md-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
    <div class="collapse show" id="filterCollapse">
        <div class="card-body py-3">
            <form method="GET" id="filterForm">
                <?php if ($activeTab !== 'all'): ?>
                <input type="hidden" name="tab" value="<?= e($activeTab) ?>">
                <?php endif; ?>
                <div class="row g-2 align-items-end">
                    <!-- Search -->
                    <div class="col-12 col-md-3">
                        <input type="text" class="form-control form-control-sm" name="search"
                               placeholder="Search name, phone..."
                               value="<?= e($filters['search'] ?? '') ?>">
                    </div>
                    <!-- Mode -->
                    <div class="col-6 col-md-2">
                        <select class="form-select form-select-sm" name="followup_mode">
                            <option value="">All Modes</option>
                            <option value="call"     <?= ($filters['followup_mode'] ?? '') === 'call'     ? 'selected' : '' ?>>Call</option>
                            <option value="whatsapp" <?= ($filters['followup_mode'] ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                            <option value="email"    <?= ($filters['followup_mode'] ?? '') === 'email'    ? 'selected' : '' ?>>Email</option>
                            <option value="visit"    <?= ($filters['followup_mode'] ?? '') === 'visit'    ? 'selected' : '' ?>>Visit</option>
                            <option value="meeting"  <?= ($filters['followup_mode'] ?? '') === 'meeting'  ? 'selected' : '' ?>>Meeting</option>
                        </select>
                    </div>
                    <!-- Status -->
                    <div class="col-6 col-md-2">
                        <select class="form-select form-select-sm" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending"     <?= ($filters['status'] ?? '') === 'pending'     ? 'selected' : '' ?>>Pending</option>
                            <option value="completed"   <?= ($filters['status'] ?? '') === 'completed'   ? 'selected' : '' ?>>Completed</option>
                            <option value="rescheduled" <?= ($filters['status'] ?? '') === 'rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
                            <option value="cancelled"   <?= ($filters['status'] ?? '') === 'cancelled'   ? 'selected' : '' ?>>Cancelled</option>
                            <option value="missed"      <?= ($filters['status'] ?? '') === 'missed'      ? 'selected' : '' ?>>Missed</option>
                        </select>
                    </div>
                    <!-- Priority -->
                    <div class="col-6 col-md-1">
                        <select class="form-select form-select-sm" name="priority">
                            <option value="">Priority</option>
                            <option value="high"   <?= ($filters['priority'] ?? '') === 'high'   ? 'selected' : '' ?>>High</option>
                            <option value="medium" <?= ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                            <option value="low"    <?= ($filters['priority'] ?? '') === 'low'    ? 'selected' : '' ?>>Low</option>
                        </select>
                    </div>
                    <!-- Assigned To -->
                    <div class="col-6 col-md-2">
                        <select class="form-select form-select-sm" name="assigned_to">
                            <option value="">All Counselors</option>
                            <?php foreach ($counselors as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= ($filters['assigned_to'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Date From -->
                    <div class="col-6 col-md-1">
                        <input type="date" class="form-control form-control-sm" name="date_from"
                               value="<?= e($filters['date_from'] ?? '') ?>" title="From date">
                    </div>
                    <!-- Date To -->
                    <div class="col-6 col-md-1">
                        <input type="date" class="form-control form-control-sm" name="date_to"
                               value="<?= e($filters['date_to'] ?? '') ?>" title="To date">
                    </div>
                    <!-- Buttons -->
                    <div class="col-12 col-md-auto d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search me-1"></i>Apply
                        </button>
                        <a href="<?= url('followups') ?><?= $activeTab !== 'all' ? '?tab=' . e($activeTab) : '' ?>"
                           class="btn btn-sm btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Follow-ups Table Card ──────────────────────────────────────────────── -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            Total: <strong><?= number_format($followups['total'] ?? 0) ?></strong>
            follow-up<?= ($followups['total'] ?? 0) !== 1 ? 's' : '' ?>
        </span>
        <?php
        $activeFilterCount = count(array_filter($filters ?? [], fn($v) => $v !== '' && $v !== null));
        if ($activeFilterCount > 0): ?>
        <span class="badge bg-soft-primary text-primary">
            <?= $activeFilterCount ?> filter<?= $activeFilterCount > 1 ? 's' : '' ?> active
        </span>
        <?php endif; ?>
    </div>

    <div class="card-body p-0">
        <?php
        $today = date('Y-m-d');
        $rows  = $followups['data'] ?? [];
        if (empty($rows)):
        ?>
        <!-- Empty State -->
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block"></i>
            <p class="text-muted fw-semibold mb-1">No follow-ups found</p>
            <p class="text-muted small mb-3">
                <?php if ($activeFilterCount > 0): ?>
                    Try adjusting your filters or <a href="<?= url('followups') ?>">clear all filters</a>.
                <?php else: ?>
                    Get started by scheduling your first follow-up.
                <?php endif; ?>
            </p>
            <?php if (hasPermission('followups.create')): ?>
            <a href="<?= url('followups/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>New Follow-up
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:130px;">Date &amp; Time</th>
                        <th>Contact</th>
                        <th style="width:110px;">Mode</th>
                        <th style="width:115px;">Status</th>
                        <th style="width:120px;">Response</th>
                        <th style="width:85px;">Priority</th>
                        <th>Assigned To</th>
                        <th style="width:150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $fu):
                    $fuDate    = $fu['followup_date'] ?? '';
                    $fuStatus  = $fu['status'] ?? 'pending';
                    $isOverdue = $fuDate && $fuDate < $today && $fuStatus === 'pending';
                    $isToday   = $fuDate && $fuDate === $today && $fuStatus === 'pending';
                    $trClass   = $isOverdue ? 'table-danger' : ($isToday ? 'table-warning' : '');

                    // Mode icon map
                    $modeIcons = [
                        'call'    => 'fa-phone',
                        'whatsapp'=> 'fa-whatsapp',
                        'email'   => 'fa-envelope',
                        'visit'   => 'fa-map-marker-alt',
                        'meeting' => 'fa-users',
                    ];
                    $modeIcon  = $modeIcons[$fu['followup_mode'] ?? ''] ?? 'fa-phone';
                    $modeLabel = ucfirst($fu['followup_mode'] ?? '');

                    // Status badge
                    $statusClass = match($fuStatus) {
                        'pending'     => 'badge bg-soft-warning text-warning',
                        'completed'   => 'badge bg-soft-success text-success',
                        'rescheduled' => 'badge bg-soft-info text-info',
                        'cancelled'   => 'badge bg-soft-secondary text-secondary',
                        'missed'      => 'badge bg-soft-danger text-danger',
                        default       => 'badge bg-soft-secondary text-secondary',
                    };

                    // Response badge
                    $responseClass = match($fu['response'] ?? '') {
                        'interested'     => 'badge bg-soft-success text-success',
                        'not_interested' => 'badge bg-soft-danger text-danger',
                        'call_back'      => 'badge bg-soft-warning text-warning',
                        'no_response'    => 'badge bg-soft-secondary text-secondary',
                        default          => '',
                    };
                    $responseLabel = match($fu['response'] ?? '') {
                        'interested'     => 'Interested',
                        'not_interested' => 'Not Interested',
                        'call_back'      => 'Call Back',
                        'no_response'    => 'No Response',
                        default          => '',
                    };

                    // Priority badge
                    $priorityClass = match($fu['priority'] ?? 'medium') {
                        'high'   => 'badge bg-danger',
                        'medium' => 'badge bg-warning text-dark',
                        'low'    => 'badge bg-secondary',
                        default  => 'badge bg-secondary',
                    };

                    // Contact type
                    $ctPhone = e($fu['contact_phone'] ?? '');
                    if (!empty($fu['enquiry_id'])) {
                        $ctBadge = 'badge bg-soft-primary text-primary';
                        $ctLabel = 'Enquiry';
                        $ctName  = e($fu['enquiry_name'] ?? $fu['contact_name'] ?? '');
                    } elseif (!empty($fu['student_id'])) {
                        $ctBadge = 'badge bg-soft-success text-success';
                        $ctLabel = 'Student';
                        $ctName  = e($fu['student_name'] ?? $fu['contact_name'] ?? '');
                    } else {
                        $ctBadge = 'badge bg-soft-info text-info';
                        $ctLabel = 'Lead';
                        $ctName  = e($fu['lead_name'] ?? $fu['contact_name'] ?? '');
                    }
                ?>
                <tr class="<?= $trClass ?>">
                    <!-- Date & Time -->
                    <td>
                        <div class="fw-semibold small <?= $isOverdue ? 'text-danger' : '' ?>">
                            <?php if ($isOverdue): ?>
                            <i class="fas fa-exclamation-triangle me-1" title="Overdue"></i>
                            <?php endif; ?>
                            <?= e(formatDate($fuDate, 'd M Y')) ?>
                        </div>
                        <?php if (!empty($fu['followup_time'])): ?>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1" style="font-size:.6rem;"></i>
                            <?= e(date('h:i A', strtotime($fu['followup_time']))) ?>
                        </small>
                        <?php endif; ?>
                    </td>

                    <!-- Contact -->
                    <td>
                        <span class="<?= $ctBadge ?> mb-1" style="font-size:.68rem;"><?= $ctLabel ?></span>
                        <div class="fw-semibold small"><?= $ctName ?></div>
                        <?php if ($ctPhone): ?>
                        <small class="text-muted">
                            <a href="tel:<?= $ctPhone ?>" class="text-muted text-decoration-none">
                                <i class="fas fa-phone-alt me-1" style="font-size:.6rem;"></i><?= $ctPhone ?>
                            </a>
                        </small>
                        <?php endif; ?>
                    </td>

                    <!-- Mode -->
                    <td>
                        <i class="fas <?= $modeIcon ?> me-1 text-muted"></i>
                        <small><?= $modeLabel ?></small>
                    </td>

                    <!-- Status -->
                    <td><span class="<?= $statusClass ?>"><?= ucfirst($fuStatus) ?></span></td>

                    <!-- Response -->
                    <td>
                        <?php if (!empty($responseClass)): ?>
                        <span class="<?= $responseClass ?>"><?= $responseLabel ?></span>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- Priority -->
                    <td>
                        <span class="<?= $priorityClass ?>"><?= ucfirst($fu['priority'] ?? 'medium') ?></span>
                    </td>

                    <!-- Assigned To -->
                    <td><small><?= e($fu['assigned_name'] ?? '—') ?></small></td>

                    <!-- Actions -->
                    <td>
                        <div class="btn-group btn-group-sm">
                            <!-- View -->
                            <a href="<?= url('followups/' . $fu['id']) ?>"
                               class="btn btn-outline-primary" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <!-- Edit -->
                            <?php if (hasPermission('followups.edit')): ?>
                            <a href="<?= url('followups/' . $fu['id'] . '/edit') ?>"
                               class="btn btn-outline-secondary" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <?php endif; ?>
                            <!-- Mark Complete -->
                            <?php if (in_array($fuStatus, ['pending', 'rescheduled', 'missed']) && hasPermission('followups.edit')): ?>
                            <button type="button"
                                    class="btn btn-outline-success btn-complete"
                                    data-id="<?= $fu['id'] ?>"
                                    data-action="<?= url('followups/' . $fu['id'] . '/complete') ?>"
                                    title="Mark Complete">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>
                            <!-- Delete -->
                            <?php if (hasPermission('followups.delete')): ?>
                            <form method="POST"
                                  action="<?= url('followups/' . $fu['id'] . '/delete') ?>"
                                  class="d-inline">
                                <?= csrfField() ?>
                                <button type="submit"
                                        class="btn btn-outline-danger btn-delete"
                                        data-name="<?= $ctName ?>"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if (($followups['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $followups;
        $baseUrl    = url('followups') . '?' . http_build_query(
            array_filter(array_merge($filters ?? [], $activeTab !== 'all' ? ['tab' => $activeTab] : []))
        );
        ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ── Counselor-wise Summary ─────────────────────────────────────────────── -->
<?php if (hasPermission('followups.view_all') && !empty($stats['counselor_wise'])): ?>
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-users me-2 text-muted"></i>Counselor-wise Summary
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Counselor</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Pending</th>
                        <th class="text-center">Today</th>
                        <th class="text-center">Overdue</th>
                        <th class="text-center">Completed</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($stats['counselor_wise'] as $cw): ?>
                <tr>
                    <td class="fw-semibold"><?= e($cw['counselor_name'] ?? $cw['name'] ?? '—') ?></td>
                    <td class="text-center">
                        <span class="badge bg-soft-secondary text-secondary"><?= (int)($cw['total'] ?? 0) ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-soft-warning text-warning"><?= (int)($cw['pending'] ?? 0) ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-soft-info text-info"><?= (int)($cw['today'] ?? 0) ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-soft-danger text-danger"><?= (int)($cw['overdue'] ?? 0) ?></span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-soft-success text-success"><?= (int)($cw['completed'] ?? 0) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Complete Modal ─────────────────────────────────────────────────────── -->
<div class="modal fade" id="completeModal" tabindex="-1"
     aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeModalLabel">
                    <i class="fas fa-check-circle text-success me-2"></i>Mark Follow-up Complete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="completeForm" method="POST" action="">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Response <span class="text-danger">*</span></label>
                        <select name="response" class="form-select" required>
                            <option value="">Select response...</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="call_back">Call Back</option>
                            <option value="no_response">No Response</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  placeholder="Notes about the outcome of this follow-up..."></textarea>
                    </div>
                    <hr class="my-3">
                    <p class="small text-muted mb-2 fw-semibold">
                        <i class="fas fa-forward me-1"></i>Schedule Next Follow-up (optional)
                    </p>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Next Date</label>
                            <input type="date" name="next_followup_date"
                                   class="form-control form-control-sm"
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Next Mode</label>
                            <select name="next_followup_mode" class="form-select form-select-sm">
                                <option value="">Select mode...</option>
                                <option value="call">Call</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                                <option value="visit">Visit</option>
                                <option value="meeting">Meeting</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Mark Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Complete buttons — set form action then open modal
    document.querySelectorAll('.btn-complete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('completeForm').action = this.dataset.action;
            document.getElementById('completeForm').reset();
            new bootstrap.Modal(document.getElementById('completeModal')).show();
        });
    });

    // Confirm delete
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var name = this.dataset.name || 'this follow-up';
            if (!confirm('Delete follow-up for "' + name + '"? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
