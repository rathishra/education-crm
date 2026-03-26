<?php $pageTitle = 'New Admission Application'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-graduate me-2 text-primary"></i>New Admission Application</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admissions') ?>">Admissions</a></li>
                <li class="breadcrumb-item active">New Application</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<!-- Duplicate warning banner -->
<div id="dupBanner" class="alert alert-warning d-none mb-3">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <span id="dupMsg"></span>
    <a id="dupLink" href="#" class="ms-2 fw-semibold">View existing</a>
</div>

<form method="POST" action="<?= url('admissions') ?>" id="admissionForm">
    <?= csrfField() ?>
    <?php if ($leadId): ?>
        <input type="hidden" name="lead_id" value="<?= (int)$leadId ?>">
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT COLUMN -->
        <div class="col-lg-8">

            <!-- 1. Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2 text-indigo"></i>Personal Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="<?= e(old('first_name') ?: ($prefill['first_name'] ?? '')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" value="<?= e(old('last_name') ?: ($prefill['last_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= (old('gender') ?: ($prefill['gender'] ?? '')) === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phoneInput" class="form-control" value="<?= e(old('phone') ?: ($prefill['phone'] ?? '')) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="emailInput" class="form-control" value="<?= e(old('email') ?: ($prefill['email'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= e(old('date_of_birth') ?: ($prefill['date_of_birth'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">Select</option>
                                <?php foreach (['general'=>'General','obc'=>'OBC','sc'=>'SC','st'=>'ST','ews'=>'EWS','minority'=>'Minority','other'=>'Other'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= old('category') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="<?= e(old('nationality') ?: 'Indian') ?>">
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
                            <input type="text" name="address_line1" class="form-control" value="<?= e(old('address_line1') ?: ($prefill['address_line1'] ?? '')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address_line2" class="form-control" value="<?= e(old('address_line2') ?: ($prefill['address_line2'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?= e(old('city') ?: ($prefill['city'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="<?= e(old('state') ?: ($prefill['state'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="<?= e(old('pincode') ?: ($prefill['pincode'] ?? '')) ?>">
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
                            <input type="text" name="father_name" class="form-control" value="<?= e(old('father_name')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Father's Phone</label>
                            <input type="text" name="father_phone" class="form-control" value="<?= e(old('father_phone')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mother's Name</label>
                            <input type="text" name="mother_name" class="form-control" value="<?= e(old('mother_name')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guardian Name</label>
                            <input type="text" name="guardian_name" class="form-control" value="<?= e(old('guardian_name') ?: ($prefill['guardian_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Guardian Phone</label>
                            <input type="text" name="guardian_phone" class="form-control" value="<?= e(old('guardian_phone') ?: ($prefill['guardian_phone'] ?? '')) ?>">
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
                            <input type="text" name="previous_qualification" class="form-control" placeholder="e.g. 12th, Diploma, Degree" value="<?= e(old('previous_qualification') ?: ($prefill['previous_qualification'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Percentage / CGPA</label>
                            <input type="number" name="previous_percentage" step="0.01" min="0" max="100" class="form-control" value="<?= e(old('previous_percentage') ?: ($prefill['previous_percentage'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year of Passing</label>
                            <input type="number" name="previous_year_of_passing" min="1990" max="<?= date('Y') ?>" class="form-control" value="<?= e(old('previous_year_of_passing')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Previous Institution</label>
                            <input type="text" name="previous_institution" class="form-control" value="<?= e(old('previous_institution')) ?>">
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
                            <select name="department_id" id="deptSelect" class="form-select">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= (old('department_id') ?: ($prefill['department_id'] ?? '')) == $d['id'] ? 'selected' : '' ?>>
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
                                    <option value="<?= $c['id'] ?>" <?= (old('course_id') ?: ($prefill['course_id'] ?? '')) == $c['id'] ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Batch</label>
                            <select name="batch_id" id="batchSelect" class="form-select">
                                <option value="">Select Batch</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-select">
                                <option value="">Select Year</option>
                                <?php foreach ($academicYears as $ay): ?>
                                    <option value="<?= $ay['id'] ?>" <?= old('academic_year_id') == $ay['id'] ? 'selected' : '' ?>><?= e($ay['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Admission Type</label>
                            <select name="admission_type" class="form-select">
                                <?php foreach (['regular'=>'Regular','lateral'=>'Lateral Entry','transfer'=>'Transfer','management'=>'Management','nri'=>'NRI'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= old('admission_type') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quota</label>
                            <select name="quota" class="form-select">
                                <?php foreach (['general'=>'General','management'=>'Management','government'=>'Government','scholarship'=>'Scholarship','nri'=>'NRI'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= old('quota') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Specialization</label>
                            <input type="text" name="specialization" class="form-control" value="<?= e(old('specialization')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Current Semester</label>
                            <select name="current_semester" class="form-select">
                                <?php for ($s = 1; $s <= 8; $s++): ?>
                                    <option value="<?= $s ?>" <?= (int)old('current_semester', 1) === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Application Source</label>
                            <input type="text" name="application_source" class="form-control" placeholder="Website, Walk-in…" value="<?= e(old('application_source') ?: ($prefill['application_source'] ?? '')) ?>">
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
                            <option value="<?= $c['id'] ?>" <?= (old('counselor_id') ?: ($prefill['counselor_id'] ?? '')) == $c['id'] ? 'selected' : '' ?>>
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
                        <input type="number" name="total_fee" id="totalFee" step="0.01" min="0" class="form-control" value="<?= e(old('total_fee', 0)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Amount (₹)</label>
                        <input type="number" name="discount_amount" id="discountAmt" step="0.01" min="0" class="form-control" value="<?= e(old('discount_amount', 0)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Scholarship Amount (₹)</label>
                        <input type="number" name="scholarship_amount" id="scholarshipAmt" step="0.01" min="0" class="form-control" value="<?= e(old('scholarship_amount', 0)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Final Fee (₹)</label>
                        <input type="number" id="finalFeeDisplay" class="form-control bg-light" readonly value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Payment Required (₹)</label>
                        <input type="number" name="initial_payment" step="0.01" min="0" class="form-control" value="<?= e(old('initial_payment', 0)) ?>">
                    </div>
                    <div>
                        <label class="form-label">Payment Due Date</label>
                        <input type="date" name="payment_due_date" class="form-control" value="<?= e(old('payment_due_date')) ?>">
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-comment me-2 text-indigo"></i>Remarks</div>
                <div class="card-body">
                    <textarea name="remarks" class="form-control" rows="4" placeholder="Any notes about this application…"><?= e(old('remarks')) ?></textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="card">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Submit Application
                    </button>
                    <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
// ── Fee calculator ────────────────────────────────────────────────────
function recalcFee() {
    const total       = parseFloat(document.getElementById('totalFee').value) || 0;
    const discount    = parseFloat(document.getElementById('discountAmt').value) || 0;
    const scholarship = parseFloat(document.getElementById('scholarshipAmt').value) || 0;
    document.getElementById('finalFeeDisplay').value = Math.max(0, total - discount - scholarship).toFixed(2);
}
['totalFee','discountAmt','scholarshipAmt'].forEach(id =>
    document.getElementById(id).addEventListener('input', recalcFee));
recalcFee();

// ── Batch cascade ─────────────────────────────────────────────────────
const courseSelect = document.getElementById('courseSelect');
const batchSelect  = document.getElementById('batchSelect');

function loadBatches(courseId, selectedId) {
    batchSelect.innerHTML = '<option value="">Select Batch</option>';
    if (!courseId) return;
    fetch('<?= url('admissions/ajax/batches') ?>?course_id=' + courseId)
        .then(r => r.json())
        .then(data => {
            data.forEach(b => {
                const o = document.createElement('option');
                o.value = b.id; o.textContent = b.name;
                if (selectedId && b.id == selectedId) o.selected = true;
                batchSelect.appendChild(o);
            });
        });
}

courseSelect.addEventListener('change', () => loadBatches(courseSelect.value));
<?php if (!empty($prefill['course_id'])): ?>
window.addEventListener('DOMContentLoaded', () => loadBatches(<?= (int)$prefill['course_id'] ?>));
<?php endif; ?>

// ── Duplicate check ───────────────────────────────────────────────────
function checkDuplicate() {
    const phone = document.getElementById('phoneInput').value.trim();
    const email = document.getElementById('emailInput').value.trim();
    if (!phone && !email) return;

    fetch('<?= url('admissions/check-duplicate') ?>?phone=' + encodeURIComponent(phone) + '&email=' + encodeURIComponent(email))
        .then(r => r.json())
        .then(data => {
            const banner = document.getElementById('dupBanner');
            if (data.duplicate) {
                document.getElementById('dupMsg').textContent =
                    'A similar application exists for ' + data.name + ' (' + data.admission_number + ').';
                document.getElementById('dupLink').href = '<?= url('admissions/') ?>' + data.id;
                banner.classList.remove('d-none');
            } else {
                banner.classList.add('d-none');
            }
        });
}
document.getElementById('phoneInput').addEventListener('blur', checkDuplicate);
document.getElementById('emailInput').addEventListener('blur', checkDuplicate);
</script>
