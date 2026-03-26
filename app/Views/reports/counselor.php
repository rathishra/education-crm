<?php $pageTitle = 'Counselor Performance'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-headset me-2"></i>Counselor Performance</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
                <li class="breadcrumb-item active">Counselor Performance</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('reports/counselor/export') ?>" class="btn btn-outline-success">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<!-- Date Filter -->
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
                <a href="<?= url('reports/counselor') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Counselor Table -->
<div class="card">
    <div class="card-header"><i class="fas fa-table me-2"></i>Performance Summary</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Counselor</th>
                        <th class="text-center">Total Leads</th>
                        <th class="text-center">Converted</th>
                        <th class="text-center">Lost</th>
                        <th class="text-center">Followups Done</th>
                        <th class="text-end">Conv. Rate (%)</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No data available for the selected period.</td></tr>
                <?php else: ?>
                    <?php $rank = 1; foreach ($data as $row): ?>
                    <?php
                    $convRate = $row['total'] > 0 ? round(($row['converted'] / $row['total']) * 100, 1) : 0;
                    $barColor = $convRate >= 50 ? 'success' : ($convRate >= 25 ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td class="text-muted">
                            <?php if ($rank === 1): ?><i class="fas fa-trophy text-warning"></i>
                            <?php elseif ($rank === 2): ?><i class="fas fa-medal text-secondary"></i>
                            <?php elseif ($rank === 3): ?><i class="fas fa-medal" style="color:#cd7f32;"></i>
                            <?php else: ?><?= $rank ?><?php endif; ?>
                        </td>
                        <td>
                            <strong><?= e($row['counselor_name'] ?? '—') ?></strong>
                            <?php if (!empty($row['email'])): ?>
                            <small class="text-muted d-block"><?= e($row['email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= number_format($row['total'] ?? 0) ?></td>
                        <td class="text-center text-success fw-bold"><?= number_format($row['converted'] ?? 0) ?></td>
                        <td class="text-center text-danger"><?= number_format($row['lost'] ?? 0) ?></td>
                        <td class="text-center text-info"><?= number_format($row['followups'] ?? 0) ?></td>
                        <td class="text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <div class="progress flex-grow-1" style="height:8px; max-width:80px;">
                                    <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= $convRate ?>%"></div>
                                </div>
                                <span class="fw-bold"><?= $convRate ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php $rank++; endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
