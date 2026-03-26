<?php $pageTitle = 'Edit HR Profile: ' . e($user['first_name'] . ' ' . $user['last_name']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user-edit me-2"></i>Edit HR Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('hr/staff') ?>">Staff</a></li>
                <li class="breadcrumb-item active"><?= e($user['first_name']) ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('hr/staff') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<div class="card mb-4 bg-light">
    <div class="card-body">
        <h5 class="card-title fw-bold text-primary mb-1"><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h5>
        <div class="text-muted small"><i class="fas fa-envelope me-1"></i><?= e($user['email']) ?></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="<?= url("hr/staff/{$user['id']}/edit") ?>">
            <?= csrfField() ?>

            <div class="row g-4">
                <h5 class="mb-0 text-secondary border-bottom pb-2"><i class="fas fa-briefcase me-2"></i>Employment Details</h5>
                
                <div class="col-md-6">
                    <label class="form-label required">Department</label>
                    <select class="form-select" name="department_id" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= ($profile['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Designation / Job Title</label>
                    <input type="text" class="form-control" name="designation" value="<?= e($profile['designation'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Joining Date</label>
                    <input type="date" class="form-control" name="joining_date" value="<?= e($profile['joining_date'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Highest Qualification</label>
                    <input type="text" class="form-control" name="qualification" value="<?= e($profile['qualification'] ?? '') ?>" placeholder="e.g. Ph.D, M.Tech">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Total Experience (Months)</label>
                    <input type="number" class="form-control" name="total_experience_months" value="<?= e($profile['total_experience_months'] ?? '0') ?>">
                </div>

                <h5 class="mb-0 mt-5 text-secondary border-bottom pb-2"><i class="fas fa-money-check-alt me-2"></i>Payroll & Bank Details</h5>

                <div class="col-md-12 mb-2">
                    <label class="form-label required">Annual Salary Package</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= config('app.currency', '$') ?></span>
                        <input type="number" step="0.01" class="form-control" name="salary_package" value="<?= e($profile['salary_package'] ?? '') ?>" required placeholder="e.g. 600000">
                        <span class="input-group-text">/ year</span>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bank Name</label>
                    <input type="text" class="form-control" name="bank_name" value="<?= e($profile['bank_name'] ?? '') ?>" placeholder="e.g. HDFC Bank">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bank Account Number</label>
                    <input type="text" class="form-control" name="bank_account_number" value="<?= e($profile['bank_account_number'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" class="form-control" name="bank_ifsc" value="<?= e($profile['bank_ifsc'] ?? $profile['ifsc_code'] ?? '') ?>">
                </div>

                <div class="col-12 mt-4 text-end">
                    <a href="<?= url('hr/staff') ?>" class="btn btn-light me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>
