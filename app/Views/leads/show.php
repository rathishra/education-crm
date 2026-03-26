<?php $pageTitle = 'Lead Details - ' . $lead['lead_number']; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user me-2"></i><?= e($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('leads') ?>">Leads</a></li>
                <li class="breadcrumb-item active"><?= e($lead['lead_number']) ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (hasPermission('leads.edit') && !$lead['is_won']): ?>
        <a href="<?= url('leads/' . $lead['id'] . '/convert') ?>" class="btn btn-success me-1"
           onclick="return confirm('Convert this lead to admission?')">
            <i class="fas fa-exchange-alt me-1"></i>Convert
        </a>
        <?php endif; ?>
        <?php if (hasPermission('leads.edit')): ?>
        <a href="<?= url('leads/' . $lead['id'] . '/edit') ?>" class="btn btn-primary me-1"><i class="fas fa-edit me-1"></i>Edit</a>
        <?php endif; ?>
        <a href="<?= url('leads') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column - Lead Info -->
    <div class="col-xl-4">
        <!-- Profile Card -->
        <div class="card">
            <div class="card-body text-center py-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;font-size:1.8rem">
                    <?= strtoupper(substr($lead['first_name'], 0, 1)) ?>
                </div>
                <h5 class="mb-1"><?= e($lead['first_name'] . ' ' . ($lead['last_name'] ?? '')) ?></h5>
                <p class="text-muted mb-2"><code><?= e($lead['lead_number']) ?></code></p>
                <span class="badge" style="background-color:<?= e($lead['status_color']) ?>;font-size:0.85rem">
                    <?= e($lead['status_name']) ?>
                </span>
                <span class="badge bg-secondary ms-1"><?= ucfirst($lead['priority']) ?></span>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted"><i class="fas fa-phone me-1"></i>Phone</span>
                    <a href="tel:<?= e($lead['phone']) ?>"><?= e($lead['phone']) ?></a>
                </div>
                <?php if ($lead['alternate_phone']): ?>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted"><i class="fas fa-phone me-1"></i>Alt Phone</span>
                    <span><?= e($lead['alternate_phone']) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($lead['email']): ?>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted"><i class="fas fa-envelope me-1"></i>Email</span>
                    <a href="mailto:<?= e($lead['email']) ?>"><?= e($lead['email']) ?></a>
                </div>
                <?php endif; ?>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Source</span>
                    <span><?= e($lead['source_name'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Course</span>
                    <span><?= e($lead['course_name'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Assigned To</span>
                    <span><?= e($lead['assigned_name'] ?? 'Unassigned') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Created</span>
                    <span><?= formatDateTime($lead['created_at']) ?></span>
                </div>
                <?php if ($lead['last_contacted_at']): ?>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Last Contact</span>
                    <span><?= timeAgo($lead['last_contacted_at']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header"><i class="fas fa-bolt me-2"></i>Quick Actions</div>
            <div class="card-body">
                <!-- Status Change -->
                <?php if (hasPermission('leads.edit')): ?>
                <form method="POST" action="<?= url('leads/' . $lead['id'] . '/status') ?>" class="mb-3">
                    <?= csrfField() ?>
                    <label class="form-label small">Change Status</label>
                    <div class="input-group input-group-sm">
                        <select class="form-select" name="status_id">
                            <?php foreach ($statuses as $st): ?>
                            <option value="<?= $st['id'] ?>" <?= $lead['lead_status_id'] == $st['id'] ? 'selected' : '' ?>>
                                <?= e($st['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </form>
                <?php endif; ?>

                <!-- Assign -->
                <?php if (hasPermission('leads.assign')): ?>
                <form method="POST" action="<?= url('leads/' . $lead['id'] . '/assign') ?>" class="mb-3">
                    <?= csrfField() ?>
                    <label class="form-label small">Assign To</label>
                    <div class="input-group input-group-sm">
                        <select class="form-select" name="assigned_to">
                            <option value="">Unassigned</option>
                            <?php foreach ($counselors as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $lead['assigned_to'] == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Assign</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Academic Info -->
        <?php if ($lead['qualification'] || $lead['school_college']): ?>
        <div class="card">
            <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>Academic Info</div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Qualification</span>
                    <span><?= e($lead['qualification'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Percentage</span>
                    <span><?= $lead['percentage'] ? $lead['percentage'] . '%' : '-' ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Year</span>
                    <span><?= e($lead['passing_year'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Institution</span>
                    <span><?= e($lead['school_college'] ?? '-') ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if ($lead['notes']): ?>
        <div class="card">
            <div class="card-header"><i class="fas fa-sticky-note me-2"></i>Notes</div>
            <div class="card-body"><p class="mb-0"><?= nl2br(e($lead['notes'])) ?></p></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column - Activity & Follow-ups -->
    <div class="col-xl-8">
        <!-- Add Activity -->
        <?php if (hasPermission('leads.edit')): ?>
        <div class="card">
            <div class="card-header"><i class="fas fa-plus-circle me-2"></i>Add Activity</div>
            <div class="card-body">
                <form method="POST" action="<?= url('leads/' . $lead['id'] . '/activity') ?>" class="ajax-form">
                    <?= csrfField() ?>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" name="type">
                                <option value="note">Note</option>
                                <option value="call">Phone Call</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="meeting">Meeting</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control form-control-sm" name="title" placeholder="Title/Summary" required>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" name="description" placeholder="Details (optional)">
                                <button class="btn btn-primary" type="submit"><i class="fas fa-plus me-1"></i>Add</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Follow-ups -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-phone-alt me-2"></i>Follow-ups</span>
                <a href="<?= url('followups/create?lead_id=' . $lead['id']) ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Schedule
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lead['followups'])): ?>
                <p class="text-center text-muted py-3 mb-0">No follow-ups scheduled</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Type</th><th>Subject</th><th>Scheduled</th><th>Assigned</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lead['followups'] as $fu): ?>
                        <tr>
                            <td><span class="badge bg-info"><?= ucfirst($fu['type']) ?></span></td>
                            <td><?= e($fu['subject']) ?></td>
                            <td><small><?= formatDateTime($fu['scheduled_at']) ?></small></td>
                            <td><small><?= e($fu['assigned_name'] ?? '-') ?></small></td>
                            <td>
                                <span class="badge bg-<?= match($fu['status']) {
                                    'completed' => 'success', 'missed' => 'danger', 'pending' => 'warning', default => 'secondary'
                                } ?>">
                                    <?= ucfirst($fu['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card">
            <div class="card-header"><i class="fas fa-history me-2"></i>Activity Timeline</div>
            <div class="card-body">
                <?php if (empty($lead['activities'])): ?>
                <p class="text-center text-muted mb-0">No activities yet</p>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($lead['activities'] as $act): ?>
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge bg-<?= match($act['type']) {
                                    'call' => 'success', 'email' => 'info', 'sms' => 'primary',
                                    'whatsapp' => 'success', 'meeting' => 'warning',
                                    'status_change' => 'danger', 'assignment' => 'purple',
                                    default => 'secondary'
                                } ?> me-2"><?= ucfirst(str_replace('_', ' ', $act['type'])) ?></span>
                                <strong><?= e($act['title']) ?></strong>
                                <?php if ($act['description']): ?>
                                <p class="small text-muted mb-0 mt-1"><?= e($act['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="timeline-date">
                            <?= timeAgo($act['created_at']) ?>
                            <?php if ($act['user_name']): ?>
                            &middot; by <?= e($act['user_name']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Documents -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-paperclip me-2"></i>Documents</span>
                <?php if (hasPermission('documents.upload')): ?>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                    <i class="fas fa-upload me-1"></i>Upload
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lead['documents'])): ?>
                <p class="text-center text-muted py-3 mb-0">No documents uploaded</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Document</th><th>Type</th><th>Size</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lead['documents'] as $doc): ?>
                        <tr>
                            <td><?= e($doc['title']) ?></td>
                            <td><span class="badge bg-secondary"><?= e($doc['document_type']) ?></span></td>
                            <td><small><?= number_format(($doc['file_size'] ?? 0) / 1024, 1) ?> KB</small></td>
                            <td><small><?= formatDate($doc['created_at']) ?></small></td>
                            <td>
                                <a href="<?= url('documents/' . $doc['id'] . '/download') ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-download"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= url('documents/upload') ?>" enctype="multipart/form-data" class="ajax-form">
                <?= csrfField() ?>
                <input type="hidden" name="documentable_type" value="lead">
                <input type="hidden" name="documentable_id" value="<?= $lead['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Document Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Type</label>
                        <select class="form-select" name="document_type" required>
                            <option value="marksheet">Marksheet</option>
                            <option value="transfer_cert">Transfer Certificate</option>
                            <option value="aadhar">Aadhar Card</option>
                            <option value="photo">Photo</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">File</label>
                        <input type="file" class="form-control" name="file" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
