<?php $pageTitle = 'Monthly Payroll'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-money-bill-wave me-2"></i>Monthly Payroll</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">HR & Payroll</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('users.manage')): ?>
        <form method="POST" action="<?= url('hr/payroll/generate') ?>" class="d-flex align-items-center" onsubmit="return confirm('Generate payroll for active staff for this month?');">
            <?= csrfField() ?>
            <input type="hidden" name="month" value="<?= e($month) ?>">
            <button type="submit" class="btn btn-success"><i class="fas fa-cogs me-1"></i> Generate Payroll for <?= e($month) ?></button>
        </form>
    <?php endif; ?>
</div>

<div class="card mb-4 bg-light">
    <div class="card-body py-2">
        <form method="GET" action="<?= url('hr/payroll') ?>" class="row align-items-center g-3">
            <div class="col-auto">
                <label class="form-label mb-0 fw-bold">Select Month:</label>
            </div>
            <div class="col-auto">
                <input type="month" class="form-control" name="month" value="<?= e($month) ?>" onchange="this.form.submit()">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="fas fa-list me-2"></i>Payroll Records - <?= e(date('F Y', strtotime($month.'-01'))) ?></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Staff Member</th>
                        <th>Designation</th>
                        <th>Basic Salary</th>
                        <th>Net Payable</th>
                        <th>Status</th>
                        <?php if (hasPermission('users.manage')): ?><th class="text-end">Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No payroll records generated for this month yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $p): ?>
                        <tr class="<?= $p['status'] === 'paid' ? 'table-success' : '' ?>">
                            <td>
                                <div class="fw-bold text-primary"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></div>
                            </td>
                            <td class="small text-muted"><?= e($p['designation'] ?: '—') ?></td>
                            <td><?= formatCurrency($p['basic_salary']) ?></td>
                            <td>
                                <div class="fw-bold text-success fs-6"><?= formatCurrency($p['net_salary']) ?></div>
                                <?php if ($p['allowances'] > 0 || $p['deductions'] > 0): ?>
                                    <div class="small text-muted">A: +<?= formatCurrency($p['allowances']) ?> | D: -<?= formatCurrency($p['deductions']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $statusColors = [
                                    'generated' => 'info',
                                    'processed' => 'primary',
                                    'paid' => 'success'
                                ];
                                ?>
                                <span class="badge bg-<?= $statusColors[$p['status']] ?>"><?= ucfirst($p['status']) ?></span>
                                <?php if ($p['status'] === 'paid'): ?>
                                    <div class="small text-muted mt-1"><?= formatDate($p['payment_date'], 'd M Y') ?></div>
                                <?php endif; ?>
                            </td>
                            <?php if (hasPermission('users.manage')): ?>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" onclick="openProcessModal(<?= $p['id'] ?>, '<?= e(addslashes($p['first_name'].' '.$p['last_name'])) ?>', <?= $p['basic_salary'] ?>, <?= $p['allowances'] ?>, <?= $p['deductions'] ?>, '<?= $p['status'] ?>', '<?= $p['payment_method'] ?: 'bank_transfer' ?>')">
                                    <i class="fas fa-cog me-1"></i>Process Row
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

<!-- Process Payroll Modal -->
<div class="modal fade" id="processModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="" id="processForm" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header bg-light">
                <h5 class="modal-title">Process Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 class="text-primary fw-bold mb-3" id="processStaffName"></h6>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Basic Salary</label>
                    <input type="text" class="form-control" id="processBasic" readonly disabled>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Allowances</label>
                        <input type="number" step="0.01" min="0" class="form-control calc-trigger" name="allowances" id="processAllowances">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Deductions / Leaves</label>
                        <input type="number" step="0.01" min="0" class="form-control calc-trigger" name="deductions" id="processDeductions">
                    </div>
                </div>
                <div class="mb-3 bg-light p-3 rounded text-center">
                    <span class="text-muted d-block mb-1">Calculated Net Salary</span>
                    <h3 class="text-success mb-0" id="processNet"></h3>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label required">Status</label>
                    <select class="form-select" name="status" id="processStatus" required>
                        <option value="generated">Generated (Unpaid)</option>
                        <option value="processed">Processed (Ready for Payout)</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div class="mb-3" id="paymentMethodDiv" style="display:none;">
                    <label class="form-label required">Payment Method</label>
                    <select class="form-select" name="payment_method" id="processPaymentMethod">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Payroll</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentBasic = 0;

function updateNet() {
    let a = parseFloat(document.getElementById('processAllowances').value) || 0;
    let d = parseFloat(document.getElementById('processDeductions').value) || 0;
    let net = currentBasic + a - d;
    document.getElementById('processNet').textContent = '<?= config('app.currency', '$') ?>' + net.toFixed(2);
}

document.querySelectorAll('.calc-trigger').forEach(inp => inp.addEventListener('input', updateNet));

document.getElementById('processStatus').addEventListener('change', function() {
    document.getElementById('paymentMethodDiv').style.display = this.value === 'paid' ? 'block' : 'none';
});

function openProcessModal(id, staffName, basic, allowances, deductions, status, method) {
    document.getElementById('processStaffName').textContent = staffName;
    document.getElementById('processBasic').value = basic;
    currentBasic = basic;
    document.getElementById('processAllowances').value = allowances;
    document.getElementById('processDeductions').value = deductions;
    document.getElementById('processStatus').value = status;
    document.getElementById('processPaymentMethod').value = method;
    
    document.getElementById('processForm').action = '<?= url('hr/payroll/') ?>' + id + '/process';
    
    document.getElementById('paymentMethodDiv').style.display = status === 'paid' ? 'block' : 'none';
    
    updateNet();
    
    var modal = new bootstrap.Modal(document.getElementById('processModal'));
    modal.show();
}
</script>
