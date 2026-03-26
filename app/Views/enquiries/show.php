<?php
$fullName = trim($enquiry['first_name'] . ' ' . ($enquiry['last_name'] ?? ''));
$pageTitle = 'Enquiry - ' . e($enquiry['enquiry_number']);
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-question-circle me-2"></i>Enquiry Details</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('enquiries') ?>">Enquiries</a></li>
                <li class="breadcrumb-item active"><?= e($enquiry['enquiry_number']) ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (hasPermission('enquiries.edit') && $enquiry['status'] !== 'converted'): ?>
        <a href="<?= url('enquiries/' . $enquiry['id'] . '/edit') ?>" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
        <?php endif; ?>
        <?php if (hasPermission('enquiries.convert') && $enquiry['status'] !== 'converted'): ?>
        <form method="POST" action="<?= url('enquiries/' . $enquiry['id'] . '/convert') ?>" class="d-inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-success" onclick="return confirm('Convert this enquiry to a lead?')">
                <i class="fas fa-exchange-alt me-1"></i>Convert to Lead
            </button>
        </form>
        <?php endif; ?>
        <a href="<?= url('enquiries') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i>Enquiry Information</span>
                <?php
                $statusColors = ['new' => 'primary', 'contacted' => 'info', 'converted' => 'success', 'closed' => 'secondary'];
                $color = $statusColors[$enquiry['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $color ?> fs-6"><?= ucfirst($enquiry['status']) ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Name</label>
                        <div class="fw-semibold"><?= e($fullName) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Enquiry Number</label>
                        <div><code><?= e($enquiry['enquiry_number']) ?></code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Phone</label>
                        <div><a href="tel:<?= e($enquiry['phone']) ?>"><?= e($enquiry['phone']) ?></a></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email</label>
                        <div><?= $enquiry['email'] ? '<a href="mailto:' . e($enquiry['email']) . '">' . e($enquiry['email']) . '</a>' : '<span class="text-muted">-</span>' ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Course Interested</label>
                        <div><?= e($enquiry['course_name'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Source</label>
                        <div><?= e($enquiry['source'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Institution</label>
                        <div><?= e($enquiry['institution_name'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Assigned To</label>
                        <div><?= e($enquiry['assigned_to_name'] ?? '-') ?></div>
                    </div>
                    <?php if (!empty($enquiry['message'])): ?>
                    <div class="col-12">
                        <label class="text-muted small">Message / Query</label>
                        <div class="border rounded p-3 bg-light"><?= nl2br(e($enquiry['message'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($enquiry['status'] === 'converted' && !empty($enquiry['lead_id'])): ?>
        <div class="alert alert-success mt-3">
            <i class="fas fa-check-circle me-2"></i>
            This enquiry has been converted to a lead.
            <a href="<?= url('leads/' . $enquiry['lead_id']) ?>" class="alert-link">View Lead</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><i class="fas fa-clock me-2"></i>Timeline</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Created</label>
                    <div><?= formatDate($enquiry['created_at'], 'd M Y, h:i A') ?></div>
                </div>
                <?php if (!empty($enquiry['assigned_to_name'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">Assigned To</label>
                    <div><?= e($enquiry['assigned_to_name']) ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($enquiry['updated_at'])): ?>
                <div>
                    <label class="text-muted small">Last Updated</label>
                    <div><?= formatDate($enquiry['updated_at'], 'd M Y, h:i A') ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($enquiry['status'] !== 'converted'): ?>
        <div class="card">
            <div class="card-header"><i class="fas fa-sync me-2"></i>Update Status</div>
            <div class="card-body">
                <form method="POST" action="<?= url('enquiries/' . $enquiry['id']) ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="first_name" value="<?= e($enquiry['first_name']) ?>">
                    <input type="hidden" name="phone" value="<?= e($enquiry['phone']) ?>">
                    <div class="mb-3">
                        <select class="form-select" name="status">
                            <?php foreach (['new','contacted','closed'] as $s): ?>
                            <option value="<?= $s ?>" <?= $enquiry['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Update Status</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
