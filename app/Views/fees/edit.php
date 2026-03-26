<?php $pageTitle = 'Edit Fee Structure'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Edit Fee Structure</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('fees') ?>">Fee Structures</a></li>
                <li class="breadcrumb-item active"><?= e($feeStructure['name']) ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('fees/' . $feeStructure['id']) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('fees/' . $feeStructure['id']) ?>">
    <?= csrfField() ?>
    <input type="hidden" name="_method" value="PUT">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Basic Info -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Fee Structure Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required">Structure Name</label>
                            <input type="text" class="form-control" name="name" value="<?= e(old('name', $feeStructure['name'])) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Course</label>
                            <select class="form-select select2" name="course_id" id="feesCourseSelect" required>
                                <option value="">— Select Course —</option>
                                <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>"
                                    <?= old('course_id', $feeStructure['course_id']) == $course['id'] ? 'selected' : '' ?>>
                                    <?= e($course['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batch <small class="text-muted">(optional)</small></label>
                            <select class="form-select" name="batch_id" id="feesBatchSelect">
                                <option value="">— All Batches —</option>
                                <?php if (!empty($feeStructure['batch_id'])): ?>
                                <option value="<?= $feeStructure['batch_id'] ?>" selected><?= e($feeStructure['batch_name'] ?? '') ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year_id">
                                <option value="">— Select Academic Year —</option>
                                <?php foreach ($academicYears as $ay): ?>
                                <option value="<?= $ay['id'] ?>" <?= old('academic_year_id', $feeStructure['academic_year_id'] ?? '') == $ay['id'] ? 'selected' : '' ?>>
                                    <?= e($ay['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"><?= e(old('description', $feeStructure['description'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Components -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i>Fee Components</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addComponentBtn">
                        <i class="fas fa-plus me-1"></i>Add Component
                    </button>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0" id="componentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Component Name</th>
                                <th style="width:160px">Amount (₹)</th>
                                <th class="text-center" style="width:100px">Optional</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="componentRows">
                        <?php
                        $existingComponents = $feeStructure['components'] ?? [];
                        if (empty($existingComponents)) $existingComponents = [['component_name' => '', 'component_amount' => '', 'is_optional' => 0]];
                        foreach ($existingComponents as $idx => $comp):
                        ?>
                            <tr class="component-row">
                                <?php if (!empty($comp['id'])): ?>
                                <input type="hidden" name="components[<?= $idx ?>][id]" value="<?= $comp['id'] ?>">
                                <?php endif; ?>
                                <td><input type="text" class="form-control form-control-sm" name="components[<?= $idx ?>][name]" value="<?= e($comp['component_name'] ?? '') ?>" required></td>
                                <td><input type="number" class="form-control form-control-sm component-amount" name="components[<?= $idx ?>][amount]" value="<?= e($comp['component_amount'] ?? '') ?>" min="0" step="0.01" required></td>
                                <td class="text-center"><input type="checkbox" class="form-check-input" name="components[<?= $idx ?>][is_optional]" value="1" <?= !empty($comp['is_optional']) ? 'checked' : '' ?>></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row" <?= count($existingComponents) === 1 ? 'disabled' : '' ?>><i class="fas fa-times"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="2" class="text-end fw-bold">Total:</td>
                                <td colspan="2"><span id="totalAmount" class="fw-bold text-success">₹0.00</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><i class="fas fa-toggle-on me-2"></i>Status</div>
                <div class="card-body">
                    <select class="form-select" name="status">
                        <option value="active" <?= old('status', $feeStructure['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= old('status', $feeStructure['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Update Fee Structure</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    let rowIndex = <?= count($feeStructure['components'] ?? [1]) ?>;

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.component-amount').forEach(function (inp) {
            total += parseFloat(inp.value) || 0;
        });
        document.getElementById('totalAmount').textContent = '₹' + total.toLocaleString('en-IN', {minimumFractionDigits: 2});
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.component-row');
        rows.forEach(function (row) {
            row.querySelector('.remove-row').disabled = rows.length === 1;
        });
    }

    document.getElementById('addComponentBtn').addEventListener('click', function () {
        const tbody = document.getElementById('componentRows');
        const tr = document.createElement('tr');
        tr.className = 'component-row';
        tr.innerHTML = `
            <td><input type="text" class="form-control form-control-sm" name="components[${rowIndex}][name]" placeholder="Component name" required></td>
            <td><input type="number" class="form-control form-control-sm component-amount" name="components[${rowIndex}][amount]" placeholder="0.00" min="0" step="0.01" required></td>
            <td class="text-center"><input type="checkbox" class="form-check-input" name="components[${rowIndex}][is_optional]" value="1"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(tr);
        tr.querySelector('.component-amount').addEventListener('input', updateTotal);
        rowIndex++;
        updateRemoveButtons();
    });

    document.getElementById('componentRows').addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
            updateTotal();
            updateRemoveButtons();
        }
    });

    document.getElementById('componentRows').addEventListener('input', function (e) {
        if (e.target.classList.contains('component-amount')) updateTotal();
    });

    updateTotal();
})();
</script>
