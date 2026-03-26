<?php $pageTitle = 'Add Student'; ?>
<div class="page-header">
    <div><h1><i class="fas fa-user-plus me-2"></i>Add Student</h1></div>
    <a href="<?= url('students') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<form method="POST" action="<?= url('students') ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Personal Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label required">First Name</label><input type="text" class="form-control" name="first_name" value="<?= e(old('first_name')??'') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" value="<?= e(old('last_name')??'') ?>"></div>
                        <div class="col-md-6"><label class="form-label required">Phone</label><input type="text" class="form-control" name="phone" value="<?= e(old('phone')??'') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e(old('email')??'') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Date of Birth</label><input type="date" class="form-control" name="date_of_birth" value="<?= e(old('date_of_birth')??'') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Gender</label>
                            <select class="form-select" name="gender"><option value="">Select</option>
                                <?php foreach (['male','female','other'] as $g): ?><option value="<?= $g ?>"><?= ucfirst($g) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Category</label>
                            <select class="form-select" name="category"><option value="">Select</option>
                                <?php foreach (['general','obc','sc','st','ews'] as $c): ?><option value="<?= $c ?>"><?= strtoupper($c) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Aadhar No.</label><input type="text" class="form-control" name="aadhar_number" value="<?= e(old('aadhar_number')??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Father Name</label><input type="text" class="form-control" name="father_name" value="<?= e(old('father_name')??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Father Phone</label><input type="text" class="form-control" name="father_phone" value="<?= e(old('father_phone')??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Mother Name</label><input type="text" class="form-control" name="mother_name" value="<?= e(old('mother_name')??'') ?>"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">Course & Academic</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label required">Course</label>
                        <select class="form-select select2" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Department</label>
                        <select class="form-select" name="department_id">
                            <option value="">Select</option>
                            <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Admission Date</label><input type="date" class="form-control" name="admission_date" value="<?= date('Y-m-d') ?>"></div>
                    <div class="mb-3"><label class="form-label">Admission Type</label>
                        <select class="form-select" name="admission_type">
                            <?php foreach (['regular'=>'Regular','lateral'=>'Lateral','management'=>'Management'] as $v=>$l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-1"></i>Add Student</button>
                <a href="<?= url('students') ?>" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>
