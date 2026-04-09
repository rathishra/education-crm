-- ============================================================
-- TRUNCATE ALL DATA — Fresh Product Reset
-- Safe version: skips tables that don't exist yet
-- Run this in phpMyAdmin on the education_crm database.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Helper procedure to truncate only if table exists
DROP PROCEDURE IF EXISTS safe_truncate;
DELIMITER $$
CREATE PROCEDURE safe_truncate(IN tbl VARCHAR(128))
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = tbl
    ) THEN
        SET @sql = CONCAT('TRUNCATE TABLE `', tbl, '`');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- ── LMS ──────────────────────────────────────────────────────
CALL safe_truncate('lms_academic_sync_log');
CALL safe_truncate('lms_activity_feed');
CALL safe_truncate('lms_analytics_daily');
CALL safe_truncate('lms_announcement_dismissals');
CALL safe_truncate('lms_announcements');
CALL safe_truncate('lms_assignment_submissions');
CALL safe_truncate('lms_assignments');
CALL safe_truncate('lms_attendance_records');
CALL safe_truncate('lms_attendance_sessions');
CALL safe_truncate('lms_audit_log');
CALL safe_truncate('lms_categories');
CALL safe_truncate('lms_computed_grades');
CALL safe_truncate('lms_course_reviews');
CALL safe_truncate('lms_course_sections');
CALL safe_truncate('lms_course_tags');
CALL safe_truncate('lms_courses');
CALL safe_truncate('lms_dashboard_prefs');
CALL safe_truncate('lms_deadlines');
CALL safe_truncate('lms_enrollments');
CALL safe_truncate('lms_forum_categories');
CALL safe_truncate('lms_forum_posts');
CALL safe_truncate('lms_forum_reactions');
CALL safe_truncate('lms_forum_subscriptions');
CALL safe_truncate('lms_forum_threads');
CALL safe_truncate('lms_grade_overrides');
CALL safe_truncate('lms_grade_weights');
CALL safe_truncate('lms_lesson_progress');
CALL safe_truncate('lms_lesson_views');
CALL safe_truncate('lms_lessons');
CALL safe_truncate('lms_live_classes');
CALL safe_truncate('lms_live_registrations');
CALL safe_truncate('lms_materials');
CALL safe_truncate('lms_notifications');
CALL safe_truncate('lms_permissions');
CALL safe_truncate('lms_quiz_answers');
CALL safe_truncate('lms_quiz_attempts');
CALL safe_truncate('lms_quiz_options');
CALL safe_truncate('lms_quiz_questions');
CALL safe_truncate('lms_quizzes');
CALL safe_truncate('lms_role_permissions');
CALL safe_truncate('lms_sessions');
CALL safe_truncate('lms_tags');
CALL safe_truncate('lms_user_permissions');
CALL safe_truncate('lms_users');

-- ── Academic ─────────────────────────────────────────────────
CALL safe_truncate('academic_assessment_marks');
CALL safe_truncate('academic_assessments');
CALL safe_truncate('academic_attendance_records');
CALL safe_truncate('academic_attendance_sessions');
CALL safe_truncate('academic_batches');
CALL safe_truncate('academic_sections');
CALL safe_truncate('academic_timetable');
CALL safe_truncate('academic_timetable_periods');
CALL safe_truncate('academic_years');
CALL safe_truncate('assessment_configs');
CALL safe_truncate('assessment_marks');
CALL safe_truncate('attendance_records');
CALL safe_truncate('attendance_sessions');
CALL safe_truncate('attendances');
CALL safe_truncate('batch_faculty');
CALL safe_truncate('batch_subjects');
CALL safe_truncate('batches');
CALL safe_truncate('classrooms');
CALL safe_truncate('exam_marks');
CALL safe_truncate('exam_schedules');
CALL safe_truncate('exams');
CALL safe_truncate('faculty_profiles');
CALL safe_truncate('faculty_subject_allocations');
CALL safe_truncate('grading_grade_rules');
CALL safe_truncate('grading_mark_components');
CALL safe_truncate('grading_schema_categories');
CALL safe_truncate('grading_schemas');
CALL safe_truncate('grading_sub_components');
CALL safe_truncate('sections');
CALL safe_truncate('student_section_enrollments');
CALL safe_truncate('subjects');
CALL safe_truncate('timetable');
CALL safe_truncate('timetable_periods');
CALL safe_truncate('timetables');

-- ── Admissions & Students ────────────────────────────────────
CALL safe_truncate('admission_documents');
CALL safe_truncate('admission_payments');
CALL safe_truncate('admission_timeline');
CALL safe_truncate('admissions');
CALL safe_truncate('placement_applications');
CALL safe_truncate('placement_companies');
CALL safe_truncate('placement_drives');
CALL safe_truncate('student_activities');
CALL safe_truncate('student_documents');
CALL safe_truncate('student_fees');
CALL safe_truncate('student_installments');
CALL safe_truncate('students');

-- ── CRM ──────────────────────────────────────────────────────
CALL safe_truncate('enquiries');
CALL safe_truncate('followups');
CALL safe_truncate('lead_activities');
CALL safe_truncate('lead_followups');
CALL safe_truncate('leads');

-- ── Fees ─────────────────────────────────────────────────────
CALL safe_truncate('fee_components');
CALL safe_truncate('fee_concessions');
CALL safe_truncate('fee_fine_rules');
CALL safe_truncate('fee_heads');
CALL safe_truncate('fee_installments');
CALL safe_truncate('fee_receipt_items');
CALL safe_truncate('fee_receipts');
CALL safe_truncate('fee_refunds');
CALL safe_truncate('fee_structure_details');
CALL safe_truncate('fee_structures');
CALL safe_truncate('fee_student_assignments');
CALL safe_truncate('installment_plans');
CALL safe_truncate('payments');
CALL safe_truncate('payslips');

-- ── Transport ────────────────────────────────────────────────
CALL safe_truncate('transport_allocations');
CALL safe_truncate('transport_stops');
CALL safe_truncate('transport_routes');

-- ── Hostel ───────────────────────────────────────────────────
CALL safe_truncate('hostel_allocations');
CALL safe_truncate('hostel_rooms');
CALL safe_truncate('hostels');

-- ── Library ──────────────────────────────────────────────────
CALL safe_truncate('library_issues');
CALL safe_truncate('library_books');

-- ── Communication ────────────────────────────────────────────
CALL safe_truncate('bulk_campaigns');
CALL safe_truncate('communications');
CALL safe_truncate('notifications');
CALL safe_truncate('tasks');

-- ── HR / Staff ───────────────────────────────────────────────
CALL safe_truncate('staff_leave_requests');
CALL safe_truncate('staff_profiles');

-- ── Documents / Uploads ──────────────────────────────────────
CALL safe_truncate('documents');
CALL safe_truncate('uploads');

-- ── Auth & Users ─────────────────────────────────────────────
CALL safe_truncate('audit_logs');
CALL safe_truncate('password_resets');
CALL safe_truncate('user_sessions');
CALL safe_truncate('user_permission_overrides');
CALL safe_truncate('user_roles');
CALL safe_truncate('users');

-- ── Organisation masters ─────────────────────────────────────
CALL safe_truncate('campuses');
CALL safe_truncate('courses');
CALL safe_truncate('departments');
CALL safe_truncate('organizations');

-- ── CRM masters ──────────────────────────────────────────────
CALL safe_truncate('lead_sources');
CALL safe_truncate('lead_statuses');

-- ── Settings ─────────────────────────────────────────────────
CALL safe_truncate('communication_settings');
CALL safe_truncate('communication_templates');
CALL safe_truncate('settings');

-- ── RBAC ─────────────────────────────────────────────────────
CALL safe_truncate('role_permissions');
CALL safe_truncate('permissions');
CALL safe_truncate('roles');

-- ── Institution ──────────────────────────────────────────────
CALL safe_truncate('institutions');

-- Cleanup helper
DROP PROCEDURE IF EXISTS safe_truncate;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- RE-SEED: Institution
-- ============================================================
INSERT INTO institutions (id, name, code, email, phone, address, status, created_at)
VALUES (1, 'My Institution', 'INST001', 'admin@institution.com',
        '9000000000', 'Institution Address', 'active', NOW());

-- ============================================================
-- RE-SEED: Super Admin  (password: Admin@123)
-- ============================================================
INSERT INTO users (id, first_name, last_name, email, password, is_active, created_at)
VALUES (1, 'Super', 'Admin', 'admin@institution.com',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());

-- ============================================================
-- RE-SEED: Roles
-- ============================================================
INSERT INTO roles (id, name, display_name, description, is_system) VALUES
(1,  'super_admin',       'Super Admin',         'Full system access',       1),
(2,  'institution_admin', 'Institution Admin',   'Manages one institution',  1),
(3,  'principal',         'Principal',           'Academic head',            1),
(4,  'hod',               'HOD',                 'Head of Department',       1),
(5,  'faculty',           'Faculty',             'Teaching staff',           1),
(6,  'counselor',         'Counselor',           'Admission counselor',      1),
(7,  'accountant',        'Accountant',          'Fee & finance management', 1),
(8,  'receptionist',      'Receptionist',        'Front desk',               1),
(9,  'student',           'Student',             'Student portal access',    1),
(10, 'parent',            'Parent',              'Parent portal access',     1);

-- Link Super Admin to institution
INSERT INTO user_roles (user_id, institution_id, role_id, created_at)
VALUES (1, 1, 1, NOW());

-- ============================================================
-- RE-SEED: Lead Statuses
-- ============================================================
INSERT INTO lead_statuses (institution_id, name, color, sort_order, is_default, is_won, is_lost) VALUES
(1, 'New',            '#3b82f6', 1, 1, 0, 0),
(1, 'Contacted',      '#f59e0b', 2, 0, 0, 0),
(1, 'Interested',     '#8b5cf6', 3, 0, 0, 0),
(1, 'Follow Up',      '#06b6d4', 4, 0, 0, 0),
(1, 'Converted',      '#10b981', 5, 0, 1, 0),
(1, 'Not Interested', '#ef4444', 6, 0, 0, 1);

-- ============================================================
-- RE-SEED: Lead Sources
-- ============================================================
INSERT INTO lead_sources (institution_id, name, is_active) VALUES
(1, 'Walk-in',        1),
(1, 'Website',        1),
(1, 'Social Media',   1),
(1, 'Referral',       1),
(1, 'Phone Enquiry',  1),
(1, 'Advertisement',  1),
(1, 'Email Campaign', 1),
(1, 'Education Fair', 1);

-- ============================================================
-- RE-SEED: Academic Year
-- ============================================================
INSERT INTO academic_years (institution_id, name, start_date, end_date, is_current, status)
VALUES (1, '2025-26', '2025-06-01', '2026-05-31', 1, 'active');

-- ============================================================
-- RE-SEED: Default Settings
-- ============================================================
INSERT INTO settings (institution_id, `key`, `value`) VALUES
(1, 'app_name',          'Education CRM'),
(1, 'currency',          'INR'),
(1, 'currency_symbol',   '₹'),
(1, 'date_format',       'd/m/Y'),
(1, 'time_format',       'h:i A'),
(1, 'academic_year',     '2025-26'),
(1, 'admission_prefix',  'ADM'),
(1, 'student_id_prefix', 'STU'),
(1, 'lead_prefix',       'LEAD');

-- ============================================================
-- ✅ DONE
-- Login:    admin@institution.com
-- Password: Admin@123  ← change this after first login!
-- ============================================================
