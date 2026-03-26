<?php $pageTitle = 'Library Books'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-book me-2"></i>Library Inventory</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Library</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('library.issue')): ?>
            <a href="<?= url('library/issues') ?>" class="btn btn-outline-primary"><i class="fas fa-hand-holding-box me-1"></i> Issues & Returns</a>
        <?php endif; ?>
        <?php if (hasPermission('library.manage')): ?>
            <a href="<?= url('library/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Book</a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="<?= url('library') ?>">
            <div class="input-group">
                <input type="text" class="form-control" name="search" value="<?= e($search) ?>" placeholder="Search by Title, Author, ISBN, Publisher, Category...">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search me-1"></i>Search</button>
                <?php if ($search): ?>
                    <a href="<?= url('library') ?>" class="btn btn-outline-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Book Details</th>
                        <th>Category</th>
                        <th>ISBN</th>
                        <th>Copies (Avail/Total)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($books['data'])): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No books found in the inventory.</td></tr>
                    <?php else: ?>
                        <?php foreach ($books['data'] as $b): ?>
                        <tr>
                            <td class="text-muted"><?= e($b['id']) ?></td>
                            <td>
                                <div class="fw-bold text-primary"><?= e($b['title']) ?></div>
                                <div class="small text-muted">by <?= e($b['author'] ?: 'Unknown') ?></div>
                                <?php if ($b['publisher']): ?><div class="small text-muted">Pub: <?= e($b['publisher']) ?></div><?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?= e($b['category'] ?: 'Uncategorized') ?></span></td>
                            <td><code><?= e($b['isbn'] ?: 'N/A') ?></code></td>
                            <td>
                                <?php if ($b['available_copies'] == 0): ?>
                                    <span class="badge bg-danger">Out of Stock</span> (<?= e($b['total_copies']) ?>)
                                <?php else: ?>
                                    <strong><?= e($b['available_copies']) ?></strong> / <?= e($b['total_copies']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $b['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($books['last_page'] > 1): ?>
    <div class="card-footer">
        <?= renderPagination($books) ?>
    </div>
    <?php endif; ?>
</div>
