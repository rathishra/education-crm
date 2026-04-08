<style>
.post-card { background:#fff; border-radius:14px; border:1px solid #e8e3ff; margin-bottom:1rem; overflow:hidden; }
.post-card.solution { border:2px solid #22c55e; }
.post-header { padding:.75rem 1rem; border-bottom:1px solid #f1f0ff; display:flex; align-items:center; gap:.75rem; }
.post-body { padding:1rem 1rem 0; font-size:.88rem; color:#374151; line-height:1.75; }
.post-footer { padding:.5rem 1rem .75rem; display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }
.avatar { width:36px; height:36px; border-radius:50%; background:#ede9fe; color:#6366f1; display:flex; align-items:center; justify-content:center; font-size:.8rem; font-weight:800; flex-shrink:0; }
.role-badge { font-size:.62rem; font-weight:700; padding:.15rem .45rem; border-radius:6px; }
.action-btn { font-size:.72rem; color:#94a3b8; background:none; border:none; padding:.2rem .4rem; border-radius:6px; cursor:pointer; transition:all .1s; }
.action-btn:hover { background:#f1f0ff; color:#6366f1; }
.like-btn { display:flex; align-items:center; gap:.3rem; font-size:.75rem; color:#94a3b8; background:none; border:none; padding:.3rem .6rem; border-radius:20px; cursor:pointer; transition:all .12s; }
.like-btn:hover, .like-btn.liked { color:#6366f1; background:#ede9fe; }
.sol-banner { background:#f0fdf4; border-bottom:2px solid #22c55e; padding:.4rem 1rem; font-size:.75rem; font-weight:700; color:#16a34a; }
/* Markdown-like rendering */
.post-body code { background:#f8fafc; border:1px solid #e2e8f0; border-radius:4px; padding:.1rem .35rem; font-size:.82rem; color:#e11d48; }
.post-body blockquote { border-left:3px solid #e2e8f0; padding:.25rem 0 .25rem .75rem; color:#64748b; margin:.5rem 0; }
.reply-box { background:#fff; border-radius:14px; border:2px solid #e8e3ff; padding:1.25rem; margin-top:1rem; }
</style>

<?php
$isInstructor = $lmsUser && in_array($lmsUser['role'], ['instructor','lms_admin']);
$isAuthor     = $lmsUser && (int)$lmsUser['id'] === (int)$thread['author_id'];
$canModerate  = $isInstructor;
$canMarkSol   = $isAuthor || $isInstructor;

function renderBody(string $text): string {
    $t = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $t = preg_replace('/\*\*(.*?)\*\*/s',          '<strong>$1</strong>', $t);
    $t = preg_replace('/_(.*?)_/s',                 '<em>$1</em>',        $t);
    $t = preg_replace('/`(.*?)`/',                  '<code>$1</code>',    $t);
    $t = preg_replace('/^&gt; (.+)$/m',             '<blockquote>$1</blockquote>', $t);
    return nl2br($t);
}
function _ago(string $dt): string {
    $d = time() - strtotime($dt);
    if ($d<60) return 'just now';
    if ($d<3600) return floor($d/60).'m ago';
    if ($d<86400) return floor($d/3600).'h ago';
    return date('d M Y', strtotime($dt));
}
?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap text-muted" style="font-size:.78rem">
    <a href="<?= url('elms/forum'.($thread['course_id']?"?course={$thread['course_id']}":'')) ?>" class="text-decoration-none text-muted"><i class="fas fa-comments me-1"></i>Forum</a>
    <i class="fas fa-chevron-right" style="font-size:.6rem"></i>
    <span class="text-truncate" style="max-width:300px"><?= e($thread['title']) ?></span>
    <div class="ms-auto d-flex gap-2">
        <!-- Subscribe -->
        <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.72rem" id="subBtn" onclick="toggleSubscribe()">
            <i class="fas fa-<?= $isSubscribed?'bell-slash':'bell' ?> me-1"></i><?= $isSubscribed?'Unsubscribe':'Subscribe' ?>
        </button>
        <?php if ($canModerate): ?>
        <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.72rem" id="pinBtn" onclick="pinThread()">
            <i class="fas fa-thumbtack me-1"></i><?= $thread['is_pinned']?'Unpin':'Pin' ?>
        </button>
        <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:.72rem" id="lockBtn" onclick="lockThread()">
            <i class="fas fa-<?= $thread['is_locked']?'unlock':'lock' ?> me-1"></i><?= $thread['is_locked']?'Unlock':'Lock' ?>
        </button>
        <?php endif; ?>
        <?php if ($isAuthor || $canModerate): ?>
        <form method="POST" action="<?= url('elms/forum/'.$thread['id'].'/delete') ?>" onsubmit="return confirm('Delete this thread?')" class="d-inline">
            <?= csrfField() ?>
            <button class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:.72rem"><i class="fas fa-trash"></i></button>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- Thread header -->
<div class="mb-3">
    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
        <h4 class="fw-bold mb-0" style="color:#0f172a;font-size:1.05rem"><?= e($thread['title']) ?></h4>
        <?php if ($thread['is_solved']): ?>
        <span class="badge" style="background:#d1fae5;color:#065f46;border-radius:8px"><i class="fas fa-check me-1"></i>Solved</span>
        <?php endif; ?>
        <?php if ($thread['is_locked']): ?>
        <span class="badge" style="background:#f1f5f9;color:#64748b;border-radius:8px"><i class="fas fa-lock me-1"></i>Locked</span>
        <?php endif; ?>
        <?php if ($thread['is_pinned']): ?>
        <span class="badge" style="background:#fef3c7;color:#92400e;border-radius:8px"><i class="fas fa-thumbtack me-1"></i>Pinned</span>
        <?php endif; ?>
        <?php if (!empty($thread['category_name'])): ?>
        <span class="badge" style="background:<?= e($thread['category_color']) ?>22;color:<?= e($thread['category_color']) ?>;border-radius:8px"><?= e($thread['category_name']) ?></span>
        <?php endif; ?>
    </div>
    <div class="text-muted small"><i class="fas fa-eye me-1"></i><?= $thread['views'] ?> views &middot; <i class="fas fa-reply me-1"></i><?= $thread['reply_count'] ?> replies<?= $thread['course_title']?' &middot; '.$thread['course_title']:'' ?></div>
</div>

<!-- Original post -->
<div class="post-card mb-3" id="post-op">
    <div class="post-header">
        <div class="avatar"><?= strtoupper(substr($thread['author_name'],0,2)) ?></div>
        <div style="flex:1">
            <div class="fw-bold small" style="color:#0f172a"><?= e($thread['author_name']) ?>
                <?php if (in_array($thread['author_role'],['instructor','lms_admin'])): ?>
                <span class="role-badge ms-1" style="background:#ede9fe;color:#4338ca"><?= ucfirst($thread['author_role']) ?></span>
                <?php endif; ?>
            </div>
            <div class="text-muted" style="font-size:.7rem"><?= _ago($thread['created_at']) ?></div>
        </div>
    </div>
    <div class="post-body mb-2"><?= renderBody($thread['body']) ?></div>
</div>

<!-- Replies -->
<?php if (!empty($posts)): ?>
<div id="postsContainer">
<?php foreach ($posts as $post): ?>
<?php $isMine = $lmsUser && (int)$post['author_id'] === (int)$lmsUser['id']; ?>
<div class="post-card <?= $post['is_solution']?'solution':'' ?>" id="post-<?= $post['id'] ?>">
    <?php if ($post['is_solution']): ?>
    <div class="sol-banner"><i class="fas fa-check-circle me-2"></i>Accepted Answer</div>
    <?php endif; ?>
    <div class="post-header">
        <div class="avatar" style="<?= $post['is_solution']?'background:#dcfce7;color:#16a34a':'' ?>"><?= strtoupper(substr($post['author_name'],0,2)) ?></div>
        <div style="flex:1">
            <div class="fw-bold small" style="color:#0f172a"><?= e($post['author_name']) ?>
                <?php if (in_array($post['author_role'],['instructor','lms_admin'])): ?>
                <span class="role-badge ms-1" style="background:#ede9fe;color:#4338ca"><?= ucfirst($post['author_role']) ?></span>
                <?php endif; ?>
            </div>
            <div class="text-muted" style="font-size:.7rem"><?= _ago($post['created_at']) ?><?= $post['updated_at']!==$post['created_at']?' &middot; edited':'' ?></div>
        </div>
        <div class="d-flex gap-1">
            <button class="like-btn <?= $post['i_liked']?'liked':'' ?>" id="like<?= $post['id'] ?>"
                    onclick="react(<?= $thread['id'] ?>,<?= $post['id'] ?>)">
                <i class="fas fa-heart"></i> <span id="lc<?= $post['id'] ?>"><?= $post['likes'] ?></span>
            </button>
            <?php if ($canMarkSol): ?>
            <button class="action-btn" onclick="markSolution(<?= $post['id'] ?>)" title="<?= $post['is_solution']?'Unmark solution':'Mark as solution' ?>">
                <i class="fas fa-check-circle" style="color:<?= $post['is_solution']?'#22c55e':'#d1d5db' ?>"></i>
            </button>
            <?php endif; ?>
            <?php if ($isMine || $canModerate): ?>
            <button class="action-btn" onclick="editPost(<?= $post['id'] ?>)" title="Edit"><i class="fas fa-edit"></i></button>
            <button class="action-btn" onclick="deletePost(<?= $post['id'] ?>)" title="Delete" style="color:#ef4444 !important"><i class="fas fa-trash"></i></button>
            <?php endif; ?>
        </div>
    </div>
    <div class="post-body" id="pbody<?= $post['id'] ?>"><?= renderBody($post['body']) ?></div>
    <!-- Edit textarea (hidden) -->
    <div id="pedit<?= $post['id'] ?>" class="px-3 pb-2 d-none">
        <textarea class="form-control form-control-sm mt-2" rows="4"><?= e($post['body']) ?></textarea>
        <div class="d-flex gap-2 mt-2">
            <button class="btn btn-sm btn-primary" style="border-radius:7px" onclick="saveEdit(<?= $thread['id'] ?>,<?= $post['id'] ?>)"><i class="fas fa-save me-1"></i>Save</button>
            <button class="btn btn-sm btn-outline-secondary" style="border-radius:7px" onclick="cancelEdit(<?= $post['id'] ?>)">Cancel</button>
        </div>
    </div>
    <div class="post-footer"></div>
</div>
<?php endforeach; ?>
</div>

<?php if ($postTotalPages > 1): ?>
<nav class="mb-3"><ul class="pagination pagination-sm justify-content-center">
    <?php for ($p = 1; $p <= $postTotalPages; $p++): ?>
    <li class="page-item <?= $p==$page?'active':'' ?>">
        <a class="page-link" href="?page=<?= $p ?>#postsContainer"><?= $p ?></a>
    </li>
    <?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php endif; ?>

<!-- Reply box -->
<?php if (!$thread['is_locked'] || $canModerate): ?>
<div class="reply-box" id="replyBox">
    <h6 class="fw-bold mb-2" style="color:#0f172a"><i class="fas fa-reply me-2 text-primary"></i>Your Reply</h6>
    <textarea id="replyTA" class="form-control mb-2" rows="5" placeholder="Write your reply…<?= $thread['is_locked']?' (Locked — moderator override)':'' ?>"></textarea>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn btn-primary" style="border-radius:9px" onclick="postReply()"><i class="fas fa-paper-plane me-2"></i>Post Reply</button>
        <span class="text-muted small">+2 XP for replying</span>
    </div>
</div>
<?php else: ?>
<div class="alert alert-secondary py-2 small text-center"><i class="fas fa-lock me-2"></i>This thread is locked. No new replies allowed.</div>
<?php endif; ?>

<script>
const THREAD_ID = <?= $thread['id'] ?>;
const MY_UID    = <?= (int)($lmsUser['id'] ?? 0) ?>;
const CSRF      = '<?= csrfToken() ?>';
const BASE      = '<?= url("elms/forum") ?>';

function postReply() {
    const body = document.getElementById('replyTA').value.trim();
    if (!body) return;
    fetch(`${BASE}/${THREAD_ID}/reply`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF, body}),
    })
    .then(r => r.json())
    .then(d => {
        if (d.error) { alert(d.error); return; }
        document.getElementById('replyTA').value = '';
        appendPost(d.post);
    })
    .catch(() => alert('Failed to post reply'));
}

function appendPost(p) {
    const cont = document.getElementById('postsContainer') || (() => {
        const c = document.createElement('div'); c.id='postsContainer';
        document.getElementById('replyBox').before(c);
        return c;
    })();
    const initials = (p.author_name||'?').substring(0,2).toUpperCase();
    cont.insertAdjacentHTML('beforeend', `
        <div class="post-card" id="post-${p.id}">
            <div class="post-header">
                <div class="avatar">${initials}</div>
                <div style="flex:1">
                    <div class="fw-bold small" style="color:#0f172a">${p.author_name}</div>
                    <div class="text-muted" style="font-size:.7rem">just now</div>
                </div>
                <div class="d-flex gap-1">
                    <button class="like-btn" id="like${p.id}" onclick="react(THREAD_ID,${p.id})"><i class="fas fa-heart"></i> <span id="lc${p.id}">0</span></button>
                    <button class="action-btn" onclick="editPost(${p.id})"><i class="fas fa-edit"></i></button>
                    <button class="action-btn" onclick="deletePost(${p.id})" style="color:#ef4444"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            <div class="post-body" id="pbody${p.id}">${renderMd(p.body)}</div>
            <div id="pedit${p.id}" class="px-3 pb-2 d-none"><textarea class="form-control form-control-sm mt-2" rows="4">${p.body}</textarea><div class="d-flex gap-2 mt-2"><button class="btn btn-sm btn-primary" style="border-radius:7px" onclick="saveEdit(THREAD_ID,${p.id})"><i class="fas fa-save me-1"></i>Save</button><button class="btn btn-sm btn-outline-secondary" style="border-radius:7px" onclick="cancelEdit(${p.id})">Cancel</button></div></div>
            <div class="post-footer"></div>
        </div>`);
    cont.querySelector(`#post-${p.id}`)?.scrollIntoView({behavior:'smooth', block:'center'});
}

function renderMd(t) {
    return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/\*\*(.*?)\*\*/gs,'<strong>$1</strong>')
            .replace(/_(.*?)_/gs,'<em>$1</em>')
            .replace(/`(.*?)`/g,'<code>$1</code>')
            .replace(/\n/g,'<br>');
}

function editPost(pid) {
    document.getElementById('pbody'+pid).classList.add('d-none');
    document.getElementById('pedit'+pid).classList.remove('d-none');
}
function cancelEdit(pid) {
    document.getElementById('pbody'+pid).classList.remove('d-none');
    document.getElementById('pedit'+pid).classList.add('d-none');
}
function saveEdit(tid, pid) {
    const body = document.querySelector(`#pedit${pid} textarea`).value.trim();
    if (!body) return;
    fetch(`${BASE}/${tid}/post/${pid}/edit`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF, body}),
    }).then(r => r.json()).then(d => {
        if (d.error) { alert(d.error); return; }
        document.getElementById('pbody'+pid).innerHTML = renderMd(body);
        cancelEdit(pid);
    });
}
function deletePost(pid) {
    if (!confirm('Delete this reply?')) return;
    fetch(`${BASE}/${THREAD_ID}/post/${pid}/delete`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF}),
    }).then(r => r.json()).then(d => {
        if (d.status === 'ok') document.getElementById('post-'+pid)?.remove();
    });
}
function react(tid, pid) {
    fetch(`${BASE}/${tid}/post/${pid}/react`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF}),
    }).then(r => r.json()).then(d => {
        const btn = document.getElementById('like'+pid);
        if (d.liked) btn.classList.add('liked'); else btn.classList.remove('liked');
        document.getElementById('lc'+pid).textContent = d.count;
    });
}
function markSolution(pid) {
    fetch(`${BASE}/${THREAD_ID}/post/${pid}/solution`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF}),
    }).then(r => r.json()).then(d => {
        if (d.status === 'ok') location.reload();
    });
}
function toggleSubscribe() {
    fetch(`${BASE}/${THREAD_ID}/subscribe`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF}),
    }).then(r => r.json()).then(d => {
        const btn = document.getElementById('subBtn');
        btn.innerHTML = d.subscribed
            ? '<i class="fas fa-bell-slash me-1"></i>Unsubscribe'
            : '<i class="fas fa-bell me-1"></i>Subscribe';
    });
}
function pinThread() {
    fetch(`${BASE}/${THREAD_ID}/pin`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF}),
    }).then(r => r.json()).then(d => {
        if (d.status === 'ok') location.reload();
    });
}
function lockThread() {
    fetch(`${BASE}/${THREAD_ID}/lock`, {
        method:'POST',
        headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
        body: JSON.stringify({_csrf: CSRF}),
    }).then(r => r.json()).then(d => {
        if (d.status === 'ok') location.reload();
    });
}
</script>
