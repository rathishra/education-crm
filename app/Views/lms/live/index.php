<style>
.live-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; overflow:hidden; transition:box-shadow .15s,transform .15s; height:100%; }
.live-card:hover { box-shadow:0 6px 20px rgba(99,102,241,.12); transform:translateY(-2px); }
.live-card-header { padding:.85rem 1rem .65rem; border-bottom:1px solid #f1f0ff; }
.platform-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.85rem; flex-shrink:0; }
.status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:.3rem; }
.countdown-val { font-size:1rem; font-weight:800; color:#4f46e5; }
.live-pulse { animation:livePulse 1.5s ease-in-out infinite; }
@keyframes livePulse { 0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(239,68,68,.4)} 50%{opacity:.8;box-shadow:0 0 0 6px rgba(239,68,68,0)} }
</style>

<?php
$platformIcons = [
    'zoom'        => ['bg'=>'#2D8CFF','icon'=>'fas fa-video',       'label'=>'Zoom'],
    'google_meet' => ['bg'=>'#00897B','icon'=>'fab fa-google',      'label'=>'Google Meet'],
    'teams'       => ['bg'=>'#5059C9','icon'=>'fab fa-microsoft',   'label'=>'Teams'],
    'webex'       => ['bg'=>'#05A53A','icon'=>'fas fa-video',       'label'=>'Webex'],
    'custom'      => ['bg'=>'#64748b','icon'=>'fas fa-globe',       'label'=>'Custom'],
];
$statusConfig = [
    'scheduled' => ['color'=>'#0284c7','bg'=>'#e0f2fe','dot'=>'#0284c7','label'=>'Scheduled'],
    'live'      => ['color'=>'#dc2626','bg'=>'#fee2e2','dot'=>'#dc2626','label'=>'LIVE'],
    'ended'     => ['color'=>'#64748b','bg'=>'#f1f5f9','dot'=>'#64748b','label'=>'Ended'],
    'cancelled' => ['color'=>'#dc2626','bg'=>'#fee2e2','dot'=>'#dc2626','label'=>'Cancelled'],
];
?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-broadcast-tower me-2 text-primary"></i>Live Classes</h4>
    <?php if ($lmsUser && $lmsUser['role'] !== 'learner'): ?>
    <a href="<?= url('elms/live/create') ?>" class="btn btn-primary btn-sm" style="border-radius:8px"><i class="fas fa-plus me-1"></i>Schedule Class</a>
    <?php endif; ?>
</div>

<!-- Filters -->
<form method="GET" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-auto">
            <div class="btn-group btn-group-sm" role="group">
                <a href="?tab=upcoming&course=<?= $courseId ?>" class="btn <?= $tab==='upcoming'?'btn-primary':'btn-outline-secondary' ?>" style="border-radius:8px 0 0 8px"><i class="fas fa-clock me-1"></i>Upcoming</a>
                <a href="?tab=past&course=<?= $courseId ?>" class="btn <?= $tab==='past'?'btn-primary':'btn-outline-secondary' ?>" style="border-radius:0 8px 8px 0"><i class="fas fa-history me-1"></i>Past</a>
            </div>
        </div>
        <div class="col-12 col-sm-auto">
            <select name="course" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php foreach ($myCourses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="tab" value="<?= e($tab) ?>">
        </div>
        <?php if ($courseId): ?>
        <div class="col-auto"><a href="?tab=<?= $tab ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-times me-1"></i>Clear</a></div>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($classes)): ?>
<div class="text-center py-5" style="color:#94a3b8">
    <i class="fas fa-broadcast-tower" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">No <?= $tab ?> live classes<?= $courseId?' for this course':'' ?></p>
    <?php if ($lmsUser && $lmsUser['role'] !== 'learner' && $tab === 'upcoming'): ?>
    <a href="<?= url('elms/live/create') ?>" class="btn btn-sm btn-primary mt-1" style="border-radius:8px"><i class="fas fa-plus me-1"></i>Schedule First Class</a>
    <?php endif; ?>
</div>
<?php else: ?>

<div class="row g-3">
<?php foreach ($classes as $lc): ?>
<?php
$pInfo   = $platformIcons[$lc['platform']] ?? $platformIcons['custom'];
$sConfig = $statusConfig[$lc['status']] ?? $statusConfig['scheduled'];
$schedTs = strtotime($lc['scheduled_at']);
$endTs   = $schedTs + ((int)$lc['duration_mins'] * 60);
$now     = time();
$isLive  = $lc['status'] === 'live';
$isEnded = in_array($lc['status'], ['ended','cancelled']);
$secLeft = max(0, $schedTs - $now);
$isLearner = $lmsUser && $lmsUser['role'] === 'learner';
?>
<div class="col-12 col-md-6 col-xl-4">
<div class="live-card">
    <div class="live-card-header">
        <div class="d-flex align-items-start gap-2">
            <div class="platform-icon" style="background:<?= $pInfo['bg'] ?>;color:#fff"><i class="<?= $pInfo['icon'] ?>"></i></div>
            <div style="flex:1;min-width:0">
                <div class="fw-bold" style="color:#0f172a;font-size:.9rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($lc['title']) ?></div>
                <div class="text-muted small"><?= e($lc['course_title']) ?></div>
            </div>
            <span class="badge" style="background:<?= $sConfig['bg'] ?>;color:<?= $sConfig['color'] ?>;border-radius:8px;font-size:.65rem;font-weight:700;flex-shrink:0">
                <?php if ($isLive): ?><span class="status-dot live-pulse" style="background:<?= $sConfig['dot'] ?>"></span><?php endif; ?>
                <?= $sConfig['label'] ?>
            </span>
        </div>
    </div>

    <div class="p-3">
        <!-- Date/Time -->
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fas fa-calendar text-primary" style="font-size:.8rem;width:14px"></i>
            <span class="small fw-semibold" style="color:#374151"><?= date('D, d M Y', $schedTs) ?></span>
        </div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fas fa-clock text-primary" style="font-size:.8rem;width:14px"></i>
            <span class="small" style="color:#64748b"><?= date('H:i', $schedTs) ?> — <?= date('H:i', $endTs) ?> (<?= $lc['duration_mins'] ?> min)</span>
        </div>
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fas fa-user-tie text-primary" style="font-size:.8rem;width:14px"></i>
            <span class="small" style="color:#64748b"><?= e($lc['instructor_name']) ?></span>
        </div>

        <?php if (!$isEnded && !$isLive && $secLeft > 0): ?>
        <!-- Countdown -->
        <div class="text-center py-2 mb-2" style="background:#f8f7ff;border-radius:10px">
            <div class="text-muted" style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em">Starts in</div>
            <div class="countdown-val" id="cd<?= $lc['id'] ?>" data-ts="<?= $schedTs ?>">
                <?php
                $h = floor($secLeft / 3600); $m = floor(($secLeft % 3600)/60); $s = $secLeft % 60;
                echo $h > 0 ? "{$h}h {$m}m" : ($m > 0 ? "{$m}m {$s}s" : "{$s}s");
                ?>
            </div>
        </div>
        <?php elseif ($isLive): ?>
        <div class="text-center py-2 mb-2" style="background:#fee2e2;border-radius:10px">
            <span class="fw-bold" style="color:#dc2626;font-size:.85rem"><span class="status-dot live-pulse" style="background:#dc2626"></span>Live Now!</span>
        </div>
        <?php elseif ($isEnded && $lc['recording_url']): ?>
        <div class="text-center py-2 mb-2" style="background:#f0fdf4;border-radius:10px">
            <i class="fas fa-play-circle me-1" style="color:#16a34a"></i>
            <span class="small fw-bold" style="color:#16a34a">Recording Available</span>
        </div>
        <?php endif; ?>

        <?php if (!$isLearner): ?>
        <div class="d-flex align-items-center gap-2 mb-2 text-muted" style="font-size:.72rem">
            <i class="fas fa-users"></i><?= $lc['reg_count'] ?> registered &middot; <?= $lc['attended_count'] ?> attended
        </div>
        <?php elseif (!empty($lc['i_attended'])): ?>
        <div class="mb-2"><span class="badge" style="background:#d1fae5;color:#065f46;border-radius:8px;font-size:.7rem"><i class="fas fa-check me-1"></i>Attended</span></div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= url('elms/live/'.$lc['id']) ?>" class="btn btn-sm btn-outline-secondary flex-grow-1" style="border-radius:8px;font-size:.75rem"><i class="fas fa-eye me-1"></i>Details</a>
            <?php if (!$isEnded && ($isLive || ($secLeft >= 0 && $secLeft <= 900))): ?>
            <a href="<?= url('elms/live/'.$lc['id'].'/join') ?>" class="btn btn-sm btn-success flex-grow-1" style="border-radius:8px;font-size:.75rem" target="_blank"><i class="fas fa-sign-in-alt me-1"></i>Join</a>
            <?php elseif (!$isLearner && !$isEnded): ?>
            <a href="<?= url('elms/live/'.$lc['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.75rem"><i class="fas fa-edit"></i></a>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-4"><ul class="pagination pagination-sm justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>"><a class="page-link" href="?tab=<?= $tab ?>&course=<?= $courseId ?>&page=<?= $p ?>"><?= $p ?></a></li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>

<script>
// Countdown timers
document.querySelectorAll('[id^="cd"]').forEach(el => {
    const end = parseInt(el.dataset.ts) * 1000;
    function tick() {
        const left = Math.max(0, end - Date.now());
        if (left === 0) { el.textContent = 'Starting…'; return; }
        const h = Math.floor(left/3600000);
        const m = Math.floor((left%3600000)/60000);
        const s = Math.floor((left%60000)/1000);
        el.textContent = h > 0 ? `${h}h ${m}m` : (m > 0 ? `${m}m ${s}s` : `${s}s`);
        setTimeout(tick, 1000);
    }
    tick();
});
</script>
