-- ============================================================
-- Migration 44: Enterprise Department Management Module
-- Enhances departments table + adds 5 supporting tables
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 1. ALTER departments — add all enterprise columns
-- ────────────────────────────────────────────────────────────
ALTER TABLE `departments`
    -- Core
    ADD COLUMN IF NOT EXISTS `department_type`    ENUM('academic','administrative','research') NOT NULL DEFAULT 'academic' AFTER `code`,
    ADD COLUMN IF NOT EXISTS `parent_department_id` BIGINT UNSIGNED DEFAULT NULL AFTER `department_type`,
    ADD COLUMN IF NOT EXISTS `established_year`   YEAR DEFAULT NULL AFTER `parent_department_id`,

    -- HOD & Contact
    ADD COLUMN IF NOT EXISTS `hod_employee_id`    BIGINT UNSIGNED DEFAULT NULL AFTER `hod_name`,
    ADD COLUMN IF NOT EXISTS `dept_email`         VARCHAR(255) DEFAULT NULL AFTER `hod_employee_id`,
    ADD COLUMN IF NOT EXISTS `dept_phone`         VARCHAR(20)  DEFAULT NULL AFTER `dept_email`,
    ADD COLUMN IF NOT EXISTS `extension_number`   VARCHAR(20)  DEFAULT NULL AFTER `dept_phone`,
    ADD COLUMN IF NOT EXISTS `office_block`       VARCHAR(100) DEFAULT NULL AFTER `extension_number`,
    ADD COLUMN IF NOT EXISTS `office_floor`       VARCHAR(50)  DEFAULT NULL AFTER `office_block`,
    ADD COLUMN IF NOT EXISTS `office_room`        VARCHAR(50)  DEFAULT NULL AFTER `office_floor`,
    ADD COLUMN IF NOT EXISTS `alt_contact_name`   VARCHAR(255) DEFAULT NULL AFTER `office_room`,
    ADD COLUMN IF NOT EXISTS `alt_contact_phone`  VARCHAR(20)  DEFAULT NULL AFTER `alt_contact_name`,

    -- Academic Configuration
    ADD COLUMN IF NOT EXISTS `semester_pattern`   ENUM('semester','trimester','annual') NOT NULL DEFAULT 'semester' AFTER `alt_contact_phone`,
    ADD COLUMN IF NOT EXISTS `credit_system`      TINYINT(1) NOT NULL DEFAULT 1 AFTER `semester_pattern`,
    ADD COLUMN IF NOT EXISTS `grading_scheme`     VARCHAR(100) DEFAULT NULL AFTER `credit_system`,
    ADD COLUMN IF NOT EXISTS `intake_capacity`    INT UNSIGNED DEFAULT NULL AFTER `grading_scheme`,
    ADD COLUMN IF NOT EXISTS `admission_quota`    VARCHAR(255) DEFAULT NULL AFTER `intake_capacity`,
    ADD COLUMN IF NOT EXISTS `counselling_code`   VARCHAR(50)  DEFAULT NULL AFTER `admission_quota`,

    -- Staff roles (FK'd later; stored as user IDs)
    ADD COLUMN IF NOT EXISTS `coordinator_id`     BIGINT UNSIGNED DEFAULT NULL AFTER `counselling_code`,
    ADD COLUMN IF NOT EXISTS `exam_coordinator_id` BIGINT UNSIGNED DEFAULT NULL AFTER `coordinator_id`,
    ADD COLUMN IF NOT EXISTS `timetable_incharge_id` BIGINT UNSIGNED DEFAULT NULL AFTER `exam_coordinator_id`,
    ADD COLUMN IF NOT EXISTS `faculty_count`      INT UNSIGNED NOT NULL DEFAULT 0 AFTER `timetable_incharge_id`,
    ADD COLUMN IF NOT EXISTS `student_count`      INT UNSIGNED NOT NULL DEFAULT 0 AFTER `faculty_count`,

    -- LMS Integration
    ADD COLUMN IF NOT EXISTS `lms_allow_course_creation` TINYINT(1) NOT NULL DEFAULT 1 AFTER `student_count`,
    ADD COLUMN IF NOT EXISTS `lms_attendance_required`   TINYINT(1) NOT NULL DEFAULT 1 AFTER `lms_allow_course_creation`,
    ADD COLUMN IF NOT EXISTS `lms_internal_marks`        TINYINT(1) NOT NULL DEFAULT 1 AFTER `lms_attendance_required`,
    ADD COLUMN IF NOT EXISTS `lms_lab_courses`           TINYINT(1) NOT NULL DEFAULT 0 AFTER `lms_internal_marks`,
    ADD COLUMN IF NOT EXISTS `lms_project_dissertation`  TINYINT(1) NOT NULL DEFAULT 0 AFTER `lms_lab_courses`,
    ADD COLUMN IF NOT EXISTS `lms_hod_approval`          TINYINT(1) NOT NULL DEFAULT 0 AFTER `lms_project_dissertation`,

    -- Workflow
    ADD COLUMN IF NOT EXISTS `allow_hod_login`       TINYINT(1) NOT NULL DEFAULT 0 AFTER `lms_hod_approval`,
    ADD COLUMN IF NOT EXISTS `approval_required`     TINYINT(1) NOT NULL DEFAULT 0 AFTER `allow_hod_login`,

    -- Audit
    ADD COLUMN IF NOT EXISTS `created_by`  BIGINT UNSIGNED DEFAULT NULL AFTER `description`,
    ADD COLUMN IF NOT EXISTS `updated_by`  BIGINT UNSIGNED DEFAULT NULL AFTER `created_by`,
    ADD COLUMN IF NOT EXISTS `deleted_at`  TIMESTAMP NULL DEFAULT NULL AFTER `updated_at`;

-- Index for parent FK
ALTER TABLE `departments`
    DROP INDEX IF EXISTS `idx_dept_parent`,
    ADD INDEX `idx_dept_parent` (`parent_department_id`),
    DROP INDEX IF EXISTS `idx_dept_type`,
    ADD INDEX `idx_dept_type` (`department_type`);

-- ────────────────────────────────────────────────────────────
-- 2. department_staff — faculty & non-teaching staff mapping
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `department_staff` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id`   BIGINT UNSIGNED NOT NULL,
    `user_id`         BIGINT UNSIGNED NOT NULL,
    `staff_type`      ENUM('teaching','non_teaching') NOT NULL DEFAULT 'teaching',
    `role_in_dept`    VARCHAR(100) DEFAULT NULL COMMENT 'e.g. HOD, Coordinator, Lab Incharge',
    `joined_date`     DATE DEFAULT NULL,
    `status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_dept_user` (`department_id`, `user_id`),
    KEY `idx_ds_dept` (`department_id`),
    KEY `idx_ds_user` (`user_id`),
    CONSTRAINT `fk_ds_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ds_user` FOREIGN KEY (`user_id`)       REFERENCES `users` (`id`)       ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 3. department_programs — UG/PG/PhD programs offered
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `department_programs` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id`   BIGINT UNSIGNED NOT NULL,
    `program_level`   ENUM('ug','pg','phd','diploma','certificate','mphil','other') NOT NULL,
    `degree_type`     ENUM('full_time','part_time','distance') NOT NULL DEFAULT 'full_time',
    `program_name`    VARCHAR(255) NOT NULL,
    `intake_seats`    INT UNSIGNED DEFAULT NULL,
    `status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_dp_dept` (`department_id`),
    CONSTRAINT `fk_dp_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 4. department_rooms — infrastructure assignment
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `department_rooms` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id`   BIGINT UNSIGNED NOT NULL,
    `room_name`       VARCHAR(100) NOT NULL,
    `room_type`       ENUM('classroom','lab','office','seminar_hall','library','other') NOT NULL DEFAULT 'classroom',
    `block`           VARCHAR(100) DEFAULT NULL,
    `floor`           VARCHAR(50)  DEFAULT NULL,
    `room_number`     VARCHAR(50)  DEFAULT NULL,
    `capacity`        INT UNSIGNED DEFAULT NULL,
    `has_projector`   TINYINT(1) NOT NULL DEFAULT 0,
    `has_ac`          TINYINT(1) NOT NULL DEFAULT 0,
    `status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_dr_dept` (`department_id`),
    CONSTRAINT `fk_dr_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 5. department_finance — ERP cost centre & budget mapping
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `department_finance` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id`   BIGINT UNSIGNED NOT NULL,
    `cost_center`     VARCHAR(100) DEFAULT NULL,
    `budget_allocation` DECIMAL(15,2) DEFAULT NULL,
    `expense_head`    VARCHAR(255) DEFAULT NULL,
    `revenue_account` VARCHAR(100) DEFAULT NULL,
    `fee_category`    VARCHAR(255) DEFAULT NULL,
    `financial_year`  VARCHAR(20)  DEFAULT NULL COMMENT 'e.g. 2024-25',
    `notes`           TEXT DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_df_dept_year` (`department_id`, `financial_year`),
    KEY `idx_df_dept` (`department_id`),
    CONSTRAINT `fk_df_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ────────────────────────────────────────────────────────────
-- 6. department_settings — key-value store for dept config
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `department_settings` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `department_id`   BIGINT UNSIGNED NOT NULL,
    `setting_key`     VARCHAR(100) NOT NULL,
    `setting_value`   TEXT DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ds_dept_key` (`department_id`, `setting_key`),
    KEY `idx_dset_dept` (`department_id`),
    CONSTRAINT `fk_dset_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
