<?php $pageTitle = 'Institution Details'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-university me-2"></i><?= e($inst['name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('institutions') ?>">Institutions</a></li>
                <li class="breadcrumb-item active"><?= e($inst['name']) ?></li>
            </ol>
        </nav>
    </div>
    <div>
        <?php if (hasPermission('institutions.edit')): ?>
        <a href="<?= url('institutions/' . $inst['id'] . '/edit') ?>" class="btn btn-primary"><i class="fas fa-edit me-1"></i>Edit</a>
        <?php endif; ?>
        <a href="<?= url('institutions') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-primary">
            <div class="card-body text-center">
                <div class="h3 mb-0 fw-bold"><?= count($inst['departments'] ?? []) ?></div>
                <small class="text-muted">Departments</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-success">
            <div class="card-body text-center">
                <div class="h3 mb-0 fw-bold"><?= count($inst['courses'] ?? []) ?></div>
                <small class="text-muted">Courses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-info">
            <div class="card-body text-center">
                <div class="h3 mb-0 fw-bold"><?= $inst['student_count'] ?? 0 ?></div>
                <small class="text-muted">Active Students</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-warning">
            <div class="card-body text-center">
                <div class="h3 mb-0 fw-bold"><?= $inst['lead_count'] ?? 0 ?></div>
                <small class="text-muted">Total Leads</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Info Card -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center py-4">
                <?php if (!empty($inst['logo'])): ?>
                    <img src="<?= url($inst['logo']) ?>" class="mb-3" style="max-height:80px" alt="">
                <?php else: ?>
                    <div class="bg-info text-white rounded d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;font-size:2rem">
                        <?= strtoupper(substr($inst['name'], 0, 2)) ?>
                    </div>
                <?php endif; ?>
                <h5 class="mb-1"><?= e($inst['name']) ?></h5>
                <p class="text-muted mb-1">Code: <code><?= e($inst['code']) ?></code></p>
                <span class="badge bg-info me-1"><?= e($types[$inst['type']] ?? $inst['type']) ?></span>
                <span class="badge bg-<?= $inst['status'] === 'active' ? 'success' : 'secondary' ?>">
                    <?= ucfirst($inst['status']) ?>
                </span>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Organization</span>
                    <span><?= e($inst['organization_name'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Principal</span>
                    <span><?= e($inst['principal_name'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Email</span>
                    <span><?= e($inst['email'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Phone</span>
                    <span><?= e($inst['phone'] ?? '-') ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Affiliation</span>
                    <span><?= e(($inst['affiliation_number'] ?? '-') . ' / ' . ($inst['affiliation_body'] ?? '-')) ?></span>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Established</span>
                    <span><?= e($inst['established_year'] ?? '-') ?></span>
                </div>
                <div class="list-group-item">
                    <span class="text-muted d-block mb-1">Address</span>
                    <?= e(implode(', ', array_filter([
                        $inst['address_line1'] ?? '', $inst['city'] ?? '',
                        $inst['state'] ?? '', $inst['pincode'] ?? ''
                    ]))) ?: '-' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Departments & Courses -->
    <div class="col-xl-8">
        <!-- Departments -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-sitemap me-2"></i>Departments</span>
                <?php if (hasPermission('departments.create')): ?>
                <a href="<?= url('departments/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Code</th><th>HOD</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inst['departments'])): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">No departments</td></tr>
                            <?php else: ?>
                            <?php foreach ($inst['departments'] as $dept): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($dept['name']) ?></td>
                                <td><code><?= e($dept['code']) ?></code></td>
                                <td><?= e($dept['hod_name'] ?? '-') ?></td>
                                <td><span class="badge bg-<?= $dept['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($dept['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Courses -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-book me-2"></i>Courses</span>
                <?php if (hasPermission('courses.create')): ?>
                <a href="<?= url('courses/create') ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add</a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Course</th><th>Code</th><th>Degree</th><th>Duration</th><th>Seats</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if (empty($inst['courses'])): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No courses</td></tr>
                            <?php else: ?>
                            <?php foreach ($inst['courses'] as $course): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($course['name']) ?></td>
                                <td><code><?= e($course['code']) ?></code></td>
                                <td><span class="badge bg-info"><?= e(strtoupper($course['degree_type'])) ?></span></td>
                                <td><?= $course['duration_years'] ?> yrs</td>
                                <td><?= $course['total_seats'] ?? '-' ?></td>
                                <td><span class="badge bg-<?= $course['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($course['status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
