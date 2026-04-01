<?php $pageTitle = 'Fee Assignment'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-user-tag me-2 text-primary"></i>Fee Assignment</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item active">Assignment</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalBulkAssign">
            <i class="fas fa-users me-1"></i>Bulk Assign
        </button>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach([
        ['Total Assigned', $stats['total'] ?? 0, 'user-tag', 'primary'],
        ['Pending', $stats['pending'] ?? 0, 'clock', 'warning'],
        ['Paid', $stats['paid'] ?? 0, 'check-circle', 'success'],
        ['Overdue', $stats['overdue'] ?? 0, 'exclamation-circle', 'danger'],
    ] as [$label,$val,$icon,$color]): ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body d-flex align-items-center gap-3 py-3">
            <div class="rounded-circle bg-<?= $color ?>-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px">
                <i class="fas fa-<?= $icon ?> text-<?= $color ?>"></i>
            </div>
            <div><div class="fw-bold fs-4"><?= number_format($val) ?></div><div class="text-muted small"><?= $label ?></div></div>
        </div></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-2">
        <form method="GET" action="<?= url('fees/assignment') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Academic Year</label>
                <select name="academic_year_id" class="form-select form-select-sm">
                    <option value="">All Years</option>
                    <?php foreach($academicYears as $ay): ?>
                    <option value="<?= $ay['id'] ?>" <?= ($filters['academic_year_id']??'')==$ay['id']?'selected':'' ?>><?= e($ay['year_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Course</label>
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['course_id']??'')==$c['id']?'selected':'' ?>><?= e($c['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <?php foreach(['pending','partial','paid','overdue','waived'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Student Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Name / Roll / Enroll..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i></button>
                <a href="<?= url('fees/assignment') ?>" class="btn btn-light btn-sm w-100"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Assignments Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold"><i class="fas fa-list me-2 text-primary"></i>Assignments
            <span class="badge bg-secondary-subtle text-secondary border ms-1"><?= count($assignments) ?></span>
        </span>
        <a href="<?= url('fees/assignment/export') ?><?= !empty($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:'' ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-download me-1"></i>Export
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="assignTable">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Course / Batch</th>
                    <th>Fee Head</th>
                    <th class="text-end">Gross</th>
                    <th class="text-end">Concession</th>
                    <th class="text-end">Net</th>
                    <th class="text-end">Paid</th>
                    <th class="text-end">Balance</th>
                    <th class="text-center">Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($assignments)): ?>
            <tr><td colspan="11" class="text-center py-5 text-muted">
                <i class="fas fa-user-tag fa-2x mb-2 d-block opacity-25"></i>No fee assignments found.
            </td></tr>
            <?php else: foreach($assignments as $a): ?>
            <tr>
                <td>
                    <div class="fw-semibold"><?= e($a['student_name']) ?></div>
                    <div class="small text-muted"><?= e($a['roll_number'] ?? $a['enrollment_no'] ?? '') ?></div>
                </td>
                <td>
                    <span class="badge bg-primary-subtle text-primary border"><?= e($a['course_code'] ?? $a['course_name']) ?></span>
                    <?php if($a['batch_name']): ?><br><span class="small text-muted"><?= e($a['batch_name']) ?></span><?php endif; ?>
                </td>
                <td>
                    <div class="fw-semibold"><?= e($a['head_name']) ?></div>
                    <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem"><?= e($a['head_code']) ?></span>
                </td>
                <td class="text-end">₹<?= number_format($a['gross_amount'],2) ?></td>
                <td class="text-end text-success"><?= $a['concession_amount']>0?'(₹'.number_format($a['concession_amount'],2).')':'—' ?></td>
                <td class="text-end fw-semibold">₹<?= number_format($a['net_amount'],2) ?></td>
                <td class="text-end text-success">₹<?= number_format($a['paid_amount'],2) ?></td>
                <td class="text-end <?= $a['balance_amount']>0?'text-danger fw-bold':'text-success' ?>">
                    ₹<?= number_format($a['balance_amount'],2) ?>
                </td>
                <td class="text-center">
                    <?php
                    $sc = ['pending'=>'warning','partial'=>'info','paid'=>'success','overdue'=>'danger','waived'=>'secondary'];
                    $s = $a['status'];
                    ?>
                    <span class="badge bg-<?= $sc[$s]??'secondary' ?>-subtle text-<?= $sc[$s]??'secondary' ?> border"><?= ucfirst($s) ?></span>
                </td>
                <td>
                    <?php if($a['due_date']): ?>
                    <span class="<?= strtotime($a['due_date']) < time() && $a['status']!='paid'?'text-danger fw-semibold':'' ?>"><?= date('d M Y', strtotime($a['due_date'])) ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <?php if(in_array($a['status'],['pending','partial','overdue'])): ?>
                        <a href="<?= url('fees/collection') ?>?student_id=<?= $a['student_id'] ?>" class="btn btn-light border" title="Collect Fee">
                            <i class="fas fa-cash-register text-success"></i>
                        </a>
                        <?php endif; ?>
                        <?php if($a['status']!='waived'): ?>
                        <button class="btn btn-light border btn-waive-fee" data-id="<?= $a['id'] ?>" data-name="<?= e($a['student_name']) ?>" data-head="<?= e($a['head_name']) ?>" title="Waive">
                            <i class="fas fa-hand-holding-usd text-warning"></i>
                        </button>
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

<!-- Bulk Assign Modal -->
<div class="modal fade" id="modalBulkAssign" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-users me-2 text-success"></i>Bulk Assign Fee Structure</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small"><i class="fas fa-info-circle me-1"></i>This will assign all fee heads from the selected structure to all students in the selected course/batch.</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Academic Year <span class="text-danger">*</span></label>
                        <select id="ba_ay" class="form-select">
                            <option value="">Select Year</option>
                            <?php foreach($academicYears as $ay): ?>
                            <option value="<?= $ay['id'] ?>"><?= e($ay['year_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Fee Structure <span class="text-danger">*</span></label>
                        <select id="ba_structure" class="form-select select2">
                            <option value="">Select Structure</option>
                            <?php foreach($structures as $st): ?>
                            <option value="<?= $st['id'] ?>"><?= e($st['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                        <select id="ba_course" class="form-select select2">
                            <option value="">All Students</option>
                            <?php foreach($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['course_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Batch</label>
                        <select id="ba_batch" class="form-select">
                            <option value="">All Batches</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-warning d-none" id="bulkPreview"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" id="btnDoBulkAssign"><i class="fas fa-check me-1"></i>Assign Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Waive Modal -->
<div class="modal fade" id="modalWaive" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-hand-holding-usd me-2 text-warning"></i>Waive Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Waiving fee for: <strong id="waiveStudentName"></strong> — <strong id="waiveHeadName"></strong></p>
                <input type="hidden" id="waiveId">
                <label class="form-label fw-semibold">Reason for Waiver <span class="text-danger">*</span></label>
                <textarea id="waiveReason" class="form-control" rows="3" placeholder="Enter reason..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-warning" id="btnDoWaive"><i class="fas fa-check me-1"></i>Confirm Waive</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= url('') ?>';
$(document).ready(function() {
    $('#assignTable').DataTable({ order: [[8,'asc']], pageLength: 25, dom: 'lBfrtip',
        buttons: ['copy','csv','print'] });
    $('.select2').select2({ width: '100%', dropdownParent: $('.modal') });
});

// Batch AJAX on course change
$('#ba_course').on('change', function() {
    const cid = $(this).val();
    if (!cid) return;
    $.getJSON(BASE + 'fees/structures/ajax/batches?course_id=' + cid, function(r) {
        const sel = $('#ba_batch').empty().append('<option value="">All Batches</option>');
        (r.data || []).forEach(b => sel.append(`<option value="${b.id}">${b.batch_name}</option>`));
    });
});

// Bulk Assign
$('#btnDoBulkAssign').on('click', function() {
    const data = {
        academic_year_id: $('#ba_ay').val(),
        structure_id:     $('#ba_structure').val(),
        course_id:        $('#ba_course').val(),
        batch_id:         $('#ba_batch').val(),
        csrf_token:       $('meta[name="csrf-token"]').attr('content'),
    };
    if (!data.academic_year_id || !data.structure_id) {
        return toastr.warning('Please select Academic Year and Fee Structure.');
    }
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Assigning...');
    $.post(BASE + 'fees/assignment/bulk-assign', data, function(r) {
        if (r.status === 'success') {
            toastr.success(r.message);
            $('#modalBulkAssign').modal('hide');
            setTimeout(() => location.reload(), 800);
        } else toastr.error(r.message);
    }).fail(() => toastr.error('Network error.'))
    .always(() => $('#btnDoBulkAssign').prop('disabled', false).html('<i class="fas fa-check me-1"></i>Assign Now'));
});

// Waive
$(document).on('click', '.btn-waive-fee', function() {
    $('#waiveId').val($(this).data('id'));
    $('#waiveStudentName').text($(this).data('name'));
    $('#waiveHeadName').text($(this).data('head'));
    $('#waiveReason').val('');
    $('#modalWaive').modal('show');
});
$('#btnDoWaive').on('click', function() {
    const id     = $('#waiveId').val();
    const reason = $('#waiveReason').val().trim();
    if (!reason) return toastr.warning('Please enter a reason.');
    $(this).prop('disabled', true);
    $.post(BASE + 'fees/assignment/' + id + '/waive', {
        reason: reason,
        csrf_token: $('meta[name="csrf-token"]').attr('content')
    }, function(r) {
        if (r.status === 'success') { toastr.success(r.message); $('#modalWaive').modal('hide'); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    }).always(() => $('#btnDoWaive').prop('disabled', false));
});
</script>
