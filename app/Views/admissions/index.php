<?php $pageTitle = 'Admissions'; ?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-user-graduate me-2 text-primary"></i>Admissions</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Admissions</li>
            </ol>
        </nav>
    </div>
    <?php if (hasPermission('admissions.create')): ?>
        <a href="<?= url('admissions/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>New Application
        </a>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['applied'] ?? 0) ?></div>
                <div class="stat-label">Applied</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-sky py-3">
            <div class="stat-icon"><i class="fas fa-search"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['under_review'] ?? 0) ?></div>
                <div class="stat-label">Under Review</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-amber py-3">
            <div class="stat-icon"><i class="fas fa-file-upload"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['documents_pending'] ?? 0) ?></div>
                <div class="stat-label">Docs Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-emerald py-3">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['approved'] ?? 0) ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-violet py-3">
            <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['enrolled'] ?? 0) ?></div>
                <div class="stat-label">Enrolled</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-rose py-3">
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['rejected'] ?? 0) ?></div>
                <div class="stat-label">Rejected</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="search"
                       placeholder="Search name, number, phone..."
                       value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="status">
                    <option value="">All Status</option>
                    <?php foreach (['applied','under_review','documents_pending','approved','rejected','enrolled','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" name="course_id">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($filters['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_from"
                       placeholder="From" value="<?= e($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control form-control-sm" name="date_to"
                       placeholder="To" value="<?= e($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Total: <strong><?= number_format($admissions['total'] ?? 0) ?></strong> applications</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Adm #</th>
                        <th>Applicant</th>
                        <th>Phone</th>
                        <th>Course</th>
                        <th>Batch</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th class="text-end" style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admissions['data'])): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div style="font-size:2.5rem;opacity:.18;margin-bottom:.5rem"><i class="fas fa-inbox"></i></div>
                            <div class="text-muted fw-semibold">No admissions found</div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php
                    $statusMap = [
                        'applied'           => ['soft-primary', 'Applied'],
                        'under_review'      => ['soft-info',    'Under Review'],
                        'documents_pending' => ['soft-warning', 'Docs Pending'],
                        'approved'          => ['soft-success', 'Approved'],
                        'rejected'          => ['soft-danger',  'Rejected'],
                        'enrolled'          => ['soft-success', 'Enrolled'],
                        'cancelled'         => ['soft-danger',  'Cancelled'],
                    ];
                    foreach ($admissions['data'] as $adm):
                        [$badgeCls, $badgeLabel] = $statusMap[$adm['status']] ?? ['soft-primary', ucfirst($adm['status'])];
                    ?>
                    <tr>
                        <td>
                            <a href="<?= url('admissions/'.$adm['id']) ?>" class="text-primary fw-semibold">
                                <code><?= e($adm['admission_number']) ?></code>
                            </a>
                        </td>
                        <td>
                            <a href="<?= url('admissions/'.$adm['id']) ?>" class="text-dark fw-semibold text-decoration-none">
                                <?= e($adm['first_name'].' '.($adm['last_name'] ?? '')) ?>
                            </a>
                            <?php if (!empty($adm['email'])): ?>
                                <div class="small text-muted"><?= e($adm['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted"><?= e($adm['phone']) ?></td>
                        <td><small class="fw-semibold"><?= e($adm['course_name'] ?? '—') ?></small></td>
                        <td><small class="text-muted"><?= e($adm['batch_name'] ?? '—') ?></small></td>
                        <td><span class="badge bg-<?= $badgeCls ?>"><?= $badgeLabel ?></span></td>
                        <td><small class="text-muted"><?= formatDate($adm['created_at']) ?></small></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('admissions/'.$adm['id']) ?>" class="btn btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php if (hasPermission('admissions.edit') && !in_array($adm['status'], ['enrolled','cancelled'])): ?>
                                    <a href="<?= url('admissions/'.$adm['id'].'/edit') ?>" class="btn btn-outline-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (hasPermission('admissions.approve') && in_array($adm['status'], ['applied','under_review','documents_pending'])): ?>
                                    <form method="POST" action="<?= url('admissions/'.$adm['id'].'/approve') ?>" class="d-inline">
                                        <?= csrfField() ?>
                                        <button class="btn btn-outline-success" title="Quick Approve" onclick="return confirm('Approve this admission?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if (hasPermission('admissions.enroll') && $adm['status'] === 'approved'): ?>
                                    <form method="POST" action="<?= url('admissions/'.$adm['id'].'/enroll') ?>" class="d-inline">
                                        <?= csrfField() ?>
                                        <button class="btn btn-success" title="Enroll as Student" onclick="return confirm('Enroll this admission as a student?')">
                                            <i class="fas fa-graduation-cap"></i>
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
    <?php if (($admissions['last_page'] ?? 1) > 1): ?>
    <div class="card-footer">
        <?php
            $pagination = $admissions;
            $baseUrl    = url('admissions') . '?' . http_build_query(array_filter($filters ?? []));
            include BASE_PATH . '/app/Views/partials/pagination.php';
        ?>
    </div>
    <?php endif; ?>
</div>
