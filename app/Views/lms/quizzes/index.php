<?php $isInstructor = in_array($lmsUser['role'] ?? '', ['lms_admin','instructor']); ?>
<style>
.quiz-card { background:#fff; border-radius:12px; border:1px solid #e8e3ff; box-shadow:0 1px 5px rgba(99,102,241,.06); transition:transform .15s,box-shadow .15s; }
.quiz-card:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(99,102,241,.12); }
.quiz-meta-pill { display:inline-flex; align-items:center; gap:.3rem; font-size:.72rem; color:#64748b; }
.pct-ring { width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:800; flex-shrink:0; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-question-circle me-2 text-primary"></i>Quizzes</h4>
        <div class="text-muted small mt-1"><?= number_format($total) ?> quiz<?= $total != 1 ? 'zes' : '' ?></div>
    </div>
    <?php if ($isInstructor): ?>
    <a href="<?= url('elms/quizzes/create') ?>" class="btn btn-primary" style="border-radius:9px"><i class="fas fa-plus me-2"></i>New Quiz</a>
    <?php endif; ?>
</div>

<div class="bg-white rounded-3 border p-3 mb-3" style="border-color:#e8e3ff!important">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search quizzes…" value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-6 col-md-4">
            <select name="course" class="form-select form-select-sm">
                <option value="">All Courses</option>
                <?php foreach ($myCourses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseId==(int)$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="border-radius:8px"><i class="fas fa-filter me-1"></i>Filter</button>
            <?php if ($search || $courseId): ?>
            <a href="<?= url('elms/quizzes') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (empty($quizzes)): ?>
<div class="text-center py-5">
    <div style="font-size:3.5rem;opacity:.15;color:#6366f1"><i class="fas fa-question-circle"></i></div>
    <h5 class="fw-bold mt-3 text-muted">No quizzes found</h5>
    <?php if ($isInstructor && !$search && !$courseId): ?>
    <a href="<?= url('elms/quizzes/create') ?>" class="btn btn-primary mt-2" style="border-radius:9px"><i class="fas fa-plus me-2"></i>Create First Quiz</a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="row g-3">
<?php foreach ($quizzes as $q): ?>
<?php
$now    = time();
$dueTs  = $q['due_at'] ? strtotime($q['due_at']) : null;
$isOver = $dueTs && $dueTs < $now;
$hoursLeft = $dueTs ? round(($dueTs - $now) / 3600, 1) : null;
if (!$dueTs)             [$dueCls,$dueLabel] = ['text-muted','No deadline'];
elseif ($isOver)         [$dueCls,$dueLabel] = ['text-danger','Overdue'];
elseif ($hoursLeft < 24) [$dueCls,$dueLabel] = ['text-danger','Due '.round($hoursLeft).'h'];
elseif ($hoursLeft < 72) [$dueCls,$dueLabel] = ['text-warning','Due '.ceil($hoursLeft/24).'d'];
else                     [$dueCls,$dueLabel] = ['text-success',date('d M',$dueTs)];
?>
<div class="col-12 col-md-6 col-xl-4">
    <div class="quiz-card p-3 h-100 d-flex flex-column">
        <div class="d-flex gap-3 align-items-start">
            <div style="width:44px;height:44px;background:#ede9fe;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#6366f1;font-size:1.1rem">
                <i class="fas fa-question-circle"></i>
            </div>
            <div style="flex:1;min-width:0">
                <a href="<?= url('elms/quizzes/'.$q['id']) ?>" class="text-decoration-none">
                    <h6 class="fw-bold mb-1 text-dark" style="font-size:.9rem;line-height:1.35;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical"><?= e($q['title']) ?></h6>
                </a>
                <div class="text-muted small"><?= e($q['course_title']) ?></div>
            </div>
            <?php if (!$isInstructor && isset($q['best_pct']) && $q['best_pct'] !== null): ?>
            <?php $pct = round((float)$q['best_pct']); $passed = $q['ever_passed']; ?>
            <div class="pct-ring" style="background:<?= $passed?'#d1fae5':'#fee2e2' ?>;color:<?= $passed?'#065f46':'#dc2626' ?>"><?= $pct ?>%</div>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
            <span class="quiz-meta-pill"><i class="fas fa-list-ol me-1"></i><?= (int)$q['q_count'] ?> questions</span>
            <?php if ($q['time_limit_mins']): ?>
            <span class="quiz-meta-pill"><i class="fas fa-clock me-1"></i><?= $q['time_limit_mins'] ?> min</span>
            <?php endif; ?>
            <span class="quiz-meta-pill"><i class="fas fa-redo me-1"></i><?= $q['attempts_allowed'] ?: '∞' ?> attempt<?= $q['attempts_allowed']!=1?'s':'' ?></span>
            <span class="quiz-meta-pill"><i class="fas fa-check me-1"></i>Pass <?= $q['pass_percentage'] ?>%</span>
            <?php if ($dueTs): ?>
            <span class="quiz-meta-pill <?= $dueCls ?>"><i class="fas fa-calendar me-1"></i><?= $dueLabel ?></span>
            <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
            <?php if ($isInstructor): ?>
            <div class="d-flex gap-1">
                <a href="<?= url('elms/quizzes/'.$q['id']) ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.78rem"><i class="fas fa-chart-bar me-1"></i><?= $q['attempt_count'] ?> results</a>
                <a href="<?= url('elms/quizzes/'.$q['id'].'/builder') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.78rem"><i class="fas fa-tools me-1"></i>Build</a>
                <a href="<?= url('elms/quizzes/'.$q['id'].'/edit') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.78rem"><i class="fas fa-edit"></i></a>
            </div>
            <?php else: ?>
            <a href="<?= url('elms/quizzes/'.$q['id']) ?>" class="btn btn-sm btn-primary" style="border-radius:8px;font-size:.78rem">
                <?php if ($q['my_attempts'] > 0): ?>
                <i class="fas fa-redo me-1"></i>Retake
                <?php else: ?>
                <i class="fas fa-play me-1"></i>Start Quiz
                <?php endif; ?>
            </a>
            <?php if ($q['my_attempts'] > 0): ?>
            <span class="text-muted small"><?= $q['my_attempts'] ?> attempt<?= $q['my_attempts']!=1?'s':'' ?></span>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-4"><ul class="pagination pagination-sm justify-content-center" style="gap:4px">
    <?php for ($p=1;$p<=$totalPages;$p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>">
        <a class="page-link" style="border-radius:7px" href="?page=<?=$p?>&search=<?=urlencode($search)?>&course=<?=$courseId?>"><?=$p?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>
