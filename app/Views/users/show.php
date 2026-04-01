<?php $pageTitle = 'User Details'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user me-2"></i><?= e($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('users') ?>">Users</a></li>
                <li class="breadcrumb-item active"><?= e($user['first_name']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('users.manage_permissions')): ?>
        <a href="<?= url('users/' . $user['id'] . '/permissions') ?>" class="btn btn-outline-warning">
            <i class="fas fa-key me-1"></i>Permissions
        </a>
        <?php endif; ?>
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

<?php
$roleColor = $user['role_color'] ?? 'secondary';
$roleIcon  = $user['role_icon']  ?? 'user';
$initial   = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'] ?? '', 0, 1));
?>

<div class="row g-4">
    <!-- Left: Profile Card -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <div class="position-relative d-inline-block mb-3">
                    <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= url($user['avatar']) ?>" class="rounded-circle" width="90" height="90" alt="" style="object-fit:cover">
                    <?php else: ?>
                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold"
                         style="width:90px;height:90px;font-size:1.8rem;background:var(--bs-<?= htmlspecialchars($roleColor) ?>)">
                        <?= $initial ?>
                    </div>
                    <?php endif; ?>
                </div>
                <h5 class="mb-1 fw-bold"><?= e($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></h5>
                <?php if ($user['role_name'] ?? null): ?>
                <div class="mb-2">
                    <span class="badge bg-<?= htmlspecialchars($roleColor) ?> bg-opacity-15 text-<?= htmlspecialchars($roleColor) ?> border border-<?= htmlspecialchars($roleColor) ?> border-opacity-25 px-3 py-1">
                        <i class="fas fa-<?= htmlspecialchars($roleIcon) ?> me-1"></i><?= e($user['role_name']) ?>
                    </span>
                </div>
                <?php endif; ?>
                <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
                <?php if (!empty($userOverrideCount)): ?>
                <div class="mt-2">
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-key me-1"></i><?= $userOverrideCount ?> custom permission override(s)
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Employee ID</span>
                    <span><?= e($user['employee_id'] ?? '—') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Email</span>
                    <span class="small"><?= e($user['email']) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Phone</span>
                    <span><?= e($user['phone'] ?? '—') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Last Login</span>
                    <span class="small"><?= $user['last_login_at'] ? timeAgo($user['last_login_at']) : 'Never' ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Created</span>
                    <span class="small"><?= formatDate($user['created_at']) ?></span>
                </div>
            </div>
        </div>

        <!-- Institution Access -->
        <div class="card mt-3">
            <div class="card-header"><i class="fas fa-university me-2"></i>Institution Access</div>
            <div class="list-group list-group-flush">
                <?php if (empty($userInstitutions)): ?>
                <div class="list-group-item text-muted small">All institutions (Super Admin)</div>
                <?php else: ?>
                <?php foreach ($userInstitutions as $inst): ?>
                <div class="list-group-item py-2">
                    <div class="fw-semibold small"><?= e($inst['name']) ?></div>
                    <small class="text-muted"><?= e($inst['organization_name'] ?? '') ?> · <?= e(ucfirst(str_replace('_', ' ', $inst['type']))) ?></small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right: Tabs -->
    <div class="col-xl-8">
        <ul class="nav nav-tabs mb-0" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPermissions">
                    <i class="fas fa-key me-1"></i>Permissions
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabActivity">
                    <i class="fas fa-history me-1"></i>Activity
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- Permissions Tab -->
            <div class="tab-pane fade show active" id="tabPermissions">
                <div class="card border-top-0 rounded-top-0">
                    <div class="card-body">
                        <?php if (empty($userPermissions)): ?>
                        <p class="text-muted text-center py-4">No permissions found.</p>
                        <?php else: ?>
                        <p class="text-muted small mb-3">
                            Effective permissions (role defaults + overrides). Showing
                            <strong><?= count($userPermissions) ?></strong> permission(s).
                        </p>
                        <?php
                        $grouped = [];
                        foreach ($userPermissions as $p) {
                            $grouped[$p['module']][] = $p;
                        }
                        foreach ($grouped as $module => $perms):
                        ?>
                        <div class="mb-3">
                            <h6 class="text-uppercase text-muted small fw-bold mb-2">
                                <i class="fas fa-circle me-1" style="font-size:0.5em;vertical-align:middle"></i>
                                <?= e(str_replace('_', ' ', $module)) ?>
                            </h6>
                            <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($perms as $p): ?>
                            <span class="badge bg-<?= !empty($p['is_override']) ? ($p['override_type'] === 'grant' ? 'warning' : 'danger') : 'primary' ?> bg-opacity-15 text-<?= !empty($p['is_override']) ? ($p['override_type'] === 'grant' ? 'warning' : 'danger') : 'primary' ?> border border-<?= !empty($p['is_override']) ? ($p['override_type'] === 'grant' ? 'warning' : 'danger') : 'primary' ?> border-opacity-25"
                                 title="<?= !empty($p['is_override']) ? 'Override: ' . $p['override_type'] : 'From role' ?>">
                                <?php if (!empty($p['is_override'])): ?>
                                <i class="fas fa-<?= $p['override_type'] === 'grant' ? 'plus' : 'minus' ?> me-1"></i>
                                <?php endif; ?>
                                <?= e($p['name']) ?>
                            </span>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (!empty($deniedPermissions)): ?>
                        <div class="mt-3 pt-3 border-top">
                            <h6 class="text-uppercase text-danger small fw-bold mb-2">
                                <i class="fas fa-ban me-1"></i>Denied (Overridden)
                            </h6>
                            <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($deniedPermissions as $p): ?>
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                <i class="fas fa-ban me-1"></i><?= e($p['name']) ?>
                            </span>
                            <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (hasPermission('users.manage_permissions')): ?>
                        <div class="mt-3 pt-3 border-top">
                            <a href="<?= url('users/' . $user['id'] . '/permissions') ?>" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-sliders-h me-1"></i>Manage Permission Overrides
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Activity Tab -->
            <div class="tab-pane fade" id="tabActivity">
                <div class="card border-top-0 rounded-top-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Action</th>
                                        <th>Resource</th>
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
                                                'login'  => 'success',
                                                'logout' => 'info',
                                                'create' => 'primary',
                                                'update' => 'warning',
                                                'delete' => 'danger',
                                                default  => 'secondary'
                                            } ?>">
                                                <?= e(ucfirst($log['action'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?= e($log['model_type'] ?? '—') ?>
                                            <?php if ($log['model_id']): ?>
                                            <span class="text-muted">#<?= $log['model_id'] ?></span>
                                            <?php endif; ?></small>
                                        </td>
                                        <td><small class="text-muted"><?= e($log['ip_address'] ?? '—') ?></small></td>
                                        <td><small class="text-muted"><?= formatDateTime($log['created_at']) ?></small></td>
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
    </div>
</div>
