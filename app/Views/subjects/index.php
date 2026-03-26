<?php $pageTitle = 'Subjects'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-book-open me-2"></i>Subjects</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Subjects</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('subjects.manage')): ?>
        <a href="<?= url('subjects/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Subject</a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('subjects') ?>">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by name or code..." value="<?= e($search) ?>">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i>Search</button>
                <?php if ($search): ?>
                    <a href="<?= url('subjects') ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Department</th>
                        <th>Credits</th>
                        <th>Status</th>
                        <?php if (hasPermission('subjects.manage')): ?><th class="text-end">Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subjects['data'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No subjects found.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($subjects['data'] as $sub): ?>
                        <tr>
                            <td><code><?= e($sub['code']) ?></code></td>
                            <td class="fw-semibold"><?= e($sub['name']) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', $sub['type'])) ?></td>
                            <td><?= e($sub['department_name']) ?></td>
                            <td><?= e($sub['credits']) ?></td>
                            <td>
                                <span class="badge bg-<?= $sub['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($sub['status']) ?>
                                </span>
                            </td>
                            <?php if (hasPermission('subjects.manage')): ?>
                            <td class="text-end">
                                <a href="<?= url("subjects/{$sub['id']}/edit") ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="<?= url("subjects/{$sub['id']}/delete") ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($subjects['last_page'] > 1): ?>
    <div class="card-footer">
        <?= renderPagination($subjects) ?>
    </div>
    <?php endif; ?>
</div>
