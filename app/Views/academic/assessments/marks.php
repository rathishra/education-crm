<?php $pageTitle = 'Manage Marks: ' . e($assessment['assessment_name']); ?>
<div class="row border-bottom pb-3 mb-4">
    <div class="col-md-8">
        <h4 class="text-dark font-weight-bold mb-1">Enter Assessment Marks</h4>
        <p class="text-muted mb-0">
            <strong><?= e($assessment['program_name']) ?> (<?= e($assessment['batch_term']) ?>)</strong> 
            | Subject: <strong><?= e($assessment['subject_name']) ?> (<?= e($assessment['subject_code']) ?>)</strong><br>
            Max: <strong class="text-primary"><?= e($assessment['max_marks']) ?></strong> 
            | Pass: <strong class="text-danger"><?= e($assessment['passing_marks']) ?></strong> 
            | Weightage: <strong><?= e($assessment['weightage']) ?>%</strong>
        </p>
    </div>
    <div class="col-md-4 text-end align-self-end">
        <a href="<?= url('academic/assessments') ?>" class="btn btn-light shadow-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
</div>

<?php if($assessment['status'] == 'completed'): ?>
<div class="alert alert-success border-0 shadow-sm mb-4">
    <i class="fas fa-lock me-2"></i> This assessment has been finalized and locked for grading.
</div>
<?php endif; ?>

<form id="frmSaveMarks" method="POST" action="<?= url('academic/assessments/marks/store') ?>">
    <input type="hidden" name="assessment_id" value="<?= $assessment['id'] ?>">

    <div class="card shadow-sm border-0 mb-4 text-dark">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="marksTable">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th class="ps-4">Roll Number</th>
                            <th>Student Name</th>
                            <th style="width: 150px;">Marks Obtained</th>
                            <th class="text-center" style="width: 120px;">Is Absent?</th>
                            <th>Remarks (Optional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($students)): foreach($students as $st): 
                            $sid = $st['id'];
                            $m = $records[$sid]['marks_obtained'] ?? '';
                            $is_absent = !empty($records[$sid]['is_absent']);
                            $remark = $records[$sid]['remarks'] ?? '';
                        ?>
                        <tr class="<?= $is_absent ? 'table-warning text-muted' : '' ?>">
                            <td class="ps-4 fw-bold text-muted"><?= e($st['roll_number']) ?></td>
                            <td class="fw-bold">
                                <?= e($st['first_name'] . ' ' . $st['last_name']) ?>
                            </td>
                            <td>
                                <input type="number" step="0.5" max="<?= $assessment['max_marks'] ?>" class="form-control fw-bold marks-input" name="marks[<?= $sid ?>]" value="<?= e($m) ?>" <?= $is_absent ? 'disabled' : '' ?>>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input abs-toggle" type="checkbox" name="absents[<?= $sid ?>]" value="1" <?= $is_absent ? 'checked' : '' ?>>
                                </div>
                            </td>
                            <td class="pe-4">
                                <input type="text" class="form-control border-light shadow-sm" name="remarks[<?= $sid ?>]" value="<?= e($remark) ?>" placeholder="...">
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-5">No active students found in this cohort.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if($assessment['status'] != 'completed'): ?>
        <div class="card-footer bg-white p-3 text-end">
            <input type="hidden" name="finalize" value="0" id="inpFinalize">
            <button type="button" class="btn btn-outline-primary px-4 fw-bold shadow-sm me-2 btnSave" data-fin="0">
                <i class="fas fa-save me-1"></i> Save Draft
            </button>
            <button type="button" class="btn btn-danger px-5 py-2 fw-bold shadow-sm btnSave" data-fin="1">
                <i class="fas fa-lock me-1"></i> Finalize & Lock Grades
            </button>
        </div>
        <?php endif; ?>
    </div>
</form>

<script>
$(document).ready(function() {
    
    // Toggle Absents
    $('.abs-toggle').change(function() {
        let tr = $(this).closest('tr');
        let marksInput = tr.find('.marks-input');
        if($(this).is(':checked')) {
            tr.addClass('table-warning text-muted');
            marksInput.val('').prop('disabled', true);
        } else {
            tr.removeClass('table-warning text-muted');
            marksInput.prop('disabled', false);
        }
    });

    // Submitting Forms
    $('.btnSave').click(function() {
        let finalize = $(this).data('fin');
        if(finalize === 1 && !confirm("Are you sure? Finalizing will lock the marks and they cannot be edited directly later.")) {
            return;
        }

        $('#inpFinalize').val(finalize);
        
        let form = $('#frmSaveMarks');
        let btns = $('.btnSave');
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
                    toastr.error(data.message || 'Saving failed');
                    btns.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error('Server error saving marks');
                btns.prop('disabled', false);
            }
        });
    });
});
</script>
