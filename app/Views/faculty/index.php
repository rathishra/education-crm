<?php $pageTitle = 'Faculty Management'; ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 class="mb-1"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Faculty Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Faculty</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('faculty/create') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-user-plus me-1"></i>Add Faculty
        </a>
        <a href="<?= url('faculty/export') ?>" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-csv me-1"></i>Export CSV
        </a>
        <div class="btn-group btn-group-sm" id="viewToggle">
            <button class="btn btn-outline-secondary active" id="btnGrid" title="Grid"><i class="fas fa-th-large"></i></button>
            <button class="btn btn-outline-secondary" id="btnList" title="List"><i class="fas fa-list"></i></button>
        </div>
    </div>
</div>

<!-- ── STAT CARDS ─────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                    <i class="fas fa-users text-primary fs-5"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold text-dark"><?= $totalFaculty ?></div>
                    <div class="text-muted small">Total Faculty</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                    <i class="fas fa-user-check text-success fs-5"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold text-dark"><?= $activeFaculty ?></div>
                    <div class="text-muted small">Active</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                    <i class="fas fa-calendar-times text-warning fs-5"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold text-dark"><?= $pendingLeaves ?></div>
                    <div class="text-muted small">Pending Leaves</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center" style="width:52px;height:52px">
                    <i class="fas fa-sitemap text-info fs-5"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold text-dark"><?= $deptCount ?></div>
                    <div class="text-muted small">Departments</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── FILTERS ────────────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control" placeholder="Search name, email, employee ID…" value="<?= e($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="dept_id" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['id'] ?>" <?= $deptId == $d['id'] ? 'selected' : '' ?>><?= e($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active"   <?= $status === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= url('faculty') ?>" class="btn btn-light btn-sm">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- ── GRID VIEW ──────────────────────────────────────────────── -->
<div id="gridView">
    <?php if (empty($faculty)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-chalkboard-teacher fa-3x mb-3 opacity-25"></i>
            <p class="mb-0">No faculty members found. Assign non-Student/Parent roles to users first.</p>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($faculty as $f):
            $initials = strtoupper(substr($f['first_name'],0,1) . substr($f['last_name'],0,1));
            $colors   = ['4f46e5','0891b2','059669','d97706','dc2626','7c3aed','db2777','0284c7'];
            $color    = $colors[crc32($f['email']) % count($colors)];
            $expYrs   = $f['total_experience_months'] ? round($f['total_experience_months']/12,1) . ' yrs' : '—';
        ?>
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100 faculty-card">
                <div class="card-body p-0">
                    <!-- Header band -->
                    <div class="rounded-top d-flex align-items-center gap-3 p-3"
                         style="background:linear-gradient(135deg,#<?= $color ?>22,#<?= $color ?>11)">
                        <?php if ($f['profile_photo']): ?>
                            <img src="<?= e($f['profile_photo']) ?>" class="rounded-circle" width="54" height="54" style="object-fit:cover;border:3px solid #<?= $color ?>55">
                        <?php else: ?>
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white fs-5"
                                 style="width:54px;height:54px;background:#<?= $color ?>;flex-shrink:0">
                                <?= $initials ?>
                            </div>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <div class="fw-bold text-dark lh-sm"><?= e($f['first_name'].' '.$f['last_name']) ?></div>
                            <div class="small text-muted text-truncate"><?= e($f['designation'] ?: 'No Designation') ?></div>
                            <?php if ($f['employee_id']): ?>
                            <div class="badge bg-light text-secondary border" style="font-size:.68rem"><?= e($f['employee_id']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($f['pending_leaves'] > 0): ?>
                        <span class="badge bg-warning text-dark ms-auto" title="Pending leave requests"><?= $f['pending_leaves'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Body -->
                    <div class="p-3">
                        <div class="small mb-2">
                            <i class="fas fa-building text-muted me-1" style="width:14px"></i>
                            <?= e($f['department_name'] ?: '— No Department') ?>
                        </div>
                        <div class="small mb-2">
                            <i class="fas fa-envelope text-muted me-1" style="width:14px"></i>
                            <span class="text-truncate d-inline-block" style="max-width:160px"><?= e($f['email']) ?></span>
                        </div>
                        <div class="row g-0 mt-3 text-center border rounded overflow-hidden">
                            <div class="col border-end py-2">
                                <div class="fw-bold text-primary"><?= (int)$f['subject_count'] ?></div>
                                <div class="text-muted" style="font-size:.65rem">SUBJECTS</div>
                            </div>
                            <div class="col border-end py-2">
                                <div class="fw-bold text-success"><?= (int)$f['weekly_hours'] ?></div>
                                <div class="text-muted" style="font-size:.65rem">HRS/WK</div>
                            </div>
                            <div class="col py-2">
                                <div class="fw-bold text-info"><?= $expYrs ?></div>
                                <div class="text-muted" style="font-size:.65rem">EXP</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent p-2 d-flex gap-1">
                    <a href="<?= url("faculty/{$f['user_id']}") ?>" class="btn btn-sm btn-primary flex-fill">
                        <i class="fas fa-eye me-1"></i>Profile
                    </a>
                    <a href="<?= url("faculty/{$f['user_id']}/edit") ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="<?= url("faculty/leave?faculty_id={$f['user_id']}") ?>" class="btn btn-sm btn-outline-warning" title="Leaves">
                        <i class="fas fa-calendar-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ── LIST VIEW ──────────────────────────────────────────────── -->
<div id="listView" style="display:none">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0" id="facultyTable">
                <thead class="table-light">
                    <tr>
                        <th>Faculty</th>
                        <th>Dept / Role</th>
                        <th>Designation</th>
                        <th class="text-center">Subjects</th>
                        <th class="text-center">Hrs/Wk</th>
                        <th class="text-center">Experience</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faculty as $f):
                        $expYrs = $f['total_experience_months'] ? round($f['total_experience_months']/12,1).' yrs' : '—';
                    ?>
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark"><?= e($f['first_name'].' '.$f['last_name']) ?></div>
                            <div class="small text-muted"><?= e($f['email']) ?></div>
                            <?php if ($f['employee_id']): ?>
                                <span class="badge bg-light text-secondary border" style="font-size:.65rem"><?= e($f['employee_id']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="small"><?= e($f['department_name'] ?: '—') ?></div>
                            <div class="small text-muted"><?= e($f['roles'] ?? '') ?></div>
                        </td>
                        <td><?= e($f['designation'] ?: '—') ?></td>
                        <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary"><?= (int)$f['subject_count'] ?></span></td>
                        <td class="text-center"><span class="badge bg-success bg-opacity-10 text-success"><?= (int)$f['weekly_hours'] ?></span></td>
                        <td class="text-center"><?= $expYrs ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $f['is_active'] ? 'success' : 'danger' ?>">
                                <?= $f['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                            <?php if ($f['pending_leaves'] > 0): ?>
                                <span class="badge bg-warning text-dark ms-1"><?= $f['pending_leaves'] ?> leave</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="<?= url("faculty/{$f['user_id']}") ?>" class="btn btn-sm btn-primary">View</a>
                            <a href="<?= url("faculty/{$f['user_id']}/edit") ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnGrid = document.getElementById('btnGrid');
    const btnList = document.getElementById('btnList');
    const gridV   = document.getElementById('gridView');
    const listV   = document.getElementById('listView');
    const saved   = localStorage.getItem('facultyView') || 'grid';

    function switchView(v) {
        if (v === 'list') {
            gridV.style.display = 'none'; listV.style.display = '';
            btnGrid.classList.remove('active'); btnList.classList.add('active');
        } else {
            listV.style.display = 'none'; gridV.style.display = '';
            btnList.classList.remove('active'); btnGrid.classList.add('active');
        }
        localStorage.setItem('facultyView', v);
    }

    switchView(saved);
    btnGrid.addEventListener('click', () => switchView('grid'));
    btnList.addEventListener('click', () => switchView('list'));

    // DataTable for list view
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#facultyTable').DataTable({ paging: true, searching: false, info: false, order: [] });
    }
});
</script>
