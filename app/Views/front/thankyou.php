<?php $pageTitle = $pageTitle ?? 'Application Submitted'; ?>
<div class="card shadow-sm">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <i class="fas fa-check-circle text-success" style="font-size:48px;"></i>
        </div>
        <h3>Thank you for applying!</h3>
        <p class="text-muted">We have received your application. Our admissions team will contact you soon.</p>
        <a href="<?= url('apply') ?>" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i>Submit another application</a>
    </div>
</div>
