<?php
$pageTitle = e($department['name']) . ' — Department';
$typeBadge = [
    'academic'       => ['bg-primary',           'Academic'],
    'administrative' => ['bg-warning text-dark',  'Administrative'],
    'research'       => ['bg-info text-dark',      'Research'],
];
$tb = $typeBadge[$department['department_type']] ?? ['bg-secondary', ucfirst($department['department_type'])];
$programLevelLabels = ['ug'=>'UG','pg'=>'PG','phd'=>'PhD','diploma'=>'Diploma','certificate'=>'Certificate','mphil'=>'M.Phil','other'=>'Other'];
$roomTypeIcons = ['classroom'=>'fa-door-open','lab'=>'fa-flask','office'=>'fa-briefcase','seminar_hall'=>'fa-theater-masks','library'=>'fa-book','other'=>'fa-cube'];
?>

<!-- Page Header -->
<div class="page-header">
    <div>
        <h1 class="d-flex align-items-center gap-2">
            <span class="badge <?= $tb[0] ?> fs-6 fw-normal"><?= $tb[1] ?></span>
            <?= e($department['name']) ?>
            <code class="fs-6 fw-normal text-muted">(<?= e($department['code']) ?>)</code>
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('departments') ?>">Departments</a></li>
                <li class="breadcrumb-item active"><?= e($department['name']) ?></li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <?php if (hasPermission('departments.edit')): ?>
        <a href="<?= url("departments/{$department['id']}/edit") ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= url('departments') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- ── KPI Stats ───────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php $stats = [
        ['val' => $facultyCount,   'lbl' => 'Faculty',   'icon' => 'fa-chalkboard-teacher', 'color' => 'primary'],
        ['val' => $studentCount,   'lbl' => 'Students',  'icon' => 'fa-user-graduate',       'color' => 'success'],
        ['val' => $courseCount,    'lbl' => 'Courses',   'icon' => 'fa-book-open',           'color' => 'info'],
        ['val' => $nonTeachCount,  'lbl' => 'Non-Teaching','icon'=> 'fa-users',              'color' => 'warning'],
        ['val' => count($programs ?? []), 'lbl' => 'Programs', 'icon' => 'fa-graduation-cap', 'color' => 'secondary'],
        ['val' => count($rooms ?? []),    'lbl' => 'Rooms',    'icon' => 'fa-building',       'color' => 'dark'],
    ]; ?>
    <?php foreach ($stats as $s): ?>
    <div class="col-6 col-md-2">
        <div class="card border-0 shadow-sm text-center h-100">
            <div class="card-body py-3">
                <div class="rounded-circle bg-<?= $s['color'] ?> bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:42px;height:42px">
                    <i class="fas <?= $s['icon'] ?> text-<?= $s['color'] ?>"></i>
                </div>
                <div class="fs-4 fw-bold"><?= $s['val'] ?></div>
                <div class="text-muted small"><?= $s['lbl'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Main Grid ──────────────────────────────────── -->
<div class="row g-4">

    <!-- LEFT COLUMN (8/12) -->
    <div class="col-lg-8">

        <!-- Basic Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold"><i class="fas fa-info-circle me-2 text-primary"></i>Basic Information</span>
                <span class="badge <?= $department['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($department['status']) ?></span>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-md-6"><span class="text-muted d-block">Institution</span><strong><?= e($department['institution_name'] ?? '—') ?></strong></div>
                    <div class="col-md-3"><span class="text-muted d-block">Code</span><code><?= e($department['code']) ?></code></div>
                    <div class="col-md-3"><span class="text-muted d-block">Type</span><span class="badge <?= $tb[0] ?>"><?= $tb[1] ?></span></div>
                    <?php if ($department['parent_name']): ?>
                    <div class="col-md-6"><span class="text-muted d-block">Parent Faculty / School</span><strong><?= e($department['parent_name']) ?></strong></div>
                    <?php endif; ?>
                    <?php if ($department['established_year']): ?>
                    <div class="col-md-3"><span class="text-muted d-block">Established</span><strong><?= $department['established_year'] ?></strong></div>
                    <?php endif; ?>
                    <?php if ($department['description']): ?>
                    <div class="col-12"><span class="text-muted d-block">Description</span><?= nl2br(e($department['description'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- HOD & Contact -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-user-tie me-2 text-info"></i>HOD & Contact Details</span>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-md-6"><span class="text-muted d-block">HOD Name</span><strong><?= e($department['hod_name'] ?: '—') ?></strong></div>
                    <div class="col-md-6"><span class="text-muted d-block">Department Email</span>
                        <?php if ($department['dept_email']): ?>
                        <a href="mailto:<?= e($department['dept_email']) ?>"><?= e($department['dept_email']) ?></a>
                        <?php else: ?>—<?php endif; ?>
                    </div>
                    <div class="col-md-4"><span class="text-muted d-block">Phone</span><?= e($department['dept_phone'] ?: '—') ?></div>
                    <div class="col-md-4"><span class="text-muted d-block">Extension</span><?= e($department['extension_number'] ?: '—') ?></div>
                    <div class="col-md-4"><span class="text-muted d-block">Office</span>
                        <?php
                        $loc = array_filter([$department['office_block'], $department['office_floor'], $department['office_room']]);
                        echo $loc ? e(implode(', ', $loc)) : '—';
                        ?>
                    </div>
                    <?php if ($department['alt_contact_name']): ?>
                    <div class="col-md-6"><span class="text-muted d-block">Alternate Contact</span><?= e($department['alt_contact_name']) ?><?= $department['alt_contact_phone'] ? ' — ' . e($department['alt_contact_phone']) : '' ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Programs Offered -->
        <?php if (!empty($programs)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-book me-2 text-success"></i>Programs Offered</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Program</th><th>Level</th><th>Mode</th><th class="text-center">Intake</th></tr></thead>
                    <tbody>
                    <?php foreach ($programs as $prog): ?>
                    <tr>
                        <td><?= e($prog['program_name']) ?></td>
                        <td><span class="badge bg-primary"><?= $programLevelLabels[$prog['program_level']] ?? strtoupper($prog['program_level']) ?></span></td>
                        <td><?= ucfirst(str_replace('_', ' ', $prog['degree_type'])) ?></td>
                        <td class="text-center"><?= $prog['intake_seats'] ?? '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Staff -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="fas fa-users me-2 text-warning"></i>Assigned Staff</span>
                <div>
                    <span class="badge bg-info text-dark me-1"><?= $facultyCount ?> Teaching</span>
                    <span class="badge bg-secondary"><?= $nonTeachCount ?> Non-Teaching</span>
                </div>
            </div>
            <?php if (!empty($staff)): ?>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Type</th></tr></thead>
                    <tbody>
                    <?php foreach ($staff as $s): ?>
                    <tr>
                        <td><?= e(trim("{$s['first_name']} {$s['last_name']}")) ?></td>
                        <td><small class="text-muted"><?= e($s['user_email']) ?></small></td>
                        <td><span class="badge <?= $s['staff_type'] === 'teaching' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst(str_replace('_', ' ', $s['staff_type'])) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card-body text-muted small text-center py-3">No staff assigned.</div>
            <?php endif; ?>
        </div>

        <!-- Infrastructure -->
        <?php if (!empty($rooms)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-building me-2 text-secondary"></i>Infrastructure</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Room</th><th>Type</th><th>Location</th><th class="text-center">Cap.</th><th class="text-center">Proj.</th><th class="text-center">AC</th></tr></thead>
                    <tbody>
                    <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td><i class="fas <?= $roomTypeIcons[$r['room_type']] ?? 'fa-cube' ?> text-muted me-1"></i><?= e($r['room_name']) ?></td>
                        <td><small><?= ucfirst(str_replace('_', ' ', $r['room_type'])) ?></small></td>
                        <td><small><?= implode(', ', array_filter([$r['block'], $r['floor'], $r['room_number']])) ?></small></td>
                        <td class="text-center"><?= $r['capacity'] ?? '—' ?></td>
                        <td class="text-center"><?= $r['has_projector'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>' ?></td>
                        <td class="text-center"><?= $r['has_ac']        ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sub-departments -->
        <?php if (!empty($subDepts)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-project-diagram me-2 text-primary"></i>Sub-Departments</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Code</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($subDepts as $sd): ?>
                    <tr>
                        <td><?= e($sd['name']) ?></td>
                        <td><code><?= e($sd['code']) ?></code></td>
                        <td><span class="badge bg-<?= $sd['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($sd['status']) ?></span></td>
                        <td><a href="<?= url("departments/{$sd['id']}") ?>" class="btn btn-xs btn-outline-secondary btn-sm"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /col-lg-8 -->

    <!-- RIGHT COLUMN (4/12) -->
    <div class="col-lg-4">

        <!-- Academic Config -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-graduation-cap me-2 text-success"></i>Academic Config</span>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-7 text-muted">Semester Pattern</dt>
                    <dd class="col-5"><?= ucfirst($department['semester_pattern'] ?? '—') ?></dd>
                    <dt class="col-7 text-muted">Credit System</dt>
                    <dd class="col-5"><?= $department['credit_system'] ? '<span class="text-success">✓ Yes</span>' : '<span class="text-muted">No</span>' ?></dd>
                    <dt class="col-7 text-muted">Grading Scheme</dt>
                    <dd class="col-5"><?= e($department['grading_scheme'] ?: '—') ?></dd>
                    <dt class="col-7 text-muted">Intake Capacity</dt>
                    <dd class="col-5"><?= $department['intake_capacity'] ?: '—' ?></dd>
                    <dt class="col-7 text-muted">Counselling Code</dt>
                    <dd class="col-5"><?= e($department['counselling_code'] ?: '—') ?></dd>
                    <?php if ($department['admission_quota']): ?>
                    <dt class="col-7 text-muted">Admission Quota</dt>
                    <dd class="col-5 small"><?= e($department['admission_quota']) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- LMS Settings -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-chalkboard me-2 text-info"></i>LMS Settings</span>
            </div>
            <div class="card-body small">
                <?php $lmsItems = [
                    'lms_allow_course_creation' => 'Course Creation',
                    'lms_attendance_required'   => 'Attendance Required',
                    'lms_internal_marks'        => 'Internal Marks',
                    'lms_lab_courses'           => 'Lab Courses',
                    'lms_project_dissertation'  => 'Project / Dissertation',
                    'lms_hod_approval'          => 'HOD Approval Workflow',
                ]; ?>
                <?php foreach ($lmsItems as $key => $label): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted"><?= $label ?></span>
                    <?php if ($department[$key] ?? 0): ?>
                        <span class="badge bg-success">Enabled</span>
                    <?php else: ?>
                        <span class="badge bg-light text-muted border">Disabled</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Finance -->
        <?php if (!empty($finance)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-rupee-sign me-2 text-warning"></i>Finance (<?= e($finance['financial_year'] ?? '') ?>)</span>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-6 text-muted">Cost Center</dt>
                    <dd class="col-6"><?= e($finance['cost_center'] ?: '—') ?></dd>
                    <dt class="col-6 text-muted">Budget</dt>
                    <dd class="col-6"><?= $finance['budget_allocation'] ? '₹' . number_format($finance['budget_allocation'], 2) : '—' ?></dd>
                    <dt class="col-6 text-muted">Expense Head</dt>
                    <dd class="col-6"><?= e($finance['expense_head'] ?: '—') ?></dd>
                    <dt class="col-6 text-muted">Revenue A/C</dt>
                    <dd class="col-6"><?= e($finance['revenue_account'] ?: '—') ?></dd>
                    <dt class="col-6 text-muted">Fee Category</dt>
                    <dd class="col-6"><?= e($finance['fee_category'] ?: '—') ?></dd>
                </dl>
            </div>
        </div>
        <?php endif; ?>

        <!-- Workflow -->
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-lock me-2 text-secondary"></i>Workflow</span>
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">HOD Login</span>
                    <?= $department['allow_hod_login'] ? '<span class="badge bg-success">Allowed</span>' : '<span class="badge bg-secondary">Disabled</span>' ?>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Dept. Approval</span>
                    <?= $department['approval_required'] ? '<span class="badge bg-warning text-dark">Required</span>' : '<span class="badge bg-secondary">Not Required</span>' ?>
                </div>
            </div>
        </div>

        <!-- Audit Log -->
        <?php if (!empty($auditLogs)): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header py-2">
                <span class="fw-semibold"><i class="fas fa-history me-2 text-muted"></i>Recent Activity</span>
            </div>
            <ul class="list-group list-group-flush small">
                <?php foreach ($auditLogs as $log): ?>
                <li class="list-group-item px-3 py-2">
                    <div class="d-flex justify-content-between">
                        <span>
                            <span class="badge bg-<?= $log['action'] === 'create' ? 'success' : ($log['action'] === 'delete' ? 'danger' : 'primary') ?> me-1">
                                <?= ucfirst($log['action']) ?>
                            </span>
                            <?= e(trim(($log['first_name'] ?? '') . ' ' . ($log['last_name'] ?? ''))) ?: 'System' ?>
                        </span>
                        <small class="text-muted"><?= date('d M y H:i', strtotime($log['created_at'])) ?></small>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Danger Zone -->
        <?php if (hasPermission('departments.delete')): ?>
        <div class="card border-danger shadow-sm">
            <div class="card-header py-2 bg-danger bg-opacity-10">
                <span class="fw-semibold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</span>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Deleting a department is permanent and cannot be undone.</p>
                <button class="btn btn-sm btn-outline-danger w-100" id="btnDeleteDept">
                    <i class="fas fa-trash me-1"></i>Delete Department
                </button>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /col-lg-4 -->
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title mb-0">Confirm Delete</h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Delete <strong><?= e($department['name']) ?></strong>? This cannot be undone.</div>
            <div class="modal-footer py-2">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="<?= url("departments/{$department['id']}/delete") ?>">
                    <?= csrfField() ?>
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
<?php if (hasPermission('departments.delete')): ?>
document.getElementById('btnDeleteDept').addEventListener('click', function () {
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
});
<?php endif; ?>
</script>
