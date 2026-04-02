-- ============================================================
-- MODULE 1: Enterprise LMS — Authentication & User Schema
-- ============================================================
USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ── LMS Users ────────────────────────────────────────────────
-- Standalone LMS users (can optionally link to existing student/staff)
CREATE TABLE IF NOT EXISTS `lms_users` (
    `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `institution_id`    BIGINT UNSIGNED  NOT NULL,
    `email`             VARCHAR(255)     NOT NULL,
    `password`          VARCHAR(255)     NOT NULL,
    `first_name`        VARCHAR(100)     NOT NULL,
    `last_name`         VARCHAR(100)     NOT NULL DEFAULT '',
    `display_name`      VARCHAR(150)     NULL,
    `avatar`            VARCHAR(500)     NULL,
    `bio`               TEXT             NULL,
    `phone`             VARCHAR(30)      NULL,
    `timezone`          VARCHAR(60)      NOT NULL DEFAULT 'Asia/Kolkata',
    `locale`            VARCHAR(10)      NOT NULL DEFAULT 'en',

    -- Linked accounts (optional)
    `student_id`        BIGINT UNSIGNED  NULL,   -- links to students.id
    `staff_user_id`     BIGINT UNSIGNED  NULL,   -- links to users.id

    -- Status & access
    `role`              ENUM('lms_admin','instructor','learner','content_manager') NOT NULL DEFAULT 'learner',
    `status`            ENUM('active','inactive','suspended','pending') NOT NULL DEFAULT 'pending',
    `email_verified_at` TIMESTAMP        NULL,
    `verification_token` VARCHAR(100)    NULL,

    -- Auth tokens
    `remember_token`    VARCHAR(100)     NULL,
    `reset_token`       VARCHAR(100)     NULL,
    `reset_token_at`    TIMESTAMP        NULL,

    -- Tracking
    `last_login_at`     TIMESTAMP        NULL,
    `last_login_ip`     VARCHAR(45)      NULL,
    `login_count`       INT UNSIGNED     NOT NULL DEFAULT 0,
    `last_active_at`    TIMESTAMP        NULL,

    -- Gamification seed
    `xp_points`         INT UNSIGNED     NOT NULL DEFAULT 0,
    `level`             TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `badges_count`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,

    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME         NULL,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_user_email_inst` (`email`, `institution_id`),
    KEY `idx_lms_user_student`   (`student_id`),
    KEY `idx_lms_user_staff`     (`staff_user_id`),
    KEY `idx_lms_user_inst`      (`institution_id`),
    KEY `idx_lms_user_role`      (`role`),
    KEY `idx_lms_user_status`    (`status`),
    CONSTRAINT `fk_lms_user_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── LMS Roles & Fine-grained Permissions ─────────────────────
CREATE TABLE IF NOT EXISTS `lms_permissions` (
    `id`         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`        VARCHAR(100)      NOT NULL,
    `label`      VARCHAR(150)      NOT NULL,
    `group`      VARCHAR(80)       NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_perm_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lms_role_permissions` (
    `role`          ENUM('lms_admin','instructor','learner','content_manager') NOT NULL,
    `permission_key` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`role`, `permission_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lms_user_permissions` (
    `lms_user_id`    BIGINT UNSIGNED NOT NULL,
    `permission_key` VARCHAR(100)    NOT NULL,
    `granted`        TINYINT(1)      NOT NULL DEFAULT 1,
    PRIMARY KEY (`lms_user_id`, `permission_key`),
    CONSTRAINT `fk_lms_up_user` FOREIGN KEY (`lms_user_id`) REFERENCES `lms_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Active Sessions ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_sessions` (
    `id`            VARCHAR(128)     NOT NULL,
    `lms_user_id`   BIGINT UNSIGNED  NOT NULL,
    `ip_address`    VARCHAR(45)      NULL,
    `user_agent`    VARCHAR(500)     NULL,
    `payload`       TEXT             NULL,
    `last_active`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lms_sess_user` (`lms_user_id`),
    CONSTRAINT `fk_lms_sess_user` FOREIGN KEY (`lms_user_id`) REFERENCES `lms_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Audit Log ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_audit_log` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `lms_user_id`   BIGINT UNSIGNED  NULL,
    `action`        VARCHAR(100)     NOT NULL,
    `entity_type`   VARCHAR(80)      NULL,
    `entity_id`     BIGINT UNSIGNED  NULL,
    `meta`          JSON             NULL,
    `ip_address`    VARCHAR(45)      NULL,
    `user_agent`    VARCHAR(500)     NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lms_audit_user`   (`lms_user_id`),
    KEY `idx_lms_audit_action` (`action`),
    KEY `idx_lms_audit_ts`     (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Seed default permissions ──────────────────────────────────
INSERT IGNORE INTO `lms_permissions` (`key`, `label`, `group`) VALUES
-- Courses
('courses.view',       'View Courses',           'courses'),
('courses.create',     'Create Courses',          'courses'),
('courses.edit',       'Edit Courses',            'courses'),
('courses.delete',     'Delete Courses',          'courses'),
('courses.publish',    'Publish/Unpublish',       'courses'),
('courses.enroll',     'Manage Enrollments',      'courses'),
-- Content
('content.view',       'View Content',            'content'),
('content.create',     'Create Content',          'content'),
('content.edit',       'Edit Content',            'content'),
('content.delete',     'Delete Content',          'content'),
-- Assignments
('assignments.view',   'View Assignments',        'assignments'),
('assignments.create', 'Create Assignments',      'assignments'),
('assignments.grade',  'Grade Assignments',       'assignments'),
-- Quizzes
('quizzes.view',       'View Quizzes',            'quizzes'),
('quizzes.create',     'Create Quizzes',          'quizzes'),
('quizzes.grade',      'Grade Quizzes',           'quizzes'),
-- Attendance
('attendance.view',    'View Attendance',         'attendance'),
('attendance.mark',    'Mark Attendance',         'attendance'),
-- Live Classes
('live.view',          'View Live Classes',       'live'),
('live.create',        'Create Live Classes',     'live'),
('live.host',          'Host Live Sessions',      'live'),
-- Forums
('forums.view',        'View Forums',             'forums'),
('forums.post',        'Post in Forums',          'forums'),
('forums.moderate',    'Moderate Forums',         'forums'),
-- Gradebook
('gradebook.view',     'View Gradebook',          'gradebook'),
('gradebook.edit',     'Edit Gradebook',          'gradebook'),
-- Analytics
('analytics.view',     'View Analytics',          'analytics'),
('analytics.export',   'Export Reports',          'analytics'),
-- Users
('users.view',         'View LMS Users',          'users'),
('users.manage',       'Manage LMS Users',        'users'),
-- Certificates
('certificates.view',  'View Certificates',       'certificates'),
('certificates.issue', 'Issue Certificates',      'certificates'),
-- Notifications
('notifications.send', 'Send Notifications',      'notifications');

-- ── Default role → permission mappings ───────────────────────
INSERT IGNORE INTO `lms_role_permissions` (`role`, `permission_key`) VALUES
-- LMS Admin: everything
('lms_admin','courses.view'),('lms_admin','courses.create'),('lms_admin','courses.edit'),
('lms_admin','courses.delete'),('lms_admin','courses.publish'),('lms_admin','courses.enroll'),
('lms_admin','content.view'),('lms_admin','content.create'),('lms_admin','content.edit'),('lms_admin','content.delete'),
('lms_admin','assignments.view'),('lms_admin','assignments.create'),('lms_admin','assignments.grade'),
('lms_admin','quizzes.view'),('lms_admin','quizzes.create'),('lms_admin','quizzes.grade'),
('lms_admin','attendance.view'),('lms_admin','attendance.mark'),
('lms_admin','live.view'),('lms_admin','live.create'),('lms_admin','live.host'),
('lms_admin','forums.view'),('lms_admin','forums.post'),('lms_admin','forums.moderate'),
('lms_admin','gradebook.view'),('lms_admin','gradebook.edit'),
('lms_admin','analytics.view'),('lms_admin','analytics.export'),
('lms_admin','users.view'),('lms_admin','users.manage'),
('lms_admin','certificates.view'),('lms_admin','certificates.issue'),
('lms_admin','notifications.send'),
-- Instructor
('instructor','courses.view'),('instructor','courses.create'),('instructor','courses.edit'),
('instructor','courses.publish'),('instructor','courses.enroll'),
('instructor','content.view'),('instructor','content.create'),('instructor','content.edit'),('instructor','content.delete'),
('instructor','assignments.view'),('instructor','assignments.create'),('instructor','assignments.grade'),
('instructor','quizzes.view'),('instructor','quizzes.create'),('instructor','quizzes.grade'),
('instructor','attendance.view'),('instructor','attendance.mark'),
('instructor','live.view'),('instructor','live.create'),('instructor','live.host'),
('instructor','forums.view'),('instructor','forums.post'),('instructor','forums.moderate'),
('instructor','gradebook.view'),('instructor','gradebook.edit'),
('instructor','analytics.view'),
('instructor','certificates.view'),('instructor','certificates.issue'),
('instructor','notifications.send'),
-- Content Manager
('content_manager','courses.view'),('content_manager','courses.edit'),
('content_manager','content.view'),('content_manager','content.create'),
('content_manager','content.edit'),('content_manager','content.delete'),
('content_manager','quizzes.view'),('content_manager','quizzes.create'),
-- Learner: read + participate only
('learner','courses.view'),
('learner','content.view'),
('learner','assignments.view'),
('learner','quizzes.view'),
('learner','attendance.view'),
('learner','live.view'),
('learner','forums.view'),('learner','forums.post'),
('learner','gradebook.view'),
('learner','certificates.view');

SET FOREIGN_KEY_CHECKS = 1;
