<?php $pageTitle = 'Attendance Reports'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-line me-2"></i>Attendance Reports</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('attendance') ?>">Attendance</a></li>
                <li class="breadcrumb-item active">Monthly Report</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('attendance') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="<?= url('attendance/report') ?>" id="reportFilter">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Course</label>
                    <select class="form-select" name="course_id" onchange="this.form.submit()">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Batch</label>
                    <select class="form-select" name="batch_id">
                        <option value="">-- Select Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Month</label>
                    <input type="month" class="form-control" name="month" value="<?= e($month) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Generate Report</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($batchId && !empty($students)): ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-2"></i>Monthly Attendance — <?= date('F Y', strtotime($month . '-01')) ?></span>
        <span class="badge bg-info">Total Working Days: <?= $totalDays ?></span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th class="text-center text-success">Present</th>
                        <th class="text-center text-danger">Absent</th>
                        <th class="text-center text-warning">Late</th>
                        <th class="text-center text-info">Half Day</th>
                        <?php if ($totalDays > 0): ?>
                        <th class="text-center">Attendance %</th>
                        <th>Status</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $i => $stu):
                        $r = $report[$stu['id']] ?? ['present'=>0,'absent'=>0,'late'=>0,'half_day'=>0];
                        $attended = $r['present'] + $r['late'];
                        $pct = $totalDays > 0 ? round(($attended / $totalDays) * 100, 1) : 0;
                        $pctClass = $pct >= 75 ? 'text-success' : ($pct >= 60 ? 'text-warning' : 'text-danger');
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><code><?= e($stu['student_id_number']) ?></code></td>
                        <td class="fw-semibold">
                            <?= e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                            <?php if ($stu['roll_number']): ?><br><small class="text-muted">Roll: <?= e($stu['roll_number']) ?></small><?php endif; ?>
                        </td>
                        <td class="text-center fw-bold text-success"><?= $r['present'] ?></td>
                        <td class="text-center fw-bold text-danger"><?= $r['absent'] ?></td>
                        <td class="text-center fw-bold text-warning"><?= $r['late'] ?></td>
                        <td class="text-center fw-bold text-info"><?= $r['half_day'] ?></td>
                        <?php if ($totalDays > 0): ?>
                        <td class="text-center">
                            <span class="fw-bold <?= $pctClass ?>"><?= $pct ?>%</span>
                            <div class="progress mt-1" style="height:4px">
                                <div class="progress-bar <?= $pct >= 75 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger') ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </td>
                        <td>
                            <?php if ($pct >= 75): ?>
                                <span class="badge bg-success">Good</span>
                            <?php elseif ($pct >= 60): ?>
                                <span class="badge bg-warning text-dark">Low</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Defaulter</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if ($totalDays > 0 && count($students) > 0):
                    $avgPct = array_sum(array_map(function($stu) use ($report, $totalDays) {
                        $r = $report[$stu['id']] ?? ['present'=>0,'late'=>0];
                        return ($r['present'] + $r['late']) / $totalDays * 100;
                    }, $students)) / count($students);
                ?>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">Class Average:</td>
                        <td colspan="4"></td>
                        <td class="text-center"><?= round($avgPct, 1) ?>%</td>
                        <td></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<?php elseif ($batchId): ?>
<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No attendance records found for the selected batch and month.</div>
<?php else: ?>
<div class="alert alert-secondary text-center py-5">
    <i class="fas fa-chart-bar fa-3x mb-3 d-block text-muted"></i>
    Select a Course, Batch and Month above to generate the attendance report.
</div>
<?php endif; ?>
