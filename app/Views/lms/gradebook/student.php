<style>
.grade-section { background:#fff; border-radius:14px; border:1px solid #e8e3ff; margin-bottom:1rem; overflow:hidden; }
.grade-section-header { padding:.75rem 1rem; border-bottom:1px solid #f1f0ff; display:flex; align-items:center; justify-content:between; gap:.5rem; }
.score-bar-bg { background:#f1f5f9; border-radius:10px; height:8px; overflow:hidden; }
.score-bar-fill { height:100%; border-radius:10px; }
.big-grade { font-size:3rem; font-weight:900; line-height:1; }
</style>

<?php
function _gradeColor(float $pct): array {
    if ($pct >= 90) return ['color'=>'#065f46','bg'=>'#d1fae5'];
    if ($pct >= 75) return ['color'=>'#1d4ed8','bg'=>'#dbeafe'];
    if ($pct >= 60) return ['color'=>'#92400e','bg'=>'#fef3c7'];
    return ['color'=>'#991b1b','bg'=>'#fee2e2'];
}
?>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <a href="<?= url("elms/gradebook?course={$courseId}") ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0" style="color:#0f172a;font-size:1rem"><i class="fas fa-user-graduate me-2 text-primary"></i><?= e($student['name']) ?></h4>
        <div class="text-muted small"><?= e($courseTitle) ?> &middot; <?= e($student['email']) ?></div>
    </div>
</div>

<div class="row g-3">
    <!-- Left: details -->
    <div class="col-12 col-lg-8">

        <!-- Assignments -->
        <div class="grade-section">
            <div class="grade-section-header">
                <i class="fas fa-tasks text-primary me-2"></i>
                <span class="fw-bold small" style="color:#0f172a">Assignments</span>
                <span class="ms-auto text-muted small">
                    Weight: <?= $weights['assignments_pct'] ?>% &middot;
                    Avg: <strong><?= $assignAvg !== null ? number_format($assignAvg,1).'%' : '—' ?></strong>
                </span>
            </div>
            <?php if (empty($assignRows)): ?>
            <div class="p-3 text-muted small">No assignments in this course.</div>
            <?php else: ?>
            <div class="p-3">
                <?php foreach ($assignRows as $a): ?>
                <?php
                $pct  = $a['max_score'] > 0 && $a['score'] !== null
                      ? round($a['score'] / $a['max_score'] * 100, 1) : null;
                $c    = $pct !== null ? _gradeColor($pct) : null;
                ?>
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div>
                            <span class="fw-semibold small" style="color:#0f172a"><?= e($a['title']) ?></span>
                            <?php if ($a['is_late']): ?><span class="badge ms-1" style="background:#fee2e2;color:#dc2626;border-radius:6px;font-size:.62rem">Late</span><?php endif; ?>
                        </div>
                        <div class="text-end">
                            <?php if ($pct !== null): ?>
                            <span class="fw-bold small" style="color:<?= $c['color'] ?>"><?= number_format($a['score'],1) ?>/<?= $a['max_score'] ?> (<?= $pct ?>%)</span>
                            <?php else: ?>
                            <span class="text-muted small">Not submitted</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="score-bar-bg">
                        <div class="score-bar-fill" style="width:<?= $pct ?? 0 ?>%;background:<?= $c['color'] ?? '#e2e8f0' ?>"></div>
                    </div>
                    <?php if ($a['feedback']): ?>
                    <div class="text-muted mt-1" style="font-size:.7rem"><i class="fas fa-comment me-1"></i><?= e(mb_strimwidth($a['feedback'],0,120,'…')) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Quizzes -->
        <div class="grade-section">
            <div class="grade-section-header">
                <i class="fas fa-question-circle text-primary me-2"></i>
                <span class="fw-bold small" style="color:#0f172a">Quizzes</span>
                <span class="ms-auto text-muted small">
                    Weight: <?= $weights['quizzes_pct'] ?>% &middot;
                    Avg: <strong><?= $quizAvg !== null ? number_format($quizAvg,1).'%' : '—' ?></strong>
                </span>
            </div>
            <?php if (empty($quizRows)): ?>
            <div class="p-3 text-muted small">No quizzes in this course.</div>
            <?php else: ?>
            <div class="p-3">
                <?php foreach ($quizRows as $q): ?>
                <?php
                $pct = $q['best_pct'] !== null ? round((float)$q['best_pct'], 1) : null;
                $c   = $pct !== null ? _gradeColor($pct) : null;
                ?>
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div>
                            <span class="fw-semibold small" style="color:#0f172a"><?= e($q['title']) ?></span>
                            <span class="text-muted small ms-1">(Pass: <?= $q['pass_percentage'] ?>%)</span>
                        </div>
                        <div class="text-end">
                            <?php if ($pct !== null): ?>
                            <span class="fw-bold small" style="color:<?= $c['color'] ?>"><?= $pct ?>%</span>
                            <?php if ($q['ever_passed']): ?>
                            <span class="badge ms-1" style="background:#d1fae5;color:#065f46;border-radius:6px;font-size:.62rem">Passed</span>
                            <?php else: ?>
                            <span class="badge ms-1" style="background:#fee2e2;color:#dc2626;border-radius:6px;font-size:.62rem">Failed</span>
                            <?php endif; ?>
                            <span class="text-muted small ms-1"><?= $q['attempts_used'] ?> attempt<?= $q['attempts_used']!=1?'s':'' ?></span>
                            <?php else: ?>
                            <span class="text-muted small">Not attempted</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="score-bar-bg">
                        <div class="score-bar-fill" style="width:<?= $pct ?? 0 ?>%;background:<?= $c['color'] ?? '#e2e8f0' ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Attendance -->
        <div class="grade-section">
            <div class="grade-section-header">
                <i class="fas fa-clipboard-check text-primary me-2"></i>
                <span class="fw-bold small" style="color:#0f172a">Attendance</span>
                <span class="ms-auto text-muted small">
                    Weight: <?= $weights['attendance_pct'] ?>% &middot;
                    <strong><?= $attPct !== null ? $attPct.'%' : '—' ?></strong>
                    (<?= $attRow['attended'] ?>/<?= $attRow['total_sessions'] ?> sessions)
                </span>
            </div>
            <div class="p-3">
                <div class="score-bar-bg">
                    <?php $ac = $attPct !== null ? _gradeColor((float)$attPct) : null; ?>
                    <div class="score-bar-fill" style="width:<?= $attPct ?? 0 ?>%;background:<?= $ac['color'] ?? '#e2e8f0' ?>"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: final grade card -->
    <div class="col-12 col-lg-4">
        <?php
        $fc = $final !== null ? _gradeColor((float)$final) : ['color'=>'#94a3b8','bg'=>'#f1f5f9'];
        ?>
        <div class="rounded-3 p-4 text-center mb-3" style="background:<?= $fc['bg'] ?>;border:2px solid <?= $fc['color'] ?>">
            <?php if ($override): ?>
            <div class="badge mb-2" style="background:#fef3c7;color:#92400e;border-radius:8px"><i class="fas fa-edit me-1"></i>Overridden</div>
            <?php endif; ?>
            <div class="text-muted fw-semibold mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.07em">Final Grade</div>
            <div class="big-grade" style="color:<?= $fc['color'] ?>"><?= $final !== null ? number_format((float)$final,1).'%' : '—' ?></div>
            <div style="font-size:2rem;font-weight:900;color:<?= $fc['color'] ?>;margin-top:.25rem"><?= $letter ?></div>
        </div>

        <!-- Weight breakdown -->
        <div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
            <h6 class="fw-bold mb-3 small" style="color:#0f172a">Grade Breakdown</h6>
            <?php foreach ([
                ['Assignments', $assignAvg, $weights['assignments_pct']],
                ['Quizzes',     $quizAvg,   $weights['quizzes_pct']],
                ['Attendance',  $attPct,    $weights['attendance_pct']],
            ] as [$label, $avg, $w]): ?>
            <div class="d-flex align-items-center justify-content-between mb-2 small">
                <span class="text-muted"><?= $label ?> (<?= $w ?>%)</span>
                <span class="fw-bold" style="color:#0f172a"><?= $avg !== null ? number_format((float)$avg,1).'%' : '—' ?></span>
            </div>
            <?php endforeach; ?>
            <div class="border-top pt-2 mt-1 d-flex justify-content-between fw-bold small" style="border-color:#e8e3ff!important">
                <span>Weighted Total</span>
                <span style="color:#4f46e5"><?= $final !== null ? number_format((float)$final,1).'%' : '—' ?></span>
            </div>
        </div>

        <!-- Override controls -->
        <div class="bg-white rounded-3 border p-3" style="border-color:#e8e3ff!important">
            <h6 class="fw-bold mb-2 small" style="color:#0f172a"><i class="fas fa-edit me-2 text-warning"></i>Grade Override</h6>
            <?php if ($override): ?>
            <div class="alert alert-warning py-2 small mb-2"><i class="fas fa-info-circle me-1"></i>Override active: <?= number_format((float)$override['final_grade'],1) ?>% (<?= e($override['letter_grade']) ?>)</div>
            <?php if ($override['override_note']): ?>
            <div class="text-muted small mb-2"><?= e($override['override_note']) ?></div>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-danger w-100 mb-2" style="border-radius:8px" onclick="clearOverride()"><i class="fas fa-times me-1"></i>Remove Override</button>
            <?php endif; ?>
            <div class="mb-2">
                <label class="form-label small mb-1">New Grade (%)</label>
                <input type="number" id="ovGrade" class="form-control form-control-sm" min="0" max="100" step="0.1" value="<?= number_format((float)($override['final_grade'] ?? $final ?? 0), 1) ?>">
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Letter</label>
                <input type="text" id="ovLetter" class="form-control form-control-sm" maxlength="5" value="<?= e($override['letter_grade'] ?? $letter) ?>">
            </div>
            <div class="mb-2">
                <label class="form-label small mb-1">Reason</label>
                <textarea id="ovNote" class="form-control form-control-sm" rows="2" placeholder="Optional note…"><?= e($override['override_note'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-warning btn-sm w-100" style="border-radius:8px" onclick="saveOverride()"><i class="fas fa-save me-1"></i>Save Override</button>
        </div>
    </div>
</div>

<script>
const CSRF = '<?= csrfToken() ?>';
const OV_URL    = '<?= url("elms/gradebook/{$courseId}/student/{$student['id']}/override") ?>';
const CLR_URL   = '<?= url("elms/gradebook/{$courseId}/student/{$student['id']}/override/clear") ?>';

function saveOverride() {
    const grade  = parseFloat(document.getElementById('ovGrade').value)||0;
    const letter = document.getElementById('ovLetter').value.trim();
    const note   = document.getElementById('ovNote').value.trim();
    fetch(OV_URL, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF, final_grade:grade, letter_grade:letter, note}),
    }).then(r=>r.json()).then(d=>{ if(d.status==='ok') location.reload(); else alert(d.error||'Failed'); });
}
function clearOverride() {
    if (!confirm('Remove the grade override?')) return;
    fetch(CLR_URL, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF}),
    }).then(r=>r.json()).then(d=>{ if(d.status==='ok') location.reload(); else alert(d.error||'Failed'); });
}
</script>
