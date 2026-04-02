<?php
namespace App\Middleware;

use Core\Session\Session;

class LmsAuthMiddleware
{
    public function handle(): void
    {
        $session = Session::getInstance();

        if (!$session->has('lms_user')) {
            $intended = $_SERVER['REQUEST_URI'] ?? '';
            if ($intended && $intended !== '/elms/login') {
                $session->set('lms_intended', $intended);
            }
            redirect(url('elms/login'));
            exit;
        }

        // Refresh last_active timestamp (non-blocking: every 5 min)
        $lastActive = $session->get('lms_last_active', 0);
        if (time() - $lastActive > 300) {
            $session->set('lms_last_active', time());
            try {
                $user = $session->get('lms_user');
                db()->query(
                    "UPDATE lms_users SET last_active_at = NOW() WHERE id = ?",
                    [$user['id']]
                );
            } catch (\Throwable $e) {}
        }
    }
}
