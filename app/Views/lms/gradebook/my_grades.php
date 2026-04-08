<style>
.grade-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; overflow:hidden; height:100%; transition:box-shadow .15s,transform .15s; }
.grade-card:hover { box-shadow:0 6px 20px rgba(99,102,241,.1); transform:translateY(-2px); }
.grade-hero { padding:1.25rem 1.25rem .75rem; }
.grade-ring { width:70px; height:70px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; flex-direction:column; }
.grade-ring .pct { font-size:.85rem; font-weight:900; line-height:1; }
.grade-ring .ltr { font-size:1.2rem; font-weight:900; line-height:1; }
.component-bar { display:flex; align-items:center; gap:.5rem; margin-bottom:.35rem; font-size:.75rem; }
.cb-label { min-width:80px; color:#64748b; }
.cb-bar { flex:1; height:6px; border-radius:10px; background:#f1f5f9; overflow:hidden; }
.cb-fill { height:100%; border-radius:10px; }
.cb-val { min-width:36px; text-align:right; font-weight:700; color:#374151; }
.no-grades { color:#94a3b8; text-align:center; padding:3rem 1rem; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-star me-2 text-primary"></i>My Grades</h4>
    <span class="text-muted small"><?= count($courseGrades) ?> course<?= count($courseGrades)!=1?'s':'' ?> enrolled</span>
</div>

<?php if (empty($courseGrades)): ?>
<div class="no-grades">
    <i class="fas fa-graduation-cap" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">No enrolled courses yet</p>
    <a href="<?= url('elms/courses') ?>" class="btn btn-sm btn-primary mt-1" style="border-radius:8px"><i class="fas fa-book-open me-1"></i>Browse Courses</a>
</div>
<?php else: ?>

<!-- GPA summary bar -->
<?php
$graded = array_filter($courseGrades, fn($g) => $g['final'] !== null);
$overallGpa = null;
if (!empty($graded)) {
    $scale = [['min'=>90,'gpa'=>4.0],['min'=>85,'gpa'=>3.7],['min'=>80,'gpa'=>3.3],['min'=>75,'gpa'=>3.0],
              ['min'=>70,'gpa'=>2.7],['min'=>65,'gpa'=>2.3],['min'=>60,'gpa'=>2.0],['min'=>55,'gpa'=>1.7],
              ['min'=>50,'gpa'=>1.0],['min'=>0,'gpa'=>0.0]];
    $gpas = [];
    foreach ($graded as $g) {
        foreach ($scale as $s) { if ($g['final'] >= $s['min']) { $gpas[] = $s['gpa']; break; } }
    }
    $overallGpa = $gpas ? round(array_sum($gpas)/count($gpas), 2) : null;
}
$avgPct = $graded ? round(array_sum(array_column(array_values($graded),'final')) / count($graded), 1) : null;
?>

<?php if ($avgPct !== null): ?>
<div class="bg-white rounded-3 border p-3 mb-4 d-flex align-items-center gap-4 flex-wrap" style="border-color:#e8e3ff!important">
    <div class="text-center">
        <div style="font-size:1.8rem;font-weight:900;color:#4f46e5"><?= $avgPct ?>%</div>
        <div class="text-muted" style="font-size:.7rem">Overall Average</div>
    </div>
    <?php if ($overallGpa !== null): ?>
    <div class="text-center">
        <div style="font-size:1.8rem;font-weight:900;color:#0284c7"><?= $overallGpa ?></div>
        <div class="text-muted" style="font-size:.7rem">GPA (4.0 scale)</div>
    </div>
    <?php endif; ?>
    <div class="flex-grow-1" style="min-width:200px">
        <div class="d-flex justify-content-between mb-1"><span class="small text-muted">Overall Progress</span><span class="small fw-bold" style="color:#4f46e5"><?= $avgPct ?>%</span></div>
        <div style="background:#f1f5f9;border-radius:10px;height:10px;overflow:hidden">
            <div style="width:<?= $avgPct ?>%;background:linear-gradient(90deg,#6366f1,#8b5cf6);height:100%;border-radius:10px"></div>
        </div>
    </div>
    <div class="text-center">
        <div style="font-size:1.8rem;font-weight:900;color:#16a34a"><?= count(array_filter($graded,fn($g)=>$g['final']>=60)) ?>/<?= count($graded) ?></div>
        <div class="text-muted" style="font-size:.7rem">Passing</div>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
<?php foreach ($courseGrades as $g): ?>
<?php
$final  = $g['final'];
$letter = $g['letter'];
$col    = ['color'=>'#64748b','bg'=>'#f1f5f9'];
if ($final !== null) {
    if ($final >= 90) $col = ['color'=>'#065f46','bg'=>'#d1fae5'];
    elseif ($final >= 75) $col = ['color'=>'#1d4ed8','bg'=>'#dbeafe'];
    elseif ($final >= 60) $col = ['color'=>'#92400e','bg'=>'#fef3c7'];
    else $col = ['color'=>'#991b1b','bg'=>'#fee2e2'];
}
?>
<div class="col-12 col-md-6 col-xl-4">
<div class="grade-card">
    <div class="grade-hero">
        <div class="d-flex gap-3 align-items-start">
            <!-- Grade ring -->
            <div class="grade-ring" style="background:<?= $col['bg'] ?>">
                <?php if ($final !== null): ?>
                <div class="pct" style="color:<?= $col['color'] ?>"><?= number_format($final,0) ?>%</div>
                <div class="ltr" style="color:<?= $col['color'] ?>"><?= $letter ?></div>
                <?php else: ?>
                <div style="color:#94a3b8;font-size:1.3rem"><i class="fas fa-hourglass-half"></i></div>
                <?php endif; ?>
            </div>
            <!-- Course info -->
            <div style="flex:1;min-width:0">
                <div class="fw-bold" style="color:#0f172a;font-size:.9rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($g['course_title']) ?></div>
                <div class="text-muted small mt-1">Enrolled <?= date('d M Y', strtotime($g['enrolled_at'])) ?></div>
                <?php if ($final !== null): ?>
                <span class="badge mt-1" style="background:<?= $final>=60?'#d1fae5':'#fee2e2' ?>;color:<?= $final>=60?'#065f46':'#991b1b' ?>;border-radius:8px;font-size:.65rem">
                    <?= $final >= 60 ? 'Passing' : 'At Risk' ?>
                </span>
                <?php else: ?>
                <span class="badge mt-1" style="background:#f1f5f9;color:#64748b;border-radius:8px;font-size:.65rem">No grades yet</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Component bars -->
    <div class="px-3 pb-2 border-top pt-2" style="border-color:#f1f0ff!important">
        <?php foreach ([
            ['Assignments', $g['assignAvg'], $g['weights']['assignments_pct'], '#6366f1'],
            ['Quizzes',     $g['quizAvg'],   $g['weights']['quizzes_pct'],     '#0284c7'],
            ['Attendance',  $g['att'],        $g['weights']['attendance_pct'],  '#16a34a'],
        ] as [$lbl, $avg, $wt, $color]): ?>
        <div class="component-bar">
            <span class="cb-label"><?= $lbl ?> <span class="text-muted" style="font-size:.65rem">(<?= $wt ?>%)</span></span>
            <div class="cb-bar"><div class="cb-fill" style="width:<?= min(100,(int)($avg??0)) ?>%;background:<?= $color ?>"></div></div>
            <span class="cb-val"><?= $avg !== null ? number_format((float)$avg,0).'%' : '—' ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Progress -->
    <div class="px-3 pb-3">
        <div class="d-flex justify-content-between mb-1" style="font-size:.72rem;color:#94a3b8">
            <span>Course progress</span>
            <span class="fw-bold" style="color:#6366f1"><?= (int)$g['progress_pct'] ?>%</span>
        </div>
        <div style="background:#f1f5f9;border-radius:10px;height:5px;overflow:hidden">
            <div style="width:<?= (int)$g['progress_pct'] ?>%;background:linear-gradient(90deg,#6366f1,#8b5cf6);height:100%;border-radius:10px"></div>
        </div>
    </div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
