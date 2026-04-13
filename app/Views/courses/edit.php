<?php $pageTitle = 'Edit Course'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Course</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('courses') ?>">Courses</a></li>
                <li class="breadcrumb-item active"><?= e($course['name']) ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('courses') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('courses/' . $course['id']) ?>">
    <?= csrfField() ?>
    <input type="hidden" name="_method" value="PUT">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>Course Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required">Course Name</label>
                            <input type="text" class="form-control" name="name" value="<?= e(old('name', $course['name'])) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Course Code</label>
                            <input type="text" class="form-control text-uppercase" name="code" value="<?= e(old('code', $course['code'])) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Short Name</label>
                            <input type="text" class="form-control" name="short_name" value="<?= e(old('short_name', $course['short_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id">
                                <option value="">— Select Department —</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= old('department_id', $course['department_id']) == $dept['id'] ? 'selected' : '' ?>>
                                    <?= e($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course Type</label>
                            <select class="form-select" name="course_type_id" id="courseTypeSelect">
                                <option value="">— Select Course Type —</option>
                                <?php foreach ($courseTypes as $ct): ?>
                                <option value="<?= $ct['id'] ?>"
                                    data-duration="<?= (int)($ct['duration'] ?? 0) ?>"
                                    data-semesters="<?= (int)($ct['no_of_semester'] ?? 0) ?>"
                                    <?= old('course_type_id', $course['course_type_id'] ?? '') == $ct['id'] ? 'selected' : '' ?>>
                                    <?= e($ct['code']) ?> — <?= e($ct['description']) ?>
                                </option>
                                <?php endforeach; ?>
                                <?php if(empty($courseTypes)): ?>
                                <option disabled>No course types found — add them in Academic Setup</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration (Years)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="duration_years" id="durationYears"
                                       value="<?= e(old('duration_years', $course['duration_years'])) ?>"
                                       min="1" max="10" placeholder="Auto from Course Type">
                                <span class="input-group-text text-muted" style="font-size:12px" title="Auto-filled from Course Type">
                                    <i class="fas fa-link"></i>
                                </span>
                            </div>
                            <small class="text-muted">Auto-filled when Course Type is selected</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Seats</label>
                            <input type="number" class="form-control" name="seats" value="<?= e(old('seats', $course['seats'] ?? '')) ?>" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fees Per Year (₹)</label>
                            <input type="number" class="form-control" name="fees_per_year" value="<?= e(old('fees_per_year', $course['fees_per_year'] ?? '')) ?>" min="0" step="0.01">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?= e(old('description', $course['description'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-toggle-on me-2"></i>Status</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?= old('status', $course['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status', $course['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="text-muted small">
                        <div>Created: <?= formatDate($course['created_at'] ?? '') ?></div>
                        <?php if (!empty($course['updated_at'])): ?>
                        <div>Updated: <?= timeAgo($course['updated_at']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Course</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    const ctSelect = document.getElementById('courseTypeSelect');
    const durInput = document.getElementById('durationYears');

    function applyFromType(isOnLoad) {
        const opt = ctSelect.options[ctSelect.selectedIndex];
        const dur = parseInt(opt.dataset.duration || '0', 10);
        if (dur > 0) {
            durInput.value    = dur;
            durInput.readOnly = true;
            durInput.classList.add('bg-light');
        } else {
            durInput.readOnly = false;
            durInput.classList.remove('bg-light');
            // On change (not page load) clear the field so user can enter manually
            if (!isOnLoad) durInput.value = '';
        }
    }

    ctSelect.addEventListener('change', () => applyFromType(false));

    // On page load: lock field if a course type is already selected
    if (ctSelect.value) applyFromType(true);
})();
</script>
