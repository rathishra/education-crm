<?php $pageTitle = 'Edit Section: ' . e($section['section_name']); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/sections') ?>">Sections</a></li>
                <li class="breadcrumb-item"><a href="<?= url('academic/sections/' . $section['id']) ?>"><?= e($section['section_name']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">Edit Section — <?= e($section['program_name']) ?> / <?= e($section['section_name']) ?></h4>
    </div>
    <a href="<?= url('academic/sections/' . $section['id']) ?>" class="btn btn-light border shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form id="frmEditSection">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Batch / Cohort</label>
                        <input type="text" class="form-control bg-light" value="<?= e($section['program_name']) ?> (<?= e($section['batch_term']) ?>)" readonly>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Section Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="section_name" value="<?= e($section['section_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Capacity</label>
                            <input type="number" class="form-control" name="capacity" value="<?= (int)($section['capacity'] ?? 30) ?>" min="1" max="200">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Default Classroom</label>
                        <select class="form-select" name="default_classroom_id">
                            <option value="">— Not Assigned —</option>
                            <?php foreach($classrooms as $cr): ?>
                            <option value="<?= $cr['id'] ?>" <?= ($section['default_classroom_id'] ?? '') == $cr['id'] ? 'selected' : '' ?>>
                                <?= e($cr['room_number']) ?><?= $cr['room_name'] ? ' — ' . e($cr['room_name']) : '' ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?= ($section['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($section['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4 pt-3 border-top">
                        <a href="<?= url('academic/sections/' . $section['id']) ?>" class="btn btn-light border px-4">Cancel</a>
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
document.getElementById('frmEditSection').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
    try {
        const res = await fetch('<?= url('academic/sections/update/' . $section['id']) ?>', {
            method: 'POST',
            body: new FormData(this)
        });
        const data = await res.json();
        if(data.status === 'success') {
            toastr.success(data.message);
            setTimeout(() => window.location.href = '<?= url('academic/sections/' . $section['id']) ?>', 1200);
        } else {
            toastr.error(data.message || 'Failed to update section');
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
