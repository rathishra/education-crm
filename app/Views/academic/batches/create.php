<?php $pageTitle = 'Form New Cohort'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <form id="frmAddBatch" method="POST" action="<?= url('academic/batches/store') ?>">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-dark font-weight-bold mb-0">Setup Academic Cohort</h4>
                <a href="<?= url('academic/batches') ?>" class="btn btn-light shadow-sm">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h6 class="font-weight-bold text-primary mb-4 border-bottom pb-2">Cohort Blueprint</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label class="form-label text-muted small fw-bold">Degree / Program Name *</label>
                            <input type="text" class="form-control" name="program_name" placeholder="E.g. B.Tech Computer Science" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label text-muted small fw-bold">Batch Term / Identifier *</label>
                            <input type="text" class="form-control" name="batch_term" placeholder="E.g. 2024-2028 or Fall 2024" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Commencement Date *</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Expected Graduation</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-success mb-4 border-bottom pb-2">Academic & Enrollment Targets</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Max Intake Capacity</label>
                            <input type="number" class="form-control" name="max_intake" value="60" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Total Semesters / Terms</label>
                            <input type="number" class="form-control" name="total_semesters" value="8" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold">Graduation Credits Min.</label>
                            <input type="number" step="0.5" class="form-control" name="graduation_credits" value="120.0">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-5">
                        <button type="submit" class="btn btn-primary px-4" id="btnSaveBatch">
                            <i class="fas fa-save me-1"></i> Initialize Cohort
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$('#frmAddBatch').submit(function(e) {
    e.preventDefault();
    let btn = $('#btnSaveBatch');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if(data.status === 'success') {
                toastr.success(data.message);
                setTimeout(() => window.location.href = APP_URL + '/academic/batches/' + data.id, 1000);
            } else {
                toastr.error(data.message || 'Validation failed');
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Initialize Cohort');
            }
        },
        error: function(xhr) {
            toastr.error('An error occurred. Check inputs.');
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Initialize Cohort');
        }
    });
});
</script>
