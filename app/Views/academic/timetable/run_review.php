<?php
$pageTitle = 'Review Run: ' . ($run['run_name'] ?? '');
$breadcrumbs = [
    ['label'=>'Academic'],
    ['label'=>'Timetable','url'=>url('academic/timetable')],
    ['label'=>'Generator','url'=>url('academic/timetable/generator')],
    ['label'=>'Review Run'],
];

$isApproved  = $run['status'] === 'approved';
$isCompleted = $run['status'] === 'completed';

$scoreColor = (float)$run['score'] >= 90 ? 'success' : ((float)$run['score'] >= 70 ? 'warning' : 'danger');

// Group assignments by section → day → period
$bySection = [];
foreach ($assignments as $a) {
    $bySection[$a['section_id']]['name'] = $a['section_name'] ?? ('Section #' . $a['section_id']);
    $bySection[$a['section_id']]['rows'][$a['day']][$a['period_id']] = $a;
}
ksort($bySection);

// Ordered working days
$allDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
$scopeDays = $workingDays ?? $allDays;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="fas fa-eye text-primary me-2"></i><?= e($run['run_name']) ?>
        </h4>
        <p class="text-muted small mb-0">
            Config: <strong><?= e($run['config_name'] ?? '—') ?></strong>
            &middot; Generated <?= date('d M Y, H:i', strtotime($run['created_at'])) ?>
            &middot; <?= $run['duration_ms'] ?>ms
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($isCompleted): ?>
            <button class="btn btn-success" onclick="approveRun(<?= $run['id'] ?>)">
                <i class="fas fa-check-circle me-1"></i> Approve & Publish
            </button>
            <button class="btn btn-outline-danger" onclick="discardRun(<?= $run['id'] ?>)">
                <i class="fas fa-ban me-1"></i> Discard
            </button>
        <?php elseif ($isApproved): ?>
            <span class="badge bg-success fs-6 px-3 py-2"><i class="fas fa-check-circle me-1"></i>Approved</span>
        <?php endif; ?>
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download me-1"></i> Export
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= url('academic/timetable/generator/export/' . $run['id'] . '?format=html') ?>" target="_blank"><i class="fas fa-print me-2"></i>Print View</a></li>
                <li><a class="dropdown-item" href="<?= url('academic/timetable/generator/export/' . $run['id'] . '?format=csv') ?>"><i class="fas fa-file-csv me-2"></i>Download CSV</a></li>
                <li><a class="dropdown-item" href="<?= url('academic/timetable/generator/export/' . $run['id'] . '?format=ical') ?>"><i class="fas fa-calendar me-2"></i>iCalendar (.ics)</a></li>
            </ul>
        </div>
        <a href="<?= url('academic/timetable/generator') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
    </div>
</div>

<?php flash_alerts(); ?>

<!-- Score Summary -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-<?= $scoreColor ?>"><?= number_format((float)$run['score'], 1) ?>%</div>
                <div class="text-muted small">Quality Score</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-success"><?= (int)$run['assigned_count'] ?></div>
                <div class="text-muted small">Slots Assigned</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-<?= (int)$run['conflict_count'] > 0 ? 'danger' : 'success' ?>"><?= (int)$run['conflict_count'] ?></div>
                <div class="text-muted small">Conflicts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold"><?= (int)$run['total_requirements'] ?></div>
                <div class="text-muted small">Total Requirements</div>
            </div>
        </div>
    </div>
</div>

<!-- Section Tabs -->
<?php if (!empty($bySection)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-table me-2 text-muted"></i>Generated Timetable</h6>
        <div class="d-flex gap-2 align-items-center">
            <small class="text-muted">Section:</small>
            <select id="sectionSelect" class="form-select form-select-sm" style="width:auto" onchange="showSection(this.value)">
                <?php foreach ($bySection as $secId => $sec): ?>
                    <option value="sec_<?= $secId ?>"><?= e($sec['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <?php foreach ($bySection as $secId => $sec): ?>
            <div id="sec_<?= $secId ?>" class="section-grid" style="display:none">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="text-muted" style="width:120px">Period</th>
                                <?php foreach ($scopeDays as $day): ?>
                                    <th class="text-center text-capitalize"><?= substr($day, 0, 3) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($periods as $period): ?>
                                <?php if ($period['is_break'] ?? false): ?>
                                    <tr class="table-warning">
                                        <td class="text-muted small text-center" colspan="<?= count($scopeDays) + 1 ?>">
                                            <i class="fas fa-coffee me-1"></i><?= e($period['period_name']) ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td class="bg-light">
                                            <div class="fw-semibold"><?= e($period['period_name']) ?></div>
                                            <div class="text-muted" style="font-size:.7rem">
                                                <?= date('g:i A', strtotime($period['start_time'])) ?>
                                                – <?= date('g:i A', strtotime($period['end_time'])) ?>
                                            </div>
                                        </td>
                                        <?php foreach ($scopeDays as $day): ?>
                                            <?php $slot = $sec['rows'][$day][$period['id']] ?? null; ?>
                                            <td class="text-center <?= $slot ? '' : 'text-muted' ?>" style="min-width:110px">
                                                <?php if ($slot): ?>
                                                    <div class="fw-semibold text-primary small"><?= e($slot['subject_name'] ?? ('Sub #' . $slot['subject_id'])) ?></div>
                                                    <div class="text-muted" style="font-size:.7rem"><?= e($slot['faculty_name'] ?? '') ?></div>
                                                    <?php if (!empty($slot['room_name'])): ?>
                                                        <div class="text-muted" style="font-size:.65rem"><i class="fas fa-door-open"></i> <?= e($slot['room_name']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (($slot['entry_type'] ?? '') === 'lab'): ?>
                                                        <span class="badge bg-warning bg-opacity-10 text-warning" style="font-size:.6rem">Lab</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="opacity-25">—</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Conflicts -->
<?php if (!empty($conflicts)): ?>
<div class="card border-0 shadow-sm mb-4 border-danger">
    <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger">
        <h6 class="mb-0 fw-semibold text-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Unresolved Conflicts (<?= count($conflicts) ?>)
        </h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Faculty</th>
                    <th>Occurrence</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conflicts as $c): ?>
                    <tr>
                        <td><?= e($c['section_name'] ?? ('Section #' . $c['section_id'])) ?></td>
                        <td><?= e($c['subject_name'] ?? ('Subject #' . $c['subject_id'])) ?></td>
                        <td><?= e($c['faculty_name'] ?? '—') ?></td>
                        <td><?= (int)($c['occurrence'] ?? 1) ?> of <?= (int)($c['periods_per_week'] ?? 1) ?></td>
                        <td class="text-danger"><i class="fas fa-times-circle me-1"></i>No available slot</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Generation Log -->
<?php if (!empty($run['log'])): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom">
        <a class="text-muted small fw-semibold text-decoration-none collapsed" data-bs-toggle="collapse" href="#genLog">
            <i class="fas fa-terminal me-1"></i> Generation Log
            <i class="fas fa-chevron-down ms-1" style="font-size:.7rem"></i>
        </a>
    </div>
    <div class="collapse" id="genLog">
        <div class="card-body p-0">
            <pre class="bg-dark text-light p-3 mb-0 small" style="max-height:300px;overflow-y:auto;font-size:.75rem"><?= e($run['log']) ?></pre>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Show first section on load
document.addEventListener('DOMContentLoaded', function () {
    const first = document.querySelector('.section-grid');
    if (first) first.style.display = '';
});

function showSection(id) {
    document.querySelectorAll('.section-grid').forEach(el => el.style.display = 'none');
    const target = document.getElementById(id);
    if (target) target.style.display = '';
}

function approveRun(runId) {
    if (!confirm('Approve this run and publish the timetable? This will overwrite existing timetable slots for the sections in scope.')) return;
    fetch('<?= url('academic/timetable/generator/approve/') ?>' + runId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>' },
        body: JSON.stringify({}),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert(d.message || 'Approval failed.');
        }
    });
}

function discardRun(runId) {
    if (!confirm('Discard this run? The generated timetable will not be published.')) return;
    fetch('<?= url('academic/timetable/generator/discard/') ?>' + runId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>' },
        body: JSON.stringify({}),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            window.location = '<?= url('academic/timetable/generator') ?>';
        } else {
            alert(d.message || 'Failed to discard.');
        }
    });
}
</script>
