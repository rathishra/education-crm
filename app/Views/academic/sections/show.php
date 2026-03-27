<?php $pageTitle = 'Section: ' . e($section['program_name']) . ' - ' . e($section['section_name']); ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('academic/sections') ?>">Sections</a></li>
                <li class="breadcrumb-item active"><?= e($section['program_name']) ?> - Section <?= e($section['section_name']) ?></li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-0">
            <?= e($section['program_name']) ?>
            <span class="badge bg-dark fs-6 ms-2">SECTION <?= e($section['section_name']) ?></span>
            <span class="badge bg-secondary fs-6 ms-1"><?= e($section['batch_term']) ?></span>
            <span class="badge bg-<?= $section['status'] === 'active' ? 'success' : 'danger' ?> ms-1 fs-6"><?= ucfirst($section['status']) ?></span>
        </h4>
        <?php if($section['advisor_name']): ?>
        <small class="text-muted"><i class="fas fa-user-tie me-1"></i>Advisor: <?= e($section['advisor_name']) ?></small>
        <?php endif; ?>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEnroll">
            <i class="fas fa-user-plus me-1"></i> Enroll Students
        </button>
        <a href="<?= url('academic/sections/edit/' . $section['id']) ?>" class="btn btn-light border shadow-sm">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
    </div>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle p-3"><i class="fas fa-users text-success fa-lg"></i></div>
                <div>
                    <div class="fs-3 fw-bold text-success"><?= count(array_filter($enrolled, fn($e) => $e['enroll_status'] === 'active')) ?></div>
                    <div class="text-muted small">Active Students</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle p-3"><i class="fas fa-door-open text-primary fa-lg"></i></div>
                <div>
                    <div class="fs-3 fw-bold text-primary"><?= $section['room_number'] ? e($section['room_number']) : '—' ?></div>
                    <div class="text-muted small">Classroom</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning-subtle p-3"><i class="fas fa-calendar-alt text-warning fa-lg"></i></div>
                <div>
                    <div class="fs-3 fw-bold text-warning"><?= count($todaySchedule) ?></div>
                    <div class="text-muted small">Classes Today</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info-subtle p-3"><i class="fas fa-clipboard-check text-info fa-lg"></i></div>
                <div>
                    <div class="fs-3 fw-bold text-info"><?= count($recentSessions) ?></div>
                    <div class="text-muted small">Recent Sessions</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Students List -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-success"></i>Enrolled Students</h6>
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalEnroll">
                    <i class="fas fa-user-plus me-1"></i> Enroll
                </button>
            </div>
            <div class="card-body p-0">
                <?php if(!empty($enrolled)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="enrolledTable">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Roll No</th>
                                <th>Name</th>
                                <th class="text-center">Attendance</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($enrolled as $st): ?>
                        <?php
                            $pct = ($st['total_marked'] > 0) ? round($st['present_count'] / $st['total_marked'] * 100) : null;
                            $pctClass = ($pct === null) ? 'secondary' : ($pct >= 75 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'));
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted small"><?= e($st['roll_number'] ?? '—') ?></td>
                            <td>
                                <div class="fw-bold small"><?= e($st['first_name'] . ' ' . $st['last_name']) ?></div>
                                <div class="text-muted" style="font-size:.75rem"><?= e($st['email'] ?? '') ?></div>
                            </td>
                            <td class="text-center">
                                <?php if($pct !== null): ?>
                                <div class="d-flex align-items-center gap-1 justify-content-center">
                                    <div class="progress flex-grow-1" style="height:6px;max-width:60px">
                                        <div class="progress-bar bg-<?= $pctClass ?>" style="width:<?= $pct ?>%"></div>
                                    </div>
                                    <span class="badge bg-<?= $pctClass ?>-subtle text-<?= $pctClass ?> border border-<?= $pctClass ?>-subtle" style="font-size:.7rem">
                                        <?= $st['present_count'] ?>/<?= $st['total_marked'] ?> (<?= $pct ?>%)
                                    </span>
                                </div>
                                <?php else: ?>
                                <span class="text-muted small">No records</span>
                                <?php endif; ?>
                            </td>
                            <td class="small">Sem <?= (int)$st['current_semester'] ?></td>
                            <td>
                                <span class="badge bg-<?= $st['enroll_status'] === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= $st['enroll_status'] === 'active' ? 'success' : 'secondary' ?> border border-<?= $st['enroll_status'] === 'active' ? 'success' : 'secondary' ?>-subtle small">
                                    <?= ucfirst($st['enroll_status']) ?>
                                </span>
                            </td>
                            <td class="pe-3">
                                <?php if($st['enroll_status'] === 'active'): ?>
                                <button class="btn btn-sm btn-light border text-danger btn-unenroll"
                                    data-id="<?= $st['enrollment_id'] ?>"
                                    data-name="<?= e($st['first_name'] . ' ' . $st['last_name']) ?>"
                                    title="Unenroll">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-users fa-2x mb-2 d-block opacity-25"></i>
                    No students enrolled yet.
                    <div class="mt-2">
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalEnroll">
                            <i class="fas fa-user-plus me-1"></i> Enroll Students
                        </button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Today's Schedule + Recent Sessions -->
    <div class="col-lg-4">
        <!-- Today's Schedule -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-calendar-day me-2 text-warning"></i>Today's Schedule</h6>
                <small class="text-muted"><?= date('l, d M') ?></small>
            </div>
            <?php if(!empty($todaySchedule)): ?>
            <div class="list-group list-group-flush">
                <?php foreach($todaySchedule as $cls): ?>
                <div class="list-group-item px-3 py-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold small"><?= e($cls['subject_name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><i class="fas fa-user me-1"></i><?= e($cls['faculty_name'] ?? 'Unassigned') ?></div>
                        </div>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:.7rem">
                            <?= date('h:i A', strtotime($cls['start_time'])) ?>
                        </span>
                    </div>
                    <div class="text-muted mt-1" style="font-size:.72rem"><?= e($cls['period_name']) ?> · <?= date('h:i A', strtotime($cls['start_time'])) ?> – <?= date('h:i A', strtotime($cls['end_time'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="card-body text-center text-muted py-3 small">
                <i class="fas fa-calendar-times me-1"></i>No classes scheduled today.
            </div>
            <?php endif; ?>
        </div>

        <!-- Recent Attendance Sessions -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-clipboard-check me-2 text-info"></i>Recent Sessions</h6>
                <a href="<?= url('academic/attendance/history?section_id=' . $section['id']) ?>" class="btn btn-sm btn-outline-info">All</a>
            </div>
            <div class="list-group list-group-flush">
                <?php if(!empty($recentSessions)): foreach($recentSessions as $sess): ?>
                <div class="list-group-item px-3 py-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold small"><?= e($sess['subject_name']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= date('d M Y', strtotime($sess['attendance_date'])) ?> · <?= e($sess['faculty_name']) ?></div>
                        </div>
                        <?php
                            $pct2 = $sess['total_count'] > 0 ? round($sess['present_count'] / $sess['total_count'] * 100) : 0;
                            $cls2 = $pct2 >= 75 ? 'success' : ($pct2 >= 50 ? 'warning' : 'danger');
                        ?>
                        <span class="badge bg-<?= $cls2 ?>-subtle text-<?= $cls2 ?> border border-<?= $cls2 ?>-subtle" style="font-size:.7rem">
                            <?= $sess['present_count'] ?>/<?= $sess['total_count'] ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div class="list-group-item text-center text-muted py-3 small">No sessions recorded.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Enroll Modal -->
<div class="modal fade" id="modalEnroll" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-success"></i>Enroll Students</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="frmEnroll">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Current Semester</label>
                            <select class="form-select" name="current_semester">
                                <?php for($s=1; $s<=12; $s++): ?><option value="<?= $s ?>">Semester <?= $s ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Academic Year</label>
                            <input type="text" class="form-control" name="academic_year" value="<?= date('Y') . '-' . (date('Y')+1) ?>" placeholder="2024-2025">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Enrollment Date</label>
                            <input type="date" class="form-control" name="enrollment_date" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Select Students <span class="text-danger">*</span></label>
                        <?php if(!empty($available)): ?>
                        <div class="input-group mb-2">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="studentSearch" placeholder="Search by name or roll number...">
                        </div>
                        <div class="border rounded" style="max-height:280px;overflow-y:auto" id="studentListBox">
                            <?php foreach($available as $av): ?>
                            <label class="d-flex align-items-center gap-2 px-3 py-2 student-row" style="cursor:pointer;border-bottom:1px solid #f0f0f0"
                                data-name="<?= strtolower(e($av['first_name'] . ' ' . $av['last_name'])) ?>"
                                data-roll="<?= strtolower(e($av['roll_number'] ?? '')) ?>">
                                <input type="checkbox" name="student_ids[]" value="<?= $av['id'] ?>" class="form-check-input mt-0">
                                <span class="small">
                                    <strong><?= e($av['first_name'] . ' ' . $av['last_name']) ?></strong>
                                    <?php if($av['roll_number']): ?><span class="text-muted ms-1"><?= e($av['roll_number']) ?></span><?php endif; ?>
                                </span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="chkSelectAll">
                                <label class="form-check-label small" for="chkSelectAll">Select All</label>
                            </div>
                            <small class="text-muted" id="selCount">0 selected</small>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-1"></i>All active students are already enrolled in this section.</div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btnEnroll" <?= empty($available) ? 'disabled' : '' ?>>
                    <i class="fas fa-user-plus me-1"></i> Enroll Selected
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Student search filter
const searchInput = document.getElementById('studentSearch');
if(searchInput) {
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.student-row').forEach(row => {
            row.style.display = (row.dataset.name.includes(q) || row.dataset.roll.includes(q)) ? '' : 'none';
        });
    });
}

// Select All
const chkAll = document.getElementById('chkSelectAll');
if(chkAll) {
    chkAll.addEventListener('change', function() {
        document.querySelectorAll('input[name="student_ids[]"]').forEach(cb => {
            if(cb.closest('.student-row').style.display !== 'none') cb.checked = this.checked;
        });
        updateCount();
    });
}

function updateCount() {
    const cnt = document.querySelectorAll('input[name="student_ids[]"]:checked').length;
    const el = document.getElementById('selCount');
    if(el) el.textContent = cnt + ' selected';
}
document.querySelectorAll('input[name="student_ids[]"]').forEach(cb => cb.addEventListener('change', updateCount));

// Enroll submit
document.getElementById('btnEnroll')?.addEventListener('click', async function() {
    const form = document.getElementById('frmEnroll');
    const fd = new FormData(form);
    const checked = document.querySelectorAll('input[name="student_ids[]"]:checked');
    if(!checked.length) { toastr.warning('Please select at least one student'); return; }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enrolling...';
    try {
        const res = await fetch('<?= url('academic/sections/' . $section['id'] . '/enroll') ?>', {
            method: 'POST', body: fd
        });
        const data = await res.json();
        if(data.status === 'success') {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(data.message || 'Enrollment failed');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-user-plus me-1"></i> Enroll Selected';
        }
    } catch(e) {
        toastr.error('Server error');
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-user-plus me-1"></i> Enroll Selected';
    }
});

// Unenroll
document.querySelectorAll('.btn-unenroll').forEach(btn => {
    btn.addEventListener('click', async function() {
        const name = this.dataset.name;
        const id = this.dataset.id;
        if(!confirm('Remove ' + name + ' from this section?')) return;
        try {
            const res = await fetch('<?= url('academic/sections/unenroll/') ?>' + id, { method: 'POST' });
            const data = await res.json();
            if(data.status === 'success') {
                toastr.success(data.message);
                setTimeout(() => location.reload(), 1200);
            } else {
                toastr.error(data.message || 'Failed');
            }
        } catch(e) {
            toastr.error('Server error');
        }
    });
});

// DataTable
if(typeof jQuery !== 'undefined' && $.fn.DataTable) {
    $('#enrolledTable').DataTable({ order: [[0,'asc']], pageLength: 25, info: false });
}
</script>
