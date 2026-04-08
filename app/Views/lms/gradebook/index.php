<style>
.gb-wrap { overflow-x:auto; }
.gb-table { border-collapse:separate; border-spacing:0; min-width:600px; width:100%; }
.gb-table th { background:#f8f7ff; font-size:.72rem; font-weight:700; color:#64748b; padding:.55rem .65rem; white-space:nowrap; border-bottom:2px solid #e8e3ff; position:sticky; top:0; z-index:2; }
.gb-table td { padding:.55rem .65rem; font-size:.8rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.gb-table tr:hover td { background:#f8f7ff; }
.gb-table th.sticky-col, .gb-table td.sticky-col { position:sticky; left:0; z-index:1; background:#fff; border-right:1px solid #e8e3ff; }
.gb-table th.sticky-col { background:#f8f7ff; z-index:3; }
.score-cell { text-align:center; min-width:60px; }
.grade-pill { font-size:.72rem; font-weight:800; padding:.25rem .6rem; border-radius:8px; display:inline-block; }
.weight-input { width:60px; text-align:center; border:1px solid #e2e8f0; border-radius:6px; padding:.2rem .3rem; font-size:.75rem; }
.ov-badge { font-size:.62rem; color:#d97706; background:#fef3c7; border-radius:4px; padding:.1rem .3rem; font-weight:700; }
</style>

<?php
function gradeColor(float $pct): string {
    if ($pct >= 90) return '#065f46;background:#d1fae5';
    if ($pct >= 75) return '#1d4ed8;background:#dbeafe';
    if ($pct >= 60) return '#92400e;background:#fef3c7';
    return '#991b1b;background:#fee2e2';
}
?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-table me-2 text-primary"></i>Gradebook</h4>
    <?php if ($courseId): ?>
    <div class="d-flex gap-2">
        <a href="<?= url("elms/gradebook/{$courseId}/export") ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-download me-1"></i>CSV</a>
        <?php if (!empty($course['subject_id'])): ?>
        <button type="button" class="btn btn-outline-success btn-sm" style="border-radius:8px" onclick="syncAcademic()"><i class="fas fa-sync me-1"></i>Sync to Academic</button>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Course selector -->
<form method="GET" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label small fw-semibold mb-1">Select Course</label>
            <select name="course" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">— Choose a course —</option>
                <?php foreach ($myCourses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($courseId): ?>
        <div class="col-auto">
            <div class="btn-group btn-group-sm">
                <a href="?course=<?= $courseId ?>&sort=name"       class="btn <?= $sort==='name'?'btn-primary':'btn-outline-secondary' ?>" style="border-radius:8px 0 0 8px">Name</a>
                <a href="?course=<?= $courseId ?>&sort=grade_desc" class="btn <?= $sort==='grade_desc'?'btn-primary':'btn-outline-secondary' ?>">Grade ↓</a>
                <a href="?course=<?= $courseId ?>&sort=grade_asc"  class="btn <?= $sort==='grade_asc'?'btn-primary':'btn-outline-secondary' ?>" style="border-radius:0 8px 8px 0">Grade ↑</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</form>

<?php if (!$courseId): ?>
<div class="text-center py-5 text-muted">
    <i class="fas fa-table" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">Select a course to view the gradebook</p>
</div>
<?php elseif (empty($students)): ?>
<div class="text-center py-5 text-muted">
    <i class="fas fa-user-graduate" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">No enrolled students found</p>
</div>
<?php else: ?>

<!-- Grade Weights -->
<div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="fw-bold small" style="color:#0f172a"><i class="fas fa-balance-scale me-2 text-primary"></i>Grade Weights</span>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted">Assignments</label>
            <input type="number" id="wAssign" class="weight-input" value="<?= $weights['assignments_pct'] ?>" min="0" max="100">%
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted">Quizzes</label>
            <input type="number" id="wQuiz" class="weight-input" value="<?= $weights['quizzes_pct'] ?>" min="0" max="100">%
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="small text-muted">Attendance</label>
            <input type="number" id="wAtt" class="weight-input" value="<?= $weights['attendance_pct'] ?>" min="0" max="100">%
        </div>
        <span class="small text-muted" id="wTotal">(Total: <?= $weights['assignments_pct']+$weights['quizzes_pct']+$weights['attendance_pct'] ?>%)</span>
        <button class="btn btn-primary btn-sm ms-auto" style="border-radius:8px" onclick="saveWeights()"><i class="fas fa-save me-1"></i>Save Weights</button>
    </div>
</div>

<!-- Summary stats -->
<?php
$count       = count($students);
$graded      = array_filter($students, fn($s) => $s['final'] !== null);
$classAvg    = count($graded) ? round(array_sum(array_column($graded,'final')) / count($graded), 1) : null;
$passing     = count(array_filter($graded, fn($s) => $s['final'] >= 60));
?>
<div class="row g-2 mb-3">
    <?php foreach ([['Students',$count,'#4f46e5'],['Class Avg',$classAvg!==null?$classAvg.'%':'—','#0284c7'],['Passing',$passing,'#16a34a'],['Failing',$count-$passing,'#dc2626']] as [$lbl,$val,$col]): ?>
    <div class="col-6 col-md-3">
        <div class="bg-white rounded-3 border p-2 text-center" style="border-color:#e8e3ff!important">
            <div style="font-size:1.4rem;font-weight:900;color:<?= $col ?>"><?= $val ?></div>
            <div class="text-muted" style="font-size:.7rem"><?= $lbl ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Gradebook table -->
<div class="bg-white rounded-3 border" style="border-color:#e8e3ff!important;overflow:hidden">
    <div class="gb-wrap">
        <table class="gb-table">
            <thead>
                <tr>
                    <th class="sticky-col" style="min-width:180px">Student</th>
                    <?php foreach ($assignments as $a): ?>
                    <th class="score-cell" title="<?= e($a['title']) ?>">📝 <?= e(mb_strimwidth($a['title'],0,18,'…')) ?></th>
                    <?php endforeach; ?>
                    <?php foreach ($quizzes as $q): ?>
                    <th class="score-cell" title="<?= e($q['title']) ?>">🧩 <?= e(mb_strimwidth($q['title'],0,18,'…')) ?></th>
                    <?php endforeach; ?>
                    <th class="score-cell" style="border-left:2px solid #e8e3ff">Assign Avg</th>
                    <th class="score-cell">Quiz Avg</th>
                    <th class="score-cell">Attend %</th>
                    <th class="score-cell" style="border-left:2px solid #e8e3ff">Final %</th>
                    <th class="score-cell">Grade</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $st): ?>
            <tr id="row<?= $st['id'] ?>">
                <td class="sticky-col">
                    <div class="fw-semibold" style="color:#0f172a;font-size:.82rem"><?= e($st['name']) ?></div>
                    <div class="text-muted" style="font-size:.68rem"><?= e($st['email']) ?></div>
                    <?php if ($st['is_overridden']): ?><span class="ov-badge">Override</span><?php endif; ?>
                </td>
                <?php foreach ($assignments as $a): ?>
                <?php $s = $st['assignment_scores'][$a['id']] ?? null; ?>
                <td class="score-cell text-center">
                    <?php if ($s !== null): ?>
                    <span style="color:<?= gradeColor($s) ?>;font-weight:700;font-size:.78rem"><?= number_format($s,0) ?>%</span>
                    <?php else: ?>
                    <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
                <?php foreach ($quizzes as $q): ?>
                <?php $s = $st['quiz_scores'][$q['id']] ?? null; ?>
                <td class="score-cell text-center">
                    <?php if ($s !== null): ?>
                    <span style="color:<?= gradeColor($s) ?>;font-weight:700;font-size:.78rem"><?= number_format($s,0) ?>%</span>
                    <?php else: ?>
                    <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
                <td class="score-cell text-center" style="border-left:2px solid #f1f5f9;font-weight:600">
                    <?= $st['assign_avg'] !== null ? number_format($st['assign_avg'],1).'%' : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="score-cell text-center" style="font-weight:600">
                    <?= $st['quiz_avg'] !== null ? number_format($st['quiz_avg'],1).'%' : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="score-cell text-center" style="font-weight:600">
                    <?= $st['att_pct'] !== null ? number_format($st['att_pct'],1).'%' : '<span class="text-muted">—</span>' ?>
                </td>
                <td class="score-cell text-center" style="border-left:2px solid #f1f5f9">
                    <?php if ($st['final'] !== null): ?>
                    <strong style="font-size:.9rem;color:<?= $st['final']>=60?'#16a34a':'#dc2626' ?>"><?= number_format($st['final'],1) ?>%</strong>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </td>
                <td class="score-cell text-center">
                    <?php if ($st['letter'] !== '—'): ?>
                    <?php $c = gradeColor((float)($st['final'] ?? 0)); ?>
                    <span class="grade-pill" style="color:<?= explode(';',$c)[0] ?>;<?= explode(';',$c)[1] ?>"><?= $st['letter'] ?></span>
                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="<?= url("elms/gradebook/{$courseId}/student/{$st['id']}") ?>" class="btn btn-sm btn-outline-primary" style="border-radius:7px;font-size:.7rem" title="Details"><i class="fas fa-eye"></i></a>
                        <button class="btn btn-sm btn-outline-warning" style="border-radius:7px;font-size:.7rem" title="Override" onclick="openOverride(<?= $st['id'] ?>, '<?= e($st['name']) ?>', <?= (float)($st['final']??0) ?>, '<?= e($st['letter']) ?>')"><i class="fas fa-edit"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Override Modal -->
<div class="modal fade" id="overrideModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:14px;border:none">
            <div class="modal-header" style="background:#f8f7ff;border-bottom:1px solid #e8e3ff">
                <h6 class="modal-title fw-bold" id="ovModalTitle"><i class="fas fa-edit me-2 text-warning"></i>Grade Override</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <input type="hidden" id="ovUserId">
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Final Grade (%)</label>
                    <input type="number" id="ovGrade" class="form-control form-control-sm" min="0" max="100" step="0.1">
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold">Letter Grade</label>
                    <input type="text" id="ovLetter" class="form-control form-control-sm" placeholder="A, B+, C…" maxlength="5">
                </div>
                <div class="mb-1">
                    <label class="form-label small fw-semibold">Reason (optional)</label>
                    <textarea id="ovNote" class="form-control form-control-sm" rows="2" placeholder="Reason for override…"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="border-radius:8px">Cancel</button>
                <button class="btn btn-sm btn-warning" style="border-radius:8px" onclick="saveOverride()"><i class="fas fa-save me-1"></i>Save Override</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
const COURSE_ID = <?= $courseId ?: 0 ?>;
const CSRF      = '<?= csrfToken() ?>';
const BASE      = '<?= url("elms/gradebook") ?>';
const ovModal   = <?= $courseId ? 'new bootstrap.Modal(document.getElementById("overrideModal"))' : 'null' ?>;

function saveWeights() {
    const a = parseInt(document.getElementById('wAssign').value)||0;
    const q = parseInt(document.getElementById('wQuiz').value)||0;
    const t = parseInt(document.getElementById('wAtt').value)||0;
    document.getElementById('wTotal').textContent = `(Total: ${a+q+t}%)`;
    if (a+q+t !== 100) { alert('Weights must sum to exactly 100%'); return; }
    fetch(`${BASE}/${COURSE_ID}/weights`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF, assignments_pct:a, quizzes_pct:q, attendance_pct:t}),
    }).then(r=>r.json()).then(d=>{
        if (d.status==='ok') { alert('Weights saved! Refresh to recalculate.'); }
        else alert(d.error||'Failed');
    });
}

function openOverride(uid, name, grade, letter) {
    document.getElementById('ovModalTitle').innerHTML = `<i class="fas fa-edit me-2 text-warning"></i>Override — ${name}`;
    document.getElementById('ovUserId').value  = uid;
    document.getElementById('ovGrade').value   = grade;
    document.getElementById('ovLetter').value  = letter === '—' ? '' : letter;
    document.getElementById('ovNote').value    = '';
    ovModal.show();
}

function saveOverride() {
    const uid    = document.getElementById('ovUserId').value;
    const grade  = parseFloat(document.getElementById('ovGrade').value)||0;
    const letter = document.getElementById('ovLetter').value.trim();
    const note   = document.getElementById('ovNote').value.trim();
    fetch(`${BASE}/${COURSE_ID}/student/${uid}/override`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF, final_grade:grade, letter_grade:letter, note}),
    }).then(r=>r.json()).then(d=>{
        if (d.status==='ok') { ovModal.hide(); location.reload(); }
        else alert(d.error||'Failed');
    });
}

// Weight total live update
['wAssign','wQuiz','wAtt'].forEach(id => {
    document.getElementById(id)?.addEventListener('input', () => {
        const a = parseInt(document.getElementById('wAssign')?.value)||0;
        const q = parseInt(document.getElementById('wQuiz')?.value)||0;
        const t = parseInt(document.getElementById('wAtt')?.value)||0;
        const el = document.getElementById('wTotal');
        if (el) { el.textContent = `(Total: ${a+q+t}%)`; el.style.color = (a+q+t===100)?'#16a34a':'#dc2626'; }
    });
});

function syncAcademic() {
    if (!confirm('Sync all LMS grades to the academic module for this course?')) return;
    fetch(`${BASE}/${COURSE_ID}/sync-academic`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF}),
    }).then(r=>r.json()).then(d=>{
        if (d.status==='ok') alert(d.message||'Synced successfully!');
        else alert(d.message||'Sync failed');
    }).catch(()=>alert('Network error'));
}
</script>
