<?php $pageTitle = 'Mark Attendance: ' . e($subject['subject_code']); ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/attendance') ?>">Attendance Portal</a></li>
                <li class="breadcrumb-item active">Mark Register</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-1">Attendance Register</h4>
        <p class="text-muted mb-0">
            <strong><?= e($section['program_name']) ?> (<?= e($section['batch_term']) ?>) — Section <?= e($section['section_name']) ?></strong>
            <span class="mx-2">·</span>
            <span class="text-primary fw-bold"><?= e($subject['subject_name']) ?> (<?= e($subject['subject_code']) ?>)</span>
            <span class="mx-2">·</span>
            <?= date('l, d M Y', strtotime($date)) ?>
            <?php if(!empty($periodInfo)): ?>
            <span class="mx-2">·</span>
            <i class="far fa-clock me-1"></i><?= e($periodInfo['period_name']) ?> (<?= date('h:i A', strtotime($periodInfo['start_time'])) ?>–<?= date('h:i A', strtotime($periodInfo['end_time'])) ?>)
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= url('academic/attendance') ?>" class="btn btn-light border shadow-sm">
        <i class="fas fa-arrow-left me-1"></i> Portal
    </a>
</div>

<!-- Summary KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 bg-primary text-white shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold" id="kpiTotal"><?= count($students) ?></div>
                <div class="small opacity-75">Total Students</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 bg-success text-white shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold" id="kpiPresent"><?= $summary['present'] ?? 0 ?></div>
                <div class="small opacity-75">Present</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 bg-danger text-white shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold" id="kpiAbsent"><?= $summary['absent'] ?? 0 ?></div>
                <div class="small opacity-75">Absent</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 bg-warning text-dark shadow-sm">
            <div class="card-body text-center py-3">
                <div class="fs-2 fw-bold" id="kpiLate"><?= ($summary['late'] ?? 0) + ($summary['excused'] ?? 0) ?></div>
                <div class="small">Late / Excused</div>
            </div>
        </div>
    </div>
</div>

<form id="frmSaveAttendance" method="POST" action="<?= url('academic/attendance/store') ?>">
    <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
    <input type="hidden" name="batch_id" value="<?= $section['batch_id'] ?>">
    <input type="hidden" name="subject_id" value="<?= $subject['id'] ?>">
    <input type="hidden" name="attendance_date" value="<?= $date ?>">
    <?php if(!empty($_GET['period_id'])): ?>
    <input type="hidden" name="period_id" value="<?= (int)$_GET['period_id'] ?>">
    <?php endif; ?>

    <!-- Topic / Status -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <label class="form-label text-muted small fw-bold">Topic Covered Today</label>
                    <input type="text" class="form-control" name="topic_covered"
                        value="<?= $session ? e($session['topic_covered']) : '' ?>"
                        placeholder="E.g. Chapter 4: Introduction to Neural Networks">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Session Type</label>
                    <select class="form-select" name="session_type">
                        <option value="lecture" <?= ($session['session_type'] ?? 'lecture') === 'lecture' ? 'selected' : '' ?>>Lecture</option>
                        <option value="lab" <?= ($session['session_type'] ?? '') === 'lab' ? 'selected' : '' ?>>Lab</option>
                        <option value="tutorial" <?= ($session['session_type'] ?? '') === 'tutorial' ? 'selected' : '' ?>>Tutorial</option>
                        <option value="seminar" <?= ($session['session_type'] ?? '') === 'seminar' ? 'selected' : '' ?>>Seminar</option>
                    </select>
                </div>
            </div>
            <?php if($session): ?>
            <div class="mt-2">
                <span class="badge bg-<?= $session['status'] === 'submitted' ? 'success' : 'warning' ?> bg-opacity-75">
                    <i class="fas fa-<?= $session['status'] === 'submitted' ? 'check-circle' : 'edit' ?> me-1"></i>
                    <?= strtoupper($session['status']) ?> — Last updated <?= date('d M Y H:i', strtotime($session['created_at'])) ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Attendance Register Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-bottom py-2 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-list-check me-2"></i>Student Register <small class="text-muted fw-normal ms-1">(<?= count($students) ?> students)</small></h6>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-success fw-bold mark-all-btn" data-val="present">All Present</button>
                <button type="button" class="btn btn-outline-danger fw-bold mark-all-btn" data-val="absent">All Absent</button>
                <button type="button" class="btn btn-outline-secondary fw-bold mark-all-btn" data-val="late">All Late</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="attTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width:100px">Roll No</th>
                            <th>Student Name</th>
                            <th class="text-center" style="width:320px">Attendance</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($students)): foreach($students as $st):
                            $sid = $st['id'];
                            $currentStatus = $records[$sid]['attendance_status'] ?? 'present';
                            $currentRemark = $records[$sid]['remarks'] ?? '';
                        ?>
                        <tr id="row_<?= $sid ?>" class="att-row att-<?= $currentStatus ?>">
                            <td class="ps-4 fw-bold text-muted small"><?= e($st['roll_number'] ?? '—') ?></td>
                            <td class="fw-bold">
                                <?= e($st['first_name'] . ' ' . $st['last_name']) ?>
                                <?php if(!empty($st['email'])): ?>
                                <div class="text-muted small fw-normal"><?= e($st['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group w-100 att-toggle" role="group">
                                    <input type="radio" class="btn-check att-radio" name="attendance[<?= $sid ?>]"
                                        id="att_p_<?= $sid ?>" value="present"
                                        data-sid="<?= $sid ?>" data-val="present"
                                        <?= $currentStatus === 'present' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-success fw-bold" for="att_p_<?= $sid ?>">P</label>

                                    <input type="radio" class="btn-check att-radio" name="attendance[<?= $sid ?>]"
                                        id="att_a_<?= $sid ?>" value="absent"
                                        data-sid="<?= $sid ?>" data-val="absent"
                                        <?= $currentStatus === 'absent' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-danger fw-bold" for="att_a_<?= $sid ?>">A</label>

                                    <input type="radio" class="btn-check att-radio" name="attendance[<?= $sid ?>]"
                                        id="att_l_<?= $sid ?>" value="late"
                                        data-sid="<?= $sid ?>" data-val="late"
                                        <?= $currentStatus === 'late' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-warning fw-bold" for="att_l_<?= $sid ?>">L</label>

                                    <input type="radio" class="btn-check att-radio" name="attendance[<?= $sid ?>]"
                                        id="att_e_<?= $sid ?>" value="excused"
                                        data-sid="<?= $sid ?>" data-val="excused"
                                        <?= $currentStatus === 'excused' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-info fw-bold" for="att_e_<?= $sid ?>">E</label>
                                </div>
                            </td>
                            <td class="pe-4">
                                <input type="text" class="form-control form-control-sm"
                                    name="remarks[<?= $sid ?>]"
                                    value="<?= e($currentRemark) ?>"
                                    placeholder="Optional note">
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                            No students found for this section. <a href="<?= url('academic/sections') ?>">Enroll students</a> first.
                        </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                <span class="badge bg-success-subtle text-success border border-success-subtle me-1">P = Present</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1">A = Absent</span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle me-1">L = Late</span>
                <span class="badge bg-info-subtle text-info border border-info-subtle">E = Excused</span>
            </div>
            <div class="d-flex gap-2">
                <input type="hidden" name="session_status" id="session_status" value="draft">
                <button type="button" class="btn btn-outline-secondary px-4 fw-bold btn-submit" data-status="draft">
                    <i class="fas fa-save me-1"></i> Save Draft
                </button>
                <button type="button" class="btn btn-success px-5 fw-bold shadow-sm btn-submit" data-status="submitted">
                    <i class="fas fa-check-double me-1"></i> Submit Final Register
                </button>
            </div>
        </div>
    </div>
</form>

<style>
.att-toggle .btn { padding: 7px 14px; font-size: .9rem; }
.btn-check:checked + .btn-outline-success  { background:#198754; color:#fff; border-color:#198754; }
.btn-check:checked + .btn-outline-danger   { background:#dc3545; color:#fff; border-color:#dc3545; }
.btn-check:checked + .btn-outline-warning  { background:#ffc107; color:#000; border-color:#ffc107; }
.btn-check:checked + .btn-outline-info     { background:#0dcaf0; color:#000; border-color:#0dcaf0; }
.att-absent  { background-color: #fff5f5; }
.att-late    { background-color: #fffdf0; }
.att-excused { background-color: #f0fcff; }
</style>

<script>
// Live KPI counter
function updateKPI() {
    const rows = document.querySelectorAll('.att-row');
    let P = 0, A = 0, L = 0, E = 0;
    rows.forEach(row => {
        const checked = row.querySelector('.att-radio:checked');
        if(!checked) return;
        if(checked.value === 'present') P++;
        else if(checked.value === 'absent') A++;
        else if(checked.value === 'late') L++;
        else if(checked.value === 'excused') E++;
        // Row highlight
        row.className = 'att-row att-' + checked.value;
    });
    document.getElementById('kpiPresent').textContent = P;
    document.getElementById('kpiAbsent').textContent  = A;
    document.getElementById('kpiLate').textContent    = L + E;
}

document.querySelectorAll('.att-radio').forEach(r => r.addEventListener('change', updateKPI));

// Mark All
document.querySelectorAll('.mark-all-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const val = this.dataset.val;
        document.querySelectorAll('.att-radio[value="' + val + '"]').forEach(r => r.checked = true);
        updateKPI();
    });
});

// Submit
document.querySelectorAll('.btn-submit').forEach(btn => {
    btn.addEventListener('click', function() {
        const status = this.dataset.status;
        document.getElementById('session_status').value = status;
        const form = document.getElementById('frmSaveAttendance');
        const allBtns = document.querySelectorAll('.btn-submit');
        allBtns.forEach(b => b.disabled = true);
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        fetch(form.action, { method: 'POST', body: new FormData(form) })
            .then(r => r.json())
            .then(data => {
                if(data.status === 'success') {
                    toastr.success(data.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    toastr.error(data.message || 'Failed to save attendance');
                    allBtns.forEach(b => b.disabled = false);
                    document.querySelectorAll('.btn-submit').forEach(b => {
                        b.innerHTML = b.dataset.status === 'submitted'
                            ? '<i class="fas fa-check-double me-1"></i> Submit Final Register'
                            : '<i class="fas fa-save me-1"></i> Save Draft';
                    });
                }
            })
            .catch(() => {
                toastr.error('Server error while saving attendance');
                allBtns.forEach(b => b.disabled = false);
            });
    });
});

updateKPI();
</script>
