<div class="card shadow-lg border-0 rounded-lg">
    <div class="card-header bg-white text-center py-4">
        <h3 class="text-primary mb-0">
            <i class="fas fa-key me-2"></i>Forgot Password
        </h3>
        <p class="text-muted mb-0 mt-2">Enter your email to reset password</p>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="<?= url('forgot-password') ?>">
            <?= csrfField() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= e(old('email')) ?>" placeholder="Enter your email" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
            </button>
        </form>
    </div>
    <div class="card-footer bg-white text-center py-3">
        <a href="<?= url('login') ?>" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Back to Login
        </a>
    </div>
</div>
