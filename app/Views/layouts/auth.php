<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'Sign In') ?> — <?= e(config('app.name', 'Edu Matrix')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <style>
        /* Auth page specific */
        body { background: #0f172a; min-height: 100vh; }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
        }

        /* Left brand panel */
        .auth-brand-panel {
            flex: 1;
            background: linear-gradient(145deg, #1e3a8a 0%, #312e81 40%, #4c1d95 75%, #7c3aed 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .auth-brand-panel::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            top: -150px; right: -150px;
        }

        .auth-brand-panel::after {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            bottom: -100px; left: -100px;
        }

        .auth-brand-logo {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .auth-brand-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            letter-spacing: -0.04em;
            margin-bottom: 1rem;
        }

        .auth-brand-desc {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.6);
            line-height: 1.7;
            max-width: 380px;
        }

        .auth-feature-list { list-style: none; padding: 0; margin: 2rem 0 0; }
        .auth-feature-list li {
            display: flex; align-items: center; gap: 0.75rem;
            color: rgba(255,255,255,0.75);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.85rem;
        }
        .auth-feature-list li i {
            width: 28px; height: 28px;
            background: rgba(255,255,255,0.12);
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
            color: #c4b5fd;
        }

        .auth-brand-footer { font-size: 0.775rem; color: rgba(255,255,255,0.35); }

        /* Right form panel */
        .auth-form-panel {
            width: 480px;
            flex-shrink: 0;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 2.5rem;
            box-shadow: -20px 0 60px rgba(0,0,0,0.3);
        }

        .auth-form-header { margin-bottom: 2rem; }
        .auth-form-header .auth-greeting { font-size: 0.8rem; font-weight: 600; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.4rem; }
        .auth-form-header h2 { font-size: 1.65rem; font-weight: 800; color: #0f172a; letter-spacing: -0.03em; margin-bottom: 0.4rem; }
        .auth-form-header p { font-size: 0.875rem; color: #64748b; margin: 0; }

        .auth-form .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.4rem;
        }

        .auth-form .form-control {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.875rem;
            padding: 0.65rem 0.95rem;
            color: #0f172a;
            background: #f8fafc;
            transition: all 0.15s;
        }

        .auth-form .form-control:focus {
            border-color: #4f46e5;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        .auth-form .input-group-text {
            background: #f1f5f9;
            border: 1.5px solid #e2e8f0;
            border-right: none;
            color: #94a3b8;
            border-radius: 10px 0 0 10px;
        }

        .auth-form .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }

        .auth-form .input-group .form-control:focus { border-left: none; }
        .auth-form .input-group:focus-within .input-group-text {
            border-color: #4f46e5;
            background: #eef2ff;
            color: #4f46e5;
        }
        .auth-form .input-group:focus-within .form-control { border-color: #4f46e5; }

        .auth-form .btn-toggle-pass {
            border: 1.5px solid #e2e8f0;
            border-left: none;
            border-radius: 0 10px 10px 0;
            background: #f8fafc;
            color: #94a3b8;
            padding: 0 0.85rem;
        }
        .auth-form .btn-toggle-pass:hover { background: #eef2ff; color: #4f46e5; border-color: #4f46e5; }

        .btn-signin {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.72rem;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            box-shadow: 0 6px 20px rgba(79,70,229,0.35);
            transition: all 0.15s;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-signin:hover { transform: translateY(-1px); box-shadow: 0 10px 30px rgba(79,70,229,0.45); }
        .btn-signin:active { transform: none; }

        .auth-divider {
            display: flex; align-items: center; gap: 1rem;
            color: #94a3b8; font-size: 0.775rem; margin: 1.5rem 0;
        }
        .auth-divider::before, .auth-divider::after {
            content: ''; flex: 1; height: 1px; background: #e2e8f0;
        }

        .auth-footer { margin-top: 2rem; text-align: center; font-size: 0.775rem; color: #94a3b8; }

        @media (max-width: 991.98px) {
            .auth-brand-panel { display: none; }
            .auth-form-panel { width: 100%; box-shadow: none; padding: 2rem 1.5rem; }
            body { background: #fff; }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <!-- Brand Panel -->
        <div class="auth-brand-panel">
            <div class="position-relative" style="z-index:1">
                <div class="auth-brand-logo">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h1 class="auth-brand-title"><?= e(config('app.name', 'Edu Matrix')) ?></h1>
                <p class="auth-brand-desc">
                    The complete enterprise platform for managing admissions, academics,
                    fees, and student lifecycle — all in one place.
                </p>
                <ul class="auth-feature-list">
                    <li><i class="fas fa-funnel-dollar"></i>CRM — Enquiries, Leads & Follow-ups</li>
                    <li><i class="fas fa-user-graduate"></i>Full Student Lifecycle Management</li>
                    <li><i class="fas fa-calendar-check"></i>Attendance & Timetable</li>
                    <li><i class="fas fa-file-invoice-dollar"></i>Fee Collection & Payments</li>
                    <li><i class="fas fa-chart-bar"></i>Analytics & Reports</li>
                </ul>
            </div>
            <div class="auth-brand-footer position-relative" style="z-index:1">
                &copy; <?= date('Y') ?> <?= e(config('app.name', 'Edu Matrix')) ?>. Enterprise Edition.
            </div>
        </div>

        <!-- Form Panel -->
        <div class="auth-form-panel">
            <?php
            $flashError = getFlash('error');
            $flashSuccess = getFlash('success');
            ?>
            <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?= e($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-check-circle"></i> <?= e($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?= $content ?>

            <div class="auth-footer">
                Edu Matrix &bull; Enterprise Education Platform
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
