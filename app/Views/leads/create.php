<?php $pageTitle = 'New Lead'; ?>

<style>
.enquiry-sidebar-sticky {
    position: sticky;
    top: 80px;
}
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
    box-shadow: 0 -4px 16px rgba(79, 70, 229, .10);
}
form#leadForm {
    padding-bottom: 4.5rem;
}
.score-progress-wrap {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-top: .35rem;
}
.score-progress-wrap .progress {
    flex: 1;
    height: 8px;
}
.score-label {
    min-width: 28px;
    font-size: .8rem;
    font-weight: 600;
    color: var(--bs-body-color);
}
.toggle-switch-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
}
.toggle-switch-item {
    display: flex;
    align-items: center;
    gap: .4rem;
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-plus me-2 text-primary"></i>New Lead</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('leads') ?>">Leads</a></li>
                <li class="breadcrumb-item active">New</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('leads') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="<?= url('leads') ?>" id="leadForm" autocomplete="off">
    <?= csrfField() ?>

    <div class="row g-4">

        <!-- ══════════════════════════════════════════════════════════
             LEFT COLUMN — Main Form (col-lg-8)
             ══════════════════════════════════════════════════════════ -->
        <div class="col-lg-8">

            <!-- ── Card 1: Personal Details ───────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-2 text-primary"></i>Personal Details
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <!-- First Name -->
                        <div class="col-md-6">
                            <label class="form-label required">First Name</label>
                            <input type="text" class="form-control" name="first_name"
                                   id="firstNameInput"
                                   value="<?= e(old('first_name')) ?>"
                                   placeholder="e.g. Rahul"
                                   required>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name"
                                   value="<?= e(old('last_name')) ?>"
                                   placeholder="e.g. Sharma">
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label class="form-label required">Phone</label>
                            <input type="text" class="form-control" name="phone"
                                   id="phoneInput"
                                   value="<?= e(old('phone')) ?>"
                                   placeholder="10-digit mobile"
                                   required>
                            <div id="phoneWarning" class="alert alert-warning alert-sm py-2 px-3 mt-1 d-none small">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="phoneWarningText">A lead with this phone number may already exist.</span>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                   id="emailInput"
                                   value="<?= e(old('email')) ?>"
                                   placeholder="example@email.com">
                            <div id="emailWarning" class="alert alert-warning alert-sm py-2 px-3 mt-1 d-none small">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <span id="emailWarningText">A lead with this email may already exist.</span>
                            </div>
                        </div>

                        <!-- Gender -->
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">Select gender</option>
                                <option value="male"   <?= old('gender') === 'male'   ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other"  <?= old('gender') === 'other'  ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <!-- Date of Birth -->
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth"
                                   value="<?= e(old('date_of_birth')) ?>"
                                   max="<?= date('Y-m-d') ?>">
                        </div>

                    </div><!-- /row -->
                </div>
            </div>

            <!-- ── Card 2: Academic Interest ──────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-graduation-cap me-2 text-success"></i>Academic Interest
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <!-- Institution -->
                        <div class="col-md-6">
                            <label class="form-label">Institution</label>
                            <select class="form-select select2" name="institution_id"
                                    id="institutionSelect">
                                <option value="">Select Institution</option>
                                <?php foreach ($institutions ?? [] as $inst): ?>
                                <option value="<?= $inst['id'] ?>"
                                    <?= old('institution_id') == $inst['id'] ? 'selected' : '' ?>>
                                    <?= e($inst['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Department (AJAX populated) -->
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id" id="deptSelect">
                                <option value="">Select Institution First</option>
                                <?php foreach ($departments ?? [] as $dept): ?>
                                <option value="<?= $dept['id'] ?>"
                                    <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= e($dept['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Course Interested (AJAX populated from dept) -->
                        <div class="col-md-6">
                            <label class="form-label">Course Interested</label>
                            <select class="form-select select2" name="course_interested_id"
                                    id="courseSelect">
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= old('course_interested_id') == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Academic Year -->
                        <div class="col-md-6">
                            <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year">
                                <option value="">Select Year</option>
                                <?php
                                $academicYears = ['2023-24', '2024-25', '2025-26', '2026-27'];
                                foreach ($academicYears as $ay):
                                ?>
                                <option value="<?= $ay ?>"
                                    <?= old('academic_year') === $ay ? 'selected' : '' ?>>
                                    <?= $ay ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Preferred Mode -->
                        <div class="col-md-12">
                            <label class="form-label">Preferred Mode</label>
                            <div class="btn-group w-100" role="group" aria-label="Preferred mode">
                                <?php
                                $modes = ['online' => 'Online', 'offline' => 'Offline', 'hybrid' => 'Hybrid'];
                                $oldMode = old('preferred_mode') ?: '';
                                foreach ($modes as $mVal => $mLabel):
                                    $checked = $oldMode === $mVal ? 'checked' : '';
                                ?>
                                <input type="radio" class="btn-check" name="preferred_mode"
                                       id="mode_<?= $mVal ?>" value="<?= $mVal ?>" <?= $checked ?>>
                                <label class="btn btn-outline-primary" for="mode_<?= $mVal ?>">
                                    <?php if ($mVal === 'online'): ?><i class="fas fa-laptop me-1"></i><?php
                                    elseif ($mVal === 'offline'): ?><i class="fas fa-building me-1"></i><?php
                                    else: ?><i class="fas fa-layer-group me-1"></i><?php endif; ?>
                                    <?= $mLabel ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div><!-- /row -->
                </div>
            </div>

            <!-- ── Card 3: Lead Qualification ─────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-star me-2 text-warning"></i>Lead Qualification
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <!-- Status -->
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="lead_status_id">
                                <?php foreach ($statuses as $st): ?>
                                <option value="<?= $st['id'] ?>"
                                    <?= (!empty($st['is_default']) || old('lead_status_id') == $st['id']) ? 'selected' : '' ?>>
                                    <?= e($st['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Priority btn-group -->
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <div class="btn-group w-100" role="group" aria-label="Lead priority">
                                <?php
                                $oldPriority = old('priority') ?: 'warm';
                                $priorities = [
                                    'hot'  => ['label' => '🔥 Hot',  'cls' => 'btn-outline-danger'],
                                    'warm' => ['label' => 'Warm',    'cls' => 'btn-outline-warning'],
                                    'cold' => ['label' => '❄️ Cold', 'cls' => 'btn-outline-info'],
                                ];
                                foreach ($priorities as $pVal => $pData):
                                ?>
                                <input type="radio" class="btn-check" name="priority"
                                       id="priority_<?= $pVal ?>" value="<?= $pVal ?>"
                                    <?= $oldPriority === $pVal ? 'checked' : '' ?>>
                                <label class="btn <?= $pData['cls'] ?>" for="priority_<?= $pVal ?>">
                                    <?= $pData['label'] ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Lead Score with progress bar -->
                        <div class="col-md-6">
                            <label class="form-label" for="leadScore">
                                Lead Score
                                <small class="text-muted">(0–100)</small>
                            </label>
                            <input type="number" class="form-control" name="lead_score"
                                   id="leadScore"
                                   value="<?= e(old('lead_score', '0')) ?>"
                                   min="0" max="100" placeholder="0">
                            <div class="score-progress-wrap">
                                <div class="progress flex-grow-1">
                                    <div class="progress-bar bg-primary" id="scoreBar"
                                         role="progressbar"
                                         style="width:<?= min((int)(old('lead_score', 0)), 100) ?>%"
                                         aria-valuenow="<?= (int)(old('lead_score', 0)) ?>"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="score-label" id="scoreLabel">
                                    <?= (int)(old('lead_score', 0)) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Expected Join Date -->
                        <div class="col-md-6">
                            <label class="form-label">Expected Join Date</label>
                            <input type="date" class="form-control" name="expected_join_date"
                                   value="<?= e(old('expected_join_date')) ?>"
                                   min="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Budget -->
                        <div class="col-md-6">
                            <label class="form-label">Budget</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control" name="budget"
                                       value="<?= e(old('budget')) ?>"
                                       placeholder="0" min="0" step="500">
                            </div>
                        </div>

                        <!-- Enquiry Reference -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Enquiry Reference
                                <small class="text-muted ms-1">(optional)</small>
                            </label>
                            <input type="text" class="form-control form-control-sm mt-1"
                                   name="enquiry_id"
                                   value="<?= e(old('enquiry_id')) ?>"
                                   placeholder="Enquiry # or ID">
                        </div>

                    </div><!-- /row -->
                </div>
            </div>

            <!-- ── Card 4: Source & Assignment ────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-bullhorn me-2 text-info"></i>Source &amp; Assignment
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <!-- Lead Source -->
                        <div class="col-md-6">
                            <label class="form-label">Lead Source</label>
                            <select class="form-select" name="lead_source_id">
                                <option value="">Select Source</option>
                                <?php foreach ($sources as $src): ?>
                                <option value="<?= $src['id'] ?>"
                                    <?= old('lead_source_id') == $src['id'] ? 'selected' : '' ?>>
                                    <?= e($src['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Campaign Name -->
                        <div class="col-md-6">
                            <label class="form-label">Campaign Name</label>
                            <input type="text" class="form-control" name="campaign_name"
                                   value="<?= e(old('campaign_name')) ?>"
                                   placeholder="e.g. Google Ads - Jan 2026">
                        </div>

                        <!-- Reference Name -->
                        <div class="col-md-6">
                            <label class="form-label">Reference Name</label>
                            <input type="text" class="form-control" name="reference_name"
                                   value="<?= e(old('reference_name')) ?>"
                                   placeholder="Who referred this lead?">
                        </div>

                        <!-- Assigned To -->
                        <div class="col-md-6">
                            <label class="form-label">Assign To (Counselor)</label>
                            <select class="form-select select2" name="assigned_to">
                                <option value="">Unassigned</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= old('assigned_to') == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Next Follow-up Date -->
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" class="form-control" name="next_followup_date"
                                   value="<?= e(old('next_followup_date')) ?>"
                                   min="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Follow-up Mode -->
                        <div class="col-md-6">
                            <label class="form-label">Follow-up Mode</label>
                            <select class="form-select" name="followup_mode">
                                <option value="">Select mode</option>
                                <?php
                                $followupModes = [
                                    'call'    => 'Call',
                                    'whatsapp'=> 'WhatsApp',
                                    'visit'   => 'Visit',
                                    'meeting' => 'Meeting',
                                    'email'   => 'Email',
                                ];
                                foreach ($followupModes as $fVal => $fLabel):
                                ?>
                                <option value="<?= $fVal ?>"
                                    <?= old('followup_mode') === $fVal ? 'selected' : '' ?>>
                                    <?= $fLabel ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div><!-- /row -->
                </div>
            </div>

            <!-- ── Card 5: Requirements & Remarks ─────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-clipboard-list me-2 text-secondary"></i>Requirements &amp; Remarks
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <!-- Toggle Switches -->
                        <div class="col-12">
                            <label class="form-label d-block mb-2">Requirements</label>
                            <div class="toggle-switch-row">

                                <!-- Hostel -->
                                <div class="toggle-switch-item">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               role="switch"
                                               name="hostel_required" value="1"
                                               id="hostelRequired"
                                            <?= old('hostel_required') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="hostelRequired">
                                            <i class="fas fa-bed me-1 text-muted"></i>Hostel Required
                                        </label>
                                    </div>
                                </div>

                                <!-- Transport -->
                                <div class="toggle-switch-item">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               role="switch"
                                               name="transport_required" value="1"
                                               id="transportRequired"
                                            <?= old('transport_required') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="transportRequired">
                                            <i class="fas fa-bus me-1 text-muted"></i>Transport Required
                                        </label>
                                    </div>
                                </div>

                                <!-- Scholarship -->
                                <div class="toggle-switch-item">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               role="switch"
                                               name="scholarship_required" value="1"
                                               id="scholarshipRequired"
                                            <?= old('scholarship_required') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="scholarshipRequired">
                                            <i class="fas fa-award me-1 text-muted"></i>Scholarship Required
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Remarks / Notes -->
                        <div class="col-12">
                            <label class="form-label" for="remarksTa">Remarks / Notes</label>
                            <textarea class="form-control" name="remarks" id="remarksTa"
                                      rows="4"
                                      placeholder="Any additional information about this lead..."><?= e(old('remarks')) ?></textarea>
                        </div>

                    </div><!-- /row -->
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- ══════════════════════════════════════════════════════════
             RIGHT COLUMN — Sticky Sidebar (col-lg-4)
             ══════════════════════════════════════════════════════════ -->
        <div class="col-lg-4">
            <div class="enquiry-sidebar-sticky">

                <!-- Duplicate Alert (hidden by default) -->
                <div class="card border-warning mb-4 d-none" id="duplicateAlert">
                    <div class="card-header bg-soft-warning text-warning border-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Possible Duplicate
                    </div>
                    <div class="card-body">
                        <p class="small mb-2 text-muted">
                            A lead with matching contact details was found:
                        </p>
                        <div id="duplicateInfo" class="mb-2">
                            <!-- JS-populated: name + link -->
                        </div>
                        <p class="small text-muted mb-0">
                            You can still save this lead, but please verify it is not a duplicate.
                        </p>
                    </div>
                </div>

                <!-- Save Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-save me-2 text-primary"></i>Save Lead
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" form="leadForm" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Create Lead
                            </button>
                            <a href="<?= url('leads') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6 class="fw-semibold text-muted mb-3">
                            <i class="fas fa-info-circle me-2 text-info"></i>What happens on save
                        </h6>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                A unique lead number is assigned automatically
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                The assigned counselor receives a notification
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Follow-up reminder is scheduled if a date is set
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Lead activity log entry is created
                            </li>
                            <li>
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Lead appears in pipeline &amp; reports immediately
                            </li>
                        </ul>
                    </div>
                </div>

            </div><!-- /enquiry-sidebar-sticky -->
        </div><!-- /col-lg-4 -->

    </div><!-- /row -->
</form>

<!-- ── Sticky Bottom Action Bar ───────────────────────────────────────────── -->
<div class="enquiry-action-bar">
    <small class="text-muted">
        <i class="fas fa-asterisk text-danger me-1" style="font-size:.55rem;"></i>
        Fields marked <strong>required</strong> must be filled before saving.
    </small>
    <div class="d-flex gap-2">
        <a href="<?= url('leads') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <button type="submit" form="leadForm" class="btn btn-primary btn-sm px-4">
            <i class="fas fa-user-plus me-1"></i>Create Lead
        </button>
    </div>
</div>

<script>
(function () {
    'use strict';

    var csrfToken = document.querySelector('meta[name="csrf-token"]')
                    ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    : '';

    /* ── Helper: build query string ─────────────────────────────── */
    function buildQuery(params) {
        return Object.keys(params)
            .filter(function (k) { return params[k] !== ''; })
            .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
            .join('&');
    }

    /* ── Helper: show/hide warning div ──────────────────────────── */
    function setWarning(warningEl, textEl, show, message) {
        if (show && message) {
            textEl.textContent = message;
            warningEl.classList.remove('d-none');
        } else {
            warningEl.classList.add('d-none');
        }
    }

    /* ── Helper: show/hide duplicate alert card ──────────────────── */
    function showDuplicateAlert(data) {
        var alertCard  = document.getElementById('duplicateAlert');
        var infoDiv    = document.getElementById('duplicateInfo');
        if (!alertCard || !infoDiv) return;

        if (data && data.found && data.lead) {
            var lead = data.lead;
            var name = (lead.first_name || '') + ' ' + (lead.last_name || '');
            infoDiv.innerHTML =
                '<a href="' + (lead.url || '/leads/' + lead.id) + '" target="_blank" class="fw-semibold text-warning">' +
                '<i class="fas fa-external-link-alt me-1"></i>' + name.trim() + '</a>' +
                '<span class="d-block text-muted small mt-1">' +
                (lead.lead_number ? 'Lead #: ' + lead.lead_number + ' &bull; ' : '') +
                (lead.status_name || '') + '</span>';
            alertCard.classList.remove('d-none');
        } else {
            alertCard.classList.add('d-none');
            infoDiv.innerHTML = '';
        }
    }

    /* ── Duplicate check fetch ──────────────────────────────────── */
    function checkDuplicate(type, value) {
        if (!value || value.length < 3) return;
        var params = {};
        params[type] = value;
        var qs = buildQuery(params);

        fetch('/leads/check-duplicate?' + qs, {
            headers: { 'X-CSRF-Token': csrfToken, 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var warningEl, textEl, msg;
            if (type === 'phone') {
                warningEl = document.getElementById('phoneWarning');
                textEl    = document.getElementById('phoneWarningText');
                msg = data.found ? 'A lead with this phone already exists.' : null;
                setWarning(warningEl, textEl, data.found, msg);
            } else if (type === 'email') {
                warningEl = document.getElementById('emailWarning');
                textEl    = document.getElementById('emailWarningText');
                msg = data.found ? 'A lead with this email already exists.' : null;
                setWarning(warningEl, textEl, data.found, msg);
            }
            // Show sidebar duplicate card if either check hits
            if (data.found) {
                showDuplicateAlert(data);
            }
        })
        .catch(function () { /* silent fail */ });
    }

    /* ── Phone blur ──────────────────────────────────────────────── */
    var phoneInput = document.getElementById('phoneInput');
    if (phoneInput) {
        phoneInput.addEventListener('blur', function () {
            checkDuplicate('phone', this.value.trim());
        });
    }

    /* ── Email blur ──────────────────────────────────────────────── */
    var emailInput = document.getElementById('emailInput');
    if (emailInput) {
        emailInput.addEventListener('blur', function () {
            checkDuplicate('email', this.value.trim());
        });
    }

    /* ── Institution → Department (AJAX cascade) ─────────────────── */
    var institutionSelect = document.getElementById('institutionSelect');
    var deptSelect        = document.getElementById('deptSelect');
    var courseSelect      = document.getElementById('courseSelect');

    function populateSelect(selectEl, items, placeholder) {
        selectEl.innerHTML = '<option value="">' + placeholder + '</option>';
        if (items && items.length) {
            items.forEach(function (item) {
                var opt = document.createElement('option');
                opt.value       = item.id;
                opt.textContent = item.name;
                selectEl.appendChild(opt);
            });
            selectEl.disabled = false;
        } else {
            selectEl.disabled = true;
        }
    }

    if (institutionSelect && deptSelect) {
        institutionSelect.addEventListener('change', function () {
            var instId = this.value;

            // Reset downstream
            populateSelect(deptSelect, [], 'Select Department');
            populateSelect(courseSelect, [], 'Select Course');

            if (!instId) {
                deptSelect.innerHTML  = '<option value="">Select Institution First</option>';
                courseSelect.innerHTML = '<option value="">Select Department First</option>';
                return;
            }

            fetch('/leads/ajax/departments?institution_id=' + encodeURIComponent(instId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                populateSelect(deptSelect, data.departments || data, 'Select Department');
            })
            .catch(function () {
                deptSelect.innerHTML = '<option value="">Failed to load departments</option>';
            });
        });
    }

    /* ── Department → Course (AJAX cascade) ─────────────────────── */
    if (deptSelect && courseSelect) {
        deptSelect.addEventListener('change', function () {
            var deptId = this.value;

            populateSelect(courseSelect, [], 'Select Course');

            if (!deptId) {
                courseSelect.innerHTML = '<option value="">Select Department First</option>';
                return;
            }

            fetch('/leads/ajax/courses?department_id=' + encodeURIComponent(deptId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                populateSelect(courseSelect, data.courses || data, 'Select Course');
            })
            .catch(function () {
                courseSelect.innerHTML = '<option value="">Failed to load courses</option>';
            });
        });
    }

    /* ── Lead Score → Progress Bar ──────────────────────────────── */
    var scoreInput = document.getElementById('leadScore');
    var scoreBar   = document.getElementById('scoreBar');
    var scoreLabel = document.getElementById('scoreLabel');

    function updateScoreBar(val) {
        val = Math.max(0, Math.min(100, parseInt(val) || 0));
        if (scoreBar) {
            scoreBar.style.width = val + '%';
            scoreBar.setAttribute('aria-valuenow', val);

            // Dynamic colour
            scoreBar.className = 'progress-bar ' + (
                val >= 70 ? 'bg-success' :
                val >= 40 ? 'bg-warning' :
                            'bg-danger'
            );
        }
        if (scoreLabel) {
            scoreLabel.textContent = val;
        }
    }

    if (scoreInput) {
        scoreInput.addEventListener('input', function () {
            updateScoreBar(this.value);
        });
        // Initialise on load
        updateScoreBar(scoreInput.value);
    }

})();
</script>
