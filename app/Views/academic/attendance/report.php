<?php $pageTitle = 'Attendance Report'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/attendance') ?>">Attendance Portal</a></li>
                <li class="breadcrumb-item active">Report</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Attendance Report</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('academic/attendance/history') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-history me-1"></i> Session History
        </a>
        <a href="<?= url('academic/attendance') ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Portal
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('academic/attendance/report') ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted">Section <span class="text-danger">*</span></label>
                    <select class="form-select" name="section_id" required>
                        <option value="">— Select Section —</option>
                        <?php foreach($sections as $s): ?>
                        <option value="<?= $s['section_id'] ?>" <?= $sectionId == $s['section_id'] ? 'selected' : '' ?>>
                            <?= e($s['program_name']) ?> — Sec <?= e($s['section_name']) ?> (<?= e($s['batch_term']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">From Date</label>
                    <input type="date" class="form-control" name="from" value="<?= e($from ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">To Date</label>
                    <input type="date" class="form-control" name="to" value="<?= e($to ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Generate
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if($sectionId && !empty($students) && !empty($subjects)): ?>

<?php
    // Compute overall stats
    $belowThreshold = 0;
    foreach($students as $stu) {
        $totalP = 0; $totalT = 0;
        foreach($subjects as $sub) {
            $cell = $matrix[$stu['id']][$sub['id']] ?? null;
            if($cell) { $totalP += $cell['present']; $totalT += $cell['total']; }
        }
        if($totalT > 0 && round($totalP / $totalT * 100) < 75) $belowThreshold++;
    }
    $totalSessionsAll = array_sum($sessionCounts);
?>

<!-- Summary KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-primary"><?= count($students) ?></div>
                <div class="text-muted small">Students</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-info"><?= count($subjects) ?></div>
                <div class="text-muted small">Subjects</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-warning"><?= $belowThreshold ?></div>
                <div class="text-muted small">Below 75% Overall</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold text-secondary"><?= $totalSessionsAll ?></div>
                <div class="text-muted small">Total Sessions</div>
            </div>
        </div>
    </div>
</div>

<!-- Matrix Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2 text-secondary"></i>Student × Subject Attendance Matrix</h6>
        <div class="d-flex gap-2 align-items-center">
            <span class="badge bg-success-subtle text-success border border-success-subtle">≥75% Pass</span>
            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">50–74% At Risk</span>
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">&lt;50% Critical</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:70vh;overflow:auto">
            <table class="table table-bordered table-hover table-sm align-middle mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th class="ps-3" style="min-width:80px">Roll No</th>
                        <th style="min-width:160px">Student Name</th>
                        <?php foreach($subjects as $sub): ?>
                        <th class="text-center" style="min-width:110px">
                            <div><?= e($sub['subject_code']) ?></div>
                            <div class="fw-normal opacity-75" style="font-size:.7rem"><?= (int)($sessionCounts[$sub['id']] ?? 0) ?> sessions</div>
                        </th>
                        <?php endforeach; ?>
                        <th class="text-center" style="min-width:110px">Overall</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $stu): ?>
                    <?php
                        $overallP = 0; $overallT = 0;
                        foreach($subjects as $sub) {
                            $cell = $matrix[$stu['id']][$sub['id']] ?? null;
                            if($cell) { $overallP += $cell['present']; $overallT += $cell['total']; }
                        }
                        $overallPct = ($overallT > 0) ? round($overallP / $overallT * 100) : null;
                        $oCls = ($overallPct === null) ? 'secondary' : ($overallPct >= 75 ? 'success' : ($overallPct >= 50 ? 'warning' : 'danger'));
                    ?>
                    <tr>
                        <td class="ps-3 text-muted small fw-bold"><?= e($stu['roll_number'] ?? '—') ?></td>
                        <td class="fw-bold small"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></td>
                        <?php foreach($subjects as $sub): ?>
                        <?php
                            $cell = $matrix[$stu['id']][$sub['id']] ?? null;
                            if($cell && $cell['total'] > 0) {
                                $cpct = round($cell['present'] / $cell['total'] * 100);
                                $ccls = $cpct >= 75 ? 'success' : ($cpct >= 50 ? 'warning' : 'danger');
                            } else {
                                $cpct = null; $ccls = 'secondary';
                            }
                        ?>
                        <td class="text-center">
                            <?php if($cell && $cell['total'] > 0): ?>
                            <div>
                                <span class="badge bg-<?= $ccls ?>-subtle text-<?= $ccls ?> border border-<?= $ccls ?>-subtle">
                                    <?= $cpct ?>%
                                </span>
                            </div>
                            <div class="text-muted" style="font-size:.7rem"><?= $cell['present'] ?>/<?= $cell['total'] ?></div>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <td class="text-center">
                            <?php if($overallPct !== null): ?>
                            <div>
                                <span class="badge bg-<?= $oCls ?> text-white fw-bold">
                                    <?= $overallPct ?>%
                                </span>
                            </div>
                            <div class="text-muted" style="font-size:.7rem"><?= $overallP ?>/<?= $overallT ?></div>
                            <?php else: ?>
                            <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="2" class="ps-3 text-end small text-muted">Section Average →</td>
                        <?php
                            $grandP = 0; $grandT = 0;
                            foreach($subjects as $sub):
                                $subP = 0; $subT = 0;
                                foreach($students as $stu) {
                                    $cell = $matrix[$stu['id']][$sub['id']] ?? null;
                                    if($cell) { $subP += $cell['present']; $subT += $cell['total']; }
                                }
                                $grandP += $subP; $grandT += $subT;
                                $avgPct = ($subT > 0) ? round($subP / $subT * 100) : null;
                                $aCls   = ($avgPct === null) ? 'secondary' : ($avgPct >= 75 ? 'success' : ($avgPct >= 50 ? 'warning' : 'danger'));
                        ?>
                        <td class="text-center">
                            <?php if($avgPct !== null): ?>
                            <span class="badge bg-<?= $aCls ?>"><?= $avgPct ?>%</span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <?php $gPct = ($grandT > 0) ? round($grandP / $grandT * 100) : null; $gCls = ($gPct === null) ? 'secondary' : ($gPct >= 75 ? 'success' : ($gPct >= 50 ? 'warning' : 'danger')); ?>
                        <td class="text-center">
                            <?php if($gPct !== null): ?>
                            <span class="badge bg-<?= $gCls ?> text-white fw-bold"><?= $gPct ?>%</span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php elseif($sectionId): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>No attendance data found for the selected section and date range. Ensure attendance sessions have been submitted.
</div>
<?php else: ?>
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5 text-muted">
        <i class="fas fa-chart-bar fa-3x mb-3 opacity-25 d-block"></i>
        <h6>Select a Section to Generate Report</h6>
        <p class="small">Choose a section and optional date range, then click Generate.</p>
    </div>
</div>
<?php endif; ?>
