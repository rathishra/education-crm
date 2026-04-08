<?php $isEdit = !empty($quiz); $q = $quiz ?? []; ?>
<style>
.lms-form-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; margin-bottom:1.25rem; overflow:hidden; }
.lms-form-card .card-header { background:#f8f7ff; border-bottom:1px solid #e8e3ff; padding:.85rem 1.25rem; font-weight:700; font-size:.88rem; color:#3730a3; }
.lms-form-card .card-body { padding:1.25rem; }
</style>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('elms/quizzes') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <h4 class="fw-bold mb-0" style="color:#0f172a">
        <i class="fas fa-<?= $isEdit?'edit':'plus-circle' ?> me-2 text-primary"></i><?= $isEdit ? 'Edit Quiz' : 'Create Quiz' ?>
    </h4>
</div>

<form method="POST" action="<?= url($isEdit ? 'elms/quizzes/'.$q['id'].'/update' : 'elms/quizzes/store') ?>">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Quiz Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= e($q['title'] ?? '') ?>" required placeholder="e.g. Chapter 1 Assessment">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Description / Instructions</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Instructions shown to learners before the quiz starts…"><?= e($q['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Course <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-select" id="courseSelect" required>
                                <option value="">— Select Course —</option>
                                <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($q['course_id'] ?? $courseId)==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Link to Lesson</label>
                            <select name="lesson_id" class="form-select" id="lessonSelect">
                                <option value="">— None —</option>
                                <?php foreach ($lessons as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= ($q['lesson_id'] ?? '')==$l['id']?'selected':'' ?>><?= e($l['section_title']) ?> › <?= e($l['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-sliders-h me-2"></i>Quiz Settings</div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Time Limit (min)</label>
                            <input type="number" name="time_limit_mins" class="form-control" min="1" value="<?= e($q['time_limit_mins'] ?? '') ?>" placeholder="No limit">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Attempts Allowed</label>
                            <input type="number" name="attempts_allowed" class="form-control" min="0" value="<?= e($q['attempts_allowed'] ?? 1) ?>">
                            <div class="form-text">0 = unlimited</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Pass %</label>
                            <div class="input-group">
                                <input type="number" name="pass_percentage" class="form-control" min="1" max="100" value="<?= e($q['pass_percentage'] ?? 60) ?>">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Show Results</label>
                            <select name="show_result" class="form-select">
                                <?php foreach (['immediately'=>'Immediately','after_due'=>'After Due Date','never'=>'Never'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($q['show_result'] ?? 'immediately')===$v?'selected':'' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="shuffle_questions" id="chkShuffleQ" value="1" <?= !empty($q['shuffle_questions'])?'checked':'' ?>>
                                <label class="form-check-label small fw-semibold" for="chkShuffleQ">Shuffle Questions</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="shuffle_options" id="chkShuffleO" value="1" <?= !empty($q['shuffle_options'])?'checked':'' ?>>
                                <label class="form-check-label small fw-semibold" for="chkShuffleO">Shuffle Options</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="show_correct" id="chkShowCorrect" value="1" <?= ($q['show_correct'] ?? 1)?'checked':'' ?>>
                                <label class="form-check-label small fw-semibold" for="chkShowCorrect">Show Correct Answers After</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Due Date & Time</label>
                            <input type="datetime-local" name="due_at" class="form-control"
                                   value="<?= e(!empty($q['due_at']) ? date('Y-m-d\TH:i', strtotime($q['due_at'])) : '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Publish</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_published" id="chkPub" value="1" <?= ($q['is_published'] ?? 1)?'checked':'' ?>>
                        <label class="form-check-label small fw-semibold" for="chkPub">Published (visible to learners)</label>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" style="border-radius:9px">
                            <i class="fas fa-save me-2"></i><?= $isEdit ? 'Save & Go to Builder' : 'Create & Add Questions' ?>
                        </button>
                        <a href="<?= url('elms/quizzes') ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
                    </div>
                    <?php if ($isEdit): ?>
                    <hr>
                    <a href="<?= url('elms/quizzes/'.$q['id'].'/builder') ?>" class="btn btn-outline-primary w-100" style="border-radius:9px">
                        <i class="fas fa-tools me-2"></i>Open Question Builder
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
document.getElementById('courseSelect')?.addEventListener('change', function () {
    const courseId = this.value;
    const sel = document.getElementById('lessonSelect');
    sel.innerHTML = '<option value="">— Loading… —</option>';
    if (!courseId) { sel.innerHTML = '<option value="">— None —</option>'; return; }
    fetch(`<?= url('elms/courses') ?>/${courseId}/lessons-list`, { headers: {'X-Requested-With':'XMLHttpRequest'} })
        .then(r => r.json())
        .then(d => {
            sel.innerHTML = '<option value="">— None —</option>';
            d.forEach(l => sel.innerHTML += `<option value="${l.id}">${l.section_title} › ${l.title}</option>`);
        })
        .catch(() => { sel.innerHTML = '<option value="">— None —</option>'; });
});
</script>
