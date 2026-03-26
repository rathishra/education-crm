<?php $pageTitle = 'Fee Structures'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-invoice-dollar me-2"></i>Fee Structures</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Fee Structures</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('fees.create')): ?>
    <a href="<?= url('fees/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Create Fee Structure
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search by name, course..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Search</button>
                <a href="<?= url('fees') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Batch</th>
                        <th>Total Amount</th>
                        <th class="text-center">Components</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($feeStructures['data'])): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No fee structures found.</td></tr>
                <?php else: ?>
                    <?php foreach ($feeStructures['data'] as $fs): ?>
                    <tr>
                        <td><strong><?= e($fs['name']) ?></strong></td>
                        <td><?= e($fs['course_name'] ?? '—') ?></td>
                        <td><?= e($fs['batch_name'] ?? '—') ?></td>
                        <td><strong class="text-success"><?= formatCurrency($fs['total_amount'] ?? 0) ?></strong></td>
                        <td class="text-center">
                            <span class="badge bg-info"><?= e($fs['component_count'] ?? 0) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if (($fs['status'] ?? 'active') === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= url('fees/' . $fs['id']) ?>" class="btn btn-sm btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasPermission('fees.edit')): ?>
                            <a href="<?= url('fees/' . $fs['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('fees.delete')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDelete('<?= url('fees/' . $fs['id']) ?>')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($feeStructures['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $feeStructures;
        $baseUrl = url('fees') . '?' . http_build_query(array_filter(compact('search') ?? []));
        include BASE_PATH . '/app/Views/partials/pagination.php';
        ?>
    </div>
    <?php endif; ?>
</div>
