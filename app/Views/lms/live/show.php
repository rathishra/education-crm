<style>
.detail-row { display:flex; align-items:flex-start; gap:.75rem; padding:.5rem 0; border-bottom:1px solid #f1f0ff; font-size:.85rem; }
.detail-row:last-child { border-bottom:none; }
.detail-icon { width:18px; text-align:center; color:#6366f1; flex-shrink:0; margin-top:.1rem; }
.big-countdown { display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap; margin:.75rem 0; }
.cd-unit { text-align:center; background:#f8f7ff; border-radius:12px; padding:.5rem .75rem; min-width:60px; }
.cd-unit .num { font-size:1.8rem; font-weight:900; color:#4f46e5; line-height:1; }
.cd-unit .lbl { font-size:.6rem; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; }
.platform-badge { display:inline-flex; align-items:center; gap:.4rem; font-size:.75rem; font-weight:700; padding:.3rem .8rem; border-radius:20px; color:#fff; }
.live-badge { animation:liveBlink 1.5s ease-in-out infinite; }
@keyframes liveBlink { 0%,100%{opacity:1} 50%{opacity:.6} }
.att-pill { font-size:.72rem; font-weight:700; padding:.2rem .6rem; border-radius:8px; }
</style>

<?php
$schedTs   = strtotime($class['scheduled_at']);
$endTs     = $schedTs + ((int)$class['duration_mins'] * 60);
$now       = time();
$secLeft   = max(0, $schedTs - $now);
$isLearner = $lmsUser && $lmsUser['role'] === 'learner';
$platformColors = ['zoom'=>'#2D8CFF','google_meet'=>'#00897B','teams'=>'#5059C9','webex'=>'#05A53A','custom'=>'#64748b'];
$platformIcons  = ['zoom'=>'fas fa-video','google_meet'=>'fab fa-google','teams'=>'fab fa-microsoft','webex'=>'fas fa-video','custom'=>'fas fa-globe'];
$platformColor  = $platformColors[$class['platform']] ?? '#64748b';
$platformIcon   = $platformIcons[$class['platform']]  ?? 'fas fa-globe';
$statusConfig   = [
    'scheduled' => ['bg'=>'#e0f2fe','color'=>'#0284c7','label'=>'Scheduled'],
    'live'      => ['bg'=>'#fee2e2','color'=>'#dc2626','label'=>'LIVE NOW'],
    'ended'     => ['bg'=>'#f1f5f9','color'=>'#64748b','label'=>'Ended'],
    'cancelled' => ['bg'=>'#fee2e2','color'=>'#dc2626','label'=>'Cancelled'],
];
$sc = $statusConfig[$class['status']] ?? $statusConfig['scheduled'];
?>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <a href="<?= url('elms/live') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div style="flex:1;min-width:0">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h4 class="fw-bold mb-0" style="color:#0f172a;font-size:1.05rem"><?= e($class['title']) ?></h4>
            <span class="badge <?= $class['status']==='live'?'live-badge':'' ?>" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;border-radius:8px;font-size:.7rem;font-weight:700">
                <?php if ($class['status']==='live'): ?><span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#dc2626;margin-right:.3rem"></span><?php endif; ?>
                <?= $sc['label'] ?>
            </span>
        </div>
        <div class="text-muted small mt-1"><?= e($class['course_title']) ?></div>
    </div>
    <?php if (!$isLearner): ?>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($class['status'] === 'scheduled'): ?>
        <button class="btn btn-danger btn-sm" style="border-radius:8px" onclick="setStatus('live')"><i class="fas fa-broadcast-tower me-1"></i>Go Live</button>
        <a href="<?= url('elms/live/'.$class['id'].'/edit') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-edit me-1"></i>Edit</a>
        <button class="btn btn-outline-danger btn-sm" style="border-radius:8px" onclick="setStatus('cancelled')"><i class="fas fa-ban me-1"></i>Cancel</button>
        <?php elseif ($class['status'] === 'live'): ?>
        <button class="btn btn-outline-secondary btn-sm" style="border-radius:8px" onclick="setStatus('ended')"><i class="fas fa-stop me-1"></i>End Session</button>
        <?php endif; ?>
        <?php if (!$isLearner): ?>
        <form method="POST" action="<?= url('elms/live/'.$class['id'].'/delete') ?>" onsubmit="return confirm('Delete this live class?')" class="d-inline">
            <?= csrfField() ?>
            <button class="btn btn-outline-danger btn-sm" style="border-radius:8px"><i class="fas fa-trash"></i></button>
        </form>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div class="row g-3">
    <!-- Main column -->
    <div class="col-12 col-lg-8">

        <!-- Countdown / Join section -->
        <div class="bg-white rounded-3 border p-4 mb-3 text-center" style="border-color:#e8e3ff!important">
            <div class="platform-badge mb-3" style="background:<?= $platformColor ?>"><i class="<?= $platformIcon ?>"></i><?= ucwords(str_replace('_',' ',$class['platform'])) ?></div>

            <?php if ($isEnded): ?>
                <?php if ($class['recording_url']): ?>
                <div class="mb-3">
                    <div class="fw-bold mb-1" style="color:#0f172a"><i class="fas fa-play-circle me-2 text-success"></i>Recording Available</div>
                    <?php if ($class['recording_password']): ?>
                    <div class="text-muted small mb-2"><i class="fas fa-key me-1"></i>Password: <strong><?= e($class['recording_password']) ?></strong></div>
                    <?php endif; ?>
                    <a href="<?= e($class['recording_url']) ?>" target="_blank" class="btn btn-success" style="border-radius:9px"><i class="fas fa-play me-2"></i>Watch Recording</a>
                </div>
                <?php else: ?>
                <div class="text-muted small py-2"><i class="fas fa-info-circle me-1"></i>Session has ended. No recording is available yet.</div>
                <?php endif; ?>

            <?php elseif ($class['status'] === 'live' || $canJoin): ?>
                <div class="fw-bold mb-2" style="color:#dc2626;font-size:1rem"><i class="fas fa-broadcast-tower me-2"></i>Session is Live!</div>
                <a href="<?= url('elms/live/'.$class['id'].'/join') ?>" target="_blank" class="btn btn-danger btn-lg" style="border-radius:10px">
                    <i class="fas fa-sign-in-alt me-2"></i>Join Now
                </a>
                <?php if ($class['meeting_id']): ?>
                <div class="text-muted small mt-2">Meeting ID: <strong><?= e($class['meeting_id']) ?></strong><?= $class['meeting_password']?' · Passcode: <strong>'.e($class['meeting_password']).'</strong>':'' ?></div>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-muted small mb-2 fw-semibold">Class starts in</div>
                <div class="big-countdown" id="countdownBlock">
                    <div class="cd-unit"><div class="num" id="cdH">--</div><div class="lbl">Hours</div></div>
                    <div class="cd-unit"><div class="num" id="cdM">--</div><div class="lbl">Min</div></div>
                    <div class="cd-unit"><div class="num" id="cdS">--</div><div class="lbl">Sec</div></div>
                </div>
                <div class="text-muted small"><?= date('l, d F Y \a\t H:i', $schedTs) ?></div>
                <?php if ($class['meeting_id']): ?>
                <div class="mt-2 small text-muted">Meeting ID: <strong><?= e($class['meeting_id']) ?></strong><?= $class['meeting_password']?' · Passcode: <strong>'.e($class['meeting_password']).'</strong>':'' ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Description -->
        <?php if ($class['description']): ?>
        <div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
            <h6 class="fw-bold mb-2" style="color:#0f172a"><i class="fas fa-align-left me-2 text-primary"></i>About this Session</h6>
            <p class="mb-0 small" style="color:#374151;line-height:1.7"><?= nl2br(e($class['description'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Recording upload (instructor, post-session) -->
        <?php if (!$isLearner && in_array($class['status'], ['ended','live'])): ?>
        <div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
            <h6 class="fw-bold mb-3" style="color:#0f172a"><i class="fas fa-video me-2 text-primary"></i>Recording URL</h6>
            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <input type="url" id="recUrl" class="form-control form-control-sm" placeholder="https://..." value="<?= e($class['recording_url'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" id="recPass" class="form-control form-control-sm" placeholder="Password (opt.)" value="<?= e($class['recording_password'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100" style="border-radius:8px" onclick="saveRecording()"><i class="fas fa-save"></i></button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Host notes (instructor only) -->
        <?php if (!$isLearner && $class['host_notes']): ?>
        <div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
            <h6 class="fw-bold mb-2" style="color:#0f172a"><i class="fas fa-sticky-note me-2 text-warning"></i>Host Notes</h6>
            <p class="mb-0 small" style="color:#374151"><?= nl2br(e($class['host_notes'])) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-12 col-lg-4">
        <!-- Details card -->
        <div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
            <h6 class="fw-bold mb-2" style="color:#0f172a"><i class="fas fa-info-circle me-2 text-primary"></i>Details</h6>
            <div class="detail-row"><i class="<?= $platformIcon ?> detail-icon"></i><div><?= ucwords(str_replace('_',' ',$class['platform'])) ?></div></div>
            <div class="detail-row"><i class="fas fa-calendar detail-icon"></i><div><?= date('D, d M Y', $schedTs) ?></div></div>
            <div class="detail-row"><i class="fas fa-clock detail-icon"></i><div><?= date('H:i', $schedTs) ?> — <?= date('H:i', $endTs) ?> (<?= $class['duration_mins'] ?>min)</div></div>
            <div class="detail-row"><i class="fas fa-book-open detail-icon"></i><div><?= e($class['course_title']) ?></div></div>
            <div class="detail-row"><i class="fas fa-user-tie detail-icon"></i><div><?= e($class['instructor_name']) ?></div></div>
            <?php if ($class['max_participants']): ?>
            <div class="detail-row"><i class="fas fa-users detail-icon"></i><div>Max <?= $class['max_participants'] ?> participants</div></div>
            <?php endif; ?>
            <?php if ($class['is_recorded']): ?>
            <div class="detail-row"><i class="fas fa-record-vinyl detail-icon"></i><div>Will be recorded</div></div>
            <?php endif; ?>
        </div>

        <!-- Participants (instructor) -->
        <?php if (!$isLearner && !empty($participants)): ?>
        <div class="bg-white rounded-3 border" style="border-color:#e8e3ff!important;overflow:hidden">
            <div class="p-3 border-bottom d-flex justify-content-between" style="border-color:#e8e3ff!important">
                <span class="fw-bold small" style="color:#0f172a"><i class="fas fa-users me-2 text-primary"></i>Participants</span>
                <span class="text-muted small"><?= count($participants) ?> registered · <?= count(array_filter($participants, fn($p) => $p['attended'])) ?> attended</span>
            </div>
            <div style="max-height:320px;overflow-y:auto">
                <?php foreach ($participants as $p): ?>
                <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom" style="border-color:#f8fafc!important;font-size:.8rem">
                    <div style="width:30px;height:30px;border-radius:50%;background:#ede9fe;color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0"><?= strtoupper(substr($p['student_name'],0,2)) ?></div>
                    <div style="flex:1;min-width:0">
                        <div class="fw-semibold" style="color:#0f172a;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($p['student_name']) ?></div>
                    </div>
                    <?php if ($p['attended']): ?>
                    <span class="att-pill" style="background:#d1fae5;color:#065f46"><i class="fas fa-check me-1"></i>Attended</span>
                    <?php else: ?>
                    <span class="att-pill" style="background:#f1f5f9;color:#64748b">Registered</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php elseif ($isLearner && $myReg): ?>
        <div class="bg-white rounded-3 border p-3" style="border-color:#e8e3ff!important">
            <h6 class="fw-bold mb-2" style="color:#0f172a"><i class="fas fa-check-circle me-2 text-success"></i>Your Status</h6>
            <?php if ($myReg['attended']): ?>
            <span class="badge" style="background:#d1fae5;color:#065f46;border-radius:8px"><i class="fas fa-check me-1"></i>Attended</span>
            <?php if ($myReg['joined_at']): ?>
            <div class="text-muted small mt-2">Joined: <?= date('H:i', strtotime($myReg['joined_at'])) ?></div>
            <?php endif; ?>
            <?php else: ?>
            <span class="badge" style="background:#fef3c7;color:#92400e;border-radius:8px">Registered</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const CLASS_ID = <?= $class['id'] ?>;
const CSRF     = '<?= csrfToken() ?>';

<?php if (!$isEnded && !$canJoin && $secLeft > 0): ?>
(function() {
    const end = <?= $schedTs * 1000 ?>;
    function tick() {
        const left = Math.max(0, end - Date.now());
        if (left === 0) { location.reload(); return; }
        const h = Math.floor(left/3600000);
        const m = Math.floor((left%3600000)/60000);
        const s = Math.floor((left%60000)/1000);
        document.getElementById('cdH').textContent = String(h).padStart(2,'0');
        document.getElementById('cdM').textContent = String(m).padStart(2,'0');
        document.getElementById('cdS').textContent = String(s).padStart(2,'0');
        setTimeout(tick, 1000);
    }
    tick();
})();
<?php endif; ?>

function setStatus(status) {
    const labels = {live:'Go Live?', ended:'End this session?', cancelled:'Cancel this class?'};
    if (!confirm(labels[status] || 'Confirm?')) return;
    fetch('<?= url("elms/live/{$class['id']}/status") ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF, status}),
    }).then(r => r.json()).then(d => {
        if (d.status === 'ok') location.reload();
        else alert(d.error || 'Failed');
    });
}

function saveRecording() {
    const url  = document.getElementById('recUrl').value.trim();
    const pass = document.getElementById('recPass').value.trim();
    if (!url) { alert('Please enter a recording URL'); return; }
    fetch('<?= url("elms/live/{$class['id']}/recording") ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF, recording_url: url, recording_password: pass}),
    }).then(r => r.json()).then(d => {
        if (d.status === 'ok') { alert('Recording saved!'); location.reload(); }
        else alert(d.error || 'Failed');
    });
}
</script>
