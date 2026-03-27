-- ============================================================
-- Grading Schemas — Configurable Internal/External Mark Structure
-- ============================================================

-- Level 1: Exam Scheme (equivalent to "Mark Definition")
CREATE TABLE IF NOT EXISTS `grading_schemas` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `code`           VARCHAR(50)  NOT NULL,
    `name`           VARCHAR(150) NOT NULL,
    `min_mark`       DECIMAL(6,2) NOT NULL DEFAULT 50.00,
    `max_mark`       DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    `is_embedded`    TINYINT(1)   NOT NULL DEFAULT 0,
    `max_ratio_mark` TINYINT(1)   NOT NULL DEFAULT 0,
    `status`         ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_by`     BIGINT UNSIGNED NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_gs_inst_code` (`institution_id`, `code`),
    KEY `idx_gs_inst` (`institution_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Level 2: Scheme Category (equivalent to "Mark Category")
CREATE TABLE IF NOT EXISTS `grading_schema_categories` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `schema_id`      BIGINT UNSIGNED NOT NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `code`           VARCHAR(50)  NOT NULL,
    `name`           VARCHAR(150) NOT NULL,
    `sort_order`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gsc_schema` (`schema_id`),
    FOREIGN KEY (`schema_id`) REFERENCES `grading_schemas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Level 3: Mark Component  (equivalent to "Mark Component" — Internal/External splits)
CREATE TABLE IF NOT EXISTS `grading_mark_components` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `schema_id`      BIGINT UNSIGNED NOT NULL,
    `category_id`    BIGINT UNSIGNED NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `component_type` ENUM('internal','external','practical','viva','project','other') NOT NULL DEFAULT 'internal',
    `code`           VARCHAR(50)  NOT NULL,
    `name`           VARCHAR(150) NOT NULL,
    `min_mark`       DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `max_mark`       DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    `sort_order`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gmc_schema` (`schema_id`),
    FOREIGN KEY (`schema_id`)   REFERENCES `grading_schemas`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `grading_schema_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Level 4: Sub Component  (equivalent to "Sub Component" — items within a component)
CREATE TABLE IF NOT EXISTS `grading_sub_components` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `component_id`   BIGINT UNSIGNED NOT NULL,
    `schema_id`      BIGINT UNSIGNED NOT NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `code`           VARCHAR(50)  NOT NULL,
    `name`           VARCHAR(150) NOT NULL,
    `max_mark`       DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `sort_order`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gsc_comp` (`component_id`),
    FOREIGN KEY (`component_id`) REFERENCES `grading_mark_components`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grade Rules: Letter grade bands per scheme (O, A+, A, B+, B, C, F …)
CREATE TABLE IF NOT EXISTS `grading_grade_rules` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `schema_id`      BIGINT UNSIGNED NOT NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `grade_label`    VARCHAR(10)  NOT NULL,
    `grade_point`    DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `min_percentage` DECIMAL(5,2) NOT NULL,
    `max_percentage` DECIMAL(5,2) NOT NULL,
    `description`    VARCHAR(100) NULL,
    `is_pass`        TINYINT(1)   NOT NULL DEFAULT 1,
    `sort_order`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ggr_schema` (`schema_id`),
    FOREIGN KEY (`schema_id`) REFERENCES `grading_schemas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Extend existing tables ────────────────────────────────────────
-- Link assessments to a grading schema + support internal/external mode
ALTER TABLE `academic_assessments`
    ADD COLUMN `grading_schema_id`   BIGINT UNSIGNED NULL AFTER `weightage`,
    ADD COLUMN `evaluation_mode`     ENUM('direct','internal_external') NOT NULL DEFAULT 'direct' AFTER `grading_schema_id`,
    ADD COLUMN `internal_max_marks`  DECIMAL(6,2) NULL AFTER `evaluation_mode`,
    ADD COLUMN `external_max_marks`  DECIMAL(6,2) NULL AFTER `internal_max_marks`,
    ADD COLUMN `internal_min_marks`  DECIMAL(6,2) NULL AFTER `external_max_marks`,
    ADD COLUMN `external_min_marks`  DECIMAL(6,2) NULL AFTER `internal_min_marks`;

-- Store computed grade per mark record
ALTER TABLE `academic_assessment_marks`
    ADD COLUMN `internal_marks`          DECIMAL(6,2) NULL AFTER `marks_obtained`,
    ADD COLUMN `external_marks`          DECIMAL(6,2) NULL AFTER `internal_marks`,
    ADD COLUMN `consolidated_marks`      DECIMAL(6,2) NULL AFTER `external_marks`,
    ADD COLUMN `consolidated_percentage` DECIMAL(5,2) NULL AFTER `consolidated_marks`,
    ADD COLUMN `grade_label`             VARCHAR(10)  NULL AFTER `consolidated_percentage`,
    ADD COLUMN `grade_point`             DECIMAL(5,2) NULL AFTER `grade_label`,
    ADD COLUMN `is_pass`                 TINYINT(1)   NULL AFTER `grade_point`;

-- Link subjects to a grading schema
ALTER TABLE `subjects`
    ADD COLUMN `grading_schema_id` BIGINT UNSIGNED NULL;
