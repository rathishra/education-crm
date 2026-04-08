<?php $pageTitle = 'Edit Admission — ' . e($admission['admission_number']); ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-edit me-2 text-primary"></i>Edit Admission</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admissions') ?>">Admissions</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admissions/' . $admission['id']) ?>"><?= e($admission['admission_number']) ?></a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('admissions/' . $admission['id']) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>

<?php
function adm($field, array $admission, $default = '') {
    $o = old($field);
    return $o !== null && $o !== '' ? $o : ($admission[$field] ?? $default);
}
?>

<form method="POST" action="<?= url('admissions/' . $admission['id']) ?>">
    <?= csrfField() ?>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="editTabs">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-personal"><i class="fas fa-user me-1"></i>Personal</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-family"><i class="fas fa-users me-1"></i>Family</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-education"><i class="fas fa-graduation-cap me-1"></i>Education</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-academic"><i class="fas fa-book me-1"></i>Academic</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-fees"><i class="fas fa-rupee-sign me-1"></i>Fees</a></li>
    </ul>

    <div class="tab-content">

        <!-- ── TAB 1: Personal ─────────────────────────────── -->
        <div class="tab-pane fade show active" id="tab-personal">
            <div class="row g-4">
                <div class="col-lg-8">

                    <!-- Basic Info -->
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-id-card me-2"></i>Basic Information</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" value="<?= e(adm('first_name', $admission)) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" value="<?= e(adm('last_name', $admission)) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        <?php foreach (['male'=>'Male','female'=>'Female','other'=>'Other'] as $v=>$l): ?>
                                            <option value="<?= $v ?>" <?= adm('gender', $admission) === $v ? 'selected' : '' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="date_of_birth" id="dob" class="form-control" value="<?= e(adm('date_of_birth', $admission)) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Age</label>
                                    <input type="text" id="ageDisplay" class="form-control bg-light" readonly placeholder="Auto">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="">Select</option>
                                        <?php foreach (['general'=>'General','obc'=>'OBC','sc'=>'SC','st'=>'ST','ews'=>'EWS','minority'=>'Minority','other'=>'Other'] as $v=>$l): ?>
                                            <option value="<?= $v ?>" <?= adm('category', $admission) === $v ? 'selected' : '' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" value="<?= e(adm('nationality', $admission, 'Indian')) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <option value="">Select</option>
                                        <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $bg): ?>
                                            <option value="<?= $bg ?>" <?= adm('blood_group', $admission) === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Mother Tongue</label>
                                    <input type="text" name="mother_tongue" class="form-control" value="<?= e(adm('mother_tongue', $admission)) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Religion</label>
                                    <input type="text" name="religion" class="form-control" value="<?= e(adm('religion', $admission)) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Community</label>
                                    <input type="text" name="community" class="form-control" value="<?= e(adm('community', $admission)) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Place of Birth</label>
                                    <input type="text" name="place_of_birth" class="form-control" value="<?= e(adm('place_of_birth', $admission)) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Sibling in College</label>
                                    <input type="text" name="sibling_in_college" class="form-control" value="<?= e(adm('sibling_in_college', $admission)) ?>" placeholder="Name / Roll No.">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-phone me-2"></i>Contact Details</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="<?= e(adm('phone', $admission)) ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Code</label>
                                    <input type="text" name="country_code" class="form-control" value="<?= e(adm('country_code', $admission, '+91')) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">WhatsApp Number</label>
                                    <input type="text" name="whatsapp_number" class="form-control" value="<?= e(adm('whatsapp_number', $admission)) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?= e(adm('email', $admission)) ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Aadhaar Number</label>
                                    <input type="text" name="aadhaar_number" class="form-control" maxlength="14" value="<?= e(adm('aadhaar_number', $admission)) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Communication Address -->
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-map-marker-alt me-2"></i>Communication Address</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <input type="text" name="address_line1" class="form-control" placeholder="Address Line 1" value="<?= e(adm('address_line1', $admission)) ?>">
                                </div>
                                <div class="col-12">
                                    <input type="text" name="address_line2" class="form-control" placeholder="Address Line 2" value="<?= e(adm('address_line2', $admission)) ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="city" class="form-control" placeholder="City" value="<?= e(adm('city', $admission)) ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="state" class="form-control" placeholder="State" value="<?= e(adm('state', $admission)) ?>">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="pincode" class="form-control" placeholder="Pincode" value="<?= e(adm('pincode', $admission)) ?>">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="country" class="form-control" placeholder="Country" value="<?= e(adm('country', $admission, 'India')) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permanent Address -->
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <span><i class="fas fa-home me-2"></i>Permanent Address</span>
                            <div class="form-check mb-0">
                                <input type="checkbox" name="permanent_same_as_comm" id="permSame" class="form-check-input" value="1" <?= adm('permanent_same_as_comm', $admission, 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="permSame">Same as Communication</label>
                            </div>
                        </div>
                        <div class="card-body" id="permanentFields" style="<?= adm('permanent_same_as_comm', $admission, 1) ? 'display:none' : '' ?>">
                            <div class="row g-3">
                                <div class="col-12">
                                    <input type="text" name="permanent_address_line1" class="form-control" placeholder="Address Line 1" value="<?= e(adm('permanent_address_line1', $admission)) ?>">
                                </div>
                                <div class="col-12">
                                    <input type="text" name="permanent_address_line2" class="form-control" placeholder="Address Line 2" value="<?= e(adm('permanent_address_line2', $admission)) ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="permanent_city" class="form-control" placeholder="City" value="<?= e(adm('permanent_city', $admission)) ?>">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="permanent_state" class="form-control" placeholder="State" value="<?= e(adm('permanent_state', $admission)) ?>">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="permanent_pincode" class="form-control" placeholder="Pincode" value="<?= e(adm('permanent_pincode', $admission)) ?>">
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="permanent_country" class="form-control" placeholder="Country" value="<?= e(adm('permanent_country', $admission, 'India')) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-lg-4">
                    <!-- Programme Preferences -->
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-sliders-h me-2"></i>Programme Preferences</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Programme Level</label>
                                <select name="programme_level" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach (['UG'=>'UG','PG'=>'PG','Diploma'=>'Diploma','Certificate'=>'Certificate','PhD'=>'PhD'] as $v=>$l): ?>
                                        <option value="<?= $v ?>" <?= adm('programme_level', $admission) === $v ? 'selected' : '' ?>><?= $l ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Domain / Stream</label>
                                <input type="text" name="domain" class="form-control" value="<?= e(adm('domain', $admission)) ?>" placeholder="e.g. Science, Commerce">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">1st Course Preference</label>
                                <select name="course_preference_1" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= adm('course_preference_1', $admission) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">2nd Course Preference</label>
                                <select name="course_preference_2" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= adm('course_preference_2', $admission) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">3rd Course Preference</label>
                                <select name="course_preference_3" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= adm('course_preference_3', $admission) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">Hostel Required?</label>
                                <select name="hostel_required" class="form-select">
                                    <option value="0" <?= !adm('hostel_required', $admission) ? 'selected' : '' ?>>No</option>
                                    <option value="1" <?= adm('hostel_required', $admission) ? 'selected' : '' ?>>Yes</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Transport Required?</label>
                                <select name="transport_required" class="form-select">
                                    <option value="0" <?= !adm('transport_required', $admission) ? 'selected' : '' ?>>No</option>
                                    <option value="1" <?= adm('transport_required', $admission) ? 'selected' : '' ?>>Yes</option>
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Nearest Bus Stop</label>
                                <input type="text" name="nearest_bus_stop" class="form-control" value="<?= e(adm('nearest_bus_stop', $admission)) ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Counselor -->
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-headset me-2"></i>Counselor</div>
                        <div class="card-body">
                            <select name="counselor_id" class="form-select">
                                <option value="">Not Assigned</option>
                                <?php foreach ($counselors as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= adm('counselor_id', $admission) == $c['id'] ? 'selected' : '' ?>>
                                        <?= e($c['first_name'] . ' ' . $c['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TAB 2: Family ───────────────────────────────── -->
        <div class="tab-pane fade" id="tab-family">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-primary bg-opacity-10"><i class="fas fa-male me-2"></i>Father's Details</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Father's Name</label>
                                    <input type="text" name="father_name" class="form-control" value="<?= e(adm('father_name', $admission)) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Father's Phone</label>
                                    <input type="text" name="father_phone" class="form-control" value="<?= e(adm('father_phone', $admission)) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" name="father_occupation" class="form-control" value="<?= e(adm('father_occupation', $admission)) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Annual Income</label>
                                    <select name="father_annual_income" class="form-select">
                                        <option value="">Select Range</option>
                                        <?php foreach (['Below 1 Lakh','1-3 Lakhs','3-5 Lakhs','5-10 Lakhs','Above 10 Lakhs'] as $inc): ?>
                                            <option value="<?= $inc ?>" <?= adm('father_annual_income', $admission) === $inc ? 'selected' : '' ?>><?= $inc ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header bg-success bg-opacity-10"><i class="fas fa-female me-2"></i>Mother's Details</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Mother's Name</label>
                                    <input type="text" name="mother_name" class="form-control" value="<?= e(adm('mother_name', $admission)) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mother's Phone</label>
                                    <input type="text" name="mother_phone" class="form-control" value="<?= e(adm('mother_phone', $admission)) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" name="mother_occupation" class="form-control" value="<?= e(adm('mother_occupation', $admission)) ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Annual Income</label>
                                    <select name="mother_annual_income" class="form-select">
                                        <option value="">Select Range</option>
                                        <?php foreach (['Below 1 Lakh','1-3 Lakhs','3-5 Lakhs','5-10 Lakhs','Above 10 Lakhs'] as $inc): ?>
                                            <option value="<?= $inc ?>" <?= adm('mother_annual_income', $admission) === $inc ? 'selected' : '' ?>><?= $inc ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><i class="fas fa-user-shield me-2"></i>Guardian Details</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Guardian Name</label>
                                <input type="text" name="guardian_name" class="form-control" value="<?= e(adm('guardian_name', $admission)) ?>">
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Guardian Phone</label>
                                <input type="text" name="guardian_phone" class="form-control" value="<?= e(adm('guardian_phone', $admission)) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TAB 3: Education ─────────────────────────────── -->
        <div class="tab-pane fade" id="tab-education">

            <!-- SSLC / 10th -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-school me-2"></i>SSLC / 10th Standard</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">School Name</label>
                            <input type="text" name="sslc_school_name" class="form-control" value="<?= e(adm('sslc_school_name', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input type="text" name="sslc_state" class="form-control" value="<?= e(adm('sslc_state', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City / District</label>
                            <input type="text" name="sslc_city" class="form-control" value="<?= e(adm('sslc_city', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Board</label>
                            <input type="text" name="sslc_board" class="form-control" value="<?= e(adm('sslc_board', $admission)) ?>" placeholder="CBSE / State Board">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Medium</label>
                            <input type="text" name="sslc_medium" class="form-control" value="<?= e(adm('sslc_medium', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Year of Passing</label>
                            <input type="text" name="sslc_year_of_passing" class="form-control" value="<?= e(adm('sslc_year_of_passing', $admission)) ?>" placeholder="2022">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Marks</label>
                            <input type="number" name="sslc_max_marks" id="sslcMax" class="form-control" min="0" value="<?= e(adm('sslc_max_marks', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Marks Obtained</label>
                            <input type="number" name="sslc_marks_obtained" id="sslcObtained" class="form-control" min="0" value="<?= e(adm('sslc_marks_obtained', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Percentage</label>
                            <input type="number" name="sslc_percentage" id="sslcPct" step="0.01" class="form-control bg-light" value="<?= e(adm('sslc_percentage', $admission)) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- HSC / 12th -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-university me-2"></i>HSC / 12th Standard</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">School / College Name</label>
                            <input type="text" name="hsc_school_name" class="form-control" value="<?= e(adm('hsc_school_name', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input type="text" name="hsc_state" class="form-control" value="<?= e(adm('hsc_state', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">District</label>
                            <input type="text" name="hsc_district" class="form-control" value="<?= e(adm('hsc_district', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Board</label>
                            <input type="text" name="hsc_board" class="form-control" value="<?= e(adm('hsc_board', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Medium</label>
                            <input type="text" name="hsc_medium" class="form-control" value="<?= e(adm('hsc_medium', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Group / Stream</label>
                            <input type="text" name="hsc_group" class="form-control" value="<?= e(adm('hsc_group', $admission)) ?>" placeholder="Science / Arts / Commerce">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Result Status</label>
                            <select name="hsc_result_status" class="form-select">
                                <option value="">Select</option>
                                <?php foreach (['passed'=>'Passed','appearing'=>'Appearing','failed'=>'Failed'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= adm('hsc_result_status', $admission) === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Reg. No.</label>
                            <input type="text" name="hsc_registration_no" class="form-control" value="<?= e(adm('hsc_registration_no', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Marks</label>
                            <input type="number" name="hsc_max_marks" id="hscMax" class="form-control" min="0" value="<?= e(adm('hsc_max_marks', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Marks Obtained</label>
                            <input type="number" name="hsc_marks_obtained" id="hscObtained" class="form-control" min="0" value="<?= e(adm('hsc_marks_obtained', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Percentage</label>
                            <input type="number" name="hsc_percentage" id="hscPct" step="0.01" class="form-control bg-light" value="<?= e(adm('hsc_percentage', $admission)) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Previous / UG Qualification -->
            <div class="card">
                <div class="card-header"><i class="fas fa-graduation-cap me-2"></i>Previous / UG Qualification</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Qualification</label>
                            <input type="text" name="previous_qualification" class="form-control" value="<?= e(adm('previous_qualification', $admission)) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Percentage / CGPA</label>
                            <input type="number" name="previous_percentage" step="0.01" min="0" max="100" class="form-control" value="<?= e(adm('previous_percentage', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Year of Passing</label>
                            <input type="number" name="previous_year_of_passing" min="1990" max="<?= date('Y') ?>" class="form-control" value="<?= e(adm('previous_year_of_passing', $admission)) ?>">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Previous Institution</label>
                            <input type="text" name="previous_institution" class="form-control" value="<?= e(adm('previous_institution', $admission)) ?>">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── TAB 4: Academic ─────────────────────────────── -->
        <div class="tab-pane fade" id="tab-academic">
            <div class="card">
                <div class="card-header"><i class="fas fa-book me-2"></i>Academic Placement</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= adm('department_id', $admission) == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <select name="course_id" id="courseSelect" class="form-select" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= adm('course_id', $admission) == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Batch</label>
                            <select name="batch_id" id="batchSelect" class="form-select">
                                <option value="">Select Batch</option>
                                <?php foreach ($batches as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= adm('batch_id', $admission) == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Academic Year</label>
                            <select name="academic_year_id" class="form-select">
                                <option value="">Select Year</option>
                                <?php foreach ($academicYears as $ay): ?>
                                    <option value="<?= $ay['id'] ?>" <?= adm('academic_year_id', $admission) == $ay['id'] ? 'selected' : '' ?>><?= e($ay['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Admission Type</label>
                            <select name="admission_type" class="form-select">
                                <?php foreach (['regular'=>'Regular','lateral'=>'Lateral Entry','transfer'=>'Transfer','management'=>'Management','nri'=>'NRI'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= adm('admission_type', $admission, 'regular') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quota</label>
                            <select name="quota" class="form-select">
                                <?php foreach (['general'=>'General','management'=>'Management','government'=>'Government','scholarship'=>'Scholarship','nri'=>'NRI'] as $v=>$l): ?>
                                    <option value="<?= $v ?>" <?= adm('quota', $admission, 'general') === $v ? 'selected' : '' ?>><?= $l ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Specialization</label>
                            <input type="text" name="specialization" class="form-control" value="<?= e(adm('specialization', $admission)) ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Semester</label>
                            <select name="current_semester" class="form-select">
                                <?php for ($s = 1; $s <= 8; $s++): ?>
                                    <option value="<?= $s ?>" <?= (int)adm('current_semester', $admission, 1) === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Application Source</label>
                            <input type="text" name="application_source" class="form-control" value="<?= e(adm('application_source', $admission)) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"><?= e(adm('remarks', $admission)) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TAB 5: Fees ─────────────────────────────────── -->
        <div class="tab-pane fade" id="tab-fees">
            <div class="row g-4 justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header"><i class="fas fa-rupee-sign me-2"></i>Fee Details</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Total Fee (₹)</label>
                                <input type="number" name="total_fee" id="totalFee" step="0.01" min="0" class="form-control" value="<?= e(adm('total_fee', $admission, 0)) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Discount Amount (₹)</label>
                                <input type="number" name="discount_amount" id="discountAmt" step="0.01" min="0" class="form-control" value="<?= e(adm('discount_amount', $admission, 0)) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Scholarship Amount (₹)</label>
                                <input type="number" name="scholarship_amount" id="scholarshipAmt" step="0.01" min="0" class="form-control" value="<?= e(adm('scholarship_amount', $admission, 0)) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Final Fee (₹)</label>
                                <input type="number" id="finalFeeDisplay" class="form-control bg-light fw-bold" readonly value="<?= e($admission['final_fee'] ?? 0) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Initial Payment Required (₹)</label>
                                <input type="number" name="initial_payment" step="0.01" min="0" class="form-control" value="<?= e(adm('initial_payment', $admission, 0)) ?>">
                            </div>
                            <div>
                                <label class="form-label">Payment Due Date</label>
                                <input type="date" name="payment_due_date" class="form-control" value="<?= e(adm('payment_due_date', $admission)) ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /tab-content -->

    <!-- Save Bar -->
    <div class="d-flex justify-content-end gap-2 mt-4 pb-4">
        <a href="<?= url('admissions/' . $admission['id']) ?>" class="btn btn-outline-secondary px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-5">
            <i class="fas fa-save me-2"></i>Save Changes
        </button>
    </div>

</form>

<script>
// Age auto-calc
document.getElementById('dob').addEventListener('change', function () {
    if (!this.value) return;
    const today = new Date(), dob = new Date(this.value);
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
    document.getElementById('ageDisplay').value = age + ' yrs';
});
document.getElementById('dob').dispatchEvent(new Event('change'));

// Permanent address toggle
document.getElementById('permSame').addEventListener('change', function () {
    document.getElementById('permanentFields').style.display = this.checked ? 'none' : '';
});

// Fee recalc
function recalcFee() {
    const t = parseFloat(document.getElementById('totalFee').value) || 0;
    const d = parseFloat(document.getElementById('discountAmt').value) || 0;
    const s = parseFloat(document.getElementById('scholarshipAmt').value) || 0;
    document.getElementById('finalFeeDisplay').value = Math.max(0, t - d - s).toFixed(2);
}
['totalFee','discountAmt','scholarshipAmt'].forEach(id =>
    document.getElementById(id).addEventListener('input', recalcFee));
recalcFee();

// SSLC percentage auto-calc
function calcPct(maxId, obtId, pctId) {
    document.getElementById(obtId).addEventListener('input', function () {
        const max = parseFloat(document.getElementById(maxId).value) || 0;
        const obt = parseFloat(this.value) || 0;
        if (max > 0) document.getElementById(pctId).value = (obt / max * 100).toFixed(2);
    });
}
calcPct('sslcMax','sslcObtained','sslcPct');
calcPct('hscMax','hscObtained','hscPct');

// Batch cascade
const courseSelect = document.getElementById('courseSelect');
const batchSelect  = document.getElementById('batchSelect');
const currentBatch = <?= (int)($admission['batch_id'] ?? 0) ?>;

courseSelect.addEventListener('change', function () {
    batchSelect.innerHTML = '<option value="">Select Batch</option>';
    if (!this.value) return;
    fetch('<?= url('admissions/ajax/batches') ?>?course_id=' + this.value)
        .then(r => r.json())
        .then(data => {
            data.forEach(b => {
                const o = document.createElement('option');
                o.value = b.id; o.textContent = b.name;
                if (b.id == currentBatch) o.selected = true;
                batchSelect.appendChild(o);
            });
        });
});
</script>
