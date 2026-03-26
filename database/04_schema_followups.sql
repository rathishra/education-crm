-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 4: Follow-ups & Task Management
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 18. FOLLOW-UPS
-- ============================================================
CREATE TABLE `followups` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `lead_id` BIGINT UNSIGNED DEFAULT NULL,
  `student_id` BIGINT UNSIGNED DEFAULT NULL,
  `assigned_to` BIGINT UNSIGNED NOT NULL COMMENT 'Responsible user',
  `type` ENUM('call','email','sms','whatsapp','meeting','visit','other') NOT NULL DEFAULT 'call',
  `subject` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `scheduled_at` DATETIME NOT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `outcome` TEXT DEFAULT NULL,
  `status` ENUM('pending','completed','missed','rescheduled','cancelled') NOT NULL DEFAULT 'pending',
  `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `reminder_at` DATETIME DEFAULT NULL,
  `reminder_sent` TINYINT(1) NOT NULL DEFAULT 0,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fu_inst` (`institution_id`),
  KEY `idx_fu_lead` (`lead_id`),
  KEY `idx_fu_student` (`student_id`),
  KEY `idx_fu_assigned` (`assigned_to`),
  KEY `idx_fu_scheduled` (`scheduled_at`),
  KEY `idx_fu_status` (`status`),
  KEY `idx_fu_reminder` (`reminder_at`, `reminder_sent`),
  CONSTRAINT `fk_fu_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fu_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fu_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fu_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 19. TASKS
-- ============================================================
CREATE TABLE `tasks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `assigned_to` BIGINT UNSIGNED NOT NULL,
  `related_type` VARCHAR(50) DEFAULT NULL COMMENT 'lead, student, admission, etc.',
  `related_id` BIGINT UNSIGNED DEFAULT NULL,
  `due_date` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `status` ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task_inst` (`institution_id`),
  KEY `idx_task_assigned` (`assigned_to`),
  KEY `idx_task_status` (`status`),
  KEY `idx_task_due` (`due_date`),
  KEY `idx_task_related` (`related_type`, `related_id`),
  CONSTRAINT `fk_task_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_task_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_task_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
