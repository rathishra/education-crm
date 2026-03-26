<?php $pageTitle = 'Create Organization'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-plus me-2"></i>Create Organization</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('organizations') ?>">Organizations</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('organizations') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="<?= url('organizations') ?>">
            <?= csrfField() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">Organization Name</label>
                    <input type="text" class="form-control" name="organization_name" value="<?= e(old('organization_name')) ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label required">Organization Code</label>
                    <input type="text" class="form-control" name="organization_code" value="<?= e(old('organization_code')) ?>" required placeholder="e.g. ORG-001">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?= e(old('email')) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" name="phone" value="<?= e(old('phone')) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Max Institutions Limit</label>
                    <input type="number" class="form-control" name="max_institutions" value="<?= e(old('max_institutions', '1')) ?>" min="1" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-12 text-end mt-4">
                    <a href="<?= url('organizations') ?>" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Organization</button>
                </div>
            </div>
        </form>
    </div>
</div>
