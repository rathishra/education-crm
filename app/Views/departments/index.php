<?php $pageTitle = 'Departments'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-sitemap me-2"></i>Departments</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Departments</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('departments.create')): ?>
    <a href="<?= url('departments/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add Department
    </a>
    <?php endif; ?>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <?php
    $totalDepts   = $total ?? 0;
    $activeDepts  = count(array_filter($departments ?? [], fn($d) => $d['status'] === 'active'));
    $totalFaculty = array_sum(array_column($departments ?? [], 'live_faculty_count'));
    $totalStudents= array_sum(array_column($departments ?? [], 'live_student_count'));
    ?>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="fas fa-sitemap text-primary fa-lg"></i></div>
                <div><div class="fs-4 fw-bold"><?= $totalDepts ?></div><div class="text-muted small">Total Departments</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-check-circle text-success fa-lg"></i></div>
                <div><div class="fs-4 fw-bold"><?= $activeDepts ?></div><div class="text-muted small">Active</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-chalkboard-teacher text-info fa-lg"></i></div>
                <div><div class="fs-4 fw-bold"><?= $totalFaculty ?></div><div class="text-muted small">Total Faculty</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-user-graduate text-warning fa-lg"></i></div>
                <div><div class="fs-4 fw-bold"><?= $totalStudents ?></div><div class="text-muted small">Total Students</div></div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Name, code, HOD…" value="<?= e($search ?? '') ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="type">
                    <option value="">All Types</option>
                    <option value="academic"       <?= ($type ?? '') === 'academic'       ? 'selected' : '' ?>>Academic</option>
                    <option value="administrative" <?= ($type ?? '') === 'administrative' ? 'selected' : '' ?>>Administrative</option>
                    <option value="research"       <?= ($type ?? '') === 'research'       ? 'selected' : '' ?>>Research</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active"   <?= ($status ?? '') === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <?php if (count($institutions ?? []) > 1): ?>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="institution_id">
                    <option value="">All Institutions</option>
                    <?php foreach ($institutions as $inst): ?>
                    <option value="<?= $inst['id'] ?>" <?= ($instId ?? 0) == $inst['id'] ? 'selected' : '' ?>><?= e($inst['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary me-1"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= url('departments') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span class="fw-semibold text-muted small">
            Showing <?= count($departments ?? []) ?> of <?= $total ?? 0 ?> department(s)
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Department</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>HOD</th>
                        <th class="text-center">Courses</th>
                        <th class="text-center">Faculty</th>
                        <th class="text-center">Students</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($departments)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-5">
                        <i class="fas fa-sitemap fa-2x mb-2 d-block"></i>No departments found
                    </td></tr>
                <?php else: ?>
                <?php
                $typeBadge = [
                    'academic'       => ['bg-primary',   'Academic'],
                    'administrative' => ['bg-warning text-dark', 'Admin'],
                    'research'       => ['bg-info text-dark',    'Research'],
                ];
                ?>
                <?php foreach ($departments as $dept): ?>
                    <?php $tb = $typeBadge[$dept['department_type']] ?? ['bg-secondary', ucfirst($dept['department_type'])]; ?>
                    <tr>
                        <td>
                            <a href="<?= url('departments/' . $dept['id']) ?>" class="fw-semibold text-decoration-none">
                                <?= e($dept['name']) ?>
                            </a>
                            <?php if ($dept['parent_name']): ?>
                            <br><small class="text-muted"><i class="fas fa-level-up-alt fa-xs me-1"></i><?= e($dept['parent_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code class="bg-light px-2 py-1 rounded"><?= e($dept['code']) ?></code></td>
                        <td><span class="badge <?= $tb[0] ?>"><?= $tb[1] ?></span></td>
                        <td>
                            <?php if ($dept['hod_name']): ?>
                                <i class="fas fa-user-tie text-muted me-1"></i><?= e($dept['hod_name']) ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><span class="badge bg-secondary rounded-pill"><?= $dept['course_count'] ?? 0 ?></span></td>
                        <td class="text-center"><span class="badge bg-info text-dark rounded-pill"><?= $dept['live_faculty_count'] ?? $dept['faculty_count'] ?? 0 ?></span></td>
                        <td class="text-center"><span class="badge bg-success rounded-pill"><?= $dept['live_student_count'] ?? $dept['student_count'] ?? 0 ?></span></td>
                        <td>
                            <?php if (hasPermission('departments.edit')): ?>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input toggle-status" type="checkbox"
                                       data-id="<?= $dept['id'] ?>"
                                       <?= $dept['status'] === 'active' ? 'checked' : '' ?>>
                            </div>
                            <?php else: ?>
                            <span class="badge bg-<?= $dept['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($dept['status']) ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('departments/' . $dept['id']) ?>" class="btn btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
                                <?php if (hasPermission('departments.edit')): ?>
                                <a href="<?= url('departments/' . $dept['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if (hasPermission('departments.delete')): ?>
                                <button class="btn btn-outline-danger btn-delete"
                                        data-id="<?= $dept['id'] ?>"
                                        data-name="<?= e($dept['name']) ?>"
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (($pages ?? 1) > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <li class="page-item <?= ($page ?? 1) <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => ($page ?? 1) - 1])) ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
                <?php for ($p = 1; $p <= ($pages ?? 1); $p++): ?>
                <li class="page-item <?= $p === ($page ?? 1) ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page ?? 1) >= ($pages ?? 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => ($page ?? 1) + 1])) ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title mb-0"><i class="fas fa-trash me-2"></i>Delete Department</h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Delete <strong id="deleteName"></strong>? This cannot be undone.
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
    el.addEventListener('change', function () {
        const id = this.dataset.id;
        const cb = this;
        fetch(`<?= url('') ?>departments/${id}/toggle-status`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= csrfToken() ?>'
        })
        .then(r => r.json())
        .then(d => { if (d.status !== 'success') cb.checked = !cb.checked; })
        .catch(() => { cb.checked = !cb.checked; });
    });
});

// Delete modal
const delModal = new bootstrap.Modal(document.getElementById('deleteModal'));
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('deleteName').textContent = this.dataset.name;
        document.getElementById('deleteForm').action = `<?= url('') ?>departments/${this.dataset.id}/delete`;
        delModal.show();
    });
});
</script>
