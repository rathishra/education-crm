<?php $pageTitle = 'Manage Stops: ' . e($route['name']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-map-marker-alt me-2"></i>Stops - <?= e($route['name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('transport') ?>">Transport</a></li>
                <li class="breadcrumb-item active">Stops</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('transport') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <?php if (hasPermission('transport.manage')): ?>
        <div class="card">
            <div class="card-header bg-light"><i class="fas fa-plus me-2"></i>Add Stop</div>
            <div class="card-body">
                <form method="POST" action="<?= url("transport/{$route['id']}/stops") ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label required">Stop Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pickup Time (Morning)</label>
                        <input type="time" class="form-control" name="pickup_time">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Drop Time (Evening)</label>
                        <input type="time" class="form-control" name="drop_time">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="0">
                        <small class="text-muted">Order of the stop on the route.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Add Stop</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-light"><i class="fas fa-list me-2"></i>Stops on <?= e($route['name']) ?></div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">Order</th>
                            <th>Stop Name</th>
                            <th>Pickup</th>
                            <th>Drop</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($stops)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No stops added yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($stops as $s): ?>
                            <tr>
                                <td class="text-muted fw-bold"><?= e($s['sort_order']) ?></td>
                                <td class="fw-bold text-primary"><?= e($s['name']) ?></td>
                                <td><?= $s['pickup_time'] ? date('h:i A', strtotime($s['pickup_time'])) : '—' ?></td>
                                <td><?= $s['drop_time'] ? date('h:i A', strtotime($s['drop_time'])) : '—' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
