<?php $pageTitle = 'Partner Companies'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-building me-2"></i>Partner Companies</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('placement/drives') ?>">Placement</a></li>
                <li class="breadcrumb-item active">Companies</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('placement/drives') ?>" class="btn btn-outline-secondary"><i class="fas fa-bullhorn me-1"></i> Drives</a>
        <?php if (hasPermission('placement.manage')): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal"><i class="fas fa-plus me-1"></i> Add Company</button>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <?php if (empty($companies)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-building fs-1 mb-3"></i>
            <h4>No Companies Found</h4>
            <p>Add your partner companies to start organizing placement drives.</p>
        </div>
    <?php else: ?>
        <?php foreach ($companies as $c): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 border-top border-4 border-<?= $c['status'] === 'active' ? 'primary' : 'secondary' ?>">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-primary mb-1"><?= e($c['name']) ?></h5>
                    <p class="small text-muted mb-3"><i class="fas fa-industry me-1"></i><?= e($c['industry'] ?: 'General') ?></p>
                    
                    <ul class="list-unstyled mb-4 small">
                        <li class="mb-2"><i class="fas fa-user-tie text-muted me-2"></i> <?= e($c['contact_person'] ?: 'N/A') ?></li>
                        <li class="mb-2"><i class="fas fa-envelope text-muted me-2"></i> <?= e($c['contact_email'] ?: 'N/A') ?></li>
                        <li class="mb-2"><i class="fas fa-phone text-muted me-2"></i> <?= e($c['contact_phone'] ?: 'N/A') ?></li>
                        <?php if ($c['website']): ?>
                            <li><i class="fas fa-globe text-muted me-2"></i> <a href="<?= e($c['website']) ?>" target="_blank"><?= e($c['website']) ?></a></li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="badge bg-light text-dark border"><i class="fas fa-bullhorn me-1"></i> <?= e($c['total_drives']) ?> Drives</span>
                        <span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($c['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Add Company Modal -->
<div class="modal fade" id="addCompanyModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('placement/companies') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Add Partner Company</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Company Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Industry Sector</label>
                    <input type="text" class="form-control" name="industry" placeholder="e.g. IT, Manufacturing, Finance">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-control" name="contact_person">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" class="form-control" name="contact_phone">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Email</label>
                    <input type="email" class="form-control" name="contact_email">
                </div>
                <div class="mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" class="form-control" name="website" placeholder="https://">
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
