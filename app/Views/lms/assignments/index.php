<?php
$isInstructor = in_array($lmsUser['role'] ?? '', ['lms_admin','instructor']);
?>
<style>
.asn-card { background:#fff; border-radius:12px; border:1px solid #e8e3ff; box-shadow:0 1px 5px rgba(99,102,241,.06); transition:transform .15s,box-shadow .15s; }
.asn-card:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(99,102,241,.12); }
.asn-status { display:inline-flex; align-items:center; gap:.3rem; font-size:.7rem; font-weight:700; padding:2px 10px; border-radius:20px; }
.asn-due-urgent { color:#dc2626; background:#fee2e2; }
.asn-due-soon   { color:#d97706; background:#fef3c7; }
.asn-due-ok     { color:#059669; background:#d1fae5; }
.asn-due-none   { color:#64748b; background:#f1f5f9; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-tasks me-2 text-primary"></i>Assignments</h4>
        <div class="text-muted small mt-1"><?= number_format($total) ?> assignment<?= $total != 1 ? 's' : '' ?></div>
    </div>
    <?php if ($isInstructor): ?>
    <a href="<?= url('elms/assignments/create') ?>" class="btn btn-primary" style="border-radius:9px">
        <i class="fas fa-plus me-2"></i>New Assignment
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search assignments…" value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-6 col-md-3">
            <select name="course" class="form-select form-select-sm">
                <option value="">All Courses</option>
                <?php foreach ($myCourses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseId==(int)$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="border-radius:8px"><i class="fas fa-filter me-1"></i>Filter</button>
            <?php if ($search || $courseId): ?>
            <a href="<?= url('elms/assignments') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (empty($assignments)): ?>
<div class="text-center py-5">
    <div style="font-size:3.5rem;opacity:.15;color:#6366f1"><i class="fas fa-tasks"></i></div>
    <h5 class="fw-bold mt-3 text-muted">No assignments found</h5>
    <?php if ($isInstructor && !$search && !$courseId): ?>
    <a href="<?= url('elms/assignments/create') ?>" class="btn btn-primary mt-2" style="border-radius:9px"><i class="fas fa-plus me-2"></i>Create First Assignment</a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($assignments as $a): ?>
    <?php
    $now   = time();
    $dueTs = $a['due_at'] ? strtotime($a['due_at']) : null;
    $hoursLeft = $dueTs ? round(($dueTs - $now) / 3600, 1) : null;
    if (!$dueTs)                   [$dueCls, $dueLabel] = ['asn-due-none',   'No due date'];
    elseif ($dueTs < $now)         [$dueCls, $dueLabel] = ['asn-due-urgent', 'Overdue'];
    elseif ($hoursLeft < 24)       [$dueCls, $dueLabel] = ['asn-due-urgent', 'Due in '.round($hoursLeft).'h'];
    elseif ($hoursLeft < 72)       [$dueCls, $dueLabel] = ['asn-due-soon',   'Due in '.ceil($hoursLeft/24).'d'];
    else                           [$dueCls, $dueLabel] = ['asn-due-ok',     date('d M', $dueTs)];
    ?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="asn-card p-3 h-100 d-flex flex-column">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <div style="flex:1">
                    <a href="<?= url('elms/assignments/'.$a['id']) ?>" class="text-decoration-none">
                        <h6 class="fw-bold mb-1 text-dark" style="font-size:.9rem;line-height:1.35"><?= e($a['title']) ?></h6>
                    </a>
                    <div class="text-muted small"><?= e($a['course_title']) ?></div>
                </div>
                <span class="asn-status <?= $dueCls ?>"><i class="fas fa-clock"></i><?= $dueLabel ?></span>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3 mt-auto">
                <span class="badge bg-light text-muted border" style="font-size:.7rem">
                    <i class="fas fa-star me-1 text-warning"></i><?= $a['max_score'] ?> pts
                </span>
                <span class="badge bg-light text-muted border" style="font-size:.7rem">
                    <i class="fas fa-redo me-1"></i><?= $a['attempts_allowed'] ?: '∞' ?> attempt<?= $a['attempts_allowed'] != 1 ? 's' : '' ?>
                </span>
                <?php if ($isInstructor): ?>
                <span class="badge bg-light text-muted border" style="font-size:.7rem">
                    <i class="fas fa-paper-plane me-1"></i><?= $a['sub_count'] ?> submitted
                </span>
                <?php if ($a['sub_count'] > 0): ?>
                <span class="badge" style="background:#d1fae5;color:#065f46;font-size:.7rem">
                    <i class="fas fa-check-double me-1"></i><?= $a['graded_count'] ?> graded
                </span>
                <?php endif; ?>
                <?php else: ?>
                <?php if ($a['sub_status'] === 'graded'): ?>
                <span class="badge" style="background:#d1fae5;color:#065f46;font-size:.7rem">
                    <i class="fas fa-check-circle me-1"></i>Graded: <?= number_format($a['score'], 1) ?>/<?= $a['max_score'] ?>
                </span>
                <?php elseif ($a['sub_status']): ?>
                <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:.7rem">
                    <i class="fas fa-paper-plane me-1"></i>Submitted
                </span>
                <?php else: ?>
                <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.7rem">
                    <i class="fas fa-exclamation-circle me-1"></i>Pending
                </span>
                <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                <a href="<?= url('elms/assignments/'.$a['id']) ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.78rem">
                    <?= $isInstructor ? '<i class="fas fa-eye me-1"></i>View Submissions' : '<i class="fas fa-upload me-1"></i>'.(!$a['sub_status']?'Submit':'View') ?>
                </a>
                <?php if ($isInstructor): ?>
                <a href="<?= url('elms/assignments/'.$a['id'].'/edit') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.75rem">
                    <i class="fas fa-edit"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-4"><ul class="pagination pagination-sm justify-content-center" style="gap:4px">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>">
        <a class="page-link" style="border-radius:7px" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>&course=<?= $courseId ?>"><?= $p ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>
