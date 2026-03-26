<?php
namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(): bool
    {
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            if (!verifyCsrf()) {
                if (isAjax()) {
                    jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 419);
                }
                flash('error', 'Session expired. Please try again.');
                back();
                return false;
            }
        }
        return true;
    }
}
