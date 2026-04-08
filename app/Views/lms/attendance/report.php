<style>
.rpt-stat { background:#f8f7ff; border-radius:12px; border:1px solid #e8e3ff; padding:.8rem 1rem; text-align:center; }
.rpt-stat .val { font-size:1.5rem; font-weight:900; color:#4f46e5; line-height:1; }
.rpt-stat .lbl { font-size:.68rem; color:#64748b; margin-top:.2rem; }
.att-bar { height:10px; border-radius:10px; overflow:hidden; display:flex; background:#f1f5f9; }
.below-thr { background:#fff5f5; border-left:3px solid #ef4444; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <a href="<?= url('elms/attendance') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
        <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-chart-pie me-2 text-primary"></i>Attendance Report</h4>
    </div>
    <?php if ($courseId && !empty($summary)): ?>
    <button class="btn btn-outline-secondary btn-sm" style="border-radius:8px" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
    <?php endif; ?>
</div>

<!-- Filter bar -->
<form method="GET" class="bg-white border rounded-3 p-3 mb-4" style="border-color:#e8e3ff!important">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-5 col-md-4">
            <label class="form-label small fw-semibold mb-1">Course <span class="text-danger">*</span></label>
            <select name="course" class="form-select form-select-sm" required>
                <option value="">— Select Course —</option>
                <?php foreach ($myCourses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-sm-3 col-md-2">
            <label class="form-label small fw-semibold mb-1">Month</label>
            <input type="month" name="month" value="<?= e($month) ?>" class="form-control form-control-sm">
        </div>
        <div class="col-6 col-sm-3 col-md-2">
            <label class="form-label small fw-semibold mb-1">Min. Threshold %</label>
            <input type="number" name="threshold" value="<?= (int)$threshold ?>" min="1" max="100" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm" style="border-radius:8px"><i class="fas fa-filter me-1"></i>Generate</button>
        </div>
    </div>
</form>

<?php if (empty($summary)): ?>
<div class="text-center py-5 text-muted">
    <i class="fas fa-chart-pie" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">Select a course and generate to view the report</p>
</div>
<?php else: ?>

<?php
$sessionStats = $summary['sessionStats'] ?? [];
$studentStats = $summary['studentStats'] ?? [];
$totalSess    = count($sessionStats);
$avgPct       = $studentStats ? round(array_sum(array_column($studentStats,'pct')) / count($studentStats), 1) : 0;
$belowCount   = count(array_filter($studentStats, fn($s) => $s['below_threshold']));
?>

<!-- Overview stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="rpt-stat">
            <div class="val"><?= $totalSess ?></div>
            <div class="lbl">Sessions</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rpt-stat">
            <div class="val"><?= count($studentStats) ?></div>
            <div class="lbl">Students</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rpt-stat">
            <div class="val"><?= $avgPct ?>%</div>
            <div class="lbl">Avg Attendance</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rpt-stat">
            <div class="val" style="color:<?= $belowCount>0?'#ef4444':'#22c55e' ?>"><?= $belowCount ?></div>
            <div class="lbl">Below <?= $threshold ?>% threshold</div>
        </div>
    </div>
</div>

<?php if (!empty($sessionStats)): ?>
<!-- Sessions summary table -->
<div class="bg-white rounded-3 border mb-4" style="border-color:#e8e3ff!important;overflow:hidden">
    <div class="p-3 border-bottom fw-bold small" style="color:#0f172a;border-color:#e8e3ff!important"><i class="fas fa-calendar-alt me-2 text-primary"></i>Session Breakdown</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.82rem">
            <thead style="background:#f8f7ff">
                <tr>
                    <th class="ps-3 fw-semibold" style="color:#64748b">Date</th>
                    <th class="fw-semibold" style="color:#64748b">Session</th>
                    <th class="text-center fw-semibold" style="color:#64748b">Type</th>
                    <th class="text-center fw-semibold" style="color:#22c55e">Present</th>
                    <th class="text-center fw-semibold" style="color:#f59e0b">Late</th>
                    <th class="text-center fw-semibold" style="color:#6366f1">Excused</th>
                    <th class="text-center fw-semibold" style="color:#ef4444">Absent</th>
                    <th class="fw-semibold" style="color:#64748b">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sessionStats as $s): ?>
            <?php
            $marked   = (int)$s['marked_cnt'];
            $pPresent = $marked > 0 ? round($s['present_cnt']/$marked*100) : 0;
            $pLate    = $marked > 0 ? round($s['late_cnt']/$marked*100) : 0;
            $pExcused = $marked > 0 ? round($s['excused_cnt']/$marked*100) : 0;
            $pAbsent  = $marked > 0 ? round($s['absent_cnt']/$marked*100) : 0;
            ?>
            <tr>
                <td class="ps-3 py-2 text-muted"><?= date('d M', strtotime($s['session_date'])) ?></td>
                <td class="py-2 fw-semibold" style="color:#0f172a"><?= e($s['title']) ?></td>
                <td class="text-center py-2"><span class="badge bg-secondary text-white" style="font-size:.65rem"><?= ucfirst($s['type']) ?></span></td>
                <td class="text-center py-2" style="color:#065f46;font-weight:700"><?= $s['present_cnt'] ?></td>
                <td class="text-center py-2" style="color:#92400e;font-weight:700"><?= $s['late_cnt'] ?></td>
                <td class="text-center py-2" style="color:#4338ca;font-weight:700"><?= $s['excused_cnt'] ?></td>
                <td class="text-center py-2" style="color:#991b1b;font-weight:700"><?= $s['absent_cnt'] ?></td>
                <td class="py-2">
                    <?php if ($s['is_locked']): ?>
                    <span class="badge" style="background:#fef3c7;color:#92400e;border-radius:8px;font-size:.65rem"><i class="fas fa-lock me-1"></i>Locked</span>
                    <?php else: ?>
                    <span class="badge bg-light text-muted" style="border-radius:8px;font-size:.65rem">Open</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($studentStats)): ?>
<!-- Per-student table -->
<div class="bg-white rounded-3 border" style="border-color:#e8e3ff!important;overflow:hidden">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="border-color:#e8e3ff!important">
        <span class="fw-bold small" style="color:#0f172a"><i class="fas fa-users me-2 text-primary"></i>Student Attendance</span>
        <?php if ($belowCount > 0): ?>
        <span class="badge" style="background:#fee2e2;color:#991b1b;border-radius:8px"><i class="fas fa-exclamation-triangle me-1"></i><?= $belowCount ?> below <?= $threshold ?>%</span>
        <?php endif; ?>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="font-size:.82rem">
            <thead style="background:#f8f7ff">
                <tr>
                    <th class="ps-3 fw-semibold" style="color:#64748b">Student</th>
                    <th class="text-center fw-semibold" style="color:#64748b">Sessions</th>
                    <th class="text-center fw-semibold" style="color:#22c55e">Present</th>
                    <th class="text-center fw-semibold" style="color:#f59e0b">Late</th>
                    <th class="text-center fw-semibold" style="color:#6366f1">Excused</th>
                    <th class="text-center fw-semibold" style="color:#ef4444">Absent</th>
                    <th class="fw-semibold" style="color:#64748b">Attendance</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($studentStats as $st): ?>
            <?php $pct = (float)$st['pct']; $below = $st['below_threshold']; ?>
            <tr class="<?= $below?'below-thr':'' ?>">
                <td class="ps-3 py-2">
                    <div class="fw-semibold" style="color:#0f172a"><?= e($st['student_name']) ?></div>
                    <div class="text-muted" style="font-size:.7rem"><?= e($st['email']) ?></div>
                </td>
                <td class="text-center py-2 text-muted"><?= $st['total_sessions'] ?></td>
                <td class="text-center py-2" style="color:#065f46;font-weight:700"><?= $st['present'] ?></td>
                <td class="text-center py-2" style="color:#92400e;font-weight:700"><?= $st['late'] ?></td>
                <td class="text-center py-2" style="color:#4338ca;font-weight:700"><?= $st['excused'] ?></td>
                <td class="text-center py-2" style="color:#991b1b;font-weight:700"><?= $st['absent'] ?></td>
                <td class="py-2" style="min-width:140px">
                    <div class="d-flex align-items-center gap-2">
                        <div class="att-bar flex-grow-1" style="flex:1">
                            <div style="width:<?= $pct ?>%;background:<?= $pct>=$threshold?'#22c55e':'#ef4444' ?>;border-radius:10px;height:100%"></div>
                        </div>
                        <span class="fw-bold" style="color:<?= $pct>=$threshold?'#16a34a':'#dc2626' ?>;min-width:38px;font-size:.8rem"><?= $pct ?>%</span>
                        <?php if ($below): ?><i class="fas fa-exclamation-triangle text-danger" style="font-size:.7rem" title="Below threshold"></i><?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>
