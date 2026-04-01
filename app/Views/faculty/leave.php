<?php $pageTitle = 'Faculty Leave Management'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-1"><i class="fas fa-calendar-alt me-2 text-primary"></i>Leave Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('faculty') ?>">Faculty</a></li>
                <li class="breadcrumb-item active">Leave</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
        <i class="fas fa-plus me-1"></i>New Leave Request
    </button>
</div>

<!-- ── STAT CARDS ─────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $leaveStats = [
        ['pending',  'Pending',  'warning', 'fa-clock'],
        ['approved', 'Approved', 'success', 'fa-check-circle'],
        ['rejected', 'Rejected', 'danger',  'fa-times-circle'],
        ['cancelled','Cancelled','secondary','fa-ban'],
    ];
    foreach ($leaveStats as [$k,$label,$cls,$icon]):
        $cnt = (int)($summary[$k] ?? 0);
    ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 <?= $statusFilter === $k ? 'border-'.$cls.' border-2' : '' ?>">
            <a href="<?= url("faculty/leave?status=$k") ?>" class="text-decoration-none">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-<?= $cls ?> bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                        <i class="fas <?= $icon ?> text-<?= $cls ?>"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark"><?= $cnt ?></div>
                        <div class="text-muted small"><?= $label ?></div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── FILTERS ────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="faculty_id" class="form-select form-select-sm">
                    <option value="">All Faculty</option>
                    <?php foreach ($facultyList as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= $facultyId == $f['id'] ? 'selected' : '' ?>>
                        <?= e($f['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Status</option>
                    <option value="pending"   <?= $statusFilter === 'pending'   ? 'selected' : '' ?>>Pending</option>
                    <option value="approved"  <?= $statusFilter === 'approved'  ? 'selected' : '' ?>>Approved</option>
                    <option value="rejected"  <?= $statusFilter === 'rejected'  ? 'selected' : '' ?>>Rejected</option>
                    <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= url('faculty/leave') ?>" class="btn btn-light btn-sm w-100">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- ── TABLE ──────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
        <span class="fw-semibold">
            <?= ucfirst($statusFilter === 'all' ? 'All' : $statusFilter) ?> Leave Requests
            <span class="badge bg-secondary ms-1"><?= count($leaves) ?></span>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Faculty</th>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th class="text-center">Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actioned By</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leaves)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-calendar-check fa-2x mb-2 d-block opacity-25"></i>
                        No leave requests found.
                    </td></tr>
                    <?php else: ?>
                    <?php foreach ($leaves as $l):
                        $sCls = ['pending'=>'warning text-dark','approved'=>'success','rejected'=>'danger','cancelled'=>'secondary'];
                        $sc   = $sCls[$l['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td>
                            <a href="<?= url("faculty/{$l['user_id']}") ?>" class="fw-semibold text-dark text-decoration-none">
                                <?= e($l['faculty_name']) ?>
                            </a>
                            <div class="text-muted" style="font-size:.7rem"><?= e($l['designation'] ?: '') ?></div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= ucfirst(str_replace('_',' ',$l['leave_type'])) ?></span>
                        </td>
                        <td><?= date('d M Y', strtotime($l['start_date'])) ?></td>
                        <td><?= date('d M Y', strtotime($l['end_date'])) ?></td>
                        <td class="text-center fw-bold"><?= (int)$l['days'] ?></td>
                        <td class="text-muted">
                            <?= $l['reason'] ? e(strlen($l['reason']) > 50 ? substr($l['reason'],0,50).'…' : $l['reason']) : '—' ?>
                        </td>
                        <td><span class="badge bg-<?= $sc ?>"><?= ucfirst($l['status']) ?></span></td>
                        <td class="text-muted small"><?= e($l['approved_by_name'] ?: '—') ?></td>
                        <td class="text-center">
                            <?php if ($l['status'] === 'pending'): ?>
                            <form method="POST" action="<?= url("faculty/leave/{$l['id']}/action") ?>" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-xs btn-success py-0 px-2"
                                        onclick="return confirm('Approve this leave?')" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <form method="POST" action="<?= url("faculty/leave/{$l['id']}/action") ?>" class="d-inline ms-1">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-xs btn-danger py-0 px-2"
                                        onclick="return confirm('Reject this leave?')" title="Reject">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── APPLY LEAVE MODAL ──────────────────────────────────────── -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('faculty/leave/apply') ?>">
                <?= csrfField() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2 text-primary"></i>New Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Faculty Member <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select select2" required>
                            <option value="">— Select Faculty —</option>
                            <?php foreach ($facultyList as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= $facultyId == $f['id'] ? 'selected' : '' ?>>
                                <?= e($f['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Leave Type</label>
                        <select name="leave_type" class="form-select">
                            <option value="casual">Casual Leave (CL)</option>
                            <option value="sick">Sick Leave (SL)</option>
                            <option value="earned">Earned Leave (EL)</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label fw-semibold">From <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="leaveFrom" class="form-control" required>
                        </div>
                        <div class="col">
                            <label class="form-label fw-semibold">To <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="leaveTo" class="form-control" required>
                        </div>
                    </div>
                    <div id="leaveDaysInfo" class="alert alert-info py-2 small d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Reason / Remarks</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Optional…"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({ dropdownParent: $('#applyLeaveModal'), theme: 'bootstrap-5' });
    }
    const from = document.getElementById('leaveFrom');
    const to   = document.getElementById('leaveTo');
    const info = document.getElementById('leaveDaysInfo');
    function updateDays() {
        if (from.value && to.value) {
            const d1 = new Date(from.value), d2 = new Date(to.value);
            const diff = Math.round((d2 - d1) / 86400000) + 1;
            if (diff > 0) {
                info.textContent = diff + ' day' + (diff > 1 ? 's' : '') + ' requested';
                info.classList.remove('d-none');
                to.setCustomValidity('');
            } else {
                info.classList.add('d-none');
                to.setCustomValidity('End date must be on or after start date');
            }
        }
    }
    from.addEventListener('change', updateDays);
    to.addEventListener('change', updateDays);
});
</script>
