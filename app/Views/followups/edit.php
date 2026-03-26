<?php $pageTitle = 'Edit Follow-up'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-phone-alt me-2"></i>Edit Follow-up</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('followups') ?>">Follow-ups</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <div>
        <span class="badge bg-<?= match($followup['status']) {
            'completed' => 'success',
            'cancelled' => 'secondary',
            'pending' => 'warning',
            default => 'secondary'
        } ?> fs-6 me-2">
            <?= ucfirst($followup['status']) ?>
        </span>
        <a href="<?= url('followups') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<form method="POST" action="<?= url('followups/' . $followup['id']) ?>">
    <?= csrfField() ?>

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><i class="fas fa-edit me-2"></i>Follow-up Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label required">Lead</label>
                            <select class="form-select select2" name="lead_id" required>
                                <option value="">Select Lead</option>
                                <?php foreach ($leads as $lead): ?>
                                <option value="<?= $lead['id'] ?>"
                                    <?= (old('lead_id') ?: $followup['lead_id']) == $lead['id'] ? 'selected' : '' ?>>
                                    <?= e($lead['first_name'] . ' ' . ($lead['last_name'] ?? '') . ' - ' . $lead['phone']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label required">Subject</label>
                            <input type="text" class="form-control" name="subject"
                                   value="<?= e(old('subject') ?: $followup['subject']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Type</label>
                            <select class="form-select" name="type" required>
                                <?php
                                    $types = ['call' => 'Phone Call', 'email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp', 'meeting' => 'Meeting', 'visit' => 'Visit', 'other' => 'Other'];
                                    $currentType = old('type') ?: $followup['type'];
                                    foreach ($types as $val => $label):
                                ?>
                                <option value="<?= $val ?>" <?= $currentType === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Scheduled Date & Time</label>
                            <input type="datetime-local" class="form-control" name="scheduled_at"
                                   value="<?= e(old('scheduled_at') ?: date('Y-m-d\TH:i', strtotime($followup['scheduled_at']))) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <?php $currentPriority = old('priority') ?: $followup['priority']; ?>
                            <select class="form-select" name="priority">
                                <option value="low" <?= $currentPriority === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $currentPriority === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $currentPriority === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= $currentPriority === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned To</label>
                            <select class="form-select select2" name="assigned_to">
                                <option value="">Select Counselor</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= (old('assigned_to') ?: $followup['assigned_to']) == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"><?= e(old('description') ?: $followup['description']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($followup['status'] === 'completed'): ?>
            <!-- Completed Info (Read-only) -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle me-2"></i>Completion Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Completed At</label>
                            <p class="form-control-plaintext fw-semibold">
                                <?= formatDateTime($followup['completed_at']) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Completed By</label>
                            <p class="form-control-plaintext fw-semibold">
                                <?= e($followup['completed_by_name'] ?? '-') ?>
                            </p>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-muted">Outcome</label>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(e($followup['outcome'] ?? '-')) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                <a href="<?= url('followups') ?>" class="btn btn-light me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-1"></i>Update Follow-up
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
