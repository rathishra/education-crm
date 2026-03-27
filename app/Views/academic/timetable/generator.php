<?php
$pageTitle = 'Timetable: ' . e($section['program_name']) . ' — Section ' . e($section['section_name']);
$savedSlots = 0;
foreach($timetable as $day => $periods) { $savedSlots += count($periods); }
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/timetable') ?>">Scheduling</a></li>
                <li class="breadcrumb-item active">Timetable Editor</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-1">Timetable Matrix Editor</h4>
        <p class="text-muted mb-0 small">
            <strong><?= e($section['program_name']) ?> (<?= e($section['batch_term']) ?>)</strong>
            — Section <strong class="text-dark"><?= e($section['section_name']) ?></strong>
            <?php if($savedSlots > 0): ?>
            · <span class="badge bg-success-subtle text-success border border-success-subtle"><?= $savedSlots ?> slots saved</span>
            <?php else: ?>
            · <span class="badge bg-warning-subtle text-warning border border-warning-subtle">No timetable yet</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <?php if($savedSlots > 0): ?>
        <a href="<?= url('academic/timetable/' . $section['id'] . '/view') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-eye me-1 text-primary"></i>View Timetable
        </a>
        <?php endif; ?>
        <a href="<?= url('academic/timetable') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- Next Step Hint (only if no students enrolled — link to section) -->
<?php if(empty($subjects)): ?>
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
    <i class="fas fa-exclamation-triangle fa-lg"></i>
    <div>
        <strong>No subjects found.</strong> Add academic subjects first before configuring the timetable.
        <a href="<?= url('academic/subjects/create') ?>" class="ms-2 btn btn-sm btn-warning">Add Subject</a>
    </div>
</div>
<?php endif; ?>

<?php if(empty($faculty)): ?>
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="fas fa-info-circle fa-lg"></i>
    <div>
        <strong>No faculty found.</strong> Add staff/faculty users first.
        <a href="<?= url('users/create') ?>" class="ms-2 btn btn-sm btn-info text-white">Add User</a>
    </div>
</div>
<?php endif; ?>

<!-- Subject Legend -->
<?php if(!empty($subjects)): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted small fw-bold me-1">Subject Colors:</span>
            <?php foreach($subjects as $sub): ?>
            <span class="badge px-2 py-1" style="background:<?= $subjectColors[$sub['id']] ?>20;color:<?= $subjectColors[$sub['id']] ?>;border:1px solid <?= $subjectColors[$sub['id']] ?>40;font-size:.72rem">
                <?= e($sub['subject_code']) ?> — <?= e($sub['subject_name']) ?>
            </span>
            <?php endforeach; ?>
            <span class="ms-auto text-muted small">Select subject & faculty in each cell below, then Save.</span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Timetable Grid -->
<form id="frmGenerateTT" method="POST" action="<?= url('academic/timetable/store') ?>">
    <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
    <input type="hidden" name="batch_id" value="<?= $section['batch_id'] ?>">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive" style="overflow-x:auto">
                <table class="table table-bordered mb-0 align-middle text-center" id="ttGrid" style="min-width:1100px;table-layout:fixed">
                    <thead>
                        <tr class="table-dark">
                            <th style="width:90px" class="py-3">Day</th>
                            <?php foreach($periods as $p): ?>
                            <th class="<?= $p['is_break'] ? 'bg-secondary' : '' ?>" style="width:<?= $p['is_break'] ? '70px' : '160px' ?>">
                                <div class="fw-bold small"><?= e($p['period_name']) ?></div>
                                <div class="opacity-75" style="font-size:.65rem"><?= substr($p['start_time'],0,5) ?>–<?= substr($p['end_time'],0,5) ?></div>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($days as $day): ?>
                        <tr>
                            <td class="fw-bold text-uppercase text-secondary bg-light small py-2">
                                <?= substr($day,0,3) ?>
                            </td>
                            <?php foreach($periods as $p):
                                $pid = $p['id'];
                                $curSub  = $timetable[$day][$pid]['subject_id'] ?? '';
                                $curFac  = $timetable[$day][$pid]['faculty_id'] ?? '';
                                $curTyp  = $timetable[$day][$pid]['entry_type'] ?? 'lecture';
                                $cellBg  = $curSub ? ($subjectColors[$curSub] ?? '#6b7280') : '';
                            ?>
                            <?php if($p['is_break']): ?>
                            <td class="bg-light text-muted py-2" style="font-size:.7rem;vertical-align:middle">
                                <i class="fas fa-mug-hot d-block mb-1 opacity-50"></i>
                                <?= e($p['break_name'] ?: 'BREAK') ?>
                            </td>
                            <?php else: ?>
                            <td class="p-1 tt-cell" id="cell_<?= $day ?>_<?= $pid ?>"
                                style="<?= $cellBg ? "border-left:3px solid $cellBg" : '' ?>;vertical-align:top;min-width:150px">
                                <!-- Subject -->
                                <select class="form-select form-select-sm mb-1 tt-subject"
                                    name="schedule[<?= $day ?>][<?= $pid ?>][subject_id]"
                                    data-day="<?= $day ?>" data-pid="<?= $pid ?>"
                                    style="font-size:.72rem;<?= $cellBg ? "border-color:$cellBg" : '' ?>">
                                    <option value="">— Subject —</option>
                                    <?php foreach($subjects as $sub): ?>
                                    <option value="<?= $sub['id'] ?>"
                                        data-color="<?= $subjectColors[$sub['id']] ?>"
                                        <?= $curSub == $sub['id'] ? 'selected' : '' ?>>
                                        <?= e($sub['subject_code']) ?> — <?= e($sub['subject_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- Faculty -->
                                <select class="form-select form-select-sm mb-1"
                                    name="schedule[<?= $day ?>][<?= $pid ?>][faculty_id]"
                                    style="font-size:.72rem">
                                    <option value="">— Faculty —</option>
                                    <?php foreach($faculty as $fac): ?>
                                    <option value="<?= $fac['id'] ?>" <?= $curFac == $fac['id'] ? 'selected' : '' ?>>
                                        <?= e($fac['first_name'] . ' ' . $fac['last_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <!-- Type -->
                                <select class="form-select form-select-sm"
                                    name="schedule[<?= $day ?>][<?= $pid ?>][entry_type]"
                                    style="font-size:.65rem;background:#f9f9f9">
                                    <option value="lecture"  <?= $curTyp=='lecture'  ? 'selected' : '' ?>>Lecture</option>
                                    <option value="lab"      <?= $curTyp=='lab'      ? 'selected' : '' ?>>Lab</option>
                                    <option value="tutorial" <?= $curTyp=='tutorial' ? 'selected' : '' ?>>Tutorial</option>
                                    <option value="seminar"  <?= $curTyp=='seminar'  ? 'selected' : '' ?>>Seminar</option>
                                </select>
                            </td>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white d-flex justify-content-between align-items-center p-3">
            <div class="text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                Leave subject blank to mark a cell as free. Only cells with both Subject + Faculty are saved.
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="text-muted small" id="slotCounter">
                    <?= $savedSlots ?> slot(s) saved
                </span>
                <button type="button" class="btn btn-outline-secondary px-3" id="btnClearAll">
                    <i class="fas fa-eraser me-1"></i>Clear All
                </button>
                <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm" id="btnSaveTT">
                    <i class="fas fa-save me-1"></i>Save Timetable
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Post-save next step prompt -->
<div id="nextStepPrompt" class="alert alert-success d-flex align-items-center gap-3 mt-3" style="display:none!important">
    <i class="fas fa-check-circle fa-lg"></i>
    <div>
        <strong>Timetable saved!</strong> Faculty can now see their schedule in the attendance portal.
        <a href="<?= url('academic/attendance') ?>" class="ms-2 btn btn-sm btn-success">
            <i class="fas fa-user-check me-1"></i>Go to Attendance Portal
        </a>
    </div>
</div>

<!-- Subject color map for JS -->
<script>
const SUBJECT_COLORS = <?= json_encode($subjectColors) ?>;

// Color cell when subject changes
document.querySelectorAll('.tt-subject').forEach(function(sel) {
    sel.addEventListener('change', function() {
        const cell = document.getElementById('cell_' + this.dataset.day + '_' + this.dataset.pid);
        const opt  = this.options[this.selectedIndex];
        const color = opt.dataset.color || '';
        if(color) {
            cell.style.borderLeft = '3px solid ' + color;
            this.style.borderColor = color;
        } else {
            cell.style.borderLeft = '';
            this.style.borderColor = '';
        }
    });
});

// Clear all
document.getElementById('btnClearAll').addEventListener('click', function() {
    if(!confirm('Clear all timetable cells?')) return;
    document.querySelectorAll('.tt-cell select').forEach(function(s) {
        s.selectedIndex = 0;
        s.style.borderColor = '';
    });
    document.querySelectorAll('.tt-cell').forEach(function(c) {
        c.style.borderLeft = '';
    });
});

// Save via fetch
document.getElementById('frmGenerateTT').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveTT');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

    try {
        const res = await fetch(this.action, { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if(data.status === 'success') {
            toastr.success(data.message);
            document.getElementById('slotCounter').textContent = (data.count || 0) + ' slot(s) saved';
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Saved!';
            // Show next step
            const prompt = document.getElementById('nextStepPrompt');
            prompt.style.display = 'flex';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Timetable';
            }, 3000);
        } else {
            toastr.error(data.message || 'Failed to save timetable');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Timetable';
        }
    } catch(err) {
        toastr.error('Server error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Timetable';
    }
});
</script>

<style>
#ttGrid td { padding: 4px !important; }
#ttGrid select { border-radius: 4px !important; }
.tt-cell:hover { background: #fafbff !important; }
</style>
