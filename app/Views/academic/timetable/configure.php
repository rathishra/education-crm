<?php
$isEdit = !empty($config);
$pageTitle = $isEdit ? 'Edit Configuration' : 'New Configuration';
$breadcrumbs = [
    ['label'=>'Academic'],
    ['label'=>'Timetable','url'=>url('academic/timetable')],
    ['label'=>'Generator','url'=>url('academic/timetable/generator')],
    ['label'=>$pageTitle],
];
$configId = $configId ?? ($config['id'] ?? null);

// Decode working days bitmask
$workingDayBits = (int)($config['working_days'] ?? 31);
$dayMap = ['monday'=>1,'tuesday'=>2,'wednesday'=>4,'thursday'=>8,'friday'=>16,'saturday'=>32,'sunday'=>64];
function isDayChecked(string $day, int $bits, array $map): bool { return (bool)($bits & ($map[$day] ?? 0)); }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-sliders-h text-primary me-2"></i><?= $pageTitle ?></h4>
        <p class="text-muted small mb-0">Define working days, periods, subject requirements, and scheduling preferences.</p>
    </div>
    <a href="<?= url('academic/timetable/generator') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Generator
    </a>
</div>

<?php flash_alerts(); ?>

<form method="POST" action="<?= url('academic/timetable/generator/save-config') ?>">
    <?= csrfField() ?>
    <?php if ($configId): ?>
        <input type="hidden" name="config_id" value="<?= $configId ?>">
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left: Config Settings -->
        <div class="col-lg-5">

            <!-- Basic Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2 text-muted"></i>Basic Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Configuration Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="<?= e($config['name'] ?? 'Default Config') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Academic Year</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">— Select —</option>
                            <?php foreach ($academicYears as $yr): ?>
                                <option value="<?= $yr['id'] ?>"
                                    <?= ($config['academic_year_id'] ?? '') == $yr['id'] ? 'selected' : '' ?>>
                                    <?= e($yr['year_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Working Days -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-week me-2 text-muted"></i>Working Days</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($dayMap as $day => $bit): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox"
                                       name="working_days[]" value="<?= $day ?>"
                                       id="day_<?= $day ?>"
                                    <?= isDayChecked($day, $workingDayBits, $dayMap) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="day_<?= $day ?>">
                                    <?= ucfirst(substr($day, 0, 3)) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Period & Scheduling Settings -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-cog me-2 text-muted"></i>Scheduling Preferences</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Max Periods/Day</label>
                            <input type="number" name="max_periods_per_day" class="form-control"
                                   min="1" max="16" value="<?= (int)($config['max_periods_per_day'] ?? 8) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Max Consecutive Same</label>
                            <input type="number" name="max_consecutive_same" class="form-control"
                                   min="1" max="6" value="<?= (int)($config['max_consecutive_same'] ?? 2) ?>">
                            <div class="form-text">Same subject back-to-back limit</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Lab Block Size</label>
                            <input type="number" name="lab_block_size" class="form-control"
                                   min="1" max="4" value="<?= (int)($config['lab_block_size'] ?? 2) ?>">
                            <div class="form-text">Consecutive periods for labs</div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="distribute_evenly"
                                   name="distribute_evenly" value="1"
                                <?= ($config['distribute_evenly'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="distribute_evenly">
                                <span class="fw-semibold">Distribute Evenly</span>
                                <div class="text-muted small">Spread subjects across the week</div>
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="avoid_first_last_same"
                                   name="avoid_first_last_same" value="1"
                                <?= ($config['avoid_first_last_same'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="avoid_first_last_same">
                                <span class="fw-semibold">Avoid First &amp; Last Same</span>
                                <div class="text-muted small">Don't put same subject first + last period</div>
                            </label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="balance_faculty_load"
                                   name="balance_faculty_load" value="1"
                                <?= ($config['balance_faculty_load'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="balance_faculty_load">
                                <span class="fw-semibold">Balance Faculty Load</span>
                                <div class="text-muted small">Distribute teaching hours evenly per faculty</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-save me-1"></i> Save Configuration
            </button>
        </div>

        <!-- Right: Requirements Builder -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-list-check me-2 text-muted"></i>Subject Requirements</h6>
                    <?php if ($configId): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addReqRow()">
                            <i class="fas fa-plus me-1"></i> Add Row
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (!$configId): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-save fa-2x mb-2 d-block opacity-25"></i>
                            Save the configuration first to add subject requirements.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 small" id="reqTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Section</th>
                                        <th>Subject</th>
                                        <th>Faculty</th>
                                        <th style="width:60px">Hrs/Wk</th>
                                        <th style="width:90px">Type</th>
                                        <th style="width:50px">Pri</th>
                                        <th style="width:40px"></th>
                                    </tr>
                                </thead>
                                <tbody id="reqBody">
                                    <?php foreach ($requirements as $req): ?>
                                        <tr data-id="<?= $req['id'] ?>">
                                            <td><?= e($req['section_name']) ?></td>
                                            <td>
                                                <div><?= e($req['subject_name']) ?></div>
                                                <small class="text-muted"><?= e($req['subject_code']) ?></small>
                                            </td>
                                            <td><?= e($req['faculty_name'] ?? '—') ?></td>
                                            <td class="text-center fw-semibold"><?= $req['periods_per_week'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= $req['entry_type'] === 'lab' ? 'warning' : 'primary' ?> bg-opacity-10 text-<?= $req['entry_type'] === 'lab' ? 'warning' : 'primary' ?>">
                                                    <?= ucfirst($req['entry_type']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center"><?= $req['priority'] ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteReq(<?= $req['id'] ?>, this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($requirements)): ?>
                                        <tr id="emptyRow">
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                                No requirements yet. Click <strong>Add Row</strong> to begin.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                            <small class="text-muted"><?= count($requirements) ?> requirement(s)</small>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="importFromAllocations()">
                                <i class="fas fa-file-import me-1"></i> Import from Allocations
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($configId && !empty($constraints)): ?>
            <!-- Constraints summary -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-bottom">
                    <h6 class="mb-0 fw-semibold"><i class="fas fa-shield-alt me-2 text-muted"></i>Active Constraints</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php foreach ($constraints as $c): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                                <div>
                                    <span class="badge bg-<?= $c['constraint_type'] === 'hard' ? 'danger' : 'warning' ?> bg-opacity-10 text-<?= $c['constraint_type'] === 'hard' ? 'danger' : 'warning' ?> me-2">
                                        <?= ucfirst($c['constraint_type']) ?>
                                    </span>
                                    <small><?= e($c['constraint_key']) ?></small>
                                    <?php if ($c['description']): ?><small class="text-muted ms-1">— <?= e($c['description']) ?></small><?php endif; ?>
                                </div>
                                <span class="badge bg-light text-dark"><?= e($c['target_type']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($configId): ?>
<!-- Add Requirement Modal -->
<div class="modal fade" id="addReqModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Subject Requirement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Section <span class="text-danger">*</span></label>
                        <select id="req_section" class="form-select">
                            <option value="">— Select Section —</option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>">
                                    <?= e($sec['course_name'] . ' › ' . $sec['batch_name'] . ' › ' . $sec['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                        <select id="req_subject" class="form-select">
                            <option value="">— Select Subject —</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?= $sub['id'] ?>" data-type="<?= e($sub['subject_type'] ?? 'theory') ?>">
                                    <?= e($sub['subject_name']) ?> (<?= e($sub['subject_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Faculty</label>
                        <select id="req_faculty" class="form-select">
                            <option value="">— Unassigned —</option>
                            <?php foreach ($faculty as $f): ?>
                                <option value="<?= $f['id'] ?>"><?= e($f['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Periods / Week</label>
                        <input type="number" id="req_periods" class="form-control" min="1" max="20" value="3">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Priority</label>
                        <input type="number" id="req_priority" class="form-control" min="1" max="10" value="5">
                        <div class="form-text">1=highest, 10=lowest</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Entry Type</label>
                        <select id="req_entry_type" class="form-select">
                            <option value="lecture">Lecture</option>
                            <option value="lab">Lab (consecutive periods)</option>
                            <option value="tutorial">Tutorial</option>
                            <option value="activity">Activity</option>
                        </select>
                    </div>
                </div>
                <div id="reqSaveError" class="alert alert-danger mt-3 d-none"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveReq()">
                    <i class="fas fa-save me-1"></i> Add Requirement
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const CONFIG_ID = <?= (int)$configId ?>;
const CSRF = '<?= csrfToken() ?>';
const SAVE_REQ_URL = '<?= url('academic/timetable/generator/save-requirements') ?>';
const DEL_REQ_URL  = '<?= url('academic/timetable/generator/delete-requirement') ?>';
const IMPORT_URL   = '<?= url('academic/timetable/generator/import-requirements') ?>';

function addReqRow() {
    document.getElementById('reqSaveError').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('addReqModal')).show();
}

function saveReq() {
    const sectionId   = document.getElementById('req_section').value;
    const subjectId   = document.getElementById('req_subject').value;
    const facultyId   = document.getElementById('req_faculty').value;
    const periods     = document.getElementById('req_periods').value;
    const priority    = document.getElementById('req_priority').value;
    const entryType   = document.getElementById('req_entry_type').value;
    const errEl       = document.getElementById('reqSaveError');

    if (!sectionId || !subjectId) {
        errEl.textContent = 'Section and Subject are required.';
        errEl.classList.remove('d-none');
        return;
    }

    fetch(SAVE_REQ_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
        body: JSON.stringify({
            config_id: CONFIG_ID,
            section_id: parseInt(sectionId),
            subject_id: parseInt(subjectId),
            faculty_id: facultyId ? parseInt(facultyId) : null,
            periods_per_week: parseInt(periods),
            priority: parseInt(priority),
            entry_type: entryType,
        }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            bootstrap.Modal.getInstance(document.getElementById('addReqModal')).hide();
            location.reload();
        } else {
            errEl.textContent = d.message || 'Error saving requirement.';
            errEl.classList.remove('d-none');
        }
    });
}

function deleteReq(id, btn) {
    if (!confirm('Remove this requirement?')) return;
    btn.disabled = true;
    fetch(DEL_REQ_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
        body: JSON.stringify({ id }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            btn.closest('tr').remove();
        } else {
            btn.disabled = false;
            alert(d.message || 'Error deleting.');
        }
    });
}

function importFromAllocations() {
    if (!confirm('Import subject requirements from current faculty allocations for all sections? This will skip duplicates.')) return;
    fetch(IMPORT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
        body: JSON.stringify({ config_id: CONFIG_ID }),
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert(`Imported ${d.imported} requirement(s). ${d.skipped} skipped (already exist).`);
            location.reload();
        } else {
            alert(d.message || 'Import failed.');
        }
    });
}
</script>
<?php endif; ?>
