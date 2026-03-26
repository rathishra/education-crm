<?php $pageTitle = 'Schedule Follow-up'; ?>

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
        <h1 class="page-title"><i class="fas fa-phone-plus me-2 text-primary"></i>Schedule Follow-up</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('followups') ?>">Follow-ups</a></li>
                <li class="breadcrumb-item active">New</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('followups') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<?php
// Pre-selection detection
$hasEnquiry = !empty($selectedEnquiryId);
$hasLead    = !empty($selectedLeadId);
$hasStudent = !empty($selectedStudentId);

// Default contact type
if ($hasEnquiry)      { $defaultCt = 'enquiry'; }
elseif ($hasStudent)  { $defaultCt = 'student'; }
else                  { $defaultCt = 'lead'; }

// Pre-selected contact label for the action bar
$preContactLabel = '';
if ($hasEnquiry) {
    foreach ($enquiries as $enq) {
        if ($enq['id'] == $selectedEnquiryId) {
            $preContactLabel = e($enq['name'] ?? $enq['enquiry_number'] ?? '');
            break;
        }
    }
} elseif ($hasLead) {
    foreach ($leads as $ld) {
        if ($ld['id'] == $selectedLeadId) {
            $preContactLabel = e(trim(($ld['first_name'] ?? '') . ' ' . ($ld['last_name'] ?? '')));
            break;
        }
    }
} elseif ($hasStudent) {
    foreach ($students as $st) {
        if ($st['id'] == $selectedStudentId) {
            $preContactLabel = e($st['name'] ?? '');
            break;
        }
    }
}
?>

<form method="POST" action="<?= url('followups') ?>" id="followupForm" autocomplete="off">
    <?= csrfField() ?>

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
                               <?= $defaultCt === 'enquiry' ? 'checked' : '' ?>>
                        <label for="ctEnquiry" class="btn btn-outline-primary">
                            <i class="fas fa-question-circle me-1"></i>Enquiry
                        </label>

                        <input type="radio" name="contact_type" value="lead"
                               class="btn-check" id="ctLead"
                               <?= $defaultCt === 'lead' ? 'checked' : '' ?>>
                        <label for="ctLead" class="btn btn-outline-primary">
                            <i class="fas fa-user-tie me-1"></i>Lead
                        </label>

                        <input type="radio" name="contact_type" value="student"
                               class="btn-check" id="ctStudent"
                               <?= $defaultCt === 'student' ? 'checked' : '' ?>>
                        <label for="ctStudent" class="btn btn-outline-primary">
                            <i class="fas fa-user-graduate me-1"></i>Student
                        </label>
                    </div>

                    <!-- Enquiry select -->
                    <div id="enquirySelectRow"
                         style="<?= $defaultCt !== 'enquiry' ? 'display:none;' : '' ?>">
                        <label class="form-label">Select Enquiry</label>
                        <select name="enquiry_id" class="form-select"
                                id="enquirySelect"
                                <?= $defaultCt === 'enquiry' ? 'required' : '' ?>>
                            <option value="">— Choose Enquiry —</option>
                            <?php foreach ($enquiries as $enq): ?>
                            <option value="<?= $enq['id'] ?>"
                                data-label="<?= e($enq['name'] ?? $enq['enquiry_number'] ?? '') ?>"
                                <?= (old('enquiry_id') ?: ($selectedEnquiryId ?? '')) == $enq['id'] ? 'selected' : '' ?>>
                                <?= e(($enq['name'] ?? '') . ' — ' . ($enq['enquiry_number'] ?? $enq['phone'] ?? '')) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Lead select -->
                    <div id="leadSelectRow"
                         style="<?= $defaultCt !== 'lead' ? 'display:none;' : '' ?>">
                        <label class="form-label">Select Lead</label>
                        <select name="lead_id" class="form-select"
                                id="leadSelect"
                                <?= $defaultCt === 'lead' ? 'required' : '' ?>>
                            <option value="">— Choose Lead —</option>
                            <?php foreach ($leads as $ld): ?>
                            <?php $ldName = trim(($ld['first_name'] ?? '') . ' ' . ($ld['last_name'] ?? '')); ?>
                            <option value="<?= $ld['id'] ?>"
                                data-label="<?= e($ldName) ?>"
                                <?= (old('lead_id') ?: ($selectedLeadId ?? '')) == $ld['id'] ? 'selected' : '' ?>>
                                <?= e($ldName . ' — ' . ($ld['phone'] ?? '') . ($ld['lead_number'] ? ' (' . $ld['lead_number'] . ')' : '')) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Student select -->
                    <div id="studentSelectRow"
                         style="<?= $defaultCt !== 'student' ? 'display:none;' : '' ?>">
                        <label class="form-label">Select Student</label>
                        <select name="student_id" class="form-select"
                                id="studentSelect"
                                <?= $defaultCt === 'student' ? 'required' : '' ?>>
                            <option value="">— Choose Student —</option>
                            <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>"
                                data-label="<?= e($st['name'] ?? '') ?>"
                                <?= (old('student_id') ?: ($selectedStudentId ?? '')) == $st['id'] ? 'selected' : '' ?>>
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
                                   value="<?= e(old('followup_date') ?: date('Y-m-d')) ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Follow-up Time</label>
                            <input type="time" class="form-control" name="followup_time"
                                   value="<?= e(old('followup_time')) ?>">
                        </div>

                        <!-- Mode -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Mode <span class="text-danger">*</span>
                            </label>
                            <select name="followup_mode" class="form-select" required>
                                <option value="">— Select Mode —</option>
                                <option value="call"     <?= old('followup_mode') === 'call'     ? 'selected' : '' ?>>📞 Call</option>
                                <option value="whatsapp" <?= old('followup_mode') === 'whatsapp' ? 'selected' : '' ?>>💬 WhatsApp</option>
                                <option value="email"    <?= old('followup_mode') === 'email'    ? 'selected' : '' ?>>✉️ Email</option>
                                <option value="visit"    <?= old('followup_mode') === 'visit'    ? 'selected' : '' ?>>📍 Visit</option>
                                <option value="meeting"  <?= old('followup_mode') === 'meeting'  ? 'selected' : '' ?>>👥 Meeting</option>
                            </select>
                        </div>

                        <!-- Subject -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" class="form-control" name="subject"
                                   value="<?= e(old('subject')) ?>"
                                   placeholder="Follow-up subject / topic">
                        </div>

                        <!-- Priority -->
                        <div class="col-12">
                            <label class="form-label fw-semibold d-block">Priority</label>
                            <div class="btn-group" role="group" aria-label="Priority">
                                <input type="radio" class="btn-check" name="priority"
                                       id="priLow" value="low"
                                       <?= (old('priority') ?: 'medium') === 'low' ? 'checked' : '' ?>>
                                <label for="priLow" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-down me-1"></i>Low
                                </label>

                                <input type="radio" class="btn-check" name="priority"
                                       id="priMedium" value="medium"
                                       <?= (old('priority') ?: 'medium') === 'medium' ? 'checked' : '' ?>>
                                <label for="priMedium" class="btn btn-outline-warning">
                                    Medium
                                </label>

                                <input type="radio" class="btn-check" name="priority"
                                       id="priHigh" value="high"
                                       <?= old('priority') === 'high' ? 'checked' : '' ?>>
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
                                    <?= old('assigned_to') == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"
                                      placeholder="Additional notes or instructions..."><?= e(old('remarks')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Next Follow-up ─────────────────────────────────────── -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-forward me-2 text-violet"></i>Next Follow-up
                    <small class="text-muted ms-2 fw-normal">Optional — schedule a follow-up after this one</small>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Date</label>
                            <input type="date" class="form-control" name="next_followup_date"
                                   value="<?= e(old('next_followup_date')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Next Follow-up Time</label>
                            <input type="time" class="form-control" name="next_followup_time"
                                   value="<?= e(old('next_followup_time')) ?>">
                        </div>
                    </div>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Leave empty if no next follow-up needed yet.
                    </p>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- ═══════════════════════════════════════════════════════════════════
             RIGHT COLUMN — Sidebar
             ═══════════════════════════════════════════════════════════════════ -->
        <div class="col-lg-4">
            <div class="followup-sidebar-sticky">

                <!-- Info Card -->
                <div class="card mb-3 bg-light border-0">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">
                            <i class="fas fa-info-circle me-2 text-info"></i>What happens on save
                        </h6>
                        <ul class="list-unstyled small mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Follow-up is scheduled and visible in the calendar
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                The assigned counselor is notified
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Contact's follow-up history is updated
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Appears on your Today dashboard if date is today
                            </li>
                            <li>
                                <i class="fas fa-check text-success me-2"></i>
                                Overdue alerts fire if not completed in time
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Save Card -->
                <div class="card">
                    <div class="card-body d-grid gap-2">
                        <button type="submit" form="followupForm" class="btn btn-primary btn-lg">
                            <i class="fas fa-calendar-check me-2"></i>Schedule Follow-up
                        </button>
                        <a href="<?= url('followups') ?>" class="btn btn-light">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </div>

            </div>
        </div><!-- /col-lg-4 -->

    </div><!-- /row -->
</form>

<!-- ── Sticky Bottom Action Bar ──────────────────────────────────────────── -->
<div class="followup-action-bar">
    <div class="text-muted small" id="actionBarContact">
        <?php if ($preContactLabel): ?>
        <i class="fas fa-user me-1 text-primary"></i>
        Scheduling for: <strong id="contactLabelText"><?= $preContactLabel ?></strong>
        <?php else: ?>
        <i class="fas fa-phone-alt me-1 text-primary"></i>
        <span id="contactLabelText">Select a contact above to continue</span>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('followups') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <button type="submit" form="followupForm" class="btn btn-primary btn-sm">
            <i class="fas fa-calendar-check me-1"></i>Schedule Follow-up
        </button>
    </div>
</div>

<script>
(function () {
    'use strict';

    var defaultCt = <?= json_encode($defaultCt) ?>;

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
            if (rows[key]) {
                rows[key].style.display = key === ct ? '' : 'none';
            }
            if (selects[key]) {
                selects[key].required = (key === ct);
            }
        });
        updateActionBarLabel(ct);
    }

    function updateActionBarLabel(ct) {
        var sel = selects[ct];
        var label = document.getElementById('contactLabelText');
        if (!label) return;
        if (sel && sel.value) {
            var opt = sel.options[sel.selectedIndex];
            label.textContent = 'Scheduling for: ' + (opt.dataset.label || opt.text);
        } else {
            label.textContent = 'Select a contact above to continue';
        }
    }

    // Radio change
    document.querySelectorAll('input[name="contact_type"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            showContactType(this.value);
        });
    });

    // Select change — update action bar
    Object.keys(selects).forEach(function (ct) {
        if (selects[ct]) {
            selects[ct].addEventListener('change', function () {
                updateActionBarLabel(ct);
            });
        }
    });

    // Initial state
    showContactType(defaultCt);

}());
</script>
