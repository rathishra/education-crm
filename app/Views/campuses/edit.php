<?php $pageTitle = 'Edit Campus: ' . e($campus['name']); ?>
<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Campus</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= url('campuses') ?>">Campuses</a></li>
            <li class="breadcrumb-item active"><?= e($campus['name']) ?></li>
        </ol></nav>
    </div>
    <a href="<?= url('campuses') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('campuses/' . $campus['id']) ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Campus Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Institution</label>
                            <input type="text" class="form-control" value="<?= e($campus['institution_name'] ?? '') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Campus Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="<?= e(old('name', $campus['name'])) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Campus Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control text-uppercase" name="code" value="<?= e(old('code', $campus['code'])) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= e(old('phone', $campus['phone'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= e(old('email', $campus['email'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Principal Name</label>
                            <input type="text" class="form-control" name="principal_name" value="<?= e(old('principal_name', $campus['principal_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" value="<?= e(old('capacity', $campus['capacity'] ?? 0)) ?>" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="fas fa-map-marker-alt me-2"></i>Address</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1" value="<?= e(old('address_line1', $campus['address_line1'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="<?= e(old('city', $campus['city'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state" value="<?= e(old('state', $campus['state'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="pincode" value="<?= e(old('pincode', $campus['pincode'] ?? '')) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?= old('status', $campus['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= old('status', $campus['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="text-muted small">
                        <div>Created: <?= !empty($campus['created_at']) ? date('d M Y', strtotime($campus['created_at'])) : '-' ?></div>
                        <div>Updated: <?= !empty($campus['updated_at']) ? date('d M Y', strtotime($campus['updated_at'])) : '-' ?></div>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Update Campus</button>
                <a href="<?= url('campuses') ?>" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>
