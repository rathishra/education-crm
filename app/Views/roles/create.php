<?php
$isEdit     = isset($role);
$pageTitle  = $isEdit ? 'Edit Role: ' . e($role['name']) : 'Create Role';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-shield-alt me-2"></i><?= $pageTitle ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('roles') ?>">Roles</a></li>
                <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Create' ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('roles') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form id="roleForm">
<?= csrfField() ?>
<div class="row g-4">

    <!-- Left: Role Meta -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header fw-semibold"><i class="fas fa-tag me-2"></i>Role Properties</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Role Name <span class="text-danger">*</span></label>
                    <?php if ($isEdit && $role['is_system']): ?>
                    <input type="text" class="form-control" value="<?= e($role['name']) ?>" readonly>
                    <small class="text-muted">System role name cannot be changed.</small>
                    <?php else: ?>
                    <input type="text" class="form-control" name="name" id="roleName"
                           value="<?= e($role['name'] ?? '') ?>" required
                           placeholder="e.g. Admission Manager">
                    <?php endif; ?>
                </div>

                <?php if (!($isEdit && $role['is_system'])): ?>
                <div class="mb-3">
                    <label class="form-label">Slug</label>
                    <input type="text" class="form-control" name="slug" id="roleSlug"
                           value="<?= e($role['slug'] ?? '') ?>"
                           placeholder="auto_generated" pattern="[a-z0-9_]+">
                    <small class="text-muted">Lowercase letters, digits, underscore only. Leave blank to auto-generate.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Hierarchy Level</label>
                    <select class="form-select" name="level">
                        <option value="1" <?= ($role['level'] ?? 3) == 1 ? 'selected' : '' ?>>1 — Super Admin</option>
                        <option value="2" <?= ($role['level'] ?? 3) == 2 ? 'selected' : '' ?>>2 — Org Admin</option>
                        <option value="3" <?= ($role['level'] ?? 3) == 3 ? 'selected' : '' ?>>3 — Institution Admin</option>
                        <option value="4" <?= ($role['level'] ?? 3) == 4 ? 'selected' : '' ?>>4 — Manager</option>
                        <option value="5" <?= ($role['level'] ?? 3) == 5 ? 'selected' : '' ?>>5 — Staff</option>
                        <option value="6" <?= ($role['level'] ?? 3) == 6 ? 'selected' : '' ?>>6 — Viewer</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2"
                              placeholder="Brief description of this role's purpose"><?= e($role['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Badge Color</label>
                    <div class="d-flex flex-wrap gap-2" id="colorPicker">
                        <?php $colors = ['primary','success','danger','warning','info','secondary','teal','purple','orange']; ?>
                        <?php $selectedColor = $role['color'] ?? 'secondary'; ?>
                        <?php foreach ($colors as $c): ?>
                        <button type="button"
                                class="btn btn-<?= $c ?> btn-sm color-opt <?= $selectedColor === $c ? 'ring-selected' : '' ?>"
                                data-color="<?= $c ?>"
                                style="width:32px;height:32px;padding:0;<?= $selectedColor === $c ? 'outline:2px solid #333;outline-offset:2px;' : '' ?>"
                                title="<?= ucfirst($c) ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="color" id="colorInput" value="<?= e($selectedColor) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Icon</label>
                    <div class="input-group">
                        <span class="input-group-text" id="iconPreview">
                            <i class="fas fa-<?= e($role['icon'] ?? 'user') ?>"></i>
                        </span>
                        <input type="text" class="form-control" name="icon" id="iconInput"
                               value="<?= e($role['icon'] ?? 'user') ?>"
                               placeholder="Font Awesome icon name (without fa-)">
                    </div>
                    <small class="text-muted">Examples: user, shield, building, crown, cog</small>
                </div>

                <!-- Preview Card -->
                <div class="mt-4 pt-3 border-top">
                    <label class="form-label text-muted small">PREVIEW</label>
                    <div id="rolePreview" class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                        <div id="previewIcon" class="rounded-3 p-2 bg-<?= e($selectedColor) ?> bg-opacity-15 text-<?= e($selectedColor) ?>" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-<?= e($role['icon'] ?? 'user') ?> fa-lg"></i>
                        </div>
                        <div>
                            <div id="previewName" class="fw-bold"><?= e($role['name'] ?? 'Role Name') ?></div>
                            <span id="previewBadge" class="badge bg-<?= e($selectedColor) ?>"><?= e($selectedColor) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Permission Matrix -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-semibold"><i class="fas fa-key me-2"></i>Permission Matrix</span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success" id="grantAllBtn">
                        <i class="fas fa-check-double me-1"></i>Grant All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="revokeAllBtn">
                        <i class="fas fa-times me-1"></i>Revoke All
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="permAccordion">
                <?php
                $moduleIcons = [
                    'system'        => 'cog',
                    'admin'         => 'building',
                    'crm'           => 'filter',
                    'admissions'    => 'clipboard-check',
                    'students'      => 'user-graduate',
                    'academic'      => 'graduation-cap',
                    'attendance'    => 'calendar-check',
                    'assessments'   => 'file-alt',
                    'fees'          => 'file-invoice-dollar',
                    'faculty'       => 'chalkboard-teacher',
                    'hr'            => 'id-badge',
                    'lms'           => 'laptop',
                    'transport'     => 'bus',
                    'hostel'        => 'bed',
                    'library'       => 'book',
                    'communication' => 'bell',
                    'placement'     => 'briefcase',
                    'reports'       => 'chart-bar',
                    'portal'        => 'globe',
                ];
                $moduleColors = [
                    'system'        => 'secondary',
                    'admin'         => 'danger',
                    'crm'           => 'purple',
                    'admissions'    => 'orange',
                    'students'      => 'primary',
                    'academic'      => 'info',
                    'attendance'    => 'teal',
                    'assessments'   => 'warning',
                    'fees'          => 'success',
                    'faculty'       => 'indigo',
                    'hr'            => 'pink',
                    'lms'           => 'primary',
                    'transport'     => 'secondary',
                    'hostel'        => 'warning',
                    'library'       => 'success',
                    'communication' => 'info',
                    'placement'     => 'teal',
                    'reports'       => 'dark',
                    'portal'        => 'primary',
                ];
                $idx = 0;
                foreach ($allPermissions as $module => $perms):
                    $mIcon  = $moduleIcons[$module]  ?? 'circle';
                    $mColor = $moduleColors[$module] ?? 'secondary';
                    $moduleChecked = 0;
                    foreach ($perms as $p) {
                        if (in_array($p['id'], $rolePermIds)) $moduleChecked++;
                    }
                    $isOpen = ($idx === 0);
                    $idx++;
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $isOpen ? '' : 'collapsed' ?>" type="button"
                                data-bs-toggle="collapse" data-bs-target="#module_<?= $module ?>">
                            <div class="d-flex align-items-center gap-2 w-100 me-3">
                                <i class="fas fa-<?= $mIcon ?> text-<?= $mColor ?>"></i>
                                <span class="fw-semibold text-capitalize"><?= e(str_replace('_', ' ', $module)) ?></span>
                                <span class="badge bg-<?= $mColor ?> bg-opacity-15 text-<?= $mColor ?> ms-auto module-count-<?= $module ?>">
                                    <?= $moduleChecked ?>/<?= count($perms) ?>
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div id="module_<?= $module ?>" class="accordion-collapse collapse <?= $isOpen ? 'show' : '' ?>" data-bs-parent="">
                        <div class="accordion-body p-3">
                            <div class="mb-2 d-flex align-items-center gap-2">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input module-all-toggle" type="checkbox"
                                           id="all_<?= $module ?>"
                                           data-module="<?= $module ?>"
                                           <?= $moduleChecked === count($perms) && count($perms) > 0 ? 'checked' : '' ?>>
                                    <label class="form-check-label text-muted small" for="all_<?= $module ?>">
                                        Select all in <?= e(ucfirst($module)) ?>
                                    </label>
                                </div>
                            </div>
                            <div class="row g-2">
                            <?php foreach ($perms as $perm): ?>
                            <?php $checked = in_array($perm['id'], $rolePermIds); ?>
                            <div class="col-sm-6 col-lg-4">
                                <div class="form-check perm-item">
                                    <input class="form-check-input perm-check"
                                           type="checkbox"
                                           name="permissions[]"
                                           value="<?= $perm['id'] ?>"
                                           id="perm_<?= $perm['id'] ?>"
                                           data-module="<?= $module ?>"
                                           data-total="<?= count($perms) ?>"
                                           <?= $checked ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="perm_<?= $perm['id'] ?>">
                                        <?= e($perm['name']) ?>
                                        <br><code class="text-muted" style="font-size:0.7em"><?= e($perm['slug']) ?></code>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <span id="selectedCount">
                        <?= count($rolePermIds) ?>
                    </span> permission(s) selected
                </span>
                <div class="d-flex gap-2">
                    <a href="<?= url('roles') ?>" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Role' : 'Create Role' ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
</form>

<script>
const SAVE_URL = '<?= $isEdit ? url('roles/' . $role['id'] . '/update') : url('roles') ?>';
const IS_EDIT  = <?= $isEdit ? 'true' : 'false' ?>;

// ── Color picker ─────────────────────────────────────────────
document.querySelectorAll('.color-opt').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.color-opt').forEach(b => b.style.outline = '');
        btn.style.outline = '2px solid #333';
        btn.style.outlineOffset = '2px';
        const color = btn.dataset.color;
        document.getElementById('colorInput').value = color;
        updatePreview();
    });
});

// ── Icon preview ─────────────────────────────────────────────
document.getElementById('iconInput').addEventListener('input', updatePreview);
document.getElementById('roleName')?.addEventListener('input', updatePreview);

function updatePreview() {
    const color = document.getElementById('colorInput').value;
    const icon  = document.getElementById('iconInput').value || 'user';
    const name  = document.getElementById('roleName')?.value || 'Role Name';

    const previewIcon = document.getElementById('previewIcon');
    previewIcon.className = `rounded-3 p-2 bg-${color} bg-opacity-15 text-${color}`;
    previewIcon.style = 'width:44px;height:44px;display:flex;align-items:center;justify-content:center;';
    previewIcon.innerHTML = `<i class="fas fa-${icon} fa-lg"></i>`;

    document.getElementById('iconPreview').innerHTML = `<i class="fas fa-${icon}"></i>`;
    document.getElementById('previewName').textContent = name;
    document.getElementById('previewBadge').className = `badge bg-${color}`;
    document.getElementById('previewBadge').textContent = color;
}

// ── Permission counters ────────────────────────────────────────
function updateCounts() {
    const modules = {};
    document.querySelectorAll('.perm-check').forEach(cb => {
        const mod = cb.dataset.module;
        if (!modules[mod]) modules[mod] = {checked: 0, total: parseInt(cb.dataset.total)};
        if (cb.checked) modules[mod].checked++;
    });
    for (const [mod, counts] of Object.entries(modules)) {
        const badge = document.querySelector(`.module-count-${mod}`);
        if (badge) badge.textContent = `${counts.checked}/${counts.total}`;
        const allToggle = document.getElementById(`all_${mod}`);
        if (allToggle) allToggle.checked = counts.checked === counts.total && counts.total > 0;
    }
    const total = document.querySelectorAll('.perm-check:checked').length;
    document.getElementById('selectedCount').textContent = total;
}

document.querySelectorAll('.perm-check').forEach(cb => cb.addEventListener('change', updateCounts));

// ── Module select-all ─────────────────────────────────────────
document.querySelectorAll('.module-all-toggle').forEach(toggle => {
    toggle.addEventListener('change', () => {
        const mod = toggle.dataset.module;
        document.querySelectorAll(`.perm-check[data-module="${mod}"]`).forEach(cb => {
            cb.checked = toggle.checked;
        });
        updateCounts();
    });
});

// ── Grant/Revoke all ─────────────────────────────────────────
document.getElementById('grantAllBtn').addEventListener('click', () => {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = true);
    document.querySelectorAll('.module-all-toggle').forEach(t => t.checked = true);
    updateCounts();
});
document.getElementById('revokeAllBtn').addEventListener('click', () => {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('.module-all-toggle').forEach(t => t.checked = false);
    updateCounts();
});

// ── Form submit ───────────────────────────────────────────────
document.getElementById('roleForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    const fd = new FormData(e.target);
    const resp = await fetch(SAVE_URL, {method: 'POST', body: fd});
    const data = await resp.json();

    btn.disabled = false;
    btn.innerHTML = IS_EDIT ? '<i class="fas fa-save me-1"></i>Update Role' : '<i class="fas fa-save me-1"></i>Create Role';

    if (data.status === 'success') {
        toastr.success(data.message);
        setTimeout(() => window.location = data.redirect || '<?= url('roles') ?>', 1200);
    } else {
        toastr.error(data.message || 'Failed to save role.');
    }
});

// Auto-slug from name (create mode only)
<?php if (!$isEdit): ?>
document.getElementById('roleName')?.addEventListener('blur', () => {
    const slugField = document.getElementById('roleSlug');
    if (!slugField.value) {
        slugField.value = document.getElementById('roleName').value
            .toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
    }
});
<?php endif; ?>
</script>
