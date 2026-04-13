<?php
$isEdit    = !is_null($department);
$pageTitle = $isEdit ? 'Edit Department' : 'Add Department';
$action    = $isEdit ? url("departments/{$department['id']}") : url('departments');

// Helper: get value from old input → department → default
$v = function (string $key, $default = '') use ($department) {
    $old = getFlash('old_input.' . $key, null);
    if ($old !== null) return $old;
    return $department[$key] ?? $default;
};
$chk = fn(string $key, int $def = 1) => ($v($key, $def) ? 'checked' : '');

// Lookup maps for user dropdowns
$userMap = [];
foreach ($users ?? [] as $u) {
    $userMap[$u['id']] = trim("{$u['first_name']} {$u['last_name']}") . " ({$u['email']})";
}
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-sitemap me-2"></i><?= $pageTitle ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('departments') ?>">Departments</a></li>
                <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Add' ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url('departments') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<!-- Validation errors -->
<?php $formErrors = (array)(getFlash('errors') ?? []); ?>
<?php if (!empty($formErrors)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <strong><i class="fas fa-exclamation-triangle me-1"></i>Please fix the following errors:</strong>
    <ul class="mb-0 mt-1 ps-3">
        <?php foreach ($formErrors as $fe): ?><li><?= e(is_array($fe) ? implode(', ', $fe) : $fe) ?></li><?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" id="deptForm">
    <?= csrfField() ?>

    <!-- Tab Nav -->
    <ul class="nav nav-tabs nav-tabs-bordered mb-0" id="deptTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-basic"><i class="fas fa-info-circle me-1"></i>Basic Info</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-hod"><i class="fas fa-user-tie me-1"></i>HOD & Contact</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-academic"><i class="fas fa-graduation-cap me-1"></i>Academic</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-staff"><i class="fas fa-users me-1"></i>Staff</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-programs"><i class="fas fa-book me-1"></i>Programs</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-infra"><i class="fas fa-building me-1"></i>Infrastructure</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-finance"><i class="fas fa-rupee-sign me-1"></i>Finance</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-lms"><i class="fas fa-chalkboard me-1"></i>LMS & Settings</a></li>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom bg-white p-4 shadow-sm" id="deptTabContent">

        <!-- ══════════════════════════════════════
             TAB 1 – BASIC INFORMATION
        ══════════════════════════════════════ -->
        <div class="tab-pane fade show active" id="tab-basic">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Institution <span class="text-danger">*</span></label>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="institution_id" value="<?= $department['institution_id'] ?>">
                        <input type="text" class="form-control" value="<?= e($department['institution_name'] ?? '') ?>" disabled>
                    <?php else: ?>
                        <select name="institution_id" class="form-select" required>
                            <option value="">— Select Institution —</option>
                            <?php foreach ($institutions ?? [] as $inst): ?>
                            <option value="<?= $inst['id'] ?>" <?= $v('institution_id') == $inst['id'] ? 'selected' : '' ?>><?= e($inst['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="code" value="<?= e($department['code']) ?>">
                        <input type="text" class="form-control bg-light" value="<?= e($department['code']) ?>" disabled>
                    <?php else: ?>
                        <input type="text" name="code" class="form-control text-uppercase" value="<?= e($v('code')) ?>" placeholder="e.g. CSE" maxlength="50" required>
                    <?php endif; ?>
                    <small class="text-muted">Unique per institution. Cannot be changed after creation.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="active"   <?= $v('status', 'active')   === 'active'   ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $v('status', 'active')   === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= e($v('name')) ?>" placeholder="e.g. Computer Science & Engineering" maxlength="255" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Department Type <span class="text-danger">*</span></label>
                    <select name="department_type" class="form-select" required>
                        <option value="academic"       <?= $v('department_type', 'academic') === 'academic'       ? 'selected' : '' ?>>Academic</option>
                        <option value="administrative" <?= $v('department_type', 'academic') === 'administrative' ? 'selected' : '' ?>>Administrative</option>
                        <option value="research"       <?= $v('department_type', 'academic') === 'research'       ? 'selected' : '' ?>>Research</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Parent Faculty / School</label>
                    <select name="parent_department_id" class="form-select">
                        <option value="">— None (Top-level) —</option>
                        <?php foreach ($parentDepts ?? [] as $pd): ?>
                        <option value="<?= $pd['id'] ?>" <?= $v('parent_department_id') == $pd['id'] ? 'selected' : '' ?>>
                            <?= e($pd['name']) ?> (<?= e($pd['code']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Established Year</label>
                    <input type="number" name="established_year" class="form-control" value="<?= e($v('established_year')) ?>" placeholder="e.g. 1998" min="1800" max="<?= date('Y') + 1 ?>">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Brief overview of the department's focus and objectives…"><?= e($v('description')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             TAB 2 – HOD & CONTACT
        ══════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-hod">
            <div class="row g-3">
                <div class="col-12"><h6 class="text-muted fw-semibold border-bottom pb-2 mb-1"><i class="fas fa-user-tie me-2"></i>Head of Department</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">HOD Name</label>
                    <input type="text" name="hod_name" class="form-control" value="<?= e($v('hod_name')) ?>" placeholder="Full name of HOD">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">HOD (Staff Lookup)</label>
                    <select name="hod_employee_id" class="form-select">
                        <option value="">— Select from Staff —</option>
                        <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $v('hod_employee_id') == $u['id'] ? 'selected' : '' ?>>
                            <?= e(trim("{$u['first_name']} {$u['last_name']}")) ?> (<?= e($u['email']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Linked staff account for portal login.</small>
                </div>

                <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2 mb-1"><i class="fas fa-phone me-2"></i>Contact Details</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Department Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="dept_email" class="form-control" value="<?= e($v('dept_email')) ?>" placeholder="dept@college.edu">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Phone</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="dept_phone" class="form-control" value="<?= e($v('dept_phone')) ?>" placeholder="+91 XXXXX XXXXX">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Extension Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                        <input type="text" name="extension_number" class="form-control" value="<?= e($v('extension_number')) ?>" placeholder="e.g. 204">
                    </div>
                </div>

                <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2 mb-1"><i class="fas fa-map-marker-alt me-2"></i>Office Location</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Block / Building</label>
                    <input type="text" name="office_block" class="form-control" value="<?= e($v('office_block')) ?>" placeholder="e.g. Block A">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Floor</label>
                    <input type="text" name="office_floor" class="form-control" value="<?= e($v('office_floor')) ?>" placeholder="e.g. 2nd Floor">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Room Number</label>
                    <input type="text" name="office_room" class="form-control" value="<?= e($v('office_room')) ?>" placeholder="e.g. 201">
                </div>

                <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2 mb-1"><i class="fas fa-user-friends me-2"></i>Alternate Contact</h6></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Alternate Contact Name</label>
                    <input type="text" name="alt_contact_name" class="form-control" value="<?= e($v('alt_contact_name')) ?>" placeholder="Name">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Alternate Contact Phone</label>
                    <input type="text" name="alt_contact_phone" class="form-control" value="<?= e($v('alt_contact_phone')) ?>" placeholder="+91 XXXXX XXXXX">
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             TAB 3 – ACADEMIC CONFIGURATION
        ══════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-academic">
            <div class="row g-3">
                <div class="col-12"><h6 class="text-muted fw-semibold border-bottom pb-2 mb-1"><i class="fas fa-calendar-alt me-2"></i>Academic Pattern</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Semester Pattern</label>
                    <select name="semester_pattern" class="form-select">
                        <option value="semester"  <?= $v('semester_pattern', 'semester') === 'semester'  ? 'selected' : '' ?>>Semester</option>
                        <option value="trimester" <?= $v('semester_pattern', 'semester') === 'trimester' ? 'selected' : '' ?>>Trimester</option>
                        <option value="annual"    <?= $v('semester_pattern', 'semester') === 'annual'    ? 'selected' : '' ?>>Annual</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Grading Scheme</label>
                    <input type="text" name="grading_scheme" class="form-control" value="<?= e($v('grading_scheme')) ?>" placeholder="e.g. 10-point CGPA, Letter Grade">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="credit_system" id="creditSystem" <?= $chk('credit_system') ?>>
                        <label class="form-check-label fw-semibold" for="creditSystem">Credit System Enabled</label>
                    </div>
                </div>

                <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2 mb-1"><i class="fas fa-users me-2"></i>Capacity & Admission</h6></div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Intake Capacity</label>
                    <input type="number" name="intake_capacity" class="form-control" value="<?= e($v('intake_capacity')) ?>" placeholder="Total seats" min="0">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Admission Quota</label>
                    <input type="text" name="admission_quota" class="form-control" value="<?= e($v('admission_quota')) ?>" placeholder="e.g. General 60%, OBC 27%, SC 13%">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Counselling Code</label>
                    <input type="text" name="counselling_code" class="form-control" value="<?= e($v('counselling_code')) ?>" placeholder="e.g. TNEA-1234">
                </div>

                <div class="col-12 mt-2"><h6 class="text-muted fw-semibold border-bottom pb-2 mb-1"><i class="fas fa-user-cog me-2"></i>Key Role Assignments</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Department Coordinator</label>
                    <select name="coordinator_id" class="form-select">
                        <option value="">— Not Assigned —</option>
                        <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $v('coordinator_id') == $u['id'] ? 'selected' : '' ?>>
                            <?= e(trim("{$u['first_name']} {$u['last_name']}")) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Exam Coordinator</label>
                    <select name="exam_coordinator_id" class="form-select">
                        <option value="">— Not Assigned —</option>
                        <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $v('exam_coordinator_id') == $u['id'] ? 'selected' : '' ?>>
                            <?= e(trim("{$u['first_name']} {$u['last_name']}")) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Timetable Incharge</label>
                    <select name="timetable_incharge_id" class="form-select">
                        <option value="">— Not Assigned —</option>
                        <?php foreach ($users ?? [] as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= $v('timetable_incharge_id') == $u['id'] ? 'selected' : '' ?>>
                            <?= e(trim("{$u['first_name']} {$u['last_name']}")) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             TAB 4 – STAFF MAPPING
        ══════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-staff">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted fw-semibold mb-0"><i class="fas fa-users me-2"></i>Assigned Staff</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addStaffRow">
                    <i class="fas fa-plus me-1"></i>Add Staff
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle" id="staffTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50%">Staff Member</th>
                            <th style="width:35%">Type</th>
                            <th class="text-center" style="width:15%">Remove</th>
                        </tr>
                    </thead>
                    <tbody id="staffBody">
                    <?php if (!empty($staff)): ?>
                        <?php foreach ($staff as $si => $s): ?>
                        <tr>
                            <td>
                                <select name="staff_ids[]" class="form-select form-select-sm">
                                    <option value="">— Select Staff —</option>
                                    <?php foreach ($users ?? [] as $u): ?>
                                    <option value="<?= $u['id'] ?>" <?= $s['user_id'] == $u['id'] ? 'selected' : '' ?>>
                                        <?= e(trim("{$u['first_name']} {$u['last_name']}")) ?> (<?= e($u['email']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="staff_types[]" class="form-select form-select-sm">
                                    <option value="teaching"     <?= $s['staff_type'] === 'teaching'     ? 'selected' : '' ?>>Teaching</option>
                                    <option value="non_teaching" <?= $s['staff_type'] === 'non_teaching' ? 'selected' : '' ?>>Non-Teaching</option>
                                </select>
                            </td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="staffEmptyRow"><td colspan="3" class="text-center text-muted py-3">No staff assigned. Click "Add Staff" to begin.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Staff row template (hidden) -->
            <template id="staffRowTpl">
                <tr>
                    <td>
                        <select name="staff_ids[]" class="form-select form-select-sm">
                            <option value="">— Select Staff —</option>
                            <?php foreach ($users ?? [] as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e(trim("{$u['first_name']} {$u['last_name']}")) ?> (<?= e($u['email']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="staff_types[]" class="form-select form-select-sm">
                            <option value="teaching">Teaching</option>
                            <option value="non_teaching">Non-Teaching</option>
                        </select>
                    </td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                </tr>
            </template>
        </div>

        <!-- ══════════════════════════════════════
             TAB 5 – PROGRAMS OFFERED
        ══════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-programs">
            <?php if (empty($courseTypes)): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No Course Types found. <a href="<?= url('course-types/create') ?>" target="_blank">Create Course Types</a> first to add programs.
            </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted fw-semibold mb-0"><i class="fas fa-book me-2"></i>Programs Offered</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addProgramRow" <?= empty($courseTypes) ? 'disabled' : '' ?>>
                    <i class="fas fa-plus me-1"></i>Add Program
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle" id="programTable">
                    <thead class="table-light">
                        <tr>
                            <th>Course Type</th>
                            <th style="width:130px">Degree Type</th>
                            <th style="width:90px">Intake</th>
                            <th class="text-center" style="width:70px">Remove</th>
                        </tr>
                    </thead>
                    <tbody id="programBody">
                    <?php if (!empty($programs)): ?>
                        <?php foreach ($programs as $prog): ?>
                        <tr>
                            <td>
                                <select name="programs[][program_name]" class="form-select form-select-sm">
                                    <option value="">— Select Course Type —</option>
                                    <?php foreach ($courseTypes as $ct): ?>
                                    <option value="<?= e($ct['code']) ?>"
                                            data-degree="<?= e($ct['degree_type']) ?>"
                                            <?= $prog['program_name'] === $ct['code'] ? 'selected' : '' ?>>
                                        <?= e($ct['code']) ?> — <?= e($ct['description']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="programs[][degree_type]" class="form-select form-select-sm">
                                    <option value="full_time" <?= $prog['degree_type'] === 'full_time' ? 'selected' : '' ?>>Full Time</option>
                                    <option value="part_time" <?= $prog['degree_type'] === 'part_time' ? 'selected' : '' ?>>Part Time</option>
                                    <option value="distance"  <?= $prog['degree_type'] === 'distance'  ? 'selected' : '' ?>>Distance</option>
                                </select>
                            </td>
                            <td><input type="number" name="programs[][intake_seats]" class="form-control form-control-sm" value="<?= e($prog['intake_seats']) ?>" min="0" placeholder="Seats"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="programEmptyRow"><td colspan="4" class="text-center text-muted py-3">No programs added. Click "Add Program".</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <!-- Template rendered server-side with full course type options -->
            <template id="programRowTpl">
                <tr>
                    <td>
                        <select name="programs[][program_name]" class="form-select form-select-sm ct-select">
                            <option value="">— Select Course Type —</option>
                            <?php foreach ($courseTypes as $ct): ?>
                            <option value="<?= e($ct['code']) ?>" data-degree="<?= e($ct['degree_type']) ?>">
                                <?= e($ct['code']) ?> — <?= e($ct['description']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="programs[][degree_type]" class="form-select form-select-sm">
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="distance">Distance</option>
                        </select>
                    </td>
                    <td><input type="number" name="programs[][intake_seats]" class="form-control form-control-sm" min="0" placeholder="Seats"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                </tr>
            </template>
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Manage Course Types under <a href="<?= url('course-types') ?>" target="_blank">Academic Setup → Course Types</a></small>
        </div>

        <!-- ══════════════════════════════════════
             TAB 6 – INFRASTRUCTURE
        ══════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-infra">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted fw-semibold mb-0"><i class="fas fa-building me-2"></i>Rooms & Labs</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addRoomRow">
                    <i class="fas fa-plus me-1"></i>Add Room
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle" id="roomTable">
                    <thead class="table-light">
                        <tr>
                            <th>Room Name</th>
                            <th style="width:130px">Type</th>
                            <th style="width:100px">Block</th>
                            <th style="width:80px">Floor</th>
                            <th style="width:80px">Room No.</th>
                            <th style="width:80px">Capacity</th>
                            <th class="text-center" style="width:60px">Proj.</th>
                            <th class="text-center" style="width:50px">AC</th>
                            <th class="text-center" style="width:60px">Del</th>
                        </tr>
                    </thead>
                    <tbody id="roomBody">
                    <?php if (!empty($rooms)): ?>
                        <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><input type="text" name="rooms[][room_name]" class="form-control form-control-sm" value="<?= e($room['room_name']) ?>"></td>
                            <td>
                                <select name="rooms[][room_type]" class="form-select form-select-sm">
                                    <?php foreach (['classroom'=>'Classroom','lab'=>'Lab','office'=>'Office','seminar_hall'=>'Seminar Hall','library'=>'Library','other'=>'Other'] as $rv=>$rl): ?>
                                    <option value="<?= $rv ?>" <?= $room['room_type'] === $rv ? 'selected' : '' ?>><?= $rl ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" name="rooms[][block]" class="form-control form-control-sm" value="<?= e($room['block']) ?>"></td>
                            <td><input type="text" name="rooms[][floor]" class="form-control form-control-sm" value="<?= e($room['floor']) ?>"></td>
                            <td><input type="text" name="rooms[][room_number]" class="form-control form-control-sm" value="<?= e($room['room_number']) ?>"></td>
                            <td><input type="number" name="rooms[][capacity]" class="form-control form-control-sm" value="<?= e($room['capacity']) ?>" min="0"></td>
                            <td class="text-center"><input type="checkbox" name="rooms[][has_projector]" class="form-check-input" <?= $room['has_projector'] ? 'checked' : '' ?>></td>
                            <td class="text-center"><input type="checkbox" name="rooms[][has_ac]" class="form-check-input" <?= $room['has_ac'] ? 'checked' : '' ?>></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr id="roomEmptyRow"><td colspan="9" class="text-center text-muted py-3">No rooms added. Click "Add Room".</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <template id="roomRowTpl">
                <tr>
                    <td><input type="text" name="rooms[][room_name]" class="form-control form-control-sm" placeholder="Room name"></td>
                    <td>
                        <select name="rooms[][room_type]" class="form-select form-select-sm">
                            <option value="classroom">Classroom</option><option value="lab">Lab</option><option value="office">Office</option>
                            <option value="seminar_hall">Seminar Hall</option><option value="library">Library</option><option value="other">Other</option>
                        </select>
                    </td>
                    <td><input type="text" name="rooms[][block]" class="form-control form-control-sm" placeholder="Block"></td>
                    <td><input type="text" name="rooms[][floor]" class="form-control form-control-sm" placeholder="Floor"></td>
                    <td><input type="text" name="rooms[][room_number]" class="form-control form-control-sm" placeholder="No."></td>
                    <td><input type="number" name="rooms[][capacity]" class="form-control form-control-sm" min="0" placeholder="0"></td>
                    <td class="text-center"><input type="checkbox" name="rooms[][has_projector]" class="form-check-input"></td>
                    <td class="text-center"><input type="checkbox" name="rooms[][has_ac]" class="form-check-input"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="fas fa-times"></i></button></td>
                </tr>
            </template>
        </div>

        <!-- ══════════════════════════════════════
             TAB 7 – FINANCE MAPPING
        ══════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-finance">
            <div class="row g-3">
                <div class="col-12"><h6 class="text-muted fw-semibold border-bottom pb-2 mb-1"><i class="fas fa-rupee-sign me-2"></i>ERP Finance Mapping</h6></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Financial Year</label>
                    <input type="text" name="financial_year" class="form-control" value="<?= e($finance['financial_year'] ?? (date('Y') . '-' . substr((string)(date('Y')+1),-2))) ?>" placeholder="e.g. 2024-25">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cost Center Code</label>
                    <input type="text" name="cost_center" class="form-control" value="<?= e($finance['cost_center'] ?? '') ?>" placeholder="e.g. CC-CSE-001">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Budget Allocation (₹)</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" name="budget_allocation" class="form-control" value="<?= e($finance['budget_allocation'] ?? '') ?>" placeholder="0.00" min="0" step="0.01">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Expense Head</label>
                    <input type="text" name="expense_head" class="form-control" value="<?= e($finance['expense_head'] ?? '') ?>" placeholder="e.g. Academic Expenses">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Revenue Account</label>
                    <input type="text" name="revenue_account" class="form-control" value="<?= e($finance['revenue_account'] ?? '') ?>" placeholder="e.g. RA-CSE-FEE">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Fee Category Mapping</label>
                    <input type="text" name="fee_category" class="form-control" value="<?= e($finance['fee_category'] ?? '') ?>" placeholder="e.g. UG-Tech-Tuition">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Finance Notes</label>
                    <textarea name="finance_notes" class="form-control" rows="2" placeholder="Any additional finance notes…"><?= e($finance['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════
             TAB 8 – LMS & SETTINGS
        ══════════════════════════════════════ -->
        <div class="tab-pane fade" id="tab-lms">
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-muted fw-semibold border-bottom pb-2 mb-3"><i class="fas fa-chalkboard me-2"></i>LMS Integration</h6>
                    <?php $lmsToggles = [
                        'lms_allow_course_creation' => ['Allow Course Creation',        'Instructors can create and publish new courses'],
                        'lms_attendance_required'   => ['Attendance Required',           'Students must meet minimum attendance to qualify for exams'],
                        'lms_internal_marks'        => ['Internal Marks Enabled',        'Internal assessment marks recorded in LMS'],
                        'lms_lab_courses'           => ['Lab Courses Enabled',           'Department has practical/lab components'],
                        'lms_project_dissertation'  => ['Project / Dissertation',        'Final year project or dissertation module active'],
                        'lms_hod_approval'          => ['HOD Course Approval Workflow',  'New courses require HOD approval before publishing'],
                    ]; ?>
                    <?php foreach ($lmsToggles as $key => [$label, $hint]): ?>
                    <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                        <div>
                            <div class="fw-semibold"><?= $label ?></div>
                            <small class="text-muted"><?= $hint ?></small>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" name="<?= $key ?>" id="<?= $key ?>" <?= $chk($key) ?>>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted fw-semibold border-bottom pb-2 mb-3"><i class="fas fa-lock me-2"></i>Workflow & Permissions</h6>
                    <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                        <div>
                            <div class="fw-semibold">Allow HOD Login</div>
                            <small class="text-muted">HOD can log into department portal with elevated access</small>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" name="allow_hod_login" id="allow_hod_login" <?= $chk('allow_hod_login', 0) ?>>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                        <div>
                            <div class="fw-semibold">Department Approval Required</div>
                            <small class="text-muted">Admissions to this department require departmental approval</small>
                        </div>
                        <div class="form-check form-switch ms-3">
                            <input class="form-check-input" type="checkbox" name="approval_required" id="approval_required" <?= $chk('approval_required', 0) ?>>
                        </div>
                    </div>

                    <h6 class="text-muted fw-semibold border-bottom pb-2 mb-3 mt-4"><i class="fas fa-info-circle me-2"></i>Record Info</h6>
                    <?php if ($isEdit): ?>
                    <table class="table table-sm table-borderless small text-muted">
                        <tr><th>Created By</th><td><?= $department['created_by'] ?? '—' ?></td></tr>
                        <tr><th>Updated By</th><td><?= $department['updated_by'] ?? '—' ?></td></tr>
                        <tr><th>Created At</th><td><?= $department['created_at'] ?? '—' ?></td></tr>
                        <tr><th>Updated At</th><td><?= $department['updated_at'] ?? '—' ?></td></tr>
                    </table>
                    <?php else: ?>
                    <p class="text-muted small">Record info will be available after saving.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /tab-content -->

    <!-- Footer Actions -->
    <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
        <a href="<?= url('departments') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-times me-1"></i>Cancel
        </a>
        <?php if ($isEdit): ?>
        <a href="<?= url("departments/{$department['id']}") ?>" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i>View Dashboard
        </a>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary px-4">
            <i class="fas fa-save me-1"></i><?= $isEdit ? 'Update Department' : 'Create Department' ?>
        </button>
    </div>
</form>

<script>
// ── Dynamic row add/remove ──────────────────────────
function addRow(btnId, tplId, bodyId, emptyRowId) {
    document.getElementById(btnId).addEventListener('click', function () {
        const tpl   = document.getElementById(tplId);
        const tbody = document.getElementById(bodyId);
        const empty = document.getElementById(emptyRowId);
        if (empty) empty.remove();
        const clone = tpl.content.cloneNode(true);
        tbody.appendChild(clone);
    });
}
addRow('addStaffRow',   'staffRowTpl',   'staffBody',   'staffEmptyRow');
addRow('addProgramRow', 'programRowTpl', 'programBody', 'programEmptyRow');
addRow('addRoomRow',    'roomRowTpl',    'roomBody',    'roomEmptyRow');

// Remove row
document.addEventListener('click', function (e) {
    if (e.target.closest('.remove-row')) {
        e.target.closest('tr').remove();
    }
});

// Auto-set Degree Type when Course Type is selected
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('ct-select')) {
        const sel     = e.target;
        const opt     = sel.options[sel.selectedIndex];
        const degree  = opt.dataset.degree;
        if (degree) {
            const row      = sel.closest('tr');
            const degSel   = row.querySelector('[name="programs[][degree_type]"]');
            if (degSel) {
                degSel.value = degree === 'full_time' ? 'full_time' : degree === 'part_time' ? 'part_time' : 'distance';
            }
        }
    }
});

// Code uppercase
const codeInput = document.querySelector('[name="code"]');
if (codeInput) {
    codeInput.addEventListener('input', function () {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });
}

// Restore active tab after validation failure
const activeTab = sessionStorage.getItem('deptActiveTab');
if (activeTab) {
    const tab = document.querySelector(`[href="${activeTab}"]`);
    if (tab) new bootstrap.Tab(tab).show();
}
document.querySelectorAll('#deptTabs .nav-link').forEach(t => {
    t.addEventListener('shown.bs.tab', e => sessionStorage.setItem('deptActiveTab', e.target.getAttribute('href')));
});
document.getElementById('deptForm').addEventListener('submit', () => sessionStorage.removeItem('deptActiveTab'));
</script>
