<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
.stu-ana-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; padding:1.1rem 1.25rem; margin-bottom:1rem; }
.event-icon { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; flex-shrink:0; }
.enroll-bar { height:8px; border-radius:10px; background:#f1f5f9; overflow:hidden; }
.enroll-fill { height:100%; border-radius:10px; }
</style>

<?php
$eventConfig = [
    'lesson_completed'  => ['fas fa-check',          '#d1fae5','#065f46'],
    'quiz_submitted'    => ['fas fa-question-circle', '#dbeafe','#1d4ed8'],
    'assignment_submitted'=> ['fas fa-tasks',         '#fef3c7','#92400e'],
    'live_joined'       => ['fas fa-broadcast-tower', '#fee2e2','#dc2626'],
    'thread_created'    => ['fas fa-comments',        '#ede9fe','#4338ca'],
    'forum_reply'       => ['fas fa-reply',           '#f0fdf4','#16a34a'],
];
function _eventIcon(string $event): array {
    global $eventConfig;
    return $eventConfig[$event] ?? ['fas fa-circle','#f1f5f9','#64748b'];
}
function _ago2(string $dt): string {
    $d = time() - strtotime($dt);
    if ($d<60) return 'just now';
    if ($d<3600) return floor($d/60).'m ago';
    if ($d<86400) return floor($d/3600).'h ago';
    if ($d<604800) return floor($d/86400).'d ago';
    return date('d M Y', strtotime($dt));
}
?>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <a href="<?= url('elms/analytics') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div style="flex:1">
        <h4 class="fw-bold mb-0" style="color:#0f172a;font-size:1.05rem"><i class="fas fa-user me-2 text-primary"></i><?= e($student['name']) ?></h4>
        <div class="text-muted small"><?= e($student['email']) ?> &middot; Joined <?= date('d M Y', strtotime($student['joined_at'])) ?></div>
    </div>
    <a href="<?= url('elms/gradebook?course='.($enrollments[0]['course_id']??'')) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-star-half-alt me-1"></i>Grades</a>
</div>

<!-- Profile stats row -->
<div class="row g-2 mb-3">
    <?php foreach ([
        ['XP Points', number_format($student['xp_points']), 'fas fa-bolt','#fef3c7','#d97706'],
        ['Level',     $student['level'],                     'fas fa-layer-group','#ede9fe','#6366f1'],
        ['Courses',   count($enrollments),                   'fas fa-book-open','#dbeafe','#2563eb'],
        ['Activities',count($activity),                      'fas fa-fire','#fee2e2','#dc2626'],
    ] as [$lbl,$val,$icon,$bg,$col]): ?>
    <div class="col-6 col-md-3">
        <div class="stu-ana-card d-flex gap-3 align-items-center py-2 px-3 mb-0">
            <div style="width:38px;height:38px;border-radius:10px;background:<?= $bg ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="<?= $icon ?>" style="color:<?= $col ?>"></i></div>
            <div>
                <div style="font-size:1.4rem;font-weight:900;color:#0f172a;line-height:1"><?= $val ?></div>
                <div class="text-muted" style="font-size:.7rem"><?= $lbl ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <!-- XP trend line -->
    <div class="col-12 col-lg-8">
        <div class="stu-ana-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-bolt me-2 text-warning"></i>XP Earned (Last 30 Days)</div>
            <canvas id="xpChart" height="90"></canvas>
        </div>
    </div>

    <!-- Course progress -->
    <div class="col-12 col-lg-4">
        <div class="stu-ana-card h-100">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-book-open me-2 text-primary"></i>Course Progress</div>
            <?php if (empty($enrollments)): ?>
            <div class="text-muted small text-center py-3">No enrollments</div>
            <?php else: ?>
            <?php foreach ($enrollments as $en): ?>
            <?php $pct = (int)$en['progress_pct']; ?>
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-semibold" style="color:#0f172a;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:75%"><?= e($en['title']) ?></span>
                    <span class="small fw-bold" style="color:#6366f1"><?= $pct ?>%</span>
                </div>
                <div class="enroll-bar">
                    <div class="enroll-fill" style="width:<?= $pct ?>%;background:<?= $pct>=100?'#22c55e':($pct>=50?'#6366f1':'#f59e0b') ?>"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:.68rem"><?= $en['completed_lessons'] ?>/<?= $en['total_lessons'] ?> lessons &middot; <?= date('d M Y', strtotime($en['enrolled_at'])) ?></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3 mt-0">
    <!-- Quiz performance -->
    <?php if (!empty($quizPerf)): ?>
    <div class="col-12 col-lg-6">
        <div class="stu-ana-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-question-circle me-2 text-primary"></i>Quiz Performance</div>
            <canvas id="quizChart" height="130"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Assignment scores -->
    <?php if (!empty($assignPerf)): ?>
    <div class="col-12 col-lg-6">
        <div class="stu-ana-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-tasks me-2 text-primary"></i>Assignment Scores</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size:.77rem">
                    <thead style="background:#f8f7ff">
                        <tr>
                            <th class="fw-semibold" style="color:#64748b">Assignment</th>
                            <th class="text-center fw-semibold" style="color:#64748b">Score</th>
                            <th class="fw-semibold" style="color:#64748b">Graded</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($assignPerf as $a): ?>
                    <?php $pct = $a['max_score']>0 ? round($a['score']/$a['max_score']*100) : 0; ?>
                    <tr>
                        <td>
                            <?= e(mb_strimwidth($a['title'],0,28,'…')) ?>
                            <?php if ($a['is_late']): ?><span class="badge ms-1" style="background:#fee2e2;color:#dc2626;border-radius:4px;font-size:.6rem">Late</span><?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold" style="color:<?= $pct>=60?'#16a34a':'#dc2626' ?>"><?= $a['score'] ?>/<?= $a['max_score'] ?> (<?= $pct ?>%)</span>
                        </td>
                        <td class="text-muted" style="font-size:.7rem"><?= $a['graded_at'] ? date('d M', strtotime($a['graded_at'])) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Recent activity feed -->
<?php if (!empty($activity)): ?>
<div class="stu-ana-card mt-3">
    <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-history me-2 text-primary"></i>Recent Activity</div>
    <?php foreach ($activity as $act): ?>
    <?php [$icon, $bg, $col] = _eventIcon($act['event']); ?>
    <div class="d-flex gap-3 align-items-start mb-2 pb-2 border-bottom" style="border-color:#f8fafc!important">
        <div class="event-icon" style="background:<?= $bg ?>"><i class="<?= $icon ?>" style="color:<?= $col ?>"></i></div>
        <div style="flex:1;min-width:0">
            <div class="small fw-semibold" style="color:#0f172a"><?= e(ucwords(str_replace('_',' ',$act['event']))) ?></div>
            <?php if ($act['entity_title']): ?>
            <div class="text-muted" style="font-size:.72rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($act['entity_title']) ?></div>
            <?php endif; ?>
        </div>
        <?php if ($act['xp_earned'] > 0): ?>
        <span class="badge flex-shrink-0" style="background:#fef3c7;color:#92400e;border-radius:8px;font-size:.65rem">+<?= $act['xp_earned'] ?> XP</span>
        <?php endif; ?>
        <span class="text-muted flex-shrink-0" style="font-size:.7rem"><?= _ago2($act['created_at']) ?></span>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
Chart.defaults.color = '#64748b';

// XP trend
<?php
$xpMap = array_column($xpTrend, 'xp', 'day');
$xpDays = []; $xpVals = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $xpDays[] = date('d M', strtotime($d));
    $xpVals[] = (int)($xpMap[$d] ?? 0);
}
?>
new Chart(document.getElementById('xpChart'), {
    type:'bar',
    data:{
        labels:<?= json_encode($xpDays) ?>,
        datasets:[{
            label:'XP Earned', data:<?= json_encode($xpVals) ?>,
            backgroundColor:'rgba(245,158,11,.7)', borderRadius:4, borderSkipped:false,
        }]
    },
    options:{
        plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true,grid:{color:'#f1f5f9'}},x:{grid:{display:false},ticks:{maxTicksLimit:10}}}
    }
});

// Quiz performance radar/bar
<?php if (!empty($quizPerf)): ?>
new Chart(document.getElementById('quizChart'), {
    type:'bar',
    data:{
        labels:<?= json_encode(array_map(fn($q)=>mb_strimwidth($q['title'],0,18,'…'),$quizPerf)) ?>,
        datasets:[
            {label:'Best Score %', data:<?= json_encode(array_map(fn($q)=>round((float)($q['best_pct']??0),1),$quizPerf)) ?>, backgroundColor:'rgba(99,102,241,.7)', borderRadius:5},
            {label:'Pass %',       data:<?= json_encode(array_map(fn($q)=>$q['pass_percentage'],$quizPerf)) ?>,                  backgroundColor:'rgba(34,197,94,.25)',  borderRadius:5, type:'line', borderColor:'#22c55e',borderWidth:1.5,pointRadius:3,fill:false},
        ]
    },
    options:{
        plugins:{legend:{labels:{boxWidth:10,font:{size:11}}}},
        scales:{y:{beginAtZero:true,max:100,grid:{color:'#f1f5f9'},ticks:{callback:v=>v+'%'}},x:{grid:{display:false}}}
    }
});
<?php endif; ?>
</script>
