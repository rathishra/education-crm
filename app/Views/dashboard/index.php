<?php $pageTitle = 'Dashboard'; ?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-th-large"></i>Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="d-flex align-items-center gap-1 px-3 py-2 rounded-10" style="background:#fff;border:1.5px solid #e2e8f0;font-size:.8rem;color:#64748b">
            <i class="fas fa-calendar-day text-primary" style="font-size:.75rem"></i>
            <span class="fw-600"><?= date('l, d M Y') ?></span>
        </div>
        <a href="<?= url('reports') ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
    </div>
</div>

<!-- ============================================================
     KPI STAT CARDS — ROW 1
     ============================================================ -->
<div class="row g-3 mb-4">

    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card stat-indigo h-100 mb-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Total Leads</div>
                    <div class="stat-value"><?= number_format($stats['total_leads']) ?></div>
                    <div class="stat-sub">
                        <span class="text-success fw-600"><i class="fas fa-arrow-up fa-xs"></i> <?= $stats['new_leads_today'] ?></span>
                        <span class="text-muted">new today</span>
                    </div>
                </div>
                <div class="stat-icon-wrap stat-indigo">
                    <i class="fas fa-funnel-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card stat-emerald h-100 mb-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Conversions</div>
                    <div class="stat-value"><?= number_format($stats['converted_leads']) ?></div>
                    <div class="stat-sub">
                        <span class="text-primary fw-600"><?= $stats['conversion_rate'] ?>%</span>
                        <span class="text-muted">conversion rate</span>
                    </div>
                </div>
                <div class="stat-icon-wrap stat-emerald">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card stat-sky h-100 mb-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Active Students</div>
                    <div class="stat-value"><?= number_format($stats['total_students']) ?></div>
                    <div class="stat-sub">
                        <span class="text-warning fw-600"><?= $stats['pending_admissions'] ?></span>
                        <span class="text-muted">pending admissions</span>
                    </div>
                </div>
                <div class="stat-icon-wrap stat-sky">
                    <i class="fas fa-user-graduate"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card stat-amber h-100 mb-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Revenue (Month)</div>
                    <div class="stat-value" style="font-size:1.5rem"><?= formatCurrency($stats['revenue_month']) ?></div>
                    <div class="stat-sub">
                        <span class="text-danger fw-600">Due: <?= formatCurrency($stats['total_dues']) ?></span>
                    </div>
                </div>
                <div class="stat-icon-wrap stat-amber">
                    <i class="fas fa-rupee-sign"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- KPI ROW 2 -->
<div class="row g-3 mb-4">

    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card stat-violet h-100 mb-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Enquiries (Month)</div>
                    <div class="stat-value"><?= number_format($stats['enquiries_month']) ?></div>
                    <div class="stat-sub"><span class="text-muted">This month</span></div>
                </div>
                <div class="stat-icon-wrap stat-violet">
                    <i class="fas fa-comments"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card stat-rose h-100 mb-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Follow-ups Today</div>
                    <div class="stat-value"><?= number_format($stats['pending_followups']) ?></div>
                    <div class="stat-sub"><span class="text-muted">Pending</span></div>
                </div>
                <div class="stat-icon-wrap stat-rose">
                    <i class="fas fa-phone-volume"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card stat-teal h-100 mb-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">New Leads (Month)</div>
                    <div class="stat-value"><?= number_format($stats['new_leads_month']) ?></div>
                    <div class="stat-sub"><span class="text-muted">This month</span></div>
                </div>
                <div class="stat-icon-wrap stat-teal">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card stat-card stat-orange h-100 mb-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Pending Admissions</div>
                    <div class="stat-value"><?= number_format($stats['pending_admissions']) ?></div>
                    <div class="stat-sub"><a href="<?= url('admissions') ?>" class="text-primary fw-600 small">View all</a></div>
                </div>
                <div class="stat-icon-wrap stat-orange">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================
     LEAD PIPELINE
     ============================================================ -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-stream"></i> Lead Pipeline</span>
        <a href="<?= url('leads') ?>" class="btn btn-outline-primary btn-sm">View All Leads</a>
    </div>
    <div class="card-body pb-3">
        <div class="row g-2">
            <?php foreach ($pipeline as $stage): ?>
            <div class="col">
                <a href="<?= url('leads?status=' . urlencode($stage['name'])) ?>" class="text-decoration-none">
                    <div class="pipeline-stage" style="background: linear-gradient(135deg, <?= e($stage['color']) ?>, <?= e($stage['color']) ?>cc)">
                        <div class="stage-count"><?= number_format($stage['count'] ?? 0) ?></div>
                        <small><?= e($stage['name']) ?></small>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ============================================================
     CHARTS ROW
     ============================================================ -->
<div class="row g-4 mb-4">

    <!-- Lead Trend -->
    <div class="col-xl-8">
        <div class="card h-100 mb-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-area"></i> Lead Trend — Last 6 Months</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="status-dot active"></span>
                    <small class="text-muted fw-600">Leads</small>
                </div>
            </div>
            <div class="card-body">
                <canvas id="leadTrendChart" height="110"></canvas>
            </div>
        </div>
    </div>

    <!-- Source Distribution -->
    <div class="col-xl-4">
        <div class="card h-100 mb-0">
            <div class="card-header">
                <i class="fas fa-chart-donut"></i> Lead Sources
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="sourceChart" height="200" style="max-height:220px"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================
     TABLES ROW
     ============================================================ -->
<div class="row g-4">

    <!-- Recent Leads -->
    <div class="col-xl-7">
        <div class="card mb-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users"></i> Recent Leads</span>
                <a href="<?= url('leads') ?>" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLeads)): ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state py-4">
                                        <i class="fas fa-users"></i>
                                        <h6>No leads yet</h6>
                                        <p><a href="<?= url('leads/create') ?>">Add your first lead</a></p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recentLeads as $lead): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('leads/' . $lead['id']) ?>" class="fw-600 text-primary">
                                        <?= e($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?>
                                    </a>
                                    <?php if (!empty($lead['email'])): ?>
                                    <div class="small text-muted"><?= e($lead['email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= e($lead['phone']) ?></td>
                                <td><small class="text-muted"><?= e($lead['source_name'] ?? '—') ?></small></td>
                                <td>
                                    <span class="badge" style="background:<?= e($lead['status_color'] ?? '#4f46e5') ?>22;color:<?= e($lead['status_color'] ?? '#4f46e5') ?>;border:1px solid <?= e($lead['status_color'] ?? '#4f46e5') ?>44">
                                        <?= e($lead['status_name'] ?? '—') ?>
                                    </span>
                                </td>
                                <td><small class="text-muted"><?= timeAgo($lead['created_at']) ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Follow-ups + Activity -->
    <div class="col-xl-5">

        <!-- Upcoming Follow-ups -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-phone-volume"></i> Upcoming Follow-ups</span>
                <a href="<?= url('followups') ?>" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcomingFollowups)): ?>
                <div class="empty-state py-4">
                    <i class="fas fa-calendar-check"></i>
                    <h6>No upcoming follow-ups</h6>
                    <p><a href="<?= url('followups/create') ?>">Schedule one now</a></p>
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($upcomingFollowups, 0, 5) as $fu): ?>
                    <?php
                    $typeIcon = match($fu['type'] ?? '') {
                        'call'    => 'fa-phone',
                        'email'   => 'fa-envelope',
                        'meeting' => 'fa-handshake',
                        default   => 'fa-calendar'
                    };
                    ?>
                    <div class="list-group-item">
                        <div class="d-flex align-items-start gap-3">
                            <div class="activity-icon flex-shrink-0" style="background:#eef2ff">
                                <i class="fas <?= $typeIcon ?>" style="color:#4f46e5"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-600 small text-truncate">
                                    <?= e(($fu['lead_first_name'] ?? '') . ' ' . ($fu['lead_last_name'] ?? '')) ?>
                                </div>
                                <div class="small text-muted text-truncate"><?= e($fu['subject']) ?></div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div class="small text-muted"><?= formatDateTime($fu['scheduled_at']) ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mb-0">
            <div class="card-header">
                <i class="fas fa-activity"></i> Recent Activity
            </div>
            <div class="card-body">
                <?php if (empty($recentActivities)): ?>
                <div class="empty-state py-3">
                    <i class="fas fa-history"></i>
                    <h6>No recent activity</h6>
                </div>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach (array_slice($recentActivities, 0, 7) as $activity): ?>
                    <div class="timeline-item">
                        <div class="fw-600 small"><?= e($activity['title']) ?></div>
                        <div class="small text-muted">
                            <?= e(($activity['lead_first_name'] ?? '') . ' ' . ($activity['lead_last_name'] ?? '')) ?>
                            <?php if (!empty($activity['user_name'])): ?>
                            &middot; <?= e($activity['user_name']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="timeline-date"><?= timeAgo($activity['created_at']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- ============================================================
     CHARTS SCRIPT
     ============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js global defaults
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#94a3b8';

    // Lead Trend
    var trendData = <?= json_encode($leadTrend) ?>;
    var trendCtx  = document.getElementById('leadTrendChart');
    if (trendCtx && trendData.length) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.label),
                datasets: [{
                    label: 'Leads',
                    data: trendData.map(d => d.total),
                    borderColor: '#4f46e5',
                    backgroundColor: function(ctx) {
                        var gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                        gradient.addColorStop(0,   'rgba(79,70,229,0.18)');
                        gradient.addColorStop(0.6, 'rgba(79,70,229,0.04)');
                        gradient.addColorStop(1,   'rgba(79,70,229,0)');
                        return gradient;
                    },
                    borderWidth: 2.5,
                    tension: 0.45,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#e2e8f0',
                        bodyColor: '#94a3b8',
                        padding: 10,
                        cornerRadius: 8,
                        borderColor: '#334155',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: '#f1f5f9' },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // Source Doughnut
    var sourceData = <?= json_encode($sourceDistribution) ?>;
    var sourceCtx  = document.getElementById('sourceChart');
    if (sourceCtx && sourceData.length) {
        var colors = ['#4f46e5','#059669','#d97706','#dc2626','#7c3aed','#0284c7','#0d9488','#ea580c'];
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: sourceData.map(d => d.name),
                datasets: [{
                    data: sourceData.map(d => d.count),
                    backgroundColor: colors.slice(0, sourceData.length),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 11 },
                            color: '#64748b'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#e2e8f0',
                        bodyColor: '#94a3b8',
                        padding: 10,
                        cornerRadius: 8
                    }
                }
            }
        });
    }
});
</script>
