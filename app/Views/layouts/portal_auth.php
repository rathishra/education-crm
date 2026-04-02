<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'Student Portal') ?> — <?= e(config('app.name', 'Edu Matrix')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #0a1628; min-height: 100vh; font-family: 'Segoe UI', Arial, sans-serif; }

        .portal-auth-wrapper { min-height: 100vh; display: flex; }

        .portal-brand-panel {
            flex: 1;
            background: linear-gradient(145deg, #065f46 0%, #047857 30%, #059669 65%, #10b981 100%);
            display: flex; flex-direction: column;
            justify-content: space-between; padding: 3rem;
            position: relative; overflow: hidden;
        }
        .portal-brand-panel::before {
            content: ''; position: absolute;
            width: 500px; height: 500px; border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -150px; right: -150px;
        }
        .portal-brand-panel::after {
            content: ''; position: absolute;
            width: 350px; height: 350px; border-radius: 50%;
            background: rgba(255,255,255,0.04);
            bottom: -100px; left: -100px;
        }
        .portal-brand-logo {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #fff; margin-bottom: 1.5rem;
        }
        .portal-brand-title { font-size: 2.2rem; font-weight: 800; color: #fff; line-height: 1.15; letter-spacing: -0.04em; margin-bottom: 0.4rem; }
        .portal-brand-sub { font-size: 1rem; color: rgba(255,255,255,0.75); font-weight: 500; margin-bottom: 1rem; }
        .portal-brand-desc { font-size: 0.9rem; color: rgba(255,255,255,0.55); line-height: 1.7; max-width: 380px; }

        .portal-feature-list { list-style: none; padding: 0; margin: 2rem 0 0; }
        .portal-feature-list li {
            display: flex; align-items: center; gap: 0.75rem;
            color: rgba(255,255,255,0.8); font-size: 0.875rem; font-weight: 500;
            margin-bottom: 0.85rem;
        }
        .portal-feature-list li i {
            width: 30px; height: 30px;
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem; flex-shrink: 0; color: #6ee7b7;
        }
        .portal-brand-footer { font-size: 0.775rem; color: rgba(255,255,255,0.35); }

        .portal-form-panel {
            width: 480px; flex-shrink: 0;
            background: #fff;
            display: flex; flex-direction: column; justify-content: center;
            padding: 3rem 2.5rem;
            box-shadow: -20px 0 60px rgba(0,0,0,0.35);
        }
        .portal-form-header { margin-bottom: 2rem; }
        .portal-form-header .portal-greeting { font-size: 0.8rem; font-weight: 600; color: #059669; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.4rem; }
        .portal-form-header h2 { font-size: 1.65rem; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; margin-bottom: 0.4rem; }
        .portal-form-header p { font-size: 0.875rem; color: #64748b; margin: 0; }

        .portal-form .form-label { font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem; }
        .portal-form .form-control {
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 0.875rem; padding: 0.65rem 0.95rem;
            color: #0f172a; background: #f8fafc; transition: all 0.15s;
        }
        .portal-form .form-control:focus { border-color: #059669; background: #fff; box-shadow: 0 0 0 3px rgba(5,150,105,0.12); }
        .portal-form .input-group-text {
            background: #f1f5f9; border: 1.5px solid #e2e8f0;
            border-right: none; color: #94a3b8;
            border-radius: 10px 0 0 10px;
        }
        .portal-form .input-group .form-control { border-left: none; border-radius: 0 10px 10px 0; }
        .portal-form .input-group .form-control:focus { border-left: none; }
        .portal-form .input-group:focus-within .input-group-text { border-color: #059669; background: #ecfdf5; color: #059669; }
        .portal-form .input-group:focus-within .form-control { border-color: #059669; }
        .btn-portal-signin {
            background: linear-gradient(135deg, #059669, #10b981);
            color: #fff; border: none; border-radius: 10px;
            padding: 0.72rem; font-size: 0.9rem; font-weight: 700;
            letter-spacing: 0.01em;
            box-shadow: 0 6px 20px rgba(5,150,105,0.35);
            transition: all 0.15s; width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        }
        .btn-portal-signin:hover { transform: translateY(-1px); box-shadow: 0 10px 30px rgba(5,150,105,0.45); color: #fff; }
        .portal-auth-footer { margin-top: 2rem; text-align: center; font-size: 0.775rem; color: #94a3b8; }
        .portal-form .btn-toggle-pass {
            border: 1.5px solid #e2e8f0; border-left: none;
            border-radius: 0 10px 10px 0; background: #f8fafc;
            color: #94a3b8; padding: 0 0.85rem;
        }
        .portal-form .btn-toggle-pass:hover { background: #ecfdf5; color: #059669; border-color: #059669; }

        @media (max-width: 991.98px) {
            .portal-brand-panel { display: none; }
            .portal-form-panel { width: 100%; box-shadow: none; padding: 2rem 1.5rem; }
            body { background: #fff; }
        }
    </style>
</head>
<body>
<div class="portal-auth-wrapper">
    <!-- Brand Panel -->
    <div class="portal-brand-panel">
        <div class="position-relative" style="z-index:1">
            <div class="portal-brand-logo"><i class="fas fa-graduation-cap"></i></div>
            <h1 class="portal-brand-title"><?= e(config('app.name', 'Edu Matrix')) ?></h1>
            <p class="portal-brand-sub">Student Self-Service Portal</p>
            <p class="portal-brand-desc">Access your academic records, fee details, timetable, attendance, and more — all in one place.</p>
            <ul class="portal-feature-list">
                <li><i class="fas fa-chart-bar"></i>Live attendance tracking</li>
                <li><i class="fas fa-file-invoice-dollar"></i>Fee & payment history</li>
                <li><i class="fas fa-calendar-alt"></i>Timetable & schedules</li>
                <li><i class="fas fa-star"></i>Exam results & grade cards</li>
                <li><i class="fas fa-book-open"></i>Course materials (LMS)</li>
                <li><i class="fas fa-file-alt"></i>Documents & certificates</li>
            </ul>
        </div>
        <div class="portal-brand-footer position-relative" style="z-index:1">
            &copy; <?= date('Y') ?> <?= e(config('app.name', 'Edu Matrix')) ?> &mdash; Student Portal
        </div>
    </div>

    <!-- Form Panel -->
    <div class="portal-form-panel">
        <!-- Flash messages -->
        <?php $errors = getFlash('errors', []); if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php foreach ((array)$errors as $err): ?><?= e($err) ?><?php endforeach; ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php $successMsg = getFlash('success'); if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= e($successMsg) ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
