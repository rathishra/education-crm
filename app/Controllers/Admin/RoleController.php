<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Role;

class RoleController extends BaseController
{
    private Role $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->roleModel = new Role();
    }

    // ── INDEX ────────────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('roles.view');

        $this->db->query(
            "SELECT r.*,
                    COUNT(DISTINCT ur.user_id) AS user_count,
                    COUNT(DISTINCT rp.permission_id) AS permission_count
             FROM roles r
             LEFT JOIN user_roles ur ON ur.role_id = r.id
             LEFT JOIN role_permissions rp ON rp.role_id = r.id
             GROUP BY r.id
             ORDER BY r.level, r.name"
        );
        $roles = $this->db->fetchAll();

        // Stats
        $totalRoles    = count($roles);
        $totalUsers    = (int)array_sum(array_column($roles, 'user_count'));
        $customRoles   = count(array_filter($roles, fn($r) => !$r['is_system']));
        $systemRoles   = $totalRoles - $customRoles;

        $this->db->query("SELECT COUNT(*) AS cnt FROM permissions");
        $totalPermissions = (int)($this->db->fetch()['cnt'] ?? 0);

        $this->view('roles/index', compact('roles', 'totalRoles', 'totalUsers', 'customRoles', 'systemRoles', 'totalPermissions'));
    }

    // ── CREATE FORM ──────────────────────────────────────────
    public function create(): void
    {
        $this->authorize('roles.manage');

        $allPermissions = $this->roleModel->getAllPermissionsGrouped();
        $rolePermIds    = [];

        $this->view('roles/create', compact('allPermissions', 'rolePermIds'));
    }

    // ── STORE ────────────────────────────────────────────────
    public function store(): void
    {
        $this->authorize('roles.manage');

        $name  = trim($this->input('name', ''));
        $slug  = trim($this->input('slug', ''));
        $desc  = trim($this->input('description', ''));
        $color = trim($this->input('color', 'secondary'));
        $icon  = trim($this->input('icon', 'user'));
        $level = (int)$this->input('level', 3);
        $permIds = (array)($_POST['permissions'] ?? []);

        if (!$name) {
            $this->json(['status' => 'error', 'message' => 'Role name is required.'], 422);
        }

        // Auto-generate slug
        if (!$slug) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
        }

        // Check slug unique
        $this->db->query("SELECT id FROM roles WHERE slug = ?", [$slug]);
        if ($this->db->fetch()) {
            $slug .= '_' . time();
        }

        try {
            $this->db->beginTransaction();

            $roleId = $this->db->insert('roles', [
                'name'        => $name,
                'slug'        => $slug,
                'description' => $desc,
                'color'       => $color,
                'icon'        => $icon,
                'level'       => $level,
                'is_system'   => 0,
            ]);

            $this->roleModel->syncPermissions($roleId, array_map('intval', $permIds));

            $this->logAudit('create', 'role', $roleId);
            $this->db->commit();

            $this->json(['status' => 'success', 'message' => "Role '{$name}' created.", 'redirect' => url('roles')]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['status' => 'error', 'message' => 'Failed to create role: ' . $e->getMessage()], 500);
        }
    }

    // ── EDIT FORM ────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('roles.manage');

        $role = $this->roleModel->findWithPermissions($id);
        if (!$role) {
            $this->redirectWith(url('roles'), 'error', 'Role not found.');
            return;
        }

        $allPermissions = $this->roleModel->getAllPermissionsGrouped();
        $rolePermIds    = array_column($role['permissions'], 'id');

        $this->view('roles/create', compact('role', 'allPermissions', 'rolePermIds'));
    }

    // ── UPDATE ───────────────────────────────────────────────
    public function update(int $id): void
    {
        $this->authorize('roles.manage');

        $this->db->query("SELECT * FROM roles WHERE id = ?", [$id]);
        $role = $this->db->fetch();
        if (!$role) {
            $this->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        }

        $permIds = array_map('intval', (array)($_POST['permissions'] ?? []));

        $updateData = [
            'description' => trim($this->input('description', '')),
            'color'       => trim($this->input('color', $role['color'])),
            'icon'        => trim($this->input('icon', $role['icon'])),
        ];

        // System roles cannot have name/slug/level changed
        if (!$role['is_system']) {
            $updateData['name']  = trim($this->input('name', $role['name']));
            $updateData['level'] = (int)$this->input('level', $role['level']);
        }

        try {
            $this->db->beginTransaction();
            $this->db->update('roles', $updateData, 'id = ?', [$id]);
            $this->roleModel->syncPermissions($id, $permIds);
            $this->logAudit('update', 'role', $id);
            $this->db->commit();

            $this->json(['status' => 'success', 'message' => 'Role updated successfully.', 'redirect' => url('roles')]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['status' => 'error', 'message' => 'Failed to update role.'], 500);
        }
    }

    // ── DESTROY ──────────────────────────────────────────────
    public function destroy(int $id): void
    {
        $this->authorize('roles.manage');

        $this->db->query("SELECT * FROM roles WHERE id = ?", [$id]);
        $role = $this->db->fetch();
        if (!$role) {
            $this->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        }
        if ($role['is_system']) {
            $this->json(['status' => 'error', 'message' => 'System roles cannot be deleted.'], 403);
        }

        // Check if role is in use
        $this->db->query("SELECT COUNT(*) AS c FROM user_roles WHERE role_id = ?", [$id]);
        $cnt = $this->db->fetch();
        if (($cnt['c'] ?? 0) > 0) {
            $this->json(['status' => 'error', 'message' => 'Cannot delete role assigned to users.'], 422);
        }

        $this->db->delete('role_permissions', 'role_id = ?', [$id]);
        $this->db->delete('roles', 'id = ?', [$id]);
        $this->logAudit('delete', 'role', $id);

        $this->json(['status' => 'success', 'message' => 'Role deleted.']);
    }

    // ── CLONE ────────────────────────────────────────────────
    public function clone(int $id): void
    {
        $this->authorize('roles.manage');

        $role = $this->roleModel->findWithPermissions($id);
        if (!$role) {
            $this->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        }

        $newName = 'Copy of ' . $role['name'];
        $newSlug = $role['slug'] . '_copy_' . time();

        try {
            $this->db->beginTransaction();
            $newId = $this->db->insert('roles', [
                'name'        => $newName,
                'slug'        => $newSlug,
                'description' => $role['description'] . ' (Cloned)',
                'color'       => $role['color'] ?? 'secondary',
                'icon'        => $role['icon'] ?? 'user',
                'level'       => $role['level'],
                'is_system'   => 0,
            ]);
            $permIds = array_column($role['permissions'], 'id');
            $this->roleModel->syncPermissions($newId, $permIds);
            $this->logAudit('clone', 'role', $id, null, ['new_role_id' => $newId]);
            $this->db->commit();

            $this->json(['status' => 'success', 'message' => "Role cloned as '{$newName}'.", 'redirect' => url('roles/' . $newId . '/edit')]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['status' => 'error', 'message' => 'Failed to clone role.'], 500);
        }
    }

    // ── GET PERMISSIONS JSON (for modal preview + matrix) ────
    public function getPermissions(int $id): void
    {
        $this->authorize('roles.view');

        $this->db->query(
            "SELECT p.id, p.name, p.module
             FROM role_permissions rp
             JOIN permissions p ON p.id = rp.permission_id
             WHERE rp.role_id = ?
             ORDER BY p.module, p.name",
            [$id]
        );
        $rows    = $this->db->fetchAll();
        $permIds = array_column($rows, 'id');

        $grouped = [];
        foreach ($rows as $p) {
            $grouped[$p['module']][] = $p['name'];
        }

        $this->json(['status' => 'success', 'data' => $permIds, 'grouped' => $grouped]);
    }

    // ── SAVE PERMISSIONS (AJAX inline matrix save) ───────────
    public function savePermissions(int $id): void
    {
        $this->authorize('roles.manage');

        $this->db->query("SELECT id, is_system FROM roles WHERE id = ?", [$id]);
        $role = $this->db->fetch();
        if (!$role) {
            $this->json(['status' => 'error', 'message' => 'Role not found.'], 404);
        }

        $permIds = array_map('intval', (array)($_POST['permissions'] ?? []));

        try {
            $this->roleModel->syncPermissions($id, $permIds);
            $this->logAudit('update_permissions', 'role', $id);
            $this->json(['status' => 'success', 'message' => 'Permissions saved.', 'count' => count($permIds)]);
        } catch (\Exception $e) {
            $this->json(['status' => 'error', 'message' => 'Failed to save permissions.'], 500);
        }
    }
}
