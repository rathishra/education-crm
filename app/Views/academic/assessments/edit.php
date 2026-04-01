<?php $pageTitle = 'Edit Assessment'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="fas fa-edit me-2 text-warning"></i>Edit Assessment</h4>
        <p class="text-muted small mb-0">Editing: <strong><?= e($assessment['assessment_name']) ?></strong></p>
    </div>
    <a href="<?= url('academic/assessments') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if ($assessment['status'] === 'published'): ?>
<div class="alert alert-warning">
    <i class="fas fa-lock me-2"></i>This assessment is <strong>published & locked</strong>. Unlock it first to edit.
</div>
<?php endif; ?>

<form id="frmEditAssessment" method="POST" action="<?= url('academic/assessments/'.$assessment['id'].'/update') ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Assessment Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Assessment Name <span class="text-danger">*</span></label>
                            <input type="text" name="assessment_name" class="form-control"
                                   value="<?= e($assessment['assessment_name']) ?>" required
                                   <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                            <select name="batch_id" class="form-select select2" required <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                                <option value="">— Select Batch —</option>
                                <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $assessment['batch_id'] == $b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select select2" required <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                                <option value="">— Select Subject —</option>
                                <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $assessment['subject_id'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= e($s['subject_code'] ?? '') ?> — <?= e($s['subject_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="assessment_type" class="form-select" <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                                <?php foreach (['internal' => 'Internal', 'midterm' => 'Midterm', 'external' => 'External', 'quiz' => 'Quiz', 'assignment' => 'Assignment', 'practical' => 'Practical', 'viva' => 'Viva'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= $assessment['assessment_type'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Max Marks</label>
                            <input type="number" name="max_marks" class="form-control" value="<?= $assessment['max_marks'] ?>" step="0.5" min="0"
                                   <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Passing Marks</label>
                            <input type="number" name="passing_marks" class="form-control" value="<?= $assessment['passing_marks'] ?>" step="0.5" min="0"
                                   <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Weightage (%)</label>
                            <input type="number" name="weightage" class="form-control" value="<?= $assessment['weightage'] ?>" step="0.1" min="0" max="100"
                                   <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" name="assessment_date" class="form-control" value="<?= $assessment['assessment_date'] ?? '' ?>"
                                   <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Grading Schema</label>
                            <select name="grading_schema_id" class="form-select select2" <?= $assessment['status'] === 'published' ? 'disabled' : '' ?>>
                                <option value="">— None —</option>
                                <?php foreach ($schemas as $gs): ?>
                                <option value="<?= $gs['id'] ?>" <?= $assessment['grading_schema_id'] == $gs['id'] ? 'selected' : '' ?>>
                                    <?= e($gs['code']) ?> — <?= e($gs['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Current Status</div>
                    <span class="badge fs-6 bg-<?= $assessment['status'] === 'published' ? 'info' : ($assessment['status'] === 'completed' ? 'success' : 'warning text-dark') ?>">
                        <?= ucfirst($assessment['status']) ?>
                    </span>
                </div>
            </div>
            <?php if ($assessment['status'] !== 'published'): ?>
            <button type="submit" class="btn btn-warning w-100 fw-semibold">
                <i class="fas fa-save me-2"></i>Update Assessment
            </button>
            <?php endif; ?>
            <a href="<?= url('academic/assessments') ?>" class="btn btn-light w-100 mt-2">Cancel</a>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }
});
</script>
