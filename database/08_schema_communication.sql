-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 8: Communication & Notifications
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 34. COMMUNICATION TEMPLATES
-- ============================================================
CREATE TABLE `communication_templates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL = global template',
  `name` VARCHAR(255) NOT NULL,
  `channel` ENUM('email','sms','whatsapp') NOT NULL,
  `subject` VARCHAR(500) DEFAULT NULL COMMENT 'For email',
  `body` TEXT NOT NULL,
  `variables` JSON DEFAULT NULL COMMENT '["{{name}}","{{course}}",...]',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ct_inst` (`institution_id`),
  KEY `idx_ct_channel` (`channel`),
  CONSTRAINT `fk_ct_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 35. COMMUNICATION LOG
-- ============================================================
CREATE TABLE `communications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `channel` ENUM('email','sms','whatsapp') NOT NULL,
  `recipient_type` VARCHAR(50) NOT NULL COMMENT 'lead, student, bulk',
  `recipient_id` BIGINT UNSIGNED DEFAULT NULL,
  `recipient_contact` VARCHAR(255) NOT NULL COMMENT 'Email or phone',
  `template_id` BIGINT UNSIGNED DEFAULT NULL,
  `subject` VARCHAR(500) DEFAULT NULL,
  `body` TEXT NOT NULL,
  `status` ENUM('queued','sent','delivered','failed','bounced') NOT NULL DEFAULT 'queued',
  `sent_at` TIMESTAMP NULL DEFAULT NULL,
  `delivered_at` TIMESTAMP NULL DEFAULT NULL,
  `failed_reason` VARCHAR(500) DEFAULT NULL,
  `external_id` VARCHAR(255) DEFAULT NULL COMMENT 'Provider message ID',
  `metadata` JSON DEFAULT NULL,
  `sent_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comm_inst` (`institution_id`),
  KEY `idx_comm_channel` (`channel`),
  KEY `idx_comm_recipient` (`recipient_type`, `recipient_id`),
  KEY `idx_comm_status` (`status`),
  KEY `idx_comm_date` (`created_at`),
  CONSTRAINT `fk_comm_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_comm_template` FOREIGN KEY (`template_id`) REFERENCES `communication_templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_comm_sent` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 36. BULK MESSAGING CAMPAIGNS
-- ============================================================
CREATE TABLE `bulk_campaigns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `channel` ENUM('email','sms','whatsapp') NOT NULL,
  `template_id` BIGINT UNSIGNED DEFAULT NULL,
  `target_type` ENUM('leads','students','custom') NOT NULL,
  `target_filters` JSON DEFAULT NULL COMMENT 'Filter criteria for recipients',
  `total_recipients` INT UNSIGNED NOT NULL DEFAULT 0,
  `sent_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `delivered_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `failed_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `status` ENUM('draft','scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'draft',
  `scheduled_at` DATETIME DEFAULT NULL,
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_bc_inst` (`institution_id`),
  KEY `idx_bc_status` (`status`),
  CONSTRAINT `fk_bc_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_bc_template` FOREIGN KEY (`template_id`) REFERENCES `communication_templates` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_bc_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 37. NOTIFICATIONS (In-app)
-- ============================================================
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `institution_id` BIGINT UNSIGNED DEFAULT NULL,
  `type` VARCHAR(100) NOT NULL COMMENT 'lead_assigned, followup_reminder, fee_due, etc.',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `action_url` VARCHAR(500) DEFAULT NULL,
  `data` JSON DEFAULT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_inst` (`institution_id`),
  KEY `idx_notif_read` (`is_read`),
  KEY `idx_notif_type` (`type`),
  KEY `idx_notif_date` (`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 38. COMMUNICATION SETTINGS (per institution)
-- ============================================================
CREATE TABLE `communication_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `channel` ENUM('email','sms','whatsapp') NOT NULL,
  `provider` VARCHAR(50) NOT NULL COMMENT 'smtp, mailgun, twilio, msg91, etc.',
  `config` JSON NOT NULL COMMENT 'Encrypted credentials and settings',
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cs_inst_channel` (`institution_id`, `channel`),
  CONSTRAINT `fk_cs_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
