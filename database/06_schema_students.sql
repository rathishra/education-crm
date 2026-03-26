-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 6: Students & Admissions
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 24. STUDENTS
-- ============================================================
CREATE TABLE `students` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `admission_id` BIGINT UNSIGNED DEFAULT NULL,
  `student_id_number` VARCHAR(50) NOT NULL COMMENT 'Roll/Reg number',
  `roll_number` VARCHAR(50) DEFAULT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `alternate_phone` VARCHAR(20) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `gender` ENUM('male','female','other') DEFAULT NULL,
  `blood_group` VARCHAR(5) DEFAULT NULL,
  `nationality` VARCHAR(50) DEFAULT 'Indian',
  `religion` VARCHAR(50) DEFAULT NULL,
  `caste` VARCHAR(50) DEFAULT NULL,
  `category` ENUM('general','obc','sc','st','ews','other') DEFAULT NULL,
  `aadhar_number` VARCHAR(12) DEFAULT NULL,
  `photo` VARCHAR(500) DEFAULT NULL,

  -- Address
  `address_line1` VARCHAR(255) DEFAULT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',

  -- Permanent address
  `permanent_address_line1` VARCHAR(255) DEFAULT NULL,
  `permanent_address_line2` VARCHAR(255) DEFAULT NULL,
  `permanent_city` VARCHAR(100) DEFAULT NULL,
  `permanent_state` VARCHAR(100) DEFAULT NULL,
  `permanent_pincode` VARCHAR(10) DEFAULT NULL,

  -- Academic
  `course_id` BIGINT UNSIGNED DEFAULT NULL,
  `batch_id` BIGINT UNSIGNED DEFAULT NULL,
  `department_id` BIGINT UNSIGNED DEFAULT NULL,
  `academic_year_id` BIGINT UNSIGNED DEFAULT NULL,
  `current_semester` TINYINT UNSIGNED DEFAULT NULL,
  `admission_date` DATE DEFAULT NULL,
  `admission_type` ENUM('regular','lateral','management','scholarship','other') DEFAULT 'regular',

  -- Previous education
  `previous_qualification` VARCHAR(255) DEFAULT NULL,
  `previous_institution` VARCHAR(255) DEFAULT NULL,
  `previous_percentage` DECIMAL(5,2) DEFAULT NULL,
  `previous_year_of_passing` YEAR DEFAULT NULL,

  -- Parent/Guardian
  `father_name` VARCHAR(255) DEFAULT NULL,
  `father_phone` VARCHAR(20) DEFAULT NULL,
  `father_email` VARCHAR(255) DEFAULT NULL,
  `father_occupation` VARCHAR(100) DEFAULT NULL,
  `mother_name` VARCHAR(255) DEFAULT NULL,
  `mother_phone` VARCHAR(20) DEFAULT NULL,
  `mother_occupation` VARCHAR(100) DEFAULT NULL,
  `guardian_name` VARCHAR(255) DEFAULT NULL,
  `guardian_phone` VARCHAR(20) DEFAULT NULL,
  `guardian_relation` VARCHAR(50) DEFAULT NULL,
  `annual_income` DECIMAL(12,2) DEFAULT NULL,

  `lead_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Source lead',
  `status` ENUM('active','inactive','passed_out','dropped','suspended','transferred') NOT NULL DEFAULT 'active',
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_student_id_num` (`institution_id`, `student_id_number`),
  KEY `idx_student_inst` (`institution_id`),
  KEY `idx_student_course` (`course_id`),
  KEY `idx_student_batch` (`batch_id`),
  KEY `idx_student_dept` (`department_id`),
  KEY `idx_student_ay` (`academic_year_id`),
  KEY `idx_student_status` (`status`),
  KEY `idx_student_lead` (`lead_id`),
  KEY `idx_student_phone` (`phone`),
  KEY `idx_student_email` (`email`),
  KEY `idx_student_name` (`first_name`, `last_name`),
  KEY `idx_student_deleted` (`deleted_at`),
  CONSTRAINT `fk_student_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_student_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_student_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_student_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_student_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_student_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_student_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 25. ADMISSIONS
-- ============================================================
CREATE TABLE `admissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `admission_number` VARCHAR(30) NOT NULL COMMENT 'Auto-generated: ADM-INST-YYYYMMDD-XXXX',
  `lead_id` BIGINT UNSIGNED DEFAULT NULL,
  `student_id` BIGINT UNSIGNED DEFAULT NULL,
  `course_id` BIGINT UNSIGNED NOT NULL,
  `batch_id` BIGINT UNSIGNED DEFAULT NULL,
  `academic_year_id` BIGINT UNSIGNED DEFAULT NULL,

  -- Applicant info (copied from lead at time of application)
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `gender` ENUM('male','female','other') DEFAULT NULL,

  `application_date` DATE NOT NULL,
  `admission_date` DATE DEFAULT NULL,
  `admission_type` ENUM('regular','lateral','management','scholarship','other') DEFAULT 'regular',
  `status` ENUM('applied','under_review','documents_pending','approved','rejected','enrolled','cancelled') NOT NULL DEFAULT 'applied',
  `remarks` TEXT DEFAULT NULL,

  `approved_by` BIGINT UNSIGNED DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_adm_number` (`admission_number`),
  KEY `idx_adm_inst` (`institution_id`),
  KEY `idx_adm_lead` (`lead_id`),
  KEY `idx_adm_student` (`student_id`),
  KEY `idx_adm_course` (`course_id`),
  KEY `idx_adm_status` (`status`),
  KEY `idx_adm_date` (`application_date`),
  CONSTRAINT `fk_adm_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_adm_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_adm_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_adm_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_adm_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_adm_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_adm_approved` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_adm_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add FK from students back to admissions
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_adm` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- 26. DOCUMENTS
-- ============================================================
CREATE TABLE `documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `documentable_type` VARCHAR(50) NOT NULL COMMENT 'student, admission, lead',
  `documentable_id` BIGINT UNSIGNED NOT NULL,
  `document_type` VARCHAR(100) NOT NULL COMMENT 'marksheet, transfer_cert, aadhar, photo, etc.',
  `title` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_size` INT UNSIGNED DEFAULT NULL COMMENT 'In bytes',
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `is_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `verified_by` BIGINT UNSIGNED DEFAULT NULL,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `remarks` VARCHAR(500) DEFAULT NULL,
  `uploaded_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_doc_inst` (`institution_id`),
  KEY `idx_doc_poly` (`documentable_type`, `documentable_id`),
  KEY `idx_doc_type` (`document_type`),
  CONSTRAINT `fk_doc_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_doc_uploaded` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_doc_verified` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 27. STUDENT TIMELINE
-- ============================================================
CREATE TABLE `student_activities` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `type` ENUM('note','attendance','fee_payment','grade','document','status_change','course_change','system') NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `metadata` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sa_student` (`student_id`),
  KEY `idx_sa_type` (`type`),
  KEY `idx_sa_date` (`created_at`),
  CONSTRAINT `fk_sa_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
