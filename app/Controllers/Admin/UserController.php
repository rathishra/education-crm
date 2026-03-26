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
            'is_active'      => $this->input('status') !== null ? (int)$this->input('status') : null,
            'institution_id' => $this->institutionId,
        ];

        // Super admin and org admin can see all
        if (hasRole('super_admin') || hasRole('org_admin')) {
            unset($filters['institution_id']);
        }

        $users = $this->userModel->getListPaginated($page, 15, $filters);
        $roles = $this->roleModel->getAllRoles();

        $this->view('users.index', [
            'pageTitle' => 'User Management',
            'users'     => $users,
            'roles'     => $roles,
            'filters'   => $filters,
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

        // Get user's institutions
        $userInstitutions = $this->userModel->getUserInstitutions((int)$id);

        // Get recent audit logs for this user
        $this->db->query(
            "SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 20",
            [(int)$id]
        );
        $auditLogs = $this->db->fetchAll();

        $this->view('users.show', [
            'pageTitle'        => 'User Details',
            'user'             => $user,
            'userInstitutions' => $userInstitutions,
            'auditLogs'        => $auditLogs,
        ]);
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
