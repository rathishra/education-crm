<?php $pageTitle = 'Edit Material'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="fas fa-edit me-2 text-warning"></i>Edit Study Material</h4>
        <p class="text-muted small mb-0">Update the details for <strong><?= e($material['title']) ?></strong></p>
    </div>
    <a href="<?= url('academic/lms') ?>" class="btn btn-light btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to LMS</a>
</div>

<form method="POST" action="<?= url('academic/lms/'.$material['id'].'/update') ?>" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-file-alt me-2 text-primary"></i>Material Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                   value="<?= e($material['title']) ?>" required placeholder="e.g. Unit 3 — Notes">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select select2" required>
                                <option value="">— Select Subject —</option>
                                <?php foreach ($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= $material['subject_id'] == $s['id'] ? 'selected' : '' ?>>
                                    <?= e($s['subject_code']) ?> — <?= e($s['subject_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Batch <span class="text-muted small">(optional)</span></label>
                            <select name="batch_id" class="form-select select2">
                                <option value="">— All Batches —</option>
                                <?php foreach ($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $material['batch_id'] == $b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Material Type</label>
                            <select name="material_type" class="form-select">
                                <?php foreach (['notes' => 'Notes / Handout', 'video' => 'Video', 'assignment' => 'Assignment', 'quiz' => 'Quiz', 'announcement' => 'Announcement', 'reference' => 'Reference / Link', 'lab_manual' => 'Lab Manual', 'other' => 'Other'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= $material['material_type'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Unit #</label>
                            <input type="number" name="unit_number" class="form-control"
                                   value="<?= $material['unit_number'] ?>" min="1" max="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Publish Date</label>
                            <input type="date" name="publish_date" class="form-control"
                                   value="<?= $material['publish_date'] ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= e($material['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tags <span class="text-muted small">(comma separated)</span></label>
                            <input type="text" name="tags" class="form-control" value="<?= e($material['tags'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- File / Link Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-paperclip me-2 text-success"></i>File or Link</div>
                <div class="card-body">
                    <?php if ($material['file_path'] ?? ''): ?>
                    <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3">
                        <i class="fas fa-file text-info"></i>
                        <div>
                            Current file: <strong><?= e($material['original_filename']) ?></strong>
                            (<?= $material['file_size'] ? number_format($material['file_size'] / 1024, 1) . ' KB' : '' ?>)
                            — Upload a new file below to replace it.
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <?= ($material['file_path'] ?? '') ? 'Replace File' : 'Upload File' ?>
                                <span class="text-muted small">(PDF, DOC, PPT, ZIP, images, video — max 50MB)</span>
                            </label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Video Link</label>
                            <input type="url" name="video_link" class="form-control"
                                   value="<?= e($material['video_link'] ?? '') ?>" placeholder="https://youtube.com/…">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">External Link</label>
                            <input type="url" name="external_link" class="form-control"
                                   value="<?= e($material['external_link'] ?? '') ?>" placeholder="https://…">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-cog me-2 text-secondary"></i>Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Due Date <span class="text-muted small">(assignments only)</span></label>
                        <input type="date" name="due_date" class="form-control" value="<?= $material['due_date'] ?? '' ?>">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" value="1"
                               <?= $material['is_published'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isPublished">Published</label>
                    </div>
                </div>
            </div>

            <!-- Download stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="text-muted small mb-1">Downloads</div>
                    <div class="fs-2 fw-bold text-primary"><?= (int)($material['download_count'] ?? 0) ?></div>
                </div>
            </div>

            <button type="submit" class="btn btn-warning w-100 fw-semibold">
                <i class="fas fa-save me-2"></i>Update Material
            </button>
            <a href="<?= url('academic/lms') ?>" class="btn btn-light w-100 mt-2">Cancel</a>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }
});
</script>
