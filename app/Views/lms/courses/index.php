<?php
$pageTitle = 'LMS Courses';
$isAdmin     = in_array($lmsUser['role'] ?? '', ['lms_admin']);
$isInstructor = in_array($lmsUser['role'] ?? '', ['lms_admin','instructor']);
?>
<style>
.lms-course-card {
    background:#fff; border-radius:14px; border:1px solid #e8e3ff;
    box-shadow:0 1px 6px rgba(99,102,241,.06); overflow:hidden;
    transition:transform .15s,box-shadow .15s; display:flex; flex-direction:column;
}
.lms-course-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(99,102,241,.14); }
.lms-course-thumb {
    height:140px; background:linear-gradient(135deg,#4f46e5,#7c3aed);
    display:flex; align-items:center; justify-content:center;
    font-size:2.8rem; color:rgba(255,255,255,.35); position:relative; overflow:hidden;
}
.lms-course-thumb img { width:100%; height:100%; object-fit:cover; position:absolute; inset:0; }
.lms-course-thumb-icon { position:relative; z-index:1; }
.lms-badge-status {
    position:absolute; top:.6rem; left:.6rem; z-index:2;
    font-size:.62rem; font-weight:700; padding:2px 8px; border-radius:10px; text-transform:uppercase; letter-spacing:.04em;
}
.lms-badge-level {
    position:absolute; top:.6rem; right:.6rem; z-index:2;
    background:rgba(0,0,0,.45); color:#fff;
    font-size:.62rem; font-weight:700; padding:2px 8px; border-radius:10px; text-transform:capitalize;
}
.lms-course-body { padding:1rem; flex:1; display:flex; flex-direction:column; }
.lms-stat-pill { display:inline-flex; align-items:center; gap:.3rem; font-size:.7rem; color:#64748b; }
.status-draft     { background:#fef3c7; color:#92400e; }
.status-published { background:#d1fae5; color:#065f46; }
.status-archived  { background:#f1f5f9; color:#64748b; }
.status-coming_soon { background:#dbeafe; color:#1e40af; }
.lms-filters { background:#fff; border-radius:12px; border:1px solid #e8e3ff; padding:1rem 1.25rem; margin-bottom:1.25rem; }
</style>

<!-- PAGE HEADER -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-book-open me-2 text-primary"></i>Courses</h4>
        <div class="text-muted small mt-1"><?= number_format($total) ?> course<?= $total != 1 ? 's' : '' ?> found</div>
    </div>
    <?php if ($isInstructor): ?>
    <a href="<?= url('elms/courses/create') ?>" class="btn btn-primary" style="border-radius:9px">
        <i class="fas fa-plus me-2"></i>New Course
    </a>
    <?php endif; ?>
</div>

<!-- FILTERS -->
<div class="lms-filters">
    <form method="GET" action="<?= url('elms/courses') ?>" class="row g-2 align-items-end">
        <div class="col-12 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text border-end-0 bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search courses…" value="<?= e($search) ?>">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="draft"       <?= $status==='draft'       ?'selected':'' ?>>Draft</option>
                <option value="published"   <?= $status==='published'   ?'selected':'' ?>>Published</option>
                <option value="archived"    <?= $status==='archived'    ?'selected':'' ?>>Archived</option>
                <option value="coming_soon" <?= $status==='coming_soon' ?'selected':'' ?>>Coming Soon</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="level" class="form-select form-select-sm">
                <option value="">All Levels</option>
                <option value="beginner"     <?= $level==='beginner'     ?'selected':'' ?>>Beginner</option>
                <option value="intermediate" <?= $level==='intermediate' ?'selected':'' ?>>Intermediate</option>
                <option value="advanced"     <?= $level==='advanced'     ?'selected':'' ?>>Advanced</option>
                <option value="all_levels"   <?= $level==='all_levels'   ?'selected':'' ?>>All Levels</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $catId==(int)$cat['id'] ?'selected':'' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="border-radius:8px">
                <i class="fas fa-filter me-1"></i>Filter
            </button>
            <?php if ($search || $status || $catId || $level): ?>
            <a href="<?= url('elms/courses') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px" title="Clear"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- COURSE GRID -->
<?php if (empty($courses)): ?>
<div class="text-center py-5">
    <div style="font-size:3.5rem;opacity:.15;color:#6366f1"><i class="fas fa-book-open"></i></div>
    <h5 class="fw-bold mt-3 mb-1 text-muted">No courses found</h5>
    <p class="text-muted small">
        <?= $search || $status || $catId || $level ? 'Try adjusting your filters.' : 'Create your first course to get started.' ?>
    </p>
    <?php if ($isInstructor && !$search && !$status && !$catId && !$level): ?>
    <a href="<?= url('elms/courses/create') ?>" class="btn btn-primary mt-2" style="border-radius:9px">
        <i class="fas fa-plus me-2"></i>Create First Course
    </a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($courses as $c): ?>
    <?php
    $statusConf = [
        'draft'       => ['Draft',        'status-draft'],
        'published'   => ['Published',    'status-published'],
        'archived'    => ['Archived',     'status-archived'],
        'coming_soon' => ['Coming Soon',  'status-coming_soon'],
    ];
    [$statusLabel, $statusClass] = $statusConf[$c['status']] ?? ['Unknown', 'status-draft'];
    $thumbColors = ['#4f46e5,#7c3aed','#0891b2,#0e7490','#059669,#047857','#d97706,#b45309','#dc2626,#b91c1c'];
    $colorPair   = $thumbColors[$c['id'] % count($thumbColors)];
    ?>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="lms-course-card h-100">
            <!-- Thumbnail -->
            <div class="lms-course-thumb" style="background:linear-gradient(135deg,<?= $colorPair ?>)">
                <?php if (!empty($c['thumbnail'])): ?>
                <img src="<?= asset($c['thumbnail']) ?>" alt="">
                <?php else: ?>
                <i class="fas fa-book lms-course-thumb-icon"></i>
                <?php endif; ?>
                <span class="lms-badge-status <?= $statusClass ?>"><?= $statusLabel ?></span>
                <span class="lms-badge-level"><?= e(str_replace('_', ' ', $c['level'])) ?></span>
            </div>
            <!-- Body -->
            <div class="lms-course-body">
                <div class="mb-1">
                    <?php if (!empty($c['category_name'])): ?>
                    <span class="badge" style="background:<?= e($c['cat_color'] ?? '#6366f1') ?>1a;color:<?= e($c['cat_color'] ?? '#6366f1') ?>;font-size:.65rem;border-radius:6px"><?= e($c['category_name']) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= url('elms/courses/' . $c['id']) ?>" class="text-decoration-none">
                    <h6 class="fw-bold mb-1 text-dark" style="font-size:.9rem;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?= e($c['title']) ?></h6>
                </a>
                <div class="text-muted small mb-2" style="font-size:.75rem"><?= e($c['instructor_name']) ?></div>
                <!-- Stats row -->
                <div class="d-flex gap-3 mt-auto pt-2 border-top flex-wrap">
                    <span class="lms-stat-pill"><i class="fas fa-users text-primary"></i><?= number_format($c['enroll_cnt']) ?> enrolled</span>
                    <span class="lms-stat-pill"><i class="fas fa-play-circle text-success"></i><?= (int)$c['lesson_cnt'] ?> lessons</span>
                    <?php if ($c['rating_avg'] > 0): ?>
                    <span class="lms-stat-pill"><i class="fas fa-star text-warning"></i><?= number_format($c['rating_avg'],1) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Footer actions -->
            <div class="px-3 py-2 border-top d-flex justify-content-between align-items-center" style="background:#fafaf9">
                <a href="<?= url('elms/courses/' . $c['id']) ?>" class="btn btn-xs btn-outline-primary" style="font-size:.75rem;padding:3px 10px;border-radius:7px">
                    <i class="fas fa-cog me-1"></i>Manage
                </a>
                <div class="d-flex gap-1">
                    <?php if ($isInstructor): ?>
                    <a href="<?= url('elms/courses/' . $c['id'] . '/edit') ?>" class="btn btn-xs btn-outline-secondary" style="font-size:.72rem;padding:3px 8px;border-radius:7px" title="Edit"><i class="fas fa-edit"></i></a>
                    <button class="btn btn-xs btn-outline-<?= $c['status']==='published'?'warning':'success' ?> btn-toggle-status"
                        data-id="<?= $c['id'] ?>" style="font-size:.72rem;padding:3px 8px;border-radius:7px"
                        title="<?= $c['status']==='published'?'Unpublish':'Publish' ?>">
                        <i class="fas fa-<?= $c['status']==='published'?'eye-slash':'globe' ?>"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- PAGINATION -->
<?php if ($totalPages > 1): ?>
<nav class="mt-4">
    <ul class="pagination pagination-sm justify-content-center" style="gap:4px">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link" style="border-radius:7px" href="<?= url('elms/courses') ?>?page=<?= $p ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&level=<?= urlencode($level) ?>&category=<?= $catId ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>

<script>
document.querySelectorAll('.btn-toggle-status').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        fetch(`<?= url('elms/courses') ?>/${id}/toggle-status`, {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}
        }).then(r => r.json()).then(d => {
            if (d.status === 'ok') location.reload();
        });
    });
});
</script>
