<style>
.att-row { display:flex; align-items:center; gap:.75rem; padding:.6rem .75rem; border-radius:10px; border:1px solid #e8e3ff; background:#fff; margin-bottom:.4rem; transition:background .1s; }
.att-row:hover { background:#f8f7ff; }
.status-radio-group { display:flex; gap:.4rem; }
.status-radio-btn { display:none; }
.status-radio-label { padding:.28rem .7rem; border-radius:20px; font-size:.72rem; font-weight:700; cursor:pointer; border:1.5px solid #e2e8f0; color:#64748b; background:#f8fafc; transition:all .12s; white-space:nowrap; user-select:none; }
.status-radio-btn:checked + .status-radio-label { border-color:transparent; }
.status-radio-btn[value="present"]:checked + .status-radio-label { background:#d1fae5;color:#065f46;border-color:#86efac; }
.status-radio-btn[value="late"]:checked + .status-radio-label    { background:#fef3c7;color:#92400e;border-color:#fbbf24; }
.status-radio-btn[value="excused"]:checked + .status-radio-label { background:#ede9fe;color:#4338ca;border-color:#a5b4fc; }
.status-radio-btn[value="absent"]:checked + .status-radio-label  { background:#fee2e2;color:#991b1b;border-color:#fca5a5; }
.stat-chip { font-size:.78rem; font-weight:700; padding:.3rem .8rem; border-radius:20px; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('elms/attendance') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h4 class="fw-bold mb-0" style="color:#0f172a;font-size:1rem"><i class="fas fa-pen me-2 text-primary"></i><?= e($session['title']) ?></h4>
            <div class="text-muted small"><?= e($session['course_title']) ?> &middot; <?= date('D, d M Y', strtotime($session['session_date'])) ?> &middot; <?= ucfirst($session['type']) ?></div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- Live stats -->
        <span class="stat-chip" style="background:#d1fae5;color:#065f46" id="statPresent"><?= $stats['present'] ?> Present</span>
        <span class="stat-chip" style="background:#fef3c7;color:#92400e" id="statLate"><?= $stats['late'] ?> Late</span>
        <span class="stat-chip" style="background:#fee2e2;color:#991b1b" id="statAbsent"><?= $stats['absent'] ?> Absent</span>
        <span class="stat-chip" style="background:#ede9fe;color:#4338ca" id="statExcused"><?= $stats['excused'] ?> Excused</span>
        <?php if (!$session['is_locked']): ?>
        <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.75rem" onclick="markAll('present')"><i class="fas fa-check me-1"></i>All Present</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($session['is_locked']): ?>
<div class="alert alert-warning py-2 small mb-3"><i class="fas fa-lock me-2"></i>This session is locked. Attendance can no longer be modified.</div>
<?php endif; ?>

<?php if (empty($students)): ?>
<div class="text-center py-5 text-muted">
    <i class="fas fa-users" style="font-size:2.5rem;opacity:.2"></i>
    <p class="mt-2 small">No enrolled students found for this course.</p>
</div>
<?php else: ?>

<!-- Bulk actions bar -->
<?php if (!$session['is_locked']): ?>
<div class="bg-white border rounded-3 p-2 mb-2 d-flex align-items-center gap-2 flex-wrap" style="border-color:#e8e3ff!important">
    <span class="small fw-semibold text-muted">Bulk:</span>
    <button type="button" class="btn btn-sm" style="border-radius:20px;background:#d1fae5;color:#065f46;font-size:.72rem;font-weight:700" onclick="markAll('present')"><i class="fas fa-check me-1"></i>All Present</button>
    <button type="button" class="btn btn-sm" style="border-radius:20px;background:#fee2e2;color:#991b1b;font-size:.72rem;font-weight:700" onclick="markAll('absent')"><i class="fas fa-times me-1"></i>All Absent</button>
    <button type="button" class="btn btn-sm" style="border-radius:20px;background:#fef3c7;color:#92400e;font-size:.72rem;font-weight:700" onclick="markAll('late')"><i class="fas fa-clock me-1"></i>All Late</button>
    <div class="ms-auto d-flex gap-2">
        <button type="button" class="btn btn-success btn-sm" style="border-radius:8px" onclick="saveAll(false)"><i class="fas fa-save me-1"></i>Save</button>
        <button type="button" class="btn btn-outline-warning btn-sm" style="border-radius:8px" onclick="saveAll(true)"><i class="fas fa-lock me-1"></i>Save &amp; Lock</button>
    </div>
</div>
<?php endif; ?>

<div id="studentList">
<?php foreach ($students as $i => $st): ?>
<div class="att-row" data-uid="<?= $st['lms_user_id'] ?>">
    <!-- Avatar -->
    <div style="width:36px;height:36px;border-radius:50%;background:#ede9fe;color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;flex-shrink:0">
        <?= strtoupper(substr($st['student_name'],0,2)) ?>
    </div>
    <!-- Name -->
    <div style="flex:1;min-width:0">
        <div class="fw-semibold small" style="color:#0f172a;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($st['student_name']) ?></div>
        <div class="text-muted" style="font-size:.7rem"><?= e($st['email']) ?></div>
    </div>
    <!-- Status radios -->
    <div class="status-radio-group" <?= $session['is_locked']?'style="pointer-events:none;opacity:.7"':'' ?>>
        <?php foreach (['present'=>'Present','late'=>'Late','excused'=>'Excused','absent'=>'Absent'] as $v=>$l): ?>
        <input class="status-radio-btn" type="radio" name="status_<?= $st['lms_user_id'] ?>" id="s<?= $st['lms_user_id'] ?>_<?= $v ?>"
               value="<?= $v ?>" <?= $st['status']===$v?'checked':'' ?> onchange="onStatusChange(<?= $st['lms_user_id'] ?>)">
        <label class="status-radio-label" for="s<?= $st['lms_user_id'] ?>_<?= $v ?>"><?= $l ?></label>
        <?php endforeach; ?>
    </div>
    <!-- Notes input -->
    <input type="text" class="form-control form-control-sm notes-input" placeholder="Note…" style="max-width:120px;border-radius:8px;font-size:.72rem" value="<?= e($st['rec_notes'] ?? '') ?>" <?= $session['is_locked']?'disabled':'' ?>>
</div>
<?php endforeach; ?>
</div>

<?php if (!$session['is_locked']): ?>
<div class="d-flex gap-2 mt-3">
    <button type="button" class="btn btn-success" style="border-radius:9px" onclick="saveAll(false)"><i class="fas fa-save me-2"></i>Save Attendance</button>
    <button type="button" class="btn btn-warning" style="border-radius:9px" onclick="saveAll(true)"><i class="fas fa-lock me-2"></i>Save &amp; Lock</button>
    <a href="<?= url('elms/attendance') ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
</div>
<?php else: ?>
<div class="d-flex gap-2 mt-3">
    <form method="POST" action="<?= url('elms/attendance/'.$session['id'].'/lock') ?>">
        <?= csrfField() ?>
        <button type="submit" class="btn btn-outline-warning btn-sm" style="border-radius:8px" onclick="return confirm('Unlock this session?')"><i class="fas fa-unlock me-1"></i>Unlock Session</button>
    </form>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
const SAVE_URL = '<?= url("elms/attendance/{$session['id']}/save") ?>';
const CSRF     = '<?= csrfToken() ?>';

function markAll(status) {
    document.querySelectorAll(`input[value="${status}"]`).forEach(r => {
        r.checked = true;
        onStatusChange(parseInt(r.name.replace('status_','')));
    });
}

function onStatusChange(uid) {
    updateStats();
}

function updateStats() {
    const counts = {present:0, late:0, absent:0, excused:0};
    document.querySelectorAll('#studentList .att-row').forEach(row => {
        const checked = row.querySelector('input[type=radio]:checked');
        if (checked) counts[checked.value] = (counts[checked.value]||0) + 1;
    });
    document.getElementById('statPresent').textContent = counts.present + ' Present';
    document.getElementById('statLate').textContent    = counts.late    + ' Late';
    document.getElementById('statAbsent').textContent  = counts.absent  + ' Absent';
    document.getElementById('statExcused').textContent = counts.excused + ' Excused';
}

function saveAll(lock) {
    const rows    = document.querySelectorAll('#studentList .att-row');
    const records = [];
    rows.forEach(row => {
        const uid     = parseInt(row.dataset.uid);
        const checked = row.querySelector('input[type=radio]:checked');
        const notes   = row.querySelector('.notes-input')?.value.trim() || '';
        if (checked) records.push({ lms_user_id: uid, status: checked.value, notes });
    });

    fetch(SAVE_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({ _csrf: CSRF, records, lock: lock ? 1 : 0 }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        if (lock) { location.href = '<?= url("elms/attendance") ?>'; }
        else {
            const btn = document.querySelector('button[onclick="saveAll(false)"]');
            if (btn) { const orig = btn.innerHTML; btn.innerHTML = '<i class="fas fa-check me-2"></i>Saved!'; setTimeout(()=>btn.innerHTML=orig, 2000); }
        }
    })
    .catch(() => alert('Failed to save.'));
}
</script>
