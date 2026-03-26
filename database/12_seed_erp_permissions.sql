-- ============================================================
-- ERP MODULE PERMISSIONS
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. ATTENDANCE
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Attendance', 'attendance.view', 'attendance'),
('Mark Attendance', 'attendance.mark', 'attendance'),
('Edit Attendance', 'attendance.edit', 'attendance'),
('Attendance Reports', 'attendance.reports', 'attendance');

-- 2. ACADEMICS & TIMETABLE
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Subjects', 'subjects.view', 'academics'),
('Manage Subjects', 'subjects.manage', 'academics'),
('View Timetable', 'timetable.view', 'academics'),
('Manage Timetable', 'timetable.manage', 'academics');

-- 3. EXAMS
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Exams', 'exams.view', 'exams'),
('Manage Exams', 'exams.manage', 'exams'),
('Enter Marks', 'exams.enter_marks', 'exams'),
('Publish Results', 'exams.publish', 'exams');

-- 4. HOSTEL
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Hostels', 'hostel.view', 'hostel'),
('Manage Hostels', 'hostel.manage', 'hostel'),
('Allocate Rooms', 'hostel.allocate', 'hostel');

-- 5. TRANSPORT
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Transport', 'transport.view', 'transport'),
('Manage Transport', 'transport.manage', 'transport'),
('Allocate Transport', 'transport.allocate', 'transport');

-- 6. LIBRARY
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Library', 'library.view', 'library'),
('Manage Books', 'library.manage_books', 'library'),
('Issue/Return Books', 'library.issue_return', 'library');

-- 7. HR & PAYROLL
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Staff', 'staff.view', 'hr'),
('Manage Staff', 'staff.manage', 'hr'),
('Manage Leave', 'staff.leave', 'hr'),
('Process Payroll', 'payroll.process', 'hr'),
('View Payslips', 'payroll.payslips', 'hr');

-- 8. PLACEMENT
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Placements', 'placements.view', 'placement'),
('Manage Placements', 'placements.manage', 'placement');

-- ============================================================
-- ROLE MAPPINGS FOR NEW PERMISSIONS
-- ============================================================

-- Super Admin: All new permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions` WHERE `module` IN ('attendance', 'academics', 'exams', 'hostel', 'transport', 'library', 'hr', 'placement');

-- Org Admin: All except HR and Payroll process
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions` WHERE `module` IN ('attendance', 'academics', 'exams', 'hostel', 'transport', 'library', 'placement')
OR (`module` = 'hr' AND `slug` NOT IN ('payroll.process'));

-- Institution Admin: Most except delicate HR
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions` WHERE `module` IN ('attendance', 'academics', 'exams', 'hostel', 'transport', 'library', 'placement')
OR (`module` = 'hr' AND `slug` IN ('staff.view', 'staff.leave', 'payroll.payslips'));

-- Faculty: Attendance mark/view, Timetable view, Subject view, Exam enter marks
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, id FROM `permissions` WHERE `slug` IN (
  'attendance.view', 'attendance.mark',
  'subjects.view', 'timetable.view',
  'exams.view', 'exams.enter_marks'
);

SET FOREIGN_KEY_CHECKS = 1;
