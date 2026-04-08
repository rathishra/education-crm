<style>
.thread-row { background:#fff; border-radius:12px; border:1px solid #e8e3ff; padding:.85rem 1rem; transition:box-shadow .15s; margin-bottom:.4rem; }
.thread-row:hover { box-shadow:0 3px 14px rgba(99,102,241,.1); }
.thread-row.pinned { border-left:3px solid #f59e0b; }
.th-badge { font-size:.65rem; font-weight:700; padding:.18rem .5rem; border-radius:8px; margin-right:.25rem; }
.cat-dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:.3rem; }
.avatar-sm { width:28px; height:28px; border-radius:50%; background:#ede9fe; color:#6366f1; display:flex; align-items:center; justify-content:center; font-size:.65rem; font-weight:800; flex-shrink:0; }
.filter-tab { font-size:.78rem; font-weight:600; padding:.3rem .75rem; border-radius:20px; border:1px solid #e2e8f0; color:#64748b; background:#fff; cursor:pointer; transition:all .12s; text-decoration:none; }
.filter-tab.active, .filter-tab:hover { background:#6366f1; color:#fff; border-color:#6366f1; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-comments me-2 text-primary"></i>Discussion Forum</h4>
    <a href="<?= url('elms/forum/create'.($courseId?"?course_id={$courseId}":'')) ?>" class="btn btn-primary btn-sm" style="border-radius:8px">
        <i class="fas fa-plus me-1"></i>New Thread
    </a>
</div>

<!-- Filters + Search -->
<form method="GET" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-sm-4 col-md-3">
            <select name="course" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Courses</option>
                <?php foreach ($myCourses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (!empty($categories)): ?>
        <div class="col-12 col-sm-4 col-md-3">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId==$cat['id']?'selected':'' ?>>
                    <?= e($cat['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-12 col-sm-4 col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text" style="background:#f8f7ff;border-color:#e8e3ff"><i class="fas fa-search text-muted"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search threads…" value="<?= e($search) ?>">
                <input type="hidden" name="course" value="<?= $courseId ?>">
                <input type="hidden" name="category" value="<?= $categoryId ?>">
            </div>
        </div>
        <?php if ($search || $courseId || $categoryId): ?>
        <div class="col-auto"><a href="<?= url('elms/forum') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-times"></i></a></div>
        <?php endif; ?>
    </div>
</form>

<!-- Filter tabs -->
<div class="d-flex gap-2 flex-wrap mb-3">
    <?php foreach (['latest'=>'Latest','popular'=>'Popular','solved'=>'Solved','unsolved'=>'Unsolved','mine'=>'My Threads'] as $k=>$l): ?>
    <a href="?course=<?= $courseId ?>&category=<?= $categoryId ?>&search=<?= urlencode($search) ?>&filter=<?= $k ?>"
       class="filter-tab <?= $filter===$k?'active':'' ?>"><?= $l ?></a>
    <?php endforeach; ?>
    <span class="ms-auto text-muted small align-self-center"><?= $total ?> thread<?= $total!=1?'s':'' ?></span>
</div>

<?php if (empty($threads)): ?>
<div class="text-center py-5" style="color:#94a3b8">
    <i class="fas fa-comments" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">No threads found<?= $search?' matching "'.e($search).'"':'' ?></p>
    <a href="<?= url('elms/forum/create') ?>" class="btn btn-sm btn-primary mt-1" style="border-radius:8px"><i class="fas fa-plus me-1"></i>Start a Discussion</a>
</div>
<?php else: ?>

<?php foreach ($threads as $t): ?>
<?php
$timeAgo = _timeAgo($t['last_post_at'] ?? $t['created_at']);
?>
<div class="thread-row <?= $t['is_pinned']?'pinned':'' ?>">
    <div class="d-flex align-items-start gap-3">
        <!-- Status column -->
        <div class="text-center flex-shrink-0" style="min-width:36px">
            <?php if ($t['is_solved']): ?>
            <div style="background:#d1fae5;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center" title="Solved">
                <i class="fas fa-check-circle" style="color:#16a34a;font-size:1rem"></i>
            </div>
            <?php else: ?>
            <div style="background:#f1f5f9;border-radius:50%;width:34px;height:34px;display:flex;align-items:center;justify-content:center">
                <i class="fas fa-comment-dots" style="color:#94a3b8;font-size:.9rem"></i>
            </div>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div style="flex:1;min-width:0">
            <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                <?php if ($t['is_pinned']): ?>
                <span class="th-badge" style="background:#fef3c7;color:#92400e"><i class="fas fa-thumbtack me-1"></i>Pinned</span>
                <?php endif; ?>
                <?php if ($t['is_locked']): ?>
                <span class="th-badge" style="background:#f1f5f9;color:#64748b"><i class="fas fa-lock me-1"></i>Locked</span>
                <?php endif; ?>
                <?php if (!empty($t['category_name'])): ?>
                <span class="th-badge" style="background:<?= e($t['category_color']) ?>22;color:<?= e($t['category_color']) ?>">
                    <span class="cat-dot" style="background:<?= e($t['category_color']) ?>"></span><?= e($t['category_name']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($t['course_title'])): ?>
                <span class="th-badge" style="background:#ede9fe;color:#4338ca"><?= e($t['course_title']) ?></span>
                <?php endif; ?>
            </div>

            <a href="<?= url('elms/forum/'.$t['id']) ?>" class="fw-bold text-decoration-none" style="color:#0f172a;font-size:.9rem">
                <?= e($t['title']) ?>
            </a>

            <div class="d-flex align-items-center gap-3 mt-1 flex-wrap" style="font-size:.72rem;color:#94a3b8">
                <span><div class="d-inline-flex align-items-center gap-1"><div class="avatar-sm"><?= strtoupper(substr($t['author_name'],0,2)) ?></div><?= e($t['author_name']) ?></div></span>
                <span><i class="fas fa-eye me-1"></i><?= $t['views'] ?></span>
                <span><i class="fas fa-reply me-1"></i><?= $t['reply_count'] ?> <?= $t['reply_count']==1?'reply':'replies' ?></span>
                <span class="ms-auto"><i class="fas fa-clock me-1"></i><?= $timeAgo ?> by <?= e($t['last_poster_name'] ?? $t['author_name']) ?></span>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if ($totalPages > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>">
        <a class="page-link" href="?course=<?= $courseId ?>&category=<?= $categoryId ?>&search=<?= urlencode($search) ?>&filter=<?= $filter ?>&page=<?= $p ?>"><?= $p ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>

<?php
function _timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff/60) . 'm ago';
    if ($diff < 86400)  return floor($diff/3600) . 'h ago';
    if ($diff < 604800) return floor($diff/86400) . 'd ago';
    return date('d M', strtotime($datetime));
}
?>
