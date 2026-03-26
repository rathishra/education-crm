<?php $pageTitle = 'Student Dashboard'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-chart-pie me-2"></i>Student Dashboard</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('students') ?>">Students</a></li>
                <li class="breadcrumb-item active">Analytics</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('students/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Student</a>
        <a href="<?= url('students') ?>" class="btn btn-outline-secondary"><i class="fas fa-users me-1"></i> Directory</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Stat Cards -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-start border-primary border-4 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Students</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total']) ?></div></div>
                    <div class="col-auto"><i class="fas fa-users fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-start border-success border-4 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Students</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_active']) ?></div></div>
                    <div class="col-auto"><i class="fas fa-user-check fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-start border-info border-4 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">New This Month</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['new_this_month']) ?></div></div>
                    <div class="col-auto"><i class="fas fa-user-plus fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-start border-warning border-4 py-2">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Alumni / Graduated</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($stats['total_graduated']) ?></div></div>
                    <div class="col-auto"><i class="fas fa-user-graduate fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Course Wise Distribution -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light"><h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-book me-2"></i>Course Wise Distribution</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Course Name</th><th class="text-end">Student Count</th></tr></thead>
                        <tbody>
                            <?php if (empty($stats['course_wise'])): ?>
                                <tr><td colspan="2" class="text-muted text-center py-3">No courses found</td></tr>
                            <?php else: ?>
                                <?php foreach ($stats['course_wise'] as $c): ?>
                                <tr>
                                    <td><?= e($c['course_name'] ?: 'Unassigned') ?></td>
                                    <td class="text-end fw-bold"><?= number_format($c['cnt']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Wise Distribution -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light"><h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-sitemap me-2"></i>Department Wise Distribution</h6></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Department</th><th class="text-end">Student Count</th></tr></thead>
                        <tbody>
                            <?php if (empty($departmentWise)): ?>
                                <tr><td colspan="2" class="text-muted text-center py-3">No departments mapped</td></tr>
                            <?php else: ?>
                                <?php foreach ($departmentWise as $d): ?>
                                <tr>
                                    <td><?= e($d['department_name']) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($d['cnt']) ?></td>
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
