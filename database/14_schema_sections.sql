-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 14: Sections, Batch Subjects, Student-Section mapping
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- SECTIONS (subdivisions of a batch, e.g. Section A, B, C)
-- ============================================================
CREATE TABLE IF NOT EXISTS `sections` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `batch_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL COMMENT 'e.g. Section A',
  `code` VARCHAR(20) DEFAULT NULL COMMENT 'Short code e.g. A',
  `capacity` INT UNSIGNED DEFAULT 60,
  `room_number` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_section_batch` (`batch_id`),
  KEY `idx_section_inst` (`institution_id`),
  KEY `idx_section_deleted` (`deleted_at`),
  CONSTRAINT `fk_section_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_section_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BATCH SUBJECTS (regulation: subjects assigned to batches)
-- ============================================================
CREATE TABLE IF NOT EXISTS `batch_subjects` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_id` BIGINT UNSIGNED NOT NULL,
  `subject_id` BIGINT UNSIGNED NOT NULL,
  `semester` TINYINT UNSIGNED DEFAULT NULL COMMENT 'For semester-based assignment',
  `is_elective` TINYINT(1) NOT NULL DEFAULT 0,
  `teaching_hours_per_week` TINYINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_batch_subject` (`batch_id`, `subject_id`),
  KEY `idx_bs_batch` (`batch_id`),
  KEY `idx_bs_subject` (`subject_id`),
  CONSTRAINT `fk_bs_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bs_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ADD section_id to students
-- ============================================================
ALTER TABLE `students`
  ADD COLUMN IF NOT EXISTS `section_id` BIGINT UNSIGNED DEFAULT NULL AFTER `batch_id`,
  ADD COLUMN IF NOT EXISTS `mobile_number` VARCHAR(20) DEFAULT NULL AFTER `phone`,
  ADD COLUMN IF NOT EXISTS `admission_number` VARCHAR(50) DEFAULT NULL AFTER `student_id_number`,
  ADD COLUMN IF NOT EXISTS `organization_id` BIGINT UNSIGNED DEFAULT NULL AFTER `institution_id`,
  ADD COLUMN IF NOT EXISTS `student_type` ENUM('day_scholar','hosteller') DEFAULT 'day_scholar' AFTER `admission_type`,
  ADD COLUMN IF NOT EXISTS `notes` TEXT DEFAULT NULL AFTER `lead_id`;

-- Add FK for section_id separately (in case column already exists)
ALTER TABLE `students`
  ADD CONSTRAINT IF NOT EXISTS `fk_student_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- ADD section_id to attendances
-- ============================================================
ALTER TABLE `attendances`
  ADD COLUMN IF NOT EXISTS `section_id` BIGINT UNSIGNED DEFAULT NULL AFTER `batch_id`;

SET FOREIGN_KEY_CHECKS = 1;
