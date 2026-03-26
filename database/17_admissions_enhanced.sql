-- ============================================================
-- ADMISSION MODULE ENHANCEMENT — Migration 17
-- ============================================================
USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. ALTER admissions — add missing columns
-- ============================================================
ALTER TABLE `admissions`
    ADD COLUMN IF NOT EXISTS `organization_id`          BIGINT UNSIGNED NULL             AFTER `institution_id`,
    ADD COLUMN IF NOT EXISTS `enquiry_id`               BIGINT UNSIGNED NULL             AFTER `lead_id`,
    ADD COLUMN IF NOT EXISTS `department_id`            BIGINT UNSIGNED NULL             AFTER `course_id`,
    ADD COLUMN IF NOT EXISTS `specialization`           VARCHAR(150) NULL                AFTER `department_id`,
    ADD COLUMN IF NOT EXISTS `current_semester`         TINYINT UNSIGNED DEFAULT 1       AFTER `batch_id`,
    ADD COLUMN IF NOT EXISTS `section_id`               BIGINT UNSIGNED NULL             AFTER `current_semester`,
    ADD COLUMN IF NOT EXISTS `quota`                    ENUM('general','management','government','scholarship','nri') NOT NULL DEFAULT 'general' AFTER `admission_type`,
    ADD COLUMN IF NOT EXISTS `counselor_id`             BIGINT UNSIGNED NULL             AFTER `approved_by`,
    ADD COLUMN IF NOT EXISTS `application_source`       VARCHAR(100) NULL                AFTER `counselor_id`,
    ADD COLUMN IF NOT EXISTS `address_line1`            VARCHAR(255) NULL                AFTER `gender`,
    ADD COLUMN IF NOT EXISTS `address_line2`            VARCHAR(255) NULL                AFTER `address_line1`,
    ADD COLUMN IF NOT EXISTS `city`                     VARCHAR(100) NULL                AFTER `address_line2`,
    ADD COLUMN IF NOT EXISTS `state`                    VARCHAR(100) NULL                AFTER `city`,
    ADD COLUMN IF NOT EXISTS `pincode`                  VARCHAR(10) NULL                 AFTER `state`,
    ADD COLUMN IF NOT EXISTS `nationality`              VARCHAR(100) DEFAULT 'Indian'    AFTER `pincode`,
    ADD COLUMN IF NOT EXISTS `category`                 ENUM('general','obc','sc','st','ews','minority','other') NULL AFTER `nationality`,
    ADD COLUMN IF NOT EXISTS `father_name`              VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS `father_phone`             VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS `mother_name`              VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS `guardian_name`            VARCHAR(150) NULL,
    ADD COLUMN IF NOT EXISTS `guardian_phone`           VARCHAR(20) NULL,
    ADD COLUMN IF NOT EXISTS `previous_qualification`   VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS `previous_percentage`      DECIMAL(5,2) NULL,
    ADD COLUMN IF NOT EXISTS `previous_institution`     VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS `previous_year_of_passing` YEAR NULL,
    ADD COLUMN IF NOT EXISTS `fee_structure_id`         BIGINT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS `total_fee`                DECIMAL(12,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `discount_amount`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `scholarship_amount`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `final_fee`                DECIMAL(12,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `paid_amount`              DECIMAL(12,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `balance_amount`           DECIMAL(12,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `payment_status`           ENUM('pending','partial','paid') NOT NULL DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS `initial_payment`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `payment_due_date`         DATE NULL,
    ADD COLUMN IF NOT EXISTS `rejection_reason`         TEXT NULL;

-- ============================================================
-- 2. Migrate existing status values → new vocabulary
-- ============================================================
UPDATE `admissions` SET `status` = 'pending'          WHERE `status` = 'applied';
UPDATE `admissions` SET `status` = 'pending'          WHERE `status` = 'under_review';
UPDATE `admissions` SET `status` = 'document_pending' WHERE `status` = 'documents_pending';
UPDATE `admissions` SET `status` = 'confirmed'        WHERE `status` = 'approved';

-- ============================================================
-- 3. Modify status ENUM
-- ============================================================
ALTER TABLE `admissions`
    MODIFY COLUMN `status` ENUM('draft','pending','document_pending','payment_pending','confirmed','enrolled','rejected','cancelled') NOT NULL DEFAULT 'pending';

-- ============================================================
-- 4. Add Indexes
-- ============================================================
ALTER TABLE `admissions`
    ADD INDEX IF NOT EXISTS `idx_adm_dept`       (`department_id`),
    ADD INDEX IF NOT EXISTS `idx_adm_counselor`  (`counselor_id`),
    ADD INDEX IF NOT EXISTS `idx_adm_payment_st` (`payment_status`),
    ADD INDEX IF NOT EXISTS `idx_adm_enquiry`    (`enquiry_id`),
    ADD INDEX IF NOT EXISTS `idx_adm_section`    (`section_id`);

-- ============================================================
-- 5. CREATE TABLE admission_documents
-- ============================================================
CREATE TABLE IF NOT EXISTS `admission_documents` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admission_id`        BIGINT UNSIGNED NOT NULL,
    `institution_id`      BIGINT UNSIGNED NOT NULL,
    `document_type`       ENUM('marksheet','transfer_certificate','id_proof','community_certificate','income_certificate','photo','migration_certificate','character_certificate','medical_certificate','other') NOT NULL,
    `document_name`       VARCHAR(255) NULL,
    `original_filename`   VARCHAR(255) NULL,
    `file_path`           VARCHAR(500) NULL,
    `file_size`           INT UNSIGNED NULL,
    `file_type`           VARCHAR(100) NULL,
    `is_required`         TINYINT(1) NOT NULL DEFAULT 1,
    `is_submitted`        TINYINT(1) NOT NULL DEFAULT 0,
    `submitted_at`        TIMESTAMP NULL,
    `is_verified`         TINYINT(1) NOT NULL DEFAULT 0,
    `verification_status` ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    `verified_by`         BIGINT UNSIGNED NULL,
    `verified_at`         TIMESTAMP NULL,
    `verification_notes`  TEXT NULL,
    `uploaded_by`         BIGINT UNSIGNED NULL,
    `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_adoc_admission` (`admission_id`),
    KEY `idx_adoc_type`      (`document_type`),
    KEY `idx_adoc_status`    (`verification_status`),
    CONSTRAINT `fk_adoc_admission` FOREIGN KEY (`admission_id`)   REFERENCES `admissions`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_adoc_inst`      FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_adoc_verified`  FOREIGN KEY (`verified_by`)    REFERENCES `users`(`id`)        ON DELETE SET NULL,
    CONSTRAINT `fk_adoc_uploaded`  FOREIGN KEY (`uploaded_by`)    REFERENCES `users`(`id`)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. CREATE TABLE admission_payments
-- ============================================================
CREATE TABLE IF NOT EXISTS `admission_payments` (
    `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admission_id`          BIGINT UNSIGNED NOT NULL,
    `institution_id`        BIGINT UNSIGNED NOT NULL,
    `payment_date`          DATE NOT NULL,
    `amount`                DECIMAL(12,2) NOT NULL,
    `payment_mode`          ENUM('cash','cheque','dd','online','upi','card','bank_transfer','other') NOT NULL DEFAULT 'cash',
    `transaction_reference` VARCHAR(100) NULL,
    `receipt_number`        VARCHAR(50) NULL,
    `fee_head`              VARCHAR(100) NULL,
    `academic_year_id`      BIGINT UNSIGNED NULL,
    `remarks`               TEXT NULL,
    `collected_by`          BIGINT UNSIGNED NULL,
    `created_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_apay_admission` (`admission_id`),
    KEY `idx_apay_date`      (`payment_date`),
    CONSTRAINT `fk_apay_admission` FOREIGN KEY (`admission_id`)   REFERENCES `admissions`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_apay_inst`      FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_apay_collected` FOREIGN KEY (`collected_by`)   REFERENCES `users`(`id`)        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. CREATE TABLE admission_timeline
-- ============================================================
CREATE TABLE IF NOT EXISTS `admission_timeline` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admission_id` BIGINT UNSIGNED NOT NULL,
    `event_type`   ENUM('created','status_change','document_uploaded','document_verified','document_rejected','payment_recorded','note_added','approved','rejected','confirmed','enrolled','cancelled','reopened','assigned','fee_updated') NOT NULL,
    `title`        VARCHAR(255) NOT NULL,
    `description`  TEXT NULL,
    `old_value`    VARCHAR(100) NULL,
    `new_value`    VARCHAR(100) NULL,
    `metadata`     JSON NULL,
    `performed_by` BIGINT UNSIGNED NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_atl_admission` (`admission_id`),
    KEY `idx_atl_type`      (`event_type`),
    KEY `idx_atl_date`      (`created_at`),
    CONSTRAINT `fk_atl_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_atl_user`      FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
