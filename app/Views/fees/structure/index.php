<?php $pageTitle = 'Fee Structures'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-sitemap me-2 text-primary"></i>Fee Structures</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item active">Structures</li>
        </ol></nav>
    </div>
    <a href="<?= url('fees/structures/create') ?>" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-1"></i>New Structure
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3 py-3">
            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px"><i class="fas fa-sitemap text-primary"></i></div>
            <div><div class="fw-bold fs-4"><?= $stats['total'] ?></div><div class="text-muted small">Total Structures</div></div>
        </div></div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3 py-3">
            <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px"><i class="fas fa-check-circle text-success"></i></div>
            <div><div class="fw-bold fs-4"><?= $stats['active'] ?></div><div class="text-muted small">Active</div></div>
        </div></div>
    </div>
</div>

<?php foreach($academicYears as $ay):
    $ayStructures = array_filter($structures, fn($s) => $s['academic_year_id'] == $ay['id']);
    if(empty($ayStructures)) continue; ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary-subtle border-0 py-2 px-3">
        <span class="fw-bold text-primary"><i class="fas fa-calendar me-2"></i><?= e($ay['name']) ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Structure Name</th><th>Course</th><th>Batch/Semester</th><th>Type</th>
                    <th class="text-end">Total Amount</th><th class="text-center">Fee Heads</th>
                    <th class="text-center">Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach($ayStructures as $s): ?>
            <tr>
                <td><div class="fw-semibold"><?= e($s['name']) ?></div></td>
                <td><span class="badge bg-primary-subtle text-primary border"><?= e($s['course_name'] ?? '') ?> <small class="text-muted"><?= e($s['course_code'] ?? '') ?></small></span></td>
                <td>
                    <?php if(!empty($s['batch_name'])): ?><span class="badge bg-secondary-subtle text-secondary border me-1"><?= e($s['batch_name']) ?></span><?php endif; ?>
                    <?php if($s['semester']): ?><span class="badge bg-info-subtle text-info border">Sem <?= $s['semester'] ?></span><?php endif; ?>
                </td>
                <td><span class="badge bg-warning-subtle text-warning border"><?= ucfirst($s['admission_type']) ?></span></td>
                <td class="text-end fw-bold text-success">₹<?= number_format($s['total_amount'],2) ?></td>
                <td class="text-center"><span class="badge bg-primary-subtle text-primary border"><?= $s['head_count'] ?> heads</span></td>
                <td class="text-center">
                    <button class="btn btn-sm <?= $s['status']==='active'?'btn-success':'btn-secondary' ?> btn-toggle-struct" data-id="<?= $s['id'] ?>">
                        <?= ucfirst($s['status']) ?>
                    </button>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <a href="<?= url('fees/structures/'.$s['id'].'/edit') ?>" class="btn btn-light border" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                        <button class="btn btn-light border btn-copy-struct" data-id="<?= $s['id'] ?>" data-name="<?= e($s['name']) ?>" title="Copy"><i class="fas fa-copy text-info"></i></button>
                        <button class="btn btn-light border btn-delete-struct" data-id="<?= $s['id'] ?>" data-name="<?= e($s['name']) ?>" title="Delete"><i class="fas fa-trash text-danger"></i></button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
$unassigned = array_filter($structures, fn($s) => !$s['academic_year_id']);
if(!empty($unassigned)): ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-secondary-subtle border-0 py-2 px-3"><span class="fw-bold text-secondary"><i class="fas fa-calendar-times me-2"></i>No Academic Year</span></div>
    <div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Structure Name</th><th>Course</th><th class="text-end">Total Amount</th><th class="text-center">Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach($unassigned as $s): ?>
            <tr>
                <td class="fw-semibold"><?= e($s['name']) ?></td>
                <td><span class="badge bg-primary-subtle text-primary border"><?= e($s['course_name'] ?? '') ?> <small class="text-muted"><?= e($s['course_code'] ?? '') ?></small></span></td>
                <td class="text-end fw-bold">₹<?= number_format($s['total_amount'],2) ?></td>
                <td class="text-center"><span class="badge <?= $s['status']==='active'?'bg-success':'bg-secondary' ?>"><?= ucfirst($s['status']) ?></span></td>
                <td><div class="btn-group btn-group-sm">
                    <a href="<?= url('fees/structures/'.$s['id'].'/edit') ?>" class="btn btn-light border"><i class="fas fa-edit text-primary"></i></a>
                    <button class="btn btn-light border btn-delete-struct" data-id="<?= $s['id'] ?>" data-name="<?= e($s['name']) ?>"><i class="fas fa-trash text-danger"></i></button>
                </div></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
</div>
<?php endif; ?>

<?php if(empty($structures)): ?>
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5 text-muted">
        <i class="fas fa-sitemap fa-3x mb-3 opacity-25"></i>
        <p>No fee structures yet.</p>
        <a href="<?= url('fees/structures/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Create First Structure</a>
    </div>
</div>
<?php endif; ?>

<!-- Copy Modal -->
<div class="modal fade" id="modalCopy" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title fw-bold"><i class="fas fa-copy me-2 text-info"></i>Copy Structure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Copying: <strong id="copyStructName"></strong></p>
                <input type="hidden" id="copyStructId">
                <label class="form-label fw-semibold">Target Academic Year</label>
                <select id="copyAyId" class="form-select">
                    <option value="">Same year</option>
                    <?php foreach($academicYears as $ay): ?>
                    <option value="<?= $ay['id'] ?>"><?= e($ay['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-info text-white" id="btnDoCopy"><i class="fas fa-copy me-1"></i>Copy</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= url('') ?>';
$(document).on('click', '.btn-copy-struct', function() {
    $('#copyStructId').val($(this).data('id'));
    $('#copyStructName').text($(this).data('name'));
    $('#modalCopy').modal('show');
});
$('#btnDoCopy').on('click', function() {
    const id = $('#copyStructId').val();
    $.post(BASE + 'fees/structures/' + id + '/copy', {
        academic_year_id: $('#copyAyId').val(),
        csrf_token: $('meta[name="csrf-token"]').attr('content')
    }, function(r) {
        if (r.status === 'success') { toastr.success(r.message); $('#modalCopy').modal('hide'); setTimeout(() => location.reload(), 800); }
        else toastr.error(r.message);
    });
});
$(document).on('click', '.btn-toggle-struct', function() {
    const btn = $(this), id = btn.data('id');
    $.post(BASE + 'fees/structures/' + id + '/toggle', {csrf_token: $('meta[name="csrf-token"]').attr('content')}, function(r) {
        if (r.status === 'success') {
            const isActive = r.new_status === 'active';
            btn.toggleClass('btn-success btn-secondary').text(isActive ? 'Active' : 'Inactive');
        }
    });
});
$(document).on('click', '.btn-delete-struct', function() {
    const id = $(this).data('id'), name = $(this).data('name');
    if (!confirm('Delete "' + name + '"?')) return;
    $.post(BASE + 'fees/structures/' + id + '/delete', {csrf_token: $('meta[name="csrf-token"]').attr('content')}, function(r) {
        if (r.status === 'success') { location.reload(); }
        else toastr.error(r.message);
    });
});
</script>
