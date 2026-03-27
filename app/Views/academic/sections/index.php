<?php $pageTitle = 'Class Sections'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-dark font-weight-bold mb-0">Cohort Class Sections</h4>
    <a href="<?= url('academic/sections/create') ?>" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus me-1"></i> Add New Section
    </a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="sectionsTable">
                <thead class="table-light">
                    <tr>
                        <th>Batch / Cohort</th>
                        <th>Term</th>
                        <th>Section Name</th>
                        <th>Default Classroom</th>
                        <th>Max Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($sections)): foreach($sections as $s): ?>
                    <tr>
                        <td class="fw-bold text-primary"><?= e($s['program_name']) ?></td>
                        <td><span class="badge bg-secondary"><?= e($s['batch_term']) ?></span></td>
                        <td><span class="badge bg-dark fw-bold px-3 py-2"><?= e($s['section_name']) ?></span></td>
                        <td>
                            <?php if($s['room_number']): ?>
                                <i class="fas fa-door-open text-muted me-1"></i> <?= e($s['room_number']) ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Not Assigned</span>
                            <?php endif; ?>
                        </td>
                        <td><i class="fas fa-users text-muted me-1"></i> <?= e($s['capacity']) ?> Students</td>
                        <td>
                            <?php if($s['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-light border" title="Edit"><i class="fas fa-edit text-primary"></i></button>
                            <button class="btn btn-sm btn-light border text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No sections defined yet.</td></tr>
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
        $('#sectionsTable').DataTable({
            "order": [[ 0, "asc" ], [ 2, "asc" ]],
            "pageLength": 25
        });
    }
});
</script>
