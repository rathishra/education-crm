<?php $pageTitle = 'Batches'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-layer-group me-2"></i>Batches</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Batches</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('batches.create')): ?>
    <a href="<?= url('batches/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Add Batch
    </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search batch name or code..."
                       value="<?= e($search ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" name="course_id">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>" <?= ($courseId ?? '') == $course['id'] ? 'selected' : '' ?>>
                        <?= e($course['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <?php foreach (['upcoming' => 'Upcoming', 'active' => 'Active', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($status ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('batches') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                        <th>Batch Name</th>
                        <th>Code</th>
                        <th>Course</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th class="text-center">Capacity</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Students</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($batches['data'])): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No batches found.</td></tr>
                <?php else: ?>
                    <?php foreach ($batches['data'] as $batch): ?>
                    <?php
                    $statusColors = ['upcoming' => 'info', 'active' => 'success', 'completed' => 'secondary', 'cancelled' => 'danger'];
                    $badgeColor = $statusColors[$batch['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><strong><?= e($batch['name']) ?></strong></td>
                        <td><code><?= e($batch['code'] ?? '—') ?></code></td>
                        <td><?= e($batch['course_name'] ?? '—') ?></td>
                        <td><?= !empty($batch['start_date']) ? formatDate($batch['start_date']) : '—' ?></td>
                        <td><?= !empty($batch['end_date']) ? formatDate($batch['end_date']) : '—' ?></td>
                        <td class="text-center"><?= e($batch['capacity'] ?? '—') ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $badgeColor ?>"><?= ucfirst(e($batch['status'])) ?></span>
                        </td>
                        <td class="text-center"><?= e($batch['student_count'] ?? 0) ?></td>
                        <td class="text-end">
                            <?php if (hasPermission('batches.edit')): ?>
                            <a href="<?= url('batches/' . $batch['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('batches.delete')): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDelete('<?= url('batches/' . $batch['id']) ?>')" title="Delete">
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
    <?php if (($batches['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
        $pagination = $batches;
        $baseUrl = url('batches') . '?' . http_build_query(array_filter(compact('search', 'courseId', 'status') ?? []));
        include BASE_PATH . '/app/Views/partials/pagination.php';
        ?>
    </div>
    <?php endif; ?>
</div>
