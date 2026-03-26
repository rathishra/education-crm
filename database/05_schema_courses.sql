-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 5: Courses & Batch Management
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 20. COURSES
-- ============================================================
CREATE TABLE `courses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `department_id` BIGINT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `short_name` VARCHAR(50) DEFAULT NULL,
  `degree_type` ENUM('diploma','ug','pg','phd','certificate','other') NOT NULL,
  `duration_years` DECIMAL(3,1) NOT NULL COMMENT 'e.g. 4.0, 2.5',
  `total_semesters` TINYINT UNSIGNED DEFAULT NULL,
  `total_seats` INT UNSIGNED DEFAULT NULL,
  `eligibility` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_course_inst_code` (`institution_id`, `code`),
  KEY `idx_course_dept` (`department_id`),
  KEY `idx_course_type` (`degree_type`),
  KEY `idx_course_status` (`status`),
  KEY `idx_course_deleted` (`deleted_at`),
  CONSTRAINT `fk_course_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_course_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 21. BATCHES
-- ============================================================
CREATE TABLE `batches` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `course_id` BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `section` VARCHAR(10) DEFAULT NULL,
  `semester` TINYINT UNSIGNED DEFAULT NULL,
  `max_students` INT UNSIGNED DEFAULT NULL,
  `current_strength` INT UNSIGNED NOT NULL DEFAULT 0,
  `start_date` DATE DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `class_timing` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_batch_inst_code` (`institution_id`, `code`),
  KEY `idx_batch_course` (`course_id`),
  KEY `idx_batch_ay` (`academic_year_id`),
  KEY `idx_batch_status` (`status`),
  CONSTRAINT `fk_batch_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_batch_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_batch_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 22. FACULTY (extends users with teaching info)
-- ============================================================
CREATE TABLE `faculty_profiles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `department_id` BIGINT UNSIGNED DEFAULT NULL,
  `designation` VARCHAR(100) DEFAULT NULL,
  `specialization` VARCHAR(255) DEFAULT NULL,
  `qualification` VARCHAR(255) DEFAULT NULL,
  `experience_years` TINYINT UNSIGNED DEFAULT NULL,
  `joining_date` DATE DEFAULT NULL,
  `status` ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_faculty_user_inst` (`user_id`, `institution_id`),
  KEY `idx_faculty_inst` (`institution_id`),
  KEY `idx_faculty_dept` (`department_id`),
  CONSTRAINT `fk_faculty_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_faculty_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_faculty_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 23. BATCH-FACULTY ASSIGNMENT
-- ============================================================
CREATE TABLE `batch_faculty` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `batch_id` BIGINT UNSIGNED NOT NULL,
  `faculty_id` BIGINT UNSIGNED NOT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bf` (`batch_id`, `faculty_id`),
  KEY `idx_bf_faculty` (`faculty_id`),
  CONSTRAINT `fk_bf_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_bf_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty_profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign key to leads for course_interested_id
ALTER TABLE `leads`
  ADD CONSTRAINT `fk_lead_course` FOREIGN KEY (`course_interested_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Add foreign key to enquiries for course_interested_id
ALTER TABLE `enquiries`
  ADD CONSTRAINT `fk_enq_course` FOREIGN KEY (`course_interested_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
