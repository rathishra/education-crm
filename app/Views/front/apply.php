<?php
$pageTitle = $pageTitle ?? 'Apply for Admission';
$errors = getFlash('errors');
$flashError = getFlash('error');
$flashSuccess = getFlash('success');
?>
<div class="card shadow-sm">
    <div class="card-header text-center bg-white border-0">
        <h3 class="mb-1">Apply for Admission</h3>
        <p class="text-muted mb-0">Fill in your details and we will reach out.</p>
    </div>
    <div class="card-body">
        <?php if ($flashError): ?>
            <div class="alert alert-danger"><?= e($flashError) ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?= e($flashSuccess) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ((array)$errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('apply') ?>" novalidate>
            <?= csrfField() ?>

            <div class="mb-3">
                <label class="form-label required">Institution</label>
                <select name="institution_id" class="form-select" required>
                    <option value="">Select institution</option>
                    <?php foreach ($institutions as $inst): ?>
                        <option value="<?= $inst['id'] ?>" <?= old('institution_id') == $inst['id'] ? 'selected' : '' ?>>
                            <?= e($inst['name']) ?> (<?= e($inst['code']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label required">Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="">Select course</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= old('course_id') == $c['id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?> — <?= e($c['institution_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Academic Year</label>
                <select name="academic_year_id" class="form-select">
                    <option value="">Select academic year (optional)</option>
                    <?php foreach ($academicYears as $ay): ?>
                        <option value="<?= $ay['id'] ?>" <?= old('academic_year_id') == $ay['id'] ? 'selected' : '' ?>>
                            <?= e($ay['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label required">First Name</label>
                    <input type="text" name="first_name" class="form-control" value="<?= e(old('first_name')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" value="<?= e(old('last_name')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label required">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e(old('phone')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e(old('email')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="<?= e(old('date_of_birth')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <?php foreach (['male','female','other'] as $g): ?>
                            <option value="<?= $g ?>" <?= old('gender') === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">Select</option>
                        <?php foreach (['general','obc','sc','st','ews','other'] as $c): ?>
                            <option value="<?= $c ?>" <?= old('category') === $c ? 'selected' : '' ?>><?= strtoupper($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-8">
                    <label class="form-label">Address</label>
                    <input type="text" name="address_line1" class="form-control" placeholder="Address line" value="<?= e(old('address_line1')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="<?= e(old('city')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" value="<?= e(old('state')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pincode</label>
                    <input type="text" name="pincode" class="form-control" value="<?= e(old('pincode')) ?>">
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="form-label">Last Qualification</label>
                    <input type="text" name="previous_qualification" class="form-control" value="<?= e(old('previous_qualification')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">% / CGPA</label>
                    <input type="number" step="0.01" name="previous_percentage" class="form-control" value="<?= e(old('previous_percentage')) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Year</label>
                    <input type="number" name="previous_year_of_passing" class="form-control" value="<?= e(old('previous_year_of_passing')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Previous Institution</label>
                    <input type="text" name="previous_institution" class="form-control" value="<?= e(old('previous_institution')) ?>">
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Remarks</label>
                <textarea name="remarks" class="form-control" rows="3" placeholder="Any notes or questions"><?= e(old('remarks')) ?></textarea>
            </div>

            <div class="d-grid mt-4">
                <button class="btn btn-primary btn-lg" type="submit">
                    <i class="fas fa-paper-plane me-1"></i> Submit Application
                </button>
            </div>
        </form>
    </div>
</div>
