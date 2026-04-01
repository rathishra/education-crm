<?php $pageTitle = 'Fee Heads Master'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-tags me-2 text-primary"></i>Fee Heads Master</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item active">Fee Heads</li>
        </ol></nav>
    </div>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalFeeHead">
        <i class="fas fa-plus me-1"></i>Add Fee Head
    </button>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach([
        ['Total Heads', $stats['total'], 'tags', 'primary'],
        ['Active', $stats['active'], 'check-circle', 'success'],
        ['Mandatory', $stats['mandatory'], 'asterisk', 'warning'],
        ['Refundable', $stats['refundable'], 'undo', 'info'],
    ] as [$label,$val,$icon,$color]): ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle bg-<?= $color ?>-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px">
                    <i class="fas fa-<?= $icon ?> text-<?= $color ?>"></i>
                </div>
                <div><div class="fw-bold fs-4"><?= $val ?></div><div class="text-muted small"><?= $label ?></div></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Fee Heads
            <span class="badge bg-secondary-subtle text-secondary border ms-1"><?= count($heads) ?></span>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="headsTable">
            <thead class="table-light">
                <tr>
                    <th>#</th><th>Head Code</th><th>Head Name</th><th>Type</th><th>Category</th>
                    <th class="text-center">Mandatory</th><th class="text-center">Refundable</th>
                    <th class="text-center">Structures</th><th class="text-center">Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($heads)): ?>
            <tr><td colspan="10" class="text-center py-5 text-muted">
                <i class="fas fa-tags fa-2x mb-2 d-block opacity-25"></i>No fee heads yet. Click Add Fee Head to start.
            </td></tr>
            <?php else: foreach($heads as $i => $h): ?>
            <tr id="row-<?= $h['id'] ?>">
                <td class="text-muted small"><?= $i+1 ?></td>
                <td><span class="badge bg-dark-subtle text-dark border fw-bold"><?= e($h['head_code']) ?></span></td>
                <td>
                    <div class="fw-semibold"><?= e($h['head_name']) ?></div>
                    <?php if($h['description']): ?><div class="small text-muted"><?= e(substr($h['description'],0,60)) ?></div><?php endif; ?>
                </td>
                <td><span class="badge bg-info-subtle text-info border"><?= ucfirst(str_replace('_',' ',$h['fee_type'])) ?></span></td>
                <td>
                    <?php $catColors=['tuition'=>'primary','exam'=>'danger','transport'=>'warning','hostel'=>'success','library'=>'info','lab'=>'purple','sports'=>'orange','miscellaneous'=>'secondary'];
                    $cc = $catColors[$h['category']] ?? 'secondary'; ?>
                    <span class="badge bg-<?= $cc ?>-subtle text-<?= $cc ?> border"><?= ucfirst($h['category']) ?></span>
                </td>
                <td class="text-center">
                    <?= $h['is_mandatory'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-muted"></i>' ?>
                </td>
                <td class="text-center">
                    <?= $h['is_refundable'] ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-muted"></i>' ?>
                </td>
                <td class="text-center">
                    <span class="badge bg-primary-subtle text-primary border"><?= $h['structure_count'] ?></span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm <?= $h['is_active'] ? 'btn-success' : 'btn-secondary' ?> btn-toggle-head"
                        data-id="<?= $h['id'] ?>" title="Toggle status">
                        <?= $h['is_active'] ? 'Active' : 'Inactive' ?>
                    </button>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-light border btn-edit-head" data-id="<?= $h['id'] ?>" title="Edit">
                            <i class="fas fa-edit text-primary"></i>
                        </button>
                        <button class="btn btn-light border btn-delete-head" data-id="<?= $h['id'] ?>"
                            data-name="<?= e($h['head_name']) ?>"
                            <?= $h['structure_count'] > 0 ? 'disabled title="Used in structures"' : '' ?>>
                            <i class="fas fa-trash text-danger"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="modalFeeHead" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="headModalTitle"><i class="fas fa-plus me-2 text-primary"></i>Add Fee Head</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editHeadId" value="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Head Code <span class="text-danger">*</span></label>
                        <input type="text" id="fh_code" class="form-control text-uppercase" placeholder="e.g. TUITION" maxlength="30">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Head Name <span class="text-danger">*</span></label>
                        <input type="text" id="fh_name" class="form-control" placeholder="e.g. Tuition Fee">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fee Type</label>
                        <select id="fh_type" class="form-select">
                            <option value="one_time">One Time</option>
                            <option value="annual" selected>Annual</option>
                            <option value="semester">Semester</option>
                            <option value="recurring">Recurring</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <select id="fh_category" class="form-select">
                            <option value="tuition">Tuition</option>
                            <option value="exam">Exam</option>
                            <option value="transport">Transport</option>
                            <option value="hostel">Hostel</option>
                            <option value="library">Library</option>
                            <option value="lab">Lab</option>
                            <option value="sports">Sports</option>
                            <option value="uniform">Uniform</option>
                            <option value="development">Development</option>
                            <option value="miscellaneous">Miscellaneous</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-4 align-items-end pb-1">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="fh_mandatory" checked>
                            <label class="form-check-label" for="fh_mandatory">Mandatory</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="fh_refundable">
                            <label class="form-check-label" for="fh_refundable">Refundable</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea id="fh_description" class="form-control" rows="2" placeholder="Optional description..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnSaveHead"><i class="fas fa-save me-1"></i>Save Fee Head</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= url('') ?>';

$(document).ready(function() {
    $('#headsTable').DataTable({ order: [[1,'asc']], pageLength: 25, dom: 'lBfrtip',
        buttons: ['copy','csv','print'] });
});

// Edit
$(document).on('click', '.btn-edit-head', function() {
    const id = $(this).data('id');
    $.getJSON(BASE + 'fees/heads/' + id + '/json', function(r) {
        const d = r.data;
        $('#editHeadId').val(d.id);
        $('#fh_code').val(d.head_code);
        $('#fh_name').val(d.head_name);
        $('#fh_type').val(d.fee_type);
        $('#fh_category').val(d.category);
        $('#fh_mandatory').prop('checked', d.is_mandatory == 1);
        $('#fh_refundable').prop('checked', d.is_refundable == 1);
        $('#fh_description').val(d.description || '');
        $('#headModalTitle').html('<i class="fas fa-edit me-2 text-warning"></i>Edit Fee Head');
        $('#modalFeeHead').modal('show');
    });
});

// Reset on open
$('#modalFeeHead').on('show.bs.modal', function(e) {
    if (!$(e.relatedTarget).length) return;
    $('#editHeadId').val('');
    $('#fh_code, #fh_name, #fh_description').val('');
    $('#fh_type').val('annual');
    $('#fh_category').val('tuition');
    $('#fh_mandatory').prop('checked', true);
    $('#fh_refundable').prop('checked', false);
    $('#headModalTitle').html('<i class="fas fa-plus me-2 text-primary"></i>Add Fee Head');
});

// Save
$('#btnSaveHead').on('click', function() {
    const id  = $('#editHeadId').val();
    const url = id ? BASE + 'fees/heads/' + id + '/update' : BASE + 'fees/heads/store';
    const data = {
        head_code:     $('#fh_code').val(),
        head_name:     $('#fh_name').val(),
        fee_type:      $('#fh_type').val(),
        category:      $('#fh_category').val(),
        is_mandatory:  $('#fh_mandatory').is(':checked') ? 1 : 0,
        is_refundable: $('#fh_refundable').is(':checked') ? 1 : 0,
        description:   $('#fh_description').val(),
        csrf_token:    $('meta[name="csrf-token"]').attr('content'),
    };
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
    $.post(url, data, function(r) {
        if (r.status === 'success') {
            toastr.success(r.message);
            $('#modalFeeHead').modal('hide');
            setTimeout(() => location.reload(), 600);
        } else {
            toastr.error(r.message);
        }
    }).fail(() => toastr.error('Network error.'))
    .always(() => $('#btnSaveHead').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Fee Head'));
});

// Toggle
$(document).on('click', '.btn-toggle-head', function() {
    const btn = $(this); const id = btn.data('id');
    $.post(BASE + 'fees/heads/' + id + '/toggle', {csrf_token: $('meta[name="csrf-token"]').attr('content')}, function(r) {
        if (r.status === 'success') {
            btn.toggleClass('btn-success btn-secondary').text(r.is_active ? 'Active' : 'Inactive');
        }
    });
});

// Delete
$(document).on('click', '.btn-delete-head', function() {
    const id = $(this).data('id'), name = $(this).data('name');
    if (!confirm('Delete fee head "' + name + '"? This cannot be undone.')) return;
    $.post(BASE + 'fees/heads/' + id + '/delete', {csrf_token: $('meta[name="csrf-token"]').attr('content')}, function(r) {
        if (r.status === 'success') { $('#row-' + id).fadeOut(); toastr.success(r.message); }
        else toastr.error(r.message);
    });
});
</script>
