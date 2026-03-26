<?php $pageTitle = 'Dashboard'; ?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
    <div class="text-muted small">
        <i class="fas fa-calendar me-1"></i><?= date('l, d M Y') ?>
    </div>
</div>

<!-- KPI Stat Cards Row 1 -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-primary h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-bold">Total Leads</div>
                    <div class="h3 mb-0 fw-bold"><?= number_format($stats['total_leads']) ?></div>
                    <small class="text-success"><i class="fas fa-arrow-up"></i> <?= $stats['new_leads_today'] ?> today</small>
                </div>
                <div class="stat-icon text-primary"><i class="fas fa-user-plus"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-success h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-bold">Converted</div>
                    <div class="h3 mb-0 fw-bold"><?= number_format($stats['converted_leads']) ?></div>
                    <small class="text-info"><?= $stats['conversion_rate'] ?>% conversion rate</small>
                </div>
                <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-info h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-bold">Active Students</div>
                    <div class="h3 mb-0 fw-bold"><?= number_format($stats['total_students']) ?></div>
                    <small class="text-muted"><?= $stats['pending_admissions'] ?> pending admissions</small>
                </div>
                <div class="stat-icon text-info"><i class="fas fa-user-graduate"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-warning h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-bold">Revenue (Month)</div>
                    <div class="h3 mb-0 fw-bold"><?= formatCurrency($stats['revenue_month']) ?></div>
                    <small class="text-danger">Due: <?= formatCurrency($stats['total_dues']) ?></small>
                </div>
                <div class="stat-icon text-warning"><i class="fas fa-rupee-sign"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Stat Cards Row 2 -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-purple h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-bold">Enquiries (Month)</div>
                    <div class="h3 mb-0 fw-bold"><?= number_format($stats['enquiries_month']) ?></div>
                </div>
                <div class="stat-icon" style="color:#6f42c1"><i class="fas fa-question-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-danger h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-bold">Follow-ups Today</div>
                    <div class="h3 mb-0 fw-bold"><?= number_format($stats['pending_followups']) ?></div>
                </div>
                <div class="stat-icon text-danger"><i class="fas fa-phone-alt"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-info h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-bold">New Leads (Month)</div>
                    <div class="h3 mb-0 fw-bold"><?= number_format($stats['new_leads_month']) ?></div>
                </div>
                <div class="stat-icon text-info"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-success h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-bold">Pending Admissions</div>
                    <div class="h3 mb-0 fw-bold"><?= number_format($stats['pending_admissions']) ?></div>
                </div>
                <div class="stat-icon text-success"><i class="fas fa-clipboard-check"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Lead Pipeline -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-filter me-2"></i>Lead Pipeline</span>
        <a href="<?= url('leads') ?>" class="btn btn-sm btn-outline-primary">View All Leads</a>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <?php foreach ($pipeline as $stage): ?>
            <div class="col">
                <div class="pipeline-stage" style="background-color: <?= e($stage['color']) ?>">
                    <div class="fw-bold h4 mb-0"><?= number_format($stage['count'] ?? 0) ?></div>
                    <small><?= e($stage['name']) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <!-- Lead Trend Chart -->
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i>Lead Trend (Last 6 Months)
            </div>
            <div class="card-body">
                <canvas id="leadTrendChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <!-- Source Distribution -->
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Lead Sources
            </div>
            <div class="card-body">
                <canvas id="sourceChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="row g-4">
    <!-- Recent Leads -->
    <div class="col-xl-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-users me-2"></i>Recent Leads</span>
                <a href="<?= url('leads') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentLeads)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">No leads yet</td></tr>
                            <?php else: ?>
                            <?php foreach ($recentLeads as $lead): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('leads/' . $lead['id']) ?>" class="fw-semibold">
                                        <?= e($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?>
                                    </a>
                                </td>
                                <td><?= e($lead['phone']) ?></td>
                                <td><small class="text-muted"><?= e($lead['source_name'] ?? '-') ?></small></td>
                                <td>
                                    <span class="badge" style="background-color: <?= e($lead['status_color'] ?? '#6c757d') ?>">
                                        <?= e($lead['status_name'] ?? '-') ?>
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

    <!-- Upcoming Follow-ups -->
    <div class="col-xl-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-phone-alt me-2"></i>Upcoming Follow-ups</span>
                <a href="<?= url('followups') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($upcomingFollowups)): ?>
                    <div class="list-group-item text-center text-muted py-4">No upcoming follow-ups</div>
                    <?php else: ?>
                    <?php foreach ($upcomingFollowups as $fu): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold">
                                    <?= e(($fu['lead_first_name'] ?? '') . ' ' . ($fu['lead_last_name'] ?? '')) ?>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-<?= $fu['type'] === 'call' ? 'phone' : ($fu['type'] === 'email' ? 'envelope' : 'calendar') ?> me-1"></i>
                                    <?= e($fu['subject']) ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block"><?= formatDateTime($fu['scheduled_at']) ?></small>
                                <small class="text-muted"><?= e($fu['assigned_name'] ?? '') ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-history me-2"></i>Recent Activity
            </div>
            <div class="card-body">
                <?php if (empty($recentActivities)): ?>
                <p class="text-center text-muted mb-0">No recent activity</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach (array_slice($recentActivities, 0, 8) as $activity): ?>
                    <div class="timeline-item">
                        <div class="fw-semibold small"><?= e($activity['title']) ?></div>
                        <div class="small text-muted">
                            <?= e(($activity['lead_first_name'] ?? '') . ' ' . ($activity['lead_last_name'] ?? '')) ?>
                            <?php if (!empty($activity['user_name'])): ?>
                            &middot; by <?= e($activity['user_name']) ?>
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

<!-- Charts Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lead Trend Chart
    var trendData = <?= json_encode($leadTrend) ?>;
    var trendCtx = document.getElementById('leadTrendChart');

    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(function(d) { return d.label; }),
                datasets: [{
                    label: 'Leads',
                    data: trendData.map(function(d) { return d.total; }),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#0d6efd',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Source Distribution Chart
    var sourceData = <?= json_encode($sourceDistribution) ?>;
    var sourceCtx = document.getElementById('sourceChart');

    if (sourceCtx) {
        var colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14', '#20c997', '#0dcaf0'];
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: sourceData.map(function(d) { return d.name; }),
                datasets: [{
                    data: sourceData.map(function(d) { return d.count; }),
                    backgroundColor: colors.slice(0, sourceData.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 15, usePointStyle: true, pointStyle: 'circle' }
                    }
                }
            }
        });
    }
});
</script>
