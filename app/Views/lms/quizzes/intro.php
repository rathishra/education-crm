<style>
.intro-stat { text-align:center; padding:.75rem 1rem; background:#f8f7ff; border-radius:12px; border:1px solid #e8e3ff; }
.intro-stat .val { font-size:1.4rem; font-weight:800; color:#4f46e5; line-height:1 }
.intro-stat .lbl { font-size:.7rem; color:#64748b; margin-top:.2rem }
.attempt-row { display:flex; align-items:center; gap:.75rem; padding:.6rem 1rem; border-radius:9px; background:#f8fafc; border:1px solid #e2e8f0; margin-bottom:.5rem; }
</style>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/quizzes') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-question-circle me-2 text-primary"></i><?= e($quiz['title']) ?></h4>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="bg-white rounded-3 border p-4" style="border-color:#e8e3ff!important">
            <?php if ($quiz['description']): ?>
            <p class="mb-4" style="color:#374151;line-height:1.7"><?= nl2br(e($quiz['description'])) ?></p>
            <?php endif; ?>

            <div class="row g-2 mb-4">
                <div class="col-6 col-sm-3">
                    <div class="intro-stat">
                        <div class="val">
                            <?php
                            try {
                                global $db;
                                // count from questions array passed via controller, but we only have quiz here
                                // use q_count if available
                                echo '—';
                            } catch (\Throwable $e) { echo '—'; }
                            ?>
                        </div>
                        <div class="lbl">Questions</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="intro-stat">
                        <div class="val"><?= $quiz['time_limit_mins'] ? $quiz['time_limit_mins'].'m' : '∞' ?></div>
                        <div class="lbl">Time Limit</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="intro-stat">
                        <div class="val"><?= $quiz['pass_percentage'] ?>%</div>
                        <div class="lbl">Pass Score</div>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="intro-stat">
                        <div class="val"><?= $quiz['attempts_allowed'] ?: '∞' ?></div>
                        <div class="lbl">Attempt<?= $quiz['attempts_allowed']!=1?'s':'' ?></div>
                    </div>
                </div>
            </div>

            <?php if ($quiz['due_at']): ?>
            <?php $dueTs = strtotime($quiz['due_at']); $isOver = $dueTs < time(); ?>
            <div class="alert <?= $isOver?'alert-danger':'alert-warning' ?> py-2 small mb-4">
                <i class="fas fa-calendar me-2"></i>
                <?= $isOver ? 'Due date passed: ' : 'Due: ' ?><strong><?= date('l, d M Y H:i', $dueTs) ?></strong>
            </div>
            <?php endif; ?>

            <ul class="small text-muted mb-4 ps-3" style="line-height:2">
                <?php if ($quiz['shuffle_questions']): ?><li>Questions will appear in random order</li><?php endif; ?>
                <?php if ($quiz['shuffle_options']): ?><li>Answer options will be shuffled</li><?php endif; ?>
                <?php if ($quiz['show_correct']): ?><li>Correct answers shown after submission</li><?php endif; ?>
                <?php if ($quiz['time_limit_mins']): ?><li>Timer starts when you click Start and cannot be paused</li><?php endif; ?>
                <li>Ensure a stable internet connection before starting</li>
            </ul>

            <?php if (!$canStart && !empty($attempts)): ?>
            <div class="alert alert-warning py-2 small"><i class="fas fa-lock me-2"></i>You have used all <?= $quiz['attempts_allowed'] ?> allowed attempt<?= $quiz['attempts_allowed']!=1?'s':'' ?>.</div>
            <?php elseif (!$canStart): ?>
            <div class="alert alert-danger py-2 small"><i class="fas fa-lock me-2"></i>You are not enrolled in this course.</div>
            <?php else: ?>
            <form method="POST" action="<?= url('elms/quizzes/'.$quiz['id'].'/start') ?>">
                <?= csrfField() ?>
                <button type="submit" class="btn btn-primary btn-lg w-100" style="border-radius:10px">
                    <i class="fas fa-<?= $attemptCount>0?'redo':'play' ?> me-2"></i>
                    <?= $attemptCount > 0 ? 'Start Attempt '.($attemptCount+1) : 'Start Quiz' ?>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($attempts)): ?>
    <div class="col-12 col-lg-4">
        <div class="bg-white rounded-3 border p-3" style="border-color:#e8e3ff!important">
            <h6 class="fw-bold mb-3" style="color:#0f172a"><i class="fas fa-history me-2 text-primary"></i>My Attempts</h6>
            <?php foreach ($attempts as $a): ?>
            <?php $pct = round((float)$a['percentage']); $passed = $a['passed']; ?>
            <div class="attempt-row">
                <div style="width:44px;height:44px;border-radius:50%;background:<?= $passed?'#d1fae5':'#fee2e2' ?>;color:<?= $passed?'#065f46':'#dc2626' ?>;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0"><?= $pct ?>%</div>
                <div style="flex:1;min-width:0">
                    <div class="small fw-semibold">Attempt <?= $a['attempt'] ?> — <?= $passed?'<span class="text-success">Passed</span>':'<span class="text-danger">Failed</span>' ?></div>
                    <div class="text-muted" style="font-size:.72rem"><?= $a['submitted_at'] ? date('d M Y H:i', strtotime($a['submitted_at'])) : '—' ?></div>
                </div>
                <?php if ($a['status'] !== 'in_progress'): ?>
                <a href="<?= url('elms/quizzes/'.$quiz['id'].'/attempt/'.$a['id'].'/result') ?>" class="btn btn-sm btn-outline-primary" style="border-radius:7px;font-size:.72rem">View</a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
