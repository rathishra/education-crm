<?php $pageTitle = 'Examination Settings'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-dark font-weight-bold mb-0">Assessments & Examinations</h4>
    <a href="<?= url('academic/assessments/create') ?>" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-1"></i> Setup Assessment
    </a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="assessmentsTable">
                <thead class="table-light">
                    <tr>
                        <th>Cohort & Subject</th>
                        <th>Assessment Name</th>
                        <th>Type</th>
                        <th>Max / Pass Marks</th>
                        <th>Weightage</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($assessments)): foreach($assessments as $a): ?>
                    <tr>
                        <td>
                            <strong class="text-primary d-block mb-1"><?= e($a['program_name']) ?> (<?= e($a['batch_term']) ?>)</strong>
                            <small class="text-muted"><i class="fas fa-book me-1"></i> <?= e($a['subject_code']) ?></small>
                        </td>
                        <td class="fw-bold text-dark"><?= e($a['assessment_name']) ?>
                            <?php if($a['assessment_date']): ?>
                            <small class="d-block text-muted fw-normal mt-1"><i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($a['assessment_date'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-secondary text-uppercase"><?= e($a['assessment_type']) ?></span></td>
                        <td>
                            Max: <strong><?= e($a['max_marks']) ?></strong><br>
                            Pass: <span class="text-danger"><?= e($a['passing_marks']) ?></span>
                        </td>
                        <td><span class="badge bg-info text-dark"><?= e($a['weightage']) ?>%</span></td>
                        <td>
                            <?php if($a['status'] == 'completed'): ?>
                                <span class="badge bg-success"><i class="fas fa-check-double me-1"></i> Graded</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= url('academic/assessments/marks?id='.$a['id']) ?>" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-edit me-1"></i> Manage Marks
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No assessments scheduled.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $extraJs[] = asset('js/academic.js'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if(typeof jQuery !== 'undefined' && $.fn.DataTable) {
        $('#assessmentsTable').DataTable({
            "order": [[ 0, "asc" ]],
            "pageLength": 25
        });
    }
});
</script>
