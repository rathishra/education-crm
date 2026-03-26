<?php $pageTitle = 'Add Institution'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-university me-2"></i>Add Institution</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('institutions') ?>">Institutions</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('institutions') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row justify-content-center">
    <div class="col-xl-9">
        <form method="POST" action="<?= url('institutions') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>

            <div class="card">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Organization</label>
                            <select class="form-select" name="organization_id" required>
                                <option value="">Select Organization</option>
                                <?php foreach ($organizations as $orgId => $orgName): ?>
                                <option value="<?= $orgId ?>" <?= old('organization_id') == $orgId ? 'selected' : '' ?>>
                                    <?= e($orgName) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Institution Type</label>
                            <select class="form-select" name="type" required>
                                <option value="">Select Type</option>
                                <?php foreach ($types as $val => $label): ?>
                                <option value="<?= $val ?>" <?= old('type') === $val ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label required">Institution Name</label>
                            <input type="text" class="form-control" name="name" value="<?= e(old('name')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label required">Code</label>
                            <input type="text" class="form-control" name="code" value="<?= e(old('code')) ?>"
                                   placeholder="e.g. ENGCLG01" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?= e(old('email')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" value="<?= e(old('phone')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" value="<?= e(old('website')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Principal Name</label>
                            <input type="text" class="form-control" name="principal_name" value="<?= e(old('principal_name')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Established Year</label>
                            <input type="number" class="form-control" name="established_year"
                                   value="<?= e(old('established_year')) ?>" min="1900" max="<?= date('Y') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Logo</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Affiliation</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Affiliation Number</label>
                            <input type="text" class="form-control" name="affiliation_number" value="<?= e(old('affiliation_number')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Affiliation Body</label>
                            <input type="text" class="form-control" name="affiliation_body"
                                   value="<?= e(old('affiliation_body')) ?>" placeholder="e.g. Anna University">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Address</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1" value="<?= e(old('address_line1')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2" value="<?= e(old('address_line2')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city" value="<?= e(old('city')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state" value="<?= e(old('state')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="pincode" value="<?= e(old('pincode')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="country" value="<?= e(old('country') ?: 'India') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="<?= url('institutions') ?>" class="btn btn-light me-2">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Institution</button>
            </div>
        </form>
    </div>
</div>
