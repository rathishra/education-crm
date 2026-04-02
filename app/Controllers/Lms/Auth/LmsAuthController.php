<?php
namespace App\Controllers\Lms\Auth;

use App\Controllers\Lms\LmsBaseController;

class LmsAuthController extends LmsBaseController
{
    public function showLogin(): void
    {
        $pageTitle = 'LMS Sign In';
        $this->view('lms/auth/login', compact('pageTitle'), 'lms_auth');
    }

    public function login(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired. Please try again.']);
            redirect(url('elms/login'));
            return;
        }

        $email    = trim(strtolower($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        if (!$email || !$password) {
            flash('errors', ['Email and password are required.']);
            redirect(url('elms/login'));
            return;
        }

        // Rate limiting
        $attempts = $this->session->get('lms_login_attempts', 0);
        $lastAt   = $this->session->get('lms_login_last', 0);
        if ($attempts >= 5 && (time() - $lastAt) < 300) {
            flash('errors', ['Too many login attempts. Please wait 5 minutes.']);
            redirect(url('elms/login'));
            return;
        }

        // Look up user
        $this->db->query(
            "SELECT * FROM lms_users
             WHERE email = ? AND institution_id = ? AND deleted_at IS NULL
             LIMIT 1",
            [$email, $this->_resolveInstitution()]
        );
        $user = $this->db->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $this->session->set('lms_login_attempts', $attempts + 1);
            $this->session->set('lms_login_last', time());
            flash('errors', ['Invalid email or password.']);
            redirect(url('elms/login'));
            return;
        }

        if ($user['status'] !== 'active') {
            $statusMsg = match($user['status']) {
                'pending'   => 'Your account is pending email verification.',
                'inactive'  => 'Your account is inactive. Please contact the administrator.',
                'suspended' => 'Your account has been suspended.',
                default     => 'Account access is restricted.',
            };
            flash('errors', [$statusMsg]);
            redirect(url('elms/login'));
            return;
        }

        // Establish session
        $this->_createSession($user, $remember);

        // Clear rate limit
        $this->session->remove('lms_login_attempts');
        $this->session->remove('lms_login_last');

        // Audit
        $this->db->query(
            "UPDATE lms_users SET last_login_at = NOW(), last_login_ip = ?, login_count = login_count + 1, last_active_at = NOW() WHERE id = ?",
            [$_SERVER['REMOTE_ADDR'] ?? null, $user['id']]
        );
        $this->audit('login', 'lms_user', $user['id']);

        // Redirect to intended or dashboard
        $intended = $this->session->get('lms_intended', url('elms/dashboard'));
        $this->session->remove('lms_intended');
        redirect($intended);
    }

    public function logout(): void
    {
        $userId = $this->lmsUserId;
        if ($userId) {
            $this->audit('logout', 'lms_user', $userId);
            try {
                $this->db->query("DELETE FROM lms_sessions WHERE lms_user_id = ?", [$userId]);
            } catch (\Throwable $e) {}
        }

        $this->session->remove('lms_user');
        $this->session->remove('lms_permissions');
        $this->session->remove('lms_last_active');
        flash('success', 'You have been signed out.');
        redirect(url('elms/login'));
    }

    public function showForgotPassword(): void
    {
        $pageTitle = 'Forgot Password';
        $this->view('lms/auth/forgot-password', compact('pageTitle'), 'lms_auth');
    }

    public function forgotPassword(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired.']);
            redirect(url('elms/forgot-password'));
            return;
        }

        $email = trim(strtolower($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('errors', ['Please enter a valid email address.']);
            redirect(url('elms/forgot-password'));
            return;
        }

        $this->db->query(
            "SELECT id, first_name FROM lms_users WHERE email = ? AND deleted_at IS NULL LIMIT 1",
            [$email]
        );
        $user = $this->db->fetch();

        if ($user) {
            $token     = bin2hex(random_bytes(32));
            $this->db->query(
                "UPDATE lms_users SET reset_token = ?, reset_token_at = NOW() WHERE id = ?",
                [$token, $user['id']]
            );
            appLog("LMS password reset for {$email} — token: {$token}", 'info');
        }

        flash('success', 'If that email is registered, a reset link has been sent.');
        redirect(url('elms/forgot-password'));
    }

    public function showResetPassword(string $token): void
    {
        $this->db->query(
            "SELECT id FROM lms_users WHERE reset_token = ? AND reset_token_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND deleted_at IS NULL LIMIT 1",
            [$token]
        );
        if (!$this->db->fetch()) {
            flash('errors', ['Reset link is invalid or expired.']);
            redirect(url('elms/forgot-password'));
            return;
        }
        $pageTitle = 'Reset Password';
        $this->view('lms/auth/reset-password', compact('pageTitle', 'token'), 'lms_auth');
    }

    public function resetPassword(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired.']);
            redirect(url('elms/login'));
            return;
        }

        $token    = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        if (strlen($password) < 8) {
            flash('errors', ['Password must be at least 8 characters.']);
            redirect(url('elms/reset-password/' . $token));
            return;
        }
        if ($password !== $confirm) {
            flash('errors', ['Passwords do not match.']);
            redirect(url('elms/reset-password/' . $token));
            return;
        }

        $this->db->query(
            "SELECT id FROM lms_users WHERE reset_token = ? AND reset_token_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND deleted_at IS NULL LIMIT 1",
            [$token]
        );
        $user = $this->db->fetch();
        if (!$user) {
            flash('errors', ['Reset link expired. Please request a new one.']);
            redirect(url('elms/forgot-password'));
            return;
        }

        $this->db->query(
            "UPDATE lms_users SET password = ?, reset_token = NULL, reset_token_at = NULL WHERE id = ?",
            [password_hash($password, PASSWORD_BCRYPT), $user['id']]
        );

        flash('success', 'Password updated. Please sign in with your new password.');
        redirect(url('elms/login'));
    }

    // ── Account Registration (for learner self-sign-up) ───────
    public function showRegister(): void
    {
        $pageTitle = 'Create Account';
        $this->view('lms/auth/register', compact('pageTitle'), 'lms_auth');
    }

    public function register(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired.']);
            redirect(url('elms/register'));
            return;
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name']  ?? '');
        $email     = trim(strtolower($_POST['email'] ?? ''));
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['password_confirmation'] ?? '';

        $errors = [];
        if (!$firstName)                           $errors[] = 'First name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email required.';
        if (strlen($password) < 8)                 $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)                $errors[] = 'Passwords do not match.';

        if ($errors) {
            flash('errors', $errors);
            redirect(url('elms/register'));
            return;
        }

        $instId = $this->_resolveInstitution();

        // Duplicate check
        $this->db->query(
            "SELECT id FROM lms_users WHERE email = ? AND institution_id = ? LIMIT 1",
            [$email, $instId]
        );
        if ($this->db->fetch()) {
            flash('errors', ['An account with this email already exists.']);
            redirect(url('elms/register'));
            return;
        }

        $token = bin2hex(random_bytes(16));
        $this->db->query(
            "INSERT INTO lms_users (institution_id, email, password, first_name, last_name, role, status, verification_token, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 'learner', 'active', ?, NOW(), NOW())",
            [$instId, $email, password_hash($password, PASSWORD_BCRYPT), $firstName, $lastName, $token]
        );

        flash('success', 'Account created! You can now sign in.');
        redirect(url('elms/login'));
    }

    // ── Private helpers ───────────────────────────────────────
    private function _createSession(array $user, bool $remember = false): void
    {
        $sessionData = [
            'id'             => $user['id'],
            'institution_id' => $user['institution_id'],
            'email'          => $user['email'],
            'first_name'     => $user['first_name'],
            'last_name'      => $user['last_name'],
            'display_name'   => $user['display_name'] ?: trim($user['first_name'] . ' ' . $user['last_name']),
            'role'           => $user['role'],
            'avatar'         => $user['avatar'],
            'xp_points'      => $user['xp_points'],
            'level'          => $user['level'],
        ];

        $this->session->set('lms_user', $sessionData);
        $this->session->set('lms_last_active', time());
        $this->session->remove('lms_permissions'); // force permission reload
        $this->session->regenerate();

        // Persist session record
        try {
            $sessionId = session_id();
            $this->db->query(
                "INSERT INTO lms_sessions (id, lms_user_id, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE last_active = NOW()",
                [$sessionId, $user['id'], $_SERVER['REMOTE_ADDR'] ?? null, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)]
            );
        } catch (\Throwable $e) {}
    }

    private function _resolveInstitution(): int
    {
        // Use first institution for now; can extend for multi-tenant subdomain routing
        try {
            $this->db->query("SELECT id FROM institutions ORDER BY id LIMIT 1");
            $row = $this->db->fetch();
            return (int)($row['id'] ?? 1);
        } catch (\Throwable $e) {
            return 1;
        }
    }
}
