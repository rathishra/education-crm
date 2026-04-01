<?php $pageTitle = 'Edit Admission — ' . e($admission['admission_number']); ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Admission</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admissions') ?>">Admissions</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admissions/' . $admission['id']) ?>"><?= e($admission['admission_number']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('admissions/' . $admission['id']) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php
// Helper: return old() value if set, otherwise fall back to admission field
function adm($field, array $admission, $default = '') {
    $o = old($field);
    return $o !== null && $o !== '' ? $o : ($admission[$field] ?? $default);
}
?>

<form method="POST" action="<?= url('admissions/' . $admission['id']) ?>">
    <?= csrfField() ?>

    <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-lg-8">

            <!-- 1. Personal Information -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-user me-2 text-indigo"></i>Personal Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="<?= e(adm('first_name', $admission)) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= e(adm('last_name', $admission)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= adm('gender', $admission) === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" value="<?= e(adm('phone', $admission)) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e(adm('email', $admission)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= e(adm('date_of_birth', $admission)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">Select</option>
                                <?php foreach (['general'=>'General','obc'=>'OBC','sc'=>'SC','st'=>'ST','ews'=>'EWS','minority'=>'Minority','other'=>'Other'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= adm('category', $admission) === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="<?= e(adm('nationality', $admission, 'Indian')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Address -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-map-marker-alt me-2 text-indigo"></i>Address</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address_line1" class="form-control" value="<?= e(adm('address_line1', $admission)) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="<?= e(adm('address_line2', $admission)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?= e(adm('city', $admission)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="<?= e(adm('state', $admission)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="<?= e(adm('pincode', $admission)) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Parent / Guardian -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-users me-2 text-indigo"></i>Parent / Guardian Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Father's Name</label>
                            <input type="text" name="father_name" class="form-control" value="<?= e(adm('father_name', $admission)) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father's Phone</label>
                            <input type="text" name="father_phone" class="form-control" value="<?= e(adm('father_phone', $admission)) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother's Name</label>
                            <input type="text" name="mother_name" class="form-control" value="<?= e(adm('mother_name', $admission)) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guardian Name</label>
                            <input type="text" name="guardian_name" class="form-control" value="<?= e(adm('guardian_name', $admission)) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guardian Phone</label>
                            <input type="text" name="guardian_phone" class="form-control" value="<?= e(adm('guardian_phone', $admission)) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Previous Education -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-graduation-cap me-2 text-indigo"></i>Previous Education</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="previous_qualification" class="form-control" value="<?= e(adm('previous_qualification', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Percentage / CGPA</label>
                            <input type="number" name="previous_percentage" step="0.01" min="0" max="100" class="form-control" value="<?= e(adm('previous_percentage', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year of Passing</label>
                            <input type="number" name="previous_year_of_passing" min="1990" max="<?= date('Y') ?>" class="form-control" value="<?= e(adm('previous_year_of_passing', $admission)) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Previous Institution</label>
                            <input type="text" name="previous_institution" class="form-control" value="<?= e(adm('previous_institution', $admission)) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Academic Preference -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-book me-2 text-indigo"></i>Academic Preference</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= adm('department_id', $admission) == $d['id'] ? 'selected' : '' ?>>
                                        <?= e($d['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <select name="course_id" id="courseSelect" class="form-select" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= adm('course_id', $admission) == $c['id'] ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Batch</label>
                            <select name="batch_id" id="batchSelect" class="form-select">
                                <option value="">Select Batch</option>
                                <?php foreach ($batches as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= adm('batch_id', $admission) == $b['id'] ? 'selected' : '' ?>>
                                        <?= e($b['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-select">
                                <option value="">Select Year</option>
                                <?php foreach ($academicYears as $ay): ?>
                                    <option value="<?= $ay['id'] ?>" <?= adm('academic_year_id', $admission) == $ay['id'] ? 'selected' : '' ?>>
                                        <?= e($ay['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Admission Type</label>
                            <select name="admission_type" class="form-select">
                                <?php foreach (['regular'=>'Regular','lateral'=>'Lateral Entry','transfer'=>'Transfer','management'=>'Management','nri'=>'NRI'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= adm('admission_type', $admission, 'regular') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quota</label>
                            <select name="quota" class="form-select">
                                <?php foreach (['general'=>'General','management'=>'Management','government'=>'Government','scholarship'=>'Scholarship','nri'=>'NRI'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= adm('quota', $admission, 'general') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Specialization</label>
                            <input type="text" name="specialization" class="form-control" value="<?= e(adm('specialization', $admission)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current Semester</label>
                            <select name="current_semester" class="form-select">
                                <?php for ($s = 1; $s <= 8; $s++): ?>
                                    <option value="<?= $s ?>" <?= (int)adm('current_semester', $admission, 1) === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Application Source</label>
                            <input type="text" name="application_source" class="form-control" value="<?= e(adm('application_source', $admission)) ?>">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-lg-4">

            <!-- Counselor -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-headset me-2 text-indigo"></i>Counselor</div>
                <div class="card-body">
                    <label class="form-label">Assigned Counselor</label>
                    <select name="counselor_id" class="form-select">
                        <option value="">Not Assigned</option>
                        <?php foreach ($counselors as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= adm('counselor_id', $admission) == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['first_name'] . ' ' . $c['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Fee Details -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-rupee-sign me-2 text-indigo"></i>Fee Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Total Fee (₹)</label>
                        <input type="number" name="total_fee" id="totalFee" step="0.01" min="0" class="form-control" value="<?= e(adm('total_fee', $admission, 0)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Amount (₹)</label>
                        <input type="number" name="discount_amount" id="discountAmt" step="0.01" min="0" class="form-control" value="<?= e(adm('discount_amount', $admission, 0)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Scholarship Amount (₹)</label>
                        <input type="number" name="scholarship_amount" id="scholarshipAmt" step="0.01" min="0" class="form-control" value="<?= e(adm('scholarship_amount', $admission, 0)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Final Fee (₹)</label>
                        <input type="number" id="finalFeeDisplay" class="form-control bg-light" readonly value="<?= e($admission['final_fee'] ?? 0) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Payment Required (₹)</label>
                        <input type="number" name="initial_payment" step="0.01" min="0" class="form-control" value="<?= e(adm('initial_payment', $admission, 0)) ?>">
                    </div>
                    <div>
                        <label class="form-label">Payment Due Date</label>
                        <input type="date" name="payment_due_date" class="form-control" value="<?= e(adm('payment_due_date', $admission)) ?>">
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-comment me-2 text-indigo"></i>Remarks</div>
                <div class="card-body">
                    <textarea name="remarks" class="form-control" rows="4"><?= e(adm('remarks', $admission)) ?></textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="card">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                    <a href="<?= url('admissions/' . $admission['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
function recalcFee() {
    const total       = parseFloat(document.getElementById('totalFee').value) || 0;
    const discount    = parseFloat(document.getElementById('discountAmt').value) || 0;
    const scholarship = parseFloat(document.getElementById('scholarshipAmt').value) || 0;
    document.getElementById('finalFeeDisplay').value = Math.max(0, total - discount - scholarship).toFixed(2);
}
['totalFee','discountAmt','scholarshipAmt'].forEach(id =>
    document.getElementById(id).addEventListener('input', recalcFee));
recalcFee();

const courseSelect = document.getElementById('courseSelect');
const batchSelect  = document.getElementById('batchSelect');
const currentBatch = <?= (int)($admission['batch_id'] ?? 0) ?>;

courseSelect.addEventListener('change', function () {
    batchSelect.innerHTML = '<option value="">Select Batch</option>';
    if (!this.value) return;
    fetch('<?= url('admissions/ajax/batches') ?>?course_id=' + this.value)
        .then(r => r.json())
        .then(data => {
            data.forEach(b => {
                const o = document.createElement('option');
                o.value = b.id; o.textContent = b.name;
                if (b.id == currentBatch) o.selected = true;
                batchSelect.appendChild(o);
            });
        });
});
</script>
