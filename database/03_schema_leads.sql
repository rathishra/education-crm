-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 3: Lead Management & Enquiries
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 13. LEAD SOURCES
-- ============================================================
CREATE TABLE `lead_sources` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ls_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 14. LEAD STATUSES
-- ============================================================
CREATE TABLE `lead_statuses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `color` VARCHAR(7) DEFAULT '#6c757d',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `is_won` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Marks conversion stage',
  `is_lost` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Marks lost/rejected stage',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_lst_slug` (`slug`),
  KEY `idx_lst_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 15. LEADS
-- ============================================================
CREATE TABLE `leads` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `lead_number` VARCHAR(30) NOT NULL COMMENT 'Auto-generated: LD-INST-YYYYMMDD-XXXX',
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `alternate_phone` VARCHAR(20) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `gender` ENUM('male','female','other') DEFAULT NULL,
  `address_line1` VARCHAR(255) DEFAULT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',

  -- Academic info
  `qualification` VARCHAR(255) DEFAULT NULL,
  `percentage` DECIMAL(5,2) DEFAULT NULL,
  `passing_year` YEAR DEFAULT NULL,
  `school_college` VARCHAR(255) DEFAULT NULL,

  -- Lead meta
  `lead_source_id` BIGINT UNSIGNED DEFAULT NULL,
  `lead_status_id` BIGINT UNSIGNED NOT NULL,
  `assigned_to` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Counselor user ID',
  `course_interested_id` BIGINT UNSIGNED DEFAULT NULL,
  `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `notes` TEXT DEFAULT NULL,
  `enquiry_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'If converted from enquiry',

  -- Tracking
  `first_contacted_at` TIMESTAMP NULL DEFAULT NULL,
  `last_contacted_at` TIMESTAMP NULL DEFAULT NULL,
  `converted_at` TIMESTAMP NULL DEFAULT NULL,
  `lost_reason` VARCHAR(500) DEFAULT NULL,
  `is_duplicate` TINYINT(1) NOT NULL DEFAULT 0,
  `duplicate_of` BIGINT UNSIGNED DEFAULT NULL,

  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_lead_number` (`lead_number`),
  KEY `idx_lead_inst` (`institution_id`),
  KEY `idx_lead_phone` (`phone`),
  KEY `idx_lead_email` (`email`),
  KEY `idx_lead_status` (`lead_status_id`),
  KEY `idx_lead_source` (`lead_source_id`),
  KEY `idx_lead_assigned` (`assigned_to`),
  KEY `idx_lead_course` (`course_interested_id`),
  KEY `idx_lead_priority` (`priority`),
  KEY `idx_lead_created` (`created_at`),
  KEY `idx_lead_deleted` (`deleted_at`),
  KEY `idx_lead_dup` (`duplicate_of`),
  CONSTRAINT `fk_lead_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_lead_source` FOREIGN KEY (`lead_source_id`) REFERENCES `lead_sources` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_lead_status` FOREIGN KEY (`lead_status_id`) REFERENCES `lead_statuses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_lead_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_lead_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_lead_dup` FOREIGN KEY (`duplicate_of`) REFERENCES `leads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 16. LEAD ACTIVITIES (Timeline)
-- ============================================================
CREATE TABLE `lead_activities` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `type` ENUM('note','call','email','sms','whatsapp','meeting','status_change','assignment','document','system') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `metadata` JSON DEFAULT NULL COMMENT 'Extra data like old_status, new_status, etc.',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_la_lead` (`lead_id`),
  KEY `idx_la_user` (`user_id`),
  KEY `idx_la_type` (`type`),
  KEY `idx_la_date` (`created_at`),
  CONSTRAINT `fk_la_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_la_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 17. ENQUIRIES
-- ============================================================
CREATE TABLE `enquiries` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `enquiry_number` VARCHAR(30) NOT NULL COMMENT 'Auto-generated: ENQ-INST-YYYYMMDD-XXXX',
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `course_interested_id` BIGINT UNSIGNED DEFAULT NULL,
  `source` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
  `lead_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'If converted to lead',
  `assigned_to` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_enq_number` (`enquiry_number`),
  KEY `idx_enq_inst` (`institution_id`),
  KEY `idx_enq_status` (`status`),
  KEY `idx_enq_phone` (`phone`),
  KEY `idx_enq_lead` (`lead_id`),
  CONSTRAINT `fk_enq_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_enq_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_enq_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
