<?php $pageTitle = 'Courses'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-graduation-cap me-2"></i>Courses</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Courses</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('courses.create')): ?>
    <a href="<?= url('courses/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add Course
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search course name or code..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="dept_id">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= ($deptId ?? '') == $dept['id'] ? 'selected' : '' ?>>
                        <?= e($dept['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('courses') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Course Name</th>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Duration</th>
                        <th>Type</th>
                        <th class="text-center">Seats</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Batches</th>
                        <th class="text-center">Students</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($courses['data'])): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">No courses found.</td></tr>
                <?php else: ?>
                    <?php foreach ($courses['data'] as $course): ?>
                    <tr>
                        <td>
                            <strong><?= e($course['name']) ?></strong>
                            <?php if (!empty($course['short_name'])): ?>
                            <small class="text-muted d-block"><?= e($course['short_name']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code><?= e($course['code']) ?></code></td>
                        <td><?= e($course['department_name'] ?? '—') ?></td>
                        <td><?= e($course['duration_years']) ?> yr<?= $course['duration_years'] != 1 ? 's' : '' ?></td>
                        <td><span class="badge bg-secondary text-uppercase"><?= e($course['degree_type'] ?? '') ?></span></td>
                        <td class="text-center"><?= e($course['seats'] ?? '—') ?></td>
                        <td class="text-center">
                            <?php if ($course['status'] === 'active'): ?>
                            <span class="badge bg-success">Active</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= e($course['batch_count'] ?? 0) ?></td>
                        <td class="text-center"><?= e($course['student_count'] ?? 0) ?></td>
                        <td class="text-end">
                            <?php if (hasPermission('courses.edit')): ?>
                            <a href="<?= url('courses/' . $course['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('courses.delete')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDelete('<?= url('courses/' . $course['id']) ?>')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($courses['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $courses;
        $baseUrl = url('courses') . '?' . http_build_query(array_filter(compact('search', 'deptId', 'status') ?? []));
        include BASE_PATH . '/app/Views/partials/pagination.php';
        ?>
    </div>
    <?php endif; ?>
</div>
