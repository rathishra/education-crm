<?php
$formErrors  = (array)(getFlash('errors') ?? []);
$flashError  = getFlash('error');
$selectedId  = (int)($selectedId ?? 0);
$pageTitle   = $pageTitle ?? 'Student Enquiry';

// Value helper — flash > default
$v = fn(string $key, $default = '') => old($key, $default);

$degreeLabels = [
    'diploma'     => 'Diploma',
    'ug'          => 'Under Graduate',
    'pg'          => 'Post Graduate',
    'phd'         => 'PhD',
    'certificate' => 'Certificate',
    'other'       => 'Other',
];

$currentYear  = (int)date('Y');
$academicYear = $currentYear . '-' . ($currentYear + 1);
?>

<style>
/* ── Stepper ── */
.enq-stepper { display:flex; gap:0; margin-bottom:2rem; }
.enq-step {
    flex:1; display:flex; flex-direction:column; align-items:center;
    position:relative; cursor:pointer;
}
.enq-step::after {
    content:''; position:absolute; top:20px; left:50%; width:100%; height:2px;
    background:#e2e8f0; z-index:0;
}
.enq-step:last-child::after { display:none; }
.enq-step-icon {
    width:40px; height:40px; border-radius:50%; border:2px solid #e2e8f0;
    background:#fff; display:flex; align-items:center; justify-content:center;
    font-size:15px; font-weight:700; color:#94a3b8; position:relative; z-index:1;
    transition:all .25s;
}
.enq-step-label { font-size:11px; color:#94a3b8; margin-top:6px; text-align:center; font-weight:500; }
.enq-step.active .enq-step-icon { background:#2c3e8c; border-color:#2c3e8c; color:#fff; }
.enq-step.active .enq-step-label { color:#2c3e8c; font-weight:700; }
.enq-step.done .enq-step-icon { background:#10b981; border-color:#10b981; color:#fff; }
.enq-step.done::after { background:#10b981; }
.enq-step.done .enq-step-label { color:#10b981; }

/* ── Step panels ── */
.step-panel { display:none; }
.step-panel.active { display:block; animation: fadeIn .25s ease; }
@keyframes fadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:none} }

/* ── Course card grid ── */
.course-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:12px; }
.course-card {
    border:2px solid #e2e8f0; border-radius:10px; padding:14px; cursor:pointer;
    transition:all .2s; background:#fff; position:relative;
}
.course-card:hover { border-color:#2c3e8c; background:#eef2ff; }
.course-card.selected { border-color:#2c3e8c; background:#eef2ff; }
.course-card.selected::after {
    content:'\f00c'; font-family:'Font Awesome 6 Free'; font-weight:900;
    position:absolute; top:8px; right:10px; color:#2c3e8c; font-size:14px;
}
.course-badge { font-size:10px; padding:2px 7px; border-radius:20px; font-weight:600; }

/* ── Radio pill ── */
.radio-pill-group { display:flex; flex-wrap:wrap; gap:8px; }
.radio-pill { display:none; }
.radio-pill + label {
    padding:6px 16px; border:2px solid #e2e8f0; border-radius:20px;
    cursor:pointer; font-size:13px; color:#64748b; transition:all .2s;
}
.radio-pill:checked + label { border-color:#2c3e8c; background:#2c3e8c; color:#fff; }

/* ── Checkbox toggle card ── */
.toggle-card {
    display:flex; align-items:center; gap:12px; border:2px solid #e2e8f0;
    border-radius:10px; padding:12px 16px; cursor:pointer; transition:all .2s;
}
.toggle-card:hover { border-color:#2c3e8c20; background:#f8fafc; }
.toggle-card input:checked ~ .toggle-icon { color:#2c3e8c; }

/* ── Progress bar ── */
.enq-progress { height:4px; background:#e2e8f0; border-radius:2px; margin-bottom:2rem; overflow:hidden; }
.enq-progress-bar { height:100%; background:linear-gradient(90deg,#2c3e8c,#6366f1); border-radius:2px; transition:width .4s; }

/* ── Institution card ── */
.inst-card {
    border:2px solid #e2e8f0; border-radius:10px; padding:14px;
    cursor:pointer; transition:all .2s; text-align:center;
}
.inst-card:hover { border-color:#2c3e8c; background:#eef2ff; }
.inst-card.selected { border-color:#2c3e8c; background:#eef2ff; }
.inst-logo { width:56px; height:56px; border-radius:8px; object-fit:contain; margin:0 auto 8px; display:block; }
.inst-initials {
    width:56px; height:56px; border-radius:8px; margin:0 auto 8px;
    background:#2c3e8c; color:#fff; font-weight:700; font-size:18px;
    display:flex; align-items:center; justify-content:center;
}
</style>

<!-- Header -->
<div class="text-center mb-4">
    <h2 class="fw-bold mb-1" style="color:#1e293b">Student Enquiry Form</h2>
    <p class="text-muted mb-0">Tell us about yourself and we'll connect you with the right program</p>
</div>

<!-- Progress -->
<div class="enq-progress" id="enqProgress">
    <div class="enq-progress-bar" id="enqProgressBar" style="width:25%"></div>
</div>

<!-- Stepper -->
<div class="enq-stepper" id="enqStepper">
    <div class="enq-step active" data-step="1">
        <div class="enq-step-icon">1</div>
        <div class="enq-step-label">Institution &<br>Course</div>
    </div>
    <div class="enq-step" data-step="2">
        <div class="enq-step-icon">2</div>
        <div class="enq-step-label">Personal<br>Details</div>
    </div>
    <div class="enq-step" data-step="3">
        <div class="enq-step-icon">3</div>
        <div class="enq-step-label">Academic<br>Background</div>
    </div>
    <div class="enq-step" data-step="4">
        <div class="enq-step-icon">4</div>
        <div class="enq-step-label">Preferences &<br>Submit</div>
    </div>
</div>

<?php if (!empty($formErrors)): ?>
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <strong><i class="fas fa-exclamation-triangle me-1"></i>Please fix the following:</strong>
    <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($formErrors as $fe): ?>
        <li><?= e(is_array($fe) ? implode(', ',$fe) : $fe) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="<?= url('enquire') ?>" id="enquiryForm" novalidate>
    <?= csrfField() ?>

    <!-- Hidden UTM fields (auto-populated from URL params) -->
    <input type="hidden" name="utm_campaign" id="utmCampaign">

    <!-- ═══════════════════════ STEP 1: Institution & Course ═══════════════════════ -->
    <div class="step-panel active" id="step1">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1"><i class="fas fa-university me-2 text-primary"></i>Select Institution</h5>
                <p class="text-muted small mb-3">Choose the institution you want to enquire about</p>

                <?php if (count($institutions) === 1): ?>
                <!-- Single institution — auto-select silently -->
                <input type="hidden" name="institution_id" id="institutionId" value="<?= $institutions[0]['id'] ?>">
                <div class="alert alert-info d-flex align-items-center gap-3 mb-4">
                    <?php if (!empty($institutions[0]['logo'])): ?>
                    <img src="<?= e($institutions[0]['logo']) ?>" class="rounded" style="width:40px;height:40px;object-fit:contain">
                    <?php else: ?>
                    <div class="rounded d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                         style="width:40px;height:40px;background:#2c3e8c">
                        <?= strtoupper(substr($institutions[0]['name'],0,2)) ?>
                    </div>
                    <?php endif; ?>
                    <div>
                        <div class="fw-semibold"><?= e($institutions[0]['name']) ?></div>
                        <div class="text-muted small"><?= e($institutions[0]['city'] ?? '') ?> &middot; <?= e($institutions[0]['code']) ?></div>
                    </div>
                </div>
                <?php else: ?>
                <div class="row g-3 mb-4" id="institutionCards">
                    <input type="hidden" name="institution_id" id="institutionId" value="<?= e($v('institution_id', $selectedId ?: '')) ?>">
                    <?php foreach ($institutions as $inst): ?>
                    <div class="col-sm-6 col-md-4 col-xl-3">
                        <div class="inst-card <?= ($v('institution_id', $selectedId) == $inst['id']) ? 'selected' : '' ?>"
                             data-id="<?= $inst['id'] ?>">
                            <?php if (!empty($inst['logo'])): ?>
                            <img src="<?= e($inst['logo']) ?>" class="inst-logo" alt="<?= e($inst['name']) ?>">
                            <?php else: ?>
                            <div class="inst-initials"><?= strtoupper(substr($inst['name'],0,2)) ?></div>
                            <?php endif; ?>
                            <div class="fw-semibold small"><?= e($inst['name']) ?></div>
                            <?php if (!empty($inst['city'])): ?>
                            <div class="text-muted" style="font-size:11px"><i class="fas fa-map-marker-alt me-1"></i><?= e($inst['city']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Department filter -->
                <div class="mb-3" id="deptRow" style="<?= empty($departments) ? 'display:none' : '' ?>">
                    <label class="form-label fw-semibold">Filter by Department <span class="text-muted fw-normal">(optional)</span></label>
                    <select name="department_id" id="departmentSelect" class="form-select">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= $v('department_id') == $dept['id'] ? 'selected' : '' ?>>
                            <?= e($dept['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Course selection -->
                <div id="courseSection">
                    <label class="form-label fw-semibold">
                        <span class="text-danger me-1">*</span>Select Course / Program
                    </label>

                    <input type="hidden" name="course_interested_id" id="courseInterestedId" value="<?= e($v('course_interested_id')) ?>">

                    <?php
                    // Show "select institution" message only when no institution is pre-selected AND no courses loaded
                    $showLoadMsg = empty($courses) && !$selectedId && count($institutions) > 1;
                    ?>
                    <div id="courseLoadingMsg" class="text-center py-4 text-muted" style="<?= $showLoadMsg ? '' : 'display:none' ?>">
                        <i class="fas fa-university fa-2x mb-2 d-block text-muted"></i>
                        Select an institution above to view available courses
                    </div>

                    <div id="courseSpinner" class="text-center py-3" style="display:none">
                        <div class="spinner-border text-primary" style="width:1.5rem;height:1.5rem"></div>
                        <span class="ms-2 text-muted">Loading courses…</span>
                    </div>

                    <div class="course-grid" id="courseGrid">
                        <?php foreach ($courses as $c): ?>
                        <div class="course-card <?= $v('course_interested_id') == $c['id'] ? 'selected' : '' ?>"
                             data-id="<?= $c['id'] ?>"
                             data-dept="<?= $c['department_id'] ?? 0 ?>"
                             data-name="<?= e($c['name']) ?>"
                             data-degree="<?= e($c['degree_type'] ?? '') ?>"
                             data-duration="<?= $c['duration_years'] ?>"
                             data-fees="<?= $c['fees_per_year'] ?? '' ?>">
                            <div class="fw-semibold small mb-1"><?= e($c['name']) ?></div>
                            <?php if (!empty($c['dept_name'])): ?>
                            <div class="text-muted" style="font-size:11px"><i class="fas fa-sitemap me-1"></i><?= e($c['dept_name']) ?></div>
                            <?php endif; ?>
                            <div class="mt-2 d-flex gap-1 flex-wrap">
                                <span class="course-badge bg-primary bg-opacity-10 text-primary">
                                    <?= $degreeLabels[$c['degree_type'] ?? ''] ?? strtoupper($c['degree_type'] ?? '') ?>
                                </span>
                                <?php if ($c['duration_years']): ?>
                                <span class="course-badge bg-secondary bg-opacity-10 text-secondary">
                                    <?= $c['duration_years'] ?> yr
                                </span>
                                <?php endif; ?>
                                <?php if (!empty($c['fees_per_year'])): ?>
                                <span class="course-badge bg-success bg-opacity-10 text-success">
                                    ₹<?= number_format($c['fees_per_year']) ?>/yr
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="noCourseMsg" class="text-center py-3 text-muted" style="display:none">
                        <i class="fas fa-inbox me-1"></i> No courses found for this selection.
                    </div>
                </div>

                <!-- Selected course summary pill -->
                <div id="selectedCoursePill" class="mt-3" style="display:none">
                    <span class="badge rounded-pill" style="background:#eef2ff;color:#2c3e8c;font-size:13px;padding:8px 14px">
                        <i class="fas fa-graduation-cap me-1"></i>
                        <span id="selectedCourseName"></span>
                        <button type="button" class="btn-close ms-2" style="font-size:9px" id="clearCourse"></button>
                    </span>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4">
                <button type="button" class="btn btn-primary px-5" id="step1Next">
                    Next: Personal Details <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════ STEP 2: Personal Details ═══════════════════════ -->
    <div class="step-panel" id="step2">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1"><i class="fas fa-user me-2 text-primary"></i>Personal Details</h5>
                <p class="text-muted small mb-4">We'll use these to contact you about your enquiry</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><span class="text-danger me-1">*</span>First Name</label>
                        <input type="text" name="first_name" class="form-control form-control-lg"
                               value="<?= e($v('first_name')) ?>"
                               placeholder="e.g. Rathish" maxlength="100" autocomplete="given-name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Last Name</label>
                        <input type="text" name="last_name" class="form-control form-control-lg"
                               value="<?= e($v('last_name')) ?>"
                               placeholder="e.g. Kumar" maxlength="100" autocomplete="family-name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><span class="text-danger me-1">*</span>Mobile Number</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?= e($v('phone')) ?>"
                                   placeholder="10-digit mobile" maxlength="15" autocomplete="tel">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control"
                                   value="<?= e($v('email')) ?>"
                                   placeholder="your@email.com" autocomplete="email">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender</label>
                        <div class="radio-pill-group mt-1">
                            <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $val => $lbl): ?>
                            <input type="radio" name="gender" id="gender_<?= $val ?>" value="<?= $val ?>" class="radio-pill"
                                   <?= $v('gender') === $val ? 'checked' : '' ?>>
                            <label for="gender_<?= $val ?>"><?= $lbl ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control form-control-lg"
                               value="<?= e($v('date_of_birth')) ?>"
                               max="<?= date('Y-m-d', strtotime('-14 years')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">City / District</label>
                        <input type="text" name="city" class="form-control form-control-lg"
                               value="<?= e($v('city')) ?>" placeholder="Your city" maxlength="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">State</label>
                        <select name="state" class="form-select form-select-lg">
                            <option value="">Select State</option>
                            <?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'] as $st): ?>
                            <option value="<?= $st ?>" <?= $v('state') === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Parent / Guardian Phone</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fas fa-user-friends"></i></span>
                            <input type="tel" name="parent_phone" class="form-control"
                                   value="<?= e($v('parent_phone')) ?>"
                                   placeholder="Parent contact number" maxlength="15">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary px-4" id="step2Back">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary px-5" id="step2Next">
                    Next: Academic Background <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════ STEP 3: Academic Background ═══════════════════════ -->
    <div class="step-panel" id="step3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1"><i class="fas fa-book me-2 text-primary"></i>Academic Background</h5>
                <p class="text-muted small mb-4">Tell us about your educational qualifications</p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Highest Qualification</label>
                        <select name="qualification" class="form-select form-select-lg">
                            <option value="">Select qualification</option>
                            <?php foreach ([
                                '10th'         => '10th Standard (SSLC)',
                                '12th'         => '12th Standard (HSC)',
                                'diploma'      => 'Diploma',
                                'ug'           => 'Under Graduate (B.E / B.Tech / B.Sc etc.)',
                                'pg'           => 'Post Graduate (M.E / M.Tech / M.Sc etc.)',
                                'phd'          => 'PhD',
                            ] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= $v('qualification') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Percentage / CGPA</label>
                        <div class="input-group input-group-lg">
                            <input type="number" name="percentage" class="form-control"
                                   value="<?= e($v('percentage')) ?>"
                                   placeholder="e.g. 85.5" min="0" max="100" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Year of Passing</label>
                        <select name="passing_year" class="form-select form-select-lg">
                            <option value="">Select year</option>
                            <?php for ($y = $currentYear + 1; $y >= $currentYear - 15; $y--): ?>
                            <option value="<?= $y ?>" <?= $v('passing_year') == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">School / College Name</label>
                        <input type="text" name="school_college" class="form-control form-control-lg"
                               value="<?= e($v('school_college')) ?>"
                               placeholder="Name of your last institution" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Preferred Academic Year</label>
                        <div class="radio-pill-group mt-1">
                            <?php
                            $ayOptions = [
                                "{$currentYear}-" . ($currentYear+1) => "Current Year ({$currentYear}-" . ($currentYear+1) . ")",
                                ($currentYear+1) . '-' . ($currentYear+2) => "Next Year (" . ($currentYear+1) . "-" . ($currentYear+2) . ")",
                            ];
                            foreach ($ayOptions as $val => $lbl): ?>
                            <input type="radio" name="academic_year" id="ay_<?= str_replace('-','_',$val) ?>"
                                   value="<?= $val ?>" class="radio-pill"
                                   <?= ($v('academic_year', $academicYear) === $val) ? 'checked' : '' ?>>
                            <label for="ay_<?= str_replace('-','_',$val) ?>"><?= $lbl ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary px-4" id="step3Back">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary px-5" id="step3Next">
                    Next: Preferences <i class="fas fa-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════ STEP 4: Preferences & Submit ═══════════════════════ -->
    <div class="step-panel" id="step4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1"><i class="fas fa-sliders-h me-2 text-primary"></i>Preferences & Additional Info</h5>
                <p class="text-muted small mb-4">Help us understand your needs better</p>

                <div class="row g-3">
                    <!-- Preferred mode -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Preferred Study Mode</label>
                        <div class="radio-pill-group mt-1">
                            <?php foreach (['offline'=>'On-Campus','online'=>'Online','hybrid'=>'Hybrid'] as $val => $lbl): ?>
                            <input type="radio" name="preferred_mode" id="mode_<?= $val ?>" value="<?= $val ?>" class="radio-pill"
                                   <?= $v('preferred_mode','offline') === $val ? 'checked' : '' ?>>
                            <label for="mode_<?= $val ?>"><?= $lbl ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Requirements toggle cards -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Additional Requirements</label>
                        <div class="row g-2">
                            <div class="col-sm-4">
                                <label class="toggle-card d-flex w-100 mb-0">
                                    <input type="checkbox" name="hostel_required" value="1" class="form-check-input flex-shrink-0"
                                           <?= $v('hostel_required') ? 'checked' : '' ?>>
                                    <i class="fas fa-bed toggle-icon ms-2 text-muted"></i>
                                    <div class="ms-2">
                                        <div class="fw-semibold small">Hostel</div>
                                        <div class="text-muted" style="font-size:11px">Need accommodation</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-sm-4">
                                <label class="toggle-card d-flex w-100 mb-0">
                                    <input type="checkbox" name="transport_required" value="1" class="form-check-input flex-shrink-0"
                                           <?= $v('transport_required') ? 'checked' : '' ?>>
                                    <i class="fas fa-bus toggle-icon ms-2 text-muted"></i>
                                    <div class="ms-2">
                                        <div class="fw-semibold small">Transport</div>
                                        <div class="text-muted" style="font-size:11px">Need bus facility</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-sm-4">
                                <label class="toggle-card d-flex w-100 mb-0">
                                    <input type="checkbox" name="scholarship_required" value="1" class="form-check-input flex-shrink-0"
                                           <?= $v('scholarship_required') ? 'checked' : '' ?>>
                                    <i class="fas fa-graduation-cap toggle-icon ms-2 text-muted"></i>
                                    <div class="ms-2">
                                        <div class="fw-semibold small">Scholarship</div>
                                        <div class="text-muted" style="font-size:11px">Financial assistance</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- How did you hear -->
                    <?php if (!empty($sources)): ?>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">How did you hear about us?</label>
                        <select name="source_id" class="form-select form-select-lg">
                            <option value="">Select source</option>
                            <?php foreach ($sources as $src): ?>
                            <option value="<?= $src['id'] ?>" <?= $v('source_id') == $src['id'] ? 'selected' : '' ?>>
                                <?= e($src['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Reference name -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Reference Name <span class="text-muted fw-normal">(if any)</span></label>
                        <input type="text" name="reference_name" class="form-control form-control-lg"
                               value="<?= e($v('reference_name')) ?>"
                               placeholder="Name of person who referred you" maxlength="150">
                    </div>

                    <!-- Message -->
                    <div class="col-12">
                        <label class="form-label fw-semibold">Message / Specific Query</label>
                        <textarea name="message" class="form-control" rows="3"
                                  placeholder="Any specific questions about the course, fees, campus, etc.…"><?= e($v('message')) ?></textarea>
                    </div>
                </div>

                <!-- Summary box -->
                <div class="alert mt-4 mb-0 p-3" style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px">
                    <div class="fw-semibold mb-2" style="color:#2c3e8c"><i class="fas fa-clipboard-check me-1"></i>Enquiry Summary</div>
                    <div class="row g-1 small text-muted">
                        <div class="col-sm-6"><i class="fas fa-graduation-cap me-1"></i>Course: <strong id="summCourse">—</strong></div>
                        <div class="col-sm-6"><i class="fas fa-user me-1"></i>Name: <strong id="summName">—</strong></div>
                        <div class="col-sm-6"><i class="fas fa-phone me-1"></i>Phone: <strong id="summPhone">—</strong></div>
                        <div class="col-sm-6"><i class="fas fa-envelope me-1"></i>Email: <strong id="summEmail">—</strong></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 px-4 pb-4 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary px-4" id="step4Back">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </button>
                <button type="submit" class="btn btn-primary px-5" id="submitBtn">
                    <i class="fas fa-paper-plane me-1"></i>Submit Enquiry
                </button>
            </div>
        </div>
    </div>

</form>

<script>
(function () {
    const URL_DEPTS   = '<?= url('enquire/ajax/departments') ?>';
    const URL_COURSES = '<?= url('enquire/ajax/courses') ?>';
    let currentStep = 1;
    const totalSteps = 4;

    // ── UTM params from URL ──
    const urlParams = new URLSearchParams(window.location.search);
    const utmCampaign = urlParams.get('utm_campaign') || urlParams.get('campaign') || '';
    document.getElementById('utmCampaign').value = utmCampaign;

    // ── Step navigation ──
    function goTo(step) {
        document.querySelectorAll('.step-panel').forEach((p, i) => {
            p.classList.toggle('active', i + 1 === step);
        });
        document.querySelectorAll('.enq-step').forEach((s, i) => {
            s.classList.remove('active', 'done');
            if (i + 1 < step) s.classList.add('done');
            if (i + 1 === step) s.classList.add('active');
        });
        const pct = (step / totalSteps) * 100;
        document.getElementById('enqProgressBar').style.width = pct + '%';
        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Institution card selection ──
    document.querySelectorAll('.inst-card').forEach(card => {
        card.addEventListener('click', function () {
            document.querySelectorAll('.inst-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const id = this.dataset.id;
            document.getElementById('institutionId').value = id;
            loadDepartments(id);
            loadCourses(id, '');
        });
    });

    // ── Department filter change ──
    const deptSelect = document.getElementById('departmentSelect');
    if (deptSelect) {
        deptSelect.addEventListener('change', function () {
            const instId = document.getElementById('institutionId').value;
            if (instId) loadCourses(instId, this.value);
        });
    }

    // ── Load departments via AJAX ──
    function loadDepartments(instId) {
        fetch(`${URL_DEPTS}?institution_id=${instId}`)
            .then(r => r.json())
            .then(data => {
                const sel = document.getElementById('departmentSelect');
                const row = document.getElementById('deptRow');
                if (!data.length) { row.style.display = 'none'; return; }
                sel.innerHTML = '<option value="">All Departments</option>';
                data.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.id;
                    opt.textContent = d.name;
                    sel.appendChild(opt);
                });
                row.style.display = '';
            })
            .catch(() => {});
    }

    // ── Load courses via AJAX ──
    function loadCourses(instId, deptId) {
        const grid    = document.getElementById('courseGrid');
        const spinner = document.getElementById('courseSpinner');
        const loadMsg = document.getElementById('courseLoadingMsg');
        const noMsg   = document.getElementById('noCourseMsg');
        const deptHint = document.getElementById('deptFallbackHint');

        loadMsg.style.display = 'none';
        noMsg.style.display   = 'none';
        if (deptHint) deptHint.style.display = 'none';
        spinner.style.display = 'block';
        grid.innerHTML = '';

        fetch(`${URL_COURSES}?institution_id=${instId}&department_id=${deptId || ''}`)
            .then(r => r.json())
            .then(data => {
                spinner.style.display = 'none';
                if (!data.length && deptId) {
                    // Dept filter returned nothing — load all courses as fallback
                    loadCourses(instId, '');
                    return;
                }
                if (!data.length) { noMsg.style.display = 'block'; return; }
                data.forEach(c => grid.appendChild(buildCourseCard(c)));
                bindCourseCards();
                // Restore previously selected course card after AJAX reload
                const sel = document.getElementById('courseInterestedId').value;
                if (sel) {
                    const card = grid.querySelector(`.course-card[data-id="${sel}"]`);
                    if (card) {
                        card.classList.add('selected');
                        document.getElementById('selectedCourseName').textContent = card.dataset.name;
                        document.getElementById('selectedCoursePill').style.display = 'block';
                    }
                }
            })
            .catch(() => { spinner.style.display = 'none'; noMsg.style.display = 'block'; });
    }

    const degreeLabels = {
        diploma:'Diploma', ug:'UG', pg:'PG', phd:'PhD',
        certificate:'Certificate', other:'Other'
    };

    function buildCourseCard(c) {
        const div = document.createElement('div');
        div.className = 'course-card';
        div.dataset.id       = c.id;
        div.dataset.dept     = c.department_id || 0;
        div.dataset.name     = c.name;
        div.dataset.degree   = c.degree_type || '';
        div.dataset.duration = c.duration_years || '';
        div.dataset.fees     = c.fees_per_year || '';

        const badges = [
            `<span class="course-badge bg-primary bg-opacity-10 text-primary">${degreeLabels[c.degree_type] || c.degree_type || ''}</span>`,
            c.duration_years ? `<span class="course-badge bg-secondary bg-opacity-10 text-secondary">${c.duration_years} yr</span>` : '',
            c.fees_per_year  ? `<span class="course-badge bg-success bg-opacity-10 text-success">₹${parseInt(c.fees_per_year).toLocaleString('en-IN')}/yr</span>` : '',
        ].join('');

        div.innerHTML = `
            <div class="fw-semibold small mb-1">${c.name}</div>
            ${c.dept_name ? `<div class="text-muted" style="font-size:11px"><i class="fas fa-sitemap me-1"></i>${c.dept_name}</div>` : ''}
            <div class="mt-2 d-flex gap-1 flex-wrap">${badges}</div>
        `;
        return div;
    }

    function bindCourseCards() {
        document.querySelectorAll('.course-card').forEach(card => {
            card.addEventListener('click', function () {
                document.querySelectorAll('.course-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                const id   = this.dataset.id;
                const name = this.dataset.name;
                document.getElementById('courseInterestedId').value = id;
                document.getElementById('selectedCourseName').textContent = name;
                document.getElementById('selectedCoursePill').style.display = 'block';
                updateSummary();
            });
        });
    }
    bindCourseCards(); // also bind static server-rendered cards

    // ── Auto-load courses on page load if institution is already set ──
    // This fires when there is only one institution (hidden field pre-filled)
    // or when returning after a validation failure (institution_id in old flash)
    (function autoBootstrap() {
        const instId  = document.getElementById('institutionId')?.value;
        const hasGrid = document.querySelectorAll('.course-card').length > 0;

        if (instId && !hasGrid) {
            // No server-rendered cards — fetch them now
            loadDepartments(instId);
            loadCourses(instId, '');
        }

        // Also mark the matching institution card selected if any
        if (instId) {
            document.querySelectorAll('.inst-card').forEach(c => {
                c.classList.toggle('selected', c.dataset.id === instId);
            });
        }
    })();

    // ── Clear course selection ──
    document.getElementById('clearCourse')?.addEventListener('click', function () {
        document.getElementById('courseInterestedId').value = '';
        document.querySelectorAll('.course-card').forEach(c => c.classList.remove('selected'));
        document.getElementById('selectedCoursePill').style.display = 'none';
    });

    // Pre-fill pill if course already selected (after validation failure)
    const preCourseId = document.getElementById('courseInterestedId').value;
    if (preCourseId) {
        const preCard = document.querySelector(`.course-card[data-id="${preCourseId}"]`);
        if (preCard) {
            preCard.classList.add('selected');
            document.getElementById('selectedCourseName').textContent = preCard.dataset.name;
            document.getElementById('selectedCoursePill').style.display = 'block';
        }
    }

    // ── Summary updater ──
    function updateSummary() {
        const courseName  = document.getElementById('selectedCourseName')?.textContent || '—';
        const firstName   = document.querySelector('[name="first_name"]')?.value || '';
        const lastName    = document.querySelector('[name="last_name"]')?.value  || '';
        const phone       = document.querySelector('[name="phone"]')?.value      || '';
        const email       = document.querySelector('[name="email"]')?.value      || '';

        document.getElementById('summCourse').textContent = courseName || '—';
        document.getElementById('summName').textContent   = [firstName, lastName].filter(Boolean).join(' ') || '—';
        document.getElementById('summPhone').textContent  = phone  || '—';
        document.getElementById('summEmail').textContent  = email  || '—';
    }

    // ── Step 1 → Step 2 ──
    document.getElementById('step1Next').addEventListener('click', () => {
        const instId   = document.getElementById('institutionId').value;
        const courseId = document.getElementById('courseInterestedId').value;

        if (!instId) {
            showStepError('step1', 'Please select an institution.');
            return;
        }
        if (!courseId) {
            showStepError('step1', 'Please select a course you are interested in.');
            return;
        }
        clearStepError('step1');
        goTo(2);
    });

    // ── Step 2 → Step 3 ──
    document.getElementById('step2Next').addEventListener('click', () => {
        const firstName = document.querySelector('[name="first_name"]').value.trim();
        const phone     = document.querySelector('[name="phone"]').value.trim();
        const email     = document.querySelector('[name="email"]').value.trim();

        if (!firstName) { showStepError('step2', 'First name is required.'); return; }
        if (!phone)      { showStepError('step2', 'Mobile number is required.'); return; }
        if (phone && !/^[\d\s\+\-\(\)]{7,20}$/.test(phone)) {
            showStepError('step2', 'Please enter a valid phone number.'); return;
        }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showStepError('step2', 'Please enter a valid email address.'); return;
        }
        clearStepError('step2');
        goTo(3);
    });

    // ── Step 3 → Step 4 ──
    document.getElementById('step3Next').addEventListener('click', () => {
        clearStepError('step3');
        updateSummary();
        goTo(4);
    });

    // ── Back buttons ──
    document.getElementById('step2Back').addEventListener('click', () => goTo(1));
    document.getElementById('step3Back').addEventListener('click', () => goTo(2));
    document.getElementById('step4Back').addEventListener('click', () => goTo(3));

    // ── Submit spinner ──
    document.getElementById('enquiryForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting…';
    });

    // ── Error helper ──
    function showStepError(stepId, msg) {
        let el = document.getElementById(stepId + 'Error');
        if (!el) {
            el = document.createElement('div');
            el.id = stepId + 'Error';
            el.className = 'alert alert-danger alert-dismissible mt-3';
            document.getElementById(stepId).querySelector('.card-footer').before(el);
        }
        el.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>${msg}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>`;
    }

    function clearStepError(stepId) {
        document.getElementById(stepId + 'Error')?.remove();
    }

    // ── If form had errors (server-side), jump to step 1 ──
    const hasErrors = document.querySelectorAll('.alert-danger').length > 0;
    if (hasErrors) goTo(1);

})();
</script>
