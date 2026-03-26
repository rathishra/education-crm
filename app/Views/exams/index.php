<?php $pageTitle = 'Exams'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-signature me-2"></i>Exams</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Exams</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('exams.manage')): ?>
        <a href="<?= url('exams/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Exam</a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Exam Name</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exams['data'])): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No exams found.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($exams['data'] as $ex): ?>
                        <tr>
                            <td class="fw-semibold">
                                <a href="<?= url("exams/{$ex['id']}") ?>"><?= e($ex['name']) ?></a>
                            </td>
                            <td><?= ucfirst(str_replace('_', ' ', $ex['type'])) ?></td>
                            <td><?= formatDate($ex['start_date']) ?></td>
                            <td><?= formatDate($ex['end_date']) ?></td>
                            <td>
                                <?php
                                $statusColors = [
                                    'upcoming' => 'info',
                                    'ongoing' => 'primary',
                                    'completed' => 'success',
                                    'published' => 'dark'
                                ];
                                $color = $statusColors[$ex['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst($ex['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url("exams/{$ex['id']}") ?>" class="btn btn-sm btn-outline-primary" title="Details / Schedules"><i class="fas fa-calendar-alt"></i> Sch</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($exams['last_page'] > 1): ?>
    <div class="card-footer">
        <?= renderPagination($exams) ?>
    </div>
    <?php endif; ?>
</div>
