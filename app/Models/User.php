<?php
namespace App\Models;

class User extends BaseModel
{
    protected string $table = 'users';

    /**
     * Find user by email with role info
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT u.*, ur.role_id, ur.organization_id, ur.institution_id,
                       r.name as role_name, r.slug as role_slug, r.level as role_level
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                WHERE u.email = ? AND u.is_active = 1
                LIMIT 1";
        $this->db->query($sql, [$email]);
        return $this->db->fetch();
    }

    /**
     * Get user with full profile
     */
    public function findWithRole(int $id): ?array
    {
        $sql = "SELECT u.*, ur.role_id, ur.organization_id, ur.institution_id,
                       r.name as role_name, r.slug as role_slug, r.level as role_level
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                WHERE u.id = ?
                LIMIT 1";
        $this->db->query($sql, [$id]);
        return $this->db->fetch();
    }

    /**
     * Get all institutions a user has access to
     */
    public function getUserInstitutions(int $userId): array
    {
        $user = $this->findWithRole($userId);
        if (!$user) return [];

        // Super admin: all institutions
        $orgNameRef = organizationNameReference('o');

        if ($user['role_slug'] === 'super_admin') {
            $sql = "SELECT i.id, i.name, i.code, i.type, {$orgNameRef} as organization_name
                    FROM institutions i
                    JOIN organizations o ON o.id = i.organization_id
                    WHERE i.status = 'active'
                    ORDER BY {$orgNameRef}, i.name";
            $this->db->query($sql);
            return $this->db->fetchAll();
        }

        // Org admin: all institutions in their organization
        if ($user['role_slug'] === 'org_admin' && $user['organization_id']) {
            $sql = "SELECT i.id, i.name, i.code, i.type, {$orgNameRef} as organization_name
                    FROM institutions i
                    JOIN organizations o ON o.id = i.organization_id
                    WHERE i.organization_id = ? AND i.status = 'active'
                    ORDER BY i.name";
            $this->db->query($sql, [$user['organization_id']]);
            return $this->db->fetchAll();
        }

        // Other roles: only assigned institutions
        $sql = "SELECT DISTINCT i.id, i.name, i.code, i.type, {$orgNameRef} as organization_name
                FROM user_roles ur
                JOIN institutions i ON i.id = ur.institution_id
                JOIN organizations o ON o.id = i.organization_id
                WHERE ur.user_id = ? AND i.status = 'active'
                ORDER BY i.name";
        $this->db->query($sql, [$userId]);
        return $this->db->fetchAll();
    }

    /**
     * Get permissions for user at a specific institution
     */
    public function getUserPermissions(int $userId, ?int $institutionId = null): array
    {
        $sql = "SELECT DISTINCT p.slug
                FROM user_roles ur
                JOIN role_permissions rp ON rp.role_id = ur.role_id
                JOIN permissions p ON p.id = rp.permission_id
                WHERE ur.user_id = ?";
        $params = [$userId];

        if ($institutionId) {
            $sql .= " AND (ur.institution_id = ? OR ur.institution_id IS NULL)";
            $params[] = $institutionId;
        }

        $this->db->query($sql, $params);
        $results = $this->db->fetchAll();
        return array_column($results, 'slug');
    }

    /**
     * Update last login
     */
    public function updateLastLogin(int $userId, string $ip): void
    {
        $this->db->update('users', [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip,
        ], 'id = ?', [$userId]);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Hash password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Get users list with roles (paginated)
     */
    public function getListPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.employee_id LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search, $search]);
        }

        if (isset($filters['is_active'])) {
            $where .= " AND u.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (!empty($filters['role_id'])) {
            $where .= " AND ur.role_id = ?";
            $params[] = $filters['role_id'];
        }

        if (!empty($filters['institution_id'])) {
            $where .= " AND ur.institution_id = ?";
            $params[] = $filters['institution_id'];
        }

        $sql = "SELECT u.*, r.name as role_name, r.slug as role_slug,
                       i.name as institution_name
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_id = u.id
                LEFT JOIN roles r ON r.id = ur.role_id
                LEFT JOIN institutions i ON i.id = ur.institution_id
                WHERE {$where}
                ORDER BY u.created_at DESC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Create password reset token
     */
    public function createPasswordResetToken(string $email): string
    {
        $token = bin2hex(random_bytes(32));

        // Remove old tokens
        $this->db->delete('password_resets', 'email = ?', [$email]);

        // Insert new token
        $this->db->insert('password_resets', [
            'email' => $email,
            'token' => hash('sha256', $token),
        ]);

        return $token;
    }

    /**
     * Verify password reset token
     */
    public function verifyResetToken(string $token): ?string
    {
        $hashedToken = hash('sha256', $token);
        $this->db->query(
            "SELECT email FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$hashedToken]
        );
        $result = $this->db->fetch();
        return $result ? $result['email'] : null;
    }

    /**
     * Delete password reset tokens for email
     */
    public function deleteResetTokens(string $email): void
    {
        $this->db->delete('password_resets', 'email = ?', [$email]);
    }

    /**
     * Get counselors for an institution
     */
    public function getCounselors(?int $institutionId = null): array
    {
        $sql = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
                FROM users u
                JOIN user_roles ur ON ur.user_id = u.id
                JOIN roles r ON r.id = ur.role_id
                WHERE r.slug IN ('counselor', 'inst_admin', 'org_admin', 'super_admin')
                  AND u.is_active = 1";
        $params = [];

        if ($institutionId) {
            $sql .= " AND (ur.institution_id = ? OR ur.institution_id IS NULL)";
            $params[] = $institutionId;
        }

        $sql .= " ORDER BY u.first_name";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }
}
