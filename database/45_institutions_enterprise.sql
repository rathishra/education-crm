-- ============================================================
-- Migration 45: Enterprise Institution Master Module
-- Tables: institutions (ALTER) + 7 supporting tables
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 1. ALTER institutions — add all enterprise columns
-- ────────────────────────────────────────────────────────────
ALTER TABLE `institutions`
    -- Basic Info enhancements
    ADD COLUMN IF NOT EXISTS `short_name`        VARCHAR(100)  DEFAULT NULL AFTER `name`,
    ADD COLUMN IF NOT EXISTS `institution_type`  ENUM('college','school','university','training_institute','polytechnic','deemed_university','autonomous','other') NOT NULL DEFAULT 'college' AFTER `code`,
    ADD COLUMN IF NOT EXISTS `parent_org_name`   VARCHAR(255)  DEFAULT NULL AFTER `institution_type`,
    ADD COLUMN IF NOT EXISTS `description`       TEXT          DEFAULT NULL AFTER `parent_org_name`,

    -- Address
    ADD COLUMN IF NOT EXISTS `latitude`          DECIMAL(10,7) DEFAULT NULL AFTER `pincode`,
    ADD COLUMN IF NOT EXISTS `longitude`         DECIMAL(10,7) DEFAULT NULL AFTER `latitude`,

    -- Contact
    ADD COLUMN IF NOT EXISTS `alt_phone`         VARCHAR(20)   DEFAULT NULL AFTER `phone`,
    ADD COLUMN IF NOT EXISTS `fax`               VARCHAR(20)   DEFAULT NULL AFTER `alt_phone`,
    ADD COLUMN IF NOT EXISTS `admission_phone`   VARCHAR(20)   DEFAULT NULL AFTER `fax`,
    ADD COLUMN IF NOT EXISTS `support_email`     VARCHAR(255)  DEFAULT NULL AFTER `email`,

    -- Administration
    ADD COLUMN IF NOT EXISTS `director_name`     VARCHAR(255)  DEFAULT NULL AFTER `principal_name`,
    ADD COLUMN IF NOT EXISTS `principal_emp_id`  BIGINT UNSIGNED DEFAULT NULL AFTER `director_name`,
    ADD COLUMN IF NOT EXISTS `registrar_name`    VARCHAR(255)  DEFAULT NULL AFTER `principal_emp_id`,
    ADD COLUMN IF NOT EXISTS `coe_name`          VARCHAR(255)  DEFAULT NULL COMMENT 'Controller of Examination' AFTER `registrar_name`,
    ADD COLUMN IF NOT EXISTS `admission_head`    VARCHAR(255)  DEFAULT NULL AFTER `coe_name`,
    ADD COLUMN IF NOT EXISTS `finance_officer`   VARCHAR(255)  DEFAULT NULL AFTER `admission_head`,

    -- Audit
    ADD COLUMN IF NOT EXISTS `created_by`        BIGINT UNSIGNED DEFAULT NULL AFTER `settings`,
    ADD COLUMN IF NOT EXISTS `updated_by`        BIGINT UNSIGNED DEFAULT NULL AFTER `created_by`;

-- ────────────────────────────────────────────────────────────
-- 2. institution_address — dedicated address + geo table
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institution_address` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`  BIGINT UNSIGNED NOT NULL,
    `address_type`    ENUM('registered','campus','correspondence') NOT NULL DEFAULT 'registered',
    `address_line1`   VARCHAR(255) DEFAULT NULL,
    `address_line2`   VARCHAR(255) DEFAULT NULL,
    `city`            VARCHAR(100) DEFAULT NULL,
    `state`           VARCHAR(100) DEFAULT NULL,
    `country`         VARCHAR(100) DEFAULT 'India',
    `pincode`         VARCHAR(20)  DEFAULT NULL,
    `latitude`        DECIMAL(10,7) DEFAULT NULL,
    `longitude`       DECIMAL(10,7) DEFAULT NULL,
    `is_primary`      TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ia_inst` (`institution_id`),
    CONSTRAINT `fk_ia_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. institution_academic_config — academic configuration
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institution_academic_config` (
    `id`                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`            BIGINT UNSIGNED NOT NULL,
    `academic_year_start_month` TINYINT UNSIGNED NOT NULL DEFAULT 6 COMMENT '1=Jan…12=Dec',
    `academic_pattern`          ENUM('annual','semester','trimester') NOT NULL DEFAULT 'semester',
    `credit_system`             TINYINT(1) NOT NULL DEFAULT 1,
    `grading_system`            VARCHAR(100) DEFAULT NULL COMMENT 'e.g. 10-Point CGPA, Letter Grade',
    `max_credits_per_semester`  TINYINT UNSIGNED DEFAULT NULL,
    `attendance_policy`         TINYINT UNSIGNED NOT NULL DEFAULT 75 COMMENT 'Minimum attendance %',
    `internal_assessment`       TINYINT(1) NOT NULL DEFAULT 1,
    `internal_marks_percentage` TINYINT UNSIGNED DEFAULT 30,
    `pass_marks_percentage`     TINYINT UNSIGNED DEFAULT 50,
    `arrear_policy`             VARCHAR(255) DEFAULT NULL,
    `created_at`                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_iac_inst` (`institution_id`),
    CONSTRAINT `fk_iac_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 4. institution_modules — ERP & LMS module toggles
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institution_modules` (
    `id`                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`            BIGINT UNSIGNED NOT NULL,
    -- ERP Modules
    `erp_departments`           TINYINT(1) NOT NULL DEFAULT 1,
    `erp_programs`              TINYINT(1) NOT NULL DEFAULT 1,
    `erp_courses`               TINYINT(1) NOT NULL DEFAULT 1,
    `erp_admissions`            TINYINT(1) NOT NULL DEFAULT 1,
    `erp_fees`                  TINYINT(1) NOT NULL DEFAULT 1,
    `erp_exams`                 TINYINT(1) NOT NULL DEFAULT 1,
    `erp_hr`                    TINYINT(1) NOT NULL DEFAULT 0,
    `erp_hostel`                TINYINT(1) NOT NULL DEFAULT 0,
    `erp_transport`             TINYINT(1) NOT NULL DEFAULT 0,
    `erp_library`               TINYINT(1) NOT NULL DEFAULT 0,
    `erp_placement`             TINYINT(1) NOT NULL DEFAULT 0,
    -- LMS Modules
    `lms_enabled`               TINYINT(1) NOT NULL DEFAULT 1,
    `lms_online_classes`        TINYINT(1) NOT NULL DEFAULT 0,
    `lms_assignments`           TINYINT(1) NOT NULL DEFAULT 1,
    `lms_quiz`                  TINYINT(1) NOT NULL DEFAULT 1,
    `lms_discussion_forum`      TINYINT(1) NOT NULL DEFAULT 0,
    `lms_attendance`            TINYINT(1) NOT NULL DEFAULT 1,
    `lms_gradebook`             TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_im_inst` (`institution_id`),
    CONSTRAINT `fk_im_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 5. institution_finance — finance configuration
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institution_finance` (
    `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`       BIGINT UNSIGNED NOT NULL,
    `base_currency`        VARCHAR(10)  NOT NULL DEFAULT 'INR',
    `currency_symbol`      VARCHAR(5)   NOT NULL DEFAULT '₹',
    `fee_collection_mode`  ENUM('online','offline','both') NOT NULL DEFAULT 'both',
    `tax_enabled`          TINYINT(1) NOT NULL DEFAULT 0,
    `tax_percentage`       DECIMAL(5,2) DEFAULT NULL,
    `default_fee_template` VARCHAR(255) DEFAULT NULL,
    `finance_start_month`  TINYINT UNSIGNED NOT NULL DEFAULT 4 COMMENT '1=Jan…12=Dec (4=April)',
    `cost_center_code`     VARCHAR(100) DEFAULT NULL,
    `bank_name`            VARCHAR(255) DEFAULT NULL,
    `bank_account`         VARCHAR(50)  DEFAULT NULL,
    `bank_ifsc`            VARCHAR(20)  DEFAULT NULL,
    `payment_gateway`      VARCHAR(100) DEFAULT NULL,
    `created_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_if_inst` (`institution_id`),
    CONSTRAINT `fk_if_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 6. institution_branding — UI/theme/branding
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institution_branding` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`      BIGINT UNSIGNED NOT NULL,
    `primary_color`       VARCHAR(20)  DEFAULT '#2c3e8c',
    `secondary_color`     VARCHAR(20)  DEFAULT '#e74c3c',
    `theme`               ENUM('light','dark','system') NOT NULL DEFAULT 'light',
    `login_banner`        VARCHAR(500) DEFAULT NULL,
    `report_header_name`  VARCHAR(255) DEFAULT NULL,
    `footer_text`         VARCHAR(500) DEFAULT NULL,
    `favicon`             VARCHAR(500) DEFAULT NULL,
    `email_header_logo`   VARCHAR(500) DEFAULT NULL,
    `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ib_inst` (`institution_id`),
    CONSTRAINT `fk_ib_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 7. institution_infrastructure — campus/rooms inventory
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institution_infrastructure` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`      BIGINT UNSIGNED NOT NULL,
    `campus_type`         ENUM('single','multi') NOT NULL DEFAULT 'single',
    `total_buildings`     SMALLINT UNSIGNED DEFAULT NULL,
    `total_classrooms`    SMALLINT UNSIGNED DEFAULT NULL,
    `total_labs`          SMALLINT UNSIGNED DEFAULT NULL,
    `total_departments`   SMALLINT UNSIGNED DEFAULT NULL,
    `total_area_sqft`     INT UNSIGNED DEFAULT NULL,
    `library_available`   TINYINT(1) NOT NULL DEFAULT 0,
    `hostel_available`    TINYINT(1) NOT NULL DEFAULT 0,
    `hostel_boys_seats`   SMALLINT UNSIGNED DEFAULT NULL,
    `hostel_girls_seats`  SMALLINT UNSIGNED DEFAULT NULL,
    `transport_available` TINYINT(1) NOT NULL DEFAULT 0,
    `canteen_available`   TINYINT(1) NOT NULL DEFAULT 0,
    `sports_available`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ii_inst` (`institution_id`),
    CONSTRAINT `fk_ii_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 8. institution_settings — generic key-value config store
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institution_settings` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `group_name`     VARCHAR(100) NOT NULL DEFAULT 'general',
    `setting_key`    VARCHAR(100) NOT NULL,
    `setting_value`  TEXT         DEFAULT NULL,
    `setting_type`   ENUM('string','boolean','integer','json') NOT NULL DEFAULT 'string',
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_is_inst_key` (`institution_id`, `setting_key`),
    KEY `idx_is_group` (`institution_id`, `group_name`),
    CONSTRAINT `fk_is_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 9. institution_permissions — per-institution feature flags
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `institution_permissions` (
    `id`                         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`             BIGINT UNSIGNED NOT NULL,
    `allow_multi_campus`         TINYINT(1) NOT NULL DEFAULT 0,
    `allow_multi_department`     TINYINT(1) NOT NULL DEFAULT 1,
    `allow_multi_academic_year`  TINYINT(1) NOT NULL DEFAULT 1,
    `data_isolation`             TINYINT(1) NOT NULL DEFAULT 1,
    `allow_hod_login`            TINYINT(1) NOT NULL DEFAULT 0,
    `allow_student_portal`       TINYINT(1) NOT NULL DEFAULT 1,
    `allow_parent_portal`        TINYINT(1) NOT NULL DEFAULT 0,
    `allow_faculty_portal`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`                 TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                 TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ip_inst` (`institution_id`),
    CONSTRAINT `fk_ip_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
