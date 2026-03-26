<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!isLoggedIn()) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
            }
            flash('error', 'Please login to continue.');
            redirect(url('login'));
            return false;
        }
        return true;
    }
}
