<?php
/**
 * Global Helper Functions
 */

use Core\App;
use Core\Session\Session;

/**
 * Get environment variable
 */
function env(string $key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }

    // Convert string booleans
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }

    return $value;
}

/**
 * Get app config
 */
function config(string $key, $default = null)
{
    return App::getInstance()->config($key, $default);
}

/**
 * Get database instance
 */
function db(): Core\Database\Database
{
    return App::getInstance()->db();
}

/**
 * Get session instance
 */
function session(string $key = null, $default = null)
{
    $session = App::getInstance()->session();
    if ($key === null) {
        return $session;
    }
    return $session->get($key, $default);
}

/**
 * Generate URL
 */
function url(string $path = ''): string
{
    $base = rtrim(config('app.url', ''), '/');
    if (empty($path)) {
        return $base;
    }
    return $base . '/' . ltrim($path, '/');
}

/**
 * Asset URL
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Redirect to URL
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Redirect back to previous page
 */
function back(): void
{
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
    redirect($referer);
}

/**
 * Get old input value (for form repopulation)
 */
function old(string $key, $default = '')
{
    return Session::getInstance()->getFlash('old_input.' . $key, $default);
}

/**
 * Set flash message
 */
function flash(string $key, $value): void
{
    Session::getInstance()->setFlash($key, $value);
}

/**
 * Get flash message
 */
function getFlash(string $key, $default = null)
{
    return Session::getInstance()->getFlash($key, $default);
}

/**
 * Check if user is authenticated
 */
function auth(): ?array
{
    return Session::getInstance()->get('user');
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return Session::getInstance()->has('user');
}

/**
 * Student portal: get authenticated student from session
 */
function portalAuth(): ?array
{
    return \Core\Session\Session::getInstance()->get('student_portal_user');
}

/**
 * Student portal: check if a student is logged in
 */
function isPortalLoggedIn(): bool
{
    return \Core\Session\Session::getInstance()->has('student_portal_user');
}

/**
 * LMS: get authenticated LMS user from session (resolved via admin session)
 */
function lmsAuth(): ?array
{
    return \Core\Session\Session::getInstance()->get('_lms_ctx');
}

/**
 * LMS: check if current admin user has a resolved LMS context
 */
function isLmsLoggedIn(): bool
{
    return \Core\Session\Session::getInstance()->has('_lms_ctx');
}

/**
 * LMS: check if current LMS user has permission
 */
function lmsCan(string $permission): bool
{
    $user = lmsAuth();
    if (!$user) return false;
    $perms = \Core\Session\Session::getInstance()->get('lms_permissions', null);
    if ($perms === null) return false; // permissions not loaded yet
    return in_array($permission, $perms, true);
}

/**
 * Get current institution ID
 */
function currentInstitutionId(): ?int
{
    return Session::getInstance()->get('current_institution_id');
}

/**
 * Check permission
 */
function hasPermission(string $permission): bool
{
    $user = auth();
    if (!$user) return false;

    // Super admin has all permissions
    if (($user['role_slug'] ?? '') === 'super_admin') return true;

    $permissions = Session::getInstance()->get('permissions', []);
    return in_array($permission, $permissions);
}

/**
 * Check role
 */
function hasRole(string $role): bool
{
    $user = auth();
    if (!$user) return false;
    return ($user['role_slug'] ?? '') === $role;
}

/**
 * Escape HTML
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Soft delete condition helper
 */
function softDeleteCondition(string $table, string $alias = null): ?string
{
    $alias = trim($alias ?? $table);
    if (db()->hasColumn($table, 'deleted_at')) {
        return "{$alias}.deleted_at IS NULL";
    }
    return null;
}

/**
 * Determine the organization display name column for SELECTs.
 */
function organizationNameColumn(): string
{
    static $column = null;
    if ($column !== null) {
        return $column;
    }

    if (db()->hasColumn('organizations', 'organization_name')) {
        return $column = 'organization_name';
    }

    if (db()->hasColumn('organizations', 'name')) {
        return $column = 'name';
    }

    return $column = 'organization_name';
}

/**
 * Reference the organization display name column with alias.
 */
function organizationNameReference(string $alias = 'o'): string
{
    $column = organizationNameColumn();
    return "{$alias}.{$column}";
}

/**
 * CSRF token generation
 */
function csrfToken(): string
{
    $session = Session::getInstance();
    $token = $session->get('csrf_token');
    if (!$token) {
        $token = bin2hex(random_bytes(32));
        $session->set('csrf_token', $token);
    }
    return $token;
}

/**
 * CSRF hidden field
 */
function csrfField(): string
{
    return '<input type="hidden" name="_token" value="' . csrfToken() . '">';
}

/**
 * Verify CSRF token
 */
function verifyCsrf(): bool
{
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = Session::getInstance()->get('csrf_token', '');
    return hash_equals($sessionToken, $token);
}

/**
 * Format date
 */
function formatDate(?string $date, string $format = null): string
{
    if (empty($date)) return '-';
    $format = $format ?? config('app.date_format', 'd-m-Y');
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime(?string $date): string
{
    if (empty($date)) return '-';
    $dateFormat = config('app.date_format', 'd-m-Y');
    $timeFormat = config('app.time_format', 'h:i A');
    return date($dateFormat . ' ' . $timeFormat, strtotime($date));
}

/**
 * Format currency
 */
function formatCurrency($amount): string
{
    $symbol = config('app.currency_symbol', '₹');
    return $symbol . number_format((float)$amount, 2);
}

/**
 * Generate a unique number with prefix
 */
function generateNumber(string $prefix, string $instCode = ''): string
{
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -4));
    $parts = array_filter([$prefix, $instCode, $date, $random]);
    return implode('-', $parts);
}

/**
 * Sanitize input
 */
function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Get request method
 */
function requestMethod(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

/**
 * Check if request is AJAX
 */
function isAjax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Send JSON response
 */
function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Get client IP
 */
function getClientIp(): string
{
    $headers = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            return trim($ips[0]);
        }
    }
    return '127.0.0.1';
}

/**
 * Log message
 */
function appLog(string $message, string $level = 'info'): void
{
    $logFile = BASE_PATH . '/storage/logs/' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

/**
 * Truncate text
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Get time ago string
 */
function timeAgo(string $datetime): string
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}
