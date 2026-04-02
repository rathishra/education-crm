<?php
$attColor = (float)$overall['percentage'] >= 75 ? 'success' : ((float)$overall['percentage'] >= 50 ? 'warning' : 'danger');
$monthNames = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$prevMonth = $selMonth - 1 < 1 ? 12 : $selMonth - 1;
$prevYear  = $selMonth - 1 < 1 ? $selYear - 1 : $selYear;
$nextMonth = $selMonth + 1 > 12 ? 1 : $selMonth + 1;
$nextYear  = $selMonth + 1 > 12 ? $selYear + 1 : $selYear;
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-calendar-check me-2 text-success"></i>Attendance</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; Attendance</div>
    </div>
</div>

<!-- Overall Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#d1fae5;color:#065f46"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div class="portal-stat-label">Overall</div>
                <div class="portal-stat-value"><?= number_format((float)$overall['percentage'], 1) ?>%</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#bbf7d0;color:#065f46"><i class="fas fa-check"></i></div>
            <div>
                <div class="portal-stat-label">Present</div>
                <div class="portal-stat-value"><?= (int)$overall['attended'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#fee2e2;color:#991b1b"><i class="fas fa-times"></i></div>
            <div>
                <div class="portal-stat-label">Absent</div>
                <div class="portal-stat-value"><?= (int)$overall['absent'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="portal-stat-card">
            <div class="portal-stat-icon" style="background:#e0e7ff;color:#3730a3"><i class="fas fa-list"></i></div>
            <div>
                <div class="portal-stat-label">Total Sessions</div>
                <div class="portal-stat-value"><?= (int)$overall['total'] ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Progress -->
<div class="portal-card mb-3 p-3">
    <div class="d-flex align-items-center gap-3 mb-2">
        <div class="fw-bold" style="font-size:1.5rem;color:<?= $attColor === 'success' ? '#059669' : ($attColor === 'warning' ? '#d97706' : '#dc2626') ?>"><?= number_format((float)$overall['percentage'], 1) ?>%</div>
        <div class="flex-grow-1">
            <div class="progress" style="height:12px;border-radius:6px">
                <div class="progress-bar bg-<?= $attColor ?>" style="width:<?= min(100, (float)$overall['percentage']) ?>%"></div>
            </div>
            <div class="d-flex justify-content-between mt-1" style="font-size:0.72rem;color:#94a3b8">
                <span>0%</span><span style="color:<?= $attColor === 'success' ? '#059669' : '#dc2626' ?>">Min. required: 75%</span><span>100%</span>
            </div>
        </div>
    </div>
    <?php if ((float)$overall['percentage'] < 75 && (int)$overall['total'] > 0): ?>
    <div class="alert alert-warning py-2 px-3 mb-0 mt-2" style="font-size:0.82rem">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Your attendance is below the required 75%. Please attend more classes to avoid academic penalties.
    </div>
    <?php endif; ?>
</div>

<div class="row g-3">
    <!-- Subject-wise Table -->
    <div class="col-lg-7">
        <div class="portal-card">
            <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
                <div class="fw-bold" style="color:#1e293b"><i class="fas fa-book me-2 text-success"></i>Subject-wise Attendance</div>
            </div>
            <?php if (empty($subjectWise)): ?>
            <div class="text-center py-5 text-muted"><i class="fas fa-calendar d-block fs-2 mb-2 opacity-25"></i>No attendance records found.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table portal-table mb-0">
                    <thead><tr><th>Subject</th><th class="text-center">Total</th><th class="text-center">Present</th><th class="text-center">Absent</th><th>%</th></tr></thead>
                    <tbody>
                        <?php foreach ($subjectWise as $sub):
                            $pct = (float)$sub['percentage'];
                            $c   = $pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= e($sub['subject_name']) ?></div>
                                <?php if (!empty($sub['subject_code'])): ?><div class="text-muted" style="font-size:0.72rem"><?= e($sub['subject_code']) ?></div><?php endif; ?>
                            </td>
                            <td class="text-center"><?= (int)$sub['total'] ?></td>
                            <td class="text-center text-success fw-semibold"><?= (int)$sub['attended'] ?></td>
                            <td class="text-center text-danger"><?= (int)$sub['absent'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px">
                                        <div class="progress-bar bg-<?= $c ?>" style="width:<?= min(100, $pct) ?>%"></div>
                                    </div>
                                    <span class="text-<?= $c ?> fw-semibold" style="font-size:0.8rem;min-width:38px"><?= $pct ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Monthly Calendar -->
    <div class="col-lg-5">
        <div class="portal-card">
            <div class="card-header bg-transparent border-bottom px-3 pt-3 pb-2">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="<?= url('portal/student/attendance?month=' . $prevMonth . '&year=' . $prevYear) ?>" class="btn btn-sm btn-light" style="border-radius:8px"><i class="fas fa-chevron-left"></i></a>
                    <div class="fw-bold" style="color:#1e293b;font-size:0.9rem"><?= $monthNames[$selMonth] ?> <?= $selYear ?></div>
                    <a href="<?= url('portal/student/attendance?month=' . $nextMonth . '&year=' . $nextYear) ?>" class="btn btn-sm btn-light" style="border-radius:8px"><i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="row row-cols-7 g-0 text-center mb-1">
                    <?php foreach (['M','T','W','T','F','S','S'] as $day): ?>
                    <div class="col" style="font-size:0.7rem;color:#94a3b8;font-weight:700;padding:2px"><?= $day ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="row row-cols-7 g-1 text-center">
                    <?php
                    // Leading empty cells
                    for ($d = 1; $d < $startDow; $d++): ?>
                    <div class="col"></div>
                    <?php endfor;
                    for ($day = 1; $day <= $daysInMonth; $day++):
                        $dateStr = sprintf('%04d-%02d-%02d', $selYear, $selMonth, $day);
                        $dayData = $calendarDays[$dateStr] ?? null;
                        if (!$dayData) {
                            $bg = '#f8fafc'; $color = '#cbd5e1';
                        } elseif ($dayData['has_absent'] && !$dayData['has_present']) {
                            $bg = '#fee2e2'; $color = '#991b1b';
                        } elseif ($dayData['has_absent']) {
                            $bg = '#fef3c7'; $color = '#92400e';
                        } else {
                            $bg = '#d1fae5'; $color = '#065f46';
                        }
                        $isToday = $dateStr === date('Y-m-d');
                    ?>
                    <div class="col">
                        <div class="rounded-2 d-flex align-items-center justify-content-center mx-auto"
                             style="width:28px;height:28px;font-size:0.75rem;font-weight:<?= $isToday ? '800' : '500' ?>;background:<?= $bg ?>;color:<?= $color ?>;<?= $isToday ? 'outline:2px solid #059669;outline-offset:1px' : '' ?>"
                             <?= $dayData ? 'title="' . htmlspecialchars(implode(', ', array_column($dayData['sessions'], 'subject_name'))) . '"' : '' ?>>
                            <?= $day ?>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Legend -->
                <div class="d-flex gap-3 justify-content-center mt-3 flex-wrap" style="font-size:0.72rem">
                    <span><span class="d-inline-block rounded me-1" style="width:10px;height:10px;background:#d1fae5"></span>Present</span>
                    <span><span class="d-inline-block rounded me-1" style="width:10px;height:10px;background:#fee2e2"></span>Absent</span>
                    <span><span class="d-inline-block rounded me-1" style="width:10px;height:10px;background:#fef3c7"></span>Mixed</span>
                </div>
            </div>
        </div>
    </div>
</div>
