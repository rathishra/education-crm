-- ============================================================
-- ENTERPRISE RBAC ENHANCEMENTS
-- Per-user permission overrides, role visual properties,
-- and comprehensive permissions for all modules
-- ============================================================

USE `education_crm`;

-- ── Add visual columns to roles ──────────────────────────────
ALTER TABLE `roles`
    ADD COLUMN IF NOT EXISTS `color` VARCHAR(20) NOT NULL DEFAULT 'secondary' AFTER `is_system`,
    ADD COLUMN IF NOT EXISTS `icon`  VARCHAR(50) NOT NULL DEFAULT 'user'      AFTER `color`,
    ADD COLUMN IF NOT EXISTS `permissions_cache` TEXT NULL AFTER `description`;

-- Set colors and icons for default roles
UPDATE `roles` SET `color` = 'danger',    `icon` = 'crown'               WHERE `slug` = 'super_admin';
UPDATE `roles` SET `color` = 'primary',   `icon` = 'building'            WHERE `slug` = 'org_admin';
UPDATE `roles` SET `color` = 'info',      `icon` = 'university'          WHERE `slug` = 'inst_admin';
UPDATE `roles` SET `color` = 'success',   `icon` = 'comments'            WHERE `slug` = 'counselor';
UPDATE `roles` SET `color` = 'warning',   `icon` = 'user-plus'           WHERE `slug` = 'admission_officer';
UPDATE `roles` SET `color` = 'teal',      `icon` = 'file-invoice-dollar' WHERE `slug` = 'finance_officer';
UPDATE `roles` SET `color` = 'purple',    `icon` = 'chalkboard-teacher'  WHERE `slug` = 'faculty';
UPDATE `roles` SET `color` = 'orange',    `icon` = 'concierge-bell'      WHERE `slug` = 'front_desk';
UPDATE `roles` SET `color` = 'secondary', `icon` = 'chart-bar'           WHERE `slug` = 'report_viewer';

-- ── Per-user permission overrides ────────────────────────────
CREATE TABLE IF NOT EXISTS `user_permission_overrides` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `type`          ENUM('grant','deny') NOT NULL DEFAULT 'grant'
                    COMMENT 'grant = give extra; deny = block role permission',
    `reason`        VARCHAR(255) NULL,
    `granted_by`    INT UNSIGNED NULL COMMENT 'Admin who created this override',
    `expires_at`    DATE NULL COMMENT 'NULL = permanent',
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_user_perm` (`user_id`, `permission_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Additional permissions for Fees Module ───────────────────
INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Fee Heads',           'fees.heads.view',         'fees'),
('Manage Fee Heads',         'fees.heads.manage',        'fees'),
('View Fee Structures',      'fees.structures.view',     'fees'),
('Manage Fee Structures',    'fees.structures.manage',   'fees'),
('View Fee Assignments',     'fees.assignment.view',     'fees'),
('Manage Fee Assignments',   'fees.assignment.manage',   'fees'),
('Collect Fees',             'fees.collection.collect',  'fees'),
('View Fee Collection',      'fees.collection.view',     'fees'),
('Cancel Receipt',           'fees.receipts.cancel',     'fees'),
('Manage Concessions',       'fees.concessions.manage',  'fees'),
('Approve Concessions',      'fees.concessions.approve', 'fees'),
('Manage Refunds',           'fees.refunds.manage',      'fees'),
('Approve Refunds',          'fees.refunds.approve',     'fees'),
('View Fee Reports',         'fees.reports.view',        'fees'),
('Export Fee Reports',       'fees.reports.export',      'fees');

-- ── Additional permissions for Academic Module ───────────────
INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Batches',             'batches.view',             'academic'),
('Manage Batches',           'batches.manage',           'academic'),
('View Sections',            'sections.view',            'academic'),
('Manage Sections',          'sections.manage',          'academic'),
('View Classrooms',          'classrooms.view',          'academic'),
('Manage Classrooms',        'classrooms.manage',        'academic'),
('View Periods',             'periods.view',             'academic'),
('Manage Periods',           'periods.manage',           'academic'),
('View Subject Allocation',  'subjectalloc.view',        'academic'),
('Manage Subject Allocation','subjectalloc.manage',      'academic'),
('View Faculty Allocation',  'facultyalloc.view',        'academic'),
('Manage Faculty Allocation','facultyalloc.manage',      'academic'),
('View LMS',                 'lms.view',                 'academic'),
('Manage LMS',               'lms.manage',               'academic'),
('View Assessments',         'assessments.view',         'academic'),
('Manage Assessments',       'assessments.manage',       'academic'),
('View Grading Schemas',     'grading.view',             'academic'),
('Manage Grading Schemas',   'grading.manage',           'academic');

-- ── Additional permissions for Faculty Module ────────────────
INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Faculty',             'faculty.view',             'faculty'),
('Create Faculty',           'faculty.create',           'faculty'),
('Edit Faculty',             'faculty.edit',             'faculty'),
('Delete Faculty',           'faculty.delete',           'faculty'),
('Manage Faculty Leave',     'faculty.leave.manage',     'faculty'),
('Manage Faculty Attendance','faculty.attendance.manage','faculty'),
('View Faculty Performance', 'faculty.performance.view', 'faculty');

-- ── Additional permissions for HR & Payroll ─────────────────
INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Staff',               'staff.view',               'hr'),
('Edit Staff',               'staff.edit',               'hr'),
('View Payroll',             'payroll.view',             'hr'),
('Generate Payroll',         'payroll.generate',         'hr');

-- ── Additional permissions for System ───────────────────────
INSERT IGNORE INTO `permissions` (`name`, `slug`, `module`) VALUES
('Manage Roles',             'roles.manage',             'system'),
('View Roles',               'roles.view',               'system'),
('Manage User Permissions',  'users.manage_permissions', 'system'),
('View Audit Logs',          'audit.view',               'system'),
('Export Audit Logs',        'audit.export',             'system'),
('Manage Settings',          'settings.manage',          'system'),
('View Settings',            'settings.view',            'system');

-- ── Grant new fees + academic permissions to system roles ────
-- Super Admin (role_id=1): grant all new permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`
WHERE slug IN (
  'fees.heads.view','fees.heads.manage','fees.structures.view','fees.structures.manage',
  'fees.assignment.view','fees.assignment.manage','fees.collection.collect','fees.collection.view',
  'fees.receipts.cancel','fees.concessions.manage','fees.concessions.approve',
  'fees.refunds.manage','fees.refunds.approve','fees.reports.view','fees.reports.export',
  'batches.view','batches.manage','sections.view','sections.manage',
  'classrooms.view','classrooms.manage','periods.view','periods.manage',
  'subjectalloc.view','subjectalloc.manage','facultyalloc.view','facultyalloc.manage',
  'lms.view','lms.manage','assessments.view','assessments.manage',
  'grading.view','grading.manage',
  'faculty.view','faculty.create','faculty.edit','faculty.delete',
  'faculty.leave.manage','faculty.attendance.manage','faculty.performance.view',
  'staff.view','staff.edit','payroll.view','payroll.generate',
  'roles.manage','roles.view','users.manage_permissions','audit.view','audit.export',
  'settings.manage','settings.view'
);

-- Institution Admin (role_id=3): grant fees + academic (no payroll/org management)
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions`
WHERE slug IN (
  'fees.heads.view','fees.structures.view','fees.assignment.view','fees.assignment.manage',
  'fees.collection.collect','fees.collection.view','fees.concessions.manage',
  'fees.concessions.approve','fees.refunds.manage','fees.refunds.approve',
  'fees.reports.view','fees.reports.export',
  'batches.view','batches.manage','sections.view','sections.manage',
  'classrooms.view','classrooms.manage','periods.view','periods.manage',
  'subjectalloc.view','subjectalloc.manage','facultyalloc.view','facultyalloc.manage',
  'lms.view','lms.manage','assessments.view','assessments.manage',
  'grading.view','grading.manage',
  'faculty.view','faculty.create','faculty.edit','faculty.leave.manage',
  'faculty.attendance.manage','faculty.performance.view',
  'staff.view','staff.edit','roles.view','settings.view','audit.view'
);

-- Finance Officer (role_id=6): grant all fee permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, id FROM `permissions`
WHERE slug IN (
  'fees.heads.view','fees.structures.view','fees.assignment.view','fees.assignment.manage',
  'fees.collection.collect','fees.collection.view','fees.receipts.cancel',
  'fees.concessions.manage','fees.concessions.approve',
  'fees.refunds.manage','fees.refunds.approve','fees.reports.view','fees.reports.export'
);

-- Faculty (role_id=7): grant academic view + lms + attendance
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, id FROM `permissions`
WHERE slug IN (
  'batches.view','sections.view','classrooms.view','subjectalloc.view',
  'lms.view','lms.manage','assessments.view','assessments.manage',
  'grading.view'
);
