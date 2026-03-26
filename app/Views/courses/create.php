<?php $pageTitle = 'Add Course'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-plus-circle me-2"></i>Add Course</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('courses') ?>">Courses</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('courses') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('courses') ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>Course Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label required">Course Name</label>
                            <input type="text" class="form-control" name="name" value="<?= e(old('name')) ?>" required placeholder="e.g. Bachelor of Computer Applications">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Course Code</label>
                            <input type="text" class="form-control text-uppercase" name="code" value="<?= e(old('code')) ?>" required placeholder="e.g. BCA">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Short Name</label>
                            <input type="text" class="form-control" name="short_name" value="<?= e(old('short_name')) ?>" placeholder="e.g. BCA">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id">
                                <option value="">— Select Department —</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= e($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course Type</label>
                            <select class="form-select" name="degree_type">
                                <?php foreach (['ug' => 'UG', 'pg' => 'PG', 'diploma' => 'Diploma', 'certificate' => 'Certificate', 'other' => 'Other'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= old('degree_type') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Duration (Years)</label>
                            <input type="number" class="form-control" name="duration_years" value="<?= e(old('duration_years', 1)) ?>" min="1" max="10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Seats</label>
                            <input type="number" class="form-control" name="seats" value="<?= e(old('seats')) ?>" min="1" placeholder="e.g. 60">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fees Per Year (₹)</label>
                            <input type="number" class="form-control" name="fees_per_year" value="<?= e(old('fees_per_year')) ?>" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Optional description..."><?= e(old('description')) ?></textarea>
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
                            <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Save Course</button>
                </div>
            </div>
        </div>
    </div>
</form>
