<?php $pageTitle = 'Campuses'; ?>
<div class="page-header">
    <div>
        <h1><i class="fas fa-building me-2"></i>Campuses</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Campuses</li>
        </ol></nav>
    </div>
    <?php if (hasPermission('institutions.create')): ?>
    <a href="<?= url('campuses/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Campus</a>
    <?php endif; ?>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search name, code, city..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="institution_id">
                    <option value="">All Institutions</option>
                    <?php foreach ($institutions as $i): ?>
                    <option value="<?= $i['id'] ?>" <?= $instId == $i['id'] ? 'selected' : '' ?>><?= e($i['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-primary"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
            <div class="col-auto">
                <a href="<?= url('campuses') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total: <strong><?= number_format($campuses['total'] ?? 0) ?></strong> campuses</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Campus Name</th>
                        <th>Code</th>
                        <th>Institution</th>
                        <th>Location</th>
                        <th>Phone</th>
                        <th>Principal</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($campuses['data'])): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-building fa-2x mb-2 d-block"></i>No campuses found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($campuses['data'] as $i => $c): ?>
                    <tr>
                        <td><?= ($campuses['from'] ?? 0) + $i ?></td>
                        <td>
                            <div class="fw-semibold"><?= e($c['name']) ?></div>
                            <small class="text-muted"><?= e($c['email'] ?? '') ?></small>
                        </td>
                        <td><code><?= e($c['code']) ?></code></td>
                        <td><?= e($c['institution_name'] ?? '-') ?></td>
                        <td><?= e(implode(', ', array_filter([$c['city'] ?? '', $c['state'] ?? '']))) ?: '-' ?></td>
                        <td><?= e($c['phone'] ?? '-') ?></td>
                        <td><?= e($c['principal_name'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'secondary' ?> status-badge"
                                  data-id="<?= $c['id'] ?>"
                                  style="cursor:pointer"
                                  title="Click to toggle">
                                <?= ucfirst($c['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (hasPermission('institutions.edit')): ?>
                                <a href="<?= url('campuses/' . $c['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (hasPermission('institutions.delete')): ?>
                                <form method="POST" action="<?= url('campuses/' . $c['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete campus &quot;<?= e($c['name']) ?>&quot;? This cannot be undone.')">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($campuses['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $campuses;
        $baseUrl = url('campuses') . '?' . http_build_query(array_filter(['search' => $search, 'institution_id' => $instId, 'status' => $status]));
        include BASE_PATH . '/app/Views/partials/pagination.php';
        ?>
    </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.status-badge').forEach(function(badge) {
    badge.addEventListener('click', function() {
        var self = this;
        fetch('<?= url('campuses') ?>/' + this.dataset.id + '/toggle-status', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: '_token=<?= csrfToken() ?>'
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            self.textContent = d.status.charAt(0).toUpperCase() + d.status.slice(1);
            self.className = 'badge bg-' + (d.status === 'active' ? 'success' : 'secondary') + ' status-badge';
        })
        .catch(function(err) { console.error('Toggle failed:', err); });
    });
});
</script>
