<?php $pageTitle = 'My Profile'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user me-2"></i>My Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Profile</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-4">
    <!-- Profile Info -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= url($user['avatar']) ?>" class="rounded-circle mb-3" width="100" height="100" alt="Avatar">
                <?php else: ?>
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px;font-size:2.5rem">
                        <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'] ?? '', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <h4 class="mb-1"><?= e($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></h4>
                <p class="text-muted mb-2"><?= e($user['role_name'] ?? 'User') ?></p>
                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </div>
            <hr class="my-0">
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted d-block">Employee ID</small>
                    <span><?= e($user['employee_id'] ?? '-') ?></span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Email</small>
                    <span><?= e($user['email']) ?></span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Phone</small>
                    <span><?= e($user['phone'] ?? '-') ?></span>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Last Login</small>
                    <span><?= $user['last_login_at'] ? formatDateTime($user['last_login_at']) : '-' ?></span>
                </div>
                <div>
                    <small class="text-muted d-block">Last Login IP</small>
                    <span><?= e($user['last_login_ip'] ?? '-') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile & Change Password -->
    <div class="col-xl-8">
        <!-- Edit Profile -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i>Edit Profile
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('profile') ?>" enctype="multipart/form-data">
                    <?= csrfField() ?>

                    <div class="row g-3">
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
                            <label class="form-label">Avatar</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-lock me-2"></i>Change Password
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('change-password') ?>">
                    <?= csrfField() ?>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label required">Current Password</label>
                            <input type="password" class="form-control" name="current_password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">New Password</label>
                            <input type="password" class="form-control" name="new_password"
                                   minlength="8" required>
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Confirm New Password</label>
                            <input type="password" class="form-control" name="new_password_confirmation" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
