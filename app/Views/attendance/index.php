<?php $pageTitle = 'Attendance Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-calendar-check me-2"></i>Attendance</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Mark Attendance</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('attendance.reports')): ?>
        <a href="<?= url('attendance/report') ?>" class="btn btn-primary"><i class="fas fa-chart-line me-1"></i>Reports</a>
    <?php endif; ?>
</div>

<div class="card mb-4">
    <div class="card-body bg-light">
        <form method="GET" action="<?= url('attendance') ?>" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label required">Course</label>
                    <select class="form-select" name="course_id" id="filterCourse" required onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label required">Batch</label>
                    <select class="form-select" name="batch_id" required>
                        <option value="">-- Select Batch --</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label required">Date</label>
                    <input type="date" class="form-control" name="date" value="<?= e($date) ?>" required max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Subject <small class="text-muted">(Optional)</small></label>
                    <select class="form-select" name="subject_id">
                        <option value="">-- Daily Attendance --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $subjectId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?> (<?= e($s['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($batchId && $date): ?>
    <?php if (empty($students)): ?>
        <div class="alert alert-info py-4 text-center">
            <i class="fas fa-info-circle fs-4 mb-2"></i><br>
            No active students found in the selected batch.
        </div>
    <?php else: ?>
        <form method="POST" action="<?= url('attendance/store') ?>">
            <?= csrfField() ?>
            <input type="hidden" name="course_id" value="<?= e($courseId) ?>">
            <input type="hidden" name="batch_id" value="<?= e($batchId) ?>">
            <input type="hidden" name="date" value="<?= e($date) ?>">
            <input type="hidden" name="subject_id" value="<?= e($subjectId) ?>">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i>Student List - <?= e($date) ?></span>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-success mark-all" data-status="present">Mark All Present</button>
                        <button type="button" class="btn btn-sm btn-outline-danger mark-all" data-status="absent">Mark All Absent</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th style="width: 350px;">Attendance Status</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $i => $stu): ?>
                                <?php 
                                    $att = $existingAttendance[$stu['id']] ?? null;
                                    $status = $att['status'] ?? 'present';
                                    $remarks = $att['remarks'] ?? '';
                                ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= e($stu['student_id_number']) ?></td>
                                    <td class="fw-semibold">
                                        <?= e($stu['first_name'] . ' ' . $stu['last_name']) ?>
                                        <?php if ($stu['roll_number']): ?>
                                            <br><small class="text-muted">Roll: <?= e($stu['roll_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="att_present_<?= $stu['id'] ?>" value="present" <?= $status === 'present' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-success" for="att_present_<?= $stu['id'] ?>">Present</label>

                                            <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="att_absent_<?= $stu['id'] ?>" value="absent" <?= $status === 'absent' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-danger" for="att_absent_<?= $stu['id'] ?>">Absent</label>

                                            <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="att_late_<?= $stu['id'] ?>" value="late" <?= $status === 'late' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning" for="att_late_<?= $stu['id'] ?>">Late</label>

                                            <input type="radio" class="btn-check att-radio" name="attendance[<?= $stu['id'] ?>]" id="att_half_<?= $stu['id'] ?>" value="half_day" <?= $status === 'half_day' ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-info" for="att_half_<?= $stu['id'] ?>">Half Day</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="remarks[<?= $stu['id'] ?>]" value="<?= e($remarks) ?>" placeholder="Optional remarks">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (hasPermission('attendance.mark')): ?>
                    <div class="card-footer bg-light text-end">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Save Attendance</button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
<?php endif; ?>

<script>
document.querySelectorAll('.mark-all').forEach(btn => {
    btn.addEventListener('click', function() {
        const status = this.dataset.status;
        document.querySelectorAll(`.att-radio[value="${status}"]`).forEach(radio => {
            radio.checked = true;
        });
    });
});
</script>
