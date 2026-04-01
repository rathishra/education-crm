<?php $pageTitle = 'Add Faculty Member'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-1"><i class="fas fa-user-plus me-2 text-primary"></i>Add Faculty Member</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('faculty') ?>">Faculty</a></li>
                <li class="breadcrumb-item active">Add New</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('faculty') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back to Directory
    </a>
</div>

<form method="POST" action="<?= url('faculty') ?>" id="createFacultyForm">
    <?= csrfField() ?>

    <div class="row g-4">

        <!-- ── LEFT: Account Details ─────────────────────────────── -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom fw-semibold">
                    <i class="fas fa-user-circle me-2 text-primary"></i>Account Information
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control"
                                   value="<?= e(old('first_name')) ?>" required placeholder="e.g. Rajesh">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control"
                                   value="<?= e(old('last_name')) ?>" required placeholder="e.g. Kumar">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= e(old('email')) ?>" required placeholder="faculty@institution.edu">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?= e(old('phone')) ?>" placeholder="10-digit mobile">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control"
                                   value="<?= e(old('employee_id')) ?>" placeholder="e.g. FAC-001">
                            <div class="form-text">Must be unique across the system</div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordField"
                                       class="form-control" minlength="8" required placeholder="Min. 8 characters">
                                <button type="button" class="btn btn-outline-secondary" id="togglePwd" tabindex="-1">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="generatePwd" tabindex="-1" title="Generate password">
                                    <i class="fas fa-dice"></i>
                                </button>
                            </div>
                            <div id="pwdStrength" class="mt-1"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── RIGHT: Professional Details ──────────────────────── -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom fw-semibold">
                    <i class="fas fa-briefcase me-2 text-success"></i>Professional Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">— Select Department —</option>
                                <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
                            <input type="text" name="designation" class="form-control" required
                                   value="<?= e(old('designation')) ?>"
                                   placeholder="e.g. Assistant Professor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control"
                                   value="<?= e(old('joining_date', date('Y-m-d'))) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Experience (months)</label>
                            <input type="number" name="total_experience_months" class="form-control"
                                   min="0" value="<?= e(old('total_experience_months', 0)) ?>">
                            <div class="form-text" id="expHint"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Highest Qualification</label>
                            <input type="text" name="qualification" class="form-control"
                                   value="<?= e(old('qualification')) ?>"
                                   placeholder="e.g. M.Tech Computer Science, Ph.D">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Specialization</label>
                            <input type="text" name="specialization" class="form-control"
                                   value="<?= e(old('specialization')) ?>"
                                   placeholder="e.g. Machine Learning, Data Structures">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Annual Salary Package</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= config('app.currency', '₹') ?></span>
                                <input type="number" name="salary_package" class="form-control"
                                       step="1000" min="0" placeholder="e.g. 600000"
                                       value="<?= e(old('salary_package')) ?>">
                                <span class="input-group-text">/ year</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /row -->

    <!-- ── Submit ──────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="text-muted small">
                <i class="fas fa-info-circle me-1 text-primary"></i>
                The faculty member will be assigned the <strong>Faculty</strong> role for this institution.
                They can log in immediately after creation.
            </div>
            <div class="d-flex gap-2">
                <a href="<?= url('faculty') ?>" class="btn btn-light px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-5" id="submitBtn">
                    <i class="fas fa-user-plus me-2"></i>Create Faculty Member
                </button>
            </div>
        </div>
    </div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Toggle password visibility
    const pwdField = document.getElementById('passwordField');
    const eyeIcon  = document.getElementById('eyeIcon');
    document.getElementById('togglePwd').addEventListener('click', function () {
        const show = pwdField.type === 'password';
        pwdField.type = show ? 'text' : 'password';
        eyeIcon.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    // ── Generate random password
    document.getElementById('generatePwd').addEventListener('click', function () {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#$!';
        let pwd = '';
        for (let i = 0; i < 12; i++) pwd += chars[Math.floor(Math.random() * chars.length)];
        pwdField.value = pwd;
        pwdField.type = 'text';
        eyeIcon.className = 'fas fa-eye-slash';
        checkStrength(pwd);
    });

    // ── Password strength indicator
    function checkStrength(val) {
        const bar = document.getElementById('pwdStrength');
        if (!val) { bar.innerHTML = ''; return; }
        let score = 0;
        if (val.length >= 8)  score++;
        if (val.length >= 12) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const levels = [
            [1, 'danger',  'Weak'],
            [2, 'warning', 'Fair'],
            [3, 'info',    'Good'],
            [4, 'primary', 'Strong'],
            [5, 'success', 'Very Strong'],
        ];
        const [, cls, label] = levels[Math.min(score, 5) - 1] || [0, 'secondary', ''];
        bar.innerHTML = `<div class="d-flex align-items-center gap-2 mt-1">
            <div class="progress flex-grow-1" style="height:4px">
                <div class="progress-bar bg-${cls}" style="width:${score*20}%"></div>
            </div>
            <span class="text-${cls}" style="font-size:.72rem;white-space:nowrap">${label}</span>
        </div>`;
    }
    pwdField.addEventListener('input', () => checkStrength(pwdField.value));

    // ── Experience → years hint
    const expInput = document.querySelector('[name="total_experience_months"]');
    const expHint  = document.getElementById('expHint');
    expInput.addEventListener('input', function () {
        const yrs = (this.value / 12).toFixed(1);
        expHint.textContent = this.value > 0 ? `= ${yrs} years` : '';
    });

    // ── Auto-generate email suggestion
    const fnField = document.querySelector('[name="first_name"]');
    const lnField = document.querySelector('[name="last_name"]');
    const emField = document.querySelector('[name="email"]');
    function suggestEmail() {
        if (emField.value) return;
        const fn = fnField.value.toLowerCase().replace(/\s+/g,'');
        const ln = lnField.value.toLowerCase().replace(/\s+/g,'');
        if (fn && ln) emField.placeholder = `${fn}.${ln}@institution.edu`;
    }
    fnField.addEventListener('blur', suggestEmail);
    lnField.addEventListener('blur', suggestEmail);

    // ── Prevent double submit
    document.getElementById('createFacultyForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating…';
    });

});
</script>
