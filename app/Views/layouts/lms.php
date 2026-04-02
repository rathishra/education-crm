<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'LMS') ?> — <?= e(config('app.name', 'Edu Matrix')) ?> LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --lms-primary:   #6366f1;
            --lms-primary-d: #4f46e5;
            --lms-accent:    #818cf8;
            --lms-dark:      #1e1b4b;
            --lms-sidebar-w: 260px;
            --lms-top-h:     58px;
            --lms-bg:        #f5f3ff;
        }
        body { font-family:'Segoe UI',system-ui,sans-serif; background:var(--lms-bg); margin:0; font-size:.9rem; }
        .lms-topnav {
            position:fixed; top:0; left:0; right:0; height:var(--lms-top-h);
            background:linear-gradient(135deg,var(--lms-dark),var(--lms-primary-d));
            z-index:1030; display:flex; align-items:center; gap:1rem; padding:0 1rem 0 0;
            box-shadow:0 2px 12px rgba(0,0,0,.15);
        }
        .lms-brand {
            width:var(--lms-sidebar-w); display:flex; align-items:center; gap:.65rem;
            padding:0 1.25rem; flex-shrink:0; border-right:1px solid rgba(255,255,255,.1);
        }
        .lms-brand-icon { width:32px; height:32px; border-radius:8px; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; color:#fff; font-size:.9rem; }
        .lms-brand-name { font-size:.85rem; font-weight:700; color:#fff; }
        .lms-brand-sub  { font-size:.62rem; color:rgba(255,255,255,.45); }
        .lms-topnav-right { margin-left:auto; display:flex; align-items:center; gap:.5rem; }
        .lms-topnav-btn { width:34px; height:34px; border-radius:8px; border:1px solid rgba(255,255,255,.15); background:rgba(255,255,255,.08); color:rgba(255,255,255,.8); cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.85rem; transition:all .15s; }
        .lms-topnav-btn:hover { background:rgba(255,255,255,.15); color:#fff; }
        .lms-user-btn { display:flex; align-items:center; gap:.6rem; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15); border-radius:10px; padding:.35rem .75rem .35rem .35rem; cursor:pointer; color:#fff; }
        .lms-avatar { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700; color:#fff; background:var(--lms-accent); }

        .lms-sidebar { position:fixed; top:var(--lms-top-h); left:0; bottom:0; width:var(--lms-sidebar-w); background:#fff; box-shadow:2px 0 12px rgba(0,0,0,.06); overflow-y:auto; z-index:1020; }
        .lms-nav { padding:1rem .75rem; }
        .lms-nav-heading { font-size:.62rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.1em; padding:.75rem .75rem .3rem; }
        .lms-nav-link { display:flex; align-items:center; gap:.75rem; padding:.58rem .85rem; border-radius:9px; color:#475569; font-size:.84rem; font-weight:500; text-decoration:none; transition:all .15s; margin-bottom:2px; position:relative; }
        .lms-nav-link:hover { background:#ede9fe; color:var(--lms-primary); }
        .lms-nav-link.active { background:#ede9fe; color:var(--lms-primary-d); font-weight:600; }
        .lms-nav-link.active::before { content:''; position:absolute; left:0; top:6px; bottom:6px; width:3px; border-radius:0 3px 3px 0; background:var(--lms-primary); }
        .lms-nav-icon { width:30px; height:30px; border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:.78rem; flex-shrink:0; background:#f1f5f9; color:#64748b; transition:all .15s; }
        .lms-nav-link:hover  .lms-nav-icon { background:#ddd6fe; color:var(--lms-primary); }
        .lms-nav-link.active .lms-nav-icon { background:var(--lms-primary); color:#fff; }

        .lms-main { margin-left:var(--lms-sidebar-w); margin-top:var(--lms-top-h); min-height:calc(100vh - var(--lms-top-h)); padding:1.5rem; }
        .lms-card { background:#fff; border-radius:14px; box-shadow:0 1px 8px rgba(0,0,0,.06); border:1px solid #ede9fe; }
        .lms-page-title { font-size:1.35rem; font-weight:800; color:#0f172a; margin:0; }
        .lms-breadcrumb { font-size:.78rem; color:#94a3b8; margin-top:.2rem; }
        .lms-page-header { margin-bottom:1.5rem; }

        @media(max-width:991.98px){
            .lms-sidebar{transform:translateX(-100%);}
            .lms-sidebar.open{transform:none;}
            .lms-main{margin-left:0;}
            .lms-brand{width:auto;border:none;}
        }
    </style>
    <?php if (!empty($extraCss)): foreach((array)$extraCss as $css): ?>
    <link href="<?= $css ?>" rel="stylesheet">
    <?php endforeach; endif; ?>
</head>
<body>
<?php
$lmsUser  = lmsAuth();
$initials = strtoupper(substr($lmsUser['first_name']??'L',0,1).substr($lmsUser['last_name']??'',0,1));
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
function lmsActive(string $path): string {
    global $uri;
    return str_starts_with($uri, '/elms' . $path) ? 'active' : '';
}
?>
<nav class="lms-topnav">
    <div class="lms-brand">
        <div class="lms-brand-icon"><i class="fas fa-graduation-cap"></i></div>
        <div>
            <div class="lms-brand-name"><?= e(config('app.name','Edu Matrix')) ?></div>
            <div class="lms-brand-sub">Enterprise LMS</div>
        </div>
    </div>
    <button class="lms-topnav-btn d-lg-none" id="lmsSidebarToggle"><i class="fas fa-bars"></i></button>
    <div class="lms-topnav-right">
        <a href="<?= url('elms/notifications') ?>" class="lms-topnav-btn text-decoration-none"><i class="fas fa-bell"></i></a>
        <div class="dropdown">
            <div class="lms-user-btn" data-bs-toggle="dropdown" style="cursor:pointer">
                <div class="lms-avatar"><?= $initials ?></div>
                <div class="d-none d-md-block">
                    <div style="font-size:.8rem;font-weight:600;line-height:1.2"><?= e($lmsUser['display_name'] ?? '') ?></div>
                    <div style="font-size:.62rem;color:rgba(255,255,255,.5);text-transform:capitalize"><?= e($lmsUser['role'] ?? '') ?></div>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1" style="border-radius:12px;min-width:190px">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small"><?= e($lmsUser['display_name'] ?? '') ?></div>
                    <div class="text-muted" style="font-size:.72rem"><?= e($lmsUser['email'] ?? '') ?></div>
                </li>
                <li><a class="dropdown-item small" href="<?= url('elms/profile') ?>"><i class="fas fa-user me-2 text-primary"></i>My Profile</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="<?= url('elms/logout') ?>">
                        <?= csrfField() ?>
                        <button type="submit" class="dropdown-item small text-danger"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<aside class="lms-sidebar" id="lmsSidebar">
    <nav class="lms-nav">
        <a href="<?= url('elms/dashboard') ?>" class="lms-nav-link <?= lmsActive('/dashboard') ?>">
            <span class="lms-nav-icon"><i class="fas fa-th-large"></i></span>Dashboard
        </a>

        <div class="lms-nav-heading">Learning</div>
        <a href="<?= url('elms/courses') ?>" class="lms-nav-link <?= lmsActive('/courses') ?>">
            <span class="lms-nav-icon"><i class="fas fa-book-open"></i></span>Courses
        </a>
        <a href="<?= url('elms/my-courses') ?>" class="lms-nav-link <?= lmsActive('/my-courses') ?>">
            <span class="lms-nav-icon"><i class="fas fa-layer-group"></i></span>My Courses
        </a>
        <a href="<?= url('elms/assignments') ?>" class="lms-nav-link <?= lmsActive('/assignments') ?>">
            <span class="lms-nav-icon"><i class="fas fa-tasks"></i></span>Assignments
        </a>
        <a href="<?= url('elms/quizzes') ?>" class="lms-nav-link <?= lmsActive('/quizzes') ?>">
            <span class="lms-nav-icon"><i class="fas fa-question-circle"></i></span>Quizzes
        </a>
        <a href="<?= url('elms/live') ?>" class="lms-nav-link <?= lmsActive('/live') ?>">
            <span class="lms-nav-icon"><i class="fas fa-video"></i></span>Live Classes
        </a>

        <div class="lms-nav-heading">Community</div>
        <a href="<?= url('elms/forums') ?>" class="lms-nav-link <?= lmsActive('/forums') ?>">
            <span class="lms-nav-icon"><i class="fas fa-comments"></i></span>Forums
        </a>

        <div class="lms-nav-heading">Progress</div>
        <a href="<?= url('elms/gradebook') ?>" class="lms-nav-link <?= lmsActive('/gradebook') ?>">
            <span class="lms-nav-icon"><i class="fas fa-star"></i></span>Gradebook
        </a>
        <a href="<?= url('elms/certificates') ?>" class="lms-nav-link <?= lmsActive('/certificates') ?>">
            <span class="lms-nav-icon"><i class="fas fa-certificate"></i></span>Certificates
        </a>
        <a href="<?= url('elms/achievements') ?>" class="lms-nav-link <?= lmsActive('/achievements') ?>">
            <span class="lms-nav-icon"><i class="fas fa-trophy"></i></span>Achievements
        </a>

        <?php if (lmsCan('analytics.view')): ?>
        <div class="lms-nav-heading">Management</div>
        <a href="<?= url('elms/analytics') ?>" class="lms-nav-link <?= lmsActive('/analytics') ?>">
            <span class="lms-nav-icon"><i class="fas fa-chart-line"></i></span>Analytics
        </a>
        <?php endif; ?>
        <?php if (lmsCan('users.view')): ?>
        <a href="<?= url('elms/users') ?>" class="lms-nav-link <?= lmsActive('/users') ?>">
            <span class="lms-nav-icon"><i class="fas fa-users-cog"></i></span>LMS Users
        </a>
        <?php endif; ?>

        <div class="border-top mt-3 pt-3 px-1">
            <form method="POST" action="<?= url('elms/logout') ?>">
                <?= csrfField() ?>
                <button type="submit" class="lms-nav-link w-100 border-0 bg-transparent text-start text-danger" style="font-size:.84rem">
                    <span class="lms-nav-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-sign-out-alt"></i></span>Sign Out
                </button>
            </form>
        </div>
    </nav>
</aside>

<main class="lms-main">
    <?php $fe = getFlash('errors',[]); if(!empty($fe)): ?>
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px;font-size:.875rem">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php foreach((array)$fe as $e_msg): ?><?= e($e_msg) ?>&nbsp;<?php endforeach; ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php $fs = getFlash('success'); if($fs): ?>
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:10px;font-size:.875rem">
        <i class="fas fa-check-circle me-2"></i><?= e($fs) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if(!empty($extraJs)): foreach((array)$extraJs as $js): ?>
<script src="<?= $js ?>"></script>
<?php endforeach; endif; ?>
<script>
document.getElementById('lmsSidebarToggle')?.addEventListener('click',()=>
    document.getElementById('lmsSidebar').classList.toggle('open'));
</script>
</body>
</html>
