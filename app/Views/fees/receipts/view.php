<?php $pageTitle = 'Receipt — ' . e($receipt['receipt_number'] ?? ''); ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-receipt me-2 text-success"></i>Receipt Detail</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fees</a></li>
            <li class="breadcrumb-item"><a href="<?= url('fees/collection') ?>">Collection</a></li>
            <li class="breadcrumb-item active"><?= e($receipt['receipt_number']) ?></li>
        </ol></nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('fees/receipts/' . $receipt['id'] . '/print') ?>" target="_blank" class="btn btn-primary">
            <i class="fas fa-print me-1"></i>Print Receipt
        </a>
        <?php if ($receipt['status'] === 'active'): ?>
        <button class="btn btn-outline-danger" id="btnCancel">
            <i class="fas fa-ban me-1"></i>Cancel Receipt
        </button>
        <?php endif; ?>
        <a href="<?= url('fees/collection') ?>" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php if ($receipt['status'] === 'cancelled'): ?>
<div class="alert alert-danger d-flex gap-2 mb-4">
    <i class="fas fa-ban fa-lg mt-1"></i>
    <div>
        <strong>Receipt Cancelled</strong><br>
        <span class="small"><?= e($receipt['cancel_reason'] ?? '') ?></span>
        <?php if (!empty($receipt['cancelled_at'])): ?>
        <span class="small text-muted ms-2">on <?= date('d M Y h:i A', strtotime($receipt['cancelled_at'])) ?></span>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Receipt Summary Card -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-success text-white py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-bold fs-5"><i class="fas fa-receipt me-2"></i><?= e($receipt['receipt_number']) ?></span>
                    <?php if ($receipt['status'] === 'cancelled'): ?>
                    <span class="badge bg-danger">Cancelled</span>
                    <?php else: ?>
                    <span class="badge bg-light text-success">Active</span>
                    <?php endif; ?>
                </div>
                <div class="small opacity-75 mt-1"><?= date('d M Y, h:i A', strtotime($receipt['receipt_date'])) ?></div>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Student</td>
                        <td class="fw-semibold"><?= e($receipt['student_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Enrollment No.</td>
                        <td><?= e($receipt['enrollment_no']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Course</td>
                        <td><?= e($receipt['course_name'] ?? '—') ?></td>
                    </tr>
                    <?php if (!empty($receipt['batch_name'])): ?>
                    <tr>
                        <td class="text-muted">Batch</td>
                        <td><?= e($receipt['batch_name']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Academic Year</td>
                        <td><?= e($receipt['academic_year'] ?: '—') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Mode</td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border"><?= strtoupper($receipt['payment_mode']) ?></span>
                        </td>
                    </tr>
                    <?php if (!empty($receipt['reference_number'])): ?>
                    <tr>
                        <td class="text-muted">Reference No.</td>
                        <td><?= e($receipt['reference_number']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($receipt['cheque_number'])): ?>
                    <tr>
                        <td class="text-muted">Cheque No.</td>
                        <td><?= e($receipt['cheque_number']) ?><?= !empty($receipt['cheque_date']) ? ' (' . date('d M Y', strtotime($receipt['cheque_date'])) . ')' : '' ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Bank</td>
                        <td><?= e($receipt['bank_name'] ?? '—') ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="text-muted">Collected By</td>
                        <td><?= e($receipt['collector_name'] ?? '—') ?></td>
                    </tr>
                    <?php if (!empty($receipt['remarks'])): ?>
                    <tr>
                        <td class="text-muted">Remarks</td>
                        <td class="small"><?= e($receipt['remarks']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>

                <hr>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Fee Amount</span>
                    <span class="fw-semibold">₹<?= number_format($receipt['total_amount'], 2) ?></span>
                </div>
                <?php if (($receipt['fine_amount'] ?? 0) > 0): ?>
                <div class="d-flex justify-content-between mb-1 text-danger">
                    <span>Fine</span>
                    <span class="fw-semibold">+₹<?= number_format($receipt['fine_amount'], 2) ?></span>
                </div>
                <?php endif; ?>
                <?php if (($receipt['discount_amount'] ?? 0) > 0): ?>
                <div class="d-flex justify-content-between mb-1 text-success">
                    <span>Discount</span>
                    <span class="fw-semibold">-₹<?= number_format($receipt['discount_amount'], 2) ?></span>
                </div>
                <?php endif; ?>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-5">Total Paid</span>
                    <span class="fw-bold fs-4 text-success">₹<?= number_format($receipt['net_amount'], 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Items Breakdown -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-semibold py-3">
                <i class="fas fa-list-ul me-2 text-primary"></i>Fee Breakdown
            </div>
            <div class="card-body p-0">
                <?php if (empty($receipt['items'])): ?>
                <div class="text-center py-4 text-muted">No items found.</div>
                <?php else: ?>
                <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fee Head</th>
                            <th>Category</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Fine</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($receipt['items'] as $item): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= e($item['head_name']) ?></div>
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size:.65rem"><?= e($item['head_code'] ?? '') ?></span>
                        </td>
                        <td><span class="badge bg-info-subtle text-info border"><?= ucfirst($item['category'] ?? '') ?></span></td>
                        <td class="text-end">₹<?= number_format($item['amount'], 2) ?></td>
                        <td class="text-end text-danger"><?= ($item['fine_amount'] ?? 0) > 0 ? '+₹' . number_format($item['fine_amount'], 2) : '—' ?></td>
                        <td class="text-end fw-bold">₹<?= number_format(($item['amount'] ?? 0) + ($item['fine_amount'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold table-light">
                            <td colspan="2">Total</td>
                            <td class="text-end">₹<?= number_format(array_sum(array_column($receipt['items'], 'amount')), 2) ?></td>
                            <td class="text-end text-danger">+₹<?= number_format(array_sum(array_column($receipt['items'], 'fine_amount')), 2) ?></td>
                            <td class="text-end text-success fs-6">₹<?= number_format($receipt['net_amount'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Institution Info -->
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                        <i class="fas fa-university text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?= e($receipt['institution_name'] ?? '') ?></div>
                        <div class="small text-muted"><?= e($receipt['institution_address'] ?? '') ?></div>
                        <?php if (!empty($receipt['institution_phone'])): ?>
                        <div class="small text-muted"><i class="fas fa-phone me-1"></i><?= e($receipt['institution_phone']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<?php if ($receipt['status'] === 'active'): ?>
<div class="modal fade" id="modalCancel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-ban me-2"></i>Cancel Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Cancelling this receipt will reverse all fee payments and restore outstanding balances.
                </div>
                <label class="form-label fw-semibold">Cancellation Reason <span class="text-danger">*</span></label>
                <textarea id="cancelReason" class="form-control" rows="3" placeholder="Enter reason for cancellation..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-danger" id="btnConfirmCancel"><i class="fas fa-ban me-1"></i>Confirm Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btnCancel').addEventListener('click', function() {
    new bootstrap.Modal(document.getElementById('modalCancel')).show();
});
document.getElementById('btnConfirmCancel').addEventListener('click', function() {
    const reason = document.getElementById('cancelReason').value.trim();
    if (!reason) { toastr.warning('Please enter a cancellation reason.'); return; }
    this.disabled = true;
    const body = new URLSearchParams({ reason: reason, csrf_token: '<?= csrfToken() ?>' });
    fetch('<?= url('fees/receipts/' . $receipt['id'] . '/cancel') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === 'success') {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(data.message);
            this.disabled = false;
        }
    })
    .catch(() => { toastr.error('Network error.'); this.disabled = false; });
});
</script>
<?php endif; ?>
