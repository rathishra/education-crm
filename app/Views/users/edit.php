<?php $pageTitle = 'Edit User'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user-edit me-2"></i>Edit User</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('users') ?>">Users</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('users') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">Edit User: <?= e($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></div>
            <div class="card-body">
                <form method="POST" action="<?= url('users/' . $user['id']) ?>" enctype="multipart/form-data">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" name="employee_id"
                                   value="<?= e(old('employee_id') ?: ($user['employee_id'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">First Name</label>
                            <input type="text" class="form-control" name="first_name"
                                   value="<?= e(old('first_name') ?: $user['first_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Last Name</label>
                            <input type="text" class="form-control" name="last_name"
                                   value="<?= e(old('last_name') ?: ($user['last_name'] ?? '')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone"
                                   value="<?= e(old('phone') ?: ($user['phone'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" minlength="8">
                            <small class="text-muted">Leave empty to keep current password</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Avatar</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>

                        <hr>

                        <div class="col-md-6">
                            <label class="form-label required">Role</label>
                            <select class="form-select" name="role_id" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"
                                    <?= ($user['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                                    <?= e($role['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Institution</label>
                            <select class="form-select" name="institution_id">
                                <option value="">All Institutions</option>
                                <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>"
                                    <?= ($user['institution_id'] ?? '') == $inst['id'] ? 'selected' : '' ?>>
                                    <?= e($inst['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update User
                        </button>
                        <a href="<?= url('users') ?>" class="btn btn-light ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
