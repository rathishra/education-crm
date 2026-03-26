<?php $pageTitle = 'Payment Receipt'; ?>

<style>
@media print {
    .no-print { display: none !important; }
    body { background: white; }
    .receipt-card { box-shadow: none !important; border: 1px solid #000 !important; }
}
.receipt-card { max-width: 700px; margin: 0 auto; }
</style>

<div class="page-header no-print">
    <div>
        <h1><i class="fas fa-receipt me-2"></i>Payment Receipt</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('payments') ?>">Payments</a></li>
                <li class="breadcrumb-item active">Receipt</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-1"></i>Print</button>
        <a href="<?= url('payments') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="receipt-card card shadow">
    <div class="card-body p-4">
        <!-- Header -->
        <div class="text-center border-bottom pb-3 mb-3">
            <h4 class="fw-bold mb-1"><?= e($payment['institution_name'] ?? session('institution_name') ?? 'Institution Name') ?></h4>
            <p class="text-muted small mb-0"><?= e($payment['institution_address'] ?? '') ?></p>
            <?php if (!empty($payment['institution_phone'])): ?>
            <p class="text-muted small mb-0">Ph: <?= e($payment['institution_phone']) ?></p>
            <?php endif; ?>
            <div class="mt-2">
                <span class="badge bg-primary fs-6 px-3 py-2">PAYMENT RECEIPT</span>
            </div>
        </div>

        <!-- Receipt Meta -->
        <div class="row mb-3">
            <div class="col-6">
                <small class="text-muted">Receipt No.</small>
                <div class="fw-bold fs-5"><?= e($payment['receipt_number'] ?? '—') ?></div>
            </div>
            <div class="col-6 text-end">
                <small class="text-muted">Date</small>
                <div class="fw-bold"><?= !empty($payment['payment_date']) ? formatDate($payment['payment_date']) : '—' ?></div>
            </div>
        </div>

        <!-- Student Details -->
        <div class="bg-light rounded p-3 mb-3">
            <div class="row g-2">
                <div class="col-6">
                    <small class="text-muted">Student Name</small>
                    <div class="fw-bold"><?= e($payment['student_name'] ?? '—') ?></div>
                </div>
                <div class="col-6">
                    <small class="text-muted">Student ID</small>
                    <div class="fw-bold"><?= e($payment['student_id_number'] ?? '—') ?></div>
                </div>
                <div class="col-6">
                    <small class="text-muted">Course</small>
                    <div><?= e($payment['course_name'] ?? '—') ?></div>
                </div>
                <div class="col-6">
                    <small class="text-muted">Batch</small>
                    <div><?= e($payment['batch_name'] ?? '—') ?></div>
                </div>
            </div>
        </div>

        <!-- Payment Details -->
        <table class="table table-bordered mb-3">
            <thead class="table-light">
                <tr>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= e($payment['installment_name'] ?? 'Fee Payment') ?></td>
                    <td class="text-end fw-bold"><?= formatCurrency($payment['amount'] ?? 0) ?></td>
                </tr>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td class="text-end fw-bold fs-5">Total Paid</td>
                    <td class="text-end fw-bold fs-5 text-success"><?= formatCurrency($payment['amount'] ?? 0) ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Amount in Words -->
        <div class="bg-light rounded p-2 mb-3">
            <small class="text-muted">Amount in Words:</small>
            <div class="fw-bold"><?= e($payment['amount_in_words'] ?? '—') ?></div>
        </div>

        <!-- Payment Mode & Signature -->
        <div class="row">
            <div class="col-6">
                <small class="text-muted">Payment Mode</small>
                <div>
                    <span class="badge bg-primary text-uppercase"><?= e($payment['payment_mode'] ?? '—') ?></span>
                    <?php if (!empty($payment['transaction_id'])): ?>
                    <div class="small text-muted">Ref: <?= e($payment['transaction_id']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-6 text-end">
                <small class="text-muted">Collected By</small>
                <div class="fw-bold"><?= e($payment['collected_by_name'] ?? '—') ?></div>
            </div>
        </div>

        <hr>
        <div class="row mt-3">
            <div class="col-6">
                <div class="text-muted small">Student Signature</div>
                <div style="border-bottom:1px solid #000; height:40px; margin-top:10px;"></div>
            </div>
            <div class="col-6 text-end">
                <div class="text-muted small">Authorized Signature</div>
                <div style="border-bottom:1px solid #000; height:40px; margin-top:10px;"></div>
            </div>
        </div>

        <div class="text-center text-muted small mt-3">
            <i class="fas fa-info-circle me-1"></i>This is a computer-generated receipt.
        </div>
    </div>
</div>
