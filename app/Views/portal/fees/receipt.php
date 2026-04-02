<?php
$totalAmount = (float)($receipt['total_amount'] ?? $receipt['amount'] ?? 0);
?>
<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-receipt me-2 text-success"></i>Fee Receipt</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; <a href="<?= url('portal/student/fees') ?>">Fees</a> &rsaquo; Receipt</div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('portal/student/fees/receipt/' . $receipt['id'] . '/print') ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-print me-1"></i>Print
        </a>
        <a href="<?= url('portal/student/fees') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="portal-card" style="max-width:750px">
    <div class="card-body p-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
            <div>
                <div class="fw-bold fs-5"><?= e($receipt['institution_name'] ?? config('app.name')) ?></div>
                <?php if (!empty($receipt['institution_address'])): ?><div class="text-muted small"><?= e($receipt['institution_address']) ?></div><?php endif; ?>
            </div>
            <div class="text-end">
                <div class="badge bg-success-subtle text-success border px-3 py-2 fs-6 mb-1">FEE RECEIPT</div>
                <div class="text-muted small"><?= e($receipt['receipt_number'] ?? '#' . $receipt['id']) ?></div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="row g-3 mb-4" style="font-size:0.875rem">
            <div class="col-6"><div class="text-muted small">Student</div><div class="fw-semibold"><?= e($receipt['student_name'] ?? '') ?></div></div>
            <div class="col-6"><div class="text-muted small">Student ID</div><div class="fw-semibold"><?= e($receipt['student_id_number'] ?? '') ?></div></div>
            <div class="col-6"><div class="text-muted small">Course</div><div><?= e($receipt['course_name'] ?? '—') ?></div></div>
            <div class="col-6"><div class="text-muted small">Batch</div><div><?= e($receipt['batch_name'] ?? '—') ?></div></div>
            <div class="col-6"><div class="text-muted small">Receipt Date</div><div><?= !empty($receipt['receipt_date']) ? date('d M Y', strtotime($receipt['receipt_date'])) : '—' ?></div></div>
            <div class="col-6"><div class="text-muted small">Payment Mode</div><div><?= ucfirst(str_replace('_', ' ', $receipt['payment_mode'] ?? $receipt['payment_method'] ?? 'cash')) ?></div></div>
        </div>

        <!-- Items -->
        <?php if (!empty($items)): ?>
        <div class="table-responsive mb-3">
            <table class="table table-bordered portal-table">
                <thead><tr><th>#</th><th>Fee Head</th><th>Category</th><th class="text-end">Amount (₹)</th></tr></thead>
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
                    <tr class="table-success fw-bold"><td colspan="3" class="text-end">Total Paid</td><td class="text-end">₹<?= number_format($totalAmount, 2) ?></td></tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center p-4 bg-light rounded-3 mb-3">
            <div class="fw-bold fs-4 text-success">₹<?= number_format($totalAmount, 2) ?></div>
            <div class="text-muted small">Total Amount Paid</div>
        </div>
        <?php endif; ?>

        <?php if (!empty($receipt['remarks'])): ?>
        <div class="text-muted small border-top pt-2">Remarks: <?= e($receipt['remarks']) ?></div>
        <?php endif; ?>
    </div>
</div>
