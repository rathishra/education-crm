<?php $pageTitle = 'Edit Faculty Profile: ' . e($user['first_name'].' '.$user['last_name']); ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-1"><i class="fas fa-user-edit me-2 text-primary"></i>Edit Faculty Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('faculty') ?>">Faculty</a></li>
                <li class="breadcrumb-item"><a href="<?= url("faculty/{$user['id']}") ?>"><?= e($user['first_name']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url("faculty/{$user['id']}") ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back to Profile
    </a>
</div>

<!-- Name badge -->
<div class="card border-0 shadow-sm mb-4 bg-light">
    <div class="card-body d-flex align-items-center gap-3">
        <?php
        $initials = strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1));
        $colors   = ['4f46e5','0891b2','059669','d97706','dc2626','7c3aed'];
        $color    = $colors[crc32($user['email']) % count($colors)];
        ?>
        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
             style="width:48px;height:48px;background:#<?= $color ?>;flex-shrink:0">
            <?= $initials ?>
        </div>
        <div>
            <h5 class="mb-0 fw-bold"><?= e($user['first_name'].' '.$user['last_name']) ?></h5>
            <div class="small text-muted"><?= e($user['email']) ?></div>
        </div>
    </div>
</div>

<form method="POST" action="<?= url("faculty/{$user['id']}/update") ?>" id="editForm">
    <?= csrfField() ?>

    <!-- ── Tab Navigation ────────────────────────────────────── -->
    <ul class="nav nav-tabs mb-0" id="editTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabPersonal"><i class="fas fa-user me-1"></i>Personal</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabEmployment"><i class="fas fa-briefcase me-1"></i>Employment</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAcademic"><i class="fas fa-graduation-cap me-1"></i>Academic</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBank"><i class="fas fa-university me-1"></i>Bank & Payroll</a></li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom shadow-sm bg-white mb-4 p-4">

        <!-- ── PERSONAL ───────────────────────────────────────── -->
        <div class="tab-pane fade show active" id="tabPersonal">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+91 00000 00000">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control"
                           value="<?= e($profile['emergency_contact_name'] ?? '') ?>" placeholder="e.g. Spouse name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Emergency Contact Phone</label>
                    <input type="tel" name="emergency_contact_phone" class="form-control"
                           value="<?= e($profile['emergency_contact_phone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Profile Photo URL</label>
                    <input type="url" name="profile_photo" class="form-control"
                           value="<?= e($profile['profile_photo'] ?? '') ?>" placeholder="https://…">
                </div>
                <div class="col-12">
                    <label class="form-label">Bio / About</label>
                    <textarea name="bio" class="form-control" rows="3"
                              placeholder="Brief professional bio…"><?= e($profile['bio'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ── EMPLOYMENT ─────────────────────────────────────── -->
        <div class="tab-pane fade" id="tabEmployment">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Employee ID</label>
                    <input type="text" name="employee_id" class="form-control"
                           value="<?= e($profile['employee_id'] ?? '') ?>" placeholder="e.g. FAC-001">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                    <select name="department_id" class="form-select" required>
                        <option value="">— Select Department —</option>
                        <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($profile['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                            <?= e($d['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
                    <input type="text" name="designation" class="form-control"
                           value="<?= e($profile['designation'] ?? '') ?>" required placeholder="e.g. Associate Professor">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control"
                           value="<?= e($profile['joining_date'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Total Experience (months)</label>
                    <input type="number" name="total_experience_months" class="form-control" min="0"
                           value="<?= (int)($profile['total_experience_months'] ?? 0) ?>">
                    <div class="form-text">
                        <?= $profile['total_experience_months'] ? round($profile['total_experience_months']/12,1).' years' : '' ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── ACADEMIC ───────────────────────────────────────── -->
        <div class="tab-pane fade" id="tabAcademic">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Highest Qualification</label>
                    <input type="text" name="qualification" class="form-control"
                           value="<?= e($profile['qualification'] ?? '') ?>" placeholder="e.g. Ph.D Computer Science">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Specialization / Subject Area</label>
                    <input type="text" name="specialization" class="form-control"
                           value="<?= e($profile['specialization'] ?? '') ?>" placeholder="e.g. Machine Learning, DBMS">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Publications Count</label>
                    <input type="number" name="publications_count" class="form-control" min="0"
                           value="<?= (int)($profile['publications_count'] ?? 0) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Certifications / Awards</label>
                    <textarea name="certifications" class="form-control" rows="3"
                              placeholder="List certifications or awards, one per line…"><?= e($profile['certifications'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ── BANK & PAYROLL ─────────────────────────────────── -->
        <div class="tab-pane fade" id="tabBank">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Annual Salary Package</label>
                    <div class="input-group">
                        <span class="input-group-text"><?= config('app.currency', '₹') ?></span>
                        <input type="number" step="0.01" name="salary_package" class="form-control"
                               value="<?= e($profile['salary_package'] ?? '') ?>" placeholder="e.g. 600000">
                        <span class="input-group-text">/ year</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control"
                           value="<?= e($profile['bank_name'] ?? '') ?>" placeholder="e.g. HDFC Bank">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account_number" class="form-control"
                           value="<?= e($profile['bank_account_number'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="bank_ifsc" class="form-control"
                           value="<?= e($profile['bank_ifsc'] ?? '') ?>" placeholder="e.g. HDFC0001234">
                </div>
            </div>
        </div>

    </div><!-- /tab-content -->

    <div class="d-flex justify-content-end gap-2">
        <a href="<?= url("faculty/{$user['id']}") ?>" class="btn btn-light px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-5">
            <i class="fas fa-save me-1"></i>Save Profile
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const key   = 'facultyEditTab';
    const saved = localStorage.getItem(key);
    if (saved) {
        const t = document.querySelector('[href="'+saved+'"]');
        if (t) bootstrap.Tab.getOrCreateInstance(t).show();
    }
    document.querySelectorAll('#editTabs .nav-link').forEach(tab => {
        tab.addEventListener('shown.bs.tab', e => localStorage.setItem(key, e.target.getAttribute('href')));
    });
    // Experience helper
    const expInput = document.querySelector('[name="total_experience_months"]');
    const expHint  = expInput?.nextElementSibling;
    if (expInput && expHint) {
        expInput.addEventListener('input', () => {
            const yrs = Math.round(expInput.value / 12 * 10) / 10;
            expHint.textContent = expInput.value > 0 ? yrs + ' years' : '';
        });
    }
});
</script>
