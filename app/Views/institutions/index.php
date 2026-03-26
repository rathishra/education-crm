<?php $pageTitle = 'Institutions'; ?>

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
    <a href="<?= url('institutions/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add Institution
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search name, code, city..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="organization_id">
                    <option value="">All Organizations</option>
                    <?php foreach ($organizations as $orgId => $orgName): ?>
                    <option value="<?= $orgId ?>" <?= ($filters['organization_id'] ?? '') == $orgId ? 'selected' : '' ?>>
                        <?= e($orgName) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="type">
                    <option value="">All Types</option>
                    <?php foreach ($types as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($filters['type'] ?? '') === $val ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('institutions') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                        <th>Institution</th>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Organization</th>
                        <th>City</th>
                        <th>Depts</th>
                        <th>Courses</th>
                        <th>Status</th>
                        <th width="140">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($institutions['data'])): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No institutions found</td></tr>
                    <?php else: ?>
                    <?php foreach ($institutions['data'] as $inst): ?>
                    <tr>
                        <td>
                            <a href="<?= url('institutions/' . $inst['id']) ?>" class="fw-semibold">
                                <?= e($inst['name']) ?>
                            </a>
                        </td>
                        <td><code><?= e($inst['code']) ?></code></td>
                        <td>
                            <span class="badge bg-info"><?= e($types[$inst['type']] ?? $inst['type']) ?></span>
                        </td>
                        <td><small><?= e($inst['organization_name'] ?? '-') ?></small></td>
                        <td><?= e($inst['city'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= $inst['department_count'] ?? 0 ?></span></td>
                        <td><span class="badge bg-secondary"><?= $inst['course_count'] ?? 0 ?></span></td>
                        <td>
                            <span class="badge bg-<?= $inst['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($inst['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('institutions/' . $inst['id']) ?>" class="btn btn-outline-info"><i class="fas fa-eye"></i></a>
                                <?php if (hasPermission('institutions.edit')): ?>
                                <a href="<?= url('institutions/' . $inst['id'] . '/edit') ?>" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if (hasPermission('institutions.delete')): ?>
                                <form method="POST" action="<?= url('institutions/' . $inst['id'] . '/delete') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-delete" data-name="<?= e($inst['name']) ?>">
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
    <?php if (($institutions['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $institutions; $baseUrl = url('institutions') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
