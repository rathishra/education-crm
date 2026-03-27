<?php $pageTitle = 'Add Subject'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Add Subject</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?= url('academic/subjects') ?>">Subjects</a></li>
            <li class="breadcrumb-item active">Add</li>
        </ol></nav>
    </div>
    <a href="<?= url('academic/subjects') ?>" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('academic/subjects/store') ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-book me-2 text-primary"></i>Subject Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" name="subject_code" class="form-control text-uppercase <?= isset($errors['subject_code'])?'is-invalid':'' ?>"
                                   value="<?= e(old('subject_code','')) ?>" placeholder="e.g. CS301" maxlength="50">
                            <?php if(isset($errors['subject_code'])): ?><div class="invalid-feedback"><?= e($errors['subject_code']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" name="subject_name" class="form-control <?= isset($errors['subject_name'])?'is-invalid':'' ?>"
                                   value="<?= e(old('subject_name','')) ?>" placeholder="e.g. Data Structures & Algorithms">
                            <?php if(isset($errors['subject_name'])): ?><div class="invalid-feedback"><?= e($errors['subject_name']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Short Name</label>
                            <input type="text" name="short_name" class="form-control" value="<?= e(old('short_name','')) ?>" placeholder="DSA" maxlength="50">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Subject Type <span class="text-danger">*</span></label>
                            <select name="subject_type" class="form-select">
                                <?php foreach(['theory','lab','tutorial','project','elective'] as $t): ?>
                                <option value="<?= $t ?>" <?= old('subject_type','theory')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Semester</label>
                            <select name="semester" class="form-select">
                                <option value="">— Select —</option>
                                <?php for($i=1;$i<=10;$i++): ?>
                                <option value="<?= $i ?>" <?= old('semester')==$i?'selected':'' ?>>Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department_id" class="form-select select2">
                                <option value="">— None —</option>
                                <?php foreach($departments as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= old('department_id')==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Course</label>
                            <select name="course_id" class="form-select select2">
                                <option value="">— None —</option>
                                <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= old('course_id')==$c['id']?'selected':'' ?>><?= e($c['name']) ?> (<?= e($c['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Regulation</label>
                            <input type="text" name="regulation" class="form-control" value="<?= e(old('regulation','')) ?>" placeholder="R2021">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control" value="<?= e(old('description','')) ?>" placeholder="Brief description…">
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_elective" id="isElective" value="1" <?= old('is_elective')?'checked':'' ?>>
                                <label class="form-check-label" for="isElective">Elective Subject</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="fas fa-clock me-2 text-warning"></i>Hours &amp; Credits</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Credits</label>
                        <input type="number" name="credits" class="form-control" value="<?= e(old('credits',3)) ?>" min="0" max="10" step="0.5">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hours/Week (Total)</label>
                        <input type="number" name="hours_per_week" class="form-control" value="<?= e(old('hours_per_week',3)) ?>" min="0" max="30">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Theory Hours</label>
                        <input type="number" name="theory_hours" class="form-control" value="<?= e(old('theory_hours',3)) ?>" min="0" max="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lab Hours</label>
                        <input type="number" name="lab_hours" class="form-control" value="<?= e(old('lab_hours',0)) ?>" min="0" max="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tutorial Hours</label>
                        <input type="number" name="tutorial_hours" class="form-control" value="<?= e(old('tutorial_hours',0)) ?>" min="0" max="10">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Save Subject</button>
            <a href="<?= url('academic/subjects') ?>" class="btn btn-light w-100 mt-2">Cancel</a>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }
    var codeInput = document.querySelector('[name=subject_code]');
    if (codeInput) codeInput.addEventListener('input', function() { this.value = this.value.toUpperCase(); });
});
</script>
