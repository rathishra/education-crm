<?php $pageTitle = 'Enter Marks : ' . e($schedule['subject_name']); ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-edit me-2"></i>Enter Marks</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('exams') ?>">Exams</a></li>
                <li class="breadcrumb-item"><a href="<?= url("exams/{$exam['id']}") ?>"><?= e($exam['name']) ?></a></li>
                <li class="breadcrumb-item active"><?= e($schedule['subject_code']) ?></li>
            </ol>
        </nav>
    </div>
    <a href="<?= url("exams/{$exam['id']}") ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Exam</a>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light"><i class="fas fa-info-circle me-2"></i>Schedule Details</div>
            <div class="card-body">
                <h5 class="card-title"><?= e($schedule['subject_name']) ?></h5>
                <h6 class="card-subtitle mb-3 text-muted"><?= e($schedule['subject_code']) ?></h6>
                
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fas fa-calendar-day text-muted me-2"></i> <?= formatDate($schedule['date']) ?></li>
                    <li class="mb-2"><i class="fas fa-clock text-muted me-2"></i> <?= date('H:i', strtotime($schedule['start_time'])) ?> - <?= date('H:i', strtotime($schedule['end_time'])) ?></li>
                    <li class="mb-2"><i class="fas fa-check-circle text-muted me-2"></i> Max Marks: <?= e($schedule['max_marks']) ?></li>
                    <li><i class="fas fa-exclamation-circle text-muted me-2"></i> Pass Marks: <?= e($schedule['min_marks']) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-9 mb-4">
        <div class="card mb-4">
            <div class="card-body bg-light">
                <form method="GET" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Select Course</label>
                            <select class="form-select" name="course_id" onchange="document.getElementById('filterForm').submit()">
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Select Batch</label>
                            <select class="form-select" name="batch_id" required>
                                <option value="">-- Select Batch --</option>
                                <?php foreach ($batches as $b): ?>
                                    <option value="<?= $b['id'] ?>" <?= $batchId == $b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Load Students</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($batchId): ?>
            <div class="card">
                <form method="POST" action="<?= url("exams/{$exam['id']}/schedule/{$schedule['id']}/marks") ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="batch_id" value="<?= e($batchId) ?>">
                    
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2"></i>Student Marks Entry</span>
                        <span class="badge bg-primary">Max: <?= e($schedule['max_marks']) ?></span>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px;">#</th>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th style="width:120px;" class="text-center">Absent?</th>
                                        <th style="width:150px;">Marks Obtained</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($students)): ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No active students in this batch.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($students as $i => $stu): 
                                            $m = $marks[$stu['id']] ?? null;
                                            $isAbsent = $m ? $m['is_absent'] : 0;
                                            $obtained = $m ? $m['marks_obtained'] : '';
                                            $remarks = $m ? $m['remarks'] : '';
                                        ?>
                                        <tr>
                                            <td><?= $i+1 ?></td>
                                            <td><code><?= e($stu['student_id_number']) ?></code></td>
                                            <td class="fw-semibold text-nowrap"><?= e($stu['first_name'] . ' ' . $stu['last_name']) ?></td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input absent-toggle" type="checkbox" name="is_absent[<?= $stu['id'] ?>]" value="1" <?= $isAbsent ? 'checked' : '' ?> title="Mark as Absent" data-student="<?= $stu['id'] ?>">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" max="<?= e($schedule['max_marks']) ?>" class="form-control form-control-sm marks-input" name="marks[<?= $stu['id'] ?>]" value="<?= e($obtained) ?>" id="marks_<?= $stu['id'] ?>" <?= $isAbsent ? 'readonly tabindex="-1"' : '' ?> required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="remarks[<?= $stu['id'] ?>]" value="<?= e($remarks) ?>" placeholder="Optional">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php if(!empty($students)): ?>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save ALL Marks</button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.absent-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const studentId = this.dataset.student;
        const marksInput = document.getElementById('marks_' + studentId);
        if (this.checked) {
            marksInput.value = '';
            marksInput.readOnly = true;
            marksInput.removeAttribute('required');
            marksInput.tabIndex = -1;
        } else {
            marksInput.readOnly = false;
            marksInput.setAttribute('required', 'required');
            marksInput.removeAttribute('tabindex');
        }
    });
});
</script>
