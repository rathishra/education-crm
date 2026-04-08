<?php
$e = $enrollment;
$prog = (int)($e['progress'] ?? 0);
function pColor(int $p): string {
    if ($p >= 80) return '#10b981';
    if ($p >= 40) return '#6366f1';
    if ($p > 0)   return '#f59e0b';
    return '#94a3b8';
}
$lessonIcons = ['video'=>'fas fa-play-circle','document'=>'fas fa-file-alt','quiz'=>'fas fa-question-circle','assignment'=>'fas fa-tasks','interactive'=>'fas fa-laptop-code'];
?>

<div class="portal-page-header d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <div class="portal-breadcrumb mb-1">
            <a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo;
            <a href="<?= url('portal/student/lms') ?>">My Learning</a> &rsaquo;
            <?= e($e['title']) ?>
        </div>
        <h1 class="portal-page-title"><i class="fas fa-book-open me-2 text-primary"></i><?= e($e['title']) ?></h1>
        <?php if (!empty($e['subject_code'])): ?>
        <span class="badge bg-primary-subtle text-primary border" style="font-size:.75rem"><?= e($e['subject_code']) ?> &mdash; <?= e($e['subject_name']) ?></span>
        <?php endif; ?>
    </div>
    <a href="<?= url('portal/student/lms') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<!-- Progress & Info Bar -->
<div class="portal-card p-3 mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-6">
            <div class="d-flex align-items-center gap-3">
                <div class="text-center">
                    <div class="fw-bold fs-3" style="color:<?= pColor($prog) ?>"><?= $prog ?>%</div>
                    <div class="text-muted" style="font-size:.72rem">Progress</div>
                </div>
                <div class="flex-grow-1">
                    <div style="height:10px;background:#e2e8f0;border-radius:5px;overflow:hidden">
                        <div style="width:<?= $prog ?>%;height:100%;background:<?= pColor($prog) ?>;border-radius:5px;transition:width .3s"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex gap-4 flex-wrap justify-content-md-end" style="font-size:.82rem">
                <div><i class="fas fa-chalkboard-teacher text-primary me-1"></i><?= e($e['instructor_name']) ?></div>
                <?php if (!empty($e['pass_percentage'])): ?>
                <div><i class="fas fa-trophy text-warning me-1"></i>Pass: <?= (int)$e['pass_percentage'] ?>%</div>
                <?php endif; ?>
                <div>
                    <span class="badge <?= $e['status'] === 'completed' ? 'bg-success' : 'bg-primary' ?>"><?= ucfirst($e['status']) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- LEFT: Lessons -->
    <div class="col-12 col-lg-8">
        <h5 class="fw-bold mb-3" style="color:#1e293b"><i class="fas fa-list-ul me-2 text-primary"></i>Course Content</h5>

        <?php if (empty($sections)): ?>
        <div class="portal-card p-4 text-center text-muted">
            <i class="fas fa-folder-open d-block fs-2 mb-2 opacity-25"></i>
            No lessons available yet.
        </div>
        <?php else: ?>
        <div class="accordion" id="courseAccordion">
            <?php foreach ($sections as $i => $sec): ?>
            <div class="portal-card mb-2" style="overflow:hidden">
                <div class="p-3 d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="collapse" data-bs-target="#sec<?= $sec['id'] ?>" style="cursor:pointer;background:#f8fafc">
                    <i class="fas fa-chevron-down text-muted small"></i>
                    <span class="fw-bold small"><?= e($sec['section_title']) ?></span>
                    <span class="badge bg-secondary ms-auto" style="font-size:.68rem"><?= count($sec['lessons']) ?> lessons</span>
                </div>
                <div class="collapse <?= $i === 0 ? 'show' : '' ?>" id="sec<?= $sec['id'] ?>">
                    <?php if (empty($sec['lessons'])): ?>
                    <div class="p-3 text-muted small">No lessons in this section.</div>
                    <?php else: ?>
                    <?php foreach ($sec['lessons'] as $lesson):
                        $lIcon = $lessonIcons[$lesson['type'] ?? ''] ?? 'fas fa-file';
                        $isDone = ($lesson['progress_status'] === 'completed');
                        $isStarted = ($lesson['progress_status'] === 'in_progress');
                    ?>
                    <div class="d-flex align-items-center gap-3 px-3 py-2 border-top" style="<?= $isDone ? 'opacity:.7' : '' ?>">
                        <div class="flex-shrink-0">
                            <?php if ($isDone): ?>
                            <i class="fas fa-check-circle text-success"></i>
                            <?php elseif ($isStarted): ?>
                            <i class="fas fa-spinner text-warning"></i>
                            <?php else: ?>
                            <i class="<?= $lIcon ?> text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold small <?= $isDone ? 'text-decoration-line-through' : '' ?>"><?= e($lesson['title']) ?></div>
                            <div class="text-muted" style="font-size:.7rem">
                                <?= ucfirst($lesson['type'] ?? 'lesson') ?>
                                <?php if (!empty($lesson['xp_reward'])): ?>&bull; <i class="fas fa-star text-warning"></i> <?= (int)$lesson['xp_reward'] ?> XP<?php endif; ?>
                            </div>
                        </div>
                        <?php if ($isDone): ?>
                        <span class="badge bg-success-subtle text-success" style="font-size:.68rem">Done</span>
                        <?php elseif ($isStarted): ?>
                        <span class="badge bg-warning-subtle text-warning" style="font-size:.68rem">In Progress</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- RIGHT: Assignments & Quizzes -->
    <div class="col-12 col-lg-4">
        <!-- Assignments -->
        <h5 class="fw-bold mb-3" style="color:#1e293b"><i class="fas fa-tasks me-2 text-warning"></i>Assignments</h5>
        <?php if (empty($assignments)): ?>
        <div class="portal-card p-3 text-center text-muted small mb-4">No assignments yet.</div>
        <?php else: ?>
        <?php foreach ($assignments as $a):
            $submitted = !empty($a['sub_status']);
            $graded = ($a['sub_status'] === 'graded');
            $due = !empty($a['due_at']) ? strtotime($a['due_at']) : null;
            $overdue = $due && $due < time() && !$submitted;
        ?>
        <div class="portal-card p-3 mb-2">
            <div class="fw-semibold small"><?= e($a['title']) ?></div>
            <div class="d-flex gap-3 mt-1" style="font-size:.72rem">
                <?php if ($due): ?>
                <span class="<?= $overdue ? 'text-danger fw-bold' : 'text-muted' ?>"><i class="fas fa-calendar me-1"></i><?= date('d M, h:i A', $due) ?></span>
                <?php endif; ?>
                <?php if (!empty($a['max_score'])): ?>
                <span class="text-muted"><i class="fas fa-star me-1"></i>Max: <?= (int)$a['max_score'] ?></span>
                <?php endif; ?>
            </div>
            <div class="mt-2">
                <?php if ($graded): ?>
                <span class="badge bg-success">Graded: <?= $a['score'] ?>/<?= $a['max_score'] ?></span>
                <?php elseif ($submitted): ?>
                <span class="badge bg-info">Submitted <?= date('d M', strtotime($a['submitted_at'])) ?></span>
                <?php elseif ($overdue): ?>
                <span class="badge bg-danger">Overdue</span>
                <?php else: ?>
                <span class="badge bg-warning-subtle text-warning">Pending</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <!-- Quizzes -->
        <h5 class="fw-bold mb-3 mt-4" style="color:#1e293b"><i class="fas fa-question-circle me-2 text-info"></i>Quizzes</h5>
        <?php if (empty($quizzes)): ?>
        <div class="portal-card p-3 text-center text-muted small mb-4">No quizzes yet.</div>
        <?php else: ?>
        <?php foreach ($quizzes as $q):
            $attempts = (int)($q['attempt_count'] ?? 0);
            $maxAttempts = (int)($q['attempts_allowed'] ?? 0);
            $best = $q['best_score'] !== null ? round($q['best_score'], 1) : null;
            $passed = $best !== null && !empty($q['pass_percentage']) && $best >= (float)$q['pass_percentage'];
        ?>
        <div class="portal-card p-3 mb-2">
            <div class="fw-semibold small"><?= e($q['title']) ?></div>
            <div class="d-flex gap-3 mt-1 flex-wrap" style="font-size:.72rem">
                <?php if (!empty($q['due_at'])): ?>
                <span class="text-muted"><i class="fas fa-calendar me-1"></i><?= date('d M, h:i A', strtotime($q['due_at'])) ?></span>
                <?php endif; ?>
                <?php if (!empty($q['time_limit_mins'])): ?>
                <span class="text-muted"><i class="fas fa-stopwatch me-1"></i><?= (int)$q['time_limit_mins'] ?> min</span>
                <?php endif; ?>
                <span class="text-muted"><i class="fas fa-redo me-1"></i><?= $attempts ?><?= $maxAttempts ? '/'.$maxAttempts : '' ?> attempts</span>
            </div>
            <div class="mt-2">
                <?php if ($best !== null): ?>
                <span class="badge <?= $passed ? 'bg-success' : 'bg-warning' ?>">Best: <?= $best ?>%</span>
                <?php else: ?>
                <span class="badge bg-secondary">Not attempted</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
