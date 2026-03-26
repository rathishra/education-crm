<div class="card shadow-lg border-0 rounded-lg">
    <div class="card-header bg-white text-center py-4">
        <h3 class="text-primary mb-0">
            <i class="fas fa-key me-2"></i>Reset Password
        </h3>
        <p class="text-muted mb-0 mt-2">Enter your new password</p>
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

        <form method="POST" action="<?= url('reset-password') ?>">
            <?= csrfField() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="<?= e($email) ?>" disabled>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password"
                       minlength="8" placeholder="Minimum 8 characters" required>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation"
                       name="password_confirmation" placeholder="Confirm password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">
                <i class="fas fa-save me-2"></i>Reset Password
            </button>
        </form>
    </div>
    <div class="card-footer bg-white text-center py-3">
        <a href="<?= url('login') ?>" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Back to Login
        </a>
    </div>
</div>
