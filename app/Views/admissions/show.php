<?php
$pageTitle = 'Admission — ' . e($admission['admission_number']);
$statusLabels = \App\Models\Admission::STATUS_LABELS;
[$statusLabel, $statusClass] = $statusLabels[$admission['status']] ?? [ucfirst($admission['status']), 'bg-secondary'];
$payColors = ['pending' => 'warning', 'partial' => 'info', 'paid' => 'success'];
$payColor  = $payColors[$admission['payment_status'] ?? 'pending'] ?? 'secondary';

$pipeline = [
    'pending'          => ['Pending',     'fas fa-clock',         '#f59e0b'],
    'document_pending' => ['Docs Review', 'fas fa-file-alt',      '#06b6d4'],
    'payment_pending'  => ['Payment',     'fas fa-money-bill',    '#3b82f6'],
    'confirmed'        => ['Confirmed',   'fas fa-check-circle',  '#10b981'],
    'enrolled'         => ['Enrolled',    'fas fa-graduation-cap','#22c55e'],
];
$pipelineOrder = array_keys($pipeline);
$curIdx = array_search($admission['status'], $pipelineOrder);

$finalFee   = (float)($admission['final_fee'] ?? $admission['total_fee'] ?? 0);
$paidAmt    = (float)($admission['paid_amount'] ?? 0);
$feePercent = $finalFee > 0 ? min(100, round($paidAmt / $finalFee * 100)) : 0;
$balance    = max(0, $finalFee - $paidAmt);

$hasInterview = !empty($admission['interview_date']);
$itvResult = $admission['interview_result'] ?? 'pending';
$itvResultColors = ['pending'=>'warning','passed'=>'success','failed'=>'danger','on_hold'=>'secondary'];
?>

<div class="page-header d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="page-title mb-1">
            <i class="fas fa-user-graduate me-2 text-primary"></i>
            <?= e($admission['first_name'] . ' ' . ($admission['last_name'] ?? '')) ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admissions') ?>">Admissions</a></li>
                <li class="breadcrumb-item active"><?= e($admission['admission_number']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <!-- Offer / Admission Letter -->
        <a href="<?= url('admissions/' . $admission['id'] . '/offer-letter') ?>" target="_blank"
           class="btn btn-outline-secondary btn-sm" title="Print Offer Letter">
            <i class="fas fa-envelope-open-text me-1"></i>Offer Letter
        </a>
        <?php if (in_array($admission['status'], ['confirmed', 'enrolled'])): ?>
        <a href="<?= url('admissions/' . $admission['id'] . '/admission-letter') ?>" target="_blank"
           class="btn btn-outline-success btn-sm" title="Print Admission Letter">
            <i class="fas fa-certificate me-1"></i>Admission Letter
        </a>
        <?php endif; ?>
        <?php if (hasPermission('admissions.edit') && !in_array($admission['status'], ['enrolled', 'cancelled', 'rejected'])): ?>
        <a href="<?= url('admissions/' . $admission['id'] . '/edit') ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- Status & Fee Summary Banner -->
<div class="card border-0 shadow-sm mb-3" style="background:linear-gradient(135deg,#f8faff 0%,#eef3ff 100%);">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-md-auto">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0"
                         style="width:52px;height:52px;font-size:1.1rem;background:<?= '#' . substr(md5($admission['first_name']), 0, 6) ?>">
                        <?= strtoupper(substr($admission['first_name'], 0, 1) . substr($admission['last_name'] ?? '', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold fs-5 lh-1"><?= e($admission['first_name'] . ' ' . ($admission['last_name'] ?? '')) ?></div>
                        <div class="text-muted small"><?= e($admission['admission_number']) ?> &bull; <?= e($admission['phone']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge <?= $statusClass ?> px-3 py-2"><?= $statusLabel ?></span>
                    <span class="badge bg-<?= $payColor ?>-subtle text-<?= $payColor ?> border border-<?= $payColor ?> px-3 py-2">
                        <?= ucfirst($admission['payment_status'] ?? 'pending') ?> Payment
                    </span>
                    <?php if ($hasInterview): ?>
                    <span class="badge bg-<?= $itvResultColors[$itvResult] ?? 'secondary' ?>-subtle text-<?= $itvResultColors[$itvResult] ?? 'secondary' ?> border px-3 py-2">
                        <i class="fas fa-user-tie me-1"></i>Interview: <?= ucfirst(str_replace('_',' ',$itvResult)) ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!empty($admission['offer_letter_sent_at'])): ?>
                    <span class="badge bg-light text-muted border px-2 py-2" title="Offer letter sent <?= date('d M Y', strtotime($admission['offer_letter_sent_at'])) ?>">
                        <i class="fas fa-envelope-open-text me-1"></i>Offer Sent
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($finalFee > 0): ?>
            <div class="col-md-auto">
                <div class="text-end">
                    <div class="small text-muted mb-1">Fee Collection</div>
                    <div class="fw-bold text-success fs-5">₹<?= number_format($paidAmt, 0) ?> <small class="text-muted fw-normal fs-6">/ ₹<?= number_format($finalFee, 0) ?></small></div>
                    <div class="progress mt-1" style="height:6px;width:160px;margin-left:auto;">
                        <div class="progress-bar <?= $feePercent>=100?'bg-success':($feePercent>0?'bg-info':'bg-warning') ?>" style="width:<?= $feePercent ?>%"></div>
                    </div>
                    <?php if ($balance > 0): ?><div class="small text-danger mt-1">Balance: ₹<?= number_format($balance, 0) ?></div><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pipeline Bar -->
<?php if (!in_array($admission['status'], ['rejected', 'cancelled'])): ?>
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body py-3 px-4">
        <div class="d-flex align-items-center">
            <?php foreach ($pipeline as $stepKey => [$stepLabel, $stepIcon, $stepColor]):
                $stepIdx = array_search($stepKey, $pipelineOrder);
                $done    = $curIdx !== false && $stepIdx < $curIdx;
                $active  = $stepKey === $admission['status'];
            ?>
            <div class="d-flex flex-column align-items-center flex-fill text-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-1"
                     style="width:36px;height:36px;<?= $active ? "background:{$stepColor};color:#fff;" : ($done ? 'background:#10b981;color:#fff;' : 'background:#e2e8f0;color:#94a3b8;') ?>">
                    <i class="<?= $stepIcon ?>" style="font-size:.85rem;"></i>
                </div>
                <div style="font-size:.7rem;<?= $active ? "color:{$stepColor};font-weight:700;" : ($done ? 'color:#10b981;' : 'color:#94a3b8;') ?>"><?= $stepLabel ?></div>
                <?php if ($active): ?><div style="font-size:.6rem;" class="text-primary fw-bold">● Current</div><?php elseif($done): ?><div style="font-size:.6rem;" class="text-success">✓ Done</div><?php endif; ?>
            </div>
            <?php if ($stepKey !== 'enrolled'): ?>
            <div class="flex-fill" style="height:2px;background:<?= $done ? '#10b981' : '#e2e8f0' ?>;margin:0 2px;margin-top:-20px;max-width:60px;"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php elseif($admission['status'] === 'rejected'): ?>
<div class="alert alert-danger mb-3 py-2"><i class="fas fa-times-circle me-2"></i><strong>Rejected</strong><?= !empty($admission['rejection_reason']) ? ' — ' . e($admission['rejection_reason']) : '' ?></div>
<?php else: ?>
<div class="alert alert-secondary mb-3 py-2"><i class="fas fa-ban me-2"></i><strong>Cancelled</strong><?= !empty($admission['cancellation_reason']) ? ' — ' . e($admission['cancellation_reason']) : '' ?></div>
<?php endif; ?>

<!-- Workflow Actions -->
<?php if (hasPermission('admissions.approve')): ?>
<div class="card mb-3 border-0 bg-light">
    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
        <span class="text-muted small fw-semibold me-1"><i class="fas fa-bolt me-1"></i>Actions:</span>
        <?php if (in_array($admission['status'], ['pending','document_pending','payment_pending'])): ?>
            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/approve') ?>" class="d-inline">
                <?= csrfField() ?>
                <button class="btn btn-sm btn-success" onclick="return confirm('Confirm this admission?')">
                    <i class="fas fa-check me-1"></i>Confirm
                </button>
            </form>
            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/mark-document-pending') ?>" class="d-inline">
                <?= csrfField() ?>
                <button class="btn btn-sm btn-info text-white"><i class="fas fa-file-alt me-1"></i>Docs Pending</button>
            </form>
            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/mark-payment-pending') ?>" class="d-inline">
                <?= csrfField() ?>
                <button class="btn btn-sm btn-primary"><i class="fas fa-money-bill me-1"></i>Pay Pending</button>
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
                <span class="badge bg-warning text-dark py-2 px-3"><i class="fas fa-lock me-1"></i><?= e($canConfirm['reason']) ?></span>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!in_array($admission['status'], ['enrolled','rejected','cancelled'])): ?>
            <button class="btn btn-sm btn-outline-danger ms-auto" data-bs-toggle="modal" data-bs-target="#rejectModal">
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

<!-- ── TABS ───────────────────────────────────────────────────────── -->
<ul class="nav nav-tabs mb-0" id="admTabs" role="tablist" style="border-bottom:none;">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-overview"><i class="fas fa-info-circle me-1"></i>Overview</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-documents">
        <i class="fas fa-file-alt me-1"></i>Documents
        <?php $docCount = count($admission['documents'] ?? []); $pendingDocs = count(array_filter($admission['documents']??[], fn($d)=>$d['status']==='pending')); ?>
        <?php if($pendingDocs): ?><span class="badge bg-warning text-dark ms-1"><?= $pendingDocs ?></span><?php elseif($docCount): ?><span class="badge bg-success ms-1"><?= $docCount ?></span><?php endif; ?>
    </a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payments"><i class="fas fa-rupee-sign me-1"></i>Payments</a></li>
    <li class="nav-item"><a class="nav-link" id="tab-interview-link" data-bs-toggle="tab" href="#tab-interview">
        <i class="fas fa-user-tie me-1"></i>Interview
        <?php if($hasInterview): ?><span class="badge bg-<?= $itvResultColors[$itvResult]??'secondary' ?> ms-1" style="font-size:.6rem;"><?= ucfirst($itvResult) ?></span><?php endif; ?>
    </a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-timeline"><i class="fas fa-history me-1"></i>Timeline</a></li>
</ul>

<div class="tab-content border border-top-0 rounded-bottom bg-white p-0 shadow-sm mb-4" id="admTabContent">

    <!-- ── TAB: OVERVIEW ──────────────────────────────────────────── -->
    <div class="tab-pane fade show active p-4" id="tab-overview">
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Personal Info -->
                <div class="card border-0 bg-light mb-3">
                    <div class="card-header bg-transparent border-0 py-2 fw-semibold">
                        <i class="fas fa-user me-2 text-primary"></i>Personal Information
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-sm-4"><div class="text-muted small">Full Name</div><div class="fw-semibold"><?= e($admission['first_name'].' '.($admission['last_name']??'')) ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Phone</div><div><?= e($admission['phone']) ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Email</div><div><?= e($admission['email']??'—') ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Gender</div><div><?= ucfirst($admission['gender']??'—') ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Date of Birth</div><div><?= $admission['date_of_birth'] ? date('d M Y',strtotime($admission['date_of_birth'])) : '—' ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Category</div><div><?= strtoupper($admission['category']??'—') ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Nationality</div><div><?= e($admission['nationality']??'Indian') ?></div></div>
                            <?php if(!empty($admission['hostel_required'])): ?>
                            <div class="col-sm-4"><div class="text-muted small">Hostel</div><div><span class="badge bg-info-subtle text-info border">Required</span></div></div>
                            <?php endif; ?>
                            <?php if(!empty($admission['transport_required'])): ?>
                            <div class="col-sm-4"><div class="text-muted small">Transport</div><div><span class="badge bg-info-subtle text-info border">Required</span></div></div>
                            <?php endif; ?>
                        </div>
                        <?php if($admission['address_line1']): ?>
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-muted small mb-1">Address</div>
                            <div><?= e($admission['address_line1']) ?><?= $admission['address_line2'] ? ', '.e($admission['address_line2']) : '' ?></div>
                            <div class="text-muted small"><?= implode(', ',array_filter([$admission['city']??'',$admission['state']??'',$admission['pincode']??''])) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if($admission['father_name']||$admission['mother_name']||$admission['guardian_name']): ?>
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-muted small mb-2 fw-semibold">Family</div>
                            <div class="row g-2">
                                <?php if($admission['father_name']): ?>
                                <div class="col-sm-4"><div class="text-muted small">Father</div><div><?= e($admission['father_name']) ?></div><?php if($admission['father_phone']): ?><div class="text-muted small"><?= e($admission['father_phone']) ?></div><?php endif; ?></div>
                                <?php endif; ?>
                                <?php if($admission['mother_name']): ?>
                                <div class="col-sm-4"><div class="text-muted small">Mother</div><div><?= e($admission['mother_name']) ?></div></div>
                                <?php endif; ?>
                                <?php if($admission['guardian_name']): ?>
                                <div class="col-sm-4"><div class="text-muted small">Guardian</div><div><?= e($admission['guardian_name']) ?></div><?php if($admission['guardian_phone']): ?><div class="text-muted small"><?= e($admission['guardian_phone']) ?></div><?php endif; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Academic Details -->
                <div class="card border-0 bg-light mb-3">
                    <div class="card-header bg-transparent border-0 py-2 fw-semibold">
                        <i class="fas fa-book me-2 text-primary"></i>Academic Details
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-sm-4"><div class="text-muted small">Department</div><div class="fw-semibold"><?= e($admission['department_name']??'—') ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Course</div><div class="fw-semibold"><?= e($admission['course_name']??'—') ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Batch</div><div><?= e($admission['batch_name']??'—') ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Academic Year</div><div><?= e($admission['academic_year_name']??'—') ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Admission Type</div><div><?= ucfirst($admission['admission_type']??'regular') ?></div></div>
                            <div class="col-sm-4"><div class="text-muted small">Quota</div><div><?= ucfirst($admission['quota']??'general') ?></div></div>
                            <?php if($admission['specialization']): ?>
                            <div class="col-sm-4"><div class="text-muted small">Specialization</div><div><?= e($admission['specialization']) ?></div></div>
                            <?php endif; ?>
                            <div class="col-sm-4"><div class="text-muted small">Semester</div><div><?= (int)($admission['current_semester']??1) ?></div></div>
                            <?php if($admission['application_source']): ?>
                            <div class="col-sm-4"><div class="text-muted small">Source</div><div><?= e($admission['application_source']) ?></div></div>
                            <?php endif; ?>
                        </div>
                        <?php if($admission['previous_qualification']): ?>
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-muted small mb-2 fw-semibold">Previous Education</div>
                            <div class="row g-2">
                                <div class="col-sm-4"><div class="text-muted small">Qualification</div><div><?= e($admission['previous_qualification']) ?></div></div>
                                <?php if($admission['previous_percentage']): ?><div class="col-sm-3"><div class="text-muted small">Percentage</div><div><?= e($admission['previous_percentage']) ?>%</div></div><?php endif; ?>
                                <?php if($admission['previous_institution']): ?><div class="col-sm-5"><div class="text-muted small">Institution</div><div><?= e($admission['previous_institution']) ?></div></div><?php endif; ?>
                                <?php if($admission['previous_year_of_passing']): ?><div class="col-sm-3"><div class="text-muted small">Year</div><div><?= e($admission['previous_year_of_passing']) ?></div></div><?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($admission['remarks']): ?>
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body py-3">
                        <div class="text-muted small fw-semibold mb-1"><i class="fas fa-comment me-1"></i>Remarks</div>
                        <div><?= nl2br(e($admission['remarks'])) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Counselor & Source -->
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="fas fa-headset text-primary"></i>
                            </div>
                            <div>
                                <div class="text-muted small">Counselor</div>
                                <div class="fw-semibold"><?= e($admission['counselor_name']??'—') ?></div>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row g-2 text-center">
                            <div class="col-6">
                                <div class="text-muted small">Applied</div>
                                <div class="fw-semibold small"><?= date('d M Y', strtotime($admission['application_date']??$admission['created_at'])) ?></div>
                            </div>
                            <?php if($admission['admission_date']): ?>
                            <div class="col-6">
                                <div class="text-muted small">Admitted</div>
                                <div class="fw-semibold small"><?= date('d M Y', strtotime($admission['admission_date'])) ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Fee Summary -->
                <div class="card border-0 bg-light mb-3">
                    <div class="card-header bg-transparent border-0 py-2 fw-semibold">
                        <i class="fas fa-rupee-sign me-2 text-success"></i>Fee Summary
                    </div>
                    <div class="card-body pt-0">
                        <?php if($finalFee > 0): ?>
                        <table class="table table-sm mb-2">
                            <tr><td class="text-muted small border-0">Total Fee</td><td class="text-end border-0 fw-semibold">₹<?= number_format((float)($admission['total_fee']??0),2) ?></td></tr>
                            <?php if((float)($admission['discount_amount']??0)>0): ?>
                            <tr><td class="text-muted small border-0">Discount</td><td class="text-end border-0 text-danger">−₹<?= number_format((float)$admission['discount_amount'],2) ?></td></tr>
                            <?php endif; ?>
                            <?php if((float)($admission['scholarship_amount']??0)>0): ?>
                            <tr><td class="text-muted small border-0">Scholarship</td><td class="text-end border-0 text-danger">−₹<?= number_format((float)$admission['scholarship_amount'],2) ?></td></tr>
                            <?php endif; ?>
                            <tr class="border-top"><td class="fw-semibold small">Final Fee</td><td class="text-end fw-bold">₹<?= number_format($finalFee,2) ?></td></tr>
                            <tr><td class="text-muted small border-0">Paid</td><td class="text-end border-0 text-success fw-semibold">₹<?= number_format($paidAmt,2) ?></td></tr>
                            <tr><td class="fw-semibold small border-0">Balance</td><td class="text-end border-0 <?= $balance>0?'text-danger fw-bold':'text-success' ?>">₹<?= number_format($balance,2) ?></td></tr>
                        </table>
                        <div class="progress" style="height:8px;border-radius:4px;">
                            <div class="progress-bar <?= $feePercent>=100?'bg-success':($feePercent>0?'bg-info':'bg-warning') ?>" style="width:<?= $feePercent ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1"><small class="text-muted"><?= $feePercent ?>% collected</small><?php if($admission['payment_due_date']): ?><small class="text-muted">Due: <?= date('d M Y', strtotime($admission['payment_due_date'])) ?></small><?php endif; ?></div>
                        <?php else: ?>
                        <div class="text-muted small">No fee details set.</div>
                        <?php endif; ?>
                        <?php if(hasPermission('admissions.edit')): ?>
                        <button class="btn btn-sm btn-outline-success w-100 mt-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="fas fa-plus me-1"></i>Record Payment
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Add Note -->
                <?php if(hasPermission('admissions.view')): ?>
                <div class="card border-0 bg-light">
                    <div class="card-header bg-transparent border-0 py-2 fw-semibold"><i class="fas fa-sticky-note me-2 text-warning"></i>Add Note</div>
                    <div class="card-body pt-0">
                        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/notes') ?>">
                            <?= csrfField() ?>
                            <textarea name="note" class="form-control form-control-sm mb-2" rows="3" placeholder="Add a note…" required></textarea>
                            <button class="btn btn-sm btn-warning w-100"><i class="fas fa-save me-1"></i>Save Note</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── TAB: DOCUMENTS ─────────────────────────────────────────── -->
    <div class="tab-pane fade p-4" id="tab-documents">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i>Document Checklist</h6>
            <?php if(hasPermission('admissions.edit')): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                <i class="fas fa-upload me-1"></i>Upload Document
            </button>
            <?php endif; ?>
        </div>

        <?php
        $docStatusColors = ['pending'=>'warning','submitted'=>'info','verified'=>'success','rejected'=>'danger'];
        $docStatusIcons  = ['pending'=>'fa-clock','submitted'=>'fa-file-upload','verified'=>'fa-check-circle','rejected'=>'fa-times-circle'];
        $requiredDocs    = ['marksheet'=>'10th/12th Marksheet','transfer_certificate'=>'Transfer Certificate','id_proof'=>'ID Proof (Aadhar/PAN)','photo'=>'Passport Photo'];
        $optionalDocs    = ['community_certificate'=>'Community Certificate','income_certificate'=>'Income Certificate','migration_certificate'=>'Migration Certificate','medical_certificate'=>'Medical Certificate'];
        $uploadedByType  = [];
        foreach ($admission['documents']??[] as $d) {
            $uploadedByType[$d['document_type'] ?? $d['type'] ?? ''] = $d;
        }
        ?>

        <div class="row g-3 mb-4">
            <div class="col-12"><div class="text-uppercase text-muted small fw-bold mb-2">Required Documents</div></div>
            <?php foreach ($requiredDocs as $dtype => $dlabel):
                $doc = $uploadedByType[$dtype] ?? null;
                $dStatus = $doc ? ($doc['status'] ?? 'submitted') : 'pending';
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="card border h-100 <?= $dStatus==='verified'?'border-success':($dStatus==='rejected'?'border-danger':'border-warning') ?>" style="border-width:2px!important;">
                    <div class="card-body p-3 text-center">
                        <div class="mb-2">
                            <i class="fas <?= $docStatusIcons[$dStatus]??'fa-clock' ?> fa-2x text-<?= $docStatusColors[$dStatus]??'warning' ?>"></i>
                        </div>
                        <div class="fw-semibold small mb-1"><?= $dlabel ?></div>
                        <span class="badge bg-<?= $docStatusColors[$dStatus]??'warning' ?>-subtle text-<?= $docStatusColors[$dStatus]??'warning' ?> border mb-2"><?= ucfirst($dStatus) ?></span>
                        <?php if($doc): ?>
                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                            <?php if(!empty($doc['file_path'])): ?>
                            <a href="/<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary" style="font-size:.7rem;padding:2px 8px;">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                            <?php endif; ?>
                            <?php if(hasPermission('admissions.approve') && $dStatus==='submitted'): ?>
                            <form method="POST" action="<?= url('admissions/'.$admission['id'].'/documents/verify') ?>" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                <input type="hidden" name="status" value="verified">
                                <button class="btn btn-xs btn-success" style="font-size:.7rem;padding:2px 8px;"><i class="fas fa-check me-1"></i>Verify</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php elseif(hasPermission('admissions.edit')): ?>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" style="font-size:.7rem;"
                                onclick="quickUpload('<?= $dtype ?>')">
                                <i class="fas fa-upload me-1"></i>Upload
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-3">
            <div class="col-12"><div class="text-uppercase text-muted small fw-bold mb-2">Optional Documents</div></div>
            <?php foreach ($optionalDocs as $dtype => $dlabel):
                $doc = $uploadedByType[$dtype] ?? null;
                $dStatus = $doc ? ($doc['status'] ?? 'submitted') : 'not_uploaded';
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="card border h-100 <?= $dStatus==='verified'?'border-success':($dStatus==='not_uploaded'?'border-light':'') ?>">
                    <div class="card-body p-3 d-flex align-items-center gap-2">
                        <i class="fas <?= $doc ? ($docStatusIcons[$dStatus]??'fa-file') : 'fa-file-circle-plus' ?> fa-lg text-<?= $doc ? ($docStatusColors[$dStatus]??'info') : 'muted' ?>"></i>
                        <div class="flex-fill">
                            <div class="small fw-semibold"><?= $dlabel ?></div>
                            <span class="badge bg-<?= $doc ? ($docStatusColors[$dStatus]??'info') : 'light' ?> text-<?= $doc ? ($docStatusColors[$dStatus]??'info') : 'muted' ?> border" style="font-size:.65rem;">
                                <?= $doc ? ucfirst($dStatus) : 'Not uploaded' ?>
                            </span>
                        </div>
                        <?php if($doc && !empty($doc['file_path'])): ?>
                        <a href="/<?= e($doc['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary" style="font-size:.7rem;padding:2px 7px;"><i class="fas fa-eye"></i></a>
                        <?php elseif(hasPermission('admissions.edit')): ?>
                        <button class="btn btn-xs btn-outline-secondary" style="font-size:.7rem;padding:2px 7px;" onclick="quickUpload('<?= $dtype ?>')"><i class="fas fa-upload"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php $otherDocs = array_filter($admission['documents']??[], fn($d)=>!array_key_exists($d['document_type']??$d['type']??'', array_merge($requiredDocs,$optionalDocs)));
        if($otherDocs): ?>
        <div class="mt-4">
            <div class="text-uppercase text-muted small fw-bold mb-2">Other Documents</div>
            <table class="table table-sm">
                <thead class="table-light"><tr><th>Type</th><th>Status</th><th>Uploaded</th><th></th></tr></thead>
                <tbody>
                <?php foreach($otherDocs as $d): ?>
                <tr>
                    <td><?= e(ucfirst(str_replace('_',' ',$d['document_type']??$d['type']??''))) ?></td>
                    <td><span class="badge bg-<?= $docStatusColors[$d['status']??'submitted']??'info' ?>-subtle text-<?= $docStatusColors[$d['status']??'submitted']??'info' ?> border"><?= ucfirst($d['status']??'submitted') ?></span></td>
                    <td><small class="text-muted"><?= date('d M Y', strtotime($d['created_at']??'now')) ?></small></td>
                    <td><?php if(!empty($d['file_path'])): ?><a href="/<?= e($d['file_path']) ?>" target="_blank" class="btn btn-xs btn-outline-primary" style="font-size:.7rem;padding:2px 7px;"><i class="fas fa-eye"></i></a><?php endif; ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── TAB: PAYMENTS ──────────────────────────────────────────── -->
    <div class="tab-pane fade p-4" id="tab-payments">
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light text-center p-3">
                    <div class="text-muted small">Total Fee</div>
                    <div class="fw-bold fs-5">₹<?= number_format((float)($admission['total_fee']??0),0) ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-success-subtle text-center p-3">
                    <div class="text-muted small">Paid</div>
                    <div class="fw-bold fs-5 text-success">₹<?= number_format($paidAmt,0) ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 <?= $balance>0?'bg-danger-subtle':'bg-success-subtle' ?> text-center p-3">
                    <div class="text-muted small">Balance</div>
                    <div class="fw-bold fs-5 <?= $balance>0?'text-danger':'text-success' ?>">₹<?= number_format($balance,0) ?></div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 bg-light text-center p-3">
                    <div class="text-muted small">Collected</div>
                    <div class="fw-bold fs-5"><?= $feePercent ?>%</div>
                    <div class="progress mt-2" style="height:6px;"><div class="progress-bar bg-<?= $feePercent>=100?'success':($feePercent>0?'info':'warning') ?>" style="width:<?= $feePercent ?>%"></div></div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Payment History</h6>
            <?php if(hasPermission('admissions.edit')): ?>
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="fas fa-plus me-1"></i>Record Payment
            </button>
            <?php endif; ?>
        </div>

        <?php if(empty($admission['payments'])): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-receipt fa-3x mb-3 opacity-25"></i><br>No payments recorded yet.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle table-hover">
                <thead class="table-light">
                    <tr><th>Receipt #</th><th>Date</th><th>Mode</th><th>Fee Head</th><th class="text-end">Amount</th><th>Ref</th><th>Recorded By</th></tr>
                </thead>
                <tbody>
                <?php foreach($admission['payments'] as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e($p['receipt_number']??'—') ?></td>
                    <td><small><?= date('d M Y', strtotime($p['payment_date']??$p['created_at'])) ?></small></td>
                    <td><span class="badge bg-light text-dark border"><?= ucfirst($p['payment_mode']??'cash') ?></span></td>
                    <td><small class="text-muted"><?= e($p['fee_head']??'—') ?></small></td>
                    <td class="text-end fw-bold text-success">₹<?= number_format((float)($p['amount']??0),2) ?></td>
                    <td><small class="text-muted font-monospace"><?= e($p['transaction_reference']??'—') ?></small></td>
                    <td><small class="text-muted"><?= e($p['collected_by_name']??'—') ?></small></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr><td colspan="4" class="fw-bold text-end">Total Paid</td><td class="text-end fw-bold text-success">₹<?= number_format($paidAmt,2) ?></td><td colspan="2"></td></tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── TAB: INTERVIEW ─────────────────────────────────────────── -->
    <div class="tab-pane fade p-4" id="tab-interview">
        <div class="row g-4">
            <div class="col-lg-6">
                <h6 class="fw-bold mb-3"><i class="fas fa-calendar-alt me-2 text-primary"></i>Schedule Interview</h6>
                <?php if(hasPermission('admissions.approve')): ?>
                <form method="POST" action="<?= url('admissions/'.$admission['id'].'/interview') ?>">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Interview Date <span class="text-danger">*</span></label>
                        <input type="date" name="interview_date" class="form-control" value="<?= e($admission['interview_date']??'') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Time</label>
                        <input type="time" name="interview_time" class="form-control" value="<?= e($admission['interview_time']??'') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mode</label>
                        <select name="interview_mode" class="form-select">
                            <?php foreach(['in_person'=>'In Person','online'=>'Online (Video)','phone'=>'Phone'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($admission['interview_mode']??'in_person')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Venue / Link</label>
                        <input type="text" name="interview_venue" class="form-control" placeholder="Room no. or meeting link" value="<?= e($admission['interview_venue']??'') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Interview Panel</label>
                        <input type="text" name="interview_panel" class="form-control" placeholder="Names of interviewers" value="<?= e($admission['interview_panel']??'') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="interview_notes" class="form-control" rows="2" placeholder="Pre-interview notes…"><?= e($admission['interview_notes']??'') ?></textarea>
                    </div>
                    <button class="btn btn-primary"><i class="fas fa-calendar-check me-1"></i><?= $hasInterview?'Update Schedule':'Schedule Interview' ?></button>
                </form>
                <?php else: ?>
                <div class="alert alert-warning">You don't have permission to schedule interviews.</div>
                <?php endif; ?>
            </div>

            <div class="col-lg-6">
                <?php if($hasInterview): ?>
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Interview Details</h6>
                        <div class="row g-2">
                            <div class="col-6"><div class="text-muted small">Date</div><div class="fw-semibold"><?= date('d M Y', strtotime($admission['interview_date'])) ?></div></div>
                            <?php if($admission['interview_time']): ?><div class="col-6"><div class="text-muted small">Time</div><div><?= date('H:i', strtotime($admission['interview_time'])) ?></div></div><?php endif; ?>
                            <div class="col-6"><div class="text-muted small">Mode</div><div><?= ucfirst(str_replace('_',' ',$admission['interview_mode']??'in_person')) ?></div></div>
                            <?php if($admission['interview_venue']): ?><div class="col-12"><div class="text-muted small">Venue / Link</div><div><?= e($admission['interview_venue']) ?></div></div><?php endif; ?>
                            <?php if($admission['interview_panel']): ?><div class="col-12"><div class="text-muted small">Panel</div><div><?= e($admission['interview_panel']) ?></div></div><?php endif; ?>
                            <div class="col-6">
                                <div class="text-muted small">Result</div>
                                <span class="badge bg-<?= $itvResultColors[$itvResult]??'secondary' ?> px-3 py-1"><?= ucfirst(str_replace('_',' ',$itvResult)) ?></span>
                            </div>
                            <?php if($admission['interview_score']): ?><div class="col-6"><div class="text-muted small">Score</div><div class="fw-bold"><?= e($admission['interview_score']) ?>/100</div></div><?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if(hasPermission('admissions.approve')): ?>
                <div class="card border-0 bg-light">
                    <div class="card-header bg-transparent border-0 fw-semibold py-2"><i class="fas fa-clipboard-check me-2 text-success"></i>Record Result</div>
                    <div class="card-body pt-0">
                        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/interview-result') ?>">
                            <?= csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Result</label>
                                <select name="interview_result" class="form-select">
                                    <?php foreach(['pending'=>'Pending','passed'=>'Passed ✓','failed'=>'Failed ✗','on_hold'=>'On Hold'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= $itvResult===$v?'selected':'' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Score (out of 100)</label>
                                <input type="number" name="interview_score" class="form-control" min="0" max="100" step="0.5" value="<?= e($admission['interview_score']??'') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="interview_notes" class="form-control" rows="2" placeholder="Post-interview notes…"><?= e($admission['interview_notes']??'') ?></textarea>
                            </div>
                            <button class="btn btn-success w-100"><i class="fas fa-save me-1"></i>Save Result</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-3x mb-3 opacity-25"></i>
                    <div>No interview scheduled yet.</div>
                    <div class="small mt-1">Use the form to schedule one.</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── TAB: TIMELINE ──────────────────────────────────────────── -->
    <div class="tab-pane fade p-4" id="tab-timeline">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Activity Timeline</h6>
        </div>
        <?php
        $typeIcons = [
            'created'      => ['fa-plus-circle',    'text-primary'],
            'status_change'=> ['fa-sync-alt',        'text-info'],
            'note_added'   => ['fa-sticky-note',     'text-warning'],
            'doc_uploaded' => ['fa-file-upload',     'text-success'],
            'doc_verified' => ['fa-check-double',    'text-success'],
            'payment'      => ['fa-money-bill-wave', 'text-success'],
            'enrolled'     => ['fa-graduation-cap',  'text-dark'],
        ];
        $timeline = $admission['timeline'] ?? [];
        ?>
        <?php if(empty($timeline)): ?>
        <div class="text-center py-5 text-muted"><i class="fas fa-history fa-3x mb-3 opacity-25"></i><br>No activity recorded yet.</div>
        <?php else: ?>
        <div class="position-relative" style="padding-left:30px;">
            <div class="position-absolute top-0 start-0 h-100" style="left:14px;width:2px;background:#e2e8f0;"></div>
            <?php foreach(array_reverse($timeline) as $entry):
                $tType = $entry['type'] ?? 'note_added';
                [$tIcon, $tColor] = $typeIcons[$tType] ?? ['fa-circle', 'text-muted'];
            ?>
            <div class="d-flex gap-3 mb-3 position-relative">
                <div class="position-absolute d-flex align-items-center justify-content-center rounded-circle bg-white border"
                     style="left:-22px;top:2px;width:26px;height:26px;">
                    <i class="fas <?= $tIcon ?> <?= $tColor ?>" style="font-size:.7rem;"></i>
                </div>
                <div class="flex-fill">
                    <div class="small fw-semibold"><?= e($entry['description'] ?? '') ?></div>
                    <?php if(!empty($entry['notes'])): ?>
                    <div class="small text-muted mt-1 bg-light rounded p-2"><?= nl2br(e($entry['notes'])) ?></div>
                    <?php endif; ?>
                    <div class="d-flex gap-2 mt-1">
                        <small class="text-muted"><?= date('d M Y H:i', strtotime($entry['created_at']??'now')) ?></small>
                        <?php if(!empty($entry['user_name'])): ?><small class="text-muted">· <?= e($entry['user_name']) ?></small><?php endif; ?>
                        <?php if(!empty($entry['new_status'])): ?><span class="badge bg-secondary-subtle text-secondary border" style="font-size:.6rem;"><?= ucfirst(str_replace('_',' ',$entry['new_status'])) ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div><!-- end tab-content -->

<!-- ── MODALS ─────────────────────────────────────────────────────── -->

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-times me-2"></i>Reject Admission</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/reject') ?>">
            <?= csrfField() ?>
            <div class="modal-body">
                <label class="form-label fw-semibold">Reason for Rejection <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="State the reason…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Reject Application</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-ban me-2"></i>Cancel Admission</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/cancel') ?>">
            <?= csrfField() ?>
            <div class="modal-body">
                <label class="form-label fw-semibold">Reason for Cancellation <span class="text-danger">*</span></label>
                <textarea name="reason" class="form-control" rows="3" required placeholder="State the reason…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-ban me-1"></i>Cancel Application</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="fas fa-upload me-2"></i>Upload Document</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/documents') ?>" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                    <select name="document_type" id="docTypeSelect" class="form-select" required>
                        <option value="">Select type…</option>
                        <optgroup label="Required">
                            <?php foreach($requiredDocs as $v=>$l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Optional">
                            <?php foreach($optionalDocs as $v=>$l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?>
                        </optgroup>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">File <span class="text-danger">*</span></label>
                    <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                    <div class="form-text">PDF, JPG, PNG up to 5 MB</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header bg-success text-white"><h5 class="modal-title"><i class="fas fa-rupee-sign me-2"></i>Record Payment</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <form method="POST" action="<?= url('admissions/'.$admission['id'].'/payments') ?>">
            <?= csrfField() ?>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="dd">Demand Draft</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Fee Head</label>
                        <input type="text" name="fee_head" class="form-control" placeholder="e.g. Tuition Fee">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Transaction / Ref #</label>
                        <input type="text" name="transaction_reference" class="form-control" placeholder="UTR, Cheque No., etc.">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold">Receipt #</label>
                        <input type="text" name="receipt_number" class="form-control" placeholder="Auto if blank">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <?php if($balance > 0): ?>
                <div class="alert alert-info mt-3 py-2 mb-0"><i class="fas fa-info-circle me-1"></i>Outstanding balance: <strong>₹<?= number_format($balance,2) ?></strong></div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Payment</button>
            </div>
        </form>
    </div></div>
</div>

<script>
// Handle hash-based tab activation
function activateTabFromHash() {
    const hash = window.location.hash;
    if (hash) {
        const tab = document.querySelector(`[href="${hash}"]`);
        if (tab) { bootstrap.Tab.getOrCreateInstance(tab).show(); }
    }
}
activateTabFromHash();
window.addEventListener('hashchange', activateTabFromHash);

// Quick upload — pre-select doc type and open modal
function quickUpload(dtype) {
    const sel = document.getElementById('docTypeSelect');
    if (sel) sel.value = dtype;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('uploadDocModal')).show();
}

// Auto-open payment modal from query param
if (new URLSearchParams(window.location.search).get('pay') === '1') {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('paymentModal')).show();
}
</script>
