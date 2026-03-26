<?php $pageTitle = 'Student Management'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-user-graduate me-2"></i>Student Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Students</li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (hasPermission('students.export')): ?>
        <a href="<?= url('students/export') . '?' . http_build_query(array_filter($filters ?? [])) ?>" class="btn btn-outline-success me-1">
            <i class="fas fa-file-csv me-1"></i>Export CSV
        </a>
        <?php endif; ?>
        <?php if (hasPermission('students.create')): ?>
        <a href="<?= url('students/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Student
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0"><?= number_format($stats['total_active'] ?? 0) ?></h3>
                        <small class="text-muted">Total Active</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                            <i class="fas fa-graduation-cap fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0"><?= number_format($stats['total_graduated'] ?? 0) ?></h3>
                        <small class="text-muted">Graduated</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                            <i class="fas fa-user-times fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0"><?= number_format($stats['total_inactive'] ?? 0) ?></h3>
                        <small class="text-muted">Inactive / Dropped</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                            <i class="fas fa-user-plus fa-lg"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h3 class="mb-0"><?= number_format($stats['new_this_month'] ?? 0) ?></h3>
                        <small class="text-muted">New This Month</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search name, ID, phone, email..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <?php foreach (['active','inactive','passed_out','dropped','suspended','transferred'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>>
                        <?= ucfirst(str_replace('_', ' ', $st)) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="course_id">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="batch_id">
                    <option value="">All Batches</option>
                    <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($filters['batch_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                        <?= e($b['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="department_id">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>>
                        <?= e($d['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-outline-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Students Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total: <strong><?= number_format($students['total'] ?? 0) ?></strong> students</span>
        <a href="<?= url('students') ?>" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Batch</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students['data'])): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No students found</td></tr>
                    <?php else: ?>
                    <?php foreach ($students['data'] as $s): ?>
                    <tr>
                        <td>
                            <a href="<?= url('students/' . $s['id']) ?>" class="text-decoration-none">
                                <code><?= e($s['student_id_number']) ?></code>
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;font-size:0.8rem">
                                    <?= strtoupper(substr($s['first_name'], 0, 1)) ?><?= strtoupper(substr($s['last_name'] ?? '', 0, 1)) ?>
                                </div>
                                <div>
                                    <a href="<?= url('students/' . $s['id']) ?>" class="fw-semibold text-decoration-none">
                                        <?= e($s['first_name'] . ' ' . ($s['last_name'] ?? '')) ?>
                                    </a>
                                    <?php if ($s['email']): ?>
                                    <br><small class="text-muted"><?= e($s['email']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><small><?= e($s['course_name'] ?? '-') ?></small></td>
                        <td><small><?= e($s['batch_name'] ?? '-') ?></small></td>
                        <td><?= e($s['phone'] ?? '-') ?></td>
                        <td>
                            <?php
                            $statusColors = [
                                'active' => 'success',
                                'inactive' => 'secondary',
                                'passed_out' => 'primary',
                                'dropped' => 'danger',
                                'suspended' => 'warning',
                                'transferred' => 'info',
                            ];
                            $badgeColor = $statusColors[$s['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badgeColor ?>"><?= ucfirst(str_replace('_', ' ', $s['status'])) ?></span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('students/' . $s['id']) ?>" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <?php if (hasPermission('students.edit')): ?>
                                <a href="<?= url('students/' . $s['id'] . '/edit') ?>" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                                <?php if (hasPermission('students.delete')): ?>
                                <form method="POST" action="<?= url('students/' . $s['id'] . '/delete') ?>" class="d-inline">
                                    <?= csrfField() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-delete" data-name="<?= e($s['first_name']) ?>"><i class="fas fa-trash"></i></button>
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
    <?php if (($students['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php $pagination = $students; $baseUrl = url('students') . '?' . http_build_query(array_filter($filters ?? [])); ?>
        <?php include BASE_PATH . '/app/Views/partials/pagination.php'; ?>
    </div>
    <?php endif; ?>
</div>
