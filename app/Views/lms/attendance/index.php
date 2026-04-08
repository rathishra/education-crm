<style>
.sess-card { background:#fff; border-radius:12px; border:1px solid #e8e3ff; padding:1rem 1.25rem; transition:box-shadow .15s; }
.sess-card:hover { box-shadow:0 4px 16px rgba(99,102,241,.1); }
.status-bar { display:flex; height:8px; border-radius:20px; overflow:hidden; }
.type-pill { font-size:.68rem; font-weight:700; padding:.2rem .55rem; border-radius:20px; }
.my-badge { font-size:.72rem; font-weight:700; padding:.25rem .65rem; border-radius:12px; }
</style>

<?php
$statusColors = ['present'=>'#22c55e','late'=>'#f59e0b','excused'=>'#6366f1','absent'=>'#ef4444'];
$typeColors   = ['online'=>'bg-info','offline'=>'bg-secondary','live'=>'bg-danger'];
$myBadgeStyle = ['present'=>'background:#d1fae5;color:#065f46','late'=>'background:#fef3c7;color:#92400e',
                 'excused'=>'background:#ede9fe;color:#4338ca','absent'=>'background:#fee2e2;color:#991b1b',
                 'not_marked'=>'background:#f1f5f9;color:#64748b'];
?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-clipboard-check me-2 text-primary"></i>Attendance</h4>
    <?php if (!$lmsUser || $lmsUser['role'] !== 'learner'): ?>
    <div class="d-flex gap-2">
        <a href="<?= url('elms/attendance/report') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-chart-pie me-1"></i>Report</a>
        <a href="<?= url('elms/attendance/create') ?>" class="btn btn-primary btn-sm" style="border-radius:8px"><i class="fas fa-plus me-1"></i>New Session</a>
    </div>
    <?php endif; ?>
</div>

<!-- Filters -->
<form method="GET" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-4 col-md-3">
            <label class="form-label small fw-semibold mb-1">Course</label>
            <select name="course" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php foreach ($myCourses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-sm-4 col-md-3">
            <label class="form-label small fw-semibold mb-1">Month</label>
            <input type="month" name="month" value="<?= e($month) ?>" class="form-control form-control-sm" onchange="this.form.submit()">
        </div>
        <div class="col-auto">
            <a href="<?= url('elms/attendance') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-times me-1"></i>Clear</a>
        </div>
    </div>
</form>

<?php if (empty($sessions)): ?>
<div class="text-center py-5" style="color:#94a3b8">
    <i class="fas fa-clipboard-list" style="font-size:2.5rem;opacity:.2"></i>
    <p class="mt-2 small fw-semibold">No sessions found for this period</p>
    <?php if (!$lmsUser || $lmsUser['role'] !== 'learner'): ?>
    <a href="<?= url('elms/attendance/create') ?>" class="btn btn-sm btn-primary mt-1" style="border-radius:8px"><i class="fas fa-plus me-1"></i>Create Session</a>
    <?php endif; ?>
</div>
<?php else: ?>

<div class="row g-2 mb-2">
    <div class="col-12"><span class="text-muted small"><?= $total ?> session<?= $total!=1?'s':'' ?></span></div>
</div>

<?php foreach ($sessions as $s): ?>
<?php
$typeClass = $typeColors[$s['type']] ?? 'bg-secondary';
$sessionDate = date('D, d M Y', strtotime($s['session_date']));
$isLearner = $lmsUser && $lmsUser['role'] === 'learner';
?>
<div class="sess-card mb-2">
    <div class="d-flex align-items-start gap-3">
        <!-- Date block -->
        <div style="min-width:52px;text-align:center;background:#f8f7ff;border-radius:10px;padding:.4rem .3rem;border:1px solid #e8e3ff">
            <div style="font-size:.65rem;color:#94a3b8;font-weight:700;text-transform:uppercase"><?= date('M', strtotime($s['session_date'])) ?></div>
            <div style="font-size:1.3rem;font-weight:900;color:#4f46e5;line-height:1"><?= date('d', strtotime($s['session_date'])) ?></div>
            <div style="font-size:.6rem;color:#94a3b8"><?= date('D', strtotime($s['session_date'])) ?></div>
        </div>

        <div style="flex:1;min-width:0">
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="fw-bold" style="color:#0f172a;font-size:.9rem"><?= e($s['title']) ?></span>
                <span class="badge <?= $typeClass ?> text-white type-pill"><?= ucfirst($s['type']) ?></span>
                <?php if (!empty($s['is_locked'])): ?>
                <span class="badge" style="background:#fef3c7;color:#92400e;border-radius:8px;font-size:.65rem"><i class="fas fa-lock me-1"></i>Locked</span>
                <?php endif; ?>
            </div>
            <div class="text-muted small mb-2">
                <?= e($s['course_title']) ?>
                <?php if ($s['start_time']): ?>&middot; <?= date('H:i', strtotime($s['start_time'])) ?>–<?= $s['end_time']?date('H:i',strtotime($s['end_time'])):'?' ?><?php endif; ?>
            </div>

            <?php if ($isLearner): ?>
            <?php $myStatus = $s['my_status'] ?? 'not_marked'; ?>
            <span class="my-badge" style="<?= $myBadgeStyle[$myStatus] ?? $myBadgeStyle['not_marked'] ?>">
                <i class="fas fa-<?= $myStatus==='present'?'check':($myStatus==='absent'?'times':($myStatus==='late'?'clock':($myStatus==='excused'?'shield-alt':'minus'))) ?> me-1"></i>
                <?= ucfirst(str_replace('_', ' ', $myStatus)) ?>
            </span>
            <?php else: ?>
            <?php
            $total_m  = (int)($s['total_marked'] ?? 0);
            $present  = (int)($s['present_cnt'] ?? 0);
            $late     = (int)($s['late_cnt'] ?? 0);
            $absent   = (int)($s['absent_cnt'] ?? 0);
            $excused  = $total_m - $present - $late - $absent;
            $excused  = max(0, $excused);
            $pctAtt   = $total_m > 0 ? round(($present+$late+$excused)/$total_m*100) : 0;
            ?>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <?php if ($total_m > 0): ?>
                <div class="status-bar flex-grow-1" style="max-width:180px" title="<?= $pctAtt ?>% attended">
                    <div style="width:<?= $total_m>0?round($present/$total_m*100):0 ?>%;background:#22c55e"></div>
                    <div style="width:<?= $total_m>0?round($late/$total_m*100):0 ?>%;background:#f59e0b"></div>
                    <div style="width:<?= $total_m>0?round($excused/$total_m*100):0 ?>%;background:#6366f1"></div>
                    <div style="width:<?= $total_m>0?round($absent/$total_m*100):0 ?>%;background:#ef4444"></div>
                </div>
                <span class="small text-muted"><?= $present ?> present · <?= $absent ?> absent · <?= $late ?> late</span>
                <?php else: ?>
                <span class="small text-muted"><i class="fas fa-exclamation-circle me-1 text-warning"></i>Not yet marked</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!$isLearner): ?>
        <div class="d-flex gap-1 flex-shrink-0">
            <?php if (empty($s['is_locked'])): ?>
            <a href="<?= url('elms/attendance/'.$s['id'].'/mark') ?>" class="btn btn-sm btn-primary" style="border-radius:8px;font-size:.75rem" title="Mark Attendance"><i class="fas fa-pen"></i></a>
            <?php else: ?>
            <a href="<?= url('elms/attendance/'.$s['id'].'/mark') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.75rem" title="View Attendance"><i class="fas fa-eye"></i></a>
            <?php endif; ?>
            <?php if (empty($s['is_locked'])): ?>
            <a href="<?= url('elms/attendance/'.$s['id'].'/edit') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.75rem"><i class="fas fa-edit"></i></a>
            <form method="POST" action="<?= url('elms/attendance/'.$s['id'].'/delete') ?>" onsubmit="return confirm('Delete this session?')">
                <?= csrfField() ?>
                <button class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:.75rem"><i class="fas fa-trash"></i></button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<?php if ($totalPages > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>">
        <a class="page-link" href="?page=<?= $p ?>&course=<?= $courseId ?>&month=<?= $month ?>"><?= $p ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>
