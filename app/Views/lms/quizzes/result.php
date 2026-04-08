<style>
.result-hero { background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); border-radius:16px; color:#fff; padding:2.5rem; text-align:center; margin-bottom:1.5rem; }
.result-hero .pct { font-size:3.5rem; font-weight:900; line-height:1; }
.result-hero .label { font-size:1rem; opacity:.85; margin-top:.25rem; }
.result-badge { display:inline-flex; align-items:center; gap:.4rem; font-size:.85rem; font-weight:700; padding:.35rem 1rem; border-radius:20px; margin-top:.75rem; }
.ans-card { background:#fff; border-radius:12px; border:1px solid #e8e3ff; padding:1.25rem; margin-bottom:.75rem; }
.ans-card.correct { border-left:4px solid #22c55e; }
.ans-card.incorrect { border-left:4px solid #ef4444; }
.ans-card.pending { border-left:4px solid #f59e0b; }
.opt-result { display:flex; align-items:center; gap:.5rem; padding:.4rem .75rem; border-radius:8px; margin-bottom:.3rem; font-size:.85rem; }
.opt-result.was-selected { background:#eef2ff; }
.opt-result.is-correct { background:#f0fdf4; }
.opt-result.selected-correct { background:#dcfce7; }
.opt-result.selected-wrong { background:#fee2e2; }
</style>

<?php
$pct        = round((float)$attempt['percentage']);
$passed     = (bool)$attempt['passed'];
$timeTaken  = (int)$attempt['time_taken_s'];
$m = floor($timeTaken / 60); $s = $timeTaken % 60;
?>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/quizzes/'.$quiz['id']) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-flag-checkered me-2 text-primary"></i>Quiz Result</h4>
</div>

<div class="result-hero">
    <div class="pct"><?= $pct ?>%</div>
    <div class="label">Score: <?= number_format((float)$attempt['score'],1) ?> / <?= number_format((float)$attempt['max_score'],1) ?> points</div>
    <div>
        <?php if ($passed): ?>
        <span class="result-badge" style="background:rgba(34,197,94,.2);color:#bbf7d0"><i class="fas fa-trophy"></i>Passed +20 XP</span>
        <?php else: ?>
        <span class="result-badge" style="background:rgba(239,68,68,.2);color:#fecaca"><i class="fas fa-times-circle"></i>Not Passed (<?= $quiz['pass_percentage'] ?>% required)</span>
        <?php endif; ?>
    </div>
    <div class="mt-3 d-flex justify-content-center gap-4 flex-wrap" style="opacity:.85;font-size:.82rem">
        <span><i class="fas fa-clock me-1"></i><?= $m ?>m <?= $s ?>s</span>
        <span><i class="fas fa-calendar me-1"></i><?= date('d M Y H:i', strtotime($attempt['submitted_at'])) ?></span>
        <span><i class="fas fa-redo me-1"></i>Attempt #<?= $attempt['attempt'] ?></span>
    </div>
</div>

<?php if (!$showResult): ?>
<div class="alert alert-info text-center" style="border-radius:12px">
    <i class="fas fa-hourglass-half me-2"></i>
    <?= $quiz['show_result'] === 'after_due' ? 'Detailed results will be shown after the due date.' : 'Results are not shown for this quiz.' ?>
</div>
<?php else: ?>

<?php if (!empty($answers)): ?>
<h6 class="fw-bold mb-3" style="color:#0f172a"><i class="fas fa-list-check me-2 text-primary"></i>Question Review</h6>
<?php foreach ($answers as $i => $ans): ?>
<?php
$isCorrect = $ans['is_correct'];
$isPending = ($isCorrect === null);
$cardClass = $isPending ? 'pending' : ($isCorrect ? 'correct' : 'incorrect');
$icon      = $isPending ? 'fa-clock text-warning' : ($isCorrect ? 'fa-check-circle text-success' : 'fa-times-circle text-danger');
$pts       = number_format((float)$ans['points_earned'], 1);
$maxPts    = number_format((float)$ans['points'], 1);
?>
<div class="ans-card <?= $cardClass ?>">
    <div class="d-flex align-items-start gap-3 mb-2">
        <i class="fas <?= $icon ?> mt-1" style="font-size:1rem;flex-shrink:0"></i>
        <div style="flex:1">
            <div class="fw-semibold small" style="color:#0f172a;line-height:1.5"><?= ($i+1) ?>. <?= nl2br(e($ans['question'])) ?></div>
            <div class="text-muted" style="font-size:.72rem"><?= $pts ?>/<?= $maxPts ?> pts<?= $isPending?' &middot; Pending grading':'' ?></div>
        </div>
    </div>

    <?php if ($ans['text_answer'] !== null && $ans['text_answer'] !== ''): ?>
    <div class="bg-light rounded p-2 small ms-4" style="color:#374151"><em>"<?= e($ans['text_answer']) ?>"</em></div>

    <?php elseif ($showCorrect && !empty($ans['options'])): ?>
    <div class="ms-4">
        <?php
        $selectedIds = $ans['option_ids'] ?: [];
        foreach ($ans['options'] as $opt):
            $wasSelected = in_array((int)$opt['id'], array_map('intval', $selectedIds));
            $isCorr      = (bool)$opt['is_correct'];
            $cls = '';
            if ($wasSelected && $isCorr)       $cls = 'selected-correct';
            elseif ($wasSelected && !$isCorr)  $cls = 'selected-wrong';
            elseif (!$wasSelected && $isCorr)  $cls = 'is-correct';
            elseif ($wasSelected)              $cls = 'was-selected';
        ?>
        <div class="opt-result <?= $cls ?>">
            <?php if ($wasSelected && $isCorr): ?>  <i class="fas fa-check text-success" style="width:14px"></i>
            <?php elseif ($wasSelected && !$isCorr): ?><i class="fas fa-times text-danger" style="width:14px"></i>
            <?php elseif ($isCorr): ?>              <i class="fas fa-check text-success" style="width:14px;opacity:.5"></i>
            <?php else: ?>                          <i class="far fa-circle text-muted" style="width:14px"></i>
            <?php endif; ?>
            <span><?= e($opt['option_text']) ?></span>
            <?php if ($isCorr): ?><span class="ms-auto text-success small fw-bold">Correct</span><?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php elseif (!$showCorrect && !empty($ans['option_ids'])): ?>
    <div class="ms-4 text-muted small"><i class="fas fa-hand-point-right me-1"></i>Your answer was recorded.</div>
    <?php endif; ?>

    <?php if ($ans['explanation']): ?>
    <div class="ms-4 mt-2 small" style="color:#92400e;background:#fffbeb;border-radius:8px;padding:.4rem .75rem"><i class="fas fa-lightbulb me-1 text-warning"></i><?= e($ans['explanation']) ?></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php endif; ?>

<div class="d-flex gap-2 mt-3 flex-wrap">
    <a href="<?= url('elms/quizzes/'.$quiz['id']) ?>" class="btn btn-outline-secondary" style="border-radius:9px"><i class="fas fa-arrow-left me-2"></i>Back to Quiz</a>
    <a href="<?= url('elms/quizzes') ?>" class="btn btn-outline-primary" style="border-radius:9px"><i class="fas fa-list me-2"></i>All Quizzes</a>
</div>
