<?php $pageTitle = 'Fee Collection Report'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-chart-line me-2 text-success"></i>Fee Collection Report</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item"><a href="<?= url('fees/reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Collection</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('fees/reports/collection/export') ?>?<?= http_build_query($filters) ?>" class="btn btn-outline-success">
            <i class="fas fa-download me-1"></i>Export CSV
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i>Print
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('fees/reports/collection') ?>" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($filters['date_from'] ?? date('Y-m-01')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($filters['date_to'] ?? date('Y-m-d')) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Group By</label>
                <select name="group_by" class="form-select form-select-sm">
                    <?php foreach(['day'=>'Day','course'=>'Course','mode'=>'Payment Mode','head'=>'Fee Head'] as $v => $l): ?>
                    <option value="<?= $v ?>" <?= ($filters['group_by']??'day')===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Academic Year</label>
                <select name="academic_year_id" class="form-select form-select-sm">
                    <option value="">All Years</option>
                    <?php foreach($academicYears as $ay): ?>
                    <option value="<?= $ay['id'] ?>" <?= ($filters['academic_year_id']??'')==$ay['id']?'selected':'' ?>><?= e($ay['year_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Payment Mode</label>
                <select name="payment_mode" class="form-select form-select-sm">
                    <option value="">All Modes</option>
                    <?php foreach(['cash','upi','card','netbanking','cheque','dd','online'] as $m): ?>
                    <option value="<?= $m ?>" <?= ($filters['payment_mode']??'')===$m?'selected':'' ?>><?= strtoupper($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search me-1"></i>Generate</button>
                <a href="<?= url('fees/reports/collection') ?>" class="btn btn-light btn-sm"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<?php if(!empty($summary)): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body py-3">
                <div class="small opacity-75">Total Collected</div>
                <div class="fw-bold fs-4">₹<?= number_format($summary['total_collected'] ?? 0,2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="small text-muted">No. of Receipts</div>
                <div class="fw-bold fs-4 text-primary"><?= number_format($summary['receipt_count'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="small text-muted">Total Fine Collected</div>
                <div class="fw-bold fs-4 text-warning">₹<?= number_format($summary['total_fine'] ?? 0,2) ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="small text-muted">Students Paid</div>
                <div class="fw-bold fs-4 text-info"><?= number_format($summary['student_count'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Data Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold">
            <i class="fas fa-table me-2 text-success"></i>
            <?php
            $groupLabels = ['day'=>'Daily Collection','course'=>'Course-wise Collection','mode'=>'Mode-wise Collection','head'=>'Head-wise Collection'];
            echo $groupLabels[$filters['group_by'] ?? 'day'] ?? 'Collection Data';
            ?>
        </span>
        <span class="text-muted small">
            <?= date('d M Y', strtotime($filters['date_from'] ?? date('Y-m-01'))) ?>
            — <?= date('d M Y', strtotime($filters['date_to'] ?? date('Y-m-d'))) ?>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="collectionTable">
            <thead class="table-light">
                <?php if(($filters['group_by']??'day') === 'day'): ?>
                <tr><th>Date</th><th class="text-end">Cash</th><th class="text-end">UPI</th><th class="text-end">Card</th><th class="text-end">Net Banking</th><th class="text-end">Cheque/DD</th><th class="text-end">Total</th><th class="text-end">Receipts</th></tr>
                <?php elseif(($filters['group_by']??'') === 'course'): ?>
                <tr><th>Course</th><th class="text-end">Gross Fees</th><th class="text-end">Collected</th><th class="text-end">Pending</th><th class="text-end">Students</th><th class="text-end">Collection %</th></tr>
                <?php elseif(($filters['group_by']??'') === 'mode'): ?>
                <tr><th>Payment Mode</th><th class="text-end">Amount</th><th class="text-end">Receipts</th><th class="text-end">Avg Receipt</th><th class="text-end">%&nbsp;of&nbsp;Total</th></tr>
                <?php else: ?>
                <tr><th>Fee Head</th><th>Category</th><th class="text-end">Amount Collected</th><th class="text-end">Receipts</th></tr>
                <?php endif; ?>
            </thead>
            <tbody>
            <?php if(empty($rows)): ?>
            <tr><td colspan="8" class="text-center py-5 text-muted">
                <i class="fas fa-chart-bar fa-2x mb-2 d-block opacity-25"></i>No data for selected filters.
            </td></tr>
            <?php else:
                $grandTotal = 0;
                foreach($rows as $row):
                    $grandTotal += $row['total'] ?? $row['amount'] ?? $row['collected'] ?? 0;
            ?>
            <?php if(($filters['group_by']??'day') === 'day'): ?>
            <tr>
                <td class="fw-semibold"><?= date('d M Y', strtotime($row['day'])) ?></td>
                <td class="text-end"><?= ($row['cash']??0)>0 ? '₹'.number_format($row['cash'],2) : '—' ?></td>
                <td class="text-end"><?= ($row['upi']??0)>0 ? '₹'.number_format($row['upi'],2) : '—' ?></td>
                <td class="text-end"><?= ($row['card']??0)>0 ? '₹'.number_format($row['card'],2) : '—' ?></td>
                <td class="text-end"><?= ($row['netbanking']??0)>0 ? '₹'.number_format($row['netbanking'],2) : '—' ?></td>
                <td class="text-end"><?= (($row['cheque']??0)+($row['dd']??0))>0 ? '₹'.number_format(($row['cheque']??0)+($row['dd']??0),2) : '—' ?></td>
                <td class="text-end fw-bold text-success">₹<?= number_format($row['total'],2) ?></td>
                <td class="text-end text-muted"><?= $row['receipt_count'] ?></td>
            </tr>
            <?php elseif(($filters['group_by']??'') === 'course'): ?>
            <tr>
                <td><div class="fw-semibold"><?= e($row['course_name']) ?></div><div class="small text-muted"><?= e($row['course_code'] ?? '') ?></div></td>
                <td class="text-end">₹<?= number_format($row['gross'],2) ?></td>
                <td class="text-end fw-bold text-success">₹<?= number_format($row['collected'],2) ?></td>
                <td class="text-end text-danger">₹<?= number_format($row['pending'],2) ?></td>
                <td class="text-end"><?= $row['students'] ?></td>
                <td class="text-end">
                    <?php $pct = $row['gross']>0 ? round($row['collected']/$row['gross']*100,1) : 0; ?>
                    <div class="progress" style="height:6px;min-width:60px">
                        <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                    </div>
                    <small class="text-muted"><?= $pct ?>%</small>
                </td>
            </tr>
            <?php elseif(($filters['group_by']??'') === 'mode'): ?>
            <tr>
                <td>
                    <?php $modeColors=['cash'=>'success','upi'=>'primary','card'=>'info','netbanking'=>'warning','cheque'=>'secondary','dd'=>'dark','online'=>'purple']; ?>
                    <span class="badge bg-<?= $modeColors[$row['payment_mode']]??'secondary' ?>-subtle text-<?= $modeColors[$row['payment_mode']]??'secondary' ?> border">
                        <?= strtoupper($row['payment_mode']) ?>
                    </span>
                </td>
                <td class="text-end fw-bold">₹<?= number_format($row['amount'],2) ?></td>
                <td class="text-end"><?= $row['receipt_count'] ?></td>
                <td class="text-end text-muted">₹<?= $row['receipt_count']>0?number_format($row['amount']/$row['receipt_count'],2):'0.00' ?></td>
                <td class="text-end">
                    <?php $pct2 = ($summary['total_collected']??0)>0 ? round($row['amount']/($summary['total_collected'])*100,1) : 0; ?>
                    <span class="badge bg-success-subtle text-success border"><?= $pct2 ?>%</span>
                </td>
            </tr>
            <?php else: ?>
            <tr>
                <td class="fw-semibold"><?= e($row['head_name']) ?></td>
                <td><span class="badge bg-secondary-subtle text-secondary border"><?= ucfirst($row['category'] ?? '') ?></span></td>
                <td class="text-end fw-bold text-success">₹<?= number_format($row['amount'],2) ?></td>
                <td class="text-end"><?= $row['receipt_count'] ?></td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            <!-- Totals Row -->
            <?php if(!empty($rows)): ?>
            <tr class="fw-bold table-light">
                <td>Grand Total</td>
                <?php if(($filters['group_by']??'day') === 'day'): ?>
                <td colspan="5" class="text-end">—</td>
                <td class="text-end text-success fs-6">₹<?= number_format($grandTotal,2) ?></td>
                <td class="text-end text-muted"><?= array_sum(array_column($rows,'receipt_count')) ?></td>
                <?php elseif(($filters['group_by']??'') === 'course'): ?>
                <td class="text-end">₹<?= number_format(array_sum(array_column($rows,'gross')),2) ?></td>
                <td class="text-end text-success">₹<?= number_format(array_sum(array_column($rows,'collected')),2) ?></td>
                <td class="text-end text-danger">₹<?= number_format(array_sum(array_column($rows,'pending')),2) ?></td>
                <td class="text-end"><?= array_sum(array_column($rows,'students')) ?></td>
                <td></td>
                <?php elseif(($filters['group_by']??'') === 'mode'): ?>
                <td class="text-end text-success fs-6">₹<?= number_format($grandTotal,2) ?></td>
                <td class="text-end"><?= array_sum(array_column($rows,'receipt_count')) ?></td>
                <td colspan="2"></td>
                <?php else: ?>
                <td></td>
                <td class="text-end text-success fs-6">₹<?= number_format($grandTotal,2) ?></td>
                <td class="text-end"><?= array_sum(array_column($rows,'receipt_count')) ?></td>
                <?php endif; ?>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#collectionTable').DataTable({ order: [[0,'desc']], pageLength: 50, dom: 'lBfrtip',
        buttons: ['copy','csv','print'], searching: false, paging: false, info: false });
});
</script>
