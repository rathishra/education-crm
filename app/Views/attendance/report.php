<?php $pageTitle = 'Attendance Report'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-chart-bar me-2 text-primary"></i>Monthly Attendance Report</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('attendance') ?>">Attendance</a></li>
                <li class="breadcrumb-item active">Monthly Report</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('attendance') ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2 text-primary"></i>Report Filters
    </div>
    <div class="card-body">
        <form method="GET" action="<?= url('attendance/report') ?>" id="reportFilter">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Course</label>
                    <select class="form-select" name="course_id" onchange="this.form.submit()">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Batch</label>
                    <select class="form-select" name="batch_id">
                        <option value="">-- Select Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Month</label>
                    <input type="month" class="form-control" name="month" value="<?= e($month) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($batchId && !empty($students)): ?>

<?php
// Pre-compute stats for summary cards
$totalStudents = count($students);
$defaulters    = 0;
$lowAttendance = 0;
$goodAttendance= 0;
$totalAttended = 0;

foreach ($students as $stu) {
    $r = $report[$stu['id']] ?? ['present'=>0,'absent'=>0,'late'=>0,'half_day'=>0];
    $attended = $r['present'] + $r['late'];
    $totalAttended += $attended;
    $pct = $totalDays > 0 ? ($attended / $totalDays) * 100 : 0;
    if ($pct >= 75) $goodAttendance++;
    elseif ($pct >= 60) $lowAttendance++;
    else $defaulters++;
}
$avgPct = $totalDays > 0 && $totalStudents > 0 ? round($totalAttended / ($totalDays * $totalStudents) * 100, 1) : 0;
?>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $totalDays ?></div>
                <div class="stat-label">Working Days</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-emerald py-3">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $goodAttendance ?></div>
                <div class="stat-label">≥75% Attendance</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-amber py-3">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $lowAttendance ?></div>
                <div class="stat-label">Low Attendance (60–74%)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-rose py-3">
            <div class="stat-icon"><i class="fas fa-user-times"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $defaulters ?></div>
                <div class="stat-label">Defaulters (&lt;60%)</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>
            <i class="fas fa-table me-2 text-primary"></i>
            Attendance — <strong><?= date('F Y', strtotime($month . '-01')) ?></strong>
        </span>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-soft-info">Working Days: <?= $totalDays ?></span>
            <span class="badge bg-soft-primary">Class Avg: <?= $avgPct ?>%</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Student</th>
                        <th class="text-center" style="width:80px"><span class="text-success">Present</span></th>
                        <th class="text-center" style="width:80px"><span class="text-danger">Absent</span></th>
                        <th class="text-center" style="width:80px"><span class="text-warning">Late</span></th>
                        <th class="text-center" style="width:80px"><span class="text-info">Half</span></th>
                        <?php if ($totalDays > 0): ?>
                        <th style="width:160px">Attendance</th>
                        <th style="width:100px" class="text-center">Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $i => $stu):
                        $r        = $report[$stu['id']] ?? ['present'=>0,'absent'=>0,'late'=>0,'half_day'=>0];
                        $attended = $r['present'] + $r['late'];
                        $pct      = $totalDays > 0 ? round(($attended / $totalDays) * 100, 1) : 0;
                        $pctClass = $pct >= 75 ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
                        $badgeMap = ['success' => 'Good', 'warning' => 'Low', 'danger' => 'Defaulter'];
                    ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td>
                            <div class="fw-semibold text-dark"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></div>
                            <div class="small text-muted">
                                <code class="text-xs"><?= e($stu['student_id_number']) ?></code>
                                <?php if ($stu['roll_number']): ?>&bull; Roll <?= e($stu['roll_number']) ?><?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center fw-bold text-success"><?= $r['present'] ?></td>
                        <td class="text-center fw-bold text-danger"><?= $r['absent'] ?></td>
                        <td class="text-center fw-bold text-warning"><?= $r['late'] ?></td>
                        <td class="text-center fw-bold text-info"><?= $r['half_day'] ?></td>
                        <?php if ($totalDays > 0): ?>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <div class="progress" style="height:6px;border-radius:3px">
                                        <div class="progress-bar bg-<?= $pctClass ?>" style="width:<?= min($pct,100) ?>%"></div>
                                    </div>
                                </div>
                                <span class="fw-bold text-<?= $pctClass ?>" style="min-width:42px;font-size:.82rem"><?= $pct ?>%</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-soft-<?= $pctClass ?>"><?= $badgeMap[$pctClass] ?></span>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if ($totalDays > 0 && $totalStudents > 0): ?>
                <tfoot>
                    <tr class="fw-bold" style="background:#f8fafc">
                        <td colspan="6" class="text-end text-muted">Class Average</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <div class="progress" style="height:6px;border-radius:3px">
                                        <div class="progress-bar bg-<?= $avgPct >= 75 ? 'success' : ($avgPct >= 60 ? 'warning' : 'danger') ?>" style="width:<?= min($avgPct,100) ?>%"></div>
                                    </div>
                                </div>
                                <span class="fw-bold text-<?= $avgPct >= 75 ? 'success' : ($avgPct >= 60 ? 'warning' : 'danger') ?>" style="min-width:42px;font-size:.82rem"><?= $avgPct ?>%</span>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php elseif ($batchId): ?>
<div class="card">
    <div class="card-body text-center py-5">
        <div style="font-size:3rem;opacity:.18;margin-bottom:.75rem"><i class="fas fa-calendar-times"></i></div>
        <h6 class="text-muted fw-semibold">No Attendance Records</h6>
        <p class="text-muted small mb-0">No attendance records found for <?= date('F Y', strtotime($month . '-01')) ?>.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="card-body text-center py-5">
        <div style="font-size:3.5rem;opacity:.18;margin-bottom:.75rem"><i class="fas fa-chart-bar"></i></div>
        <h5 class="text-muted fw-semibold mb-1">Generate Attendance Report</h5>
        <p class="text-muted small mb-0">Select a Course, Batch and Month above to generate the monthly report.</p>
    </div>
</div>
<?php endif; ?>
