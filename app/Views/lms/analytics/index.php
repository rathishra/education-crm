<style>
.kpi-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; padding:1.1rem 1.25rem; transition:box-shadow .15s; }
.kpi-card:hover { box-shadow:0 4px 16px rgba(99,102,241,.1); }
.kpi-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.kpi-val { font-size:1.7rem; font-weight:900; line-height:1; color:#0f172a; }
.kpi-lbl { font-size:.72rem; color:#64748b; margin-top:.2rem; }
.chart-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; padding:1.25rem; }
.range-tab { font-size:.75rem; font-weight:600; padding:.3rem .75rem; border-radius:20px; border:1px solid #e2e8f0; color:#64748b; background:#fff; text-decoration:none; transition:all .12s; }
.range-tab.active { background:#6366f1; color:#fff; border-color:#6366f1; }
.lb-rank { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:800; flex-shrink:0; }
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-chart-line me-2 text-primary"></i>LMS Analytics</h4>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <?php foreach (['7'=>'7d','30'=>'30d','90'=>'90d','365'=>'1y'] as $v=>$l): ?>
        <a href="?range=<?= $v ?>" class="range-tab <?= $range===$v?'active':'' ?>"><?= $l ?></a>
        <?php endforeach; ?>
        <a href="<?= url('elms/analytics/courses') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-book-open me-1"></i>By Course</a>
    </div>
</div>

<!-- KPI cards -->
<div class="row g-3 mb-4">
<?php
$kpiDefs = [
    ['total_learners',   'Total Learners',   'fas fa-user-graduate', '#ede9fe','#6366f1'],
    ['active_learners',  'Active Learners',  'fas fa-fire',          '#fce7f3','#db2777'],
    ['total_enrollments','Enrollments',      'fas fa-list-check',    '#dbeafe','#2563eb'],
    ['new_enrollments',  'New Enrollments',  'fas fa-user-plus',     '#d1fae5','#059669'],
    ['completion_rate',  'Completion Rate',  'fas fa-trophy',        '#fef3c7','#d97706'],
    ['quiz_pass_rate',   'Quiz Pass Rate',   'fas fa-question-circle','#e0f2fe','#0284c7'],
    ['avg_progress',     'Avg Progress',     'fas fa-tasks',         '#f3e8ff','#9333ea'],
    ['forum_posts',      'Forum Posts',      'fas fa-comments',      '#ecfdf5','#10b981'],
];
foreach ($kpiDefs as [$key, $label, $icon, $bg, $color]):
    $val = $kpis[$key] ?? 0;
    $display = in_array($key, ['completion_rate','quiz_pass_rate','avg_progress']) ? $val.'%' : number_format((int)$val);
?>
<div class="col-6 col-md-3">
    <div class="kpi-card d-flex gap-3 align-items-center">
        <div class="kpi-icon" style="background:<?= $bg ?>"><i class="<?= $icon ?>" style="color:<?= $color ?>"></i></div>
        <div>
            <div class="kpi-val"><?= $display ?></div>
            <div class="kpi-lbl"><?= $label ?></div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
    <!-- Enrollment trend line chart -->
    <div class="col-12 col-lg-8">
        <div class="chart-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-users me-2 text-primary"></i>Enrollment Trend</div>
            <canvas id="enrollChart" height="100"></canvas>
        </div>
    </div>

    <!-- Progress distribution donut -->
    <div class="col-12 col-lg-4">
        <div class="chart-card h-100">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-chart-pie me-2 text-primary"></i>Progress Distribution</div>
            <canvas id="progressChart" height="160"></canvas>
            <div class="mt-2" id="progressLegend"></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Top courses horizontal bar -->
    <div class="col-12 col-lg-7">
        <div class="chart-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="fw-bold small" style="color:#0f172a"><i class="fas fa-book-open me-2 text-primary"></i>Top Courses by Enrollment</span>
            </div>
            <?php if (empty($topCourses)): ?>
            <div class="text-center text-muted py-4 small">No course data yet</div>
            <?php else: ?>
            <?php $maxEnroll = max(array_column($topCourses,'enrollments')) ?: 1; ?>
            <?php foreach ($topCourses as $c): ?>
            <?php $pct = round($c['enrollments']/$maxEnroll*100); ?>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1" style="font-size:.8rem">
                    <a href="<?= url('elms/analytics/course/'.$c['id']) ?>" class="text-decoration-none fw-semibold" style="color:#0f172a;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:65%"><?= e($c['title']) ?></a>
                    <div class="text-muted flex-shrink-0 ms-2">
                        <?= $c['enrollments'] ?> enrolled &middot; <?= $c['completion_rate'] ?? 0 ?>% complete
                    </div>
                </div>
                <div style="background:#f1f5f9;border-radius:8px;height:8px;overflow:hidden">
                    <div style="width:<?= $pct ?>%;background:linear-gradient(90deg,#6366f1,#8b5cf6);height:100%;border-radius:8px"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Content type doughnut -->
    <div class="col-12 col-lg-5">
        <div class="chart-card h-100">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-layer-group me-2 text-primary"></i>Content Type Breakdown</div>
            <?php if (!empty($contentTypes)): ?>
            <canvas id="contentChart" height="140"></canvas>
            <?php else: ?>
            <div class="text-center text-muted py-4 small">No lessons created yet</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Quiz pass rates table -->
    <div class="col-12 col-lg-7">
        <div class="chart-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-question-circle me-2 text-primary"></i>Quiz Performance</div>
            <?php if (empty($quizPassRates)): ?>
            <div class="text-center text-muted py-3 small">No quiz attempts yet</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size:.78rem">
                    <thead style="background:#f8f7ff">
                        <tr>
                            <th class="fw-semibold" style="color:#64748b">Quiz</th>
                            <th class="fw-semibold text-center" style="color:#64748b">Attempts</th>
                            <th class="fw-semibold text-center" style="color:#64748b">Avg Score</th>
                            <th class="fw-semibold" style="color:#64748b">Pass Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($quizPassRates as $q): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold" style="color:#0f172a"><?= e(mb_strimwidth($q['title'],0,28,'…')) ?></div>
                            <div class="text-muted" style="font-size:.68rem"><?= e($q['course_title']) ?></div>
                        </td>
                        <td class="text-center"><?= $q['attempts'] ?></td>
                        <td class="text-center fw-bold" style="color:#4f46e5"><?= $q['avg_score'] ?>%</td>
                        <td>
                            <?php $pr = (float)$q['pass_rate']; ?>
                            <div class="d-flex align-items-center gap-2">
                                <div style="flex:1;background:#f1f5f9;border-radius:10px;height:6px;overflow:hidden">
                                    <div style="width:<?= $pr ?>%;background:<?= $pr>=60?'#22c55e':'#ef4444' ?>;height:100%;border-radius:10px"></div>
                                </div>
                                <span class="fw-bold" style="color:<?= $pr>=60?'#16a34a':'#dc2626' ?>;min-width:36px"><?= $pr ?>%</span>
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

    <!-- XP Leaderboard -->
    <div class="col-12 col-lg-5">
        <div class="chart-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-crown me-2 text-warning"></i>XP Leaderboard</div>
            <?php if (empty($leaderboard)): ?>
            <div class="text-center text-muted py-3 small">No learner data yet</div>
            <?php else: ?>
            <?php $rankColors = ['#f59e0b','#94a3b8','#b45309','#6366f1','#6366f1','#6366f1','#6366f1','#6366f1','#6366f1','#6366f1']; ?>
            <?php foreach ($leaderboard as $i => $lb): ?>
            <div class="d-flex align-items-center gap-2 mb-2 py-1">
                <div class="lb-rank" style="background:<?= $i<3?$rankColors[$i].'22':'#f1f5f9' ?>;color:<?= $rankColors[$i] ?>"><?= $i+1 ?></div>
                <div style="flex:1;min-width:0">
                    <div class="fw-semibold" style="font-size:.8rem;color:#0f172a;overflow:hidden;white-space:nowrap;text-overflow:ellipsis">
                        <a href="<?= url('elms/analytics/student/'.$lb['id']) ?>" class="text-decoration-none" style="color:inherit"><?= e($lb['name']) ?></a>
                    </div>
                    <div class="text-muted" style="font-size:.68rem"><?= $lb['courses'] ?> courses &middot; Lv.<?= $lb['level'] ?></div>
                </div>
                <div class="text-end flex-shrink-0">
                    <div class="fw-bold" style="font-size:.82rem;color:#f59e0b"><?= number_format($lb['xp_points']) ?> XP</div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
Chart.defaults.color = '#64748b';

// ── Enrollment trend ──
<?php
$days   = [];
$counts = [];
$dateMap = array_column($enrollTrend, 'cnt', 'day');
for ($i = $rangeInt - 1; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $days[]   = date('d M', strtotime($d));
    $counts[] = (int)($dateMap[$d] ?? 0);
}
?>
new Chart(document.getElementById('enrollChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($days) ?>,
        datasets: [{
            label: 'New Enrollments',
            data: <?= json_encode($counts) ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99,102,241,.08)',
            borderWidth: 2,
            pointRadius: <?= $rangeInt <= 30 ? 3 : 0 ?>,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1 } },
            x: { grid: { display: false }, ticks: { maxTicksLimit: <?= min($rangeInt, 12) ?> } }
        },
        interaction: { intersect: false, mode: 'index' },
    }
});

// ── Progress distribution donut ──
<?php
$buckets = [
    'Not Started'  => (int)($progressBuckets['not_started'] ?? 0),
    '1–25%'        => (int)($progressBuckets['bucket_25']   ?? 0),
    '26–50%'       => (int)($progressBuckets['bucket_50']   ?? 0),
    '51–75%'       => (int)($progressBuckets['bucket_75']   ?? 0),
    '76–99%'       => (int)($progressBuckets['bucket_99']   ?? 0),
    'Completed'    => (int)($progressBuckets['completed']   ?? 0),
];
$bucketColors = ['#e2e8f0','#fbbf24','#f59e0b','#60a5fa','#6366f1','#22c55e'];
?>
const progressCtx = document.getElementById('progressChart');
if (progressCtx) {
    new Chart(progressCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($buckets)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($buckets)) ?>,
                backgroundColor: <?= json_encode($bucketColors) ?>,
                borderWidth: 2, borderColor: '#fff',
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 11 } } }
            },
            cutout: '65%',
        }
    });
}

// ── Content types doughnut ──
<?php
$ctLabels = array_map(fn($r) => ucfirst(str_replace('_',' ',$r['type'])), $contentTypes);
$ctCounts = array_column($contentTypes, 'cnt');
$ctColors = ['#6366f1','#0284c7','#22c55e','#f59e0b','#ef4444','#a855f7','#06b6d4'];
?>
const contentCtx = document.getElementById('contentChart');
if (contentCtx) {
    new Chart(contentCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($ctLabels) ?>,
            datasets: [{
                data: <?= json_encode($ctCounts) ?>,
                backgroundColor: <?= json_encode(array_slice($ctColors, 0, count($ctLabels))) ?>,
                borderWidth: 2, borderColor: '#fff',
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 11 } } } },
            cutout: '60%',
        }
    });
}
</script>
