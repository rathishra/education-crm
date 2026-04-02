<div class="lms-form-eyebrow"><i class="fas fa-user-graduate me-1"></i>New Learner</div>
<h2 class="lms-form-title">Create Account</h2>
<p class="lms-form-subtitle">Join the learning platform and start your journey.</p>

<form method="POST" action="<?= url('elms/register') ?>" class="lms-form">
    <?= csrfField() ?>
    <div class="row g-2 mb-3">
        <div class="col-6">
            <label class="form-label">First Name <span class="text-danger">*</span></label>
            <input type="text" name="first_name" class="form-control" placeholder="First name" required autofocus autocomplete="given-name">
        </div>
        <div class="col-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" placeholder="Last name" autocomplete="family-name">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Email Address <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" name="email" class="form-control" placeholder="you@email.com" required autocomplete="email">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="regPw" class="form-control" placeholder="Min 8 characters" required>
            <button type="button" class="btn btn-toggle-pass" onclick="toggleLmsPass('regPw',this)" tabindex="-1"><i class="fas fa-eye-slash"></i></button>
        </div>
    </div>
    <div class="mb-4">
        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
        </div>
    </div>
    <button type="submit" class="btn-lms-signin mb-3"><i class="fas fa-user-plus me-1"></i>Create Account</button>
    <div class="text-center" style="font-size:.8rem;color:#64748b">
        Already have an account? <a href="<?= url('elms/login') ?>" style="color:var(--lms-primary);font-weight:600;text-decoration:none">Sign In</a>
    </div>
</form>
