<?php $pageTitle = 'Applications: ' . e($drive['title']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-users me-2"></i>Applications</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('placement/drives') ?>">Placement</a></li>
                <li class="breadcrumb-item active"><?= e($drive['title']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('placement/drives') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        <?php if (hasPermission('placement.manage')): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppModal"><i class="fas fa-plus me-1"></i> Add Applicant</button>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4 bg-light">
    <div class="card-body">
        <h5 class="card-title fw-bold text-primary mb-1"><?= e($drive['title']) ?></h5>
        <div class="d-flex flex-wrap gap-4 text-muted small mt-2">
            <div><i class="fas fa-building me-1"></i><?= e($drive['company_name']) ?></div>
            <div><i class="fas fa-calendar-day me-1"></i><?= formatDate($drive['drive_date'], 'd M Y') ?></div>
            <?php if ($drive['min_cgpa']): ?>
                <div><i class="fas fa-graduation-cap me-1"></i>Min CGPA: <?= e($drive['min_cgpa']) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="fas fa-list me-2"></i>Applicant List</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Course & Batch</th>
                        <th>Status</th>
                        <th>Offer Package</th>
                        <th>Remarks</th>
                        <?php if (hasPermission('placement.manage')): ?><th class="text-end">Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No applications found for this drive.</td></tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold">
                                    <a href="<?= url('students/'.$app['student_id']) ?>"><?= e($app['first_name'] . ' ' . $app['last_name']) ?></a>
                                </div>
                                <code class="small text-muted"><?= e($app['student_id_number']) ?></code>
                            </td>
                            <td>
                                <div><?= e($app['course_name'] ?: 'N/A') ?></div>
                                <div class="small text-muted"><?= e($app['batch_name'] ?: 'N/A') ?></div>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'applied' => 'secondary',
                                    'shortlisted' => 'info',
                                    'interviewed' => 'primary',
                                    'selected' => 'success',
                                    'rejected' => 'danger'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColors[$app['status']] ?>"><?= ucfirst($app['status']) ?></span>
                            </td>
                            <td>
                                <?php if ($app['offer_package']): ?>
                                    <span class="fw-bold text-success"><?= formatCurrency($app['offer_package']) ?></span> 
                                    <small class="text-muted">/ yr</small>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-truncate" style="max-width: 150px;" title="<?= e($app['remarks']) ?>">
                                <?= e($app['remarks'] ?: '—') ?>
                            </td>
                            <?php if (hasPermission('placement.manage')): ?>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" onclick="openUpdateModal(<?= $app['id'] ?>, '<?= e(addslashes($app['first_name'].' '.$app['last_name'])) ?>', '<?= e($app['status']) ?>', '<?= e($app['offer_package']) ?>', '<?= e(addslashes($app['remarks'])) ?>')">
                                    <i class="fas fa-edit me-1"></i>Update Stage
                                </button>
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

<!-- Add Applicant Modal -->
<div class="modal fade" id="addAppModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url("placement/drives/{$drive['id']}/applications") ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Add Applicant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Student DB ID</label>
                    <input type="number" class="form-control" name="student_id" required>
                    <small class="text-muted">Enter internal student ID. Typically this would be a selection dropdown.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Initial Remarks</label>
                    <textarea class="form-control" name="remarks" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add to Drive</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Application Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="" id="updateForm" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header">
                <h5 class="modal-title">Update Application Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Student:</strong> <span id="updateStudentName"></span></p>
                
                <div class="mb-3 mt-3">
                    <label class="form-label required">Current Stage</label>
                    <select class="form-select" name="status" id="updateStatus" required>
                        <option value="applied">Applied</option>
                        <option value="shortlisted">Shortlisted</option>
                        <option value="interviewed">Interviewed</option>
                        <option value="selected">Selected</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Offer Package (LPA / Annual)</label>
                    <input type="number" step="0.01" class="form-control" name="offer_package" id="updatePackage" placeholder="e.g. 500000">
                    <small class="text-muted">Fill this if student is Selected.</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="remarks" id="updateRemarks" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Updates</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUpdateModal(appId, studentName, status, packageAmt, remarks) {
    document.getElementById('updateStudentName').textContent = studentName;
    document.getElementById('updateStatus').value = status;
    document.getElementById('updatePackage').value = packageAmt;
    document.getElementById('updateRemarks').value = remarks;
    document.getElementById('updateForm').action = '<?= url("placement/drives/{$drive['id']}/applications/") ?>' + appId;
    
    var modal = new bootstrap.Modal(document.getElementById('updateModal'));
    modal.show();
}
</script>
