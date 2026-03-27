<?php $pageTitle = 'Edit Batch: ' . e($batch['program_name']); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/batches') ?>">Batches</a></li>
                <li class="breadcrumb-item"><a href="<?= url('academic/batches/' . $batch['id']) ?>"><?= e($batch['program_name']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">Edit Cohort / Batch</h4>
    </div>
    <a href="<?= url('academic/batches/' . $batch['id']) ?>" class="btn btn-light border shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form id="frmEditBatch">

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Program Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="program_name" value="<?= e($batch['program_name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Term / Session <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="batch_term" value="<?= e($batch['batch_term']) ?>" placeholder="e.g. 2024-2025" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="start_date" value="<?= e($batch['start_date']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" class="form-control" name="end_date" value="<?= e($batch['end_date'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Max Intake</label>
                            <input type="number" class="form-control" name="max_intake" value="<?= (int)$batch['max_intake'] ?>" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Total Semesters</label>
                            <input type="number" class="form-control" name="total_semesters" value="<?= (int)$batch['total_semesters'] ?>" min="1" max="12">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status">
                                <option value="active" <?= $batch['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $batch['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                <option value="graduated" <?= $batch['status'] === 'graduated' ? 'selected' : '' ?>>Graduated</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
                        <a href="<?= url('academic/batches/' . $batch['id']) ?>" class="btn btn-light border px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5" id="btnSave">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('frmEditBatch').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    try {
        const res = await fetch('<?= url('academic/batches/update/' . $batch['id']) ?>', {
            method: 'POST',
            body: new FormData(this)
        });
        const data = await res.json();
        if(data.status === 'success') {
            toastr.success(data.message);
            setTimeout(() => window.location.href = '<?= url('academic/batches/' . $batch['id']) ?>', 1200);
        } else {
            toastr.error(data.message || 'Failed to update batch');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
        }
    } catch(err) {
        toastr.error('Server error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
    }
});
</script>
