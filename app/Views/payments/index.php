<?php $pageTitle = 'Payments'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-rupee-sign me-2"></i>Payments</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Payments</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('payments.create')): ?>
        <a href="<?= url('payments/collect') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Collect Payment
        </a>
        <?php endif; ?>
        <a href="<?= url('payments/due-list') ?>" class="btn btn-outline-warning">
            <i class="fas fa-exclamation-circle me-1"></i>Due List
        </a>
    </div>
</div>

<!-- Today's Collection -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75">Today's Collection</div>
                        <div class="fs-4 fw-bold"><?= formatCurrency($todayCollection ?? 0) ?></div>
                    </div>
                    <i class="fas fa-wallet fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Student name, phone, receipt..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       value="<?= e($dateFrom ?? '') ?>" placeholder="From date">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       value="<?= e($dateTo ?? '') ?>" placeholder="To date">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="mode">
                    <option value="">All Modes</option>
                    <?php foreach (['cash' => 'Cash', 'card' => 'Card', 'upi' => 'UPI', 'cheque' => 'Cheque', 'neft' => 'NEFT', 'dd' => 'DD'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($mode ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('payments') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Receipt #</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th class="text-end">Amount</th>
                        <th>Mode</th>
                        <th>Date</th>
                        <th>Collected By</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($payments['data'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No payments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments['data'] as $payment): ?>
                    <?php
                    $modeColors = ['cash' => 'success', 'card' => 'primary', 'upi' => 'info', 'cheque' => 'warning', 'neft' => 'secondary', 'dd' => 'dark'];
                    $modeColor = $modeColors[$payment['payment_mode']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><code><?= e($payment['receipt_number'] ?? '—') ?></code></td>
                        <td>
                            <strong><?= e($payment['student_name'] ?? '—') ?></strong>
                            <small class="text-muted d-block"><?= e($payment['student_id_number'] ?? '') ?></small>
                        </td>
                        <td><?= e($payment['course_name'] ?? '—') ?></td>
                        <td class="text-end fw-bold text-success"><?= formatCurrency($payment['amount'] ?? 0) ?></td>
                        <td><span class="badge bg-<?= $modeColor ?> text-uppercase"><?= e($payment['payment_mode'] ?? '—') ?></span></td>
                        <td><?= !empty($payment['payment_date']) ? formatDate($payment['payment_date']) : '—' ?></td>
                        <td><?= e($payment['collected_by_name'] ?? '—') ?></td>
                        <td class="text-end">
                            <a href="<?= url('payments/' . $payment['id'] . '/receipt') ?>" class="btn btn-sm btn-outline-success" title="View Receipt" target="_blank">
                                <i class="fas fa-receipt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($payments['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $payments;
        $baseUrl = url('payments') . '?' . http_build_query(array_filter(compact('search', 'dateFrom', 'dateTo', 'mode') ?? []));
        include BASE_PATH . '/app/Views/partials/pagination.php';
        ?>
    </div>
    <?php endif; ?>
</div>
