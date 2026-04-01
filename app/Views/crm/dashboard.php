<?php $pageTitle = 'CRM Pipeline Dashboard'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-chart-line me-2 text-primary"></i>CRM Pipeline Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">CRM Pipeline</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('enquiries') ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-comments me-1"></i>Enquiries</a>
        <a href="<?= url('leads') ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-funnel-dollar me-1"></i>Leads</a>
        <a href="<?= url('admissions') ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-user-graduate me-1"></i>Admissions</a>
    </div>
</div>

<!-- Funnel Pipeline -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-4">
        <div class="row g-0 align-items-center text-center">
            <?php
            $funnelSteps = [
                ['label'=>'Enquiries', 'count'=>$totalEnquiries, 'icon'=>'fa-comments', 'color'=>'#6366f1', 'link'=>'enquiries'],
                ['label'=>'Leads', 'count'=>$totalLeads, 'icon'=>'fa-funnel-dollar', 'color'=>'#3b82f6', 'link'=>'leads'],
                ['label'=>'Admissions', 'count'=>$totalAdmissions, 'icon'=>'fa-file-alt', 'color'=>'#10b981', 'link'=>'admissions'],
                ['label'=>'Enrolled', 'count'=>$totalEnrolled, 'icon'=>'fa-user-graduate', 'color'=>'#f59e0b', 'link'=>'admissions?status=enrolled'],
            ];
            foreach ($funnelSteps as $i => $step):
                $prev = $i > 0 ? $funnelSteps[$i-1]['count'] : 0;
                $convRate = ($i > 0 && $prev > 0) ? round($step['count'] / $prev * 100, 1) : null;
            ?>
            <?php if ($i > 0): ?>
            <div class="col-auto px-2 d-none d-md-block">
                <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-chevron-right text-muted fa-lg"></i>
                    <?php if ($convRate !== null): ?>
                    <span class="badge bg-soft-success text-success mt-1" style="font-size:.65rem;"><?= $convRate ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            <div class="col">
                <a href="<?= url($step['link']) ?>" class="text-decoration-none">
                    <div class="py-3 px-2 rounded-3" style="background:<?= $step['color'] ?>15; border: 2px solid <?= $step['color'] ?>30;">
                        <div class="mb-2" style="color:<?= $step['color'] ?>; font-size:1.6rem;">
                            <i class="fas <?= $step['icon'] ?>"></i>
                        </div>
                        <div class="fw-bold" style="font-size:2rem; color:<?= $step['color'] ?>; line-height:1;">
                            <?= number_format($step['count']) ?>
                        </div>
                        <div class="text-muted small mt-1"><?= $step['label'] ?></div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Quick Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="stat-card stat-sky py-3">
            <div class="stat-icon"><i class="fas fa-inbox"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($enqByStatus['new'] ?? 0) ?></div>
                <div class="stat-label">New Enquiries</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-rose py-3">
            <div class="stat-icon"><i class="fas fa-fire"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($leadByPriority['hot'] ?? 0) ?></div>
                <div class="stat-label">Hot Leads</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-amber py-3">
            <div class="stat-icon"><i class="fas fa-bell"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format(count($todayFollowups)) ?></div>
                <div class="stat-label">Follow-up Due</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($admByStatus['pending'] ?? 0) ?></div>
                <div class="stat-label">Pending Apps</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-emerald py-3">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($admByStatus['enrolled'] ?? 0) ?></div>
                <div class="stat-label">Enrolled</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="stat-card stat-violet py-3">
            <div class="stat-icon"><i class="fas fa-percentage"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $totalLeads > 0 ? round(($admByStatus['enrolled'] ?? 0) / $totalLeads * 100, 1) : 0 ?>%</div>
                <div class="stat-label">Lead→Enroll Rate</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="fas fa-chart-area me-2 text-primary"></i>Enquiries &amp; Leads Trend (6 Months)</span>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="80"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <span class="fw-semibold"><i class="fas fa-chart-pie me-2 text-success"></i>Lead Sources</span>
            </div>
            <div class="card-body">
                <?php if (!empty($sourceStats)): ?>
                <canvas id="sourceChart" height="160"></canvas>
                <?php else: ?>
                <div class="text-center text-muted py-4"><i class="fas fa-chart-pie fa-2x mb-2 d-block opacity-25"></i>No source data</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Follow-ups + Hot Leads row -->
<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="fas fa-phone-volume me-2 text-warning"></i>Pending Follow-ups</span>
                <a href="<?= url('leads?followup_overdue=1') ?>" class="btn btn-sm btn-outline-warning">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($todayFollowups)): ?>
                <div class="text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>All caught up!</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Lead</th>
                                <th>Status</th>
                                <th>Due</th>
                                <th>Mode</th>
                                <th>Counselor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayFollowups as $f): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('leads/'.$f['id']) ?>" class="fw-semibold text-dark text-decoration-none">
                                        <?= e($f['first_name'].' '.$f['last_name']) ?>
                                    </a>
                                    <br><small class="text-muted"><?= e($f['phone']) ?></small>
                                </td>
                                <td>
                                    <span class="badge" style="background-color:<?= e($f['status_color'] ?? '#6c757d') ?>">
                                        <?= e($f['status_name'] ?? '-') ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-danger fw-semibold"><?= date('d M', strtotime($f['next_followup_date'])) ?></small>
                                </td>
                                <td><small class="text-muted"><?= ucfirst(e($f['followup_mode'] ?? '-')) ?></small></td>
                                <td><small><?= e($f['assigned_name'] ?? '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="fas fa-fire me-2 text-danger"></i>Hot Leads</span>
                <a href="<?= url('leads?priority=hot') ?>" class="btn btn-sm btn-outline-danger">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($hotLeads)): ?>
                <div class="text-center py-4 text-muted">No hot leads</div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($hotLeads as $hl): ?>
                    <a href="<?= url('leads/'.$hl['id']) ?>" class="list-group-item list-group-item-action py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold small"><?= e($hl['first_name'].' '.$hl['last_name']) ?></div>
                                <small class="text-muted"><?= e($hl['course_name'] ?? '-') ?></small>
                            </div>
                            <div class="text-end">
                                <?php $score = (int)($hl['lead_score'] ?? 0); ?>
                                <?php if ($score > 0): ?>
                                <div style="width:60px;">
                                    <div class="progress" style="height:4px;">
                                        <div class="progress-bar bg-<?= $score>=70?'success':($score>=40?'warning':'danger') ?>" style="width:<?= min($score,100) ?>%"></div>
                                    </div>
                                    <div class="text-end" style="font-size:.65rem;color:#999;"><?= $score ?></div>
                                </div>
                                <?php endif; ?>
                                <span class="badge mt-1" style="background-color:<?= e($hl['status_color']??'#6c757d') ?>;font-size:.6rem;">
                                    <?= e($hl['status_name']??'-') ?>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Leaderboard + Recent Admissions -->
<div class="row g-3 mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <span class="fw-semibold"><i class="fas fa-trophy me-2 text-warning"></i>Counselor Leaderboard</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($leaderboard)): ?>
                <div class="text-center py-4 text-muted">No data available</div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Counselor</th>
                                <th class="text-center">Leads</th>
                                <th class="text-center">Hot</th>
                                <th class="text-center">Converted</th>
                                <th class="text-center">Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaderboard as $rank => $lb): ?>
                            <tr>
                                <td>
                                    <?php if ($rank === 0): ?>
                                    <i class="fas fa-trophy text-warning"></i>
                                    <?php elseif ($rank === 1): ?>
                                    <i class="fas fa-medal text-secondary"></i>
                                    <?php elseif ($rank === 2): ?>
                                    <i class="fas fa-medal" style="color:#cd7f32;"></i>
                                    <?php else: ?>
                                    <span class="text-muted"><?= $rank+1 ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold small"><?= e($lb['counselor']) ?></td>
                                <td class="text-center"><?= $lb['total_leads'] ?></td>
                                <td class="text-center"><span class="badge bg-danger"><?= $lb['hot_leads'] ?></span></td>
                                <td class="text-center"><span class="badge bg-success"><?= $lb['converted'] ?></span></td>
                                <td class="text-center">
                                    <?php $rate = $lb['total_leads'] > 0 ? round($lb['converted']/$lb['total_leads']*100) : 0; ?>
                                    <span class="badge bg-<?= $rate>=50?'success':($rate>=25?'warning':'secondary') ?>"><?= $rate ?>%</span>
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
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="fas fa-user-graduate me-2 text-success"></i>Recent Admissions</span>
                <a href="<?= url('admissions') ?>" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                <?php
                $admStatusClasses = [
                    'applied'=>'bg-primary','pending'=>'bg-warning text-dark',
                    'document_pending'=>'bg-info','payment_pending'=>'bg-warning text-dark',
                    'confirmed'=>'bg-success','enrolled'=>'bg-success','rejected'=>'bg-danger','cancelled'=>'bg-secondary'
                ];
                ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recentAdmissions as $adm): ?>
                    <a href="<?= url('admissions/'.$adm['id']) ?>" class="list-group-item list-group-item-action py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold small"><?= e($adm['first_name'].' '.$adm['last_name']) ?></div>
                                <small class="text-muted"><?= e($adm['course_name'] ?? '-') ?></small>
                            </div>
                            <div class="text-end">
                                <span class="badge <?= $admStatusClasses[$adm['status']] ?? 'bg-secondary' ?>" style="font-size:.65rem;">
                                    <?= ucfirst(str_replace('_',' ',$adm['status'])) ?>
                                </span>
                                <div style="font-size:.65rem;color:#999;"><?= date('d M', strtotime($adm['created_at'])) ?></div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php if (empty($recentAdmissions)): ?>
                    <div class="text-center py-4 text-muted">No admissions yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trend Chart
    var ctx = document.getElementById('trendChart').getContext('2d');
    var trendMonths = <?= json_encode($trendMonths) ?>;
    var monthLabels = trendMonths.map(function(m) {
        var parts = m.split('-');
        var months = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return months[parseInt(parts[1])] + ' ' + parts[0].substr(2);
    });
    var enqData = trendMonths.map(function(m) { return <?= json_encode($enqTrend) ?>[m] || 0; });
    var leadData = trendMonths.map(function(m) { return <?= json_encode($leadTrend) ?>[m] || 0; });

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [
                {
                    label: 'Enquiries',
                    data: enqData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4
                },
                {
                    label: 'Leads',
                    data: leadData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.07)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 11 } } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } } },
                x: { ticks: { font: { size: 11 } } }
            }
        }
    });

    <?php if (!empty($sourceStats)): ?>
    // Source Doughnut Chart
    var sCtx = document.getElementById('sourceChart').getContext('2d');
    var sourceLabels = <?= json_encode(array_column($sourceStats, 'source_name')) ?>.map(function(s) { return s || 'Unknown'; });
    var sourceData = <?= json_encode(array_map(fn($r) => (int)$r['cnt'], $sourceStats)) ?>;
    var palette = ['#6366f1','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316'];
    new Chart(sCtx, {
        type: 'doughnut',
        data: {
            labels: sourceLabels,
            datasets: [{ data: sourceData, backgroundColor: palette, borderWidth: 2 }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12, padding: 8 } }
            }
        }
    });
    <?php endif; ?>
});
</script>
