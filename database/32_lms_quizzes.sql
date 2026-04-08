-- ============================================================
-- MODULE 6: Enterprise LMS — Quiz Module
-- ============================================================
USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ── Quizzes ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_quizzes` (
    `id`                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `course_id`         BIGINT UNSIGNED  NOT NULL,
    `lesson_id`         BIGINT UNSIGNED  NULL,
    `institution_id`    BIGINT UNSIGNED  NOT NULL,
    `created_by`        BIGINT UNSIGNED  NOT NULL,
    `title`             VARCHAR(255)     NOT NULL,
    `description`       TEXT             NULL,
    `time_limit_mins`   SMALLINT UNSIGNED NULL,     -- NULL = no limit
    `attempts_allowed`  TINYINT UNSIGNED NOT NULL DEFAULT 1,  -- 0 = unlimited
    `pass_percentage`   TINYINT UNSIGNED NOT NULL DEFAULT 60,
    `shuffle_questions` TINYINT(1)       NOT NULL DEFAULT 0,
    `shuffle_options`   TINYINT(1)       NOT NULL DEFAULT 0,
    `show_result`       ENUM('immediately','after_due','never') NOT NULL DEFAULT 'immediately',
    `show_correct`      TINYINT(1)       NOT NULL DEFAULT 1,
    `is_published`      TINYINT(1)       NOT NULL DEFAULT 1,
    `due_at`            DATETIME         NULL,
    `created_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`        DATETIME         NULL,
    PRIMARY KEY (`id`),
    KEY `idx_lms_quiz_course` (`course_id`),
    KEY `idx_lms_quiz_inst`   (`institution_id`),
    CONSTRAINT `fk_lms_quiz_course` FOREIGN KEY (`course_id`) REFERENCES `lms_courses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_quiz_by`     FOREIGN KEY (`created_by`) REFERENCES `lms_users`(`id`)  ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Questions ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_quiz_questions` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `quiz_id`       BIGINT UNSIGNED  NOT NULL,
    `type`          ENUM('mcq','multi','true_false','short','fill_blank','match') NOT NULL DEFAULT 'mcq',
    `question`      TEXT             NOT NULL,
    `explanation`   TEXT             NULL,      -- shown after answer
    `points`        TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `sort_order`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `media_url`     VARCHAR(500)     NULL,      -- optional image/video
    `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lms_qq_quiz` (`quiz_id`),
    CONSTRAINT `fk_lms_qq_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `lms_quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Options (for MCQ / multi / true_false / match) ────────────
CREATE TABLE IF NOT EXISTS `lms_quiz_options` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `question_id`   BIGINT UNSIGNED  NOT NULL,
    `option_text`   TEXT             NOT NULL,
    `is_correct`    TINYINT(1)       NOT NULL DEFAULT 0,
    `match_pair`    VARCHAR(255)     NULL,      -- for match questions
    `sort_order`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_lms_qo_q` (`question_id`),
    CONSTRAINT `fk_lms_qo_question` FOREIGN KEY (`question_id`) REFERENCES `lms_quiz_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Attempts ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_quiz_attempts` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `quiz_id`       BIGINT UNSIGNED  NOT NULL,
    `lms_user_id`   BIGINT UNSIGNED  NOT NULL,
    `attempt`       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `status`        ENUM('in_progress','submitted','graded') NOT NULL DEFAULT 'in_progress',
    `score`         DECIMAL(6,2)     NULL,
    `max_score`     DECIMAL(6,2)     NULL,
    `percentage`    DECIMAL(5,2)     NULL,
    `passed`        TINYINT(1)       NULL,
    `time_taken_s`  INT UNSIGNED     NULL,
    `started_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `submitted_at`  TIMESTAMP        NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_qa` (`quiz_id`, `lms_user_id`, `attempt`),
    KEY `idx_lms_qa_user`  (`lms_user_id`),
    KEY `idx_lms_qa_quiz`  (`quiz_id`),
    CONSTRAINT `fk_lms_qa_quiz` FOREIGN KEY (`quiz_id`)     REFERENCES `lms_quizzes`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_lms_qa_user` FOREIGN KEY (`lms_user_id`) REFERENCES `lms_users`(`id`)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Answers ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lms_quiz_answers` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `attempt_id`    BIGINT UNSIGNED  NOT NULL,
    `question_id`   BIGINT UNSIGNED  NOT NULL,
    `option_ids`    JSON             NULL,      -- selected option IDs (array)
    `text_answer`   TEXT             NULL,      -- for short/fill_blank
    `is_correct`    TINYINT(1)       NULL,      -- null until graded
    `points_earned` DECIMAL(5,2)     NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_lms_ans` (`attempt_id`, `question_id`),
    KEY `idx_lms_ans_attempt` (`attempt_id`),
    CONSTRAINT `fk_lms_ans_attempt`  FOREIGN KEY (`attempt_id`)  REFERENCES `lms_quiz_attempts`(`id`)  ON DELETE CASCADE,
    CONSTRAINT `fk_lms_ans_question` FOREIGN KEY (`question_id`) REFERENCES `lms_quiz_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
