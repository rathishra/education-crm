<?php $pageTitle = 'User Details'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user me-2"></i>User Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('users') ?>">Users</a></li>
                <li class="breadcrumb-item active"><?= e($user['first_name']) ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (hasPermission('users.edit')): ?>
        <a href="<?= url('users/' . $user['id'] . '/edit') ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= url('users') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- User Profile Card -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= url($user['avatar']) ?>" class="rounded-circle mb-3" width="100" height="100" alt="">
                <?php else: ?>
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:100px;height:100px;font-size:2.5rem">
                        <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'] ?? '', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <h4 class="mb-1"><?= e($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></h4>
                <p class="text-muted mb-2"><?= e($user['role_name'] ?? '-') ?></p>
                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?> mb-3">
                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Employee ID</span>
                    <span><?= e($user['employee_id'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Email</span>
                    <span><?= e($user['email']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Phone</span>
                    <span><?= e($user['phone'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Last Login</span>
                    <span><?= $user['last_login_at'] ? formatDateTime($user['last_login_at']) : 'Never' ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Created</span>
                    <span><?= formatDate($user['created_at']) ?></span>
                </div>
            </div>
        </div>

        <!-- Institutions Access -->
        <div class="card">
            <div class="card-header"><i class="fas fa-university me-2"></i>Institution Access</div>
            <div class="list-group list-group-flush">
                <?php if (empty($userInstitutions)): ?>
                <div class="list-group-item text-muted">All institutions (Super Admin)</div>
                <?php else: ?>
                <?php foreach ($userInstitutions as $inst): ?>
                <div class="list-group-item">
                    <div class="fw-semibold"><?= e($inst['name']) ?></div>
                    <small class="text-muted"><?= e($inst['organization_name'] ?? '') ?> &middot; <?= e(ucfirst(str_replace('_', ' ', $inst['type']))) ?></small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Activity Log -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-history me-2"></i>Recent Activity</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Action</th>
                                <th>Model</th>
                                <th>IP Address</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($auditLogs)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No activity logs</td></tr>
                            <?php else: ?>
                            <?php foreach ($auditLogs as $log): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?= match($log['action']) {
                                        'login' => 'success',
                                        'logout' => 'info',
                                        'create' => 'primary',
                                        'update' => 'warning',
                                        'delete' => 'danger',
                                        default => 'secondary'
                                    } ?>">
                                        <?= e(ucfirst($log['action'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= e($log['model_type'] ?? '-') ?>
                                    <?php if ($log['model_id']): ?>
                                    <small class="text-muted">#<?= $log['model_id'] ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= e($log['ip_address'] ?? '-') ?></small></td>
                                <td><small><?= formatDateTime($log['created_at']) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
