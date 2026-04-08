<?php
$greeting = $greetingHour < 12 ? 'Good morning' : ($greetingHour < 17 ? 'Good afternoon' : 'Good evening');
$isLearner = ($lmsUser['role'] === 'learner');
$isInstructor = in_array($lmsUser['role'], ['instructor', 'lms_admin']);

// Activity event labels
$activityLabels = [
    'lesson_completed'   => ['fas fa-check-circle', '#059669', 'Completed a lesson'],
    'quiz_submitted'     => ['fas fa-question-circle', '#7c3aed', 'Submitted a quiz'],
    'assignment_submitted'=> ['fas fa-file-upload', '#0891b2', 'Submitted assignment'],
    'course_enrolled'    => ['fas fa-graduation-cap', '#d97706', 'Enrolled in course'],
    'course_completed'   => ['fas fa-trophy', '#f59e0b', 'Completed a course'],
    'badge_earned'       => ['fas fa-medal', '#ef4444', 'Earned a badge'],
    'forum_post'         => ['fas fa-comments', '#6366f1', 'Posted in forum'],
    'live_attended'      => ['fas fa-video', '#0284c7', 'Attended live class'],
    'certificate_earned' => ['fas fa-certificate', '#065f46', 'Earned certificate'],
    'login'              => ['fas fa-sign-in-alt', '#94a3b8', 'Signed in'],
];
function actInfo(string $event, array $map): array {
    return $map[$event] ?? ['fas fa-dot-circle', '#94a3b8', ucwords(str_replace('_',' ',$event))];
}

$levelThresholds = [1=>0,2=>100,3=>250,4=>500,5=>900,6=>1400,7=>2000,8=>2800,9=>3800,10=>5000];
$xp = (int)($stats['xp_points'] ?? $lmsUser['xp_points'] ?? 0);
$lvl = (int)($stats['level'] ?? $lmsUser['level'] ?? 1);
$nextLvlXp  = $levelThresholds[min($lvl+1, 10)] ?? 5000;
$thisLvlXp  = $levelThresholds[$lvl] ?? 0;
$xpProgress = $nextLvlXp > $thisLvlXp
    ? min(100, round(($xp - $thisLvlXp) / ($nextLvlXp - $thisLvlXp) * 100))
    : 100;
?>

<style>
/* ── Dashboard Styles ──────────────────────────────────────── */
.dash-stat {
    background:#fff; border-radius:14px; border:1px solid #ede9fe;
    padding:1.25rem 1.5rem; display:flex; align-items:center; gap:1rem;
    box-shadow:0 1px 6px rgba(99,102,241,.07); transition:transform .15s,box-shadow .15s;
}
.dash-stat:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(99,102,241,.12); }
.dash-stat-icon {
    width:50px; height:50px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.3rem; flex-shrink:0;
}
.dash-stat-val  { font-size:1.6rem; font-weight:900; color:#0f172a; line-height:1.1; }
.dash-stat-lbl  { font-size:.72rem; color:#64748b; font-weight:500; margin-top:.1rem; }
.dash-stat-sub  { font-size:.7rem; color:#94a3b8; margin-top:.15rem; }

/* Course card */
.lms-course-card {
    background:#fff; border-radius:14px; border:1px solid #ede9fe;
    box-shadow:0 1px 6px rgba(99,102,241,.06); overflow:hidden;
    transition:transform .15s, box-shadow .15s;
    display:flex; flex-direction:column;
}
.lms-course-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(99,102,241,.14); }
.lms-course-thumb {
    height:130px; background:linear-gradient(135deg,#4f46e5,#7c3aed);
    display:flex; align-items:center; justify-content:center;
    font-size:2.5rem; color:rgba(255,255,255,.5); position:relative; overflow:hidden;
}
.lms-course-thumb img { width:100%; height:100%; object-fit:cover; }
.lms-course-level {
    position:absolute; top:.6rem; right:.6rem;
    background:rgba(0,0,0,.45); color:#fff; font-size:.65rem;
    font-weight:700; padding:2px 8px; border-radius:10px; text-transform:capitalize;
}
.lms-course-body { padding:1rem; flex:1; display:flex; flex-direction:column; }
.lms-progress-bar-sm { height:5px; border-radius:3px; background:#ede9fe; overflow:hidden; }
.lms-progress-fill   { height:100%; border-radius:3px; background:linear-gradient(90deg,#6366f1,#818cf8); transition:width .4s ease; }

/* Activity feed */
.activity-item { display:flex; gap:.75rem; align-items:flex-start; padding:.6rem 0; border-bottom:1px solid #f1f5f9; }
.activity-item:last-child { border-bottom:none; }
.activity-dot  { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; flex-shrink:0; margin-top:.1rem; }

/* Deadline item */
.deadline-item { display:flex; align-items:center; gap:.85rem; padding:.65rem 0; border-bottom:1px solid #f1f5f9; }
.deadline-item:last-child { border-bottom:none; }
.deadline-type-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* Leaderboard */
.lb-row { display:flex; align-items:center; gap:.75rem; padding:.5rem 0; border-bottom:1px solid #f1f5f9; }
.lb-row:last-child { border-bottom:none; }
.lb-rank { width:24px; height:24px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:800; flex-shrink:0; }

/* XP bar */
.xp-bar-wrap { height:8px; border-radius:4px; background:#ede9fe; overflow:hidden; }
.xp-bar-fill { height:100%; border-radius:4px; background:linear-gradient(90deg,#f59e0b,#fbbf24); transition:width .5s ease; }

/* Announcements */
.ann-item { border-left:3px solid; padding:.75rem 1rem; border-radius:0 8px 8px 0; margin-bottom:.6rem; }
.ann-info    { border-color:#6366f1; background:#f5f3ff; }
.ann-warning { border-color:#d97706; background:#fffbeb; }
.ann-success { border-color:#059669; background:#f0fdf4; }
.ann-alert   { border-color:#dc2626; background:#fef2f2; }

/* Role badge */
.lms-role-badge {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.2rem .75rem; border-radius:20px; font-size:.7rem; font-weight:700;
    letter-spacing:.04em; text-transform:uppercase;
}
</style>

<!-- ── PAGE HEADER ─────────────────────────────────────────── -->
<div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <div class="text-muted small mb-1"><?= date('l, d F Y') ?></div>
        <h1 style="font-size:1.5rem;font-weight:900;color:#0f172a;margin:0">
            <?= $greeting ?>, <?= e($lmsUser['first_name'] ?? 'there') ?>! 👋
        </h1>
        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
            <?php
            $roleConf = [
                'learner'         => ['#6366f1','#ede9fe','fas fa-user-graduate','Learner'],
                'instructor'      => ['#0891b2','#e0f2fe','fas fa-chalkboard-teacher','Instructor'],
                'lms_admin'       => ['#7c3aed','#ede9fe','fas fa-shield-alt','LMS Admin'],
                'content_manager' => ['#d97706','#fef3c7','fas fa-pencil-ruler','Content Manager'],
            ];
            $rc = $roleConf[$lmsUser['role']] ?? ['#6366f1','#ede9fe','fas fa-user','User'];
            ?>
            <span class="lms-role-badge" style="color:<?= $rc[0] ?>;background:<?= $rc[1] ?>">
                <i class="<?= $rc[2] ?>"></i><?= $rc[3] ?>
            </span>
            <?php if ($isLearner && $xp > 0): ?>
            <span class="text-muted small"><i class="fas fa-star me-1 text-warning"></i><?= number_format($xp) ?> XP &bull; Level <?= $lvl ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('elms/courses') ?>" class="btn btn-sm btn-primary" style="border-radius:9px;font-weight:600">
            <i class="fas fa-search me-1"></i>Browse Courses
        </a>
        <?php if ($isInstructor): ?>
        <a href="<?= url('elms/courses/create') ?>" class="btn btn-sm btn-outline-primary" style="border-radius:9px;font-weight:600">
            <i class="fas fa-plus me-1"></i>New Course
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- ── STATS ROW ──────────────────────────────────────────────── -->
<?php if ($isLearner): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="dash-stat">
            <div class="dash-stat-icon" style="background:#ede9fe;color:#6366f1"><i class="fas fa-book-open"></i></div>
            <div>
                <div class="dash-stat-val"><?= $stats['enrolled'] ?? 0 ?></div>
                <div class="dash-stat-lbl">Enrolled</div>
                <div class="dash-stat-sub"><?= $stats['in_progress'] ?? 0 ?> in progress</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-stat">
            <div class="dash-stat-icon" style="background:#d1fae5;color:#059669"><i class="fas fa-check-double"></i></div>
            <div>
                <div class="dash-stat-val"><?= $stats['completed'] ?? 0 ?></div>
                <div class="dash-stat-lbl">Completed</div>
                <div class="dash-stat-sub"><?= $stats['avg_score'] ?? 0 ?>% avg score</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-stat">
            <div class="dash-stat-icon" style="background:#fef3c7;color:#d97706"><i class="fas fa-clock"></i></div>
            <div>
                <div class="dash-stat-val"><?= ($stats['assignments_due'] ?? 0) + ($stats['quizzes_due'] ?? 0) ?></div>
                <div class="dash-stat-lbl">Due This Week</div>
                <div class="dash-stat-sub"><?= $stats['assignments_due'] ?? 0 ?> assign · <?= $stats['quizzes_due'] ?? 0 ?> quiz</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-stat">
            <div class="dash-stat-icon" style="background:#fce7f3;color:#be185d"><i class="fas fa-certificate"></i></div>
            <div>
                <div class="dash-stat-val"><?= $stats['certificates'] ?? 0 ?></div>
                <div class="dash-stat-lbl">Certificates</div>
                <div class="dash-stat-sub"><?= $stats['streak_days'] ?? 0 ?> day streak 🔥</div>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Instructor / Admin Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="dash-stat">
            <div class="dash-stat-icon" style="background:#ede9fe;color:#6366f1"><i class="fas fa-book-open"></i></div>
            <div>
                <div class="dash-stat-val"><?= $instructorStats['my_courses'] ?? 0 ?></div>
                <div class="dash-stat-lbl">My Courses</div>
                <?php if ($this->isAdmin()): ?>
                <div class="dash-stat-sub"><?= $instructorStats['total_courses'] ?? 0 ?> total on platform</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-stat">
            <div class="dash-stat-icon" style="background:#d1fae5;color:#059669"><i class="fas fa-users"></i></div>
            <div>
                <div class="dash-stat-val"><?= number_format($instructorStats['total_learners'] ?? 0) ?></div>
                <div class="dash-stat-lbl">Total Learners</div>
                <?php if ($this->isAdmin()): ?>
                <div class="dash-stat-sub"><?= $instructorStats['active_learners'] ?? 0 ?> active</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-stat">
            <div class="dash-stat-icon" style="background:#fef3c7;color:#d97706"><i class="fas fa-tasks"></i></div>
            <div>
                <div class="dash-stat-val"><?= $instructorStats['pending_grading'] ?? 0 ?></div>
                <div class="dash-stat-lbl">Pending Grading</div>
                <div class="dash-stat-sub">Assignments &amp; quizzes</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="dash-stat">
            <div class="dash-stat-icon" style="background:#e0f2fe;color:#0891b2"><i class="fas fa-chart-bar"></i></div>
            <div>
                <div class="dash-stat-val"><?= $instructorStats['avg_completion'] ?? 0 ?>%</div>
                <div class="dash-stat-lbl">Avg Completion</div>
                <div class="dash-stat-sub">Across all courses</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── MAIN CONTENT GRID ───────────────────────────────────── -->
<div class="row g-3">

    <!-- LEFT COLUMN (8/12) -->
    <div class="col-lg-8">

        <!-- XP Progress (learner only) -->
        <?php if ($isLearner && $lvl > 0): ?>
        <div class="lms-card p-3 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;font-size:.85rem">
                        <?= $lvl ?>
                    </div>
                    <div>
                        <div class="fw-bold small" style="color:#0f172a">Level <?= $lvl ?></div>
                        <div class="text-muted" style="font-size:.7rem"><?= number_format($xp) ?> XP</div>
                    </div>
                </div>
                <div class="text-muted" style="font-size:.72rem">
                    <?= number_format($nextLvlXp - $xp) ?> XP to Level <?= $lvl + 1 ?>
                </div>
            </div>
            <div class="xp-bar-wrap">
                <div class="xp-bar-fill" style="width:<?= $xpProgress ?>%"></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Courses Section -->
        <div class="lms-card mb-3">
            <div class="d-flex align-items-center justify-content-between px-4 pt-3 pb-2 border-bottom">
                <div class="fw-bold" style="color:#0f172a;font-size:.95rem">
                    <i class="fas fa-book-open me-2 text-primary"></i>
                    <?= $isLearner ? 'My Courses' : 'My Courses Overview' ?>
                </div>
                <a href="<?= url($isLearner ? 'elms/my-courses' : 'elms/courses') ?>" class="text-decoration-none" style="font-size:.78rem;color:#6366f1;font-weight:600">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <?php if (empty($courses)): ?>
            <div class="text-center py-5">
                <i class="fas fa-book-open d-block mb-3" style="font-size:2.5rem;opacity:.15;color:#6366f1"></i>
                <div class="fw-semibold text-muted mb-1"><?= $isLearner ? 'No courses yet' : 'No courses created' ?></div>
                <div class="text-muted small mb-3"><?= $isLearner ? 'Browse and enrol in courses to get started.' : 'Create your first course to begin teaching.' ?></div>
                <a href="<?= url($isLearner ? 'elms/courses' : 'elms/courses/create') ?>" class="btn btn-sm btn-primary" style="border-radius:9px">
                    <i class="fas fa-<?= $isLearner ? 'search' : 'plus' ?> me-1"></i><?= $isLearner ? 'Browse Courses' : 'Create Course' ?>
                </a>
            </div>
            <?php else: ?>
            <div class="p-3">
                <div class="row g-3">
                    <?php foreach ($courses as $course):
                        $pct    = (int)($course['progress'] ?? 0);
                        $status = $course['enroll_status'] ?? $course['status'] ?? '';
                        $thumbColors = ['#4f46e5','#7c3aed','#0891b2','#059669','#d97706','#be185d'];
                        $thumbBg = $thumbColors[crc32($course['title'] ?? '') % count($thumbColors)];
                    ?>
                    <div class="col-sm-6">
                        <div class="lms-course-card">
                            <div class="lms-course-thumb" style="<?= empty($course['thumbnail']) ? 'background:linear-gradient(135deg,' . $thumbBg . ','. $thumbBg . 'cc)' : '' ?>">
                                <?php if (!empty($course['thumbnail'])): ?>
                                <img src="/<?= e($course['thumbnail']) ?>" alt="">
                                <?php else: ?>
                                <i class="fas fa-graduation-cap"></i>
                                <?php endif; ?>
                                <?php if (!empty($course['level'])): ?>
                                <span class="lms-course-level"><?= ucfirst($course['level']) ?></span>
                                <?php endif; ?>
                                <?php if ($status === 'completed'): ?>
                                <div style="position:absolute;top:.6rem;left:.6rem;background:#059669;color:#fff;font-size:.62rem;font-weight:700;padding:2px 8px;border-radius:10px">
                                    <i class="fas fa-check me-1"></i>Completed
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="lms-course-body">
                                <?php if (!empty($course['cat_color']) && !empty($course['category_name'])): ?>
                                <div class="mb-1" style="font-size:.65rem;font-weight:700;color:<?= e($course['cat_color']) ?>;text-transform:uppercase;letter-spacing:.05em">
                                    <?= e($course['category_name']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="fw-bold mb-1" style="font-size:.875rem;color:#0f172a;line-height:1.35">
                                    <?= e($course['title']) ?>
                                </div>
                                <?php if ($isLearner): ?>
                                <div class="text-muted mb-2" style="font-size:.72rem">
                                    by <?= e($course['instructor_name'] ?? '') ?>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="text-muted" style="font-size:.7rem"><?= $course['lessons_completed'] ?? 0 ?> / <?= $course['total_lessons'] ?? '?' ?> lessons</span>
                                    <span class="fw-bold" style="font-size:.78rem;color:<?= $pct >= 100 ? '#059669' : '#6366f1' ?>"><?= $pct ?>%</span>
                                </div>
                                <div class="lms-progress-bar-sm mb-2">
                                    <div class="lms-progress-fill" style="width:<?= $pct ?>%;<?= $pct >= 100 ? 'background:linear-gradient(90deg,#059669,#10b981)' : '' ?>"></div>
                                </div>
                                <div class="mt-auto">
                                    <a href="<?= url('elms/courses/' . $course['id'] . '/learn') ?>"
                                       class="btn btn-sm w-100 fw-semibold"
                                       style="border-radius:8px;font-size:.78rem;<?= $pct > 0 ? 'background:#6366f1;color:#fff' : 'background:#ede9fe;color:#4f46e5' ?>">
                                        <i class="fas fa-<?= $pct >= 100 ? 'redo' : ($pct > 0 ? 'play' : 'rocket') ?> me-1"></i>
                                        <?= $pct >= 100 ? 'Review' : ($pct > 0 ? 'Continue' : 'Start') ?>
                                    </a>
                                </div>
                                <?php else: ?>
                                <!-- Instructor course card -->
                                <div class="d-flex gap-3 mt-auto pt-1">
                                    <div class="text-center">
                                        <div class="fw-bold" style="font-size:.95rem;color:#0f172a"><?= number_format($course['enrolled_count'] ?? 0) ?></div>
                                        <div class="text-muted" style="font-size:.65rem">Learners</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="fw-bold" style="font-size:.95rem;color:#0f172a"><?= number_format($course['completed_count'] ?? 0) ?></div>
                                        <div class="text-muted" style="font-size:.65rem">Completed</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="fw-bold" style="font-size:.95rem;color:#f59e0b">
                                            <i class="fas fa-star" style="font-size:.8rem"></i> <?= number_format((float)($course['rating_avg'] ?? 0), 1) ?>
                                        </div>
                                        <div class="text-muted" style="font-size:.65rem">Rating</div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="badge" style="font-size:.65rem;background:<?= $course['status']==='published' ? '#d1fae5' : '#fef3c7' ?>;color:<?= $course['status']==='published' ? '#065f46' : '#92400e' ?>;border-radius:6px">
                                        <?= ucfirst($course['status']) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Deadlines -->
        <?php if ($isLearner): ?>
        <div class="lms-card mb-3">
            <div class="d-flex align-items-center justify-content-between px-4 pt-3 pb-2 border-bottom">
                <div class="fw-bold" style="color:#0f172a;font-size:.95rem">
                    <i class="fas fa-clock me-2 text-warning"></i>Upcoming Deadlines
                </div>
            </div>
            <?php if (empty($deadlines)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-calendar-check d-block mb-2 fs-3 opacity-25"></i>
                <div class="small">No upcoming deadlines. You're all caught up!</div>
            </div>
            <?php else: ?>
            <div class="px-4 py-2">
                <?php
                $typeConf = [
                    'assignment' => ['#6366f1', 'fas fa-tasks'],
                    'quiz'       => ['#7c3aed', 'fas fa-question-circle'],
                    'live_class' => ['#0891b2', 'fas fa-video'],
                    'project'    => ['#d97706', 'fas fa-project-diagram'],
                ];
                foreach ($deadlines as $dl):
                    $dConf   = $typeConf[$dl['type']] ?? ['#94a3b8', 'fas fa-calendar'];
                    $dueTs   = strtotime($dl['due_at']);
                    $hoursLeft = ($dueTs - time()) / 3600;
                    $urgency = $hoursLeft < 24 ? 'danger' : ($hoursLeft < 72 ? 'warning' : 'muted');
                ?>
                <div class="deadline-item">
                    <div class="deadline-type-dot" style="background:<?= $dConf[0] ?>;margin-top:.45rem"></div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="font-size:.84rem"><?= e($dl['title']) ?></div>
                        <div class="text-muted" style="font-size:.72rem">
                            <i class="<?= $dConf[1] ?> me-1"></i><?= ucfirst($dl['type']) ?>
                            &bull; <?= e($dl['course_title'] ?? '') ?>
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-semibold text-<?= $urgency ?>" style="font-size:.78rem">
                            <?php
                            if ($hoursLeft < 1)      echo 'Due in <1h';
                            elseif ($hoursLeft < 24) echo 'Due in ' . (int)$hoursLeft . 'h';
                            else                     echo date('d M', $dueTs);
                            ?>
                        </div>
                        <div class="text-muted" style="font-size:.68rem"><?= date('h:i A', $dueTs) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div><!-- /left col -->

    <!-- RIGHT COLUMN (4/12) -->
    <div class="col-lg-4">

        <!-- Announcements -->
        <?php if (!empty($announcements)): ?>
        <div class="lms-card mb-3">
            <div class="px-4 pt-3 pb-2 border-bottom fw-bold" style="color:#0f172a;font-size:.95rem">
                <i class="fas fa-bullhorn me-2 text-primary"></i>Announcements
            </div>
            <div class="p-3">
                <?php
                $annColors = ['info'=>'ann-info','warning'=>'ann-warning','success'=>'ann-success','alert'=>'ann-alert'];
                foreach ($announcements as $ann):
                    $cls = $annColors[$ann['type'] ?? 'info'] ?? 'ann-info';
                ?>
                <div class="ann-item <?= $cls ?>">
                    <div class="d-flex align-items-start justify-content-between gap-1">
                        <div class="fw-semibold" style="font-size:.82rem;color:#1e293b">
                            <?php if ($ann['is_pinned']): ?><i class="fas fa-thumbtack me-1" style="font-size:.7rem;color:#6366f1"></i><?php endif; ?>
                            <?= e($ann['title']) ?>
                        </div>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.75rem;line-height:1.5"><?= nl2br(e(mb_strimwidth($ann['body'], 0, 140, '…'))) ?></div>
                    <div class="text-muted mt-1" style="font-size:.68rem">
                        <?= e($ann['author_name'] ?? '') ?>
                        <?php if ($ann['course_title']): ?>&bull; <?= e($ann['course_title']) ?><?php endif; ?>
                        &bull; <?= date('d M', strtotime($ann['publish_at'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Activity Feed -->
        <div class="lms-card mb-3">
            <div class="px-4 pt-3 pb-2 border-bottom fw-bold" style="color:#0f172a;font-size:.95rem">
                <i class="fas fa-history me-2 text-primary"></i>Recent Activity
            </div>
            <?php if (empty($activity)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-history d-block mb-2 fs-3 opacity-20"></i>
                <div class="small">No activity yet. Start learning!</div>
            </div>
            <?php else: ?>
            <div class="px-4 py-2">
                <?php foreach ($activity as $act):
                    [$aIcon, $aColor, $aLabel] = actInfo($act['event'] ?? '', $activityLabels);
                    $diff = time() - strtotime($act['created_at']);
                    $agoText = $diff < 60 ? 'Just now' : ($diff < 3600 ? floor($diff/60).'m ago' : ($diff < 86400 ? floor($diff/3600).'h ago' : date('d M', strtotime($act['created_at']))));
                ?>
                <div class="activity-item">
                    <div class="activity-dot" style="background:<?= $aColor ?>18;color:<?= $aColor ?>">
                        <i class="<?= $aIcon ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:.8rem;color:#334155;line-height:1.35">
                            <?= $aLabel ?>
                            <?php if (!empty($act['entity_title'])): ?>
                            — <span class="fw-semibold"><?= e(mb_strimwidth($act['entity_title'], 0, 50, '…')) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="text-muted" style="font-size:.68rem">
                            <?= $agoText ?>
                            <?php if ($act['xp_earned'] > 0): ?>
                            &bull; <span class="text-warning fw-semibold">+<?= $act['xp_earned'] ?> XP</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Leaderboard (learner only) -->
        <?php if ($isLearner && !empty($leaderboard)): ?>
        <div class="lms-card mb-3">
            <div class="px-4 pt-3 pb-2 border-bottom fw-bold" style="color:#0f172a;font-size:.95rem">
                <i class="fas fa-trophy me-2 text-warning"></i>Top Learners
            </div>
            <div class="px-4 py-2">
                <?php
                $rankStyles = [
                    1 => ['background:#fef3c7;color:#d97706', '🥇'],
                    2 => ['background:#f1f5f9;color:#64748b', '🥈'],
                    3 => ['background:#fef3c7;color:#b45309', '🥉'],
                ];
                foreach ($leaderboard as $rank => $lb):
                    $pos = $rank + 1;
                    $rStyle = $rankStyles[$pos] ?? ['background:#f1f5f9;color:#94a3b8', (string)$pos];
                    $lbInitials = strtoupper(substr($lb['first_name'],0,1).substr($lb['last_name'],0,1));
                ?>
                <div class="lb-row <?= $lb['is_me'] ? 'rounded-2 px-1' : '' ?>" style="<?= $lb['is_me'] ? 'background:#ede9fe' : '' ?>">
                    <div class="lb-rank" style="<?= $rStyle[0] ?>;font-size:.8rem">
                        <?= is_numeric($rStyle[1]) ? $rStyle[1] : $rStyle[1] ?>
                    </div>
                    <div class="lms-avatar flex-shrink-0" style="width:28px;height:28px;font-size:.65rem;background:<?= ['#6366f1','#059669','#d97706','#7c3aed','#0891b2'][$rank % 5] ?>">
                        <?= $lbInitials ?>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-size:.8rem;font-weight:<?= $lb['is_me'] ? '700' : '500' ?>;color:#0f172a">
                            <?= e($lb['first_name'] . ' ' . substr($lb['last_name'],0,1) . '.') ?>
                            <?= $lb['is_me'] ? '<span style="font-size:.65rem;color:#6366f1">(you)</span>' : '' ?>
                        </div>
                        <div class="text-muted" style="font-size:.68rem">Level <?= $lb['level'] ?></div>
                    </div>
                    <div class="fw-bold" style="font-size:.78rem;color:#f59e0b">
                        <?= number_format($lb['xp_points']) ?> XP
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="lms-card">
            <div class="px-4 pt-3 pb-2 border-bottom fw-bold" style="color:#0f172a;font-size:.95rem">
                <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
            </div>
            <div class="p-3 d-grid gap-2">
                <?php if ($isLearner): ?>
                <a href="<?= url('elms/courses') ?>" class="btn btn-sm text-start fw-semibold" style="border-radius:9px;background:#ede9fe;color:#4f46e5;font-size:.82rem">
                    <i class="fas fa-search me-2"></i>Browse All Courses
                </a>
                <a href="<?= url('elms/assignments') ?>" class="btn btn-sm text-start fw-semibold" style="border-radius:9px;background:#fef3c7;color:#92400e;font-size:.82rem">
                    <i class="fas fa-tasks me-2"></i>My Assignments
                </a>
                <a href="<?= url('elms/quizzes') ?>" class="btn btn-sm text-start fw-semibold" style="border-radius:9px;background:#e0f2fe;color:#0369a1;font-size:.82rem">
                    <i class="fas fa-question-circle me-2"></i>Practice Quizzes
                </a>
                <a href="<?= url('elms/certificates') ?>" class="btn btn-sm text-start fw-semibold" style="border-radius:9px;background:#fce7f3;color:#9d174d;font-size:.82rem">
                    <i class="fas fa-certificate me-2"></i>My Certificates
                </a>
                <?php else: ?>
                <a href="<?= url('elms/courses/create') ?>" class="btn btn-sm text-start fw-semibold" style="border-radius:9px;background:#ede9fe;color:#4f46e5;font-size:.82rem">
                    <i class="fas fa-plus me-2"></i>Create New Course
                </a>
                <a href="<?= url('elms/live/create') ?>" class="btn btn-sm text-start fw-semibold" style="border-radius:9px;background:#e0f2fe;color:#0369a1;font-size:.82rem">
                    <i class="fas fa-video me-2"></i>Schedule Live Class
                </a>
                <a href="<?= url('elms/analytics') ?>" class="btn btn-sm text-start fw-semibold" style="border-radius:9px;background:#f0fdf4;color:#065f46;font-size:.82rem">
                    <i class="fas fa-chart-line me-2"></i>View Analytics
                </a>
                <?php if (lmsCan('users.manage')): ?>
                <a href="<?= url('elms/users') ?>" class="btn btn-sm text-start fw-semibold" style="border-radius:9px;background:#fef3c7;color:#92400e;font-size:.82rem">
                    <i class="fas fa-users-cog me-2"></i>Manage LMS Users
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /right col -->
</div>

<script>
// Animate stat values on load
document.querySelectorAll('.dash-stat-val').forEach(el => {
    const target = parseInt(el.textContent.replace(/[^0-9]/g, ''));
    if (!target || target > 9999) return;
    let current = 0;
    const step  = Math.ceil(target / 30);
    const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = el.textContent.replace(/\d[\d,]*/, current.toLocaleString());
        if (current >= target) clearInterval(timer);
    }, 30);
});
</script>
