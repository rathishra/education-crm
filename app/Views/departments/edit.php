<?php $pageTitle = 'Edit Department'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-sitemap me-2"></i>Edit Department</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('departments') ?>">Departments</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('departments') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">Edit: <?= e($dept['name']) ?></div>
            <div class="card-body">
                <form method="POST" action="<?= url('departments/' . $dept['id']) ?>">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Institution</label>
                            <input type="text" class="form-control" value="<?= e($dept['institution_name'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Code</label>
                            <input type="text" class="form-control" value="<?= e($dept['code']) ?>" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label required">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active" <?= $dept['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $dept['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label required">Department Name</label>
                            <input type="text" class="form-control" name="name"
                                   value="<?= e(old('name') ?: $dept['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">HOD Name</label>
                            <input type="text" class="form-control" name="hod_name"
                                   value="<?= e(old('hod_name') ?: ($dept['hod_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   value="<?= e(old('email') ?: ($dept['email'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone"
                                   value="<?= e(old('phone') ?: ($dept['phone'] ?? '')) ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?= e(old('description') ?: ($dept['description'] ?? '')) ?></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Department</button>
                        <a href="<?= url('departments') ?>" class="btn btn-light ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
