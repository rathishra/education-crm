<?php $pageTitle = 'Transport Routes'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-bus me-2"></i>Transport Routes</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Transport</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('transport.manage')): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal"><i class="fas fa-plus me-1"></i> Add Route</button>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Route Name</th>
                            <th>Start Point</th>
                            <th>End Point</th>
                            <th>Total Stops</th>
                            <th>Status</th>
                            <?php if (hasPermission('transport.manage')): ?><th class="text-end">Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($routes)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No transport routes added yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($routes as $r): ?>
                            <tr>
                                <td class="fw-bold"><?= e($r['name']) ?></td>
                                <td><?= e($r['start_point'] ?: '—') ?></td>
                                <td><?= e($r['end_point'] ?: '—') ?></td>
                                <td><span class="badge bg-secondary"><?= e($r['total_stops']) ?> Stops</span></td>
                                <td>
                                    <span class="badge bg-<?= $r['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>
                                <?php if (hasPermission('transport.manage')): ?>
                                <td class="text-end">
                                    <a href="<?= url("transport/{$r['id']}/stops") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-map-marker-alt me-1"></i>Manage Stops</a>
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
</div>

<!-- Add Route Modal -->
<div class="modal fade" id="addRouteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('transport') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Add Transport Route</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Route Name/Number</label>
                    <input type="text" class="form-control" name="name" required placeholder="e.g. Route 1 - City Center">
                </div>
                <div class="mb-3">
                    <label class="form-label">Start Point</label>
                    <input type="text" class="form-control" name="start_point">
                </div>
                <div class="mb-3">
                    <label class="form-label">End Point</label>
                    <input type="text" class="form-control" name="end_point">
                </div>
                <div class="mb-3">
                    <label class="form-label required">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
            </div>
        </form>
    </div>
</div>
