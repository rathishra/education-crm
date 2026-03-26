<?php $pageTitle = 'New Enquiry'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-plus-circle me-2 text-primary"></i>New Enquiry</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('enquiries') ?>">Enquiries</a></li>
                <li class="breadcrumb-item active">New</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('enquiries') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="<?= url('enquiries') ?>" id="enquiryForm" novalidate>
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
                            <input type="text" class="form-control" name="first_name"
                                   value="<?= e(old('first_name')) ?>" required
                                   placeholder="e.g. Rahul">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name"
                                   value="<?= e(old('last_name')) ?>" placeholder="e.g. Sharma">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone"
                                   id="phoneInput"
                                   value="<?= e(old('phone')) ?>"
                                   placeholder="10-digit mobile" required>
                            <div id="phoneWarning" class="text-warning small mt-1 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="phoneWarningText"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   id="emailInput"
                                   value="<?= e(old('email')) ?>"
                                   placeholder="example@email.com">
                            <div id="emailWarning" class="text-warning small mt-1 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="emailWarningText"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">-- Select --</option>
                                <option value="male"   <?= old('gender') === 'male'   ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other"  <?= old('gender') === 'other'  ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth"
                                   value="<?= e(old('date_of_birth')) ?>">
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
                            <select class="form-select" name="institution_id" id="institutionSelect">
                                <option value="">-- Select Institution --</option>
                                <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>"
                                    <?= (old('institution_id') == $inst['id'] || (empty(old('institution_id')) && $inst['id'] == $institutionId)) ? 'selected' : '' ?>>
                                    <?= e($inst['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id" id="departmentSelect">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"
                                    <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= e($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <select class="form-select" name="course_interested_id" id="courseSelect" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= old('course_interested_id') == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year">
                                <option value="">-- Select Year --</option>
                                <?php foreach (['2023-24','2024-25','2025-26','2026-27'] as $yr): ?>
                                <option value="<?= $yr ?>" <?= old('academic_year') === $yr ? 'selected' : '' ?>>
                                    <?= $yr ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Preferred Mode</label>
                            <div class="btn-group w-100" role="group" aria-label="Preferred mode">
                                <?php
                                $modes = ['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid'];
                                $selMode = old('preferred_mode', 'offline');
                                foreach ($modes as $mval => $mlabel):
                                ?>
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
                            <select class="form-select" name="source_id">
                                <option value="">-- Select Source --</option>
                                <?php foreach ($sources as $src): ?>
                                <option value="<?= $src['id'] ?>"
                                    <?= old('source_id') == $src['id'] ? 'selected' : '' ?>>
                                    <?= e($src['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Campaign Name</label>
                            <input type="text" class="form-control" name="campaign_name"
                                   value="<?= e(old('campaign_name')) ?>"
                                   placeholder="e.g. Diwali Admission Drive">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference Name</label>
                            <input type="text" class="form-control" name="reference_name"
                                   value="<?= e(old('reference_name')) ?>"
                                   placeholder="Referred by whom?">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Counselor</label>
                            <select class="form-select" name="counselor_id">
                                <option value="">-- Assign Counselor --</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= old('counselor_id') == $c['id'] ? 'selected' : '' ?>>
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
                            <select class="form-select" name="status">
                                <?php
                                $statuses  = ['new','contacted','interested','not_interested','closed'];
                                $selStatus = old('status', 'new');
                                foreach ($statuses as $sv):
                                ?>
                                <option value="<?= $sv ?>" <?= $selStatus === $sv ? 'selected' : '' ?>>
                                    <?= ucwords(str_replace('_', ' ', $sv)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <div class="btn-group w-100" role="group">
                                <?php
                                $priorities = ['hot' => ['label' => 'Hot', 'icon' => 'fa-fire'], 'warm' => ['label' => 'Warm', 'icon' => 'fa-thermometer-half'], 'cold' => ['label' => 'Cold', 'icon' => 'fa-snowflake']];
                                $selPriority = old('priority', 'warm');
                                foreach ($priorities as $pval => $pdata):
                                ?>
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
                                   value="<?= e(old('next_followup_date')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Follow-up Mode</label>
                            <select class="form-select" name="followup_mode">
                                <option value="">-- Select Mode --</option>
                                <option value="call"      <?= old('followup_mode') === 'call'      ? 'selected' : '' ?>>Call</option>
                                <option value="whatsapp"  <?= old('followup_mode') === 'whatsapp'  ? 'selected' : '' ?>>WhatsApp</option>
                                <option value="visit"     <?= old('followup_mode') === 'visit'     ? 'selected' : '' ?>>Visit</option>
                                <option value="email"     <?= old('followup_mode') === 'email'     ? 'selected' : '' ?>>Email</option>
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
                                           <?= old('hostel_required') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="hostelSwitch">
                                        <i class="fas fa-bed me-1"></i>Hostel Required
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="transport_required" id="transportSwitch" value="1"
                                           <?= old('transport_required') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="transportSwitch">
                                        <i class="fas fa-bus me-1"></i>Transport Required
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="scholarship_required" id="scholarshipSwitch" value="1"
                                           <?= old('scholarship_required') ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="scholarshipSwitch">
                                        <i class="fas fa-award me-1"></i>Scholarship Required
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Message / Query</label>
                            <textarea class="form-control" name="message" rows="2"
                                      placeholder="What would the enquirer like to know?"><?= e(old('message')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea class="form-control" name="remarks" rows="4"
                                      placeholder="Internal notes, counselor observations..."><?= e(old('remarks')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- ==================== SIDEBAR ==================== -->
        <div class="col-lg-4">
            <div class="enquiry-sidebar-sticky">

                <!-- Duplicate Alert (hidden by default) -->
                <div id="duplicateAlert" class="card border-warning mb-3 d-none">
                    <div class="card-header bg-warning bg-opacity-10 text-warning fw-semibold py-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>Possible Duplicate
                    </div>
                    <div class="card-body py-3">
                        <p class="mb-1 text-muted small">Matches existing enquiry by <strong id="dupField"></strong>:</p>
                        <p class="mb-2 fw-semibold" id="dupName"></p>
                        <a id="dupLink" href="#" target="_blank" class="btn btn-sm btn-outline-warning w-100 mb-2">
                            <i class="fas fa-external-link-alt me-1"></i>View Existing
                        </a>
                        <p class="text-muted small mb-0">You can still save this as a new record.</p>
                    </div>
                </div>

                <!-- Save Card -->
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <i class="fas fa-paper-plane me-2 text-primary"></i><strong>Save Enquiry</strong>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2" style="padding:.65rem;">
                            <i class="fas fa-save me-2"></i>Submit Enquiry
                        </button>
                        <a href="<?= url('enquiries') ?>" class="btn btn-outline-secondary w-100" style="padding:.6rem;">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card border-0 bg-light">
                    <div class="card-body py-3">
                        <p class="text-muted small fw-semibold mb-2">
                            <i class="fas fa-info-circle me-1 text-primary"></i>What happens on save:
                        </p>
                        <ul class="text-muted small ps-3 mb-0" style="line-height:1.8">
                            <li>Unique enquiry number auto-generated</li>
                            <li>Status set to <strong class="text-primary">New</strong></li>
                            <li>Duplicate phone/email check performed</li>
                            <li>Can be converted to Lead or Admission</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div><!-- /col-lg-4 -->

    </div><!-- /row -->
</form>

<!-- Sticky bottom action bar (visible on all screen sizes while scrolling) -->
<div class="enquiry-action-bar">
    <div class="d-flex align-items-center gap-2 text-muted small">
        <i class="fas fa-user-plus text-primary"></i>
        <span>New Enquiry &mdash; fill required fields (<span class="text-danger">*</span>) then submit</span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('enquiries') ?>" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <button type="submit" form="enquiryForm" class="btn btn-primary btn-sm px-4">
            <i class="fas fa-save me-1"></i>Submit Enquiry
        </button>
    </div>
</div>

<style>
/* Sticky sidebar — floats beside the form as user scrolls */
.enquiry-sidebar-sticky {
    position: sticky;
    top: 80px; /* clears the fixed topnav */
}

/* Sticky bottom action bar */
.enquiry-action-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1040;
    background: #fff;
    border-top: 2px solid var(--brand-primary, #4f46e5);
    padding: .75rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 -4px 16px rgba(79,70,229,.10);
}

/* Push page content up so it isn't hidden behind the bar */
form#enquiryForm {
    padding-bottom: 4.5rem;
}

@media (max-width: 991.98px) {
    .enquiry-sidebar-sticky {
        position: static; /* disable sticky on mobile — flows naturally */
    }
    .enquiry-action-bar {
        padding: .6rem 1rem;
    }
    .enquiry-action-bar .d-flex.align-items-center {
        display: none !important; /* hide hint text on small screens */
    }
}
</style>

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

    function getInstitutionId() {
        return institutionSelect ? institutionSelect.value : '<?= (int)$institutionId ?>';
    }

    function showDuplicate(data, fieldLabel) {
        dupField.textContent = fieldLabel;
        dupName.textContent  = data.name + ' (' + data.enquiry_number + ')';
        dupLink.href         = '<?= url('enquiries') ?>/' + data.id;
        dupAlert.classList.remove('d-none');
    }

    function hideDuplicate() {
        dupAlert.classList.add('d-none');
    }

    function checkDup(params) {
        const qs = new URLSearchParams(params).toString();
        fetch('<?= url('enquiries/check-duplicate') ?>?' + qs)
            .then(r => r.json())
            .then(data => {
                if (data.duplicate) {
                    showDuplicate(data, data.field);
                } else {
                    hideDuplicate();
                }
            })
            .catch(() => {});
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', function () {
            const phone = this.value.trim();
            if (!phone) return;
            const params = { phone: phone, institution_id: getInstitutionId() };
            fetch('<?= url('enquiries/check-duplicate') ?>?' + new URLSearchParams(params).toString())
                .then(r => r.json())
                .then(data => {
                    if (data.duplicate) {
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
            const params = { email: email, institution_id: getInstitutionId() };
            fetch('<?= url('enquiries/check-duplicate') ?>?' + new URLSearchParams(params).toString())
                .then(r => r.json())
                .then(data => {
                    if (data.duplicate) {
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
            // Reset department and course
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
