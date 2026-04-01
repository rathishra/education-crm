<?php
$pageTitle = 'Lead: ' . e($lead['lead_number']);

$fullName = trim($lead['first_name'] . ' ' . ($lead['last_name'] ?? ''));

// Status colour lookup — fall back to lead_status_color if controller provides it
$statusColor = $lead['status_color'] ?? ($lead['lead_status_color'] ?? '#6c757d');
$statusName  = $lead['status_name']  ?? ($lead['lead_status_name']  ?? 'Unknown');

// Priority badge class
$priorityBadgeClass = match($lead['priority'] ?? 'warm') {
    'hot'  => 'badge bg-danger',
    'cold' => 'badge bg-info text-white',
    default => 'badge bg-warning text-dark',
};
$priorityIcon = match($lead['priority'] ?? 'warm') {
    'hot'  => 'fa-fire',
    'cold' => 'fa-snowflake',
    default => 'fa-thermometer-half',
};

// Activity type icon/colour map
$activityMeta = [
    'call'          => ['icon' => 'fa-phone',        'color' => 'success'],
    'email'         => ['icon' => 'fa-envelope',     'color' => 'info'],
    'whatsapp'      => ['icon' => 'fa-whatsapp',     'color' => 'success'],
    'meeting'       => ['icon' => 'fa-calendar',     'color' => 'warning'],
    'status_change' => ['icon' => 'fa-exchange-alt', 'color' => 'primary'],
    'assignment'    => ['icon' => 'fa-user-check',   'color' => 'secondary'],
    'system'        => ['icon' => 'fa-cog',          'color' => 'secondary'],
    'note'          => ['icon' => 'fa-sticky-note',  'color' => 'warning'],
];

// Follow-up mode icon map
$fuModeIcon = [
    'call'      => 'fa-phone',
    'whatsapp'  => 'fa-whatsapp',
    'email'     => 'fa-envelope',
    'sms'       => 'fa-sms',
    'visit'     => 'fa-walking',
    'video'     => 'fa-video',
];

// Next follow-up overdue check
$nextFollowup     = $lead['next_followup_date'] ?? null;
$isFollowupOverdue = false;
if ($nextFollowup) {
    $isFollowupOverdue = (new DateTime($nextFollowup)) < (new DateTime('today'));
}

// Lead score (0-100)
$leadScore     = (int)($lead['lead_score'] ?? 0);
$scoreColorClass = match(true) {
    $leadScore >= 70 => 'bg-success',
    $leadScore >= 40 => 'bg-warning',
    default          => 'bg-danger',
};
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <code class="me-2 fs-5"><?= e($lead['lead_number']) ?></code>
            <?= e($fullName) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('leads') ?>">Leads</a></li>
                <li class="breadcrumb-item active"><?= e($lead['lead_number']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (hasPermission('leads.edit')): ?>
        <a href="<?= url('leads/' . $lead['id'] . '/edit') ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>
        <?php if (hasPermission('leads.delete')): ?>
        <form method="POST" action="<?= url('leads/' . $lead['id']) ?>" class="d-inline"
              onsubmit="return confirm('Permanently delete this lead? This cannot be undone.')">
            <?= csrfField() ?>
            <input type="hidden" name="_method" value="DELETE">
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash-alt me-1"></i>Delete
            </button>
        </form>
        <?php endif; ?>
        <a href="<?= url('leads') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to List
        </a>
    </div>
</div>

<div class="row g-4">

    <!-- ==================== MAIN COLUMN ==================== -->
    <div class="col-lg-8">

        <!-- Card 1: Lead Info -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-2 text-primary"></i>Lead Information
            </div>
            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Lead Number</div>
                        <div><code><?= e($lead['lead_number']) ?></code></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Full Name</div>
                        <div class="fw-semibold"><?= e($fullName) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Phone</div>
                        <div>
                            <a href="tel:<?= e($lead['phone']) ?>" class="text-decoration-none">
                                <i class="fas fa-phone me-1 text-muted"></i><?= e($lead['phone']) ?>
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Email</div>
                        <div>
                            <?php if (!empty($lead['email'])): ?>
                            <a href="mailto:<?= e($lead['email']) ?>" class="text-decoration-none">
                                <i class="fas fa-envelope me-1 text-muted"></i><?= e($lead['email']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Gender</div>
                        <div><?= !empty($lead['gender']) ? ucfirst($lead['gender']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Date of Birth</div>
                        <div><?= !empty($lead['date_of_birth']) ? formatDate($lead['date_of_birth'], 'd M Y') : '<span class="text-muted">—</span>' ?></div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Institution</div>
                        <div><?= !empty($lead['institution_name']) ? e($lead['institution_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Department</div>
                        <div><?= !empty($lead['department_name']) ? e($lead['department_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Course Interested</div>
                        <div><?= !empty($lead['course_name']) ? e($lead['course_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Academic Year</div>
                        <div><?= !empty($lead['academic_year']) ? e($lead['academic_year']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Preferred Mode</div>
                        <div><?= !empty($lead['preferred_mode']) ? ucfirst($lead['preferred_mode']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Lead Score</div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:8px">
                                <div class="progress-bar <?= $scoreColorClass ?>"
                                     style="width:<?= $leadScore ?>%"
                                     role="progressbar"
                                     aria-valuenow="<?= $leadScore ?>"
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="fw-semibold small"><?= $leadScore ?></span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Expected Join Date</div>
                        <div><?= !empty($lead['expected_join_date']) ? formatDate($lead['expected_join_date'], 'd M Y') : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Budget</div>
                        <div>
                            <?php if (!empty($lead['budget'])): ?>
                            <i class="fas fa-rupee-sign me-1 text-muted"></i><?= number_format((float)$lead['budget'], 0) ?>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Priority</div>
                        <div>
                            <span class="<?= $priorityBadgeClass ?>">
                                <i class="fas <?= $priorityIcon ?> me-1"></i>
                                <?= ucfirst($lead['priority'] ?? 'warm') ?>
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Source</div>
                        <div><?= !empty($lead['source_name']) ? e($lead['source_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Campaign</div>
                        <div><?= !empty($lead['campaign_name']) ? e($lead['campaign_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Reference</div>
                        <div><?= !empty($lead['reference_name']) ? e($lead['reference_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Counselor</div>
                        <div><?= !empty($lead['counselor_name']) ? e($lead['counselor_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Hostel</div>
                        <div>
                            <?php if (!empty($lead['hostel_required'])): ?>
                            <span class="badge bg-soft-success text-success"><i class="fas fa-check me-1"></i>Required</span>
                            <?php else: ?>
                            <span class="badge bg-light text-muted border">Not Required</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Transport</div>
                        <div>
                            <?php if (!empty($lead['transport_required'])): ?>
                            <span class="badge bg-soft-success text-success"><i class="fas fa-check me-1"></i>Required</span>
                            <?php else: ?>
                            <span class="badge bg-light text-muted border">Not Required</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Scholarship</div>
                        <div>
                            <?php if (!empty($lead['scholarship_required'])): ?>
                            <span class="badge bg-soft-success text-success"><i class="fas fa-check me-1"></i>Required</span>
                            <?php else: ?>
                            <span class="badge bg-light text-muted border">Not Required</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($lead['notes'])): ?>
                    <div class="col-12">
                        <div class="text-muted small mb-1">Notes / Remarks</div>
                        <div class="border rounded p-3 bg-light"><?= nl2br(e($lead['notes'])) ?></div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div><!-- /Card 1 -->

        <!-- Card 2: Follow-up History -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2 text-primary"></i>Follow-up History</span>
                <span class="badge bg-soft-secondary text-secondary">
                    <?= count($lead['followups'] ?? []) ?> record<?= count($lead['followups'] ?? []) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lead['followups'])): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-calendar-times fa-2x mb-2 d-block opacity-50"></i>
                    No follow-ups recorded yet
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Counselor</th>
                                <th>Status</th>
                                <th>Outcome</th>
                                <th>Duration</th>
                                <th>Notes</th>
                                <th>Next Follow-up</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lead['followups'] as $fu): ?>
                        <?php
                            $fuIcon = $fuModeIcon[$fu['mode'] ?? ''] ?? 'fa-calendar-check';
                            $fuStatusClass = match($fu['status'] ?? '') {
                                'completed'  => 'bg-soft-success text-success',
                                'missed'     => 'bg-soft-danger text-danger',
                                'cancelled'  => 'bg-soft-secondary text-secondary',
                                default      => 'bg-soft-warning text-warning',
                            };
                            $fuOutcomeClass = match($fu['outcome'] ?? '') {
                                'interested'     => 'bg-soft-success text-success',
                                'not_interested' => 'bg-soft-danger text-danger',
                                'callback'       => 'bg-soft-info text-info',
                                default          => 'bg-soft-secondary text-secondary',
                            };
                        ?>
                        <tr>
                            <td class="text-nowrap">
                                <small><?= !empty($fu['followup_date']) ? formatDate($fu['followup_date'], 'd M Y') : '—' ?></small>
                            </td>
                            <td class="text-nowrap">
                                <i class="fas <?= $fuIcon ?> me-1 text-muted"></i>
                                <small><?= ucfirst($fu['mode'] ?? '—') ?></small>
                            </td>
                            <td>
                                <small><?= !empty($fu['counselor_name']) ? e($fu['counselor_name']) : '—' ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $fuStatusClass ?> small">
                                    <?= ucfirst($fu['status'] ?? '—') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($fu['outcome'])): ?>
                                <span class="badge <?= $fuOutcomeClass ?> small">
                                    <?= ucwords(str_replace('_', ' ', $fu['outcome'])) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <small><?= !empty($fu['duration_minutes']) ? $fu['duration_minutes'] . ' min' : '—' ?></small>
                            </td>
                            <td style="max-width:160px">
                                <small class="text-muted"><?= !empty($fu['notes']) ? e(mb_strimwidth($fu['notes'], 0, 80, '…')) : '—' ?></small>
                            </td>
                            <td class="text-nowrap">
                                <?php if (!empty($fu['next_followup_date'])): ?>
                                <?php $nfd = new DateTime($fu['next_followup_date']); $nfPast = $nfd < new DateTime('today'); ?>
                                <small class="<?= $nfPast ? 'text-danger' : 'text-success' ?>">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= formatDate($fu['next_followup_date'], 'd M Y') ?>
                                </small>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /Card 2 -->

        <!-- Card 3: Add Follow-up (collapsible, shown by default) -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center"
                 role="button"
                 data-bs-toggle="collapse"
                 data-bs-target="#addFollowupBody"
                 aria-expanded="true"
                 aria-controls="addFollowupBody"
                 style="cursor:pointer">
                <span><i class="fas fa-phone-alt me-2 text-primary"></i>Add Follow-up</span>
                <i class="fas fa-chevron-up text-muted small" id="addFollowupChevron"></i>
            </div>
            <div class="collapse show" id="addFollowupBody">
                <div class="card-body">
                    <form method="POST" action="<?= url('leads/' . $lead['id'] . '/followup') ?>">
                        <?= csrfField() ?>

                        <!-- Row 1: Date + Mode -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Follow-up Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control"
                                       name="followup_date"
                                       value="<?= date('Y-m-d\TH:i') ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mode <span class="text-danger">*</span></label>
                                <select class="form-select" name="followup_mode" required>
                                    <option value="">-- Select Mode --</option>
                                    <option value="call">     <i class="fas fa-phone"></i> Call</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="visit">Visit</option>
                                    <option value="video">Video Call</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2: Status + Outcome -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="scheduled" selected>Scheduled</option>
                                    <option value="completed">Completed</option>
                                    <option value="missed">Missed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Outcome</label>
                                <select class="form-select" name="outcome">
                                    <option value="">-- Select Outcome --</option>
                                    <option value="interested">Interested</option>
                                    <option value="not_interested">Not Interested</option>
                                    <option value="callback">Callback Requested</option>
                                    <option value="no_response">No Response</option>
                                    <option value="visit_scheduled">Visit Scheduled</option>
                                    <option value="admitted">Admitted</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 3: Duration + Counselor -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" name="duration_minutes"
                                       min="1" max="240" placeholder="e.g. 15">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Counselor</label>
                                <select class="form-select" name="counselor_id">
                                    <option value="">-- Assign Counselor --</option>
                                    <?php foreach ($counselors as $c): ?>
                                    <option value="<?= $c['id'] ?>"
                                        <?= ($lead['assigned_to'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Row 4: Next Follow-up Date + Mode -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Next Follow-up Date</label>
                                <input type="date" class="form-control" name="next_followup_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Next Follow-up Mode</label>
                                <select class="form-select" name="next_followup_mode">
                                    <option value="">-- Select Mode --</option>
                                    <option value="call">Call</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="visit">Visit</option>
                                    <option value="video">Video Call</option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 5: Notes -->
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"
                                      placeholder="Summary of this follow-up interaction..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Follow-up
                        </button>
                    </form>
                </div>
            </div>
        </div><!-- /Card 3 -->

        <!-- Card 4: Activity Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-stream me-2 text-primary"></i>Activity Timeline
            </div>
            <div class="card-body">
                <?php if (empty($lead['activities'])): ?>
                <div class="text-center text-muted py-3">
                    <i class="fas fa-stream fa-2x mb-2 d-block opacity-50"></i>
                    No activity recorded yet
                </div>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($lead['activities'] as $act): ?>
                    <?php
                        $meta   = $activityMeta[$act['type'] ?? 'note'] ?? ['icon' => 'fa-circle', 'color' => 'secondary'];
                        $aIcon  = $meta['icon'];
                        $aColor = $meta['color'];
                    ?>
                    <div class="d-flex gap-3 mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-<?= $aColor ?> bg-opacity-15 text-<?= $aColor ?> rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:36px;height:36px;min-width:36px">
                                <i class="fas <?= $aIcon ?> small"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold"><?= e($act['title'] ?? '') ?></div>
                            <?php if (!empty($act['description'])): ?>
                            <div class="text-muted small mt-1"><?= nl2br(e($act['description'])) ?></div>
                            <?php endif; ?>
                            <div class="text-muted small mt-1">
                                <?php if (!empty($act['user_name'])): ?>
                                <i class="fas fa-user me-1"></i><?= e($act['user_name']) ?> &middot;
                                <?php endif; ?>
                                <?= timeAgo($act['created_at']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div><!-- /Card 4 -->

        <!-- Card 5: Add Activity Note -->
        <?php if (hasPermission('leads.edit')): ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-pen me-2 text-primary"></i>Add Activity Note
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('leads/' . $lead['id'] . '/activity') ?>">
                    <?= csrfField() ?>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small">Type</label>
                            <select class="form-select form-select-sm" name="type">
                                <option value="note">Note</option>
                                <option value="call">Call</option>
                                <option value="email">Email</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="meeting">Meeting</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm"
                                   name="title" placeholder="Brief summary" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Description</label>
                            <div class="input-group input-group-sm">
                                <textarea class="form-control form-control-sm" name="description"
                                          rows="1" placeholder="Details (optional)"></textarea>
                                <button type="submit" class="btn btn-sm btn-outline-primary px-3">
                                    <i class="fas fa-plus me-1"></i>Add Note
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- /Card 5 -->
        <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- ==================== SIDEBAR ==================== -->
    <div class="col-lg-4">

        <!-- Sidebar Card 1: Status & Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-sliders-h me-2 text-primary"></i>Status &amp; Actions
            </div>
            <div class="card-body">

                <!-- Current status badge (large) -->
                <div class="text-center mb-3">
                    <span class="badge fs-6 px-3 py-2 d-inline-block mb-2"
                          style="background-color:<?= e($statusColor) ?>">
                        <?= e($statusName) ?>
                    </span>
                    <br>
                    <span class="<?= $priorityBadgeClass ?> px-3 py-1 d-inline-block rounded-pill">
                        <i class="fas <?= $priorityIcon ?> me-1"></i>
                        <?= ucfirst($lead['priority'] ?? 'warm') ?> Priority
                    </span>
                </div>

                <!-- Lead Score progress bar -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Lead Score</span>
                        <span class="fw-semibold"><?= $leadScore ?>/100</span>
                    </div>
                    <div class="progress" style="height:10px">
                        <div class="progress-bar <?= $scoreColorClass ?>"
                             role="progressbar"
                             style="width:<?= $leadScore ?>%"
                             aria-valuenow="<?= $leadScore ?>"
                             aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <hr>

                <!-- Status change form -->
                <?php if (hasPermission('leads.edit')): ?>
                <form method="POST" action="<?= url('leads/' . $lead['id'] . '/status') ?>" class="mb-3">
                    <?= csrfField() ?>
                    <label class="form-label small fw-semibold">Change Status</label>
                    <div class="input-group input-group-sm">
                        <select class="form-select" name="status_id">
                            <?php foreach ($statuses as $st): ?>
                            <option value="<?= $st['id'] ?>"
                                <?= ($lead['lead_status_id'] ?? '') == $st['id'] ? 'selected' : '' ?>>
                                <?= e($st['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </form>
                <?php endif; ?>

                <hr>

                <!-- Convert to Admission -->
                <?php if (empty($lead['converted_at'])): ?>
                    <?php if (hasPermission('admissions.create')): ?>
                    <form method="POST" action="<?= url('leads/' . $lead['id'] . '/convert') ?>">
                        <?= csrfField() ?>
                        <button class="btn btn-success w-100"
                                onclick="return confirm('Convert this lead to an Admission? This will create a new Admission record.')">
                            <i class="fas fa-user-graduate me-2"></i>Convert to Admission
                        </button>
                    </form>
                    <?php endif; ?>
                <?php else: ?>
                <div class="text-center">
                    <span class="badge bg-soft-success text-success fs-6 px-3 py-2 d-block mb-2">
                        <i class="fas fa-check-circle me-1"></i>Converted
                    </span>
                    <?php if (!empty($lead['admission_id'])): ?>
                    <a href="<?= url('admissions/' . $lead['admission_id']) ?>"
                       class="btn btn-sm btn-outline-success w-100">
                        <i class="fas fa-external-link-alt me-1"></i>View Admission
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div><!-- /Sidebar Card 1 -->

        <!-- Sidebar Card 2: Assignment -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-tie me-2 text-primary"></i>Assignment
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small mb-1">Currently Assigned To</div>
                    <div class="fw-semibold">
                        <?php if (!empty($lead['assigned_name'])): ?>
                        <i class="fas fa-user-circle me-1 text-muted"></i><?= e($lead['assigned_name']) ?>
                        <?php else: ?>
                        <span class="text-muted">Unassigned</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (hasPermission('leads.assign')): ?>
                <form method="POST" action="<?= url('leads/' . $lead['id'] . '/assign') ?>">
                    <?= csrfField() ?>
                    <label class="form-label small fw-semibold">Re-assign To</label>
                    <div class="input-group input-group-sm">
                        <select class="form-select" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            <?php foreach ($counselors as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= ($lead['assigned_to'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Assign</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div><!-- /Sidebar Card 2 -->

        <!-- Sidebar Card 3: Lead Meta -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2 text-primary"></i>Lead Meta
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">

                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Created By</span>
                        <span><?= !empty($lead['created_by_name']) ? e($lead['created_by_name']) : '<span class="text-muted">—</span>' ?></span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Created At</span>
                        <span><?= formatDate($lead['created_at'], 'd M Y, h:i A') ?></span>
                    </li>

                    <?php if (!empty($lead['updated_at']) && $lead['updated_at'] !== $lead['created_at']): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Last Updated</span>
                        <span><?= formatDate($lead['updated_at'], 'd M Y, h:i A') ?></span>
                    </li>
                    <?php endif; ?>

                    <?php if (!empty($lead['enquiry_id'])): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Source Enquiry</span>
                        <a href="<?= url('enquiries/' . $lead['enquiry_id']) ?>"
                           class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="fas fa-external-link-alt me-1"></i>View
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted">Next Follow-up</span>
                        <?php if ($nextFollowup): ?>
                        <span class="<?= $isFollowupOverdue ? 'text-danger fw-semibold' : 'text-success' ?>">
                            <i class="fas fa-calendar me-1"></i>
                            <?= formatDate($nextFollowup, 'd M Y') ?>
                            <?php if ($isFollowupOverdue): ?><small>(overdue)</small><?php endif; ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted">—</span>
                        <?php endif; ?>
                    </li>

                </ul>
            </div>
        </div><!-- /Sidebar Card 3 -->

    </div><!-- /col-lg-4 -->

</div><!-- /row -->

<script>
// Toggle chevron icon on collapse
(function () {
    const collapseEl = document.getElementById('addFollowupBody');
    const chevron    = document.getElementById('addFollowupChevron');
    if (!collapseEl || !chevron) return;
    collapseEl.addEventListener('hide.bs.collapse', function () {
        chevron.classList.replace('fa-chevron-up', 'fa-chevron-down');
    });
    collapseEl.addEventListener('show.bs.collapse', function () {
        chevron.classList.replace('fa-chevron-down', 'fa-chevron-up');
    });
})();
</script>
