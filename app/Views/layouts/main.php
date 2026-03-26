<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= e(config('app.name', 'EduCRM')) ?></title>

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
            <span class="brand-name"><?= e(config('app.name', 'EduCRM')) ?></span>
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
                    <a class="nav-link <?= (parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH) === '/' || strpos($_SERVER['REQUEST_URI'],'dashboard') !== false) ? 'active' : '' ?>" href="<?= url('dashboard') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-th-large"></i></div>
                        Dashboard
                    </a>

                    <!-- ORGANIZATION -->
                    <?php if (hasPermission('organizations.view')): ?>
                    <div class="sb-sidenav-menu-heading">Organization</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/organizations') !== false ? 'active' : '' ?>" href="<?= url('organizations') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-sitemap"></i></div>
                        Organizations
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/institutions') !== false ? 'active' : '' ?>" href="<?= url('institutions') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-university"></i></div>
                        Institutions
                    </a>
                    <?php if (hasPermission('departments.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/departments') !== false ? 'active' : '' ?>" href="<?= url('departments') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-project-diagram"></i></div>
                        Departments
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('academic_years.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/academic-years') !== false ? 'active' : '' ?>" href="<?= url('academic-years') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-calendar-alt"></i></div>
                        Academic Years
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- CRM -->
                    <?php if (hasPermission('leads.view') || hasPermission('enquiries.view')): ?>
                    <div class="sb-sidenav-menu-heading">CRM</div>
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

                    <!-- ACADEMIC -->
                    <?php if (hasPermission('admissions.view') || hasPermission('students.view') || hasPermission('courses.view')): ?>
                    <div class="sb-sidenav-menu-heading">Academic</div>
                    <?php endif; ?>

                    <?php if (hasPermission('courses.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/courses') !== false ? 'active' : '' ?>" href="<?= url('courses') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                        Courses
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('batches.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/batches') !== false && strpos($_SERVER['REQUEST_URI'],'/subjects') === false ? 'active' : '' ?>" href="<?= url('batches') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
                        Batches
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/sections') !== false ? 'active' : '' ?>" href="<?= url('sections') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-object-group"></i></div>
                        Sections
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('admissions.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/admissions') !== false ? 'active' : '' ?>" href="<?= url('admissions') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-clipboard-list"></i></div>
                        Admissions
                    </a>
                    <?php endif; ?>

                    <?php if (hasPermission('students.view')): ?>
                    <a class="nav-link collapsed <?= strpos($_SERVER['REQUEST_URI'],'/students') !== false ? 'active' : '' ?>"
                       href="#navStudents" data-bs-toggle="collapse" aria-expanded="<?= strpos($_SERVER['REQUEST_URI'],'/students') !== false ? 'true' : 'false' ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-user-graduate"></i></div>
                        Students
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-down"></i></div>
                    </a>
                    <div class="collapse <?= strpos($_SERVER['REQUEST_URI'],'/students') !== false ? 'show' : '' ?>" id="navStudents" data-bs-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/students/dashboard') !== false ? 'active' : '' ?>" href="<?= url('students/dashboard') ?>">Overview</a>
                            <a class="nav-link <?= $_SERVER['REQUEST_URI'] === url('students') ? 'active' : '' ?>" href="<?= url('students') ?>">All Students</a>
                            <?php if (hasPermission('students.create')): ?>
                            <a class="nav-link" href="<?= url('students/create') ?>">Add Student</a>
                            <?php endif; ?>
                            <a class="nav-link" href="<?= url('students?status=passed_out') ?>">Alumni</a>
                        </nav>
                    </div>
                    <?php endif; ?>

                    <?php if (hasPermission('attendance.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/attendance') !== false ? 'active' : '' ?>" href="<?= url('attendance') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                        Attendance
                    </a>
                    <?php endif; ?>

                    <!-- ACADEMICS (Subjects / Timetable) -->
                    <?php if (hasPermission('subjects.view') || hasPermission('timetable.view')): ?>
                    <div class="sb-sidenav-menu-heading">Academics</div>
                    <?php if (hasPermission('subjects.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/subjects') !== false ? 'active' : '' ?>" href="<?= url('subjects') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                        Subjects
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('timetable.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/timetable') !== false ? 'active' : '' ?>" href="<?= url('timetable') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-clock"></i></div>
                        Timetable
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- EXAMS -->
                    <?php if (hasPermission('exams.view')): ?>
                    <div class="sb-sidenav-menu-heading">Exams</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/exams') !== false ? 'active' : '' ?>" href="<?= url('exams') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-signature"></i></div>
                        Exams & Marks
                    </a>
                    <?php endif; ?>

                    <!-- FINANCE -->
                    <?php if (hasPermission('fees.view')): ?>
                    <div class="sb-sidenav-menu-heading">Finance</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/fees') !== false ? 'active' : '' ?>" href="<?= url('fees') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                        Fee Structures
                    </a>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/payments') !== false ? 'active' : '' ?>" href="<?= url('payments') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                        Payments
                    </a>
                    <?php endif; ?>

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

                    <!-- PLACEMENT -->
                    <?php if (hasPermission('placements.view')): ?>
                    <div class="sb-sidenav-menu-heading">Placement</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/placement') !== false ? 'active' : '' ?>" href="<?= url('placement/drives') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-briefcase"></i></div>
                        Placement Cell
                    </a>
                    <?php endif; ?>

                    <!-- HR & PAYROLL -->
                    <?php if (hasPermission('staff.view') || hasPermission('payroll.payslips')): ?>
                    <div class="sb-sidenav-menu-heading">HR & Payroll</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/hr/staff') !== false ? 'active' : '' ?>" href="<?= url('hr/staff') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-id-badge"></i></div>
                        Staff Directory
                    </a>
                    <?php if (hasPermission('payroll.payslips')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/hr/payroll') !== false ? 'active' : '' ?>" href="<?= url('hr/payroll') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-money-check-alt"></i></div>
                        Payroll
                    </a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- COMMUNICATION -->
                    <?php if (hasPermission('communication.send_email') || hasPermission('communication.send_sms')): ?>
                    <div class="sb-sidenav-menu-heading">Communication</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/communication') !== false ? 'active' : '' ?>" href="<?= url('communication/templates') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-paper-plane"></i></div>
                        Messages
                    </a>
                    <?php endif; ?>

                    <!-- ANALYTICS -->
                    <?php if (hasPermission('reports.view')): ?>
                    <div class="sb-sidenav-menu-heading">Analytics</div>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/reports') !== false ? 'active' : '' ?>" href="<?= url('reports') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                        Reports
                    </a>
                    <?php endif; ?>

                    <!-- ADMIN -->
                    <?php if (hasPermission('users.view') || hasPermission('settings.manage')): ?>
                    <div class="sb-sidenav-menu-heading">Administration</div>
                    <?php if (hasPermission('users.view')): ?>
                    <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'],'/users') !== false ? 'active' : '' ?>" href="<?= url('users') ?>">
                        <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>
                        User Management
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
                        <div class="sb-nav-link-icon"><i class="fas fa-shield-alt"></i></div>
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
                <span>&copy; <?= date('Y') ?> <?= e(config('app.name', 'EduCRM')) ?> &mdash; All rights reserved.</span>
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
