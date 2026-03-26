<?php $pageTitle = 'Schedule Follow-up'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-phone-alt me-2"></i>Schedule Follow-up</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('followups') ?>">Follow-ups</a></li>
                <li class="breadcrumb-item active">Schedule</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('followups') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('followups') ?>">
    <?= csrfField() ?>

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-calendar-plus me-2"></i>Follow-up Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label required">Lead</label>
                            <select class="form-select select2" name="lead_id" required>
                                <option value="">Select Lead</option>
                                <?php foreach ($leads as $lead): ?>
                                <option value="<?= $lead['id'] ?>"
                                    <?= (old('lead_id') ?: ($selectedLeadId ?? '')) == $lead['id'] ? 'selected' : '' ?>>
                                    <?= e($lead['first_name'] . ' ' . ($lead['last_name'] ?? '') . ' - ' . $lead['phone']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label required">Subject</label>
                            <input type="text" class="form-control" name="subject"
                                   value="<?= e(old('subject')) ?>" placeholder="e.g. Follow-up call regarding admission" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Type</label>
                            <select class="form-select" name="type" required>
                                <?php
                                    $types = ['call' => 'Phone Call', 'email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'meeting' => 'Meeting', 'visit' => 'Visit', 'other' => 'Other'];
                                    foreach ($types as $val => $label):
                                ?>
                                <option value="<?= $val ?>" <?= old('type') === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Scheduled Date & Time</label>
                            <input type="datetime-local" class="form-control" name="scheduled_at"
                                   value="<?= e(old('scheduled_at')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="low" <?= old('priority') === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= old('priority') !== 'low' && old('priority') !== 'high' && old('priority') !== 'urgent' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= old('priority') === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= old('priority') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned To</label>
                            <select class="form-select select2" name="assigned_to">
                                <option value="">Select Counselor</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= (old('assigned_to') ?: ($currentUser['id'] ?? '')) == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"
                                      placeholder="Additional details about this follow-up..."><?= e(old('description')) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                <a href="<?= url('followups') ?>" class="btn btn-light me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar-check me-1"></i>Schedule Follow-up
                </button>
            </div>
        </div>
    </div>
</form>

<script>
$(function() {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});
</script>
