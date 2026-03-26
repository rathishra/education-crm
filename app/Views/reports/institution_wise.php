<?php $pageTitle = 'Institution-wise Report'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-building me-2"></i>Institution-wise Report</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('reports') ?>">Reports</a></li>
                <li class="breadcrumb-item active">Institution-wise</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('reports/institution-wise/export') ?>" class="btn btn-outline-success">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-2"></i>Institution Summary</span>
        <span class="badge bg-primary"><?= count($data ?? []) ?> institution(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Institution</th>
                        <th>Type</th>
                        <th class="text-center">Total Leads</th>
                        <th class="text-center">Admissions</th>
                        <th class="text-center">Active Students</th>
                        <th class="text-end">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($data)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No data available.</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($data as $row): ?>
                    <tr>
                        <td class="text-muted"><?= $i++ ?></td>
                        <td>
                            <strong><?= e($row['institution_name'] ?? '—') ?></strong>
                            <?php if (!empty($row['city'])): ?>
                            <small class="text-muted d-block"><i class="fas fa-map-marker-alt me-1"></i><?= e($row['city']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= ucfirst(e($row['institution_type'] ?? '—')) ?></span>
                        </td>
                        <td class="text-center"><?= number_format($row['total_leads'] ?? 0) ?></td>
                        <td class="text-center">
                            <span class="fw-bold text-success"><?= number_format($row['admissions'] ?? 0) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-primary"><?= number_format($row['active_students'] ?? 0) ?></span>
                        </td>
                        <td class="text-end fw-bold text-success">
                            <?= formatCurrency($row['revenue'] ?? 0) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($data)): ?>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Totals</td>
                        <td class="text-center fw-bold"><?= number_format(array_sum(array_column($data, 'total_leads'))) ?></td>
                        <td class="text-center fw-bold text-success"><?= number_format(array_sum(array_column($data, 'admissions'))) ?></td>
                        <td class="text-center fw-bold text-primary"><?= number_format(array_sum(array_column($data, 'active_students'))) ?></td>
                        <td class="text-end fw-bold text-success"><?= formatCurrency(array_sum(array_column($data, 'revenue'))) ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
