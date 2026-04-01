<?php $pageTitle = 'Upload Material'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Upload Study Material</h4>
        <p class="text-muted small mb-0">Share notes, videos, assignments and announcements with students.</p>
    </div>
    <a href="<?= url('academic/lms') ?>" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('academic/lms/store') ?>" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-file-upload me-2 text-primary"></i>Material Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control <?= isset($errors['title'])?'is-invalid':'' ?>"
                                   value="<?= e(old('title','')) ?>" placeholder="e.g. Unit 3 — Neural Networks Notes">
                            <?php if(isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select select2 <?= isset($errors['subject_id'])?'is-invalid':'' ?>" required>
                                <option value="">— Select Subject —</option>
                                <?php foreach($subjects as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= old('subject_id')==$s['id']?'selected':'' ?>><?= e($s['subject_code']) ?> — <?= e($s['subject_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if(isset($errors['subject_id'])): ?><div class="invalid-feedback"><?= e($errors['subject_id']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Batch <span class="text-muted small">(optional)</span></label>
                            <select name="batch_id" class="form-select select2">
                                <option value="">— All Batches —</option>
                                <?php foreach($batches as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= old('batch_id')==$b['id']?'selected':'' ?>><?= e($b['program_name']) ?> (<?= e($b['batch_term']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Material Type</label>
                            <select name="material_type" class="form-select" id="materialType">
                                <?php $selType = old('material_type','notes'); ?>
                                <?php foreach(['notes'=>'Notes / Handout','video'=>'Video','assignment'=>'Assignment','quiz'=>'Quiz','announcement'=>'Announcement','reference'=>'Reference / Link','lab_manual'=>'Lab Manual','other'=>'Other'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= $selType===$v?'selected':'' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Unit #</label>
                            <input type="number" name="unit_number" class="form-control" value="<?= e(old('unit_number','')) ?>" placeholder="1" min="1" max="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Publish Date</label>
                            <input type="date" name="publish_date" class="form-control" value="<?= e(old('publish_date',date('Y-m-d'))) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="What students will find in this material…"><?= e(old('description','')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tags <span class="text-muted small">(comma separated)</span></label>
                            <input type="text" name="tags" class="form-control" value="<?= e(old('tags','')) ?>" placeholder="machine-learning, neural-networks, deep-learning">
                        </div>
                    </div>
                </div>
            </div>

            <!-- File / Link Upload -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-paperclip me-2 text-success"></i>File or Link</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12" id="fileBlock">
                            <label class="form-label fw-semibold">Upload File <span class="text-muted small">(PDF, DOC, PPT, ZIP, images, video — max 50MB)</span></label>
                            <input type="file" name="file" class="form-control <?= isset($errors['file'])?'is-invalid':'' ?>">
                            <?php if(isset($errors['file'])): ?><div class="invalid-feedback"><?= e($errors['file']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6" id="videoBlock">
                            <label class="form-label fw-semibold">Video Link <span class="text-muted small">(YouTube, Google Drive…)</span></label>
                            <input type="url" name="video_link" class="form-control" value="<?= e(old('video_link','')) ?>" placeholder="https://youtube.com/…">
                        </div>
                        <div class="col-md-6" id="linkBlock">
                            <label class="form-label fw-semibold">External Link</label>
                            <input type="url" name="external_link" class="form-control" value="<?= e(old('external_link','')) ?>" placeholder="https://…">
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
                        <input type="date" name="due_date" class="form-control" value="<?= e(old('due_date','')) ?>">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_published" id="isPublished" value="1"
                               <?= old('is_published','1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isPublished">Publish Immediately</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-success w-100"><i class="fas fa-upload me-2"></i>Upload Material</button>
            <a href="<?= url('academic/lms') ?>" class="btn btn-light w-100 mt-2">Cancel</a>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }
});
</script>
