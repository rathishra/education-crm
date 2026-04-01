<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\User;
use App\Models\Role;

class UserController extends BaseController
{
    private User $userModel;
    private Role $roleModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    /**
     * List users
     */
    public function index(): void
    {
        $this->authorize('users.view');

        $page = (int)($this->input('page') ?? 1);
        $filters = [
            'search'         => $this->input('search'),
            'role_id'        => $this->input('role_id'),
            'is_active'      => $this->input('status') !== null ? $this->input('status') : null,
            'institution_id' => $this->institutionId,
        ];

        // Super admin and org admin can see all
        if (hasRole('super_admin') || hasRole('org_admin')) {
            unset($filters['institution_id']);
        }

        $users = $this->userModel->getListPaginated($page, 15, $filters);
        $roles = $this->roleModel->getAllRoles();

        // Stats
        $this->db->query("SELECT COUNT(*) AS total, SUM(is_active) AS active, SUM(1 - is_active) AS inactive FROM users");
        $sc = $this->db->fetch();
        $overrideCount = 0;
        $overrideUsers = [];
        try {
            $this->db->query("SELECT COUNT(DISTINCT user_id) AS cnt FROM user_permission_overrides WHERE (expires_at IS NULL OR expires_at >= CURDATE())");
            $overrideCount = (int)($this->db->fetch()['cnt'] ?? 0);
            $this->db->query("SELECT DISTINCT user_id FROM user_permission_overrides WHERE (expires_at IS NULL OR expires_at >= CURDATE())");
            $overrideUsers = array_column($this->db->fetchAll(), 'user_id');
        } catch (\Exception $e) {
            // user_permission_overrides table not yet migrated — run database/19_rbac_enterprise.sql
        }
        $stats = [
            'total'          => (int)($sc['total'] ?? 0),
            'active'         => (int)($sc['active'] ?? 0),
            'inactive'       => (int)($sc['inactive'] ?? 0),
            'with_overrides' => $overrideCount,
        ];

        // Add role color/icon and override flags to each user row
        $roleColors = [];
        try {
            $this->db->query("SELECT r.id, r.color, r.icon FROM roles r");
            foreach ($this->db->fetchAll() as $r) {
                $roleColors[$r['id']] = ['color' => $r['color'] ?? 'secondary', 'icon' => $r['icon'] ?? 'user'];
            }
        } catch (\Exception $e) {
            // color/icon columns not yet migrated — run database/19_rbac_enterprise.sql
        }

        foreach ($users['data'] as &$u) {
            $rid = $u['role_id'] ?? null;
            $u['role_color']    = $rid && isset($roleColors[$rid]) ? $roleColors[$rid]['color'] : 'secondary';
            $u['role_icon']     = $rid && isset($roleColors[$rid]) ? $roleColors[$rid]['icon']  : 'user';
            $u['has_overrides'] = in_array($u['id'], $overrideUsers);
        }
        unset($u);

        $this->view('users.index', [
            'pageTitle'   => 'User Management',
            'users'       => $users,
            'roles'       => $roles,
            'filters'     => $filters,
            'stats'       => $stats,
            'currentUser' => $this->user,
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->authorize('users.create');

        $roles = $this->roleModel->getAllRoles();
        $institutions = session('user_institutions', []);

        $this->view('users.create', [
            'pageTitle'    => 'Add User',
            'roles'        => $roles,
            'institutions' => $institutions,
        ]);
    }

    /**
     * Store new user
     */
    public function store(): void
    {
        $this->authorize('users.create');

        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired.']);
            return;
        }

        $data = $this->postData([
            'employee_id', 'first_name', 'last_name', 'email', 'phone', 'password'
        ]);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'last_name'  => 'required|max:100',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'phone',
            'password'   => 'required|min:8',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        $roleId = (int)($_POST['role_id'] ?? 0);
        $assignedInstitutionId = (int)($_POST['institution_id'] ?? 0);

        if (!$roleId) {
            $this->backWithErrors(['Please select a role.'], $data);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Create user
            $data['password'] = $this->userModel->hashPassword($data['password']);
            $data['is_active'] = 1;
            $userId = $this->userModel->create($data);

            // Assign role
            $this->db->insert('user_roles', [
                'user_id'         => $userId,
                'role_id'         => $roleId,
                'organization_id' => $this->user['organization_id'] ?? null,
                'institution_id'  => $assignedInstitutionId ?: null,
            ]);

            $this->logAudit('create', 'user', $userId);
            $this->db->commit();

            $this->redirectWith(url('users'), 'success', 'User created successfully.');
        } catch (\Exception $e) {
            $this->db->rollBack();
            appLog("User creation failed: " . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create user. Please try again.'], $data);
        }
    }

    /**
     * Show user details
     */
    public function show(string $id): void
    {
        $this->authorize('users.view');

        $user = $this->userModel->findWithRole((int)$id);
        if (!$user) {
            $this->redirectWith(url('users'), 'error', 'User not found.');
            return;
        }

        // Role visuals (color/icon added by migration 19_rbac_enterprise.sql)
        $user['role_color'] = 'secondary';
        $user['role_icon']  = 'user';
        if ($user['role_id'] ?? null) {
            try {
                $this->db->query("SELECT color, icon FROM roles WHERE id = ?", [$user['role_id']]);
                $rv = $this->db->fetch();
                $user['role_color'] = $rv['color'] ?? 'secondary';
                $user['role_icon']  = $rv['icon']  ?? 'user';
            } catch (\Exception $e) { /* columns not yet migrated */ }
        }

        $userInstitutions = $this->userModel->getUserInstitutions((int)$id);

        // Detailed permissions with override info
        $userPermissions   = [];
        $deniedPermissions = [];
        try {
            $permData = $this->userModel->getUserPermissionsDetailed((int)$id);
            $userPermissions   = $permData['permissions'];
            $deniedPermissions = $permData['denied'];
        } catch (\Exception $e) { /* overrides table not yet migrated */ }

        $userOverrideCount = 0;
        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM user_permission_overrides WHERE user_id = ? AND (expires_at IS NULL OR expires_at >= CURDATE())",
                [(int)$id]
            );
            $userOverrideCount = (int)($this->db->fetch()['cnt'] ?? 0);
        } catch (\Exception $e) { /* overrides table not yet migrated */ }

        $this->db->query(
            "SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 30",
            [(int)$id]
        );
        $auditLogs = $this->db->fetchAll();

        $this->view('users.show', [
            'pageTitle'         => 'User Details',
            'user'              => $user,
            'userInstitutions'  => $userInstitutions,
            'userPermissions'   => $userPermissions,
            'deniedPermissions' => $deniedPermissions,
            'userOverrideCount' => $userOverrideCount,
            'auditLogs'         => $auditLogs,
        ]);
    }

    /**
     * Per-user permission overrides page
     */
    public function permissions(string $id): void
    {
        $this->authorize('users.manage_permissions');

        $user = $this->userModel->findWithRole((int)$id);
        if (!$user) {
            $this->redirectWith(url('users'), 'error', 'User not found.');
            return;
        }

        // Role visuals (color/icon added by migration 19_rbac_enterprise.sql)
        $user['role_color'] = 'secondary';
        $user['role_icon']  = 'user';
        if ($user['role_id'] ?? null) {
            try {
                $this->db->query("SELECT color, icon FROM roles WHERE id = ?", [$user['role_id']]);
                $rv = $this->db->fetch();
                $user['role_color'] = $rv['color'] ?? 'secondary';
                $user['role_icon']  = $rv['icon']  ?? 'user';
            } catch (\Exception $e) { /* columns not yet migrated */ }
        }

        // All permissions grouped by module
        $allPermissions = $this->roleModel->getAllPermissionsGrouped();

        // Role permissions
        $rolePermissions = [];
        if ($user['role_id'] ?? null) {
            $this->db->query(
                "SELECT p.id, p.name, p.slug, p.module FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ?",
                [$user['role_id']]
            );
            $rolePermissions = $this->db->fetchAll();
        }

        // Current overrides (requires migration 19_rbac_enterprise.sql)
        $overrides = [];
        try {
            $this->db->query(
                "SELECT * FROM user_permission_overrides WHERE user_id = ? AND (expires_at IS NULL OR expires_at >= CURDATE())",
                [(int)$id]
            );
            $overrides = $this->db->fetchAll();
        } catch (\Exception $e) { /* table not yet migrated */ }

        $this->view('users.permissions', [
            'pageTitle'       => 'Permission Overrides',
            'user'            => $user,
            'allPermissions'  => $allPermissions,
            'rolePermissions' => $rolePermissions,
            'overrides'       => $overrides,
        ]);
    }

    /**
     * Save per-user permission overrides (AJAX)
     */
    public function savePermissions(string $id): void
    {
        $this->authorize('users.manage_permissions');

        $user = $this->userModel->find((int)$id);
        if (!$user) {
            $this->json(['status' => 'error', 'message' => 'User not found.'], 404);
            return;
        }

        $overridesInput = (array)($_POST['overrides'] ?? []);

        try {
            $this->db->beginTransaction();

            // Remove all existing overrides for this user
            $this->db->delete('user_permission_overrides', 'user_id = ?', [(int)$id]);

            $count = 0;
            foreach ($overridesInput as $permId => $data) {
                $type = $data['type'] ?? 'none';
                if ($type === 'none') continue;

                $this->db->insert('user_permission_overrides', [
                    'user_id'       => (int)$id,
                    'permission_id' => (int)$permId,
                    'type'          => $type,
                    'reason'        => trim($data['reason'] ?? '') ?: null,
                    'expires_at'    => !empty($data['expires_at']) ? $data['expires_at'] : null,
                    'granted_by'    => $this->user['id'] ?? null,
                ]);
                $count++;
            }

            $this->logAudit('update_permissions', 'user', (int)$id);
            $this->db->commit();

            $this->json(['status' => 'success', 'message' => "$count override(s) saved successfully."]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['status' => 'error', 'message' => 'Failed to save overrides.'], 500);
        }
    }

    /**
     * Show edit form
     */
    public function edit(string $id): void
    {
        $this->authorize('users.edit');

        $user = $this->userModel->findWithRole((int)$id);
        if (!$user) {
            $this->redirectWith(url('users'), 'error', 'User not found.');
            return;
        }

        $roles = $this->roleModel->getAllRoles();
        $institutions = session('user_institutions', []);

        $this->view('users.edit', [
            'pageTitle'    => 'Edit User',
            'user'         => $user,
            'roles'        => $roles,
            'institutions' => $institutions,
        ]);
    }

    /**
     * Update user
     */
    public function update(string $id): void
    {
        $this->authorize('users.edit');

        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired.']);
            return;
        }

        $user = $this->userModel->find((int)$id);
        if (!$user) {
            $this->redirectWith(url('users'), 'error', 'User not found.');
            return;
        }

        $data = $this->postData(['employee_id', 'first_name', 'last_name', 'phone']);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'last_name'  => 'required|max:100',
            'phone'      => 'phone',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        // Handle password change (optional)
        $newPassword = $_POST['password'] ?? '';
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                $this->backWithErrors(['Password must be at least 8 characters.'], $data);
                return;
            }
            $data['password'] = $this->userModel->hashPassword($newPassword);
        }

        // Handle avatar upload
        $avatar = $this->uploadFile('avatar', 'avatars');
        if ($avatar) {
            $data['avatar'] = $avatar['file_path'];
        }

        try {
            $this->db->beginTransaction();

            $this->userModel->update((int)$id, $data);

            // Update role assignment if provided
            $roleId = (int)($_POST['role_id'] ?? 0);
            $assignedInstitutionId = (int)($_POST['institution_id'] ?? 0);

            if ($roleId) {
                $this->db->delete('user_roles', 'user_id = ?', [(int)$id]);
                $this->db->insert('user_roles', [
                    'user_id'         => (int)$id,
                    'role_id'         => $roleId,
                    'organization_id' => $this->user['organization_id'] ?? null,
                    'institution_id'  => $assignedInstitutionId ?: null,
                ]);
            }

            $this->logAudit('update', 'user', (int)$id, $user, $data);
            $this->db->commit();

            $this->redirectWith(url('users'), 'success', 'User updated successfully.');
        } catch (\Exception $e) {
            $this->db->rollBack();
            appLog("User update failed: " . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to update user.'], $data);
        }
    }

    /**
     * Delete user
     */
    public function destroy(string $id): void
    {
        $this->authorize('users.delete');

        if ((int)$id === ($this->user['id'] ?? 0)) {
            $this->redirectWith(url('users'), 'error', 'You cannot delete your own account.');
            return;
        }

        $user = $this->userModel->find((int)$id);
        if (!$user) {
            $this->redirectWith(url('users'), 'error', 'User not found.');
            return;
        }

        try {
            $this->db->beginTransaction();
            $this->db->delete('user_roles', 'user_id = ?', [(int)$id]);
            $this->userModel->update((int)$id, ['is_active' => 0]);
            $this->logAudit('delete', 'user', (int)$id);
            $this->db->commit();

            $this->redirectWith(url('users'), 'success', 'User deactivated successfully.');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->redirectWith(url('users'), 'error', 'Failed to delete user.');
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(string $id): void
    {
        $this->authorize('users.edit');

        $user = $this->userModel->find((int)$id);
        if (!$user) {
            if (isAjax()) {
                $this->error('User not found.', 404);
            }
            $this->redirectWith(url('users'), 'error', 'User not found.');
            return;
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->userModel->update((int)$id, ['is_active' => $newStatus]);
        $this->logAudit('toggle_status', 'user', (int)$id);

        if (isAjax()) {
            $this->success($newStatus ? 'User activated.' : 'User deactivated.');
            return;
        }

        $msg = $newStatus ? 'User activated successfully.' : 'User deactivated successfully.';
        $this->redirectWith(url('users'), 'success', $msg);
    }
}
