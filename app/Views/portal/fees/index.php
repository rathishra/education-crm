<?php
$totalFee     = (float)($summary['total_fees']    ?? 0);
$totalPaid    = (float)($summary['total_paid']    ?? 0);
$totalBalance = (float)($summary['total_balance'] ?? 0);
$feePercent   = $totalFee > 0 ? min(100, round($totalPaid / $totalFee * 100)) : 0;
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-file-invoice-dollar me-2 text-success"></i>Fees & Payments</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; Fees & Payments</div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#dbeafe;color:#1e40af"><i class="fas fa-rupee-sign"></i></div>
            <div>
                <div class="portal-stat-label">Total Fee</div>
                <div class="portal-stat-value" style="font-size:1.2rem">₹<?= number_format($totalFee, 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#d1fae5;color:#065f46"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="portal-stat-label">Paid</div>
                <div class="portal-stat-value" style="font-size:1.2rem;color:#059669">₹<?= number_format($totalPaid, 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:<?= $totalBalance > 0 ? '#fef3c7' : '#d1fae5' ?>;color:<?= $totalBalance > 0 ? '#92400e' : '#065f46' ?>"><i class="fas fa-<?= $totalBalance > 0 ? 'exclamation-circle' : 'check-circle' ?>"></i></div>
            <div>
                <div class="portal-stat-label">Balance Due</div>
                <div class="portal-stat-value" style="font-size:1.2rem;color:<?= $totalBalance > 0 ? '#dc2626' : '#059669' ?>">₹<?= number_format($totalBalance, 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#ede9fe;color:#5b21b6"><i class="fas fa-percent"></i></div>
            <div>
                <div class="portal-stat-label">Paid</div>
                <div class="portal-stat-value" style="font-size:1.2rem"><?= $feePercent ?>%</div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Bar -->
<div class="portal-card mb-3 p-3">
    <div class="d-flex justify-content-between small mb-1">
        <span class="text-success fw-semibold">Paid ₹<?= number_format($totalPaid, 0) ?></span>
        <span class="text-danger fw-semibold">Due ₹<?= number_format($totalBalance, 0) ?></span>
    </div>
    <div class="progress" style="height:10px;border-radius:5px">
        <div class="progress-bar bg-success" style="width:<?= $feePercent ?>%;border-radius:5px"></div>
    </div>
</div>

<!-- Installment Schedule -->
<?php if (!empty($installments)): ?>
<div class="portal-card mb-3">
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
        <div class="fw-bold" style="color:#1e293b"><i class="fas fa-calendar-alt me-2 text-success"></i>Installment Schedule</div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover portal-table mb-0">
            <thead>
                <tr>
                    <th>Installment</th>
                    <th>Due Date</th>
                    <th class="text-end">Amount (₹)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $instColors = ['paid' => 'success', 'due' => 'warning', 'overdue' => 'danger', 'upcoming' => 'secondary', 'partial' => 'info'];
                foreach ($installments as $inst):
                    $iColor = $instColors[$inst['status'] ?? 'upcoming'] ?? 'secondary';
                ?>
                <tr>
                    <td class="fw-semibold"><?= e($inst['installment_name'] ?? ('Installment ' . ($inst['installment_number'] ?? ''))) ?></td>
                    <td><?= !empty($inst['due_date']) ? date('d M Y', strtotime($inst['due_date'])) : '—' ?></td>
                    <td class="text-end">₹<?= number_format((float)$inst['amount'], 2) ?></td>
                    <td><span class="badge bg-<?= $iColor ?>-subtle text-<?= $iColor ?> border"><?= ucfirst($inst['status'] ?? 'upcoming') ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Payment Receipts -->
<div class="portal-card">
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
        <div class="fw-bold" style="color:#1e293b"><i class="fas fa-receipt me-2 text-success"></i>Payment Receipts</div>
    </div>
    <?php if (empty($receipts)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-receipt d-block fs-2 mb-2 opacity-25"></i>
        No payment receipts found.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover portal-table mb-0">
            <thead>
                <tr>
                    <th>Receipt No.</th>
                    <th>Date</th>
                    <th>Mode</th>
                    <th class="text-end">Amount (₹)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receipts as $rec): ?>
                <tr>
                    <td class="fw-semibold"><?= e($rec['receipt_number'] ?? $rec['id']) ?></td>
                    <td><?= !empty($rec['receipt_date']) ? date('d M Y', strtotime($rec['receipt_date'])) : '—' ?></td>
                    <td><span class="badge bg-light text-dark border"><?= e(ucfirst(str_replace('_', ' ', $rec['payment_mode'] ?? $rec['payment_method'] ?? 'cash'))) ?></span></td>
                    <td class="text-end fw-semibold text-success">₹<?= number_format((float)($rec['total_amount'] ?? $rec['amount'] ?? 0), 2) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= url('portal/student/fees/receipt/' . $rec['id']) ?>" class="btn btn-xs btn-outline-primary" style="font-size:0.72rem;padding:2px 8px" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= url('portal/student/fees/receipt/' . $rec['id'] . '/print') ?>" target="_blank" class="btn btn-xs btn-outline-secondary" style="font-size:0.72rem;padding:2px 8px" title="Print">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
