<?php
$name   = $name   ?? 'Student';
$number = $number ?? '';
$phone  = $phone  ?? '';
?>

<div class="text-center py-4">
    <!-- Success animation circle -->
    <div class="mx-auto mb-4 d-flex align-items-center justify-content-center"
         style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);
                box-shadow:0 0 0 12px rgba(16,185,129,.12)">
        <i class="fas fa-check fa-2x text-white"></i>
    </div>

    <h2 class="fw-bold mb-1" style="color:#1e293b">Enquiry Submitted!</h2>
    <p class="text-muted mb-4">Thank you for your interest. We've received your enquiry and our counselors will reach out to you shortly.</p>

    <!-- Enquiry reference card -->
    <?php if ($number): ?>
    <div class="card border-0 shadow-sm mx-auto mb-4" style="max-width:420px">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-muted small fw-semibold text-uppercase">Enquiry Reference</span>
                <span class="badge rounded-pill bg-success fs-6 px-3 py-2"><?= e($number) ?></span>
            </div>
            <hr class="my-2">
            <div class="row g-2 text-start small">
                <?php if ($name): ?>
                <div class="col-6">
                    <div class="text-muted">Student Name</div>
                    <div class="fw-semibold"><?= e(trim($name)) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($phone): ?>
                <div class="col-6">
                    <div class="text-muted">Registered Phone</div>
                    <div class="fw-semibold"><?= e($phone) ?></div>
                </div>
                <?php endif; ?>
                <div class="col-12 mt-2">
                    <div class="text-muted">Status</div>
                    <div class="fw-semibold text-warning"><i class="fas fa-clock me-1"></i>Under Review — Our team will call you within 24 hours</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- What happens next -->
    <div class="card border-0 shadow-sm mx-auto mb-4" style="max-width:480px">
        <div class="card-header border-0 bg-white pt-4 pb-0">
            <h6 class="fw-bold text-muted text-uppercase small tracking-wide mb-0">What Happens Next?</h6>
        </div>
        <div class="card-body p-4 pt-3">
            <div class="d-flex gap-3 mb-3 text-start">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-bold"
                     style="width:32px;height:32px;font-size:13px">1</div>
                <div>
                    <div class="fw-semibold small">Counselor Review</div>
                    <div class="text-muted" style="font-size:12px">Our academic counselor will review your enquiry and profile</div>
                </div>
            </div>
            <div class="d-flex gap-3 mb-3 text-start">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-bold"
                     style="width:32px;height:32px;font-size:13px">2</div>
                <div>
                    <div class="fw-semibold small">Personal Call</div>
                    <div class="text-muted" style="font-size:12px">We'll call <?= $phone ? '<strong>' . e($phone) . '</strong>' : 'your registered number' ?> within 24 hours to discuss your options</div>
                </div>
            </div>
            <div class="d-flex gap-3 text-start">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0 text-white fw-bold"
                     style="width:32px;height:32px;font-size:13px">3</div>
                <div>
                    <div class="fw-semibold small">Campus Visit / Online Session</div>
                    <div class="text-muted" style="font-size:12px">Schedule a campus tour or online counseling session at your convenience</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action buttons -->
    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
        <a href="<?= url('enquire') ?>" class="btn btn-primary px-4">
            <i class="fas fa-plus me-1"></i> New Enquiry
        </a>
        <a href="<?= url('apply') ?>" class="btn btn-outline-primary px-4">
            <i class="fas fa-file-alt me-1"></i> Apply for Admission
        </a>
    </div>

    <p class="text-muted small">
        Save your reference number <strong><?= e($number) ?></strong> for future correspondence.
    </p>
</div>
