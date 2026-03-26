<?php
$pageTitle = 'Follow-up Details';

$fuId = (int)($followup['id'] ?? 0);

// Status badge class
$statusClass = match($followup['status'] ?? '') {
    'pending'     => 'badge bg-soft-warning text-warning',
    'completed'   => 'badge bg-soft-success text-success',
    'rescheduled' => 'badge bg-soft-info text-info',
    'cancelled'   => 'badge bg-soft-secondary text-secondary',
    'missed'      => 'badge bg-soft-danger text-danger',
    default       => 'badge bg-soft-secondary text-secondary',
};

// Priority badge
$priorityClass = match($followup['priority'] ?? 'medium') {
    'high'   => 'badge bg-danger',
    'medium' => 'badge bg-warning text-dark',
    'low'    => 'badge bg-secondary',
    default  => 'badge bg-secondary',
};

// Mode icon map
$modeIcons = [
    'call'    => 'fa-phone',
    'whatsapp'=> 'fa-whatsapp',
    'email'   => 'fa-envelope',
    'visit'   => 'fa-map-marker-alt',
    'meeting' => 'fa-users',
];
$modeIcon  = $modeIcons[$followup['followup_mode'] ?? ''] ?? 'fa-phone';
$modeLabel = ucfirst($followup['followup_mode'] ?? '');

// Contact type
if (!empty($followup['enquiry_id'])) {
    $ctType  = 'Enquiry';
    $ctClass = 'badge bg-soft-primary text-primary';
    $ctName  = $followup['enquiry_name'] ?? $followup['contact_name'] ?? '—';
    $ctLink  = url('enquiries/' . $followup['enquiry_id']);
    $ctNum   = $followup['enquiry_number'] ?? '';
} elseif (!empty($followup['student_id'])) {
    $ctType  = 'Student';
    $ctClass = 'badge bg-soft-success text-success';
    $ctName  = $followup['student_name'] ?? $followup['contact_name'] ?? '—';
    $ctLink  = url('students/' . $followup['student_id']);
    $ctNum   = $followup['enrollment_number'] ?? '';
} else {
    $ctType  = 'Lead';
    $ctClass = 'badge bg-soft-info text-info';
    $ctName  = $followup['lead_name'] ?? $followup['contact_name'] ?? '—';
    $ctLink  = !empty($followup['lead_id']) ? url('leads/' . $followup['lead_id']) : null;
    $ctNum   = $followup['lead_number'] ?? '';
}

// Contact phone
$ctPhone = $followup['contact_phone'] ?? $followup['lead_number'] ?? '';

// Response label
$responseLabel = match($followup['response'] ?? '') {
    'interested'     => 'Interested',
    'not_interested' => 'Not Interested',
    'call_back'      => 'Call Back',
    'no_response'    => 'No Response',
    default          => '',
};
$responseClass = match($followup['response'] ?? '') {
    'interested'     => 'badge bg-soft-success text-success',
    'not_interested' => 'badge bg-soft-danger text-danger',
    'call_back'      => 'badge bg-soft-warning text-warning',
    'no_response'    => 'badge bg-soft-secondary text-secondary',
    default          => '',
};

$fuStatus = $followup['status'] ?? 'pending';
$today    = date('Y-m-d');
$fuDate   = $followup['followup_date'] ?? '';
$isOverdue = $fuDate && $fuDate < $today && $fuStatus === 'pending';
?>

<!-- ── Page Header ────────────────────────────────────────────────────────── -->
<div class="page-header">
    <div>
        <h1 class="page-title d-flex align-items-center gap-2 flex-wrap">
            <i class="fas fa-phone-alt text-primary"></i>
            Follow-up <span class="text-muted">#<?= $fuId ?></span>
            <span class="<?= $statusClass ?> ms-1"><?= ucfirst($fuStatus) ?></span>
            <span class="<?= $priorityClass ?> ms-1"><?= ucfirst($followup['priority'] ?? 'medium') ?> Priority</span>
            <?php if ($isOverdue): ?>
            <span class="badge bg-danger ms-1">
                <i class="fas fa-exclamation-triangle me-1"></i>Overdue
            </span>
            <?php endif; ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('followups') ?>">Follow-ups</a></li>
                <li class="breadcrumb-item active">#<?= $fuId ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (hasPermission('followups.edit')): ?>
        <a href="<?= url('followups/' . $fuId . '/edit') ?>" class="btn btn-outline-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>

        <?php if (in_array($fuStatus, ['pending', 'rescheduled', 'missed']) && hasPermission('followups.edit')): ?>
        <button type="button" class="btn btn-success" id="openCompleteBtn">
            <i class="fas fa-check me-1"></i>Mark Complete
        </button>
        <?php endif; ?>

        <?php if ($fuStatus === 'pending' && hasPermission('followups.edit')): ?>
        <button type="button" class="btn btn-outline-warning" id="openRescheduleBtn">
            <i class="fas fa-redo me-1"></i>Reschedule
        </button>
        <?php endif; ?>

        <?php if (hasPermission('followups.delete')): ?>
        <form method="POST" action="<?= url('followups/' . $fuId . '/delete') ?>"
              class="d-inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-outline-danger btn-delete"
                    data-name="#<?= $fuId ?>">
                <i class="fas fa-trash me-1"></i>Delete
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- ── Two-column Layout ──────────────────────────────────────────────────── -->
<div class="row g-4">

    <!-- ═══════════════════════════════════════════════════════════════════
         LEFT COLUMN — Details (col-lg-8)
         ═══════════════════════════════════════════════════════════════════ -->
    <div class="col-lg-8">

        <!-- Card 1: Contact Info ──────────────────────────────────────────── -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-2 text-primary"></i>Contact Information
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Contact Type</div>
                        <span class="<?= $ctClass ?>"><?= $ctType ?></span>
                    </div>
                    <div class="col-sm-8">
                        <div class="text-muted small fw-semibold mb-1">Name</div>
                        <div class="fw-semibold">
                            <?php if ($ctLink): ?>
                            <a href="<?= $ctLink ?>" class="text-decoration-none">
                                <?= e($ctName) ?>
                                <i class="fas fa-external-link-alt ms-1 text-muted" style="font-size:.7rem;"></i>
                            </a>
                            <?php else: ?>
                            <?= e($ctName) ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($ctNum): ?>
                        <small class="text-muted"><?= e($ctNum) ?></small>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($followup['contact_phone'])): ?>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Phone</div>
                        <a href="tel:<?= e($followup['contact_phone']) ?>"
                           class="text-decoration-none">
                            <i class="fas fa-phone-alt me-1 text-muted"></i>
                            <?= e($followup['contact_phone']) ?>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ($ctLink): ?>
                    <div class="col-sm-8">
                        <div class="text-muted small fw-semibold mb-1">Record Link</div>
                        <a href="<?= $ctLink ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt me-1"></i>View <?= $ctType ?> Record
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card 2: Follow-up Details ──────────────────────────────────────── -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-phone-alt me-2 text-success"></i>Follow-up Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Date</div>
                        <div class="<?= $isOverdue ? 'text-danger fw-semibold' : '' ?>">
                            <?php if ($isOverdue): ?>
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <?php endif; ?>
                            <?= $fuDate ? e(formatDate($fuDate, 'd M Y')) : '—' ?>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Time</div>
                        <div>
                            <?php if (!empty($followup['followup_time'])): ?>
                            <i class="fas fa-clock me-1 text-muted"></i>
                            <?= e(date('h:i A', strtotime($followup['followup_time']))) ?>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Mode</div>
                        <div>
                            <i class="fas <?= $modeIcon ?> me-1 text-muted"></i>
                            <?= $modeLabel ?: '—' ?>
                        </div>
                    </div>
                    <?php if (!empty($followup['subject'])): ?>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold mb-1">Subject</div>
                        <div><?= e($followup['subject']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Assigned To</div>
                        <div><?= e($followup['assigned_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Created By</div>
                        <div><?= e($followup['created_by_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Priority</div>
                        <span class="<?= $priorityClass ?>"><?= ucfirst($followup['priority'] ?? 'medium') ?></span>
                    </div>
                    <?php if (!empty($followup['created_at'])): ?>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Created At</div>
                        <div class="small"><?= e(formatDate($followup['created_at'], 'd M Y, h:i A')) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($followup['updated_at'])): ?>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Last Updated</div>
                        <div class="small text-muted"><?= e(timeAgo($followup['updated_at'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card 3: Outcome (only if completed) ───────────────────────────── -->
        <?php if ($fuStatus === 'completed'): ?>
        <div class="card mb-4 border-success">
            <div class="card-header bg-soft-success text-success">
                <i class="fas fa-check-circle me-2"></i>Outcome
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Response</div>
                        <?php if (!empty($responseClass)): ?>
                        <span class="<?= $responseClass ?>"><?= $responseLabel ?></span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold mb-1">Remarks</div>
                        <div class="text-body">
                            <?= !empty($followup['remarks']) ? nl2br(e($followup['remarks'])) : '<span class="text-muted">No remarks recorded.</span>' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif (!empty($followup['remarks'])): ?>
        <!-- Remarks even when not completed -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-sticky-note me-2 text-muted"></i>Remarks
            </div>
            <div class="card-body">
                <?= nl2br(e($followup['remarks'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card 4: Next Follow-up (if scheduled) ─────────────────────────── -->
        <?php if (!empty($followup['next_followup_date'])): ?>
        <div class="card mb-4 border-info">
            <div class="card-header bg-soft-info text-info">
                <i class="fas fa-forward me-2"></i>Next Follow-up Scheduled
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Next Date</div>
                        <div class="fw-semibold">
                            <?= e(formatDate($followup['next_followup_date'], 'd M Y')) ?>
                        </div>
                    </div>
                    <?php if (!empty($followup['next_followup_time'])): ?>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Next Time</div>
                        <div>
                            <i class="fas fa-clock me-1 text-muted"></i>
                            <?= e(date('h:i A', strtotime($followup['next_followup_time']))) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-sm-4">
                        <div class="text-muted small fw-semibold mb-1">Days Away</div>
                        <div>
                            <?php
                            $nextDate = new DateTime($followup['next_followup_date']);
                            $todayDt  = new DateTime('today');
                            $diff     = $todayDt->diff($nextDate);
                            if ($nextDate > $todayDt) {
                                echo '<span class="text-success">' . $diff->days . ' day' . ($diff->days !== 1 ? 's' : '') . ' from now</span>';
                            } elseif ($nextDate == $todayDt) {
                                echo '<span class="badge bg-warning text-dark">Today</span>';
                            } else {
                                echo '<span class="text-danger">' . $diff->days . ' day' . ($diff->days !== 1 ? 's' : '') . ' ago</span>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <a href="<?= url('followups/create') ?>?contact_type=<?= $ctType === 'Enquiry' ? 'enquiry' : ($ctType === 'Student' ? 'student' : 'lead') ?>&<?= $ctType === 'Enquiry' ? 'enquiry_id=' . ($followup['enquiry_id'] ?? '') : ($ctType === 'Student' ? 'student_id=' . ($followup['student_id'] ?? '') : 'lead_id=' . ($followup['lead_id'] ?? '')) ?>"
                           class="btn btn-sm btn-outline-info">
                            <i class="fas fa-plus me-1"></i>Schedule Follow-up for this Contact
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card 5: History / Rescheduled Chain ─────────────────────────── -->
        <?php if (!empty($followup['history'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-history me-2 text-muted"></i>Follow-up History Chain
                <span class="badge bg-soft-secondary text-secondary ms-2">
                    <?= count($followup['history']) ?> record<?= count($followup['history']) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th>Response</th>
                                <th>Assigned To</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($followup['history'] as $hist):
                            $histStatus = $hist['status'] ?? '';
                            $histStatusClass = match($histStatus) {
                                'pending'     => 'badge bg-soft-warning text-warning',
                                'completed'   => 'badge bg-soft-success text-success',
                                'rescheduled' => 'badge bg-soft-info text-info',
                                'cancelled'   => 'badge bg-soft-secondary text-secondary',
                                'missed'      => 'badge bg-soft-danger text-danger',
                                default       => 'badge bg-soft-secondary text-secondary',
                            };
                            $histResponse = match($hist['response'] ?? '') {
                                'interested'     => ['Interested', 'badge bg-soft-success text-success'],
                                'not_interested' => ['Not Interested', 'badge bg-soft-danger text-danger'],
                                'call_back'      => ['Call Back', 'badge bg-soft-warning text-warning'],
                                'no_response'    => ['No Response', 'badge bg-soft-secondary text-secondary'],
                                default          => ['—', ''],
                            };
                            $histModeIcon = $modeIcons[$hist['followup_mode'] ?? ''] ?? 'fa-phone';
                        ?>
                        <tr <?= isset($hist['id']) && $hist['id'] == $fuId ? 'class="table-active fw-semibold"' : '' ?>>
                            <td>
                                <?= !empty($hist['followup_date']) ? e(formatDate($hist['followup_date'], 'd M Y')) : '—' ?>
                                <?php if (!empty($hist['followup_time'])): ?>
                                <br><small class="text-muted"><?= e(date('h:i A', strtotime($hist['followup_time']))) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fas <?= $histModeIcon ?> me-1 text-muted"></i>
                                <?= ucfirst($hist['followup_mode'] ?? '—') ?>
                            </td>
                            <td><span class="<?= $histStatusClass ?>"><?= ucfirst($histStatus) ?></span></td>
                            <td>
                                <?php if ($histResponse[1]): ?>
                                <span class="<?= $histResponse[1] ?>"><?= $histResponse[0] ?></span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($hist['assigned_name'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- ═══════════════════════════════════════════════════════════════════
         RIGHT COLUMN — Sidebar (col-lg-4)
         ═══════════════════════════════════════════════════════════════════ -->
    <div class="col-lg-4">
        <div style="position:sticky; top:80px;">

            <!-- Quick Actions Card ──────────────────────────────────────── -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                </div>
                <div class="card-body d-grid gap-2">
                    <?php if (in_array($fuStatus, ['pending', 'rescheduled', 'missed']) && hasPermission('followups.edit')): ?>
                    <button type="button" class="btn btn-success" id="sideCompleteBtn">
                        <i class="fas fa-check me-2"></i>Mark as Complete
                    </button>
                    <?php endif; ?>

                    <?php if ($fuStatus === 'pending' && hasPermission('followups.edit')): ?>
                    <button type="button" class="btn btn-outline-warning" id="sideRescheduleBtn">
                        <i class="fas fa-redo me-2"></i>Reschedule
                    </button>
                    <?php endif; ?>

                    <?php if (hasPermission('followups.edit')): ?>
                    <a href="<?= url('followups/' . $fuId . '/edit') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit Follow-up
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('followups.create')): ?>
                    <a href="<?= url('followups/create') ?>?<?= $ctType === 'Enquiry' ? 'enquiry_id=' . ($followup['enquiry_id'] ?? '') : ($ctType === 'Student' ? 'student_id=' . ($followup['student_id'] ?? '') : 'lead_id=' . ($followup['lead_id'] ?? '')) ?>"
                       class="btn btn-outline-secondary">
                        <i class="fas fa-plus me-2"></i>New Follow-up for <?= $ctType ?>
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('followups.delete')): ?>
                    <hr class="my-1">
                    <form method="POST" action="<?= url('followups/' . $fuId . '/delete') ?>">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-outline-danger w-100 btn-delete"
                                data-name="#<?= $fuId ?>">
                            <i class="fas fa-trash me-2"></i>Delete Follow-up
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Meta Card ──────────────────────────────────────────────── -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2 text-muted"></i>Details
                </div>
                <div class="card-body">
                    <dl class="row small mb-0">
                        <dt class="col-5 text-muted fw-normal">Follow-up #</dt>
                        <dd class="col-7"><?= $fuId ?></dd>

                        <dt class="col-5 text-muted fw-normal">Status</dt>
                        <dd class="col-7">
                            <span class="<?= $statusClass ?>"><?= ucfirst($fuStatus) ?></span>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Priority</dt>
                        <dd class="col-7">
                            <span class="<?= $priorityClass ?>"><?= ucfirst($followup['priority'] ?? 'medium') ?></span>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Mode</dt>
                        <dd class="col-7">
                            <i class="fas <?= $modeIcon ?> me-1 text-muted"></i><?= $modeLabel ?: '—' ?>
                        </dd>

                        <dt class="col-5 text-muted fw-normal">Assigned To</dt>
                        <dd class="col-7"><?= e($followup['assigned_name'] ?? '—') ?></dd>

                        <dt class="col-5 text-muted fw-normal">Created By</dt>
                        <dd class="col-7"><?= e($followup['created_by_name'] ?? '—') ?></dd>

                        <?php if (!empty($followup['created_at'])): ?>
                        <dt class="col-5 text-muted fw-normal">Created</dt>
                        <dd class="col-7"><?= e(formatDate($followup['created_at'], 'd M Y')) ?></dd>
                        <?php endif; ?>

                        <?php if (!empty($followup['updated_at'])): ?>
                        <dt class="col-5 text-muted fw-normal">Updated</dt>
                        <dd class="col-7 text-muted"><?= e(timeAgo($followup['updated_at'])) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

        </div>
    </div><!-- /col-lg-4 -->

</div><!-- /row -->

<!-- ═══════════════════════════════════════════════════════════════════════════
     COMPLETE MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="completeModal" tabindex="-1"
     aria-labelledby="completeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeModalLabel">
                    <i class="fas fa-check-circle text-success me-2"></i>Mark Follow-up Complete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <form method="POST"
                  action="<?= url('followups/' . $fuId . '/complete') ?>">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Response <span class="text-danger">*</span>
                        </label>
                        <select name="response" class="form-select" required>
                            <option value="">Select response...</option>
                            <option value="interested">Interested</option>
                            <option value="not_interested">Not Interested</option>
                            <option value="call_back">Call Back</option>
                            <option value="no_response">No Response</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"
                                  placeholder="Notes about the outcome of this follow-up..."></textarea>
                    </div>
                    <hr class="my-3">
                    <p class="small text-muted mb-2 fw-semibold">
                        <i class="fas fa-forward me-1"></i>Schedule Next Follow-up (optional)
                    </p>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label small">Next Follow-up Date</label>
                            <input type="date" name="next_followup_date"
                                   class="form-control form-control-sm"
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Next Mode</label>
                            <select name="next_followup_mode"
                                    class="form-select form-select-sm">
                                <option value="">Select mode...</option>
                                <option value="call">Call</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="email">Email</option>
                                <option value="visit">Visit</option>
                                <option value="meeting">Meeting</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Mark Complete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     RESCHEDULE MODAL
     ═══════════════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="rescheduleModal" tabindex="-1"
     aria-labelledby="rescheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rescheduleModalLabel">
                    <i class="fas fa-redo text-warning me-2"></i>Reschedule Follow-up
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <form method="POST"
                  action="<?= url('followups/' . $fuId . '/reschedule') ?>">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                New Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="followup_date"
                                   class="form-control"
                                   min="<?= date('Y-m-d') ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">New Time</label>
                            <input type="time" name="followup_time"
                                   class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Mode <span class="text-danger">*</span>
                            </label>
                            <select name="followup_mode" class="form-select" required>
                                <option value="">Select mode...</option>
                                <option value="call"     <?= ($followup['followup_mode'] ?? '') === 'call'     ? 'selected' : '' ?>>📞 Call</option>
                                <option value="whatsapp" <?= ($followup['followup_mode'] ?? '') === 'whatsapp' ? 'selected' : '' ?>>💬 WhatsApp</option>
                                <option value="email"    <?= ($followup['followup_mode'] ?? '') === 'email'    ? 'selected' : '' ?>>✉️ Email</option>
                                <option value="visit"    <?= ($followup['followup_mode'] ?? '') === 'visit'    ? 'selected' : '' ?>>📍 Visit</option>
                                <option value="meeting"  <?= ($followup['followup_mode'] ?? '') === 'meeting'  ? 'selected' : '' ?>>👥 Meeting</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Assign To</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">— Keep Current —</option>
                                <?php foreach ($counselors as $c): ?>
                                <option value="<?= $c['id'] ?>"
                                    <?= ($followup['assigned_to'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low"    <?= ($followup['priority'] ?? '') === 'low'    ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= ($followup['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high"   <?= ($followup['priority'] ?? '') === 'high'   ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Reason / Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"
                                      placeholder="Why is this being rescheduled?"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-redo me-1"></i>Reschedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Open complete modal from both header and sidebar buttons
    var completeModal = new bootstrap.Modal(document.getElementById('completeModal'));
    ['openCompleteBtn', 'sideCompleteBtn'].forEach(function (id) {
        var btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', function () { completeModal.show(); });
    });

    // Open reschedule modal from both header and sidebar buttons
    var rescheduleModal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
    ['openRescheduleBtn', 'sideRescheduleBtn'].forEach(function (id) {
        var btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', function () { rescheduleModal.show(); });
    });

    // Confirm delete
    document.querySelectorAll('.btn-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var name = this.dataset.name || 'this follow-up';
            if (!confirm('Delete follow-up ' + name + '? This cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
