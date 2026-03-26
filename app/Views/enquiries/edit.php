<?php
$pageTitle  = 'Edit Enquiry - ' . e($enquiry['enquiry_number']);
$eId        = $enquiry['id'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-edit me-2 text-primary"></i>Edit Enquiry
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('enquiries') ?>">Enquiries</a></li>
                <li class="breadcrumb-item"><a href="<?= url('enquiries/' . $eId) ?>"><?= e($enquiry['enquiry_number']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('enquiries/' . $eId) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="<?= url('enquiries/' . $eId) ?>" id="enquiryEditForm" novalidate>
    <?= csrfField() ?>
    <div class="row g-4">

        <!-- ==================== MAIN COLUMN ==================== -->
        <div class="col-lg-8">

            <!-- Card 1: Personal Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2 text-primary"></i>Personal Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="first_name" required
                                   value="<?= e(old('first_name') ?: $enquiry['first_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name"
                                   value="<?= e(old('last_name') ?: ($enquiry['last_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone"
                                   id="phoneInput" required
                                   value="<?= e(old('phone') ?: $enquiry['phone']) ?>">
                            <div id="phoneWarning" class="text-warning small mt-1 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="phoneWarningText"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   id="emailInput"
                                   value="<?= e(old('email') ?: ($enquiry['email'] ?? '')) ?>">
                            <div id="emailWarning" class="text-warning small mt-1 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="emailWarningText"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <?php $selGender = old('gender') ?: ($enquiry['gender'] ?? ''); ?>
                            <select class="form-select" name="gender">
                                <option value="">-- Select --</option>
                                <option value="male"   <?= $selGender === 'male'   ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $selGender === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other"  <?= $selGender === 'other'  ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth"
                                   value="<?= e(old('date_of_birth') ?: ($enquiry['date_of_birth'] ?? '')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Academic Interest -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-graduation-cap me-2 text-primary"></i>Academic Interest
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Institution</label>
                            <?php $selInst = old('institution_id') ?: ($enquiry['institution_id'] ?? $institutionId); ?>
                            <select class="form-select" name="institution_id" id="institutionSelect">
                                <option value="">-- Select Institution --</option>
                                <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>" <?= $selInst == $inst['id'] ? 'selected' : '' ?>>
                                    <?= e($inst['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <?php $selDept = old('department_id') ?: ($enquiry['department_id'] ?? ''); ?>
                            <select class="form-select" name="department_id" id="departmentSelect">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= $selDept == $dept['id'] ? 'selected' : '' ?>>
                                    <?= e($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <?php $selCourse = old('course_interested_id') ?: ($enquiry['course_interested_id'] ?? ''); ?>
                            <select class="form-select" name="course_interested_id" id="courseSelect" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $selCourse == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Year</label>
                            <?php $selYear = old('academic_year') ?: ($enquiry['academic_year'] ?? ''); ?>
                            <select class="form-select" name="academic_year">
                                <option value="">-- Select Year --</option>
                                <?php foreach (['2023-24','2024-25','2025-26','2026-27'] as $yr): ?>
                                <option value="<?= $yr ?>" <?= $selYear === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Preferred Mode</label>
                            <?php
                            $selMode = old('preferred_mode') ?: ($enquiry['preferred_mode'] ?? 'offline');
                            $modes   = ['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid'];
                            ?>
                            <div class="btn-group w-100" role="group">
                                <?php foreach ($modes as $mval => $mlabel): ?>
                                <input type="radio" class="btn-check" name="preferred_mode"
                                       id="mode_<?= $mval ?>" value="<?= $mval ?>"
                                       <?= $selMode === $mval ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="mode_<?= $mval ?>">
                                    <?php if ($mval === 'online'): ?><i class="fas fa-wifi me-1"></i><?php endif; ?>
                                    <?php if ($mval === 'offline'): ?><i class="fas fa-building me-1"></i><?php endif; ?>
                                    <?php if ($mval === 'hybrid'): ?><i class="fas fa-blender me-1"></i><?php endif; ?>
                                    <?= $mlabel ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Lead Source -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bullhorn me-2 text-primary"></i>Lead Source
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <?php
                            // Match current source string back to source ID
                            $currentSourceName = $enquiry['source'] ?? '';
                            $selSourceId       = old('source_id') ?: '';
                            ?>
                            <select class="form-select" name="source_id">
                                <option value="">-- Select Source --</option>
                                <?php foreach ($sources as $src): ?>
                                <option value="<?= $src['id'] ?>"
                                    <?= ($selSourceId == $src['id'] || ($selSourceId === '' && $currentSourceName === $src['name'])) ? 'selected' : '' ?>>
                                    <?= e($src['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Campaign Name</label>
                            <input type="text" class="form-control" name="campaign_name"
                                   value="<?= e(old('campaign_name') ?: ($enquiry['campaign_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference Name</label>
                            <input type="text" class="form-control" name="reference_name"
                                   value="<?= e(old('reference_name') ?: ($enquiry['reference_name'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Counselor</label>
                            <?php $selCounselor = old('counselor_id') ?: ($enquiry['counselor_id'] ?? ''); ?>
                            <select class="form-select" name="counselor_id">
                                <option value="">-- Assign Counselor --</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $selCounselor == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Follow-up -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-2 text-primary"></i>Follow-up
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <?php $selStatus = old('status') ?: ($enquiry['status'] ?? 'new'); ?>
                            <select class="form-select" name="status">
                                <?php foreach (['new','contacted','interested','not_interested','closed'] as $sv): ?>
                                <option value="<?= $sv ?>" <?= $selStatus === $sv ? 'selected' : '' ?>>
                                    <?= ucwords(str_replace('_', ' ', $sv)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <?php
                            $selPriority = old('priority') ?: ($enquiry['priority'] ?? 'warm');
                            $priorities  = ['hot' => ['label' => 'Hot', 'icon' => 'fa-fire'], 'warm' => ['label' => 'Warm', 'icon' => 'fa-thermometer-half'], 'cold' => ['label' => 'Cold', 'icon' => 'fa-snowflake']];
                            ?>
                            <div class="btn-group w-100" role="group">
                                <?php foreach ($priorities as $pval => $pdata): ?>
                                <input type="radio" class="btn-check" name="priority"
                                       id="priority_<?= $pval ?>" value="<?= $pval ?>"
                                       <?= $selPriority === $pval ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="priority_<?= $pval ?>">
                                    <i class="fas <?= $pdata['icon'] ?> me-1"></i><?= $pdata['label'] ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" class="form-control" name="next_followup_date"
                                   value="<?= e(old('next_followup_date') ?: ($enquiry['next_followup_date'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Follow-up Mode</label>
                            <?php $selFMode = old('followup_mode') ?: ($enquiry['followup_mode'] ?? ''); ?>
                            <select class="form-select" name="followup_mode">
                                <option value="">-- Select Mode --</option>
                                <option value="call"     <?= $selFMode === 'call'     ? 'selected' : '' ?>>Call</option>
                                <option value="whatsapp" <?= $selFMode === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                                <option value="visit"    <?= $selFMode === 'visit'    ? 'selected' : '' ?>>Visit</option>
                                <option value="email"    <?= $selFMode === 'email'    ? 'selected' : '' ?>>Email</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 5: Requirements & Remarks -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-clipboard-list me-2 text-primary"></i>Requirements &amp; Remarks
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Requirements</label>
                            <div class="d-flex gap-4 flex-wrap">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="hostel_required" id="hostelSwitch" value="1"
                                           <?= (!empty(old('hostel_required')) || (!isset($_POST['hostel_required']) && !empty($enquiry['hostel_required']))) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="hostelSwitch">
                                        <i class="fas fa-bed me-1"></i>Hostel Required
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="transport_required" id="transportSwitch" value="1"
                                           <?= (!empty(old('transport_required')) || (!isset($_POST['transport_required']) && !empty($enquiry['transport_required']))) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="transportSwitch">
                                        <i class="fas fa-bus me-1"></i>Transport Required
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="scholarship_required" id="scholarshipSwitch" value="1"
                                           <?= (!empty(old('scholarship_required')) || (!isset($_POST['scholarship_required']) && !empty($enquiry['scholarship_required']))) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="scholarshipSwitch">
                                        <i class="fas fa-award me-1"></i>Scholarship Required
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message / Query</label>
                            <textarea class="form-control" name="message" rows="2"><?= e(old('message') ?: ($enquiry['message'] ?? '')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="4"><?= e(old('remarks') ?: ($enquiry['remarks'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- ==================== SIDEBAR ==================== -->
        <div class="col-lg-4">

            <!-- Duplicate Alert (hidden by default) -->
            <div id="duplicateAlert" class="card border-warning mb-4 d-none">
                <div class="card-header bg-warning bg-opacity-10 text-warning fw-semibold">
                    <i class="fas fa-exclamation-triangle me-2"></i>Possible Duplicate Found
                </div>
                <div class="card-body">
                    <p class="mb-1 text-muted small">
                        An existing enquiry matches the same <strong id="dupField"></strong>:
                    </p>
                    <p class="mb-3 fw-semibold" id="dupName"></p>
                    <a id="dupLink" href="#" target="_blank" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-external-link-alt me-1"></i>View Existing Enquiry
                    </a>
                </div>
            </div>

            <!-- Save Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Enquiry Info
                </div>
                <div class="card-body">
                    <div class="mb-2 small">
                        <span class="text-muted">Number:</span>
                        <code class="ms-2"><?= e($enquiry['enquiry_number']) ?></code>
                    </div>
                    <div class="mb-2 small">
                        <span class="text-muted">Created:</span>
                        <span class="ms-2"><?= formatDate($enquiry['created_at'], 'd M Y') ?></span>
                    </div>
                    <div class="mb-3 small">
                        <span class="text-muted">Current Status:</span>
                        <span class="ms-2 badge bg-secondary">
                            <?= ucwords(str_replace('_', ' ', $enquiry['status'] ?? 'new')) ?>
                        </span>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-1"></i>Update Enquiry
                        </button>
                        <a href="<?= url('enquiries/' . $eId) ?>" class="btn btn-light">Cancel</a>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-4 -->

    </div><!-- /row -->
</form>

<script>
(function () {
    const institutionSelect = document.getElementById('institutionSelect');
    const departmentSelect  = document.getElementById('departmentSelect');
    const courseSelect      = document.getElementById('courseSelect');
    const phoneInput        = document.getElementById('phoneInput');
    const emailInput        = document.getElementById('emailInput');
    const dupAlert          = document.getElementById('duplicateAlert');
    const dupField          = document.getElementById('dupField');
    const dupName           = document.getElementById('dupName');
    const dupLink           = document.getElementById('dupLink');
    const phoneWarning      = document.getElementById('phoneWarning');
    const phoneWarningText  = document.getElementById('phoneWarningText');
    const emailWarning      = document.getElementById('emailWarning');
    const emailWarningText  = document.getElementById('emailWarningText');
    const currentEnquiryId  = <?= (int)$eId ?>;

    function getInstitutionId() {
        return institutionSelect ? institutionSelect.value : '<?= (int)$institutionId ?>';
    }

    function showDuplicate(data, fieldLabel) {
        // Do not warn about the current enquiry
        if (data.id === currentEnquiryId) return;
        dupField.textContent = fieldLabel;
        dupName.textContent  = data.name + ' (' + data.enquiry_number + ')';
        dupLink.href         = '<?= url('enquiries') ?>/' + data.id;
        dupAlert.classList.remove('d-none');
    }

    function hideDuplicate() {
        dupAlert.classList.add('d-none');
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', function () {
            const phone = this.value.trim();
            if (!phone) return;
            const params = { phone: phone, institution_id: getInstitutionId(), exclude_id: currentEnquiryId };
            fetch('<?= url('enquiries/check-duplicate') ?>?' + new URLSearchParams(params).toString())
                .then(r => r.json())
                .then(data => {
                    if (data.duplicate && data.id !== currentEnquiryId) {
                        phoneWarningText.textContent = 'Phone already used by ' + data.name + ' (' + data.enquiry_number + ')';
                        phoneWarning.classList.remove('d-none');
                        showDuplicate(data, 'phone');
                    } else {
                        phoneWarning.classList.add('d-none');
                    }
                })
                .catch(() => {});
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            const email = this.value.trim();
            if (!email) return;
            const params = { email: email, institution_id: getInstitutionId(), exclude_id: currentEnquiryId };
            fetch('<?= url('enquiries/check-duplicate') ?>?' + new URLSearchParams(params).toString())
                .then(r => r.json())
                .then(data => {
                    if (data.duplicate && data.id !== currentEnquiryId) {
                        emailWarningText.textContent = 'Email already used by ' + data.name + ' (' + data.enquiry_number + ')';
                        emailWarning.classList.remove('d-none');
                        showDuplicate(data, 'email');
                    } else {
                        emailWarning.classList.add('d-none');
                    }
                })
                .catch(() => {});
        });
    }

    if (institutionSelect) {
        institutionSelect.addEventListener('change', function () {
            const instId = this.value;
            departmentSelect.innerHTML = '<option value="">-- Select Department --</option>';
            courseSelect.innerHTML     = '<option value="">-- Select Course --</option>';
            if (!instId) return;

            fetch('<?= url('enquiries/ajax/departments') ?>?institution_id=' + instId)
                .then(r => r.json())
                .then(depts => {
                    depts.forEach(d => {
                        const opt    = document.createElement('option');
                        opt.value    = d.id;
                        opt.textContent = d.name;
                        departmentSelect.appendChild(opt);
                    });
                })
                .catch(() => {});

            fetch('<?= url('enquiries/ajax/courses') ?>?institution_id=' + instId)
                .then(r => r.json())
                .then(courses => {
                    courses.forEach(c => {
                        const opt    = document.createElement('option');
                        opt.value    = c.id;
                        opt.textContent = c.name;
                        courseSelect.appendChild(opt);
                    });
                })
                .catch(() => {});
        });
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', function () {
            const deptId = this.value;
            courseSelect.innerHTML = '<option value="">-- Select Course --</option>';
            if (!deptId) return;

            fetch('<?= url('enquiries/ajax/courses') ?>?department_id=' + deptId)
                .then(r => r.json())
                .then(courses => {
                    courses.forEach(c => {
                        const opt    = document.createElement('option');
                        opt.value    = c.id;
                        opt.textContent = c.name;
                        courseSelect.appendChild(opt);
                    });
                })
                .catch(() => {});
        });
    }
})();
</script>
