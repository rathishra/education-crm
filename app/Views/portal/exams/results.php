<?php
function examGradeLabel(float $pct): string {
    if ($pct >= 90) return 'O';
    if ($pct >= 75) return 'A+';
    if ($pct >= 60) return 'A';
    if ($pct >= 50) return 'B';
    if ($pct >= 40) return 'C';
    return 'F';
}

// Calculate totals per subject
$subjectTotals = [];
foreach ($bySubject as $subName => $rows) {
    $total = $obtained = $count = 0;
    foreach ($rows as $row) {
        if (!$row['is_absent']) {
            $total    += (float)$row['max_marks'];
            $obtained += (float)$row['marks_obtained'];
            $count++;
        }
    }
    $pct = $total > 0 ? round($obtained / $total * 100, 1) : 0;
    $subjectTotals[$subName] = compact('total', 'obtained', 'count', 'pct');
}
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-star me-2 text-success"></i>Exam Results</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; <a href="<?= url('portal/student/exams') ?>">Exams</a> &rsaquo; Results</div>
    </div>
</div>

<?php if (empty($results)): ?>
<div class="portal-card p-5 text-center">
    <i class="fas fa-star d-block fs-1 mb-3 text-muted opacity-25"></i>
    <div class="fw-semibold mb-1">No Results Available</div>
    <div class="text-muted small">Your exam marks have not been published yet.</div>
</div>
<?php else: ?>

<!-- Subject Summary Cards -->
<div class="row g-3 mb-4">
    <?php foreach ($subjectTotals as $subName => $st):
        $color = $st['pct'] >= 60 ? 'success' : ($st['pct'] >= 40 ? 'warning' : 'danger');
    ?>
    <div class="col-6 col-md-4 col-lg-3">
        <div class="portal-card p-3 text-center">
            <div class="fw-semibold mb-1" style="font-size:0.82rem"><?= e($subName) ?></div>
            <div class="fw-bold" style="font-size:1.4rem;color:var(--portal-primary)"><?= $st['pct'] ?>%</div>
            <div class="text-muted" style="font-size:0.75rem"><?= $st['obtained'] ?> / <?= $st['total'] ?> marks</div>
            <div class="mt-2">
                <div class="progress" style="height:5px">
                    <div class="progress-bar bg-<?= $color ?>" style="width:<?= $st['pct'] ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Detailed Results Table -->
<div class="portal-card">
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
        <div class="fw-bold" style="color:#1e293b"><i class="fas fa-table me-2 text-success"></i>Detailed Marks Sheet</div>
    </div>
    <div class="table-responsive">
        <table class="table portal-table mb-0">
            <thead>
                <tr>
                    <th>Assessment</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-end">Max</th>
                    <th class="text-end">Obtained</th>
                    <th class="text-end">%</th>
                    <th>Grade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r):
                    $pct    = (float)($r['percentage'] ?? 0);
                    $passed = !$r['is_absent'] && (!isset($r['passing_marks']) || (float)$r['marks_obtained'] >= (float)$r['passing_marks']);
                    $color  = $pct >= 60 ? 'success' : ($pct >= 40 ? 'warning' : 'danger');
                ?>
                <tr>
                    <td class="fw-semibold"><?= e($r['assessment_name']) ?></td>
                    <td><?= e($r['subject_name']) ?> <?= !empty($r['subject_code']) ? '<span class="text-muted small">(' . e($r['subject_code']) . ')</span>' : '' ?></td>
                    <td><?= !empty($r['assessment_date']) ? date('d M Y', strtotime($r['assessment_date'])) : '—' ?></td>
                    <td><span class="badge bg-light text-dark border" style="font-size:0.7rem"><?= ucfirst($r['assessment_type'] ?? '') ?></span></td>
                    <td class="text-end"><?= (int)$r['max_marks'] ?></td>
                    <td class="text-end fw-semibold <?= $r['is_absent'] ? 'text-muted' : 'text-' . $color ?>">
                        <?= $r['is_absent'] ? '<span class="text-danger">AB</span>' : number_format((float)$r['marks_obtained'], 1) ?>
                    </td>
                    <td class="text-end" style="font-size:0.82rem"><?= $r['is_absent'] ? '—' : $pct . '%' ?></td>
                    <td><?php if (!$r['is_absent'] && $pct > 0): ?><span class="badge bg-secondary-subtle text-secondary border"><?= examGradeLabel($pct) ?></span><?php else: ?>—<?php endif; ?></td>
                    <td>
                        <?php if ($r['is_absent']): ?>
                        <span class="badge bg-secondary-subtle text-secondary border" style="font-size:0.7rem">Absent</span>
                        <?php elseif ($passed): ?>
                        <span class="badge bg-success-subtle text-success border" style="font-size:0.7rem">Pass</span>
                        <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger border" style="font-size:0.7rem">Fail</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>
