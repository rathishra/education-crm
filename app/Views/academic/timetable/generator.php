<?php $pageTitle = 'Generate Timetable: ' . e($section['section_name']); ?>
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="text-dark font-weight-bold mb-0">Timetable Matrix Designer</h4>
            <p class="text-muted mb-0 mt-1">
                Cohort: <strong class="text-primary"><?= e($section['program_name']) ?> (<?= e($section['batch_term']) ?>)</strong> 
                | Section: <strong class="text-dark"><?= e($section['section_name']) ?></strong>
            </p>
        </div>
        <a href="<?= url('academic/timetable') ?>" class="btn btn-light shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<form id="frmGenerateTT" method="POST" action="<?= url('academic/timetable/store') ?>">
    <input type="hidden" name="section_id" value="<?= $section['id'] ?>">
    <input type="hidden" name="batch_id" value="<?= $section['batch_id'] ?>">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle text-center" style="min-width: 1200px;">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 bg-white" style="width: 120px;">Day \ Period</th>
                            <?php foreach($periods as $p): ?>
                                <th class="<?= $p['is_break'] ? 'bg-secondary text-white' : 'bg-primary text-white' ?>">
                                    <div class="fw-bold"><?= e($p['period_name']) ?></div>
                                    <div class="small fw-light"><?= substr($p['start_time'],0,5) ?> - <?= substr($p['end_time'],0,5) ?></div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($days as $day): ?>
                            <tr>
                                <td class="fw-bold bg-light text-uppercase text-secondary"><?= $day ?></td>
                                <?php foreach($periods as $p): 
                                    $pid = $p['id'];
                                    $is_break = $p['is_break'];
                                    
                                    // Current saved data if any
                                    $curSub = $timetable[$day][$pid]['subject_id'] ?? '';
                                    $curFac = $timetable[$day][$pid]['faculty_id'] ?? '';
                                    $curTyp = $timetable[$day][$pid]['entry_type'] ?? 'lecture';
                                ?>
                                    
                                    <?php if($is_break): ?>
                                        <td class="bg-light text-muted fw-bold align-middle" style="background-color:#f8f9fa !important;">
                                            <i class="fas fa-mug-hot fa-2x opacity-25 mb-2"></i><br>
                                            <?= e($p['break_name'] ?: 'BREAK') ?>
                                        </td>
                                    <?php else: ?>
                                        <td class="p-2" style="width: 180px;">
                                            <div class="mb-2">
                                                <select class="form-select form-select-sm border-info" name="schedule[<?= $day ?>][<?= $pid ?>][subject_id]">
                                                    <option value="">- Subject -</option>
                                                    <?php foreach($subjects as $sub): ?>
                                                        <option value="<?= $sub['id'] ?>" <?= $curSub == $sub['id'] ? 'selected' : '' ?>>
                                                            <?= e($sub['subject_code']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <select class="form-select form-select-sm" name="schedule[<?= $day ?>][<?= $pid ?>][faculty_id]">
                                                    <option value="">- Faculty -</option>
                                                    <?php foreach($faculty as $fac): ?>
                                                        <option value="<?= $fac['id'] ?>" <?= $curFac == $fac['id'] ? 'selected' : '' ?>>
                                                            <?= e($fac['first_name'] . ' ' . $fac['last_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div>
                                                <select class="form-select form-select-sm bg-light text-muted" name="schedule[<?= $day ?>][<?= $pid ?>][entry_type]">
                                                    <option value="lecture" <?= $curTyp == 'lecture' ? 'selected' : '' ?>>Lecture</option>
                                                    <option value="lab" <?= $curTyp == 'lab' ? 'selected' : '' ?>>Laboratory</option>
                                                    <option value="tutorial" <?= $curTyp == 'tutorial' ? 'selected' : '' ?>>Tutorial</option>
                                                </select>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white p-3 text-end">
            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" id="btnSaveTT">
                <i class="fas fa-save me-1"></i> Save Timetable Matrix
            </button>
        </div>
    </div>
</form>

<script>
$('#frmGenerateTT').submit(function(e) {
    e.preventDefault();
    let btn = $('#btnSaveTT');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving Setup...');
    
    $.ajax({
        url: $(this).attr('action'),
        method: 'POST',
        data: $(this).serialize(),
        success: function(res) {
            let data = typeof res === 'string' ? JSON.parse(res) : res;
            if(data.status === 'success') {
                toastr.success(data.message);
                btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i> Saved Successfully');
                setTimeout(() => { btn.html('<i class="fas fa-save me-1"></i> Save Timetable Matrix'); }, 2000);
            } else {
                toastr.error(data.message || 'Saving failed');
                btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Timetable Matrix');
            }
        },
        error: function(xhr) {
            toastr.error('An error occurred. Check inputs.');
            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Timetable Matrix');
        }
    });
});
</script>
