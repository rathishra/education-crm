-- ============================================================
-- EDU MATRIX — Enterprise Fees Management Module
-- Schema v2: Full Fee Heads, Structure, Collection, Concession, Fine, Refund
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. FEE HEADS MASTER
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_heads` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `head_name`     VARCHAR(100) NOT NULL,
  `head_code`     VARCHAR(30)  NOT NULL,
  `fee_type`      ENUM('one_time','recurring','annual','semester','monthly') NOT NULL DEFAULT 'annual',
  `category`      ENUM('tuition','exam','transport','hostel','library','lab','sports','uniform','development','miscellaneous','other') NOT NULL DEFAULT 'other',
  `is_mandatory`  TINYINT(1) NOT NULL DEFAULT 1,
  `is_refundable` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
  `description`   TEXT NULL,
  `created_by`    BIGINT UNSIGNED NULL,
  `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fh_code_inst` (`institution_id`, `head_code`),
  KEY `idx_fh_inst` (`institution_id`),
  KEY `idx_fh_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. FEE STRUCTURE (extend existing fee_structures)
-- ============================================================
ALTER TABLE `fee_structures`
  ADD COLUMN IF NOT EXISTS `batch_id`     BIGINT UNSIGNED NULL AFTER `course_id`,
  ADD COLUMN IF NOT EXISTS `semester`     TINYINT UNSIGNED NULL AFTER `batch_id`,
  ADD COLUMN IF NOT EXISTS `description`  TEXT NULL AFTER `grace_period_days`,
  ADD COLUMN IF NOT EXISTS `created_by`   BIGINT UNSIGNED NULL AFTER `description`;

-- ============================================================
-- 3. FEE STRUCTURE DETAILS (fee head + amount per structure)
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_structure_details` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `structure_id`    BIGINT UNSIGNED NOT NULL,
  `fee_head_id`     BIGINT UNSIGNED NOT NULL,
  `amount`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `due_date`        DATE NULL,
  `sort_order`      INT NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fsd_struct_head` (`structure_id`, `fee_head_id`),
  KEY `idx_fsd_struct` (`structure_id`),
  KEY `idx_fsd_head`   (`fee_head_id`),
  CONSTRAINT `fk_fsd_struct` FOREIGN KEY (`structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fsd_head`   FOREIGN KEY (`fee_head_id`)  REFERENCES `fee_heads`       (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. STUDENT FEE ASSIGNMENTS (per student, per head, per AY)
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_student_assignments` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id`  BIGINT UNSIGNED NOT NULL,
  `student_id`      BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NULL,
  `structure_id`    BIGINT UNSIGNED NULL,
  `fee_head_id`     BIGINT UNSIGNED NOT NULL,
  `gross_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `concession_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `net_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `fine_amount`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `balance_amount`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `due_date`        DATE NULL,
  `status`          ENUM('pending','partial','paid','overdue','waived') NOT NULL DEFAULT 'pending',
  `remarks`         VARCHAR(255) NULL,
  `created_by`      BIGINT UNSIGNED NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fsa_inst`    (`institution_id`),
  KEY `idx_fsa_student` (`student_id`),
  KEY `idx_fsa_ay`      (`academic_year_id`),
  KEY `idx_fsa_head`    (`fee_head_id`),
  KEY `idx_fsa_status`  (`status`),
  CONSTRAINT `fk_fsa_student` FOREIGN KEY (`student_id`)   REFERENCES `students`      (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_fsa_head`    FOREIGN KEY (`fee_head_id`)  REFERENCES `fee_heads`     (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_fsa_ay`      FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. FEE INSTALLMENTS (per student per assignment)
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_installments` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id`  BIGINT UNSIGNED NOT NULL,
  `assignment_id`   BIGINT UNSIGNED NOT NULL,
  `student_id`      BIGINT UNSIGNED NOT NULL,
  `installment_no`  TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `amount`          DECIMAL(12,2) NOT NULL,
  `due_date`        DATE NOT NULL,
  `paid_amount`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_date`       DATE NULL,
  `fine_amount`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status`          ENUM('pending','paid','partial','overdue','waived') NOT NULL DEFAULT 'pending',
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fi_assignment` (`assignment_id`),
  KEY `idx_fi_student`    (`student_id`),
  KEY `idx_fi_due`        (`due_date`),
  KEY `idx_fi_status`     (`status`),
  CONSTRAINT `fk_fi_assign` FOREIGN KEY (`assignment_id`) REFERENCES `fee_student_assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fi_student` FOREIGN KEY (`student_id`)  REFERENCES `students`                (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. FEE RECEIPTS (payment transactions)
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_receipts` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id`  BIGINT UNSIGNED NOT NULL,
  `student_id`      BIGINT UNSIGNED NOT NULL,
  `receipt_number`  VARCHAR(30) NOT NULL,
  `receipt_date`    DATE NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NULL,
  `total_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `fine_amount`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `net_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_mode`    ENUM('cash','upi','card','netbanking','cheque','dd','online','other') NOT NULL DEFAULT 'cash',
  `reference_number` VARCHAR(100) NULL,
  `cheque_number`   VARCHAR(50) NULL,
  `cheque_date`     DATE NULL,
  `bank_name`       VARCHAR(100) NULL,
  `remarks`         VARCHAR(500) NULL,
  `status`          ENUM('active','cancelled') NOT NULL DEFAULT 'active',
  `cancel_reason`   VARCHAR(255) NULL,
  `cancelled_by`    BIGINT UNSIGNED NULL,
  `cancelled_at`    TIMESTAMP NULL,
  `collected_by`    BIGINT UNSIGNED NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fr_receipt` (`institution_id`, `receipt_number`),
  KEY `idx_fr_student`   (`student_id`),
  KEY `idx_fr_date`      (`receipt_date`),
  KEY `idx_fr_status`    (`status`),
  KEY `idx_fr_inst`      (`institution_id`),
  CONSTRAINT `fk_fr_student`   FOREIGN KEY (`student_id`)   REFERENCES `students`      (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_fr_collected` FOREIGN KEY (`collected_by`) REFERENCES `users`         (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fr_ay`        FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. FEE RECEIPT ITEMS (head-wise breakdown per receipt)
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_receipt_items` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `receipt_id`      BIGINT UNSIGNED NOT NULL,
  `assignment_id`   BIGINT UNSIGNED NULL,
  `fee_head_id`     BIGINT UNSIGNED NOT NULL,
  `amount`          DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `fine_amount`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fri_receipt`    (`receipt_id`),
  KEY `idx_fri_assignment` (`assignment_id`),
  KEY `idx_fri_head`       (`fee_head_id`),
  CONSTRAINT `fk_fri_receipt` FOREIGN KEY (`receipt_id`)    REFERENCES `fee_receipts`           (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fri_assign`  FOREIGN KEY (`assignment_id`) REFERENCES `fee_student_assignments`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fri_head`    FOREIGN KEY (`fee_head_id`)   REFERENCES `fee_heads`              (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. FEE CONCESSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_concessions` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id`   BIGINT UNSIGNED NOT NULL,
  `student_id`       BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NULL,
  `assignment_id`    BIGINT UNSIGNED NULL,
  `fee_head_id`      BIGINT UNSIGNED NULL,
  `concession_name`  VARCHAR(100) NOT NULL,
  `concession_type`  ENUM('percentage','fixed') NOT NULL DEFAULT 'fixed',
  `concession_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `final_discount`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `reason`           VARCHAR(255) NULL,
  `category`         ENUM('scholarship','merit','sports','staff_ward','management','other') NOT NULL DEFAULT 'other',
  `status`           ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by`      BIGINT UNSIGNED NULL,
  `approved_at`      TIMESTAMP NULL,
  `rejected_reason`  VARCHAR(255) NULL,
  `created_by`       BIGINT UNSIGNED NULL,
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fc_student` (`student_id`),
  KEY `idx_fc_inst`    (`institution_id`),
  KEY `idx_fc_status`  (`status`),
  CONSTRAINT `fk_fc_student` FOREIGN KEY (`student_id`)  REFERENCES `students`               (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_fc_assign`  FOREIGN KEY (`assignment_id`) REFERENCES `fee_student_assignments`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_fc_head`    FOREIGN KEY (`fee_head_id`)  REFERENCES `fee_heads`              (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. FEE FINE RULES
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_fine_rules` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id`  BIGINT UNSIGNED NOT NULL,
  `fee_head_id`     BIGINT UNSIGNED NULL COMMENT 'NULL = applies to all heads',
  `rule_name`       VARCHAR(100) NOT NULL,
  `fine_type`       ENUM('per_day','flat','slab') NOT NULL DEFAULT 'per_day',
  `fine_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `grace_days`      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `max_fine`        DECIMAL(10,2) NULL COMMENT 'cap on per_day fines',
  `slab_config`     JSON NULL COMMENT '[{"days_from":1,"days_to":7,"amount":50}, ...]',
  `is_active`       TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ffr_inst`   (`institution_id`),
  KEY `idx_ffr_head`   (`fee_head_id`),
  CONSTRAINT `fk_ffr_head` FOREIGN KEY (`fee_head_id`) REFERENCES `fee_heads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. FEE REFUNDS
-- ============================================================
CREATE TABLE IF NOT EXISTS `fee_refunds` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id`  BIGINT UNSIGNED NOT NULL,
  `student_id`      BIGINT UNSIGNED NOT NULL,
  `receipt_id`      BIGINT UNSIGNED NULL,
  `assignment_id`   BIGINT UNSIGNED NULL,
  `refund_amount`   DECIMAL(12,2) NOT NULL,
  `reason`          VARCHAR(255) NOT NULL,
  `refund_mode`     ENUM('cash','upi','bank_transfer','cheque','other') NULL,
  `reference_number` VARCHAR(100) NULL,
  `status`          ENUM('pending','approved','rejected','processed') NOT NULL DEFAULT 'pending',
  `approved_by`     BIGINT UNSIGNED NULL,
  `approved_at`     TIMESTAMP NULL,
  `processed_at`    TIMESTAMP NULL,
  `rejected_reason` VARCHAR(255) NULL,
  `remarks`         VARCHAR(500) NULL,
  `created_by`      BIGINT UNSIGNED NULL,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_frf_student` (`student_id`),
  KEY `idx_frf_inst`    (`institution_id`),
  KEY `idx_frf_status`  (`status`),
  CONSTRAINT `fk_frf_student` FOREIGN KEY (`student_id`) REFERENCES `students`     (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_frf_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `fee_receipts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SAMPLE DATA
-- ============================================================
INSERT IGNORE INTO `fee_heads` (`institution_id`,`head_name`,`head_code`,`fee_type`,`category`,`is_mandatory`,`is_refundable`,`is_active`) VALUES
(1,'Tuition Fee',       'TUITION',  'annual',   'tuition',     1, 0, 1),
(1,'Exam Fee',          'EXAM',     'semester',  'exam',        1, 0, 1),
(1,'Library Fee',       'LIBRARY',  'annual',   'library',     1, 0, 1),
(1,'Lab Fee',           'LAB',      'semester',  'lab',         0, 0, 1),
(1,'Transport Fee',     'TRANSPORT','recurring', 'transport',   0, 0, 1),
(1,'Hostel Fee',        'HOSTEL',   'recurring', 'hostel',      0, 1, 1),
(1,'Development Fee',   'DEVELOP',  'one_time',  'development', 1, 0, 1),
(1,'Sports Fee',        'SPORTS',   'annual',   'sports',      1, 0, 1),
(1,'Uniform Fee',       'UNIFORM',  'one_time',  'uniform',     0, 0, 1),
(1,'Miscellaneous Fee', 'MISC',     'one_time',  'miscellaneous',0,0, 1);

INSERT IGNORE INTO `fee_fine_rules` (`institution_id`,`fee_head_id`,`rule_name`,`fine_type`,`fine_amount`,`grace_days`,`max_fine`,`is_active`) VALUES
(1, NULL, 'Default Per Day Fine', 'per_day', 10.00, 7, 500.00, 1),
(1, NULL, 'Flat Late Fine',       'flat',    100.00, 15, NULL,  0);

SET FOREIGN_KEY_CHECKS = 1;
