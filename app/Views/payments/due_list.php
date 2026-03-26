<?php $pageTitle = 'Fee Due List'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-exclamation-circle me-2"></i>Fee Due List</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('payments') ?>">Payments</a></li>
                <li class="breadcrumb-item active">Due List</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('payments/due-list/export') ?>" class="btn btn-outline-success">
        <i class="fas fa-file-excel me-1"></i>Export
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i>Students with Pending Dues</span>
        <span class="badge bg-danger"><?= count($dueList ?? []) ?> student(s)</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Course</th>
                        <th>Batch</th>
                        <th class="text-end">Total Due</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($dueList)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-check-circle text-success me-2"></i>No pending dues!</td></tr>
                <?php else: ?>
                    <?php $i = 1; foreach ($dueList as $due): ?>
                    <tr>
                        <td class="text-muted"><?= $i++ ?></td>
                        <td><code><?= e($due['student_id_number'] ?? '—') ?></code></td>
                        <td>
                            <strong><?= e($due['student_name'] ?? '—') ?></strong>
                            <?php if (!empty($due['email'])): ?>
                            <small class="text-muted d-block"><?= e($due['email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($due['phone'] ?? '—') ?></td>
                        <td><?= e($due['course_name'] ?? '—') ?></td>
                        <td><?= e($due['batch_name'] ?? '—') ?></td>
                        <td class="text-end fw-bold text-danger"><?= formatCurrency($due['total_due'] ?? 0) ?></td>
                        <td class="text-end">
                            <a href="<?= url('payments/collect?student_id=' . ($due['student_id'] ?? '')) ?>"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-rupee-sign me-1"></i>Collect
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
                <?php if (!empty($dueList)): ?>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total Outstanding:</td>
                        <td class="text-end fw-bold text-danger">
                            <?= formatCurrency(array_sum(array_column($dueList, 'total_due'))) ?>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
