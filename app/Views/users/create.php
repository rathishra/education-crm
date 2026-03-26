<?php $pageTitle = 'Add User'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user-plus me-2"></i>Add User</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('users') ?>">Users</a></li>
                <li class="breadcrumb-item active">Add</li>
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
            <div class="card-header">User Details</div>
            <div class="card-body">
                <form method="POST" action="<?= url('users') ?>">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee ID</label>
                            <input type="text" class="form-control" name="employee_id"
                                   value="<?= e(old('employee_id')) ?>" placeholder="e.g. EMP002">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Email</label>
                            <input type="email" class="form-control" name="email"
                                   value="<?= e(old('email')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">First Name</label>
                            <input type="text" class="form-control" name="first_name"
                                   value="<?= e(old('first_name')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Last Name</label>
                            <input type="text" class="form-control" name="last_name"
                                   value="<?= e(old('last_name')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone"
                                   value="<?= e(old('phone')) ?>" placeholder="10-digit number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Password</label>
                            <input type="password" class="form-control" name="password"
                                   minlength="8" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <hr>

                        <div class="col-md-6">
                            <label class="form-label required">Role</label>
                            <select class="form-select" name="role_id" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= e($role['name']) ?> (Level <?= $role['level'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assign to Institution</label>
                            <select class="form-select" name="institution_id">
                                <option value="">All Institutions</option>
                                <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>"><?= e($inst['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Leave empty for org-level access</small>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Create User
                        </button>
                        <a href="<?= url('users') ?>" class="btn btn-light ms-2">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
