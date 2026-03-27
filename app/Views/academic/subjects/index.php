<?php $pageTitle = 'Subjects'; ?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php
    $statCards = [
        ['label'=>'Total',    'val'=>$stats['total']??0,    'icon'=>'fa-book',         'color'=>'primary'],
        ['label'=>'Active',   'val'=>$stats['active']??0,   'icon'=>'fa-check-circle', 'color'=>'success'],
        ['label'=>'Theory',   'val'=>$stats['theory']??0,   'icon'=>'fa-chalkboard',   'color'=>'info'],
        ['label'=>'Lab',      'val'=>$stats['lab']??0,      'icon'=>'fa-flask',        'color'=>'warning'],
        ['label'=>'Elective', 'val'=>$stats['elective']??0, 'icon'=>'fa-star',         'color'=>'secondary'],
    ];
    foreach ($statCards as $c): ?>
    <div class="col-6 col-md-4 col-lg-2-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-3 fw-bold text-<?= $c['color'] ?>"><?= $c['val'] ?></div>
            <div class="text-muted small"><i class="fas <?= $c['icon'] ?> me-1"></i><?= $c['label'] ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filters + Action -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Code / Name…" value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    <?php foreach(['theory','lab','tutorial','project','elective'] as $t): ?>
                    <option value="<?= $t ?>" <?= $type===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="semester" class="form-select form-select-sm">
                    <option value="">All Semesters</option>
                    <?php for($i=1;$i<=10;$i++): ?>
                    <option value="<?= $i ?>" <?= $sem==$i?'selected':'' ?>>Sem <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active"   <?= $status==='active'  ?'selected':'' ?>>Active</option>
                    <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-sm btn-primary px-3"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="<?= url('academic/subjects') ?>" class="btn btn-sm btn-light">Reset</a>
                <a href="<?= url('academic/subjects/create') ?>" class="btn btn-sm btn-success ms-auto"><i class="fas fa-plus me-1"></i>Add Subject</a>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Subject Name</th>
                        <th>Type</th>
                        <th class="text-center">Sem</th>
                        <th class="text-center">Credits</th>
                        <th class="text-center">Hrs/Wk</th>
                        <th>Regulation</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subjects)): ?>
                    <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-book fa-2x d-block mb-2 opacity-25"></i>No subjects found.</td></tr>
                    <?php else: foreach ($subjects as $s): ?>
                    <tr>
                        <td class="ps-4"><span class="badge bg-secondary"><?= e($s['subject_code']) ?></span></td>
                        <td>
                            <div class="fw-semibold"><?= e($s['subject_name']) ?></div>
                            <?php if($s['dept_name']): ?><div class="text-muted small"><?= e($s['dept_name']) ?></div><?php endif; ?>
                        </td>
                        <td>
                            <?php $typeClr = ['theory'=>'primary','lab'=>'warning','tutorial'=>'info','project'=>'success','elective'=>'secondary'][$s['subject_type']]??'dark'; ?>
                            <span class="badge bg-<?= $typeClr ?>-subtle text-<?= $typeClr ?> border border-<?= $typeClr ?>-subtle">
                                <?= ucfirst($s['subject_type']) ?>
                                <?php if($s['is_elective']): ?> <i class="fas fa-star ms-1" title="Elective"></i><?php endif; ?>
                            </span>
                        </td>
                        <td class="text-center"><?= $s['semester'] ? 'Sem '.$s['semester'] : '—' ?></td>
                        <td class="text-center fw-bold text-primary"><?= number_format($s['credits'],1) ?></td>
                        <td class="text-center"><?= $s['hours_per_week'] ?>h</td>
                        <td class="text-muted small"><?= e($s['regulation'] ?? '—') ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $s['status']==='active'?'success':'danger' ?>-subtle text-<?= $s['status']==='active'?'success':'danger' ?> border border-<?= $s['status']==='active'?'success':'danger' ?>-subtle">
                                <?= ucfirst($s['status']) ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="<?= url('academic/subjects/edit/'.$s['id']) ?>" class="btn btn-sm btn-light text-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="<?= url('academic/subjects/delete/'.$s['id']) ?>" class="d-inline" onsubmit="return confirm('Delete this subject?')">
                                <?= csrfField() ?>
                                <button class="btn btn-sm btn-light text-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
