<?php $pageTitle = 'Faculty Attendance — ' . $monthName . ' ' . $year; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-1"><i class="fas fa-clipboard-check me-2 text-success"></i>Faculty Attendance</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('faculty') ?>">Faculty</a></li>
                <li class="breadcrumb-item active">Attendance</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#markAttModal">
            <i class="fas fa-pen me-1"></i>Mark Attendance
        </button>
        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#bulkAttModal">
            <i class="fas fa-list-check me-1"></i>Bulk Mark
        </button>
    </div>
</div>

<!-- ── MONTH NAVIGATOR ────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <?php
                $pm = $month - 1; $py = $year;
                if ($pm < 1)  { $pm = 12; $py--; }
                $nm = $month + 1; $ny = $year;
                if ($nm > 12) { $nm = 1;  $ny++; }
                ?>
                <a href="?month=<?= $pm ?>&year=<?= $py ?><?= $facId ? "&faculty_id=$facId" : '' ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </div>
            <div class="col-auto">
                <select name="month" class="form-select form-select-sm">
                    <?php for ($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="year" class="form-select form-select-sm">
                    <?php for ($y=date('Y')-1;$y<=date('Y')+1;$y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="faculty_id" class="form-select form-select-sm">
                    <option value="">All Faculty (Summary)</option>
                    <?php foreach ($facultyList as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= $facId == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>View</button>
            </div>
            <div class="col-auto">
                <a href="?month=<?= $nm ?>&year=<?= $ny ?><?= $facId ? "&faculty_id=$facId" : '' ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<?php if ($facId && !empty($dailyRecords)): ?>
<!-- ── CALENDAR VIEW FOR SELECTED FACULTY ─────────────────────── -->
<?php
$selFacName = '';
foreach ($facultyList as $f) {
    if ($f['id'] == $facId) { $selFacName = $f['name']; break; }
}
$statusColors = [
    'present'  => ['bg-success','P'],
    'absent'   => ['bg-danger', 'A'],
    'half_day' => ['bg-warning','H'],
    'on_leave' => ['bg-info',   'L'],
    'holiday'  => ['bg-secondary','X'],
];
$firstDow = (int)date('N', mktime(0,0,0,$month,1,$year)); // 1=Mon … 7=Sun
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold border-bottom">
        <i class="fas fa-calendar me-2 text-success"></i><?= e($selFacName) ?> — <?= $monthName ?> <?= $year ?>
    </div>
    <div class="card-body">
        <div class="row g-1 text-center mb-2">
            <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow): ?>
            <div class="col" style="font-size:.72rem;font-weight:600;color:#6b7280"><?= $dow ?></div>
            <?php endforeach; ?>
        </div>
        <?php
        $day = 1;
        $rows = ceil(($daysInMonth + $firstDow - 1) / 7);
        for ($row = 0; $row < $rows; $row++):
        ?>
        <div class="row g-1 text-center mb-1">
            <?php for ($col = 0; $col < 7; $col++):
                $cellDay = $row * 7 + $col - ($firstDow - 2);
                if ($cellDay < 1 || $cellDay > $daysInMonth):
            ?>
            <div class="col"><div style="height:42px"></div></div>
            <?php else:
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $cellDay);
                $rec     = $dailyRecords[$dateStr] ?? null;
                $isToday = $dateStr === date('Y-m-d');
                [$cl, $ltr] = $rec ? ($statusColors[$rec['status']] ?? ['bg-light','?']) : ['bg-light text-muted',''];
                $tip = $rec ? ucwords(str_replace('_',' ',$rec['status'])) . ($rec['check_in'] ? ' · in '.$rec['check_in'] : '') . ($rec['check_out'] ? ' out '.$rec['check_out'] : '') : 'Not marked';
            ?>
            <div class="col">
                <div class="rounded d-flex flex-column align-items-center justify-content-center <?= $cl ?> <?= $isToday ? 'border border-2 border-primary' : '' ?>"
                     style="height:42px;cursor:default"
                     title="<?= date('d M', strtotime($dateStr)) ?>: <?= $tip ?>"
                     data-bs-toggle="tooltip">
                    <div style="font-size:.7rem;font-weight:600"><?= $cellDay ?></div>
                    <div style="font-size:.6rem"><?= $ltr ?></div>
                </div>
            </div>
            <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endfor; ?>

        <!-- Legend -->
        <div class="d-flex flex-wrap gap-3 mt-3 small border-top pt-3">
            <?php foreach ($statusColors as $k=>[$cl,$l]): ?>
            <span><span class="badge <?= $cl ?>"><?= $l ?></span> <?= ucwords(str_replace('_',' ',$k)) ?></span>
            <?php endforeach; ?>
            <span><span class="badge bg-light text-muted border">&nbsp;</span> Not Marked</span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── MONTHLY SUMMARY TABLE ──────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold">
        <i class="fas fa-table me-2 text-primary"></i>
        <?= $monthName ?> <?= $year ?> — Attendance Summary
    </div>
    <div class="card-body p-0">
        <?php if (empty($monthlySummary)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3 opacity-25 d-block"></i>
            No attendance records found for this month.
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Faculty</th>
                        <th class="text-center text-success">Present</th>
                        <th class="text-center text-danger">Absent</th>
                        <th class="text-center text-warning">Half Day</th>
                        <th class="text-center text-info">On Leave</th>
                        <th class="text-center text-secondary">Holiday</th>
                        <th class="text-center">Marked</th>
                        <th class="text-center">Attendance %</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthlySummary as $ms):
                        $present  = (int)$ms['present'];
                        $absent   = (int)$ms['absent'];
                        $halfDay  = (int)$ms['half_day'];
                        $onLeave  = (int)$ms['on_leave'];
                        $holiday  = (int)$ms['holiday'];
                        $total    = (int)$ms['total_marked'];
                        $working  = $total - $holiday;
                        $attPct   = $working > 0 ? round(($present + $halfDay*0.5) / $working * 100, 1) : 0;
                        $attCls   = $attPct >= 80 ? 'success' : ($attPct >= 60 ? 'warning' : 'danger');
                    ?>
                    <tr>
                        <td>
                            <a href="<?= url("faculty/{$ms['faculty_id']}#tabAttendance") ?>" class="fw-semibold text-dark text-decoration-none">
                                <?= e($ms['faculty_name']) ?>
                            </a>
                            <div class="text-muted" style="font-size:.7rem"><?= e($ms['designation'] ?: '') ?></div>
                        </td>
                        <td class="text-center fw-bold text-success"><?= $present ?></td>
                        <td class="text-center fw-bold text-danger"><?= $absent ?></td>
                        <td class="text-center fw-bold text-warning"><?= $halfDay ?></td>
                        <td class="text-center fw-bold text-info"><?= $onLeave ?></td>
                        <td class="text-center fw-bold text-secondary"><?= $holiday ?></td>
                        <td class="text-center"><?= $total ?> / <?= $daysInMonth ?></td>
                        <td class="text-center">
                            <div class="progress" style="height:6px;min-width:60px">
                                <div class="progress-bar bg-<?= $attCls ?>" style="width:<?= $attPct ?>%"></div>
                            </div>
                            <div class="fw-semibold text-<?= $attCls ?>" style="font-size:.7rem"><?= $attPct ?>%</div>
                        </td>
                        <td class="text-end">
                            <a href="?month=<?= $month ?>&year=<?= $year ?>&faculty_id=<?= $ms['faculty_id'] ?>"
                               class="btn btn-xs btn-outline-primary py-0 px-2">
                                <i class="fas fa-eye me-1"></i>View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── MARK SINGLE ATTENDANCE MODAL ──────────────────────────── -->
<div class="modal fade" id="markAttModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('faculty/attendance/mark') ?>">
                <?= csrfField() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-check me-2 text-success"></i>Mark Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Faculty <span class="text-danger">*</span></label>
                            <select name="faculty_id" class="form-select" required>
                                <option value="">— Select —</option>
                                <?php foreach ($facultyList as $f): ?>
                                <option value="<?= $f['id'] ?>" <?= $facId == $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="half_day">Half Day</option>
                                <option value="on_leave">On Leave</option>
                                <option value="holiday">Holiday</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Check In</label>
                            <input type="time" name="check_in" class="form-control" value="09:00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Check Out</label>
                            <input type="time" name="check_out" class="form-control" value="17:00">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional…">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">Mark Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── BULK MARK MODAL ────────────────────────────────────────── -->
<div class="modal fade" id="bulkAttModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?= url('faculty/attendance/bulk') ?>">
                <?= csrfField() ?>
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-list-check me-2 text-primary"></i>Bulk Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-3 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0 fw-semibold">Date</label>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-auto ms-auto">
                            <button type="button" id="markAllPresent" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-check me-1"></i>Mark All Present
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height:350px;overflow-y:auto">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Faculty</th>
                                    <th>Designation</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($facultyList as $f): ?>
                                <tr>
                                    <td class="fw-semibold"><?= e($f['name']) ?></td>
                                    <td class="text-muted small"><?= e($f['designation'] ?? '') ?></td>
                                    <td>
                                        <select name="records[<?= $f['id'] ?>]" class="form-select form-select-sm bulk-status">
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="half_day">Half Day</option>
                                            <option value="on_leave">On Leave</option>
                                            <option value="holiday">Holiday</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save All</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

    // Mark all present
    document.getElementById('markAllPresent')?.addEventListener('click', function () {
        document.querySelectorAll('.bulk-status').forEach(sel => sel.value = 'present');
    });
});
</script>
