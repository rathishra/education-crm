<?php
$isEdit   = !empty($course);
$c        = $course ?? [];
$tagInput = $tagNames ?? '';
?>
<style>
.lms-form-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; box-shadow:0 1px 6px rgba(99,102,241,.06); margin-bottom:1.25rem; }
.lms-form-card .card-header { background:#f8f7ff; border-bottom:1px solid #e8e3ff; border-radius:14px 14px 0 0; padding:.85rem 1.25rem; font-weight:700; font-size:.88rem; color:#3730a3; }
.lms-form-card .card-body { padding:1.25rem; }
.lms-thumb-preview { width:100%; height:160px; border-radius:10px; border:2px dashed #c7d2fe; display:flex; align-items:center; justify-content:center; background:#f8f7ff; color:#a5b4fc; font-size:2.5rem; overflow:hidden; cursor:pointer; transition:border-color .15s; }
.lms-thumb-preview:hover { border-color:#6366f1; }
.lms-thumb-preview img { width:100%; height:100%; object-fit:cover; border-radius:8px; }
</style>

<!-- PAGE HEADER -->
<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url($isEdit ? 'elms/courses/'.$c['id'] : 'elms/courses') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0" style="color:#0f172a">
            <i class="fas fa-<?= $isEdit?'edit':'plus-circle' ?> me-2 text-primary"></i><?= $isEdit ? 'Edit Course' : 'Create New Course' ?>
        </h4>
        <?php if ($isEdit): ?>
        <div class="text-muted small"><?= e($c['title']) ?> &bull; <?= e($c['code'] ?? '') ?></div>
        <?php endif; ?>
    </div>
</div>

<form method="POST" action="<?= url($isEdit ? 'elms/courses/'.$c['id'].'/update' : 'elms/courses/store') ?>" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-3">

        <!-- LEFT: Main details -->
        <div class="col-12 col-lg-8">
            <!-- Basic Info -->
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Basic Information</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Course Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Complete Web Development Bootcamp"
                               value="<?= e($c['title'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Short Description</label>
                        <input type="text" name="short_description" class="form-control"
                               placeholder="One-line summary (shown in cards)" maxlength="500"
                               value="<?= e($c['short_description'] ?? '') ?>">
                        <div class="form-text">Max 500 characters</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Full Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Detailed course description, learning outcomes, prerequisites…"><?= e($c['description'] ?? '') ?></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold small">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">— None —</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($c['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold small">Level <span class="text-danger">*</span></label>
                            <select name="level" class="form-select" required>
                                <?php foreach (['all_levels'=>'All Levels','beginner'=>'Beginner','intermediate'=>'Intermediate','advanced'=>'Advanced'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= ($c['level'] ?? 'all_levels') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold small">Language</label>
                            <input type="text" name="language" class="form-control" value="<?= e($c['language'] ?? 'English') ?>" placeholder="English">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-semibold small">Duration (hours)</label>
                            <input type="number" name="duration_hours" class="form-control" step="0.5" min="0" value="<?= e($c['duration_hours'] ?? '') ?>" placeholder="e.g. 12.5">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-semibold small">Tags</label>
                        <input type="text" name="tags" class="form-control" value="<?= e($tagInput) ?>" placeholder="e.g. HTML, CSS, JavaScript (comma-separated)">
                        <div class="form-text">Separate tags with commas</div>
                    </div>
                </div>
            </div>

            <!-- Instructor -->
            <?php if (($lmsUser['role'] ?? '') === 'lms_admin'): ?>
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-chalkboard-teacher me-2"></i>Instructor</div>
                <div class="card-body">
                    <select name="instructor_id" class="form-select" required>
                        <option value="">— Select Instructor —</option>
                        <?php foreach ($instructors as $inst): ?>
                        <option value="<?= $inst['id'] ?>" <?= ($c['instructor_id'] ?? $lmsUser['id']) == $inst['id'] ? 'selected' : '' ?>>
                            <?= e($inst['name']) ?> <span class="text-muted">(<?= e($inst['email']) ?>)</span>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <!-- Schedule & Enrollment -->
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-calendar-alt me-2"></i>Schedule & Enrollment</div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= e($c['start_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= e($c['end_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Max Students</label>
                            <input type="number" name="max_students" class="form-control" min="1" value="<?= e($c['max_students'] ?? '') ?>" placeholder="Unlimited">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Pass % <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="pass_percentage" class="form-control" min="1" max="100" value="<?= e($c['pass_percentage'] ?? 60) ?>" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-4 flex-wrap">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="allow_self_enroll" id="chkSelfEnroll" <?= ($c['allow_self_enroll'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="chkSelfEnroll">Allow self-enrollment</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="certificate_enabled" id="chkCert" <?= !empty($c['certificate_enabled']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="chkCert">Enable certificate on completion</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Thumbnail + Publish -->
        <div class="col-12 col-lg-4">
            <!-- Thumbnail -->
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-image me-2"></i>Thumbnail</div>
                <div class="card-body">
                    <div class="lms-thumb-preview mb-2" id="thumbPreview" onclick="document.getElementById('thumbInput').click()">
                        <?php if (!empty($c['thumbnail'])): ?>
                        <img src="<?= asset($c['thumbnail']) ?>" id="thumbImg" alt="">
                        <?php else: ?>
                        <div class="text-center" id="thumbPlaceholder">
                            <i class="fas fa-image d-block mb-1"></i>
                            <span style="font-size:.72rem;color:#94a3b8">Click to upload</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="thumbnail" id="thumbInput" accept="image/*" class="d-none">
                    <div class="form-text text-center">JPG, PNG, WEBP — max 2MB<br>Recommended: 1280×720</div>
                </div>
            </div>

            <!-- Academic Link (optional) -->
            <?php if (!empty($subjects)): ?>
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>Academic Link <span class="badge bg-secondary ms-1" style="font-size:.6rem;font-weight:400">Optional</span></div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold small">Link to Academic Subject</label>
                        <select name="subject_id" class="form-select form-select-sm" id="subjectSelect" style="border-radius:8px">
                            <option value="">— Standalone Course —</option>
                            <?php foreach ($subjects as $sub): ?>
                            <option value="<?= $sub['id'] ?>" <?= ($c['subject_id'] ?? '') == $sub['id'] ? 'selected' : '' ?>>
                                <?= e($sub['subject_code']) ?> — <?= e($sub['subject_name']) ?>
                                <?php if ($sub['program_name']): ?> (<?= e($sub['program_name']) ?>)<?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Link this course to an academic subject to enable sync.</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Publish Settings -->
            <div class="lms-form-card">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Publish Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <?php foreach (['draft'=>'Draft','published'=>'Published','coming_soon'=>'Coming Soon','archived'=>'Archived'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($c['status'] ?? 'draft') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Visibility</label>
                        <select name="visibility" class="form-select">
                            <?php foreach (['enrolled'=>'Enrolled Only','public'=>'Public','private'=>'Private'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($c['visibility'] ?? 'enrolled') === $v ? 'selected' : '' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" style="border-radius:9px">
                            <i class="fas fa-save me-2"></i><?= $isEdit ? 'Save Changes' : 'Create Course' ?>
                        </button>
                        <a href="<?= url($isEdit ? 'elms/courses/'.$c['id'] : 'elms/courses') ?>" class="btn btn-outline-secondary" style="border-radius:9px">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('thumbInput')?.addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('thumbPreview');
        preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:8px">`;
    };
    reader.readAsDataURL(file);
});
</script>
