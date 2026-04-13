<?php $pageTitle = 'Library'; ?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-book-reader me-2 text-primary"></i>Library</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Library</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('library.view')): ?>
        <a href="<?= url('library/issues') ?>" class="btn btn-outline-primary">
            <i class="fas fa-exchange-alt me-1"></i>Issues &amp; Returns
        </a>
        <?php endif; ?>
        <?php if (hasPermission('library.manage')): ?>
        <a href="<?= url('library/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Book
        </a>
        <?php endif; ?>
    </div>
</div>

<?php $flash = getFlash('success'); $flashErr = getFlash('error'); ?>
<?php if ($flash): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= e($flash) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flashErr): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-circle me-2"></i><?= e($flashErr) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="fas fa-books text-primary fs-5"></i>
                </div>
                <div>
                    <div class="h4 mb-0 fw-bold"><?= number_format($stats['total_books'] ?? 0) ?></div>
                    <div class="text-muted small">Titles</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="fas fa-copy text-success fs-5"></i>
                </div>
                <div>
                    <div class="h4 mb-0 fw-bold"><?= number_format($stats['available_copies'] ?? 0) ?></div>
                    <div class="text-muted small">Available / <?= number_format($stats['total_copies'] ?? 0) ?> Total</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="fas fa-layer-group text-info fs-5"></i>
                </div>
                <div>
                    <div class="h4 mb-0 fw-bold"><?= number_format($stats['total_categories'] ?? 0) ?></div>
                    <div class="text-muted small">Categories</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 <?= ($stats['overdue_count'] ?? 0) > 0 ? 'border-warning border-opacity-75' : '' ?>">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="fas fa-exclamation-triangle text-warning fs-5"></i>
                </div>
                <div>
                    <div class="h4 mb-0 fw-bold <?= ($stats['overdue_count'] ?? 0) > 0 ? 'text-warning' : '' ?>">
                        <?= number_format($stats['overdue_count'] ?? 0) ?>
                    </div>
                    <div class="text-muted small">Overdue Issues</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('library') ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" class="form-control" name="search"
                       value="<?= e($search) ?>"
                       placeholder="Search title, author, accession no., ISBN…">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>>
                        <?= e($cat['category']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Search
                </button>
                <?php if ($search || $category || $status): ?>
                <a href="<?= url('library') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Books Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex align-items-center justify-content-between">
        <span class="fw-semibold"><i class="fas fa-list me-2"></i>Books Inventory</span>
        <span class="badge bg-secondary"><?= count($books) ?> record<?= count($books) !== 1 ? 's' : '' ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:110px">Accession No.</th>
                        <th>Title &amp; Author</th>
                        <th>Category</th>
                        <th>Shelf</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Available</th>
                        <th class="text-center">Status</th>
                        <?php if (hasPermission('library.manage')): ?>
                        <th class="text-end">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($books)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-book-open fa-2x mb-2 d-block opacity-25"></i>
                            No books found. <?php if (hasPermission('library.manage')): ?>
                                <a href="<?= url('library/create') ?>">Add the first book.</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($books as $b): ?>
                    <tr>
                        <td><code class="small"><?= e($b['accession_number']) ?></code></td>
                        <td>
                            <div class="fw-semibold"><?= e($b['title']) ?></div>
                            <div class="small text-muted">
                                <i class="fas fa-user-edit me-1"></i><?= e($b['author']) ?>
                                <?php if ($b['publisher']): ?>
                                  &nbsp;·&nbsp; <?= e($b['publisher']) ?>
                                <?php endif; ?>
                                <?php if ($b['isbn']): ?>
                                  &nbsp;·&nbsp; <code><?= e($b['isbn']) ?></code>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($b['category']): ?>
                            <span class="badge bg-light text-dark border"><?= e($b['category']) ?></span>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= e($b['shelf_location'] ?: '—') ?></td>
                        <td class="text-center"><?= $b['quantity'] ?></td>
                        <td class="text-center">
                            <?php if ((int)$b['available_quantity'] === 0): ?>
                            <span class="badge bg-danger">Full</span>
                            <?php elseif ((int)$b['available_quantity'] < (int)$b['quantity']): ?>
                            <span class="badge bg-warning text-dark"><?= $b['available_quantity'] ?></span>
                            <?php else: ?>
                            <span class="badge bg-success"><?= $b['available_quantity'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-<?= $b['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($b['status']) ?>
                            </span>
                        </td>
                        <?php if (hasPermission('library.manage')): ?>
                        <td class="text-end">
                            <a href="<?= url("library/{$b['id']}/edit") ?>"
                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= url("library/{$b['id']}/delete") ?>"
                                  class="d-inline"
                                  onsubmit="return confirm('Remove this book from the library?')">
                                <?= csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
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
</div>
