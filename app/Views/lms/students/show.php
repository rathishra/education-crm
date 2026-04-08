<style>
.profile-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; padding:1.25rem; margin-bottom:1rem; }
.info-label   { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#94a3b8; margin-bottom:.1rem; }
.info-val     { font-size:.88rem; color:#1e293b; font-weight:500; }
.prog-ring    { position:relative; width:52px; height:52px; flex-shrink:0; }
.course-row   { border-radius:10px; border:1px solid #f1f5f9; padding:.7rem .9rem; margin-bottom:.4rem; display:flex; align-items:center; gap:.75rem; transition:box-shadow .1s; }
.course-row:hover { box-shadow:0 1px 8px rgba(99,102,241,.1); }
.act-icon     { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.72rem; flex-shrink:0; }
.stat-box     { background:#f8fafc; border-radius:12px; padding:.9rem 1rem; text-align:center; }
</style>

<!-- Breadcrumb -->
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="<?= url('elms/students') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left me-1"></i>Students</a>
    <span class="text-muted small">/</span>
    <span class="fw-semibold small" style="color:#0f172a"><?= e($student['first_name'].' '.($student['last_name'] ?? '')) ?></span>
</div>

<div class="row g-3">
    <!-- LEFT: profile info -->
    <div class="col-lg-4">

        <!-- Avatar + name card -->
        <div class="profile-card text-center mb-0">
            <?php
            $colors   = ['#6366f1','#0284c7','#059669','#d97706','#dc2626','#8b5cf6'];
            $col      = $colors[abs(crc32($student['first_name'])) % count($colors)];
            $initials = strtoupper(substr($student['first_name'],0,1).substr($student['last_name'] ?? '',0,1));
            $photo    = $student['photo'] ?? $student['avatar'];
            ?>
            <?php if ($photo): ?>
            <img src="<?= e($photo) ?>" class="rounded-circle mb-2" style="width:80px;height:80px;object-fit:cover;border:3px solid #ede9fe">
            <?php else: ?>
            <div class="mx-auto rounded-circle mb-2 d-flex align-items-center justify-content-center fw-bold"
                 style="width:80px;height:80px;background:<?= $col ?>;color:#fff;font-size:1.6rem">
                <?= $initials ?>
            </div>
            <?php endif; ?>

            <div class="fw-bold" style="color:#0f172a;font-size:1.05rem"><?= e($student['first_name'].' '.($student['last_name'] ?? '')) ?></div>
            <div class="text-muted small mb-2"><?= e($student['email']) ?></div>

            <!-- Status badge -->
            <?php
            $statusColors = ['active'=>['#d1fae5','#059669'],'inactive'=>['#fee2e2','#dc2626'],'suspended'=>['#fef3c7','#d97706']];
            [$sbg,$sic] = $statusColors[$student['status']] ?? ['#f1f5f9','#64748b'];
            ?>
            <span class="badge" style="background:<?= $sbg ?>;color:<?= $sic ?>;border-radius:20px;font-size:.72rem"><?= ucfirst($student['status']) ?></span>

            <!-- XP / Level -->
            <div class="d-flex justify-content-center gap-3 mt-3 pt-2" style="border-top:1px solid #f1f5f9">
                <div>
                    <div class="fw-bold" style="color:#f59e0b;font-size:1.1rem"><?= number_format($student['xp_points']) ?></div>
                    <div class="text-muted" style="font-size:.68rem">XP Points</div>
                </div>
                <div>
                    <div class="fw-bold" style="color:#6366f1;font-size:1.1rem"><?= $student['level'] ?></div>
                    <div class="text-muted" style="font-size:.68rem">Level</div>
                </div>
                <div>
                    <div class="fw-bold" style="color:#10b981;font-size:1.1rem"><?= count($enrollments) ?></div>
                    <div class="text-muted" style="font-size:.68rem">Courses</div>
                </div>
            </div>

            <!-- Quick links -->
            <div class="d-flex gap-1 justify-content-center mt-3">
                <a href="<?= url('elms/analytics/student/'.$student['id']) ?>" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.75rem">
                    <i class="fas fa-chart-bar me-1"></i>Analytics
                </a>
                <?php if ($student['student_id']): ?>
                <a href="<?= url('students/'.$student['student_id']) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.75rem" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i>Academic
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Academic info -->
        <div class="profile-card mt-2">
            <div class="fw-semibold small mb-3" style="color:#6366f1"><i class="fas fa-graduation-cap me-1"></i>Academic Info</div>
            <?php $rows = [
                ['Roll No',        $student['roll_number']],
                ['Student ID',     $student['student_id_number']],
                ['Program',        $student['program_name']],
                ['Batch',          $student['batch_name']],
                ['Department',     $student['dept_name']],
                ['Academic Year',  $student['academic_year']],
                ['Semester',       $student['current_semester'] ? 'Semester '.$student['current_semester'] : null],
                ['Admission Date', $student['admission_date'] ? date('d M Y', strtotime($student['admission_date'])) : null],
                ['Admission Type', $student['admission_type'] ? ucfirst($student['admission_type']) : null],
            ]; ?>
            <?php foreach ($rows as [$label, $val]): ?>
            <?php if ($val): ?>
            <div class="mb-2">
                <div class="info-label"><?= $label ?></div>
                <div class="info-val"><?= e($val) ?></div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!array_filter(array_column($rows, 1))): ?>
            <div class="text-muted small">No academic record linked. <a href="<?= url('elms/sync') ?>">Run sync</a>.</div>
            <?php endif; ?>
        </div>

        <!-- Contact info -->
        <div class="profile-card mt-2">
            <div class="fw-semibold small mb-3" style="color:#6366f1"><i class="fas fa-address-card me-1"></i>Contact</div>
            <?php if ($student['phone'] ?? $student['acad_phone']): ?>
            <div class="mb-2">
                <div class="info-label">Phone</div>
                <div class="info-val"><?= e($student['phone'] ?? $student['acad_phone']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($student['city'] || $student['state']): ?>
            <div class="mb-2">
                <div class="info-label">Location</div>
                <div class="info-val"><?= e(trim(($student['city'] ?? '').', '.($student['state'] ?? ''), ', ')) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($student['father_name']): ?>
            <div class="mb-2">
                <div class="info-label">Parent / Guardian</div>
                <div class="info-val"><?= e($student['father_name']) ?></div>
                <?php if ($student['father_phone']): ?><div class="text-muted" style="font-size:.72rem"><?= e($student['father_phone']) ?></div><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- RIGHT: enrollments + activity -->
    <div class="col-lg-8">

        <!-- Stats row -->
        <div class="row g-2 mb-3">
            <?php
            $enrolled   = count(array_filter($enrollments, fn($e) => $e['status']==='active'));
            $completed  = count(array_filter($enrollments, fn($e) => $e['status']==='completed'));
            $avgProg    = $enrolled > 0 ? round(array_sum(array_column(array_filter($enrollments, fn($e)=>$e['status']==='active'), 'real_progress')) / $enrolled) : 0;
            $statCards  = [
                ['Enrolled',   $enrolled,                          '#6366f1','fas fa-book-open'],
                ['Completed',  $completed,                         '#059669','fas fa-check-circle'],
                ['Avg Progress',$avgProg.'%',                      '#0284c7','fas fa-chart-line'],
                ['Assignments',(int)($assignStats['total'] ?? 0), '#d97706','fas fa-tasks'],
                ['Quizzes',    (int)($quizStats['total']   ?? 0), '#8b5cf6','fas fa-question-circle'],
                ['Avg Quiz',   ($quizStats['avg_pct'] ?? 0).'%',  '#10b981','fas fa-star'],
            ];
            ?>
            <?php foreach ($statCards as [$label, $val, $color, $icon]): ?>
            <div class="col-6 col-md-4">
                <div class="stat-box">
                    <i class="<?= $icon ?>" style="color:<?= $color ?>;font-size:.9rem"></i>
                    <div class="fw-bold mt-1" style="font-size:1.15rem;color:#0f172a"><?= $val ?></div>
                    <div class="text-muted" style="font-size:.7rem"><?= $label ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Enrolled courses -->
        <div class="profile-card">
            <div class="fw-semibold small mb-3" style="color:#6366f1"><i class="fas fa-book-open me-1"></i>Enrolled Courses</div>
            <?php if (empty($enrollments)): ?>
            <div class="text-muted small text-center py-3">No course enrollments yet.</div>
            <?php else: ?>
            <?php foreach ($enrollments as $e):
                $prog = (int)($e['real_progress'] ?? 0);
                $statusBadges = [
                    'active'    => ['#ede9fe','#6366f1','In Progress'],
                    'completed' => ['#d1fae5','#059669','Completed'],
                    'dropped'   => ['#fee2e2','#dc2626','Dropped'],
                ];
                [$ebg,$eic,$elabel] = $statusBadges[$e['status']] ?? ['#f1f5f9','#64748b',ucfirst($e['status'])];
            ?>
            <div class="course-row">
                <!-- Thumbnail / icon -->
                <?php if ($e['thumbnail']): ?>
                <img src="<?= e($e['thumbnail']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:8px;flex-shrink:0">
                <?php else: ?>
                <div style="width:40px;height:40px;border-radius:8px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-book" style="color:#6366f1;font-size:.85rem"></i>
                </div>
                <?php endif; ?>

                <div style="flex:1;min-width:0">
                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <div>
                            <div class="fw-semibold" style="font-size:.85rem;color:#0f172a">
                                <a href="<?= url('elms/courses/'.$e['course_id']) ?>" class="text-decoration-none" style="color:inherit"><?= e($e['title']) ?></a>
                            </div>
                            <div class="text-muted" style="font-size:.7rem">
                                <?= e($e['code']) ?>
                                <?php if ($e['subject_type']): ?> &middot; <?= ucfirst($e['subject_type']) ?><?php endif; ?>
                                <?php if ($e['semester']): ?> &middot; Sem <?= $e['semester'] ?><?php endif; ?>
                                <?php if ($e['credits']): ?> &middot; <?= $e['credits'] ?> cr<?php endif; ?>
                            </div>
                        </div>
                        <span class="badge" style="background:<?= $ebg ?>;color:<?= $eic ?>;border-radius:20px;font-size:.68rem"><?= $elabel ?></span>
                    </div>
                    <!-- Progress bar -->
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <div style="flex:1;height:4px;border-radius:2px;background:#e2e8f0;overflow:hidden">
                            <div style="height:100%;width:<?= $prog ?>%;background:<?= $prog>=100?'#059669':'#6366f1' ?>;border-radius:2px;transition:width .4s"></div>
                        </div>
                        <span style="font-size:.68rem;color:#64748b;width:28px"><?= $prog ?>%</span>
                    </div>
                </div>

                <a href="<?= url('elms/gradebook/'.$e['course_id'].'/student/'.$student['id']) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:.68rem;padding:.2rem .5rem;flex-shrink:0" title="Grade detail">
                    <i class="fas fa-chart-bar"></i>
                </a>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <?php if (!empty($activity)): ?>
        <div class="profile-card mt-0">
            <div class="fw-semibold small mb-3" style="color:#6366f1"><i class="fas fa-history me-1"></i>Recent Activity</div>
            <?php
            $actIcons = [
                'lesson_completed'   => ['fas fa-play-circle','#6366f1','#ede9fe'],
                'quiz_submitted'     => ['fas fa-question-circle','#0284c7','#dbeafe'],
                'quiz_passed'        => ['fas fa-star','#f59e0b','#fef3c7'],
                'assignment_submitted'=>['fas fa-tasks','#059669','#d1fae5'],
                'live_joined'        => ['fas fa-broadcast-tower','#ef4444','#fee2e2'],
                'forum_post'         => ['fas fa-comments','#10b981','#d1fae5'],
                'badge_earned'       => ['fas fa-medal','#f59e0b','#fef3c7'],
            ];
            ?>
            <?php foreach ($activity as $act):
                [$aicon,$aic,$abg] = $actIcons[$act['event']] ?? ['fas fa-circle','#94a3b8','#f1f5f9'];
                $diff  = time() - strtotime($act['created_at']);
                $atime = $diff < 60  ? 'just now'
                       : ($diff < 3600  ? floor($diff/60).'m ago'
                       : ($diff < 86400 ? floor($diff/3600).'h ago'
                       : date('d M Y', strtotime($act['created_at']))));
            ?>
            <div class="d-flex align-items-start gap-2 mb-2">
                <div class="act-icon" style="background:<?= $abg ?>">
                    <i class="<?= $aicon ?>" style="color:<?= $aic ?>"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="small fw-semibold" style="color:#0f172a;font-size:.8rem"><?= e($act['entity_title'] ?? ucwords(str_replace('_',' ',$act['event']))) ?></div>
                    <?php if ($act['xp_earned']): ?>
                    <div style="font-size:.68rem;color:#f59e0b">+<?= $act['xp_earned'] ?> XP</div>
                    <?php endif; ?>
                </div>
                <div style="font-size:.67rem;color:#94a3b8;flex-shrink:0"><?= $atime ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>
