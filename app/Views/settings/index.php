<?php $pageTitle = 'Settings'; ?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="h3 mb-1"><i class="fas fa-cog me-2 text-primary"></i>System Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>
    </div>
</div>

<?php
// Flash messages
$flash = getFlash('success');
$flashErr = getFlash('error');
if ($flash): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i><?= e($flash) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if ($flashErr): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i><?= e($flashErr) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Module Tabs -->
<ul class="nav nav-tabs mb-0 border-bottom-0" id="settingsTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= ($tab ?? 'general') === 'general' ? 'active' : '' ?>"
           href="#tab-general" data-bs-toggle="tab" role="tab">
            <i class="fas fa-sliders-h me-1"></i>General
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($tab ?? '') === 'academic' ? 'active' : '' ?>"
           href="#tab-academic" data-bs-toggle="tab" role="tab">
            <i class="fas fa-graduation-cap me-1"></i>Academic
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($tab ?? '') === 'localization' ? 'active' : '' ?>"
           href="#tab-localization" data-bs-toggle="tab" role="tab">
            <i class="fas fa-globe me-1"></i>Localization
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($tab ?? '') === 'security' ? 'active' : '' ?>"
           href="#tab-security" data-bs-toggle="tab" role="tab">
            <i class="fas fa-shield-alt me-1"></i>Security
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= ($tab ?? '') === 'appearance' ? 'active' : '' ?>"
           href="#tab-appearance" data-bs-toggle="tab" role="tab">
            <i class="fas fa-paint-brush me-1"></i>Appearance
        </a>
    </li>
    <li class="nav-item ms-auto">
        <a class="nav-link" href="<?= url('settings/communication') ?>">
            <i class="fas fa-paper-plane me-1"></i>Communication
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?= url('audit-logs') ?>">
            <i class="fas fa-history me-1"></i>Audit Logs
        </a>
    </li>
</ul>

<div class="tab-content bg-body border border-top-0 rounded-bottom p-4" id="settingsTabContent">

    <!-- ── GENERAL ───────────────────────────────────────────────── -->
    <div class="tab-pane fade <?= ($tab ?? 'general') === 'general' ? 'show active' : '' ?>"
         id="tab-general" role="tabpanel">
        <form method="POST" action="<?= url('settings') ?>">
            <?= csrfField() ?>
            <input type="hidden" name="_group" value="general">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Application Identity
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label fw-medium">Application Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="app_name"
                                           value="<?= e($settings['app_name'] ?? '') ?>"
                                           placeholder="EduMatrix CRM" required>
                                    <div class="form-text">Shown in the browser title bar and emails.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Short Name / Code</label>
                                    <input type="text" class="form-control" name="app_short_name"
                                           value="<?= e($settings['app_short_name'] ?? '') ?>"
                                           placeholder="EDU" maxlength="10">
                                    <div class="form-text">Used in reference numbers.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">System Email</label>
                                    <input type="email" class="form-control" name="app_email"
                                           value="<?= e($settings['app_email'] ?? '') ?>"
                                           placeholder="admin@institution.com">
                                    <div class="form-text">Default "from" address for system emails.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Support Phone</label>
                                    <input type="text" class="form-control" name="app_phone"
                                           value="<?= e($settings['app_phone'] ?? '') ?>"
                                           placeholder="+91 98765 43210">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Website URL</label>
                                    <input type="url" class="form-control" name="app_url"
                                           value="<?= e($settings['app_url'] ?? '') ?>"
                                           placeholder="https://www.institution.com">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-medium">Footer Text</label>
                                    <input type="text" class="form-control" name="footer_text"
                                           value="<?= e($settings['footer_text'] ?? '') ?>"
                                           placeholder="© 2025 My Institution. All rights reserved.">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-database me-2 text-primary"></i>System Behaviour
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Records Per Page</label>
                                    <select class="form-select" name="per_page">
                                        <?php foreach ([10, 15, 25, 50, 100] as $n): ?>
                                        <option value="<?= $n ?>" <?= (int)($settings['per_page'] ?? 15) === $n ? 'selected' : '' ?>><?= $n ?> rows</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Timezone</label>
                                    <select class="form-select" name="timezone">
                                        <?php
                                        $timezones = [
                                            'Asia/Kolkata'       => 'India — IST (UTC+5:30)',
                                            'Asia/Dhaka'         => 'Bangladesh — BST (UTC+6)',
                                            'Asia/Karachi'       => 'Pakistan — PKT (UTC+5)',
                                            'Asia/Dubai'         => 'UAE — GST (UTC+4)',
                                            'Asia/Singapore'     => 'Singapore — SGT (UTC+8)',
                                            'Asia/Kuala_Lumpur'  => 'Malaysia — MYT (UTC+8)',
                                            'Europe/London'      => 'London — GMT/BST',
                                            'America/New_York'   => 'US Eastern — ET',
                                            'America/Los_Angeles'=> 'US Pacific — PT',
                                            'UTC'                => 'UTC',
                                        ];
                                        $current = $settings['timezone'] ?? 'Asia/Kolkata';
                                        foreach ($timezones as $tz => $label):
                                        ?>
                                        <option value="<?= $tz ?>" <?= $current === $tz ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Maintenance Mode</label>
                                    <select class="form-select" name="maintenance_mode">
                                        <option value="0" <?= empty($settings['maintenance_mode']) ? 'selected' : '' ?>>Off — System Active</option>
                                        <option value="1" <?= ($settings['maintenance_mode'] ?? '0') === '1' ? 'selected' : '' ?>>On — Show Maintenance Page</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Default Language</label>
                                    <select class="form-select" name="default_language">
                                        <option value="en" <?= ($settings['default_language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                                        <option value="hi" <?= ($settings['default_language'] ?? '') === 'hi' ? 'selected' : '' ?>>Hindi</option>
                                        <option value="ar" <?= ($settings['default_language'] ?? '') === 'ar' ? 'selected' : '' ?>>Arabic</option>
                                        <option value="fr" <?= ($settings['default_language'] ?? '') === 'fr' ? 'selected' : '' ?>>French</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Enable Notifications</label>
                                    <select class="form-select" name="notifications_enabled">
                                        <option value="1" <?= ($settings['notifications_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                                        <option value="0" <?= ($settings['notifications_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Save General Settings
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Info card -->
                    <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3"><i class="fas fa-lightbulb me-2"></i>Quick Links</h6>
                            <div class="list-group list-group-flush rounded">
                                <a href="<?= url('settings/communication') ?>" class="list-group-item list-group-item-action border-0 rounded mb-1">
                                    <i class="fas fa-envelope me-2 text-primary"></i>Email &amp; SMS Config
                                </a>
                                <a href="<?= url('audit-logs') ?>" class="list-group-item list-group-item-action border-0 rounded mb-1">
                                    <i class="fas fa-history me-2 text-warning"></i>Audit Logs
                                </a>
                                <a href="<?= url('users') ?>" class="list-group-item list-group-item-action border-0 rounded mb-1">
                                    <i class="fas fa-users-cog me-2 text-info"></i>Manage Users
                                </a>
                                <a href="<?= url('roles') ?>" class="list-group-item list-group-item-action border-0 rounded mb-1">
                                    <i class="fas fa-user-shield me-2 text-success"></i>Roles &amp; Permissions
                                </a>
                                <a href="<?= url('departments') ?>" class="list-group-item list-group-item-action border-0 rounded">
                                    <i class="fas fa-sitemap me-2 text-secondary"></i>Departments
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- System Info -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-server me-2 text-secondary"></i>System Info
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <td class="text-muted ps-3">PHP Version</td>
                                    <td class="fw-medium"><?= PHP_VERSION ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-3">Server</td>
                                    <td class="fw-medium"><?= php_uname('s') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-3">DB</td>
                                    <td class="fw-medium">MySQL</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-3">Timezone</td>
                                    <td class="fw-medium"><?= date_default_timezone_get() ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-3 pb-2">Date</td>
                                    <td class="fw-medium pb-2"><?= date('d M Y') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ── ACADEMIC ──────────────────────────────────────────────── -->
    <div class="tab-pane fade <?= ($tab ?? '') === 'academic' ? 'show active' : '' ?>"
         id="tab-academic" role="tabpanel">
        <form method="POST" action="<?= url('settings') ?>">
            <?= csrfField() ?>
            <input type="hidden" name="_group" value="academic">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-calendar-alt me-2 text-success"></i>Academic Year
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Current Academic Year</label>
                                    <input type="text" class="form-control" name="current_academic_year"
                                           value="<?= e($settings['current_academic_year'] ?? date('Y') . '-' . (date('Y')+1)) ?>"
                                           placeholder="2025-2026">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Session Start Month</label>
                                    <select class="form-select" name="session_start_month">
                                        <?php
                                        $months = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
                                        $curMonth = $settings['session_start_month'] ?? '06';
                                        foreach ($months as $m => $name):
                                        ?>
                                        <option value="<?= $m ?>" <?= $curMonth === $m ? 'selected' : '' ?>><?= $name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Session End Month</label>
                                    <select class="form-select" name="session_end_month">
                                        <?php
                                        $curEnd = $settings['session_end_month'] ?? '05';
                                        foreach ($months as $m => $name):
                                        ?>
                                        <option value="<?= $m ?>" <?= $curEnd === $m ? 'selected' : '' ?>><?= $name ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-star me-2 text-warning"></i>Grading System
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Grading Type</label>
                                    <select class="form-select" name="grading_type">
                                        <option value="percentage" <?= ($settings['grading_type'] ?? 'percentage') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                        <option value="gpa_4" <?= ($settings['grading_type'] ?? '') === 'gpa_4' ? 'selected' : '' ?>>GPA (4.0 Scale)</option>
                                        <option value="gpa_10" <?= ($settings['grading_type'] ?? '') === 'gpa_10' ? 'selected' : '' ?>>GPA (10.0 Scale)</option>
                                        <option value="letter" <?= ($settings['grading_type'] ?? '') === 'letter' ? 'selected' : '' ?>>Letter Grades</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Pass Mark (%)</label>
                                    <input type="number" class="form-control" name="pass_mark"
                                           value="<?= e($settings['pass_mark'] ?? 35) ?>"
                                           min="1" max="100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Max Marks</label>
                                    <input type="number" class="form-control" name="max_marks"
                                           value="<?= e($settings['max_marks'] ?? 100) ?>"
                                           min="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Show Grade in Report Card</label>
                                    <select class="form-select" name="show_grade_in_report">
                                        <option value="1" <?= ($settings['show_grade_in_report'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>
                                        <option value="0" <?= ($settings['show_grade_in_report'] ?? '') === '0' ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Grade Display Format</label>
                                    <select class="form-select" name="grade_display_format">
                                        <option value="both" <?= ($settings['grade_display_format'] ?? 'both') === 'both' ? 'selected' : '' ?>>Marks + Grade</option>
                                        <option value="marks" <?= ($settings['grade_display_format'] ?? '') === 'marks' ? 'selected' : '' ?>>Marks Only</option>
                                        <option value="grade" <?= ($settings['grade_display_format'] ?? '') === 'grade' ? 'selected' : '' ?>>Grade Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-user-check me-2 text-info"></i>Attendance Policy
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Min. Attendance (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="min_attendance_percent"
                                               value="<?= e($settings['min_attendance_percent'] ?? 75) ?>"
                                               min="1" max="100">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="form-text">Below this = attendance shortage warning.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Working Days / Week</label>
                                    <input type="number" class="form-control" name="working_days_per_week"
                                           value="<?= e($settings['working_days_per_week'] ?? 6) ?>"
                                           min="1" max="7">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Attendance Basis</label>
                                    <select class="form-select" name="attendance_basis">
                                        <option value="daily" <?= ($settings['attendance_basis'] ?? 'daily') === 'daily' ? 'selected' : '' ?>>Daily</option>
                                        <option value="period" <?= ($settings['attendance_basis'] ?? '') === 'period' ? 'selected' : '' ?>>Period-wise</option>
                                        <option value="subject" <?= ($settings['attendance_basis'] ?? '') === 'subject' ? 'selected' : '' ?>>Subject-wise</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Late Mark Threshold (min)</label>
                                    <input type="number" class="form-control" name="late_mark_minutes"
                                           value="<?= e($settings['late_mark_minutes'] ?? 15) ?>"
                                           min="0">
                                    <div class="form-text">Minutes after schedule start = late.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Auto-notify Absent Parents</label>
                                    <select class="form-select" name="auto_notify_absent">
                                        <option value="1" <?= ($settings['auto_notify_absent'] ?? '0') === '1' ? 'selected' : '' ?>>Yes — via SMS</option>
                                        <option value="0" <?= ($settings['auto_notify_absent'] ?? '0') === '0' ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fas fa-save me-2"></i>Save Academic Settings
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                        <div class="card-body">
                            <h6 class="fw-bold text-success mb-3"><i class="fas fa-info-circle me-2"></i>Academic Tips</h6>
                            <ul class="list-unstyled small text-muted mb-0">
                                <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Academic year format: <code>YYYY-YYYY</code></li>
                                <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Grading type affects report card display</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Minimum attendance shown as alert on student dashboard</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Pass mark used in exam results module</li>
                                <li><i class="fas fa-check text-success me-1"></i> Auto-notify requires SMS configured in Communication tab</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ── LOCALIZATION ──────────────────────────────────────────── -->
    <div class="tab-pane fade <?= ($tab ?? '') === 'localization' ? 'show active' : '' ?>"
         id="tab-localization" role="tabpanel">
        <form method="POST" action="<?= url('settings') ?>">
            <?= csrfField() ?>
            <input type="hidden" name="_group" value="localization">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-clock me-2 text-info"></i>Date &amp; Time Formats
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Date Format</label>
                                    <select class="form-select" name="date_format" id="dateFormatSelect">
                                        <?php
                                        $fmts = [
                                            'd/m/Y' => date('d/m/Y') . ' — DD/MM/YYYY',
                                            'm/d/Y' => date('m/d/Y') . ' — MM/DD/YYYY',
                                            'Y-m-d' => date('Y-m-d') . ' — YYYY-MM-DD',
                                            'd-m-Y' => date('d-m-Y') . ' — DD-MM-YYYY',
                                            'd M Y' => date('d M Y') . ' — DD Mon YYYY',
                                            'M d, Y' => date('M d, Y') . ' — Mon DD, YYYY',
                                        ];
                                        $cur = $settings['date_format'] ?? 'd/m/Y';
                                        foreach ($fmts as $f => $label):
                                        ?>
                                        <option value="<?= $f ?>" <?= $cur === $f ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Time Format</label>
                                    <select class="form-select" name="time_format">
                                        <option value="12" <?= ($settings['time_format'] ?? '12') === '12' ? 'selected' : '' ?>>12-hour (2:30 PM)</option>
                                        <option value="24" <?= ($settings['time_format'] ?? '') === '24' ? 'selected' : '' ?>>24-hour (14:30)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-money-bill-wave me-2 text-success"></i>Currency &amp; Numbers
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Currency Code</label>
                                    <select class="form-select" name="currency_code">
                                        <?php
                                        $currencies = ['INR'=>'₹ Indian Rupee','USD'=>'$ US Dollar','GBP'=>'£ British Pound','EUR'=>'€ Euro','AED'=>'AED UAE Dirham','BDT'=>'৳ Bangladeshi Taka','PKR'=>'₨ Pakistani Rupee','LKR'=>'₨ Sri Lankan Rupee','MYR'=>'RM Malaysian Ringgit','SGD'=>'S$ Singapore Dollar'];
                                        $curCurr = $settings['currency_code'] ?? 'INR';
                                        foreach ($currencies as $code => $label):
                                        ?>
                                        <option value="<?= $code ?>" <?= $curCurr === $code ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Currency Symbol</label>
                                    <input type="text" class="form-control" name="currency_symbol"
                                           value="<?= e($settings['currency_symbol'] ?? '₹') ?>"
                                           placeholder="₹" maxlength="5">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Decimal Separator</label>
                                    <select class="form-select" name="decimal_separator">
                                        <option value="." <?= ($settings['decimal_separator'] ?? '.') === '.' ? 'selected' : '' ?>>. (period)</option>
                                        <option value="," <?= ($settings['decimal_separator'] ?? '') === ',' ? 'selected' : '' ?>>. (comma)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Thousands Separator</label>
                                    <select class="form-select" name="thousands_separator">
                                        <option value="," <?= ($settings['thousands_separator'] ?? ',') === ',' ? 'selected' : '' ?>>, (comma)</option>
                                        <option value="." <?= ($settings['thousands_separator'] ?? '') === '.' ? 'selected' : '' ?>>. (period)</option>
                                        <option value=" " <?= ($settings['thousands_separator'] ?? '') === ' ' ? 'selected' : '' ?>>  (space)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Number System</label>
                                    <select class="form-select" name="number_system">
                                        <option value="western" <?= ($settings['number_system'] ?? 'western') === 'western' ? 'selected' : '' ?>>Western (1,000,000)</option>
                                        <option value="indian" <?= ($settings['number_system'] ?? '') === 'indian' ? 'selected' : '' ?>>Indian (10,00,000)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button type="submit" class="btn btn-info text-white px-4">
                                <i class="fas fa-save me-2"></i>Save Localization Settings
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent fw-semibold"><i class="fas fa-eye me-2"></i>Preview</div>
                        <div class="card-body">
                            <p class="text-muted small mb-2">Sample date:</p>
                            <p class="h6 mb-3" id="datePreview"><?= date($settings['date_format'] ?? 'd/m/Y') ?></p>
                            <p class="text-muted small mb-2">Sample amount:</p>
                            <p class="h6" id="amountPreview"><?= ($settings['currency_symbol'] ?? '₹') ?>10,500.00</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ── SECURITY ──────────────────────────────────────────────── -->
    <div class="tab-pane fade <?= ($tab ?? '') === 'security' ? 'show active' : '' ?>"
         id="tab-security" role="tabpanel">
        <form method="POST" action="<?= url('settings') ?>">
            <?= csrfField() ?>
            <input type="hidden" name="_group" value="security">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-lock me-2 text-danger"></i>Session &amp; Login
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Session Timeout (min)</label>
                                    <input type="number" class="form-control" name="session_timeout"
                                           value="<?= e($settings['session_timeout'] ?? 60) ?>"
                                           min="5" max="1440">
                                    <div class="form-text">Auto-logout after inactivity.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Max Login Attempts</label>
                                    <input type="number" class="form-control" name="max_login_attempts"
                                           value="<?= e($settings['max_login_attempts'] ?? 5) ?>"
                                           min="1" max="20">
                                    <div class="form-text">Before account is locked.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Lockout Duration (min)</label>
                                    <input type="number" class="form-control" name="lockout_duration"
                                           value="<?= e($settings['lockout_duration'] ?? 30) ?>"
                                           min="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Remember Me Duration (days)</label>
                                    <input type="number" class="form-control" name="remember_me_days"
                                           value="<?= e($settings['remember_me_days'] ?? 30) ?>"
                                           min="1" max="365">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Enable Two-Factor Auth</label>
                                    <select class="form-select" name="two_factor_auth">
                                        <option value="0" <?= ($settings['two_factor_auth'] ?? '0') === '0' ? 'selected' : '' ?>>Disabled</option>
                                        <option value="email" <?= ($settings['two_factor_auth'] ?? '') === 'email' ? 'selected' : '' ?>>Email OTP</option>
                                        <option value="sms" <?= ($settings['two_factor_auth'] ?? '') === 'sms' ? 'selected' : '' ?>>SMS OTP</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-key me-2 text-warning"></i>Password Policy
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Minimum Length</label>
                                    <input type="number" class="form-control" name="password_min_length"
                                           value="<?= e($settings['password_min_length'] ?? 8) ?>"
                                           min="4" max="32">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Password Expiry (days)</label>
                                    <input type="number" class="form-control" name="password_expiry_days"
                                           value="<?= e($settings['password_expiry_days'] ?? 0) ?>"
                                           min="0">
                                    <div class="form-text">0 = never expires</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Password History</label>
                                    <input type="number" class="form-control" name="password_history_count"
                                           value="<?= e($settings['password_history_count'] ?? 5) ?>"
                                           min="0">
                                    <div class="form-text">Prevent reusing last N passwords.</div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="pw_require_uppercase" value="1"
                                               id="pwUpper" <?= !empty($settings['pw_require_uppercase']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="pwUpper">Uppercase</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="pw_require_number" value="1"
                                               id="pwNum" <?= !empty($settings['pw_require_number']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="pwNum">Number</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="pw_require_special" value="1"
                                               id="pwSpec" <?= !empty($settings['pw_require_special']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="pwSpec">Special Char</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-shield-alt me-2 text-primary"></i>Access Control
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Force HTTPS</label>
                                    <select class="form-select" name="force_https">
                                        <option value="0" <?= ($settings['force_https'] ?? '0') === '0' ? 'selected' : '' ?>>No</option>
                                        <option value="1" <?= ($settings['force_https'] ?? '') === '1' ? 'selected' : '' ?>>Yes — Redirect HTTP to HTTPS</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">IP Whitelist (Admin)</label>
                                    <input type="text" class="form-control" name="admin_ip_whitelist"
                                           value="<?= e($settings['admin_ip_whitelist'] ?? '') ?>"
                                           placeholder="192.168.1.0/24, 10.0.0.1">
                                    <div class="form-text">Comma-separated. Blank = allow all.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Enable Audit Logging</label>
                                    <select class="form-select" name="audit_logging_enabled">
                                        <option value="1" <?= ($settings['audit_logging_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                                        <option value="0" <?= ($settings['audit_logging_enabled'] ?? '') === '0' ? 'selected' : '' ?>>Disabled</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Log Retention (days)</label>
                                    <input type="number" class="form-control" name="audit_log_retention"
                                           value="<?= e($settings['audit_log_retention'] ?? 90) ?>"
                                           min="7">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-save me-2"></i>Save Security Settings
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm border-danger border-opacity-25">
                        <div class="card-header bg-danger bg-opacity-10 text-danger fw-semibold">
                            <i class="fas fa-exclamation-triangle me-2"></i>Security Notes
                        </div>
                        <div class="card-body small text-muted">
                            <p><i class="fas fa-info-circle text-danger me-1"></i> Session timeout applies to ALL admin users.</p>
                            <p><i class="fas fa-info-circle text-danger me-1"></i> Two-factor auth requires SMS/Email configured in Communication tab.</p>
                            <p><i class="fas fa-info-circle text-danger me-1"></i> IP whitelist: use CIDR notation for ranges.</p>
                            <p class="mb-0"><i class="fas fa-info-circle text-danger me-1"></i> Audit logs help track unauthorized access attempts.</p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- ── APPEARANCE ────────────────────────────────────────────── -->
    <div class="tab-pane fade <?= ($tab ?? '') === 'appearance' ? 'show active' : '' ?>"
         id="tab-appearance" role="tabpanel">
        <div class="row g-4">
            <!-- Logo Upload -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold">
                        <i class="fas fa-image me-2 text-primary"></i>Branding &amp; Logo
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Main Logo -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Institution Logo</label>
                                <?php $logoPath = $settings['site_logo'] ?? ''; ?>
                                <div class="border rounded p-3 text-center bg-light mb-2" style="min-height:80px">
                                    <?php if ($logoPath && file_exists(BASE_PATH . '/public/' . $logoPath)): ?>
                                        <img src="<?= url($logoPath) ?>" alt="Logo" style="max-height:60px;max-width:180px">
                                    <?php else: ?>
                                        <p class="text-muted small mb-0 mt-2"><i class="fas fa-image fa-2x d-block mb-1"></i>No logo uploaded</p>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="<?= url('settings/upload-logo') ?>" enctype="multipart/form-data">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="logo_type" value="logo">
                                    <div class="input-group">
                                        <input type="file" class="form-control form-control-sm" name="logo_file"
                                               accept="image/png,image/jpeg,image/gif,image/svg+xml">
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Upload</button>
                                    </div>
                                    <div class="form-text">PNG/JPG/SVG, max 2MB. Recommended: 200×60px</div>
                                </form>
                            </div>
                            <!-- Favicon -->
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Favicon</label>
                                <?php $faviconPath = $settings['site_favicon'] ?? ''; ?>
                                <div class="border rounded p-3 text-center bg-light mb-2" style="min-height:80px">
                                    <?php if ($faviconPath && file_exists(BASE_PATH . '/public/' . $faviconPath)): ?>
                                        <img src="<?= url($faviconPath) ?>" alt="Favicon" style="max-height:32px">
                                    <?php else: ?>
                                        <p class="text-muted small mb-0 mt-2"><i class="fas fa-bookmark fa-2x d-block mb-1"></i>No favicon uploaded</p>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="<?= url('settings/upload-logo') ?>" enctype="multipart/form-data">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="logo_type" value="favicon">
                                    <div class="input-group">
                                        <input type="file" class="form-control form-control-sm" name="favicon_file"
                                               accept="image/x-icon,image/png,image/gif">
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Upload</button>
                                    </div>
                                    <div class="form-text">ICO/PNG, 16×16 or 32×32px</div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Color Scheme -->
                <form method="POST" action="<?= url('settings') ?>" class="mt-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="_group" value="appearance">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent fw-semibold">
                            <i class="fas fa-palette me-2 text-info"></i>Color Scheme &amp; Fonts
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Primary Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" name="primary_color"
                                               value="<?= e($settings['primary_color'] ?? '#0d6efd') ?>"
                                               id="primaryColorPicker">
                                        <input type="text" class="form-control form-control-sm font-monospace" id="primaryColorText"
                                               value="<?= e($settings['primary_color'] ?? '#0d6efd') ?>" maxlength="7">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Sidebar Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" name="sidebar_color"
                                               value="<?= e($settings['sidebar_color'] ?? '#1e293b') ?>"
                                               id="sidebarColorPicker">
                                        <input type="text" class="form-control form-control-sm font-monospace" id="sidebarColorText"
                                               value="<?= e($settings['sidebar_color'] ?? '#1e293b') ?>" maxlength="7">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Accent Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" name="accent_color"
                                               value="<?= e($settings['accent_color'] ?? '#10b981') ?>"
                                               id="accentColorPicker">
                                        <input type="text" class="form-control form-control-sm font-monospace" id="accentColorText"
                                               value="<?= e($settings['accent_color'] ?? '#10b981') ?>" maxlength="7">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Default Theme</label>
                                    <select class="form-select" name="default_theme">
                                        <option value="default" <?= ($settings['default_theme'] ?? 'default') === 'default' ? 'selected' : '' ?>>Default (Light Blue)</option>
                                        <option value="dark"    <?= ($settings['default_theme'] ?? '') === 'dark'    ? 'selected' : '' ?>>Dark</option>
                                        <option value="blue"    <?= ($settings['default_theme'] ?? '') === 'blue'    ? 'selected' : '' ?>>Blue</option>
                                        <option value="green"   <?= ($settings['default_theme'] ?? '') === 'green'   ? 'selected' : '' ?>>Green</option>
                                        <option value="rose"    <?= ($settings['default_theme'] ?? '') === 'rose'    ? 'selected' : '' ?>>Rose</option>
                                        <option value="purple"  <?= ($settings['default_theme'] ?? '') === 'purple'  ? 'selected' : '' ?>>Purple</option>
                                    </select>
                                    <div class="form-text">Default for new users. Users can override with the palette button.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Default Font Size</label>
                                    <select class="form-select" name="default_font_size">
                                        <option value="small"   <?= ($settings['default_font_size'] ?? 'default') === 'small'   ? 'selected' : '' ?>>Small</option>
                                        <option value="default" <?= ($settings['default_font_size'] ?? 'default') === 'default' ? 'selected' : '' ?>>Default</option>
                                        <option value="large"   <?= ($settings['default_font_size'] ?? '') === 'large'          ? 'selected' : '' ?>>Large</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Save Appearance Settings
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold"><i class="fas fa-eye me-2"></i>Color Preview</div>
                    <div class="card-body">
                        <div id="colorPreviewBar" class="rounded p-3 mb-3" style="background: <?= e($settings['sidebar_color'] ?? '#1e293b') ?>">
                            <span style="color:#fff;font-weight:600;font-size:13px">Sidebar Preview</span>
                        </div>
                        <button class="btn w-100 mb-2 text-white fw-semibold" id="primaryPreviewBtn"
                                style="background:<?= e($settings['primary_color'] ?? '#0d6efd') ?>">Primary Button</button>
                        <button class="btn w-100 text-white fw-semibold" id="accentPreviewBtn"
                                style="background:<?= e($settings['accent_color'] ?? '#10b981') ?>">Accent Button</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    // ── Sync color pickers ──────────────────────────────────────────
    function syncColor(picker, textInput, previewIds) {
        picker.addEventListener('input', function () {
            textInput.value = this.value;
            previewIds.forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    if (el.style.background !== undefined) el.style.background = picker.value;
                }
            });
        });
        textInput.addEventListener('input', function () {
            if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
                picker.value = this.value;
            }
        });
    }

    var pc = document.getElementById('primaryColorPicker');
    var pt = document.getElementById('primaryColorText');
    var sc = document.getElementById('sidebarColorPicker');
    var st = document.getElementById('sidebarColorText');
    var ac = document.getElementById('accentColorPicker');
    var at = document.getElementById('accentColorText');

    if (pc && pt) syncColor(pc, pt, ['primaryPreviewBtn']);
    if (sc && st) syncColor(sc, st, ['colorPreviewBar']);
    if (ac && at) syncColor(ac, at, ['accentPreviewBtn']);

    // hidden fields to carry color values to form
    if (pc) {
        pc.addEventListener('input', function() {
            // also update the named input (color picker IS the named input here)
        });
    }
})();
</script>
