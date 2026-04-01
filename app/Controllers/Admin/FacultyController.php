<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\User;

class FacultyController extends BaseController
{
    // ──────────────────────────────────────────────────────────────
    // SCHEMA SELF-HEALING
    // ──────────────────────────────────────────────────────────────
    private function ensureSchema(): void
    {
        // ── 1. staff_profiles base table first (others may depend on it) ──
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `staff_profiles` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `institution_id` BIGINT UNSIGNED NOT NULL,
                `department_id` BIGINT UNSIGNED DEFAULT NULL,
                `designation` VARCHAR(100) DEFAULT NULL,
                `joining_date` DATE DEFAULT NULL,
                `qualification` VARCHAR(255) DEFAULT NULL,
                `total_experience_months` INT DEFAULT 0,
                `salary_package` DECIMAL(15,2) DEFAULT NULL,
                `bank_name` VARCHAR(100) DEFAULT NULL,
                `bank_account_number` VARCHAR(50) DEFAULT NULL,
                `bank_ifsc` VARCHAR(20) DEFAULT NULL,
                `pf_number` VARCHAR(50) DEFAULT NULL,
                `esi_number` VARCHAR(50) DEFAULT NULL,
                `status` ENUM('working','on_leave','resigned','terminated') NOT NULL DEFAULT 'working',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) {}

        // ── 2. Extra columns on staff_profiles (each independently) ──────
        $extras = [
            'employee_id'             => "VARCHAR(50) DEFAULT NULL",
            'profile_photo'           => "VARCHAR(255) DEFAULT NULL",
            'bio'                     => "TEXT DEFAULT NULL",
            'specialization'          => "VARCHAR(255) DEFAULT NULL",
            'publications_count'      => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
            'certifications'          => "TEXT DEFAULT NULL",
            'emergency_contact_name'  => "VARCHAR(100) DEFAULT NULL",
            'emergency_contact_phone' => "VARCHAR(20) DEFAULT NULL",
        ];
        foreach ($extras as $col => $def) {
            try {
                $this->db->query("SHOW COLUMNS FROM `staff_profiles` LIKE '$col'");
                if (!$this->db->fetch()) {
                    $this->db->query("ALTER TABLE `staff_profiles` ADD COLUMN `$col` $def");
                }
            } catch (\Exception $e) {}
        }

        // ── 3. staff_leave_requests ───────────────────────────────────────
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `staff_leave_requests` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `user_id` BIGINT UNSIGNED NOT NULL,
                `leave_type` ENUM('casual','sick','earned','maternity','paternity','other') NOT NULL DEFAULT 'casual',
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `reason` TEXT DEFAULT NULL,
                `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
                `approved_by` BIGINT UNSIGNED DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_slr_user` (`user_id`),
                KEY `idx_slr_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) {}

        // ── 4. faculty_performance_reviews ───────────────────────────────
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `faculty_performance_reviews` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `institution_id` BIGINT UNSIGNED NOT NULL,
                `faculty_id` BIGINT UNSIGNED NOT NULL,
                `review_period` VARCHAR(20) NOT NULL,
                `teaching_quality` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `punctuality` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `student_feedback_score` DECIMAL(3,1) DEFAULT NULL,
                `research_contribution` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `admin_contribution` TINYINT UNSIGNED NOT NULL DEFAULT 0,
                `overall_rating` DECIMAL(3,1) NOT NULL DEFAULT 0,
                `comments` TEXT DEFAULT NULL,
                `reviewed_by` BIGINT UNSIGNED DEFAULT NULL,
                `status` ENUM('draft','submitted','acknowledged') NOT NULL DEFAULT 'submitted',
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_fpr_faculty` (`faculty_id`),
                KEY `idx_fpr_inst` (`institution_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) {}

        // ── 5. faculty_attendance ────────────────────────────────────────
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `faculty_attendance` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `institution_id` BIGINT UNSIGNED NOT NULL,
                `faculty_id` BIGINT UNSIGNED NOT NULL,
                `attendance_date` DATE NOT NULL,
                `status` ENUM('present','absent','half_day','on_leave','holiday') NOT NULL DEFAULT 'present',
                `check_in` TIME DEFAULT NULL,
                `check_out` TIME DEFAULT NULL,
                `remarks` VARCHAR(255) DEFAULT NULL,
                `marked_by` BIGINT UNSIGNED DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_fa_date` (`faculty_id`, `attendance_date`),
                KEY `idx_fa_inst` (`institution_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Exception $e) {}
    }

    // ──────────────────────────────────────────────────────────────
    // DIRECTORY
    // ──────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->authorize('staff.view');
        $this->ensureSchema();

        $search = trim($_GET['q'] ?? '');
        $deptId = (int)($_GET['dept_id'] ?? 0);
        $status = $_GET['status'] ?? '';

        $searchWhere = $search
            ? " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR sp.employee_id LIKE ?)"
            : "";
        $deptWhere   = $deptId  ? " AND sp.department_id = ?" : "";
        $statusWhere = $status === 'active'
            ? " AND u.is_active = 1"
            : ($status === 'inactive' ? " AND u.is_active = 0" : "");

        // 4 positional params: 2 subqueries + ur JOIN + sp LEFT JOIN
        $params = [$this->institutionId, $this->institutionId,
                   $this->institutionId, $this->institutionId];
        if ($search) { $s = "%$search%"; $params = array_merge($params, [$s,$s,$s,$s]); }
        if ($deptId)  { $params[] = $deptId; }

        $this->db->query("
            SELECT u.id AS user_id, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                   sp.id AS profile_id, sp.employee_id, sp.designation, sp.joining_date,
                   sp.salary_package, sp.total_experience_months, sp.qualification,
                   sp.specialization, sp.bio, sp.profile_photo,
                   d.name AS department_name, d.id AS department_id,
                   GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') AS roles,
                   (SELECT COUNT(*) FROM faculty_subject_allocations fa
                    WHERE fa.faculty_id=u.id AND fa.institution_id=? AND fa.status='active') AS subject_count,
                   (SELECT COALESCE(SUM(fa2.hours_per_week),0) FROM faculty_subject_allocations fa2
                    WHERE fa2.faculty_id=u.id AND fa2.institution_id=? AND fa2.status='active') AS weekly_hours,
                   (SELECT COUNT(*) FROM staff_leave_requests slr
                    WHERE slr.user_id=u.id AND slr.status='pending') AS pending_leaves
            FROM users u
            JOIN user_roles ur ON ur.user_id=u.id AND ur.institution_id=?
            JOIN roles r ON r.id=ur.role_id
            LEFT JOIN staff_profiles sp ON sp.user_id=u.id AND sp.institution_id=?
            LEFT JOIN departments d ON d.id=sp.department_id
            WHERE r.name NOT IN ('Student','Parent')
            $searchWhere $deptWhere $statusWhere
            GROUP BY u.id
            ORDER BY u.first_name, u.last_name
        ", $params);
        $faculty = $this->db->fetchAll();

        // Stats
        $totalFaculty  = count($faculty);
        $activeFaculty = count(array_filter($faculty, fn($f) => $f['is_active']));

        $this->db->query("
            SELECT COUNT(*) AS cnt FROM staff_leave_requests slr
            JOIN user_roles ur ON ur.user_id=slr.user_id AND ur.institution_id=?
            JOIN roles r ON r.id=ur.role_id AND r.name NOT IN ('Student','Parent')
            WHERE slr.status='pending'
        ", [$this->institutionId]);
        $pendingLeaves = (int)($this->db->fetch()['cnt'] ?? 0);

        $this->db->query("SELECT COUNT(DISTINCT sp.department_id) AS cnt FROM staff_profiles sp
            WHERE sp.institution_id=? AND sp.department_id IS NOT NULL", [$this->institutionId]);
        $deptCount = (int)($this->db->fetch()['cnt'] ?? 0);

        $this->db->query("SELECT id, name FROM departments WHERE institution_id=? ORDER BY name",
            [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('faculty/index', compact(
            'faculty','totalFaculty','activeFaculty','pendingLeaves','deptCount',
            'departments','search','deptId','status'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // PROFILE VIEW
    // ──────────────────────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->authorize('staff.view');
        $this->ensureSchema();

        $this->db->query("
            SELECT u.id AS user_id, u.first_name, u.last_name, u.email, u.phone, u.is_active,
                   u.created_at AS user_since,
                   sp.employee_id, sp.designation, sp.joining_date,
                   sp.salary_package, sp.total_experience_months, sp.qualification,
                   sp.specialization, sp.bio, sp.profile_photo, sp.publications_count,
                   sp.certifications, sp.emergency_contact_name, sp.emergency_contact_phone,
                   sp.bank_name, sp.bank_account_number, sp.bank_ifsc,
                   d.name AS department_name, d.id AS department_id
            FROM users u
            JOIN user_roles ur ON ur.user_id=u.id AND ur.institution_id=?
            LEFT JOIN staff_profiles sp ON sp.user_id=u.id AND sp.institution_id=?
            LEFT JOIN departments d ON d.id=sp.department_id
            WHERE u.id=?
            GROUP BY u.id
        ", [$this->institutionId, $this->institutionId, $id]);
        $member = $this->db->fetch();
        if (!$member) {
            $this->redirectWith('faculty', 'error', 'Faculty member not found.');
            return;
        }

        // Roles
        $this->db->query("SELECT r.name FROM roles r JOIN user_roles ur ON ur.role_id=r.id
            WHERE ur.user_id=? AND ur.institution_id=?", [$id, $this->institutionId]);
        $roles = array_column($this->db->fetchAll(), 'name');

        // Teaching allocations
        $this->db->query("
            SELECT fa.*, s.subject_name, s.subject_code, s.credits, s.subject_type,
                   b.program_name, b.batch_term, sec.section_name, fa.hours_per_week
            FROM faculty_subject_allocations fa
            JOIN subjects s ON s.id=fa.subject_id
            LEFT JOIN academic_batches b ON b.id=fa.batch_id
            LEFT JOIN academic_sections sec ON sec.id=fa.section_id
            WHERE fa.faculty_id=? AND fa.institution_id=? AND fa.status='active'
            ORDER BY b.program_name, s.subject_name
        ", [$id, $this->institutionId]);
        $allocations = $this->db->fetchAll();
        $totalHours  = array_sum(array_column($allocations, 'hours_per_week'));

        // Leave history
        $this->db->query("
            SELECT slr.*,
                   CONCAT(au.first_name,' ',au.last_name) AS approved_by_name,
                   DATEDIFF(slr.end_date, slr.start_date)+1 AS days
            FROM staff_leave_requests slr
            LEFT JOIN users au ON au.id=slr.approved_by
            WHERE slr.user_id=?
            ORDER BY slr.created_at DESC LIMIT 20
        ", [$id]);
        $leaves = $this->db->fetchAll();

        // Performance reviews
        $this->db->query("
            SELECT fpr.*, CONCAT(u.first_name,' ',u.last_name) AS reviewer_name
            FROM faculty_performance_reviews fpr
            LEFT JOIN users u ON u.id=fpr.reviewed_by
            WHERE fpr.faculty_id=? AND fpr.institution_id=?
            ORDER BY fpr.review_period DESC
        ", [$id, $this->institutionId]);
        $reviews = $this->db->fetchAll();

        // Attendance last 30 days
        $this->db->query("
            SELECT attendance_date, status, check_in, check_out
            FROM faculty_attendance
            WHERE faculty_id=? AND institution_id=? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            ORDER BY attendance_date DESC
        ", [$id, $this->institutionId]);
        $attendance    = $this->db->fetchAll();
        $attendanceMap = array_column($attendance, null, 'attendance_date');

        // This month stats
        $this->db->query("
            SELECT
                SUM(status='present')  AS present,
                SUM(status='absent')   AS absent,
                SUM(status='half_day') AS half_day,
                SUM(status='on_leave') AS on_leave
            FROM faculty_attendance
            WHERE faculty_id=? AND institution_id=?
              AND MONTH(attendance_date)=MONTH(CURDATE())
              AND YEAR(attendance_date)=YEAR(CURDATE())
        ", [$id, $this->institutionId]);
        $monthStats = $this->db->fetch();

        $this->view('faculty/show', compact(
            'member','roles','allocations','totalHours',
            'leaves','reviews','attendance','attendanceMap','monthStats'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE / STORE
    // ──────────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->authorize('users.create');
        $this->ensureSchema();

        $this->db->query("SELECT id, name FROM departments WHERE institution_id=? ORDER BY name",
            [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('faculty/create', compact('departments'));
    }

    public function store(): void
    {
        $this->authorize('users.create');
        $this->ensureSchema();
        verifyCsrf();

        // ── Collect inputs ─────────────────────────────────────────
        $firstName  = sanitize($this->input('first_name', ''));
        $lastName   = sanitize($this->input('last_name', ''));
        $email      = trim($this->input('email', ''));
        $phone      = sanitize($this->input('phone', ''));
        $empId      = sanitize($this->input('employee_id', ''));
        $password   = $this->input('password', '');

        // ── Validation ─────────────────────────────────────────────
        $errors = [];
        if (!$firstName)                   $errors[] = 'First name is required.';
        if (!$lastName)                    $errors[] = 'Last name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 8)         $errors[] = 'Password must be at least 8 characters.';

        // Duplicate email check
        $this->db->query("SELECT id FROM users WHERE email=?", [$email]);
        if ($this->db->fetch()) $errors[] = 'Email already exists.';

        // Duplicate employee_id check (global unique)
        if ($empId) {
            $this->db->query("SELECT id FROM users WHERE employee_id=?", [$empId]);
            if ($this->db->fetch()) $errors[] = 'Employee ID already taken.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            redirect('faculty/create');
            return;
        }

        // ── Get Faculty role id ────────────────────────────────────
        $this->db->query("SELECT id FROM roles WHERE slug='faculty' LIMIT 1");
        $roleRow = $this->db->fetch();
        if (!$roleRow) {
            flash('error', 'Faculty role not found. Run seed data first.');
            redirect('faculty/create');
            return;
        }
        $facultyRoleId = (int)$roleRow['id'];

        // ── Get org id ─────────────────────────────────────────────
        $this->db->query("SELECT organization_id FROM institutions WHERE id=? LIMIT 1",
            [$this->institutionId]);
        $instRow = $this->db->fetch();
        $orgId   = $instRow ? (int)$instRow['organization_id'] : null;

        try {
            $this->db->beginTransaction();

            // Insert user
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
            $this->db->insert('users', [
                'employee_id'       => $empId ?: null,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'email'             => $email,
                'phone'             => $phone,
                'password'          => $hash,
                'email_verified_at' => date('Y-m-d H:i:s'),
                'is_active'         => 1,
            ]);
            $userId = (int)$this->db->lastInsertId();

            // Assign Faculty role
            $this->db->insert('user_roles', [
                'user_id'         => $userId,
                'role_id'         => $facultyRoleId,
                'organization_id' => $orgId,
                'institution_id'  => $this->institutionId,
            ]);

            // Create staff profile
            $this->db->insert('staff_profiles', [
                'user_id'                 => $userId,
                'institution_id'          => $this->institutionId,
                'department_id'           => $this->input('department_id') ?: null,
                'designation'             => sanitize($this->input('designation', '')),
                'joining_date'            => $this->input('joining_date') ?: date('Y-m-d'),
                'qualification'           => sanitize($this->input('qualification', '')),
                'specialization'          => sanitize($this->input('specialization', '')),
                'total_experience_months' => (int)$this->input('total_experience_months', 0),
                'salary_package'          => $this->input('salary_package') ? (float)$this->input('salary_package') : null,
                'status'                  => 'working',
            ]);

            $this->logAudit('faculty_create', 'users', $userId);
            $this->db->commit();

            flash('success', "Faculty member {$firstName} {$lastName} created successfully.");
            redirect("faculty/{$userId}");

        } catch (\Exception $e) {
            $this->db->rollBack();
            appLog('Faculty create failed: ' . $e->getMessage(), 'error');
            flash('error', 'Failed to create faculty. ' . $e->getMessage());
            redirect('faculty/create');
        }
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT / UPDATE
    // ──────────────────────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->authorize('users.manage');
        $this->ensureSchema();

        $this->db->query("SELECT id, first_name, last_name, email, phone FROM users WHERE id=?", [$id]);
        $user = $this->db->fetch();
        if (!$user) { $this->redirectWith('faculty', 'error', 'User not found.'); return; }

        $this->db->query("SELECT * FROM staff_profiles WHERE user_id=? AND institution_id=?",
            [$id, $this->institutionId]);
        $profile = $this->db->fetch() ?: [];

        $this->db->query("SELECT id, name FROM departments WHERE institution_id=? ORDER BY name",
            [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->view('faculty/edit', compact('user','profile','departments'));
    }

    public function update(int $id): void
    {
        $this->authorize('users.manage');
        $this->ensureSchema();
        verifyCsrf();

        // Update phone on users table
        $phone = sanitize($this->input('phone', ''));
        $this->db->update('users', ['phone' => $phone], 'id=?', [$id]);

        $data = [
            'employee_id'             => sanitize($this->input('employee_id', '')),
            'department_id'           => $this->input('department_id') ?: null,
            'designation'             => sanitize($this->input('designation', '')),
            'joining_date'            => $this->input('joining_date') ?: null,
            'qualification'           => sanitize($this->input('qualification', '')),
            'specialization'          => sanitize($this->input('specialization', '')),
            'bio'                     => sanitize($this->input('bio', '')),
            'total_experience_months' => (int)$this->input('total_experience_months', 0),
            'publications_count'      => (int)$this->input('publications_count', 0),
            'certifications'          => sanitize($this->input('certifications', '')),
            'emergency_contact_name'  => sanitize($this->input('emergency_contact_name', '')),
            'emergency_contact_phone' => sanitize($this->input('emergency_contact_phone', '')),
            'salary_package'          => $this->input('salary_package') ? (float)$this->input('salary_package') : null,
            'bank_name'               => sanitize($this->input('bank_name', '')),
            'bank_account_number'     => sanitize($this->input('bank_account_number', '')),
            'bank_ifsc'               => sanitize($this->input('bank_ifsc', '')),
        ];

        $this->db->query("SELECT id FROM staff_profiles WHERE user_id=? AND institution_id=?",
            [$id, $this->institutionId]);
        $existing = $this->db->fetch();
        if ($existing) {
            $this->db->update('staff_profiles', $data, 'id=?', [$existing['id']]);
        } else {
            $data['user_id']        = $id;
            $data['institution_id'] = $this->institutionId;
            $this->db->insert('staff_profiles', $data);
        }

        $this->logAudit('faculty_profile_update', 'staff_profiles', $id);
        $this->redirectWith("faculty/$id", 'success', 'Profile updated successfully.');
    }

    // ──────────────────────────────────────────────────────────────
    // LEAVE MANAGEMENT
    // ──────────────────────────────────────────────────────────────
    public function leave(): void
    {
        $this->authorize('staff.view');
        $this->ensureSchema();

        $statusFilter = $_GET['status'] ?? 'pending';
        $facultyId    = (int)($_GET['faculty_id'] ?? 0);

        $extraWhere = '';
        $params     = [$this->institutionId, $this->institutionId];
        if ($statusFilter && $statusFilter !== 'all') {
            $extraWhere .= " AND slr.status=?";
            $params[]    = $statusFilter;
        }
        if ($facultyId) {
            $extraWhere .= " AND slr.user_id=?";
            $params[]    = $facultyId;
        }

        $this->db->query("
            SELECT slr.*,
                   CONCAT(u.first_name,' ',u.last_name) AS faculty_name, u.email,
                   sp.designation,
                   CONCAT(au.first_name,' ',au.last_name) AS approved_by_name,
                   DATEDIFF(slr.end_date, slr.start_date)+1 AS days
            FROM staff_leave_requests slr
            JOIN users u ON u.id=slr.user_id
            JOIN user_roles ur ON ur.user_id=slr.user_id AND ur.institution_id=?
            JOIN roles r ON r.id=ur.role_id AND r.name NOT IN ('Student','Parent')
            LEFT JOIN staff_profiles sp ON sp.user_id=slr.user_id AND sp.institution_id=?
            LEFT JOIN users au ON au.id=slr.approved_by
            WHERE 1=1 $extraWhere
            GROUP BY slr.id
            ORDER BY slr.created_at DESC
        ", $params);
        $leaves = $this->db->fetchAll();

        // Summary counts
        $this->db->query("
            SELECT slr.status, COUNT(*) AS cnt
            FROM staff_leave_requests slr
            JOIN user_roles ur ON ur.user_id=slr.user_id AND ur.institution_id=?
            JOIN roles r ON r.id=ur.role_id AND r.name NOT IN ('Student','Parent')
            GROUP BY slr.status
        ", [$this->institutionId]);
        $summary = array_column($this->db->fetchAll(), 'cnt', 'status');

        // Faculty list for filter
        $this->db->query("
            SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name
            FROM users u
            JOIN user_roles ur ON ur.user_id=u.id AND ur.institution_id=?
            JOIN roles r ON r.id=ur.role_id AND r.name NOT IN ('Student','Parent')
            GROUP BY u.id ORDER BY u.first_name
        ", [$this->institutionId]);
        $facultyList = $this->db->fetchAll();

        $this->view('faculty/leave', compact('leaves','summary','statusFilter','facultyId','facultyList'));
    }

    public function leaveStore(): void
    {
        $this->authorize('staff.view');
        verifyCsrf();

        $userId    = (int)$this->input('user_id', $this->user['id']);
        $leaveType = $this->input('leave_type', 'casual');
        $startDate = $this->input('start_date');
        $endDate   = $this->input('end_date');
        $reason    = sanitize($this->input('reason', ''));

        if (!$startDate || !$endDate) {
            $this->redirectWith('faculty/leave', 'error', 'Start and end dates are required.');
            return;
        }
        if ($endDate < $startDate) {
            $this->redirectWith('faculty/leave', 'error', 'End date must be on or after start date.');
            return;
        }

        $this->db->insert('staff_leave_requests', [
            'user_id'    => $userId,
            'leave_type' => $leaveType,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'reason'     => $reason,
            'status'     => 'pending',
        ]);

        $this->redirectWith('faculty/leave', 'success', 'Leave request submitted successfully.');
    }

    public function leaveAction(int $id): void
    {
        $this->authorize('users.manage');
        verifyCsrf();

        $action = $this->input('action', 'approve');
        $status = $action === 'approve' ? 'approved' : 'rejected';

        $this->db->update('staff_leave_requests', [
            'status'      => $status,
            'approved_by' => $this->user['id'],
        ], 'id=?', [$id]);

        $this->logAudit("leave_{$status}", 'staff_leave_requests', $id);
        $this->redirectWith('faculty/leave', 'success', 'Leave request ' . $status . '.');
    }

    // ──────────────────────────────────────────────────────────────
    // PERFORMANCE REVIEWS
    // ──────────────────────────────────────────────────────────────
    public function performance(): void
    {
        $this->authorize('staff.view');
        $this->ensureSchema();

        $periodFilter  = $_GET['period'] ?? '';
        $facultyFilter = (int)($_GET['faculty_id'] ?? 0);

        $extraWhere = '';
        $params     = [$this->institutionId];
        if ($periodFilter)  { $extraWhere .= " AND fpr.review_period=?"; $params[] = $periodFilter; }
        if ($facultyFilter) { $extraWhere .= " AND fpr.faculty_id=?";    $params[] = $facultyFilter; }

        $this->db->query("
            SELECT fpr.*,
                   CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                   sp.designation, d.name AS department_name,
                   CONCAT(ru.first_name,' ',ru.last_name) AS reviewer_name
            FROM faculty_performance_reviews fpr
            JOIN users u ON u.id=fpr.faculty_id
            LEFT JOIN staff_profiles sp ON sp.user_id=fpr.faculty_id AND sp.institution_id=fpr.institution_id
            LEFT JOIN departments d ON d.id=sp.department_id
            LEFT JOIN users ru ON ru.id=fpr.reviewed_by
            WHERE fpr.institution_id=? $extraWhere
            ORDER BY fpr.review_period DESC, u.first_name
        ", $params);
        $reviews = $this->db->fetchAll();

        // Faculty list
        $this->db->query("
            SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, sp.designation
            FROM users u
            JOIN user_roles ur ON ur.user_id=u.id AND ur.institution_id=?
            JOIN roles r ON r.id=ur.role_id AND r.name NOT IN ('Student','Parent')
            LEFT JOIN staff_profiles sp ON sp.user_id=u.id AND sp.institution_id=?
            GROUP BY u.id ORDER BY u.first_name
        ", [$this->institutionId, $this->institutionId]);
        $facultyList = $this->db->fetchAll();

        // Existing periods
        $this->db->query("SELECT DISTINCT review_period FROM faculty_performance_reviews
            WHERE institution_id=? ORDER BY review_period DESC", [$this->institutionId]);
        $periods = array_column($this->db->fetchAll(), 'review_period');

        $this->view('faculty/performance', compact('reviews','facultyList','periods','periodFilter','facultyFilter'));
    }

    public function performanceSave(): void
    {
        $this->authorize('users.manage');
        $this->ensureSchema();
        verifyCsrf();

        $facultyId = (int)$this->input('faculty_id');
        $period    = sanitize($this->input('review_period', ''));

        if (!$facultyId || !$period) {
            $this->redirectWith('faculty/performance', 'error', 'Faculty and review period are required.');
            return;
        }

        $tq = min(5, (int)$this->input('teaching_quality', 0));
        $pu = min(5, (int)$this->input('punctuality', 0));
        $rc = min(5, (int)$this->input('research_contribution', 0));
        $ac = min(5, (int)$this->input('admin_contribution', 0));
        $fb = $this->input('student_feedback_score') ? round((float)$this->input('student_feedback_score'), 1) : null;
        $overall = round(($tq + $pu + $rc + $ac) / 4, 1);

        $data = [
            'institution_id'         => $this->institutionId,
            'faculty_id'             => $facultyId,
            'review_period'          => $period,
            'teaching_quality'       => $tq,
            'punctuality'            => $pu,
            'student_feedback_score' => $fb,
            'research_contribution'  => $rc,
            'admin_contribution'     => $ac,
            'overall_rating'         => $overall,
            'comments'               => sanitize($this->input('comments', '')),
            'reviewed_by'            => $this->user['id'],
            'status'                 => 'submitted',
        ];

        $this->db->query("SELECT id FROM faculty_performance_reviews
            WHERE faculty_id=? AND review_period=? AND institution_id=?",
            [$facultyId, $period, $this->institutionId]);
        $existing = $this->db->fetch();
        if ($existing) {
            $this->db->update('faculty_performance_reviews', $data, 'id=?', [$existing['id']]);
        } else {
            $this->db->insert('faculty_performance_reviews', $data);
        }

        $this->logAudit('faculty_performance_save', 'faculty_performance_reviews', $facultyId);
        $this->redirectWith('faculty/performance', 'success', 'Performance review saved successfully.');
    }

    // ──────────────────────────────────────────────────────────────
    // ATTENDANCE
    // ──────────────────────────────────────────────────────────────
    public function attendance(): void
    {
        $this->authorize('staff.view');
        $this->ensureSchema();

        $month = (int)($_GET['month'] ?? date('n'));
        $year  = (int)($_GET['year']  ?? date('Y'));
        $facId = (int)($_GET['faculty_id'] ?? 0);

        // Faculty list
        $this->db->query("
            SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, sp.designation
            FROM users u
            JOIN user_roles ur ON ur.user_id=u.id AND ur.institution_id=?
            JOIN roles r ON r.id=ur.role_id AND r.name NOT IN ('Student','Parent')
            LEFT JOIN staff_profiles sp ON sp.user_id=u.id AND sp.institution_id=?
            GROUP BY u.id ORDER BY u.first_name
        ", [$this->institutionId, $this->institutionId]);
        $facultyList = $this->db->fetchAll();

        // Monthly summary
        $summaryWhere  = $facId ? " AND fa.faculty_id=?" : "";
        $summaryParams = array_merge([$this->institutionId, $month, $year], $facId ? [$facId] : []);
        $this->db->query("
            SELECT fa.faculty_id,
                   CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                   sp.designation,
                   SUM(fa.status='present')  AS present,
                   SUM(fa.status='absent')   AS absent,
                   SUM(fa.status='half_day') AS half_day,
                   SUM(fa.status='on_leave') AS on_leave,
                   SUM(fa.status='holiday')  AS holiday,
                   COUNT(*) AS total_marked
            FROM faculty_attendance fa
            JOIN users u ON u.id=fa.faculty_id
            LEFT JOIN staff_profiles sp ON sp.user_id=fa.faculty_id AND sp.institution_id=fa.institution_id
            WHERE fa.institution_id=?
              AND MONTH(fa.attendance_date)=? AND YEAR(fa.attendance_date)=?
              $summaryWhere
            GROUP BY fa.faculty_id
            ORDER BY u.first_name
        ", $summaryParams);
        $monthlySummary = $this->db->fetchAll();

        // Daily records for selected faculty
        $dailyRecords = [];
        if ($facId) {
            $this->db->query("
                SELECT attendance_date, status, check_in, check_out, remarks
                FROM faculty_attendance
                WHERE faculty_id=? AND institution_id=?
                  AND MONTH(attendance_date)=? AND YEAR(attendance_date)=?
                ORDER BY attendance_date
            ", [$facId, $this->institutionId, $month, $year]);
            $dailyRecords = array_column($this->db->fetchAll(), null, 'attendance_date');
        }

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $monthName   = date('F', mktime(0, 0, 0, $month, 1, $year));

        $this->view('faculty/attendance', compact(
            'facultyList','monthlySummary','dailyRecords',
            'month','year','facId','daysInMonth','monthName'
        ));
    }

    public function attendanceMark(): void
    {
        $this->authorize('users.manage');
        $this->ensureSchema();
        verifyCsrf();

        $facultyId = (int)$this->input('faculty_id');
        $date      = $this->input('date', date('Y-m-d'));
        $status    = $this->input('status', 'present');
        $checkIn   = $this->input('check_in') ?: null;
        $checkOut  = $this->input('check_out') ?: null;
        $remarks   = sanitize($this->input('remarks', ''));

        if (!$facultyId) {
            $this->redirectWith('faculty/attendance', 'error', 'Please select a faculty member.');
            return;
        }

        $attData = [
            'institution_id'  => $this->institutionId,
            'faculty_id'      => $facultyId,
            'attendance_date' => $date,
            'status'          => $status,
            'check_in'        => $checkIn,
            'check_out'       => $checkOut,
            'remarks'         => $remarks,
            'marked_by'       => $this->user['id'],
        ];

        $this->db->query("SELECT id FROM faculty_attendance
            WHERE faculty_id=? AND attendance_date=? AND institution_id=?",
            [$facultyId, $date, $this->institutionId]);
        $existing = $this->db->fetch();

        if ($existing) {
            $this->db->update('faculty_attendance', $attData, 'id=?', [$existing['id']]);
        } else {
            $this->db->insert('faculty_attendance', $attData);
        }

        $this->redirectWith('faculty/attendance', 'success', 'Attendance marked successfully.');
    }

    // ──────────────────────────────────────────────────────────────
    // BULK ATTENDANCE MARK (AJAX)
    // ──────────────────────────────────────────────────────────────
    public function attendanceBulk(): void
    {
        $this->authorize('users.manage');
        $this->ensureSchema();
        verifyCsrf();

        $date    = $this->input('date', date('Y-m-d'));
        $records = $_POST['records'] ?? [];

        foreach ($records as $facId => $status) {
            $facId = (int)$facId;
            if (!$facId) continue;
            $attData = [
                'institution_id'  => $this->institutionId,
                'faculty_id'      => $facId,
                'attendance_date' => $date,
                'status'          => in_array($status, ['present','absent','half_day','on_leave','holiday']) ? $status : 'present',
                'marked_by'       => $this->user['id'],
            ];
            $this->db->query("SELECT id FROM faculty_attendance
                WHERE faculty_id=? AND attendance_date=? AND institution_id=?",
                [$facId, $date, $this->institutionId]);
            $ex = $this->db->fetch();
            $ex ? $this->db->update('faculty_attendance', $attData, 'id=?', [$ex['id']])
                : $this->db->insert('faculty_attendance', $attData);
        }

        $this->redirectWith('faculty/attendance', 'success', 'Bulk attendance saved.');
    }

    // ──────────────────────────────────────────────────────────────
    // EXPORT CSV
    // ──────────────────────────────────────────────────────────────
    public function export(): void
    {
        $this->authorize('staff.view');
        $this->ensureSchema();

        $this->db->query("
            SELECT u.first_name, u.last_name, u.email, u.phone,
                   sp.employee_id, sp.designation, d.name AS department,
                   sp.joining_date, sp.qualification, sp.specialization,
                   ROUND(sp.total_experience_months/12, 1) AS experience_years,
                   sp.publications_count,
                   sp.salary_package,
                   IF(u.is_active,'Active','Inactive') AS status
            FROM users u
            JOIN user_roles ur ON ur.user_id=u.id AND ur.institution_id=?
            JOIN roles r ON r.id=ur.role_id AND r.name NOT IN ('Student','Parent')
            LEFT JOIN staff_profiles sp ON sp.user_id=u.id AND sp.institution_id=?
            LEFT JOIN departments d ON d.id=sp.department_id
            GROUP BY u.id ORDER BY u.first_name
        ", [$this->institutionId, $this->institutionId]);
        $rows = $this->db->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="faculty_export_' . date('Ymd') . '.csv"');
        $fh = fopen('php://output', 'w');
        fputcsv($fh, ['First Name','Last Name','Email','Phone','Employee ID','Designation','Department','Joining Date','Qualification','Specialization','Exp (Yrs)','Publications','Salary Package','Status']);
        foreach ($rows as $r) {
            fputcsv($fh, array_values($r));
        }
        fclose($fh);
        exit;
    }
}
