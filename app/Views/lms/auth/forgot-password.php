<div class="lms-form-eyebrow"><i class="fas fa-arrow-left me-1"></i><a href="<?= url('elms/login') ?>" style="color:var(--lms-primary);text-decoration:none">Back to Sign In</a></div>
<h2 class="lms-form-title">Reset Password</h2>
<p class="lms-form-subtitle">Enter your email and we'll send a reset link to your inbox.</p>

<form method="POST" action="<?= url('elms/forgot-password') ?>" class="lms-form">
    <?= csrfField() ?>
    <div class="mb-4">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="you@institution.edu" autofocus required autocomplete="email">
        </div>
    </div>
    <button type="submit" class="btn-lms-signin">
        <i class="fas fa-paper-plane me-1"></i>Send Reset Link
    </button>
</form>
