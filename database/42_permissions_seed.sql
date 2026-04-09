-- ============================================================
-- Migration 42: Complete Permissions Seed
-- All modules covered: system, admin, crm, admissions,
-- students, academic, attendance, assessments, fees, faculty,
-- hr, lms, transport, hostel, library, communication,
-- placement, reports, portal
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Clear existing permissions (re-seeding clean)
TRUNCATE TABLE role_permissions;
TRUNCATE TABLE permissions;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. SYSTEM MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Roles',               'roles.view',                'system', 'View role list and details'),
('Manage Roles',             'roles.manage',              'system', 'Create, edit, delete roles and assign permissions'),
('View Users',               'users.view',                'system', 'View user list and profiles'),
('Create User',              'users.create',              'system', 'Create new system users'),
('Edit User',                'users.edit',                'system', 'Edit user details'),
('Delete User',              'users.delete',              'system', 'Delete users'),
('Manage User Permissions',  'users.manage_permissions',  'system', 'Grant/deny per-user permission overrides'),
('Assign User Roles',        'users.assign_roles',        'system', 'Assign or change user roles'),
('View Audit Logs',          'audit.view',                'system', 'View system audit trail'),
('Export Audit Logs',        'audit.export',              'system', 'Export audit logs to CSV'),
('View Settings',            'settings.view',             'system', 'View system and institution settings'),
('Manage Settings',          'settings.manage',           'system', 'Edit system and institution settings');

-- ============================================================
-- 2. ADMIN / ORGANISATION MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Organizations',       'organizations.view',        'admin', 'View organization list'),
('Manage Organizations',     'organizations.manage',      'admin', 'Create and edit organizations'),
('View Institutions',        'institutions.view',         'admin', 'View institution details'),
('Manage Institutions',      'institutions.manage',       'admin', 'Create and edit institutions'),
('View Departments',         'departments.view',          'admin', 'View departments'),
('Manage Departments',       'departments.manage',        'admin', 'Create and edit departments'),
('View Campuses',            'campuses.view',             'admin', 'View campus list'),
('Manage Campuses',          'campuses.manage',           'admin', 'Create and edit campuses');

-- ============================================================
-- 3. CRM MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View CRM Dashboard',       'crm.dashboard',             'crm', 'View CRM dashboard and KPIs'),
('View Leads',               'leads.view',                'crm', 'View lead list and profiles'),
('Create Lead',              'leads.create',              'crm', 'Add new leads'),
('Edit Lead',                'leads.edit',                'crm', 'Edit lead details'),
('Delete Lead',              'leads.delete',              'crm', 'Delete leads'),
('Convert Lead',             'leads.convert',             'crm', 'Convert lead to admission'),
('Import Leads',             'leads.import',              'crm', 'Bulk import leads via CSV'),
('Export Leads',             'leads.export',              'crm', 'Export leads to CSV'),
('View Enquiries',           'enquiries.view',            'crm', 'View enquiry list'),
('Create Enquiry',           'enquiries.create',          'crm', 'Add new enquiries'),
('Edit Enquiry',             'enquiries.edit',            'crm', 'Edit enquiry details'),
('Delete Enquiry',           'enquiries.delete',          'crm', 'Delete enquiries'),
('Convert Enquiry',          'enquiries.convert',         'crm', 'Convert enquiry to admission'),
('View Follow-ups',          'followups.view',            'crm', 'View follow-up tasks'),
('Create Follow-up',         'followups.create',          'crm', 'Add follow-up tasks'),
('Edit Follow-up',           'followups.edit',            'crm', 'Edit follow-up tasks'),
('Delete Follow-up',         'followups.delete',          'crm', 'Delete follow-ups'),
('Manage Lead Sources',      'lead_sources.manage',       'crm', 'Create and edit lead sources'),
('Manage Lead Statuses',     'lead_statuses.manage',      'crm', 'Create and edit pipeline statuses');

-- ============================================================
-- 4. ADMISSIONS MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Admissions',          'admissions.view',           'admissions', 'View admission applications'),
('Create Admission',         'admissions.create',         'admissions', 'Create new admission applications'),
('Edit Admission',           'admissions.edit',           'admissions', 'Edit admission details'),
('Delete Admission',         'admissions.delete',         'admissions', 'Delete admission records'),
('Approve Admission',        'admissions.approve',        'admissions', 'Approve or reject admissions'),
('Enroll Student',           'admissions.enroll',         'admissions', 'Convert confirmed admission to student'),
('View Admission Documents', 'admissions.documents.view', 'admissions', 'View uploaded documents'),
('Manage Admission Documents','admissions.documents.manage','admissions','Upload and verify documents'),
('Record Admission Payment', 'admissions.payment',        'admissions', 'Record admission payments');

-- ============================================================
-- 5. STUDENTS MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Students',            'students.view',             'students', 'View student profiles'),
('Create Student',           'students.create',           'students', 'Manually create student records'),
('Edit Student',             'students.edit',             'students', 'Edit student profiles'),
('Delete Student',           'students.delete',           'students', 'Delete student records'),
('View Student Documents',   'students.documents.view',   'students', 'View student documents'),
('Manage Student Documents', 'students.documents.manage', 'students', 'Upload student documents'),
('Manage Portal Access',     'portal.manage',             'students', 'Enable/disable student portal access'),
('View Student Activities',  'students.activities',       'students', 'View student activity log');

-- ============================================================
-- 6. ACADEMIC MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Academic Years',      'academic_years.view',       'academic', 'View academic year list'),
('Manage Academic Years',    'academic_years.manage',     'academic', 'Create and edit academic years'),
('View Courses',             'courses.view',              'academic', 'View course catalogue'),
('Manage Courses',           'courses.manage',            'academic', 'Create and edit courses'),
('View Subjects',            'subjects.view',             'academic', 'View subjects'),
('Manage Subjects',          'subjects.manage',           'academic', 'Create and edit subjects'),
('View Batches',             'batches.view',              'academic', 'View batch list'),
('Manage Batches',           'batches.manage',            'academic', 'Create and edit batches'),
('View Sections',            'sections.view',             'academic', 'View sections'),
('Manage Sections',          'sections.manage',           'academic', 'Create and edit sections'),
('Manage Section Enrollment','sections.enroll',           'academic', 'Add/remove students from sections'),
('Allocate Faculty',         'faculty.allocate',          'academic', 'Assign faculty to batches/subjects'),
('View Timetable',           'timetable.view',            'academic', 'View class timetable'),
('Manage Timetable',         'timetable.manage',          'academic', 'Create and edit timetable'),
('View Classrooms',          'classrooms.view',           'academic', 'View classroom list'),
('Manage Classrooms',        'classrooms.manage',         'academic', 'Create and edit classrooms');

-- ============================================================
-- 7. ATTENDANCE MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Attendance',          'attendance.view',           'attendance', 'View attendance records'),
('Mark Attendance',          'attendance.mark',           'attendance', 'Mark student attendance'),
('Edit Attendance',          'attendance.edit',           'attendance', 'Edit/correct attendance'),
('Attendance Reports',       'attendance.reports',        'attendance', 'View and export attendance reports'),
('Bulk Attendance',          'attendance.bulk',           'attendance', 'Mark bulk attendance');

-- ============================================================
-- 8. ASSESSMENTS & EXAMS MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Assessments',         'assessments.view',          'assessments', 'View assessments and results'),
('Manage Assessments',       'assessments.manage',        'assessments', 'Create and edit assessments'),
('Enter Marks',              'assessments.enter_marks',   'assessments', 'Enter student marks'),
('Publish Results',          'assessments.publish',       'assessments', 'Publish assessment results'),
('View Exams',               'exams.view',                'assessments', 'View exam schedule'),
('Manage Exams',             'exams.manage',              'assessments', 'Create and edit exams'),
('Enter Exam Marks',         'exams.enter_marks',         'assessments', 'Enter exam marks'),
('Publish Exam Results',     'exams.publish',             'assessments', 'Publish exam results'),
('Manage Grading Schemas',   'grading.manage',            'assessments', 'Create and edit grading schemas');

-- ============================================================
-- 9. FEES MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Fee Heads',           'fees.heads.view',           'fees', 'View fee head list'),
('Manage Fee Heads',         'fees.heads.manage',         'fees', 'Create and edit fee heads'),
('View Fee Structures',      'fees.structures.view',      'fees', 'View fee structure list'),
('Manage Fee Structures',    'fees.structures.manage',    'fees', 'Create and edit fee structures'),
('Assign Fees to Students',  'fees.assign',               'fees', 'Assign fee structures to students'),
('View Fee Collection',      'fees.collection.view',      'fees', 'View payment and collection records'),
('Collect Fees',             'fees.collect',              'fees', 'Record fee payments'),
('View Receipts',            'fees.receipts.view',        'fees', 'View and print fee receipts'),
('Issue Refund',             'fees.refund',               'fees', 'Process fee refunds'),
('Apply Concession',         'fees.concession',           'fees', 'Apply fee concessions to students'),
('Waive Fees',               'fees.waive',                'fees', 'Waive student fees'),
('Fee Reports',              'fees.reports',              'fees', 'View and export fee reports'),
('Manage Installment Plans', 'fees.installments.manage',  'fees', 'Create and edit installment plans'),
('Manage Fine Rules',        'fees.fines.manage',         'fees', 'Create and edit fine/penalty rules');

-- ============================================================
-- 10. FACULTY MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Faculty',             'faculty.view',              'faculty', 'View faculty profiles'),
('Create Faculty',           'faculty.create',            'faculty', 'Add new faculty members'),
('Edit Faculty',             'faculty.edit',              'faculty', 'Edit faculty profiles'),
('Delete Faculty',           'faculty.delete',            'faculty', 'Delete faculty records'),
('Faculty Leave',            'faculty.leave',             'faculty', 'Manage faculty leave requests'),
('Faculty Performance',      'faculty.performance',       'faculty', 'View faculty performance metrics');

-- ============================================================
-- 11. HR & PAYROLL MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Staff',               'staff.view',                'hr', 'View staff list'),
('Manage Staff',             'staff.manage',              'hr', 'Create and edit staff records'),
('Staff Leave',              'staff.leave',               'hr', 'Manage staff leave requests'),
('View Payroll',             'payroll.view',              'hr', 'View payroll records'),
('Process Payroll',          'payroll.process',           'hr', 'Process and finalize payroll'),
('View Payslips',            'payroll.payslips',          'hr', 'View and download payslips');

-- ============================================================
-- 12. LMS MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View LMS Dashboard',       'lms.dashboard',             'lms', 'View LMS overview and stats'),
('View LMS Courses',         'lms.courses.view',          'lms', 'View LMS course catalogue'),
('Create LMS Course',        'lms.courses.create',        'lms', 'Create new LMS courses'),
('Edit LMS Course',          'lms.courses.edit',          'lms', 'Edit LMS course content'),
('Delete LMS Course',        'lms.courses.delete',        'lms', 'Delete LMS courses'),
('Manage LMS Enrollments',   'lms.enrollments.manage',    'lms', 'Enroll/remove students from LMS courses'),
('View LMS Students',        'lms.students.view',         'lms', 'View enrolled students per course'),
('Manage Assignments',       'lms.assignments.manage',    'lms', 'Create and grade assignments'),
('Manage Quizzes',           'lms.quizzes.manage',        'lms', 'Create and grade quizzes'),
('View Gradebook',           'lms.gradebook.view',        'lms', 'View LMS gradebook'),
('Edit Gradebook',           'lms.gradebook.edit',        'lms', 'Edit grades in LMS gradebook'),
('Sync Grades to Academic',  'lms.gradebook.sync',        'lms', 'Sync LMS grades to academic module'),
('Manage Live Classes',      'lms.live.manage',           'lms', 'Schedule and manage live sessions'),
('LMS Forum Moderate',       'lms.forum.moderate',        'lms', 'Moderate discussion forum posts'),
('LMS Attendance',           'lms.attendance.mark',       'lms', 'Mark LMS session attendance'),
('LMS Analytics',            'lms.analytics',             'lms', 'View LMS analytics and reports'),
('Manage LMS Announcements', 'lms.announcements.manage',  'lms', 'Post announcements to LMS courses');

-- ============================================================
-- 13. TRANSPORT MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Transport',           'transport.view',            'transport', 'View routes and stops'),
('Manage Transport',         'transport.manage',          'transport', 'Create and edit routes/stops'),
('Allocate Transport',       'transport.allocate',        'transport', 'Assign students to routes');

-- ============================================================
-- 14. HOSTEL MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Hostel',              'hostel.view',               'hostel', 'View hostel rooms and blocks'),
('Manage Hostel',            'hostel.manage',             'hostel', 'Create and edit hostel rooms'),
('Allocate Hostel',          'hostel.allocate',           'hostel', 'Assign students to hostel rooms');

-- ============================================================
-- 15. LIBRARY MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Library',             'library.view',              'library', 'View books and catalogue'),
('Manage Books',             'library.manage_books',      'library', 'Add and edit library books'),
('Issue / Return Books',     'library.issue_return',      'library', 'Issue and return books to students');

-- ============================================================
-- 16. COMMUNICATION MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Communications',      'communication.view',        'communication', 'View sent communications'),
('Send Notification',        'communication.notify',      'communication', 'Send notifications to users'),
('Bulk Campaign',            'communication.campaign',    'communication', 'Create and send bulk campaigns'),
('Manage Templates',         'communication.templates',   'communication', 'Create and edit message templates'),
('Manage Tasks',             'tasks.manage',              'communication', 'Create and assign tasks');

-- ============================================================
-- 17. PLACEMENT MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Placements',          'placements.view',           'placement', 'View placement drives and companies'),
('Manage Placements',        'placements.manage',         'placement', 'Create and edit placement drives'),
('Manage Applications',      'placements.applications',   'placement', 'Manage student placement applications');

-- ============================================================
-- 18. REPORTS MODULE
-- ============================================================
INSERT INTO permissions (name, slug, module, description) VALUES
('View Reports Dashboard',   'reports.view',              'reports', 'Access reports section'),
('Admission Reports',        'reports.admissions',        'reports', 'View and export admission reports'),
('Student Reports',          'reports.students',          'reports', 'View and export student reports'),
('Fee Reports',              'reports.fees',              'reports', 'View and export fee reports'),
('Attendance Reports',       'reports.attendance',        'reports', 'View and export attendance reports'),
('Academic Reports',         'reports.academic',          'reports', 'View and export academic reports'),
('CRM Reports',              'reports.crm',               'reports', 'View and export CRM reports'),
('HR Reports',               'reports.hr',                'reports', 'View and export HR reports'),
('Export Any Report',        'reports.export',            'reports', 'Export any report to CSV/Excel');

-- ============================================================
-- ASSIGN PERMISSIONS TO ROLES
-- ============================================================

-- Helper: get role IDs
SET @super_admin   = (SELECT id FROM roles WHERE slug = 'super_admin'       LIMIT 1);
SET @inst_admin    = (SELECT id FROM roles WHERE slug = 'institution_admin'  LIMIT 1);
SET @principal     = (SELECT id FROM roles WHERE slug = 'principal'          LIMIT 1);
SET @hod           = (SELECT id FROM roles WHERE slug = 'hod'                LIMIT 1);
SET @faculty       = (SELECT id FROM roles WHERE slug = 'faculty'            LIMIT 1);
SET @counselor     = (SELECT id FROM roles WHERE slug = 'counselor'          LIMIT 1);
SET @accountant    = (SELECT id FROM roles WHERE slug = 'accountant'         LIMIT 1);
SET @receptionist  = (SELECT id FROM roles WHERE slug = 'receptionist'       LIMIT 1);

-- ── SUPER ADMIN — all permissions ────────────────────────────
-- (super_admin bypasses permission checks in code, but seed anyway for completeness)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @super_admin, id FROM permissions WHERE @super_admin IS NOT NULL;

-- ── INSTITUTION ADMIN — everything except payroll.process ────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @inst_admin, id FROM permissions
WHERE @inst_admin IS NOT NULL
  AND slug NOT IN ('organizations.manage','payroll.process','audit.export','roles.manage');

-- ── PRINCIPAL — academic + students + reports + attendance ───
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @principal, id FROM permissions
WHERE @principal IS NOT NULL
  AND module IN ('academic','attendance','assessments','students','reports','admissions','communication','lms','fees')
  AND slug NOT IN ('students.delete','admissions.delete','fees.refund','fees.waive','fees.structures.manage');

-- ── HOD — academic + attendance + faculty + lms ──────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @hod, id FROM permissions
WHERE @hod IS NOT NULL
  AND module IN ('academic','attendance','assessments','faculty','lms','reports')
  AND slug NOT IN ('academic_years.manage','courses.manage','grading.manage','reports.fees','reports.hr');

-- ── FACULTY — own teaching scope ─────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @faculty, id FROM permissions
WHERE @faculty IS NOT NULL
  AND slug IN (
    'students.view',
    'attendance.view','attendance.mark','attendance.bulk',
    'assessments.view','assessments.enter_marks',
    'exams.view','exams.enter_marks',
    'timetable.view',
    'subjects.view','batches.view','sections.view',
    'lms.dashboard','lms.courses.view','lms.courses.create','lms.courses.edit',
    'lms.assignments.manage','lms.quizzes.manage',
    'lms.gradebook.view','lms.gradebook.edit',
    'lms.live.manage','lms.forum.moderate',
    'lms.attendance.mark','lms.announcements.manage',
    'communication.notify'
  );

-- ── COUNSELOR — CRM + admissions ─────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @counselor, id FROM permissions
WHERE @counselor IS NOT NULL
  AND slug IN (
    'crm.dashboard',
    'leads.view','leads.create','leads.edit','leads.convert','leads.import','leads.export',
    'enquiries.view','enquiries.create','enquiries.edit','enquiries.convert',
    'followups.view','followups.create','followups.edit','followups.delete',
    'admissions.view','admissions.create','admissions.edit','admissions.approve',
    'admissions.documents.view','admissions.documents.manage','admissions.payment',
    'students.view',
    'communication.notify','communication.view',
    'reports.crm','reports.admissions'
  );

-- ── ACCOUNTANT — fees + reports ──────────────────────────────
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @accountant, id FROM permissions
WHERE @accountant IS NOT NULL
  AND slug IN (
    'students.view',
    'fees.heads.view','fees.heads.manage',
    'fees.structures.view','fees.structures.manage',
    'fees.assign','fees.collection.view','fees.collect',
    'fees.receipts.view','fees.refund','fees.concession','fees.waive',
    'fees.reports','fees.installments.manage','fees.fines.manage',
    'reports.fees','reports.students',
    'communication.notify'
  );

-- ── RECEPTIONIST — front desk: leads + enquiries + basic view ──
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @receptionist, id FROM permissions
WHERE @receptionist IS NOT NULL
  AND slug IN (
    'leads.view','leads.create','leads.edit',
    'enquiries.view','enquiries.create','enquiries.edit',
    'followups.view','followups.create',
    'admissions.view','admissions.documents.view',
    'students.view',
    'communication.notify','communication.view',
    'transport.view','hostel.view'
  );

-- ============================================================
-- DONE ✓  Run this after 99_truncate_all_data.sql or on a fresh DB
-- ============================================================
