<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
.ana-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; padding:1.1rem 1.25rem; margin-bottom:1rem; }
.stat-pill { background:#f8f7ff; border-radius:12px; border:1px solid #e8e3ff; padding:.7rem 1rem; text-align:center; }
.stat-pill .val { font-size:1.4rem; font-weight:900; color:#4f46e5; line-height:1; }
.stat-pill .lbl { font-size:.68rem; color:#64748b; margin-top:.15rem; }
.mini-bar { height:7px; border-radius:10px; background:#f1f5f9; overflow:hidden; }
.mini-fill { height:100%; border-radius:10px; }
.stu-row { display:flex; align-items:center; gap:.75rem; padding:.55rem .75rem; border-radius:9px; border:1px solid #f1f0ff; margin-bottom:.3rem; font-size:.8rem; }
</style>

<div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
    <a href="<?= url('elms/analytics/courses') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div style="flex:1;min-width:0">
        <h4 class="fw-bold mb-0" style="color:#0f172a;font-size:1.05rem"><i class="fas fa-chart-bar me-2 text-primary"></i><?= e($course['title']) ?></h4>
    </div>
    <div class="d-flex gap-2">
        <?php foreach (['7'=>'7d','30'=>'30d','90'=>'90d','365'=>'1y'] as $v=>$l): ?>
        <a href="?range=<?= $v ?>" class="btn btn-sm <?= $range===$v?'btn-primary':'btn-outline-secondary' ?>" style="border-radius:20px;font-size:.72rem"><?= $l ?></a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Summary pills -->
<div class="row g-2 mb-3">
    <?php
    $enrolled    = count($students);
    $completed   = count(array_filter($students, fn($s) => (float)$s['progress_pct'] >= 100));
    $compRate    = $enrolled > 0 ? round($completed/$enrolled*100,1) : 0;
    $avgProgress = $enrolled > 0 ? round(array_sum(array_column($students,'progress_pct'))/$enrolled,1) : 0;
    ?>
    <?php foreach ([
        ['Enrolled', $enrolled, '#4f46e5'],
        ['Completed', $completed, '#16a34a'],
        ['Comp. Rate', $compRate.'%', '#0284c7'],
        ['Avg Progress', $avgProgress.'%', '#d97706'],
        ['Sessions', $attStats['sessions'], '#6366f1'],
        ['Avg Attend.', ($attStats['avg_attendance_pct']??0).'%', '#db2777'],
        ['Threads', $forumStats['threads'], '#9333ea'],
        ['Drop-off', $dropOff, '#ef4444'],
    ] as [$l,$v,$c]): ?>
    <div class="col-6 col-sm-3 col-xl-3">
        <div class="stat-pill"><div class="val" style="color:<?= $c ?>"><?= $v ?></div><div class="lbl"><?= $l ?></div></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <!-- Enrollment trend -->
    <div class="col-12 col-lg-8">
        <div class="ana-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-users me-2 text-primary"></i>Enrollment Trend</div>
            <canvas id="enrollChart" height="90"></canvas>
        </div>
    </div>

    <!-- Student progress distribution -->
    <div class="col-12 col-lg-4">
        <div class="ana-card h-100">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-chart-pie me-2 text-primary"></i>Progress Distribution</div>
            <canvas id="progChart" height="160"></canvas>
        </div>
    </div>
</div>

<!-- Lesson completion funnel -->
<?php if (!empty($lessonStats)): ?>
<div class="ana-card mt-3">
    <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-film me-2 text-primary"></i>Lesson Completion Rates</div>
    <?php foreach ($lessonStats as $ls): ?>
    <?php
    $enrolled = max(1, (int)$ls['enrolled']);
    $pct      = round($ls['completions'] / $enrolled * 100);
    $typeIcon = ['video'=>'fa-play-circle','text'=>'fa-file-alt','document'=>'fa-file-pdf','quiz'=>'fa-question','assignment'=>'fa-tasks','live'=>'fa-broadcast-tower','scorm'=>'fa-cube'][$ls['type']] ?? 'fa-file';
    ?>
    <div class="mb-2">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="small" style="color:#374151;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:70%">
                <i class="fas <?= $typeIcon ?> me-1 text-primary" style="font-size:.7rem"></i><?= e($ls['section_title']) ?> › <?= e($ls['title']) ?>
            </span>
            <span class="small fw-bold" style="color:<?= $pct>=75?'#16a34a':($pct>=40?'#d97706':'#dc2626') ?>"><?= $ls['completions'] ?>/<?= $enrolled ?> (<?= $pct ?>%)</span>
        </div>
        <div class="mini-bar">
            <div class="mini-fill" style="width:<?= $pct ?>%;background:<?= $pct>=75?'#22c55e':($pct>=40?'#f59e0b':'#ef4444') ?>"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-3 mt-0">
    <!-- Assignment stats -->
    <?php if (!empty($assignStats)): ?>
    <div class="col-12 col-lg-6">
        <div class="ana-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-tasks me-2 text-primary"></i>Assignment Scores</div>
            <canvas id="assignChart" height="120"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quiz stats -->
    <?php if (!empty($quizStats)): ?>
    <div class="col-12 col-lg-6">
        <div class="ana-card">
            <div class="fw-bold mb-3 small" style="color:#0f172a"><i class="fas fa-question-circle me-2 text-primary"></i>Quiz Performance</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0" style="font-size:.77rem">
                    <thead style="background:#f8f7ff">
                        <tr>
                            <th class="fw-semibold" style="color:#64748b">Quiz</th>
                            <th class="text-center fw-semibold" style="color:#64748b">Attempts</th>
                            <th class="text-center fw-semibold" style="color:#64748b">Avg</th>
                            <th class="fw-semibold" style="color:#64748b">Pass Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($quizStats as $q): ?>
                    <tr>
                        <td class="fw-semibold" style="color:#0f172a"><?= e(mb_strimwidth($q['title'],0,24,'…')) ?></td>
                        <td class="text-center text-muted"><?= $q['attempts'] ?></td>
                        <td class="text-center fw-bold" style="color:#4f46e5"><?= $q['avg_pct'] ?>%</td>
                        <td>
                            <?php $pr = (float)$q['pass_rate']; ?>
                            <div class="d-flex align-items-center gap-1">
                                <div class="mini-bar" style="flex:1"><div class="mini-fill" style="width:<?= $pr ?>%;background:<?= $pr>=60?'#22c55e':'#ef4444' ?>"></div></div>
                                <span class="fw-bold" style="color:<?= $pr>=60?'#16a34a':'#dc2626' ?>;font-size:.75rem"><?= $pr ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Student engagement table -->
<?php if (!empty($students)): ?>
<div class="ana-card mt-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="fw-bold small" style="color:#0f172a"><i class="fas fa-users me-2 text-primary"></i>Student Engagement</span>
        <span class="text-muted small"><?= count($students) ?> students</span>
    </div>
    <?php foreach ($students as $st): ?>
    <?php $pct = (int)$st['progress_pct']; ?>
    <div class="stu-row">
        <div style="width:32px;height:32px;border-radius:50%;background:#ede9fe;color:#6366f1;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0"><?= strtoupper(substr($st['name'],0,2)) ?></div>
        <div style="flex:1;min-width:0">
            <a href="<?= url('elms/analytics/student/'.$st['id']) ?>" class="fw-semibold text-decoration-none" style="color:#0f172a;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;display:block"><?= e($st['name']) ?></a>
        </div>
        <div style="min-width:100px">
            <div class="d-flex justify-content-between mb-1" style="font-size:.68rem;color:#94a3b8"><span>Progress</span><span><?= $pct ?>%</span></div>
            <div class="mini-bar"><div class="mini-fill" style="width:<?= $pct ?>%;background:<?= $pct>=75?'#22c55e':($pct>=25?'#6366f1':'#f59e0b') ?>"></div></div>
        </div>
        <div class="text-center flex-shrink-0" style="min-width:55px">
            <div class="fw-bold" style="font-size:.82rem;color:#0284c7"><?= $st['submissions'] ?></div>
            <div class="text-muted" style="font-size:.65rem">Submissions</div>
        </div>
        <div class="text-center flex-shrink-0" style="min-width:50px">
            <div class="fw-bold" style="font-size:.82rem;color:<?= $st['best_quiz']!==null?'#4f46e5':'#94a3b8' ?>"><?= $st['best_quiz'] !== null ? number_format((float)$st['best_quiz'],0).'%' : '—' ?></div>
            <div class="text-muted" style="font-size:.65rem">Best Quiz</div>
        </div>
        <div class="text-center flex-shrink-0" style="min-width:50px">
            <div class="fw-bold" style="font-size:.82rem;color:#f59e0b"><?= number_format($st['xp_points']) ?></div>
            <div class="text-muted" style="font-size:.65rem">XP</div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
Chart.defaults.color = '#64748b';

// Enrollment trend
<?php
$days = []; $cnts = [];
$eMap = array_column($enrollTrend, 'cnt', 'day');
for ($i = $rangeInt-1; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $days[] = date('d M', strtotime($d));
    $cnts[] = (int)($eMap[$d] ?? 0);
}
?>
new Chart(document.getElementById('enrollChart'), {
    type:'line',
    data:{ labels:<?= json_encode($days) ?>, datasets:[{
        label:'Enrollments', data:<?= json_encode($cnts) ?>,
        borderColor:'#6366f1', backgroundColor:'rgba(99,102,241,.07)',
        borderWidth:2, fill:true, tension:.4, pointRadius:<?= $rangeInt<=30?3:0 ?>
    }]},
    options:{ plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,grid:{color:'#f1f5f9'}},x:{grid:{display:false},ticks:{maxTicksLimit:<?= min($rangeInt,10) ?>}}}}
});

// Progress distribution donut
<?php
$buckets = ['0%'=>0,'1-25%'=>0,'26-50%'=>0,'51-75%'=>0,'76-99%'=>0,'100%'=>0];
foreach ($students as $s) {
    $p = (float)$s['progress_pct'];
    if ($p == 0)        $buckets['0%']++;
    elseif ($p <= 25)   $buckets['1-25%']++;
    elseif ($p <= 50)   $buckets['26-50%']++;
    elseif ($p <= 75)   $buckets['51-75%']++;
    elseif ($p < 100)   $buckets['76-99%']++;
    else                $buckets['100%']++;
}
?>
new Chart(document.getElementById('progChart'), {
    type:'doughnut',
    data:{
        labels:<?= json_encode(array_keys($buckets)) ?>,
        datasets:[{data:<?= json_encode(array_values($buckets)) ?>, backgroundColor:['#e2e8f0','#fbbf24','#f59e0b','#60a5fa','#6366f1','#22c55e'], borderWidth:2, borderColor:'#fff'}]
    },
    options:{plugins:{legend:{position:'bottom',labels:{boxWidth:10,padding:6,font:{size:11}}}}, cutout:'62%'}
});

// Assignment avg scores bar
<?php if (!empty($assignStats)): ?>
const aLabels = <?= json_encode(array_map(fn($a) => mb_strimwidth($a['title'],0,20,'…'), $assignStats)) ?>;
const aAvg    = <?= json_encode(array_map(fn($a) => round((float)($a['avg_pct']??0),1), $assignStats)) ?>;
new Chart(document.getElementById('assignChart'), {
    type:'bar',
    data:{labels:aLabels, datasets:[{
        label:'Avg Score %', data:aAvg,
        backgroundColor:'rgba(99,102,241,.7)', borderRadius:6, borderSkipped:false,
    }]},
    options:{
        plugins:{legend:{display:false}},
        scales:{y:{beginAtZero:true,max:100,grid:{color:'#f1f5f9'},ticks:{callback:v=>v+'%'}},x:{grid:{display:false}}}
    }
});
<?php endif; ?>
</script>
