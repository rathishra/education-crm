<?php
$pageTitle = 'Offer Letter — ' . e($admission['admission_number']);
$appName   = config('app.name', 'Edu Matrix');
$instName  = $institution['name'] ?? $appName;
$instAddr  = trim(($institution['address'] ?? '') . ($institution['city'] ? ', ' . $institution['city'] : ''));
$instPhone = $institution['phone'] ?? '';
$instEmail = $institution['email'] ?? '';
$instLogo  = $institution['logo'] ?? '';
$today     = date('d F Y');
$payDue    = !empty($admission['payment_due_date']) ? date('d F Y', strtotime($admission['payment_due_date'])) : null;
$finalFee  = (float)($admission['final_fee'] ?? $admission['total_fee'] ?? 0);
$discount  = (float)($admission['discount_amount'] ?? 0);
$scholarship = (float)($admission['scholarship_amount'] ?? 0);
?>

<div class="print-page p-5" style="min-height:297mm;position:relative;">

    <!-- Letterhead -->
    <div class="d-flex align-items-start justify-content-between border-bottom pb-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <?php if ($instLogo): ?>
            <img src="<?= e($instLogo) ?>" alt="Logo" style="height:64px;width:auto;object-fit:contain;">
            <?php else: ?>
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:64px;height:64px;font-size:1.4rem;background:#3b82f6;flex-shrink:0">
                <?= strtoupper(substr($instName, 0, 2)) ?>
            </div>
            <?php endif; ?>
            <div>
                <div class="fw-bold fs-4 lh-1"><?= e($instName) ?></div>
                <?php if ($instAddr): ?><div class="text-muted small mt-1"><?= e($instAddr) ?></div><?php endif; ?>
                <?php if ($instPhone || $instEmail): ?>
                <div class="text-muted small">
                    <?= $instPhone ? '<i class="fas fa-phone me-1"></i>' . e($instPhone) : '' ?>
                    <?= ($instPhone && $instEmail) ? ' &bull; ' : '' ?>
                    <?= $instEmail ? '<i class="fas fa-envelope me-1"></i>' . e($instEmail) : '' ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-end">
            <div class="badge bg-warning text-dark fs-6 px-3 py-2">OFFER LETTER</div>
            <div class="text-muted small mt-2">Date: <?= $today ?></div>
            <div class="text-muted small">Ref: <?= e($admission['admission_number']) ?></div>
        </div>
    </div>

    <!-- Recipient -->
    <p class="mb-1">To,</p>
    <p class="fw-semibold mb-0"><?= e($admission['first_name'] . ' ' . ($admission['last_name'] ?? '')) ?></p>
    <?php if (!empty($admission['address_line1'])): ?>
    <p class="mb-0 text-muted small"><?= e($admission['address_line1']) ?><?= !empty($admission['address_line2']) ? ', ' . e($admission['address_line2']) : '' ?></p>
    <?php endif; ?>
    <?php if (!empty($admission['city'])): ?>
    <p class="mb-0 text-muted small"><?= e($admission['city']) ?><?= !empty($admission['state']) ? ', ' . e($admission['state']) : '' ?><?= !empty($admission['pincode']) ? ' — ' . e($admission['pincode']) : '' ?></p>
    <?php endif; ?>

    <p class="mt-4 mb-4">Dear <strong><?= e($admission['first_name']) ?></strong>,</p>

    <!-- Body -->
    <p>We are pleased to inform you that based on your application and our evaluation process, you have been <strong>selected for provisional admission</strong> to the following programme at <strong><?= e($instName) ?></strong>:</p>

    <!-- Programme Details -->
    <table class="table table-bordered mb-4" style="font-size:0.92rem;">
        <tbody>
            <tr>
                <th class="bg-light" style="width:40%">Programme</th>
                <td><strong><?= e($admission['course_name'] ?? $admission['course_id']) ?></strong></td>
            </tr>
            <?php if (!empty($admission['batch_name'])): ?>
            <tr>
                <th class="bg-light">Batch / Year</th>
                <td><?= e($admission['batch_name']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($admission['academic_year_name'])): ?>
            <tr>
                <th class="bg-light">Academic Year</th>
                <td><?= e($admission['academic_year_name']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($admission['department_name'])): ?>
            <tr>
                <th class="bg-light">Department</th>
                <td><?= e($admission['department_name']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($admission['specialization'])): ?>
            <tr>
                <th class="bg-light">Specialization</th>
                <td><?= e($admission['specialization']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th class="bg-light">Admission Type</th>
                <td><?= ucfirst($admission['admission_type'] ?? 'Regular') ?></td>
            </tr>
            <tr>
                <th class="bg-light">Application Number</th>
                <td><?= e($admission['admission_number']) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Fee Details -->
    <?php if ($finalFee > 0): ?>
    <p class="fw-semibold mb-2">Fee Details:</p>
    <table class="table table-sm table-bordered mb-4" style="font-size:0.92rem;max-width:400px;">
        <tbody>
            <tr>
                <td>Total Programme Fee</td>
                <td class="text-end">₹<?= number_format((float)($admission['total_fee'] ?? $finalFee), 2) ?></td>
            </tr>
            <?php if ($discount > 0): ?>
            <tr>
                <td>Discount</td>
                <td class="text-end text-success">— ₹<?= number_format($discount, 2) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($scholarship > 0): ?>
            <tr>
                <td>Scholarship</td>
                <td class="text-end text-success">— ₹<?= number_format($scholarship, 2) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="table-primary fw-bold">
                <td>Net Payable Fee</td>
                <td class="text-end">₹<?= number_format($finalFee, 2) ?></td>
            </tr>
        </tbody>
    </table>
    <?php if ($payDue): ?>
    <p class="text-danger small"><i class="fas fa-exclamation-triangle me-1"></i>Please complete the fee payment by <strong><?= $payDue ?></strong> to confirm your seat.</p>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Conditions -->
    <p class="fw-semibold mb-1 mt-3">Terms &amp; Conditions:</p>
    <ol style="font-size:0.88rem;" class="text-muted">
        <li>This offer is provisional and subject to verification of original documents.</li>
        <li>The seat will be confirmed only upon receipt of the required fee payment.</li>
        <li>Original mark sheets, transfer certificate, and identity proof must be submitted at the time of joining.</li>
        <li>The institution reserves the right to cancel this offer in case of any discrepancy in the submitted information.</li>
        <?php if ($payDue): ?><li>Failure to pay by <strong><?= $payDue ?></strong> may result in forfeiture of the offered seat.</li><?php endif; ?>
    </ol>

    <p class="mt-4">We look forward to welcoming you to our institution. Please feel free to contact our admissions office for any queries.</p>

    <!-- Signature -->
    <div class="row mt-5">
        <div class="col-6">
            <p class="mb-5 text-muted small">Candidate's Signature</p>
            <div class="border-top pt-2 text-muted small"><?= e($admission['first_name'] . ' ' . ($admission['last_name'] ?? '')) ?></div>
        </div>
        <div class="col-6 text-end">
            <p class="mb-5 text-muted small">Authorised Signatory</p>
            <div class="border-top pt-2">
                <div class="fw-semibold small">Admissions Office</div>
                <div class="text-muted small"><?= e($instName) ?></div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="border-top mt-4 pt-3 text-center text-muted" style="font-size:0.8rem;position:absolute;bottom:20mm;left:0;right:0;padding:0 40px;">
        <?= e($instName) ?><?= $instAddr ? ' &bull; ' . e($instAddr) : '' ?><?= $instPhone ? ' &bull; ' . e($instPhone) : '' ?>
    </div>
</div>
