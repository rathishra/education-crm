<div class="auth-form-header">
    <div class="auth-greeting">Welcome back</div>
    <h2>Sign in to your account</h2>
    <p>Enter your credentials to access the dashboard</p>
</div>

<?php
$flashErrors = getFlash('errors');
if (!empty($flashErrors)):
?>
<div class="alert alert-danger py-2 mb-3">
    <i class="fas fa-exclamation-triangle me-1"></i>
    <?php if (count($flashErrors) === 1): ?>
        <?= e($flashErrors[0]) ?>
    <?php else: ?>
        <ul class="mb-0 mt-1 ps-3">
            <?php foreach ($flashErrors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('login') ?>" id="loginForm" class="auth-form">
    <?= csrfField() ?>

    <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope fa-sm"></i></span>
            <input type="email" class="form-control" id="email" name="email"
                   value="<?= e(old('email')) ?>"
                   placeholder="you@institution.edu"
                   required autofocus autocomplete="email">
        </div>
    </div>

    <div class="mb-2">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock fa-sm"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Enter your password"
                   required autocomplete="current-password">
            <button class="btn-toggle-pass" type="button" id="togglePassword" tabindex="-1">
                <i class="fas fa-eye fa-sm"></i>
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember" style="font-size:.825rem;color:#64748b">Keep me signed in</label>
        </div>
        <a href="<?= url('forgot-password') ?>" style="font-size:.825rem;color:#4f46e5;font-weight:600">Forgot password?</a>
    </div>

    <button type="submit" class="btn-signin" id="loginBtn">
        <i class="fas fa-sign-in-alt"></i>
        Sign In
    </button>
</form>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const input = document.getElementById('password');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

document.getElementById('loginForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:1rem;height:1rem;border-width:2px"></span> Signing in...';
});
</script>
