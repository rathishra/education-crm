<?php $pageTitle = 'Collect Payment'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-hand-holding-usd me-2"></i>Collect Payment</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('payments') ?>">Payments</a></li>
                <li class="breadcrumb-item active">Collect</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('payments') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('payments') ?>">
    <?= csrfField() ?>
    <input type="hidden" name="student_id" value="<?= e($student['id'] ?? '') ?>">
    <input type="hidden" name="student_fee_id" id="studentFeeId" value="">

    <div class="row g-4">
        <!-- Student Info -->
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white"><i class="fas fa-user-graduate me-2"></i>Student Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center mb-3 mb-md-0">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto" style="width:70px;height:70px;font-size:2rem;">
                                <i class="fas fa-user-circle text-muted"></i>
                            </div>
                        </div>
                        <div class="col-md-10">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <small class="text-muted">Student ID</small>
                                    <div class="fw-bold"><?= e($student['student_id_number'] ?? '—') ?></div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Name</small>
                                    <div class="fw-bold"><?= e($student['name'] ?? '—') ?></div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Phone</small>
                                    <div><?= e($student['phone'] ?? '—') ?></div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Course</small>
                                    <div><?= e($student['course_name'] ?? '—') ?></div>
                                </div>
                                <div class="col-md-3">
                                    <small class="text-muted">Batch</small>
                                    <div><?= e($student['batch_name'] ?? '—') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Installments -->
        <?php if (!empty($installments)): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header"><i class="fas fa-list-alt me-2"></i>Pending Installments</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px"></th>
                                <th>Installment</th>
                                <th>Due Date</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($installments as $inst): ?>
                        <tr>
                            <td>
                                <input type="radio" name="installment_id" value="<?= $inst['id'] ?>"
                                       class="form-check-input installment-radio"
                                       data-amount="<?= $inst['balance'] ?? 0 ?>"
                                       data-fee-id="<?= $inst['student_fee_id'] ?>"
                                       <?= (old('installment_id') == $inst['id']) ? 'checked' : '' ?> required>
                            </td>
                            <td><?= e($inst['name'] ?? 'Installment') ?></td>
                            <td><?= !empty($inst['due_date']) ? formatDate($inst['due_date']) : '—' ?></td>
                            <td class="text-end"><?= formatCurrency($inst['amount'] ?? 0) ?></td>
                            <td class="text-end text-success"><?= formatCurrency($inst['paid'] ?? 0) ?></td>
                            <td class="text-end fw-bold text-danger"><?= formatCurrency($inst['balance'] ?? 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payment Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-credit-card me-2"></i>Payment Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Amount (₹)</label>
                            <input type="number" class="form-control" name="amount" id="payAmount"
                                   value="<?= e(old('amount')) ?>" min="1" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Payment Method</label>
                            <select class="form-select" name="payment_mode" id="paymentMode" required>
                                <?php foreach (['cash' => 'Cash', 'online' => 'Online', 'upi' => 'UPI', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', 'card' => 'Card'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= old('payment_mode') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date"
                                   value="<?= e(old('payment_date', date('Y-m-d'))) ?>" required>
                        </div>
                        <div class="col-md-6" id="transactionField">
                            <label class="form-label">Transaction / Reference ID</label>
                            <input type="text" class="form-control" name="transaction_id"
                                   value="<?= e(old('transaction_id')) ?>" placeholder="UTR, Ref No., etc.">
                        </div>
                        <div class="col-md-6 d-none" id="bankField">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-control" name="bank_name"
                                   value="<?= e(old('bank_name')) ?>">
                        </div>
                        <div class="col-md-6 d-none" id="chequeField">
                            <label class="form-label">Cheque Number</label>
                            <input type="text" class="form-control" name="cheque_number"
                                   value="<?= e(old('cheque_number')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="2"><?= e(old('remarks')) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-check-circle me-2"></i>Collect Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.querySelectorAll('.installment-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.getElementById('payAmount').value = this.dataset.amount;
        document.getElementById('studentFeeId').value = this.dataset.feeId;
    });
});

document.getElementById('paymentMode').addEventListener('change', function() {
    const mode = this.value;
    const bankField = document.getElementById('bankField');
    const chequeField = document.getElementById('chequeField');
    if (mode === 'cheque') {
        bankField.classList.remove('d-none');
        chequeField.classList.remove('d-none');
    } else if (mode === 'bank_transfer' || mode === 'dd') {
        bankField.classList.remove('d-none');
        chequeField.classList.add('d-none');
    } else {
        bankField.classList.add('d-none');
        chequeField.classList.add('d-none');
    }
});
</script>
