<?php
namespace App\Controllers\Lms;

use App\Controllers\BaseController;

abstract class LmsBaseController extends BaseController
{
    protected ?array $lmsUser   = null;
    protected ?int   $lmsUserId = null;
    protected string $userRole  = 'instructor';

    public function __construct()
    {
        parent::__construct();
        $this->_resolveLmsUser();
    }

    // ── Resolve LMS user from admin session ───────────────────────
    private function _resolveLmsUser(): void
    {
        if (!$this->user || !$this->institutionId) return;

        // Use session cache to avoid DB hit on every request
        $cached = $this->session->get('_lms_ctx');
        if ($cached && (int)($cached['staff_user_id'] ?? 0) === (int)$this->user['id']) {
            $this->lmsUser   = $cached;
            $this->lmsUserId = (int)$cached['id'];
            $this->userRole  = $cached['role'];
            $this->_ensurePermissionsLoaded();
            return;
        }

        try {
            // Look up existing lms_users record for this admin user
            $this->db->query(
                "SELECT * FROM lms_users
                 WHERE staff_user_id = ? AND institution_id = ? AND deleted_at IS NULL
                 LIMIT 1",
                [$this->user['id'], $this->institutionId]
            );
            $lmsUser = $this->db->fetch();

            if (!$lmsUser) {
                // Auto-create LMS user linked to this admin account
                $role = str_contains(strtolower($this->user['role_name'] ?? ''), 'admin')
                    ? 'lms_admin'
                    : 'instructor';

                $newId = $this->db->insert('lms_users', [
                    'institution_id'    => $this->institutionId,
                    'staff_user_id'     => $this->user['id'],
                    'email'             => $this->user['email'] ?? '',
                    'password'          => password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT),
                    'first_name'        => $this->user['first_name'] ?? '',
                    'last_name'         => $this->user['last_name'] ?? '',
                    'display_name'      => trim(($this->user['first_name'] ?? '') . ' ' . ($this->user['last_name'] ?? '')),
                    'role'              => $role,
                    'status'            => 'active',
                    'email_verified_at' => date('Y-m-d H:i:s'),
                ]);

                $this->db->query("SELECT * FROM lms_users WHERE id = ?", [$newId]);
                $lmsUser = $this->db->fetch();
            }

            if ($lmsUser) {
                $this->lmsUser   = $lmsUser;
                $this->lmsUserId = (int)$lmsUser['id'];
                $this->userRole  = $lmsUser['role'];
                $this->session->set('_lms_ctx', $lmsUser);
            }

            $this->_ensurePermissionsLoaded();

        } catch (\Throwable $e) {
            // lms_users table may not exist yet — fail silently
        }
    }

    private function _ensurePermissionsLoaded(): void
    {
        if (!$this->lmsUserId) return;
        if ($this->session->get('lms_permissions') === null) {
            $this->session->set('lms_permissions', $this->_loadPermissions());
        }
    }

    private function _loadPermissions(): array
    {
        try {
            $this->db->query(
                "SELECT permission_key FROM lms_role_permissions WHERE role = ?",
                [$this->userRole]
            );
            $perms = array_column($this->db->fetchAll(), 'permission_key');

            $this->db->query(
                "SELECT permission_key, granted FROM lms_user_permissions WHERE lms_user_id = ?",
                [$this->lmsUserId]
            );
            foreach ($this->db->fetchAll() as $override) {
                if ($override['granted']) {
                    $perms[] = $override['permission_key'];
                } else {
                    $perms = array_diff($perms, [$override['permission_key']]);
                }
            }
            return array_unique(array_values($perms));
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ── View rendering — inject $lmsUser into all LMS views ───────
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $data['lmsUser'] = $this->lmsUser;
        parent::view($view, $data, $layout);
    }

    // ── LMS permission check ──────────────────────────────────────
    protected function can(string $permission): bool
    {
        if (!$this->lmsUserId) return false;
        $perms = $this->session->get('lms_permissions', null);
        if ($perms === null) {
            $perms = $this->_loadPermissions();
            $this->session->set('lms_permissions', $perms);
        }
        return in_array($permission, $perms, true);
    }

    // Override BaseController::authorize() with LMS permission check
    protected function authorize(string $permission): void
    {
        if (!$this->can($permission)) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Unauthorized', 'permission' => $permission], 403);
            }
            http_response_code(403);
            $this->view('lms/errors/403', ['pageTitle' => 'Access Denied']);
            exit;
        }
    }

    // ── Audit logging ─────────────────────────────────────────────
    protected function audit(string $action, string $entityType = null, int $entityId = null, array $meta = []): void
    {
        try {
            $this->db->query(
                "INSERT INTO lms_audit_log
                    (lms_user_id, action, entity_type, entity_id, meta, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $this->lmsUserId, $action, $entityType, $entityId,
                    $meta ? json_encode($meta) : null,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                ]
            );
        } catch (\Throwable $e) {}
    }

    protected function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || ($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';
    }

    protected function isAdmin(): bool      { return $this->userRole === 'lms_admin'; }
    protected function isInstructor(): bool { return in_array($this->userRole, ['lms_admin', 'instructor']); }
    protected function isLearner(): bool    { return $this->userRole === 'learner'; }
}
