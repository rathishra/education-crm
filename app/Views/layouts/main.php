<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <title><?= e($pageTitle ?? 'Dashboard') ?> - <?= e(config('app.name', 'Education CRM')) ?></title>

    <!-- Bootstrap 5 CSS -->
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
    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="sb-nav-fixed">
    <!-- Top Navbar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-primary">
        <!-- Brand -->
        <a class="navbar-brand ps-3" href="<?= url('dashboard') ?>">
            <i class="fas fa-graduation-cap me-2"></i>
            <span class="d-none d-sm-inline"><?= e(config('app.name', 'Education CRM')) ?></span>
            <span class="d-inline d-sm-none">EduCRM</span>
        </a>

        <!-- Sidebar Toggle -->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Institution Switcher -->
        <?php
        $userInstitutions = session('user_institutions', []);
        if (count($userInstitutions) > 1):
        ?>
        <div class="d-none d-md-inline-block ms-3">
            <form method="POST" action="<?= url('switch-institution') ?>" id="institutionSwitchForm">
                <?= csrfField() ?>
                <select name="institution_id" class="form-select form-select-sm bg-white" onchange="this.form.submit()" style="min-width:200px">
                    <?php foreach ($userInstitutions as $inst): ?>
                        <option value="<?= $inst['id'] ?>" <?= $inst['id'] == currentInstitutionId() ? 'selected' : '' ?>>
                            <?= e($inst['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <?php endif; ?>

        <!-- Right Navbar -->
        <ul class="navbar-nav ms-auto me-3 me-lg-4">
            <!-- Notifications -->
            <li class="nav-item dropdown me-2">
                <a class="nav-link dropdown-toggle position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="badge bg-danger badge-counter position-absolute top-0 start-100 translate-middle p-1 rounded-circle" id="notifCount" style="display:none">
                        <span class="count">0</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow" id="notifList" style="width:320px;max-height:400px;overflow-y:auto">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="text-center p-3 text-muted" id="noNotif">No new notifications</li>
                </ul>
            </li>

            <!-- User Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fa-fw me-1"></i>
                    <span class="d-none d-lg-inline"><?= e(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li>
                        <span class="dropdown-item-text text-muted small">
                            <?= e($currentUser['role_name'] ?? 'User') ?>
                        </span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= url('profile') ?>"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="<?= url('settings') ?>"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="<?= url('logout') ?>">
                            <?= csrfField() ?>
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <div id="layoutSidenav">
        <!-- Sidebar -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <!-- Dashboard -->
                        <a class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/dashboard' ? 'active' : '' ?>" href="<?= url('dashboard') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>

                        <?php if (hasPermission('organizations.view')): ?>
                        <!-- Organization Management -->
                        <div class="sb-sidenav-menu-heading">Organization</div>
                        <a class="nav-link" href="<?= url('organizations') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-sitemap"></i></div>
                            Organizations
                        </a>
                        <a class="nav-link" href="<?= url('campuses') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-building"></i></div>
                            Campuses
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('institutions.view')): ?>
                        <a class="nav-link" href="<?= url('institutions') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-university"></i></div>
                            Institutions
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('departments.view')): ?>
                        <a class="nav-link" href="<?= url('departments') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-sitemap"></i></div>
                            Departments
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('academic_years.view')): ?>
                        <a class="nav-link" href="<?= url('academic-years') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-alt"></i></div>
                            Academic Years
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('leads.view') || hasPermission('enquiries.view')): ?>
                        <!-- Lead Management -->
                        <div class="sb-sidenav-menu-heading">CRM</div>
                        <?php endif; ?>

                        <?php if (hasPermission('enquiries.view')): ?>
                        <a class="nav-link" href="<?= url('enquiries') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-question-circle"></i></div>
                            Enquiries
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('leads.view')): ?>
                        <a class="nav-link" href="<?= url('leads') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-plus"></i></div>
                            Leads
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('followups.view')): ?>
                        <a class="nav-link" href="<?= url('followups') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-phone-alt"></i></div>
                            Follow-ups
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('tasks.view')): ?>
                        <a class="nav-link" href="<?= url('tasks') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-tasks"></i></div>
                            Tasks
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('admissions.view') || hasPermission('students.view')): ?>
                        <!-- Academic -->
                        <div class="sb-sidenav-menu-heading">Academic</div>
                        <?php endif; ?>

                        <?php if (hasPermission('courses.view')): ?>
                        <a class="nav-link" href="<?= url('courses') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>
                            Courses
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('batches.view')): ?>
                        <a class="nav-link" href="<?= url('batches') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
                            Batches
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('batches.view')): ?>
                        <a class="nav-link" href="<?= url('sections') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-object-group"></i></div>
                            Sections
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('admissions.view')): ?>
                        <a class="nav-link" href="<?= url('admissions') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-clipboard-check"></i></div>
                            Admissions
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('students.view')): ?>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseStudents" aria-expanded="false" aria-controls="collapseStudents">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-graduate"></i></div>
                            Students
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseStudents" aria-labelledby="headingStudents" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="<?= url('students/dashboard') ?>">Dashboard</a>
                                <?php if (hasPermission('students.create')): ?>
                                <a class="nav-link" href="<?= url('students/create') ?>">Add Student</a>
                                <?php endif; ?>
                                <a class="nav-link" href="<?= url('students') ?>">Student Directory</a>
                                <a class="nav-link" href="<?= url('students?status=passed_out') ?>">Alumni Directory</a>
                            </nav>
                        </div>
                        <?php endif; ?>

                        <?php if (hasPermission('attendance.view')): ?>
                        <a class="nav-link" href="<?= url('attendance') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-check"></i></div>
                            Attendance
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('subjects.view') || hasPermission('timetable.view')): ?>
                        <!-- Academics -->
                        <div class="sb-sidenav-menu-heading">Academics</div>
                        <?php if (hasPermission('subjects.view')): ?>
                        <a class="nav-link" href="<?= url('subjects') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                            Subjects
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('timetable.view')): ?>
                        <a class="nav-link" href="<?= url('timetable') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-calendar-alt"></i></div>
                            Timetable
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (hasPermission('exams.view')): ?>
                        <!-- Exams -->
                        <div class="sb-sidenav-menu-heading">Exams</div>
                        <a class="nav-link" href="<?= url('exams') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-file-signature"></i></div>
                            Exams & Results
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('hostel.view') || hasPermission('transport.view') || hasPermission('library.view')): ?>
                        <!-- Services -->
                        <div class="sb-sidenav-menu-heading">Services</div>
                        <?php if (hasPermission('hostel.view')): ?>
                        <a class="nav-link" href="<?= url('hostel') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-hotel"></i></div>
                            Hostel
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('transport.view')): ?>
                        <a class="nav-link" href="<?= url('transport') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-bus"></i></div>
                            Transport
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('library.view')): ?>
                        <a class="nav-link" href="<?= url('library') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-book-reader"></i></div>
                            Library
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (hasPermission('placements.view')): ?>
                        <!-- Placement -->
                        <div class="sb-sidenav-menu-heading">Placement</div>
                        <a class="nav-link" href="<?= url('placement/drives') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-briefcase"></i></div>
                            Placement Cell
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('staff.view') || hasPermission('payroll.payslips')): ?>
                        <!-- HR -->
                        <div class="sb-sidenav-menu-heading">HR & Payroll</div>
                        <a class="nav-link" href="<?= url('hr/staff') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-user-tie"></i></div>
                            Staff Directory
                        </a>
                        <?php if (hasPermission('payroll.payslips')): ?>
                                <a class="nav-link" href="<?= url('hr/payroll') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-check-alt"></i></div>
                            Payroll
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>

                        <?php if (hasPermission('fees.view')): ?>
                        <!-- Finance -->
                        <div class="sb-sidenav-menu-heading">Finance</div>
                        <a class="nav-link" href="<?= url('fees') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
                            Fee Structures
                        </a>
                        <a class="nav-link" href="<?= url('payments') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-receipt"></i></div>
                            Payments
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('communication.send_email') || hasPermission('communication.send_sms')): ?>
                        <!-- Communication -->
                        <div class="sb-sidenav-menu-heading">Communication</div>
                        <a class="nav-link" href="<?= url('communication/templates') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-envelope"></i></div>
                            Messages
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('reports.view')): ?>
                        <!-- Reports -->
                        <div class="sb-sidenav-menu-heading">Analytics</div>
                        <a class="nav-link" href="<?= url('reports') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                            Reports
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('users.view') || hasPermission('settings.manage')): ?>
                        <!-- Admin -->
                        <div class="sb-sidenav-menu-heading">Admin</div>
                        <?php endif; ?>

                        <?php if (hasPermission('users.view')): ?>
                        <a class="nav-link" href="<?= url('users') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-users-cog"></i></div>
                            Users
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('settings.manage')): ?>
                        <a class="nav-link" href="<?= url('settings') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-cogs"></i></div>
                            Settings
                        </a>
                        <?php endif; ?>

                        <?php if (hasPermission('audit.view')): ?>
                        <a class="nav-link" href="<?= url('audit-logs') ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-history"></i></div>
                            Audit Logs
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="sb-sidenav-footer">
                    <div class="small text-muted">Logged in as:</div>
                    <?= e(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) ?>
                </div>
            </nav>
        </div>

        <!-- Page Content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 py-4">
                    <?php
                    // Flash messages
                    $flashSuccess = getFlash('success');
                    $flashError = getFlash('error');
                    $flashErrors = getFlash('errors');
                    ?>
                    <?php if ($flashSuccess): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= e($flashSuccess) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($flashError): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= e($flashError) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($flashErrors) && is_array($flashErrors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($flashErrors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Page content injected here -->
                    <?= $content ?>
                </div>
            </main>

            <!-- Footer -->
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">&copy; <?= date('Y') ?> <?= e(config('app.name', 'Education CRM')) ?>. All rights reserved.</div>
                        <div class="text-muted">Version 1.0</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= asset('js/app.js') ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array)$extraJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
