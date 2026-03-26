<?php $pageTitle = 'Manage Rooms: ' . e($hostel['name']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-door-open me-2"></i>Rooms - <?= e($hostel['name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('hostels') ?>">Hostels</a></li>
                <li class="breadcrumb-item active">Rooms</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('hostels') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <?php if (hasPermission('hostel.manage')): ?>
        <div class="card">
            <div class="card-header bg-light"><i class="fas fa-plus me-2"></i>Add Room</div>
            <div class="card-body">
                <form method="POST" action="<?= url("hostels/{$hostel['id']}/rooms") ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label required">Room Number</label>
                        <input type="text" class="form-control" name="room_number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Floor</label>
                        <input type="text" class="form-control" name="floor">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Capacity (Beds)</label>
                        <input type="number" class="form-control" name="capacity" min="1" max="20" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Add Room</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-light"><i class="fas fa-list me-2"></i>Room List</div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Room No.</th>
                            <th>Floor</th>
                            <th>Capacity</th>
                            <th>Available Beds</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rooms)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">No rooms added yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rooms as $r): ?>
                            <tr>
                                <td class="fw-bold"><?= e($r['room_number']) ?></td>
                                <td><?= e($r['floor'] ?: '—') ?></td>
                                <td><?= e($r['capacity']) ?></td>
                                <td>
                                    <?php if ($r['available_beds'] == 0): ?>
                                        <span class="badge bg-danger">Full</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= e($r['available_beds']) ?> Available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $r['status'] === 'active' ? 'primary' : 'warning' ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
