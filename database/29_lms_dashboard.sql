-- ============================================================
-- MODULE 2: Enterprise LMS — Dashboard Support Tables
-- ============================================================
USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Courses (minimal seed — expanded fully in Module 3) ───────
CREATE TABLE IF NOT EXISTS `lms_courses` (
    `id`                BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `institution_id`    BIGINT UNSIGNED   NOT NULL,
    `instructor_id`     BIGINT UNSIGNED   NOT NULL,
    `category_id`       BIGINT UNSIGNED   NULL,
    `code`              VARCHAR(50)       NOT NULL,
    `title`             VARCHAR(255)      NOT NULL,
    `slug`              VARCHAR(255)      NOT NULL,
    `short_description` VARCHAR(500)      NULL,
    `description`       LONGTEXT          NULL,
    `thumbnail`         VARCHAR(500)      NULL,
    `banner`            VARCHAR(500)      NULL,
    `level`             ENUM('beginner','intermediate','advanced','all_levels') NOT NULL DEFAULT 'all_levels',
    `language`          VARCHAR(30)       NOT NULL DEFAULT 'English',
    `duration_hours`    DECIMAL(6,2)      NULL,
    `total_lessons`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `total_quizzes`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `total_assignments` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `status`            ENUM('draft','published','archived','coming_soon') NOT NULL DEFAULT 'draft',
    `visibility`        ENUM('public','enrolled','private') NOT NULL DEFAULT 'enrolled',
    `is_featured`       TINYINT(1)        NOT NULL DEFAULT 0,
    `allow_self_enroll` TINYINT(1)        NOT NULL DEFAULT 1,
    `max_students`      SMALLINT UNSIGNED NULL,
    `pass_percentage`   TINYINT UNSIGNED  NOT NULL DEFAULT 60,
    `certificate_enabled` TINYINT(1)      NOT NULL DEFAULT 0,
    `start_date`        DATE              NULL,
    `end_date`          DATE              NULL,
    `enrolled_count`    INT UNSIGNED      NOT NULL DEFAULT 0,
    `rating_avg`        DECIMAL(3,2)      NOT NULL DEFAULT 0.00,
    `rating_count`      INT UNSIGNED      NOT NULL DEFAULT 0,
    `view_count`        INT UNSIGNED      NOT NULL DEFAULT 0,
    `sort_order`        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `meta`              JSON              NULL,
    `created_at`        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME          NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_course_slug_inst` (`slug`, `institution_id`),
    KEY `idx_lms_course_inst`       (`institution_id`),
    KEY `idx_lms_course_instructor` (`instructor_id`),
    KEY `idx_lms_course_status`     (`status`),
    KEY `idx_lms_course_featured`   (`is_featured`),
    CONSTRAINT `fk_lms_course_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_course_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `lms_users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Course Categories ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_categories` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `name`          VARCHAR(100)     NOT NULL,
    `slug`          VARCHAR(110)     NOT NULL,
    `icon`          VARCHAR(80)      NULL,
    `color`         VARCHAR(20)      NULL,
    `parent_id`     BIGINT UNSIGNED  NULL,
    `sort_order`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lms_cat_inst` (`institution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Enrollments ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_enrollments` (
    `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `course_id`         BIGINT UNSIGNED  NOT NULL,
    `lms_user_id`       BIGINT UNSIGNED  NOT NULL,
    `institution_id`    BIGINT UNSIGNED  NOT NULL,
    `enrolled_by`       BIGINT UNSIGNED  NULL,
    `status`            ENUM('active','completed','dropped','suspended') NOT NULL DEFAULT 'active',
    `progress`          TINYINT UNSIGNED NOT NULL DEFAULT 0,   -- 0-100 %
    `lessons_completed` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `last_accessed_at`  TIMESTAMP        NULL,
    `completed_at`      TIMESTAMP        NULL,
    `certificate_issued_at` TIMESTAMP    NULL,
    `score`             DECIMAL(5,2)     NULL,                 -- final grade
    `grade`             VARCHAR(5)       NULL,                 -- A+, A, B …
    `enrolled_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_enroll` (`course_id`, `lms_user_id`),
    KEY `idx_lms_enroll_user`   (`lms_user_id`),
    KEY `idx_lms_enroll_inst`   (`institution_id`),
    KEY `idx_lms_enroll_status` (`status`),
    CONSTRAINT `fk_lms_enroll_course` FOREIGN KEY (`course_id`)   REFERENCES `lms_courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_enroll_user`   FOREIGN KEY (`lms_user_id`) REFERENCES `lms_users`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Announcements ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_announcements` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `course_id`     BIGINT UNSIGNED  NULL,     -- NULL = platform-wide
    `author_id`     BIGINT UNSIGNED  NOT NULL,
    `title`         VARCHAR(255)     NOT NULL,
    `body`          TEXT             NOT NULL,
    `type`          ENUM('info','warning','success','alert') NOT NULL DEFAULT 'info',
    `is_pinned`     TINYINT(1)       NOT NULL DEFAULT 0,
    `publish_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expire_at`     TIMESTAMP        NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`    DATETIME         NULL,
    PRIMARY KEY (`id`),
    KEY `idx_lms_ann_inst`   (`institution_id`),
    KEY `idx_lms_ann_course` (`course_id`),
    KEY `idx_lms_ann_pinned` (`is_pinned`),
    CONSTRAINT `fk_lms_ann_author` FOREIGN KEY (`author_id`) REFERENCES `lms_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Activity Feed ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_activity_feed` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `lms_user_id`   BIGINT UNSIGNED  NOT NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `event`         VARCHAR(80)      NOT NULL,   -- 'lesson_completed','quiz_submitted', etc.
    `entity_type`   VARCHAR(60)      NULL,
    `entity_id`     BIGINT UNSIGNED  NULL,
    `entity_title`  VARCHAR(255)     NULL,
    `xp_earned`     SMALLINT         NOT NULL DEFAULT 0,
    `meta`          JSON             NULL,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lms_feed_user`  (`lms_user_id`),
    KEY `idx_lms_feed_inst`  (`institution_id`),
    KEY `idx_lms_feed_event` (`event`),
    KEY `idx_lms_feed_ts`    (`created_at`),
    CONSTRAINT `fk_lms_feed_user` FOREIGN KEY (`lms_user_id`) REFERENCES `lms_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Deadlines (denormalised for fast dashboard queries) ────────
CREATE TABLE IF NOT EXISTS `lms_deadlines` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `course_id`     BIGINT UNSIGNED  NOT NULL,
    `lms_user_id`   BIGINT UNSIGNED  NOT NULL,   -- target learner
    `type`          ENUM('assignment','quiz','live_class','project') NOT NULL,
    `entity_id`     BIGINT UNSIGNED  NOT NULL,
    `title`         VARCHAR(255)     NOT NULL,
    `due_at`        DATETIME         NOT NULL,
    `is_submitted`  TINYINT(1)       NOT NULL DEFAULT 0,
    `is_overdue`    TINYINT(1)       NOT NULL DEFAULT 0,
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lms_dl_user`     (`lms_user_id`),
    KEY `idx_lms_dl_due`      (`due_at`),
    KEY `idx_lms_dl_course`   (`course_id`),
    CONSTRAINT `fk_lms_dl_user`   FOREIGN KEY (`lms_user_id`) REFERENCES `lms_users`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_lms_dl_course` FOREIGN KEY (`course_id`)   REFERENCES `lms_courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Dashboard Widgets (user preference) ───────────────────────
CREATE TABLE IF NOT EXISTS `lms_dashboard_prefs` (
    `lms_user_id`   BIGINT UNSIGNED  NOT NULL,
    `widgets`       JSON             NULL,
    `layout`        VARCHAR(20)      NOT NULL DEFAULT 'default',
    `updated_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`lms_user_id`),
    CONSTRAINT `fk_lms_dash_user` FOREIGN KEY (`lms_user_id`) REFERENCES `lms_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
