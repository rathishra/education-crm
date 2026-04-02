<?php
namespace App\Middleware;

class PortalAuthMiddleware
{
    public function handle(): bool
    {
        if (!isPortalLoggedIn()) {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Please login to continue.'], 401);
            }
            flash('error', 'Please login to continue.');
            redirect(url('portal/student/login'));
            return false;
        }
        return true;
    }
}
