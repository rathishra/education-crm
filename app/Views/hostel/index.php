<?php $pageTitle = 'Hostels'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-building me-2"></i>Hostels</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Hostels</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('hostel.manage')): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHostelModal"><i class="fas fa-plus me-1"></i> Add Hostel</button>
    <?php endif; ?>
</div>

<div class="row">
    <?php if (empty($hostels)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-building fs-1 mb-3"></i>
            <h4>No Hostels Found</h4>
            <p>Click "Add Hostel" to create the first one.</p>
        </div>
    <?php else: ?>
        <?php foreach ($hostels as $h): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-top border-4 border-<?= $h['status'] === 'active' ? 'success' : 'secondary' ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h5 class="card-title fw-bold mb-0"><?= e($h['name']) ?></h5>
                        <span class="badge bg-<?= $h['type'] === 'boys' ? 'primary' : ($h['type'] === 'girls' ? 'danger' : 'info') ?>"><?= ucfirst($h['type']) ?></span>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-user-shield text-muted me-2"></i> Warden: <?= e($h['warden_name']) ?: '—' ?></li>
                        <li class="mb-2"><i class="fas fa-phone text-muted me-2"></i> <?= e($h['warden_phone']) ?: '—' ?></li>
                        <li class="mb-2"><i class="fas fa-door-closed text-muted me-2"></i> Rooms: <?= e($h['total_rooms'] ?? 0) ?></li>
                        <li><i class="fas fa-bed text-muted me-2"></i> Beds Available: <strong><?= e($h['total_available_beds'] ?? 0) ?></strong> / <?= e($h['total_capacity'] ?? 0) ?></li>
                    </ul>
                    <a href="<?= url("hostels/{$h['id']}/rooms") ?>" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-door-open me-2"></i>Manage Rooms</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Hostel Modal -->
<div class="modal fade" id="addHostelModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('hostels') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Add New Hostel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Hostel Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Type</label>
                    <select class="form-select" name="type" required>
                        <option value="boys">Boys</option>
                        <option value="girls">Girls</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Warden Name</label>
                    <input type="text" class="form-control" name="warden_name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Warden Phone</label>
                    <input type="text" class="form-control" name="warden_phone">
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
