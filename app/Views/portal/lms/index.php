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
function progressColor(int $p): string {
    if ($p >= 80) return '#10b981';
    if ($p >= 40) return '#6366f1';
    if ($p > 0)   return '#f59e0b';
    return '#94a3b8';
}
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h1 class="portal-page-title"><i class="fas fa-graduation-cap me-2 text-primary"></i>My Learning</h1>
        <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; My Learning</div>
    </div>
</div>

<?php if (!$lmsUserId): ?>
<div class="portal-card p-5 text-center">
    <i class="fas fa-user-slash d-block fs-1 mb-3 text-muted opacity-25"></i>
    <div class="fw-semibold mb-1">LMS Account Not Provisioned</div>
    <div class="text-muted small">Your LMS account has not been set up yet. Please contact your administrator.</div>
</div>
<?php else: ?>

<!-- ── Enrolled Courses ──────────────────────────────── -->
<?php if (!empty($enrolledCourses)): ?>
<div class="d-flex align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0" style="color:#1e293b"><i class="fas fa-book-reader me-2 text-primary"></i>My Courses</h5>
    <span class="badge bg-primary"><?= count($enrolledCourses) ?></span>
</div>
<div class="row g-3 mb-4">
    <?php foreach ($enrolledCourses as $ec):
        $prog = (int)($ec['progress'] ?? 0);
        $pColor = progressColor($prog);
        $isComplete = ($ec['enroll_status'] === 'completed');
        $lessonsDone = (int)($ec['completed_lessons'] ?? 0);
        $lessonsTotal = (int)($ec['lesson_count'] ?? $ec['total_lessons'] ?? 0);
    ?>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="<?= url('portal/student/lms/courses/' . $ec['course_id']) ?>" class="text-decoration-none">
            <div class="portal-card h-100" style="transition:transform .15s,box-shadow .15s;cursor:pointer" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(99,102,241,.12)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                <?php if (!empty($ec['thumbnail'])): ?>
                <div style="height:140px;overflow:hidden;border-radius:12px 12px 0 0">
                    <img src="<?= asset($ec['thumbnail']) ?>" style="width:100%;height:100%;object-fit:cover" alt="">
                </div>
                <?php else: ?>
                <div style="height:80px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:12px 12px 0 0;display:flex;align-items:center;justify-content:center">
                    <i class="fas fa-book-open text-white" style="font-size:1.8rem;opacity:.5"></i>
                </div>
                <?php endif; ?>
                <div class="p-3">
                    <?php if ($isComplete): ?>
                    <span class="badge bg-success-subtle text-success border mb-2" style="font-size:.68rem"><i class="fas fa-check-circle me-1"></i>Completed</span>
                    <?php endif; ?>
                    <?php if (!empty($ec['subject_code'])): ?>
                    <span class="badge bg-primary-subtle text-primary border mb-2 ms-1" style="font-size:.68rem"><?= e($ec['subject_code']) ?></span>
                    <?php endif; ?>
                    <h6 class="fw-bold mb-1" style="color:#1e293b;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical"><?= e($ec['title']) ?></h6>
                    <?php if (!empty($ec['short_description'])): ?>
                    <div class="text-muted small mb-2" style="overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical"><?= e($ec['short_description']) ?></div>
                    <?php endif; ?>
                    <div class="text-muted small mb-2"><i class="fas fa-chalkboard-teacher me-1"></i><?= e($ec['instructor_name'] ?? 'N/A') ?></div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div class="flex-grow-1" style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                            <div style="width:<?= $prog ?>%;height:100%;background:<?= $pColor ?>;border-radius:3px"></div>
                        </div>
                        <span class="fw-bold small" style="color:<?= $pColor ?>"><?= $prog ?>%</span>
                    </div>
                    <div class="text-muted" style="font-size:.72rem">
                        <i class="fas fa-play-circle me-1"></i><?= $lessonsDone ?>/<?= $lessonsTotal ?> lessons
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="portal-card p-5 text-center mb-4">
    <i class="fas fa-book-open d-block fs-1 mb-3 text-muted opacity-25"></i>
    <div class="fw-semibold mb-1">No Courses Yet</div>
    <div class="text-muted small">You are not enrolled in any LMS courses yet.</div>
</div>
<?php endif; ?>

<!-- ── Upcoming Deadlines ────────────────────────────── -->
<?php if (!empty($deadlines)): ?>
<div class="d-flex align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0" style="color:#1e293b"><i class="fas fa-clock me-2 text-warning"></i>Upcoming Deadlines</h5>
</div>
<div class="portal-card mb-4">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background:#f8fafc">
                <tr>
                    <th class="small fw-semibold text-muted">Title</th>
                    <th class="small fw-semibold text-muted">Course</th>
                    <th class="small fw-semibold text-muted">Type</th>
                    <th class="small fw-semibold text-muted">Due</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deadlines as $dl):
                    $due = strtotime($dl['due_at']);
                    $hoursLeft = ($due - time()) / 3600;
                    $urgent = $hoursLeft < 24;
                ?>
                <tr>
                    <td class="fw-semibold"><?= e($dl['title']) ?></td>
                    <td><a href="<?= url('portal/student/lms/courses/' . $dl['course_id']) ?>" class="text-decoration-none"><?= e($dl['course_title']) ?></a></td>
                    <td><span class="badge <?= $dl['type'] === 'quiz' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' ?>" style="font-size:.72rem"><?= ucfirst($dl['type']) ?></span></td>
                    <td>
                        <span class="<?= $urgent ? 'text-danger fw-bold' : '' ?>"><?= date('d M, h:i A', $due) ?></span>
                        <?php if ($urgent): ?><span class="badge bg-danger ms-1" style="font-size:.6rem">URGENT</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ── Legacy Materials (backward compat) ────────────── -->
<?php if (!empty($materials)): ?>
<div class="d-flex align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0" style="color:#1e293b"><i class="fas fa-folder-open me-2 text-success"></i>Academic Materials</h5>
    <span class="badge bg-success"><?= count($materials) ?></span>
</div>
<?php
    $bySubject = [];
    foreach ($materials as $mat) {
        $bySubject[$mat['subject_name'] ?? 'Other'][] = $mat;
    }
?>
<?php foreach ($bySubject as $subjectName => $mats): ?>
<div class="portal-card mb-3">
    <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
        <div class="fw-bold" style="color:#1e293b"><?= e($subjectName) ?> <span class="badge bg-success-subtle text-success border ms-1" style="font-size:.72rem"><?= count($mats) ?></span></div>
    </div>
    <div class="card-body p-0">
        <?php foreach ($mats as $mat):
            [$icon, $color, $label] = lmsTypeIcon($mat['material_type'] ?? 'other', $typeIcons);
        ?>
        <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:<?= $color ?>15">
                <i class="<?= $icon ?>" style="color:<?= $color ?>"></i>
            </div>
            <div class="flex-grow-1" style="min-width:0">
                <div class="fw-semibold small"><?= e($mat['title'] ?? $mat['material_title'] ?? 'Untitled') ?></div>
                <div class="text-muted" style="font-size:.72rem"><i class="fas fa-user me-1"></i><?= e($mat['faculty_name'] ?? '') ?> &bull; <?= date('d M Y', strtotime($mat['created_at'])) ?></div>
            </div>
            <?php if (!empty($mat['file_path'])): ?>
            <a href="<?= url('portal/student/lms/download/' . $mat['id']) ?>" class="btn btn-sm" style="background:#d1fae5;color:#065f46;border:none;font-size:.75rem"><i class="fas fa-download me-1"></i>Download</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php endif; ?>
