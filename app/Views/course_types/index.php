<?php $pageTitle = 'Course Types'; ?>

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mt-4 mb-3">
        <div>
            <h4 class="mb-0"><i class="fas fa-graduation-cap me-2 text-primary"></i>Course Types</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item active">Course Types</li>
            </ol></nav>
        </div>
        <?php if(hasPermission('courses.manage')): ?>
        <a href="<?= url('course-types/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Course Type
        </a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search code or description..." value="<?= e($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?= url('course-types') ?>" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <?php if(empty($courseTypes)): ?>
            <div class="text-center py-5">
                <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No Course Types Found</h6>
                <p class="text-muted small">Create your first course type to get started.</p>
                <?php if(hasPermission('courses.manage')): ?>
                <a href="<?= url('course-types/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Course Type</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Degree</th>
                            <th>Duration</th>
                            <th>Semesters</th>
                            <th>Courses</th>
                            <th>Status</th>
                            <?php if(hasPermission('courses.manage')): ?><th class="text-end">Actions</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($courseTypes as $ct): ?>
                        <?php
                        $categoryLabels = [
                            'certificate'      => ['Certificate', 'bg-info'],
                            'ug'               => ['Under Graduate', 'bg-primary'],
                            'pg'               => ['Post Graduate', 'bg-success'],
                            'school'           => ['School', 'bg-warning text-dark'],
                            'research_scholar' => ['Research Scholar', 'bg-secondary'],
                            'mphil'            => ['M.Phil', 'bg-dark'],
                            'phd'              => ['PhD', 'bg-danger'],
                        ];
                        $cat = $categoryLabels[$ct['course_category']] ?? [$ct['course_category'], 'bg-secondary'];
                        ?>
                        <tr>
                            <td><strong class="text-primary"><?= e($ct['code']) ?></strong></td>
                            <td>
                                <div><?= e($ct['description']) ?></div>
                                <?php if($ct['short_description']): ?>
                                <small class="text-muted"><?= e($ct['short_description']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge <?= $cat[1] ?>"><?= $cat[0] ?></span></td>
                            <td><span class="badge bg-light text-dark border"><?= ucfirst(str_replace('_', ' ', $ct['degree_type'])) ?></span></td>
                            <td><?= $ct['duration'] ?> <?= ucfirst($ct['duration_unit']) ?><?= $ct['duration'] > 1 ? 's' : '' ?></td>
                            <td><?= $ct['no_of_semester'] ?>/yr</td>
                            <td>
                                <?php if($ct['course_count'] > 0): ?>
                                <span class="badge bg-primary rounded-pill"><?= $ct['course_count'] ?></span>
                                <?php else: ?>
                                <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(hasPermission('courses.manage')): ?>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input toggle-status" type="checkbox" data-id="<?= $ct['id'] ?>" <?= $ct['status'] === 'active' ? 'checked' : '' ?>>
                                </div>
                                <?php else: ?>
                                <span class="badge <?= $ct['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= ucfirst($ct['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <?php if(hasPermission('courses.manage')): ?>
                            <td class="text-end">
                                <a href="<?= url("course-types/{$ct['id']}/edit") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="<?= $ct['id'] ?>" data-name="<?= e($ct['code']) ?>" <?= $ct['course_count'] > 0 ? 'disabled title="Used by '.$ct['course_count'].' course(s)"' : '' ?>>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title mb-0"><i class="fas fa-trash me-2"></i>Delete Course Type</h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Delete <strong id="deleteName"></strong>? This cannot be undone.</p>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <?= csrfField() ?>
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle status
document.querySelectorAll('.toggle-status').forEach(el => {
    el.addEventListener('change', function() {
        const id = this.dataset.id;
        fetch(`<?= url('') ?>course-types/${id}/toggle-status`, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= csrfToken() ?>'
        })
        .then(r => r.json())
        .then(d => {
            if(d.status !== 'success') { this.checked = !this.checked; }
        });
    });
});

// Delete
const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
document.querySelectorAll('.btn-delete').forEach(el => {
    el.addEventListener('click', function() {
        document.getElementById('deleteName').textContent = this.dataset.name;
        document.getElementById('deleteForm').action = `<?= url('') ?>course-types/${this.dataset.id}/delete`;
        deleteModal.show();
    });
});
</script>

