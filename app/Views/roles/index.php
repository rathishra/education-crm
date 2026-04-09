<?php $pageTitle = 'Role Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-shield-alt me-2"></i>Role Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Roles</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('roles.manage')): ?>
    <div class="d-flex gap-2">
        <?php if ($totalPermissions === 0): ?>
        <div class="alert alert-warning mb-0 py-2 px-3 d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <span>No permissions seeded. Run <code>database/42_permissions_seed.sql</code> in phpMyAdmin.</span>
        </div>
        <?php endif; ?>
        <a href="<?= url('roles/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create Role
        </a>
    </div>
    <?php endif; ?>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-2 fw-bold text-primary"><?= $totalRoles ?></div>
                <small class="text-muted">Total Roles</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-2 fw-bold text-warning"><?= $systemRoles ?></div>
                <small class="text-muted">System Roles</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-2 fw-bold text-success"><?= $customRoles ?></div>
                <small class="text-muted">Custom Roles</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 bg-info bg-opacity-10">
            <div class="card-body py-3 text-center">
                <div class="fs-2 fw-bold text-info"><?= $totalUsers ?></div>
                <small class="text-muted">Total Assigned Users</small>
            </div>
        </div>
    </div>
</div>

<!-- Roles Grid -->
<div class="row g-4" id="rolesGrid">
<?php foreach ($roles as $role):
    $color = $role['color'] ?? 'secondary';
    $icon  = $role['icon']  ?? 'user';
?>
<div class="col-xl-4 col-lg-6">
    <div class="card h-100 border-2" style="border-color: var(--bs-<?= htmlspecialchars($color) ?>)!important;">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 bg-<?= htmlspecialchars($color) ?> bg-opacity-15 text-<?= htmlspecialchars($color) ?>" style="width:48px;height:48px;display:flex!important;align-items:center;justify-content:center;">
                        <i class="fas fa-<?= htmlspecialchars($icon) ?> fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold"><?= e($role['name']) ?></h6>
                        <code class="small text-muted"><?= e($role['slug']) ?></code>
                    </div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                    <?php if ($role['is_system']): ?>
                    <span class="badge bg-warning text-dark"><i class="fas fa-lock me-1"></i>System</span>
                    <?php else: ?>
                    <span class="badge bg-success">Custom</span>
                    <?php endif; ?>
                    <span class="badge bg-light text-dark border">Level <?= (int)$role['level'] ?></span>
                </div>
            </div>

            <?php if (!empty($role['description'])): ?>
            <p class="text-muted small mb-3"><?= e($role['description']) ?></p>
            <?php endif; ?>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="bg-light rounded p-2 text-center">
                        <div class="fw-bold text-<?= htmlspecialchars($color) ?>"><?= (int)$role['user_count'] ?></div>
                        <small class="text-muted">Users</small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="bg-light rounded p-2 text-center">
                        <div class="fw-bold text-<?= htmlspecialchars($color) ?>"><?= (int)$role['permission_count'] ?></div>
                        <small class="text-muted">Permissions</small>
                    </div>
                </div>
            </div>

            <!-- Permission progress bar -->
            <?php $pct = $role['permission_count'] > 0 ? min(100, round($role['permission_count'] / max($totalPermissions, 1) * 100)) : 0; ?>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">Permission coverage</small>
                    <small class="text-muted"><?= $pct ?>%</small>
                </div>
                <div class="progress" style="height:5px">
                    <div class="progress-bar bg-<?= htmlspecialchars($color) ?>" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
            <?php if (hasPermission('roles.manage')): ?>
            <a href="<?= url('roles/' . $role['id'] . '/edit') ?>" class="btn btn-sm btn-outline-<?= htmlspecialchars($color) ?> flex-fill">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <button class="btn btn-sm btn-outline-secondary" title="View Permissions"
                    onclick="viewPermissions(<?= $role['id'] ?>, '<?= e($role['name']) ?>')">
                <i class="fas fa-key"></i>
            </button>
            <?php if (!$role['is_system']): ?>
            <button class="btn btn-sm btn-outline-info" title="Clone Role"
                    onclick="cloneRole(<?= $role['id'] ?>, '<?= e($role['name']) ?>')">
                <i class="fas fa-copy"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" title="Delete Role"
                    onclick="deleteRole(<?= $role['id'] ?>, '<?= e($role['name']) ?>', <?= (int)$role['user_count'] ?>)">
                <i class="fas fa-trash"></i>
            </button>
            <?php endif; ?>
            <?php else: ?>
            <a href="<?= url('roles/' . $role['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary flex-fill">
                <i class="fas fa-eye me-1"></i>View
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Permission Preview Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Permissions — <span id="permModalRoleName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="permModalBody">
                <div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

<script>
function viewPermissions(roleId, roleName) {
    document.getElementById('permModalRoleName').textContent = roleName;
    document.getElementById('permModalBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    new bootstrap.Modal(document.getElementById('permissionsModal')).show();

    fetch('<?= url('roles') ?>/' + roleId + '/permissions', {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(r => r.json())
    .then(data => {
        if (!data.grouped || Object.keys(data.grouped).length === 0) {
            document.getElementById('permModalBody').innerHTML = '<p class="text-muted text-center py-3">No permissions assigned.</p>';
            return;
        }
        const moduleIcons = {
            system:'cog', admin:'building', crm:'filter', admissions:'clipboard-check',
            students:'user-graduate', academic:'graduation-cap', attendance:'calendar-check',
            assessments:'file-alt', fees:'file-invoice-dollar', faculty:'chalkboard-teacher',
            hr:'id-badge', lms:'laptop', transport:'bus', hostel:'bed', library:'book',
            communication:'bell', placement:'briefcase', reports:'chart-bar', portal:'globe'
        };
        const moduleColors = {
            system:'secondary', admin:'danger', crm:'purple', admissions:'warning',
            students:'primary', academic:'info', attendance:'success', assessments:'warning',
            fees:'success', faculty:'primary', hr:'danger', lms:'primary',
            transport:'secondary', hostel:'warning', library:'success',
            communication:'info', placement:'success', reports:'dark', portal:'primary'
        };
        let html = `<p class="text-muted small mb-3">Total: <strong>${data.data.length}</strong> permission(s) assigned</p><div class="row g-3">`;
        for (const [module, perms] of Object.entries(data.grouped)) {
            const icon  = moduleIcons[module]  || 'circle';
            const color = moduleColors[module] || 'secondary';
            html += `<div class="col-md-6"><div class="border rounded p-3 h-100">
                <h6 class="text-capitalize fw-semibold mb-2 text-${color}">
                    <i class="fas fa-${icon} me-1"></i>${module.replace(/_/g,' ')}
                    <span class="badge bg-${color} bg-opacity-15 text-${color} float-end">${perms.length}</span>
                </h6>
                <div class="d-flex flex-wrap gap-1">`;
            perms.forEach(p => {
                html += `<span class="badge bg-light text-dark border" style="font-size:0.7rem">${p}</span>`;
            });
            html += `</div></div></div>`;
        }
        html += '</div>';
        document.getElementById('permModalBody').innerHTML = html;
    })
    .catch(() => {
        document.getElementById('permModalBody').innerHTML = '<p class="text-danger text-center">Failed to load permissions.</p>';
    });
}

function cloneRole(roleId, roleName) {
    if (!confirm(`Clone role "${roleName}"?`)) return;
    fetch('<?= url('roles') ?>/' + roleId + '/clone', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>'}
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            toastr.success(data.message);
            setTimeout(() => window.location = data.redirect || '<?= url('roles') ?>', 1200);
        } else {
            toastr.error(data.message);
        }
    });
}

function deleteRole(roleId, roleName, userCount) {
    if (userCount > 0) {
        toastr.error(`Cannot delete "${roleName}" — it is assigned to ${userCount} user(s).`);
        return;
    }
    if (!confirm(`Delete role "${roleName}"? This cannot be undone.`)) return;
    fetch('<?= url('roles') ?>/' + roleId + '/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrfToken() ?>'}
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            toastr.success(data.message);
            setTimeout(() => window.location.reload(), 1000);
        } else {
            toastr.error(data.message);
        }
    });
}
</script>
