<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'Student Portal') ?> — <?= e(config('app.name', 'Edu Matrix')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <?php if (!empty($extraCss)): foreach ((array)$extraCss as $css): ?>
    <link href="<?= $css ?>" rel="stylesheet">
    <?php endforeach; endif; ?>
    <style>
        :root {
            --portal-primary:   #059669;
            --portal-primary-d: #047857;
            --portal-accent:    #10b981;
            --portal-sidebar-w: 260px;
            --portal-top-h:     58px;
            --portal-bg:        #f0fdf4;
            --portal-dark:      #064e3b;
        }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: var(--portal-bg); margin: 0; padding: 0; font-size: 0.9rem; }

        /* ── TOP NAVBAR ── */
        .portal-topnav {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--portal-top-h);
            background: linear-gradient(135deg, var(--portal-dark) 0%, var(--portal-primary-d) 100%);
            z-index: 1030;
            display: flex; align-items: center; gap: 1rem; padding: 0 1rem 0 0;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .portal-brand {
            width: var(--portal-sidebar-w);
            display: flex; align-items: center; gap: 0.65rem;
            padding: 0 1.25rem;
            flex-shrink: 0;
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        .portal-brand-icon {
            width: 34px; height: 34px; border-radius: 9px;
            background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 0.9rem; flex-shrink: 0;
        }
        .portal-brand-text { line-height: 1.2; }
        .portal-brand-name { font-size: 0.85rem; font-weight: 700; color: #fff; display: block; }
        .portal-brand-sub  { font-size: 0.68rem; color: rgba(255,255,255,0.55); display: block; }

        .portal-topnav-right { margin-left: auto; display: flex; align-items: center; gap: 0.5rem; }

        .portal-topnav-btn {
            width: 34px; height: 34px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.8); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; transition: all 0.15s; position: relative;
        }
        .portal-topnav-btn:hover { background: rgba(255,255,255,0.15); color: #fff; }
        .portal-notif-dot {
            position: absolute; top: 3px; right: 3px;
            width: 8px; height: 8px; border-radius: 50%;
            background: #f59e0b; border: 1.5px solid var(--portal-dark);
        }

        .portal-user-btn {
            display: flex; align-items: center; gap: 0.6rem;
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 10px; padding: 0.35rem 0.75rem 0.35rem 0.35rem;
            cursor: pointer; transition: all 0.15s; color: #fff;
        }
        .portal-user-btn:hover { background: rgba(255,255,255,0.15); }
        .portal-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; font-weight: 700; color: #fff;
            background: var(--portal-accent); flex-shrink: 0;
        }
        .portal-user-info .portal-user-name { font-size: 0.8rem; font-weight: 600; color: #fff; display: block; line-height: 1.2; }
        .portal-user-info .portal-user-id   { font-size: 0.68rem; color: rgba(255,255,255,0.55); display: block; }

        /* ── SIDEBAR ── */
        .portal-sidebar {
            position: fixed; top: var(--portal-top-h); left: 0; bottom: 0;
            width: var(--portal-sidebar-w);
            background: #fff;
            box-shadow: 2px 0 12px rgba(0,0,0,0.07);
            overflow-y: auto; z-index: 1020;
            transition: transform 0.25s;
        }
        .portal-sidebar::-webkit-scrollbar { width: 4px; }
        .portal-sidebar::-webkit-scrollbar-thumb { background: #d1fae5; border-radius: 2px; }

        .portal-nav { padding: 1rem 0.75rem; }
        .portal-nav-heading {
            font-size: 0.65rem; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.1em;
            padding: 0.75rem 0.75rem 0.35rem;
        }
        .portal-nav-link {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.6rem 0.85rem; border-radius: 9px;
            color: #475569; font-size: 0.85rem; font-weight: 500;
            text-decoration: none; transition: all 0.15s; margin-bottom: 2px;
            position: relative;
        }
        .portal-nav-link:hover { background: #ecfdf5; color: var(--portal-primary); }
        .portal-nav-link.active {
            background: #d1fae5; color: var(--portal-primary-d);
            font-weight: 600;
        }
        .portal-nav-link.active::before {
            content: ''; position: absolute; left: 0; top: 6px; bottom: 6px;
            width: 3px; border-radius: 0 3px 3px 0; background: var(--portal-primary);
        }
        .portal-nav-icon {
            width: 30px; height: 30px; border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.78rem; flex-shrink: 0;
            background: #f1f5f9; color: #64748b; transition: all 0.15s;
        }
        .portal-nav-link:hover   .portal-nav-icon { background: #bbf7d0; color: var(--portal-primary); }
        .portal-nav-link.active  .portal-nav-icon { background: var(--portal-accent); color: #fff; }

        .portal-nav-badge {
            margin-left: auto; font-size: 0.65rem; font-weight: 700;
            background: #f59e0b; color: #fff;
            padding: 2px 6px; border-radius: 10px;
        }

        /* ── MAIN CONTENT ── */
        .portal-main {
            margin-left: var(--portal-sidebar-w);
            margin-top: var(--portal-top-h);
            min-height: calc(100vh - var(--portal-top-h));
            padding: 1.5rem;
        }

        /* ── CARD ENHANCEMENTS ── */
        .portal-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
            border: 1px solid #e7f5ef;
        }
        .portal-stat-card {
            background: #fff; border-radius: 14px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.06);
            border: 1px solid #e7f5ef;
            padding: 1.25rem 1.5rem;
            display: flex; align-items: center; gap: 1rem;
        }
        .portal-stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .portal-stat-label { font-size: 0.75rem; color: #64748b; font-weight: 500; }
        .portal-stat-value { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1.2; }

        /* ── PAGE HEADER ── */
        .portal-page-header { margin-bottom: 1.5rem; }
        .portal-page-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; }
        .portal-breadcrumb { font-size: 0.8rem; color: #94a3b8; margin-top: 0.2rem; }
        .portal-breadcrumb a { color: var(--portal-primary); text-decoration: none; }

        /* ── FLASH ALERTS ── */
        .portal-alert { border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.875rem; margin-bottom: 1rem; }

        /* ── TABLE ── */
        .portal-table { font-size: 0.875rem; }
        .portal-table th { background: #f0fdf4; font-weight: 600; color: #374151; font-size: 0.775rem; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 2px solid #d1fae5; }
        .portal-table td { vertical-align: middle; border-color: #f0fdf4; }

        /* ── BADGE COLORS ── */
        .badge-present  { background: #d1fae5; color: #065f46; }
        .badge-absent   { background: #fee2e2; color: #991b1b; }
        .badge-late     { background: #fef3c7; color: #92400e; }
        .badge-holiday  { background: #e0e7ff; color: #3730a3; }

        /* ── RESPONSIVE ── */
        @media (max-width: 991.98px) {
            .portal-sidebar { transform: translateX(-100%); }
            .portal-sidebar.open { transform: none; }
            .portal-main { margin-left: 0; }
            .portal-brand { width: auto; border: none; }
        }
    </style>
</head>
<body>

<?php
$portalUser   = portalAuth();
$initials     = strtoupper(substr($portalUser['first_name'] ?? 'S', 0, 1) . substr($portalUser['last_name'] ?? '', 0, 1));
$currentUri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePortal   = '/portal/student';

function portalIsActive(string $path): string {
    global $currentUri, $basePortal;
    return str_starts_with($currentUri, $basePortal . $path) ? 'active' : '';
}

// Unread notification count (fast query)
$_portalNotifCount = 0;
try {
    $db = db();
    $db->query("SELECT COUNT(*) AS cnt FROM notifications WHERE student_id = ? AND is_read = 0", [$portalUser['id'] ?? 0]);
    $_row = $db->fetch();
    $_portalNotifCount = (int)($_row['cnt'] ?? 0);
} catch (\Throwable $e) {}
?>

<!-- TOP NAVBAR -->
<nav class="portal-topnav">
    <div class="portal-brand">
        <div class="portal-brand-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="portal-brand-text">
            <span class="portal-brand-name"><?= e(config('app.name', 'Edu Matrix')) ?></span>
            <span class="portal-brand-sub">Student Portal</span>
        </div>
    </div>

    <!-- Mobile toggle -->
    <button class="portal-topnav-btn d-lg-none" id="portalSidebarToggle" title="Menu">
        <i class="fas fa-bars"></i>
    </button>

    <div class="portal-topnav-right">
        <!-- Notifications -->
        <a href="<?= url('portal/student/notifications') ?>" class="portal-topnav-btn text-decoration-none" title="Notifications">
            <i class="fas fa-bell"></i>
            <?php if ($_portalNotifCount > 0): ?>
            <span class="portal-notif-dot"></span>
            <?php endif; ?>
        </a>

        <!-- User dropdown -->
        <div class="dropdown">
            <div class="portal-user-btn" data-bs-toggle="dropdown" style="cursor:pointer">
                <div class="portal-avatar"><?= $initials ?></div>
                <div class="portal-user-info d-none d-md-block">
                    <span class="portal-user-name"><?= e(($portalUser['first_name'] ?? '') . ' ' . ($portalUser['last_name'] ?? '')) ?></span>
                    <span class="portal-user-id"><?= e($portalUser['student_id_number'] ?? '') ?></span>
                </div>
                <i class="fas fa-chevron-down ms-1 d-none d-md-inline" style="font-size:0.6rem;color:rgba(255,255,255,0.55)"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-1" style="border-radius:12px;min-width:200px">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-semibold small"><?= e(($portalUser['first_name'] ?? '') . ' ' . ($portalUser['last_name'] ?? '')) ?></div>
                    <div class="text-muted" style="font-size:0.75rem"><?= e($portalUser['email'] ?? '') ?></div>
                </li>
                <li><a class="dropdown-item" href="<?= url('portal/student/profile') ?>"><i class="fas fa-user-circle me-2 text-success"></i>My Profile</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="<?= url('portal/student/logout') ?>">
                        <?= csrfField() ?>
                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Sign Out</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- SIDEBAR -->
<aside class="portal-sidebar" id="portalSidebar">
    <nav class="portal-nav">
        <a href="<?= url('portal/student/dashboard') ?>" class="portal-nav-link <?= portalIsActive('/dashboard') ?>">
            <span class="portal-nav-icon"><i class="fas fa-th-large"></i></span>Dashboard
        </a>

        <div class="portal-nav-heading">Academics</div>
        <a href="<?= url('portal/student/attendance') ?>" class="portal-nav-link <?= portalIsActive('/attendance') ?>">
            <span class="portal-nav-icon"><i class="fas fa-calendar-check"></i></span>Attendance
        </a>
        <a href="<?= url('portal/student/timetable') ?>" class="portal-nav-link <?= portalIsActive('/timetable') ?>">
            <span class="portal-nav-icon"><i class="fas fa-clock"></i></span>Timetable
        </a>
        <a href="<?= url('portal/student/exams') ?>" class="portal-nav-link <?= portalIsActive('/exams') ?>">
            <span class="portal-nav-icon"><i class="fas fa-file-alt"></i></span>Exams & Results
        </a>
        <a href="<?= url('portal/student/lms') ?>" class="portal-nav-link <?= portalIsActive('/lms') ?>">
            <span class="portal-nav-icon"><i class="fas fa-book-open"></i></span>Course Materials
        </a>

        <div class="portal-nav-heading">Finance</div>
        <a href="<?= url('portal/student/fees') ?>" class="portal-nav-link <?= portalIsActive('/fees') ?>">
            <span class="portal-nav-icon"><i class="fas fa-file-invoice-dollar"></i></span>Fees & Payments
        </a>

        <div class="portal-nav-heading">Account</div>
        <a href="<?= url('portal/student/profile') ?>" class="portal-nav-link <?= portalIsActive('/profile') ?>">
            <span class="portal-nav-icon"><i class="fas fa-user-circle"></i></span>My Profile
        </a>
        <a href="<?= url('portal/student/documents') ?>" class="portal-nav-link <?= portalIsActive('/documents') ?>">
            <span class="portal-nav-icon"><i class="fas fa-folder-open"></i></span>My Documents
        </a>
        <a href="<?= url('portal/student/notifications') ?>" class="portal-nav-link <?= portalIsActive('/notifications') ?>">
            <span class="portal-nav-icon"><i class="fas fa-bell"></i></span>Notifications
            <?php if ($_portalNotifCount > 0): ?>
            <span class="portal-nav-badge"><?= $_portalNotifCount ?></span>
            <?php endif; ?>
        </a>

        <div class="border-top mt-3 pt-3 px-1">
            <form method="POST" action="<?= url('portal/student/logout') ?>">
                <?= csrfField() ?>
                <button type="submit" class="portal-nav-link w-100 border-0 bg-transparent text-start text-danger">
                    <span class="portal-nav-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-sign-out-alt"></i></span>Sign Out
                </button>
            </form>
        </div>
    </nav>
</aside>

<!-- MAIN CONTENT -->
<main class="portal-main">

    <!-- Flash Messages -->
    <?php $flashErrors = getFlash('errors', []); if (!empty($flashErrors)): ?>
    <div class="alert alert-danger alert-dismissible fade show portal-alert" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php foreach ((array)$flashErrors as $fe): ?><?= e($fe) ?>&nbsp;<?php endforeach; ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php $flashSuccess = getFlash('success'); if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show portal-alert" role="alert">
        <i class="fas fa-check-circle me-2"></i><?= e($flashSuccess) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php $flashWarning = getFlash('warning'); if ($flashWarning): ?>
    <div class="alert alert-warning alert-dismissible fade show portal-alert" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i><?= e($flashWarning) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?= $content ?? '' ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($extraJs)): foreach ((array)$extraJs as $js): ?>
<script src="<?= $js ?>"></script>
<?php endforeach; endif; ?>
<script>
// Mobile sidebar toggle
document.getElementById('portalSidebarToggle')?.addEventListener('click', function () {
    document.getElementById('portalSidebar').classList.toggle('open');
});
</script>
</body>
</html>
