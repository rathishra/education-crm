<?php
$pageTitle = 'Edit Follow-up #' . e($followup['id']);

// Detect the current contact type
if (!empty($followup['enquiry_id'])) {
    $ctType = 'enquiry';
} elseif (!empty($followup['student_id'])) {
    $ctType = 'student';
} else {
    $ctType = 'lead';
}

$fuId = (int)$followup['id'];
?>

<style>
.followup-action-bar {
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
form#followupForm {
    padding-bottom: 4.5rem;
}
.followup-sidebar-sticky {
    position: sticky;
    top: 80px;
}
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-edit me-2 text-primary"></i>Edit Follow-up
            <span class="badge bg-soft-secondary text-secondary ms-2 fs-6">#<?= $fuId ?></span>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('followups') ?>">Follow-ups</a></li>
                <li class="breadcrumb-item">
                    <a href="<?= url('followups/' . $fuId) ?>">#<?= $fuId ?></a>
                </li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('followups/' . $fuId) ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="<?= url('followups/' . $fuId) ?>" id="followupForm" autocomplete="off">
    <?= csrfField() ?>
    <input type="hidden" name="_method" value="PUT">

    <div class="row g-4">

        <!-- ═══════════════════════════════════════════════════════════════════
             LEFT COLUMN — Main Form
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="col-lg-8">

            <!-- Card 1: Link to Contact ────────────────────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-link me-2 text-primary"></i>Link to Contact
                    <small class="text-muted ms-2">Who is this follow-up for?</small>
                </div>
                <div class="card-body">
                    <!-- Contact type toggle -->
                    <div class="btn-group w-100 mb-3" id="contactTypeGroup" role="group"
                         aria-label="Contact type">
                        <input type="radio" name="contact_type" value="enquiry"
                               class="btn-check" id="ctEnquiry"
                               <?= $ctType === 'enquiry' ? 'checked' : '' ?>>
                        <label for="ctEnquiry" class="btn btn-outline-primary">
                            <i class="fas fa-question-circle me-1"></i>Enquiry
                        </label>

                        <input type="radio" name="contact_type" value="lead"
                               class="btn-check" id="ctLead"
                               <?= $ctType === 'lead' ? 'checked' : '' ?>>
                        <label for="ctLead" class="btn btn-outline-primary">
                            <i class="fas fa-user-tie me-1"></i>Lead
                        </label>

                        <input type="radio" name="contact_type" value="student"
                               class="btn-check" id="ctStudent"
                               <?= $ctType === 'student' ? 'checked' : '' ?>>
                        <label for="ctStudent" class="btn btn-outline-primary">
                            <i class="fas fa-user-graduate me-1"></i>Student
                        </label>
                    </div>

                    <!-- Enquiry select -->
                    <div id="enquirySelectRow"
                         style="<?= $ctType !== 'enquiry' ? 'display:none;' : '' ?>">
                        <label class="form-label">Select Enquiry</label>
                        <select name="enquiry_id" class="form-select"
                                id="enquirySelect"
                                <?= $ctType === 'enquiry' ? 'required' : '' ?>>
                            <option value="">— Choose Enquiry —</option>
                            <?php foreach ($enquiries as $enq): ?>
                            <option value="<?= $enq['id'] ?>"
                                data-label="<?= e($enq['name'] ?? $enq['enquiry_number'] ?? '') ?>"
                                <?= (old('enquiry_id') ?: ($followup['enquiry_id'] ?? '')) == $enq['id'] ? 'selected' : '' ?>>
                                <?= e(($enq['name'] ?? '') . ' — ' . ($enq['enquiry_number'] ?? $enq['phone'] ?? '')) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Lead select -->
                    <div id="leadSelectRow"
                         style="<?= $ctType !== 'lead' ? 'display:none;' : '' ?>">
                        <label class="form-label">Select Lead</label>
                        <select name="lead_id" class="form-select"
                                id="leadSelect"
                                <?= $ctType === 'lead' ? 'required' : '' ?>>
                            <option value="">— Choose Lead —</option>
                            <?php foreach ($leads as $ld): ?>
                            <?php $ldName = trim(($ld['first_name'] ?? '') . ' ' . ($ld['last_name'] ?? '')); ?>
                            <option value="<?= $ld['id'] ?>"
                                data-label="<?= e($ldName) ?>"
                                <?= (old('lead_id') ?: ($followup['lead_id'] ?? '')) == $ld['id'] ? 'selected' : '' ?>>
                                <?= e($ldName . ' — ' . ($ld['phone'] ?? '') . (!empty($ld['lead_number']) ? ' (' . $ld['lead_number'] . ')' : '')) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Student select -->
                    <div id="studentSelectRow"
                         style="<?= $ctType !== 'student' ? 'display:none;' : '' ?>">
                        <label class="form-label">Select Student</label>
                        <select name="student_id" class="form-select"
                                id="studentSelect"
                                <?= $ctType === 'student' ? 'required' : '' ?>>
                            <option value="">— Choose Student —</option>
                            <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>"
                                data-label="<?= e($st['name'] ?? '') ?>"
                                <?= (old('student_id') ?: ($followup['student_id'] ?? '')) == $st['id'] ? 'selected' : '' ?>>
                                <?= e(($st['name'] ?? '') . ' — ' . ($st['enrollment_number'] ?? $st['phone'] ?? '')) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Card 2: Follow-up Details ─────────────────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-2 text-success"></i>Follow-up Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Date & Time -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Follow-up Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" name="followup_date"
                                   value="<?= e(old('followup_date') ?: ($followup['followup_date'] ?? '')) ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Follow-up Time</label>
                            <input type="time" class="form-control" name="followup_time"
                                   value="<?= e(old('followup_time') ?: ($followup['followup_time'] ?? '')) ?>">
                        </div>

                        <!-- Mode -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Mode <span class="text-danger">*</span>
                            </label>
                            <?php $selMode = old('followup_mode') ?: ($followup['followup_mode'] ?? ''); ?>
                            <select name="followup_mode" class="form-select" required>
                                <option value="">— Select Mode —</option>
                                <option value="call"     <?= $selMode === 'call'     ? 'selected' : '' ?>>📞 Call</option>
                                <option value="whatsapp" <?= $selMode === 'whatsapp' ? 'selected' : '' ?>>💬 WhatsApp</option>
                                <option value="email"    <?= $selMode === 'email'    ? 'selected' : '' ?>>✉️ Email</option>
                                <option value="visit"    <?= $selMode === 'visit'    ? 'selected' : '' ?>>📍 Visit</option>
                                <option value="meeting"  <?= $selMode === 'meeting'  ? 'selected' : '' ?>>👥 Meeting</option>
                            </select>
                        </div>

                        <!-- Subject -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" class="form-control" name="subject"
                                   value="<?= e(old('subject') ?: ($followup['subject'] ?? '')) ?>"
                                   placeholder="Follow-up subject / topic">
                        </div>

                        <!-- Status (only on edit) -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <?php $selStatus = old('status') ?: ($followup['status'] ?? 'pending'); ?>
                            <select name="status" class="form-select">
                                <option value="pending"     <?= $selStatus === 'pending'     ? 'selected' : '' ?>>Pending</option>
                                <option value="completed"   <?= $selStatus === 'completed'   ? 'selected' : '' ?>>Completed</option>
                                <option value="rescheduled" <?= $selStatus === 'rescheduled' ? 'selected' : '' ?>>Rescheduled</option>
                                <option value="cancelled"   <?= $selStatus === 'cancelled'   ? 'selected' : '' ?>>Cancelled</option>
                                <option value="missed"      <?= $selStatus === 'missed'      ? 'selected' : '' ?>>Missed</option>
                            </select>
                        </div>

                        <!-- Response (shown when status=completed) -->
                        <div class="col-md-6" id="responseRow"
                             style="<?= !in_array($selStatus, ['completed']) ? 'display:none;' : '' ?>">
                            <label class="form-label fw-semibold">Response</label>
                            <?php $selResponse = old('response') ?: ($followup['response'] ?? ''); ?>
                            <select name="response" class="form-select" id="responseSelect">
                                <option value="">— Select —</option>
                                <option value="interested"     <?= $selResponse === 'interested'     ? 'selected' : '' ?>>Interested</option>
                                <option value="not_interested" <?= $selResponse === 'not_interested' ? 'selected' : '' ?>>Not Interested</option>
                                <option value="call_back"      <?= $selResponse === 'call_back'      ? 'selected' : '' ?>>Call Back</option>
                                <option value="no_response"    <?= $selResponse === 'no_response'    ? 'selected' : '' ?>>No Response</option>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="col-12">
                            <label class="form-label fw-semibold d-block">Priority</label>
                            <?php $selPri = old('priority') ?: ($followup['priority'] ?? 'medium'); ?>
                            <div class="btn-group" role="group" aria-label="Priority">
                                <input type="radio" class="btn-check" name="priority"
                                       id="priLow" value="low"
                                       <?= $selPri === 'low' ? 'checked' : '' ?>>
                                <label for="priLow" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-down me-1"></i>Low
                                </label>

                                <input type="radio" class="btn-check" name="priority"
                                       id="priMedium" value="medium"
                                       <?= $selPri === 'medium' ? 'checked' : '' ?>>
                                <label for="priMedium" class="btn btn-outline-warning">
                                    Medium
                                </label>

                                <input type="radio" class="btn-check" name="priority"
                                       id="priHigh" value="high"
                                       <?= $selPri === 'high' ? 'checked' : '' ?>>
                                <label for="priHigh" class="btn btn-outline-danger">
                                    <i class="fas fa-arrow-up me-1"></i>High
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Assignment & Notes ────────────────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-check me-2 text-info"></i>Assignment &amp; Notes
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assigned To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">— Unassigned —</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= (old('assigned_to') ?: ($followup['assigned_to'] ?? '')) == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"
                                      placeholder="Additional notes or instructions..."><?= e(old('remarks') ?: ($followup['remarks'] ?? '')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Next Follow-up ─────────────────────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-forward me-2"></i>Next Follow-up
                    <small class="text-muted ms-2 fw-normal">Optional</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" class="form-control" name="next_followup_date"
                                   value="<?= e(old('next_followup_date') ?: ($followup['next_followup_date'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Time</label>
                            <input type="time" class="form-control" name="next_followup_time"
                                   value="<?= e(old('next_followup_time') ?: ($followup['next_followup_time'] ?? '')) ?>">
                        </div>
                    </div>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Leave empty if no next follow-up is needed yet.
                    </p>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- ═══════════════════════════════════════════════════════════════════
             RIGHT COLUMN — Sidebar
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="col-lg-4">
            <div class="followup-sidebar-sticky">

                <!-- Record Info Card -->
                <div class="card mb-3 bg-light border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-info-circle me-2 text-info"></i>Record Info
                        </h6>
                        <dl class="row small mb-0">
                            <dt class="col-5 text-muted fw-normal">Follow-up #</dt>
                            <dd class="col-7"><?= $fuId ?></dd>
                            <dt class="col-5 text-muted fw-normal">Current Status</dt>
                            <dd class="col-7">
                                <?php
                                $curStatus      = $followup['status'] ?? 'pending';
                                $curStatusClass = match($curStatus) {
                                    'pending'     => 'badge bg-soft-warning text-warning',
                                    'completed'   => 'badge bg-soft-success text-success',
                                    'rescheduled' => 'badge bg-soft-info text-info',
                                    'cancelled'   => 'badge bg-soft-secondary text-secondary',
                                    'missed'      => 'badge bg-soft-danger text-danger',
                                    default       => 'badge bg-soft-secondary text-secondary',
                                };
                                ?>
                                <span class="<?= $curStatusClass ?>"><?= ucfirst($curStatus) ?></span>
                            </dd>
                            <?php if (!empty($followup['created_at'])): ?>
                            <dt class="col-5 text-muted fw-normal">Created</dt>
                            <dd class="col-7"><?= e(formatDate($followup['created_at'], 'd M Y')) ?></dd>
                            <?php endif; ?>
                            <?php if (!empty($followup['updated_at'])): ?>
                            <dt class="col-5 text-muted fw-normal">Last Updated</dt>
                            <dd class="col-7"><?= e(timeAgo($followup['updated_at'])) ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>

                <!-- Save Card -->
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" form="followupForm" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Follow-up
                        </button>
                        <a href="<?= url('followups/' . $fuId) ?>" class="btn btn-light">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </div>

                <!-- Danger Zone -->
                <?php if (hasPermission('followups.delete')): ?>
                <div class="card mt-3 border-danger">
                    <div class="card-body">
                        <p class="text-danger small fw-bold mb-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>Danger Zone
                        </p>
                        <form method="POST"
                              action="<?= url('followups/' . $fuId . '/delete') ?>">
                            <?= csrfField() ?>
                            <button type="submit"
                                    class="btn btn-outline-danger btn-sm w-100 btn-delete"
                                    data-name="#<?= $fuId ?>">
                                <i class="fas fa-trash me-1"></i>Delete This Follow-up
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div><!-- /col-lg-4 -->

    </div><!-- /row -->
</form>

<!-- ── Sticky Bottom Action Bar ──────────────────────────────────────────── -->
<div class="followup-action-bar">
    <div class="text-muted small">
        <i class="fas fa-calendar-alt me-1 text-primary"></i>
        Follow-up #<?= $fuId ?> &mdash;
        <?php
        $barDate = $followup['followup_date'] ?? '';
        $barMode = ucfirst($followup['followup_mode'] ?? '');
        echo ($barDate ? e(formatDate($barDate, 'd M Y')) : 'Date not set');
        echo $barMode ? ' &nbsp;&bull;&nbsp; ' . $barMode : '';
        ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('followups/' . $fuId) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <button type="submit" form="followupForm" class="btn btn-primary btn-sm">
            <i class="fas fa-save me-1"></i>Update Follow-up
        </button>
    </div>
</div>

<script>
(function () {
    'use strict';

    var ctType = <?= json_encode($ctType) ?>;

    var rows = {
        enquiry: document.getElementById('enquirySelectRow'),
        lead   : document.getElementById('leadSelectRow'),
        student: document.getElementById('studentSelectRow'),
    };
    var selects = {
        enquiry: document.getElementById('enquirySelect'),
        lead   : document.getElementById('leadSelect'),
        student: document.getElementById('studentSelect'),
    };

    function showContactType(ct) {
        Object.keys(rows).forEach(function (key) {
            if (rows[key]) rows[key].style.display = key === ct ? '' : 'none';
            if (selects[key]) selects[key].required = (key === ct);
        });
    }

    // Radio change
    document.querySelectorAll('input[name="contact_type"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            showContactType(this.value);
        });
    });

    // Toggle response row on status change
    var statusSelect   = document.querySelector('select[name="status"]');
    var responseRow    = document.getElementById('responseRow');
    if (statusSelect && responseRow) {
        statusSelect.addEventListener('change', function () {
            responseRow.style.display = this.value === 'completed' ? '' : 'none';
        });
    }

    // Confirm delete
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var name = this.dataset.name || 'this follow-up';
            if (!confirm('Delete follow-up ' + name + '? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Initial state
    showContactType(ctType);

}());
</script>
