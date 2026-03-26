<div class="card shadow-lg border-0 rounded-lg">
    <div class="card-header bg-white text-center py-4">
        <h3 class="text-primary mb-0">
            <i class="fas fa-graduation-cap me-2"></i>
            <?= e(config('app.name', 'Education CRM')) ?>
        </h3>
        <p class="text-muted mb-0 mt-2">Sign in to your account</p>
    </div>
    <div class="card-body p-4">
        <?php
        $flashErrors = getFlash('errors');
        if (!empty($flashErrors)):
        ?>
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                    <?php foreach ($flashErrors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('login') ?>" id="loginForm">
            <?= csrfField() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= e(old('email')) ?>" placeholder="admin@educrm.com" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Enter password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <a href="<?= url('forgot-password') ?>" class="text-decoration-none small">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2" id="loginBtn">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </button>
        </form>
    </div>
    <div class="card-footer bg-white text-center py-3">
        <small class="text-muted">Multi-Institution Education CRM &copy; <?= date('Y') ?></small>
    </div>
</div>

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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing in...';
});
</script>
