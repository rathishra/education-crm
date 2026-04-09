-- ============================================================
-- Migration 40: Extended Admission Application Fields
-- Adds all fields required for the multi-step online application form
-- ============================================================

-- ── Contact & Identity ────────────────────────────────────
ALTER TABLE admissions
    ADD COLUMN IF NOT EXISTS `whatsapp_number`       VARCHAR(20)  NULL           AFTER `phone`,
    ADD COLUMN IF NOT EXISTS `country_code`          VARCHAR(10)  DEFAULT '+91'  AFTER `whatsapp_number`,
    ADD COLUMN IF NOT EXISTS `aadhaar_number`        VARCHAR(20)  NULL           AFTER `country_code`,
    ADD COLUMN IF NOT EXISTS `otp_verified`          TINYINT(1)   DEFAULT 0      AFTER `aadhaar_number`,
    ADD COLUMN IF NOT EXISTS `otp_verified_at`       TIMESTAMP    NULL           AFTER `otp_verified`;

-- ── Programme & Preferences ──────────────────────────────
ALTER TABLE admissions
    ADD COLUMN IF NOT EXISTS `programme_level`       ENUM('UG','PG','Diploma','Certificate','PhD') NULL AFTER `category`,
    ADD COLUMN IF NOT EXISTS `domain`                VARCHAR(150) NULL           AFTER `programme_level`,
    ADD COLUMN IF NOT EXISTS `hostel_required`       TINYINT(1)   DEFAULT 0      AFTER `domain`,
    ADD COLUMN IF NOT EXISTS `transport_required`    TINYINT(1)   DEFAULT 0      AFTER `hostel_required`,
    ADD COLUMN IF NOT EXISTS `nearest_bus_stop`      VARCHAR(200) NULL           AFTER `transport_required`,
    ADD COLUMN IF NOT EXISTS `course_preference_1`   BIGINT UNSIGNED NULL        AFTER `course_id`,
    ADD COLUMN IF NOT EXISTS `course_preference_2`   BIGINT UNSIGNED NULL        AFTER `course_preference_1`,
    ADD COLUMN IF NOT EXISTS `course_preference_3`   BIGINT UNSIGNED NULL        AFTER `course_preference_2`;

-- ── Extended Family Details ──────────────────────────────
ALTER TABLE admissions
    ADD COLUMN IF NOT EXISTS `father_occupation`     VARCHAR(150) NULL           AFTER `father_phone`,
    ADD COLUMN IF NOT EXISTS `mother_phone`          VARCHAR(20)  NULL           AFTER `mother_name`,
    ADD COLUMN IF NOT EXISTS `mother_occupation`     VARCHAR(150) NULL           AFTER `mother_phone`,
    ADD COLUMN IF NOT EXISTS `father_annual_income`  VARCHAR(50)  NULL           AFTER `mother_occupation`,
    ADD COLUMN IF NOT EXISTS `mother_annual_income`  VARCHAR(50)  NULL           AFTER `father_annual_income`,
    ADD COLUMN IF NOT EXISTS `place_of_birth`        VARCHAR(150) NULL           AFTER `mother_annual_income`,
    ADD COLUMN IF NOT EXISTS `sibling_in_college`    VARCHAR(200) NULL           AFTER `place_of_birth`,
    ADD COLUMN IF NOT EXISTS `blood_group`           VARCHAR(10)  NULL           AFTER `sibling_in_college`,
    ADD COLUMN IF NOT EXISTS `mother_tongue`         VARCHAR(100) NULL           AFTER `blood_group`,
    ADD COLUMN IF NOT EXISTS `religion`              VARCHAR(100) NULL           AFTER `mother_tongue`,
    ADD COLUMN IF NOT EXISTS `community`             VARCHAR(100) NULL           AFTER `religion`;

-- ── Extended Address ─────────────────────────────────────
ALTER TABLE admissions
    ADD COLUMN IF NOT EXISTS `country`               VARCHAR(100) DEFAULT 'India' AFTER `pincode`,
    ADD COLUMN IF NOT EXISTS `permanent_same_as_comm` TINYINT(1)  DEFAULT 1      AFTER `country`,
    ADD COLUMN IF NOT EXISTS `permanent_address_line1` VARCHAR(255) NULL         AFTER `permanent_same_as_comm`,
    ADD COLUMN IF NOT EXISTS `permanent_address_line2` VARCHAR(255) NULL         AFTER `permanent_address_line1`,
    ADD COLUMN IF NOT EXISTS `permanent_city`        VARCHAR(100) NULL           AFTER `permanent_address_line2`,
    ADD COLUMN IF NOT EXISTS `permanent_state`       VARCHAR(100) NULL           AFTER `permanent_city`,
    ADD COLUMN IF NOT EXISTS `permanent_pincode`     VARCHAR(10)  NULL           AFTER `permanent_state`,
    ADD COLUMN IF NOT EXISTS `permanent_country`     VARCHAR(100) DEFAULT 'India' AFTER `permanent_pincode`;

-- ── SSLC / 10th Details ──────────────────────────────────
ALTER TABLE admissions
    ADD COLUMN IF NOT EXISTS `sslc_school_name`      VARCHAR(255) NULL           AFTER `previous_institution`,
    ADD COLUMN IF NOT EXISTS `sslc_state`            VARCHAR(100) NULL           AFTER `sslc_school_name`,
    ADD COLUMN IF NOT EXISTS `sslc_city`             VARCHAR(100) NULL           AFTER `sslc_state`,
    ADD COLUMN IF NOT EXISTS `sslc_board`            VARCHAR(150) NULL           AFTER `sslc_city`,
    ADD COLUMN IF NOT EXISTS `sslc_medium`           VARCHAR(100) NULL           AFTER `sslc_board`,
    ADD COLUMN IF NOT EXISTS `sslc_year_of_passing`  VARCHAR(10)  NULL           AFTER `sslc_medium`,
    ADD COLUMN IF NOT EXISTS `sslc_max_marks`        INT UNSIGNED NULL           AFTER `sslc_year_of_passing`,
    ADD COLUMN IF NOT EXISTS `sslc_marks_obtained`   INT UNSIGNED NULL           AFTER `sslc_max_marks`,
    ADD COLUMN IF NOT EXISTS `sslc_percentage`       DECIMAL(5,2) NULL           AFTER `sslc_marks_obtained`;

-- ── HSC / 12th Details ───────────────────────────────────
ALTER TABLE admissions
    ADD COLUMN IF NOT EXISTS `hsc_school_name`       VARCHAR(255) NULL           AFTER `sslc_percentage`,
    ADD COLUMN IF NOT EXISTS `hsc_state`             VARCHAR(100) NULL           AFTER `hsc_school_name`,
    ADD COLUMN IF NOT EXISTS `hsc_district`          VARCHAR(100) NULL           AFTER `hsc_state`,
    ADD COLUMN IF NOT EXISTS `hsc_board`             VARCHAR(150) NULL           AFTER `hsc_district`,
    ADD COLUMN IF NOT EXISTS `hsc_medium`            VARCHAR(100) NULL           AFTER `hsc_board`,
    ADD COLUMN IF NOT EXISTS `hsc_group`             VARCHAR(150) NULL           AFTER `hsc_medium`,
    ADD COLUMN IF NOT EXISTS `hsc_result_status`     VARCHAR(50)  NULL           AFTER `hsc_group`,
    ADD COLUMN IF NOT EXISTS `hsc_registration_no`   VARCHAR(50)  NULL           AFTER `hsc_result_status`,
    ADD COLUMN IF NOT EXISTS `hsc_max_marks`         INT UNSIGNED NULL           AFTER `hsc_registration_no`,
    ADD COLUMN IF NOT EXISTS `hsc_marks_obtained`    INT UNSIGNED NULL           AFTER `hsc_max_marks`,
    ADD COLUMN IF NOT EXISTS `hsc_percentage`        DECIMAL(5,2) NULL           AFTER `hsc_marks_obtained`;

-- ── Foreign keys for course preferences ─────────────────
-- (DROP first to make re-runnable; ignore errors if they don't exist)
ALTER TABLE admissions
    DROP FOREIGN KEY IF EXISTS `fk_adm_cpref1`,
    DROP FOREIGN KEY IF EXISTS `fk_adm_cpref2`,
    DROP FOREIGN KEY IF EXISTS `fk_adm_cpref3`;

ALTER TABLE admissions
    ADD CONSTRAINT `fk_adm_cpref1` FOREIGN KEY (`course_preference_1`) REFERENCES `courses`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_adm_cpref2` FOREIGN KEY (`course_preference_2`) REFERENCES `courses`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_adm_cpref3` FOREIGN KEY (`course_preference_3`) REFERENCES `courses`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ── Indexes ──────────────────────────────────────────────
ALTER TABLE admissions
    DROP INDEX IF EXISTS `idx_adm_aadhaar`,
    DROP INDEX IF EXISTS `idx_adm_whatsapp`,
    DROP INDEX IF EXISTS `idx_adm_community`;

ALTER TABLE admissions
    ADD INDEX `idx_adm_aadhaar`   (`aadhaar_number`),
    ADD INDEX `idx_adm_whatsapp`  (`whatsapp_number`),
    ADD INDEX `idx_adm_community` (`community`);
