-- ============================================================
-- TRUNCATE ALL DATA — Fresh Product Reset
-- ============================================================
-- WARNING: This permanently deletes ALL data.
-- Schema (tables/columns/indexes) is preserved.
-- Run this in phpMyAdmin on the education_crm database.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── LMS ──────────────────────────────────────────────────────
TRUNCATE TABLE lms_academic_sync_log;
TRUNCATE TABLE lms_activity_feed;
TRUNCATE TABLE lms_analytics_daily;
TRUNCATE TABLE lms_announcement_dismissals;
TRUNCATE TABLE lms_announcements;
TRUNCATE TABLE lms_assignment_submissions;
TRUNCATE TABLE lms_assignments;
TRUNCATE TABLE lms_attendance_records;
TRUNCATE TABLE lms_attendance_sessions;
TRUNCATE TABLE lms_audit_log;
TRUNCATE TABLE lms_categories;
TRUNCATE TABLE lms_computed_grades;
TRUNCATE TABLE lms_course_reviews;
TRUNCATE TABLE lms_course_sections;
TRUNCATE TABLE lms_course_tags;
TRUNCATE TABLE lms_courses;
TRUNCATE TABLE lms_dashboard_prefs;
TRUNCATE TABLE lms_deadlines;
TRUNCATE TABLE lms_enrollments;
TRUNCATE TABLE lms_forum_categories;
TRUNCATE TABLE lms_forum_posts;
TRUNCATE TABLE lms_forum_reactions;
TRUNCATE TABLE lms_forum_subscriptions;
TRUNCATE TABLE lms_forum_threads;
TRUNCATE TABLE lms_grade_overrides;
TRUNCATE TABLE lms_grade_weights;
TRUNCATE TABLE lms_lesson_progress;
TRUNCATE TABLE lms_lesson_views;
TRUNCATE TABLE lms_lessons;
TRUNCATE TABLE lms_live_classes;
TRUNCATE TABLE lms_live_registrations;
TRUNCATE TABLE lms_materials;
TRUNCATE TABLE lms_notifications;
TRUNCATE TABLE lms_quiz_answers;
TRUNCATE TABLE lms_quiz_attempts;
TRUNCATE TABLE lms_quiz_options;
TRUNCATE TABLE lms_quiz_questions;
TRUNCATE TABLE lms_quizzes;
TRUNCATE TABLE lms_sessions;
TRUNCATE TABLE lms_tags;
TRUNCATE TABLE lms_user_permissions;
TRUNCATE TABLE lms_users;

-- ── Academic ─────────────────────────────────────────────────
TRUNCATE TABLE academic_assessment_marks;
TRUNCATE TABLE academic_assessments;
TRUNCATE TABLE academic_attendance_records;
TRUNCATE TABLE academic_attendance_sessions;
TRUNCATE TABLE academic_batches;
TRUNCATE TABLE academic_sections;
TRUNCATE TABLE academic_timetable;
TRUNCATE TABLE academic_timetable_periods;
TRUNCATE TABLE academic_years;
TRUNCATE TABLE assessment_configs;
TRUNCATE TABLE assessment_marks;
TRUNCATE TABLE attendance_records;
TRUNCATE TABLE attendance_sessions;
TRUNCATE TABLE attendances;
TRUNCATE TABLE batch_faculty;
TRUNCATE TABLE batch_subjects;
TRUNCATE TABLE batches;
TRUNCATE TABLE classrooms;
TRUNCATE TABLE exam_marks;
TRUNCATE TABLE exam_schedules;
TRUNCATE TABLE exams;
TRUNCATE TABLE faculty_profiles;
TRUNCATE TABLE faculty_subject_allocations;
TRUNCATE TABLE grading_grade_rules;
TRUNCATE TABLE grading_mark_components;
TRUNCATE TABLE grading_schema_categories;
TRUNCATE TABLE grading_schemas;
TRUNCATE TABLE grading_sub_components;
TRUNCATE TABLE sections;
TRUNCATE TABLE student_section_enrollments;
TRUNCATE TABLE subjects;
TRUNCATE TABLE timetable;
TRUNCATE TABLE timetable_periods;
TRUNCATE TABLE timetables;

-- ── Admissions & Students ────────────────────────────────────
TRUNCATE TABLE admission_documents;
TRUNCATE TABLE admission_payments;
TRUNCATE TABLE admission_timeline;
TRUNCATE TABLE admissions;
TRUNCATE TABLE placement_applications;
TRUNCATE TABLE placement_companies;
TRUNCATE TABLE placement_drives;
TRUNCATE TABLE student_activities;
TRUNCATE TABLE student_documents;
TRUNCATE TABLE student_fees;
TRUNCATE TABLE student_installments;
TRUNCATE TABLE students;

-- ── CRM ──────────────────────────────────────────────────────
TRUNCATE TABLE enquiries;
TRUNCATE TABLE followups;
TRUNCATE TABLE lead_activities;
TRUNCATE TABLE lead_followups;
TRUNCATE TABLE leads;

-- ── Fees ─────────────────────────────────────────────────────
TRUNCATE TABLE fee_components;
TRUNCATE TABLE fee_concessions;
TRUNCATE TABLE fee_fine_rules;
TRUNCATE TABLE fee_heads;
TRUNCATE TABLE fee_installments;
TRUNCATE TABLE fee_receipt_items;
TRUNCATE TABLE fee_receipts;
TRUNCATE TABLE fee_refunds;
TRUNCATE TABLE fee_structure_details;
TRUNCATE TABLE fee_structures;
TRUNCATE TABLE fee_student_assignments;
TRUNCATE TABLE installment_plans;
TRUNCATE TABLE payments;
TRUNCATE TABLE payslips;

-- ── Transport ────────────────────────────────────────────────
TRUNCATE TABLE transport_allocations;
TRUNCATE TABLE transport_stops;
TRUNCATE TABLE transport_routes;

-- ── Hostel ───────────────────────────────────────────────────
TRUNCATE TABLE hostel_allocations;
TRUNCATE TABLE hostel_rooms;
TRUNCATE TABLE hostels;

-- ── Library ──────────────────────────────────────────────────
TRUNCATE TABLE library_issues;
TRUNCATE TABLE library_books;

-- ── Communication ────────────────────────────────────────────
TRUNCATE TABLE bulk_campaigns;
TRUNCATE TABLE communications;
TRUNCATE TABLE notifications;
TRUNCATE TABLE tasks;

-- ── HR / Staff ───────────────────────────────────────────────
TRUNCATE TABLE staff_leave_requests;
TRUNCATE TABLE staff_profiles;

-- ── Documents / Uploads ──────────────────────────────────────
TRUNCATE TABLE documents;
TRUNCATE TABLE uploads;

-- ── Auth & Users ─────────────────────────────────────────────
TRUNCATE TABLE audit_logs;
TRUNCATE TABLE password_resets;
TRUNCATE TABLE user_sessions;
TRUNCATE TABLE user_permission_overrides;
TRUNCATE TABLE user_roles;
TRUNCATE TABLE users;

-- ── Organisation & Courses (configurable masters) ────────────
TRUNCATE TABLE campuses;
TRUNCATE TABLE courses;
TRUNCATE TABLE departments;
TRUNCATE TABLE organizations;

-- ── Lead / Enquiry masters ───────────────────────────────────
TRUNCATE TABLE lead_sources;
TRUNCATE TABLE lead_statuses;

-- ── Settings & Comms config ──────────────────────────────────
TRUNCATE TABLE communication_settings;
TRUNCATE TABLE communication_templates;
TRUNCATE TABLE settings;

-- ── RBAC — keep structure, clear overrides ───────────────────
-- (roles & permissions rows are re-seeded below)
TRUNCATE TABLE role_permissions;
TRUNCATE TABLE lms_role_permissions;
TRUNCATE TABLE lms_permissions;
TRUNCATE TABLE permissions;
TRUNCATE TABLE roles;

-- ── Institution — reset to single fresh row ──────────────────
TRUNCATE TABLE institutions;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- RE-SEED: Single institution
-- ============================================================
INSERT INTO institutions (id, name, code, email, phone, address, status, created_at)
VALUES (1, 'My Institution', 'INST001', 'admin@institution.com', '9000000000',
        'Institution Address', 'active', NOW());

-- ============================================================
-- RE-SEED: Super Admin user  (password: Admin@123)
-- Change the password after first login!
-- ============================================================
INSERT INTO users (id, first_name, last_name, email, password, is_active, created_at)
VALUES (1, 'Super', 'Admin', 'admin@institution.com',
        '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin@123
        1, NOW());

-- Link user to institution
INSERT INTO user_roles (user_id, institution_id, role_id, created_at)
SELECT 1, 1, r.id, NOW() FROM roles r WHERE r.name = 'super_admin' LIMIT 1;

-- ============================================================
-- RE-SEED: Default lead statuses
-- ============================================================
INSERT INTO lead_statuses (institution_id, name, color, sort_order, is_default, is_won, is_lost) VALUES
(1, 'New',         '#3b82f6', 1, 1, 0, 0),
(1, 'Contacted',   '#f59e0b', 2, 0, 0, 0),
(1, 'Interested',  '#8b5cf6', 3, 0, 0, 0),
(1, 'Follow Up',   '#06b6d4', 4, 0, 0, 0),
(1, 'Converted',   '#10b981', 5, 0, 1, 0),
(1, 'Not Interested', '#ef4444', 6, 0, 0, 1);

-- ============================================================
-- RE-SEED: Default lead sources
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
-- RE-SEED: Roles & Permissions
-- (Copied from 12_seed_erp_permissions.sql baseline)
-- ============================================================
INSERT INTO roles (id, name, display_name, description, is_system) VALUES
(1,  'super_admin',    'Super Admin',        'Full system access',           1),
(2,  'institution_admin','Institution Admin', 'Manages one institution',      1),
(3,  'principal',      'Principal',          'Academic head',                1),
(4,  'hod',            'HOD',                'Head of Department',           1),
(5,  'faculty',        'Faculty',            'Teaching staff',               1),
(6,  'counselor',      'Counselor',          'Admission counselor',          1),
(7,  'accountant',     'Accountant',         'Fee & finance management',     1),
(8,  'receptionist',   'Receptionist',       'Front desk',                   1),
(9,  'student',        'Student',            'Student portal access',        1),
(10, 'parent',         'Parent',             'Parent portal access',         1);

-- Re-link super admin
INSERT IGNORE INTO user_roles (user_id, institution_id, role_id, created_at)
VALUES (1, 1, 1, NOW());

-- ============================================================
-- RE-SEED: Default academic year
-- ============================================================
INSERT INTO academic_years (institution_id, name, start_date, end_date, is_current, status)
VALUES (1, '2025-26', '2025-06-01', '2026-05-31', 1, 'active');

-- ============================================================
-- RE-SEED: Default settings
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
-- DONE ✓  Login: admin@institution.com / Admin@123
-- ============================================================
