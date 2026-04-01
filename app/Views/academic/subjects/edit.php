<?php $pageTitle = 'Edit Subject'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Edit Subject</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="<?= url('academic/subjects') ?>">Subjects</a></li>
            <li class="breadcrumb-item"><a href="<?= url('academic/subjects/'.$subject['id']) ?>"><?= e($subject['subject_code']) ?></a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol></nav>
    </div>
    <a href="<?= url('academic/subjects/'.$subject['id']) ?>" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('academic/subjects/update/'.$subject['id']) ?>">
    <?= csrfField() ?>
    <div class="row g-4">

        <!-- ── LEFT MAIN ── -->
        <div class="col-lg-8">

            <!-- Section 1: Core Identity -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-book me-2 text-primary"></i>Core Identity</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" name="subject_code" class="form-control text-uppercase <?= isset($errors['subject_code'])?'is-invalid':'' ?>"
                                   value="<?= e(old('subject_code', $subject['subject_code'])) ?>" maxlength="50">
                            <?php if(isset($errors['subject_code'])): ?><div class="invalid-feedback"><?= e($errors['subject_code']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" name="subject_name" class="form-control <?= isset($errors['subject_name'])?'is-invalid':'' ?>"
                                   value="<?= e(old('subject_name', $subject['subject_name'])) ?>">
                            <?php if(isset($errors['subject_name'])): ?><div class="invalid-feedback"><?= e($errors['subject_name']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Short Name</label>
                            <input type="text" name="short_name" class="form-control" value="<?= e(old('short_name', $subject['short_name']??'')) ?>" maxlength="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Short Label</label>
                            <input type="text" name="short_label" class="form-control" value="<?= e(old('short_label', $subject['short_label']??'')) ?>" maxlength="100" placeholder="e.g. CORE-CS">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Subject Type <span class="text-danger">*</span></label>
                            <select name="subject_type" class="form-select" id="subjectType">
                                <?php foreach(['theory'=>'Theory','lab'=>'Lab','tutorial'=>'Tutorial','project'=>'Project','elective'=>'Elective'] as $v=>$l): ?>
                                <option value="<?= $v ?>" <?= old('subject_type',$subject['subject_type'])===$v?'selected':'' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Semester</label>
                            <select name="semester" class="form-select">
                                <option value="">— Not Set —</option>
                                <?php $selSem = old('semester',$subject['semester']); for($i=1;$i<=10;$i++): ?>
                                <option value="<?= $i ?>" <?= $selSem==$i?'selected':'' ?>>Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Regulation</label>
                            <input type="text" name="regulation" class="form-control" value="<?= e(old('regulation', $subject['regulation']??'')) ?>" placeholder="R2021">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active"   <?= old('status',$subject['status'])==='active'  ?'selected':'' ?>>Active</option>
                                <option value="inactive" <?= old('status',$subject['status'])==='inactive'?'selected':'' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department_id" class="form-select select2">
                                <option value="">— None —</option>
                                <?php foreach($departments as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= old('department_id',$subject['department_id'])==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Course</label>
                            <select name="course_id" class="form-select select2">
                                <option value="">— None —</option>
                                <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= old('course_id',$subject['course_id'])==$c['id']?'selected':'' ?>><?= e($c['name']) ?> (<?= e($c['code']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="2"><?= e(old('description', $subject['description']??'')) ?></textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Syllabus URL</label>
                            <input type="url" name="syllabus_url" class="form-control" value="<?= e(old('syllabus_url', $subject['syllabus_url']??'')) ?>" placeholder="https://…">
                        </div>
                        <div class="col-md-4 d-flex align-items-center mt-md-4">
                            <div class="form-check form-switch me-3">
                                <input class="form-check-input" type="checkbox" name="is_elective" id="isElective" value="1"
                                       <?= old('is_elective',$subject['is_elective'])?'checked':'' ?>>
                                <label class="form-check-label" for="isElective">Elective</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="affects_gpa" id="affectsGpa" value="1"
                                       <?= old('affects_gpa',$subject['affects_gpa']??0)?'checked':'' ?>>
                                <label class="form-check-label" for="affectsGpa">Affects GPA</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Curriculum & Governance -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-sitemap me-2 text-info"></i>Curriculum &amp; Governance</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Delivery Mode</label>
                            <select name="delivery_mode" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach(['Classroom','Online','Hybrid','Self-paced','Workshop'] as $m): ?>
                                <option value="<?= $m ?>" <?= old('delivery_mode',$subject['delivery_mode']??'')===$m?'selected':'' ?>><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Priority Level</label>
                            <select name="priority_level" class="form-select">
                                <option value="">— Select —</option>
                                <?php foreach(['Core','Mandatory','Elective','Open Elective','Professional'] as $p): ?>
                                <option value="<?= $p ?>" <?= old('priority_level',$subject['priority_level']??'')===$p?'selected':'' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Curriculum Stream</label>
                            <input type="text" name="curriculum_stream" class="form-control" value="<?= e(old('curriculum_stream', $subject['curriculum_stream']??'')) ?>" placeholder="e.g. CS, Math, Humanities">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Architecture</label>
                            <input type="text" name="architecture" class="form-control" value="<?= e(old('architecture', $subject['architecture']??'')) ?>" placeholder="e.g. Semester-based">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Governing Body</label>
                            <input type="text" name="governing_body" class="form-control" value="<?= e(old('governing_body', $subject['governing_body']??'')) ?>" placeholder="e.g. AICTE, UGC">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Review Authority</label>
                            <input type="text" name="review_authority" class="form-control" value="<?= e(old('review_authority', $subject['review_authority']??'')) ?>" placeholder="e.g. Academic Council">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Grading Scale</label>
                            <input type="text" name="grading_scale" class="form-control" value="<?= e(old('grading_scale', $subject['grading_scale']??'')) ?>" placeholder="e.g. 10-point, Letter Grade">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">External Exam Code</label>
                            <input type="text" name="external_exam_code" class="form-control" value="<?= e(old('external_exam_code', $subject['external_exam_code']??'')) ?>" placeholder="e.g. MAT-301-EXT">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold d-block">Flags</label>
                            <div class="d-flex flex-column gap-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_sub_module" value="1"
                                           <?= old('is_sub_module',$subject['is_sub_module']??0)?'checked':'' ?> id="isSubModule">
                                    <label class="form-check-label small" for="isSubModule">Sub-module</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="attach_syllabus" value="1"
                                           <?= old('attach_syllabus',$subject['attach_syllabus']??0)?'checked':'' ?> id="attachSyllabus">
                                    <label class="form-check-label small" for="attachSyllabus">Attach Syllabus</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="track_sessions" value="1"
                                           <?= old('track_sessions',$subject['track_sessions']??0)?'checked':'' ?> id="trackSessions">
                                    <label class="form-check-label small" for="trackSessions">Track Sessions</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold d-block">Language Flags</label>
                            <div class="d-flex flex-column gap-1">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="local_language" value="1"
                                           <?= old('local_language',$subject['local_language']??0)?'checked':'' ?> id="localLang">
                                    <label class="form-check-label small" for="localLang">Local Language</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="secondary_language" value="1"
                                           <?= old('secondary_language',$subject['secondary_language']??0)?'checked':'' ?> id="secLang">
                                    <label class="form-check-label small" for="secLang">Secondary Language</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Valid From</label>
                            <input type="date" name="valid_from" class="form-control" value="<?= e(old('valid_from', $subject['valid_from']??'')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control" value="<?= e(old('valid_until', $subject['valid_until']??'')) ?>">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── RIGHT SIDEBAR ── -->
        <div class="col-lg-4">

            <!-- Hours & Credits -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-clock me-2 text-warning"></i>Hours &amp; Credits</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Credits</label>
                        <input type="number" name="credits" id="creditsInput" class="form-control"
                               value="<?= e(old('credits', $subject['credits'])) ?>" min="0" max="10" step="0.5">
                    </div>
                    <hr class="my-2">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Theory Hours/Wk</label>
                        <input type="number" name="theory_hours" id="theoryH" class="form-control hrs-input"
                               value="<?= e(old('theory_hours', $subject['theory_hours']??3)) ?>" min="0" max="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lab Hours/Wk</label>
                        <input type="number" name="lab_hours" id="labH" class="form-control hrs-input"
                               value="<?= e(old('lab_hours', $subject['lab_hours']??0)) ?>" min="0" max="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tutorial Hours/Wk</label>
                        <input type="number" name="tutorial_hours" id="tutH" class="form-control hrs-input"
                               value="<?= e(old('tutorial_hours', $subject['tutorial_hours']??0)) ?>" min="0" max="10">
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center p-2 bg-primary-subtle rounded-2">
                        <span class="fw-semibold small text-primary">Total Hours/Week</span>
                        <span class="fw-bold text-primary fs-5" id="totalHours">
                            <?= (int)($subject['theory_hours']??3)+(int)($subject['lab_hours']??0)+(int)($subject['tutorial_hours']??0) ?>
                        </span>
                    </div>
                    <input type="hidden" name="hours_per_week" id="hoursPerWeek" value="<?= e(old('hours_per_week', $subject['hours_per_week'])) ?>">
                </div>
            </div>

            <!-- Credit Preview -->
            <div class="card border-0 shadow-sm mb-4 bg-gradient" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)!important">
                <div class="card-body text-white text-center py-4">
                    <div class="fs-1 fw-bold" id="creditPreview"><?= e(old('credits', $subject['credits'])) ?></div>
                    <div class="opacity-75 mb-1">Credits</div>
                    <div class="small opacity-50" id="typePreview"><?= ucfirst(old('subject_type', $subject['subject_type'])) ?> Subject</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold"><i class="fas fa-save me-2"></i>Update Subject</button>
            <a href="<?= url('academic/subjects/'.$subject['id']) ?>" class="btn btn-light border w-100 mt-2">Cancel</a>
        </div>

    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // Auto-uppercase code
    var codeEl = document.querySelector('[name=subject_code]');
    if (codeEl) codeEl.addEventListener('input', function(){ this.value = this.value.toUpperCase(); });

    // Auto-total hours
    function recalcHours() {
        var t = parseInt(document.getElementById('theoryH').value)||0;
        var l = parseInt(document.getElementById('labH').value)||0;
        var u = parseInt(document.getElementById('tutH').value)||0;
        var total = t+l+u;
        document.getElementById('totalHours').textContent = total;
        document.getElementById('hoursPerWeek').value = total;
    }
    document.querySelectorAll('.hrs-input').forEach(function(el){
        el.addEventListener('input', recalcHours);
    });

    // Live credit preview
    var credEl = document.getElementById('creditsInput');
    if (credEl) credEl.addEventListener('input', function(){
        document.getElementById('creditPreview').textContent = parseFloat(this.value||0).toFixed(1);
    });

    // Live type preview
    var typeEl = document.getElementById('subjectType');
    if (typeEl) typeEl.addEventListener('change', function(){
        var t = this.options[this.selectedIndex].text;
        document.getElementById('typePreview').textContent = t + ' Subject';
    });

    recalcHours();
});
</script>
