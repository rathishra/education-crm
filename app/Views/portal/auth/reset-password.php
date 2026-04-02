<?php /* portal/auth/reset-password.php */ ?>
<div class="portal-form-header">
    <div class="portal-greeting">Student Portal</div>
    <h2>Set New Password</h2>
    <p>Choose a strong password (at least 8 characters).</p>
</div>

<form method="POST" action="<?= url('portal/student/reset-password') ?>" class="portal-form">
    <?= csrfField() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="mb-3">
        <label class="form-label">New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="np" class="form-control" placeholder="At least 8 characters"
                   minlength="8" required autofocus autocomplete="new-password">
            <button type="button" class="btn btn-toggle-pass" onclick="togglePass('np',this)" tabindex="-1">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label">Confirm New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password_confirmation" id="cp" class="form-control" placeholder="Repeat password"
                   minlength="8" required autocomplete="new-password">
            <button type="button" class="btn btn-toggle-pass" onclick="togglePass('cp',this)" tabindex="-1">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn-portal-signin">
        <i class="fas fa-check"></i>Update Password
    </button>

    <div class="portal-auth-footer">
        <div class="mt-3">
            <a href="<?= url('portal/student/login') ?>" style="color:#059669;text-decoration:none;font-size:0.85rem">
                <i class="fas fa-arrow-left me-1"></i>Back to Login
            </a>
        </div>
    </div>
</form>

<script>
function togglePass(id, btn) {
    const inp = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye'; }
    else { inp.type = 'password'; icon.className = 'fas fa-eye-slash'; }
}
</script>
