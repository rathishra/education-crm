<?php
namespace App\Controllers\Lms;

use Core\Database\Database;
use Core\Session\Session;

abstract class LmsBaseController
{
    protected Database $db;
    protected Session  $session;
    protected ?array   $lmsUser;
    protected ?int     $lmsUserId;
    protected ?int     $institutionId;
    protected string   $userRole;

    public function __construct()
    {
        $this->db            = db();
        $this->session       = Session::getInstance();
        $this->lmsUser       = $this->session->get('lms_user');
        $this->lmsUserId     = $this->lmsUser['id']             ?? null;
        $this->institutionId = $this->lmsUser['institution_id'] ?? null;
        $this->userRole      = $this->lmsUser['role']           ?? 'learner';
    }

    // ── View rendering ────────────────────────────────────────
    protected function view(string $view, array $data = [], string $layout = 'lms'): void
    {
        extract($data);
        $lmsUser = $this->lmsUser;

        $viewFile = BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            echo "View not found: {$view}";
            return;
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    // ── Permission check ──────────────────────────────────────
    protected function can(string $permission): bool
    {
        if (!$this->lmsUserId) return false;

        // Cache permissions in session for performance
        $cached = $this->session->get('lms_permissions', null);
        if ($cached === null) {
            $cached = $this->loadPermissions();
            $this->session->set('lms_permissions', $cached);
        }
        return in_array($permission, $cached, true);
    }

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

    private function loadPermissions(): array
    {
        // Role-level permissions
        $this->db->query(
            "SELECT permission_key FROM lms_role_permissions WHERE role = ?",
            [$this->userRole]
        );
        $rolePerms = array_column($this->db->fetchAll(), 'permission_key');

        // User-level overrides
        $this->db->query(
            "SELECT permission_key, granted FROM lms_user_permissions WHERE lms_user_id = ?",
            [$this->lmsUserId]
        );
        $overrides = $this->db->fetchAll();

        foreach ($overrides as $override) {
            if ($override['granted']) {
                $rolePerms[] = $override['permission_key'];
            } else {
                $rolePerms = array_diff($rolePerms, [$override['permission_key']]);
            }
        }
        return array_unique(array_values($rolePerms));
    }

    // ── Audit logging ─────────────────────────────────────────
    protected function audit(string $action, string $entityType = null, int $entityId = null, array $meta = []): void
    {
        try {
            $this->db->query(
                "INSERT INTO lms_audit_log (lms_user_id, action, entity_type, entity_id, meta, ip_address, user_agent, created_at)
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
        return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest')
            || (($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json');
    }

    protected function isAdmin(): bool     { return $this->userRole === 'lms_admin'; }
    protected function isInstructor(): bool { return in_array($this->userRole, ['lms_admin', 'instructor']); }
    protected function isLearner(): bool    { return $this->userRole === 'learner'; }
}
