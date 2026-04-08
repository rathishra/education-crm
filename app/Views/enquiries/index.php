<?php $pageTitle = 'Enquiry Management'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-comments me-2 text-primary"></i>Enquiry Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Enquiries</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <?php if (hasPermission('enquiries.view')): ?>
        <a href="<?= url('enquiries/export') . '?' . http_build_query(array_filter($filters ?? [])) ?>"
           class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-csv me-1"></i>Export CSV
        </a>
        <?php endif; ?>
        <?php if (hasPermission('enquiries.create')): ?>
        <a href="<?= url('enquiries/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Enquiry
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-list-alt"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <a href="<?= url('enquiries?status=new') ?>" class="text-decoration-none">
            <div class="stat-card stat-sky py-3">
                <div class="stat-icon"><i class="fas fa-inbox"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['new_count'] ?? 0) ?></div>
                    <div class="stat-label">New</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="<?= url('enquiries?status=interested') ?>" class="text-decoration-none">
            <div class="stat-card stat-emerald py-3">
                <div class="stat-icon"><i class="fas fa-thumbs-up"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['interested'] ?? 0) ?></div>
                    <div class="stat-label">Interested</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="<?= url('enquiries?priority=hot') ?>" class="text-decoration-none">
            <div class="stat-card stat-rose py-3">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['hot'] ?? 0) ?></div>
                    <div class="stat-label">Hot</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <a href="<?= url('enquiries?status=converted') ?>" class="text-decoration-none">
            <div class="stat-card stat-violet py-3">
                <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= number_format($stats['converted'] ?? 0) ?></div>
                    <div class="stat-label">Converted</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-amber py-3">
            <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['this_month'] ?? 0) ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>
</div>

<!-- Status funnel strip -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <?php
            $funnelStages = [
                ['s'=>'new',           'l'=>'New',           'color'=>'#6366f1', 'icon'=>'fa-inbox'],
                ['s'=>'contacted',     'l'=>'Contacted',     'color'=>'#06b6d4', 'icon'=>'fa-phone'],
                ['s'=>'interested',    'l'=>'Interested',    'color'=>'#10b981', 'icon'=>'fa-thumbs-up'],
                ['s'=>'not_interested','l'=>'Not Interested','color'=>'#f59e0b', 'icon'=>'fa-thumbs-down'],
                ['s'=>'converted',     'l'=>'Converted',     'color'=>'#8b5cf6', 'icon'=>'fa-exchange-alt'],
                ['s'=>'closed',        'l'=>'Closed',        'color'=>'#94a3b8', 'icon'=>'fa-times-circle'],
            ];
            $activeStatus = $filters['status'] ?? '';
            foreach ($funnelStages as $idx => $st):
                $isActive = $activeStatus === $st['s'];
                $cnt = $stats[$st['s']] ?? ($st['s'] === 'new' ? ($stats['new_count'] ?? 0) : 0);
                $style = $isActive
                    ? "background:{$st['color']};color:#fff;border-color:{$st['color']};"
                    : "border-color:{$st['color']};color:{$st['color']};background:#fff;";
            ?>
            <?php if ($idx > 0 && $idx < 4): ?><div class="text-muted" style="font-size:.65rem;"><i class="fas fa-chevron-right"></i></div><?php endif; ?>
            <?php if ($idx === 4 || $idx === 5): ?><div class="text-muted px-1">|</div><?php endif; ?>
            <a href="<?= url('enquiries?status=' . $st['s']) ?>"
               class="btn btn-sm rounded-pill px-3 text-decoration-none"
               style="<?= $style ?> font-size:.75rem;border:1px solid;transition:all .2s;">
                <i class="fas <?= $st['icon'] ?> me-1"></i><?= $st['l'] ?>
                <span class="ms-1 fw-bold opacity-75"><?= $cnt ?></span>
            </a>
            <?php endforeach; ?>
            <?php if ($activeStatus): ?>
            <a href="<?= url('enquiries') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-2" style="font-size:.75rem;">
                <i class="fas fa-times me-1"></i>Clear
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end" id="filterForm">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Name, phone, email, enquiry#..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-1">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <?php foreach (['new'=>'New','contacted'=>'Contacted','interested'=>'Interested','not_interested'=>'Not Interested','converted'=>'Converted','closed'=>'Closed'] as $sv=>$sl): ?>
                    <option value="<?= $sv ?>" <?= ($filters['status'] ?? '') === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <select class="form-select form-select-sm" name="priority">
                    <option value="">All Priority</option>
                    <option value="hot"  <?= ($filters['priority'] ?? '') === 'hot'  ? 'selected' : '' ?>>🔥 Hot</option>
                    <option value="warm" <?= ($filters['priority'] ?? '') === 'warm' ? 'selected' : '' ?>>🌡 Warm</option>
                    <option value="cold" <?= ($filters['priority'] ?? '') === 'cold' ? 'selected' : '' ?>>❄ Cold</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select form-select-sm" name="source">
                    <option value="">All Sources</option>
                    <?php foreach ($sources as $src): ?>
                    <option value="<?= e($src) ?>" <?= ($filters['source'] ?? '') === $src ? 'selected' : '' ?>><?= e($src) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select class="form-select form-select-sm" name="counselor_id">
                    <option value="">All Counselors</option>
                    <?php foreach ($counselors as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['counselor_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       title="From date" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-1">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       title="To date" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary px-3"><i class="fas fa-search"></i></button>
                <a href="<?= url('enquiries') ?>" class="btn btn-sm btn-outline-secondary px-3" title="Clear"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Action Bar -->
<div id="bulkBar" class="alert alert-primary d-none mb-3 py-2 px-3 d-flex align-items-center gap-3 flex-wrap" style="border-radius:8px;">
    <span class="fw-semibold text-primary"><span id="bulkCount">0</span> selected</span>
    <div class="d-flex gap-2 flex-wrap ms-auto">
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-tag me-1"></i>Change Status
            </button>
            <ul class="dropdown-menu shadow-sm">
                <?php foreach (['new'=>'New','contacted'=>'Contacted','interested'=>'Interested','not_interested'=>'Not Interested','closed'=>'Closed'] as $sv=>$sl): ?>
                <li><a class="dropdown-item bulk-action" href="#" data-action="status" data-value="<?= $sv ?>">
                    <?= $sl ?>
                </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if (hasPermission('enquiries.convert')): ?>
        <button class="btn btn-sm btn-outline-success bulk-action" data-action="convert_to_lead">
            <i class="fas fa-exchange-alt me-1"></i>Convert to Leads
        </button>
        <?php endif; ?>
        <?php if (hasPermission('enquiries.delete')): ?>
        <button class="btn btn-sm btn-outline-danger bulk-action" data-action="delete">
            <i class="fas fa-trash me-1"></i>Delete Selected
        </button>
        <?php endif; ?>
        <button class="btn btn-sm btn-outline-secondary" id="bulkClear">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <?php if (!empty($filters['status'])): ?>
            Showing <strong><?= number_format($enquiries['total'] ?? 0) ?></strong>
            <em><?= ucwords(str_replace('_',' ',$filters['status'])) ?></em> enquiries
            <?php else: ?>
            <strong><?= number_format($enquiries['total'] ?? 0) ?></strong> total enquiries
            <?php endif; ?>
        </span>
        <small class="text-muted">
            Conversion rate:
            <strong class="text-success">
                <?php
                $total = $stats['total'] ?? 0;
                $conv  = $stats['converted'] ?? 0;
                echo $total > 0 ? round($conv / $total * 100, 1) . '%' : '—';
                ?>
            </strong>
        </small>
    </div>
    <div class="card-body p-0">
        <?php if (empty($enquiries['data'])): ?>
        <div class="text-center py-5">
            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-2">No enquiries found.</p>
            <?php if (hasPermission('enquiries.create')): ?>
            <a href="<?= url('enquiries/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>Add First Enquiry
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="enquiriesTable">
                <thead class="table-light">
                    <tr>
                        <th width="36">
                            <input type="checkbox" class="form-check-input" id="selectAll" title="Select all">
                        </th>
                        <th>Enquiry #</th>
                        <th>Name &amp; Contact</th>
                        <th>Course / Dept</th>
                        <th>Source</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Follow-up</th>
                        <th>Counselor</th>
                        <th>Date</th>
                        <th class="text-end" width="140">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enquiries['data'] as $enq):
                        $fullName = trim($enq['first_name'] . ' ' . ($enq['last_name'] ?? ''));

                        $priorityConfig = [
                            'hot'  => ['cls'=>'bg-danger bg-opacity-10 text-danger',   'icon'=>'fa-fire',          'label'=>'Hot'],
                            'warm' => ['cls'=>'bg-warning bg-opacity-10 text-warning', 'icon'=>'fa-thermometer-half','label'=>'Warm'],
                            'cold' => ['cls'=>'bg-info bg-opacity-10 text-info',       'icon'=>'fa-snowflake',     'label'=>'Cold'],
                        ];
                        $pCfg = $priorityConfig[$enq['priority'] ?? 'warm'] ?? $priorityConfig['warm'];

                        $statusConfig = [
                            'new'           => ['cls'=>'bg-primary bg-opacity-10 text-primary',    'label'=>'New',          'style'=>''],
                            'contacted'     => ['cls'=>'bg-info bg-opacity-10 text-info',           'label'=>'Contacted',    'style'=>''],
                            'interested'    => ['cls'=>'bg-success bg-opacity-10 text-success',     'label'=>'Interested',   'style'=>''],
                            'not_interested'=> ['cls'=>'bg-secondary bg-opacity-10 text-secondary', 'label'=>'Not Interested','style'=>''],
                            'converted'     => ['cls'=>'',                                          'label'=>'Converted',    'style'=>'--bs-badge-color:#6d28d9;background:#ede9fe;color:#6d28d9;border:1px solid #c4b5fd'],
                            'closed'        => ['cls'=>'bg-light text-muted border',               'label'=>'Closed',       'style'=>''],
                        ];
                        $sCfg  = $statusConfig[$enq['status'] ?? 'new'] ?? ['cls'=>'bg-secondary','label'=>ucfirst($enq['status'] ?? 'new')];

                        // Follow-up overdue indicator
                        $fuDate    = $enq['next_followup_date'] ?? null;
                        $fuOverdue = $fuDate && $fuDate < date('Y-m-d');
                        $fuToday   = $fuDate && $fuDate === date('Y-m-d');
                    ?>
                    <tr data-id="<?= $enq['id'] ?>">
                        <td>
                            <input type="checkbox" class="form-check-input row-check" value="<?= $enq['id'] ?>">
                        </td>
                        <td>
                            <a href="<?= url('enquiries/' . $enq['id']) ?>" class="text-decoration-none fw-semibold text-primary">
                                <?= e($enq['enquiry_number']) ?>
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                     style="width:32px;height:32px;font-size:.7rem;background:<?= '#'.substr(md5($fullName),0,6) ?>">
                                    <?= strtoupper(substr($enq['first_name'],0,1).substr($enq['last_name']??'',0,1)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold lh-1">
                                        <a href="<?= url('enquiries/' . $enq['id']) ?>" class="text-dark text-decoration-none">
                                            <?= e($fullName) ?>
                                        </a>
                                    </div>
                                    <small class="text-muted">
                                        <a href="tel:<?= e($enq['phone']) ?>" class="text-muted text-decoration-none">
                                            <?= e($enq['phone']) ?>
                                        </a>
                                        <?php if (!empty($enq['email'])): ?>
                                        · <a href="mailto:<?= e($enq['email']) ?>" class="text-muted text-decoration-none"><?= e($enq['email']) ?></a>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small"><?= e($enq['course_name'] ?? '—') ?></div>
                            <?php if (!empty($enq['department_name'])): ?>
                            <small class="text-muted"><?= e($enq['department_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= e($enq['source'] ?? '—') ?></small>
                        </td>
                        <td>
                            <span class="badge <?= $pCfg['cls'] ?>">
                                <i class="fas <?= $pCfg['icon'] ?> me-1"></i><?= $pCfg['label'] ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($enq['status'] !== 'converted' && hasPermission('enquiries.edit')): ?>
                            <div class="dropdown d-inline-block">
                                <button class="badge <?= $sCfg['cls'] ?> border-0 dropdown-toggle"
                                        style="cursor:pointer;font-size:.75rem;"
                                        data-bs-toggle="dropdown" title="Click to change status">
                                    <?= $sCfg['label'] ?>
                                </button>
                                <ul class="dropdown-menu shadow-sm" style="min-width:160px;">
                                    <?php foreach (['new'=>'New','contacted'=>'Contacted','interested'=>'Interested','not_interested'=>'Not Interested','closed'=>'Closed'] as $sv=>$sl): ?>
                                    <?php if ($sv !== ($enq['status'] ?? 'new')): ?>
                                    <li>
                                        <a class="dropdown-item quick-status-change small" href="#"
                                           data-id="<?= $enq['id'] ?>" data-status="<?= $sv ?>">
                                            <?= $sl ?>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php else: ?>
                            <span class="badge <?= $sCfg['cls'] ?>" style="<?= $sCfg['style'] ?? '' ?>"><?= $sCfg['label'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($fuDate): ?>
                            <span class="small <?= $fuOverdue ? 'text-danger fw-semibold' : ($fuToday ? 'text-warning fw-semibold' : 'text-muted') ?>">
                                <?php if ($fuOverdue): ?><i class="fas fa-exclamation-circle me-1"></i><?php elseif ($fuToday): ?><i class="fas fa-bell me-1"></i><?php endif; ?>
                                <?= date('d M', strtotime($fuDate)) ?>
                                <?php if ($fuOverdue): ?><br><small>Overdue</small><?php elseif ($fuToday): ?><br><small>Today</small><?php endif; ?>
                            </span>
                            <?php else: ?>
                            <small class="text-muted">—</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= e($enq['counselor_name'] ?? $enq['assigned_to_name'] ?? '—') ?></small>
                        </td>
                        <td>
                            <small class="text-muted"><?= formatDate($enq['created_at']) ?></small>
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                <a href="<?= url('enquiries/' . $enq['id']) ?>"
                                   class="btn btn-sm btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (hasPermission('enquiries.edit') && $enq['status'] !== 'converted'): ?>
                                <a href="<?= url('enquiries/' . $enq['id'] . '/edit') ?>"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('enquiries.convert') && $enq['status'] !== 'converted'): ?>
                                <form method="POST" action="<?= url('enquiries/' . $enq['id'] . '/convert') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Convert to Lead"
                                            onclick="return confirm('Convert this enquiry to a lead?')">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                                <?php if (hasPermission('enquiries.delete')): ?>
                                <form method="POST" action="<?= url('enquiries/' . $enq['id'] . '/delete') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete"
                                            data-name="<?= e($fullName) ?>" title="Delete">
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

    <?php if (($enquiries['last_page'] ?? 1) > 1): ?>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Showing <?= number_format($enquiries['from'] ?? 0) ?>–<?= number_format($enquiries['to'] ?? 0) ?>
            of <?= number_format($enquiries['total'] ?? 0) ?>
        </small>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php for ($p = 1; $p <= ($enquiries['last_page'] ?? 1); $p++): ?>
                <li class="page-item <?= $p === ($enquiries['current_page'] ?? 1) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= url('enquiries?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Select All ──────────────────────────────────────────────────────────
    document.getElementById('selectAll')?.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        updateBulkBar();
    });
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('row-check')) updateBulkBar();
    });

    function getSelected() {
        return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
    }

    function updateBulkBar() {
        const ids = getSelected();
        const bar = document.getElementById('bulkBar');
        const cnt = document.getElementById('bulkCount');
        if (!bar) return;
        cnt.textContent = ids.length;
        if (ids.length > 0) {
            bar.classList.remove('d-none');
            bar.classList.add('d-flex');
        } else {
            bar.classList.add('d-none');
            bar.classList.remove('d-flex');
        }
    }

    document.getElementById('bulkClear')?.addEventListener('click', function () {
        document.querySelectorAll('.row-check, #selectAll').forEach(cb => cb.checked = false);
        updateBulkBar();
    });

    // ── Bulk Actions ─────────────────────────────────────────────────────────
    document.querySelectorAll('.bulk-action').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            const ids    = getSelected();
            if (!ids.length) { toastr.warning('Select at least one enquiry.'); return; }
            const action = this.dataset.action;
            const value  = this.dataset.value;

            if (action === 'delete') {
                if (!confirm(`Delete ${ids.length} enquiry(s)? This cannot be undone.`)) return;
            }
            if (action === 'convert_to_lead') {
                if (!confirm(`Convert ${ids.length} enquiry(s) to leads?`)) return;
            }

            fetch('<?= url('enquiries/bulk') ?>', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body:    JSON.stringify({ action, value, ids })
            })
            .then(r => r.json())
            .then(function (data) {
                if (data.status === 'success') {
                    toastr.success(data.message);
                    setTimeout(() => location.reload(), 800);
                } else {
                    toastr.error(data.message || 'Failed.');
                }
            })
            .catch(() => toastr.error('Network error.'));
        });
    });

    // ── Quick Status Change ──────────────────────────────────────────────────
    document.querySelectorAll('.quick-status-change').forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const id     = this.dataset.id;
            const status = this.dataset.status;
            // Optimistically update badge text while request fires
            const badge = this.closest('.dropdown')?.querySelector('button.badge');
            const origText = badge ? badge.textContent.trim() : '';

            fetch(`<?= url('enquiries') ?>/${id}/quick-status`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body:    JSON.stringify({ status })
            })
            .then(r => r.json())
            .then(function (data) {
                if (data.status === 'success') {
                    toastr.success('Status updated.');
                    setTimeout(() => location.reload(), 500);
                } else {
                    toastr.error(data.message || 'Failed.');
                }
            })
            .catch(() => toastr.error('Network error.'));
        });
    });

    // ── Delete Confirmation ──────────────────────────────────────────────────
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(`Delete enquiry for "${this.dataset.name}"?`)) e.preventDefault();
        });
    });
});
</script>
