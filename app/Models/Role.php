<?php
namespace App\Models;

class Role extends BaseModel
{
    protected string $table = 'roles';

    /**
     * Get all roles
     */
    public function getAllRoles(): array
    {
        $this->db->query("SELECT * FROM roles ORDER BY level, name");
        return $this->db->fetchAll();
    }

    /**
     * Get role with permissions
     */
    public function findWithPermissions(int $id): ?array
    {
        $role = $this->find($id);
        if (!$role) return null;

        $this->db->query(
            "SELECT p.* FROM permissions p
             JOIN role_permissions rp ON rp.permission_id = p.id
             WHERE rp.role_id = ?
             ORDER BY p.module, p.name",
            [$id]
        );
        $role['permissions'] = $this->db->fetchAll();
        return $role;
    }

    /**
     * Get all permissions grouped by module
     */
    public function getAllPermissionsGrouped(): array
    {
        $this->db->query("SELECT * FROM permissions ORDER BY module, name");
        $permissions = $this->db->fetchAll();

        $grouped = [];
        foreach ($permissions as $perm) {
            $grouped[$perm['module']][] = $perm;
        }
        return $grouped;
    }

    /**
     * Sync permissions for a role
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $this->db->delete('role_permissions', 'role_id = ?', [$roleId]);

        foreach ($permissionIds as $permId) {
            $this->db->insert('role_permissions', [
                'role_id'       => $roleId,
                'permission_id' => (int)$permId,
            ]);
        }
    }

    /**
     * Get roles as select options
     */
    public function getSelectOptions(?int $maxLevel = null): array
    {
        $sql = "SELECT id, name FROM roles";
        $params = [];

        if ($maxLevel !== null) {
            $sql .= " WHERE level >= ?";
            $params[] = $maxLevel;
        }

        $sql .= " ORDER BY level, name";
        $this->db->query($sql, $params);
        $results = $this->db->fetchAll();

        $options = [];
        foreach ($results as $row) {
            $options[$row['id']] = $row['name'];
        }
        return $options;
    }
}
