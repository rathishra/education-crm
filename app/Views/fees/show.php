<?php $pageTitle = 'Fee Structure Details'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-invoice-dollar me-2"></i><?= e($feeStructure['name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fee Structures</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('fees.edit')): ?>
        <a href="<?= url('fees/' . $feeStructure['id'] . '/edit') ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= url('fees') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle me-2"></i>Details</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Name</dt>
                    <dd class="col-7"><?= e($feeStructure['name']) ?></dd>

                    <dt class="col-5 text-muted">Course</dt>
                    <dd class="col-7"><?= e($feeStructure['course_name'] ?? '—') ?></dd>

                    <dt class="col-5 text-muted">Batch</dt>
                    <dd class="col-7"><?= e($feeStructure['batch_name'] ?? 'All Batches') ?></dd>

                    <dt class="col-5 text-muted">Academic Year</dt>
                    <dd class="col-7"><?= e($feeStructure['academic_year_name'] ?? '—') ?></dd>

                    <dt class="col-5 text-muted">Status</dt>
                    <dd class="col-7">
                        <?php if (($feeStructure['status'] ?? 'active') === 'active'): ?>
                        <span class="badge bg-success">Active</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </dd>

                    <?php if (!empty($feeStructure['description'])): ?>
                    <dt class="col-5 text-muted">Description</dt>
                    <dd class="col-7"><?= e($feeStructure['description']) ?></dd>
                    <?php endif; ?>

                    <dt class="col-5 text-muted">Created</dt>
                    <dd class="col-7"><?= formatDate($feeStructure['created_at'] ?? '') ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>Fee Components</span>
                <span class="badge bg-primary"><?= count($feeStructure['components'] ?? []) ?> component(s)</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Component Name</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Optional</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($feeStructure['components'])): ?>
                        <tr><td colspan="4" class="text-center text-muted py-3">No components defined.</td></tr>
                    <?php else: ?>
                        <?php $mandatory = 0; $i = 1; foreach ($feeStructure['components'] as $comp): ?>
                        <tr>
                            <td class="text-muted"><?= $i++ ?></td>
                            <td><?= e($comp['component_name']) ?></td>
                            <td class="text-end"><?= formatCurrency($comp['component_amount'] ?? 0) ?></td>
                            <td class="text-center">
                                <?php if (!empty($comp['is_optional'])): ?>
                                <span class="badge bg-warning text-dark">Optional</span>
                                <?php else: ?>
                                <?php $mandatory += ($comp['component_amount'] ?? 0); ?>
                                <span class="badge bg-secondary">Required</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="2" class="text-end fw-bold">Mandatory Total:</td>
                            <td class="text-end fw-bold text-success"><?= formatCurrency($mandatory ?? 0) ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-end fw-bold">Grand Total:</td>
                            <td class="text-end fw-bold text-primary"><?= formatCurrency($feeStructure['total_amount'] ?? 0) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
