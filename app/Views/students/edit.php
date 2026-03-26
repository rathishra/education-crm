<?php $pageTitle = 'Edit Student - ' . e($student['student_id_number']); ?>
<div class="page-header">
    <div><h1><i class="fas fa-edit me-2"></i>Edit Student</h1></div>
    <a href="<?= url('students/'.$student['id']) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<form method="POST" action="<?= url('students/'.$student['id']) ?>">
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">Personal Information</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label required">First Name</label><input type="text" class="form-control" name="first_name" value="<?= e(old('first_name') ?: $student['first_name']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" class="form-control" name="last_name" value="<?= e(old('last_name') ?: ($student['last_name']??'')) ?>"></div>
                        <div class="col-md-6"><label class="form-label required">Phone</label><input type="text" class="form-control" name="phone" value="<?= e(old('phone') ?: $student['phone']) ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e(old('email') ?: ($student['email']??'')) ?>"></div>
                        <div class="col-md-3"><label class="form-label">DOB</label><input type="date" class="form-control" name="date_of_birth" value="<?= e($student['date_of_birth']??'') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Gender</label>
                            <select class="form-select" name="gender"><option value="">Select</option>
                                <?php foreach (['male','female','other'] as $g): ?><option value="<?= $g ?>" <?= ($student['gender']??'')===$g?'selected':'' ?>><?= ucfirst($g) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Category</label>
                            <select class="form-select" name="category"><option value="">Select</option>
                                <?php foreach (['general','obc','sc','st','ews'] as $c): ?><option value="<?= $c ?>" <?= ($student['category']??'')===$c?'selected':'' ?>><?= strtoupper($c) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach (['active','inactive','dropped','passed_out','suspended','transferred'] as $s): ?><option value="<?= $s ?>" <?= $student['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Father Name</label><input type="text" class="form-control" name="father_name" value="<?= e($student['father_name']??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Father Phone</label><input type="text" class="form-control" name="father_phone" value="<?= e($student['father_phone']??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Mother Name</label><input type="text" class="form-control" name="mother_name" value="<?= e($student['mother_name']??'') ?>"></div>
                        <div class="col-md-4"><label class="form-label">Address</label><input type="text" class="form-control" name="address_line1" value="<?= e($student['address_line1']??'') ?>" placeholder="Line 1"></div>
                        <div class="col-md-3"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="<?= e($student['city']??'') ?>"></div>
                        <div class="col-md-3"><label class="form-label">State</label><input type="text" class="form-control" name="state" value="<?= e($student['state']??'') ?>"></div>
                        <div class="col-md-2"><label class="form-label">Pincode</label><input type="text" class="form-control" name="pincode" value="<?= e($student['pincode']??'') ?>"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">Course Details</div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label">Course</label>
                        <select class="form-select select2" name="course_id">
                            <option value="">Select</option>
                            <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>" <?= ($student['course_id']??'')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Batch</label>
                        <select class="form-select" name="batch_id">
                            <option value="">Select</option>
                            <?php foreach ($batches as $b): ?><option value="<?= $b['id'] ?>" <?= ($student['batch_id']??'')==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Department</label>
                        <select class="form-select" name="department_id">
                            <option value="">Select</option>
                            <?php foreach ($departments as $d): ?><option value="<?= $d['id'] ?>" <?= ($student['department_id']??'')==$d['id']?'selected':'' ?>><?= e($d['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Previous Qualification</label><input type="text" class="form-control" name="previous_qualification" value="<?= e($student['previous_qualification']??'') ?>"></div>
                    <div class="mb-3"><label class="form-label">Percentage</label><input type="number" class="form-control" name="previous_percentage" value="<?= e($student['previous_percentage']??'') ?>" step="0.01"></div>
                </div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Update Student</button>
                <a href="<?= url('students/'.$student['id']) ?>" class="btn btn-light">Cancel</a>
            </div>
        </div>
    </div>
</form>
