<?php
$pageTitle = 'Institutions';
$instTypes = [
    'college'            => 'College',
    'school'             => 'School',
    'university'         => 'University',
    'training_institute' => 'Training Institute',
    'polytechnic'        => 'Polytechnic',
    'deemed_university'  => 'Deemed University',
    'autonomous'         => 'Autonomous',
    'other'              => 'Other',
];
$typeBadge = [
    'college'            => 'bg-primary',
    'school'             => 'bg-success',
    'university'         => 'bg-danger',
    'training_institute' => 'bg-warning text-dark',
    'polytechnic'        => 'bg-info text-dark',
    'deemed_university'  => 'bg-secondary',
    'autonomous'         => 'bg-dark',
    'other'              => 'bg-secondary',
];

$rows          = $institutions['data'] ?? [];
$totalStudents = array_sum(array_column($rows, 'student_count'));
$totalDepts    = array_sum(array_column($rows, 'dept_count'));
$activeCount   = count(array_filter($rows, fn($r) => ($r['status'] ?? '') === 'active'));
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-university me-2"></i>Institutions</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Institutions</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('institutions.create')): ?>
    <a href="<?= url('institutions/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add Institution
    </a>
    <?php endif; ?>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#eef2ff">
                    <i class="fas fa-university fa-lg" style="color:#2c3e8c"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= $total ?></div>
                    <div class="text-muted small">Total Institutions</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#ecfdf5">
                    <i class="fas fa-check-circle fa-lg text-success"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold text-success"><?= $activeCount ?></div>
                    <div class="text-muted small">Active (this page)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#fff7ed">
                    <i class="fas fa-user-graduate fa-lg text-warning"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($totalStudents) ?></div>
                    <div class="text-muted small">Students (this page)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 flex-shrink-0" style="background:#f0fdf4">
                    <i class="fas fa-sitemap fa-lg text-info"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold"><?= number_format($totalDepts) ?></div>
                    <div class="text-muted small">Departments (this page)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('institutions') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control"
                       placeholder="Search name, code, city…"
                       value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach ($instTypes as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($type ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active"   <?= ($status ?? '') === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="organization_id" class="form-select">
                    <option value="">All Organizations</option>
                    <?php foreach ($organizations as $org): ?>
                    <option value="<?= $org['id'] ?>" <?= ($orgId ?? 0) == $org['id'] ? 'selected' : '' ?>>
                        <?= e($org['organization_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="<?= url('institutions') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card shadow-sm">
    <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background:#2c3e8c">
        <h6 class="mb-0 text-white fw-semibold">
            <i class="fas fa-list me-2"></i>Institutions
            <span class="badge bg-white text-primary ms-2"><?= $total ?></span>
        </h6>
    </div>
    <div class="card-body p-0">
        <?php if (empty($rows)): ?>
        <div class="text-center py-5">
            <i class="fas fa-university fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No institutions found</h5>
            <?php if (hasPermission('institutions.create')): ?>
            <a href="<?= url('institutions/create') ?>" class="btn btn-primary mt-2">
                <i class="fas fa-plus me-1"></i>Add First Institution
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f8f9fa">
                    <tr>
                        <th class="ps-4" style="width:40px">#</th>
                        <th>Institution</th>
                        <th>Type</th>
                        <th class="text-center">Depts</th>
                        <th class="text-center">Courses</th>
                        <th class="text-center">Students</th>
                        <th class="text-center">Users</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $i => $inst): ?>
                <tr>
                    <td class="ps-4 text-muted"><?= ($page - 1) * $perPage + $i + 1 ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($inst['logo'])): ?>
                            <img src="<?= e($inst['logo']) ?>" alt="logo"
                                 class="rounded-2 border" style="width:40px;height:40px;object-fit:contain">
                            <?php else: ?>
                            <div class="rounded-2 d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                 style="width:40px;height:40px;background:#2c3e8c;font-size:14px">
                                <?= strtoupper(substr($inst['name'] ?? 'I', 0, 2)) ?>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-semibold"><?= e($inst['name']) ?></div>
                                <div class="text-muted small">
                                    <code><?= e($inst['code']) ?></code>
                                    <?php if (!empty($inst['city'])): ?>
                                    &middot; <?= e($inst['city']) ?><?= !empty($inst['state']) ? ', ' . e($inst['state']) : '' ?>
                                    <?php endif; ?>
                                    <?php if (!empty($inst['org_name'])): ?>
                                    &middot; <span class="text-primary"><?= e($inst['org_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php $tKey = $inst['institution_type'] ?? 'other'; ?>
                        <span class="badge <?= $typeBadge[$tKey] ?? 'bg-secondary' ?>">
                            <?= $instTypes[$tKey] ?? ucfirst($tKey) ?>
                        </span>
                    </td>
                    <td class="text-center"><?= (int)($inst['dept_count'] ?? 0) ?></td>
                    <td class="text-center"><?= (int)($inst['course_count'] ?? 0) ?></td>
                    <td class="text-center"><?= number_format((int)($inst['student_count'] ?? 0)) ?></td>
                    <td class="text-center"><?= (int)($inst['user_count'] ?? 0) ?></td>
                    <td class="text-center">
                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                            <input class="form-check-input status-toggle" type="checkbox" role="switch"
                                   data-id="<?= $inst['id'] ?>"
                                   <?= ($inst['status'] ?? '') === 'active' ? 'checked' : '' ?>>
                        </div>
                    </td>
                    <td class="text-center pe-4">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="<?= url("institutions/{$inst['id']}") ?>"
                               class="btn btn-sm btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasPermission('institutions.edit')): ?>
                            <a href="<?= url("institutions/{$inst['id']}/edit") ?>"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('institutions.delete')): ?>
                            <button class="btn btn-sm btn-outline-danger btn-delete"
                                    data-id="<?= $inst['id'] ?>"
                                    data-name="<?= e($inst['name']) ?>" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <div class="d-flex justify-content-between align-items-center px-4 py-3 border-top">
            <div class="text-muted small">
                Showing <?= ($page - 1) * $perPage + 1 ?>–<?= min($page * $perPage, $total) ?> of <?= $total ?>
            </div>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">«</a>
                    </li>
                    <?php for ($p = max(1, $page - 2); $p <= min($pages, $page + 2); $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">»</a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger"><i class="fas fa-trash me-2"></i>Delete Institution</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="deleteInstName"></strong>?
                <div class="alert alert-warning mt-3 mb-0 small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    All related departments, courses, and configuration tables will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.status-toggle').forEach(sw => {
    sw.addEventListener('change', function () {
        const id = this.dataset.id, el = this;
        fetch(`<?= url('institutions') ?>/${id}/toggle-status`, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_token=<?= csrfToken() ?>'
        })
        .then(r => r.json())
        .then(d => { if (d.status !== 'success') el.checked = !el.checked; })
        .catch(() => { el.checked = !el.checked; });
    });
});

document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('deleteInstName').textContent = this.dataset.name;
        document.getElementById('deleteForm').action = `<?= url('institutions') ?>/${this.dataset.id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
});
</script>
