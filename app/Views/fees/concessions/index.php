<?php $pageTitle = 'Fee Concessions'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-percentage me-2 text-success"></i>Fee Concessions</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item active">Concessions</li>
        </ol></nav>
    </div>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddConcession">
        <i class="fas fa-plus me-1"></i>New Concession
    </button>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach([
        ['Total', $stats['total'] ?? 0, 'percentage', 'primary'],
        ['Pending Approval', $stats['pending'] ?? 0, 'clock', 'warning'],
        ['Approved', $stats['approved'] ?? 0, 'check-circle', 'success'],
        ['Total Discounted', '₹'.number_format($stats['total_amount'] ?? 0,2), 'tag', 'info'],
    ] as [$label,$val,$icon,$color]): ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3 py-3">
            <div class="rounded-circle bg-<?= $color ?>-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px">
                <i class="fas fa-<?= $icon ?> text-<?= $color ?>"></i>
            </div>
            <div><div class="fw-bold fs-5"><?= $val ?></div><div class="text-muted small"><?= $label ?></div></div>
        </div></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pending Approvals -->
<?php
$pendingConcessions = array_filter($concessions, fn($c) => $c['status'] === 'pending');
if (!empty($pendingConcessions)): ?>
<div class="card border-warning border-2 shadow-sm mb-4">
    <div class="card-header bg-warning-subtle fw-semibold py-2">
        <i class="fas fa-bell me-2 text-warning"></i>Pending Approvals (<?= count($pendingConcessions) ?>)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Student</th><th>Fee Head</th><th>Type</th><th class="text-end">Discount</th><th>Category</th><th>Requested</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach($pendingConcessions as $c): ?>
            <tr>
                <td>
                    <div class="fw-semibold"><?= e($c['student_name']) ?></div>
                    <div class="small text-muted"><?= e($c['roll_number'] ?? '') ?></div>
                </td>
                <td><?= e($c['head_name'] ?? 'All Heads') ?></td>
                <td>
                    <?php if($c['concession_type']==='percentage'): ?>
                    <span class="badge bg-info-subtle text-info border"><?= $c['concession_value'] ?>%</span>
                    <?php else: ?>
                    <span class="badge bg-primary-subtle text-primary border">₹<?= number_format($c['concession_value'],2) ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-end fw-bold text-success">₹<?= number_format($c['final_discount'],2) ?></td>
                <td><span class="badge bg-secondary-subtle text-secondary border"><?= ucfirst($c['category']) ?></span></td>
                <td class="small text-muted"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-success btn-approve-concession" data-id="<?= $c['id'] ?>" title="Approve">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-danger btn-reject-concession" data-id="<?= $c['id'] ?>" title="Reject">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- All Concessions -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold"><i class="fas fa-list me-2 text-primary"></i>All Concessions
            <span class="badge bg-secondary-subtle text-secondary border ms-1"><?= count($concessions) ?></span>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="concessionTable">
            <thead class="table-light">
                <tr><th>#</th><th>Student</th><th>Concession Name</th><th>Fee Head</th><th>Type</th>
                    <th class="text-end">Discount</th><th>Category</th><th class="text-center">Status</th>
                    <th>Approved By</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if(empty($concessions)): ?>
            <tr><td colspan="10" class="text-center py-5 text-muted">
                <i class="fas fa-percentage fa-2x mb-2 d-block opacity-25"></i>No concessions yet.
            </td></tr>
            <?php else: foreach($concessions as $i => $c): ?>
            <tr>
                <td class="text-muted small"><?= $i+1 ?></td>
                <td>
                    <div class="fw-semibold"><?= e($c['student_name']) ?></div>
                    <div class="small text-muted"><?= e($c['roll_number'] ?? '') ?></div>
                </td>
                <td class="fw-semibold"><?= e($c['concession_name']) ?></td>
                <td><?= e($c['head_name'] ?? 'All') ?></td>
                <td>
                    <?php if($c['concession_type']==='percentage'): ?>
                    <span class="badge bg-info-subtle text-info border"><?= $c['concession_value'] ?>%</span>
                    <?php else: ?>
                    <span class="badge bg-primary-subtle text-primary border">₹<?= number_format($c['concession_value'],2) ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-end fw-bold text-success">₹<?= number_format($c['final_discount'],2) ?></td>
                <td><span class="badge bg-secondary-subtle text-secondary border"><?= ucfirst($c['category']) ?></span></td>
                <td class="text-center">
                    <?php $sc=['pending'=>'warning','approved'=>'success','rejected'=>'danger'];
                    $s=$c['status']; ?>
                    <span class="badge bg-<?= $sc[$s]??'secondary' ?>-subtle text-<?= $sc[$s]??'secondary' ?> border"><?= ucfirst($s) ?></span>
                </td>
                <td class="small"><?= e($c['approver_name'] ?? '—') ?></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <?php if($c['status']==='pending'): ?>
                        <button class="btn btn-light border btn-approve-concession" data-id="<?= $c['id'] ?>" title="Approve"><i class="fas fa-check text-success"></i></button>
                        <button class="btn btn-light border btn-reject-concession" data-id="<?= $c['id'] ?>" title="Reject"><i class="fas fa-times text-danger"></i></button>
                        <?php endif; ?>
                        <?php if($c['status']==='pending'): ?>
                        <button class="btn btn-light border btn-delete-concession" data-id="<?= $c['id'] ?>" title="Delete"><i class="fas fa-trash text-danger"></i></button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Add Concession Modal -->
<div class="modal fade" id="modalAddConcession" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2 text-primary"></i>New Concession</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                        <select id="con_student" class="form-select select2-ajax" style="width:100%">
                            <option value="">Search student...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fee Assignment</label>
                        <select id="con_assignment" class="form-select">
                            <option value="">All Assignments</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Concession Name <span class="text-danger">*</span></label>
                        <input type="text" id="con_name" class="form-control" placeholder="e.g. Merit Scholarship">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <select id="con_category" class="form-select">
                            <?php foreach(['scholarship','merit','sports','staff_ward','management','other'] as $cat): ?>
                            <option value="<?= $cat ?>"><?= ucfirst(str_replace('_',' ',$cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Type</label>
                        <select id="con_type" class="form-select">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₹)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Value <span class="text-danger">*</span></label>
                        <input type="number" id="con_value" class="form-control" step="0.01" min="0" placeholder="e.g. 50 or 5000">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea id="con_remarks" class="form-control" rows="2" placeholder="Optional remarks..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnSaveConcession"><i class="fas fa-save me-1"></i>Submit Concession</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= url('') ?>';
$(document).ready(function() {
    $('#concessionTable').DataTable({ order: [[7,'asc']], pageLength: 25, dom: 'lBfrtip', buttons: ['copy','csv','print'] });

    // Select2 AJAX student search
    $('#con_student').select2({
        width: '100%',
        ajax: {
            url: BASE + 'fees/assignment/ajax/search',
            dataType: 'json',
            delay: 250,
            data: params => ({ term: params.term }),
            processResults: r => ({ results: r.results || [] }),
            minimumInputLength: 2,
        },
        placeholder: 'Type 2+ chars to search student...',
        dropdownParent: $('#modalAddConcession'),
    });
    $('#con_student').on('change', function() {
        const sid = $(this).val();
        if (!sid) return;
        $.getJSON(BASE + 'fees/assignment/ajax/student-assignments?student_id=' + sid, function(r) {
            const sel = $('#con_assignment').empty().append('<option value="">All Assignments</option>');
            (r.data || []).forEach(a => sel.append(`<option value="${a.id}">${a.head_name} (₹${parseFloat(a.net_amount).toFixed(2)})</option>`));
        });
    });
});

$('#btnSaveConcession').on('click', function() {
    const data = {
        student_id:      $('#con_student').val(),
        assignment_id:   $('#con_assignment').val() || '',
        concession_name: $('#con_name').val(),
        category:        $('#con_category').val(),
        concession_type: $('#con_type').val(),
        concession_value:$('#con_value').val(),
        remarks:         $('#con_remarks').val(),
        csrf_token:      $('meta[name="csrf-token"]').attr('content'),
    };
    if (!data.student_id || !data.concession_name || !data.concession_value) {
        return toastr.warning('Student, name and value are required.');
    }
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
    $.post(BASE + 'fees/concessions/store', data, function(r) {
        if (r.status === 'success') { toastr.success(r.message); $('#modalAddConcession').modal('hide'); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    }).fail(() => toastr.error('Network error.'))
    .always(() => $('#btnSaveConcession').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Submit Concession'));
});

$(document).on('click', '.btn-approve-concession', function() {
    const id = $(this).data('id');
    if (!confirm('Approve this concession?')) return;
    $.post(BASE + 'fees/concessions/' + id + '/approve', { csrf_token: $('meta[name="csrf-token"]').attr('content') }, function(r) {
        if (r.status === 'success') { toastr.success(r.message); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    });
});

$(document).on('click', '.btn-reject-concession', function() {
    const id = $(this).data('id');
    if (!confirm('Reject this concession?')) return;
    $.post(BASE + 'fees/concessions/' + id + '/reject', { csrf_token: $('meta[name="csrf-token"]').attr('content') }, function(r) {
        if (r.status === 'success') { toastr.success(r.message); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    });
});

$(document).on('click', '.btn-delete-concession', function() {
    const id = $(this).data('id');
    if (!confirm('Delete this concession request?')) return;
    $.post(BASE + 'fees/concessions/' + id + '/delete', { csrf_token: $('meta[name="csrf-token"]').attr('content') }, function(r) {
        if (r.status === 'success') { toastr.success(r.message); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    });
});
</script>
