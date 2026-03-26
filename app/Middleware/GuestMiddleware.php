<?php
namespace App\Middleware;

class GuestMiddleware
{
    public function handle(): bool
    {
        if (isLoggedIn()) {
            redirect(url('dashboard'));
            return false;
        }
        return true;
    }
}
