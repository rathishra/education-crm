<?php $pageTitle = 'User Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-users-cog me-2"></i>User Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('users.create')): ?>
    <a href="<?= url('users/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add User
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('users') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search name, email, ID..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="role_id">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>" <?= ($filters['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                        <?= e($role['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="1" <?= ($filters['is_active'] ?? '') === 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary me-1">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="<?= url('users') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Institution</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users['data'])): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No users found</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users['data'] as $u): ?>
                    <tr>
                        <td><span class="text-muted"><?= e($u['employee_id'] ?? '-') ?></span></td>
                        <td>
                            <a href="<?= url('users/' . $u['id']) ?>" class="fw-semibold">
                                <?= e($u['first_name'] . ' ' . ($u['last_name'] ?? '')) ?>
                            </a>
                        </td>
                        <td><?= e($u['email']) ?></td>
                        <td>
                            <span class="badge bg-secondary"><?= e($u['role_name'] ?? '-') ?></span>
                        </td>
                        <td><small><?= e($u['institution_name'] ?? 'All') ?></small></td>
                        <td>
                            <span class="badge bg-<?= $u['is_active'] ? 'success' : 'danger' ?>">
                                <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= $u['last_login_at'] ? timeAgo($u['last_login_at']) : 'Never' ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('users/' . $u['id']) ?>" class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (hasPermission('users.edit')): ?>
                                <a href="<?= url('users/' . $u['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('users.edit') && $u['id'] != ($currentUser['id'] ?? 0)): ?>
                                <form method="POST" action="<?= url('users/' . $u['id'] . '/toggle') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-<?= $u['is_active'] ? 'warning' : 'success' ?>"
                                            title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>"
                                            onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($users['last_page'] > 1): ?>
    <div class="card-footer">
        <?php $pagination = $users; $baseUrl = url('users') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
