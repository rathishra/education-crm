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
                        <th>Section</th>
                        <th>Default Classroom</th>
                        <th class="text-center">Enrolled</th>
                        <th class="text-center">Timetable</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sections)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No sections found. Create sections within your batches.</td></tr>
                    <?php endif; ?>
                    <?php foreach($sections as $s): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-primary"><?= e($s['program_name']) ?></div>
                            <small class="text-muted"><?= e($s['batch_term']) ?></small>
                        </td>
                        <td>
                            <a href="<?= url('academic/sections/' . $s['id']) ?>" class="text-decoration-none">
                                <span class="badge bg-dark fw-bold px-3 py-2"><?= e($s['section_name']) ?></span>
                            </a>
                        </td>
                        <td>
                            <?php if($s['room_number']): ?>
                                <i class="fas fa-door-open text-muted me-1"></i> <?= e($s['room_number']) ?>
                            <?php else: ?>
                                <span class="text-muted fst-italic">Not Assigned</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="fas fa-users me-1"></i><?= (int)($s['enrolled_count'] ?? 0) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <?= (int)($s['timetable_slots'] ?? 0) ?> slots
                            </span>
                        </td>
                        <td>
                            <?php if($s['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= url('academic/sections/' . $s['id']) ?>" class="btn btn-sm btn-light border me-1" title="View"><i class="fas fa-eye text-primary"></i></a>
                            <a href="<?= url('academic/sections/edit/' . $s['id']) ?>" class="btn btn-sm btn-light border" title="Edit"><i class="fas fa-edit text-secondary"></i></a>
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
        $('#sectionsTable').DataTable({
            order: [[0, 'asc'], [1, 'asc']],
            pageLength: 25,
            language: { emptyTable: 'No sections defined yet.' }
        });
    }
});
</script>
