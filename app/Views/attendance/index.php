<?php $pageTitle = 'Mark Attendance'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-calendar-check me-2 text-primary"></i>Attendance</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Mark Attendance</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('attendance.reports')): ?>
        <a href="<?= url('attendance/report') ?>" class="btn btn-outline-primary">
            <i class="fas fa-chart-bar me-1"></i>Monthly Report
        </a>
    <?php endif; ?>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2 text-primary"></i>Select Class & Date
    </div>
    <div class="card-body">
        <form method="GET" action="<?= url('attendance') ?>" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                    <select class="form-select" name="course_id" id="filterCourse" required onchange="this.form.submit()">
                        <option value="">-- Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                    <select class="form-select" name="batch_id" required onchange="this.form.submit()">
                        <option value="">-- Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Section</label>
                    <select class="form-select" name="section_id">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['id'] ?>" <?= $sectionId == $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="date" value="<?= e($date) ?>" required max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Subject <span class="text-muted fw-normal small">(optional — for subject-wise)</span></label>
                    <select class="form-select" name="subject_id">
                        <option value="">Daily Attendance</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $subjectId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($batchId && $date): ?>

    <!-- Summary bar when students loaded -->
    <?php if (!empty($students)): ?>
    <div class="row g-3 mb-4" id="summaryBar">
        <div class="col-md-3">
            <div class="stat-card stat-indigo py-3">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-body">
                    <div class="stat-value"><?= count($students) ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-emerald py-3">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-body">
                    <div class="stat-value" id="countPresent">0</div>
                    <div class="stat-label">Present</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-rose py-3">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-body">
                    <div class="stat-value" id="countAbsent">0</div>
                    <div class="stat-label">Absent</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-amber py-3">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-body">
                    <div class="stat-value" id="countOther">0</div>
                    <div class="stat-label">Late / Half Day</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3" style="font-size:3rem;opacity:.25;"><i class="fas fa-user-slash"></i></div>
                <h6 class="text-muted fw-semibold">No Active Students</h6>
                <p class="text-muted small mb-0">No active students found in the selected batch<?= $sectionId ? '/section' : '' ?>.</p>
            </div>
        </div>
    <?php else: ?>
        <form method="POST" action="<?= url('attendance/store') ?>" id="attForm">
            <?= csrfField() ?>
            <input type="hidden" name="course_id"  value="<?= e($courseId) ?>">
            <input type="hidden" name="batch_id"   value="<?= e($batchId) ?>">
            <input type="hidden" name="date"        value="<?= e($date) ?>">
            <input type="hidden" name="subject_id"  value="<?= e($subjectId) ?>">
            <input type="hidden" name="section_id"  value="<?= e($sectionId) ?>">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <i class="fas fa-list-check me-2 text-primary"></i>
                        <strong><?= date('l, d M Y', strtotime($date)) ?></strong>
                        <?php if ($subjectId): ?>
                            <span class="badge bg-soft-primary ms-2">Subject-wise</span>
                        <?php endif; ?>
                        <?php if ($sectionId): ?>
                            <?php $secName = array_column($sections, 'name', 'id')[$sectionId] ?? ''; ?>
                            <span class="badge bg-soft-info ms-1">Section: <?= e($secName) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-soft-success mark-all" data-status="present">
                            <i class="fas fa-check me-1"></i>All Present
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-danger mark-all" data-status="absent">
                            <i class="fas fa-times me-1"></i>All Absent
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="attTable">
                            <thead>
                                <tr>
                                    <th style="width:50px">#</th>
                                    <th>Student</th>
                                    <th style="width:380px">Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $i => $stu):
                                    $att    = $existingAttendance[$stu['id']] ?? null;
                                    $status  = $att['status'] ?? 'present';
                                    $remarks = $att['remarks'] ?? '';
                                    $isExisting = $att !== null;
                                ?>
                                <tr class="att-row" data-status="<?= $status ?>">
                                    <td class="text-muted"><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></div>
                                        <div class="small text-muted">
                                            <?= e($stu['student_id_number']) ?>
                                            <?php if ($stu['roll_number']): ?>
                                                &bull; Roll <?= e($stu['roll_number']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="att-btn-group d-flex gap-1">
                                            <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="p_<?= $stu['id'] ?>" value="present" <?= $status === 'present' ? 'checked' : '' ?>>
                                            <label class="btn btn-sm att-btn-present" for="p_<?= $stu['id'] ?>"><i class="fas fa-check me-1"></i>Present</label>

                                            <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="a_<?= $stu['id'] ?>" value="absent" <?= $status === 'absent' ? 'checked' : '' ?>>
                                            <label class="btn btn-sm att-btn-absent" for="a_<?= $stu['id'] ?>"><i class="fas fa-times me-1"></i>Absent</label>

                                            <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="l_<?= $stu['id'] ?>" value="late" <?= $status === 'late' ? 'checked' : '' ?>>
                                            <label class="btn btn-sm att-btn-late" for="l_<?= $stu['id'] ?>"><i class="fas fa-clock me-1"></i>Late</label>

                                            <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="h_<?= $stu['id'] ?>" value="half_day" <?= $status === 'half_day' ? 'checked' : '' ?>>
                                            <label class="btn btn-sm att-btn-half" for="h_<?= $stu['id'] ?>"><i class="fas fa-adjust me-1"></i>Half</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="remarks[<?= $stu['id'] ?>]" value="<?= e($remarks) ?>" placeholder="Optional">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (hasPermission('attendance.mark')): ?>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Saving will replace any existing attendance for this date.</small>
                    <button type="submit" class="btn btn-primary px-4" id="saveAttBtn">
                        <i class="fas fa-save me-2"></i>Save Attendance
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>

<?php else: ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <div class="mb-3" style="font-size:3.5rem;opacity:.18;"><i class="fas fa-calendar-check"></i></div>
            <h5 class="text-muted fw-semibold mb-1">Select a Batch and Date</h5>
            <p class="text-muted small mb-0">Choose a course, batch and date above to load the student list and mark attendance.</p>
        </div>
    </div>
<?php endif; ?>

<style>
/* Attendance-specific enterprise styles */
.att-btn-present, .att-btn-absent, .att-btn-late, .att-btn-half {
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.3rem 0.7rem;
    transition: all 0.15s;
    border: 1.5px solid transparent;
}

.att-btn-present  { color: #059669; background: #f0fdf4; border-color: #a7f3d0; }
.att-btn-absent   { color: #dc2626; background: #fff5f5; border-color: #fca5a5; }
.att-btn-late     { color: #d97706; background: #fffbeb; border-color: #fcd34d; }
.att-btn-half     { color: #0891b2; background: #f0f9ff; border-color: #a5f3fc; }

.btn-check:checked + .att-btn-present  { background: #059669; color: #fff; border-color: #059669; box-shadow: 0 3px 8px rgba(5,150,105,.35); }
.btn-check:checked + .att-btn-absent   { background: #dc2626; color: #fff; border-color: #dc2626; box-shadow: 0 3px 8px rgba(220,38,38,.35); }
.btn-check:checked + .att-btn-late     { background: #d97706; color: #fff; border-color: #d97706; box-shadow: 0 3px 8px rgba(217,119,6,.35); }
.btn-check:checked + .att-btn-half     { background: #0891b2; color: #fff; border-color: #0891b2; box-shadow: 0 3px 8px rgba(8,145,178,.35); }

.att-row:hover { background: #f8faff !important; }
.att-row[data-status="absent"] { background: rgba(254,242,242,.5) !important; }
.att-row[data-status="late"]   { background: rgba(255,251,235,.5) !important; }

.btn-soft-success { background: #f0fdf4; color: #059669; border: 1px solid #a7f3d0; }
.btn-soft-success:hover { background: #059669; color: #fff; border-color: #059669; }
.btn-soft-danger  { background: #fff5f5; color: #dc2626; border: 1px solid #fca5a5; }
.btn-soft-danger:hover  { background: #dc2626; color: #fff; border-color: #dc2626; }
</style>

<script>
// Live attendance counter
function updateCounts() {
    let present = 0, absent = 0, other = 0;
    document.querySelectorAll('.att-radio:checked').forEach(r => {
        if (r.value === 'present') present++;
        else if (r.value === 'absent') absent++;
        else other++;
    });
    document.getElementById('countPresent')?.textContent !== undefined && (document.getElementById('countPresent').textContent = present);
    document.getElementById('countAbsent')?.textContent  !== undefined && (document.getElementById('countAbsent').textContent  = absent);
    document.getElementById('countOther')?.textContent   !== undefined && (document.getElementById('countOther').textContent   = other);

    // Update row highlight
    document.querySelectorAll('.att-row').forEach(row => {
        const checked = row.querySelector('.att-radio:checked');
        row.dataset.status = checked ? checked.value : '';
    });
}

// Mark all
document.querySelectorAll('.mark-all').forEach(btn => {
    btn.addEventListener('click', function () {
        const status = this.dataset.status;
        document.querySelectorAll(`.att-radio[value="${status}"]`).forEach(r => { r.checked = true; });
        updateCounts();
    });
});

// On radio change
document.querySelectorAll('.att-radio').forEach(r => {
    r.addEventListener('change', updateCounts);
});

// Initial count
updateCounts();

// Submit button loading state
document.getElementById('attForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('saveAttBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" style="width:1rem;height:1rem;border-width:2px"></span>Saving...';
    }
});
</script>
