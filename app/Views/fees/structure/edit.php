<?php $pageTitle = 'Edit Fee Structure'; ?>

<div class="page-header d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="mb-1"><i class="fas fa-edit me-2 text-warning"></i>Edit Fee Structure</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= url('fees/structures') ?>">Fee Structures</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
    <a href="<?= url('fees/structures') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('fees/structures/'.$structure['id'].'/update') ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-info-circle me-2 text-primary"></i>Structure Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Structure Name *</label>
                            <input type="text" name="name" class="form-control" value="<?= e($structure['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic Year</label>
                            <select name="academic_year_id" class="form-select select2">
                                <option value="">None</option>
                                <?php foreach($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>" <?= $structure['academic_year_id']==$ay['id']?'selected':'' ?>><?= e($ay['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Course *</label>
                            <select name="course_id" class="form-select select2" required>
                                <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $structure['course_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Admission Type</label>
                            <select name="admission_type" class="form-select">
                                <?php foreach(['regular','lateral','management','scholarship'] as $t): ?>
                                <option value="<?= $t ?>" <?= $structure['admission_type']===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Grace Period (days)</label>
                            <input type="number" name="grace_period_days" class="form-control" value="<?= $structure['grace_period_days'] ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Late Fee/Day (₹)</label>
                            <input type="number" name="late_fee_per_day" class="form-control" value="<?= $structure['late_fee_per_day'] ?>" step="0.01">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="description" class="form-control" rows="2"><?= e($structure['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <span class="fw-semibold"><i class="fas fa-list me-2 text-success"></i>Fee Heads & Amounts</span>
                    <button type="button" class="btn btn-sm btn-success" id="btnAddRow"><i class="fas fa-plus me-1"></i>Add Row</button>
                </div>
                <div class="card-body p-0">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th>Fee Head</th><th>Amount (₹)</th><th>Due Date</th><th></th></tr></thead>
                        <tbody id="feeRowsBody">
                        <?php foreach($details as $i => $d): ?>
                        <tr id="frow-<?= $i ?>">
                            <td>
                                <select name="head_ids[]" class="form-select form-select-sm select2">
                                    <?php foreach($feeHeads as $h): ?>
                                    <option value="<?= $h['id'] ?>" <?= $d['fee_head_id']==$h['id']?'selected':'' ?>><?= e($h['head_name']) ?> (<?= e($h['head_code']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="head_amounts[]" class="form-control form-control-sm fee-amt" step="0.01" value="<?= $d['amount'] ?>"></td>
                            <td><input type="date" name="head_due_dates[]" class="form-control form-control-sm" value="<?= $d['due_date'] ?>"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger btn-rm-row" data-row="<?= $i ?>"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light fw-bold">
                                <td>Total</td>
                                <td id="totalAmtDisplay" class="text-success fs-5">₹<?= number_format($structure['total_amount'],2) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer text-end">
                    <a href="<?= url('fees/structures') ?>" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Structure</button>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top:80px">
                <div class="card-header bg-white fw-semibold py-3"><i class="fas fa-bookmark me-2 text-warning"></i>Add More Fee Heads</div>
                <div class="card-body p-2" style="max-height:400px;overflow-y:auto">
                    <?php foreach($feeHeads as $h): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start mb-1 btn-quick-add"
                        data-id="<?= $h['id'] ?>" data-name="<?= e($h['head_name']) ?>">
                        <i class="fas fa-plus me-1 text-success"></i><?= e($h['head_name']) ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
const feeHeads = <?= json_encode($feeHeads) ?>;
let rowIdx = <?= count($details) ?>;
function addFeeRow(headId = '') {
    rowIdx++;
    const opts = feeHeads.map(h => `<option value="${h.id}" ${h.id == headId ? 'selected' : ''}>${h.head_name} (${h.head_code})</option>`).join('');
    $('#feeRowsBody').append(`<tr id="frow-${rowIdx}">
        <td><select name="head_ids[]" class="form-select form-select-sm select2">${opts}</select></td>
        <td><input type="number" name="head_amounts[]" class="form-control form-control-sm fee-amt" step="0.01" value="" placeholder="0.00"></td>
        <td><input type="date" name="head_due_dates[]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger btn-rm-row" data-row="${rowIdx}"><i class="fas fa-times"></i></button></td>
    </tr>`);
    $('.select2').select2({width:'100%'});
}
function recalcTotal() {
    let t = 0; $('.fee-amt').each(function() { t += parseFloat($(this).val())||0; });
    $('#totalAmtDisplay').text('₹'+t.toFixed(2));
}
$('#btnAddRow').on('click', () => addFeeRow());
$(document).on('click', '.btn-quick-add', function() { addFeeRow($(this).data('id')); });
$(document).on('click', '.btn-rm-row', function() { $('#frow-'+$(this).data('row')).remove(); recalcTotal(); });
$(document).on('input', '.fee-amt', recalcTotal);
recalcTotal();
</script>
