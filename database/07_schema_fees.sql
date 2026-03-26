-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 7: Fee & Finance Module
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 28. FEE STRUCTURES
-- ============================================================
CREATE TABLE `fee_structures` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `course_id` BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(255) NOT NULL COMMENT 'e.g. B.Tech CSE 2025-26 Fee',
  `admission_type` ENUM('regular','lateral','management','scholarship','other') DEFAULT 'regular',
  `total_amount` DECIMAL(12,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'INR',
  `installments_allowed` TINYINT(1) NOT NULL DEFAULT 1,
  `max_installments` TINYINT UNSIGNED DEFAULT 12,
  `late_fee_per_day` DECIMAL(8,2) DEFAULT 0.00,
  `grace_period_days` TINYINT UNSIGNED DEFAULT 7,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fs_inst` (`institution_id`),
  KEY `idx_fs_course` (`course_id`),
  KEY `idx_fs_ay` (`academic_year_id`),
  KEY `idx_fs_status` (`status`),
  CONSTRAINT `fk_fs_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fs_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_fs_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 29. FEE COMPONENTS (Tuition, Lab, Library, etc.)
-- ============================================================
CREATE TABLE `fee_components` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fee_structure_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL COMMENT 'Tuition, Lab Fee, Library, Hostel, etc.',
  `amount` DECIMAL(12,2) NOT NULL,
  `is_optional` TINYINT(1) NOT NULL DEFAULT 0,
  `is_refundable` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fc_struct` (`fee_structure_id`),
  CONSTRAINT `fk_fc_struct` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 30. INSTALLMENT PLANS
-- ============================================================
CREATE TABLE `installment_plans` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fee_structure_id` BIGINT UNSIGNED NOT NULL,
  `installment_number` TINYINT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL COMMENT 'e.g. 1st Installment, Semester 2, etc.',
  `amount` DECIMAL(12,2) NOT NULL,
  `due_date` DATE NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ip_struct_num` (`fee_structure_id`, `installment_number`),
  CONSTRAINT `fk_ip_struct` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 31. STUDENT FEE ASSIGNMENTS
-- ============================================================
CREATE TABLE `student_fees` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `fee_structure_id` BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED DEFAULT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_reason` VARCHAR(255) DEFAULT NULL,
  `scholarship_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `scholarship_reference` VARCHAR(255) DEFAULT NULL,
  `net_amount` DECIMAL(12,2) NOT NULL COMMENT 'total - discount - scholarship',
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `balance_amount` DECIMAL(12,2) NOT NULL COMMENT 'net - paid',
  `late_fee_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pending','partial','paid','overdue','waived') NOT NULL DEFAULT 'pending',
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sf_inst` (`institution_id`),
  KEY `idx_sf_student` (`student_id`),
  KEY `idx_sf_struct` (`fee_structure_id`),
  KEY `idx_sf_status` (`status`),
  KEY `idx_sf_ay` (`academic_year_id`),
  CONSTRAINT `fk_sf_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sf_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sf_struct` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sf_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 32. STUDENT INSTALLMENTS (per student tracking)
-- ============================================================
CREATE TABLE `student_installments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_fee_id` BIGINT UNSIGNED NOT NULL,
  `installment_plan_id` BIGINT UNSIGNED DEFAULT NULL,
  `installment_number` TINYINT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `due_date` DATE NOT NULL,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_date` DATE DEFAULT NULL,
  `late_fee` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('upcoming','due','paid','overdue','partial','waived') NOT NULL DEFAULT 'upcoming',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_si_fee` (`student_fee_id`),
  KEY `idx_si_due` (`due_date`),
  KEY `idx_si_status` (`status`),
  CONSTRAINT `fk_si_fee` FOREIGN KEY (`student_fee_id`) REFERENCES `student_fees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_si_plan` FOREIGN KEY (`installment_plan_id`) REFERENCES `installment_plans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 33. PAYMENTS
-- ============================================================
CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `student_fee_id` BIGINT UNSIGNED NOT NULL,
  `student_installment_id` BIGINT UNSIGNED DEFAULT NULL,
  `receipt_number` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_method` ENUM('cash','cheque','dd','online','upi','bank_transfer','card','other') NOT NULL,
  `transaction_reference` VARCHAR(255) DEFAULT NULL,
  `cheque_number` VARCHAR(50) DEFAULT NULL,
  `cheque_date` DATE DEFAULT NULL,
  `bank_name` VARCHAR(100) DEFAULT NULL,
  `remarks` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('success','pending','failed','refunded','cancelled') NOT NULL DEFAULT 'success',
  `refund_amount` DECIMAL(12,2) DEFAULT NULL,
  `refund_date` DATE DEFAULT NULL,
  `refund_reason` VARCHAR(500) DEFAULT NULL,
  `collected_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pay_receipt` (`institution_id`, `receipt_number`),
  KEY `idx_pay_student` (`student_id`),
  KEY `idx_pay_fee` (`student_fee_id`),
  KEY `idx_pay_date` (`payment_date`),
  KEY `idx_pay_method` (`payment_method`),
  KEY `idx_pay_status` (`status`),
  CONSTRAINT `fk_pay_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pay_fee` FOREIGN KEY (`student_fee_id`) REFERENCES `student_fees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_pay_installment` FOREIGN KEY (`student_installment_id`) REFERENCES `student_installments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_pay_collected` FOREIGN KEY (`collected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
