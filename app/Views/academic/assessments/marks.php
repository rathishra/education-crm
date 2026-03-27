<?php
$pageTitle  = 'Marks: ' . e($assessment['assessment_name']);
$isIntExt   = ($assessment['evaluation_mode'] === 'internal_external');
$hasSchema  = !empty($assessment['grading_schema_id']);
$intMax     = (float)($assessment['internal_max_marks'] ?? 0);
$extMax     = (float)($assessment['external_max_marks'] ?? 0);
$intMin     = (float)($assessment['internal_min_marks'] ?? 0);
$extMin     = (float)($assessment['external_min_marks'] ?? 0);
$maxMarks   = (float)$assessment['max_marks'];
?>
<style>
.marks-input         { width: 90px; }
.grade-badge         { font-size:.72rem; min-width: 36px; text-align:center; }
.pass-row            { background: #f0fdf4!important; }
.fail-row            { background: #fef2f2!important; }
.absent-row          { background: #fffbeb!important; }
.int-input,.ext-input{ width: 80px; }
.pct-cell            { font-size:.78rem; color:#6b7280; }
</style>

<!-- Assessment Header -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:48px;height:48px;background:linear-gradient(135deg,#4f46e5,#7c3aed);font-size:1.1rem">
                        <?= strtoupper(substr($assessment['assessment_type'],0,1)) ?>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold"><?= e($assessment['assessment_name']) ?></h5>
                        <div class="text-muted small">
                            <strong><?= e($assessment['program_name']) ?> (<?= e($assessment['batch_term']) ?>)</strong>
                            &nbsp;|&nbsp; <?= e($assessment['subject_code']) ?> — <?= e($assessment['subject_name']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= url('academic/assessments') ?>" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i>Back</a>
            </div>
        </div>

        <!-- KPI row -->
        <div class="row g-2 mt-3 border-top pt-3">
            <?php
            $kpis = [
                ['Max Marks',    $assessment['max_marks'],    'fa-bullseye',     '#4f46e5'],
                ['Pass Marks',   $assessment['passing_marks'],'fa-check-circle', '#10b981'],
                ['Weightage',    $assessment['weightage'].'%','fa-balance-scale','#f59e0b'],
            ];
            if ($isIntExt) {
                $kpis[] = ['Internal Max', $intMax, 'fa-pen',     '#06b6d4'];
                $kpis[] = ['External Max', $extMax, 'fa-file-alt','#8b5cf6'];
            }
            if ($hasSchema) {
                $kpis[] = ['Grading Scheme', e($assessment['schema_code']), 'fa-layer-group', '#ec4899'];
            }
            ?>
            <?php foreach($kpis as [$label,$val,$icon,$clr]): ?>
            <div class="col">
                <div class="text-center p-2 rounded-2 border">
                    <i class="fas <?= $icon ?> mb-1 d-block" style="color:<?= $clr ?>;font-size:.9rem"></i>
                    <div class="fw-bold small" style="color:<?= $clr ?>"><?= $val ?></div>
                    <div class="text-muted" style="font-size:.68rem"><?= $label ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if($assessment['status'] === 'completed'): ?>
<div class="alert alert-success border-0 shadow-sm mb-4">
    <i class="fas fa-lock me-2"></i>This assessment is finalized. Marks are locked.
</div>
<?php endif; ?>

<!-- ── Mark Entry Table ── -->
<form id="frmSaveMarks" method="POST" action="<?= url('academic/assessments/marks/store') ?>">
    <input type="hidden" name="assessment_id" value="<?= $assessment['id'] ?>">

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="marksTable">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Roll No.</th>
                            <th>Student Name</th>
                            <?php if($isIntExt): ?>
                            <th class="text-center">Internal<br><small class="fw-normal opacity-75">Max <?= number_format($intMax,1) ?></small></th>
                            <th class="text-center">External<br><small class="fw-normal opacity-75">Max <?= number_format($extMax,1) ?></small></th>
                            <th class="text-center">Total</th>
                            <?php else: ?>
                            <th class="text-center">Marks<br><small class="fw-normal opacity-75">Max <?= number_format($maxMarks,1) ?></small></th>
                            <?php endif; ?>
                            <?php if($hasSchema): ?><th class="text-center">%</th><th class="text-center">Grade</th><?php endif; ?>
                            <th class="text-center">Absent</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($students)): ?>
                        <tr><td colspan="12" class="text-center text-muted py-5"><i class="fas fa-users-slash fa-2x d-block mb-2 opacity-25"></i>No active students found.</td></tr>
                        <?php else: ?>
                        <?php foreach($students as $i => $st):
                            $sid      = $st['id'];
                            $rec      = $records[$sid] ?? [];
                            $isAbs    = !empty($rec['is_absent']);
                            $mObt     = $rec['marks_obtained'] ?? '';
                            $intM     = $rec['internal_marks'] ?? '';
                            $extM     = $rec['external_marks'] ?? '';
                            $pct      = $rec['consolidated_percentage'] ?? '';
                            $grade    = $rec['grade_label'] ?? '';
                            $gradeP   = $rec['grade_point'] ?? null;
                            $isPas    = $rec['is_pass'] ?? null;
                            $rowCls   = $isAbs ? 'absent-row' : ($isPas === 0 ? 'fail-row' : ($isPas === 1 ? 'pass-row' : ''));
                        ?>
                        <tr class="<?= $rowCls ?>" id="row_<?= $sid ?>">
                            <td class="ps-4 text-muted small"><?= $i+1 ?></td>
                            <td class="fw-bold text-muted small"><?= e($st['roll_number']) ?></td>
                            <td class="fw-semibold small"><?= e($st['first_name'].' '.$st['last_name']) ?></td>

                            <?php if($isIntExt): ?>
                            <!-- Internal marks -->
                            <td class="text-center">
                                <input type="number" step="0.5" max="<?= $intMax ?>" min="0"
                                       class="form-control form-control-sm text-center fw-bold int-input marks-col"
                                       name="int_marks[<?= $sid ?>]"
                                       value="<?= e($intM) ?>"
                                       <?= $isAbs ? 'disabled' : '' ?>
                                       oninput="computeRow(<?= $sid ?>, <?= $maxMarks ?>, <?= $intMax ?>, <?= $extMax ?>)">
                                <?php if($intMin > 0): ?>
                                <div class="text-muted" style="font-size:.65rem">Min: <?= $intMin ?></div>
                                <?php endif; ?>
                            </td>
                            <!-- External marks -->
                            <td class="text-center">
                                <input type="number" step="0.5" max="<?= $extMax ?>" min="0"
                                       class="form-control form-control-sm text-center fw-bold ext-input marks-col"
                                       name="ext_marks[<?= $sid ?>]"
                                       value="<?= e($extM) ?>"
                                       <?= $isAbs ? 'disabled' : '' ?>
                                       oninput="computeRow(<?= $sid ?>, <?= $maxMarks ?>, <?= $intMax ?>, <?= $extMax ?>)">
                                <?php if($extMin > 0): ?>
                                <div class="text-muted" style="font-size:.65rem">Min: <?= $extMin ?></div>
                                <?php endif; ?>
                            </td>
                            <!-- Total (read-only) -->
                            <td class="text-center">
                                <span class="fw-bold" id="tot_<?= $sid ?>"><?= ($intM !== '' && $extM !== '') ? number_format((float)$intM + (float)$extM, 1) : '—' ?></span>
                            </td>
                            <?php else: ?>
                            <!-- Direct marks -->
                            <td class="text-center">
                                <input type="number" step="0.5" max="<?= $maxMarks ?>" min="0"
                                       class="form-control form-control-sm text-center fw-bold marks-input marks-col"
                                       name="marks[<?= $sid ?>]"
                                       value="<?= e($mObt) ?>"
                                       <?= $isAbs ? 'disabled' : '' ?>
                                       oninput="computeRow(<?= $sid ?>, <?= $maxMarks ?>, 0, 0)">
                            </td>
                            <?php endif; ?>

                            <?php if($hasSchema): ?>
                            <td class="text-center pct-cell" id="pct_<?= $sid ?>"><?= $pct !== '' ? number_format((float)$pct, 1).'%' : '—' ?></td>
                            <td class="text-center" id="grade_<?= $sid ?>">
                                <?php if($grade): ?>
                                <span class="badge grade-badge <?= $isPas ? 'bg-success' : 'bg-danger' ?>"><?= e($grade) ?></span>
                                <?php if($gradeP !== null): ?><div style="font-size:.65rem;color:#9ca3af"><?= number_format((float)$gradeP,1) ?>pt</div><?php endif; ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <?php endif; ?>

                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input abs-toggle" type="checkbox"
                                           name="absents[<?= $sid ?>]" value="1"
                                           <?= $isAbs ? 'checked' : '' ?>
                                           onchange="toggleAbsent(this, <?= $sid ?>)">
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm border-light"
                                       name="remarks[<?= $sid ?>]"
                                       value="<?= e($rec['remarks'] ?? '') ?>" placeholder="…">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($assessment['status'] !== 'completed'): ?>
        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <?php if($hasSchema): ?>
                <i class="fas fa-info-circle me-1"></i>Grade is auto-computed from scheme <strong><?= e($assessment['schema_code']) ?></strong>
                <?php else: ?>
                <i class="fas fa-info-circle me-1"></i>No grading scheme linked — grades will not be computed
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
                <input type="hidden" name="finalize" value="0" id="inpFinalize">
                <button type="button" class="btn btn-outline-primary fw-bold btnSave" data-fin="0">
                    <i class="fas fa-save me-1"></i>Save Draft
                </button>
                <button type="button" class="btn btn-danger fw-bold btnSave" data-fin="1">
                    <i class="fas fa-lock me-1"></i>Finalize &amp; Lock
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</form>

<script>
// Grade rules loaded server-side for client preview
const GRADE_RULES = <?= json_encode($gradeRules) ?>;
const IS_INT_EXT  = <?= $isIntExt ? 'true' : 'false' ?>;
const HAS_SCHEMA  = <?= $hasSchema ? 'true' : 'false' ?>;

function computeRow(sid, maxMark, intMax, extMax) {
    let total = 0;
    if (IS_INT_EXT) {
        const im = parseFloat(document.querySelector(`[name="int_marks[${sid}]"]`)?.value) || 0;
        const em = parseFloat(document.querySelector(`[name="ext_marks[${sid}]"]`)?.value) || 0;
        total = im + em;
        const totEl = document.getElementById('tot_' + sid);
        if (totEl) totEl.textContent = total.toFixed(1);
    } else {
        total = parseFloat(document.querySelector(`[name="marks[${sid}]"]`)?.value) || 0;
    }

    if (!HAS_SCHEMA || !GRADE_RULES.length) return;

    const pct = maxMark > 0 ? (total / maxMark) * 100 : 0;
    const pctEl = document.getElementById('pct_' + sid);
    if (pctEl) pctEl.textContent = pct.toFixed(1) + '%';

    // Find grade
    const rule = GRADE_RULES.find(r => pct >= parseFloat(r.min_percentage) && pct <= parseFloat(r.max_percentage));
    const gradeEl = document.getElementById('grade_' + sid);
    if (gradeEl) {
        if (rule) {
            const cls = rule.is_pass ? 'bg-success' : 'bg-danger';
            gradeEl.innerHTML = `<span class="badge grade-badge ${cls}">${rule.grade_label}</span><div style="font-size:.65rem;color:#9ca3af">${parseFloat(rule.grade_point).toFixed(1)}pt</div>`;
        } else {
            gradeEl.innerHTML = '—';
        }
    }

    // Row color
    const row = document.getElementById('row_' + sid);
    if (row) {
        row.classList.remove('pass-row','fail-row');
        if (rule) row.classList.add(rule.is_pass ? 'pass-row' : 'fail-row');
    }
}

function toggleAbsent(cb, sid) {
    const row = document.getElementById('row_' + sid);
    const inputs = row.querySelectorAll('.marks-col');
    if (cb.checked) {
        inputs.forEach(i => { i.value = ''; i.disabled = true; });
        row.classList.remove('pass-row','fail-row');
        row.classList.add('absent-row');
        ['tot_','pct_','grade_'].forEach(p => {
            const el = document.getElementById(p + sid);
            if (el) el.textContent = '—';
        });
    } else {
        inputs.forEach(i => i.disabled = false);
        row.classList.remove('absent-row');
    }
}

// Form submit
document.querySelectorAll('.btnSave').forEach(btn => {
    btn.addEventListener('click', function() {
        const fin = this.dataset.fin;
        if (fin == '1' && !confirm('Finalize and lock marks? This cannot be undone easily.')) return;
        document.getElementById('inpFinalize').value = fin;
        const btns = document.querySelectorAll('.btnSave');
        btns.forEach(b => b.disabled = true);
        const form   = document.getElementById('frmSaveMarks');
        const data   = new FormData(form);
        fetch(form.action, { method: 'POST', body: new URLSearchParams(data) })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    toastr.success(res.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    toastr.error(res.message || 'Saving failed');
                    btns.forEach(b => b.disabled = false);
                }
            })
            .catch(() => {
                toastr.error('Server error');
                btns.forEach(b => b.disabled = false);
            });
    });
});
</script>
