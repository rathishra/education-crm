<?php $pageTitle = 'Admission Report'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user-graduate me-2"></i>Admission Report</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
                <li class="breadcrumb-item active">Admissions</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('reports/admissions/export') ?>" class="btn btn-outline-success">
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
            <div class="col-md-3">
                <label class="form-label form-label-sm">Course</label>
                <select class="form-select form-select-sm" name="course_id">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>" <?= ($filters['course_id'] ?? '') == $course['id'] ? 'selected' : '' ?>><?= e($course['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label form-label-sm">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Statuses</option>
                    <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'enrolled' => 'Enrolled'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
                <a href="<?= url('reports/admissions') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Stat Cards by Status -->
<div class="row g-3 mb-4">
    <?php
    $statusConfig = [
        'pending'  => ['color' => 'warning',   'icon' => 'fa-hourglass-half', 'label' => 'Pending'],
        'approved' => ['color' => 'info',       'icon' => 'fa-check',         'label' => 'Approved'],
        'enrolled' => ['color' => 'success',    'icon' => 'fa-user-graduate', 'label' => 'Enrolled'],
        'rejected' => ['color' => 'danger',     'icon' => 'fa-times-circle',  'label' => 'Rejected'],
    ];
    foreach ($byStatus as $row):
        $cfg = $statusConfig[$row['status']] ?? ['color' => 'secondary', 'icon' => 'fa-circle', 'label' => ucfirst($row['status'])];
    ?>
    <div class="col-md-3">
        <div class="card border-0 bg-<?= $cfg['color'] ?> text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="small opacity-75"><?= $cfg['label'] ?></div>
                        <div class="fs-3 fw-bold"><?= number_format($row['count'] ?? 0) ?></div>
                    </div>
                    <i class="fas <?= $cfg['icon'] ?> fa-2x opacity-50 align-self-center"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Admissions by Day Chart -->
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-chart-line me-2"></i>Admissions by Day</div>
    <div class="card-body">
        <canvas id="admByDayChart" height="80"></canvas>
    </div>
</div>

<!-- By Course Table -->
<div class="card">
    <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>Admissions by Course</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Course</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Pending</th>
                    <th class="text-center">Approved</th>
                    <th class="text-center">Enrolled</th>
                    <th class="text-center">Rejected</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($byCourse)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No data available.</td></tr>
            <?php else: ?>
                <?php foreach ($byCourse as $row): ?>
                <tr>
                    <td><?= e($row['course_name'] ?? '—') ?></td>
                    <td class="text-center fw-bold"><?= number_format($row['total'] ?? 0) ?></td>
                    <td class="text-center text-warning"><?= number_format($row['pending'] ?? 0) ?></td>
                    <td class="text-center text-info"><?= number_format($row['approved'] ?? 0) ?></td>
                    <td class="text-center text-success"><?= number_format($row['enrolled'] ?? 0) ?></td>
                    <td class="text-center text-danger"><?= number_format($row['rejected'] ?? 0) ?></td>
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
    new Chart(document.getElementById('admByDayChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Admissions',
                data: values,
                borderColor: 'rgba(25,135,84,1)',
                backgroundColor: 'rgba(25,135,84,0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4
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
