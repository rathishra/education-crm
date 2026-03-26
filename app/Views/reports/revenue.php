<?php $pageTitle = 'Revenue Report'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-line me-2"></i>Revenue Report</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
                <li class="breadcrumb-item active">Revenue</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('reports/revenue/export') ?>" class="btn btn-outline-success">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label form-label-sm">From Date</label>
                <input type="date" class="form-control form-control-sm" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm">To Date</label>
                <input type="date" class="form-control form-control-sm" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2 align-self-end">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
                <a href="<?= url('reports/revenue') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Total Revenue Card -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75">Total Revenue</div>
                        <div class="fs-2 fw-bold"><?= formatCurrency($totalRevenue ?? 0) ?></div>
                    </div>
                    <i class="fas fa-rupee-sign fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue by Day Chart -->
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-chart-bar me-2"></i>Daily Revenue</div>
    <div class="card-body">
        <canvas id="revenueByDayChart" height="80"></canvas>
    </div>
</div>

<div class="row g-4">
    <!-- By Payment Mode -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-credit-card me-2"></i>By Payment Mode</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Mode</th><th class="text-center">Transactions</th><th class="text-end">Amount</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($byMode)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($byMode as $row): ?>
                        <tr>
                            <td><span class="badge bg-secondary text-uppercase"><?= e($row['payment_mode'] ?? '—') ?></span></td>
                            <td class="text-center"><?= number_format($row['count'] ?? 0) ?></td>
                            <td class="text-end fw-bold text-success"><?= formatCurrency($row['total'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                    <?php if (!empty($byMode)): ?>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="2" class="text-end fw-bold">Total</td>
                            <td class="text-end fw-bold text-success"><?= formatCurrency(array_sum(array_column($byMode, 'total'))) ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- By Course -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>By Course</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Course</th><th class="text-center">Payments</th><th class="text-end">Revenue</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($byCourse)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($byCourse as $row): ?>
                        <tr>
                            <td><?= e($row['course_name'] ?? '—') ?></td>
                            <td class="text-center"><?= number_format($row['count'] ?? 0) ?></td>
                            <td class="text-end fw-bold text-success"><?= formatCurrency($row['total'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const byDay = <?= json_encode(array_values($byDay ?? [])) ?>;
    const labels = byDay.map(function (r) { return r.date || r.day || ''; });
    const values = byDay.map(function (r) { return parseFloat(r.total) || 0; });
    new Chart(document.getElementById('revenueByDayChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue (₹)',
                data: values,
                backgroundColor: 'rgba(25,135,84,0.7)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (v) { return '₹' + v.toLocaleString('en-IN'); }
                    }
                }
            }
        }
    });
})();
</script>
