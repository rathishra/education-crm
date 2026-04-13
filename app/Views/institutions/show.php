<?php
$pageTitle = e($inst['name'] ?? 'Institution');

$instTypes = [
    'college'            => 'College',
    'school'             => 'School',
    'university'         => 'University',
    'training_institute' => 'Training Institute',
    'polytechnic'        => 'Polytechnic',
    'deemed_university'  => 'Deemed University',
    'autonomous'         => 'Autonomous',
    'other'              => 'Other',
];

$months = [
    1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
    7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December',
];

$academic = $academic ?? [];
$modules  = $modules  ?? [];
$finance  = $finance  ?? [];
$branding = $branding ?? [];
$infra    = $infra    ?? [];
$perms    = $perms    ?? [];
$isActive = ($inst['status'] ?? '') === 'active';
?>

<div class="page-header">
    <div>
        <h1>
            <i class="fas fa-university me-2"></i><?= e($inst['name']) ?>
            <?php if (!empty($inst['short_name'])): ?>
            <small class="text-muted fs-6 ms-2">(<?= e($inst['short_name']) ?>)</small>
            <?php endif; ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('institutions') ?>">Institutions</a></li>
                <li class="breadcrumb-item active"><?= e($inst['name']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('institutions.edit')): ?>
        <a href="<?= url("institutions/{$inst['id']}/edit") ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= url('institutions') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<?php $successMsg = getFlash('success'); $errorMsg = getFlash('error'); ?>
<?php if ($successMsg): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= e($successMsg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($errorMsg): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-triangle me-2"></i><?= e($errorMsg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Header Card -->
<div class="card shadow-sm mb-4" style="border-left:5px solid #2c3e8c">
    <div class="card-body">
        <div class="d-flex align-items-start gap-4">
            <div class="flex-shrink-0">
                <?php if (!empty($inst['logo'])): ?>
                <img src="<?= e($inst['logo']) ?>" alt="logo"
                     class="rounded-3 border shadow-sm" style="width:90px;height:90px;object-fit:contain">
                <?php else: ?>
                <div class="rounded-3 d-flex align-items-center justify-content-center fw-bold text-white"
                     style="width:90px;height:90px;background:#2c3e8c;font-size:28px">
                    <?= strtoupper(substr($inst['name'], 0, 2)) ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <h4 class="mb-0"><?= e($inst['name']) ?></h4>
                    <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                    <span class="badge bg-primary"><?= $instTypes[$inst['institution_type'] ?? 'other'] ?? 'Other' ?></span>
                </div>
                <div class="text-muted mb-2">
                    <code class="me-3"><?= e($inst['code']) ?></code>
                    <?php if (!empty($inst['org_name'])): ?>
                    <i class="fas fa-building me-1"></i><?= e($inst['org_name']) ?>
                    <?php endif; ?>
                </div>
                <div class="d-flex flex-wrap gap-3 small text-muted">
                    <?php if (!empty($inst['city'])): ?>
                    <span><i class="fas fa-map-marker-alt me-1"></i><?= e($inst['city']) ?><?= !empty($inst['state']) ? ', ' . e($inst['state']) : '' ?></span>
                    <?php endif; ?>
                    <?php if (!empty($inst['phone'])): ?>
                    <span><i class="fas fa-phone me-1"></i><?= e($inst['phone']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($inst['email'])): ?>
                    <span><i class="fas fa-envelope me-1"></i><?= e($inst['email']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($inst['website'])): ?>
                    <span><i class="fas fa-globe me-1"></i><a href="<?= e($inst['website']) ?>" target="_blank"><?= e($inst['website']) ?></a></span>
                    <?php endif; ?>
                    <?php if (!empty($inst['established_year'])): ?>
                    <span><i class="fas fa-calendar me-1"></i>Est. <?= e($inst['established_year']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($inst['naac_grade'])): ?>
                    <span><i class="fas fa-star me-1 text-warning"></i>NAAC: <?= e($inst['naac_grade']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['icon'=>'fas fa-sitemap',      'color'=>'#2c3e8c', 'bg'=>'#eef2ff', 'val'=>$deptCount,   'label'=>'Departments'],
        ['icon'=>'fas fa-book',         'color'=>'#0891b2', 'bg'=>'#ecfeff', 'val'=>$courseCount,  'label'=>'Courses'],
        ['icon'=>'fas fa-user-graduate','color'=>'#16a34a', 'bg'=>'#f0fdf4', 'val'=>$studentCount, 'label'=>'Students'],
        ['icon'=>'fas fa-users',        'color'=>'#7c3aed', 'bg'=>'#faf5ff', 'val'=>$userCount,    'label'=>'System Users'],
        ['icon'=>'fas fa-funnel-dollar','color'=>'#d97706', 'bg'=>'#fffbeb', 'val'=>$leadCount,    'label'=>'Leads'],
    ];
    foreach ($kpis as $kpi): ?>
    <div class="col-sm-6 col-xl">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:<?= $kpi['bg'] ?>">
                    <i class="<?= $kpi['icon'] ?> fa-lg" style="color:<?= $kpi['color'] ?>"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($kpi['val']) ?></div>
                    <div class="text-muted small"><?= $kpi['label'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <!-- LEFT -->
    <div class="col-lg-7">

        <!-- Basic Info -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    <?php
                    $fields = [
                        'Full Name'          => $inst['name'] ?? '-',
                        'Short Name'         => $inst['short_name'] ?? '-',
                        'Code'               => $inst['code'] ?? '-',
                        'Type'               => $instTypes[$inst['institution_type'] ?? 'other'] ?? '-',
                        'Parent Org / Trust' => $inst['parent_org_name'] ?? '-',
                        'Established Year'   => $inst['established_year'] ?? '-',
                        'Affiliation'        => $inst['affiliation'] ?? '-',
                        'NAAC Grade'         => $inst['naac_grade'] ?? '-',
                        'NIRF Rank'          => $inst['nirf_rank'] ?? '-',
                        'AISHE Code'         => $inst['aishe_code'] ?? '-',
                        'Approval Bodies'    => $inst['approval_bodies'] ?? '-',
                    ];
                    foreach ($fields as $label => $val): ?>
                    <div class="col-5 text-muted"><?= $label ?></div>
                    <div class="col-7 fw-semibold"><?= e((string)$val) ?: '—' ?></div>
                    <?php endforeach; ?>
                </div>
                <?php if (!empty($inst['description'])): ?>
                <hr class="my-2">
                <p class="small mb-0 text-muted"><?= nl2br(e($inst['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact & Address -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-address-card me-2"></i>Contact & Address</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    <?php
                    $addr = trim(($inst['address_line1'] ?? '') . ' ' . ($inst['address_line2'] ?? ''));
                    $cityState = trim(rtrim(($inst['city'] ?? '') . ', ' . ($inst['state'] ?? ''), ', ') . ' ' . ($inst['pincode'] ?? ''));
                    $contacts = [
                        'Address'         => $addr ?: null,
                        'City / State'    => $cityState ?: null,
                        'Country'         => $inst['country'] ?? null,
                        'Phone'           => $inst['phone'] ?? null,
                        'Alt. Phone'      => $inst['alt_phone'] ?? null,
                        'Admission Phone' => $inst['admission_phone'] ?? null,
                        'Fax'             => $inst['fax'] ?? null,
                        'Email'           => $inst['email'] ?? null,
                        'Support Email'   => $inst['support_email'] ?? null,
                        'Website'         => $inst['website'] ?? null,
                    ];
                    foreach ($contacts as $label => $val): if (!$val) continue; ?>
                    <div class="col-5 text-muted"><?= $label ?></div>
                    <div class="col-7 fw-semibold"><?= e($val) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Administration -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-user-tie me-2"></i>Administration</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    <?php
                    $adminFields = [
                        'Principal'           => $inst['principal_name'] ?? null,
                        'Director'            => $inst['director_name'] ?? null,
                        'Registrar'           => $inst['registrar_name'] ?? null,
                        'Controller of Exams' => $inst['coe_name'] ?? null,
                        'Admission Head'      => $inst['admission_head'] ?? null,
                        'Finance Officer'     => $inst['finance_officer'] ?? null,
                    ];
                    foreach ($adminFields as $label => $val): ?>
                    <div class="col-5 text-muted"><?= $label ?></div>
                    <div class="col-7 fw-semibold"><?= $val ? e($val) : '<span class="text-muted">—</span>' ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Departments List -->
        <?php if (!empty($departments)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-sitemap me-2"></i>Departments</h6>
                <a href="<?= url('departments') ?>" class="btn btn-sm btn-light">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead style="background:#f8f9fa">
                            <tr>
                                <th class="ps-3">Department</th>
                                <th>Type</th>
                                <th class="text-center">Faculty</th>
                                <th class="text-center">Students</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td class="ps-3">
                                <a href="<?= url("departments/{$dept['id']}") ?>" class="fw-semibold text-decoration-none">
                                    <?= e($dept['name']) ?>
                                </a>
                                <?php if (!empty($dept['code'])): ?>
                                <small class="text-muted ms-1">(<?= e($dept['code']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark"><?= ucfirst($dept['department_type'] ?? 'academic') ?></span></td>
                            <td class="text-center"><?= (int)($dept['faculty_count'] ?? 0) ?></td>
                            <td class="text-center"><?= (int)($dept['student_count'] ?? 0) ?></td>
                            <td class="text-center">
                                <span class="badge <?= ($dept['status'] ?? '') === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($dept['status'] ?? 'inactive') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- RIGHT -->
    <div class="col-lg-5">

        <!-- Academic Config -->
        <?php if (!empty($academic)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-graduation-cap me-2"></i>Academic Configuration</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    <?php
                    $acFields = [
                        'Year Start Month'    => $months[$academic['academic_year_start_month'] ?? 6] ?? '-',
                        'Pattern'             => ucfirst($academic['academic_pattern'] ?? '-'),
                        'Grading System'      => $academic['grading_system'] ?? '-',
                        'Min. Attendance'     => ($academic['attendance_policy'] ?? '-') . '%',
                        'Internal Marks'      => ($academic['internal_marks_percentage'] ?? '-') . '%',
                        'Pass Marks'          => ($academic['pass_marks_percentage'] ?? '-') . '%',
                        'Max Credits/Sem'     => $academic['max_credits_per_semester'] ?? '-',
                        'Credit System'       => !empty($academic['credit_system']) ? '✓ Enabled' : '✗ Disabled',
                        'Internal Assessment' => !empty($academic['internal_assessment']) ? '✓ Enabled' : '✗ Disabled',
                        'Arrear Policy'       => $academic['arrear_policy'] ?? '-',
                    ];
                    foreach ($acFields as $label => $val): ?>
                    <div class="col-6 text-muted"><?= $label ?></div>
                    <div class="col-6 fw-semibold"><?= e((string)$val) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modules -->
        <?php if (!empty($modules)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-th me-2"></i>Active Modules</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2 fw-semibold">ERP</p>
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <?php foreach (['erp_departments'=>'Departments','erp_programs'=>'Programs','erp_courses'=>'Courses','erp_admissions'=>'Admissions','erp_fees'=>'Fees','erp_exams'=>'Exams','erp_hr'=>'HR','erp_hostel'=>'Hostel','erp_transport'=>'Transport','erp_library'=>'Library','erp_placement'=>'Placement'] as $key => $label): ?>
                    <span class="badge <?= !empty($modules[$key]) ? 'bg-primary' : 'bg-light text-muted border' ?>">
                        <?= $label ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <p class="small text-muted mb-2 fw-semibold">LMS</p>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach (['lms_enabled'=>'LMS','lms_online_classes'=>'Online Classes','lms_assignments'=>'Assignments','lms_quiz'=>'Quiz','lms_discussion_forum'=>'Forum','lms_attendance'=>'Attendance','lms_gradebook'=>'Gradebook'] as $key => $label): ?>
                    <span class="badge <?= !empty($modules[$key]) ? 'bg-success' : 'bg-light text-muted border' ?>">
                        <?= $label ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Finance -->
        <?php if (!empty($finance)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-rupee-sign me-2"></i>Finance</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 small">
                    <?php
                    $finFields = [
                        'Currency'          => ($finance['currency_symbol'] ?? '₹') . ' ' . ($finance['base_currency'] ?? 'INR'),
                        'Fee Collection'    => ucfirst($finance['fee_collection_mode'] ?? '-'),
                        'Finance Yr Start'  => $months[$finance['finance_start_month'] ?? 4] ?? '-',
                        'Tax'               => !empty($finance['tax_enabled']) ? ($finance['tax_percentage'] ?? '0') . '%' : 'Disabled',
                        'Payment Gateway'   => $finance['payment_gateway'] ?? '-',
                        'Bank Name'         => $finance['bank_name'] ?? '-',
                        'Bank IFSC'         => $finance['bank_ifsc'] ?? '-',
                    ];
                    foreach ($finFields as $label => $val): ?>
                    <div class="col-6 text-muted"><?= $label ?></div>
                    <div class="col-6 fw-semibold"><?= e((string)$val) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Infrastructure -->
        <?php if (!empty($infra)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-building me-2"></i>Infrastructure</h6>
            </div>
            <div class="card-body">
                <div class="row g-2 small mb-3">
                    <?php
                    $infraFields = [
                        'Campus Type'   => ucfirst($infra['campus_type'] ?? 'single'),
                        'Buildings'     => $infra['total_buildings'] ?? '-',
                        'Classrooms'    => $infra['total_classrooms'] ?? '-',
                        'Labs'          => $infra['total_labs'] ?? '-',
                        'Area (sq.ft)'  => !empty($infra['total_area_sqft']) ? number_format($infra['total_area_sqft']) : '-',
                        'Hostel (Boys)' => $infra['hostel_boys_seats'] ?? '-',
                        'Hostel (Girls)'=> $infra['hostel_girls_seats'] ?? '-',
                    ];
                    foreach ($infraFields as $label => $val): ?>
                    <div class="col-6 text-muted"><?= $label ?></div>
                    <div class="col-6 fw-semibold"><?= e((string)$val) ?></div>
                    <?php endforeach; ?>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (['library_available'=>'Library','hostel_available'=>'Hostel','transport_available'=>'Transport','canteen_available'=>'Canteen','sports_available'=>'Sports'] as $key => $label): ?>
                    <span class="badge <?= !empty($infra[$key]) ? 'bg-success' : 'bg-light text-muted border' ?>">
                        <i class="fas fa-<?= !empty($infra[$key]) ? 'check' : 'times' ?> me-1"></i><?= $label ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Permissions -->
        <?php if (!empty($perms)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-shield-alt me-2"></i>Permissions & Portals</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (['allow_multi_campus'=>'Multi-Campus','allow_multi_department'=>'Multi-Dept','allow_multi_academic_year'=>'Multi-Yr','data_isolation'=>'Data Isolation','allow_hod_login'=>'HOD Login','allow_student_portal'=>'Student Portal','allow_parent_portal'=>'Parent Portal','allow_faculty_portal'=>'Faculty Portal'] as $key => $label): ?>
                    <span class="badge <?= !empty($perms[$key]) ? 'bg-primary' : 'bg-light text-muted border' ?>">
                        <i class="fas fa-<?= !empty($perms[$key]) ? 'check' : 'times' ?> me-1"></i><?= $label ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Branding Preview -->
        <?php if (!empty($branding)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-palette me-2"></i>Branding</h6>
            </div>
            <div class="card-body">
                <div class="d-flex gap-3 align-items-center mb-2">
                    <div>
                        <div class="small text-muted mb-1">Primary</div>
                        <div class="rounded-2 border d-flex align-items-center justify-content-center small fw-semibold"
                             style="width:60px;height:30px;background:<?= e($branding['primary_color'] ?? '#2c3e8c') ?>;color:#fff">
                            <?= e($branding['primary_color'] ?? '') ?>
                        </div>
                    </div>
                    <div>
                        <div class="small text-muted mb-1">Secondary</div>
                        <div class="rounded-2 border d-flex align-items-center justify-content-center small fw-semibold"
                             style="width:60px;height:30px;background:<?= e($branding['secondary_color'] ?? '#e74c3c') ?>;color:#fff">
                            <?= e($branding['secondary_color'] ?? '') ?>
                        </div>
                    </div>
                    <div class="small">
                        <div class="text-muted mb-1">Theme</div>
                        <strong><?= ucfirst($branding['theme'] ?? 'light') ?></strong>
                    </div>
                </div>
                <?php if (!empty($branding['report_header_name'])): ?>
                <div class="small text-muted">Report Header: <strong><?= e($branding['report_header_name']) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Audit Log -->
        <?php if (!empty($auditLogs)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-3" style="background:#2c3e8c">
                <h6 class="mb-0 text-white fw-semibold"><i class="fas fa-history me-2"></i>Recent Activity</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    <?php foreach ($auditLogs as $log): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <span class="badge bg-<?= $log['action'] === 'create' ? 'success' : ($log['action'] === 'delete' ? 'danger' : 'primary') ?> me-1">
                                    <?= ucfirst($log['action'] ?? '') ?>
                                </span>
                                <span class="text-muted">by <?= e(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? '')) ?: 'System') ?></span>
                            </span>
                            <span class="text-muted small"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <!-- Danger Zone -->
        <?php if (hasPermission('institutions.delete')): ?>
        <div class="card border-danger shadow-sm">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="mb-0 fw-semibold"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">Institutions with linked departments or students cannot be deleted — deactivate instead.</p>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash me-1"></i>Delete Institution
                </button>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-trash me-2"></i>Delete Institution</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong><?= e($inst['name']) ?></strong>?
                <div class="alert alert-warning mt-3 mb-0 small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    This cannot be undone. Institutions with departments or students cannot be deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?= url("institutions/{$inst['id']}") ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Confirm Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
