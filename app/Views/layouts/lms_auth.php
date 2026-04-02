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
        }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh; margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0f0e2a;
        }
        .lms-auth-wrap { min-height: 100vh; display: flex; }

        /* ── Brand Panel ── */
        .lms-brand-panel {
            flex: 1;
            background: linear-gradient(145deg, #1e1b4b 0%, #312e81 40%, #4f46e5 80%, #6366f1 100%);
            display: flex; flex-direction: column; justify-content: space-between;
            padding: 3rem; position: relative; overflow: hidden;
        }
        .lms-brand-panel::before {
            content:''; position:absolute; width:600px; height:600px; border-radius:50%;
            background:rgba(255,255,255,.04); top:-200px; right:-200px;
        }
        .lms-brand-panel::after {
            content:''; position:absolute; width:400px; height:400px; border-radius:50%;
            background:rgba(255,255,255,.03); bottom:-150px; left:-150px;
        }
        .lms-brand-logo {
            width:54px; height:54px; border-radius:14px;
            background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);
            display:flex; align-items:center; justify-content:center;
            font-size:1.5rem; color:#fff; margin-bottom:1.5rem;
        }
        .lms-brand-title { font-size:2.4rem; font-weight:900; color:#fff; line-height:1.1; letter-spacing:-.04em; margin-bottom:.35rem; }
        .lms-brand-badge {
            display:inline-flex; align-items:center; gap:.4rem;
            background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
            color:rgba(255,255,255,.85); font-size:.75rem; font-weight:600;
            padding:.3rem .85rem; border-radius:20px; margin-bottom:1rem; letter-spacing:.04em;
        }
        .lms-brand-desc { font-size:.9rem; color:rgba(255,255,255,.55); line-height:1.8; max-width:380px; }
        .lms-feature-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; margin-top:2rem; }
        .lms-feature-item {
            display:flex; align-items:center; gap:.65rem;
            background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1);
            border-radius:10px; padding:.65rem .85rem; color:rgba(255,255,255,.8); font-size:.8rem; font-weight:500;
        }
        .lms-feature-item i { color:#a5b4fc; font-size:.85rem; width:16px; text-align:center; }
        .lms-brand-footer { font-size:.72rem; color:rgba(255,255,255,.3); }

        /* ── Form Panel ── */
        .lms-form-panel {
            width:460px; flex-shrink:0; background:#fff;
            display:flex; flex-direction:column; justify-content:center;
            padding:3rem 2.5rem; box-shadow:-25px 0 60px rgba(0,0,0,.4);
        }
        .lms-form-eyebrow { font-size:.72rem; font-weight:700; color:var(--lms-primary); text-transform:uppercase; letter-spacing:.12em; margin-bottom:.4rem; }
        .lms-form-title { font-size:1.7rem; font-weight:900; color:#0f172a; letter-spacing:-.03em; margin-bottom:.3rem; }
        .lms-form-subtitle { font-size:.875rem; color:#64748b; margin-bottom:2rem; }

        .lms-form .form-label { font-size:.78rem; font-weight:600; color:#374151; margin-bottom:.3rem; }
        .lms-form .form-control, .lms-form .form-select {
            border:1.5px solid #e5e7eb; border-radius:10px;
            font-size:.875rem; padding:.65rem .95rem;
            background:#f9fafb; color:#111827; transition:all .15s;
        }
        .lms-form .form-control:focus, .lms-form .form-select:focus {
            border-color:var(--lms-primary); background:#fff;
            box-shadow:0 0 0 3px rgba(99,102,241,.12);
        }
        .lms-form .input-group-text {
            background:#f1f5f9; border:1.5px solid #e5e7eb; border-right:none;
            color:#9ca3af; border-radius:10px 0 0 10px;
        }
        .lms-form .input-group .form-control { border-left:none; border-radius:0 10px 10px 0; }
        .lms-form .input-group:focus-within .input-group-text { border-color:var(--lms-primary); color:var(--lms-primary); }
        .lms-form .input-group:focus-within .form-control    { border-color:var(--lms-primary); }
        .lms-form .btn-toggle-pass {
            border:1.5px solid #e5e7eb; border-left:none;
            border-radius:0 10px 10px 0; background:#f9fafb; color:#9ca3af; padding:0 .9rem;
        }
        .lms-form .btn-toggle-pass:hover { background:#ede9fe; color:var(--lms-primary); border-color:var(--lms-primary); }

        .btn-lms-signin {
            background:linear-gradient(135deg, var(--lms-primary-d), var(--lms-primary));
            color:#fff; border:none; border-radius:10px;
            padding:.72rem; font-size:.9rem; font-weight:700; width:100%;
            box-shadow:0 6px 20px rgba(99,102,241,.4); transition:all .15s;
            display:flex; align-items:center; justify-content:center; gap:.5rem;
        }
        .btn-lms-signin:hover { transform:translateY(-1px); box-shadow:0 10px 30px rgba(99,102,241,.5); color:#fff; }

        .lms-divider { display:flex; align-items:center; gap:.75rem; margin:1.25rem 0; color:#d1d5db; font-size:.75rem; }
        .lms-divider::before, .lms-divider::after { content:''; flex:1; height:1px; background:#e5e7eb; }

        .lms-role-chips { display:flex; gap:.4rem; margin-bottom:1.5rem; flex-wrap:wrap; }
        .lms-role-chip {
            padding:.2rem .65rem; border-radius:20px; font-size:.68rem; font-weight:600;
            border:1px solid;
        }
        .chip-admin    { color:#7c3aed; background:#ede9fe; border-color:#c4b5fd; }
        .chip-instructor { color:#0891b2; background:#e0f2fe; border-color:#a5f3fc; }
        .chip-learner  { color:#059669; background:#d1fae5; border-color:#a7f3d0; }

        @media (max-width:991.98px) {
            .lms-brand-panel { display:none; }
            .lms-form-panel  { width:100%; box-shadow:none; padding:2rem 1.5rem; }
            body { background:#fff; }
        }
    </style>
</head>
<body>
<div class="lms-auth-wrap">
    <!-- Brand Panel -->
    <div class="lms-brand-panel">
        <div style="position:relative;z-index:1">
            <div class="lms-brand-logo"><i class="fas fa-graduation-cap"></i></div>
            <h1 class="lms-brand-title"><?= e(config('app.name', 'Edu Matrix')) ?></h1>
            <div class="lms-brand-badge"><i class="fas fa-cube"></i> Enterprise LMS</div>
            <p class="lms-brand-desc">A complete, intelligent learning platform built for modern institutions — courses, quizzes, live classes, analytics and more.</p>
            <div class="lms-feature-grid">
                <div class="lms-feature-item"><i class="fas fa-book-open"></i>Course Builder</div>
                <div class="lms-feature-item"><i class="fas fa-video"></i>Live Classes</div>
                <div class="lms-feature-item"><i class="fas fa-tasks"></i>Assignments</div>
                <div class="lms-feature-item"><i class="fas fa-question-circle"></i>Smart Quizzes</div>
                <div class="lms-feature-item"><i class="fas fa-chart-line"></i>Analytics</div>
                <div class="lms-feature-item"><i class="fas fa-certificate"></i>Certificates</div>
                <div class="lms-feature-item"><i class="fas fa-comments"></i>Forums</div>
                <div class="lms-feature-item"><i class="fas fa-trophy"></i>Gamification</div>
            </div>
        </div>
        <div class="lms-brand-footer" style="position:relative;z-index:1">
            &copy; <?= date('Y') ?> <?= e(config('app.name','Edu Matrix')) ?> — Enterprise LMS
        </div>
    </div>

    <!-- Form Panel -->
    <div class="lms-form-panel">
        <?php $errors = getFlash('errors', []); if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius:10px;font-size:.85rem" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php foreach ((array)$errors as $err): ?><?= e($err) ?><br><?php endforeach; ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php $success = getFlash('success'); if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show mb-3" style="border-radius:10px;font-size:.85rem" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= e($success) ?>
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleLmsPass(id, btn) {
    const inp  = document.getElementById(id);
    const icon = btn.querySelector('i');
    inp.type   = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>
</body>
</html>
