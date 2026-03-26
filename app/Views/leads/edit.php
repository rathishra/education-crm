<?php
$pageTitle = 'Edit Lead: ' . e($lead['lead_number']);
$eId       = $lead['id'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-edit me-2 text-primary"></i>Edit Lead
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('leads') ?>">Leads</a></li>
                <li class="breadcrumb-item"><a href="<?= url('leads/' . $eId) ?>"><?= e($lead['lead_number']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('leads/' . $eId) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="<?= url('leads/' . $eId) ?>" id="leadForm" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="_method" value="PUT">

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
                                   value="<?= e(old('first_name') ?: $lead['first_name']) ?>"
                                   placeholder="e.g. Rahul">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name"
                                   value="<?= e(old('last_name') ?: ($lead['last_name'] ?? '')) ?>"
                                   placeholder="e.g. Sharma">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone"
                                   id="phoneInput" required
                                   value="<?= e(old('phone') ?: $lead['phone']) ?>"
                                   placeholder="10-digit mobile">
                            <div id="phoneWarning" class="text-warning small mt-1 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="phoneWarningText"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alternate Phone</label>
                            <input type="text" class="form-control" name="alternate_phone"
                                   value="<?= e(old('alternate_phone') ?: ($lead['alternate_phone'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   id="emailInput"
                                   value="<?= e(old('email') ?: ($lead['email'] ?? '')) ?>"
                                   placeholder="example@email.com">
                            <div id="emailWarning" class="text-warning small mt-1 d-none">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="emailWarningText"></span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Gender</label>
                            <?php $selGender = old('gender') ?: ($lead['gender'] ?? ''); ?>
                            <select class="form-select" name="gender">
                                <option value="">-- Select --</option>
                                <option value="male"   <?= $selGender === 'male'   ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $selGender === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other"  <?= $selGender === 'other'  ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth"
                                   value="<?= e(old('date_of_birth') ?: ($lead['date_of_birth'] ?? '')) ?>">
                        </div>
                    </div>
                </div>
            </div><!-- /Card 1 -->

            <!-- Card 2: Academic Interest -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-graduation-cap me-2 text-primary"></i>Academic Interest
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Institution</label>
                            <?php $selInst = old('institution_id') ?: ($lead['institution_id'] ?? ($institutionId ?? '')); ?>
                            <select class="form-select" name="institution_id" id="institutionSelect">
                                <option value="">-- Select Institution --</option>
                                <?php foreach ($institutions as $inst): ?>
                                <option value="<?= $inst['id'] ?>"
                                    <?= $selInst == $inst['id'] ? 'selected' : '' ?>>
                                    <?= e($inst['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <?php $selDept = old('department_id') ?: ($lead['department_id'] ?? ''); ?>
                            <select class="form-select" name="department_id" id="departmentSelect">
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"
                                    <?= $selDept == $dept['id'] ? 'selected' : '' ?>>
                                    <?= e($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <?php $selCourse = old('course_interested_id') ?: ($lead['course_interested_id'] ?? ''); ?>
                            <select class="form-select" name="course_interested_id" id="courseSelect" required>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= $selCourse == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Year</label>
                            <?php $selYear = old('academic_year') ?: ($lead['academic_year'] ?? ''); ?>
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
                            $selMode = old('preferred_mode') ?: ($lead['preferred_mode'] ?? 'offline');
                            $modes   = ['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid'];
                            ?>
                            <div class="btn-group w-100" role="group" aria-label="Preferred mode">
                                <?php foreach ($modes as $mval => $mlabel): ?>
                                <input type="radio" class="btn-check" name="preferred_mode"
                                       id="mode_<?= $mval ?>" value="<?= $mval ?>"
                                       <?= $selMode === $mval ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="mode_<?= $mval ?>">
                                    <?php if ($mval === 'online'):  ?><i class="fas fa-wifi me-1"></i><?php endif; ?>
                                    <?php if ($mval === 'offline'): ?><i class="fas fa-building me-1"></i><?php endif; ?>
                                    <?php if ($mval === 'hybrid'):  ?><i class="fas fa-blender me-1"></i><?php endif; ?>
                                    <?= $mlabel ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /Card 2 -->

            <!-- Card 3: Lead Source & Campaign -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bullhorn me-2 text-primary"></i>Lead Source &amp; Campaign
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Source</label>
                            <?php $selSource = old('lead_source_id') ?: ($lead['lead_source_id'] ?? ''); ?>
                            <select class="form-select" name="lead_source_id">
                                <option value="">-- Select Source --</option>
                                <?php foreach ($sources as $src): ?>
                                <option value="<?= $src['id'] ?>"
                                    <?= $selSource == $src['id'] ? 'selected' : '' ?>>
                                    <?= e($src['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Campaign Name</label>
                            <input type="text" class="form-control" name="campaign_name"
                                   value="<?= e(old('campaign_name') ?: ($lead['campaign_name'] ?? '')) ?>"
                                   placeholder="e.g. Diwali Admission Drive">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference Name</label>
                            <input type="text" class="form-control" name="reference_name"
                                   value="<?= e(old('reference_name') ?: ($lead['reference_name'] ?? '')) ?>"
                                   placeholder="Referred by whom?">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Counselor</label>
                            <?php $selCounselor = old('counselor_id') ?: ($lead['counselor_id'] ?? ''); ?>
                            <select class="form-select" name="counselor_id">
                                <option value="">-- Assign Counselor --</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= $selCounselor == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div><!-- /Card 3 -->

            <!-- Card 4: Admission Interest -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-check me-2 text-primary"></i>Admission Interest
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Expected Join Date</label>
                            <input type="date" class="form-control" name="expected_join_date"
                                   value="<?= e(old('expected_join_date') ?: ($lead['expected_join_date'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Budget (INR)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                <input type="number" class="form-control" name="budget"
                                       value="<?= e(old('budget') ?: ($lead['budget'] ?? '')) ?>"
                                       min="0" step="500" placeholder="e.g. 50000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lead Score (0–100)</label>
                            <input type="number" class="form-control" name="lead_score"
                                   value="<?= e(old('lead_score') ?: ($lead['lead_score'] ?? 0)) ?>"
                                   min="0" max="100">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <?php
                            $selPriority = old('priority', $lead['priority'] ?? 'warm');
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
                    </div>
                </div>
            </div><!-- /Card 4 -->

            <!-- Card 5: Follow-up Schedule -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-phone-alt me-2 text-primary"></i>Follow-up Schedule
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <?php $selStatus = old('lead_status_id') ?: ($lead['lead_status_id'] ?? ''); ?>
                            <select class="form-select" name="lead_status_id">
                                <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st['id'] ?>"
                                    <?= $selStatus == $st['id'] ? 'selected' : '' ?>>
                                    <?= e($st['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned To</label>
                            <?php $selAssigned = old('assigned_to') ?: ($lead['assigned_to'] ?? ''); ?>
                            <select class="form-select" name="assigned_to">
                                <option value="">-- Unassigned --</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= $selAssigned == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" class="form-control" name="next_followup_date"
                                   value="<?= e(old('next_followup_date') ?: ($lead['next_followup_date'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Follow-up Mode</label>
                            <?php $selFMode = old('followup_mode') ?: ($lead['followup_mode'] ?? ''); ?>
                            <select class="form-select" name="followup_mode">
                                <option value="">-- Select Mode --</option>
                                <option value="call"      <?= $selFMode === 'call'      ? 'selected' : '' ?>>Call</option>
                                <option value="whatsapp"  <?= $selFMode === 'whatsapp'  ? 'selected' : '' ?>>WhatsApp</option>
                                <option value="email"     <?= $selFMode === 'email'     ? 'selected' : '' ?>>Email</option>
                                <option value="sms"       <?= $selFMode === 'sms'       ? 'selected' : '' ?>>SMS</option>
                                <option value="visit"     <?= $selFMode === 'visit'     ? 'selected' : '' ?>>Visit</option>
                                <option value="video"     <?= $selFMode === 'video'     ? 'selected' : '' ?>>Video Call</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div><!-- /Card 5 -->

            <!-- Card 6: Address -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-map-marker-alt me-2 text-primary"></i>Address
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" name="address_line1"
                                   value="<?= e(old('address_line1') ?: ($lead['address_line1'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" class="form-control" name="address_line2"
                                   value="<?= e(old('address_line2') ?: ($lead['address_line2'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" name="city"
                                   value="<?= e(old('city') ?: ($lead['city'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input type="text" class="form-control" name="state"
                                   value="<?= e(old('state') ?: ($lead['state'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pincode</label>
                            <input type="text" class="form-control" name="pincode"
                                   value="<?= e(old('pincode') ?: ($lead['pincode'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" name="country"
                                   value="<?= e(old('country') ?: ($lead['country'] ?? 'India')) ?>">
                        </div>
                    </div>
                </div>
            </div><!-- /Card 6 -->

            <!-- Card 7: Academic Background -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-school me-2 text-primary"></i>Academic Background
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Last Qualification</label>
                            <input type="text" class="form-control" name="qualification"
                                   value="<?= e(old('qualification') ?: ($lead['qualification'] ?? '')) ?>"
                                   placeholder="e.g. 12th, B.Sc, Diploma">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Percentage / CGPA</label>
                            <input type="number" class="form-control" name="percentage"
                                   value="<?= e(old('percentage') ?: ($lead['percentage'] ?? '')) ?>"
                                   step="0.01" min="0" max="100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Year of Passing</label>
                            <input type="number" class="form-control" name="passing_year"
                                   value="<?= e(old('passing_year') ?: ($lead['passing_year'] ?? '')) ?>"
                                   min="2000" max="<?= date('Y') + 1 ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">School / College Name</label>
                            <input type="text" class="form-control" name="school_college"
                                   value="<?= e(old('school_college') ?: ($lead['school_college'] ?? '')) ?>">
                        </div>
                    </div>
                </div>
            </div><!-- /Card 7 -->

            <!-- Card 8: Requirements & Remarks -->
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
                                           <?= (old('hostel_required') ?? $lead['hostel_required']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="hostelSwitch">
                                        <i class="fas fa-bed me-1"></i>Hostel Required
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="transport_required" id="transportSwitch" value="1"
                                           <?= (old('transport_required') ?? $lead['transport_required']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="transportSwitch">
                                        <i class="fas fa-bus me-1"></i>Transport Required
                                    </label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="scholarship_required" id="scholarshipSwitch" value="1"
                                           <?= (old('scholarship_required') ?? $lead['scholarship_required']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="scholarshipSwitch">
                                        <i class="fas fa-award me-1"></i>Scholarship Required
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes / Remarks</label>
                            <textarea class="form-control" name="notes" rows="4"
                                      placeholder="Internal notes, counselor observations..."><?= e(old('notes') ?: ($lead['notes'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div><!-- /Card 8 -->

        </div><!-- /col-lg-8 -->

        <!-- ==================== SIDEBAR ==================== -->
        <div class="col-lg-4">
            <div class="lead-sidebar-sticky">

                <!-- Duplicate Alert (hidden by default) -->
                <div id="duplicateAlert" class="card border-warning mb-3 d-none">
                    <div class="card-header bg-warning bg-opacity-10 text-warning fw-semibold py-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>Possible Duplicate
                    </div>
                    <div class="card-body py-3">
                        <p class="mb-1 text-muted small">Matches existing lead by <strong id="dupField"></strong>:</p>
                        <p class="mb-2 fw-semibold" id="dupName"></p>
                        <a id="dupLink" href="#" target="_blank" class="btn btn-sm btn-outline-warning w-100">
                            <i class="fas fa-external-link-alt me-1"></i>View Existing Lead
                        </a>
                    </div>
                </div>

                <!-- Lead Info + Save Card -->
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <i class="fas fa-info-circle me-2 text-primary"></i><strong>Lead Info</strong>
                    </div>
                    <div class="card-body">
                        <div class="mb-2 small d-flex justify-content-between">
                            <span class="text-muted">Number</span>
                            <code><?= e($lead['lead_number']) ?></code>
                        </div>
                        <div class="mb-2 small d-flex justify-content-between align-items-center">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-secondary">
                                <?= e($lead['status_name'] ?? ($lead['lead_status_name'] ?? 'Unknown')) ?>
                            </span>
                        </div>
                        <div class="mb-3 small d-flex justify-content-between">
                            <span class="text-muted">Created</span>
                            <span><?= formatDate($lead['created_at'], 'd M Y') ?></span>
                        </div>
                        <hr class="my-2">
                        <button type="submit" class="btn btn-primary w-100 mb-2" style="padding:.65rem;">
                            <i class="fas fa-save me-2"></i>Update Lead
                        </button>
                        <a href="<?= url('leads/' . $eId) ?>" class="btn btn-outline-secondary w-100" style="padding:.6rem;">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </div>

            </div>
        </div><!-- /col-lg-4 -->

    </div><!-- /row -->
</form>

<!-- Sticky bottom action bar -->
<div class="lead-action-bar">
    <div class="d-flex align-items-center gap-2 text-muted small">
        <i class="fas fa-edit text-primary"></i>
        <span>Editing <strong><?= e($lead['lead_number']) ?></strong>
            &mdash; <?= e(trim($lead['first_name'] . ' ' . ($lead['last_name'] ?? ''))) ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('leads/' . $eId) ?>" class="btn btn-outline-secondary btn-sm px-3">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <button type="submit" form="leadForm" class="btn btn-primary btn-sm px-4">
            <i class="fas fa-save me-1"></i>Update Lead
        </button>
    </div>
</div>

<style>
/* Sticky sidebar */
.lead-sidebar-sticky {
    position: sticky;
    top: 80px;
}

/* Sticky bottom action bar */
.lead-action-bar {
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

/* Push page content above bar */
form#leadForm {
    padding-bottom: 4.5rem;
}

@media (max-width: 991.98px) {
    .lead-sidebar-sticky {
        position: static;
    }
    .lead-action-bar {
        padding: .6rem 1rem;
    }
    .lead-action-bar .d-flex.align-items-center {
        display: none !important;
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
    const currentLeadId     = <?= (int)$eId ?>;

    function getInstitutionId() {
        return institutionSelect ? institutionSelect.value : '';
    }

    function showDuplicate(data, fieldLabel) {
        if (data.id === currentLeadId) return;
        dupField.textContent = fieldLabel;
        dupName.textContent  = data.name + ' (' + data.lead_number + ')';
        dupLink.href         = '<?= url('leads') ?>/' + data.id;
        dupAlert.classList.remove('d-none');
    }

    function hideDuplicate() {
        dupAlert.classList.add('d-none');
    }

    if (phoneInput) {
        phoneInput.addEventListener('blur', function () {
            const phone = this.value.trim();
            if (!phone) return;
            const params = {
                phone: phone,
                institution_id: getInstitutionId(),
                exclude_id: currentLeadId
            };
            fetch('<?= url('leads/check-duplicate') ?>?' + new URLSearchParams(params).toString())
                .then(r => r.json())
                .then(data => {
                    if (data.duplicate && data.id !== currentLeadId) {
                        phoneWarningText.textContent = 'Phone already used by ' + data.name + ' (' + data.lead_number + ')';
                        phoneWarning.classList.remove('d-none');
                        showDuplicate(data, 'phone');
                    } else {
                        phoneWarning.classList.add('d-none');
                        hideDuplicate();
                    }
                })
                .catch(() => {});
        });
    }

    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            const email = this.value.trim();
            if (!email) return;
            const params = {
                email: email,
                institution_id: getInstitutionId(),
                exclude_id: currentLeadId
            };
            fetch('<?= url('leads/check-duplicate') ?>?' + new URLSearchParams(params).toString())
                .then(r => r.json())
                .then(data => {
                    if (data.duplicate && data.id !== currentLeadId) {
                        emailWarningText.textContent = 'Email already used by ' + data.name + ' (' + data.lead_number + ')';
                        emailWarning.classList.remove('d-none');
                        showDuplicate(data, 'email');
                    } else {
                        emailWarning.classList.add('d-none');
                        hideDuplicate();
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

            fetch('<?= url('leads/ajax/departments') ?>?institution_id=' + instId)
                .then(r => r.json())
                .then(depts => {
                    depts.forEach(d => {
                        const opt       = document.createElement('option');
                        opt.value       = d.id;
                        opt.textContent = d.name;
                        departmentSelect.appendChild(opt);
                    });
                })
                .catch(() => {});

            fetch('<?= url('leads/ajax/courses') ?>?institution_id=' + instId)
                .then(r => r.json())
                .then(courses => {
                    courses.forEach(c => {
                        const opt       = document.createElement('option');
                        opt.value       = c.id;
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

            fetch('<?= url('leads/ajax/courses') ?>?department_id=' + deptId)
                .then(r => r.json())
                .then(courses => {
                    courses.forEach(c => {
                        const opt       = document.createElement('option');
                        opt.value       = c.id;
                        opt.textContent = c.name;
                        courseSelect.appendChild(opt);
                    });
                })
                .catch(() => {});
        });
    }
})();
</script>
