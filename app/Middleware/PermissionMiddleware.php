<?php
namespace App\Middleware;

class PermissionMiddleware
{
    private string $permission;

    public function __construct(string $permission = '')
    {
        $this->permission = $permission;
    }

    public function handle(): bool
    {
        if (!isLoggedIn()) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            redirect(url('login'));
            return false;
        }

        if (!empty($this->permission) && !hasPermission($this->permission)) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Forbidden'], 403);
            }
            http_response_code(403);
            require BASE_PATH . '/app/Views/errors/403.php';
            return false;
        }

        return true;
    }
}
