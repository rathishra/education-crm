<?php
$attColor = $attPct >= 75 ? 'success' : ($attPct >= 50 ? 'warning' : 'danger');
$balanceColor = (float)($feeSummary['total_balance'] ?? 0) > 0 ? 'warning' : 'success';
$studentName  = e(($profile['first_name'] ?? $portalStudent['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? $portalStudent['last_name'] ?? ''));
?>

<!-- Page Header -->
<div class="portal-page-header d-flex align-items-start justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title">Good <?= (date('H') < 12 ? 'Morning' : (date('H') < 17 ? 'Afternoon' : 'Evening')) ?>, <?= e($profile['first_name'] ?? $portalStudent['first_name'] ?? 'Student') ?>!</h1>
        <div class="portal-breadcrumb mt-1">
            <?= e($profile['course_name'] ?? '') ?>
            <?= !empty($profile['batch_name']) ? ' &bull; ' . e($profile['batch_name']) : '' ?>
            <?= !empty($profile['section_name']) ? ' &bull; Section ' . e($profile['section_name']) : '' ?>
            <?= !empty($portalStudent['student_id_number']) ? ' &bull; ' . e($portalStudent['student_id_number']) : '' ?>
        </div>
    </div>
    <div class="text-muted small"><?= date('l, d F Y') ?></div>
</div>

<!-- STAT CARDS -->
<div class="row g-3 mb-4">
    <!-- Attendance -->
    <div class="col-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:<?= $attPct >= 75 ? '#d1fae5' : ($attPct >= 50 ? '#fef3c7' : '#fee2e2') ?>;color:<?= $attPct >= 75 ? '#065f46' : ($attPct >= 50 ? '#92400e' : '#991b1b') ?>">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="portal-stat-label">Attendance</div>
                <div class="portal-stat-value"><?= $attPct ?>%</div>
                <div class="text-muted" style="font-size:0.72rem"><?= (int)($attRow['present'] ?? 0) ?> / <?= $attTotal ?> days</div>
            </div>
        </div>
    </div>

    <!-- Fee Balance -->
    <div class="col-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:<?= (float)($feeSummary['total_balance'] ?? 0) > 0 ? '#fef3c7' : '#d1fae5' ?>;color:<?= (float)($feeSummary['total_balance'] ?? 0) > 0 ? '#92400e' : '#065f46' ?>">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div>
                <div class="portal-stat-label">Balance Due</div>
                <div class="portal-stat-value" style="font-size:1.2rem">₹<?= number_format((float)($feeSummary['total_balance'] ?? 0), 0) ?></div>
                <div class="text-muted" style="font-size:0.72rem">Paid: ₹<?= number_format((float)($feeSummary['total_paid'] ?? 0), 0) ?></div>
            </div>
        </div>
    </div>

    <!-- Upcoming Exams -->
    <div class="col-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#ede9fe;color:#5b21b6">
                <i class="fas fa-file-alt"></i>
            </div>
            <div>
                <div class="portal-stat-label">Upcoming Exams</div>
                <div class="portal-stat-value"><?= count($upcomingExams) ?></div>
                <div class="text-muted" style="font-size:0.72rem">Next 14 days</div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="col-6 col-lg-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#dbeafe;color:#1e40af">
                <i class="fas fa-bell"></i>
            </div>
            <div>
                <div class="portal-stat-label">Notifications</div>
                <div class="portal-stat-value"><?= count($notifications) ?></div>
                <div class="text-muted" style="font-size:0.72rem">
                    <?php $unread = array_filter($notifications, fn($n) => !$n['is_read']); ?>
                    <?= count($unread) ?> unread
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Left column -->
    <div class="col-lg-8">

        <!-- Upcoming Exams -->
        <div class="portal-card mb-3">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4 d-flex justify-content-between align-items-center">
                <div class="fw-bold" style="color:#1e293b"><i class="fas fa-file-alt me-2 text-purple" style="color:#7c3aed"></i>Upcoming Exams & Assessments</div>
                <a href="<?= url('portal/student/exams') ?>" class="btn btn-sm" style="background:#ede9fe;color:#7c3aed;border:none;font-size:0.78rem">View All</a>
            </div>
            <div class="card-body px-4 pt-2 pb-3">
                <?php if (empty($upcomingExams)): ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fs-3 d-block mb-2 text-success"></i>
                    No upcoming exams in the next 14 days.
                </div>
                <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($upcomingExams as $ex): ?>
                    <div class="list-group-item px-0 py-2 border-0 border-bottom">
                        <div class="d-flex align-items-start gap-3">
                            <div class="text-center flex-shrink-0" style="width:42px">
                                <div class="fw-bold" style="font-size:1rem;color:#7c3aed;line-height:1.1"><?= date('d', strtotime($ex['assessment_date'])) ?></div>
                                <div style="font-size:0.65rem;color:#94a3b8;text-transform:uppercase"><?= date('M', strtotime($ex['assessment_date'])) ?></div>
                            </div>
                            <div>
                                <div class="fw-semibold" style="font-size:0.875rem"><?= e($ex['assessment_name']) ?></div>
                                <div class="text-muted" style="font-size:0.78rem"><?= e($ex['subject_name']) ?> &bull; <?= ucfirst($ex['assessment_type']) ?> &bull; <?= (int)$ex['max_marks'] ?> marks</div>
                            </div>
                            <div class="ms-auto">
                                <?php
                                $daysLeft = (int)ceil((strtotime($ex['assessment_date']) - time()) / 86400);
                                $cls = $daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'secondary');
                                ?>
                                <span class="badge bg-<?= $cls ?>-subtle text-<?= $cls ?> border" style="font-size:0.7rem">
                                    <?= $daysLeft <= 0 ? 'Today' : "{$daysLeft}d left" ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Attendance Progress -->
        <div class="portal-card">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 px-4 d-flex justify-content-between align-items-center">
                <div class="fw-bold" style="color:#1e293b"><i class="fas fa-calendar-check me-2 text-success"></i>Attendance Overview</div>
                <a href="<?= url('portal/student/attendance') ?>" class="btn btn-sm" style="background:#d1fae5;color:#065f46;border:none;font-size:0.78rem">Details</a>
            </div>
            <div class="card-body px-4 py-3">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="font-size:2rem;font-weight:800;color:<?= $attColor === 'success' ? '#059669' : ($attColor === 'warning' ? '#d97706' : '#dc2626') ?>"><?= $attPct ?>%</div>
                    <div>
                        <div class="fw-semibold" style="font-size:0.85rem">Overall Attendance</div>
                        <div class="text-muted" style="font-size:0.78rem"><?= (int)($attRow['present'] ?? 0) ?> present out of <?= $attTotal ?> sessions</div>
                    </div>
                    <?php if ($attPct < 75): ?>
                    <div class="ms-auto">
                        <span class="badge bg-danger-subtle text-danger border px-2 py-2" style="font-size:0.75rem">
                            <i class="fas fa-exclamation-triangle me-1"></i>Below 75%
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="progress" style="height:10px;border-radius:5px">
                    <div class="progress-bar bg-<?= $attColor ?>" style="width:<?= $attPct ?>%" role="progressbar"></div>
                </div>
                <?php if ($attPct < 75): ?>
                <div class="mt-2 small text-danger"><i class="fas fa-info-circle me-1"></i>Minimum required attendance is 75%. Please attend more classes.</div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Right column -->
    <div class="col-lg-4">

        <!-- Fee Summary -->
        <div class="portal-card mb-3">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3 d-flex justify-content-between align-items-center">
                <div class="fw-bold" style="color:#1e293b;font-size:0.9rem"><i class="fas fa-rupee-sign me-2 text-warning"></i>Fee Status</div>
                <a href="<?= url('portal/student/fees') ?>" class="btn btn-sm" style="background:#fef3c7;color:#92400e;border:none;font-size:0.75rem">View</a>
            </div>
            <div class="card-body px-3 py-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Total Fee</span>
                    <span class="fw-semibold small">₹<?= number_format((float)($feeSummary['total_fees'] ?? 0), 0) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Paid</span>
                    <span class="fw-semibold small text-success">₹<?= number_format((float)($feeSummary['total_paid'] ?? 0), 0) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Balance</span>
                    <span class="fw-bold small <?= (float)($feeSummary['total_balance'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                        ₹<?= number_format((float)($feeSummary['total_balance'] ?? 0), 0) ?>
                    </span>
                </div>
                <?php
                $totalFee = (float)($feeSummary['total_fees'] ?? 0);
                $paidFee  = (float)($feeSummary['total_paid'] ?? 0);
                $feePercent = $totalFee > 0 ? min(100, round($paidFee / $totalFee * 100)) : 0;
                ?>
                <div class="progress" style="height:7px;border-radius:4px">
                    <div class="progress-bar bg-success" style="width:<?= $feePercent ?>%"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:0.72rem"><?= $feePercent ?>% paid</div>

                <?php if ($nextInstallment): ?>
                <div class="mt-2 p-2 rounded-2" style="background:#fff7ed;border:1px solid #fed7aa">
                    <div class="fw-semibold" style="font-size:0.75rem;color:#c2410c"><i class="fas fa-calendar-alt me-1"></i>Next Due</div>
                    <div class="fw-bold" style="font-size:0.9rem;color:#9a3412">₹<?= number_format((float)$nextInstallment['amount'], 0) ?></div>
                    <div class="text-muted" style="font-size:0.72rem"><?= date('d M Y', strtotime($nextInstallment['due_date'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications -->
        <div class="portal-card">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 px-3 d-flex justify-content-between align-items-center">
                <div class="fw-bold" style="color:#1e293b;font-size:0.9rem"><i class="fas fa-bell me-2 text-info"></i>Notices</div>
                <a href="<?= url('portal/student/notifications') ?>" class="btn btn-sm" style="background:#dbeafe;color:#1e40af;border:none;font-size:0.75rem">All</a>
            </div>
            <div class="card-body px-3 py-2">
                <?php if (empty($notifications)): ?>
                <div class="text-center py-3 text-muted small"><i class="fas fa-check-circle text-success me-1"></i>No notifications</div>
                <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                <div class="d-flex gap-2 py-2 border-bottom <?= !$notif['is_read'] ? 'fw-semibold' : '' ?>">
                    <div class="flex-shrink-0 mt-1">
                        <?php
                        $nIcon = ['info' => 'info-circle text-info', 'warning' => 'exclamation-triangle text-warning', 'success' => 'check-circle text-success', 'error' => 'times-circle text-danger'][$notif['type'] ?? 'info'] ?? 'bell text-primary';
                        ?>
                        <i class="fas fa-<?= $nIcon ?>" style="font-size:0.75rem"></i>
                    </div>
                    <div style="min-width:0">
                        <div style="font-size:0.8rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($notif['title']) ?></div>
                        <div class="text-muted" style="font-size:0.7rem"><?= date('d M', strtotime($notif['created_at'])) ?></div>
                    </div>
                    <?php if (!$notif['is_read']): ?>
                    <div class="ms-auto flex-shrink-0"><span style="width:7px;height:7px;border-radius:50%;background:#3b82f6;display:inline-block;margin-top:6px"></span></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
