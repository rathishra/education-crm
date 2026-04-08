<?php
$pageTitle = 'New Admission Application';
$pre = $prefill ?? [];
function fv(string $key, $def = ''): string {
    return e(old($key) ?: ($GLOBALS['pre'][$key] ?? $def));
}
$incomeRanges = ['0 - 2 LPA','2 - 5 LPA','5 - 8 LPA','8 - 11 LPA','11 - 15 LPA','15 - 20 LPA','Above 20 LPA'];
$bloodGroups  = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
$boards       = ['State Board','CBSE','ICSE','IGCSE','IB','Tamil Nadu Board Of Secondary Education','Others'];
$mediums      = ['English','Tamil','Hindi','Others'];
$groups12     = ['Physics, Chemistry, Maths','Physics, Chemistry, Biology','Commerce with Maths','Commerce without Maths','Arts / Humanities','Vocational','Others'];
$resultStatus = ['Pass','Waiting for result','Fail'];
$states       = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Puducherry'];
?>

<div class="page-header mb-4">
    <div>
        <h1 class="page-title"><i class="fas fa-user-graduate me-2 text-primary"></i>New Admission Application</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admissions') ?>">Admissions</a></li>
                <li class="breadcrumb-item active">New Application</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<!-- Duplicate warning -->
<div id="dupBanner" class="alert alert-warning d-none mb-3">
    <i class="fas fa-exclamation-triangle me-2"></i><span id="dupMsg"></span>
    <a id="dupLink" href="#" class="ms-2 fw-semibold">View existing</a>
</div>

<!-- ── Step Progress Bar ───────────────────────────────────────────── -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <div class="d-flex align-items-center justify-content-between px-2" id="stepNav">
            <?php
            $steps = [
                1 => ['User Registration & Information', 'fas fa-user'],
                2 => ['Education Qualification',         'fas fa-graduation-cap'],
                3 => ['Document Upload & Declaration',   'fas fa-file-upload'],
                4 => ['Declaration',                     'fas fa-check-circle'],
            ];
            $totalSteps = count($steps);
            foreach ($steps as $num => [$label, $icon]):
            ?>
            <div class="d-flex align-items-center flex-grow-1" style="<?= $num < $totalSteps ? 'flex:1' : '' ?>">
                <div class="d-flex align-items-center gap-2 step-badge" data-step="<?= $num ?>">
                    <div class="step-circle rounded-circle d-flex align-items-center justify-content-center fw-bold"
                         style="width:32px;height:32px;font-size:.8rem;
                                background:<?= $num===1?'#6366f1':'#e2e8f0' ?>;
                                color:<?= $num===1?'#fff':'#94a3b8' ?>;
                                transition:all .3s">
                        <?= $num ?>
                    </div>
                    <span class="small fw-semibold d-none d-md-inline" style="color:<?= $num===1?'#1e293b':'#94a3b8' ?>;white-space:nowrap"><?= $label ?></span>
                </div>
                <?php if ($num < $totalSteps): ?>
                <div class="step-connector flex-grow-1 mx-2" style="height:2px;background:#e2e8f0;border-radius:1px"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<form method="POST" action="<?= url('admissions') ?>" id="admissionForm" enctype="multipart/form-data">
    <?= csrfField() ?>
    <?php if (!empty($leadId)): ?><input type="hidden" name="lead_id" value="<?= (int)$leadId ?>"><?php endif; ?>

    <!-- ════════════════════════════════════════════════════════════
         STEP 1 — User Registration & Information
    ═══════════════════════════════════════════════════════════════ -->
    <div class="wizard-step" id="step1">

        <!-- User Registration -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom">
                <span class="fw-bold"><i class="fas fa-id-card me-2 text-primary"></i>User Registration</span>
                <span class="small text-muted"><i class="fas fa-clock me-1"></i><span id="timer">00:00:00</span></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Application Number</label>
                        <input type="text" class="form-control bg-light" value="Auto Generated" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="first_name" id="firstNameInput" class="form-control" placeholder="First Name" value="<?= fv('first_name') ?>" required>
                            <input type="text" name="last_name" class="form-control" placeholder="Last Name" value="<?= fv('last_name') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email ID <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="emailInput" class="form-control" value="<?= fv('email') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">WhatsApp Number <span class="text-danger">*</span></label>
                        <input type="text" name="whatsapp_number" class="form-control" value="<?= fv('whatsapp_number', fv('phone')) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Country Code <span class="text-danger">*</span></label>
                        <select name="country_code" class="form-select">
                            <option value="+91" <?= fv('country_code','+91')==='+91'?'selected':'' ?>>+91</option>
                            <option value="+1">+1</option><option value="+44">+44</option>
                            <option value="+971">+971</option><option value="+65">+65</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" id="phoneInput" class="form-control" value="<?= fv('phone') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" id="dobInput" class="form-control" value="<?= fv('date_of_birth') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Age</label>
                        <input type="text" id="ageDisplay" class="form-control bg-light" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= fv('gender')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Aadhaar Number <span class="text-danger">*</span></label>
                        <input type="text" name="aadhaar_number" class="form-control" maxlength="12" placeholder="12-digit Aadhaar" value="<?= fv('aadhaar_number') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Domain <span class="text-danger">*</span></label>
                        <select name="domain" class="form-select" required>
                            <option value="">Select Domain</option>
                            <?php foreach (['B.E/ B.Tech'=>'B.E/ B.Tech','MBA'=>'MBA','MCA'=>'MCA','B.Sc'=>'B.Sc','M.Sc'=>'M.Sc','BCA'=>'BCA','B.Com'=>'B.Com','M.Com'=>'M.Com','B.Pharm'=>'B.Pharm','M.E/ M.Tech'=>'M.E/ M.Tech','PhD'=>'PhD','Diploma'=>'Diploma','Other'=>'Other'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= fv('domain')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="programme_level" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach (['UG'=>'UG','PG'=>'PG','Diploma'=>'Diploma','Certificate'=>'Certificate','PhD'=>'PhD'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= fv('programme_level')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Do you need a hostel? <span class="text-danger">*</span></label>
                        <select name="hostel_required" class="form-select">
                            <option value="0" <?= !fv('hostel_required')?'selected':'' ?>>No</option>
                            <option value="1" <?= fv('hostel_required')==='1'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nearest MTC Bus Stop <span class="text-danger">*</span></label>
                        <input type="text" name="nearest_bus_stop" class="form-control" value="<?= fv('nearest_bus_stop') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">OTP Status</label>
                        <div class="form-control bg-light text-success fw-semibold">
                            <i class="fas fa-check-circle me-1"></i>Verified
                        </div>
                        <input type="hidden" name="otp_verified" value="1">
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Preference -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white border-bottom fw-bold">
                <i class="fas fa-book me-2 text-primary"></i>Course Preference
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">First Preference <span class="text-danger">*</span></label>
                        <select name="course_preference_1" id="cpref1" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= fv('course_preference_1')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Second Preference</label>
                        <select name="course_preference_2" class="form-select">
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= fv('course_preference_2')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Third Preference</label>
                        <select name="course_preference_3" class="form-select">
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= fv('course_preference_3')==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Actual course_id (required by DB) synced from first preference -->
                    <input type="hidden" name="course_id" id="courseIdHidden" value="<?= fv('course_id') ?: fv('course_preference_1') ?>">
                </div>
            </div>
        </div>

        <!-- Family Details -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white border-bottom fw-bold">
                <i class="fas fa-users me-2 text-primary"></i>Family Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Name of Father <span class="text-danger">*</span></label>
                        <input type="text" name="father_name" class="form-control" value="<?= fv('father_name') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Father's Occupation <span class="text-danger">*</span></label>
                        <input type="text" name="father_occupation" class="form-control" value="<?= fv('father_occupation') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Father's Contact Number <span class="text-danger">*</span></label>
                        <input type="text" name="father_phone" class="form-control" value="<?= fv('father_phone') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Name of Mother</label>
                        <input type="text" name="mother_name" class="form-control" value="<?= fv('mother_name') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mother's Occupation</label>
                        <input type="text" name="mother_occupation" class="form-control" value="<?= fv('mother_occupation') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mother's Contact Number</label>
                        <input type="text" name="mother_phone" class="form-control" value="<?= fv('mother_phone') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Father's Annual Income <span class="text-danger">*</span></label>
                        <select name="father_annual_income" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($incomeRanges as $r): ?>
                            <option value="<?= $r ?>" <?= fv('father_annual_income')===$r?'selected':'' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mother's Annual Income</label>
                        <select name="mother_annual_income" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($incomeRanges as $r): ?>
                            <option value="<?= $r ?>" <?= fv('mother_annual_income')===$r?'selected':'' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Place of Birth <span class="text-danger">*</span></label>
                        <input type="text" name="place_of_birth" class="form-control" value="<?= fv('place_of_birth') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sibling studying in college?</label>
                        <select name="sibling_in_college" class="form-select">
                            <option value="No">No</option>
                            <option value="Yes" <?= fv('sibling_in_college')==='Yes'?'selected':'' ?>>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Blood Group <span class="text-danger">*</span></label>
                        <select name="blood_group" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($bloodGroups as $bg): ?>
                            <option value="<?= $bg ?>" <?= fv('blood_group')===$bg?'selected':'' ?>><?= $bg ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mother Tongue <span class="text-danger">*</span></label>
                        <select name="mother_tongue" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Tamil','Telugu','Kannada','Malayalam','Hindi','English','Urdu','Others'] as $mt): ?>
                            <option value="<?= $mt ?>" <?= fv('mother_tongue')===$mt?'selected':'' ?>><?= $mt ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nationality <span class="text-danger">*</span></label>
                        <select name="nationality" class="form-select">
                            <?php foreach (['Indian'=>'Indian','NRI'=>'NRI','Foreign National'=>'Foreign National'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= fv('nationality','Indian')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Religion <span class="text-danger">*</span></label>
                        <select name="religion" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Hindu','Muslim','Christian','Sikh','Buddhist','Jain','Others'] as $r): ?>
                            <option value="<?= $r ?>" <?= fv('religion')===$r?'selected':'' ?>><?= $r ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Community <span class="text-danger">*</span></label>
                        <select name="community" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['OC'=>'OC (Open/General)','BC'=>'BC (Backward Class)','MBC'=>'MBC (Most Backward Class)','SC'=>'SC (Scheduled Caste)','ST'=>'ST (Scheduled Tribe)','BCM'=>'BCM','Others'=>'Others'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= fv('community')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Category (SC/OBC for reservation) -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['general'=>'General','obc'=>'OBC','sc'=>'SC','st'=>'ST','ews'=>'EWS','minority'=>'Minority','other'=>'Other'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= fv('category')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Communication Address -->
                    <div class="col-12 mt-2">
                        <label class="form-label fw-semibold">Communication Address <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="text" name="address_line1" class="form-control" placeholder="Address Line 1" value="<?= fv('address_line1') ?>">
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="address_line2" class="form-control" placeholder="Address Line 2" value="<?= fv('address_line2') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                        <select name="country" class="form-select">
                            <option value="India">India</option>
                            <option value="Others">Others</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">State <span class="text-danger">*</span></label>
                        <select name="state" class="form-select" id="commState">
                            <option value="">Select State</option>
                            <?php foreach ($states as $st): ?>
                            <option value="<?= $st ?>" <?= fv('state')===$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                        <input type="text" name="city" class="form-control" value="<?= fv('city') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Pincode <span class="text-danger">*</span></label>
                        <input type="text" name="pincode" class="form-control" maxlength="10" value="<?= fv('pincode') ?>">
                    </div>

                    <!-- Permanent Address -->
                    <div class="col-12 mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="permanent_same_as_comm" value="1" id="permYes" <?= (fv('permanent_same_as_comm','1')==='1')?'checked':'' ?>>
                            <label class="form-check-label fw-semibold" for="permYes">Is the Permanent Address the same as the Communication Address?&nbsp;&nbsp;<span class="text-success">Yes</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="permanent_same_as_comm" value="0" id="permNo" <?= (fv('permanent_same_as_comm')==='0')?'checked':'' ?>>
                            <label class="form-check-label" for="permNo">No</label>
                        </div>
                    </div>
                    <div id="permAddressFields" class="col-12" style="display:none">
                        <div class="row g-2">
                            <div class="col-md-6"><input type="text" name="permanent_address_line1" class="form-control" placeholder="Permanent Address Line 1" value="<?= fv('permanent_address_line1') ?>"></div>
                            <div class="col-md-6"><input type="text" name="permanent_address_line2" class="form-control" placeholder="Permanent Address Line 2" value="<?= fv('permanent_address_line2') ?>"></div>
                            <div class="col-md-3 mt-2">
                                <select name="permanent_state" class="form-select"><option value="">Select State</option><?php foreach ($states as $st): ?><option value="<?= $st ?>" <?= fv('permanent_state')===$st?'selected':'' ?>><?= $st ?></option><?php endforeach; ?></select>
                            </div>
                            <div class="col-md-3 mt-2"><input type="text" name="permanent_city" class="form-control" placeholder="City" value="<?= fv('permanent_city') ?>"></div>
                            <div class="col-md-3 mt-2"><input type="text" name="permanent_pincode" class="form-control" placeholder="Pincode" value="<?= fv('permanent_pincode') ?>"></div>
                            <div class="col-md-3 mt-2"><input type="text" name="permanent_country" class="form-control" placeholder="Country" value="<?= fv('permanent_country','India') ?>"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin fields (Counselor, Academic, Fees) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white border-bottom fw-bold">
                <i class="fas fa-cog me-2 text-secondary"></i>Administrative Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Assigned Counselor</label>
                        <select name="counselor_id" class="form-select">
                            <option value="">Not Assigned</option>
                            <?php foreach ($counselors as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= fv('counselor_id')==$c['id']?'selected':'' ?>><?= e(($c['first_name']??'').' '.($c['last_name']??'')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Batch</label>
                        <select name="batch_id" id="batchSelect" class="form-select">
                            <option value="">Select Batch</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Academic Year</label>
                        <select name="academic_year_id" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($academicYears as $ay): ?>
                            <option value="<?= $ay['id'] ?>" <?= fv('academic_year_id')==$ay['id']?'selected':'' ?>><?= e($ay['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Admission Type</label>
                        <select name="admission_type" class="form-select">
                            <?php foreach (['regular'=>'Regular','lateral'=>'Lateral Entry','management'=>'Management','scholarship'=>'Scholarship','nri'=>'NRI'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= fv('admission_type','regular')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Quota</label>
                        <select name="quota" class="form-select">
                            <?php foreach (['general'=>'General','management'=>'Management','government'=>'Government','scholarship'=>'Scholarship','nri'=>'NRI'] as $v=>$l): ?>
                            <option value="<?= $v ?>" <?= fv('quota','general')===$v?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Application Source</label>
                        <input type="text" name="application_source" class="form-control" placeholder="Website, Walk-in…" value="<?= fv('application_source') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="<?= fv('remarks') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1 Navigation -->
        <div class="d-flex justify-content-end gap-2 mb-4">
            <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()"><i class="fas fa-save me-1"></i>Save as Draft</button>
            <button type="button" class="btn btn-primary px-4" onclick="goStep(2)"><i class="fas fa-arrow-right me-1"></i>Next</button>
        </div>
    </div><!-- /step1 -->

    <!-- ════════════════════════════════════════════════════════════
         STEP 2 — Education Qualification
    ═══════════════════════════════════════════════════════════════ -->
    <div class="wizard-step d-none" id="step2">

        <!-- SSLC / 10th -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white border-bottom fw-bold d-flex justify-content-between">
                <span><i class="fas fa-school me-2 text-primary"></i>SSLC / 10TH Details</span>
                <span class="small text-muted"><i class="fas fa-clock me-1"></i><span id="timer2">00:00:00</span></span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">School Name <span class="text-danger">*</span></label>
                        <input type="text" name="sslc_school_name" class="form-control" value="<?= fv('sslc_school_name') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">State <span class="text-danger">*</span></label>
                        <select name="sslc_state" class="form-select">
                            <option value="">Select State</option>
                            <?php foreach ($states as $st): ?>
                            <option value="<?= $st ?>" <?= fv('sslc_state')===$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                        <input type="text" name="sslc_city" class="form-control" value="<?= fv('sslc_city') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Board Of Study <span class="text-danger">*</span></label>
                        <select name="sslc_board" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($boards as $b): ?>
                            <option value="<?= $b ?>" <?= fv('sslc_board')===$b?'selected':'' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Medium of Instruction <span class="text-danger">*</span></label>
                        <select name="sslc_medium" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($mediums as $m): ?>
                            <option value="<?= $m ?>" <?= fv('sslc_medium')===$m?'selected':'' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Year of Passing <span class="text-danger">*</span></label>
                        <input type="number" name="sslc_year_of_passing" class="form-control" min="2000" max="<?= date('Y') ?>" value="<?= fv('sslc_year_of_passing') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Total Maximum Marks <span class="text-danger">*</span></label>
                        <input type="number" name="sslc_max_marks" id="sslcMax" class="form-control" min="1" oninput="calcPct('sslc')" value="<?= fv('sslc_max_marks') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Total Marks Obtained <span class="text-danger">*</span></label>
                        <input type="number" name="sslc_marks_obtained" id="sslcObtained" class="form-control" min="0" oninput="calcPct('sslc')" value="<?= fv('sslc_marks_obtained') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Percentage</label>
                        <input type="text" id="sslcPct" name="sslc_percentage" class="form-control bg-light" readonly value="<?= fv('sslc_percentage') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- HSC / 12th -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white border-bottom fw-bold">
                <i class="fas fa-university me-2 text-primary"></i>HSC / 12TH Details
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">School Name <span class="text-danger">*</span></label>
                        <input type="text" name="hsc_school_name" class="form-control" value="<?= fv('hsc_school_name') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">State <span class="text-danger">*</span></label>
                        <select name="hsc_state" class="form-select">
                            <option value="">Select State</option>
                            <?php foreach ($states as $st): ?>
                            <option value="<?= $st ?>" <?= fv('hsc_state')===$st?'selected':'' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">District/City <span class="text-danger">*</span></label>
                        <input type="text" name="hsc_district" class="form-control" value="<?= fv('hsc_district') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Board Of Study <span class="text-danger">*</span></label>
                        <select name="hsc_board" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($boards as $b): ?>
                            <option value="<?= $b ?>" <?= fv('hsc_board')===$b?'selected':'' ?>><?= $b ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Medium of Instruction <span class="text-danger">*</span></label>
                        <select name="hsc_medium" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($mediums as $m): ?>
                            <option value="<?= $m ?>" <?= fv('hsc_medium')===$m?'selected':'' ?>><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Group <span class="text-danger">*</span></label>
                        <select name="hsc_group" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($groups12 as $g): ?>
                            <option value="<?= $g ?>" <?= fv('hsc_group')===$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Result Status <span class="text-danger">*</span></label>
                        <select name="hsc_result_status" class="form-select">
                            <option value="">Select</option>
                            <?php foreach ($resultStatus as $rs): ?>
                            <option value="<?= $rs ?>" <?= fv('hsc_result_status')===$rs?'selected':'' ?>><?= $rs ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">12th Registration Number</label>
                        <input type="text" name="hsc_registration_no" class="form-control" value="<?= fv('hsc_registration_no') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Total Maximum Marks</label>
                        <input type="number" name="hsc_max_marks" id="hscMax" class="form-control" min="1" oninput="calcPct('hsc')" value="<?= fv('hsc_max_marks') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Total Marks Obtained</label>
                        <input type="number" name="hsc_marks_obtained" id="hscObtained" class="form-control" min="0" oninput="calcPct('hsc')" value="<?= fv('hsc_marks_obtained') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Percentage</label>
                        <input type="text" id="hscPct" name="hsc_percentage" class="form-control bg-light" readonly value="<?= fv('hsc_percentage') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <button type="button" class="btn btn-outline-secondary" onclick="goStep(1)"><i class="fas fa-arrow-left me-1"></i>Back</button>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()"><i class="fas fa-save me-1"></i>Save as Draft</button>
                <button type="button" class="btn btn-primary px-4" onclick="goStep(3)"><i class="fas fa-arrow-right me-1"></i>Next</button>
            </div>
        </div>
    </div><!-- /step2 -->

    <!-- ════════════════════════════════════════════════════════════
         STEP 3 — Document Upload & Declaration
    ═══════════════════════════════════════════════════════════════ -->
    <div class="wizard-step d-none" id="step3">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white border-bottom fw-bold">
                <i class="fas fa-file-upload me-2 text-primary"></i>Document Upload
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $docTypes = [
                        'photo'       => 'Passport Size Photo',
                        'aadhaar'     => 'Aadhaar Card',
                        'sslc_cert'   => 'SSLC / 10th Certificate',
                        'hsc_cert'    => 'HSC / 12th Certificate',
                        'transfer'    => 'Transfer Certificate (TC)',
                        'conduct'     => 'Conduct Certificate',
                        'community'   => 'Community Certificate',
                        'income'      => 'Income Certificate',
                        'nativity'    => 'Nativity Certificate',
                    ];
                    foreach ($docTypes as $key => $label):
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label fw-semibold small"><?= $label ?></label>
                        <input type="file" name="doc_<?= $key ?>" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 p-3 bg-light rounded small text-muted">
                    <i class="fas fa-info-circle me-1"></i>Accepted formats: PDF, JPG, PNG. Max 2MB per file. Documents can also be uploaded later from the admission detail page.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <button type="button" class="btn btn-outline-secondary" onclick="goStep(2)"><i class="fas fa-arrow-left me-1"></i>Back</button>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()"><i class="fas fa-save me-1"></i>Save as Draft</button>
                <button type="button" class="btn btn-primary px-4" onclick="goStep(4)"><i class="fas fa-arrow-right me-1"></i>Next</button>
            </div>
        </div>
    </div><!-- /step3 -->

    <!-- ════════════════════════════════════════════════════════════
         STEP 4 — Declaration & Submit
    ═══════════════════════════════════════════════════════════════ -->
    <div class="wizard-step d-none" id="step4">
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white border-bottom fw-bold">
                <i class="fas fa-check-circle me-2 text-success"></i>Declaration
            </div>
            <div class="card-body">
                <!-- Summary -->
                <div class="row g-3 mb-4" id="declarationSummary">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3 text-muted">Application Summary</h6>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted mb-1">Applicant</div>
                            <div class="fw-semibold" id="sumName">—</div>
                            <div class="small text-muted mt-1" id="sumPhone">—</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <div class="small text-muted mb-1">Course Preference</div>
                            <div class="fw-semibold" id="sumCourse1">—</div>
                        </div>
                    </div>
                </div>

                <!-- Declaration text -->
                <div class="border rounded p-3 bg-light mb-3" style="max-height:200px;overflow-y:auto;font-size:.85rem">
                    <p class="mb-2">I hereby declare that all the information provided in this application form is true and correct to the best of my knowledge and belief. I understand that providing false or misleading information may result in disqualification of my application or cancellation of my admission.</p>
                    <p class="mb-2">I agree to abide by all the rules and regulations of the institution and understand that admission is subject to verification of all original documents.</p>
                    <p class="mb-0">I agree to the Terms and Conditions of the institution.</p>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                    <label class="form-check-label fw-semibold" for="agreeTerms">
                        I agree with the Terms &amp; Conditions <span class="text-danger">*</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-4">
            <button type="button" class="btn btn-outline-secondary" onclick="goStep(3)"><i class="fas fa-arrow-left me-1"></i>Back</button>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()"><i class="fas fa-save me-1"></i>Save as Draft</button>
                <button type="submit" class="btn btn-success px-5" id="submitBtn" onclick="return confirmSubmit()">
                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                </button>
            </div>
        </div>
    </div><!-- /step4 -->

</form>

<script>
// ── Step wizard ────────────────────────────────────────────────
let currentStep = 1;

function goStep(n) {
    document.getElementById('step' + currentStep).classList.add('d-none');
    document.getElementById('step' + n).classList.remove('d-none');
    currentStep = n;
    updateStepNav(n);
    window.scrollTo({top: 0, behavior: 'smooth'});
    if (n === 4) fillDeclarationSummary();
}

function updateStepNav(active) {
    document.querySelectorAll('.step-circle').forEach((el, i) => {
        const stepNum = i + 1;
        if (stepNum < active) {
            el.style.background = '#10b981'; el.style.color = '#fff';
            el.innerHTML = '<i class="fas fa-check" style="font-size:.7rem"></i>';
        } else if (stepNum === active) {
            el.style.background = '#6366f1'; el.style.color = '#fff';
            el.innerHTML = stepNum;
        } else {
            el.style.background = '#e2e8f0'; el.style.color = '#94a3b8';
            el.innerHTML = stepNum;
        }
    });
    document.querySelectorAll('.step-connector').forEach((el, i) => {
        el.style.background = (i + 1) < active ? '#10b981' : '#e2e8f0';
    });
    document.querySelectorAll('.step-badge span').forEach((el, i) => {
        el.style.color = (i + 1) <= active ? '#1e293b' : '#94a3b8';
    });
}

// ── Age auto-calculate ─────────────────────────────────────────
document.getElementById('dobInput').addEventListener('change', function() {
    const dob = new Date(this.value);
    if (!isNaN(dob)) {
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        document.getElementById('ageDisplay').value = age;
    }
});

// ── Auto-sync course_id from first preference ─────────────────
document.getElementById('cpref1').addEventListener('change', function() {
    document.getElementById('courseIdHidden').value = this.value;
    loadBatches(this.value);
});

// ── Batch cascade ─────────────────────────────────────────────
function loadBatches(courseId, selectedId) {
    const sel = document.getElementById('batchSelect');
    sel.innerHTML = '<option value="">Select Batch</option>';
    if (!courseId) return;
    fetch('<?= url('admissions/ajax/batches') ?>?course_id=' + courseId)
        .then(r => r.json())
        .then(data => {
            data.forEach(b => {
                const o = document.createElement('option');
                o.value = b.id; o.textContent = b.name;
                if (selectedId && b.id == selectedId) o.selected = true;
                sel.appendChild(o);
            });
        }).catch(() => {});
}
<?php if (!empty($pre['course_id'])): ?>
window.addEventListener('DOMContentLoaded', () => loadBatches(<?= (int)$pre['course_id'] ?>));
<?php endif; ?>

// ── Percentage auto-calculate ─────────────────────────────────
function calcPct(prefix) {
    const max     = parseFloat(document.getElementById(prefix + 'Max').value) || 0;
    const obtained = parseFloat(document.getElementById(prefix + 'Obtained').value) || 0;
    const pctEl   = document.getElementById(prefix + 'Pct');
    pctEl.value   = max > 0 ? (obtained / max * 100).toFixed(2) : '';
}

// ── Permanent address toggle ──────────────────────────────────
document.querySelectorAll('[name="permanent_same_as_comm"]').forEach(r => {
    r.addEventListener('change', function() {
        document.getElementById('permAddressFields').style.display =
            this.value === '0' ? 'block' : 'none';
    });
});
<?php if ((old('permanent_same_as_comm') ?? '1') === '0'): ?>
document.getElementById('permAddressFields').style.display = 'block';
<?php endif; ?>

// ── Duplicate check ───────────────────────────────────────────
function checkDuplicate() {
    const phone = (document.getElementById('phoneInput')?.value || '').trim();
    const email = (document.getElementById('emailInput')?.value || '').trim();
    if (!phone && !email) return;
    fetch('<?= url('admissions/check-duplicate') ?>?phone=' + encodeURIComponent(phone) + '&email=' + encodeURIComponent(email))
        .then(r => r.json())
        .then(data => {
            const banner = document.getElementById('dupBanner');
            if (data.duplicate) {
                document.getElementById('dupMsg').textContent =
                    'Similar application: ' + data.name + ' (' + data.admission_number + ')';
                document.getElementById('dupLink').href = '<?= url('admissions/') ?>' + data.id;
                banner.classList.remove('d-none');
            } else {
                banner.classList.add('d-none');
            }
        }).catch(() => {});
}
document.getElementById('phoneInput')?.addEventListener('blur', checkDuplicate);
document.getElementById('emailInput')?.addEventListener('blur', checkDuplicate);

// ── Timer ────────────────────────────────────────────────────
let startTime = Date.now();
setInterval(() => {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const h = String(Math.floor(elapsed/3600)).padStart(2,'0');
    const m = String(Math.floor((elapsed%3600)/60)).padStart(2,'0');
    const s = String(elapsed%60).padStart(2,'0');
    const t = `${h}:${m}:${s}`;
    ['timer','timer2'].forEach(id => { const el = document.getElementById(id); if(el) el.textContent = t; });
}, 1000);

// ── Declaration summary ───────────────────────────────────────
function fillDeclarationSummary() {
    const fn = document.getElementById('firstNameInput')?.value || '';
    const ln = document.querySelector('[name="last_name"]')?.value || '';
    const ph = document.getElementById('phoneInput')?.value || '';
    const c1 = document.getElementById('cpref1');
    document.getElementById('sumName').textContent = (fn + ' ' + ln).trim() || '—';
    document.getElementById('sumPhone').textContent = ph || '—';
    document.getElementById('sumCourse1').textContent = c1?.options[c1.selectedIndex]?.text || '—';
}

// ── Save as draft ─────────────────────────────────────────────
function saveDraft() {
    document.querySelector('[name="status"]')?.remove();
    const inp = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'status'; inp.value = 'draft';
    document.getElementById('admissionForm').appendChild(inp);
    document.getElementById('admissionForm').submit();
}

// ── Final submit guard ────────────────────────────────────────
function confirmSubmit() {
    if (!document.getElementById('agreeTerms').checked) {
        alert('Please agree to the Terms & Conditions to submit.');
        return false;
    }
    return true;
}
</script>
