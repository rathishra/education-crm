<?php
namespace App\Middleware;

use Core\Session\Session;

class LmsGuestMiddleware
{
    public function handle(): void
    {
        if (Session::getInstance()->has('lms_user')) {
            redirect(url('elms/dashboard'));
            exit;
        }
    }
}
