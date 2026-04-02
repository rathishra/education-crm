<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= e(config('app.name', 'Edu Matrix')) ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- Toastr -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <!-- Enterprise CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <?php if (!empty($extraCss)): foreach ((array)$extraCss as $css): ?>
        <link href="<?= $css ?>" rel="stylesheet">
    <?php endforeach; endif; ?>
</head>
<body class="sb-nav-fixed">

<!-- ============================================================
     TOP NAVBAR
     ============================================================ -->
<nav class="sb-topnav navbar navbar-expand navbar-dark">
    <!-- Brand -->
    <a class="navbar-brand ps-2 pe-0 me-0" href="<?= url('dashboard') ?>">
        <div class="brand-logo-wrap">
            <i class="fas fa-graduation-cap text-white"></i>
        </div>
        <div class="brand-text d-none d-sm-block">
            <span class="brand-name"><?= e(config('app.name', 'Edu Matrix')) ?></span>
            <span class="brand-sub">Education Platform</span>
        </div>
    </a>

    <!-- Sidebar Toggle -->
    <button class="btn btn-link ms-3" id="sidebarToggle" title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Institution Switcher -->
    <?php $userInstitutions = session('user_institutions', []); if (count($userInstitutions) > 1): ?>
    <div class="d-none d-md-flex align-items-center ms-3 inst-switcher">
        <form method="POST" action="<?= url('switch-institution') ?>">
            <?= csrfField() ?>
            <select name="institution_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($userInstitutions as $inst): ?>
                <option value="<?= $inst['id'] ?>" <?= $inst['id'] == currentInstitutionId() ? 'selected' : '' ?>>
                    <?= e($inst['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php endif; ?>

    <!-- Right Items -->
    <ul class="navbar-nav ms-auto align-items-center me-2 me-lg-3 gap-1">

        <!-- Quick Actions -->
        <li class="nav-item d-none d-lg-flex">
            <div class="dropdown topnav-dropdown">
                <button class="topnav-icon-btn" data-bs-toggle="dropdown" title="Quick Actions">
                    <i class="fas fa-plus"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Quick Add</h6></li>
                    <?php if (hasPermission('enquiries.create')): ?>
                    <li><a class="dropdown-item" href="<?= url('enquiries/create') ?>"><i class="fas fa-question-circle"></i>New Enquiry</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('leads.create')): ?>
                    <li><a class="dropdown-item" href="<?= url('leads/create') ?>"><i class="fas fa-user-plus"></i>New Lead</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('admissions.create')): ?>
                    <li><a class="dropdown-item" href="<?= url('admissions/create') ?>"><i class="fas fa-clipboard-check"></i>New Admission</a></li>
                    <?php endif; ?>
                    <?php if (hasPermission('students.create')): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= url('students/create') ?>"><i class="fas fa-user-graduate"></i>Add Student</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>

        <!-- Notifications -->
        <li class="nav-item dropdown topnav-dropdown">
            <button class="topnav-icon-btn" id="notifDropdown" data-bs-toggle="dropdown" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notif-badge" id="notifBadge" style="display:none"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0" id="notifList" style="width:340px">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bell me-2"></i>Notifications</span>
                    <span class="badge bg-white text-primary" id="notifCount" style="display:none">0</span>
                </div>
                <div class="p-3 text-center" id="noNotif">
                    <i class="fas fa-check-circle text-success d-block fs-3 mb-2"></i>
                    <small class="text-muted">All caught up!</small>
                </div>
            </div>
        </li>

        <!-- User Menu -->
        <li class="nav-item dropdown topnav-dropdown ms-1">
            <?php
            $initials = strtoupper(
                substr($currentUser['first_name'] ?? 'U', 0, 1) .
                substr($currentUser['last_name'] ?? '', 0, 1)
            );
            ?>
            <button class="btn btn-link p-0 d-flex align-items-center gap-2 nav-link" data-bs-toggle="dropdown">
                <div class="user-avatar"><?= $initials ?></div>
                <div class="user-info d-none d-lg-block text-start">
                    <span class="user-name"><?= e(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?></span>
                    <span class="user-role"><?= e($currentUser['role_name'] ?? 'User') ?></span>
                </div>
                <i class="fas fa-chevron-down text-muted d-none d-lg-block" style="font-size:0.65rem"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="px-3 py-2 border-bottom" style="border-color:rgba(255,255,255,.06) !important">
                    <div class="d-flex align-items-center gap-2">
                        <div class="user-avatar"><?= $initials ?></div>
                        <div>
                            <div style="font-size:.8rem;font-weight:600;color:#e2e8f0"><?= e(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?></div>
                            <div style="font-size:.7rem;color:#64748b"><?= e($currentUser['email'] ?? '') ?></div>
                        </div>
                    </div>
                </li>
                <li><a class="dropdown-item" href="<?= url('profile') ?>"><i class="fas fa-user-circle"></i>My Profile</a></li>
                <li><a class="dropdown-item" href="<?= url('settings') ?>"><i class="fas fa-cog"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="<?= url('logout') ?>">
                        <?= csrfField() ?>
                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i>Sign Out</button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<div id="layoutSidenav">
    <!-- ============================================================
         SIDEBAR
         ============================================================ -->
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">

                    <!-- DASHBOARD -->
                    <a class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH) === '/' || strpos($_SERVER['REQUEST_URI'],'/dashboard') !== false) && strpos($_SERVER['REQUEST_URI'],'/crm/dashboard') === false ? 'active' : '' ?>" href="<?= url('dashboard') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-th-large"></i></div>
                        Dashboard
                    </a>

                    <!-- CRM -->
                    <?php if (hasPermission('leads.view') || hasPermission('enquiries.view')): ?>
                    <div class="sb-sidenav-menu-heading">CRM</div>
                    <?php if (hasPermission('leads.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/crm/dashboard') !== false ? 'active' : '' ?>" href="<?= url('crm/dashboard') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
                        Pipeline
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('enquiries.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/enquiries') !== false ? 'active' : '' ?>" href="<?= url('enquiries') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-comments"></i></div>
                        Enquiries
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('leads.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/leads') !== false ? 'active' : '' ?>" href="<?= url('leads') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-funnel-dollar"></i></div>
                        Leads
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('followups.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/followups') !== false ? 'active' : '' ?>" href="<?= url('followups') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-phone-volume"></i></div>
                        Follow-ups
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('tasks.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/tasks') !== false ? 'active' : '' ?>" href="<?= url('tasks') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-check-square"></i></div>
                        Tasks
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- ADMISSIONS -->
                    <?php if (hasPermission('admissions.view')): ?>
                    <div class="sb-sidenav-menu-heading">Admissions</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/admissions') !== false ? 'active' : '' ?>" href="<?= url('admissions') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                        Applications
                    </a>
                    <?php if (hasPermission('courses.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/courses') !== false ? 'active' : '' ?>" href="<?= url('courses') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                        Programs / Courses
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- ACADEMIC -->
                    <div class="sb-sidenav-menu-heading">Academic Setup</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic-years') !== false ? 'active' : '' ?>" href="<?= url('academic-years') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                        Academic Years
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/batches') !== false ? 'active' : '' ?>" href="<?= url('academic/batches') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
                        Batches
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/sections') !== false ? 'active' : '' ?>" href="<?= url('academic/sections') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-object-group"></i></div>
                        Sections
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/subjects') !== false ? 'active' : '' ?>" href="<?= url('academic/subjects') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                        Subjects
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/classrooms') !== false ? 'active' : '' ?>" href="<?= url('academic/classrooms') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-door-open"></i></div>
                        Classrooms & Labs
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/periods') !== false ? 'active' : '' ?>" href="<?= url('academic/periods') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-clock"></i></div>
                        Period Management
                    </a>

                    <!-- TEACHING -->
                    <div class="sb-sidenav-menu-heading">Teaching</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/subject-allocation') !== false ? 'active' : '' ?>" href="<?= url('academic/subject-allocation') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-book-reader"></i></div>
                        Subject Allocation
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/faculty-allocation') !== false ? 'active' : '' ?>" href="<?= url('academic/faculty-allocation') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        Faculty Allocation
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/timetable') !== false ? 'active' : '' ?>" href="<?= url('academic/timetable') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-calendar-alt"></i></div>
                        Timetable
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/attendance') !== false ? 'active' : '' ?>" href="<?= url('academic/attendance') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-check"></i></div>
                        Attendance
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/lms') !== false ? 'active' : '' ?>" href="<?= url('academic/lms') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-graduation-cap"></i></div>
                        LMS / Materials
                    </a>

                    <!-- STUDENTS -->
                    <?php if (hasPermission('students.view')): ?>
                    <div class="sb-sidenav-menu-heading">Students</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/students/dashboard') !== false ? 'active' : '' ?>" href="<?= url('students/dashboard') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Overview
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/students') !== false && strpos($_SERVER['REQUEST_URI'],'/dashboard') === false && strpos($_SERVER['REQUEST_URI'],'/portal-access') === false ? 'active' : '' ?>" href="<?= url('students') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-graduate"></i></div>
                        All Students
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/students/portal-access') !== false ? 'active' : '' ?>" href="<?= url('students/portal-access') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                        Portal Access
                    </a>
                    <?php endif; ?>

                    <!-- EXAMINATIONS -->
                    <div class="sb-sidenav-menu-heading">Examinations</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/grading-schemas') !== false ? 'active' : '' ?>" href="<?= url('academic/grading-schemas') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-star-half-alt"></i></div>
                        Grading Schemes
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic/assessments') !== false ? 'active' : '' ?>" href="<?= url('academic/assessments') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                        Internal Assessment
                    </a>
                    <?php if (hasPermission('exams.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/exams') !== false ? 'active' : '' ?>" href="<?= url('exams') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-signature"></i></div>
                        Exams & Marks
                    </a>
                    <?php endif; ?>

                    <!-- FEES MANAGEMENT -->
                    <div class="sb-sidenav-menu-heading">Fees Management</div>
                    <a class="nav-link <?= preg_match('#^/fees$#', parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)) ? 'active' : '' ?>" href="<?= url('fees') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Fee Dashboard
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/fees/heads') !== false ? 'active' : '' ?>" href="<?= url('fees/heads') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                        Fee Heads
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/fees/structures') !== false ? 'active' : '' ?>" href="<?= url('fees/structures') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-sitemap"></i></div>
                        Fee Structures
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/fees/assignment') !== false ? 'active' : '' ?>" href="<?= url('fees/assignment') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-tag"></i></div>
                        Fee Assignment
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/fees/collection') !== false ? 'active' : '' ?>" href="<?= url('fees/collection') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-cash-register"></i></div>
                        Fee Collection
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/fees/concessions') !== false ? 'active' : '' ?>" href="<?= url('fees/concessions') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-percentage"></i></div>
                        Concessions
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/fees/refunds') !== false ? 'active' : '' ?>" href="<?= url('fees/refunds') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-undo-alt"></i></div>
                        Refunds
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/fees/reports') !== false ? 'active' : '' ?>" href="<?= url('fees/reports') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                        Fee Reports
                    </a>

                    <!-- SERVICES -->
                    <?php if (hasPermission('hostel.view') || hasPermission('transport.view') || hasPermission('library.view')): ?>
                    <div class="sb-sidenav-menu-heading">Services</div>
                    <?php if (hasPermission('hostel.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/hostel') !== false ? 'active' : '' ?>" href="<?= url('hostels') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-hotel"></i></div>
                        Hostel
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('transport.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/transport') !== false ? 'active' : '' ?>" href="<?= url('transport') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-bus-alt"></i></div>
                        Transport
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('library.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/library') !== false ? 'active' : '' ?>" href="<?= url('library') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-book-reader"></i></div>
                        Library
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- FACULTY MANAGEMENT -->
                    <?php if (hasPermission('staff.view')): ?>
                    <div class="sb-sidenav-menu-heading">Faculty</div>
                    <a class="nav-link <?= preg_match('#/faculty(/|$)(?!leave|perf|attend|create)#', $_SERVER['REQUEST_URI']) ? 'active' : '' ?>" href="<?= url('faculty') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                        Faculty Directory
                    </a>
                    <?php if (hasPermission('users.create')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/faculty/create') !== false ? 'active' : '' ?>" href="<?= url('faculty/create') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                        Add Faculty
                    </a>
                    <?php endif; ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/faculty/leave') !== false ? 'active' : '' ?>" href="<?= url('faculty/leave') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-calendar-times"></i></div>
                        Leave Management
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/faculty/attendance') !== false ? 'active' : '' ?>" href="<?= url('faculty/attendance') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-clipboard-check"></i></div>
                        Attendance
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/faculty/performance') !== false ? 'active' : '' ?>" href="<?= url('faculty/performance') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-star"></i></div>
                        Performance
                    </a>
                    <?php endif; ?>

                    <!-- HR & PAYROLL -->
                    <?php if (hasPermission('staff.view') || hasPermission('payroll.payslips')): ?>
                    <div class="sb-sidenav-menu-heading">HR & Payroll</div>
                    <?php if (hasPermission('staff.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/hr/staff') !== false ? 'active' : '' ?>" href="<?= url('hr/staff') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-id-badge"></i></div>
                        Staff Directory
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('payroll.payslips')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/hr/payroll') !== false ? 'active' : '' ?>" href="<?= url('hr/payroll') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-money-check-alt"></i></div>
                        Payroll
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- ADMINISTRATION -->
                    <?php if (hasPermission('users.view') || hasPermission('settings.manage')): ?>
                    <div class="sb-sidenav-menu-heading">Administration</div>
                    <?php if (hasPermission('organizations.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/institutions') !== false ? 'active' : '' ?>" href="<?= url('institutions') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-university"></i></div>
                        Institutions
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('departments.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/departments') !== false ? 'active' : '' ?>" href="<?= url('departments') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-project-diagram"></i></div>
                        Departments
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('users.view')): ?>
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'],'/users') !== false && strpos($_SERVER['REQUEST_URI'],'/roles') === false) ? 'active' : '' ?>" href="<?= url('users') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>
                        Users
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('roles.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/roles') !== false ? 'active' : '' ?>" href="<?= url('roles') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-shield-alt"></i></div>
                        Role Management
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('settings.manage')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/settings') !== false ? 'active' : '' ?>" href="<?= url('settings') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-sliders-h"></i></div>
                        Settings
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('audit.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/audit') !== false ? 'active' : '' ?>" href="<?= url('audit-logs') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
                        Audit Logs
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div><!-- /sb-sidenav-menu -->

            <!-- Sidebar Footer -->
            <div class="sb-sidenav-footer">
                <div class="footer-avatar"><?= strtoupper(substr($currentUser['first_name'] ?? 'U', 0, 1) . substr($currentUser['last_name'] ?? '', 0, 1)) ?></div>
                <div class="footer-info">
                    <span class="footer-name"><?= e(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?></span>
                    <span class="footer-role"><?= e($currentUser['role_name'] ?? 'User') ?></span>
                </div>
            </div>
        </nav>
    </div><!-- /layoutSidenav_nav -->

    <!-- ============================================================
         MAIN CONTENT
         ============================================================ -->
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid">

                <!-- Flash Messages -->
                <?php
                $flashSuccess = getFlash('success');
                $flashError   = getFlash('error');
                $flashWarning = getFlash('warning');
                $flashErrors  = getFlash('errors');
                ?>

                <?php if ($flashSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <?= e($flashSuccess) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($flashError): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= e($flashError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if ($flashWarning): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= e($flashWarning) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($flashErrors) && is_array($flashErrors)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <?php foreach ($flashErrors as $err): ?>
                            <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Page Content -->
                <?= $content ?>

            </div><!-- /container-fluid -->
        </main>

        <!-- Footer -->
        <footer class="mt-auto">
            <div class="d-flex justify-content-between align-items-center">
                <span>&copy; <?= date('Y') ?> <?= e(config('app.name', 'Edu Matrix')) ?> &mdash; All rights reserved.</span>
                <span>Enterprise Edition v2.0</span>
            </div>
        </footer>
    </div><!-- /layoutSidenav_content -->
</div><!-- /layoutSidenav -->

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>var APP_URL = '<?= rtrim(config('app.url', ''), '/') ?>';</script>
<script src="<?= asset('js/app.js') ?>"></script>
<?php if (!empty($extraJs)): foreach ((array)$extraJs as $js): ?>
    <script src="<?= $js ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
