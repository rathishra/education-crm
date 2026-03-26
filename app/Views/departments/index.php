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

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search name, code..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <?php if (count($institutions) > 1): ?>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="institution_id">
                    <option value="">All Institutions</option>
                    <?php foreach ($institutions as $inst): ?>
                    <option value="<?= $inst['id'] ?>" <?= ($filters['institution_id'] ?? '') == $inst['id'] ? 'selected' : '' ?>>
                        <?= e($inst['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('departments') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Department</th>
                        <th>Code</th>
                        <th>Institution</th>
                        <th>HOD</th>
                        <th>Courses</th>
                        <th>Faculty</th>
                        <th>Status</th>
                        <th width="130">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($departments['data'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No departments found</td></tr>
                    <?php else: ?>
                    <?php foreach ($departments['data'] as $dept): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($dept['name']) ?></td>
                        <td><code><?= e($dept['code']) ?></code></td>
                        <td><small><?= e($dept['institution_name'] ?? '-') ?></small></td>
                        <td><?= e($dept['hod_name'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= $dept['course_count'] ?? 0 ?></span></td>
                        <td><span class="badge bg-secondary"><?= $dept['faculty_count'] ?? 0 ?></span></td>
                        <td>
                            <span class="badge bg-<?= $dept['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($dept['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if (hasPermission('departments.edit')): ?>
                                <a href="<?= url('departments/' . $dept['id'] . '/edit') ?>" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if (hasPermission('departments.delete')): ?>
                                <form method="POST" action="<?= url('departments/' . $dept['id'] . '/delete') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-delete" data-name="<?= e($dept['name']) ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
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
    <?php if (($departments['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $departments; $baseUrl = url('departments') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
