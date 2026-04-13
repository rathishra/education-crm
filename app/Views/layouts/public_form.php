<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'Enquiry') ?> — <?= e(config('app.name', 'Edu Matrix')) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: #f0f4ff;
            min-height: 100vh;
        }

        /* ── Top navbar ── */
        .pub-navbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 14px 0;
            position: sticky; top: 0; z-index: 100;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }
        .pub-logo {
            font-weight: 800;
            font-size: 1.25rem;
            color: #2c3e8c;
            text-decoration: none;
            display: flex; align-items: center; gap: 10px;
        }
        .pub-logo-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #2c3e8c, #6366f1);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
        }

        /* ── Hero banner ── */
        .pub-hero {
            background: linear-gradient(135deg, #1e3a8a 0%, #312e81 50%, #4c1d95 100%);
            color: #fff;
            padding: 48px 0 80px;
            position: relative;
            overflow: hidden;
        }
        .pub-hero::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .pub-hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .06em;
            margin-bottom: 16px;
            color: rgba(255,255,255,.9);
        }
        .pub-hero h1 {
            font-size: clamp(1.5rem, 4vw, 2.5rem);
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -.02em;
        }
        .pub-hero p { color: rgba(255,255,255,.75); font-size: .95rem; margin: 0; }

        /* Stats row in hero */
        .pub-stats {
            display: flex; gap: 2rem; flex-wrap: wrap;
            margin-top: 28px;
        }
        .pub-stat { text-align: left; }
        .pub-stat-num { font-size: 1.75rem; font-weight: 800; line-height: 1; }
        .pub-stat-lbl { font-size: .75rem; color: rgba(255,255,255,.6); margin-top: 2px; }

        /* ── Main content lift ── */
        .pub-content {
            margin-top: -40px;
            padding-bottom: 60px;
        }

        /* ── Footer ── */
        .pub-footer {
            background: #1e293b;
            color: rgba(255,255,255,.5);
            font-size: .8rem;
            padding: 20px 0;
            text-align: center;
        }
        .pub-footer a { color: rgba(255,255,255,.4); }

        /* ── Flash alerts ── */
        .pub-alert {
            position: fixed;
            top: 80px; right: 20px;
            z-index: 9999; min-width: 280px; max-width: 360px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="pub-navbar">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between">
            <a href="<?= url('/') ?>" class="pub-logo">
                <div class="pub-logo-icon">
                    <i class="fas fa-graduation-cap text-white" style="font-size:16px"></i>
                </div>
                <?= e(config('app.name', 'Edu Matrix')) ?>
            </a>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= url('apply') ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-file-alt me-1"></i>Apply Now
                </a>
                <a href="<?= url('login') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Banner -->
<div class="pub-hero">
    <div class="container">
        <div class="pub-hero-badge">
            <i class="fas fa-envelope"></i> Student Enquiry Portal
        </div>
        <h1><?= e($pageTitle ?? 'Student Enquiry') ?></h1>
        <p>Fill in the form below and our academic counselors will reach out to you within 24 hours.</p>
        <div class="pub-stats">
            <div class="pub-stat">
                <div class="pub-stat-num">24h</div>
                <div class="pub-stat-lbl">Response Time</div>
            </div>
            <div class="pub-stat">
                <div class="pub-stat-num">100%</div>
                <div class="pub-stat-lbl">Free Counseling</div>
            </div>
            <div class="pub-stat">
                <div class="pub-stat-num">Expert</div>
                <div class="pub-stat-lbl">Guidance</div>
            </div>
        </div>
    </div>
</div>

<!-- Flash alerts -->
<?php
$flashSuccess = getFlash('success');
$flashError   = getFlash('error');
?>
<?php if ($flashSuccess || $flashError): ?>
<div class="pub-alert">
    <?php if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show shadow">
        <i class="fas fa-check-circle me-1"></i><?= e($flashSuccess) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow">
        <i class="fas fa-exclamation-triangle me-1"></i><?= e($flashError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="pub-content">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-9 col-lg-10">
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="pub-footer">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-center justify-content-md-between align-items-center gap-2">
            <span>&copy; <?= date('Y') ?> <?= e(config('app.name', 'Edu Matrix')) ?> — All rights reserved.</span>
            <span>
                <a href="<?= url('login') ?>">Admin Login</a>
                &middot;
                <a href="<?= url('apply') ?>">Apply for Admission</a>
            </span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
