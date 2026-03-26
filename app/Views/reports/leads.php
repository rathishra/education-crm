<?php $pageTitle = 'Lead Report'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user-plus me-2"></i>Lead Report</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
                <li class="breadcrumb-item active">Leads</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('reports/leads/export') ?>" class="btn btn-outline-success">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<!-- Date Filters -->
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
            <div class="col-md-2">
                <label class="form-label form-label-sm">Source</label>
                <select class="form-select form-select-sm" name="source_id">
                    <option value="">All Sources</option>
                    <?php foreach ($sources as $src): ?>
                    <option value="<?= $src['id'] ?>" <?= ($filters['source_id'] ?? '') == $src['id'] ? 'selected' : '' ?>><?= e($src['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $st): ?>
                    <option value="<?= e($st) ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
                <a href="<?= url('reports/leads') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small opacity-75">Total Leads</div>
                        <div class="fs-3 fw-bold"><?= number_format($totals['total'] ?? 0) ?></div>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50 align-self-center"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small opacity-75">Converted</div>
                        <div class="fs-3 fw-bold"><?= number_format($totals['converted'] ?? 0) ?></div>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50 align-self-center"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small opacity-75">Conversion Rate</div>
                        <?php $rate = $totals['total'] > 0 ? round(($totals['converted'] / $totals['total']) * 100, 1) : 0; ?>
                        <div class="fs-3 fw-bold"><?= $rate ?>%</div>
                    </div>
                    <i class="fas fa-percentage fa-2x opacity-50 align-self-center"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small opacity-75">In Pipeline</div>
                        <div class="fs-3 fw-bold"><?= number_format(($totals['total'] ?? 0) - ($totals['converted'] ?? 0) - ($totals['lost'] ?? 0)) ?></div>
                    </div>
                    <i class="fas fa-filter fa-2x opacity-50 align-self-center"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart: Leads by Day -->
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-chart-bar me-2"></i>Leads by Day</div>
    <div class="card-body">
        <canvas id="leadsByDayChart" height="80"></canvas>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- By Source -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-share-alt me-2"></i>By Source</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Source</th><th class="text-center">Leads</th><th class="text-center">Converted</th><th class="text-end">Rate</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($bySource)): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($bySource as $row): ?>
                        <tr>
                            <td><?= e($row['source'] ?? '—') ?></td>
                            <td class="text-center"><?= number_format($row['count'] ?? 0) ?></td>
                            <td class="text-center text-success"><?= number_format($row['converted'] ?? 0) ?></td>
                            <td class="text-end"><?= $row['count'] > 0 ? round(($row['converted'] / $row['count']) * 100, 1) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- By Status -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-tags me-2"></i>By Status</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Status</th><th class="text-center">Count</th><th class="text-end">%</th></tr>
                    </thead>
                    <tbody>
                    <?php if (empty($byStatus)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No data.</td></tr>
                    <?php else: ?>
                        <?php $total = $totals['total'] ?? 1; foreach ($byStatus as $row): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= ucfirst(e($row['status'] ?? '—')) ?></span></td>
                            <td class="text-center"><?= number_format($row['count'] ?? 0) ?></td>
                            <td class="text-end"><?= $total > 0 ? round(($row['count'] / $total) * 100, 1) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Counselor Performance -->
<div class="card">
    <div class="card-header"><i class="fas fa-headset me-2"></i>Counselor Performance</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Counselor</th>
                    <th class="text-center">Total Leads</th>
                    <th class="text-center">Converted</th>
                    <th class="text-center">Lost</th>
                    <th class="text-center">Followups</th>
                    <th class="text-end">Conv. Rate</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($byCounselor)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">No data.</td></tr>
            <?php else: ?>
                <?php foreach ($byCounselor as $row): ?>
                <tr>
                    <td><?= e($row['counselor_name'] ?? '—') ?></td>
                    <td class="text-center"><?= number_format($row['total'] ?? 0) ?></td>
                    <td class="text-center text-success"><?= number_format($row['converted'] ?? 0) ?></td>
                    <td class="text-center text-danger"><?= number_format($row['lost'] ?? 0) ?></td>
                    <td class="text-center"><?= number_format($row['followups'] ?? 0) ?></td>
                    <td class="text-end fw-bold">
                        <?= $row['total'] > 0 ? round(($row['converted'] / $row['total']) * 100, 1) : 0 ?>%
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    const byDay = <?= json_encode(array_values($byDay ?? [])) ?>;
    const labels = byDay.map(function (r) { return r.date || r.day || ''; });
    const values = byDay.map(function (r) { return parseInt(r.count) || 0; });
    new Chart(document.getElementById('leadsByDayChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Leads',
                data: values,
                backgroundColor: 'rgba(13,110,253,0.7)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
})();
</script>
