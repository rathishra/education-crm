<?php
namespace App\Controllers\Portal\Auth;

use App\Controllers\Portal\PortalBaseController;

class PortalAuthController extends PortalBaseController
{
    // ================================================================
    // SHOW LOGIN
    // ================================================================

    public function showLogin(): void
    {
        $pageTitle = 'Student Login';
        $this->view('portal/auth/login', compact('pageTitle'), 'portal_auth');
    }

    // ================================================================
    // LOGIN
    // ================================================================

    public function login(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired. Please try again.']);
            redirect(url('portal/student/login'));
            return;
        }

        $login    = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$login || !$password) {
            flash('errors', ['Student ID / email and password are required.']);
            redirect(url('portal/student/login'));
            return;
        }

        // Rate limiting
        $attempts    = $this->session->get('portal_login_attempts', 0);
        $lastAttempt = $this->session->get('portal_login_last', 0);
        if ($attempts >= 5 && (time() - $lastAttempt) < 300) {
            flash('errors', ['Too many login attempts. Please wait 5 minutes.']);
            redirect(url('portal/student/login'));
            return;
        }

        // Find student
        $this->db->query(
            "SELECT * FROM students
             WHERE (student_id_number = ? OR email = ?)
               AND deleted_at IS NULL
             LIMIT 1",
            [$login, $login]
        );
        $student = $this->db->fetch();

        if (!$student) {
            $this->_failLogin($attempts);
            flash('errors', ['Invalid credentials. Please check your Student ID or email.']);
            redirect(url('portal/student/login'));
            return;
        }

        // Portal enabled?
        if (isset($student['portal_enabled']) && !$student['portal_enabled']) {
            flash('errors', ['Portal access is disabled for your account. Contact the admin.']);
            redirect(url('portal/student/login'));
            return;
        }

        // Active student?
        if (!in_array($student['status'] ?? 'active', ['active', 'alumni'])) {
            flash('errors', ['Your account is inactive. Please contact the administration office.']);
            redirect(url('portal/student/login'));
            return;
        }

        // Password set?
        if (empty($student['password'])) {
            flash('errors', ['Portal access has not been activated for your account. Please contact admin.']);
            redirect(url('portal/student/login'));
            return;
        }

        // Verify password
        if (!password_verify($password, $student['password'])) {
            $this->_failLogin($attempts);
            flash('errors', ['Invalid credentials. Please check your password.']);
            redirect(url('portal/student/login'));
            return;
        }

        // Build session data (strip sensitive fields)
        $sessionData = [
            'id'                => $student['id'],
            'student_id_number' => $student['student_id_number'],
            'first_name'        => $student['first_name'],
            'last_name'         => $student['last_name'] ?? '',
            'email'             => $student['email'] ?? '',
            'phone'             => $student['phone'] ?? $student['mobile_number'] ?? '',
            'institution_id'    => $student['institution_id'],
            'course_id'         => $student['course_id'],
            'batch_id'          => $student['batch_id'],
            'department_id'     => $student['department_id'] ?? null,
            'status'            => $student['status'],
            'photo'             => $student['photo'] ?? $student['profile_photo'] ?? null,
        ];

        $this->session->set('student_portal_user', $sessionData);
        $this->session->remove('portal_login_attempts');
        $this->session->remove('portal_login_last');
        $this->session->regenerate();

        // Update last login
        $this->db->query(
            "UPDATE students SET last_portal_login_at = NOW(), last_portal_login_ip = ? WHERE id = ?",
            [getClientIp(), $student['id']]
        );

        redirect(url('portal/student/dashboard'));
    }

    // ================================================================
    // LOGOUT
    // ================================================================

    public function logout(): void
    {
        $this->session->remove('student_portal_user');
        flash('success', 'You have been logged out successfully.');
        redirect(url('portal/student/login'));
    }

    // ================================================================
    // FORGOT PASSWORD
    // ================================================================

    public function showForgotPassword(): void
    {
        $pageTitle = 'Forgot Password';
        $this->view('portal/auth/forgot-password', compact('pageTitle'), 'portal_auth');
    }

    public function forgotPassword(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired.']);
            redirect(url('portal/student/forgot-password'));
            return;
        }

        $email = trim($_POST['email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('errors', ['Please provide a valid email address.']);
            redirect(url('portal/student/forgot-password'));
            return;
        }

        $this->db->query(
            "SELECT id, first_name, email FROM students WHERE email = ? AND deleted_at IS NULL LIMIT 1",
            [$email]
        );
        $student = $this->db->fetch();

        // Always show success to prevent email enumeration
        if ($student) {
            $token     = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            // Clean up old tokens
            $this->db->query("DELETE FROM students WHERE password_reset_token IS NOT NULL AND password_reset_at < DATE_SUB(NOW(), INTERVAL 2 HOUR)");

            $this->db->query(
                "UPDATE students SET password_reset_token = ?, password_reset_at = NOW() WHERE id = ?",
                [$token, $student['id']]
            );

            // In production: send email with reset link
            // For now, log it (admin can see token and relay to student)
            appLog("Portal password reset for student {$student['email']} — token: {$token}", 'info');
        }

        flash('success', 'If that email is registered, a password reset link has been sent. Please check your inbox.');
        redirect(url('portal/student/forgot-password'));
    }

    // ================================================================
    // RESET PASSWORD
    // ================================================================

    public function showReset(string $token): void
    {
        $this->db->query(
            "SELECT id FROM students
             WHERE password_reset_token = ?
               AND password_reset_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
               AND deleted_at IS NULL
             LIMIT 1",
            [$token]
        );
        if (!$this->db->fetch()) {
            flash('errors', ['This reset link is invalid or has expired. Please request a new one.']);
            redirect(url('portal/student/forgot-password'));
            return;
        }

        $pageTitle = 'Reset Password';
        $this->view('portal/auth/reset-password', compact('pageTitle', 'token'), 'portal_auth');
    }

    public function resetPassword(): void
    {
        if (!verifyCsrf()) {
            flash('errors', ['Session expired.']);
            redirect(url('portal/student/login'));
            return;
        }

        $token    = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        if (!$token) {
            flash('errors', ['Invalid request.']);
            redirect(url('portal/student/login'));
            return;
        }

        if (strlen($password) < 8) {
            flash('errors', ['Password must be at least 8 characters.']);
            redirect(url('portal/student/reset-password/' . $token));
            return;
        }

        if ($password !== $confirm) {
            flash('errors', ['Passwords do not match.']);
            redirect(url('portal/student/reset-password/' . $token));
            return;
        }

        $this->db->query(
            "SELECT id FROM students
             WHERE password_reset_token = ?
               AND password_reset_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
               AND deleted_at IS NULL
             LIMIT 1",
            [$token]
        );
        $student = $this->db->fetch();

        if (!$student) {
            flash('errors', ['Reset link is invalid or expired. Please request a new one.']);
            redirect(url('portal/student/forgot-password'));
            return;
        }

        $this->db->query(
            "UPDATE students SET
                password              = ?,
                password_reset_token  = NULL,
                password_reset_at     = NULL
             WHERE id = ?",
            [password_hash($password, PASSWORD_BCRYPT), $student['id']]
        );

        flash('success', 'Password changed successfully. Please login with your new password.');
        redirect(url('portal/student/login'));
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    private function _failLogin(int $attempts): void
    {
        $this->session->set('portal_login_attempts', $attempts + 1);
        $this->session->set('portal_login_last', time());
    }
}
