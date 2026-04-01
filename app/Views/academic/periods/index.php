<?php $pageTitle = 'Period Management'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-clock me-2 text-primary"></i>Period Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('academic/timetable') ?>">Timetable</a></li>
                <li class="breadcrumb-item active">Periods</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (empty($periods)): ?>
        <button class="btn btn-outline-secondary btn-sm" id="btnSeedDefaults">
            <i class="fas fa-magic me-1"></i>Load Defaults
        </button>
        <?php else: ?>
        <button class="btn btn-outline-danger btn-sm" id="btnClearAll">
            <i class="fas fa-trash me-1"></i>Clear All
        </button>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#periodModal" id="btnAddPeriod">
            <i class="fas fa-plus me-1"></i>Add Period
        </button>
    </div>
</div>

<!-- ── STAT CARDS ─────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-chalkboard text-primary"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $totalPeriods ?></div>
                    <div class="text-muted small">Teaching Periods</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-coffee text-warning"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $totalBreaks ?></div>
                    <div class="text-muted small">Breaks</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-hourglass-half text-success"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= $teachingMins ?> <span style="font-size:.9rem">min</span></div>
                    <div class="text-muted small">Teaching / Day</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                    <i class="fas fa-stream text-info"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold"><?= count($periods) ?></div>
                    <div class="text-muted small">Total Slots</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── DAY TIMELINE ──────────────────────────────────────── -->
<?php if (!empty($periods)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom fw-semibold">
        <i class="fas fa-calendar-day me-2 text-primary"></i>Daily Schedule Timeline
    </div>
    <div class="card-body">
        <div class="d-flex gap-1 flex-wrap align-items-stretch" style="min-height:70px">
            <?php foreach ($periods as $p):
                $isBreak = (int)($p['is_break'] ?? 0);
                [$sh, $sm] = explode(':', $p['start_time']);
                [$eh, $em] = explode(':', $p['end_time']);
                $durMins = ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
                $width   = max(60, $durMins * 2); // 2px per minute
            ?>
            <div class="rounded text-center p-2 d-flex flex-column justify-content-center"
                 style="width:<?= $width ?>px;background:<?= $isBreak ? '#fff3cd' : '#e0f2fe' ?>;
                        border:1px solid <?= $isBreak ? '#ffc107' : '#0ea5e9' ?>;flex-shrink:0">
                <div class="fw-semibold" style="font-size:.75rem;color:<?= $isBreak ? '#856404' : '#0369a1' ?>">
                    <?= e($isBreak ? ($p['break_name'] ?: $p['period_name']) : $p['period_name']) ?>
                </div>
                <div style="font-size:.65rem;color:#6b7280">
                    <?= date('h:i A', strtotime($p['start_time'])) ?>–<?= date('h:i A', strtotime($p['end_time'])) ?>
                </div>
                <div style="font-size:.65rem;color:#9ca3af"><?= $durMins ?>m</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── PERIODS TABLE ──────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="periodsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-center" style="width:80px">#</th>
                        <th>Period Name</th>
                        <th class="text-center">Start Time</th>
                        <th class="text-center">End Time</th>
                        <th class="text-center">Duration</th>
                        <th class="text-center">Type</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($periods)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-clock fa-2x d-block mb-2 opacity-25"></i>
                            No periods configured yet.
                            <a href="#" id="lnkSeedDefaults">Load default periods</a> or
                            <a href="#" data-bs-toggle="modal" data-bs-target="#periodModal">add manually.</a>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($periods as $p):
                        $isBreak = (int)($p['is_break'] ?? 0);
                        [$sh, $sm] = explode(':', $p['start_time']);
                        [$eh, $em] = explode(':', $p['end_time']);
                        $durMins = ((int)$eh * 60 + (int)$em) - ((int)$sh * 60 + (int)$sm);
                    ?>
                    <tr class="<?= $isBreak ? 'table-warning' : '' ?>">
                        <td class="ps-4 text-center fw-bold text-muted"><?= $p['period_number'] ?></td>
                        <td>
                            <div class="fw-semibold"><?= e($p['period_name']) ?></div>
                            <?php if ($isBreak && ($p['break_name'] ?? '')): ?>
                                <div class="small text-warning"><i class="fas fa-coffee me-1"></i><?= e($p['break_name']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= date('h:i A', strtotime($p['start_time'])) ?></td>
                        <td class="text-center"><?= date('h:i A', strtotime($p['end_time'])) ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= $durMins ?> min</span>
                        </td>
                        <td class="text-center">
                            <?php if ($isBreak): ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-coffee me-1"></i>Break</span>
                            <?php else: ?>
                                <span class="badge bg-primary"><i class="fas fa-chalkboard me-1"></i>Teaching</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-outline-primary btn-edit-period me-1"
                                    data-id="<?= $p['id'] ?>" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-period"
                                    data-id="<?= $p['id'] ?>" data-name="<?= e($p['period_name']) ?>" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ── ADD / EDIT MODAL ───────────────────────────────────── -->
<div class="modal fade" id="periodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="frmPeriod" novalidate>
                <input type="hidden" id="periodId" name="period_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="periodModalLabel"><i class="fas fa-clock me-2 text-primary"></i>Add Period</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="periodAlerts"></div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Period Number <span class="text-danger">*</span></label>
                            <input type="number" name="period_number" id="pNum" class="form-control" min="1" value="1" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Period Name <span class="text-danger">*</span></label>
                            <input type="text" name="period_name" id="pName" class="form-control" placeholder="e.g. Period 1, Lunch" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" id="pStart" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" id="pEnd" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_break" id="pIsBreak" value="1">
                                <label class="form-check-label fw-semibold" for="pIsBreak">This is a break / recess</label>
                            </div>
                        </div>
                        <div class="col-md-12" id="breakNameRow" style="display:none">
                            <label class="form-label fw-semibold">Break Label</label>
                            <input type="text" name="break_name" id="pBreakName" class="form-control" placeholder="e.g. Lunch Break, Short Break">
                        </div>
                        <div class="col-md-12" id="durationHint" class="text-muted small"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSavePeriod">
                        <i class="fas fa-save me-1"></i>Save Period
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const pModal   = new bootstrap.Modal(document.getElementById('periodModal'));
    const pForm    = document.getElementById('frmPeriod');
    const pId      = document.getElementById('periodId');
    const pAlerts  = document.getElementById('periodAlerts');
    const pTitle   = document.getElementById('periodModalLabel');
    const isBreakChk = document.getElementById('pIsBreak');
    const breakNameRow = document.getElementById('breakNameRow');

    isBreakChk.addEventListener('change', function () {
        breakNameRow.style.display = this.checked ? '' : 'none';
    });

    // Duration hint
    function updateDurationHint() {
        const s = document.getElementById('pStart').value;
        const e = document.getElementById('pEnd').value;
        const hint = document.getElementById('durationHint');
        if (s && e) {
            const [sh,sm] = s.split(':').map(Number);
            const [eh,em] = e.split(':').map(Number);
            const mins = (eh*60+em) - (sh*60+sm);
            if (mins > 0) hint.textContent = `Duration: ${mins} minutes`;
            else hint.textContent = '⚠ End time must be after start time.';
        }
    }
    document.getElementById('pStart').addEventListener('input', updateDurationHint);
    document.getElementById('pEnd').addEventListener('input', updateDurationHint);

    function showAlert(type, msg) { pAlerts.innerHTML = `<div class="alert alert-${type} py-2 mb-3">${msg}</div>`; }
    function clearAlert() { pAlerts.innerHTML = ''; }

    document.getElementById('btnAddPeriod').addEventListener('click', function () {
        pForm.reset();
        pId.value = '';
        isBreakChk.checked = false;
        breakNameRow.style.display = 'none';
        pTitle.innerHTML = '<i class="fas fa-plus me-2 text-primary"></i>Add Period';
        clearAlert();
    });

    document.querySelectorAll('.btn-edit-period').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            clearAlert();
            fetch(`<?= url('academic/periods') ?>/${id}/json`)
                .then(r => r.json())
                .then(res => {
                    if (res.status !== 'success') { alert(res.message); return; }
                    const d = res.data;
                    pId.value = d.id;
                    document.getElementById('pNum').value   = d.period_number;
                    document.getElementById('pName').value  = d.period_name;
                    document.getElementById('pStart').value = d.start_time.substring(0,5);
                    document.getElementById('pEnd').value   = d.end_time.substring(0,5);
                    isBreakChk.checked = d.is_break == 1;
                    breakNameRow.style.display = d.is_break == 1 ? '' : 'none';
                    document.getElementById('pBreakName').value = d.break_name || '';
                    pTitle.innerHTML = '<i class="fas fa-edit me-2 text-warning"></i>Edit Period';
                    updateDurationHint();
                    pModal.show();
                });
        });
    });

    pForm.addEventListener('submit', function (e) {
        e.preventDefault();
        clearAlert();
        const fd  = new FormData(pForm);
        const id  = pId.value;
        const url = id
            ? `<?= url('academic/periods') ?>/${id}/update`
            : `<?= url('academic/periods/store') ?>`;

        document.getElementById('btnSavePeriod').disabled = true;
        fetch(url, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                document.getElementById('btnSavePeriod').disabled = false;
                if (res.status === 'success') { pModal.hide(); location.reload(); }
                else {
                    const errs = Array.isArray(res.errors) ? res.errors.join('<br>') : (res.message || 'Error');
                    showAlert('danger', errs);
                }
            })
            .catch(() => {
                document.getElementById('btnSavePeriod').disabled = false;
                showAlert('danger', 'Network error.');
            });
    });

    document.querySelectorAll('.btn-delete-period').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const name = this.dataset.name;
            if (!confirm(`Delete period "${name}"?`)) return;
            fetch(`<?= url('academic/periods') ?>/${id}/delete`, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') this.closest('tr').remove();
                    else alert(res.message);
                });
        });
    });

    // Seed defaults
    const btnSeed = document.getElementById('btnSeedDefaults') || document.getElementById('lnkSeedDefaults');
    if (btnSeed) {
        btnSeed.addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('Load 8 default periods? This cannot be undone.')) return;
            fetch('<?= url('academic/periods/seed-defaults') ?>', { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') location.reload();
                    else alert(res.message);
                });
        });
    }

    const btnClear = document.getElementById('btnClearAll');
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            if (!confirm('Clear ALL periods? This will fail if periods are used in timetable.')) return;
            fetch('<?= url('academic/periods/clear') ?>', { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') location.reload();
                    else alert(res.message);
                });
        });
    }
});
</script>
