-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 9: System Settings & Configuration
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 39. SYSTEM SETTINGS
-- ============================================================
CREATE TABLE `settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL = global setting',
  `group_name` VARCHAR(50) NOT NULL COMMENT 'general, email, sms, fee, lead, etc.',
  `key_name` VARCHAR(100) NOT NULL,
  `value` TEXT DEFAULT NULL,
  `type` ENUM('string','integer','boolean','json','text') NOT NULL DEFAULT 'string',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_setting` (`institution_id`, `group_name`, `key_name`),
  KEY `idx_setting_group` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 40. FILE UPLOADS (Central tracking)
-- ============================================================
CREATE TABLE `uploads` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED DEFAULT NULL,
  `uploaded_by` BIGINT UNSIGNED DEFAULT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `stored_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `disk` VARCHAR(50) NOT NULL DEFAULT 'local',
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `size` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_upload_inst` (`institution_id`),
  KEY `idx_upload_user` (`uploaded_by`),
  CONSTRAINT `fk_upload_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_upload_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
