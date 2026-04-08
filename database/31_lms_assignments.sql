-- ============================================================
-- MODULE 5: Enterprise LMS — Assignments
-- ============================================================
USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Assignments ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_assignments` (
    `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `course_id`         BIGINT UNSIGNED  NOT NULL,
    `lesson_id`         BIGINT UNSIGNED  NULL,       -- optional lesson link
    `institution_id`    BIGINT UNSIGNED  NOT NULL,
    `created_by`        BIGINT UNSIGNED  NOT NULL,   -- lms_user.id
    `title`             VARCHAR(255)     NOT NULL,
    `instructions`      LONGTEXT         NOT NULL,
    `max_score`         SMALLINT UNSIGNED NOT NULL DEFAULT 100,
    `pass_score`        SMALLINT UNSIGNED NOT NULL DEFAULT 50,
    `submission_type`   ENUM('file','text','url','any') NOT NULL DEFAULT 'any',
    `allowed_file_types` VARCHAR(255)    NULL,       -- csv: pdf,docx,zip …
    `max_file_size_mb`  TINYINT UNSIGNED NOT NULL DEFAULT 10,
    `due_at`            DATETIME         NULL,
    `allow_late`        TINYINT(1)       NOT NULL DEFAULT 0,
    `late_penalty_pct`  TINYINT UNSIGNED NOT NULL DEFAULT 0,   -- % deducted
    `attempts_allowed`  TINYINT UNSIGNED NOT NULL DEFAULT 1,   -- 0 = unlimited
    `is_published`      TINYINT(1)       NOT NULL DEFAULT 1,
    `rubric`            JSON             NULL,                  -- [{criterion,points}]
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME         NULL,
    PRIMARY KEY (`id`),
    KEY `idx_lms_asn_course`  (`course_id`),
    KEY `idx_lms_asn_inst`    (`institution_id`),
    KEY `idx_lms_asn_due`     (`due_at`),
    CONSTRAINT `fk_lms_asn_course` FOREIGN KEY (`course_id`) REFERENCES `lms_courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_asn_by`    FOREIGN KEY (`created_by`) REFERENCES `lms_users`(`id`)   ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Submissions ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_assignment_submissions` (
    `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `assignment_id`     BIGINT UNSIGNED  NOT NULL,
    `lms_user_id`       BIGINT UNSIGNED  NOT NULL,
    `attempt`           TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `submission_type`   ENUM('file','text','url') NOT NULL DEFAULT 'text',
    `text_content`      LONGTEXT         NULL,
    `file_path`         VARCHAR(500)     NULL,
    `file_original`     VARCHAR(255)     NULL,
    `url_content`       VARCHAR(500)     NULL,
    `status`            ENUM('submitted','graded','returned','late') NOT NULL DEFAULT 'submitted',
    `score`             DECIMAL(6,2)     NULL,
    `feedback`          TEXT             NULL,
    `graded_by`         BIGINT UNSIGNED  NULL,
    `graded_at`         TIMESTAMP        NULL,
    `is_late`           TINYINT(1)       NOT NULL DEFAULT 0,
    `submitted_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_sub` (`assignment_id`, `lms_user_id`, `attempt`),
    KEY `idx_lms_sub_assignment` (`assignment_id`),
    KEY `idx_lms_sub_user`       (`lms_user_id`),
    KEY `idx_lms_sub_status`     (`status`),
    CONSTRAINT `fk_lms_sub_asn`   FOREIGN KEY (`assignment_id`) REFERENCES `lms_assignments`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_sub_user`  FOREIGN KEY (`lms_user_id`)   REFERENCES `lms_users`(`id`)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
