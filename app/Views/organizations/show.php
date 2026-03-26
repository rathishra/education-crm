<?php $pageTitle = 'Organization Details'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-building me-2"></i><?= e($org['name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('organizations') ?>">Organizations</a></li>
                <li class="breadcrumb-item active"><?= e($org['name']) ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (hasPermission('organizations.edit')): ?>
        <a href="<?= url('organizations/' . $org['id'] . '/edit') ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= url('organizations') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Organization Info -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <?php if (!empty($org['logo'])): ?>
                    <img src="<?= url($org['logo']) ?>" class="mb-3" style="max-height:80px" alt="Logo">
                <?php else: ?>
                    <div class="bg-primary text-white rounded d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;font-size:2rem">
                        <?= strtoupper(substr($org['name'], 0, 2)) ?>
                    </div>
                <?php endif; ?>
                <h4 class="mb-1"><?= e($org['name']) ?></h4>
                <p class="text-muted mb-2">Code: <code><?= e($org['code']) ?></code></p>
                <span class="badge bg-<?= $org['status'] === 'active' ? 'success' : 'danger' ?>">
                    <?= ucfirst($org['status']) ?>
                </span>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Email</span>
                    <span><?= e($org['email'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Phone</span>
                    <span><?= e($org['phone'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Website</span>
                    <span><?= e($org['website'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Established</span>
                    <span><?= e($org['established_year'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">GST</span>
                    <span><?= e($org['gst_number'] ?? '-') ?></span>
                </div>
                <div class="list-group-item">
                    <span class="text-muted d-block mb-1">Address</span>
                    <span>
                        <?= e(implode(', ', array_filter([
                            $org['address_line1'] ?? '',
                            $org['address_line2'] ?? '',
                            $org['city'] ?? '',
                            $org['state'] ?? '',
                            $org['pincode'] ?? '',
                        ]))) ?: '-' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Institutions -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-university me-2"></i>Institutions (<?= count($org['institutions'] ?? []) ?>)</span>
                <?php if (hasPermission('institutions.create')): ?>
                <a href="<?= url('institutions/create') ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Institution
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($org['institutions'])): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No institutions yet</td></tr>
                            <?php else: ?>
                            <?php foreach ($org['institutions'] as $inst): ?>
                            <tr>
                                <td>
                                    <a href="<?= url('institutions/' . $inst['id']) ?>" class="fw-semibold">
                                        <?= e($inst['name']) ?>
                                    </a>
                                </td>
                                <td><code><?= e($inst['code']) ?></code></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= e(ucfirst(str_replace('_', ' ', $inst['type']))) ?>
                                    </span>
                                </td>
                                <td><?= e($inst['city'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-<?= $inst['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($inst['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('institutions/' . $inst['id']) ?>" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
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
</div>
