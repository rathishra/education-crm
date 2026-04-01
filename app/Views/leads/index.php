<?php $pageTitle = 'Lead Management'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-funnel me-2 text-primary"></i>Lead Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Leads</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('leads.export')): ?>
        <a href="<?= url('leads/export') . '?' . http_build_query(array_filter($filters ?? [])) ?>"
           class="btn btn-outline-success">
            <i class="fas fa-file-csv me-1"></i>Export
        </a>
        <?php endif; ?>
        <?php if (hasPermission('leads.import')): ?>
        <a href="<?= url('leads/import') ?>" class="btn btn-outline-info">
            <i class="fas fa-file-import me-1"></i>Import
        </a>
        <?php endif; ?>
        <?php if (hasPermission('leads.create')): ?>
        <a href="<?= url('leads/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Lead
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── Stat Cards ─────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <!-- Total -->
    <div class="col-6 col-sm-4 col-xl">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                <div class="stat-label">Total Leads</div>
            </div>
        </div>
    </div>
    <!-- Hot -->
    <div class="col-6 col-sm-4 col-xl">
        <a href="<?= url('leads?priority=hot') ?>" class="text-decoration-none">
            <div class="stat-card stat-rose py-3">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['hot'] ?? 0) ?></div>
                    <div class="stat-label">Hot 🔴</div>
                </div>
            </div>
        </a>
    </div>
    <!-- Warm -->
    <div class="col-6 col-sm-4 col-xl">
        <a href="<?= url('leads?priority=warm') ?>" class="text-decoration-none">
            <div class="stat-card stat-amber py-3">
                <div class="stat-icon"><i class="fas fa-thermometer-half"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['warm'] ?? 0) ?></div>
                    <div class="stat-label">Warm 🟡</div>
                </div>
            </div>
        </a>
    </div>
    <!-- Cold -->
    <div class="col-6 col-sm-4 col-xl">
        <a href="<?= url('leads?priority=cold') ?>" class="text-decoration-none">
            <div class="stat-card stat-sky py-3">
                <div class="stat-icon"><i class="fas fa-snowflake"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['cold'] ?? 0) ?></div>
                    <div class="stat-label">Cold 🔵</div>
                </div>
            </div>
        </a>
    </div>
    <!-- Follow-up Due -->
    <div class="col-6 col-sm-4 col-xl">
        <a href="<?= url('leads?followup_overdue=1') ?>" class="text-decoration-none">
            <div class="stat-card stat-orange py-3">
                <div class="stat-icon"><i class="fas fa-bell"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['followup_due'] ?? 0) ?></div>
                    <div class="stat-label">Follow-up Due ⚠️</div>
                </div>
            </div>
        </a>
    </div>
    <!-- Converted -->
    <div class="col-6 col-sm-4 col-xl">
        <a href="<?= url('leads?converted=1') ?>" class="text-decoration-none">
            <div class="stat-card stat-emerald py-3">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['converted'] ?? 0) ?></div>
                    <div class="stat-label">Converted ✅</div>
                </div>
            </div>
        </a>
    </div>
    <!-- This Month -->
    <div class="col-6 col-sm-4 col-xl">
        <div class="stat-card stat-violet py-3">
            <div class="stat-icon"><i class="fas fa-calendar-plus"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['this_month'] ?? 0) ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>
</div>

<!-- ── Pipeline Bar ────────────────────────────────────────────────────────── -->
<div class="card mb-4">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-1 flex-wrap pipeline-bar">
            <?php
            $pipelineStatuses = !empty($statuses) ? $statuses : [];
            $activeStatusId   = $filters['status_id'] ?? '';
            foreach ($pipelineStatuses as $idx => $st):
                $isActive  = (string)$activeStatusId === (string)$st['id'];
                $stColor   = !empty($st['color']) ? $st['color'] : '#6c757d';
                $pillStyle = $isActive
                    ? 'background-color:' . $stColor . ';color:#fff;border-color:' . $stColor . ';'
                    : 'border-color:' . $stColor . ';color:' . $stColor . ';background:#fff;';
            ?>
            <?php if ($idx > 0): ?>
            <div class="text-muted" style="font-size:.75rem;">
                <i class="fas fa-chevron-right"></i>
            </div>
            <?php endif; ?>
            <a href="<?= url('leads?status_id=' . $st['id']) ?>"
               class="btn btn-sm rounded-pill px-3 pipeline-pill text-decoration-none"
               style="<?= $pillStyle ?> font-size:.75rem; transition:all .2s;">
                <?= e($st['name']) ?>
                <?php if (!empty($st['count'])): ?>
                <span class="ms-1 opacity-75">(<?= (int)$st['count'] ?>)</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php if (!empty($activeStatusId)): ?>
            <a href="<?= url('leads') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-2"
               style="font-size:.75rem;">
                <i class="fas fa-times me-1"></i>Clear
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Filters ────────────────────────────────────────────────────────────── -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <!-- Row 1: Main filters -->
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-sm" name="search"
                           placeholder="Name, phone, email, lead#..."
                           value="<?= e($filters['search'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="status_id">
                        <option value="">All Statuses</option>
                        <?php foreach ($statuses as $st): ?>
                        <option value="<?= $st['id'] ?>"
                            <?= ($filters['status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
                            <?= e($st['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="priority">
                        <option value="">All Priorities</option>
                        <option value="hot"  <?= ($filters['priority'] ?? '') === 'hot'  ? 'selected' : '' ?>>
                            🔥 Hot
                        </option>
                        <option value="warm" <?= ($filters['priority'] ?? '') === 'warm' ? 'selected' : '' ?>>
                            Warm
                        </option>
                        <option value="cold" <?= ($filters['priority'] ?? '') === 'cold' ? 'selected' : '' ?>>
                            ❄️ Cold
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm" name="source_id">
                        <option value="">All Sources</option>
                        <?php foreach ($sources as $src): ?>
                        <option value="<?= $src['id'] ?>"
                            <?= ($filters['source_id'] ?? '') == $src['id'] ? 'selected' : '' ?>>
                            <?= e($src['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
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
                <div class="col-md-1">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="<?= url('leads') ?>" class="btn btn-sm btn-outline-secondary" title="Clear filters">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Row 2: More filters (collapsible) -->
            <div class="collapse mt-2" id="moreFilters">
                <div class="row g-2 align-items-end pt-2 border-top">
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="course_id">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= ($filters['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" name="department_id">
                            <option value="">All Departments</option>
                            <?php foreach ($departments ?? [] as $dept): ?>
                            <option value="<?= $dept['id'] ?>"
                                <?= ($filters['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                <?= e($dept['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_from"
                               value="<?= e($filters['date_from'] ?? '') ?>" title="Created from">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control form-control-sm" name="date_to"
                               value="<?= e($filters['date_to'] ?? '') ?>" title="Created to">
                    </div>
                    <div class="col-md-2 d-flex align-items-center gap-2">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="followup_overdue"
                                   value="1" id="chkOverdue"
                                   <?= !empty($filters['followup_overdue']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="chkOverdue">
                                Overdue follow-ups only
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-2">
                <a class="btn btn-link btn-sm p-0 text-muted" data-bs-toggle="collapse"
                   href="#moreFilters" role="button"
                   aria-expanded="<?= (!empty($filters['course_id']) || !empty($filters['department_id']) || !empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['followup_overdue'])) ? 'true' : 'false' ?>"
                   aria-controls="moreFilters">
                    <i class="fas fa-sliders-h me-1"></i>More Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ── Leads Table ────────────────────────────────────────────────────────── -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            Total: <strong><?= number_format($leads['total'] ?? 0) ?></strong>
            lead<?= ($leads['total'] ?? 0) !== 1 ? 's' : '' ?>
        </span>
        <div class="d-flex gap-2 align-items-center">
            <small class="text-muted">
                <?php
                $activeCount = count(array_filter($filters ?? [], fn($v) => $v !== '' && $v !== null));
                if ($activeCount > 0): ?>
                <span class="badge bg-soft-primary text-primary">
                    <?= $activeCount ?> filter<?= $activeCount > 1 ? 's' : '' ?> active
                </span>
                <?php endif; ?>
            </small>
        </div>
    </div>

    <div class="card-body p-0">
        <?php if (empty($leads['data'])): ?>
        <!-- Empty state -->
        <div class="text-center py-5">
            <i class="fas fa-user-slash fa-3x text-muted mb-3 d-block"></i>
            <p class="text-muted mb-1 fw-semibold">No leads found</p>
            <p class="text-muted small mb-3">
                <?php if (!empty(array_filter($filters ?? []))): ?>
                    Try adjusting your filters or
                    <a href="<?= url('leads') ?>">clear all filters</a>.
                <?php else: ?>
                    Get started by adding your first lead.
                <?php endif; ?>
            </p>
            <?php if (hasPermission('leads.create')): ?>
            <a href="<?= url('leads/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add First Lead
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:100px;">Lead #</th>
                        <th>Name &amp; Phone</th>
                        <th>Course</th>
                        <th style="width:80px;">Priority</th>
                        <th style="width:110px;">Status</th>
                        <th>Counselor</th>
                        <th style="width:120px;">Next Follow-up</th>
                        <th style="width:60px;">Score</th>
                        <th style="width:90px;">Created</th>
                        <th style="width:160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $today = date('Y-m-d');
                    foreach ($leads['data'] as $lead):
                        $fullName = trim(e($lead['first_name']) . ' ' . e($lead['last_name'] ?? ''));
                        $priority = $lead['priority'] ?? 'warm';
                        $isConverted = !empty($lead['is_converted']) || (isset($lead['status_name']) && strtolower($lead['status_name']) === 'converted');
                        $nextFollowup = $lead['next_followup_date'] ?? '';
                        $isOverdue    = $nextFollowup && $nextFollowup < $today;
                        $score        = (int)($lead['lead_score'] ?? 0);

                        // Priority badge
                        if ($priority === 'hot') {
                            $priorityBadge = '<span class="badge bg-danger"><i class="fas fa-fire me-1"></i>Hot</span>';
                        } elseif ($priority === 'cold') {
                            $priorityBadge = '<span class="badge bg-info"><i class="fas fa-snowflake me-1"></i>Cold</span>';
                        } else {
                            $priorityBadge = '<span class="badge bg-warning text-dark">Warm</span>';
                        }
                    ?>
                    <tr>
                        <!-- Lead # -->
                        <td>
                            <a href="<?= url('leads/' . $lead['id']) ?>" class="text-decoration-none">
                                <code class="fs-xs"><?= e($lead['lead_number'] ?? $lead['id']) ?></code>
                            </a>
                        </td>

                        <!-- Name + Phone -->
                        <td>
                            <div class="fw-semibold">
                                <a href="<?= url('leads/' . $lead['id']) ?>"
                                   class="text-dark text-decoration-none">
                                    <?= $fullName ?>
                                </a>
                                <?php if (!empty($lead['is_duplicate'])): ?>
                                <span class="badge bg-soft-warning text-warning ms-1"
                                      title="Possible duplicate">
                                    <i class="fas fa-copy"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">
                                <a href="tel:<?= e($lead['phone']) ?>"
                                   class="text-muted text-decoration-none">
                                    <i class="fas fa-phone-alt me-1" style="font-size:.65rem;"></i><?= e($lead['phone']) ?>
                                </a>
                            </small>
                        </td>

                        <!-- Course -->
                        <td>
                            <small><?= e($lead['course_name'] ?? '—') ?></small>
                            <?php if (!empty($lead['department_name'])): ?>
                            <br><small class="text-muted"><?= e($lead['department_name']) ?></small>
                            <?php endif; ?>
                        </td>

                        <!-- Priority -->
                        <td><?= $priorityBadge ?></td>

                        <!-- Status -->
                        <td>
                            <?php
                            $statusColor = !empty($lead['status_color']) ? $lead['status_color'] : '#6c757d';
                            $statusName  = e($lead['status_name'] ?? '—');
                            ?>
                            <span class="badge d-inline-flex align-items-center gap-1"
                                  style="background-color:<?= $statusColor ?>;">
                                <span style="width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.6);display:inline-block;"></span>
                                <?= $statusName ?>
                            </span>
                        </td>

                        <!-- Counselor -->
                        <td>
                            <?php if (!empty($lead['assigned_name'])): ?>
                            <small><?= e($lead['assigned_name']) ?></small>
                            <?php else: ?>
                            <small class="text-danger"><i class="fas fa-user-slash me-1"></i>Unassigned</small>
                            <?php endif; ?>
                        </td>

                        <!-- Next Follow-up -->
                        <td>
                            <?php if ($nextFollowup): ?>
                            <small class="<?= $isOverdue ? 'text-danger fw-semibold' : 'text-muted' ?>">
                                <?php if ($isOverdue): ?>
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <?php endif; ?>
                                <?= formatDate($nextFollowup, 'd M') ?>
                            </small>
                            <?php else: ?>
                            <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>

                        <!-- Score -->
                        <td>
                            <?php if ($score > 0): ?>
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress flex-grow-1" style="height:5px;min-width:32px;">
                                    <div class="progress-bar <?= $score >= 70 ? 'bg-success' : ($score >= 40 ? 'bg-warning' : 'bg-danger') ?>"
                                         style="width:<?= min($score, 100) ?>%"></div>
                                </div>
                                <small class="text-muted" style="font-size:.7rem;"><?= $score ?></small>
                            </div>
                            <?php else: ?>
                            <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>

                        <!-- Created -->
                        <td>
                            <small class="text-muted"><?= formatDate($lead['created_at'], 'd M y') ?></small>
                        </td>

                        <!-- Actions -->
                        <td>
                            <div class="btn-group btn-group-sm">
                                <!-- View -->
                                <a href="<?= url('leads/' . $lead['id']) ?>"
                                   class="btn btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <!-- Edit -->
                                <?php if (hasPermission('leads.edit')): ?>
                                <a href="<?= url('leads/' . $lead['id'] . '/edit') ?>"
                                   class="btn btn-outline-secondary" title="Edit">
                                    <i class="fas fa-pencil"></i>
                                </a>
                                <?php endif; ?>

                                <!-- Quick Status (only if not converted) -->
                                <?php if (!$isConverted && hasPermission('leads.edit')): ?>
                                <div class="dropdown">
                                    <button class="btn btn-outline-info btn-sm dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            title="Quick Status">
                                        <i class="fas fa-bolt"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><span class="dropdown-header small">Change Status</span></li>
                                        <?php foreach ($statuses as $st): ?>
                                        <li>
                                            <form method="POST"
                                                  action="<?= url('leads/' . $lead['id'] . '/status') ?>">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="status_id"
                                                       value="<?= $st['id'] ?>">
                                                <button type="submit" class="dropdown-item small">
                                                    <span class="me-2" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:<?= e($st['color'] ?? '#6c757d') ?>;"></span>
                                                    <?= e($st['name']) ?>
                                                </button>
                                            </form>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>

                                <!-- Delete -->
                                <?php if (hasPermission('leads.delete')): ?>
                                <form method="POST"
                                      action="<?= url('leads/' . $lead['id'] . '/delete') ?>"
                                      class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit"
                                            class="btn btn-outline-danger btn-delete"
                                            data-name="<?= $fullName ?>"
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
    <?php if (($leads['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $leads;
        $baseUrl    = url('leads') . '?' . http_build_query(array_filter($filters ?? []));
        ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>

<script>
// Confirm delete
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var name = this.dataset.name || 'this lead';
            if (!confirm('Delete lead "' + name + '"? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Auto-expand More Filters if any advanced filter is active
    var moreFilters = document.getElementById('moreFilters');
    if (moreFilters && moreFilters.getAttribute('aria-expanded') !== 'false') {
        var bsCollapse = new bootstrap.Collapse(moreFilters, { toggle: false });
        bsCollapse.show();
    }
});
</script>
