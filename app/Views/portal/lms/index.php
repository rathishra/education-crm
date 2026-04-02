<?php
$typeIcons = [
    'notes'       => ['fas fa-sticky-note', '#3b82f6', 'Notes'],
    'slides'      => ['fas fa-chalkboard', '#7c3aed', 'Slides'],
    'video'       => ['fab fa-youtube', '#dc2626', 'Video'],
    'pdf'         => ['fas fa-file-pdf', '#dc2626', 'PDF'],
    'assignment'  => ['fas fa-tasks', '#d97706', 'Assignment'],
    'reference'   => ['fas fa-book', '#059669', 'Reference'],
    'lab'         => ['fas fa-flask', '#0891b2', 'Lab'],
    'other'       => ['fas fa-paperclip', '#64748b', 'Other'],
];
function lmsTypeIcon(string $type, array $map): array {
    return $map[strtolower($type)] ?? $map['other'];
}
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-book-open me-2 text-success"></i>Course Materials</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; Course Materials</div>
    </div>
    <div class="text-muted small"><?= count($materials) ?> materials available</div>
</div>

<?php if (empty($materials)): ?>
<div class="portal-card p-5 text-center">
    <i class="fas fa-book-open d-block fs-1 mb-3 text-muted opacity-25"></i>
    <div class="fw-semibold mb-1">No Materials Available</div>
    <div class="text-muted small">Course materials for your batch will appear here once published by faculty.</div>
</div>
<?php else: ?>

<!-- Type Summary -->
<?php if (!empty($byType)): ?>
<div class="d-flex gap-2 flex-wrap mb-3">
    <?php foreach ($byType as $type => $cnt):
        [$icon, $color, $label] = lmsTypeIcon($type, $typeIcons);
    ?>
    <span class="badge px-3 py-2" style="background:<?= $color ?>18;color:<?= $color ?>;border:1px solid <?= $color ?>30;font-size:0.78rem;border-radius:8px">
        <i class="<?= $icon ?> me-1"></i><?= $label ?> (<?= $cnt ?>)
    </span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Subject-wise Materials -->
<?php foreach ($bySubject as $subjectName => $mats): ?>
<div class="portal-card mb-3">
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2 d-flex align-items-center gap-2">
        <div class="fw-bold" style="color:#1e293b"><?= e($subjectName) ?></div>
        <span class="badge bg-success-subtle text-success border ms-1" style="font-size:0.72rem"><?= count($mats) ?></span>
    </div>
    <div class="card-body p-0">
        <?php foreach ($mats as $mat):
            [$icon, $color, $label] = lmsTypeIcon($mat['material_type'] ?? 'other', $typeIcons);
            $hasFile = !empty($mat['file_path']);
            $hasLink = !empty($mat['external_url']);
        ?>
        <div class="d-flex align-items-start gap-3 p-3 border-bottom">
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3"
                 style="width:40px;height:40px;background:<?= $color ?>15">
                <i class="<?= $icon ?>" style="color:<?= $color ?>;font-size:1rem"></i>
            </div>
            <div class="flex-grow-1" style="min-width:0">
                <div class="fw-semibold"><?= e($mat['title'] ?? $mat['material_title'] ?? 'Untitled') ?></div>
                <?php if (!empty($mat['description'])): ?>
                <div class="text-muted small mt-1" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical"><?= e($mat['description']) ?></div>
                <?php endif; ?>
                <div class="d-flex gap-3 mt-1 text-muted" style="font-size:0.72rem">
                    <span><span class="badge px-1 py-0" style="background:<?= $color ?>18;color:<?= $color ?>;font-size:0.68rem"><?= $label ?></span></span>
                    <span><i class="fas fa-user me-1"></i><?= e($mat['faculty_name'] ?? '') ?></span>
                    <?php if (!empty($mat['created_at'])): ?><span><i class="fas fa-clock me-1"></i><?= date('d M Y', strtotime($mat['created_at'])) ?></span><?php endif; ?>
                    <?php if (!empty($mat['download_count'])): ?><span><i class="fas fa-download me-1"></i><?= (int)$mat['download_count'] ?></span><?php endif; ?>
                </div>
            </div>
            <div class="flex-shrink-0 d-flex gap-1">
                <?php if ($hasFile): ?>
                <a href="<?= url('portal/student/lms/download/' . $mat['id']) ?>"
                   class="btn btn-sm" style="background:#d1fae5;color:#065f46;border:none;font-size:0.78rem" title="Download">
                    <i class="fas fa-download me-1"></i><span class="d-none d-md-inline">Download</span>
                </a>
                <?php endif; ?>
                <?php if ($hasLink): ?>
                <a href="<?= e($mat['external_url']) ?>" target="_blank" rel="noopener"
                   class="btn btn-sm" style="background:#dbeafe;color:#1e40af;border:none;font-size:0.78rem" title="Open Link">
                    <i class="fas fa-external-link-alt me-1"></i><span class="d-none d-md-inline">Open</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>
