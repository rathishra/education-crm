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

<!-- Pipeline Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="stat-card stat-indigo py-3">
            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['total']) ?></div>
                <div class="stat-label">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-warning py-3">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['pending']) ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-info py-3">
            <div class="stat-icon"><i class="fas fa-file-check"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['document_pending']) ?></div>
                <div class="stat-label">Docs Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-primary py-3">
            <div class="stat-icon"><i class="fas fa-money-bill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['payment_pending']) ?></div>
                <div class="stat-label">Pay Pending</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-success py-3">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['confirmed'] + $stats['enrolled']) ?></div>
                <div class="stat-label">Confirmed / Enrolled</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="stat-card stat-amber py-3">
            <div class="stat-icon"><i class="fas fa-calendar-plus"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= number_format($stats['this_month']) ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="<?= url('admissions') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, phone, email, number..." value="<?= e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <?php foreach (['draft'=>'Draft','pending'=>'Pending','document_pending'=>'Docs Pending','payment_pending'=>'Pay Pending','confirmed'=>'Confirmed','enrolled'=>'Enrolled','rejected'=>'Rejected','cancelled'=>'Cancelled'] as $v=>$l): ?>
                        <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= ($filters['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="course_id" class="form-select form-select-sm">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($filters['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Payment Status</option>
                    <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="partial" <?= ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial</option>
                    <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                </select>
            </div>
            <div class="col-6 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-search"></i></button>
                <a href="<?= url('admissions') ?>" class="btn btn-outline-secondary btn-sm flex-fill"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($admissions['data'])): ?>
            <div class="text-center py-5">
                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No admissions found</h5>
                <?php if (hasPermission('admissions.create')): ?>
                    <a href="<?= url('admissions/create') ?>" class="btn btn-primary mt-2"><i class="fas fa-plus me-1"></i>New Application</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Admission #</th>
                            <th>Applicant</th>
                            <th>Course / Department</th>
                            <th>Academic Year</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Applied</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admissions['data'] as $adm):
                            $statusLabels = \App\Models\Admission::STATUS_LABELS;
                            [$statusLabel, $statusClass] = $statusLabels[$adm['status']] ?? [ucfirst($adm['status']), 'bg-secondary'];
                            $payColors = ['pending'=>'warning','partial'=>'info','paid'=>'success'];
                            $payColor  = $payColors[$adm['payment_status'] ?? 'pending'] ?? 'secondary';
                        ?>
                        <tr>
                            <td>
                                <a href="<?= url('admissions/' . $adm['id']) ?>" class="fw-semibold text-primary text-decoration-none">
                                    <?= e($adm['admission_number']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= e($adm['first_name'] . ' ' . $adm['last_name']) ?></div>
                                <small class="text-muted"><?= e($adm['phone']) ?></small>
                            </td>
                            <td>
                                <div><?= e($adm['course_name'] ?? '—') ?></div>
                                <small class="text-muted"><?= e($adm['department_name'] ?? '') ?></small>
                            </td>
                            <td><?= e($adm['academic_year_name'] ?? '—') ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                            <td>
                                <span class="badge bg-<?= $payColor ?>-subtle text-<?= $payColor ?> border border-<?= $payColor ?>">
                                    <?= ucfirst($adm['payment_status'] ?? 'pending') ?>
                                </span>
                            </td>
                            <td><small><?= date('d M Y', strtotime($adm['application_date'] ?? $adm['created_at'])) ?></small></td>
                            <td class="text-end">
                                <a href="<?= url('admissions/' . $adm['id']) ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                <?php if (hasPermission('admissions.edit') && !in_array($adm['status'], ['enrolled', 'cancelled'])): ?>
                                    <a href="<?= url('admissions/' . $adm['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (($admissions['last_page'] ?? 1) > 1): ?>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                    <small class="text-muted">
                        Showing <?= number_format($admissions['from'] ?? 0) ?>–<?= number_format($admissions['to'] ?? 0) ?> of <?= number_format($admissions['total'] ?? 0) ?>
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= ($admissions['last_page'] ?? 1); $p++): ?>
                                <li class="page-item <?= $p === ($admissions['current_page'] ?? 1) ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('admissions?' . http_build_query(array_merge($filters, ['page' => $p]))) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
