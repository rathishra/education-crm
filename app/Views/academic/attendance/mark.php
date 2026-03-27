<?php $pageTitle = 'Mark Attendance: ' . e($subject['subject_code']); ?>
<div class="row border-bottom pb-3 mb-4">
    <div class="col-md-8">
        <h4 class="text-dark font-weight-bold mb-1">Class Attendance Register</h4>
        <p class="text-muted mb-0">
            <strong><?= e($section['program_name']) ?> (<?= e($section['batch_term']) ?>) - SECTION <?= e($section['section_name']) ?></strong><br>
            Subject: <strong><?= e($subject['subject_name']) ?> (<?= e($subject['subject_code']) ?>)</strong> | Date: <strong class="text-primary"><?= date('d M Y', strtotime($date)) ?></strong>
        </p>
    </div>
    <div class="col-md-4 text-end align-self-end">
        <a href="<?= url('academic/attendance') ?>" class="btn btn-light shadow-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
</div>

<form id="frmSaveAttendance" method="POST" action="<?= url('academic/attendance/store') ?>">
    <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
    <input type="hidden" name="batch_id" value="<?= $section['batch_id'] ?>">
    <input type="hidden" name="subject_id" value="<?= $subject['id'] ?>">
    <input type="hidden" name="attendance_date" value="<?= $date ?>">

    <!-- Metadata Card -->
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <label class="form-label text-muted small fw-bold">Topic Covered Today</label>
                    <input type="text" class="form-control" name="topic_covered" value="<?= $session ? e($session['topic_covered']) : '' ?>" placeholder="E.g. Chapter 4: Neural Networks">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Session Status</label>
                    <div class="alert <?= ($session && $session['status'] == 'submitted') ? 'alert-success' : 'alert-warning' ?> py-2 mb-0 border">
                        <i class="fas <?= ($session && $session['status'] == 'submitted') ? 'fa-check-circle' : 'fa-edit' ?> me-1"></i>
                        <?= $session ? strtoupper($session['status']) : 'NEW DRAFT' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student List Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="attTable">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4">Roll Number</th>
                            <th>Student Name</th>
                            <th class="text-center" style="width: 300px;">
                                <div class="btn-group btn-group-sm w-100" role="group">
                                    <button type="button" class="btn btn-light fw-bold mark-all" data-val="present">All Present</button>
                                    <button type="button" class="btn btn-outline-light fw-bold mark-all" data-val="absent">All Absent</button>
                                </div>
                            </th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($students)): foreach($students as $st): 
                            $sid = $st['id'];
                            $currentStatus = $records[$sid]['attendance_status'] ?? 'present'; // Default to Present for ease
                            $currentRemark = $records[$sid]['remarks'] ?? '';
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= e($st['roll_number']) ?></td>
                            <td class="fw-bold text-dark">
                                <?= e($st['first_name'] . ' ' . $st['last_name']) ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group w-100 att-toggle" role="group">
                                    <input type="radio" class="btn-check" name="attendance[<?= $sid ?>]" id="att_p_<?= $sid ?>" value="present" <?= $currentStatus == 'present' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-success fw-bold p-2" for="att_p_<?= $sid ?>">P</label>

                                    <input type="radio" class="btn-check" name="attendance[<?= $sid ?>]" id="att_a_<?= $sid ?>" value="absent" <?= $currentStatus == 'absent' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-danger fw-bold p-2" for="att_a_<?= $sid ?>">A</label>

                                    <input type="radio" class="btn-check" name="attendance[<?= $sid ?>]" id="att_l_<?= $sid ?>" value="late" <?= $currentStatus == 'late' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-warning fw-bold p-2" for="att_l_<?= $sid ?>">L</label>
                                    
                                    <input type="radio" class="btn-check" name="attendance[<?= $sid ?>]" id="att_e_<?= $sid ?>" value="excused" <?= $currentStatus == 'excused' ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-info fw-bold p-2" for="att_e_<?= $sid ?>">E</label>
                                </div>
                            </td>
                            <td class="pe-4">
                                <input type="text" class="form-control form-control-sm" name="remarks[<?= $sid ?>]" value="<?= e($currentRemark) ?>" placeholder="Optional note">
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="4" class="text-center text-muted py-5">No students found matching this criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white p-3 text-end">
            <input type="hidden" name="session_status" id="session_status" value="draft">
            
            <button type="button" class="btn btn-outline-secondary px-4 fw-bold me-2 btn-submit" data-status="draft">
                <i class="fas fa-save me-1"></i> Save Draft
            </button>
            <button type="button" class="btn btn-success px-5 py-2 fw-bold shadow-sm btn-submit" data-status="submitted">
                <i class="fas fa-check-double me-1"></i> Submit Final Register
            </button>
        </div>
    </div>
</form>

<style>
.att-toggle .btn { padding: 8px 15px; font-size: 0.95rem; }
.btn-check:checked + .btn-outline-success { background-color: #198754; color: white; border-color: #198754; }
.btn-check:checked + .btn-outline-danger { background-color: #dc3545; color: white; border-color: #dc3545; }
.btn-check:checked + .btn-outline-warning { background-color: #ffc107; color: black; border-color: #ffc107; }
.btn-check:checked + .btn-outline-info { background-color: #0dcaf0; color: black; border-color: #0dcaf0; }
</style>

<script>
$(document).ready(function() {
    // Quick Mark All
    $('.mark-all').click(function() {
        let val = $(this).data('val');
        $('input.btn-check[value="'+val+'"]').prop('checked', true);
    });

    // Form Submit
    $('.btn-submit').click(function() {
        let status = $(this).data('status');
        $('#session_status').val(status);
        
        let form = $('#frmSaveAttendance');
        let btns = $('.btn-submit');
        btns.prop('disabled', true);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                let data = typeof res === 'string' ? JSON.parse(res) : res;
                if(data.status === 'success') {
                    toastr.success(data.message);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    toastr.error(data.message || 'Failed to save');
                    btns.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error('Server error saving attendance');
                btns.prop('disabled', false);
            }
        });
    });
});
</script>
