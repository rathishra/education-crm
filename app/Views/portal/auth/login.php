<?php /* portal/auth/login.php — rendered inside portal_auth layout */ ?>
<div class="portal-form-header">
    <div class="portal-greeting">Student Portal</div>
    <h2>Welcome back!</h2>
    <p>Enter your Student ID (or email) and password to access your portal.</p>
</div>

<form method="POST" action="<?= url('portal/student/login') ?>" class="portal-form" autocomplete="on">
    <?= csrfField() ?>

    <div class="mb-3">
        <label class="form-label">Student ID / Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
            <input type="text" name="login" class="form-control" placeholder="e.g. STU-GEN-2024-0001 or email"
                   value=""
                   autofocus required autocomplete="username">
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0">Password</label>
            <a href="<?= url('portal/student/forgot-password') ?>" class="text-decoration-none" style="font-size:0.78rem;color:#059669">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="portalPassword" class="form-control" placeholder="Your password"
                   required autocomplete="current-password">
            <button type="button" class="btn btn-toggle-pass" onclick="togglePass('portalPassword',this)" tabindex="-1">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <div class="mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="rememberMe">
            <label class="form-check-label" for="rememberMe" style="font-size:0.8rem;color:#64748b">Keep me signed in</label>
        </div>
    </div>

    <button type="submit" class="btn-portal-signin">
        <i class="fas fa-sign-in-alt"></i>Sign In to Portal
    </button>

    <div class="portal-auth-footer">
        <div class="mt-3 p-3 rounded-3 text-start" style="background:#f0fdf4;border:1px solid #bbf7d0">
            <div class="fw-semibold mb-1" style="font-size:0.78rem;color:#065f46"><i class="fas fa-info-circle me-1"></i>First Time?</div>
            <div style="font-size:0.75rem;color:#047857">Contact the administration office to activate your student portal access and set your password.</div>
        </div>
        <div class="mt-3" style="color:#94a3b8;font-size:0.75rem">
            Are you an admin? <a href="<?= url('login') ?>" style="color:#059669">Admin Login</a>
        </div>
    </div>
</form>

<script>
function togglePass(id, btn) {
    const inp = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'fas fa-eye';
    } else {
        inp.type = 'password';
        icon.className = 'fas fa-eye-slash';
    }
}
</script>
