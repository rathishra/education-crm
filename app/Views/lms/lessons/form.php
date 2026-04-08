<?php
$isEdit   = !empty($lesson);
$l        = $lesson ?? [];
$typeConf = [
    'video'      => ['fa-play-circle',    'Video Lesson',      '#1d4ed8', '#dbeafe'],
    'document'   => ['fa-file-pdf',       'Document',          '#92400e', '#fef3c7'],
    'text'       => ['fa-file-alt',       'Text / Article',    '#065f46', '#d1fae5'],
    'quiz'       => ['fa-question-circle','Quiz',              '#7c3aed', '#ede9fe'],
    'assignment' => ['fa-tasks',          'Assignment',        '#9d174d', '#fce7f3'],
    'live'       => ['fa-video',          'Live Class',        '#dc2626', '#fee2e2'],
    'scorm'      => ['fa-cube',           'SCORM Package',     '#0369a1', '#e0f2fe'],
];
$selectedType = $l['type'] ?? 'video';
?>
<style>
.lms-form-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; margin-bottom:1.25rem; overflow:hidden; }
.lms-form-card .card-header { background:#f8f7ff; border-bottom:1px solid #e8e3ff; padding:.85rem 1.25rem; font-weight:700; font-size:.88rem; color:#3730a3; }
.lms-form-card .card-body { padding:1.25rem; }
.type-pill {
    display:flex; align-items:center; gap:.6rem; padding:.65rem 1rem; border-radius:10px;
    border:2px solid #e8e3ff; cursor:pointer; transition:all .15s; flex:1; min-width:130px;
}
.type-pill:hover { border-color:#6366f1; background:#f8f7ff; }
.type-pill.selected { border-color:#6366f1; background:#f5f3ff; }
.type-pill input[type=radio] { display:none; }
.type-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.8rem; flex-shrink:0; }
.content-panel { display:none; }
.content-panel.active { display:block; }
</style>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/courses/'.$course['id']) ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0" style="color:#0f172a">
            <i class="fas fa-<?= $isEdit?'edit':'plus-circle' ?> me-2 text-primary"></i><?= $isEdit ? 'Edit Lesson' : 'Add Lesson' ?>
        </h4>
        <div class="text-muted small"><?= e($course['title']) ?></div>
    </div>
</div>

<form method="POST" action="<?= url($isEdit ? 'elms/courses/'.$course['id'].'/lessons/'.$l['id'].'/update' : 'elms/courses/'.$course['id'].'/lessons/store') ?>" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-3">
        <!-- LEFT -->
        <div class="col-12 col-lg-8">

            <!-- Basic Info -->
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Lesson Details</div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Lesson Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Introduction to HTML Tags"
                                   value="<?= e($l['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Section <span class="text-danger">*</span></label>
                            <select name="section_id" class="form-select" required>
                                <option value="">— Select Section —</option>
                                <?php foreach ($sections as $sec): ?>
                                <option value="<?= $sec['id'] ?>"
                                    <?= ($l['section_id'] ?? $sectionId ?? '') == $sec['id'] ? 'selected' : '' ?>>
                                    <?= e($sec['title']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Type selector -->
                    <label class="form-label fw-semibold small d-block mb-2">Lesson Type <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="typePills">
                        <?php foreach ($typeConf as $val => [$icon, $label, $color, $bg]): ?>
                        <label class="type-pill <?= $selectedType === $val ? 'selected' : '' ?>">
                            <input type="radio" name="type" value="<?= $val ?>" <?= $selectedType === $val ? 'checked' : '' ?>>
                            <div class="type-icon" style="background:<?= $bg ?>;color:<?= $color ?>"><i class="fas <?= $icon ?>"></i></div>
                            <span style="font-size:.8rem;font-weight:600"><?= $label ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Type-specific content panels -->

            <!-- VIDEO -->
            <div class="lms-form-card content-panel <?= $selectedType==='video'?'active':'' ?>" id="panel-video">
                <div class="card-header"><i class="fas fa-play-circle me-2 text-primary"></i>Video Content</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Video URL</label>
                        <input type="url" name="video_url" class="form-control" placeholder="YouTube, Vimeo, or direct .mp4 URL"
                               value="<?= e($l['video_url'] ?? '') ?>">
                        <div class="form-text">Paste a YouTube or Vimeo link — it will be converted to embed automatically.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Duration (mm:ss or seconds)</label>
                        <input type="text" name="video_duration" class="form-control" placeholder="e.g. 12:34" style="max-width:180px"
                               value="<?= $l['video_duration'] ? gmdate('i:s', (int)$l['video_duration']) : '' ?>">
                    </div>
                    <?php if (!empty($l['video_url'])): ?>
                    <div class="ratio ratio-16x9 mb-2" style="border-radius:10px;overflow:hidden;max-width:480px">
                        <iframe src="<?= e($l['video_url']) ?>" allowfullscreen></iframe>
                    </div>
                    <?php endif; ?>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Additional Notes / Transcript</label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Optional notes shown below the video…"><?= e($l['content'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- TEXT / ARTICLE -->
            <div class="lms-form-card content-panel <?= $selectedType==='text'?'active':'' ?>" id="panel-text">
                <div class="card-header"><i class="fas fa-file-alt me-2 text-success"></i>Text / Article Content</div>
                <div class="card-body">
                    <label class="form-label fw-semibold small">Content <span class="text-danger">*</span></label>
                    <textarea name="content" id="textContent" class="form-control" rows="15"
                              placeholder="Write your lesson content here. HTML is supported."><?= e($l['type']==='text' ? ($l['content'] ?? '') : '') ?></textarea>
                    <div class="form-text mt-1">Basic HTML formatting is supported (headings, bold, lists, code blocks).</div>
                </div>
            </div>

            <!-- DOCUMENT -->
            <div class="lms-form-card content-panel <?= $selectedType==='document'?'active':'' ?>" id="panel-document">
                <div class="card-header"><i class="fas fa-file-pdf me-2" style="color:#92400e"></i>Document Upload</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Upload File</label>
                        <input type="file" name="lesson_file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip">
                        <div class="form-text">PDF, Word, PowerPoint, Excel, ZIP — max 20MB</div>
                        <?php if (!empty($l['file_path'])): ?>
                        <div class="mt-2 p-2 bg-light rounded d-flex align-items-center gap-2" style="font-size:.82rem">
                            <i class="fas fa-paperclip text-muted"></i>
                            <span><?= e(basename($l['file_path'])) ?></span>
                            <span class="text-muted">(Current file — upload new to replace)</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Description / Instructions</label>
                        <textarea name="content" class="form-control" rows="4" placeholder="Describe what learners will find in this document…"><?= e($l['type']==='document' ? ($l['content'] ?? '') : '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- QUIZ placeholder -->
            <div class="lms-form-card content-panel <?= $selectedType==='quiz'?'active':'' ?>" id="panel-quiz">
                <div class="card-header"><i class="fas fa-question-circle me-2" style="color:#7c3aed"></i>Quiz</div>
                <div class="card-body">
                    <div class="alert alert-info border-0 d-flex gap-2" style="border-radius:10px;font-size:.85rem">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>Quiz questions are managed in <strong>Module 6 — Quiz Builder</strong>. Save this lesson first, then add questions from the Quiz module.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Quiz Instructions</label>
                        <textarea name="content" class="form-control" rows="3" placeholder="Instructions shown to learners before they start the quiz…"><?= e($l['type']==='quiz' ? ($l['content'] ?? '') : '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- ASSIGNMENT placeholder -->
            <div class="lms-form-card content-panel <?= $selectedType==='assignment'?'active':'' ?>" id="panel-assignment">
                <div class="card-header"><i class="fas fa-tasks me-2" style="color:#9d174d"></i>Assignment</div>
                <div class="card-body">
                    <div class="alert alert-info border-0 d-flex gap-2" style="border-radius:10px;font-size:.85rem">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>Assignment submissions &amp; grading are managed in <strong>Module 5 — Assignments</strong>.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Assignment Brief <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="6" placeholder="Describe the assignment task, deliverables, and submission requirements…"><?= e($l['type']==='assignment' ? ($l['content'] ?? '') : '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- LIVE -->
            <div class="lms-form-card content-panel <?= $selectedType==='live'?'active':'' ?>" id="panel-live">
                <div class="card-header"><i class="fas fa-video me-2 text-danger"></i>Live Class</div>
                <div class="card-body">
                    <div class="alert alert-info border-0 d-flex gap-2" style="border-radius:10px;font-size:.85rem">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>Live session scheduling is managed in <strong>Module 8 — Live Classes</strong>.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Session Description</label>
                        <textarea name="content" class="form-control" rows="3" placeholder="What will be covered in this live session…"><?= e($l['type']==='live' ? ($l['content'] ?? '') : '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- SCORM -->
            <div class="lms-form-card content-panel <?= $selectedType==='scorm'?'active':'' ?>" id="panel-scorm">
                <div class="card-header"><i class="fas fa-cube me-2" style="color:#0369a1"></i>SCORM Package</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Upload SCORM Package (.zip)</label>
                        <input type="file" name="lesson_file" class="form-control" accept=".zip">
                        <div class="form-text">Upload a SCORM 1.2 or 2004 compliant .zip package — max 50MB</div>
                        <?php if (!empty($l['file_path']) && $l['type']==='scorm'): ?>
                        <div class="mt-2 text-muted small"><i class="fas fa-check-circle text-success me-1"></i>Package uploaded: <?= e(basename($l['file_path'])) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Description</label>
                        <textarea name="content" class="form-control" rows="3" placeholder="Describe this SCORM module…"><?= e($l['type']==='scorm' ? ($l['content'] ?? '') : '') ?></textarea>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT: Settings -->
        <div class="col-12 col-lg-4">
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Lesson Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">XP Reward</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-star text-warning"></i></span>
                            <input type="number" name="xp_reward" class="form-control" min="0" max="500"
                                   value="<?= e($l['xp_reward'] ?? 10) ?>">
                            <span class="input-group-text">XP</span>
                        </div>
                        <div class="form-text">Points awarded when learner completes this lesson.</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_published" id="chkPublished" value="1"
                                   <?= ($l['is_published'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold" for="chkPublished">Published</label>
                            <div class="form-text">Unpublished lessons are hidden from learners.</div>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_free" id="chkFree" value="1"
                                   <?= !empty($l['is_free']) ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-semibold" for="chkFree">Free Preview</label>
                            <div class="form-text">Non-enrolled users can view this lesson as a preview.</div>
                        </div>
                    </div>
                    <?php if ($isEdit): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" min="0"
                               value="<?= e($l['sort_order'] ?? 0) ?>">
                    </div>
                    <?php endif; ?>
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary" style="border-radius:9px">
                            <i class="fas fa-save me-2"></i><?= $isEdit ? 'Save Changes' : 'Add Lesson' ?>
                        </button>
                        <a href="<?= url('elms/courses/'.$course['id']) ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
                    </div>
                </div>
            </div>

            <!-- Preview card for video -->
            <div class="lms-form-card" id="videoPreviewCard" style="<?= $selectedType!=='video'?'display:none':'' ?>">
                <div class="card-header"><i class="fas fa-eye me-2"></i>Video Preview</div>
                <div class="card-body p-2">
                    <div id="videoPreviewContainer">
                        <?php if (!empty($l['video_url']) && $l['type']==='video'): ?>
                        <div class="ratio ratio-16x9" style="border-radius:8px;overflow:hidden">
                            <iframe src="<?= e($l['video_url']) ?>" allowfullscreen></iframe>
                        </div>
                        <?php else: ?>
                        <div class="text-center text-muted py-3" style="font-size:.8rem"><i class="fas fa-play-circle d-block fs-2 mb-1 opacity-25"></i>Enter a video URL to preview</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// Type pill switching
document.querySelectorAll('#typePills .type-pill').forEach(pill => {
    pill.addEventListener('click', function () {
        const val = this.querySelector('input[type=radio]').value;
        document.querySelectorAll('.type-pill').forEach(p => p.classList.remove('selected'));
        this.classList.add('selected');
        document.querySelectorAll('.content-panel').forEach(p => p.classList.remove('active'));
        const panel = document.getElementById('panel-' + val);
        if (panel) panel.classList.add('active');
        document.getElementById('videoPreviewCard').style.display = val === 'video' ? '' : 'none';
    });
});

// Live video preview
const videoUrlInput = document.querySelector('input[name="video_url"]');
videoUrlInput?.addEventListener('blur', function () {
    const url = this.value.trim();
    if (!url) return;
    let embedUrl = url;
    const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_\-]+)/);
    const vmMatch = url.match(/vimeo\.com\/(\d+)/);
    if (ytMatch) embedUrl = 'https://www.youtube.com/embed/' + ytMatch[1];
    else if (vmMatch) embedUrl = 'https://player.vimeo.com/video/' + vmMatch[1];

    document.getElementById('videoPreviewContainer').innerHTML =
        `<div class="ratio ratio-16x9" style="border-radius:8px;overflow:hidden">
            <iframe src="${embedUrl}" allowfullscreen></iframe>
         </div>`;
});
</script>
