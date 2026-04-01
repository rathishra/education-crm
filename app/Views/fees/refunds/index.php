<?php $pageTitle = 'Fee Refunds'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-undo-alt me-2 text-info"></i>Fee Refunds</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item active">Refunds</li>
        </ol></nav>
    </div>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddRefund">
        <i class="fas fa-plus me-1"></i>New Refund Request
    </button>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach([
        ['Total Requests', $stats['total'] ?? 0, 'undo-alt', 'info'],
        ['Pending', $stats['pending'] ?? 0, 'clock', 'warning'],
        ['Approved', $stats['approved'] ?? 0, 'check-circle', 'success'],
        ['Processed', '₹'.number_format($stats['processed_amount'] ?? 0,2), 'coins', 'primary'],
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

<!-- Pending Approvals Alert -->
<?php $pendingRefunds = array_filter($refunds, fn($r) => $r['status'] === 'pending');
if(!empty($pendingRefunds)): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
    <i class="fas fa-bell fa-lg"></i>
    <span><strong><?= count($pendingRefunds) ?></strong> refund request(s) are awaiting approval.</span>
</div>
<?php endif; ?>

<!-- Refunds Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold"><i class="fas fa-list me-2 text-info"></i>Refund Requests
            <span class="badge bg-secondary-subtle text-secondary border ms-1"><?= count($refunds) ?></span>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="refundTable">
            <thead class="table-light">
                <tr><th>#</th><th>Student</th><th>Receipt</th><th class="text-end">Refund Amount</th>
                    <th>Reason</th><th>Refund Mode</th><th class="text-center">Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if(empty($refunds)): ?>
            <tr><td colspan="9" class="text-center py-5 text-muted">
                <i class="fas fa-undo-alt fa-2x mb-2 d-block opacity-25"></i>No refund requests yet.
            </td></tr>
            <?php else: foreach($refunds as $i => $r): ?>
            <tr>
                <td class="text-muted small"><?= $i+1 ?></td>
                <td>
                    <div class="fw-semibold"><?= e($r['student_name']) ?></div>
                    <div class="small text-muted"><?= e($r['roll_number'] ?? '') ?></div>
                </td>
                <td>
                    <?php if($r['receipt_number']): ?>
                    <a href="<?= url('fees/receipts/'.$r['receipt_id'].'/view') ?>" class="badge bg-primary-subtle text-primary border text-decoration-none">
                        <?= e($r['receipt_number']) ?>
                    </a>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td class="text-end fw-bold">₹<?= number_format($r['refund_amount'],2) ?></td>
                <td class="text-muted small" style="max-width:160px"><?= e(substr($r['reason'],0,60)).( strlen($r['reason'])>60?'...':'' ) ?></td>
                <td>
                    <span class="badge bg-secondary-subtle text-secondary border"><?= strtoupper($r['refund_mode'] ?? '—') ?></span>
                </td>
                <td class="text-center">
                    <?php $sc=['pending'=>'warning','approved'=>'info','rejected'=>'danger','processed'=>'success'];
                    $s=$r['status']; ?>
                    <span class="badge bg-<?= $sc[$s]??'secondary' ?>-subtle text-<?= $sc[$s]??'secondary' ?> border"><?= ucfirst($s) ?></span>
                </td>
                <td class="small text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <?php if($r['status']==='pending'): ?>
                        <button class="btn btn-light border btn-approve-refund" data-id="<?= $r['id'] ?>" title="Approve">
                            <i class="fas fa-check text-success"></i>
                        </button>
                        <button class="btn btn-light border btn-reject-refund" data-id="<?= $r['id'] ?>" title="Reject">
                            <i class="fas fa-times text-danger"></i>
                        </button>
                        <?php elseif($r['status']==='approved'): ?>
                        <button class="btn btn-light border btn-process-refund" data-id="<?= $r['id'] ?>" title="Mark Processed">
                            <i class="fas fa-coins text-primary"></i>
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

<!-- Add Refund Modal -->
<div class="modal fade" id="modalAddRefund" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-undo-alt me-2 text-info"></i>New Refund Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                        <select id="ref_student" class="form-select" style="width:100%">
                            <option value="">Search student...</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Receipt</label>
                        <select id="ref_receipt" class="form-select">
                            <option value="">Select Receipt</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Refund Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" id="ref_amount" class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Refund Mode</label>
                        <select id="ref_mode" class="form-select">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="dd">Demand Draft</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reference No.</label>
                        <input type="text" id="ref_reference" class="form-control" placeholder="Transaction / Cheque number">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                        <textarea id="ref_reason" class="form-control" rows="3" placeholder="Reason for refund..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-info text-white" id="btnSaveRefund"><i class="fas fa-save me-1"></i>Submit Request</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= url('') ?>';
$(document).ready(function() {
    $('#refundTable').DataTable({ order: [[7,'desc']], pageLength: 25, dom: 'lBfrtip', buttons: ['copy','csv','print'] });

    $('#ref_student').select2({
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
        dropdownParent: $('#modalAddRefund'),
    });

    $('#ref_student').on('change', function() {
        const sid = $(this).val();
        if (!sid) return;
        $.getJSON(BASE + 'fees/collection/student-receipts?student_id=' + sid, function(r) {
            const sel = $('#ref_receipt').empty().append('<option value="">Select Receipt</option>');
            (r.data || []).forEach(rec => sel.append(`<option value="${rec.id}">${rec.receipt_number} — ₹${parseFloat(rec.total_paid).toFixed(2)} (${rec.receipt_date})</option>`));
        });
    });
});

$('#btnSaveRefund').on('click', function() {
    const data = {
        student_id:   $('#ref_student').val(),
        receipt_id:   $('#ref_receipt').val() || '',
        refund_amount:$('#ref_amount').val(),
        refund_mode:  $('#ref_mode').val(),
        reference_no: $('#ref_reference').val(),
        reason:       $('#ref_reason').val(),
        csrf_token:   $('meta[name="csrf-token"]').attr('content'),
    };
    if (!data.student_id || !data.refund_amount || !data.reason) {
        return toastr.warning('Student, amount and reason are required.');
    }
    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Submitting...');
    $.post(BASE + 'fees/refunds/store', data, function(r) {
        if (r.status === 'success') { toastr.success(r.message); $('#modalAddRefund').modal('hide'); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    }).fail(() => toastr.error('Network error.'))
    .always(() => $('#btnSaveRefund').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Submit Request'));
});

$(document).on('click', '.btn-approve-refund', function() {
    const id = $(this).data('id');
    if (!confirm('Approve this refund request?')) return;
    $.post(BASE + 'fees/refunds/' + id + '/approve', { csrf_token: $('meta[name="csrf-token"]').attr('content') }, function(r) {
        if (r.status === 'success') { toastr.success(r.message); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    });
});
$(document).on('click', '.btn-reject-refund', function() {
    const id = $(this).data('id');
    if (!confirm('Reject this refund request?')) return;
    $.post(BASE + 'fees/refunds/' + id + '/reject', { csrf_token: $('meta[name="csrf-token"]').attr('content') }, function(r) {
        if (r.status === 'success') { toastr.success(r.message); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    });
});
$(document).on('click', '.btn-process-refund', function() {
    const id = $(this).data('id');
    if (!confirm('Mark this refund as processed?')) return;
    $.post(BASE + 'fees/refunds/' + id + '/process', { csrf_token: $('meta[name="csrf-token"]').attr('content') }, function(r) {
        if (r.status === 'success') { toastr.success(r.message); setTimeout(() => location.reload(), 700); }
        else toastr.error(r.message);
    });
});
</script>
