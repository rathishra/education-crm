<?php $pageTitle = 'Edit Allocation'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="fas fa-edit me-2 text-warning"></i>Edit Faculty Allocation</h4>
        <p class="text-muted small mb-0">Modify the assignment details below.</p>
    </div>
    <a href="<?= url('academic/faculty-allocation') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
<div class="col-lg-8">
<form method="POST" action="<?= url('academic/faculty-allocation/'.$allocation['id'].'/update') ?>">
    <?= csrfField() ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Allocation Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Faculty <span class="text-danger">*</span></label>
                    <select name="faculty_id" class="form-select select2" required>
                        <option value="">— Select Faculty —</option>
                        <?php foreach ($faculty as $f): ?>
                        <option value="<?= $f['id'] ?>" <?= $allocation['faculty_id'] == $f['id'] ? 'selected' : '' ?>>
                            <?= e($f['name']) ?> — <?= e($f['email']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select select2" required>
                        <option value="">— Select Subject —</option>
                        <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $allocation['subject_id'] == $s['id'] ? 'selected' : '' ?>>
                            <?= e($s['subject_code']) ?> — <?= e($s['subject_name']) ?> (<?= $s['subject_type'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Batch</label>
                    <select name="batch_id" class="form-select select2" id="batchSelect">
                        <option value="">— All Batches —</option>
                        <?php foreach ($batches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= $allocation['batch_id'] == $b['id'] ? 'selected' : '' ?>>
                            <?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>) — <?= $b['total_semesters'] ?> Sem
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Section</label>
                    <select name="section_id" class="form-select select2" id="sectionSelect">
                        <option value="">— All Sections —</option>
                        <?php foreach ($sections as $sec): ?>
                        <option value="<?= $sec['id'] ?>" data-batch="<?= $sec['batch_id'] ?>"
                                <?= $allocation['section_id'] == $sec['id'] ? 'selected' : '' ?>>
                            <?= e($sec['section_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Allocation Type</label>
                    <select name="allocation_type" class="form-select">
                        <?php foreach (['theory' => 'Theory', 'lab' => 'Lab', 'both' => 'Theory + Lab'] as $v => $l): ?>
                        <option value="<?= $v ?>" <?= $allocation['allocation_type'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Hours / Week</label>
                    <input type="number" name="hours_per_week" class="form-control"
                           value="<?= $allocation['hours_per_week'] ?>" min="0" max="40">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Lab Batch # <span class="text-muted small">(lab only)</span></label>
                    <input type="number" name="lab_batch_number" class="form-control"
                           value="<?= $allocation['lab_batch_number'] ?>" placeholder="1, 2, 3…" min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Semester</label>
                    <select name="semester" class="form-select">
                        <option value="">— All —</option>
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?= $i ?>" <?= $allocation['semester'] == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-warning px-4 fw-semibold"><i class="fas fa-save me-2"></i>Update Allocation</button>
        <a href="<?= url('academic/faculty-allocation') ?>" class="btn btn-light">Cancel</a>
    </div>
</form>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }
    const batchSel   = document.getElementById('batchSelect');
    const sectionSel = document.getElementById('sectionSelect');
    const allOpts    = Array.from(sectionSel.querySelectorAll('option'));

    function filterSections() {
        const bid = batchSel.value;
        allOpts.forEach(o => {
            if (!o.value) { o.hidden = false; return; }
            o.hidden = bid && o.dataset.batch != bid;
        });
        if (typeof $(sectionSel).select2 === 'function') $(sectionSel).trigger('change');
    }
    batchSel.addEventListener('change', filterSections);
    if (typeof $(batchSel).on === 'function') $(batchSel).on('change', filterSections);
});
</script>
