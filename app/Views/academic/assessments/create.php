<?php $pageTitle = 'Setup Assessment'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form id="frmAddAssessment" method="POST" action="<?= url('academic/assessments/store') ?>">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-dark font-weight-bold mb-0">Configure Extracurricular/Exam Setup</h4>
                <a href="<?= url('academic/assessments') ?>" class="btn btn-light shadow-sm">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body p-4">
                    
                    <div class="row mb-4 bg-white p-3 rounded border">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label text-muted small fw-bold">Target Cohort / Batch *</label>
                            <select class="form-select" name="batch_id" required>
                                <option value="">-- Select Active Batch --</option>
                                <?php if(!empty($batches)): foreach($batches as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)</option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Academic Subject *</label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">-- Choose Subject --</option>
                                <?php if(!empty($subjects)): foreach($subjects as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= e($s['subject_code']) ?> - <?= e($s['subject_name']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-primary mb-3 mt-4 border-bottom pb-2">Assessment Details</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Assessment Name *</label>
                            <input type="text" class="form-control" name="assessment_name" placeholder="E.g. Midterm Physics Exam" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Assessment Type</label>
                            <select class="form-select" name="assessment_type">
                                <option value="internal">Internal Theory Exam</option>
                                <option value="assignment">Assignment / Report</option>
                                <option value="quiz">Quiz / MCQ</option>
                                <option value="lab">Lab Practical</option>
                                <option value="project">Project / Portfolio</option>
                                <option value="final">Final Semester Exam</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Max Marks</label>
                            <input type="number" step="0.5" class="form-control" name="max_marks" value="100.0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Passing Marks</label>
                            <input type="number" step="0.5" class="form-control" name="passing_marks" value="40.0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Final Weightage %</label>
                            <div class="input-group">
                                <input type="number" step="0.5" class="form-control" name="weightage" value="20.0" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 w-50">
                        <label class="form-label text-muted small fw-bold">Scheduled Date (Optional)</label>
                        <input type="date" class="form-control" name="assessment_date">
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" id="btnSaveAss">
                            <i class="fas fa-save me-1"></i> Register Assessment
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$('#frmAddAssessment').submit(function(e) {
    e.preventDefault();
    let btn = $('#btnSaveAss');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if(data.status === 'success') {
                toastr.success(data.message);
                setTimeout(() => window.location.href = APP_URL + '/academic/assessments', 1000);
            } else {
                toastr.error(data.message || 'Validation failed');
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Register Assessment');
            }
        },
        error: function(xhr) {
            toastr.error('An error occurred. Check inputs.');
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Register Assessment');
        }
    });
});
</script>
