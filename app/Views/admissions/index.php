<?php $pageTitle = 'Admissions'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-graduate me-2 text-primary"></i>Admissions</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Admissions</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('admissions/export?' . http_build_query(array_filter($filters))) ?>"
           class="btn btn-outline-success btn-sm">
            <i class="fas fa-download me-1"></i>Export CSV
        </a>
        <?php if (hasPermission('admissions.create')): ?>
        <a href="<?= url('admissions/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Application
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Pipeline Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-warning py-3">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['pending']) ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-info py-3">
            <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['document_pending']) ?></div>
                <div class="stat-label">Docs Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-primary py-3">
            <div class="stat-icon"><i class="fas fa-money-bill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['payment_pending']) ?></div>
                <div class="stat-label">Pay Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-success py-3">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format(($stats['confirmed'] ?? 0) + ($stats['enrolled'] ?? 0)) ?></div>
                <div class="stat-label">Confirmed / Enrolled</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-amber py-3">
            <div class="stat-icon"><i class="fas fa-calendar-plus"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['this_month']) ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>
</div>

<!-- Pipeline Stage Flow -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-1 flex-wrap" id="admissionPipeline">
            <?php
            $pipelineStages = [
                ['status'=>'applied',          'label'=>'Applied',      'color'=>'#6366f1', 'icon'=>'fa-file-alt'],
                ['status'=>'pending',          'label'=>'Pending',      'color'=>'#f59e0b', 'icon'=>'fa-clock'],
                ['status'=>'document_pending', 'label'=>'Docs Pending', 'color'=>'#06b6d4', 'icon'=>'fa-folder-open'],
                ['status'=>'payment_pending',  'label'=>'Pay Pending',  'color'=>'#3b82f6', 'icon'=>'fa-money-bill'],
                ['status'=>'confirmed',        'label'=>'Confirmed',    'color'=>'#10b981', 'icon'=>'fa-check-circle'],
                ['status'=>'enrolled',         'label'=>'Enrolled',     'color'=>'#22c55e', 'icon'=>'fa-user-graduate'],
                ['status'=>'rejected',         'label'=>'Rejected',     'color'=>'#ef4444', 'icon'=>'fa-times-circle'],
            ];
            $activeStatus = $filters['status'] ?? '';
            foreach ($pipelineStages as $idx => $stage):
                $isActive = $activeStatus === $stage['status'];
                $count = $stats[$stage['status']] ?? 0;
                $pillStyle = $isActive
                    ? 'background-color:'.$stage['color'].';color:#fff;border-color:'.$stage['color'].';'
                    : 'border-color:'.$stage['color'].';color:'.$stage['color'].';background:#fff;';
            ?>
            <?php if ($idx > 0 && $idx < 6): ?>
            <div class="text-muted" style="font-size:.7rem;"><i class="fas fa-chevron-right"></i></div>
            <?php elseif ($idx === 6): ?>
            <div class="ms-2 text-muted small">|</div>
            <?php endif; ?>
            <a href="<?= url('admissions?status=' . $stage['status']) ?>"
               class="btn btn-sm rounded-pill px-3 text-decoration-none"
               style="<?= $pillStyle ?> font-size:.75rem; border: 1px solid; transition:all .2s;">
                <i class="fas <?= $stage['icon'] ?> me-1"></i>
                <?= $stage['label'] ?>
                <span class="ms-1 opacity-75 fw-bold"><?= $count ?></span>
            </a>
            <?php endforeach; ?>
            <?php if ($activeStatus): ?>
            <a href="<?= url('admissions') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 ms-2"
               style="font-size:.75rem;">
                <i class="fas fa-times me-1"></i>Clear
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('admissions') ?>" class="row g-2 align-items-end" id="filterForm">
            <div class="col-12 col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="Search name, phone, email, number..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach (['draft'=>'Draft','applied'=>'Applied','pending'=>'Pending','document_pending'=>'Docs Pending','payment_pending'=>'Pay Pending','confirmed'=>'Confirmed','enrolled'=>'Enrolled','rejected'=>'Rejected','cancelled'=>'Cancelled'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($filters['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="counselor_id" class="form-select form-select-sm">
                    <option value="">All Counselors</option>
                    <?php foreach ($counselors as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filters['counselor_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                            <?= e($u['first_name'] . ' ' . $u['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Payment</option>
                    <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                    <option value="paid"    <?= ($filters['payment_status'] ?? '') === 'paid'    ? 'selected' : '' ?>>Paid</option>
                </select>
            </div>
            <div class="col-6 col-md-1">
                <input type="date" name="date_from" class="form-control form-control-sm"
                       title="From date" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-1">
                <input type="date" name="date_to" class="form-control form-control-sm"
                       title="To date" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-auto d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-search"></i></button>
                <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary btn-sm px-3"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Action Bar (hidden until rows selected) -->
<?php if (hasPermission('admissions.approve')): ?>
<div id="bulkBar" class="alert alert-primary d-none mb-3 py-2 px-3 d-flex align-items-center gap-3 flex-wrap" style="border-radius:8px;">
    <span class="fw-semibold text-primary"><span id="bulkCount">0</span> selected</span>
    <div class="d-flex gap-2 flex-wrap ms-auto">
        <select id="bulkStatusSel" class="form-select form-select-sm" style="width:auto;">
            <option value="">Change Status To…</option>
            <option value="pending">Pending</option>
            <option value="document_pending">Docs Pending</option>
            <option value="payment_pending">Pay Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="cancelled">Cancelled</option>
            <option value="rejected">Rejected</option>
        </select>
        <button class="btn btn-sm btn-primary" id="btnBulkStatus"><i class="fas fa-sync-alt me-1"></i>Apply Status</button>
        <button class="btn btn-sm btn-outline-success" id="btnBulkExport"><i class="fas fa-download me-1"></i>Export Selected</button>
        <button class="btn btn-sm btn-outline-danger" id="btnBulkDelete"><i class="fas fa-trash me-1"></i>Delete</button>
        <button class="btn btn-sm btn-outline-secondary" id="btnBulkClear"><i class="fas fa-times"></i></button>
    </div>
</div>
<?php endif; ?>

<!-- Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <?php if ($activeStatus): ?>
            Showing <strong><?= number_format($admissions['total'] ?? 0) ?></strong>
            <em><?= ucfirst(str_replace('_',' ',$activeStatus)) ?></em> applications
            <?php else: ?>
            <strong><?= number_format($admissions['total'] ?? 0) ?></strong> total applications
            <?php endif; ?>
        </span>
        <small class="text-muted">
            Conversion rate:
            <strong class="text-success">
                <?php
                $total   = $stats['total'] ?? 0;
                $enrolled = ($stats['enrolled'] ?? 0) + ($stats['confirmed'] ?? 0);
                echo $total > 0 ? round($enrolled / $total * 100, 1) . '%' : '—';
                ?>
            </strong>
        </small>
    </div>
    <div class="card-body p-0">
        <?php if (empty($admissions['data'])): ?>
            <div class="text-center py-5">
                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No admissions found</h5>
                <?php if (hasPermission('admissions.create')): ?>
                    <a href="<?= url('admissions/create') ?>" class="btn btn-primary mt-2"><i class="fas fa-plus me-1"></i>New Application</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="admissionsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="32">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Admission #</th>
                            <th>Applicant</th>
                            <th>Course</th>
                            <th>Counselor</th>
                            <th>Status</th>
                            <th>Fee / Balance</th>
                            <th>Payment</th>
                            <th>Applied</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admissions['data'] as $adm):
                            $statusLabels = \App\Models\Admission::STATUS_LABELS;
                            [$statusLabel, $statusClass] = $statusLabels[$adm['status']] ?? [ucfirst($adm['status']), 'bg-secondary'];
                            $payColors = ['pending'=>'warning','partial'=>'info','paid'=>'success'];
                            $payColor  = $payColors[$adm['payment_status'] ?? 'pending'] ?? 'secondary';
                            $finalFee  = (float)($adm['final_fee'] ?? $adm['total_fee'] ?? 0);
                            $paid      = (float)($adm['paid_amount'] ?? 0);
                            $feePercent = $finalFee > 0 ? min(100, round($paid / $finalFee * 100)) : 0;
                        ?>
                        <tr data-id="<?= $adm['id'] ?>">
                            <td>
                                <input type="checkbox" class="form-check-input row-check" value="<?= $adm['id'] ?>">
                            </td>
                            <td>
                                <a href="<?= url('admissions/' . $adm['id']) ?>" class="fw-semibold text-primary text-decoration-none">
                                    <?= e($adm['admission_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                                         style="width:34px;height:34px;font-size:.75rem;background:<?= '#' . substr(md5($adm['first_name']), 0, 6) ?>">
                                        <?= strtoupper(substr($adm['first_name'], 0, 1) . substr($adm['last_name'] ?? '', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="fw-semibold lh-1"><?= e($adm['first_name'] . ' ' . ($adm['last_name'] ?? '')) ?></div>
                                        <small class="text-muted"><?= e($adm['phone']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small"><?= e($adm['course_name'] ?? '—') ?></div>
                                <?php if (!empty($adm['academic_year_name'])): ?>
                                <small class="text-muted"><?= e($adm['academic_year_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?= e($adm['counselor_name'] ?? '—') ?></small>
                            </td>
                            <td>
                                <?php if (hasPermission('admissions.approve') && !in_array($adm['status'], ['enrolled'])): ?>
                                <select class="form-select form-select-sm quick-status-sel border-0 p-0 fw-semibold"
                                        data-id="<?= $adm['id'] ?>"
                                        style="background:transparent;width:auto;font-size:.8rem;cursor:pointer;">
                                    <?php foreach (['pending','document_pending','payment_pending','confirmed','cancelled','rejected'] as $sv): ?>
                                    <option value="<?= $sv ?>" <?= $adm['status'] === $sv ? 'selected' : '' ?>>
                                        <?= ucfirst(str_replace('_', ' ', $sv)) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php else: ?>
                                <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="min-width:110px;">
                                <?php if ($finalFee > 0): ?>
                                <div class="d-flex justify-content-between" style="font-size:.72rem;">
                                    <span class="text-muted">₹<?= number_format($paid, 0) ?></span>
                                    <span class="text-muted">/ <?= number_format($finalFee, 0) ?></span>
                                </div>
                                <div class="progress mt-1" style="height:4px;border-radius:4px;">
                                    <div class="progress-bar <?= $feePercent >= 100 ? 'bg-success' : ($feePercent > 0 ? 'bg-info' : 'bg-warning') ?>"
                                         style="width:<?= $feePercent ?>%"></div>
                                </div>
                                <?php else: ?>
                                <small class="text-muted">—</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $payColor ?>-subtle text-<?= $payColor ?> border border-<?= $payColor ?>">
                                    <?= ucfirst($adm['payment_status'] ?? 'pending') ?>
                                </span>
                            </td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($adm['application_date'] ?? $adm['created_at'])) ?></small></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?= url('admissions/' . $adm['id']) ?>" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (hasPermission('admissions.edit') && !in_array($adm['status'], ['enrolled','cancelled'])): ?>
                                    <a href="<?= url('admissions/' . $adm['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('admissions.approve') && in_array($adm['status'], ['pending','document_pending','payment_pending'])): ?>
                                    <form method="POST" action="<?= url('admissions/' . $adm['id'] . '/approve') ?>" class="d-inline quick-approve-form">
                                        <?= csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-success" title="Confirm" onclick="return confirm('Confirm this admission?')">
                                            <i class="fas fa-check"></i>
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

            <!-- Pagination -->
            <?php if (($admissions['last_page'] ?? 1) > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">
                        Showing <?= number_format($admissions['from'] ?? 0) ?>–<?= number_format($admissions['to'] ?? 0) ?>
                        of <?= number_format($admissions['total'] ?? 0) ?>
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= ($admissions['last_page'] ?? 1); $p++): ?>
                                <li class="page-item <?= $p === ($admissions['current_page'] ?? 1) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('admissions?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
const BULK_URL      = '<?= url('admissions/bulk') ?>';
const QSTATUS_BASE  = '<?= url('admissions') ?>';

// ── Select All ─────────────────────────────────────────────────────────────
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBar();
});
document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateBulkBar));

function getSelected() {
    return [...document.querySelectorAll('.row-check:checked')].map(cb => parseInt(cb.value));
}

function updateBulkBar() {
    const ids  = getSelected();
    const bar  = document.getElementById('bulkBar');
    const cnt  = document.getElementById('bulkCount');
    if (!bar) return;
    if (ids.length > 0) {
        bar.classList.remove('d-none');
        bar.classList.add('d-flex');
        cnt.textContent = ids.length;
    } else {
        bar.classList.add('d-none');
        bar.classList.remove('d-flex');
    }
}

// ── Bulk Status ─────────────────────────────────────────────────────────────
document.getElementById('btnBulkStatus')?.addEventListener('click', function () {
    const status = document.getElementById('bulkStatusSel').value;
    const ids    = getSelected();
    if (!status) { toastr.warning('Select a status to apply'); return; }
    if (!ids.length) { toastr.warning('No rows selected'); return; }
    if (!confirm(`Change ${ids.length} application(s) to "${status.replace('_',' ')}"?`)) return;
    bulkPost({ ids, action: status });
});

// ── Bulk Delete ─────────────────────────────────────────────────────────────
document.getElementById('btnBulkDelete')?.addEventListener('click', function () {
    const ids = getSelected();
    if (!ids.length) return;
    if (!confirm(`Permanently delete ${ids.length} application(s)? This cannot be undone.`)) return;
    bulkPost({ ids, action: 'delete' });
});

// ── Bulk Export (export selected IDs via CSV) ───────────────────────────────
document.getElementById('btnBulkExport')?.addEventListener('click', function () {
    const ids = getSelected();
    if (!ids.length) return;
    // Export just filtered list; pass IDs as query string
    const url = '<?= url('admissions/export') ?>?ids=' + ids.join(',');
    window.location.href = url;
});

// ── Clear Selection ─────────────────────────────────────────────────────────
document.getElementById('btnBulkClear')?.addEventListener('click', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = false);
    const sa = document.getElementById('selectAll');
    if (sa) sa.checked = false;
    updateBulkBar();
});

async function bulkPost(payload) {
    try {
        const res  = await fetch(BULK_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 900);
        } else {
            toastr.error(data.message || 'Action failed');
        }
    } catch(e) {
        toastr.error('Server error');
    }
}

// ── Quick Status Change ─────────────────────────────────────────────────────
document.querySelectorAll('.quick-status-sel').forEach(sel => {
    sel.addEventListener('change', async function () {
        const id     = this.dataset.id;
        const status = this.value;
        const orig   = this.querySelector('[selected]')?.value || this.dataset.orig;
        this.dataset.orig = orig;
        try {
            const res  = await fetch(`${QSTATUS_BASE}/${id}/quick-status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status })
            });
            const data = await res.json();
            if (data.status === 'success') {
                toastr.success(`Status updated to ${data.label}`);
                // Update selected option
                [...this.options].forEach(o => o.removeAttribute('selected'));
                this.querySelector(`option[value="${status}"]`)?.setAttribute('selected', 'selected');
            } else {
                toastr.error(data.message || 'Failed');
                this.value = this.dataset.orig || status;
            }
        } catch(e) {
            toastr.error('Server error');
        }
    });
    // Track initial value
    sel.dataset.orig = sel.value;
});
</script>
