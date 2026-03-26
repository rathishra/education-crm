<?php $pageTitle = 'Admissions'; ?>
<div class="page-header">
    <div>
        <h1><i class="fas fa-user-graduate me-2"></i>Admissions</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li><li class="breadcrumb-item active">Admissions</li></ol></nav>
    </div>
    <?php if (hasPermission('admissions.create')): ?><a href="<?= url('admissions/create') ?>" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Application</a><?php endif; ?>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach ([['applied','primary','Applied'],['under_review','info','Under Review'],['approved','success','Approved'],['enrolled','dark','Enrolled'],['rejected','danger','Rejected']] as [$key,$color,$label]): ?>
    <div class="col-6 col-md-2">
        <div class="card text-center">
            <div class="card-body py-3">
                <h4 class="text-<?= $color ?> mb-0"><?= number_format($stats[$key] ?? 0) ?></h4>
                <small class="text-muted"><?= $label ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="search" placeholder="Search name, number, phone..." value="<?= e($filters['search'] ?? '') ?>"></div>
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
                    <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>" <?= ($filters['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><input type="date" class="form-control form-control-sm" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>"></div>
            <div class="col-md-2"><input type="date" class="form-control form-control-sm" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>"></div>
            <div class="col-md-1"><button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Total: <strong><?= number_format($admissions['total'] ?? 0) ?></strong></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Adm #</th><th>Applicant</th><th>Phone</th><th>Course</th><th>Batch</th><th>Status</th><th>Date</th><th width="120">Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($admissions['data'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No admissions found</td></tr>
                    <?php else: ?>
                    <?php foreach ($admissions['data'] as $adm):
                        $sc = ['applied'=>'primary','under_review'=>'info','documents_pending'=>'warning','approved'=>'success','rejected'=>'danger','enrolled'=>'dark','cancelled'=>'secondary'];
                        $c = $sc[$adm['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><a href="<?= url('admissions/'.$adm['id']) ?>"><code><?= e($adm['admission_number']) ?></code></a></td>
                        <td><a href="<?= url('admissions/'.$adm['id']) ?>" class="fw-semibold"><?= e($adm['first_name'].' '.($adm['last_name']??'')) ?></a></td>
                        <td><?= e($adm['phone']) ?></td>
                        <td><small><?= e($adm['course_name']??'-') ?></small></td>
                        <td><small><?= e($adm['batch_name']??'-') ?></small></td>
                        <td><span class="badge bg-<?= $c ?>"><?= ucfirst(str_replace('_',' ',$adm['status'])) ?></span></td>
                        <td><small class="text-muted"><?= formatDate($adm['created_at']) ?></small></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('admissions/'.$adm['id']) ?>" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <?php if (hasPermission('admissions.edit') && !in_array($adm['status'],['enrolled','cancelled'])): ?><a href="<?= url('admissions/'.$adm['id'].'/edit') ?>" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a><?php endif; ?>
                                <?php if (hasPermission('admissions.approve') && in_array($adm['status'],['applied','under_review','documents_pending'])): ?>
                                <form method="POST" action="<?= url('admissions/'.$adm['id'].'/approve') ?>" class="d-inline"><?= csrfField() ?><button class="btn btn-outline-success" title="Approve" onclick="return confirm('Approve?')"><i class="fas fa-check"></i></button></form>
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
    <div class="card-footer"><?php $pagination = $admissions; $baseUrl = url('admissions').'?'.http_build_query(array_filter($filters??[])); include BASE_PATH.'/app/Views/partials/pagination.php'; ?></div>
    <?php endif; ?>
</div>
