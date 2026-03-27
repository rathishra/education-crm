<?php $pageTitle = 'Timetable & Scheduling'; ?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fas fa-calendar-alt me-2 text-primary"></i>Academic Scheduling Workflow</h4>
        <p class="text-muted mb-0 small">Follow the steps for each section: Batch → Enroll Students → Timetable → Attendance</p>
    </div>
    <a href="<?= url('academic/batches/create') ?>" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-1"></i> New Batch
    </a>
</div>

<!-- Workflow Steps Banner -->
<div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%)">
    <div class="card-body py-3">
        <div class="row g-0 text-white text-center">
            <?php
            $wfSteps = [
                ['1','fa-layer-group','Create Batch','Program & term'],
                ['2','fa-object-group','Create Section','Section A, B, C…'],
                ['3','fa-user-plus','Enroll Students','Allocate to sections'],
                ['4','fa-calendar-alt','Set Timetable','Subjects & faculty'],
                ['5','fa-user-check','Mark Attendance','Faculty posts daily'],
            ];
            foreach($wfSteps as $i => $wf): ?>
            <?php if($i > 0): ?>
            <div class="col-auto d-flex align-items-center px-1 opacity-50"><i class="fas fa-chevron-right small"></i></div>
            <?php endif; ?>
            <div class="col">
                <div class="d-flex flex-column align-items-center">
                    <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center mb-1" style="width:34px;height:34px">
                        <span class="fw-bold small"><?= $wf[0] ?></span>
                    </div>
                    <i class="fas <?= $wf[1] ?> mb-1 small"></i>
                    <div class="small fw-bold"><?= $wf[2] ?></div>
                    <div style="font-size:.68rem;opacity:.8"><?= $wf[3] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if(!empty($sections)): ?>

<!-- Section Pipeline Cards -->
<div class="row g-3">
<?php foreach($sections as $s):
    $enrolled  = (int)($s['enrolled_count']  ?? 0);
    $ttSlots   = (int)($s['timetable_slots'] ?? 0);
    $attDays   = (int)($s['attendance_days'] ?? 0);
    $s2 = $enrolled > 0;
    $s3 = $ttSlots  > 0;
    $s4 = $attDays  > 0;
    $done = 1 + (int)$s2 + (int)$s3 + (int)$s4;
    $pct  = round($done / 4 * 100);
    $pColor = $pct === 100 ? 'success' : ($pct >= 75 ? 'info' : ($pct >= 50 ? 'warning' : 'primary'));
?>
<div class="col-12">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold mb-0">
                        <?= e($s['program_name']) ?>
                        <span class="badge bg-secondary ms-1"><?= e($s['batch_term']) ?></span>
                        <span class="badge bg-dark ms-1">SECTION <?= e($s['section_name']) ?></span>
                    </h6>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <div class="progress flex-grow-1" style="height:5px;max-width:120px">
                            <div class="progress-bar bg-<?= $pColor ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $pct ?>% complete</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?php if($s3): ?>
                    <a href="<?= url('academic/timetable/' . $s['id'] . '/view') ?>" class="btn btn-sm btn-light border">
                        <i class="fas fa-eye text-primary me-1"></i>View Timetable
                    </a>
                    <?php endif; ?>
                    <a href="<?= url('academic/timetable/generator?section_id=' . $s['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-<?= $s3 ? 'edit' : 'calendar-plus' ?> me-1"></i><?= $s3 ? 'Edit Timetable' : 'Setup Timetable' ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="row g-0">

                <!-- Step 1: Batch Created -->
                <div class="col border-end">
                    <div class="p-3 text-center">
                        <div class="step-circle bg-success mx-auto mb-2">
                            <i class="fas fa-check text-white small"></i>
                        </div>
                        <div class="small fw-bold text-success">Batch</div>
                        <div class="text-muted" style="font-size:.72rem">Created ✓</div>
                        <a href="<?= url('academic/batches/' . $s['batch_id']) ?>" class="btn-link-xs text-success">Details</a>
                    </div>
                </div>

                <!-- Step 2: Students Enrolled -->
                <div class="col border-end">
                    <div class="p-3 text-center<?= !$s2 ? ' bg-warning bg-opacity-10' : '' ?>">
                        <div class="step-circle bg-<?= $s2 ? 'success' : 'warning' ?> mx-auto mb-2">
                            <?php if($s2): ?>
                            <i class="fas fa-check text-white small"></i>
                            <?php else: ?>
                            <span class="text-white fw-bold small">2</span>
                            <?php endif; ?>
                        </div>
                        <div class="small fw-bold text-<?= $s2 ? 'success' : 'warning' ?>">Students</div>
                        <div class="fw-bold" style="font-size:.8rem;color:<?= $s2 ? '#198754' : '#f59e0b' ?>"><?= $s2 ? $enrolled . ' enrolled' : 'None yet' ?></div>
                        <a href="<?= url('academic/sections/' . $s['id']) ?>" class="btn-link-xs text-<?= $s2 ? 'success' : 'warning' ?>"><?= $s2 ? 'Manage' : 'Enroll →' ?></a>
                    </div>
                </div>

                <!-- Step 3: Timetable -->
                <div class="col border-end">
                    <div class="p-3 text-center<?= !$s3 ? ' bg-primary bg-opacity-10' : '' ?>">
                        <div class="step-circle bg-<?= $s3 ? 'success' : 'primary' ?> mx-auto mb-2">
                            <?php if($s3): ?>
                            <i class="fas fa-check text-white small"></i>
                            <?php else: ?>
                            <span class="text-white fw-bold small">3</span>
                            <?php endif; ?>
                        </div>
                        <div class="small fw-bold text-<?= $s3 ? 'success' : 'primary' ?>">Timetable</div>
                        <div class="fw-bold" style="font-size:.8rem;color:<?= $s3 ? '#198754' : '#3b82f6' ?>"><?= $s3 ? $ttSlots . ' slots' : 'Not set' ?></div>
                        <a href="<?= url('academic/timetable/generator?section_id=' . $s['id']) ?>" class="btn-link-xs text-<?= $s3 ? 'success' : 'primary' ?>"><?= $s3 ? 'Edit' : 'Configure →' ?></a>
                    </div>
                </div>

                <!-- Step 4: Attendance -->
                <div class="col">
                    <div class="p-3 text-center<?= ($s3 && !$s4) ? ' bg-info bg-opacity-10' : '' ?>">
                        <div class="step-circle bg-<?= $s4 ? 'success' : ($s3 ? 'info' : 'secondary') ?> mx-auto mb-2">
                            <?php if($s4): ?>
                            <i class="fas fa-check text-white small"></i>
                            <?php elseif($s3): ?>
                            <span class="text-white fw-bold small">4</span>
                            <?php else: ?>
                            <i class="fas fa-lock text-white" style="font-size:.6rem"></i>
                            <?php endif; ?>
                        </div>
                        <div class="small fw-bold text-<?= $s4 ? 'success' : ($s3 ? 'info' : 'muted') ?>">Attendance</div>
                        <div class="fw-bold" style="font-size:.8rem">
                            <?php if($s4): ?><span class="text-success"><?= $attDays ?> day(s) posted</span>
                            <?php elseif($s3): ?><span class="text-info">Ready to mark!</span>
                            <?php else: ?><span class="text-muted">After timetable</span>
                            <?php endif; ?>
                        </div>
                        <?php if($s3): ?>
                        <a href="<?= url('academic/attendance') ?>" class="btn-link-xs text-<?= $s4 ? 'success' : 'info' ?>"><?= $s4 ? 'View Sessions' : 'Mark Now →' ?></a>
                        <?php else: ?>
                        <span class="btn-link-xs text-muted">Locked</span>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="row g-3 mt-2">
    <div class="col-md-4">
        <a href="<?= url('academic/batches/create') ?>" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle bg-primary-subtle p-3"><i class="fas fa-plus text-primary"></i></div>
                <div><div class="fw-bold small text-dark">New Batch / Cohort</div><div class="text-muted" style="font-size:.75rem">Create a new program batch</div></div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('academic/sections/create') ?>" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle bg-success-subtle p-3"><i class="fas fa-object-group text-success"></i></div>
                <div><div class="fw-bold small text-dark">New Section</div><div class="text-muted" style="font-size:.75rem">Add a section to an existing batch</div></div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('academic/attendance') ?>" class="card border-0 shadow-sm text-decoration-none h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-circle bg-info-subtle p-3"><i class="fas fa-user-check text-info"></i></div>
                <div><div class="fw-bold small text-dark">Attendance Portal</div><div class="text-muted" style="font-size:.75rem">Faculty: mark today's classes</div></div>
            </div>
        </a>
    </div>
</div>

<?php else: ?>
<!-- Empty state -->
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="fas fa-calendar-alt fa-4x text-primary opacity-25 mb-4 d-block"></i>
        <h5 class="fw-bold">Start by Creating Your First Batch</h5>
        <p class="text-muted mb-4 col-md-5 mx-auto">
            A Batch is a program cohort (e.g., B.Tech CSE 2024-2028).
            Next: create Sections → enroll students → configure timetable → faculty marks attendance.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= url('academic/batches/create') ?>" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-plus me-2"></i>Create First Batch
            </a>
        </div>
        <div class="mt-3 text-muted small">
            Already have a batch?
            <a href="<?= url('academic/batches') ?>">View Batches</a> ·
            <a href="<?= url('academic/sections/create') ?>">Create Section</a>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.step-circle {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.btn-link-xs {
    font-size: .72rem;
    text-decoration: none;
    display: inline-block;
    margin-top: 2px;
}
.btn-link-xs:hover { text-decoration: underline; }
</style>
