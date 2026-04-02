<?php
namespace App\Middleware;

class PortalGuestMiddleware
{
    public function handle(): bool
    {
        if (isPortalLoggedIn()) {
            redirect(url('portal/student/dashboard'));
            return false;
        }
        return true;
    }
}
