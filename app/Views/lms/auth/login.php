<div class="lms-form-eyebrow"><i class="fas fa-cube me-1"></i>Enterprise LMS</div>
<h2 class="lms-form-title">Welcome back</h2>
<p class="lms-form-subtitle">Sign in to continue to your learning dashboard.</p>

<div class="lms-role-chips">
    <span class="lms-role-chip chip-admin"><i class="fas fa-shield-alt me-1"></i>Admin</span>
    <span class="lms-role-chip chip-instructor"><i class="fas fa-chalkboard-teacher me-1"></i>Instructor</span>
    <span class="lms-role-chip chip-learner"><i class="fas fa-user-graduate me-1"></i>Learner</span>
</div>

<form method="POST" action="<?= url('elms/login') ?>" class="lms-form" autocomplete="on">
    <?= csrfField() ?>

    <div class="mb-3">
        <label class="form-label">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="you@institution.edu"
                   autofocus required autocomplete="email">
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0">Password</label>
            <a href="<?= url('elms/forgot-password') ?>" class="text-decoration-none" style="font-size:.75rem;color:var(--lms-primary)">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="lmsPassword" class="form-control" placeholder="Your password"
                   required autocomplete="current-password">
            <button type="button" class="btn btn-toggle-pass" onclick="toggleLmsPass('lmsPassword',this)" tabindex="-1">
                <i class="fas fa-eye-slash"></i>
            </button>
        </div>
    </div>

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="rememberLms">
            <label class="form-check-label" for="rememberLms" style="font-size:.78rem;color:#64748b">Keep me signed in</label>
        </div>
    </div>

    <button type="submit" class="btn-lms-signin mb-3">
        <i class="fas fa-sign-in-alt"></i>Sign In to LMS
    </button>

    <div class="lms-divider">or</div>

    <div class="text-center" style="font-size:.8rem;color:#64748b">
        New learner?
        <a href="<?= url('elms/register') ?>" style="color:var(--lms-primary);font-weight:600;text-decoration:none">Create an account</a>
    </div>

    <div class="mt-4 p-3 rounded-3" style="background:#f5f3ff;border:1px solid #ddd6fe;font-size:.75rem;color:#5b21b6">
        <i class="fas fa-info-circle me-1"></i>
        Students can also sign in using their <strong>Student Portal</strong> credentials.
        Faculty use their <strong>staff email</strong> set up by the administrator.
    </div>
</form>
