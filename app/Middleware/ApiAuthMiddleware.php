<?php
namespace App\Middleware;

class ApiAuthMiddleware
{
    public function handle(): bool
    {
        $token = $this->getBearerToken();

        if (!$token) {
            jsonResponse(['success' => false, 'message' => 'No token provided'], 401);
            return false;
        }

        // Validate API token against users table or a dedicated api_tokens table
        $db = db();
        $db->query("SELECT u.*, ur.role_id, ur.institution_id, r.slug as role_slug
                     FROM users u
                     JOIN user_sessions us ON us.user_id = u.id
                     JOIN user_roles ur ON ur.user_id = u.id
                     JOIN roles r ON r.id = ur.role_id
                     WHERE us.id = ? AND u.is_active = 1
                     LIMIT 1", [$token]);

        $user = $db->fetch();

        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'Invalid token'], 401);
            return false;
        }

        // Set user in session for this request
        $_SESSION['user'] = $user;
        $_SESSION['current_institution_id'] = $user['institution_id'];

        return true;
    }

    private function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        return $_GET['api_token'] ?? null;
    }
}
