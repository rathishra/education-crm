<?php $pageTitle = 'Student Fee Ledger — ' . ($student['name'] ?? ''); ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4 no-print">
    <div>
        <h1 class="mb-1"><i class="fas fa-book-open me-2 text-primary"></i>Student Fee Ledger</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item"><a href="<?= url('fees/reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Ledger</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="fas fa-print me-1"></i>Print Ledger
        </button>
        <a href="<?= url('fees/reports') ?>" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php if(empty($student)): ?>
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5 text-muted">
        <i class="fas fa-user-graduate fa-3x mb-3 opacity-25"></i>
        <p>Student not found.</p>
        <a href="<?= url('fees/reports') ?>" class="btn btn-primary">Go to Reports</a>
    </div>
</div>
<?php return; endif; ?>

<!-- Student Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:56px;height:56px;font-size:1.4rem;font-weight:700;color:#1a56db">
                        <?= strtoupper(substr($student['name'],0,1)) ?>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold"><?= e($student['name']) ?></h4>
                        <div class="text-muted">
                            <?php if($student['roll_number']): ?><span class="me-3">Roll: <strong><?= e($student['roll_number']) ?></strong></span><?php endif; ?>
                            <?php if($student['enrollment_no']): ?><span>Enroll: <strong><?= e($student['enrollment_no']) ?></strong></span><?php endif; ?>
                        </div>
                        <div class="text-primary small">
                            <?= e($student['course_name'] ?? '') ?>
                            <?= !empty($student['batch_name']) ? ' — '.e($student['batch_name']) : '' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="border rounded py-2">
                            <div class="fw-bold text-primary">₹<?= number_format($ledgerSummary['total_assigned'] ?? 0,0) ?></div>
                            <div class="small text-muted">Total Fees</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded py-2">
                            <div class="fw-bold text-success">₹<?= number_format($ledgerSummary['total_paid'] ?? 0,0) ?></div>
                            <div class="small text-muted">Paid</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded py-2 <?= ($ledgerSummary['total_balance'] ?? 0) > 0 ? 'border-danger' : '' ?>">
                            <div class="fw-bold <?= ($ledgerSummary['total_balance'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                                ₹<?= number_format($ledgerSummary['total_balance'] ?? 0,0) ?>
                            </div>
                            <div class="small text-muted">Balance</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fee Assignments Section -->
<?php if(!empty($assignments)): ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-user-tag me-2 text-primary"></i>Fee Assignments</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Fee Head</th><th>Academic Year</th><th class="text-end">Gross</th><th class="text-end">Concession</th>
                    <th class="text-end">Fine</th><th class="text-end">Net</th><th class="text-end">Paid</th>
                    <th class="text-end">Balance</th><th class="text-center">Status</th><th>Due Date</th></tr>
            </thead>
            <tbody>
            <?php foreach($assignments as $a): ?>
            <tr>
                <td>
                    <div class="fw-semibold"><?= e($a['head_name']) ?></div>
                    <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem"><?= e($a['head_code'] ?? '') ?></span>
                </td>
                <td class="small text-muted"><?= e($a['year_name'] ?? '—') ?></td>
                <td class="text-end">₹<?= number_format($a['gross_amount'],2) ?></td>
                <td class="text-end text-success"><?= $a['concession_amount']>0?'(₹'.number_format($a['concession_amount'],2).')':'—' ?></td>
                <td class="text-end text-danger"><?= $a['fine_amount']>0?'+₹'.number_format($a['fine_amount'],2):'—' ?></td>
                <td class="text-end fw-semibold">₹<?= number_format($a['net_amount'],2) ?></td>
                <td class="text-end text-success">₹<?= number_format($a['paid_amount'],2) ?></td>
                <td class="text-end <?= $a['balance_amount']>0?'text-danger fw-bold':'text-success' ?>">₹<?= number_format($a['balance_amount'],2) ?></td>
                <td class="text-center">
                    <?php $sc=['pending'=>'warning','partial'=>'info','paid'=>'success','overdue'=>'danger','waived'=>'secondary'];
                    $s=$a['status']; ?>
                    <span class="badge bg-<?= $sc[$s]??'secondary' ?>-subtle text-<?= $sc[$s]??'secondary' ?> border"><?= ucfirst($s) ?></span>
                </td>
                <td class="small"><?= $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Transactions (Receipts) -->
<?php if(!empty($receipts)): ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-receipt me-2 text-success"></i>Payment Receipts</div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Receipt No.</th><th>Date</th><th>Mode</th><th>Reference</th>
                    <th class="text-end">Amount</th><th>Status</th><th>Print</th></tr>
            </thead>
            <tbody>
            <?php foreach($receipts as $r): ?>
            <tr class="<?= $r['status']==='cancelled'?'table-secondary text-decoration-line-through':'' ?>">
                <td class="fw-semibold"><?= e($r['receipt_number']) ?></td>
                <td><?= date('d M Y', strtotime($r['receipt_date'])) ?></td>
                <td><span class="badge bg-primary-subtle text-primary border"><?= strtoupper($r['payment_mode']) ?></span></td>
                <td class="small text-muted"><?= e($r['reference_number'] ?? $r['cheque_number'] ?? '—') ?></td>
                <td class="text-end fw-bold <?= $r['status']==='cancelled'?'text-muted':'text-success' ?>">
                    <?= $r['status']==='cancelled'?'<s>':'' ?>₹<?= number_format($r['total_paid'],2) ?><?= $r['status']==='cancelled'?'</s>':'' ?>
                </td>
                <td>
                    <?php if($r['status']==='cancelled'): ?>
                    <span class="badge bg-danger">Cancelled</span>
                    <?php else: ?>
                    <span class="badge bg-success">Active</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($r['status']!=='cancelled'): ?>
                    <a href="<?= url('fees/receipts/'.$r['id'].'/print') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-print"></i>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold table-light">
                    <td colspan="4" class="text-end">Total Paid</td>
                    <td class="text-end text-success">₹<?= number_format(array_sum(array_map(fn($r) => $r['status']==='cancelled'?0:$r['total_paid'], $receipts)),2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Concessions -->
<?php if(!empty($concessions)): ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-percentage me-2 text-info"></i>Concessions Applied</div>
    <div class="card-body p-0">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Concession Name</th><th>Category</th><th>Type</th><th class="text-end">Discount</th><th class="text-center">Status</th><th>Approved By</th></tr>
            </thead>
            <tbody>
            <?php foreach($concessions as $c): ?>
            <tr>
                <td class="fw-semibold"><?= e($c['concession_name']) ?></td>
                <td><span class="badge bg-secondary-subtle text-secondary border"><?= ucfirst($c['category']) ?></span></td>
                <td><?= $c['concession_type']==='percentage' ? $c['concession_value'].'%' : '₹'.number_format($c['concession_value'],2) ?></td>
                <td class="text-end text-success fw-bold">₹<?= number_format($c['final_discount'],2) ?></td>
                <td class="text-center">
                    <?php $sc=['pending'=>'warning','approved'=>'success','rejected'=>'danger']; $s=$c['status']; ?>
                    <span class="badge bg-<?= $sc[$s]??'secondary' ?>-subtle text-<?= $sc[$s]??'secondary' ?> border"><?= ucfirst($s) ?></span>
                </td>
                <td class="small"><?= e($c['approver_name'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Refunds -->
<?php if(!empty($refunds)): ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-undo-alt me-2 text-warning"></i>Refunds</div>
    <div class="card-body p-0">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Reason</th><th>Mode</th><th class="text-end">Amount</th><th class="text-center">Status</th></tr>
            </thead>
            <tbody>
            <?php foreach($refunds as $r): ?>
            <tr>
                <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                <td class="small text-muted"><?= e(substr($r['reason'],0,60)) ?></td>
                <td><span class="badge bg-secondary-subtle text-secondary border"><?= strtoupper($r['refund_mode'] ?? '—') ?></span></td>
                <td class="text-end fw-bold text-danger">₹<?= number_format($r['refund_amount'],2) ?></td>
                <td class="text-center">
                    <?php $sc=['pending'=>'warning','approved'=>'info','rejected'=>'danger','processed'=>'success']; $s=$r['status']; ?>
                    <span class="badge bg-<?= $sc[$s]??'secondary' ?>-subtle text-<?= $sc[$s]??'secondary' ?> border"><?= ucfirst($s) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Print Footer -->
<div class="text-center text-muted small mt-4 border-top pt-3 no-print">
    Generated on <?= date('d M Y h:i A') ?> by <?= e(session('user_name', 'Admin')) ?>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .badge { border: 1px solid #ccc !important; }
}
</style>
