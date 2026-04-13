<?php
$isEdit    = $isEdit ?? false;
$pageTitle = $isEdit ? 'Edit Institution' : 'Add Institution';
$action    = $isEdit ? url("institutions/{$inst['id']}") : url('institutions');

// Value helper — flash > existing record > default
$v = function (string $key, $default = '') use ($inst) {
    $old = getFlash('old_input.' . $key, null);
    if ($old !== null) return $old;
    return ($inst[$key] ?? $default);
};

// Boolean helper for related tables
$bv = function (string $key, array $src, $default = 0) {
    $old = getFlash('old_input.' . $key, null);
    if ($old !== null) return (bool)$old;
    return isset($src[$key]) ? (bool)$src[$key] : (bool)$default;
};

$academic = $academic ?? [];
$modules  = $modules  ?? [];
$finance  = $finance  ?? [];
$branding = $branding ?? [];
$infra    = $infra    ?? [];
$perms    = $perms    ?? [];

$months = [
    1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
    7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December',
];

$formErrors = (array)(getFlash('errors') ?? []);
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-university me-2"></i><?= $pageTitle ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('institutions') ?>">Institutions</a></li>
                <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Add' ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('institutions') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<?php if (!empty($formErrors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="fas fa-exclamation-triangle me-1"></i>Please fix the errors below:</strong>
    <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($formErrors as $fe): ?>
        <li><?= e(is_array($fe) ? implode(', ', $fe) : $fe) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data" id="instForm">
    <?= csrfField() ?>
    <?php if ($isEdit): ?>
    <input type="hidden" name="_method" value="PUT">
    <?php endif; ?>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-0 border-bottom-0" id="instTabs" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-basic"    type="button"><i class="fas fa-info-circle me-1"></i>Basic Info</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-address"  type="button"><i class="fas fa-map-marker-alt me-1"></i>Address</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-contact"  type="button"><i class="fas fa-phone me-1"></i>Contact</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-admin"    type="button"><i class="fas fa-user-tie me-1"></i>Administration</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-academic" type="button"><i class="fas fa-graduation-cap me-1"></i>Academic</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-modules"  type="button"><i class="fas fa-th me-1"></i>Modules</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-finance"  type="button"><i class="fas fa-rupee-sign me-1"></i>Finance</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-infra"    type="button"><i class="fas fa-building me-1"></i>Infrastructure</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-branding" type="button"><i class="fas fa-palette me-1"></i>Branding</button></li>
        <li class="nav-item"><button class="nav-link"        data-bs-toggle="tab" data-bs-target="#tab-perms"    type="button"><i class="fas fa-shield-alt me-1"></i>Permissions</button></li>
    </ul>

    <div class="card shadow-sm border-top-0 rounded-0 rounded-bottom">
        <div class="tab-content p-4" id="instTabContent">

            <!-- ─────────────────────── TAB 1: Basic Info ─────────────────────── -->
            <div class="tab-pane fade show active" id="tab-basic">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><span class="text-danger me-1">*</span>Organization / Trust</label>
                        <select name="organization_id" class="form-select" required>
                            <option value="">— Select Organization —</option>
                            <?php foreach ($organizations as $org): ?>
                            <option value="<?= $org['id'] ?>" <?= $v('organization_id') == $org['id'] ? 'selected' : '' ?>>
                                <?= e($org['organization_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold"><span class="text-danger me-1">*</span>Institution Code</label>
                        <input type="text" name="code" class="form-control"
                               value="<?= e($v('code')) ?>"
                               placeholder="e.g. ABC-COLLEGE"
                               maxlength="50"
                               <?= $isEdit ? 'readonly' : 'required' ?>
                               style="text-transform:uppercase"
                               oninput="this.value=this.value.toUpperCase()">
                        <?php if ($isEdit): ?><small class="text-muted">Code cannot be changed after creation.</small><?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Short Name</label>
                        <input type="text" name="short_name" class="form-control"
                               value="<?= e($v('short_name')) ?>" placeholder="e.g. ABC College" maxlength="100">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold"><span class="text-danger me-1">*</span>Full Institution Name</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= e($v('name')) ?>"
                               placeholder="e.g. ABC College of Engineering and Technology"
                               maxlength="255" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold"><span class="text-danger me-1">*</span>Institution Type</label>
                        <select name="institution_type" class="form-select" required>
                            <?php foreach ([
                                'college'=>'College','school'=>'School','university'=>'University',
                                'training_institute'=>'Training Institute','polytechnic'=>'Polytechnic',
                                'deemed_university'=>'Deemed University','autonomous'=>'Autonomous','other'=>'Other'
                            ] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= $v('institution_type','college') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Parent Organization / Trust Name</label>
                        <input type="text" name="parent_org_name" class="form-control"
                               value="<?= e($v('parent_org_name')) ?>" placeholder="e.g. ABC Educational Trust" maxlength="255">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Established Year</label>
                        <input type="number" name="established_year" class="form-control"
                               value="<?= e($v('established_year')) ?>"
                               min="1800" max="<?= date('Y') + 1 ?>" placeholder="e.g. 1998">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   <?= $v('status','active') === 'active'   ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $v('status','active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Brief description of the institution..."><?= e($v('description')) ?></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Institution Logo</label>
                        <?php if (!empty($inst['logo'])): ?>
                        <div class="mb-2">
                            <img src="<?= e($inst['logo']) ?>" alt="logo" class="img-thumbnail" style="max-height:80px">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <small class="text-muted">PNG/JPG, max 2 MB</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Affiliation / University</label>
                        <input type="text" name="affiliation" class="form-control"
                               value="<?= e($v('affiliation')) ?>" placeholder="e.g. Anna University" maxlength="255">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">NAAC Grade</label>
                        <input type="text" name="naac_grade" class="form-control"
                               value="<?= e($v('naac_grade')) ?>" placeholder="A++ / A+ / A" maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">NIRF Rank</label>
                        <input type="number" name="nirf_rank" class="form-control"
                               value="<?= e($v('nirf_rank')) ?>" placeholder="e.g. 45" min="1">
                    </div>
                </div>
            </div>

            <!-- ─────────────────────── TAB 2: Address ─────────────────────── -->
            <div class="tab-pane fade" id="tab-address">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Address Line 1</label>
                        <input type="text" name="address_line1" class="form-control"
                               value="<?= e($v('address_line1')) ?>" placeholder="Street / Door No." maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Address Line 2</label>
                        <input type="text" name="address_line2" class="form-control"
                               value="<?= e($v('address_line2')) ?>" placeholder="Area / Locality" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" class="form-control" value="<?= e($v('city')) ?>" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">State</label>
                        <input type="text" name="state" class="form-control" value="<?= e($v('state')) ?>" maxlength="100">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Pincode</label>
                        <input type="text" name="pincode" class="form-control" value="<?= e($v('pincode')) ?>" maxlength="20">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" class="form-control" value="<?= e($v('country','India')) ?>" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Latitude</label>
                        <input type="number" name="latitude" class="form-control" step="0.0000001"
                               value="<?= e($v('latitude')) ?>" placeholder="e.g. 13.0827">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Longitude</label>
                        <input type="number" name="longitude" class="form-control" step="0.0000001"
                               value="<?= e($v('longitude')) ?>" placeholder="e.g. 80.2707">
                    </div>
                </div>
            </div>

            <!-- ─────────────────────── TAB 3: Contact ─────────────────────── -->
            <div class="tab-pane fade" id="tab-contact">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Primary Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($v('phone')) ?>" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Alternate Phone</label>
                        <input type="text" name="alt_phone" class="form-control" value="<?= e($v('alt_phone')) ?>" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fax</label>
                        <input type="text" name="fax" class="form-control" value="<?= e($v('fax')) ?>" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admission Phone</label>
                        <input type="text" name="admission_phone" class="form-control" value="<?= e($v('admission_phone')) ?>" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Primary Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e($v('email')) ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Support Email</label>
                        <input type="email" name="support_email" class="form-control" value="<?= e($v('support_email')) ?>" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Website</label>
                        <input type="url" name="website" class="form-control" value="<?= e($v('website')) ?>"
                               placeholder="https://www.example.edu.in" maxlength="255">
                    </div>
                </div>
            </div>

            <!-- ─────────────────────── TAB 4: Administration ─────────────────────── -->
            <div class="tab-pane fade" id="tab-admin">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Principal Name</label>
                        <input type="text" name="principal_name" class="form-control" value="<?= e($v('principal_name')) ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Director Name</label>
                        <input type="text" name="director_name" class="form-control" value="<?= e($v('director_name')) ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Registrar Name</label>
                        <input type="text" name="registrar_name" class="form-control" value="<?= e($v('registrar_name')) ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Controller of Examinations</label>
                        <input type="text" name="coe_name" class="form-control" value="<?= e($v('coe_name')) ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admission Head</label>
                        <input type="text" name="admission_head" class="form-control" value="<?= e($v('admission_head')) ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Finance Officer</label>
                        <input type="text" name="finance_officer" class="form-control" value="<?= e($v('finance_officer')) ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">AISHE Code</label>
                        <input type="text" name="aishe_code" class="form-control" value="<?= e($v('aishe_code')) ?>" maxlength="50">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Approval / Recognition</label>
                        <input type="text" name="approval_bodies" class="form-control"
                               value="<?= e($v('approval_bodies')) ?>"
                               placeholder="e.g. AICTE, UGC" maxlength="255">
                    </div>
                </div>
            </div>

            <!-- ─────────────────────── TAB 5: Academic Config ─────────────────────── -->
            <div class="tab-pane fade" id="tab-academic">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Academic Year Start Month</label>
                        <select name="academic_year_start_month" class="form-select">
                            <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= ($academic['academic_year_start_month'] ?? 6) == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Academic Pattern</label>
                        <select name="academic_pattern" class="form-select">
                            <?php foreach (['annual'=>'Annual','semester'=>'Semester','trimester'=>'Trimester'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= ($academic['academic_pattern'] ?? 'semester') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Grading System</label>
                        <input type="text" name="grading_system" class="form-control"
                               value="<?= e($academic['grading_system'] ?? '') ?>"
                               placeholder="e.g. 10-Point CGPA" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Min. Attendance %</label>
                        <input type="number" name="attendance_policy" class="form-control"
                               value="<?= e($academic['attendance_policy'] ?? 75) ?>" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Internal Marks %</label>
                        <input type="number" name="internal_marks_percentage" class="form-control"
                               value="<?= e($academic['internal_marks_percentage'] ?? 30) ?>" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Pass Marks %</label>
                        <input type="number" name="pass_marks_percentage" class="form-control"
                               value="<?= e($academic['pass_marks_percentage'] ?? 50) ?>" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Max Credits / Semester</label>
                        <input type="number" name="max_credits_per_semester" class="form-control"
                               value="<?= e($academic['max_credits_per_semester'] ?? '') ?>" min="1" max="50">
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-3 mt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="credit_system" id="creditSystem" value="1"
                                   <?= $bv('credit_system', $academic, 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="creditSystem">Credit System Enabled</label>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-3 mt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="internal_assessment" id="intAssess" value="1"
                                   <?= $bv('internal_assessment', $academic, 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="intAssess">Internal Assessment</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Arrear Policy</label>
                        <input type="text" name="arrear_policy" class="form-control"
                               value="<?= e($academic['arrear_policy'] ?? '') ?>"
                               placeholder="e.g. Max 3 arrears allowed" maxlength="255">
                    </div>
                </div>
            </div>

            <!-- ─────────────────────── TAB 6: Modules ─────────────────────── -->
            <div class="tab-pane fade" id="tab-modules">
                <h6 class="fw-semibold mb-3">ERP Modules</h6>
                <div class="row g-2 mb-4">
                    <?php
                    $erpMods = [
                        'erp_departments' => 'Departments',
                        'erp_programs'    => 'Programs',
                        'erp_courses'     => 'Courses',
                        'erp_admissions'  => 'Admissions',
                        'erp_fees'        => 'Fees',
                        'erp_exams'       => 'Examinations',
                        'erp_hr'          => 'HR / Payroll',
                        'erp_hostel'      => 'Hostel',
                        'erp_transport'   => 'Transport',
                        'erp_library'     => 'Library',
                        'erp_placement'   => 'Placement',
                    ];
                    $erpDefaults = ['erp_departments'=>1,'erp_programs'=>1,'erp_courses'=>1,'erp_admissions'=>1,'erp_fees'=>1,'erp_exams'=>1];
                    foreach ($erpMods as $key => $label): ?>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="mod_<?= $key ?>" value="1"
                                   <?= $bv($key, $modules, $erpDefaults[$key] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mod_<?= $key ?>"><?= $label ?></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr>
                <h6 class="fw-semibold mb-3">LMS Modules</h6>
                <div class="row g-2">
                    <?php
                    $lmsMods = [
                        'lms_enabled'          => 'LMS Enabled',
                        'lms_online_classes'   => 'Online Classes',
                        'lms_assignments'      => 'Assignments',
                        'lms_quiz'             => 'Quiz',
                        'lms_discussion_forum' => 'Discussion Forum',
                        'lms_attendance'       => 'Attendance',
                        'lms_gradebook'        => 'Gradebook',
                    ];
                    $lmsDefaults = ['lms_enabled'=>1,'lms_assignments'=>1,'lms_quiz'=>1,'lms_attendance'=>1,'lms_gradebook'=>1];
                    foreach ($lmsMods as $key => $label): ?>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="mod_<?= $key ?>" value="1"
                                   <?= $bv($key, $modules, $lmsDefaults[$key] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mod_<?= $key ?>"><?= $label ?></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ─────────────────────── TAB 7: Finance ─────────────────────── -->
            <div class="tab-pane fade" id="tab-finance">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Base Currency</label>
                        <input type="text" name="base_currency" class="form-control"
                               value="<?= e($finance['base_currency'] ?? 'INR') ?>" maxlength="10">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Currency Symbol</label>
                        <input type="text" name="currency_symbol" class="form-control"
                               value="<?= e($finance['currency_symbol'] ?? '₹') ?>" maxlength="5">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Fee Collection Mode</label>
                        <select name="fee_collection_mode" class="form-select">
                            <?php foreach (['online'=>'Online','offline'=>'Offline','both'=>'Both'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= ($finance['fee_collection_mode'] ?? 'both') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Finance Year Start Month</label>
                        <select name="finance_start_month" class="form-select">
                            <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" <?= ($finance['finance_start_month'] ?? 4) == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tax %</label>
                        <div class="input-group">
                            <input type="number" name="tax_percentage" class="form-control" step="0.01" min="0" max="100"
                                   value="<?= e($finance['tax_percentage'] ?? '') ?>">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Payment Gateway</label>
                        <input type="text" name="payment_gateway" class="form-control"
                               value="<?= e($finance['payment_gateway'] ?? '') ?>" maxlength="100" placeholder="e.g. Razorpay">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" value="<?= e($finance['bank_name'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Bank IFSC</label>
                        <input type="text" name="bank_ifsc" class="form-control" value="<?= e($finance['bank_ifsc'] ?? '') ?>" maxlength="20">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="tax_enabled" id="taxEnabled" value="1"
                                   <?= $bv('tax_enabled', $finance, 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="taxEnabled">Tax Enabled</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─────────────────────── TAB 8: Infrastructure ─────────────────────── -->
            <div class="tab-pane fade" id="tab-infra">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Campus Type</label>
                        <select name="campus_type" class="form-select">
                            <option value="single" <?= ($infra['campus_type'] ?? 'single') === 'single' ? 'selected' : '' ?>>Single Campus</option>
                            <option value="multi"  <?= ($infra['campus_type'] ?? 'single') === 'multi'  ? 'selected' : '' ?>>Multi Campus</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Total Buildings</label>
                        <input type="number" name="total_buildings" class="form-control" min="0"
                               value="<?= e($infra['total_buildings'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Total Classrooms</label>
                        <input type="number" name="total_classrooms" class="form-control" min="0"
                               value="<?= e($infra['total_classrooms'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Total Labs</label>
                        <input type="number" name="total_labs" class="form-control" min="0"
                               value="<?= e($infra['total_labs'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Total Departments</label>
                        <input type="number" name="total_departments" class="form-control" min="0"
                               value="<?= e($infra['total_departments'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Total Area (sq.ft)</label>
                        <input type="number" name="total_area_sqft" class="form-control" min="0"
                               value="<?= e($infra['total_area_sqft'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Hostel Boys Seats</label>
                        <input type="number" name="hostel_boys_seats" class="form-control" min="0"
                               value="<?= e($infra['hostel_boys_seats'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Hostel Girls Seats</label>
                        <input type="number" name="hostel_girls_seats" class="form-control" min="0"
                               value="<?= e($infra['hostel_girls_seats'] ?? '') ?>">
                    </div>
                    <div class="col-12"><hr class="my-2"></div>
                    <?php
                    $infraFlags = [
                        'library_available'   => 'Library Available',
                        'hostel_available'    => 'Hostel Available',
                        'transport_available' => 'Transport Available',
                        'canteen_available'   => 'Canteen Available',
                        'sports_available'    => 'Sports Facility',
                    ];
                    foreach ($infraFlags as $key => $label): ?>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="infra_<?= $key ?>" value="1"
                                   <?= $bv($key, $infra, 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="infra_<?= $key ?>"><?= $label ?></label>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ─────────────────────── TAB 9: Branding ─────────────────────── -->
            <div class="tab-pane fade" id="tab-branding">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Primary Color</label>
                        <div class="input-group">
                            <input type="color" name="primary_color" class="form-control form-control-color"
                                   value="<?= e($branding['primary_color'] ?? '#2c3e8c') ?>">
                            <input type="text" class="form-control" id="primaryColorText"
                                   value="<?= e($branding['primary_color'] ?? '#2c3e8c') ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Secondary Color</label>
                        <div class="input-group">
                            <input type="color" name="secondary_color" class="form-control form-control-color"
                                   value="<?= e($branding['secondary_color'] ?? '#e74c3c') ?>">
                            <input type="text" class="form-control" id="secondaryColorText"
                                   value="<?= e($branding['secondary_color'] ?? '#e74c3c') ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Theme</label>
                        <select name="theme" class="form-select">
                            <?php foreach (['light'=>'Light','dark'=>'Dark','system'=>'System Default'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= ($branding['theme'] ?? 'light') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Report Header Name</label>
                        <input type="text" name="report_header_name" class="form-control"
                               value="<?= e($branding['report_header_name'] ?? '') ?>"
                               placeholder="Name as it appears on report headers" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Footer Text</label>
                        <input type="text" name="footer_text" class="form-control"
                               value="<?= e($branding['footer_text'] ?? '') ?>"
                               placeholder="Appears in report/email footers" maxlength="500">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Login Banner Image</label>
                        <?php if (!empty($branding['login_banner'])): ?>
                        <div class="mb-2"><img src="<?= e($branding['login_banner']) ?>" class="img-thumbnail" style="max-height:60px"></div>
                        <?php endif; ?>
                        <input type="file" name="login_banner" class="form-control" accept="image/*">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Favicon</label>
                        <?php if (!empty($branding['favicon'])): ?>
                        <div class="mb-2"><img src="<?= e($branding['favicon']) ?>" class="img-thumbnail" style="max-height:60px"></div>
                        <?php endif; ?>
                        <input type="file" name="favicon" class="form-control" accept="image/x-icon,image/png,image/gif">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email Header Logo</label>
                        <?php if (!empty($branding['email_header_logo'])): ?>
                        <div class="mb-2"><img src="<?= e($branding['email_header_logo']) ?>" class="img-thumbnail" style="max-height:60px"></div>
                        <?php endif; ?>
                        <input type="file" name="email_header_logo" class="form-control" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- ─────────────────────── TAB 10: Permissions ─────────────────────── -->
            <div class="tab-pane fade" id="tab-perms">
                <div class="row g-3">
                    <?php
                    $permFlags = [
                        'allow_multi_campus'        => ['label' => 'Multi-Campus Support',        'default' => 0],
                        'allow_multi_department'    => ['label' => 'Multiple Departments',         'default' => 1],
                        'allow_multi_academic_year' => ['label' => 'Multiple Academic Years',      'default' => 1],
                        'data_isolation'            => ['label' => 'Data Isolation per Dept',      'default' => 1],
                        'allow_hod_login'           => ['label' => 'HOD Login Allowed',            'default' => 0],
                        'allow_student_portal'      => ['label' => 'Student Portal Enabled',       'default' => 1],
                        'allow_parent_portal'       => ['label' => 'Parent Portal Enabled',        'default' => 0],
                        'allow_faculty_portal'      => ['label' => 'Faculty Portal Enabled',       'default' => 1],
                    ];
                    foreach ($permFlags as $key => $cfg): ?>
                    <div class="col-md-4">
                        <div class="card border p-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="perm_<?= $key ?>" value="1"
                                       <?= $bv($key, $perms, $cfg['default']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="perm_<?= $key ?>"><?= $cfg['label'] ?></label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /tab-content -->

        <!-- Footer -->
        <div class="card-footer d-flex gap-2 py-3 px-4 border-top">
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Institution' : 'Create Institution' ?>
            </button>
            <a href="<?= $isEdit ? url("institutions/{$inst['id']}") : url('institutions') ?>"
               class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</form>

<script>
// Persist active tab across validation failures
const TAB_KEY = 'instFormTab';
const instTabs = document.getElementById('instTabs');
const savedTab = sessionStorage.getItem(TAB_KEY);
if (savedTab) {
    const tabEl = instTabs.querySelector(`[data-bs-target="${savedTab}"]`);
    if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
}
instTabs.querySelectorAll('[data-bs-toggle="tab"]').forEach(btn => {
    btn.addEventListener('shown.bs.tab', e => sessionStorage.setItem(TAB_KEY, e.target.dataset.bsTarget));
});

// Sync color pickers with text display
document.querySelector('[name="primary_color"]').addEventListener('input', function () {
    document.getElementById('primaryColorText').value = this.value;
});
document.querySelector('[name="secondary_color"]').addEventListener('input', function () {
    document.getElementById('secondaryColorText').value = this.value;
});
</script>
