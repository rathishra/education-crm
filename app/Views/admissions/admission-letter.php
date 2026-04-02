<?php
$pageTitle  = 'Admission Letter — ' . e($admission['admission_number']);
$appName    = config('app.name', 'Edu Matrix');
$instName   = $institution['name'] ?? $appName;
$instAddr   = trim(($institution['address'] ?? '') . ($institution['city'] ? ', ' . $institution['city'] : ''));
$instPhone  = $institution['phone'] ?? '';
$instEmail  = $institution['email'] ?? '';
$instLogo   = $institution['logo'] ?? '';
$today      = date('d F Y');
$joinDate   = !empty($admission['created_at']) ? date('d F Y', strtotime($admission['created_at'])) : $today;
$finalFee   = (float)($admission['final_fee'] ?? $admission['total_fee'] ?? 0);
$paidAmt    = (float)($admission['paid_amount'] ?? 0);
$balance    = max(0, $finalFee - $paidAmt);
?>

<div class="print-page p-5" style="min-height:297mm;position:relative;">

    <!-- Letterhead -->
    <div class="d-flex align-items-start justify-content-between border-bottom pb-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <?php if ($instLogo): ?>
            <img src="<?= e($instLogo) ?>" alt="Logo" style="height:64px;width:auto;object-fit:contain;">
            <?php else: ?>
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:64px;height:64px;font-size:1.4rem;background:#10b981;flex-shrink:0">
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
            <div class="badge bg-success fs-6 px-3 py-2">ADMISSION LETTER</div>
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
    <p>We are pleased to inform you that your admission to <strong><?= e($instName) ?></strong> has been <strong>confirmed</strong>. The details of your admission are as follows:</p>

    <!-- Admission Details -->
    <table class="table table-bordered mb-4" style="font-size:0.92rem;">
        <tbody>
            <tr>
                <th class="bg-light" style="width:40%">Admission Number</th>
                <td><strong><?= e($admission['admission_number']) ?></strong></td>
            </tr>
            <tr>
                <th class="bg-light">Full Name</th>
                <td><?= e($admission['first_name'] . ' ' . ($admission['last_name'] ?? '')) ?></td>
            </tr>
            <?php if (!empty($admission['date_of_birth'])): ?>
            <tr>
                <th class="bg-light">Date of Birth</th>
                <td><?= date('d F Y', strtotime($admission['date_of_birth'])) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th class="bg-light">Programme</th>
                <td><strong><?= e($admission['course_name'] ?? $admission['course_id']) ?></strong></td>
            </tr>
            <?php if (!empty($admission['batch_name'])): ?>
            <tr>
                <th class="bg-light">Batch</th>
                <td><?= e($admission['batch_name']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if (!empty($admission['section_name'])): ?>
            <tr>
                <th class="bg-light">Section</th>
                <td><?= e($admission['section_name']) ?></td>
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
                <th class="bg-light">Current Semester</th>
                <td><?= (int)($admission['current_semester'] ?? 1) ?></td>
            </tr>
            <tr>
                <th class="bg-light">Admission Type</th>
                <td><?= ucfirst($admission['admission_type'] ?? 'Regular') ?></td>
            </tr>
            <tr>
                <th class="bg-light">Status</th>
                <td><span class="badge bg-success">Confirmed</span></td>
            </tr>
        </tbody>
    </table>

    <!-- Fee Summary -->
    <?php if ($finalFee > 0): ?>
    <p class="fw-semibold mb-2">Fee Summary:</p>
    <table class="table table-sm table-bordered mb-4" style="font-size:0.92rem;max-width:420px;">
        <tbody>
            <tr>
                <td>Total Programme Fee</td>
                <td class="text-end">₹<?= number_format($finalFee, 2) ?></td>
            </tr>
            <tr>
                <td>Amount Paid</td>
                <td class="text-end text-success">₹<?= number_format($paidAmt, 2) ?></td>
            </tr>
            <?php if ($balance > 0): ?>
            <tr class="table-warning fw-semibold">
                <td>Balance Due</td>
                <td class="text-end">₹<?= number_format($balance, 2) ?></td>
            </tr>
            <?php else: ?>
            <tr class="table-success fw-semibold">
                <td>Fee Status</td>
                <td class="text-end">Fully Paid</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Hostel / Transport -->
    <?php if (!empty($admission['hostel_required']) || !empty($admission['transport_required'])): ?>
    <p class="fw-semibold mb-2">Additional Services:</p>
    <ul style="font-size:0.92rem;" class="mb-4">
        <?php if (!empty($admission['hostel_required'])): ?>
        <li><i class="fas fa-building me-1 text-primary"></i>Hostel accommodation requested — please contact the hostel office for allotment.</li>
        <?php endif; ?>
        <?php if (!empty($admission['transport_required'])): ?>
        <li><i class="fas fa-bus me-1 text-primary"></i>Transport service requested — please contact the transport office for route details.</li>
        <?php endif; ?>
    </ul>
    <?php endif; ?>

    <!-- Instructions -->
    <p class="fw-semibold mb-1 mt-3">Important Instructions:</p>
    <ol style="font-size:0.88rem;" class="text-muted">
        <li>Please report to the institution on or before the stipulated joining date with all original documents.</li>
        <li>Carry this admission letter along with your original mark sheets, identity proof, and transfer certificate.</li>
        <li>Ensure all pending fees are cleared before the commencement of classes.</li>
        <li>You are required to strictly adhere to the institution's rules and regulations.</li>
        <li>This letter does not guarantee continuation if eligibility criteria are not met post-verification.</li>
    </ol>

    <p class="mt-4">Congratulations on your admission. We wish you a successful and enriching academic journey at <strong><?= e($instName) ?></strong>.</p>

    <!-- Signature -->
    <div class="row mt-5">
        <div class="col-6">
            <p class="mb-5 text-muted small">Student's Signature</p>
            <div class="border-top pt-2 text-muted small"><?= e($admission['first_name'] . ' ' . ($admission['last_name'] ?? '')) ?></div>
        </div>
        <div class="col-6 text-end">
            <p class="mb-5 text-muted small">Principal / Authorised Signatory</p>
            <div class="border-top pt-2">
                <div class="fw-semibold small">Admissions Office</div>
                <div class="text-muted small"><?= e($instName) ?></div>
            </div>
        </div>
    </div>

    <!-- Stamp area -->
    <div class="d-flex justify-content-end mt-3 no-print" style="opacity:0.4;font-size:0.75rem;font-style:italic;text-align:center;">
        <div class="border rounded p-3" style="width:100px;height:80px;display:flex;align-items:center;justify-content:center;">Institution Seal</div>
    </div>

    <!-- Footer -->
    <div class="border-top mt-4 pt-3 text-center text-muted" style="font-size:0.8rem;position:absolute;bottom:20mm;left:0;right:0;padding:0 40px;">
        <?= e($instName) ?><?= $instAddr ? ' &bull; ' . e($instAddr) : '' ?><?= $instPhone ? ' &bull; ' . e($instPhone) : '' ?>
    </div>
</div>
