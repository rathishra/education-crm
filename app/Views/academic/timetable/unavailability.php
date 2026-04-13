<?php
$pageTitle = 'Teacher Unavailability';
$breadcrumbs = [
    ['label'=>'Academic'],
    ['label'=>'Timetable','url'=>url('academic/timetable')],
    ['label'=>'Generator','url'=>url('academic/timetable/generator')],
    ['label'=>'Teacher Unavailability'],
];
$days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-user-clock text-primary me-2"></i>Teacher Unavailability</h4>
        <p class="text-muted small mb-0">Block specific periods when teachers are unavailable. The generator will skip these slots.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus me-1"></i> Add Block
        </button>
        <a href="<?= url('academic/timetable/generator') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<?php flash_alerts(); ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="fas fa-list me-2 text-muted"></i>Blocked Slots</h6>
        <input type="text" id="searchInput" class="form-control form-control-sm" style="width:200px"
               placeholder="Search faculty..." onkeyup="filterTable()">
    </div>
    <div class="card-body p-0">
        <?php if (empty($unavailability)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-check fa-2x mb-2 d-block opacity-25"></i>
                No blocked slots defined. All teachers are available all periods by default.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="unavailTable">
                    <thead class="table-light">
                        <tr>
                            <th>Teacher</th>
                            <th>Day</th>
                            <th>Period</th>
                            <th>Reason</th>
                            <th>Effective</th>
                            <th>Recurring</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unavailability as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($u['faculty_name'] ?? ('Faculty #' . $u['faculty_id'])) ?></div>
                                </td>
                                <td class="text-capitalize"><?= e($u['day_of_week']) ?></td>
                                <td>
                                    <?php if ($u['period_id']): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning">
                                            <?= e($u['period_name'] ?? ('Period #' . $u['period_id'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Entire Day</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= e($u['reason'] ?? '—') ?></td>
                                <td>
                                    <?php if ($u['effective_from'] || $u['effective_to']): ?>
                                        <small><?= $u['effective_from'] ? date('d M', strtotime($u['effective_from'])) : '?' ?>
                                        → <?= $u['effective_to'] ? date('d M', strtotime($u['effective_to'])) : '∞' ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Always</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['is_recurring']): ?>
                                        <span class="badge bg-info bg-opacity-10 text-info">Weekly</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Once</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="deleteBlock(<?= $u['id'] ?>, this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-user-slash me-2 text-warning"></i>Add Unavailability Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('academic/timetable/generator/unavailability/save') ?>">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teacher <span class="text-danger">*</span></label>
                        <select name="faculty_id" class="form-select" required>
                            <option value="">— Select Teacher —</option>
                            <?php foreach ($faculty as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= e($f['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Day <span class="text-danger">*</span></label>
                            <select name="day_of_week" class="form-select" required>
                                <option value="">— Day —</option>
                                <?php foreach ($days as $day): ?>
                                    <option value="<?= $day ?>"><?= ucfirst($day) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Period</label>
                            <select name="period_id" class="form-select">
                                <option value="">Entire Day</option>
                                <?php foreach ($periods as $p): ?>
                                    <?php if (!($p['is_break'] ?? false)): ?>
                                        <option value="<?= $p['id'] ?>"><?= e($p['period_name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. External duty, Medical leave">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Effective From</label>
                            <input type="date" name="effective_from" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Effective To</label>
                            <input type="date" name="effective_to" class="form-control">
                        </div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_recurring" value="1" id="isRecurring" checked>
                        <label class="form-check-label" for="isRecurring">Weekly recurring</label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-ban me-1"></i> Block Slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteBlock(id, btn) {
    if (!confirm('Remove this unavailability block?')) return;
    btn.disabled = true;
    fetch('<?= url('academic/timetable/generator/unavailability/delete') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>' },
        body: JSON.stringify({ id }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) btn.closest('tr').remove();
        else { btn.disabled = false; alert(d.message || 'Error.'); }
    });
}

function filterTable() {
    const val = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#unavailTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
}
</script>
