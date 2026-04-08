<style>
.stu-row { background:#fff; border-radius:12px; border:1px solid #e8e3ff; padding:.85rem 1rem; margin-bottom:.4rem; display:flex; align-items:center; gap:.85rem; transition:box-shadow .12s; }
.stu-row:hover { box-shadow:0 2px 12px rgba(99,102,241,.1); }
.stu-avatar { width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.88rem; font-weight:700; flex-shrink:0; color:#fff; }
.stu-badge { font-size:.68rem; font-weight:600; padding:.15rem .5rem; border-radius:20px; }
.prog-bar  { height:5px; border-radius:3px; background:#e2e8f0; overflow:hidden; width:90px; }
.prog-fill { height:100%; border-radius:3px; background:#6366f1; }
.filter-tab { font-size:.78rem; font-weight:600; padding:.3rem .85rem; border-radius:20px; border:1px solid #e2e8f0; color:#64748b; background:#fff; cursor:pointer; text-decoration:none; transition:all .12s; }
.filter-tab.active { background:#6366f1; color:#fff; border-color:#6366f1; }
</style>

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a">
        <i class="fas fa-user-graduate me-2 text-primary"></i>LMS Students
        <span class="badge ms-1" style="background:#ede9fe;color:#6366f1;font-size:.7rem;border-radius:20px"><?= number_format($total) ?></span>
    </h4>
    <a href="<?= url('elms/sync') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px">
        <i class="fas fa-sync-alt me-1"></i>Sync from Academic
    </a>
</div>

<!-- Filters row -->
<form method="GET" class="mb-3">
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <!-- Search -->
        <div class="input-group input-group-sm" style="max-width:260px">
            <span class="input-group-text" style="border-radius:8px 0 0 8px;background:#f8fafc"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="q" class="form-control" style="border-radius:0 8px 8px 0" placeholder="Name, email, roll no…" value="<?= e($search) ?>">
        </div>

        <!-- Course filter -->
        <?php if (!empty($courses)): ?>
        <select name="course" class="form-select form-select-sm" style="max-width:200px;border-radius:8px" onchange="this.form.submit()">
            <option value="">All Courses</option>
            <?php foreach ($courses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>>
                <?= e($c['subject_code'] ?: $c['code']) ?> — <?= e($c['subject_name'] ?: $c['title']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>

        <!-- Status tabs -->
        <div class="d-flex gap-1 ms-auto">
            <a href="?q=<?= urlencode($search) ?>&course=<?= $courseId ?>&status=all"    class="filter-tab <?= $status==='all'?'active':'' ?>">All</a>
            <a href="?q=<?= urlencode($search) ?>&course=<?= $courseId ?>&status=active" class="filter-tab <?= $status==='active'?'active':'' ?>">Active</a>
            <a href="?q=<?= urlencode($search) ?>&course=<?= $courseId ?>&status=inactive" class="filter-tab <?= $status==='inactive'?'active':'' ?>">Inactive</a>
        </div>

        <?php if ($search || $courseId): ?>
        <a href="?" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($students)): ?>
<div class="text-center py-5" style="color:#94a3b8">
    <i class="fas fa-user-graduate" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">No students found</p>
    <?php if (!$search && !$courseId): ?>
    <a href="<?= url('elms/sync') ?>" class="btn btn-sm btn-outline-primary mt-1" style="border-radius:8px">
        <i class="fas fa-sync-alt me-1"></i>Sync students from Academic module
    </a>
    <?php endif; ?>
</div>
<?php else: ?>

<!-- Table header -->
<div class="d-flex align-items-center gap-2 px-1 mb-1" style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8">
    <div style="width:42px;flex-shrink:0"></div>
    <div style="flex:1">Student</div>
    <div style="width:150px;display:none" class="d-none d-md-block">Program / Batch</div>
    <div style="width:110px;text-align:center" class="d-none d-lg-block">Courses</div>
    <div style="width:110px" class="d-none d-lg-block">Progress</div>
    <div style="width:80px;text-align:center" class="d-none d-md-block">XP</div>
    <div style="width:70px"></div>
</div>

<?php foreach ($students as $s):
    // Avatar colour from name
    $colors = ['#6366f1','#0284c7','#059669','#d97706','#dc2626','#8b5cf6','#10b981','#f59e0b'];
    $col = $colors[abs(crc32($s['first_name'])) % count($colors)];
    $initials = strtoupper(substr($s['first_name'],0,1).substr($s['last_name'] ?? '',0,1));
    $prog = (int)($s['avg_progress'] ?? 0);
    $lastSeen = '';
    if ($s['last_active_at']) {
        $diff = time() - strtotime($s['last_active_at']);
        $lastSeen = $diff < 3600 ? 'Active '.floor($diff/60).'m ago'
                  : ($diff < 86400 ? 'Active '.floor($diff/3600).'h ago'
                  : 'Active '.floor($diff/86400).'d ago');
    }
?>
<div class="stu-row">
    <!-- Avatar -->
    <?php if (!empty($s['photo']) || !empty($s['avatar'])): ?>
    <img src="<?= e($s['photo'] ?? $s['avatar']) ?>" class="stu-avatar" style="object-fit:cover" alt="">
    <?php else: ?>
    <div class="stu-avatar" style="background:<?= $col ?>; font-size:.82rem"><?= $initials ?></div>
    <?php endif; ?>

    <!-- Name + meta -->
    <div style="flex:1;min-width:0">
        <div class="fw-semibold" style="color:#0f172a;font-size:.9rem">
            <a href="<?= url('elms/students/'.$s['id']) ?>" class="text-decoration-none" style="color:inherit">
                <?= e($s['first_name'].' '.($s['last_name'] ?? '')) ?>
            </a>
            <?php if ($s['status'] !== 'active'): ?>
            <span class="stu-badge ms-1" style="background:#fee2e2;color:#dc2626"><?= ucfirst($s['status']) ?></span>
            <?php endif; ?>
        </div>
        <div class="text-muted" style="font-size:.72rem">
            <?= e($s['email']) ?>
            <?php if ($s['student_id_number']): ?>
            &middot; <span style="color:#6366f1"><?= e($s['student_id_number']) ?></span>
            <?php endif; ?>
            <?php if ($lastSeen): ?>
            &middot; <span><?= $lastSeen ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Program / Batch -->
    <div style="width:150px;min-width:0" class="d-none d-md-block">
        <?php if ($s['program_name']): ?>
        <div class="small fw-semibold" style="color:#374151;font-size:.78rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($s['program_name']) ?></div>
        <?php endif; ?>
        <?php if ($s['batch_name']): ?>
        <div class="text-muted" style="font-size:.7rem"><?= e($s['batch_name']) ?><?= $s['current_semester'] ? ' · Sem '.$s['current_semester'] : '' ?></div>
        <?php endif; ?>
        <?php if (!$s['program_name'] && !$s['batch_name']): ?>
        <span class="text-muted" style="font-size:.72rem">—</span>
        <?php endif; ?>
    </div>

    <!-- Courses -->
    <div style="width:110px;text-align:center" class="d-none d-lg-block">
        <span class="stu-badge" style="background:#ede9fe;color:#6366f1"><?= (int)$s['enrolled_courses'] ?> enrolled</span>
        <?php if ($s['completed_courses'] > 0): ?>
        <div class="text-muted" style="font-size:.68rem;margin-top:.15rem"><?= (int)$s['completed_courses'] ?> done</div>
        <?php endif; ?>
    </div>

    <!-- Progress bar -->
    <div style="width:110px" class="d-none d-lg-block">
        <div class="d-flex align-items-center gap-1">
            <div class="prog-bar" style="flex:1">
                <div class="prog-fill" style="width:<?= $prog ?>%"></div>
            </div>
            <span style="font-size:.7rem;color:#64748b;width:28px"><?= $prog ?>%</span>
        </div>
    </div>

    <!-- XP -->
    <div style="width:80px;text-align:center" class="d-none d-md-block">
        <span style="font-size:.78rem;font-weight:700;color:#f59e0b"><i class="fas fa-star" style="font-size:.65rem"></i> <?= number_format($s['xp_points']) ?></span>
        <div style="font-size:.65rem;color:#94a3b8">Lv <?= $s['level'] ?></div>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-1" style="width:70px;flex-shrink:0">
        <a href="<?= url('elms/students/'.$s['id']) ?>" class="btn btn-sm btn-outline-primary" style="border-radius:7px;font-size:.7rem;padding:.25rem .55rem" title="View Profile">
            <i class="fas fa-eye"></i>
        </a>
        <a href="<?= url('elms/gradebook') ?>?student=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:.7rem;padding:.25rem .55rem" title="Gradebook">
            <i class="fas fa-star-half-alt"></i>
        </a>
    </div>
</div>
<?php endforeach; ?>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>">
        <a class="page-link" href="?q=<?= urlencode($search) ?>&course=<?= $courseId ?>&status=<?= $status ?>&page=<?= $p ?>"><?= $p ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>

<?php endif; ?>
