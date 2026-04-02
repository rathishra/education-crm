<div class="lms-form-eyebrow"><i class="fas fa-key me-1"></i>Password Reset</div>
<h2 class="lms-form-title">New Password</h2>
<p class="lms-form-subtitle">Choose a strong password with at least 8 characters.</p>

<form method="POST" action="<?= url('elms/reset-password') ?>" class="lms-form">
    <?= csrfField() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="mb-3">
        <label class="form-label">New Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="np1" class="form-control" placeholder="Min 8 characters" required autofocus>
            <button type="button" class="btn btn-toggle-pass" onclick="toggleLmsPass('np1',this)" tabindex="-1"><i class="fas fa-eye-slash"></i></button>
        </div>
    </div>
    <div class="mb-4">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password_confirmation" id="np2" class="form-control" placeholder="Re-enter password" required>
            <button type="button" class="btn btn-toggle-pass" onclick="toggleLmsPass('np2',this)" tabindex="-1"><i class="fas fa-eye-slash"></i></button>
        </div>
    </div>
    <button type="submit" class="btn-lms-signin"><i class="fas fa-save me-1"></i>Update Password</button>
</form>
