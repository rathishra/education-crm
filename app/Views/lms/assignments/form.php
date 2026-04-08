<?php
$isEdit = !empty($assignment);
$a      = $assignment ?? [];
?>
<style>
.lms-form-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; margin-bottom:1.25rem; overflow:hidden; }
.lms-form-card .card-header { background:#f8f7ff; border-bottom:1px solid #e8e3ff; padding:.85rem 1.25rem; font-weight:700; font-size:.88rem; color:#3730a3; }
.lms-form-card .card-body { padding:1.25rem; }
.rubric-row { display:grid; grid-template-columns:1fr 80px 28px; gap:.5rem; align-items:center; margin-bottom:.5rem; }
</style>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/assignments') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <h4 class="fw-bold mb-0" style="color:#0f172a">
        <i class="fas fa-<?= $isEdit?'edit':'plus-circle' ?> me-2 text-primary"></i><?= $isEdit ? 'Edit Assignment' : 'Create Assignment' ?>
    </h4>
</div>

<form method="POST" action="<?= url($isEdit ? 'elms/assignments/'.$a['id'].'/update' : 'elms/assignments/store') ?>">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-12 col-lg-8">

            <!-- Basic -->
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Assignment Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= e($a['title'] ?? '') ?>" required placeholder="e.g. Chapter 3 Review Assignment">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Instructions <span class="text-danger">*</span></label>
                        <textarea name="instructions" class="form-control" rows="8" required
                                  placeholder="Describe the assignment task, requirements, submission format, and evaluation criteria…"><?= e($a['instructions'] ?? '') ?></textarea>
                        <div class="form-text">HTML formatting is supported.</div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Course <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select" id="courseSelect" required>
                                <option value="">— Select Course —</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($a['course_id'] ?? $courseId) == $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Link to Lesson <span class="text-muted">(optional)</span></label>
                            <select name="lesson_id" class="form-select" id="lessonSelect">
                                <option value="">— None —</option>
                                <?php foreach ($lessons as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= ($a['lesson_id'] ?? '') == $l['id'] ? 'selected' : '' ?>>
                                    <?= e($l['section_title']) ?> › <?= e($l['title']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submission settings -->
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-upload me-2"></i>Submission Settings</div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Submission Type</label>
                            <select name="submission_type" class="form-select" id="subTypeSelect">
                                <?php foreach (['any'=>'Any (file, text, or URL)','file'=>'File Upload Only','text'=>'Text Entry Only','url'=>'URL Submission Only'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($a['submission_type'] ?? 'any') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4" id="fileTypeRow">
                            <label class="form-label fw-semibold small">Allowed File Types</label>
                            <input type="text" name="allowed_file_types" class="form-control" value="<?= e($a['allowed_file_types'] ?? '') ?>" placeholder="pdf,docx,zip">
                            <div class="form-text">Comma-separated, blank = any</div>
                        </div>
                        <div class="col-md-4" id="fileSizeRow">
                            <label class="form-label fw-semibold small">Max File Size (MB)</label>
                            <input type="number" name="max_file_size_mb" class="form-control" min="1" max="100" value="<?= e($a['max_file_size_mb'] ?? 10) ?>">
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Max Score</label>
                            <input type="number" name="max_score" class="form-control" min="1" max="1000" value="<?= e($a['max_score'] ?? 100) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Pass Score</label>
                            <input type="number" name="pass_score" class="form-control" min="0" max="1000" value="<?= e($a['pass_score'] ?? 50) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Attempts Allowed</label>
                            <input type="number" name="attempts_allowed" class="form-control" min="0" max="10" value="<?= e($a['attempts_allowed'] ?? 1) ?>">
                            <div class="form-text">0 = unlimited</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rubric (optional) -->
            <div class="lms-form-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-list-check me-2"></i>Grading Rubric <span class="text-muted fw-normal">(optional)</span></span>
                    <button type="button" class="btn btn-xs btn-outline-primary" id="btnAddCriterion" style="font-size:.75rem;padding:3px 10px;border-radius:7px"><i class="fas fa-plus me-1"></i>Add Criterion</button>
                </div>
                <div class="card-body">
                    <div id="rubricRows">
                        <?php
                        $rubric = [];
                        if (!empty($a['rubric'])) {
                            $rubric = is_string($a['rubric']) ? json_decode($a['rubric'], true) : $a['rubric'];
                        }
                        foreach ((array)$rubric as $i => $row): ?>
                        <div class="rubric-row">
                            <input type="text" name="rubric[<?= $i ?>][criterion]" class="form-control form-control-sm" placeholder="Criterion description" value="<?= e($row['criterion'] ?? '') ?>">
                            <input type="number" name="rubric[<?= $i ?>][points]" class="form-control form-control-sm" placeholder="Pts" min="0" value="<?= e($row['points'] ?? '') ?>">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-remove-row" style="padding:3px 7px;border-radius:6px"><i class="fas fa-times"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-muted small mt-1" id="rubricEmpty" <?= !empty($rubric)?'style="display:none"':'' ?>>No rubric criteria added. Click "Add Criterion" to build a rubric.</div>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="col-12 col-lg-4">
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-calendar-alt me-2"></i>Due Date & Deadline</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Due Date & Time</label>
                        <input type="datetime-local" name="due_at" class="form-control"
                               value="<?= e($a['due_at'] ? date('Y-m-d\TH:i', strtotime($a['due_at'])) : '') ?>">
                        <div class="form-text">Leave blank for no deadline.</div>
                    </div>
                    <div class="form-check form-switch mb-2" id="lateSwitchRow">
                        <input class="form-check-input" type="checkbox" name="allow_late" id="chkAllowLate" value="1" <?= !empty($a['allow_late'])?'checked':'' ?>>
                        <label class="form-check-label small fw-semibold" for="chkAllowLate">Allow Late Submissions</label>
                    </div>
                    <div id="penaltyRow" class="mb-3" style="<?= empty($a['allow_late'])?'display:none':'' ?>">
                        <label class="form-label fw-semibold small">Late Penalty (%)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="late_penalty_pct" class="form-control" min="0" max="100" value="<?= e($a['late_penalty_pct'] ?? 0) ?>">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Score deducted for late submissions.</div>
                    </div>
                </div>
            </div>

            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Publish</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_published" id="chkPub" value="1" <?= ($a['is_published'] ?? 1)?'checked':'' ?>>
                        <label class="form-check-label small fw-semibold" for="chkPub">Published (visible to learners)</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" style="border-radius:9px">
                            <i class="fas fa-save me-2"></i><?= $isEdit ? 'Save Changes' : 'Create Assignment' ?>
                        </button>
                        <a href="<?= url('elms/assignments') ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Show/hide late penalty
document.getElementById('chkAllowLate')?.addEventListener('change', function () {
    document.getElementById('penaltyRow').style.display = this.checked ? '' : 'none';
});

// Show/hide file options
document.getElementById('subTypeSelect')?.addEventListener('change', function () {
    const show = ['file','any'].includes(this.value);
    document.getElementById('fileTypeRow').style.display = show ? '' : 'none';
    document.getElementById('fileSizeRow').style.display = show ? '' : 'none';
});

// Rubric builder
let rubricIdx = <?= count((array)($rubric ?? [])) ?>;
document.getElementById('btnAddCriterion')?.addEventListener('click', () => {
    document.getElementById('rubricEmpty').style.display = 'none';
    const row = document.createElement('div');
    row.className = 'rubric-row';
    row.innerHTML = `
        <input type="text" name="rubric[${rubricIdx}][criterion]" class="form-control form-control-sm" placeholder="Criterion description">
        <input type="number" name="rubric[${rubricIdx}][points]" class="form-control form-control-sm" placeholder="Pts" min="0">
        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-row" style="padding:3px 7px;border-radius:6px"><i class="fas fa-times"></i></button>
    `;
    document.getElementById('rubricRows').appendChild(row);
    rubricIdx++;
});
document.getElementById('rubricRows').addEventListener('click', e => {
    if (e.target.closest('.btn-remove-row')) {
        e.target.closest('.rubric-row').remove();
        if (!document.querySelectorAll('.rubric-row').length)
            document.getElementById('rubricEmpty').style.display = '';
    }
});

// Dynamic lesson select on course change
document.getElementById('courseSelect')?.addEventListener('change', function () {
    const courseId = this.value;
    const sel = document.getElementById('lessonSelect');
    sel.innerHTML = '<option value="">— Loading… —</option>';
    if (!courseId) { sel.innerHTML = '<option value="">— None —</option>'; return; }
    fetch(`<?= url('elms/courses') ?>/${courseId}/lessons-list`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        sel.innerHTML = '<option value="">— None —</option>';
        data.forEach(l => sel.innerHTML += `<option value="${l.id}">${l.section_title} › ${l.title}</option>`);
    })
    .catch(() => { sel.innerHTML = '<option value="">— None —</option>'; });
});
</script>
