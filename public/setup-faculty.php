<?php
/**
 * ONE-TIME FACULTY SETUP SCRIPT
 * Step 1 → Visit: http://localhost:8000/setup-faculty.php
 * Step 2 → Use the login credentials shown on screen
 * Step 3 → DELETE this file immediately after use!
 */

// ── Bootstrap (mirrors public/index.php) ───────────────────
define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class) {
    $map = ['Core\\' => BASE_PATH . '/core/', 'App\\' => BASE_PATH . '/app/'];
    foreach ($map as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (file_exists($file)) { require_once $file; return; }
        }
    }
});

require_once BASE_PATH . '/core/helpers.php';
Core\App::getInstance();   // boots DB, session, env

// ── Faculty users to seed ───────────────────────────────────
$facultyUsers = [
    [
        'employee_id'  => 'FAC001',
        'first_name'   => 'Rajesh',
        'last_name'    => 'Kumar',
        'email'        => 'rajesh.kumar@educrm.com',
        'phone'        => '9876543210',
        'password'     => 'Faculty@123',
        'designation'  => 'Associate Professor',
        'qualification'=> 'M.Tech Computer Science',
        'experience'   => 72,
        'salary'       => 720000.00,
    ],
    [
        'employee_id'  => 'FAC002',
        'first_name'   => 'Priya',
        'last_name'    => 'Sharma',
        'email'        => 'priya.sharma@educrm.com',
        'phone'        => '9876543211',
        'password'     => 'Faculty@123',
        'designation'  => 'Assistant Professor',
        'qualification'=> 'M.Sc Mathematics',
        'experience'   => 38,
        'salary'       => 560000.00,
    ],
    [
        'employee_id'  => 'FAC003',
        'first_name'   => 'Anil',
        'last_name'    => 'Verma',
        'email'        => 'anil.verma@educrm.com',
        'phone'        => '9876543212',
        'password'     => 'Faculty@123',
        'designation'  => 'Senior Lecturer',
        'qualification'=> 'MBA Finance',
        'experience'   => 84,
        'salary'       => 650000.00,
    ],
];

$db  = db();
$msg = [];
$err = [];

// Resolve institution & org
$db->query("SELECT id, organization_id FROM institutions ORDER BY id LIMIT 1");
$inst          = $db->fetch();
$institutionId = $inst ? (int)$inst['id']              : 1;
$orgId         = $inst ? (int)$inst['organization_id'] : 1;

// Resolve Faculty role
$db->query("SELECT id FROM roles WHERE slug='faculty' LIMIT 1");
$roleRow = $db->fetch();
if (!$roleRow) {
    die('<p style="color:red;font-family:sans-serif">❌ Faculty role not found — run <code>database/10_seed_data.sql</code> first.</p>');
}
$facultyRoleId = (int)$roleRow['id'];

// First department (optional)
$db->query("SELECT id FROM departments WHERE institution_id=? ORDER BY id LIMIT 1", [$institutionId]);
$deptRow = $db->fetch();
$deptId  = $deptRow ? (int)$deptRow['id'] : null;

// ── Create users ────────────────────────────────────────────
foreach ($facultyUsers as $f) {
    $db->query("SELECT id FROM users WHERE email=?", [$f['email']]);
    if ($db->fetch()) {
        $msg[] = ['warn', "{$f['email']} already exists — skipped."];
        continue;
    }

    try {
        $hash = password_hash($f['password'], PASSWORD_BCRYPT, ['cost' => 10]);

        $db->insert('users', [
            'employee_id'       => $f['employee_id'],
            'first_name'        => $f['first_name'],
            'last_name'         => $f['last_name'],
            'email'             => $f['email'],
            'phone'             => $f['phone'],
            'password'          => $hash,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'is_active'         => 1,
        ]);
        $userId = (int)$db->lastInsertId();

        $db->insert('user_roles', [
            'user_id'         => $userId,
            'role_id'         => $facultyRoleId,
            'organization_id' => $orgId,
            'institution_id'  => $institutionId,
        ]);

        $db->insert('staff_profiles', [
            'user_id'                 => $userId,
            'institution_id'          => $institutionId,
            'department_id'           => $deptId,
            'designation'             => $f['designation'],
            'joining_date'            => date('Y-m-d', strtotime('-' . rand(12, 48) . ' months')),
            'qualification'           => $f['qualification'],
            'total_experience_months' => $f['experience'],
            'salary_package'          => $f['salary'],
            'status'                  => 'working',
        ]);

        $msg[] = ['ok', "Created <strong>{$f['first_name']} {$f['last_name']}</strong> (ID: $userId)"];

    } catch (\Exception $e) {
        $err[] = "{$f['email']}: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Faculty Setup — Edu Matrix</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: system-ui, sans-serif; background: #f8fafc; color: #1e293b; padding: 40px 20px; }
.wrap { max-width: 720px; margin: 0 auto; }
h1 { font-size: 1.6rem; color: #4f46e5; margin-bottom: 6px; }
h2 { font-size: 1.1rem; margin: 24px 0 10px; color: #374151; border-bottom: 1px solid #e5e7eb; padding-bottom: 6px; }
.badge-info { display:inline-block; background:#ede9fe; color:#6d28d9; border-radius:99px; padding:2px 10px; font-size:.78rem; font-weight:600; }
.log { margin-bottom: 6px; padding: 8px 14px; border-radius: 6px; font-size: .9rem; }
.log.ok   { background: #f0fdf4; border-left: 4px solid #22c55e; }
.log.warn { background: #fefce8; border-left: 4px solid #eab308; }
.log.err  { background: #fef2f2; border-left: 4px solid #ef4444; }
table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: .9rem; }
th,td { border: 1px solid #e5e7eb; padding: 9px 14px; text-align: left; }
th { background: #f3f4f6; font-weight: 600; }
code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: .85em; color: #0f172a; }
.warn-box { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 16px; margin-top: 28px; font-size: .88rem; line-height: 1.6; }
.btn { display:inline-block; margin-top:16px; background:#4f46e5; color:#fff; padding:10px 22px; border-radius:6px; text-decoration:none; font-weight:600; }
</style>
</head>
<body>
<div class="wrap">
    <h1>🎓 Faculty User Setup</h1>
    <p class="badge-info">Institution ID: <?= $institutionId ?> &nbsp;·&nbsp; Faculty Role ID: <?= $facultyRoleId ?></p>

    <h2>Setup Results</h2>
    <?php foreach ($msg as [$type, $text]): ?>
        <div class="log <?= $type ?>"><?= $text ?></div>
    <?php endforeach; ?>
    <?php foreach ($err as $e): ?>
        <div class="log err">❌ <?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <h2>✅ Login Credentials</h2>
    <table>
        <tr><th>#</th><th>Name</th><th>Email</th><th>Password</th><th>Role</th></tr>
        <?php foreach ($facultyUsers as $i => $f): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= $f['first_name'].' '.$f['last_name'] ?></td>
            <td><code><?= $f['email'] ?></code></td>
            <td><code><?= $f['password'] ?></code></td>
            <td><span style="background:#ddd1fc;color:#4f46e5;padding:2px 8px;border-radius:99px;font-size:.78rem;font-weight:600">Faculty</span></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <a class="btn" href="/login" target="_blank">Go to Login →</a>

    <div class="warn-box">
        ⚠️ <strong>Security Warning</strong><br>
        Delete this file immediately after use:<br>
        <code>D:\CRM\public\setup-faculty.php</code>
    </div>
</div>
</body>
</html>
