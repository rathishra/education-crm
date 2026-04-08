-- ============================================================
-- MODULE 3: Enterprise LMS — Course Management Schema
-- ============================================================
USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Course Sections (curriculum structure) ────────────────────
CREATE TABLE IF NOT EXISTS `lms_course_sections` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `course_id`     BIGINT UNSIGNED  NOT NULL,
    `title`         VARCHAR(255)     NOT NULL,
    `description`   TEXT             NULL,
    `sort_order`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_free`       TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lms_sec_course` (`course_id`),
    CONSTRAINT `fk_lms_sec_course` FOREIGN KEY (`course_id`) REFERENCES `lms_courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Lessons ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_lessons` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `course_id`     BIGINT UNSIGNED  NOT NULL,
    `section_id`    BIGINT UNSIGNED  NOT NULL,
    `title`         VARCHAR(255)     NOT NULL,
    `slug`          VARCHAR(270)     NOT NULL,
    `type`          ENUM('video','document','text','quiz','assignment','live','scorm') NOT NULL DEFAULT 'video',
    `content`       LONGTEXT         NULL,      -- rich text / embed HTML
    `video_url`     VARCHAR(500)     NULL,      -- YouTube / Vimeo / direct URL
    `video_duration` SMALLINT UNSIGNED NULL,    -- seconds
    `file_path`     VARCHAR(500)     NULL,      -- uploaded file
    `sort_order`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `is_free`       TINYINT(1)       NOT NULL DEFAULT 0,
    `is_published`  TINYINT(1)       NOT NULL DEFAULT 1,
    `xp_reward`     SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    DATETIME         NULL,
    PRIMARY KEY (`id`),
    KEY `idx_lms_less_course`  (`course_id`),
    KEY `idx_lms_less_section` (`section_id`),
    CONSTRAINT `fk_lms_less_course`  FOREIGN KEY (`course_id`)  REFERENCES `lms_courses`(`id`)          ON DELETE CASCADE,
    CONSTRAINT `fk_lms_less_section` FOREIGN KEY (`section_id`) REFERENCES `lms_course_sections`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Lesson Progress ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_lesson_progress` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `enrollment_id` BIGINT UNSIGNED  NOT NULL,
    `lesson_id`     BIGINT UNSIGNED  NOT NULL,
    `lms_user_id`   BIGINT UNSIGNED  NOT NULL,
    `status`        ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
    `watch_seconds` INT UNSIGNED     NOT NULL DEFAULT 0,
    `completed_at`  TIMESTAMP        NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_lp` (`enrollment_id`, `lesson_id`),
    KEY `idx_lms_lp_user`   (`lms_user_id`),
    KEY `idx_lms_lp_lesson` (`lesson_id`),
    CONSTRAINT `fk_lms_lp_enroll` FOREIGN KEY (`enrollment_id`) REFERENCES `lms_enrollments`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_lp_lesson` FOREIGN KEY (`lesson_id`)     REFERENCES `lms_lessons`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Course Tags ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_tags` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `name`          VARCHAR(80)      NOT NULL,
    `slug`          VARCHAR(90)      NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_tag` (`slug`, `institution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lms_course_tags` (
    `course_id` BIGINT UNSIGNED NOT NULL,
    `tag_id`    BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`course_id`, `tag_id`),
    CONSTRAINT `fk_lms_ct_course` FOREIGN KEY (`course_id`) REFERENCES `lms_courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_ct_tag`    FOREIGN KEY (`tag_id`)    REFERENCES `lms_tags`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Course Reviews / Ratings ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_course_reviews` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `course_id`     BIGINT UNSIGNED  NOT NULL,
    `lms_user_id`   BIGINT UNSIGNED  NOT NULL,
    `rating`        TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `review`        TEXT             NULL,
    `is_approved`   TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_rev` (`course_id`, `lms_user_id`),
    KEY `idx_lms_rev_course` (`course_id`),
    CONSTRAINT `fk_lms_rev_course` FOREIGN KEY (`course_id`)   REFERENCES `lms_courses`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_lms_rev_user`   FOREIGN KEY (`lms_user_id`) REFERENCES `lms_users`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
