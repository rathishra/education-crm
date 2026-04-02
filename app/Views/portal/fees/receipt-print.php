<?php
$appName = config('app.name', 'Edu Matrix');
$today   = date('d F Y');
$totalAmount = (float)($receipt['total_amount'] ?? $receipt['amount'] ?? 0);
?>
<div class="print-page p-5" style="min-height:200mm">
    <!-- Letterhead -->
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
        <div>
            <div class="fw-bold fs-4"><?= e($receipt['institution_name'] ?? $appName) ?></div>
            <?php if (!empty($receipt['institution_address'])): ?><div class="text-muted small"><?= e($receipt['institution_address']) ?></div><?php endif; ?>
            <?php if (!empty($receipt['institution_phone'])): ?><div class="text-muted small"><?= e($receipt['institution_phone']) ?></div><?php endif; ?>
        </div>
        <div class="text-end">
            <div class="badge bg-success fs-6 px-3 py-2 mb-1">FEE RECEIPT</div>
            <div class="text-muted small">Date: <?= $today ?></div>
        </div>
    </div>

    <!-- Student Info -->
    <div class="row mb-4">
        <div class="col-6">
            <table class="table table-sm table-borderless mb-0" style="font-size:0.88rem">
                <tr><th class="text-muted fw-normal p-1" style="width:40%">Receipt No.</th><td class="fw-bold p-1"><?= e($receipt['receipt_number'] ?? $receipt['id']) ?></td></tr>
                <tr><th class="text-muted fw-normal p-1">Student Name</th><td class="p-1"><?= e($receipt['student_name'] ?? '') ?></td></tr>
                <tr><th class="text-muted fw-normal p-1">Student ID</th><td class="p-1"><?= e($receipt['student_id_number'] ?? '') ?></td></tr>
                <?php if (!empty($receipt['admission_number'])): ?>
                <tr><th class="text-muted fw-normal p-1">Adm. No.</th><td class="p-1"><?= e($receipt['admission_number']) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-sm table-borderless mb-0" style="font-size:0.88rem">
                <tr><th class="text-muted fw-normal p-1" style="width:40%">Date</th><td class="p-1"><?= !empty($receipt['receipt_date']) ? date('d M Y', strtotime($receipt['receipt_date'])) : $today ?></td></tr>
                <tr><th class="text-muted fw-normal p-1">Course</th><td class="p-1"><?= e($receipt['course_name'] ?? '—') ?></td></tr>
                <tr><th class="text-muted fw-normal p-1">Batch</th><td class="p-1"><?= e($receipt['batch_name'] ?? '—') ?></td></tr>
                <tr><th class="text-muted fw-normal p-1">Payment Mode</th><td class="p-1"><?= ucfirst(str_replace('_', ' ', $receipt['payment_mode'] ?? $receipt['payment_method'] ?? 'cash')) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- Items Table -->
    <?php if (!empty($items)): ?>
    <table class="table table-bordered mb-3" style="font-size:0.88rem">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Fee Head</th>
                <th>Category</th>
                <th class="text-end">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($item['head_name']) ?></td>
                <td><?= e(ucfirst($item['category'] ?? '')) ?></td>
                <td class="text-end">₹<?= number_format((float)($item['amount'] ?? 0), 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr class="table-success fw-bold">
                <td colspan="3" class="text-end">Total Paid</td>
                <td class="text-end">₹<?= number_format($totalAmount, 2) ?></td>
            </tr>
        </tfoot>
    </table>
    <?php else: ?>
    <div class="border p-3 mb-3 text-center" style="font-size:0.9rem">
        <strong>Total Amount Paid: ₹<?= number_format($totalAmount, 2) ?></strong>
    </div>
    <?php endif; ?>

    <?php if (!empty($receipt['remarks'])): ?>
    <p class="text-muted small">Remarks: <?= e($receipt['remarks']) ?></p>
    <?php endif; ?>

    <!-- Signature -->
    <div class="row mt-5">
        <div class="col-6">
            <p class="mb-5 text-muted small">Student's Signature</p>
            <div class="border-top pt-1 text-muted small"><?= e($receipt['student_name'] ?? '') ?></div>
        </div>
        <div class="col-6 text-end">
            <p class="mb-5 text-muted small">Authorised Signatory</p>
            <div class="border-top pt-1">
                <div class="fw-semibold small">Accounts Office</div>
                <div class="text-muted small"><?= e($receipt['institution_name'] ?? $appName) ?></div>
            </div>
        </div>
    </div>

    <div class="text-center text-muted border-top pt-3 mt-3" style="font-size:0.75rem">
        This is a computer-generated receipt and does not require a physical signature.
    </div>
</div>
