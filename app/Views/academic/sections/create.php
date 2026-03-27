<?php $pageTitle = 'Add New Section'; ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <form id="frmAddSection" method="POST" action="<?= url('academic/sections/store') ?>">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="text-dark font-weight-bold mb-0">Create Class Section</h4>
                <a href="<?= url('academic/sections') ?>" class="btn btn-light shadow-sm">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Parent Cohort / Batch *</label>
                        <select class="form-select" name="batch_id" required>
                            <option value="">-- Select Active Batch --</option>
                            <?php if(!empty($batches)): foreach($batches as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)</option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Section Identifier *</label>
                            <input type="text" class="form-control" name="section_name" placeholder="E.g. Section A, Alpha" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold">Max Capacity</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-users"></i></span>
                                <input type="number" class="form-control" name="capacity" value="30" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">Default Classroom Allocation (Optional)</label>
                        <select class="form-select" name="default_classroom_id">
                            <option value="">-- No Room Assigned --</option>
                            <?php if(!empty($classrooms)): foreach($classrooms as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['room_number']) ?> - <?= e($c['room_name']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-4" id="btnSaveSection">
                            <i class="fas fa-plus me-1"></i> Create Section
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$('#frmAddSection').submit(function(e) {
    e.preventDefault();
    let btn = $('#btnSaveSection');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Processing...');
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if(data.status === 'success') {
                toastr.success(data.message);
                setTimeout(() => window.location.href = APP_URL + '/academic/sections', 1000);
            } else {
                toastr.error(data.message || 'Validation failed');
                btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Create Section');
            }
        },
        error: function(xhr) {
            toastr.error('An error occurred. Check inputs.');
            btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i> Create Section');
        }
    });
});
</script>
