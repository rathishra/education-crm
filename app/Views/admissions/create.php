<?php $pageTitle = 'New Admission Application'; ?>
<div class="page-header">
    <div><h1><i class="fas fa-user-plus me-2"></i>New Admission Application</h1></div>
    <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="<?= url('admissions') ?>">
    <?= csrfField() ?>
    <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= $leadId ?>"><?php endif; ?>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-user me-2"></i>Personal Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label required">First Name</label><input type="text" class="form-control" name="first_name" value="<?= e(old('first_name') ?: ($prefill['first_name']??'')) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" value="<?= e(old('last_name') ?: ($prefill['last_name']??'')) ?>"></div>
                        <div class="col-md-6"><label class="form-label required">Phone</label><input type="text" class="form-control" name="phone" value="<?= e(old('phone') ?: ($prefill['phone']??'')) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e(old('email') ?: ($prefill['email']??'')) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Date of Birth</label><input type="date" class="form-control" name="date_of_birth" value="<?= e(old('date_of_birth') ?: ($prefill['date_of_birth']??'')) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">Select</option>
                                <?php foreach (['male','female','other'] as $g): ?><option value="<?= $g ?>" <?= (old('gender')?:($prefill['gender']??''))===$g?'selected':'' ?>><?= ucfirst($g) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="">Select</option>
                                <?php foreach (['general','obc','sc','st','ews','other'] as $cat): ?><option value="<?= $cat ?>" <?= (old('category')??'')===$cat?'selected':'' ?>><?= strtoupper($cat) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Nationality</label><input type="text" class="form-control" name="nationality" value="<?= e(old('nationality') ?: 'Indian') ?>"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-users me-2"></i>Guardian / Family Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Father Name</label><input type="text" class="form-control" name="father_name" value="<?= e(old('father_name')??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Father Phone</label><input type="text" class="form-control" name="father_phone" value="<?= e(old('father_phone')??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Mother Name</label><input type="text" class="form-control" name="mother_name" value="<?= e(old('mother_name')??'') ?>"></div>
                        <div class="col-md-6"><label class="form-label">Guardian Name</label><input type="text" class="form-control" name="guardian_name" value="<?= e(old('guardian_name') ?: ($prefill['guardian_name']??'')) ?>"></div>
                        <div class="col-md-6"><label class="form-label">Guardian Phone</label><input type="text" class="form-control" name="guardian_phone" value="<?= e(old('guardian_phone') ?: ($prefill['guardian_phone']??'')) ?>"></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>Previous Education</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">Last Qualification</label><input type="text" class="form-control" name="previous_qualification" value="<?= e(old('previous_qualification') ?: ($prefill['previous_qualification']??'')) ?>"></div>
                        <div class="col-md-4"><label class="form-label">Institution</label><input type="text" class="form-control" name="previous_institution" value="<?= e(old('previous_institution')??'') ?>"></div>
                        <div class="col-md-2"><label class="form-label">%/CGPA</label><input type="number" class="form-control" name="previous_percentage" value="<?= e(old('previous_percentage') ?: ($prefill['previous_percentage']??'')) ?>" step="0.01"></div>
                        <div class="col-md-2"><label class="form-label">Year</label><input type="number" class="form-control" name="previous_year_of_passing" value="<?= e(old('previous_year_of_passing')??'') ?>"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-cog me-2"></i>Admission Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label required">Course</label>
                        <select class="form-select select2" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>" <?= (old('course_id')?:($prefill['course_id']??''))==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <select class="form-select" name="academic_year_id">
                            <option value="">Select Year</option>
                            <?php foreach ($academicYears as $ay): ?><option value="<?= $ay['id'] ?>"><?= e($ay['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admission Type</label>
                        <select class="form-select" name="admission_type">
                            <?php foreach (['regular'=>'Regular','lateral'=>'Lateral','management'=>'Management','nri'=>'NRI'] as $v=>$l): ?>
                            <option value="<?= $v ?>"><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control mb-2" name="address_line1" placeholder="Address Line 1" value="<?= e(old('address_line1') ?: ($prefill['address_line1']??'')) ?>">
                        <div class="row g-2">
                            <div class="col-6"><input type="text" class="form-control" name="city" placeholder="City" value="<?= e(old('city') ?: ($prefill['city']??'')) ?>"></div>
                            <div class="col-6"><input type="text" class="form-control" name="state" placeholder="State" value="<?= e(old('state') ?: ($prefill['state']??'')) ?>"></div>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="3"><?= e(old('remarks')??'') ?></textarea></div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Submit Application</button>
                <a href="<?= url('admissions') ?>" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>
