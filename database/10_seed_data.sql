-- ============================================================
-- MULTI-INSTITUTION MEDU MATRIX - SEED DATA
-- Default roles, permissions, lead statuses, sources, and admin user
-- ============================================================

USE `education_crm`;

-- ============================================================
-- DEFAULT ROLES
-- ============================================================
INSERT INTO `roles` (`name`, `slug`, `description`, `is_system`, `level`) VALUES
('Super Admin', 'super_admin', 'Full system access across all organizations', 1, 0),
('Organization Admin', 'org_admin', 'Manages all institutions under an organization', 1, 1),
('Institution Admin', 'inst_admin', 'Full access within a single institution', 1, 2),
('Counselor', 'counselor', 'Manages leads and enquiries', 1, 3),
('Admission Officer', 'admission_officer', 'Handles admissions and documents', 1, 3),
('Finance Officer', 'finance_officer', 'Manages fees, payments, and financial reports', 1, 3),
('Faculty', 'faculty', 'Teaching staff with limited access', 1, 3),
('Front Desk', 'front_desk', 'Handles enquiries and walk-ins', 1, 3),
('Report Viewer', 'report_viewer', 'View-only access to reports and dashboards', 1, 3);

-- ============================================================
-- PERMISSIONS
-- ============================================================
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
-- Dashboard
('View Dashboard', 'dashboard.view', 'dashboard'),

-- Organization
('View Organizations', 'organizations.view', 'organizations'),
('Create Organization', 'organizations.create', 'organizations'),
('Edit Organization', 'organizations.edit', 'organizations'),
('Delete Organization', 'organizations.delete', 'organizations'),

-- Institution
('View Institutions', 'institutions.view', 'institutions'),
('Create Institution', 'institutions.create', 'institutions'),
('Edit Institution', 'institutions.edit', 'institutions'),
('Delete Institution', 'institutions.delete', 'institutions'),

-- Department
('View Departments', 'departments.view', 'departments'),
('Create Department', 'departments.create', 'departments'),
('Edit Department', 'departments.edit', 'departments'),
('Delete Department', 'departments.delete', 'departments'),

-- Users
('View Users', 'users.view', 'users'),
('Create User', 'users.create', 'users'),
('Edit User', 'users.edit', 'users'),
('Delete User', 'users.delete', 'users'),
('Manage Roles', 'users.manage_roles', 'users'),

-- Leads
('View Leads', 'leads.view', 'leads'),
('Create Lead', 'leads.create', 'leads'),
('Edit Lead', 'leads.edit', 'leads'),
('Delete Lead', 'leads.delete', 'leads'),
('Assign Lead', 'leads.assign', 'leads'),
('Import Leads', 'leads.import', 'leads'),
('Export Leads', 'leads.export', 'leads'),
('View All Leads', 'leads.view_all', 'leads'),

-- Enquiries
('View Enquiries', 'enquiries.view', 'enquiries'),
('Create Enquiry', 'enquiries.create', 'enquiries'),
('Edit Enquiry', 'enquiries.edit', 'enquiries'),
('Delete Enquiry', 'enquiries.delete', 'enquiries'),
('Convert Enquiry', 'enquiries.convert', 'enquiries'),

-- Follow-ups
('View Follow-ups', 'followups.view', 'followups'),
('Create Follow-up', 'followups.create', 'followups'),
('Edit Follow-up', 'followups.edit', 'followups'),
('Delete Follow-up', 'followups.delete', 'followups'),

-- Tasks
('View Tasks', 'tasks.view', 'tasks'),
('Create Task', 'tasks.create', 'tasks'),
('Edit Task', 'tasks.edit', 'tasks'),
('Delete Task', 'tasks.delete', 'tasks'),

-- Admissions
('View Admissions', 'admissions.view', 'admissions'),
('Create Admission', 'admissions.create', 'admissions'),
('Edit Admission', 'admissions.edit', 'admissions'),
('Delete Admission', 'admissions.delete', 'admissions'),
('Approve Admission', 'admissions.approve', 'admissions'),

-- Students
('View Students', 'students.view', 'students'),
('Create Student', 'students.create', 'students'),
('Edit Student', 'students.edit', 'students'),
('Delete Student', 'students.delete', 'students'),
('Export Students', 'students.export', 'students'),

-- Courses
('View Courses', 'courses.view', 'courses'),
('Create Course', 'courses.create', 'courses'),
('Edit Course', 'courses.edit', 'courses'),
('Delete Course', 'courses.delete', 'courses'),

-- Batches
('View Batches', 'batches.view', 'batches'),
('Create Batch', 'batches.create', 'batches'),
('Edit Batch', 'batches.edit', 'batches'),
('Delete Batch', 'batches.delete', 'batches'),

-- Fees
('View Fees', 'fees.view', 'fees'),
('Create Fee Structure', 'fees.create', 'fees'),
('Edit Fee Structure', 'fees.edit', 'fees'),
('Delete Fee Structure', 'fees.delete', 'fees'),
('Collect Payment', 'fees.collect_payment', 'fees'),
('View Payments', 'fees.view_payments', 'fees'),
('Generate Receipt', 'fees.generate_receipt', 'fees'),
('Manage Discounts', 'fees.manage_discounts', 'fees'),

-- Communication
('Send Email', 'communication.send_email', 'communication'),
('Send SMS', 'communication.send_sms', 'communication'),
('Send WhatsApp', 'communication.send_whatsapp', 'communication'),
('Manage Templates', 'communication.manage_templates', 'communication'),
('Bulk Messaging', 'communication.bulk_messaging', 'communication'),

-- Reports
('View Reports', 'reports.view', 'reports'),
('Export Reports', 'reports.export', 'reports'),
('View Financial Reports', 'reports.financial', 'reports'),

-- Settings
('Manage Settings', 'settings.manage', 'settings'),
('Manage Communication Settings', 'settings.communication', 'settings'),

-- Documents
('View Documents', 'documents.view', 'documents'),
('Upload Documents', 'documents.upload', 'documents'),
('Delete Documents', 'documents.delete', 'documents'),
('Verify Documents', 'documents.verify', 'documents'),

-- Audit
('View Audit Logs', 'audit.view', 'audit');

-- ============================================================
-- ROLE-PERMISSION MAPPINGS
-- Super Admin gets all permissions
-- ============================================================
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

-- Org Admin gets everything except org management and audit
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions`
WHERE `module` NOT IN ('organizations')
  AND `slug` NOT IN ('audit.view');

-- Institution Admin gets institution-level permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions`
WHERE `module` NOT IN ('organizations')
  AND `slug` NOT IN ('institutions.create', 'institutions.delete', 'audit.view', 'settings.manage');

-- Counselor permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, id FROM `permissions`
WHERE `slug` IN (
  'dashboard.view',
  'leads.view', 'leads.create', 'leads.edit', 'leads.export',
  'enquiries.view', 'enquiries.create', 'enquiries.edit', 'enquiries.convert',
  'followups.view', 'followups.create', 'followups.edit',
  'tasks.view', 'tasks.create', 'tasks.edit',
  'courses.view', 'batches.view',
  'communication.send_email', 'communication.send_sms', 'communication.send_whatsapp',
  'reports.view'
);

-- Admission Officer permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 5, id FROM `permissions`
WHERE `slug` IN (
  'dashboard.view',
  'leads.view',
  'admissions.view', 'admissions.create', 'admissions.edit', 'admissions.approve',
  'students.view', 'students.create', 'students.edit',
  'courses.view', 'batches.view',
  'documents.view', 'documents.upload', 'documents.verify',
  'communication.send_email', 'communication.send_sms',
  'reports.view'
);

-- Finance Officer permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, id FROM `permissions`
WHERE `slug` IN (
  'dashboard.view',
  'students.view',
  'fees.view', 'fees.create', 'fees.edit', 'fees.collect_payment', 'fees.view_payments',
  'fees.generate_receipt', 'fees.manage_discounts',
  'reports.view', 'reports.export', 'reports.financial'
);

-- Faculty permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, id FROM `permissions`
WHERE `slug` IN (
  'dashboard.view',
  'students.view',
  'courses.view', 'batches.view'
);

-- Front Desk permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 8, id FROM `permissions`
WHERE `slug` IN (
  'dashboard.view',
  'enquiries.view', 'enquiries.create', 'enquiries.edit',
  'leads.view', 'leads.create',
  'followups.view', 'followups.create',
  'courses.view'
);

-- Report Viewer permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 9, id FROM `permissions`
WHERE `slug` IN (
  'dashboard.view',
  'reports.view', 'reports.export', 'reports.financial',
  'leads.view', 'students.view', 'fees.view', 'fees.view_payments'
);

-- ============================================================
-- LEAD SOURCES
-- ============================================================
INSERT INTO `lead_sources` (`name`, `slug`) VALUES
('Website', 'website'),
('Walk-in', 'walk_in'),
('Phone Call', 'phone_call'),
('Email', 'email'),
('Social Media - Facebook', 'facebook'),
('Social Media - Instagram', 'instagram'),
('Google Ads', 'google_ads'),
('Newspaper Ad', 'newspaper'),
('Referral', 'referral'),
('Education Fair', 'education_fair'),
('Alumni', 'alumni'),
('Agent/Consultant', 'agent'),
('WhatsApp', 'whatsapp'),
('SMS Campaign', 'sms_campaign'),
('Other', 'other');

-- ============================================================
-- LEAD STATUSES (Pipeline)
-- ============================================================
INSERT INTO `lead_statuses` (`name`, `slug`, `color`, `sort_order`, `is_default`, `is_won`, `is_lost`) VALUES
('New', 'new', '#0d6efd', 1, 1, 0, 0),
('Contacted', 'contacted', '#6f42c1', 2, 0, 0, 0),
('Interested', 'interested', '#fd7e14', 3, 0, 0, 0),
('Campus Visit', 'campus_visit', '#20c997', 4, 0, 0, 0),
('Application Submitted', 'application_submitted', '#0dcaf0', 5, 0, 0, 0),
('Converted', 'converted', '#198754', 6, 0, 1, 0),
('Not Interested', 'not_interested', '#dc3545', 7, 0, 0, 1),
('Lost', 'lost', '#6c757d', 8, 0, 0, 1);

-- ============================================================
-- DEFAULT SUPER ADMIN USER
-- Password: Admin@123 (bcrypt hash)
-- ============================================================
INSERT INTO `users` (`employee_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `email_verified_at`, `is_active`)
VALUES ('EMP001', 'Super', 'Admin', 'admin@educrm.com', '9999999999',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), 1);

INSERT INTO `user_roles` (`user_id`, `role_id`, `organization_id`, `institution_id`)
VALUES (1, 1, NULL, NULL);

-- ============================================================
-- DEFAULT SETTINGS
-- ============================================================
INSERT INTO `settings` (`institution_id`, `group`, `key`, `value`, `type`) VALUES
(NULL, 'general', 'app_name', 'Edu Matrix', 'string'),
(NULL, 'general', 'app_logo', '/assets/images/logo.png', 'string'),
(NULL, 'general', 'date_format', 'd-m-Y', 'string'),
(NULL, 'general', 'time_format', 'h:i A', 'string'),
(NULL, 'general', 'timezone', 'Asia/Kolkata', 'string'),
(NULL, 'general', 'currency', 'INR', 'string'),
(NULL, 'general', 'currency_symbol', '₹', 'string'),
(NULL, 'lead', 'auto_assign', '0', 'boolean'),
(NULL, 'lead', 'duplicate_check_fields', '["phone","email"]', 'json'),
(NULL, 'lead', 'lead_number_prefix', 'LD', 'string'),
(NULL, 'fee', 'receipt_prefix', 'RCP', 'string'),
(NULL, 'fee', 'enable_online_payment', '0', 'boolean'),
(NULL, 'admission', 'admission_number_prefix', 'ADM', 'string'),
(NULL, 'enquiry', 'enquiry_number_prefix', 'ENQ', 'string');
