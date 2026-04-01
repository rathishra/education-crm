<?php
$fullName = trim($enquiry['first_name'] . ' ' . ($enquiry['last_name'] ?? ''));
$pageTitle = 'Enquiry - ' . e($enquiry['enquiry_number']);

$statusClasses = [
    'new'           => 'bg-soft-primary text-primary',
    'contacted'     => 'bg-soft-info text-info',
    'interested'    => 'bg-soft-success text-success',
    'not_interested'=> 'bg-secondary text-white',
    'converted'     => 'bg-soft-primary text-primary',
    'closed'        => 'text-muted bg-light border',
];
$statusClass = $statusClasses[$enquiry['status']] ?? 'bg-secondary';
$statusLabel = ucwords(str_replace('_', ' ', $enquiry['status'] ?? 'new'));

$priorityClasses = [
    'hot'  => 'badge bg-soft-danger text-danger',
    'warm' => 'badge bg-soft-warning text-warning',
    'cold' => 'badge bg-soft-info text-info',
];
$priorityClass = $priorityClasses[$enquiry['priority'] ?? 'warm'] ?? 'badge bg-secondary';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="fas fa-question-circle me-2 text-primary"></i>Enquiry Details
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('enquiries') ?>">Enquiries</a></li>
                <li class="breadcrumb-item active"><?= e($enquiry['enquiry_number']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (hasPermission('enquiries.edit') && $enquiry['status'] !== 'converted'): ?>
        <a href="<?= url('enquiries/' . $enquiry['id'] . '/edit') ?>" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>
        <?php if (hasPermission('enquiries.convert') && $enquiry['status'] !== 'converted'): ?>
        <form method="POST" action="<?= url('enquiries/' . $enquiry['id'] . '/convert') ?>" class="d-inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-success"
                    onclick="return confirm('Convert this enquiry to a lead?')">
                <i class="fas fa-exchange-alt me-1"></i>Convert to Lead
            </button>
        </form>
        <?php endif; ?>
        <?php if (hasPermission('admissions.create') && $enquiry['status'] !== 'converted'): ?>
        <form method="POST" action="<?= url('enquiries/' . $enquiry['id'] . '/convert-to-admission') ?>" class="d-inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-dark"
                    onclick="return confirm('Convert this enquiry directly to an admission application?')">
                <i class="fas fa-user-graduate me-1"></i>Convert to Admission
            </button>
        </form>
        <?php endif; ?>
        <a href="<?= url('enquiries') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<?php if ($enquiry['status'] === 'converted' && !empty($enquiry['lead_id'])): ?>
<div class="alert alert-success d-flex align-items-center mb-4">
    <i class="fas fa-check-circle me-2 fs-5"></i>
    <span>
        This enquiry has been converted to a lead.
        <a href="<?= url('leads/' . $enquiry['lead_id']) ?>" class="alert-link ms-1">View Lead</a>
    </span>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- ==================== MAIN COLUMN ==================== -->
    <div class="col-lg-8">

        <!-- Card 1: Personal & Academic -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-2 text-primary"></i>Personal &amp; Academic Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Full Name</div>
                        <div class="fw-semibold"><?= e($fullName) ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Enquiry Number</div>
                        <div><code><?= e($enquiry['enquiry_number']) ?></code></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Phone</div>
                        <div>
                            <a href="tel:<?= e($enquiry['phone']) ?>" class="text-decoration-none">
                                <i class="fas fa-phone me-1 text-muted"></i><?= e($enquiry['phone']) ?>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Email</div>
                        <div>
                            <?php if (!empty($enquiry['email'])): ?>
                            <a href="mailto:<?= e($enquiry['email']) ?>" class="text-decoration-none">
                                <i class="fas fa-envelope me-1 text-muted"></i><?= e($enquiry['email']) ?>
                            </a>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Gender</div>
                        <div><?= !empty($enquiry['gender']) ? ucfirst($enquiry['gender']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Date of Birth</div>
                        <div><?= !empty($enquiry['date_of_birth']) ? formatDate($enquiry['date_of_birth'], 'd M Y') : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Institution</div>
                        <div><?= e($enquiry['institution_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Department</div>
                        <div><?= !empty($enquiry['department_name']) ? e($enquiry['department_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Course Interested</div>
                        <div><?= !empty($enquiry['course_name']) ? e($enquiry['course_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Academic Year</div>
                        <div><?= !empty($enquiry['academic_year']) ? e($enquiry['academic_year']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small mb-1">Preferred Mode</div>
                        <div><?= !empty($enquiry['preferred_mode']) ? ucfirst($enquiry['preferred_mode']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Lead Source & Follow-up -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bullhorn me-2 text-primary"></i>Lead Source &amp; Follow-up
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Source</div>
                        <div><?= !empty($enquiry['source']) ? e($enquiry['source']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Campaign</div>
                        <div><?= !empty($enquiry['campaign_name']) ? e($enquiry['campaign_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Reference Name</div>
                        <div><?= !empty($enquiry['reference_name']) ? e($enquiry['reference_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Counselor</div>
                        <div><?= !empty($enquiry['counselor_name']) ? e($enquiry['counselor_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small mb-1">Assigned To</div>
                        <div><?= !empty($enquiry['assigned_to_name']) ? e($enquiry['assigned_to_name']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Next Follow-up</div>
                        <div>
                            <?php if (!empty($enquiry['next_followup_date'])): ?>
                                <?php
                                $fDate   = new DateTime($enquiry['next_followup_date']);
                                $today   = new DateTime();
                                $isPast  = $fDate < $today;
                                ?>
                                <span class="<?= $isPast ? 'text-danger' : 'text-success' ?>">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= formatDate($enquiry['next_followup_date'], 'd M Y') ?>
                                    <?php if ($isPast): ?> <small>(overdue)</small><?php endif; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Follow-up Mode</div>
                        <div><?= !empty($enquiry['followup_mode']) ? ucfirst($enquiry['followup_mode']) : '<span class="text-muted">—</span>' ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Priority</div>
                        <div><span class="<?= $priorityClass ?>">
                            <?= ucfirst($enquiry['priority'] ?? 'warm') ?>
                        </span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Requirements & Remarks -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-clipboard-list me-2 text-primary"></i>Requirements &amp; Remarks
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Hostel</div>
                        <div>
                            <?php if (!empty($enquiry['hostel_required'])): ?>
                            <span class="badge bg-soft-success text-success"><i class="fas fa-check me-1"></i>Required</span>
                            <?php else: ?>
                            <span class="badge bg-light text-muted border">Not Required</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Transport</div>
                        <div>
                            <?php if (!empty($enquiry['transport_required'])): ?>
                            <span class="badge bg-soft-success text-success"><i class="fas fa-check me-1"></i>Required</span>
                            <?php else: ?>
                            <span class="badge bg-light text-muted border">Not Required</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small mb-1">Scholarship</div>
                        <div>
                            <?php if (!empty($enquiry['scholarship_required'])): ?>
                            <span class="badge bg-soft-success text-success"><i class="fas fa-check me-1"></i>Required</span>
                            <?php else: ?>
                            <span class="badge bg-light text-muted border">Not Required</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($enquiry['message'])): ?>
                <div class="mb-3">
                    <div class="text-muted small mb-1">Message / Query</div>
                    <div class="border rounded p-3 bg-light"><?= nl2br(e($enquiry['message'])) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($enquiry['remarks'])): ?>
                <div>
                    <div class="text-muted small mb-1">Remarks</div>
                    <div class="border rounded p-3 bg-light"><?= nl2br(e($enquiry['remarks'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col-lg-8 -->

    <!-- ==================== SIDEBAR ==================== -->
    <div class="col-lg-4">

        <!-- Status Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2 text-primary"></i>Status
            </div>
            <div class="card-body text-center pb-3">
                <span class="badge <?= $statusClass ?> fs-6 px-3 py-2 mb-2 d-block">
                    <?= $statusLabel ?>
                </span>
                <span class="<?= $priorityClass ?> px-3 py-1 d-inline-block rounded-pill mb-3">
                    <?php if (($enquiry['priority'] ?? '') === 'hot'): ?>
                        <i class="fas fa-fire me-1"></i>
                    <?php elseif (($enquiry['priority'] ?? '') === 'cold'): ?>
                        <i class="fas fa-snowflake me-1"></i>
                    <?php else: ?>
                        <i class="fas fa-thermometer-half me-1"></i>
                    <?php endif; ?>
                    <?= ucfirst($enquiry['priority'] ?? 'warm') ?> Priority
                </span>
                <div class="d-grid gap-2 mt-2">
                    <?php if (hasPermission('enquiries.edit') && $enquiry['status'] !== 'converted'): ?>
                    <a href="<?= url('enquiries/' . $enquiry['id'] . '/edit') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit Enquiry
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('enquiries.convert') && $enquiry['status'] !== 'converted'): ?>
                    <form method="POST" action="<?= url('enquiries/' . $enquiry['id'] . '/convert') ?>">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-outline-success btn-sm w-100"
                                onclick="return confirm('Convert to lead?')">
                            <i class="fas fa-exchange-alt me-1"></i>Convert to Lead
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if (hasPermission('admissions.create') && $enquiry['status'] !== 'converted'): ?>
                    <form method="POST" action="<?= url('enquiries/' . $enquiry['id'] . '/convert-to-admission') ?>">
                        <?= csrfField() ?>
                        <button type="submit" class="btn btn-outline-dark btn-sm w-100"
                                onclick="return confirm('Convert to admission?')">
                            <i class="fas fa-user-graduate me-1"></i>Convert to Admission
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Timeline Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-clock me-2 text-primary"></i>Timeline
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small mb-1">Enquiry Number</div>
                    <div><code><?= e($enquiry['enquiry_number']) ?></code></div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small mb-1">Created</div>
                    <div><?= formatDate($enquiry['created_at'], 'd M Y, h:i A') ?></div>
                </div>
                <?php if (!empty($enquiry['updated_at']) && $enquiry['updated_at'] !== $enquiry['created_at']): ?>
                <div class="mb-3">
                    <div class="text-muted small mb-1">Last Updated</div>
                    <div><?= formatDate($enquiry['updated_at'], 'd M Y, h:i A') ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($enquiry['lead_id'])): ?>
                <div>
                    <div class="text-muted small mb-1">Converted Lead</div>
                    <div>
                        <a href="<?= url('leads/' . $enquiry['lead_id']) ?>" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-external-link-alt me-1"></i>View Lead
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Follow-up Update (AJAX) -->
        <?php if ($enquiry['status'] !== 'converted'): ?>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-sync me-2 text-primary"></i>Quick Follow-up Update
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('enquiries/' . $enquiry['id']) ?>"
                      id="quickUpdateForm">
                    <?= csrfField() ?>
                    <!-- Pass required fields to pass controller validation -->
                    <input type="hidden" name="first_name"           value="<?= e($enquiry['first_name']) ?>">
                    <input type="hidden" name="phone"                value="<?= e($enquiry['phone']) ?>">
                    <input type="hidden" name="course_interested_id" value="<?= e($enquiry['course_interested_id'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <?php
                            $qStatuses = ['new','contacted','interested','not_interested','closed'];
                            foreach ($qStatuses as $qs):
                            ?>
                            <option value="<?= $qs ?>" <?= ($enquiry['status'] ?? '') === $qs ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_', ' ', $qs)) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Priority</label>
                        <select class="form-select form-select-sm" name="priority">
                            <option value="hot"  <?= ($enquiry['priority'] ?? '') === 'hot'  ? 'selected' : '' ?>>Hot</option>
                            <option value="warm" <?= ($enquiry['priority'] ?? '') === 'warm' ? 'selected' : '' ?>>Warm</option>
                            <option value="cold" <?= ($enquiry['priority'] ?? '') === 'cold' ? 'selected' : '' ?>>Cold</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Next Follow-up Date</label>
                        <input type="date" class="form-control form-control-sm"
                               name="next_followup_date"
                               value="<?= e($enquiry['next_followup_date'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Follow-up Mode</label>
                        <select class="form-select form-select-sm" name="followup_mode">
                            <option value="">-- Select --</option>
                            <option value="call"     <?= ($enquiry['followup_mode'] ?? '') === 'call'     ? 'selected' : '' ?>>Call</option>
                            <option value="whatsapp" <?= ($enquiry['followup_mode'] ?? '') === 'whatsapp' ? 'selected' : '' ?>>WhatsApp</option>
                            <option value="visit"    <?= ($enquiry['followup_mode'] ?? '') === 'visit'    ? 'selected' : '' ?>>Visit</option>
                            <option value="email"    <?= ($enquiry['followup_mode'] ?? '') === 'email'    ? 'selected' : '' ?>>Email</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-save me-1"></i>Update
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /col-lg-4 -->

</div><!-- /row -->
