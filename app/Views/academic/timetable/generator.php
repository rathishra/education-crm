<?php
$pageTitle = 'Timetable: ' . e($section['program_name']) . ' — Section ' . e($section['section_name']);
$savedSlots = 0;
foreach($timetable as $day => $ps) { $savedSlots += count($ps); }
$teachingPeriods = array_values(array_filter($periods, fn($p) => !(int)($p['is_break'] ?? 0)));
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
        <button type="button" class="btn btn-light border shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCopyTT">
            <i class="fas fa-copy me-1 text-success"></i>Copy From Section
        </button>
        <a href="<?= url('academic/timetable') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- Hints / Warnings -->
<?php if(!$batchSubjectAllocated): ?>
<div class="alert alert-warning d-flex align-items-center gap-3 mb-3">
    <i class="fas fa-exclamation-triangle fa-lg flex-shrink-0"></i>
    <div>
        <strong>No subjects allocated to this batch.</strong>
        Showing all institution subjects. For better filtering, allocate subjects to this batch first.
        <a href="<?= url('academic/subject-allocation?batch_id=' . $section['batch_id']) ?>" class="ms-2 btn btn-sm btn-warning">
            Allocate Subjects →
        </a>
    </div>
</div>
<?php endif; ?>

<?php if(empty($subjects)): ?>
<div class="alert alert-danger d-flex align-items-center gap-3 mb-3">
    <i class="fas fa-times-circle fa-lg flex-shrink-0"></i>
    <div>
        <strong>No subjects found.</strong> Add subjects to the system first.
        <a href="<?= url('academic/subjects/create') ?>" class="ms-2 btn btn-sm btn-danger">Add Subject</a>
    </div>
</div>
<?php endif; ?>

<!-- Conflict alert (shown after save if conflicts detected) -->
<div id="conflictAlert" class="alert alert-danger d-none mb-3">
    <strong><i class="fas fa-exclamation-triangle me-1"></i>Faculty Conflicts Detected:</strong>
    <ul class="mb-0 mt-1 small" id="conflictList"></ul>
</div>

<!-- Subject Legend -->
<?php if(!empty($subjects)): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <span class="text-muted small fw-bold me-1">Subjects (<?= count($subjects) ?>):</span>
            <?php foreach($subjects as $sub): ?>
            <?php $color = $subjectColors[$sub['id']]; ?>
            <span class="badge px-2 py-1 subject-legend-badge" data-subject-id="<?= $sub['id'] ?>"
                  style="background:<?= $color ?>20;color:<?= $color ?>;border:1px solid <?= $color ?>40;font-size:.72rem;cursor:pointer"
                  title="<?= e($sub['subject_name']) ?> <?= isset($sub['semester']) && $sub['semester'] ? '· Sem '.$sub['semester'] : '' ?>">
                <?= e($sub['subject_code']) ?>
                <?php if($sub['is_compulsory'] ?? 1): ?>
                <i class="fas fa-asterisk ms-1" style="font-size:.5rem;opacity:.6" title="Compulsory"></i>
                <?php endif; ?>
            </span>
            <?php endforeach; ?>
            <span class="ms-auto text-muted small">Cells highlighted in <span class="text-danger fw-bold">red</span> = faculty conflict</span>
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
                <table class="table table-bordered mb-0 align-middle text-center" id="ttGrid"
                       style="min-width:1000px;table-layout:auto">
                    <thead>
                        <tr class="table-dark">
                            <th style="width:72px" class="py-3 text-center">Day</th>
                            <?php foreach($periods as $p): ?>
                            <th class="<?= ($p['is_break'] ?? 0) ? 'bg-secondary' : '' ?>"
                                style="width:<?= ($p['is_break'] ?? 0) ? '60px' : '170px' ?>;min-width:<?= ($p['is_break'] ?? 0) ? '60px' : '155px' ?>">
                                <div class="fw-bold small"><?= e($p['period_name']) ?></div>
                                <div class="opacity-75" style="font-size:.63rem"><?= substr($p['start_time'],0,5) ?>–<?= substr($p['end_time'],0,5) ?></div>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($days as $day): ?>
                        <tr>
                            <td class="fw-bold text-uppercase text-secondary bg-light small py-2 text-center" style="letter-spacing:.3px">
                                <?= substr($day,0,3) ?>
                            </td>
                            <?php foreach($periods as $p):
                                $pid     = $p['id'] ?? $p['period_number'] ?? 0;
                                $isBreak = (int)($p['is_break'] ?? 0);
                                $curSub  = $timetable[$day][$pid]['subject_id'] ?? '';
                                $curFac  = $timetable[$day][$pid]['faculty_id'] ?? '';
                                $curTyp  = $timetable[$day][$pid]['entry_type'] ?? 'lecture';
                                $curRoom = $timetable[$day][$pid]['room_id'] ?? '';
                                $cellBg  = $curSub ? ($subjectColors[$curSub] ?? '#6b7280') : '';

                                // Detect saved conflict
                                $isConflict = false;
                                if ($curFac && isset($conflictMap[$curFac][$day][$pid])) {
                                    $isConflict = true;
                                }
                            ?>
                            <?php if($isBreak): ?>
                            <td class="bg-light text-muted py-2" style="font-size:.68rem;vertical-align:middle">
                                <i class="fas fa-mug-hot d-block mb-1 opacity-40"></i>
                                <?= e($p['break_name'] ?: 'BREAK') ?>
                            </td>
                            <?php else: ?>
                            <td class="p-1 tt-cell <?= $isConflict ? 'conflict-cell' : '' ?>"
                                id="cell_<?= $day ?>_<?= $pid ?>"
                                data-day="<?= $day ?>" data-pid="<?= $pid ?>"
                                style="<?= $cellBg ? "border-left:3px solid $cellBg;" : '' ?><?= $isConflict ? 'background:#fff5f5' : '' ?>;vertical-align:top;min-width:140px">

                                <?php if($isConflict): ?>
                                <div class="text-danger" style="font-size:.62rem;line-height:1.1;margin-bottom:2px">
                                    <i class="fas fa-exclamation-circle"></i>
                                    Conflict: <?= e($conflictMap[$curFac][$day][$pid]) ?>
                                </div>
                                <?php endif; ?>

                                <!-- Subject -->
                                <select class="form-select form-select-sm mb-1 tt-subject"
                                    name="schedule[<?= $day ?>][<?= $pid ?>][subject_id]"
                                    data-day="<?= $day ?>" data-pid="<?= $pid ?>"
                                    data-batch="<?= $section['batch_id'] ?>"
                                    style="font-size:.71rem;<?= $cellBg ? "border-color:$cellBg" : '' ?>">
                                    <option value="">— Subject —</option>
                                    <?php foreach($subjects as $sub): ?>
                                    <option value="<?= $sub['id'] ?>"
                                        data-color="<?= $subjectColors[$sub['id']] ?>"
                                        <?= $curSub == $sub['id'] ? 'selected' : '' ?>>
                                        <?= e($sub['subject_code']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- Faculty (filtered by subject allocation) -->
                                <select class="form-select form-select-sm mb-1 tt-faculty"
                                    name="schedule[<?= $day ?>][<?= $pid ?>][faculty_id]"
                                    data-day="<?= $day ?>" data-pid="<?= $pid ?>"
                                    style="font-size:.71rem">
                                    <option value="">— Faculty —</option>
                                    <?php foreach($allFaculty as $fac): ?>
                                    <option value="<?= $fac['id'] ?>" <?= $curFac == $fac['id'] ? 'selected' : '' ?>>
                                        <?= e($fac['full_name'] ?? ($fac['first_name'].' '.$fac['last_name'])) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>

                                <!-- Type + Room in a row -->
                                <div class="d-flex gap-1">
                                    <select class="form-select form-select-sm tt-type flex-shrink-0"
                                        name="schedule[<?= $day ?>][<?= $pid ?>][entry_type]"
                                        style="font-size:.63rem;background:#f9f9f9;width:72px">
                                        <option value="lecture"  <?= $curTyp=='lecture'  ? 'selected':'' ?>>Lecture</option>
                                        <option value="lab"      <?= $curTyp=='lab'      ? 'selected':'' ?>>Lab</option>
                                        <option value="tutorial" <?= $curTyp=='tutorial' ? 'selected':'' ?>>Tutorial</option>
                                        <option value="seminar"  <?= $curTyp=='seminar'  ? 'selected':'' ?>>Seminar</option>
                                    </select>
                                    <?php if(!empty($rooms)): ?>
                                    <select class="form-select form-select-sm flex-grow-1"
                                        name="schedule[<?= $day ?>][<?= $pid ?>][room_id]"
                                        style="font-size:.63rem">
                                        <option value="">Room</option>
                                        <?php foreach($rooms as $rm): ?>
                                        <option value="<?= $rm['id'] ?>" <?= $curRoom == $rm['id'] ? 'selected':'' ?>>
                                            <?= e($rm['room_number']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>
                                </div>
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
                Only cells with Subject + Faculty are saved. Red cells = faculty conflict.
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="text-muted small" id="slotCounter"><?= $savedSlots ?> slot(s) saved</span>
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
<div id="nextStepPrompt" class="alert alert-success d-flex align-items-center gap-3 mt-3 d-none">
    <i class="fas fa-check-circle fa-lg flex-shrink-0"></i>
    <div>
        <strong>Timetable saved!</strong> Faculty can now see their schedule.
        <a href="<?= url('academic/attendance') ?>" class="ms-2 btn btn-sm btn-success">
            <i class="fas fa-user-check me-1"></i>Go to Attendance →
        </a>
    </div>
</div>

<!-- ── Copy From Section Modal ──────────────────────────────── -->
<div class="modal fade" id="modalCopyTT" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-copy me-2 text-success"></i>Copy Timetable from Another Section</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">This will <strong>overwrite</strong> the current timetable of Section <?= e($section['section_name']) ?>.</p>
                <label class="form-label fw-semibold small">Source Section</label>
                <select class="form-select" id="copySourceSection">
                    <option value="">Select source section…</option>
                    <?php
                    // We'll load sections via a small inline query via JS or just hard-code here
                    // We'll use AJAX to fetch it on modal open instead
                    ?>
                </select>
                <div class="text-muted small mt-2" id="copySourceInfo"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success px-4" id="btnDoCopy">
                    <i class="fas fa-copy me-1"></i>Copy Timetable
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JSON data for JS -->
<script>
const SUBJECT_COLORS   = <?= json_encode($subjectColors) ?>;
const FACULTY_BY_SUB   = <?= json_encode($facultyBySubject) ?>;
const ALL_FACULTY      = <?= json_encode(array_map(fn($f) => [
    'id'   => $f['id'],
    'name' => $f['full_name'] ?? ($f['first_name'].' '.$f['last_name'])
], $allFaculty)) ?>;
const CONFLICT_MAP     = <?= json_encode($conflictMap) ?>;
const SECTION_ID       = <?= (int)$section['id'] ?>;
const BATCH_ID         = <?= (int)$section['batch_id'] ?>;

// ── Subject color + smart faculty filter on change ────────────
document.querySelectorAll('.tt-subject').forEach(function(sel) {
    sel.addEventListener('change', function() {
        const day = this.dataset.day;
        const pid = this.dataset.pid;
        const cell    = document.getElementById('cell_' + day + '_' + pid);
        const opt     = this.options[this.selectedIndex];
        const color   = opt.dataset.color || '';
        const subId   = parseInt(this.value) || 0;

        // Color
        if(color) {
            cell.style.borderLeft = '3px solid ' + color;
            this.style.borderColor = color;
        } else {
            cell.style.borderLeft = '';
            this.style.borderColor = '';
        }

        // Update faculty dropdown for this cell
        const facSel = cell.querySelector('.tt-faculty');
        const prevFacId = facSel.value;
        facSel.innerHTML = '<option value="">— Faculty —</option>';

        let facultyList = ALL_FACULTY;
        if(subId && FACULTY_BY_SUB[subId] && FACULTY_BY_SUB[subId].length) {
            facultyList = FACULTY_BY_SUB[subId];
        }
        facultyList.forEach(function(f) {
            const opt = document.createElement('option');
            opt.value = f.id;
            opt.textContent = f.name + (f.hours_per_week ? ' ('+f.hours_per_week+'h/wk)' : '');
            if(f.id == prevFacId) opt.selected = true;
            facSel.appendChild(opt);
        });
    });
});

// ── Faculty conflict highlight on change ────────────────────
document.querySelectorAll('.tt-faculty').forEach(function(sel) {
    sel.addEventListener('change', function() {
        const cell  = this.closest('.tt-cell');
        const day   = cell.dataset.day;
        const pid   = cell.dataset.pid;
        const facId = parseInt(this.value) || 0;
        checkConflict(cell, day, pid, facId);
    });
});

function checkConflict(cell, day, pid, facId) {
    const existing = cell.querySelector('.live-conflict-hint');
    if(existing) existing.remove();

    if(!facId) { cell.style.background = ''; return; }

    const cm = CONFLICT_MAP[facId];
    if(cm && cm[day] && cm[day][pid]) {
        cell.style.background = '#fff5f5';
        const hint = document.createElement('div');
        hint.className = 'text-danger live-conflict-hint';
        hint.style.cssText = 'font-size:.6rem;line-height:1.1;margin-bottom:2px';
        hint.innerHTML = '<i class="fas fa-exclamation-circle"></i> Conflict: ' + cm[day][pid];
        cell.prepend(hint);
    } else {
        cell.style.background = '';
    }
}

// ── Clear all ────────────────────────────────────────────────
document.getElementById('btnClearAll').addEventListener('click', function() {
    if(!confirm('Clear all timetable cells?')) return;
    document.querySelectorAll('.tt-cell').forEach(function(cell) {
        cell.querySelectorAll('select').forEach(s => { s.selectedIndex = 0; s.style.borderColor = ''; });
        cell.style.borderLeft = '';
        cell.style.background = '';
        const hint = cell.querySelector('.live-conflict-hint');
        if(hint) hint.remove();
    });
});

// ── Save via fetch ───────────────────────────────────────────
document.getElementById('frmGenerateTT').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveTT');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    document.getElementById('conflictAlert').classList.add('d-none');

    try {
        const res  = await fetch(this.action, { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if(data.status === 'success') {
            document.getElementById('slotCounter').textContent = (data.count || 0) + ' slot(s) saved';
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Saved!';
            document.getElementById('nextStepPrompt').classList.remove('d-none');

            // Show conflicts
            if(data.conflicts && data.conflicts.length) {
                const list = document.getElementById('conflictList');
                list.innerHTML = '';
                data.conflicts.forEach(c => {
                    const li = document.createElement('li');
                    li.textContent = c.faculty + ' — ' + c.day.charAt(0).toUpperCase()+c.day.slice(1) + ', Period ' + c.period + ' (also in Section ' + c.section + ')';
                    list.appendChild(li);
                });
                document.getElementById('conflictAlert').classList.remove('d-none');
                if(typeof toastr !== 'undefined') toastr.warning(data.conflicts.length + ' conflict(s) detected!');
            } else {
                if(typeof toastr !== 'undefined') toastr.success(data.message);
            }

            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Timetable';
            }, 2000);
        } else {
            if(typeof toastr !== 'undefined') toastr.error(data.message || 'Failed to save');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Timetable';
        }
    } catch(err) {
        if(typeof toastr !== 'undefined') toastr.error('Server error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Timetable';
    }
});

// ── Copy Timetable ───────────────────────────────────────────
document.getElementById('modalCopyTT').addEventListener('show.bs.modal', async function() {
    const sel = document.getElementById('copySourceSection');
    sel.innerHTML = '<option value="">Loading…</option>';
    try {
        const res  = await fetch('<?= url('academic/timetable/ajax/sections?batch_id=' . $section['batch_id']) ?>');
        const data = await res.json();
        sel.innerHTML = '<option value="">Select source section…</option>';
        (data.sections || []).forEach(function(s) {
            if(s.id == SECTION_ID) return; // skip self
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = 'Section ' + s.section_name + ' (' + (s.timetable_slots||0) + ' slots)';
            sel.appendChild(opt);
        });
    } catch(e) {
        sel.innerHTML = '<option value="">Error loading sections</option>';
    }
});

document.getElementById('copySourceSection').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('copySourceInfo').textContent =
        this.value ? 'Will copy timetable from: ' + opt.textContent : '';
});

document.getElementById('btnDoCopy').addEventListener('click', async function() {
    const srcId = document.getElementById('copySourceSection').value;
    if(!srcId) { alert('Please select a source section.'); return; }
    if(!confirm('This will overwrite the current timetable. Continue?')) return;

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Copying…';
    try {
        const fd = new FormData();
        fd.append('source_section_id', srcId);
        fd.append('target_section_id', SECTION_ID);
        const res  = await fetch('<?= url('academic/timetable/copy') ?>', { method: 'POST', body: fd });
        const data = await res.json();
        if(data.status === 'success') {
            if(typeof toastr !== 'undefined') toastr.success(data.message);
            bootstrap.Modal.getInstance(document.getElementById('modalCopyTT')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            if(typeof toastr !== 'undefined') toastr.error(data.message || 'Copy failed');
        }
    } catch(e) {
        if(typeof toastr !== 'undefined') toastr.error('Server error');
    }
    this.disabled = false;
    this.innerHTML = '<i class="fas fa-copy me-1"></i>Copy Timetable';
});

// ── Subject legend click — highlight that subject's cells ────
document.querySelectorAll('.subject-legend-badge').forEach(function(badge) {
    badge.addEventListener('click', function() {
        const subId = this.dataset.subjectId;
        document.querySelectorAll('.tt-subject').forEach(function(sel) {
            const cell = sel.closest('.tt-cell');
            if(sel.value == subId) {
                cell.style.outline = '2px solid #3b82f6';
            } else {
                cell.style.outline = '';
            }
        });
    });
});
</script>

<style>
#ttGrid td { padding: 3px !important; }
#ttGrid select { border-radius: 4px !important; }
.tt-cell:hover { background-color: #fafbff !important; }
.conflict-cell { background: #fff5f5 !important; }
</style>
