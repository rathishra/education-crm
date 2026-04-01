<?php $pageTitle = 'Fee Collection'; ?>

<style>
.student-card { border-left: 4px solid #4f46e5; background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%); }
.fee-row-check:checked + label { background: #eef2ff; border-color: #4f46e5 !important; }
.fee-item { transition: all .15s; border: 1.5px solid #e2e8f0; cursor: pointer; }
.fee-item:hover { border-color: #c7d2fe; background: #f8f9ff; }
.fee-item.selected { border-color: #4f46e5 !important; background: #eef2ff; }
.payment-mode-btn { border: 1.5px solid #e2e8f0; border-radius: 10px; padding: .5rem 1rem; cursor: pointer; transition: all .15s; }
.payment-mode-btn:hover, .payment-mode-btn.active { border-color: #4f46e5; background: #eef2ff; color: #4f46e5; }
.receipt-badge { font-size: .65rem; letter-spacing: .04em; }
</style>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-cash-register me-2 text-primary"></i>Fee Collection</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item active">Collection</li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('fees/reports/collection') ?>" class="btn btn-outline-primary"><i class="fas fa-chart-bar me-1"></i>Reports</a>
    </div>
</div>

<!-- Today Stats -->
<div class="row g-3 mb-4">
    <?php foreach([
        ['Today Collection', '₹'.number_format($todayStats['total_collected']??0,2), 'cash-register', 'primary'],
        ['Receipts Today',   $todayStats['receipt_count']??0, 'receipt', 'success'],
        ['Cash',             '₹'.number_format($todayStats['cash_amt']??0,2), 'money-bill', 'warning'],
        ['UPI / Card',       '₹'.number_format(($todayStats['upi_amt']??0)+($todayStats['card_amt']??0),2), 'credit-card', 'info'],
    ] as [$label,$val,$icon,$color]): ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle bg-<?= $color ?>-subtle d-flex align-items-center justify-content-center" style="width:46px;height:46px">
                    <i class="fas fa-<?= $icon ?> text-<?= $color ?>"></i>
                </div>
                <div><div class="fw-bold fs-5"><?= $val ?></div><div class="text-muted small"><?= $label ?></div></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- Left: Student Search + Fee Panel -->
    <div class="col-lg-8">
        <!-- Student Search -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <label class="form-label fw-semibold mb-2"><i class="fas fa-search me-2 text-primary"></i>Search Student</label>
                <select id="studentSearch" class="form-select select2" style="width:100%">
                    <option value="">Type student name or enrollment number...</option>
                </select>
            </div>
        </div>

        <!-- Student Info + Pending Fees (hidden until student selected) -->
        <div id="studentPanel" style="display:none">
            <!-- Student Card -->
            <div class="card shadow-sm border-0 student-card mb-3">
                <div class="card-body py-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold" style="width:48px;height:48px;font-size:1.2rem" id="sAvatar">A</div>
                    <div class="flex-grow-1">
                        <div class="fw-bold fs-5" id="sName">—</div>
                        <div class="small text-muted">
                            <span id="sEnroll" class="me-3"><i class="fas fa-id-card me-1"></i></span>
                            <span id="sCourse"><i class="fas fa-book me-1"></i></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Outstanding Balance</div>
                        <div class="fw-bold fs-4 text-danger" id="sBalance">₹0</div>
                    </div>
                </div>
            </div>

            <!-- Fee Items -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <span class="fw-semibold"><i class="fas fa-list-ul me-2 text-primary"></i>Pending Fees</span>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" id="btnSelectAll">Select All</button>
                        <button class="btn btn-sm btn-outline-secondary" id="btnClearAll">Clear</button>
                    </div>
                </div>
                <div class="card-body p-0" id="feeItemsList">
                    <div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin me-2"></i>Loading fees...</div>
                </div>
                <!-- Totals Row -->
                <div class="card-footer bg-light">
                    <div class="row g-2 text-center">
                        <div class="col-3"><div class="small text-muted">Fee Amount</div><div class="fw-bold" id="sumNet">₹0</div></div>
                        <div class="col-3"><div class="small text-muted">Fine</div><div class="fw-bold text-danger" id="sumFine">₹0</div></div>
                        <div class="col-3"><div class="small text-muted">Selected</div><div class="fw-bold text-primary" id="sumSelected">₹0</div></div>
                        <div class="col-3"><div class="small text-danger">Balance After</div><div class="fw-bold text-success" id="sumAfter">₹0</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div id="emptyPanel" class="card shadow-sm border-0 bg-light">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-user-search fa-3x mb-3 opacity-25 d-block"></i>
                <p class="mb-0">Search a student above to view their pending fees</p>
            </div>
        </div>
    </div>

    <!-- Right: Payment Panel + Today's Receipts -->
    <div class="col-lg-4">
        <!-- Payment Form -->
        <div class="card shadow-sm border-0 mb-3 sticky-top" style="top:80px">
            <div class="card-header bg-primary text-white py-3 fw-semibold"><i class="fas fa-rupee-sign me-2"></i>Collect Payment</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Mode</label>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach([['cash','Cash','money-bill'],['upi','UPI','qrcode'],['card','Card','credit-card'],['netbanking','Net Banking','university'],['cheque','Cheque','file-alt']] as [$v,$l,$i]): ?>
                        <div class="payment-mode-btn <?= $v==='cash'?'active':'' ?>" data-mode="<?= $v ?>">
                            <i class="fas fa-<?= $i ?> me-1"></i><?= $l ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selectedMode" value="cash">
                </div>

                <div id="refSection" style="display:none" class="mb-3">
                    <label class="form-label fw-semibold">Reference / UTR Number</label>
                    <input type="text" id="refNumber" class="form-control" placeholder="Transaction reference">
                </div>

                <div id="chequeSection" style="display:none" class="mb-3">
                    <div class="row g-2">
                        <div class="col-12"><label class="form-label fw-semibold small">Cheque Number</label>
                            <input type="text" id="chequeNumber" class="form-control form-control-sm"></div>
                        <div class="col-6"><label class="form-label fw-semibold small">Cheque Date</label>
                            <input type="date" id="chequeDate" class="form-control form-control-sm"></div>
                        <div class="col-6"><label class="form-label fw-semibold small">Bank Name</label>
                            <input type="text" id="bankName" class="form-control form-control-sm"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Remarks</label>
                    <input type="text" id="payRemarks" class="form-control form-control-sm" placeholder="Optional remarks">
                </div>

                <!-- Payment Summary -->
                <div class="bg-primary-subtle rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between mb-1"><span class="small">Fees</span><span class="fw-semibold" id="payFees">₹0.00</span></div>
                    <div class="d-flex justify-content-between mb-1 text-danger"><span class="small">Fine</span><span class="fw-semibold" id="payFine">₹0.00</span></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between"><span class="fw-bold">Total Payable</span><span class="fw-bold fs-5 text-primary" id="payTotal">₹0.00</span></div>
                </div>

                <button class="btn btn-primary w-100 btn-lg" id="btnCollect" disabled>
                    <i class="fas fa-check-circle me-2"></i>Collect Payment
                </button>
            </div>
        </div>

        <!-- Today's Receipts -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold py-2"><i class="fas fa-history me-2 text-success"></i>Today's Receipts
                <span class="badge bg-success ms-1"><?= count($todayReceipts) ?></span>
            </div>
            <div class="card-body p-0" style="max-height:350px;overflow-y:auto">
                <?php if(empty($todayReceipts)): ?>
                <div class="text-center py-3 text-muted small">No receipts today</div>
                <?php else: foreach($todayReceipts as $r): ?>
                <div class="d-flex align-items-center gap-2 p-2 border-bottom hover-bg">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold small text-truncate"><?= e($r['student_name']) ?></div>
                        <div class="text-muted" style="font-size:.7rem"><?= e($r['receipt_number']) ?> · <?= ucfirst($r['payment_mode']) ?></div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold text-success small">₹<?= number_format($r['net_amount'],0) ?></div>
                        <a href="<?= url('fees/receipts/'.$r['id'].'/print') ?>" target="_blank" class="badge bg-primary-subtle text-primary border receipt-badge">Print</a>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="modalReceiptSuccess" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center py-4">
                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center mx-auto mb-3 text-white" style="width:64px;height:64px;font-size:1.8rem">
                    <i class="fas fa-check"></i>
                </div>
                <h5 class="fw-bold mb-1">Payment Collected!</h5>
                <p class="text-muted small mb-3">Receipt: <strong id="modalReceiptNo"></strong></p>
                <div class="d-flex gap-2">
                    <a href="#" id="btnPrintReceipt" target="_blank" class="btn btn-primary flex-grow-1"><i class="fas fa-print me-1"></i>Print</a>
                    <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal" onclick="resetCollection()">New</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= url('') ?>';
const CSRF = $('meta[name="csrf-token"]').attr('content');
let currentStudent = null;
let currentAssignments = [];

// Student Select2
$('#studentSearch').select2({
    placeholder: 'Type name or enrollment number...',
    minimumInputLength: 2,
    ajax: {
        url: BASE + 'fees/assignment/ajax/search',
        dataType: 'json',
        delay: 300,
        data: params => ({ q: params.term, csrf_token: CSRF }),
        processResults: data => ({ results: data.results }),
    }
});

$('#studentSearch').on('select2:select', function(e) {
    loadStudentFees(e.params.data.id);
});

function loadStudentFees(studentId) {
    currentStudent = studentId;
    $.getJSON(BASE + 'fees/collection/student-fees?student_id=' + studentId, function(r) {
        if (r.status !== 'success') { toastr.error(r.message); return; }
        const st = r.student;
        const totals = r.totals;

        // Fill student card
        $('#sName').text(st.full_name);
        $('#sEnroll').html('<i class="fas fa-id-card me-1"></i>' + st.enrollment_number);
        $('#sCourse').html('<i class="fas fa-book me-1"></i>' + (st.course_name || 'N/A'));
        $('#sBalance').text('₹' + parseFloat(totals.balance).toLocaleString('en-IN'));
        $('#sAvatar').text((st.full_name || 'A')[0].toUpperCase());

        // Render fee items
        currentAssignments = r.assignments;
        renderFeeItems(r.assignments, totals);

        $('#emptyPanel').hide();
        $('#studentPanel').show();
    }).fail(() => toastr.error('Failed to load student fees.'));
}

function renderFeeItems(assignments, totals) {
    if (!assignments.length) {
        $('#feeItemsList').html('<div class="text-center py-4 text-success"><i class="fas fa-check-circle fa-2x mb-2 d-block"></i>No pending fees!</div>');
        return;
    }
    let html = '';
    assignments.forEach((a, i) => {
        const isOverdue = a.due_date && new Date(a.due_date) < new Date();
        const balance = parseFloat(a.balance_amount);
        const fine = parseFloat(a.fine_amount);
        html += `<div class="fee-item p-3 m-2 rounded-2 selected" data-id="${a.id}" data-balance="${balance}" data-fine="${fine}" onclick="toggleFeeItem(this)">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" class="form-check-input fee-checkbox" checked data-id="${a.id}" onclick="event.stopPropagation()">
                <div class="flex-grow-1">
                    <div class="fw-semibold small">${a.head_name}</div>
                    <div class="text-muted" style="font-size:.7rem">${a.category} · ${a.year_name || '—'}
                        ${a.due_date ? '<span class="ms-1 badge ' + (isOverdue ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success') + ' border">' + a.due_date + '</span>' : ''}
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-danger small">₹${parseFloat(balance).toFixed(2)}</div>
                    ${fine > 0 ? '<div class="text-danger" style="font-size:.65rem">+₹' + fine.toFixed(2) + ' fine</div>' : ''}
                </div>
            </div>
            <div class="mt-2">
                <label class="form-label mb-0 small fw-semibold">Pay Amount:</label>
                <input type="number" class="form-control form-control-sm pay-amt-input"
                    data-id="${a.id}" value="${balance.toFixed(2)}" max="${balance.toFixed(2)}"
                    step="0.01" min="0" onclick="event.stopPropagation()">
            </div>
        </div>`;
    });
    $('#feeItemsList').html(html);
    recalcPayment();
}

function toggleFeeItem(el) {
    $(el).toggleClass('selected');
    $(el).find('.fee-checkbox').prop('checked', $(el).hasClass('selected'));
    recalcPayment();
}

function recalcPayment() {
    let fees = 0, fine = 0;
    $('.fee-item.selected').each(function() {
        const id = $(this).data('id');
        const amt = parseFloat($(this).find('.pay-amt-input').val()) || 0;
        fees += amt;
        fine += parseFloat($(this).data('fine')) || 0;
    });
    const total = fees + fine;
    $('#sumSelected').text('₹' + fees.toFixed(2));
    $('#sumFine').text('₹' + fine.toFixed(2));
    $('#payFees').text('₹' + fees.toFixed(2));
    $('#payFine').text('₹' + fine.toFixed(2));
    $('#payTotal').text('₹' + total.toFixed(2));
    $('#btnCollect').prop('disabled', total <= 0 || !currentStudent);
    // Update balance after
    const totalPending = parseFloat(currentAssignments.reduce((s,a) => s + parseFloat(a.balance_amount), 0));
    $('#sumAfter').text('₹' + Math.max(0, totalPending - fees).toFixed(2));
}

$(document).on('input', '.pay-amt-input', recalcPayment);

$('#btnSelectAll').on('click', function() {
    $('.fee-item').addClass('selected').find('.fee-checkbox').prop('checked', true);
    recalcPayment();
});
$('#btnClearAll').on('click', function() {
    $('.fee-item').removeClass('selected').find('.fee-checkbox').prop('checked', false);
    recalcPayment();
});

// Payment mode toggle
$(document).on('click', '.payment-mode-btn', function() {
    $('.payment-mode-btn').removeClass('active');
    $(this).addClass('active');
    const mode = $(this).data('mode');
    $('#selectedMode').val(mode);
    $('#refSection').toggle(mode === 'upi' || mode === 'netbanking' || mode === 'card');
    $('#chequeSection').toggle(mode === 'cheque');
});

// Collect payment
$('#btnCollect').on('click', function() {
    const selected = $('.fee-item.selected');
    if (!selected.length) { toastr.warning('Select at least one fee.'); return; }

    const assignIds = [], payAmounts = [];
    selected.each(function() {
        assignIds.push($(this).data('id'));
        payAmounts.push($(this).find('.pay-amt-input').val());
    });

    const data = {
        student_id:       currentStudent,
        payment_mode:     $('#selectedMode').val(),
        reference_number: $('#refNumber').val(),
        cheque_number:    $('#chequeNumber').val(),
        cheque_date:      $('#chequeDate').val(),
        bank_name:        $('#bankName').val(),
        remarks:          $('#payRemarks').val(),
        csrf_token:       CSRF,
        'assignment_ids[]': assignIds,
        'pay_amounts[]':    payAmounts,
    };

    $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
    $.post(BASE + 'fees/collection/collect', data, function(r) {
        if (r.status === 'success') {
            $('#modalReceiptNo').text(r.receipt_number);
            $('#btnPrintReceipt').attr('href', BASE + 'fees/receipts/' + r.receipt_id + '/print');
            $('#modalReceiptSuccess').modal('show');
        } else {
            toastr.error(r.message);
        }
    }).fail(() => toastr.error('Network error.'))
    .always(() => $('#btnCollect').prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Collect Payment'));
});

function resetCollection() {
    currentStudent = null;
    currentAssignments = [];
    $('#studentSearch').val(null).trigger('change');
    $('#studentPanel').hide();
    $('#emptyPanel').show();
    recalcPayment();
}
</script>
