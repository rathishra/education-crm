<style>
.ann-card { border-radius:14px; border:2px solid transparent; padding:1.1rem 1.25rem; margin-bottom:.75rem; }
.ann-info    { background:#eff6ff; border-color:#bfdbfe; }
.ann-success { background:#f0fdf4; border-color:#bbf7d0; }
.ann-warning { background:#fffbeb; border-color:#fde68a; }
.ann-danger  { background:#fff1f2; border-color:#fecdd3; }
.ann-header  { display:flex; align-items:flex-start; gap:.75rem; }
.ann-type-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:.95rem; flex-shrink:0; }
</style>

<?php
$typeConfig = [
    'info'    => ['bg'=>'#dbeafe','ic'=>'#2563eb','icon'=>'fas fa-info-circle',    'label'=>'Info'],
    'success' => ['bg'=>'#d1fae5','ic'=>'#059669','icon'=>'fas fa-check-circle',   'label'=>'Success'],
    'warning' => ['bg'=>'#fef3c7','ic'=>'#d97706','icon'=>'fas fa-exclamation-triangle','label'=>'Warning'],
    'danger'  => ['bg'=>'#fee2e2','ic'=>'#dc2626','icon'=>'fas fa-times-circle',   'label'=>'Alert'],
];
$isInstructor = $lmsUser && in_array($lmsUser['role'],['instructor','lms_admin']);
?>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a"><i class="fas fa-bullhorn me-2 text-primary"></i>Announcements</h4>
    <div class="d-flex gap-2">
        <a href="<?= url('elms/notifications') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-bell me-1"></i>Notifications</a>
        <?php if ($isInstructor): ?>
        <a href="<?= url('elms/announcements/create') ?>" class="btn btn-sm btn-primary" style="border-radius:8px"><i class="fas fa-plus me-1"></i>New Announcement</a>
        <?php endif; ?>
    </div>
</div>

<!-- Course filter -->
<?php if (!empty($myCourses)): ?>
<form method="GET" class="mb-3">
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <select name="course" class="form-select form-select-sm" style="max-width:260px" onchange="this.form.submit()">
            <option value="">All Courses + Institution-wide</option>
            <?php foreach ($myCourses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $courseId==$c['id']?'selected':'' ?>><?= e($c['title']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($courseId): ?>
        <a href="<?= url('elms/announcements') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:8px"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </div>
</form>
<?php endif; ?>

<?php if (empty($announcements)): ?>
<div class="text-center py-5" style="color:#94a3b8">
    <i class="fas fa-bullhorn" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold">No announcements at this time</p>
</div>
<?php else: ?>

<?php foreach ($announcements as $ann): ?>
<?php
$tc  = $typeConfig[$ann['type']] ?? $typeConfig['info'];
$cls = 'ann-' . $ann['type'];
?>
<div class="ann-card <?= $cls ?>" id="ann<?= $ann['id'] ?>">
    <div class="ann-header">
        <div class="ann-type-icon" style="background:<?= $tc['bg'] ?>">
            <i class="<?= $tc['icon'] ?>" style="color:<?= $tc['ic'] ?>"></i>
        </div>
        <div style="flex:1;min-width:0">
            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                <div>
                    <div class="fw-bold" style="color:#0f172a;font-size:.92rem"><?= e($ann['title']) ?></div>
                    <div class="text-muted small mt-1">
                        <i class="fas fa-user me-1"></i><?= e($ann['author_name']) ?>
                        <?php if ($ann['course_title']): ?>
                        &middot; <i class="fas fa-book-open me-1"></i><?= e($ann['course_title']) ?>
                        <?php else: ?>
                        &middot; <span style="color:<?= $tc['ic'] ?>">Institution-wide</span>
                        <?php endif; ?>
                        &middot; <i class="fas fa-clock me-1"></i><?= date('d M Y', strtotime($ann['created_at'])) ?>
                        <?php if ($ann['expires_at']): ?>
                        &middot; <i class="fas fa-calendar-times me-1 text-danger"></i>Expires <?= date('d M Y', strtotime($ann['expires_at'])) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <?php if ($isInstructor): ?>
                    <a href="<?= url('elms/announcements/'.$ann['id'].'/edit') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:.72rem"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="<?= url('elms/announcements/'.$ann['id'].'/delete') ?>" onsubmit="return confirm('Delete?')" class="d-inline">
                        <?= csrfField() ?>
                        <button class="btn btn-sm btn-outline-danger" style="border-radius:7px;font-size:.72rem"><i class="fas fa-trash"></i></button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" style="border-radius:7px;font-size:.7rem" onclick="dismiss(<?= $ann['id'] ?>)" title="Dismiss"><i class="fas fa-times"></i></button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="mt-2" style="font-size:.85rem;color:#374151;line-height:1.7"><?= nl2br(e($ann['body'])) ?></div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if ($totalPages > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>">
        <a class="page-link" href="?course=<?= $courseId ?>&page=<?= $p ?>"><?= $p ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>

<script>
const CSRF = '<?= csrfToken() ?>';

function dismiss(id) {
    fetch('<?= url('elms/announcements') ?>/'+id+'/dismiss', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF}),
    }).then(r=>r.json()).then(d=>{
        if (d.status==='ok') {
            const el = document.getElementById('ann'+id);
            if (el) { el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(()=>el.remove(),300); }
        }
    });
}
</script>
