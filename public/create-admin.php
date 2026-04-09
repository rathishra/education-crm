<?php
/**
 * One-time Super Admin Setup
 * Visit: localhost:8000/create-admin.php
 * DELETE this file after use!
 */

// ── Load app config for DB connection ──────────────────────
define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/config/database.php';

$dsn  = "mysql:host={$config['host']};dbname={$config['database']};charset=utf8mb4";
$pdo  = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$message = '';
$msgType = '';

// ── Handle form submit ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name']  ?? '');
    $email     = trim($_POST['email']      ?? '');
    $password  = trim($_POST['password']   ?? '');
    $confirm   = trim($_POST['confirm']    ?? '');

    if (!$firstName || !$email || !$password) {
        $message = 'First name, email and password are required.';
        $msgType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
        $msgType = 'danger';
    } elseif (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
        $msgType = 'danger';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
        $msgType = 'danger';
    } else {
        try {
            // Check if email already exists
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                // Update existing user
                $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, password=?, is_active=1 WHERE email=?");
                $stmt->execute([$firstName, $lastName, password_hash($password, PASSWORD_BCRYPT, ['cost'=>12]), $email]);
                $userId = $pdo->query("SELECT id FROM users WHERE email='" . addslashes($email) . "'")->fetchColumn();
                $message = "✅ Existing user updated with new password.";
            } else {
                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password, is_active, created_at)
                                       VALUES (?, ?, ?, ?, 1, NOW())");
                $stmt->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_BCRYPT, ['cost'=>12])]);
                $userId = $pdo->lastInsertId();
                $message = "✅ Super Admin user created successfully.";
            }

            // Ensure organization & institution row 1 exists
            $orgExists = $pdo->query("SELECT id FROM organizations LIMIT 1")->fetch();
            if (!$orgExists) {
                $pdo->exec("INSERT INTO organizations (id, organization_name, organization_code, status, created_at)
                            VALUES (1, 'My Organization', 'ORG001', 'active', NOW())");
            }
            $instExists = $pdo->query("SELECT id FROM institutions LIMIT 1")->fetch();
            if (!$instExists) {
                $pdo->exec("INSERT INTO institutions (id, organization_id, name, code, type, email, status, created_at)
                            VALUES (1, 1, 'My Institution', 'INST001', 'arts_science', '$email', 'active', NOW())");
            }

            // Ensure super_admin role exists
            $role = $pdo->query("SELECT id FROM roles WHERE slug = 'super_admin' LIMIT 1")->fetch();
            if (!$role) {
                $pdo->exec("INSERT INTO roles (name, slug, description, is_system, level)
                            VALUES ('Super Admin','super_admin','Full system access',1,0)");
                $roleId = $pdo->lastInsertId();
            } else {
                $roleId = $role['id'];
            }

            // Assign role
            $orgId  = $pdo->query("SELECT id FROM organizations LIMIT 1")->fetchColumn();
            $instId = $pdo->query("SELECT id FROM institutions LIMIT 1")->fetchColumn();

            $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);
            $pdo->prepare("INSERT INTO user_roles (user_id, role_id, organization_id, institution_id, created_at)
                           VALUES (?,?,?,?,NOW())")
                ->execute([$userId, $roleId, $orgId, $instId]);

            $msgType = 'success';
        } catch (Exception $e) {
            $message = '❌ Error: ' . $e->getMessage();
            $msgType = 'danger';
        }
    }
}

// ── Check current admin count ───────────────────────────────
try {
    $adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
} catch (Exception $e) {
    $adminCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4ff; }
        .setup-card { max-width: 480px; margin: 80px auto; }
        .brand-icon { width: 64px; height: 64px; background: linear-gradient(135deg,#4f46e5,#7c3aed);
                      border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
    </style>
</head>
<body>
<div class="setup-card">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">

            <div class="text-center mb-4">
                <div class="brand-icon"><i class="fas fa-shield-halved fa-2x text-white"></i></div>
                <h4 class="fw-bold">Create Super Admin</h4>
                <p class="text-muted small">One-time setup — delete this file after use</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $msgType ?> rounded-3">
                    <?= htmlspecialchars($message) ?>
                    <?php if ($msgType === 'success'): ?>
                        <hr>
                        <a href="/login" class="btn btn-primary w-100 mt-1">
                            <i class="fas fa-sign-in-alt me-2"></i>Go to Login
                        </a>
                        <div class="alert alert-warning mt-3 small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Delete this file!</strong> Remove <code>public/create-admin.php</code> from your server now.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($msgType !== 'success'): ?>
            <form method="POST">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwd" class="form-control" minlength="8" required placeholder="Min. 8 characters">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwd',this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="confirm" id="cpwd" class="form-control" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('cpwd',this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="fas fa-user-shield me-2"></i>Create Super Admin
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>

            <?php if ($adminCount > 0 && $msgType !== 'success'): ?>
            <p class="text-center text-muted small mt-3">
                <i class="fas fa-info-circle me-1"></i>
                <?= $adminCount ?> active user(s) already in system.
                You can reset an existing user's password too.
            </p>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function togglePwd(id, btn) {
    const inp = document.getElementById(id);
    const isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    btn.innerHTML = isText ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
}
</script>
</body>
</html>
