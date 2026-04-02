-- ============================================================
-- STUDENT PORTAL
-- Part 26: Student self-service portal — auth columns,
--           notification student mapping, document table
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- Student portal auth columns
ALTER TABLE `students`
    ADD COLUMN IF NOT EXISTS `password`               VARCHAR(255) NULL          AFTER `email`,
    ADD COLUMN IF NOT EXISTS `password_reset_token`   VARCHAR(255) NULL          AFTER `password`,
    ADD COLUMN IF NOT EXISTS `password_reset_at`      TIMESTAMP    NULL          AFTER `password_reset_token`,
    ADD COLUMN IF NOT EXISTS `portal_enabled`         TINYINT(1)   NOT NULL DEFAULT 1 AFTER `password_reset_at`,
    ADD COLUMN IF NOT EXISTS `last_portal_login_at`   TIMESTAMP    NULL          AFTER `portal_enabled`,
    ADD COLUMN IF NOT EXISTS `last_portal_login_ip`   VARCHAR(45)  NULL          AFTER `last_portal_login_at`;

-- Student notifications (portal-targeted)
ALTER TABLE `notifications`
    ADD COLUMN IF NOT EXISTS `student_id` BIGINT UNSIGNED NULL AFTER `user_id`;

ALTER TABLE `notifications`
    ADD INDEX IF NOT EXISTS `idx_notif_student` (`student_id`);

-- Student documents (dedicated table for portal doc management)
CREATE TABLE IF NOT EXISTS `student_documents` (
    `id`              BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `institution_id`  BIGINT UNSIGNED   NOT NULL,
    `student_id`      BIGINT UNSIGNED   NOT NULL,
    `document_type`   VARCHAR(100)      NOT NULL,
    `title`           VARCHAR(255)      NOT NULL,
    `file_path`       VARCHAR(500)      NOT NULL,
    `file_name`       VARCHAR(255)      NOT NULL,
    `file_size`       INT UNSIGNED      NULL,
    `mime_type`       VARCHAR(100)      NULL,
    `is_verified`     TINYINT(1)        NOT NULL DEFAULT 0,
    `remarks`         VARCHAR(500)      NULL,
    `uploaded_by`     BIGINT UNSIGNED   NULL,
    `deleted_at`      DATETIME          NULL,
    `created_at`      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_sd_student`  (`student_id`),
    KEY `idx_sd_inst`     (`institution_id`),
    CONSTRAINT `fk_sd_student` FOREIGN KEY (`student_id`)     REFERENCES `students`(`id`)     ON DELETE CASCADE,
    CONSTRAINT `fk_sd_inst`    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for portal auth lookups
ALTER TABLE `students`
    ADD INDEX IF NOT EXISTS `idx_stu_portal_token`  (`password_reset_token`),
    ADD INDEX IF NOT EXISTS `idx_stu_portal_email`  (`email`);

SET FOREIGN_KEY_CHECKS = 1;
