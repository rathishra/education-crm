<?php
$pageTitle = 'Admission — ' . e($admission['admission_number']);
$statusLabels = \App\Models\Admission::STATUS_LABELS;
[$statusLabel, $statusClass] = $statusLabels[$admission['status']] ?? [ucfirst($admission['status']), 'bg-secondary'];
$payColors = ['pending'=>'warning','partial'=>'info','paid'=>'success'];
$payColor  = $payColors[$admission['payment_status'] ?? 'pending'] ?? 'secondary';

// Pipeline steps
$pipeline = [
    'pending'          => ['Pending',          'fas fa-clock'],
    'document_pending' => ['Docs Pending',      'fas fa-file-alt'],
    'payment_pending'  => ['Pay Pending',       'fas fa-money-bill'],
    'confirmed'        => ['Confirmed',         'fas fa-check-circle'],
    'enrolled'         => ['Enrolled',          'fas fa-graduation-cap'],
];
$pipelineOrder = array_keys($pipeline);
$curIdx = array_search($admission['status'], $pipelineOrder);
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-graduate me-2 text-primary"></i>Admission Application</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admissions') ?>">Admissions</a></li>
                <li class="breadcrumb-item active"><?= e($admission['admission_number']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <?php if (hasPermission('admissions.edit') && !in_array($admission['status'], ['enrolled','cancelled','rejected'])): ?>
            <a href="<?= url('admissions/' . $admission['id'] . '/edit') ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
        <?php endif; ?>
        <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<!-- Pipeline Bar (only for active applications) -->
<?php if (!in_array($admission['status'], ['draft','rejected','cancelled'])): ?>
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="d-flex align-items-center justify-content-between">
            <?php foreach ($pipeline as $stepKey => [$stepLabel, $stepIcon]): ?>
                <?php
                $stepIdx = array_search($stepKey, $pipelineOrder);
                $done    = $curIdx !== false && $stepIdx < $curIdx;
                $active  = $stepKey === $admission['status'];
                $cls     = $active ? 'text-primary fw-bold' : ($done ? 'text-success' : 'text-muted');
                ?>
                <div class="text-center flex-fill">
                    <div class="mb-1">
                        <i class="<?= $stepIcon ?> fa-lg <?= $cls ?>"></i>
                    </div>
                    <small class="<?= $cls ?>"><?= $stepLabel ?></small>
                    <?php if ($active): ?>
                        <div class="mt-1"><span class="badge bg-primary">Current</span></div>
                    <?php elseif ($done): ?>
                        <div class="mt-1"><i class="fas fa-check text-success small"></i></div>
                    <?php endif; ?>
                </div>
                <?php if ($stepKey !== 'enrolled'): ?>
                    <div class="flex-fill" style="height:2px;background:<?= $done ? '#10b981' : '#e2e8f0' ?>;margin:0 4px;margin-top:-18px;"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Workflow Actions Bar -->
<?php if (hasPermission('admissions.approve')): ?>
<div class="card mb-4 border-0 bg-light">
    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted small me-2">Actions:</span>

        <?php if (in_array($admission['status'], ['pending','document_pending','payment_pending'])): ?>
            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/approve') ?>" class="d-inline">
                <?= csrfField() ?>
                <button class="btn btn-sm btn-success" onclick="return confirm('Confirm this admission?')">
                    <i class="fas fa-check me-1"></i>Confirm
                </button>
            </form>
        <?php endif; ?>

        <?php if ($admission['status'] === 'pending'): ?>
            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/mark-document-pending') ?>" class="d-inline">
                <?= csrfField() ?>
                <button class="btn btn-sm btn-info text-white">
                    <i class="fas fa-file-alt me-1"></i>Docs Pending
                </button>
            </form>
        <?php endif; ?>

        <?php if (in_array($admission['status'], ['pending','document_pending'])): ?>
            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/mark-payment-pending') ?>" class="d-inline">
                <?= csrfField() ?>
                <button class="btn btn-sm btn-primary">
                    <i class="fas fa-money-bill me-1"></i>Pay Pending
                </button>
            </form>
        <?php endif; ?>

        <?php if ($admission['status'] === 'confirmed'): ?>
            <?php if ($canConfirm['can']): ?>
                <form method="POST" action="<?= url('admissions/'.$admission['id'].'/enroll') ?>" class="d-inline">
                    <?= csrfField() ?>
                    <button class="btn btn-sm btn-dark" onclick="return confirm('Enroll and create student record?')">
                        <i class="fas fa-graduation-cap me-1"></i>Enroll Student
                    </button>
                </form>
            <?php else: ?>
                <span class="badge bg-warning text-dark py-2 px-3">
                    <i class="fas fa-lock me-1"></i><?= e($canConfirm['reason']) ?>
                </span>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!in_array($admission['status'], ['enrolled','rejected','cancelled'])): ?>
            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="fas fa-times me-1"></i>Reject
            </button>
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cancelModal">
                <i class="fas fa-ban me-1"></i>Cancel
            </button>
        <?php endif; ?>

        <?php if (in_array($admission['status'], ['cancelled','rejected'])): ?>
            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/reopen') ?>" class="d-inline">
                <?= csrfField() ?>
                <button class="btn btn-sm btn-outline-primary" onclick="return confirm('Reopen this admission?')">
                    <i class="fas fa-redo me-1"></i>Reopen
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- MAIN CONTENT -->
    <div class="col-lg-8">

        <!-- Application Details -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i>Application Details</span>
                <div class="d-flex gap-2">
                    <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                    <span class="badge bg-<?= $payColor ?>-subtle text-<?= $payColor ?> border border-<?= $payColor ?>">
                        <?= ucfirst($admission['payment_status'] ?? 'pending') ?> Payment
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-muted small">Admission #</div>
                        <div class="fw-semibold"><?= e($admission['admission_number']) ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Applicant</div>
                        <div class="fw-semibold"><?= e($admission['first_name'] . ' ' . $admission['last_name']) ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Phone</div>
                        <div><?= e($admission['phone']) ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Email</div>
                        <div><?= e($admission['email'] ?: '—') ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Gender</div>
                        <div><?= ucfirst($admission['gender'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Date of Birth</div>
                        <div><?= $admission['date_of_birth'] ? date('d M Y', strtotime($admission['date_of_birth'])) : '—' ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Category</div>
                        <div><?= strtoupper($admission['category'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-muted small">Nationality</div>
                        <div><?= e($admission['nationality'] ?? '—') ?></div>
                    </div>
                </div>

                <?php if ($admission['address_line1']): ?>
                <hr class="my-3">
                <div class="text-muted small mb-1">Address</div>
                <div><?= e($admission['address_line1']) ?><?= $admission['address_line2'] ? ', ' . e($admission['address_line2']) : '' ?></div>
                <div><?= implode(', ', array_filter([$admission['city'] ?? '', $admission['state'] ?? '', $admission['pincode'] ?? ''])) ?></div>
                <?php endif; ?>

                <?php if ($admission['father_name'] || $admission['mother_name'] || $admission['guardian_name']): ?>
                <hr class="my-3">
                <div class="row g-2">
                    <?php if ($admission['father_name']): ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Father</div>
                        <div><?= e($admission['father_name']) ?> <?= $admission['father_phone'] ? '<small class="text-muted">('.e($admission['father_phone']).')</small>' : '' ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($admission['mother_name']): ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Mother</div>
                        <div><?= e($admission['mother_name']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($admission['guardian_name']): ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Guardian</div>
                        <div><?= e($admission['guardian_name']) ?> <?= $admission['guardian_phone'] ? '<small class="text-muted">('.e($admission['guardian_phone']).')</small>' : '' ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Academic Details -->
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-book me-2"></i>Academic Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted small">Department</div>
                        <div class="fw-semibold"><?= e($admission['department_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Course</div>
                        <div class="fw-semibold"><?= e($admission['course_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Batch</div>
                        <div><?= e($admission['batch_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Academic Year</div>
                        <div><?= e($admission['academic_year_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Admission Type</div>
                        <div><?= ucfirst($admission['admission_type'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Quota</div>
                        <div><?= ucfirst($admission['quota'] ?? 'general') ?></div>
                    </div>
                    <?php if ($admission['specialization']): ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Specialization</div>
                        <div><?= e($admission['specialization']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Semester</div>
                        <div><?= (int)($admission['current_semester'] ?? 1) ?></div>
                    </div>
                    <?php if ($admission['counselor_name']): ?>
                    <div class="col-md-4">
                        <div class="text-muted small">Counselor</div>
                        <div><?= e($admission['counselor_name']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($admission['previous_qualification']): ?>
                <hr class="my-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="text-muted small">Previous Qualification</div>
                        <div><?= e($admission['previous_qualification']) ?></div>
                    </div>
                    <?php if ($admission['previous_percentage']): ?>
                    <div class="col-md-3">
                        <div class="text-muted small">Percentage</div>
                        <div><?= e($admission['previous_percentage']) ?>%</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($admission['previous_institution']): ?>
                    <div class="col-md-5">
                        <div class="text-muted small">Institution</div>
                        <div><?= e($admission['previous_institution']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Documents -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-alt me-2"></i>Documents</span>
                <?php if (hasPermission('admissions.edit')): ?>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                        <i class="fas fa-upload me-1"></i>Upload
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($admission['documents'])): ?>
                    <div class="text-center py-4 text-muted"><i class="fas fa-folder-open fa-2x mb-2"></i><br>No documents yet</div>
                <?php else: ?>
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Document</th>
                                <th>Required</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <?php if (hasPermission('admissions.approve')): ?><th></th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($admission['documents'] as $doc):
                            $vColors = ['pending'=>'warning','verified'=>'success','rejected'=>'danger'];
                            $vColor  = $vColors[$doc['verification_status']] ?? 'secondary';
                        ?>
                            <tr>
                                <td>
                                    <?= e(ucwords(str_replace('_',' ',$doc['document_type']))) ?>
                                    <?php if ($doc['document_name'] && $doc['document_name'] !== ucwords(str_replace('_',' ',$doc['document_type']))): ?>
                                        <br><small class="text-muted"><?= e($doc['document_name']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $doc['is_required'] ? '<span class="badge bg-danger-subtle text-danger border border-danger">Required</span>' : '<span class="badge bg-light text-muted border">Optional</span>' ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $vColor ?>">
                                        <?= $doc['is_submitted'] ? ucfirst($doc['verification_status']) : 'Not Submitted' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($doc['is_submitted'] && $doc['file_path']): ?>
                                        <a href="/<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-secondary">
                                            <i class="fas fa-download me-1"></i><?= e($doc['original_filename'] ?? 'View') ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (hasPermission('admissions.approve')): ?>
                                <td>
                                    <?php if ($doc['is_submitted'] && $doc['verification_status'] === 'pending'): ?>
                                        <button class="btn btn-xs btn-success" onclick="verifyDoc(<?= $doc['id'] ?>,'verified')" title="Verify">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-xs btn-danger" onclick="verifyDoc(<?= $doc['id'] ?>,'rejected')" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payments -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-rupee-sign me-2"></i>Payments</span>
                <?php if (hasPermission('admissions.edit')): ?>
                    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="fas fa-plus me-1"></i>Record Payment
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Fee summary -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Total Fee</div>
                        <div class="fw-semibold">₹<?= number_format((float)($admission['total_fee'] ?? 0), 2) ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Discount + Scholarship</div>
                        <div class="fw-semibold text-success">
                            ₹<?= number_format((float)($admission['discount_amount'] ?? 0) + (float)($admission['scholarship_amount'] ?? 0), 2) ?>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Final Fee</div>
                        <div class="fw-semibold text-primary">₹<?= number_format((float)($admission['final_fee'] ?? 0), 2) ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Balance Due</div>
                        <div class="fw-semibold text-<?= (float)($admission['balance_amount'] ?? 0) > 0 ? 'danger' : 'success' ?>">
                            ₹<?= number_format((float)($admission['balance_amount'] ?? 0), 2) ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($admission['payments'])): ?>
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Date</th><th>Amount</th><th>Mode</th><th>Receipt #</th><th>Collected By</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($admission['payments'] as $pay): ?>
                            <tr>
                                <td><?= date('d M Y', strtotime($pay['payment_date'])) ?></td>
                                <td class="fw-semibold text-success">₹<?= number_format((float)$pay['amount'], 2) ?></td>
                                <td><?= ucfirst($pay['payment_mode']) ?></td>
                                <td><?= e($pay['receipt_number'] ?: '—') ?></td>
                                <td><?= e($pay['collector_name'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center py-3 text-muted small">No payments recorded yet</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-history me-2"></i>Timeline</span>
                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                    <i class="fas fa-sticky-note me-1"></i>Add Note
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (empty($admission['timeline'])): ?>
                    <div class="text-center py-4 text-muted"><small>No timeline events</small></div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                    <?php foreach ($admission['timeline'] as $tl):
                        $tlIcons = [
                            'created'             => ['fas fa-plus-circle','text-success'],
                            'status_change'       => ['fas fa-exchange-alt','text-primary'],
                            'document_uploaded'   => ['fas fa-upload','text-info'],
                            'document_verified'   => ['fas fa-check-circle','text-success'],
                            'document_rejected'   => ['fas fa-times-circle','text-danger'],
                            'payment_recorded'    => ['fas fa-rupee-sign','text-success'],
                            'note_added'          => ['fas fa-sticky-note','text-warning'],
                            'approved'            => ['fas fa-check','text-success'],
                            'rejected'            => ['fas fa-times','text-danger'],
                            'confirmed'           => ['fas fa-check-double','text-success'],
                            'enrolled'            => ['fas fa-graduation-cap','text-primary'],
                            'cancelled'           => ['fas fa-ban','text-secondary'],
                            'reopened'            => ['fas fa-redo','text-info'],
                        ];
                        [$tlIcon, $tlColor] = $tlIcons[$tl['event_type']] ?? ['fas fa-circle','text-muted'];
                    ?>
                        <li class="list-group-item py-2">
                            <div class="d-flex align-items-start gap-3">
                                <div class="mt-1"><i class="<?= $tlIcon ?> <?= $tlColor ?>"></i></div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold small"><?= e($tl['title']) ?></span>
                                        <span class="text-muted small"><?= date('d M Y, h:i A', strtotime($tl['created_at'])) ?></span>
                                    </div>
                                    <?php if ($tl['description']): ?><div class="text-muted small"><?= e($tl['description']) ?></div><?php endif; ?>
                                    <?php if ($tl['performed_by_name']): ?><div class="text-muted small">By: <?= e($tl['performed_by_name']) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- SIDEBAR -->
    <div class="col-lg-4">

        <!-- Status & Meta -->
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-info me-2"></i>Summary</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Status</dt>
                    <dd class="col-7"><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></dd>
                    <dt class="col-5 text-muted">Payment</dt>
                    <dd class="col-7"><span class="badge bg-<?= $payColor ?>"><?= ucfirst($admission['payment_status'] ?? 'pending') ?></span></dd>
                    <dt class="col-5 text-muted">Applied On</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($admission['application_date'] ?? $admission['created_at'])) ?></dd>
                    <?php if ($admission['admission_date']): ?>
                    <dt class="col-5 text-muted">Admission Date</dt>
                    <dd class="col-7"><?= date('d M Y', strtotime($admission['admission_date'])) ?></dd>
                    <?php endif; ?>
                    <?php if ($admission['counselor_name']): ?>
                    <dt class="col-5 text-muted">Counselor</dt>
                    <dd class="col-7"><?= e($admission['counselor_name']) ?></dd>
                    <?php endif; ?>
                    <?php if ($admission['created_by_name']): ?>
                    <dt class="col-5 text-muted">Created By</dt>
                    <dd class="col-7"><?= e($admission['created_by_name']) ?></dd>
                    <?php endif; ?>
                    <?php if ($admission['approved_by_name']): ?>
                    <dt class="col-5 text-muted">Approved By</dt>
                    <dd class="col-7"><?= e($admission['approved_by_name']) ?></dd>
                    <?php endif; ?>
                    <?php if ($admission['student_id_number']): ?>
                    <dt class="col-5 text-muted">Student ID</dt>
                    <dd class="col-7">
                        <a href="<?= url('students/' . $admission['student_id']) ?>" class="text-primary fw-semibold">
                            <?= e($admission['student_id_number']) ?>
                        </a>
                    </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Lead / Enquiry Link -->
        <?php if ($admission['lead_number'] || ($admission['enquiry_ref_id'] ?? null)): ?>
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-link me-2"></i>Source</div>
            <div class="card-body">
                <?php if ($admission['lead_number']): ?>
                    <a href="<?= url('leads/' . $admission['lead_id']) ?>" class="btn btn-sm btn-outline-primary w-100 mb-2">
                        <i class="fas fa-funnel-dollar me-1"></i>Lead: <?= e($admission['lead_number']) ?>
                    </a>
                <?php endif; ?>
                <?php if ($admission['enquiry_ref_id'] ?? null): ?>
                    <a href="<?= url('enquiries/' . $admission['enquiry_ref_id']) ?>" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="fas fa-question-circle me-1"></i>View Enquiry
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rejection Reason -->
        <?php if ($admission['rejection_reason'] && in_array($admission['status'], ['rejected','cancelled'])): ?>
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-exclamation-circle me-2"></i><?= ucfirst($admission['status']) ?> Reason
            </div>
            <div class="card-body">
                <p class="mb-0 small"><?= e($admission['rejection_reason']) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Remarks -->
        <?php if ($admission['remarks']): ?>
        <div class="card mb-4">
            <div class="card-header"><i class="fas fa-comment me-2"></i>Remarks</div>
            <div class="card-body"><p class="mb-0 small"><?= e($admission['remarks']) ?></p></div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ================================================================ -->
<!-- MODALS                                                            -->
<!-- ================================================================ -->

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/reject') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header"><h5 class="modal-title">Reject Admission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="Provide a clear reason…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/cancel') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header"><h5 class="modal-title">Cancel Admission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Reason for Cancellation</label>
                <textarea name="reason" class="form-control" rows="3" placeholder="Optional reason…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-warning">Cancel Admission</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/documents') ?>" enctype="multipart/form-data" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header"><h5 class="modal-title">Upload Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Document Type <span class="text-danger">*</span></label>
                    <select name="document_type" class="form-select" required>
                        <option value="">Select type…</option>
                        <?php foreach (['marksheet'=>'Marksheet','transfer_certificate'=>'Transfer Certificate','id_proof'=>'ID Proof','community_certificate'=>'Community Certificate','income_certificate'=>'Income Certificate','photo'=>'Photo','migration_certificate'=>'Migration Certificate','character_certificate'=>'Character Certificate','medical_certificate'=>'Medical Certificate','other'=>'Other'] as $v=>$l): ?>
                            <option value="<?= $v ?>"><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-0">
                    <label class="form-label">File <span class="text-danger">*</span></label>
                    <input type="file" name="document_file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <div class="form-text">PDF, JPG, PNG, DOC accepted (max 5MB)</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/payments') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select" required>
                            <?php foreach (['cash'=>'Cash','cheque'=>'Cheque','dd'=>'DD','online'=>'Online','upi'=>'UPI','card'=>'Card','bank_transfer'=>'Bank Transfer'] as $v=>$l): ?>
                                <option value="<?= $v ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Receipt Number</label>
                        <input type="text" name="receipt_number" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Transaction Reference</label>
                        <input type="text" name="transaction_reference" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Fee Head</label>
                        <input type="text" name="fee_head" class="form-control" placeholder="e.g. Tuition Fee, Exam Fee">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Record Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/notes') ?>" class="modal-content">
            <?= csrfField() ?>
            <div class="modal-header"><h5 class="modal-title">Add Note</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <label class="form-label">Note <span class="text-danger">*</span></label>
                <textarea name="note" class="form-control" rows="4" required placeholder="Enter your note…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Add Note</button>
            </div>
        </form>
    </div>
</div>

<!-- Verify Document form (hidden, JS-triggered) -->
<form id="verifyDocForm" method="POST" action="<?= url('admissions/'.$admission['id'].'/documents/verify') ?>" class="d-none">
    <?= csrfField() ?>
    <input type="hidden" name="document_id" id="verifyDocId">
    <input type="hidden" name="status" id="verifyDocStatus">
    <input type="hidden" name="notes" value="">
</form>

<script>
function verifyDoc(docId, status) {
    if (!confirm('Mark this document as ' + status + '?')) return;
    document.getElementById('verifyDocId').value = docId;
    document.getElementById('verifyDocStatus').value = status;
    document.getElementById('verifyDocForm').submit();
}
</script>
