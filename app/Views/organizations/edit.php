<?php $pageTitle = 'Edit Organization: ' . e($organization['organization_name']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Organization</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('organizations') ?>">Organizations</a></li>
                <li class="breadcrumb-item active"><?= e($organization['organization_name']) ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('organizations') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('organizations/' . $organization['id'] . '/edit') ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Organization Name</label>
                    <input type="text" class="form-control" name="organization_name" value="<?= e(old('organization_name', $organization['organization_name'])) ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required">Organization Code</label>
                    <input type="text" class="form-control" name="organization_code" value="<?= e(old('organization_code', $organization['organization_code'])) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?= e(old('email', $organization['email'])) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" name="phone" value="<?= e(old('phone', $organization['phone'])) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Max Institutions Limit</label>
                    <input type="number" class="form-control" name="max_institutions" value="<?= e(old('max_institutions', $organization['max_institutions'])) ?>" min="1" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active" <?= (old('status', $organization['status']) === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (old('status', $organization['status']) === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="<?= url('organizations') ?>" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Organization</button>
                </div>
            </div>
        </form>
    </div>
</div>
