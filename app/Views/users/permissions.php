<?php
$pageTitle = 'Permission Overrides — ' . e($user['first_name'] . ' ' . ($user['last_name'] ?? ''));
$roleColor = $user['role_color'] ?? 'secondary';
$roleIcon  = $user['role_icon']  ?? 'user';
$initial   = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'] ?? '', 0, 1));
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-key me-2"></i>Permission Overrides</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('users') ?>">Users</a></li>
                <li class="breadcrumb-item"><a href="<?= url('users/' . $user['id']) ?>"><?= e($user['first_name']) ?></a></li>
                <li class="breadcrumb-item active">Permission Overrides</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('users/' . $user['id']) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to User
    </a>
</div>

<!-- User Header -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:52px;height:52px;font-size:1.1rem;background:var(--bs-<?= htmlspecialchars($roleColor) ?>)">
                <?= $initial ?>
            </div>
            <div>
                <h5 class="mb-0 fw-bold"><?= e($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?></h5>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge bg-<?= htmlspecialchars($roleColor) ?> bg-opacity-15 text-<?= htmlspecialchars($roleColor) ?> border border-<?= htmlspecialchars($roleColor) ?> border-opacity-25">
                        <i class="fas fa-<?= htmlspecialchars($roleIcon) ?> me-1"></i><?= e($user['role_name'] ?? 'No Role') ?>
                    </span>
                    <span class="text-muted small"><?= e($user['email']) ?></span>
                </div>
            </div>
            <div class="ms-auto text-end">
                <div class="small text-muted">Base role permissions</div>
                <div class="fw-bold text-primary"><?= count($rolePermissions) ?></div>
            </div>
            <div class="text-end">
                <div class="small text-muted">Active overrides</div>
                <div class="fw-bold text-warning"><?= count($overrides) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info d-flex gap-2 align-items-start">
    <i class="fas fa-info-circle mt-1"></i>
    <div>
        <strong>How overrides work:</strong> A <span class="badge bg-success">Grant</span> gives this user a permission they don't have from their role.
        A <span class="badge bg-danger">Deny</span> blocks a permission they would normally have from their role.
        <span class="badge bg-secondary">None</span> means no override — the role default applies.
    </div>
</div>

<form id="overridesForm">
<?= csrfField() ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="fas fa-sliders-h me-2"></i>Permission Override Matrix</span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="showChangedOnly">
                <i class="fas fa-filter me-1"></i>Show Overridden Only
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="clearAllOverrides">
                <i class="fas fa-undo me-1"></i>Clear All Overrides
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php
        $moduleIcons = [
            'system'   => 'cog',
            'admin'    => 'user-shield',
            'academic' => 'graduation-cap',
            'fees'     => 'file-invoice-dollar',
            'faculty'  => 'chalkboard-teacher',
            'hr'       => 'id-badge',
            'crm'      => 'comments',
            'admission'=> 'clipboard-check',
            'report'   => 'chart-bar',
        ];
        // Build override lookup: permission_id => type
        $overrideLookup = [];
        foreach ($overrides as $ov) {
            $overrideLookup[$ov['permission_id']] = $ov['type']; // 'grant' or 'deny'
        }
        // Build role permission set
        $rolePermSet = array_column($rolePermissions, 'id');
        ?>
        <div class="accordion accordion-flush" id="permOverrideAccordion">
        <?php $idx = 0; foreach ($allPermissions as $module => $perms):
            $mIcon = $moduleIcons[$module] ?? 'circle';
            $overrideCount = 0;
            foreach ($perms as $p) {
                if (isset($overrideLookup[$p['id']])) $overrideCount++;
            }
            $isOpen = ($idx === 0 || $overrideCount > 0);
            $idx++;
        ?>
        <div class="accordion-item" data-module="<?= $module ?>">
            <h2 class="accordion-header">
                <button class="accordion-button <?= $isOpen ? '' : 'collapsed' ?>" type="button"
                        data-bs-toggle="collapse" data-bs-target="#ovModule_<?= $module ?>">
                    <div class="d-flex align-items-center gap-2 w-100 me-3">
                        <i class="fas fa-<?= $mIcon ?> text-secondary"></i>
                        <span class="fw-semibold text-capitalize"><?= e(str_replace('_', ' ', $module)) ?></span>
                        <?php if ($overrideCount > 0): ?>
                        <span class="badge bg-warning text-dark ms-2"><?= $overrideCount ?> override(s)</span>
                        <?php endif; ?>
                    </div>
                </button>
            </h2>
            <div id="ovModule_<?= $module ?>" class="accordion-collapse collapse <?= $isOpen ? 'show' : '' ?>">
                <div class="accordion-body p-0">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Permission</th>
                                <th class="text-center" width="80">Role Has</th>
                                <th class="text-center" width="180">Override</th>
                                <th width="160">Reason</th>
                                <th class="text-center" width="80">Expires</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($perms as $perm):
                            $hasFromRole    = in_array($perm['id'], $rolePermSet);
                            $overrideType   = $overrideLookup[$perm['id']] ?? null;
                            $overrideReason = '';
                            $overrideExpiry = '';
                            foreach ($overrides as $ov) {
                                if ($ov['permission_id'] == $perm['id']) {
                                    $overrideReason = $ov['reason'] ?? '';
                                    $overrideExpiry = $ov['expires_at'] ?? '';
                                    break;
                                }
                            }
                        ?>
                        <tr class="perm-row <?= $overrideType ? 'table-' . ($overrideType === 'grant' ? 'warning' : 'danger') . ' bg-opacity-10' : '' ?>"
                            data-has-override="<?= $overrideType ? '1' : '0' ?>"
                            data-perm-id="<?= $perm['id'] ?>">
                            <td class="py-2">
                                <div class="small fw-semibold"><?= e($perm['name']) ?></div>
                                <code class="text-muted" style="font-size:0.7em"><?= e($perm['slug']) ?></code>
                            </td>
                            <td class="text-center">
                                <?php if ($hasFromRole): ?>
                                <i class="fas fa-check-circle text-success" title="Role grants this"></i>
                                <?php else: ?>
                                <i class="fas fa-times-circle text-muted" title="Role does not grant this"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <input type="radio" class="btn-check override-radio"
                                           name="overrides[<?= $perm['id'] ?>][type]"
                                           id="ov_grant_<?= $perm['id'] ?>"
                                           value="grant"
                                           data-perm-id="<?= $perm['id'] ?>"
                                           <?= $overrideType === 'grant' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-success btn-sm" for="ov_grant_<?= $perm['id'] ?>" title="Grant">
                                        <i class="fas fa-plus"></i>
                                    </label>

                                    <input type="radio" class="btn-check override-radio"
                                           name="overrides[<?= $perm['id'] ?>][type]"
                                           id="ov_none_<?= $perm['id'] ?>"
                                           value="none"
                                           data-perm-id="<?= $perm['id'] ?>"
                                           <?= !$overrideType ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-secondary btn-sm" for="ov_none_<?= $perm['id'] ?>" title="None (role default)">
                                        <i class="fas fa-minus"></i>
                                    </label>

                                    <input type="radio" class="btn-check override-radio"
                                           name="overrides[<?= $perm['id'] ?>][type]"
                                           id="ov_deny_<?= $perm['id'] ?>"
                                           value="deny"
                                           data-perm-id="<?= $perm['id'] ?>"
                                           <?= $overrideType === 'deny' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-danger btn-sm" for="ov_deny_<?= $perm['id'] ?>" title="Deny">
                                        <i class="fas fa-ban"></i>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm"
                                       name="overrides[<?= $perm['id'] ?>][reason]"
                                       value="<?= e($overrideReason) ?>"
                                       placeholder="Reason (optional)"
                                       <?= !$overrideType ? 'disabled' : '' ?>>
                            </td>
                            <td>
                                <input type="date" class="form-control form-control-sm"
                                       name="overrides[<?= $perm['id'] ?>][expires_at]"
                                       value="<?= e($overrideExpiry) ?>"
                                       title="Leave blank for permanent"
                                       <?= !$overrideType ? 'disabled' : '' ?>>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <span class="text-muted small" id="overrideSummary">
            <?= count($overrides) ?> active override(s)
        </span>
        <div class="d-flex gap-2">
            <a href="<?= url('users/' . $user['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-warning" id="saveOverridesBtn">
                <i class="fas fa-save me-1"></i>Save Overrides
            </button>
        </div>
    </div>
</div>
</form>

<script>
// Enable/disable reason+expiry when override type changes
document.addEventListener('change', (e) => {
    if (!e.target.classList.contains('override-radio')) return;
    const permId = e.target.dataset.permId;
    const row    = document.querySelector(`tr[data-perm-id="${permId}"]`);
    const type   = e.target.value;
    const reason = row.querySelector('input[type="text"]');
    const expiry = row.querySelector('input[type="date"]');

    reason.disabled = (type === 'none');
    expiry.disabled = (type === 'none');

    row.classList.remove('table-warning', 'table-danger', 'bg-opacity-10');
    row.dataset.hasOverride = type !== 'none' ? '1' : '0';
    if (type === 'grant') row.classList.add('table-warning', 'bg-opacity-10');
    if (type === 'deny')  row.classList.add('table-danger', 'bg-opacity-10');

    updateOverrideSummary();
});

function updateOverrideSummary() {
    const count = document.querySelectorAll('.override-radio[value="grant"]:checked, .override-radio[value="deny"]:checked').length;
    document.getElementById('overrideSummary').textContent = `${count} active override(s)`;
}

// Show only overridden rows
let showingAll = true;
document.getElementById('showChangedOnly').addEventListener('click', function() {
    showingAll = !showingAll;
    document.querySelectorAll('.perm-row').forEach(row => {
        row.style.display = showingAll || row.dataset.hasOverride === '1' ? '' : 'none';
    });
    this.innerHTML = showingAll
        ? '<i class="fas fa-filter me-1"></i>Show Overridden Only'
        : '<i class="fas fa-list me-1"></i>Show All';
});

// Clear all overrides
document.getElementById('clearAllOverrides').addEventListener('click', () => {
    if (!confirm('Clear all permission overrides for this user?')) return;
    document.querySelectorAll('.override-radio[value="none"]').forEach(r => {
        r.checked = true;
        r.dispatchEvent(new Event('change', {bubbles: true}));
    });
});

// Form submit
document.getElementById('overridesForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('saveOverridesBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    const fd = new FormData(e.target);
    const resp = await fetch('<?= url('users/' . $user['id'] . '/permissions/save') ?>', {
        method: 'POST', body: fd
    });
    const data = await resp.json();

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-save me-1"></i>Save Overrides';

    if (data.status === 'success') {
        toastr.success(data.message);
    } else {
        toastr.error(data.message || 'Failed to save overrides.');
    }
});
</script>
