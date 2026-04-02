<?php
$typeColors = [
    'internal'  => ['bg' => '#dbeafe','color' => '#1e40af','label' => 'Internal'],
    'external'  => ['bg' => '#fee2e2','color' => '#991b1b','label' => 'External'],
    'quiz'      => ['bg' => '#d1fae5','color' => '#065f46','label' => 'Quiz'],
    'practical' => ['bg' => '#fef3c7','color' => '#92400e','label' => 'Practical'],
    'assignment'=> ['bg' => '#ede9fe','color' => '#5b21b6','label' => 'Assignment'],
    'midterm'   => ['bg' => '#ffedd5','color' => '#9a3412','label' => 'Midterm'],
    'final'     => ['bg' => '#fee2e2','color' => '#991b1b','label' => 'Final'],
];
function examTypeBadge(string $type, array $typeColors): string {
    $c = $typeColors[strtolower($type)] ?? ['bg' => '#f1f5f9','color' => '#475569','label' => ucfirst($type)];
    return "<span class=\"badge px-2 py-1\" style=\"background:{$c['bg']};color:{$c['color']};font-size:0.7rem\">{$c['label']}</span>";
}
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-file-alt me-2 text-success"></i>Exams & Assessments</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; Exams</div>
    </div>
    <a href="<?= url('portal/student/exams/results') ?>" class="btn btn-success btn-sm px-3">
        <i class="fas fa-star me-1"></i>View Results
    </a>
</div>

<!-- Upcoming -->
<div class="portal-card mb-3">
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
        <div class="fw-bold" style="color:#1e293b"><i class="fas fa-calendar me-2 text-purple" style="color:#7c3aed"></i>Upcoming Exams <span class="badge bg-purple-subtle text-purple border ms-1" style="background:#ede9fe;color:#7c3aed;font-size:0.75rem"><?= count($upcoming) ?></span></div>
    </div>
    <?php if (empty($upcoming)): ?>
    <div class="text-center py-5 text-muted"><i class="fas fa-check-circle d-block fs-2 mb-2 text-success"></i>No upcoming exams. All clear!</div>
    <?php else: ?>
    <div class="list-group list-group-flush">
        <?php foreach ($upcoming as $ex):
            $daysLeft  = (int)ceil((strtotime($ex['assessment_date']) - time()) / 86400);
            $urgency   = $daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success');
        ?>
        <div class="list-group-item px-4 py-3 border-0 border-bottom">
            <div class="d-flex align-items-start gap-3">
                <!-- Date blob -->
                <div class="text-center flex-shrink-0 rounded-3 p-2" style="min-width:52px;background:#f8fafc;border:1px solid #e2e8f0">
                    <div class="fw-bold lh-1" style="font-size:1.2rem;color:#7c3aed"><?= date('d', strtotime($ex['assessment_date'])) ?></div>
                    <div style="font-size:0.65rem;color:#94a3b8;text-transform:uppercase"><?= date('M Y', strtotime($ex['assessment_date'])) ?></div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold"><?= e($ex['assessment_name']) ?></div>
                            <div class="text-muted" style="font-size:0.82rem"><?= e($ex['subject_name']) ?> <?= !empty($ex['subject_code']) ? '(' . e($ex['subject_code']) . ')' : '' ?></div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <?= examTypeBadge($ex['assessment_type'] ?? 'exam', $typeColors) ?>
                            <span class="badge bg-<?= $urgency ?>-subtle text-<?= $urgency ?> border" style="font-size:0.7rem">
                                <?= $daysLeft === 0 ? 'Today' : ($daysLeft === 1 ? 'Tomorrow' : "{$daysLeft} days") ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-1 d-flex gap-3 text-muted" style="font-size:0.78rem">
                        <span><i class="fas fa-star me-1"></i>Max: <?= (int)($ex['max_marks'] ?? 0) ?> marks</span>
                        <?php if (!empty($ex['passing_marks'])): ?><span><i class="fas fa-check me-1 text-success"></i>Pass: <?= (int)$ex['passing_marks'] ?></span><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Past Exams -->
<?php if (!empty($past)): ?>
<div class="portal-card">
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
        <div class="fw-bold" style="color:#1e293b"><i class="fas fa-history me-2 text-muted"></i>Past Exams</div>
    </div>
    <div class="table-responsive">
        <table class="table portal-table mb-0">
            <thead><tr><th>Exam</th><th>Subject</th><th>Date</th><th>Type</th><th class="text-end">Max Marks</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($past, 0, 10) as $ex): ?>
                <tr>
                    <td class="fw-semibold"><?= e($ex['assessment_name']) ?></td>
                    <td><?= e($ex['subject_name']) ?></td>
                    <td><?= !empty($ex['assessment_date']) ? date('d M Y', strtotime($ex['assessment_date'])) : '—' ?></td>
                    <td><?= examTypeBadge($ex['assessment_type'] ?? '', $typeColors) ?></td>
                    <td class="text-end"><?= (int)($ex['max_marks'] ?? 0) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-transparent text-center py-2">
        <a href="<?= url('portal/student/exams/results') ?>" class="btn btn-outline-success btn-sm px-4">
            <i class="fas fa-star me-1"></i>View All Results & Marks
        </a>
    </div>
</div>
<?php endif; ?>
