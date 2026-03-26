<?php
namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\User;

class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show login form
     */
    public function showLogin(): void
    {
        $this->view('auth.login', [
            'pageTitle' => 'Login'
        ], 'auth');
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        // Validate CSRF
        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired. Please try again.']);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        $errors = $this->validate(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required|min:6']
        );

        if (!empty($errors)) {
            $this->session->flashInput(['email' => $email]);
            $this->backWithErrors(array_values($errors));
            return;
        }

        // Rate limiting - simple check
        $attempts = $this->session->get('login_attempts', 0);
        $lastAttempt = $this->session->get('login_last_attempt', 0);

        if ($attempts >= 5 && (time() - $lastAttempt) < 300) {
            $this->backWithErrors(['Too many login attempts. Please wait 5 minutes.']);
            return;
        }

        // Find user
        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            // Increment attempts
            $this->session->set('login_attempts', $attempts + 1);
            $this->session->set('login_last_attempt', time());

            $this->session->flashInput(['email' => $email]);
            $this->backWithErrors(['Invalid email or password.']);

            // Log failed attempt
            appLog("Failed login attempt for: {$email} from " . getClientIp(), 'warning');
            return;
        }

        // Check if user is active
        if (!$user['is_active']) {
            $this->backWithErrors(['Your account has been deactivated. Please contact administrator.']);
            return;
        }

        // Check if password change is required
        if ($user['force_password_change']) {
            $this->session->set('force_password_change_user_id', $user['id']);
            flash('warning', 'You must change your password before continuing.');
            redirect(url('change-password'));
            return;
        }

        // Login successful - set session
        $this->setUserSession($user);

        // Reset login attempts
        $this->session->remove('login_attempts');
        $this->session->remove('login_last_attempt');

        // Update last login
        $this->userModel->updateLastLogin($user['id'], getClientIp());

        // Log audit
        $this->db->insert('audit_logs', [
            'user_id'    => $user['id'],
            'action'     => 'login',
            'model_type' => 'user',
            'model_id'   => $user['id'],
            'ip_address' => getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'url'        => '/login',
        ]);

        // Regenerate session ID for security
        $this->session->regenerate();

        appLog("User logged in: {$user['email']} (ID: {$user['id']})", 'info');

        redirect(url('dashboard'));
    }

    /**
     * Set user session data
     */
    private function setUserSession(array $user): void
    {
        // Remove sensitive data
        unset($user['password'], $user['remember_token']);

        $this->session->set('user', $user);

        // Get user institutions
        $institutions = $this->userModel->getUserInstitutions($user['id']);
        $this->session->set('user_institutions', $institutions);

        // Set current institution
        if (!empty($institutions)) {
            $currentInstId = $institutions[0]['id'];
            $this->session->set('current_institution_id', $currentInstId);
        } elseif ($user['role_slug'] === 'super_admin') {
            // Super admin may not have institution assignment
            $this->session->set('current_institution_id', null);
        }

        // Load permissions
        $permissions = $this->userModel->getUserPermissions(
            $user['id'],
            $this->session->get('current_institution_id')
        );
        $this->session->set('permissions', $permissions);
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        if ($this->user) {
            // Log audit
            $this->db->insert('audit_logs', [
                'user_id'    => $this->user['id'],
                'action'     => 'logout',
                'model_type' => 'user',
                'model_id'   => $this->user['id'],
                'ip_address' => getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'url'        => '/logout',
            ]);

            appLog("User logged out: {$this->user['email']}", 'info');
        }

        $this->session->destroy();
        redirect(url('login'));
    }

    /**
     * Switch institution
     */
    public function switchInstitution(): void
    {
        $institutionId = (int)($_POST['institution_id'] ?? 0);

        // Verify user has access to this institution
        $institutions = $this->session->get('user_institutions', []);
        $valid = false;

        foreach ($institutions as $inst) {
            if ((int)$inst['id'] === $institutionId) {
                $valid = true;
                break;
            }
        }

        if (!$valid && !hasRole('super_admin')) {
            flash('error', 'You do not have access to this institution.');
            back();
            return;
        }

        // Update session
        $this->session->set('current_institution_id', $institutionId);

        // Reload permissions for new institution
        $permissions = $this->userModel->getUserPermissions($this->user['id'], $institutionId);
        $this->session->set('permissions', $permissions);

        // Log audit
        $this->logAudit('switch_institution', 'institution', $institutionId);

        flash('success', 'Institution switched successfully.');
        redirect(url('dashboard'));
    }

    /**
     * Show profile
     */
    public function profile(): void
    {
        $user = $this->userModel->findWithRole($this->user['id']);

        $this->view('auth.profile', [
            'pageTitle' => 'My Profile',
            'user'      => $user,
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(): void
    {
        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired.']);
            return;
        }

        $data = $this->postData(['first_name', 'last_name', 'phone']);

        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'last_name'  => 'required|max:100',
            'phone'      => 'phone',
        ]);

        if (!empty($errors)) {
            $this->backWithErrors(array_values($errors), $data);
            return;
        }

        // Handle avatar upload
        $avatar = $this->uploadFile('avatar', 'avatars');
        if ($avatar) {
            $data['avatar'] = $avatar['file_path'];
        }

        $this->userModel->update($this->user['id'], $data);

        // Update session
        $updatedUser = $this->userModel->findWithRole($this->user['id']);
        unset($updatedUser['password'], $updatedUser['remember_token']);
        $this->session->set('user', $updatedUser);

        $this->logAudit('update_profile', 'user', $this->user['id']);

        $this->redirectWith(url('profile'), 'success', 'Profile updated successfully.');
    }

    /**
     * Change password
     */
    public function changePassword(): void
    {
        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired.']);
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['new_password_confirmation'] ?? '';

        // Validate
        $errors = [];
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required.';
        }
        if (empty($newPassword) || strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New password confirmation does not match.';
        }

        if (!empty($errors)) {
            $this->backWithErrors($errors);
            return;
        }

        // Verify current password
        $user = $this->userModel->find($this->user['id']);
        if (!$this->userModel->verifyPassword($currentPassword, $user['password'])) {
            $this->backWithErrors(['Current password is incorrect.']);
            return;
        }

        // Update password
        $this->userModel->update($this->user['id'], [
            'password'              => $this->userModel->hashPassword($newPassword),
            'force_password_change' => 0,
        ]);

        $this->logAudit('change_password', 'user', $this->user['id']);

        $this->redirectWith(url('profile'), 'success', 'Password changed successfully.');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword(): void
    {
        $this->view('auth.forgot-password', [
            'pageTitle' => 'Forgot Password'
        ], 'auth');
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword(): void
    {
        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired.']);
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->backWithErrors(['Please enter a valid email address.']);
            return;
        }

        // Always show success to prevent email enumeration
        $user = $this->userModel->findByEmail($email);

        if ($user) {
            $token = $this->userModel->createPasswordResetToken($email);

            // In production, send email here
            // For now, log the token
            appLog("Password reset token for {$email}: {$token}", 'info');

            // TODO: Send email with reset link
            // $resetUrl = url("reset-password/{$token}");
            // EmailService::send($email, 'Password Reset', "Click here: {$resetUrl}");
        }

        $this->redirectWith(url('login'), 'success',
            'If the email exists, a password reset link has been sent.');
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(string $token): void
    {
        $email = $this->userModel->verifyResetToken($token);

        if (!$email) {
            $this->redirectWith(url('login'), 'error',
                'Invalid or expired password reset link.');
            return;
        }

        $this->view('auth.reset-password', [
            'pageTitle' => 'Reset Password',
            'token'     => $token,
            'email'     => $email,
        ], 'auth');
    }

    /**
     * Handle reset password
     */
    public function resetPassword(): void
    {
        if (!verifyCsrf()) {
            $this->backWithErrors(['Session expired.']);
            return;
        }

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['password_confirmation'] ?? '';

        // Verify token
        $email = $this->userModel->verifyResetToken($token);
        if (!$email) {
            $this->redirectWith(url('login'), 'error', 'Invalid or expired reset link.');
            return;
        }

        // Validate password
        if (empty($password) || strlen($password) < 8) {
            $this->backWithErrors(['Password must be at least 8 characters.']);
            return;
        }

        if ($password !== $confirmPassword) {
            $this->backWithErrors(['Password confirmation does not match.']);
            return;
        }

        // Update password
        $user = $this->userModel->findByEmail($email);
        if ($user) {
            $this->userModel->update($user['id'], [
                'password'              => $this->userModel->hashPassword($password),
                'force_password_change' => 0,
            ]);

            $this->userModel->deleteResetTokens($email);

            $this->logAudit('reset_password', 'user', $user['id']);
        }

        $this->redirectWith(url('login'), 'success',
            'Password has been reset. Please login with your new password.');
    }
}
