<?php $pageTitle = 'Pending Dues Report'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-exclamation-circle me-2 text-danger"></i>Pending Dues Report</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item"><a href="<?= url('fees/reports') ?>">Reports</a></li>
            <li class="breadcrumb-item active">Pending Dues</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('fees/reports/pending/export') ?>?<?= http_build_query($filters) ?>" class="btn btn-outline-danger">
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
        <form method="GET" action="<?= url('fees/reports/pending') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Academic Year</label>
                <select name="academic_year_id" class="form-select form-select-sm">
                    <option value="">All Years</option>
                    <?php foreach($academicYears as $ay): ?>
                    <option value="<?= $ay['id'] ?>" <?= ($filters['academic_year_id']??'')==$ay['id']?'selected':'' ?>><?= e($ay['year_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Course</label>
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['course_id']??'')==$c['id']?'selected':'' ?>><?= e($c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Fee Head</label>
                <select name="fee_head_id" class="form-select form-select-sm">
                    <option value="">All Heads</option>
                    <?php foreach($feeHeads as $h): ?>
                    <option value="<?= $h['id'] ?>" <?= ($filters['fee_head_id']??'')==$h['id']?'selected':'' ?>><?= e($h['head_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Pending & Overdue</option>
                    <option value="pending" <?= ($filters['status']??'')==='pending'?'selected':'' ?>>Pending Only</option>
                    <option value="overdue" <?= ($filters['status']??'')==='overdue'?'selected':'' ?>>Overdue Only</option>
                    <option value="partial" <?= ($filters['status']??'')==='partial'?'selected':'' ?>>Partial</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <div class="form-check mt-1">
                    <input type="checkbox" name="overdue_only" id="overdueOnly" class="form-check-input" value="1" <?= !empty($filters['overdue_only'])?'checked':'' ?>>
                    <label for="overdueOnly" class="form-check-label small">Overdue</label>
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('fees/reports/pending') ?>" class="btn btn-light btn-sm"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<?php if(!empty($summary)): ?>
<div class="row g-3 mb-4">
    <?php foreach([
        ['Total Pending', '₹'.number_format($summary['total_pending'] ?? 0,2), 'danger'],
        ['Overdue Amount', '₹'.number_format($summary['overdue_amount'] ?? 0,2), 'warning'],
        ['Students Pending', $summary['student_count'] ?? 0, 'info'],
        ['Average Per Student', '₹'.number_format($summary['avg_pending'] ?? 0,2), 'secondary'],
    ] as [$label,$val,$color]): ?>
    <div class="col-6 col-md-3">
        <div class="card border-<?= $color ?> border-2 shadow-sm">
            <div class="card-body py-3">
                <div class="text-muted small"><?= $label ?></div>
                <div class="fw-bold fs-5 text-<?= $color ?>"><?= $val ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Pending Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold"><i class="fas fa-table me-2 text-danger"></i>Pending Dues
            <span class="badge bg-danger-subtle text-danger border ms-1"><?= count($rows) ?></span>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="pendingTable">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Student</th><th>Course / Batch</th><th>Fee Head</th>
                    <th class="text-end">Net Amount</th><th class="text-end">Paid</th>
                    <th class="text-end">Balance</th><th class="text-center">Status</th>
                    <th>Due Date</th><th>Days Overdue</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($rows)): ?>
            <tr><td colspan="11" class="text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-2x mb-2 d-block text-success opacity-50"></i>No pending dues found!
            </td></tr>
            <?php else:
                $grandBalance = 0;
                foreach($rows as $i => $row):
                    $grandBalance += $row['balance_amount'];
                    $daysOverdue = !empty($row['due_date']) && strtotime($row['due_date']) < time() && $row['status']!='paid'
                        ? (int)floor((time() - strtotime($row['due_date'])) / 86400) : 0;
            ?>
            <tr class="<?= $daysOverdue > 30 ? 'table-danger' : ($daysOverdue > 0 ? 'table-warning' : '') ?>">
                <td class="text-muted small"><?= $i+1 ?></td>
                <td>
                    <div class="fw-semibold"><?= e($row['student_name']) ?></div>
                    <div class="small text-muted"><?= e($row['roll_number'] ?? $row['enrollment_no'] ?? '') ?></div>
                </td>
                <td>
                    <span class="badge bg-primary-subtle text-primary border"><?= e($row['course_code'] ?? $row['course_name']) ?></span>
                    <?php if($row['batch_name']): ?><div class="small text-muted"><?= e($row['batch_name']) ?></div><?php endif; ?>
                </td>
                <td>
                    <div class="fw-semibold"><?= e($row['head_name']) ?></div>
                    <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem"><?= e($row['head_code'] ?? '') ?></span>
                </td>
                <td class="text-end">₹<?= number_format($row['net_amount'],2) ?></td>
                <td class="text-end text-success">₹<?= number_format($row['paid_amount'],2) ?></td>
                <td class="text-end fw-bold text-danger">₹<?= number_format($row['balance_amount'],2) ?></td>
                <td class="text-center">
                    <?php $sc=['pending'=>'warning','partial'=>'info','overdue'=>'danger']; $s=$row['status']; ?>
                    <span class="badge bg-<?= $sc[$s]??'secondary' ?>-subtle text-<?= $sc[$s]??'secondary' ?> border"><?= ucfirst($s) ?></span>
                </td>
                <td>
                    <?php if($row['due_date']): ?>
                    <span class="<?= $daysOverdue>0?'text-danger fw-semibold':'' ?>"><?= date('d M Y', strtotime($row['due_date'])) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <?php if($daysOverdue > 0): ?>
                    <span class="badge bg-danger text-white"><?= $daysOverdue ?> days</span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <a href="<?= url('fees/collection') ?>?student_id=<?= $row['student_id'] ?>" class="btn btn-sm btn-success" title="Collect">
                        <i class="fas fa-cash-register"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <!-- Grand Total -->
            <tr class="fw-bold table-light">
                <td colspan="6" class="text-end">Total Balance</td>
                <td class="text-end text-danger fs-6">₹<?= number_format($grandBalance,2) ?></td>
                <td colspan="4"></td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#pendingTable').DataTable({ order: [[9,'desc']], pageLength: 50, dom: 'lBfrtip',
        buttons: ['copy','csv','print'] });
});
</script>
