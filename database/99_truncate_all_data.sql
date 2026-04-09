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
-- RE-SEED: Organization
-- ============================================================
INSERT INTO organizations (id, organization_name, organization_code, status, created_at)
VALUES (1, 'My Organization', 'ORG001', 'active', NOW());

-- ============================================================
-- RE-SEED: Institution
-- ============================================================
INSERT INTO institutions (id, organization_id, name, code, type, email, phone,
                          address_line1, city, state, country, status, created_at)
VALUES (1, 1, 'My Institution', 'INST001', 'arts_science',
        'admin@institution.com', '9000000000',
        'Institution Address', 'City', 'State', 'India', 'active', NOW());

-- ============================================================
-- RE-SEED: Super Admin  (password: Admin@123)
-- ============================================================
INSERT INTO users (id, first_name, last_name, email, password, is_active, created_at)
VALUES (1, 'Super', 'Admin', 'admin@institution.com',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, NOW());

-- ============================================================
-- RE-SEED: Roles  (slug = unique key)
-- ============================================================
INSERT INTO roles (id, name, slug, description, is_system, level) VALUES
(1,  'Super Admin',        'super_admin',       'Full system access',        1, 0),
(2,  'Institution Admin',  'institution_admin', 'Manages one institution',   1, 2),
(3,  'Principal',          'principal',         'Academic head',             1, 2),
(4,  'HOD',                'hod',               'Head of Department',        1, 3),
(5,  'Faculty',            'faculty',           'Teaching staff',            1, 3),
(6,  'Counselor',          'counselor',         'Admission counselor',       1, 3),
(7,  'Accountant',         'accountant',        'Fee & finance management',  1, 3),
(8,  'Receptionist',       'receptionist',      'Front desk',                1, 3),
(9,  'Student',            'student',           'Student portal access',     1, 3),
(10, 'Parent',             'parent',            'Parent portal access',      1, 3);

-- Link Super Admin
INSERT INTO user_roles (user_id, role_id, organization_id, institution_id, created_at)
VALUES (1, 1, 1, 1, NOW());

-- ============================================================
-- RE-SEED: Lead Statuses  (slug = unique key)
-- ============================================================
INSERT INTO lead_statuses (name, slug, color, sort_order, is_default, is_won, is_lost) VALUES
('New',            'new',            '#3b82f6', 1, 1, 0, 0),
('Contacted',      'contacted',      '#f59e0b', 2, 0, 0, 0),
('Interested',     'interested',     '#8b5cf6', 3, 0, 0, 0),
('Follow Up',      'follow-up',      '#06b6d4', 4, 0, 0, 0),
('Converted',      'converted',      '#10b981', 5, 0, 1, 0),
('Not Interested', 'not-interested', '#ef4444', 6, 0, 0, 1);

-- ============================================================
-- RE-SEED: Lead Sources  (slug = unique key)
-- ============================================================
INSERT INTO lead_sources (name, slug, is_active) VALUES
('Walk-in',        'walk-in',        1),
('Website',        'website',        1),
('Social Media',   'social-media',   1),
('Referral',       'referral',       1),
('Phone Enquiry',  'phone-enquiry',  1),
('Advertisement',  'advertisement',  1),
('Email Campaign', 'email-campaign', 1),
('Education Fair', 'education-fair', 1);

-- ============================================================
-- RE-SEED: Academic Year
-- ============================================================
INSERT INTO academic_years (institution_id, name, start_date, end_date, is_current, status)
VALUES (1, '2025-26', '2025-06-01', '2026-05-31', 1, 'active');

-- ============================================================
-- RE-SEED: Default Settings  (group_name + key_name columns)
-- ============================================================
INSERT INTO settings (institution_id, group_name, key_name, value, type) VALUES
(1, 'general', 'app_name',          'Education CRM', 'string'),
(1, 'general', 'currency',          'INR',           'string'),
(1, 'general', 'currency_symbol',   '₹',             'string'),
(1, 'general', 'date_format',       'd/m/Y',         'string'),
(1, 'general', 'time_format',       'h:i A',         'string'),
(1, 'general', 'academic_year',     '2025-26',       'string'),
(1, 'general', 'admission_prefix',  'ADM',           'string'),
(1, 'general', 'student_id_prefix', 'STU',           'string'),
(1, 'general', 'lead_prefix',       'LEAD',          'string');

-- ============================================================
-- ✅ DONE
-- Login:    admin@institution.com
-- Password: Admin@123  ← change this after first login!
-- ============================================================
