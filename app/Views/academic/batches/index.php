<?php $pageTitle = 'Academic Batches'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-dark font-weight-bold mb-0">Academic Cohorts & Batches</h4>
    <a href="<?= url('academic/batches/create') ?>" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-1"></i> Form New Cohort
    </a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="batchesTable">
                <thead class="table-light">
                    <tr>
                        <th>Program Name</th>
                        <th>Term/Session</th>
                        <th>Timeline</th>
                        <th>Max Intake</th>
                        <th>Total Semesters</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($batches)): foreach($batches as $b): ?>
                    <tr>
                        <td class="fw-bold text-primary"><?= e($b['program_name']) ?></td>
                        <td><span class="badge bg-secondary"><?= e($b['batch_term']) ?></span></td>
                        <td>
                            <small class="text-muted d-block"><i class="far fa-calendar-alt me-1"></i>Start: <?= date('d M Y', strtotime($b['start_date'])) ?></small>
                            <?php if($b['end_date']): ?>
                            <small class="text-muted d-block"><i class="far fa-flag me-1"></i>End: <?= date('d M Y', strtotime($b['end_date'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= e($b['max_intake']) ?> Students</td>
                        <td><?= e($b['total_semesters']) ?> Semesters</td>
                        <td>
                            <?php if($b['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif($b['status'] == 'graduated'): ?>
                                <span class="badge bg-info">Graduated</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-light border" title="Edit"><i class="fas fa-edit text-primary"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No cohorts defined yet.</td></tr>
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
        $('#batchesTable').DataTable({
            "order": [[ 2, "desc" ]],
            "pageLength": 25
        });
    }
});
</script>
