<?php
$fullName  = e(trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')));
$initials  = strtoupper(substr($profile['first_name'] ?? 'S', 0, 1) . substr($profile['last_name'] ?? '', 0, 1));
$statusColors = ['active' => 'success', 'inactive' => 'secondary', 'alumni' => 'info', 'suspended' => 'danger', 'dropout' => 'danger'];
$statusColor  = $statusColors[$profile['status'] ?? 'active'] ?? 'secondary';
?>

<div class="portal-page-header">
    <h1 class="portal-page-title"><i class="fas fa-user-circle me-2 text-success"></i>My Profile</h1>
    <div class="portal-breadcrumb"><a href="<?= url('portal/student/dashboard') ?>">Dashboard</a> &rsaquo; My Profile</div>
</div>

<div class="row g-3">
    <!-- Profile Card -->
    <div class="col-lg-4">
        <div class="portal-card p-4 text-center mb-3">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                 style="width:80px;height:80px;font-size:1.6rem;background:linear-gradient(135deg,#059669,#10b981)">
                <?= $initials ?>
            </div>
            <div class="fw-bold fs-5 mb-1"><?= $fullName ?></div>
            <div class="text-muted small mb-2"><?= e($profile['student_id_number'] ?? '') ?></div>
            <span class="badge bg-<?= $statusColor ?>-subtle text-<?= $statusColor ?> border px-3 py-1 mb-3"><?= ucfirst($profile['status'] ?? 'active') ?></span>

            <?php if (!empty($profile['email'])): ?>
            <div class="text-muted small mb-1"><i class="fas fa-envelope me-2"></i><?= e($profile['email']) ?></div>
            <?php endif; ?>
            <?php $phone = $profile['phone'] ?? $profile['mobile_number'] ?? ''; if ($phone): ?>
            <div class="text-muted small mb-1"><i class="fas fa-phone me-2"></i><?= e($phone) ?></div>
            <?php endif; ?>
            <?php if (!empty($profile['date_of_birth'])): ?>
            <div class="text-muted small mb-1"><i class="fas fa-birthday-cake me-2"></i><?= date('d M Y', strtotime($profile['date_of_birth'])) ?></div>
            <?php endif; ?>
        </div>

        <!-- Academic Info -->
        <div class="portal-card p-3 mb-3">
            <div class="fw-semibold mb-2 pb-1 border-bottom" style="font-size:0.85rem;color:#065f46"><i class="fas fa-graduation-cap me-2"></i>Academic Details</div>
            <table class="table table-sm mb-0 portal-table" style="font-size:0.82rem">
                <tr><th class="text-muted fw-normal border-0 py-1" style="width:45%">Course</th><td class="border-0 py-1 fw-semibold"><?= e($profile['course_name'] ?? '—') ?></td></tr>
                <?php if (!empty($profile['batch_name'])): ?>
                <tr><th class="text-muted fw-normal border-0 py-1">Batch</th><td class="border-0 py-1"><?= e($profile['batch_name']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($profile['section_name'])): ?>
                <tr><th class="text-muted fw-normal border-0 py-1">Section</th><td class="border-0 py-1"><?= e($profile['section_name']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($profile['department_name'])): ?>
                <tr><th class="text-muted fw-normal border-0 py-1">Department</th><td class="border-0 py-1"><?= e($profile['department_name']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($profile['admission_number'])): ?>
                <tr><th class="text-muted fw-normal border-0 py-1">Adm. No.</th><td class="border-0 py-1"><?= e($profile['admission_number']) ?></td></tr>
                <?php endif; ?>
                <?php if (!empty($profile['admission_date']) || !empty($profile['sa_admission_date'])): ?>
                <tr><th class="text-muted fw-normal border-0 py-1">Admitted</th><td class="border-0 py-1"><?= date('d M Y', strtotime($profile['sa_admission_date'] ?? $profile['admission_date'])) ?></td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-8">

        <!-- Personal Info -->
        <div class="portal-card mb-3">
            <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
                <div class="fw-bold" style="color:#1e293b"><i class="fas fa-id-card me-2 text-success"></i>Personal Information</div>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3" style="font-size:0.875rem">
                    <?php
                    $personalFields = [
                        ['Gender', ucfirst($profile['gender'] ?? '—')],
                        ['Blood Group', $profile['blood_group'] ?? '—'],
                        ['Category', $profile['category'] ?? '—'],
                        ['Nationality', $profile['nationality'] ?? '—'],
                        ['Aadhaar', $profile['aadhaar_number'] ?? $profile['aadhar_number'] ?? '—'],
                    ];
                    foreach ($personalFields as [$label, $val]):
                    ?>
                    <div class="col-6 col-md-4">
                        <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em"><?= $label ?></div>
                        <div class="fw-semibold"><?= e($val) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($profile['address_line1'])): ?>
                <hr class="my-2">
                <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;color:#94a3b8" class="mb-1">Address</div>
                <div style="font-size:0.875rem"><?= e($profile['address_line1']) ?><?= !empty($profile['address_line2']) ? ', ' . e($profile['address_line2']) : '' ?></div>
                <?php if (!empty($profile['city'])): ?>
                <div class="text-muted" style="font-size:0.82rem"><?= e($profile['city']) ?><?= !empty($profile['state']) ? ', ' . e($profile['state']) : '' ?><?= !empty($profile['pincode']) ? ' — ' . e($profile['pincode']) : '' ?></div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Parents / Guardian -->
        <?php if (!empty($parents) || !empty($profile['father_name'])): ?>
        <div class="portal-card mb-3">
            <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
                <div class="fw-bold" style="color:#1e293b"><i class="fas fa-users me-2 text-success"></i>Parent / Guardian Details</div>
            </div>
            <div class="card-body px-4 py-3">
                <div class="row g-3" style="font-size:0.875rem">
                    <?php
                    $fatherName  = $parents['father_name']  ?? $profile['father_name']  ?? '';
                    $fatherPhone = $parents['father_phone'] ?? $profile['father_phone'] ?? '';
                    $motherName  = $parents['mother_name']  ?? $profile['mother_name']  ?? '';
                    $guardName   = $parents['guardian_name']  ?? $profile['guardian_name']  ?? '';
                    $guardPhone  = $parents['guardian_phone'] ?? $profile['guardian_phone'] ?? '';
                    ?>
                    <?php if ($fatherName): ?>
                    <div class="col-6">
                        <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em">Father</div>
                        <div class="fw-semibold"><?= e($fatherName) ?></div>
                        <?php if ($fatherPhone): ?><div class="text-muted small"><?= e($fatherPhone) ?></div><?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($motherName): ?>
                    <div class="col-6">
                        <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em">Mother</div>
                        <div class="fw-semibold"><?= e($motherName) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if ($guardName): ?>
                    <div class="col-6">
                        <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em">Guardian</div>
                        <div class="fw-semibold"><?= e($guardName) ?></div>
                        <?php if ($guardPhone): ?><div class="text-muted small"><?= e($guardPhone) ?></div><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Change Password -->
        <div class="portal-card">
            <div class="card-header bg-transparent border-bottom px-4 pt-3 pb-2">
                <div class="fw-bold" style="color:#1e293b"><i class="fas fa-key me-2 text-success"></i>Change Password</div>
            </div>
            <div class="card-body px-4 py-3">
                <form method="POST" action="<?= url('portal/student/profile/change-password') ?>" style="max-width:460px">
                    <?= csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                        <div class="form-text">Minimum 8 characters.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm px-4">
                        <i class="fas fa-check me-1"></i>Update Password
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
