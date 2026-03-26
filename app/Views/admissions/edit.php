<?php $pageTitle = 'Edit Admission - ' . e($admission['admission_number']); ?>
<div class="page-header">
    <div><h1><i class="fas fa-edit me-2"></i>Edit Admission</h1></div>
    <a href="<?= url('admissions/'.$admission['id']) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<form method="POST" action="<?= url('admissions/'.$admission['id']) ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Personal Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label required">First Name</label><input type="text" class="form-control" name="first_name" value="<?= e(old('first_name') ?: $admission['first_name']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" value="<?= e(old('last_name') ?: ($admission['last_name']??'')) ?>"></div>
                        <div class="col-md-6"><label class="form-label required">Phone</label><input type="text" class="form-control" name="phone" value="<?= e(old('phone') ?: $admission['phone']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e(old('email') ?: ($admission['email']??'')) ?>"></div>
                        <div class="col-md-3"><label class="form-label">DOB</label><input type="date" class="form-control" name="date_of_birth" value="<?= e(old('date_of_birth') ?: ($admission['date_of_birth']??'')) ?>"></div>
                        <div class="col-md-3"><label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">Select</option>
                                <?php foreach (['male','female','other'] as $g): ?><option value="<?= $g ?>" <?= ($admission['gender']??'')===$g?'selected':'' ?>><?= ucfirst($g) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="">Select</option>
                                <?php foreach (['general','obc','sc','st','ews','other'] as $cat): ?><option value="<?= $cat ?>" <?= ($admission['category']??'')===$cat?'selected':'' ?>><?= strtoupper($cat) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Father Name</label><input type="text" class="form-control" name="father_name" value="<?= e($admission['father_name']??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Father Phone</label><input type="text" class="form-control" name="father_phone" value="<?= e($admission['father_phone']??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Mother Name</label><input type="text" class="form-control" name="mother_name" value="<?= e($admission['mother_name']??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Qualification</label><input type="text" class="form-control" name="previous_qualification" value="<?= e($admission['previous_qualification']??'') ?>"></div>
                        <div class="col-md-2"><label class="form-label">%</label><input type="number" class="form-control" name="previous_percentage" value="<?= e($admission['previous_percentage']??'') ?>" step="0.01"></div>
                        <div class="col-md-6"><label class="form-label">Previous Institution</label><input type="text" class="form-control" name="previous_institution" value="<?= e($admission['previous_institution']??'') ?>"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">Course & Admission</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label required">Course</label>
                        <select class="form-select select2" name="course_id" required>
                            <option value="">Select</option>
                            <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>" <?= $admission['course_id']==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Batch</label>
                        <select class="form-select" name="batch_id">
                            <option value="">Select Batch</option>
                            <?php foreach ($batches as $b): ?><option value="<?= $b['id'] ?>" <?= ($admission['batch_id']??'')==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Academic Year</label>
                        <select class="form-select" name="academic_year_id">
                            <option value="">Select</option>
                            <?php foreach ($academicYears as $ay): ?><option value="<?= $ay['id'] ?>" <?= ($admission['academic_year_id']??'')==$ay['id']?'selected':'' ?>><?= e($ay['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Admission Type</label>
                        <select class="form-select" name="admission_type">
                            <?php foreach (['regular'=>'Regular','lateral'=>'Lateral','management'=>'Management','nri'=>'NRI'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= ($admission['admission_type']??'regular')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks" rows="3"><?= e($admission['remarks']??'') ?></textarea></div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Update Admission</button>
                <a href="<?= url('admissions/'.$admission['id']) ?>" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>
