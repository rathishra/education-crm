<?php $pageTitle = 'Create Fee Structure'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-plus-circle me-2 text-primary"></i>Create Fee Structure</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= url('fees/structures') ?>">Fee Structures</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol></nav>
    </div>
    <a href="<?= url('fees/structures') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php if($errors = getFlash('errors')): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="POST" action="<?= url('fees/structures/store') ?>" id="structureForm">
    <?= csrfField() ?>
    <div class="row g-4">
        <!-- Left: Structure Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-info-circle me-2 text-primary"></i>Structure Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Structure Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. B.Tech CSE 2025-26 Fee Structure" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select name="academic_year_id" class="form-select select2">
                                <option value="">Select Academic Year</option>
                                <?php foreach($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>"><?= e($ay['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                            <select name="course_id" id="courseSelect" class="form-select select2" required>
                                <option value="">Select Course</option>
                                <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Batch</label>
                            <select name="batch_id" id="batchSelect" class="form-select">
                                <option value="">All Batches</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Semester</label>
                            <select name="semester" class="form-select">
                                <option value="">All Semesters</option>
                                <?php for($i=1;$i<=10;$i++): ?><option value="<?= $i ?>">Semester <?= $i ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Admission Type</label>
                            <select name="admission_type" class="form-select">
                                <option value="regular">Regular</option>
                                <option value="lateral">Lateral</option>
                                <option value="management">Management</option>
                                <option value="scholarship">Scholarship</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Grace Period (days)</label>
                            <input type="number" name="grace_period_days" class="form-control" value="7" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Late Fee/Day (₹)</label>
                            <input type="number" name="late_fee_per_day" class="form-control" value="0" step="0.01" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Max Installments</label>
                            <input type="number" name="max_installments" class="form-control" value="4" min="1" max="24">
                        </div>
                        <div class="col-md-3 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="installments_allowed" id="installAllowed" value="1" checked>
                                <label class="form-check-label" for="installAllowed">Allow Installments</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Heads Table -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <span class="fw-semibold"><i class="fas fa-list me-2 text-success"></i>Fee Heads & Amounts</span>
                    <button type="button" class="btn btn-sm btn-success" id="btnAddRow"><i class="fas fa-plus me-1"></i>Add Row</button>
                </div>
                <div class="card-body p-0">
                    <table class="table align-middle mb-0" id="feeHeadsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Fee Head <span class="text-danger">*</span></th>
                                <th>Amount (₹) <span class="text-danger">*</span></th>
                                <th>Due Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="feeRowsBody">
                            <!-- Rows added dynamically -->
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td>Total</td>
                                <td id="totalAmtDisplay" class="text-success fs-5">₹0.00</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Fee Heads Quick Reference -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top:80px">
                <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-bookmark me-2 text-warning"></i>Available Fee Heads</div>
                <div class="card-body p-2" style="max-height:400px;overflow-y:auto">
                    <?php foreach(array_unique(array_column($feeHeads,'category')) as $cat): ?>
                    <div class="small fw-bold text-muted text-uppercase px-2 pt-2 pb-1"><?= $cat ?></div>
                    <?php foreach(array_filter($feeHeads, fn($h) => $h['category']===$cat) as $h): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start mb-1 btn-quick-add"
                        data-id="<?= $h['id'] ?>" data-name="<?= e($h['head_name']) ?>" data-code="<?= e($h['head_code']) ?>">
                        <i class="fas fa-plus me-1 text-success"></i>
                        <?= e($h['head_name']) ?>
                        <?php if($h['is_mandatory']): ?><span class="badge bg-danger-subtle text-danger border ms-1 float-end" style="font-size:.6rem">Required</span><?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Save Fee Structure</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const BASE = '<?= url('') ?>';
const feeHeads = <?= json_encode($feeHeads) ?>;
let rowIdx = 0;

function addFeeRow(headId = '', headName = '', amount = '') {
    rowIdx++;
    const opts = feeHeads.map(h =>
        `<option value="${h.id}" ${h.id == headId ? 'selected' : ''}>${h.head_name} (${h.head_code})</option>`
    ).join('');
    const row = `<tr id="frow-${rowIdx}">
        <td><select name="head_ids[]" class="form-select form-select-sm fee-head-sel select2">${opts}</select></td>
        <td><input type="number" name="head_amounts[]" class="form-control form-control-sm fee-amt" step="0.01" min="0" value="${amount}" placeholder="0.00"></td>
        <td><input type="date" name="head_due_dates[]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-rm-row" data-row="${rowIdx}"><i class="fas fa-times"></i></button></td>
    </tr>`;
    $('#feeRowsBody').append(row);
    $('.select2').select2({width:'100%'});
}

function recalcTotal() {
    let total = 0;
    $('.fee-amt').each(function() { total += parseFloat($(this).val()) || 0; });
    $('#totalAmtDisplay').text('₹' + total.toFixed(2));
}

$('#btnAddRow').on('click', () => addFeeRow());

$(document).on('click', '.btn-quick-add', function() {
    addFeeRow($(this).data('id'), $(this).data('name'), '');
});

$(document).on('click', '.btn-rm-row', function() {
    $('#frow-' + $(this).data('row')).remove();
    recalcTotal();
});

$(document).on('input', '.fee-amt', recalcTotal);

// Course → Batch AJAX
$('#courseSelect').on('change', function() {
    const cid = $(this).val();
    if (!cid) return;
    $.getJSON(BASE + 'fees/structures/ajax/batches?course_id=' + cid, function(r) {
        const sel = $('#batchSelect').empty().append('<option value="">All Batches</option>');
        (r.data || []).forEach(b => sel.append(`<option value="${b.id}">${b.name}</option>`));
    });
});

// Add one row by default
addFeeRow();
</script>
