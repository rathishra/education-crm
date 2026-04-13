<?php
$pageTitle = 'Timetable Generator Analytics';
$breadcrumbs = [
    ['label'=>'Academic'],
    ['label'=>'Timetable','url'=>url('academic/timetable')],
    ['label'=>'Generator','url'=>url('academic/timetable/generator')],
    ['label'=>'Analytics'],
];

// Prepare chart data
$runLabels  = array_column($runHistory, 'run_name');
$runScores  = array_map(fn($r) => (float)$r['score'], $runHistory);
$runDates   = array_column($runHistory, 'created_at');

$facultyNames = array_column($facultyLoad, 'name');
$facultyHours = array_column($facultyLoad, 'total_periods');

$dayLabels   = array_column($dayLoad, 'day_of_week');
$dayCounts   = array_column($dayLoad, 'slot_count');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-chart-bar text-primary me-2"></i>Generator Analytics</h4>
        <p class="text-muted small mb-0">Quality metrics, faculty load distribution, and run history analysis.</p>
    </div>
    <a href="<?= url('academic/timetable/generator') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<!-- Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-primary"><?= (int)($summary['total_runs'] ?? 0) ?></div>
                <div class="text-muted small">Total Runs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-success"><?= number_format((float)($summary['avg_score'] ?? 0), 1) ?>%</div>
                <div class="text-muted small">Avg Quality Score</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-warning"><?= (int)($summary['total_conflicts'] ?? 0) ?></div>
                <div class="text-muted small">Total Conflicts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-3 fw-bold text-info"><?= (int)($summary['approved_count'] ?? 0) ?></div>
                <div class="text-muted small">Approved Runs</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Run Score History -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-chart-line me-2 text-muted"></i>Run Quality History</h6>
            </div>
            <div class="card-body">
                <?php if (empty($runHistory)): ?>
                    <div class="text-center py-4 text-muted opacity-50">No runs yet</div>
                <?php else: ?>
                    <canvas id="scoreChart" height="280"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Day Load -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-calendar-day me-2 text-muted"></i>Slots by Day (Latest Approved)</h6>
            </div>
            <div class="card-body">
                <?php if (empty($dayLoad)): ?>
                    <div class="text-center py-4 text-muted opacity-50">No approved run data</div>
                <?php else: ?>
                    <canvas id="dayChart" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Faculty Load -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-chalkboard-teacher me-2 text-muted"></i>Faculty Load (Latest Approved)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($facultyLoad)): ?>
                    <div class="text-center py-4 text-muted opacity-50">No data</div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Faculty</th>
                                    <th class="text-center">Periods/Week</th>
                                    <th>Load</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $maxLoad = max(array_column($facultyLoad, 'total_periods') ?: [1]);
                                foreach ($facultyLoad as $fl):
                                    $pct = round($fl['total_periods'] / $maxLoad * 100);
                                    $color = $pct > 80 ? 'danger' : ($pct > 50 ? 'warning' : 'success');
                                ?>
                                    <tr>
                                        <td><?= e($fl['name']) ?></td>
                                        <td class="text-center fw-semibold"><?= (int)$fl['total_periods'] ?></td>
                                        <td style="min-width:120px">
                                            <div class="progress" style="height:6px">
                                                <div class="progress-bar bg-<?= $color ?>" style="width:<?= $pct ?>%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Subject Distribution -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-book me-2 text-muted"></i>Subject Distribution (Latest Approved)</h6>
            </div>
            <div class="card-body p-0">
                <?php if (empty($subjectDist)): ?>
                    <div class="text-center py-4 text-muted opacity-50">No data</div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Subject</th>
                                    <th class="text-center">Slots</th>
                                    <th class="text-center">Sections</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjectDist as $sd): ?>
                                    <tr>
                                        <td>
                                            <div><?= e($sd['subject_name']) ?></div>
                                            <small class="text-muted"><?= e($sd['subject_code']) ?></small>
                                        </td>
                                        <td class="text-center fw-semibold"><?= (int)$sd['total_slots'] ?></td>
                                        <td class="text-center"><?= (int)$sd['section_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Runs Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-history me-2 text-muted"></i>All Runs</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Run</th>
                                <th>Config</th>
                                <th class="text-center">Score</th>
                                <th class="text-center">Assigned</th>
                                <th class="text-center">Conflicts</th>
                                <th class="text-center">Time (ms)</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statusMap = [
                                'pending'   => ['warning','clock'],
                                'running'   => ['info','spinner fa-spin'],
                                'completed' => ['primary','check'],
                                'approved'  => ['success','check-circle'],
                                'failed'    => ['danger','times-circle'],
                                'discarded' => ['secondary','ban'],
                            ];
                            foreach ($allRuns as $r):
                                [$sc, $ic] = $statusMap[$r['status']] ?? ['secondary','question'];
                                $pc = (float)$r['score'] >= 90 ? 'success' : ((float)$r['score'] >= 70 ? 'warning' : 'danger');
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= e($r['run_name']) ?></div>
                                        <small class="text-muted">#<?= $r['id'] ?></small>
                                    </td>
                                    <td><?= e($r['config_name'] ?? '—') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $pc ?> bg-opacity-10 text-<?= $pc ?>">
                                            <?= number_format((float)$r['score'], 1) ?>%
                                        </span>
                                    </td>
                                    <td class="text-center"><?= $r['assigned_count'] ?>/<?= $r['total_requirements'] ?></td>
                                    <td class="text-center">
                                        <?php if ($r['conflict_count'] > 0): ?>
                                            <span class="badge bg-danger"><?= $r['conflict_count'] ?></span>
                                        <?php else: ?>
                                            <i class="fas fa-check text-success"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-muted"><?= number_format((int)$r['duration_ms']) ?></td>
                                    <td><span class="badge bg-<?= $sc ?> bg-opacity-10 text-<?= $sc ?>"><i class="fas fa-<?= $ic ?> me-1"></i><?= ucfirst($r['status']) ?></span></td>
                                    <td><small class="text-muted"><?= date('d M Y, H:i', strtotime($r['created_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
<?php if (!empty($runHistory)): ?>
new Chart(document.getElementById('scoreChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($r) => date('d M', strtotime($r['created_at'])), $runHistory)) ?>,
        datasets: [{
            label: 'Quality Score (%)',
            data: <?= json_encode($runScores) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.08)',
            tension: 0.3,
            fill: true,
            pointRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { min: 0, max: 100, ticks: { callback: v => v + '%' } }
        }
    }
});
<?php endif; ?>

<?php if (!empty($dayLoad)): ?>
new Chart(document.getElementById('dayChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map('ucfirst', $dayLabels)) ?>,
        datasets: [{
            label: 'Slots',
            data: <?= json_encode(array_map('intval', $dayCounts)) ?>,
            backgroundColor: 'rgba(13,110,253,0.7)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
<?php endif; ?>
</script>
