<?php $pageTitle = 'Setup Assessment'; ?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <form id="frmAddAssessment" method="POST" action="<?= url('academic/assessments/store') ?>">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">Configure Assessment</h4>
                    <p class="text-muted small mb-0">Set up exam structure, grading scheme, and mark split</p>
                </div>
                <a href="<?= url('academic/assessments') ?>" class="btn btn-light border"><i class="fas fa-times me-1"></i>Cancel</a>
            </div>

            <!-- ── Cohort & Subject ── -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom fw-bold">
                    <i class="fas fa-users me-2 text-primary"></i>Cohort &amp; Subject
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Batch / Cohort <span class="text-danger">*</span></label>
                            <select class="form-select" name="batch_id" required>
                                <option value="">— Select Active Batch —</option>
                                <?php foreach($batches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">— Choose Subject —</option>
                                <?php foreach($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= e($s['subject_code']) ?> — <?= e($s['subject_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Assessment Details ── -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom fw-bold">
                    <i class="fas fa-clipboard-list me-2 text-warning"></i>Assessment Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assessment Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="assessment_name" placeholder="e.g. Internal Test 1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assessment Type</label>
                            <select class="form-select" name="assessment_type">
                                <option value="internal">Internal Theory Exam</option>
                                <option value="assignment">Assignment / Report</option>
                                <option value="quiz">Quiz / MCQ</option>
                                <option value="lab">Lab Practical</option>
                                <option value="project">Project / Portfolio</option>
                                <option value="midterm">Midterm</option>
                                <option value="final">Final Semester Exam</option>
                                <option value="viva">Viva</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max Marks (Total)</label>
                            <input type="number" step="0.5" class="form-control" name="max_marks" id="maxMarksInput" value="100" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Passing Marks</label>
                            <input type="number" step="0.5" class="form-control" name="passing_marks" value="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Final Weightage %</label>
                            <div class="input-group">
                                <input type="number" step="0.5" class="form-control" name="weightage" value="20">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Scheduled Date</label>
                            <input type="date" class="form-control" name="assessment_date">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Grading Scheme & Evaluation Mode ── -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom fw-bold">
                    <i class="fas fa-layer-group me-2 text-info"></i>Grading Scheme &amp; Evaluation Mode
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Grading Scheme</label>
                            <select class="form-select" name="grading_schema_id" id="schemaSelect" onchange="onSchemaChange()">
                                <option value="">— None (no auto-grading) —</option>
                                <?php foreach($schemas as $gs): ?>
                                <option value="<?= $gs['id'] ?>"
                                        data-max="<?= $gs['max_mark'] ?>"
                                        data-comps="<?= $gs['component_count'] ?>"
                                        data-rules="<?= $gs['rule_count'] ?>">
                                    <?= e($gs['code']) ?> — <?= e($gs['name']) ?>
                                    (Max: <?= $gs['max_mark'] ?> | <?= $gs['component_count'] ?> components | <?= $gs['rule_count'] ?> grade rules)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <a href="<?= url('academic/grading-schemas') ?>" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-external-link-alt me-1"></i>Manage Exam Schemes
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Evaluation Mode</label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="evaluation_mode" id="modeDir" value="direct" checked onchange="toggleEvalMode()">
                                    <label class="form-check-label" for="modeDir">
                                        <span class="fw-semibold">Direct</span><br>
                                        <span class="text-muted small">Single marks entry</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="evaluation_mode" id="modeIntExt" value="internal_external" onchange="toggleEvalMode()">
                                    <label class="form-check-label" for="modeIntExt">
                                        <span class="fw-semibold">Internal + External</span><br>
                                        <span class="text-muted small">Separate CIA &amp; Exam marks</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Internal/External split (hidden by default) -->
                        <div class="col-12" id="intExtPanel" style="display:none">
                            <div class="row g-3 p-3 bg-light rounded-3 border">
                                <div class="col-12 mb-1">
                                    <span class="fw-semibold small text-muted text-uppercase" style="letter-spacing:.06em">Internal / External Mark Split</span>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Internal Max</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm split-input" name="internal_max_marks" id="intMax" placeholder="40" oninput="syncSplitTotal()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">External Max</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm split-input" name="external_max_marks" id="extMax" placeholder="60" oninput="syncSplitTotal()">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">Internal Min (Pass)</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm" name="internal_min_marks" placeholder="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small">External Min (Pass)</label>
                                    <input type="number" step="0.5" class="form-control form-control-sm" name="external_min_marks" placeholder="27">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted">Split Total:</span>
                                        <span id="splitTotal" class="fw-bold">—</span>
                                        <span class="small text-muted">/ Max Marks:</span>
                                        <span id="splitMax" class="fw-bold text-primary">100</span>
                                        <span id="splitMatch" class="badge ms-2" style="display:none"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-2">
                <button type="submit" class="btn btn-primary px-5 fw-bold" id="btnSaveAss">
                    <i class="fas fa-save me-2"></i>Register Assessment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEvalMode() {
    const isIntExt = document.getElementById('modeIntExt').checked;
    document.getElementById('intExtPanel').style.display = isIntExt ? 'block' : 'none';
}

function onSchemaChange() {
    const sel = document.getElementById('schemaSelect');
    const opt = sel.options[sel.selectedIndex];
    if (opt.value) {
        const maxMark = opt.dataset.max;
        document.getElementById('maxMarksInput').value = maxMark;
        document.getElementById('splitMax').textContent = maxMark;
        syncSplitTotal();
    }
}

function syncSplitTotal() {
    const im = parseFloat(document.getElementById('intMax')?.value) || 0;
    const em = parseFloat(document.getElementById('extMax')?.value) || 0;
    const total = im + em;
    const max = parseFloat(document.getElementById('maxMarksInput').value) || 0;
    document.getElementById('splitTotal').textContent = total.toFixed(1);
    document.getElementById('splitMax').textContent   = max.toFixed(0);
    const badge = document.getElementById('splitMatch');
    if (total > 0) {
        badge.style.display = 'inline-block';
        if (Math.abs(total - max) < 0.01) {
            badge.className = 'badge bg-success-subtle text-success border border-success-subtle ms-2';
            badge.textContent = '✓ Balanced';
        } else {
            badge.className = 'badge bg-warning-subtle text-warning border border-warning-subtle ms-2';
            badge.textContent = `⚠ Differs by ${Math.abs(total - max).toFixed(1)}`;
        }
    } else {
        badge.style.display = 'none';
    }
}

$('#frmAddAssessment').submit(function(e) {
    e.preventDefault();
    const btn = $('#btnSaveAss').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing…');
    $.ajax({
        url: $(this).attr('action'), method: 'POST', data: $(this).serialize(),
        success: function(res) {
            const data = typeof res === 'string' ? JSON.parse(res) : res;
            if (data.status === 'success') {
                toastr.success(data.message);
                setTimeout(() => window.location.href = APP_URL + '/academic/assessments', 1000);
            } else {
                toastr.error(data.message || 'Validation failed');
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Register Assessment');
            }
        },
        error: function() {
            toastr.error('An error occurred.');
            btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Register Assessment');
        }
    });
});
</script>
