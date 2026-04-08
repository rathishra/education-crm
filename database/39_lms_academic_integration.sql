-- ============================================================
-- Module 13: LMS ↔ Academic Integration
-- Bridges lms_courses ↔ subjects
--          lms_users   ↔ students / faculty_profiles
--          lms_enrollments ↔ student_section_enrollments
-- ============================================================
USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ── 1. Extend lms_courses with academic bridge columns ────────
ALTER TABLE `lms_courses`
    ADD COLUMN IF NOT EXISTS `subject_id`       BIGINT UNSIGNED NULL COMMENT 'Links to subjects.id'       AFTER `category_id`,
    ADD COLUMN IF NOT EXISTS `batch_id`         BIGINT UNSIGNED NULL COMMENT 'Links to batches.id'        AFTER `subject_id`,
    ADD COLUMN IF NOT EXISTS `academic_year_id` BIGINT UNSIGNED NULL COMMENT 'Links to academic_years.id' AFTER `batch_id`;

-- Indexes (CREATE INDEX IF NOT EXISTS not supported pre-8.0 — use ALTER IGNORE)
ALTER TABLE `lms_courses`
    ADD INDEX IF NOT EXISTS `idx_lc_subject`  (`subject_id`),
    ADD INDEX IF NOT EXISTS `idx_lc_batch`    (`batch_id`),
    ADD INDEX IF NOT EXISTS `idx_lc_acad_yr`  (`academic_year_id`);

-- Foreign keys (safe with SET FOREIGN_KEY_CHECKS=0)
ALTER TABLE `lms_courses`
    ADD CONSTRAINT IF NOT EXISTS `fk_lc_subject`  FOREIGN KEY (`subject_id`)       REFERENCES `subjects`(`id`)       ON DELETE SET NULL,
    ADD CONSTRAINT IF NOT EXISTS `fk_lc_batch`    FOREIGN KEY (`batch_id`)          REFERENCES `batches`(`id`)        ON DELETE SET NULL,
    ADD CONSTRAINT IF NOT EXISTS `fk_lc_acad_yr`  FOREIGN KEY (`academic_year_id`)  REFERENCES `academic_years`(`id`) ON DELETE SET NULL;

-- ── 2. Fix missing permissions (notifications.view, announcements.manage) ──
INSERT IGNORE INTO `lms_permissions` (`key`, `label`, `group`) VALUES
('notifications.view',    'View Notifications',       'notifications'),
('announcements.manage',  'Manage Announcements',     'notifications'),
('sync.manage',           'Manage Academic Sync',     'sync');

-- Assign to roles
INSERT IGNORE INTO `lms_role_permissions` (`role`, `permission_key`) VALUES
-- lms_admin gets everything
('lms_admin',   'notifications.view'),
('lms_admin',   'announcements.manage'),
('lms_admin',   'sync.manage'),
-- instructors can view notifications and manage announcements
('instructor',  'notifications.view'),
('instructor',  'announcements.manage'),
-- learners can view notifications
('learner',     'notifications.view'),
('content_manager', 'notifications.view');

-- ── 3. Sync audit log ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_academic_sync_log` (
    `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED  NOT NULL,
    `sync_type`      ENUM('students','faculty','courses','enrollments','all') NOT NULL,
    `synced_by`      BIGINT UNSIGNED  NULL,
    `created_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `updated_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `skipped_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
    `error_count`    INT UNSIGNED     NOT NULL DEFAULT 0,
    `notes`          TEXT             NULL,
    `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sync_inst` (`institution_id`),
    KEY `idx_sync_type` (`sync_type`),
    KEY `idx_sync_ts`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
