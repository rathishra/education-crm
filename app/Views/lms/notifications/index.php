<style>
.notif-row { background:#fff; border-radius:12px; border:1px solid #e8e3ff; padding:.85rem 1rem; margin-bottom:.4rem; transition:background .1s,box-shadow .1s; display:flex; align-items:flex-start; gap:.9rem; }
.notif-row.unread { background:#f8f7ff; border-left:3px solid #6366f1; }
.notif-row:hover { box-shadow:0 2px 12px rgba(99,102,241,.1); }
.notif-icon { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.9rem; flex-shrink:0; }
.notif-dot { width:8px; height:8px; border-radius:50%; background:#6366f1; flex-shrink:0; margin-top:.35rem; }
.filter-tab { font-size:.78rem; font-weight:600; padding:.3rem .85rem; border-radius:20px; border:1px solid #e2e8f0; color:#64748b; background:#fff; cursor:pointer; text-decoration:none; transition:all .12s; }
.filter-tab.active { background:#6366f1; color:#fff; border-color:#6366f1; }
</style>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0" style="color:#0f172a">
        <i class="fas fa-bell me-2 text-primary"></i>Notifications
        <?php if ($unreadCount > 0): ?>
        <span class="badge ms-1" style="background:#ef4444;color:#fff;border-radius:20px;font-size:.7rem"><?= $unreadCount ?></span>
        <?php endif; ?>
    </h4>
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($unreadCount > 0): ?>
        <button class="btn btn-sm btn-outline-primary" style="border-radius:8px" onclick="markAllRead()"><i class="fas fa-check-double me-1"></i>Mark all read</button>
        <?php endif; ?>
        <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px" onclick="clearRead()"><i class="fas fa-trash me-1"></i>Clear read</button>
        <a href="<?= url('elms/announcements') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px"><i class="fas fa-bullhorn me-1"></i>Announcements</a>
    </div>
</div>

<!-- Filter tabs -->
<div class="d-flex gap-2 mb-3">
    <a href="?filter=all"    class="filter-tab <?= $filter==='all'?'active':'' ?>">All (<?= $total ?>)</a>
    <a href="?filter=unread" class="filter-tab <?= $filter==='unread'?'active':'' ?>">Unread (<?= $unreadCount ?>)</a>
</div>

<?php if (empty($notifications)): ?>
<div class="text-center py-5" style="color:#94a3b8">
    <i class="fas fa-bell-slash" style="font-size:2.5rem;opacity:.15"></i>
    <p class="mt-2 small fw-semibold"><?= $filter==='unread'?'No unread notifications':'No notifications yet' ?></p>
</div>
<?php else: ?>

<div id="notifList">
<?php foreach ($notifications as $n): ?>
<?php
$timeAgo = _notifTimeAgo($n['created_at']);
$isUnread = !$n['is_read'];
?>
<div class="notif-row <?= $isUnread?'unread':'' ?>" id="notif<?= $n['id'] ?>">
    <!-- Icon -->
    <div class="notif-icon" style="background:<?= e($n['color']) ?>22">
        <i class="<?= e($n['icon']) ?>" style="color:<?= e($n['color']) ?>"></i>
    </div>

    <!-- Content -->
    <div style="flex:1;min-width:0">
        <div class="d-flex align-items-start justify-content-between gap-2">
            <div style="flex:1;min-width:0">
                <div class="fw-semibold small" style="color:#0f172a">
                    <?php if ($n['link']): ?>
                    <a href="<?= e($n['link']) ?>" class="text-decoration-none" style="color:inherit" onclick="markRead(<?= $n['id'] ?>)"><?= e($n['title']) ?></a>
                    <?php else: ?>
                    <?= e($n['title']) ?>
                    <?php endif; ?>
                </div>
                <?php if ($n['body']): ?>
                <div class="text-muted mt-1" style="font-size:.78rem;line-height:1.5"><?= e($n['body']) ?></div>
                <?php endif; ?>
                <div class="text-muted mt-1" style="font-size:.7rem"><i class="fas fa-clock me-1"></i><?= $timeAgo ?></div>
            </div>
            <div class="d-flex gap-1 flex-shrink-0">
                <?php if ($isUnread): ?>
                <button class="btn btn-sm" style="border-radius:7px;padding:.2rem .5rem;background:#ede9fe;color:#6366f1;font-size:.7rem;border:none" onclick="markRead(<?= $n['id'] ?>)" title="Mark read"><i class="fas fa-check"></i></button>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-danger" style="border-radius:7px;padding:.2rem .5rem;font-size:.7rem" onclick="deleteNotif(<?= $n['id'] ?>)" title="Delete"><i class="fas fa-times"></i></button>
            </div>
        </div>
    </div>
    <?php if ($isUnread): ?>
    <div class="notif-dot mt-1"></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>">
        <a class="page-link" href="?filter=<?= $filter ?>&page=<?= $p ?>"><?= $p ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>

<script>
const CSRF = '<?= csrfToken() ?>';
const BASE = '<?= url('elms/notifications') ?>';

function markRead(id) {
    fetch(`${BASE}/${id}/read`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF}),
    }).then(r=>r.json()).then(d=>{
        const row = document.getElementById('notif'+id);
        if (row) { row.classList.remove('unread'); row.querySelector('.notif-dot')?.remove(); row.querySelector('[title="Mark read"]')?.remove(); }
        updateBadge(d.unread);
    });
}

function markAllRead() {
    fetch(`${BASE}/read-all`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF}),
    }).then(r=>r.json()).then(()=>location.reload());
}

function deleteNotif(id) {
    fetch(`${BASE}/${id}/delete`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF}),
    }).then(r=>r.json()).then(d=>{
        document.getElementById('notif'+id)?.remove();
        updateBadge(d.unread);
    });
}

function clearRead() {
    if (!confirm('Remove all read notifications?')) return;
    fetch(`${BASE}/clear-read`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf:CSRF}),
    }).then(r=>r.json()).then(()=>location.reload());
}

function updateBadge(count) {
    const badges = document.querySelectorAll('.notif-header-badge');
    badges.forEach(b => { b.textContent = count; b.style.display = count > 0 ? '' : 'none'; });
}
</script>

<?php
function _notifTimeAgo(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff/60).'m ago';
    if ($diff < 86400)  return floor($diff/3600).'h ago';
    if ($diff < 604800) return floor($diff/86400).'d ago';
    return date('d M Y', strtotime($dt));
}
?>
