<?php $pageTitle = 'Staff Directory'; ?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-users-cog me-2"></i>Staff Directory</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">HR & Staff</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Staff Member</th>
                        <th>Role & Dept</th>
                        <th>Designation</th>
                        <th>Salary Pkg (Annual)</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staff)): ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No staff found. (Assign roles other than Student/Parent to users)</td></tr>
                    <?php else: ?>
                        <?php foreach ($staff as $s): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-primary"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                <div class="small text-muted"><i class="fas fa-envelope me-1"></i><?= e($s['email']) ?></div>
                            </td>
                            <td>
                                <div><span class="badge bg-secondary"><?= e($s['roles']) ?></span></div>
                                <div class="small text-muted mt-1"><?= e($s['department_name'] ?: 'No Department') ?></div>
                            </td>
                            <td class="fw-semibold text-dark">
                                <?= e($s['designation'] ?: '—') ?>
                            </td>
                            <td>
                                <?php if ($s['salary_package']): ?>
                                    <span class="fw-bold text-success"><?= formatCurrency($s['salary_package']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Not Set</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($s['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <a href="<?= url("hr/staff/{$s['user_id']}/edit") ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i>HR Profile</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
