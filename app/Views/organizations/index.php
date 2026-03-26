<?php $pageTitle = 'Manage Organizations'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-sitemap me-2"></i>Organizations</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Organizations</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('organizations/export') ?>" class="btn btn-outline-success"><i class="fas fa-file-excel me-1"></i> Export CSV</a>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="fas fa-upload me-1"></i> Import CSV
        </button>
        <a href="<?= url('organizations/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Organization</a>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= url('organizations/import') ?>" method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Import Organizations</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Upload CSV File</label>
                        <input class="form-control" type="file" name="csv_file" accept=".csv" required>
                        <div class="form-text mt-2">
                            <strong>Expected CSV format (headers required):</strong><br>
                            <code>Code, Name, Email, Phone, Address, Max Institutions, Status</code>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body bg-light">
        <form method="GET" action="<?= url('organizations') ?>" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" value="<?= e($search) ?>" placeholder="Search name or code...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100"><i class="fas fa-search me-1"></i> Filter</button>
            </div>
            <?php if ($search || $status): ?>
            <div class="col-md-2">
                <a href="<?= url('organizations') ?>" class="btn btn-outline-danger w-100">Clear</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Organization Name</th>
                        <th>Contact</th>
                        <th>Institutions</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($organizations['data'])): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No organizations found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($organizations['data'] as $org): ?>
                        <tr>
                            <td><code><?= e($org['organization_code']) ?></code></td>
                            <td class="fw-bold text-primary"><?= e($org['organization_name']) ?></td>
                            <td>
                                <div class="small"><?= e($org['email'] ?: 'N/A') ?></div>
                                <div class="small text-muted"><?= e($org['phone'] ?: 'N/A') ?></div>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark rounded-pill"><?= $org['institutions_count'] ?> / <?= $org['max_institutions'] ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $org['status'] === 'active' ? 'success' : 'secondary' ?> status-badge"
                                      id="org-status-<?= $org['id'] ?>"
                                      role="button" title="Click to toggle"
                                      onclick="toggleOrgStatus(<?= $org['id'] ?>, this)">
                                    <?= ucfirst($org['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url('organizations/' . $org['id']) ?>" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= url('organizations/' . $org['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="<?= url('organizations/' . $org['id'] . '/delete') ?>" class="d-inline"
                                      onsubmit="return confirm('Delete this organization?')">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($organizations['last_page'] > 1): ?>
    <div class="card-footer border-0 bg-white">
        <?= renderPagination($organizations) ?>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleOrgStatus(id, badge) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(`<?= url('organizations') ?>/${id}/toggle-status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `_token=${token}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const isActive = data.status === 'active';
            badge.className = `badge ${isActive ? 'bg-success' : 'bg-secondary'} status-badge`;
            badge.textContent = isActive ? 'Active' : 'Inactive';
        }
    })
    .catch(() => alert('Failed to update status'));
}
</script>
