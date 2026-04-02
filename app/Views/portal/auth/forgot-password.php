<?php /* portal/auth/forgot-password.php */ ?>
<div class="portal-form-header">
    <div class="portal-greeting">Student Portal</div>
    <h2>Forgot Password?</h2>
    <p>Enter your registered email address and we'll send you a reset link.</p>
</div>

<form method="POST" action="<?= url('portal/student/forgot-password') ?>" class="portal-form">
    <?= csrfField() ?>

    <div class="mb-4">
        <label class="form-label">Registered Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="you@example.com"
                   required autofocus>
        </div>
    </div>

    <button type="submit" class="btn-portal-signin">
        <i class="fas fa-paper-plane"></i>Send Reset Link
    </button>

    <div class="portal-auth-footer">
        <div class="mt-3">
            <a href="<?= url('portal/student/login') ?>" style="color:#059669;text-decoration:none;font-size:0.85rem">
                <i class="fas fa-arrow-left me-1"></i>Back to Login
            </a>
        </div>
    </div>
</form>
