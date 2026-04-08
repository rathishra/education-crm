<?php
$isInstructor = in_array($lmsUser['role'] ?? '', ['lms_admin','instructor']);
$isEnrolled   = !empty($enrollment);
$isDone       = ($progress['status'] ?? '') === 'completed';
$typeConf = [
    'video'      => ['fa-play-circle',    '#1d4ed8','#dbeafe'],
    'document'   => ['fa-file-pdf',       '#92400e','#fef3c7'],
    'text'       => ['fa-file-alt',       '#065f46','#d1fae5'],
    'quiz'       => ['fa-question-circle','#7c3aed','#ede9fe'],
    'assignment' => ['fa-tasks',          '#9d174d','#fce7f3'],
    'live'       => ['fa-video',          '#dc2626','#fee2e2'],
    'scorm'      => ['fa-cube',           '#0369a1','#e0f2fe'],
];
[$typeIcon, $typeColor, $typeBg] = $typeConf[$lesson['type']] ?? ['fa-circle','#64748b','#f1f5f9'];
?>
<style>
.viewer-layout { display:grid; grid-template-columns:1fr 280px; gap:1.25rem; align-items:start; }
@media(max-width:991.98px) { .viewer-layout { grid-template-columns:1fr; } .viewer-sidebar { order:2; } }
.viewer-main { min-width:0; }
.viewer-sidebar { position:sticky; top:calc(58px + 1rem); max-height:calc(100vh - 80px); overflow-y:auto; }
.lesson-nav-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; overflow:hidden; }
.lesson-nav-card .lnc-header { background:#f8f7ff; border-bottom:1px solid #e8e3ff; padding:.75rem 1rem; font-weight:700; font-size:.82rem; color:#3730a3; }
.lnc-section { padding:.4rem .75rem .2rem; font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; }
.lnc-lesson { display:flex; align-items:center; gap:.6rem; padding:.5rem .75rem; font-size:.8rem; color:#475569; cursor:pointer; transition:background .12s; text-decoration:none; border-radius:0; }
.lnc-lesson:hover { background:#f1f5f9; color:#0f172a; }
.lnc-lesson.current { background:#ede9fe; color:#4f46e5; font-weight:600; }
.lnc-lesson.done .lnc-check { color:#059669; }
.lnc-check { width:16px; height:16px; border-radius:50%; border:2px solid #cbd5e1; display:flex; align-items:center; justify-content:center; font-size:.55rem; flex-shrink:0; }
.lnc-lesson.done .lnc-check { background:#d1fae5; border-color:#059669; color:#059669; }
.lnc-lesson.current .lnc-check { border-color:#6366f1; background:#ede9fe; color:#6366f1; }
.lnc-type-dot { width:22px; height:22px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.6rem; flex-shrink:0; }
.viewer-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; overflow:hidden; margin-bottom:1rem; }
.viewer-card .vc-header { padding:1rem 1.25rem; border-bottom:1px solid #e8e3ff; display:flex; align-items:center; gap:.75rem; }
.viewer-card .vc-body { padding:1.25rem; }
.progress-banner { background:linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; border-radius:14px; padding:1rem 1.5rem; margin-bottom:1rem; display:flex; align-items:center; justify-content:between; gap:1rem; }
.lesson-content { font-size:.95rem; line-height:1.75; color:#334155; }
.lesson-content h1,.lesson-content h2,.lesson-content h3 { color:#0f172a; font-weight:700; margin-top:1.5rem; }
.lesson-content p { margin-bottom:1rem; }
.lesson-content pre,.lesson-content code { background:#f1f5f9; border-radius:6px; font-size:.85rem; }
.lesson-content pre { padding:.75rem 1rem; overflow-x:auto; }
.lesson-content code { padding:.1rem .3rem; }
.lesson-content ul,.lesson-content ol { padding-left:1.5rem; margin-bottom:1rem; }
.lesson-content blockquote { border-left:4px solid #6366f1; padding:.5rem 1rem; background:#f8f7ff; border-radius:0 8px 8px 0; color:#475569; margin:1rem 0; }
.lesson-content img { max-width:100%; border-radius:8px; margin:.5rem 0; }
.lesson-content table { width:100%; border-collapse:collapse; margin:1rem 0; font-size:.87rem; }
.lesson-content th,.lesson-content td { border:1px solid #e2e8f0; padding:.5rem .75rem; }
.lesson-content th { background:#f8fafc; font-weight:600; }
</style>

<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="<?= url('elms/courses/'.$course['id']) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px">
        <i class="fas fa-arrow-left me-1"></i><?= e($course['title']) ?>
    </a>
    <?php if ($isInstructor): ?>
    <a href="<?= url('elms/courses/'.$course['id'].'/lessons/'.$lesson['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary ms-auto" style="border-radius:8px">
        <i class="fas fa-edit me-1"></i>Edit Lesson
    </a>
    <?php endif; ?>
</div>

<div class="viewer-layout">
    <!-- MAIN CONTENT -->
    <div class="viewer-main">

        <!-- Video -->
        <?php if ($lesson['type'] === 'video' && !empty($lesson['video_url'])): ?>
        <div class="viewer-card mb-3">
            <div class="ratio ratio-16x9" style="max-height:520px">
                <iframe src="<?= e($lesson['video_url']) ?>"
                        allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        style="border:none"></iframe>
            </div>
        </div>
        <?php endif; ?>

        <!-- Document download -->
        <?php if ($lesson['type'] === 'document' && !empty($lesson['file_path'])): ?>
        <div class="viewer-card">
            <div class="vc-body text-center py-4">
                <div style="font-size:3rem;color:#92400e;opacity:.7" class="mb-3"><i class="fas fa-file-pdf"></i></div>
                <h5 class="fw-bold mb-2"><?= e($lesson['title']) ?></h5>
                <p class="text-muted small mb-3">Click below to download and view the document.</p>
                <a href="<?= asset($lesson['file_path']) ?>" download class="btn btn-primary" style="border-radius:9px">
                    <i class="fas fa-download me-2"></i>Download Document
                </a>
                <a href="<?= asset($lesson['file_path']) ?>" target="_blank" class="btn btn-outline-primary ms-2" style="border-radius:9px">
                    <i class="fas fa-external-link-alt me-2"></i>Open in New Tab
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- SCORM -->
        <?php if ($lesson['type'] === 'scorm' && !empty($lesson['file_path'])): ?>
        <div class="viewer-card">
            <div class="vc-body text-center py-4">
                <div style="font-size:3rem;color:#0369a1;opacity:.7" class="mb-3"><i class="fas fa-cube"></i></div>
                <h5 class="fw-bold mb-2"><?= e($lesson['title']) ?></h5>
                <p class="text-muted small mb-3">Launch the interactive SCORM package below.</p>
                <a href="<?= asset($lesson['file_path']) ?>" target="_blank" class="btn btn-primary" style="border-radius:9px">
                    <i class="fas fa-play me-2"></i>Launch Module
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Lesson title & meta -->
        <div class="viewer-card">
            <div class="vc-header">
                <div class="lnc-type-dot" style="background:<?= $typeBg ?>;color:<?= $typeColor ?>">
                    <i class="fas <?= $typeIcon ?>"></i>
                </div>
                <div style="flex:1">
                    <h4 class="fw-bold mb-0" style="font-size:1.15rem;color:#0f172a"><?= e($lesson['title']) ?></h4>
                    <div class="text-muted small"><?= e($lesson['section_title']) ?></div>
                </div>
                <?php if ($isEnrolled && !$isDone): ?>
                <button class="btn btn-success btn-sm" id="btnMarkDone" style="border-radius:9px">
                    <i class="fas fa-check me-1"></i>Mark Complete
                </button>
                <?php elseif ($isDone): ?>
                <span class="badge bg-success" style="border-radius:8px;font-size:.78rem;padding:.4rem .85rem">
                    <i class="fas fa-check-circle me-1"></i>Completed
                </span>
                <?php endif; ?>
            </div>

            <!-- Text / HTML content -->
            <?php if (!empty($lesson['content'])): ?>
            <div class="vc-body">
                <div class="lesson-content"><?= $lesson['content'] /* content is admin-controlled, not user input */ ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Prev / Next navigation -->
        <div class="d-flex justify-content-between gap-2 mt-1">
            <?php if ($prevLesson): ?>
            <a href="<?= url('elms/courses/'.$course['id'].'/lessons/'.$prevLesson['id']) ?>"
               class="btn btn-outline-secondary" style="border-radius:9px;flex:1;max-width:280px">
                <i class="fas fa-chevron-left me-2"></i><?= e(mb_strimwidth($prevLesson['title'], 0, 35, '…')) ?>
            </a>
            <?php else: ?>
            <div></div>
            <?php endif; ?>
            <?php if ($nextLesson): ?>
            <a href="<?= url('elms/courses/'.$course['id'].'/lessons/'.$nextLesson['id']) ?>"
               class="btn btn-<?= $isDone?'primary':'outline-primary' ?>" style="border-radius:9px;flex:1;max-width:280px;text-align:right">
                <?= e(mb_strimwidth($nextLesson['title'], 0, 35, '…')) ?><i class="fas fa-chevron-right ms-2"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- SIDEBAR: curriculum nav -->
    <div class="viewer-sidebar">
        <div class="lesson-nav-card">
            <div class="lnc-header"><i class="fas fa-list-ol me-2"></i>Course Content</div>
            <?php foreach ($sections as $sec): ?>
            <div class="lnc-section"><?= e($sec['title']) ?></div>
            <?php foreach ($sec['lessons'] as $navLesson): ?>
            <?php
            $isCurrent = ((int)$navLesson['id'] === (int)$lesson['id']);
            $navDone   = ($navLesson['progress_status'] ?? '') === 'completed';
            [$navIcon,$navColor,$navBg] = $typeConf[$navLesson['type']] ?? ['fa-circle','#94a3b8','#f1f5f9'];
            ?>
            <a href="<?= url('elms/courses/'.$course['id'].'/lessons/'.$navLesson['id']) ?>"
               class="lnc-lesson <?= $isCurrent?'current':'' ?> <?= $navDone?'done':'' ?>">
                <div class="lnc-check"><i class="fas fa-check" style="font-size:.5rem"></i></div>
                <div class="lnc-type-dot" style="background:<?= $navBg ?>;color:<?= $navColor ?>">
                    <i class="fas <?= $navIcon ?>"></i>
                </div>
                <span style="flex:1;overflow:hidden;white-space:nowrap;text-overflow:ellipsis"><?= e($navLesson['title']) ?></span>
                <?php if ($navLesson['video_duration']): ?>
                <span style="font-size:.65rem;color:#94a3b8;flex-shrink:0"><?= gmdate('i:s', (int)$navLesson['video_duration']) ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php endforeach; ?>

            <!-- Overall enrollment progress -->
            <?php if ($enrollment): ?>
            <div class="p-3 border-top">
                <div class="d-flex justify-content-between mb-1" style="font-size:.75rem;font-weight:600;color:#475569">
                    <span>Your Progress</span>
                    <span><?= $enrollment['progress'] ?>%</span>
                </div>
                <div style="height:6px;background:#e8e3ff;border-radius:3px;overflow:hidden">
                    <div style="height:100%;width:<?= $enrollment['progress'] ?>%;background:linear-gradient(90deg,#6366f1,#818cf8);border-radius:3px;transition:width .4s"></div>
                </div>
                <div class="text-muted mt-1" style="font-size:.7rem"><?= $enrollment['lessons_completed'] ?> lessons completed</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('btnMarkDone')?.addEventListener('click', function () {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    fetch('<?= url('elms/courses/'.$course['id'].'/lessons/'.$lesson['id'].'/progress') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ status: 'completed' })
    })
    .then(r => r.json())
    .then(d => {
        if (d.status === 'ok') {
            btn.outerHTML = `<span class="badge bg-success" style="border-radius:8px;font-size:.78rem;padding:.4rem .85rem">
                <i class="fas fa-check-circle me-1"></i>Completed</span>`;
            // Update progress bar in sidebar
            const bar = document.querySelector('.viewer-sidebar [style*="width:"]');
            if (bar) bar.style.width = d.progress + '%';
            if (d.completed) {
                setTimeout(() => {
                    if (confirm('You completed this course! View your certificate?')) {
                        window.location.href = '<?= url('elms/certificates') ?>';
                    }
                }, 800);
            } else if ('<?= $nextLesson['id'] ?? '' ?>') {
                setTimeout(() => {
                    window.location.href = '<?= $nextLesson ? url('elms/courses/'.$course['id'].'/lessons/'.$nextLesson['id']) : '' ?>';
                }, 900);
            }
        }
    })
    .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check me-1"></i>Mark Complete'; });
});
</script>
