<?php $pageTitle = 'Placement Drives'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-bullhorn me-2"></i>Placement Drives</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Placement Drives</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('placement/companies') ?>" class="btn btn-outline-secondary"><i class="fas fa-building me-1"></i> Companies</a>
        <?php if (hasPermission('placement.manage')): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDriveModal"><i class="fas fa-plus me-1"></i> New Drive</button>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Title & Company</th>
                        <th>Eligibility</th>
                        <th>Applications</th>
                        <th>Selected</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($drives)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No placement drives found for this academic year.</td></tr>
                    <?php else: ?>
                        <?php foreach ($drives as $d): ?>
                        <tr>
                            <td class="fw-semibold text-nowrap"><?= formatDate($d['drive_date'], 'd M Y') ?></td>
                            <td>
                                <div class="fw-bold text-primary"><?= e($d['title']) ?></div>
                                <div class="small text-muted"><i class="fas fa-building me-1"></i><?= e($d['company_name']) ?></div>
                            </td>
                            <td>
                                <?php if ($d['min_cgpa']): ?>
                                    <span class="badge bg-light text-dark border">Min CGPA: <?= e($d['min_cgpa']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">No CGPA req.</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary rounded-pill"><?= e($d['total_applications']) ?></span>
                            </td>
                            <td>
                                <?php if ($d['total_selected'] > 0): ?>
                                    <span class="badge bg-success rounded-pill"><?= e($d['total_selected']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'upcoming' => 'info',
                                    'ongoing' => 'primary',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColors[$d['status']] ?>"><?= ucfirst($d['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url("placement/drives/{$d['id']}/applications") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-users me-1"></i>Applicants</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Drive Modal -->
<div class="modal fade" id="addDriveModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('placement/drives') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Create Placement Drive</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Drive Title</label>
                    <input type="text" class="form-control" name="title" required placeholder="e.g. Mega Recruitment Drive 2026">
                </div>
                <div class="mb-3">
                    <label class="form-label required">Company</label>
                    <select class="form-select" name="company_id" required>
                        <option value="">-- Select Company --</option>
                        <?php foreach ($companies as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Drive Date</label>
                    <input type="date" class="form-control" name="drive_date" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Minimum CGPA Requirement</label>
                    <input type="number" step="0.01" min="0" max="10" class="form-control" name="min_cgpa" placeholder="e.g. 6.5">
                </div>
                <div class="mb-3">
                    <label class="form-label required">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="upcoming">Upcoming</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Drive</button>
            </div>
        </form>
    </div>
</div>
