-- ============================================================
-- Migration 43: Course Types Module
-- ============================================================

CREATE TABLE IF NOT EXISTS `course_types` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id`    BIGINT UNSIGNED NOT NULL,
  `code`              VARCHAR(50)  NOT NULL,
  `description`       VARCHAR(255) NOT NULL,
  `short_description` VARCHAR(255) DEFAULT NULL,
  `course_category`   ENUM('certificate','ug','pg','school','research_scholar','mphil','phd') NOT NULL DEFAULT 'ug',
  `degree_type`       ENUM('full_time','part_time') NOT NULL DEFAULT 'full_time',
  `duration`          TINYINT UNSIGNED NOT NULL DEFAULT 4,
  `duration_unit`     ENUM('year','month') NOT NULL DEFAULT 'year',
  `no_of_semester`    TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `status`            ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_by`        BIGINT UNSIGNED DEFAULT NULL,
  `created_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_course_type_code` (`institution_id`, `code`),
  KEY `idx_ct_institution` (`institution_id`),
  KEY `idx_ct_status` (`status`),
  CONSTRAINT `fk_ct_institution` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Year breakdown per course type
CREATE TABLE IF NOT EXISTS `course_type_years` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_type_id` BIGINT UNSIGNED NOT NULL,
  `year_code`      TINYINT UNSIGNED NOT NULL COMMENT 'Year number: 1, 2, 3...',
  `no_of_semester` TINYINT UNSIGNED NOT NULL DEFAULT 2,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cty_year` (`course_type_id`, `year_code`),
  CONSTRAINT `fk_cty_ct` FOREIGN KEY (`course_type_id`) REFERENCES `course_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link courses table to course_types (optional FK)
ALTER TABLE `courses`
  ADD COLUMN IF NOT EXISTS `course_type_id` BIGINT UNSIGNED DEFAULT NULL AFTER `department_id`,
  ADD INDEX IF NOT EXISTS `idx_course_ct` (`course_type_id`);
