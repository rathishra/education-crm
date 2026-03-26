<?php $pageTitle = 'Admission - ' . e($admission['admission_number']); ?>
<div class="page-header">
    <div>
        <h1><i class="fas fa-user-graduate me-2"></i>Admission Application</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="<?= url('admissions') ?>">Admissions</a></li><li class="breadcrumb-item active"><?= e($admission['admission_number']) ?></li></ol></nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('admissions.edit') && !in_array($admission['status'],['enrolled','cancelled'])): ?><a href="<?= url('admissions/'.$admission['id'].'/edit') ?>" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a><?php endif; ?>
        <?php if (hasPermission('admissions.approve') && in_array($admission['status'],['applied','under_review','documents_pending'])): ?>
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/approve') ?>" class="d-inline"><?= csrfField() ?><button class="btn btn-success" onclick="return confirm('Approve this admission?')"><i class="fas fa-check me-1"></i>Approve</button></form>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times me-1"></i>Reject</button>
        <?php endif; ?>
        <?php if (hasPermission('admissions.enroll') && $admission['status'] === 'approved'): ?>
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/enroll') ?>" class="d-inline"><?= csrfField() ?><button class="btn btn-dark" onclick="return confirm('Enroll this student?')"><i class="fas fa-graduation-cap me-1"></i>Enroll Student</button></form>
        <?php endif; ?>
        <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php
$sc = ['applied'=>'primary','under_review'=>'info','documents_pending'=>'warning','approved'=>'success','rejected'=>'danger','enrolled'=>'dark','cancelled'=>'secondary'];
$color = $sc[$admission['status']] ?? 'secondary';
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i>Application Details</span>
                <span class="badge bg-<?= $color ?> fs-6"><?= ucfirst(str_replace('_',' ',$admission['status'])) ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="text-muted small">Admission Number</label><div><code><?= e($admission['admission_number']) ?></code></div></div>
                    <div class="col-md-6"><label class="text-muted small">Applicant Name</label><div class="fw-semibold"><?= e($admission['first_name'].' '.($admission['last_name']??'')) ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Phone</label><div><?= e($admission['phone']) ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Email</label><div><?= e($admission['email']??'-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Gender / DOB</label><div><?= ucfirst($admission['gender']??'-') ?> <?= $admission['date_of_birth'] ? '('.formatDate($admission['date_of_birth']).')' : '' ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Course</label><div><?= e($admission['course_name']??'-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Batch</label><div><?= e($admission['batch_name']??'-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Academic Year</label><div><?= e($admission['academic_year_name']??'-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Father Name</label><div><?= e($admission['father_name']??'-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Father Phone</label><div><?= e($admission['father_phone']??'-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Mother Name</label><div><?= e($admission['mother_name']??'-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Qualification</label><div><?= e($admission['previous_qualification']??'-') ?></div></div>
                    <div class="col-md-4"><label class="text-muted small">Percentage</label><div><?= $admission['previous_percentage'] ? $admission['previous_percentage'].'%' : '-' ?></div></div>
                    <?php if ($admission['remarks']): ?><div class="col-12"><label class="text-muted small">Remarks</label><div><?= nl2br(e($admission['remarks'])) ?></div></div><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-paperclip me-2"></i>Documents</span>
                <?php if (hasPermission('documents.upload') && !in_array($admission['status'], ['enrolled', 'cancelled'])): ?>
                <form id="docUploadForm" class="d-flex align-items-center gap-2" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <input type="hidden" name="entity_type" value="admission">
                    <input type="hidden" name="entity_id" value="<?= $admission['id'] ?>">
                    <select name="document_type" class="form-select form-select-sm" style="max-width:150px">
                        <option value="id_proof">ID Proof</option>
                        <option value="marksheet">Marksheet</option>
                        <option value="transfer_certificate">Transfer Certificate</option>
                        <option value="photo">Photo</option>
                        <option value="other" selected>Other</option>
                    </select>
                    <input type="file" name="document" class="form-control form-control-sm" required>
                    <button class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Upload</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($admission['documents'])): ?>
                    <p class="text-muted mb-0">No documents uploaded yet.</p>
                <?php else: ?>
                <table class="table table-sm align-middle">
                    <thead class="table-light"><tr><th>Title</th><th>Type</th><th>Status</th><th>Uploaded</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($admission['documents'] as $doc): ?>
                        <?php $verified = (int)$doc['is_verified'] === 1; ?>
                        <tr>
                            <td>
                                <a href="<?= url('documents/'.$doc['id'].'/download') ?>"><?= e($doc['title'] ?: $doc['file_name']) ?></a>
                                <?php $sizeKb = $doc['file_size'] ? round($doc['file_size']/1024, 1).' KB' : 'size unknown'; ?>
                                <div class="text-muted small"><?= e($doc['file_name']) ?> (<?= $sizeKb ?>)</div>
                            </td>
                            <td><?= e(ucwords(str_replace('_',' ',$doc['document_type']))) ?></td>
                            <td><span class="badge bg-<?= $verified ? 'success' : 'warning' ?>"><?= $verified ? 'Verified' : 'Pending' ?></span></td>
                            <td><small><?= formatDate($doc['created_at'],'d M Y, h:i A') ?></small></td>
                            <td class="text-end">
                                <?php if (hasPermission('documents.verify') && !$verified): ?>
                                    <form method="POST" action="<?= url('documents/'.$doc['id'].'/verify') ?>" class="d-inline doc-action-form"><?= csrfField() ?><button class="btn btn-outline-success btn-sm">Verify</button></form>
                                <?php endif; ?>
                                <?php if (hasPermission('documents.delete')): ?>
                                    <form method="POST" action="<?= url('documents/'.$doc['id'].'/delete') ?>" class="d-inline doc-action-form" data-confirm="Delete this document?"><?= csrfField() ?><button class="btn btn-outline-danger btn-sm">Delete</button></form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <script>
        document.getElementById('docUploadForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            try {
                const res = await fetch("<?= url('documents/upload') ?>", {
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    body: formData
                });
                const json = await res.json();
                if (json.success) {
                    window.location.reload();
                } else {
                    alert(json.message || 'Upload failed');
                }
            } catch (err) {
                console.error(err);
                alert('Upload failed');
            }
        });

        document.querySelectorAll('.doc-action-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const confirmMsg = form.dataset.confirm;
                if (confirmMsg && !confirm(confirmMsg)) return;
                const formData = new FormData(form);
                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {'X-Requested-With': 'XMLHttpRequest'},
                        body: formData
                    });
                    const json = await res.json();
                    if (json.success) {
                        window.location.reload();
                    } else {
                        alert(json.message || 'Action failed');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Action failed');
                }
            });
        });
        </script>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-clock me-2"></i>Timeline</div>
            <div class="card-body">
                <div class="mb-2"><small class="text-muted">Created by</small><div><?= e($admission['created_by_name']??'-') ?></div></div>
                <div class="mb-2"><small class="text-muted">Applied on</small><div><?= formatDate($admission['created_at'],'d M Y, h:i A') ?></div></div>
                <?php if ($admission['approved_at']): ?>
                <div class="mb-2"><small class="text-muted">Decision by</small><div><?= e($admission['approved_by_name']??'-') ?></div></div>
                <div><small class="text-muted">Decision on</small><div><?= formatDate($admission['approved_at'],'d M Y, h:i A') ?></div></div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($admission['student_id']): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>Student enrolled.
            <a href="<?= url('students/'.$admission['student_id']) ?>" class="alert-link">View Student</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Reject Admission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/reject') ?>">
                <?= csrfField() ?>
                <div class="modal-body"><div class="mb-3"><label class="form-label">Reason for rejection</label><textarea class="form-control" name="reason" rows="3" required></textarea></div></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Reject</button></div>
            </form>
        </div>
    </div>
</div>
