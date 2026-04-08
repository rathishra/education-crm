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
                        <th>Sections</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($batches)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No batches found. Create your first batch to get started.</td></tr>
                    <?php endif; ?>
                    <?php foreach($batches as $b): ?>
                    <tr>
                        <td>
                            <a href="<?= url('academic/batches/' . $b['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                <?= e($b['program_name']) ?>
                            </a>
                            <small class="d-block text-muted"><?= $b['total_semesters'] ?> Semesters · Max <?= $b['max_intake'] ?> students</small>
                        </td>
                        <td><span class="badge bg-secondary"><?= e($b['batch_term']) ?></span></td>
                        <td>
                            <small class="text-muted d-block"><i class="far fa-calendar-alt me-1"></i>Start: <?= date('d M Y', strtotime($b['start_date'])) ?></small>
                            <?php if($b['end_date']): ?>
                            <small class="text-muted d-block"><i class="far fa-flag me-1"></i>End: <?= date('d M Y', strtotime($b['end_date'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <i class="fas fa-layer-group me-1"></i><?= (int)($b['section_count'] ?? 0) ?> Sections
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="fas fa-users me-1"></i><?= (int)($b['enrolled_count'] ?? 0) ?> Students
                            </span>
                        </td>
                        <td>
                            <?php if($b['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php elseif($b['status'] === 'graduated'): ?>
                                <span class="badge bg-info">Graduated</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= url('academic/batches/' . $b['id']) ?>" class="btn btn-sm btn-light border me-1" title="View"><i class="fas fa-eye text-primary"></i></a>
                            <a href="<?= url('academic/batches/edit/' . $b['id']) ?>" class="btn btn-sm btn-light border" title="Edit"><i class="fas fa-edit text-secondary"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if(typeof jQuery !== 'undefined' && $.fn.DataTable) {
        $('#batchesTable').DataTable({
            order: [[2, 'desc']],
            pageLength: 25,
            language: { emptyTable: 'No cohorts defined yet.' }
        });
    }
});
</script>
