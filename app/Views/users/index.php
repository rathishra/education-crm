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

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-2 fw-bold text-primary"><?= $stats['total'] ?? ($users['total'] ?? 0) ?></div>
                <small class="text-muted">Total Users</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-2 fw-bold text-success"><?= $stats['active'] ?? 0 ?></div>
                <small class="text-muted">Active</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-danger bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-2 fw-bold text-danger"><?= $stats['inactive'] ?? 0 ?></div>
                <small class="text-muted">Inactive</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-2 fw-bold text-warning"><?= $stats['with_overrides'] ?? 0 ?></div>
                <small class="text-muted">Custom Permissions</small>
            </div>
        </div>
    </div>
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
                    <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
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
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Institution</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users['data'])): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                            No users found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($users['data'] as $u):
                        $initial = strtoupper(substr($u['first_name'], 0, 1) . substr($u['last_name'] ?? '', 0, 1));
                        $roleColor = $u['role_color'] ?? 'secondary';
                        $roleIcon  = $u['role_icon']  ?? 'user';
                        $hasOverrides = !empty($u['has_overrides']);
                        $lastLogin = $u['last_login_at'] ?? null;
                        $isOnline  = $lastLogin && (time() - strtotime($lastLogin) < 900); // 15 min
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                         style="width:40px;height:40px;font-size:0.85rem;background:var(--bs-<?= htmlspecialchars($roleColor) ?>)">
                                        <?= $initial ?>
                                    </div>
                                    <?php if ($isOnline): ?>
                                    <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white"
                                          style="width:10px;height:10px;" title="Online"></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="<?= url('users/' . $u['id']) ?>" class="fw-semibold text-dark d-block">
                                        <?= e($u['first_name'] . ' ' . ($u['last_name'] ?? '')) ?>
                                    </a>
                                    <small class="text-muted"><?= e($u['email']) ?></small>
                                    <?php if (!empty($u['employee_id'])): ?>
                                    <small class="text-muted ms-1">· <?= e($u['employee_id']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($u['role_name'] ?? null): ?>
                            <span class="badge bg-<?= htmlspecialchars($roleColor) ?> bg-opacity-15 text-<?= htmlspecialchars($roleColor) ?> border border-<?= htmlspecialchars($roleColor) ?> border-opacity-25">
                                <i class="fas fa-<?= htmlspecialchars($roleIcon) ?> me-1"></i><?= e($u['role_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                            <?php if ($hasOverrides): ?>
                            <span class="badge bg-warning text-dark ms-1" title="Has custom permission overrides">
                                <i class="fas fa-key"></i>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?= e($u['institution_name'] ?? 'All') ?></small></td>
                        <td>
                            <span class="badge bg-<?= $u['is_active'] ? 'success' : 'danger' ?>">
                                <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?php if ($lastLogin): ?>
                                <span title="<?= formatDateTime($lastLogin) ?>"><?= timeAgo($lastLogin) ?></span>
                                <?php else: ?>
                                <span class="text-muted fst-italic">Never</span>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('users/' . $u['id']) ?>" class="btn btn-outline-secondary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (hasPermission('users.edit')): ?>
                                <a href="<?= url('users/' . $u['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('users.manage_permissions')): ?>
                                <a href="<?= url('users/' . $u['id'] . '/permissions') ?>" class="btn btn-outline-warning" title="Permission Overrides">
                                    <i class="fas fa-key"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('users.edit') && $u['id'] != ($currentUser['id'] ?? 0)): ?>
                                <form method="POST" action="<?= url('users/' . $u['id'] . '/toggle') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-<?= $u['is_active'] ? 'danger' : 'success' ?>"
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

    <?php if (($users['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $users; $baseUrl = url('users') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
