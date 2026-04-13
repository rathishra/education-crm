-- ============================================================
-- Migration 49: Timetable Generator System
-- Adds auto-generation engine tables:
--   timetable_generator_configs     — per-institution generation config
--   timetable_subject_requirements  — hours/week per subject per section
--   timetable_generator_runs        — generation run history + result payload
--   timetable_generator_constraints — hard & soft constraint rules
--   timetable_teacher_unavailability — blocked slots per faculty
-- ============================================================

-- ── 1. Generator Configuration ──────────────────────────────
CREATE TABLE IF NOT EXISTS `timetable_generator_configs` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`        BIGINT UNSIGNED NOT NULL,
    `academic_year_id`      BIGINT UNSIGNED NULL,
    `name`                  VARCHAR(120)    NOT NULL DEFAULT 'Default Config',
    -- Working days (bitmask: Mon=1,Tue=2,Wed=4,Thu=8,Fri=16,Sat=32,Sun=64)
    `working_days`          TINYINT UNSIGNED NOT NULL DEFAULT 31,  -- Mon–Fri
    -- Generation preferences
    `max_periods_per_day`   TINYINT UNSIGNED NOT NULL DEFAULT 8,
    `max_consecutive_same`  TINYINT UNSIGNED NOT NULL DEFAULT 2,   -- same subject back-to-back limit
    `avoid_first_last_same` TINYINT(1)      NOT NULL DEFAULT 1,    -- don't schedule same subj first + last
    `distribute_evenly`     TINYINT(1)      NOT NULL DEFAULT 1,    -- spread subject across week
    `lab_block_size`        TINYINT UNSIGNED NOT NULL DEFAULT 2,   -- labs occupy N consecutive periods
    `allow_room_sharing`    TINYINT(1)      NOT NULL DEFAULT 0,
    `balance_faculty_load`  TINYINT(1)      NOT NULL DEFAULT 1,
    `is_active`             TINYINT(1)      NOT NULL DEFAULT 1,
    `created_by`            BIGINT UNSIGNED NULL,
    `created_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tgc_inst` (`institution_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Subject Requirements (hours per week per section) ────
CREATE TABLE IF NOT EXISTS `timetable_subject_requirements` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`   BIGINT UNSIGNED NOT NULL,
    `config_id`        BIGINT UNSIGNED NOT NULL,
    `section_id`       BIGINT UNSIGNED NOT NULL,
    `subject_id`       BIGINT UNSIGNED NOT NULL,
    `faculty_id`       BIGINT UNSIGNED NULL,   -- preferred/assigned faculty
    `periods_per_week` TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `entry_type`       ENUM('lecture','lab','tutorial','activity') NOT NULL DEFAULT 'lecture',
    `preferred_room_id` BIGINT UNSIGNED NULL,
    `room_required`    TINYINT(1)      NOT NULL DEFAULT 0,
    `priority`         TINYINT UNSIGNED NOT NULL DEFAULT 5,  -- 1=highest, 10=lowest
    `notes`            VARCHAR(255)    NULL,
    `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tsr_section_subject_config` (`config_id`, `section_id`, `subject_id`, `entry_type`),
    KEY `idx_tsr_inst`    (`institution_id`),
    KEY `idx_tsr_section` (`section_id`),
    KEY `idx_tsr_subject` (`subject_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`section_id`)    REFERENCES `academic_sections`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`)    REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Faculty Unavailability / Blocked Slots ───────────────
CREATE TABLE IF NOT EXISTS `timetable_teacher_unavailability` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `faculty_id`     BIGINT UNSIGNED NOT NULL,
    `day_of_week`    ENUM('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
    `period_id`      BIGINT UNSIGNED NULL,     -- NULL = entire day blocked
    `reason`         VARCHAR(120)    NULL,
    `effective_from` DATE            NULL,
    `effective_to`   DATE            NULL,
    `is_recurring`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_by`     BIGINT UNSIGNED NULL,
    `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ttu_inst`    (`institution_id`),
    KEY `idx_ttu_faculty` (`faculty_id`, `day_of_week`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Hard & Soft Constraints ───────────────────────────────
CREATE TABLE IF NOT EXISTS `timetable_generator_constraints` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `config_id`      BIGINT UNSIGNED NOT NULL,
    `constraint_key` VARCHAR(80)     NOT NULL,  -- e.g. 'no_faculty_clash', 'max_periods_per_day'
    `constraint_type` ENUM('hard','soft') NOT NULL DEFAULT 'hard',
    `target_type`    ENUM('global','faculty','section','subject','room') NOT NULL DEFAULT 'global',
    `target_id`      BIGINT UNSIGNED NULL,      -- faculty_id / section_id etc. for targeted rules
    `value`          VARCHAR(255)    NULL,       -- JSON-encoded constraint value
    `weight`         TINYINT UNSIGNED NOT NULL DEFAULT 5,  -- soft constraint weight (1–10)
    `is_active`      TINYINT(1)      NOT NULL DEFAULT 1,
    `description`    VARCHAR(255)    NULL,
    `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tgconst_inst`   (`institution_id`),
    KEY `idx_tgconst_config` (`config_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`config_id`)      REFERENCES `timetable_generator_configs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. Generation Run History ────────────────────────────────
CREATE TABLE IF NOT EXISTS `timetable_generator_runs` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`   BIGINT UNSIGNED NOT NULL,
    `config_id`        BIGINT UNSIGNED NOT NULL,
    `academic_year_id` BIGINT UNSIGNED NULL,
    `run_name`         VARCHAR(120)    NOT NULL DEFAULT 'Auto Run',
    `status`           ENUM('pending','running','completed','failed','approved','discarded') NOT NULL DEFAULT 'pending',
    -- Stats
    `total_requirements` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `assigned_count`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `conflict_count`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `score`              DECIMAL(5,2)    NOT NULL DEFAULT 0.00,  -- 0–100 quality score
    `algorithm`          VARCHAR(50)     NOT NULL DEFAULT 'greedy_backtrack',
    `duration_ms`        INT UNSIGNED    NULL,   -- generation time in ms
    -- Payload (JSON-encoded result)
    `result_payload`     LONGTEXT        NULL,   -- JSON: assignments + conflicts
    `conflict_payload`   TEXT            NULL,   -- JSON: unresolved conflicts detail
    `log`                TEXT            NULL,   -- generation log messages
    -- Approval
    `approved_by`        BIGINT UNSIGNED NULL,
    `approved_at`        DATETIME        NULL,
    `sections_scope`     TEXT            NULL,   -- JSON: [section_id, ...] this run covers
    `created_by`         BIGINT UNSIGNED NULL,
    `created_at`         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tgr_inst`   (`institution_id`),
    KEY `idx_tgr_config` (`config_id`),
    KEY `idx_tgr_status` (`status`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`config_id`)      REFERENCES `timetable_generator_configs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
