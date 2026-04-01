-- ============================================================
-- ACADEMIC MANAGEMENT MODULE — Migration 18
-- Tables: classrooms, subjects, faculty_subject_allocations,
--         timetable_periods, timetable,
--         attendance_sessions, attendance_records,
--         lms_materials, assessment_configs, assessment_marks
-- ============================================================
USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. CLASSROOMS / ROOMS
-- ============================================================
CREATE TABLE IF NOT EXISTS `classrooms` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `room_number`    VARCHAR(50)  NOT NULL,
    `room_name`      VARCHAR(100) NULL,
    `room_type`      ENUM('classroom','lab','seminar_hall','auditorium','library','other') NOT NULL DEFAULT 'classroom',
    `capacity`       SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    `floor`          VARCHAR(20)  NULL,
    `building`       VARCHAR(100) NULL,
    `facilities`     VARCHAR(500) NULL,
    `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_room_inst` (`institution_id`, `room_number`),
    KEY `idx_room_inst`   (`institution_id`),
    KEY `idx_room_type`   (`room_type`),
    CONSTRAINT `fk_room_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. SUBJECTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `subjects` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`  BIGINT UNSIGNED NOT NULL,
    `department_id`   BIGINT UNSIGNED NULL,
    `course_id`       BIGINT UNSIGNED NULL,
    `subject_code`    VARCHAR(50)   NOT NULL,
    `subject_name`    VARCHAR(200)  NOT NULL,
    `short_name`      VARCHAR(50)   NULL,
    `subject_type`    ENUM('theory','lab','tutorial','project','elective') NOT NULL DEFAULT 'theory',
    `is_elective`     TINYINT(1)    NOT NULL DEFAULT 0,
    `credits`         DECIMAL(4,2)  NOT NULL DEFAULT 3.00,
    `hours_per_week`  TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `theory_hours`    TINYINT UNSIGNED NOT NULL DEFAULT 3,
    `lab_hours`       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `tutorial_hours`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `semester`        TINYINT UNSIGNED NULL,
    `regulation`      VARCHAR(50)   NULL,
    `syllabus_url`    VARCHAR(500)  NULL,
    `description`     TEXT          NULL,
    `status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `deleted_at`      DATETIME      NULL,
    `created_by`      BIGINT UNSIGNED NULL,
    `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_subject_code_inst` (`institution_id`, `subject_code`),
    KEY `idx_sub_inst`       (`institution_id`),
    KEY `idx_sub_dept`       (`department_id`),
    KEY `idx_sub_course`     (`course_id`),
    KEY `idx_sub_semester`   (`semester`),
    KEY `idx_sub_type`       (`subject_type`),
    KEY `idx_sub_status`     (`status`),
    KEY `idx_sub_deleted`    (`deleted_at`),
    CONSTRAINT `fk_sub_inst`  FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_dept`  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)   ON DELETE SET NULL,
    CONSTRAINT `fk_sub_course`FOREIGN KEY (`course_id`)     REFERENCES `courses`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. FACULTY SUBJECT ALLOCATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `faculty_subject_allocations` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`   BIGINT UNSIGNED NOT NULL,
    `faculty_id`       BIGINT UNSIGNED NOT NULL,
    `subject_id`       BIGINT UNSIGNED NOT NULL,
    `batch_id`         BIGINT UNSIGNED NULL,
    `section_id`       BIGINT UNSIGNED NULL,
    `academic_year_id` BIGINT UNSIGNED NULL,
    `semester`         TINYINT UNSIGNED NULL,
    `allocation_type`  ENUM('theory','lab','both') NOT NULL DEFAULT 'theory',
    `lab_batch_number` TINYINT UNSIGNED NULL,
    `hours_per_week`   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status`           ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `allocated_by`     BIGINT UNSIGNED NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_fac_alloc` (`faculty_id`,`subject_id`,`batch_id`,`section_id`,`academic_year_id`,`allocation_type`),
    KEY `idx_fa_inst`     (`institution_id`),
    KEY `idx_fa_faculty`  (`faculty_id`),
    KEY `idx_fa_subject`  (`subject_id`),
    KEY `idx_fa_batch`    (`batch_id`),
    KEY `idx_fa_ay`       (`academic_year_id`),
    CONSTRAINT `fk_fa_inst`         FOREIGN KEY (`institution_id`)   REFERENCES `institutions`(`id`)  ON DELETE CASCADE,
    CONSTRAINT `fk_fa_faculty`      FOREIGN KEY (`faculty_id`)       REFERENCES `users`(`id`)         ON DELETE CASCADE,
    CONSTRAINT `fk_fa_subject`      FOREIGN KEY (`subject_id`)       REFERENCES `subjects`(`id`)      ON DELETE CASCADE,
    CONSTRAINT `fk_fa_batch`        FOREIGN KEY (`batch_id`)         REFERENCES `batches`(`id`)       ON DELETE SET NULL,
    CONSTRAINT `fk_fa_section`      FOREIGN KEY (`section_id`)       REFERENCES `sections`(`id`)      ON DELETE SET NULL,
    CONSTRAINT `fk_fa_ay`           FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_fa_allocated_by` FOREIGN KEY (`allocated_by`)     REFERENCES `users`(`id`)         ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. TIMETABLE PERIODS  (slot / period definitions)
-- ============================================================
CREATE TABLE IF NOT EXISTS `timetable_periods` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `period_number`  TINYINT UNSIGNED NOT NULL,
    `period_name`    VARCHAR(50)   NOT NULL,
    `start_time`     TIME          NOT NULL,
    `end_time`       TIME          NOT NULL,
    `is_break`       TINYINT(1)    NOT NULL DEFAULT 0,
    `break_name`     VARCHAR(50)   NULL,
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_period_inst` (`institution_id`, `period_number`),
    KEY `idx_tp_inst`   (`institution_id`),
    CONSTRAINT `fk_tp_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. TIMETABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `timetable` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`   BIGINT UNSIGNED NOT NULL,
    `academic_year_id` BIGINT UNSIGNED NULL,
    `batch_id`         BIGINT UNSIGNED NOT NULL,
    `section_id`       BIGINT UNSIGNED NULL,
    `semester`         TINYINT UNSIGNED NULL,
    `day_of_week`      ENUM('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
    `period_id`        BIGINT UNSIGNED NOT NULL,
    `subject_id`       BIGINT UNSIGNED NOT NULL,
    `faculty_id`       BIGINT UNSIGNED NOT NULL,
    `classroom_id`     BIGINT UNSIGNED NULL,
    `room_name`        VARCHAR(100)  NULL,
    `entry_type`       ENUM('theory','lab','tutorial','activity') NOT NULL DEFAULT 'theory',
    `effective_from`   DATE          NULL,
    `effective_to`     DATE          NULL,
    `is_active`        TINYINT(1)    NOT NULL DEFAULT 1,
    `created_by`       BIGINT UNSIGNED NULL,
    `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_tt_inst`       (`institution_id`),
    KEY `idx_tt_batch`      (`batch_id`),
    KEY `idx_tt_faculty`    (`faculty_id`),
    KEY `idx_tt_day_period` (`day_of_week`, `period_id`),
    KEY `idx_tt_subject`    (`subject_id`),
    KEY `idx_tt_ay`         (`academic_year_id`),
    KEY `idx_tt_classroom`  (`classroom_id`),
    CONSTRAINT `fk_tt_inst`      FOREIGN KEY (`institution_id`)   REFERENCES `institutions`(`id`)     ON DELETE CASCADE,
    CONSTRAINT `fk_tt_batch`     FOREIGN KEY (`batch_id`)         REFERENCES `batches`(`id`)          ON DELETE CASCADE,
    CONSTRAINT `fk_tt_subject`   FOREIGN KEY (`subject_id`)       REFERENCES `subjects`(`id`)         ON DELETE CASCADE,
    CONSTRAINT `fk_tt_faculty`   FOREIGN KEY (`faculty_id`)       REFERENCES `users`(`id`)            ON DELETE CASCADE,
    CONSTRAINT `fk_tt_period`    FOREIGN KEY (`period_id`)        REFERENCES `timetable_periods`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tt_ay`        FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`)   ON DELETE SET NULL,
    CONSTRAINT `fk_tt_section`   FOREIGN KEY (`section_id`)       REFERENCES `sections`(`id`)         ON DELETE SET NULL,
    CONSTRAINT `fk_tt_classroom` FOREIGN KEY (`classroom_id`)     REFERENCES `classrooms`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. ATTENDANCE SESSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `attendance_sessions` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`   BIGINT UNSIGNED NOT NULL,
    `academic_year_id` BIGINT UNSIGNED NULL,
    `batch_id`         BIGINT UNSIGNED NOT NULL,
    `section_id`       BIGINT UNSIGNED NULL,
    `subject_id`       BIGINT UNSIGNED NOT NULL,
    `faculty_id`       BIGINT UNSIGNED NOT NULL,
    `period_id`        BIGINT UNSIGNED NULL,
    `timetable_id`     BIGINT UNSIGNED NULL,
    `semester`         TINYINT UNSIGNED NULL,
    `attendance_date`  DATE          NOT NULL,
    `day_of_week`      ENUM('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NULL,
    `session_type`     ENUM('theory','lab','tutorial','extra') NOT NULL DEFAULT 'theory',
    `topic_covered`    VARCHAR(500)  NULL,
    `total_students`   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `present_count`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `absent_count`     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `late_count`       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `status`           ENUM('draft','submitted') NOT NULL DEFAULT 'submitted',
    `marked_by`        BIGINT UNSIGNED NULL,
    `marked_at`        TIMESTAMP     NULL,
    `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_as_inst`    (`institution_id`),
    KEY `idx_as_batch`   (`batch_id`),
    KEY `idx_as_subject` (`subject_id`),
    KEY `idx_as_faculty` (`faculty_id`),
    KEY `idx_as_date`    (`attendance_date`),
    KEY `idx_as_ay`      (`academic_year_id`),
    CONSTRAINT `fk_as_inst`    FOREIGN KEY (`institution_id`)   REFERENCES `institutions`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_as_batch`   FOREIGN KEY (`batch_id`)         REFERENCES `batches`(`id`)        ON DELETE CASCADE,
    CONSTRAINT `fk_as_subject` FOREIGN KEY (`subject_id`)       REFERENCES `subjects`(`id`)       ON DELETE CASCADE,
    CONSTRAINT `fk_as_faculty` FOREIGN KEY (`faculty_id`)       REFERENCES `users`(`id`)          ON DELETE CASCADE,
    CONSTRAINT `fk_as_ay`      FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. ATTENDANCE RECORDS  (per-student per-session)
-- ============================================================
CREATE TABLE IF NOT EXISTS `attendance_records` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id`     BIGINT UNSIGNED NOT NULL,
    `student_id`     BIGINT UNSIGNED NOT NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `status`         ENUM('present','absent','late','excused') NOT NULL DEFAULT 'absent',
    `remarks`        VARCHAR(255)  NULL,
    `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_att_record`   (`session_id`, `student_id`),
    KEY `idx_ar_student`         (`student_id`),
    KEY `idx_ar_status`          (`status`),
    KEY `idx_ar_inst`            (`institution_id`),
    CONSTRAINT `fk_ar_session`   FOREIGN KEY (`session_id`) REFERENCES `attendance_sessions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ar_student`   FOREIGN KEY (`student_id`) REFERENCES `students`(`id`)            ON DELETE CASCADE,
    CONSTRAINT `fk_ar_inst`      FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. LMS MATERIALS
-- ============================================================
CREATE TABLE IF NOT EXISTS `lms_materials` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`    BIGINT UNSIGNED NOT NULL,
    `subject_id`        BIGINT UNSIGNED NOT NULL,
    `faculty_id`        BIGINT UNSIGNED NOT NULL,
    `batch_id`          BIGINT UNSIGNED NULL,
    `academic_year_id`  BIGINT UNSIGNED NULL,
    `title`             VARCHAR(300)  NOT NULL,
    `description`       TEXT          NULL,
    `material_type`     ENUM('notes','video','assignment','quiz','announcement','reference','lab_manual','other') NOT NULL DEFAULT 'notes',
    `file_path`         VARCHAR(500)  NULL,
    `original_filename` VARCHAR(255)  NULL,
    `file_size`         INT UNSIGNED  NULL,
    `file_type`         VARCHAR(100)  NULL,
    `video_link`        VARCHAR(500)  NULL,
    `external_link`     VARCHAR(500)  NULL,
    `due_date`          DATE          NULL,
    `publish_date`      DATE          NULL,
    `is_published`      TINYINT(1)    NOT NULL DEFAULT 1,
    `unit_number`       TINYINT UNSIGNED NULL,
    `tags`              VARCHAR(500)  NULL,
    `download_count`    INT UNSIGNED  NOT NULL DEFAULT 0,
    `deleted_at`        DATETIME      NULL,
    `created_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lms_inst`     (`institution_id`),
    KEY `idx_lms_subject`  (`subject_id`),
    KEY `idx_lms_faculty`  (`faculty_id`),
    KEY `idx_lms_type`     (`material_type`),
    KEY `idx_lms_batch`    (`batch_id`),
    KEY `idx_lms_deleted`  (`deleted_at`),
    CONSTRAINT `fk_lms_inst`    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lms_subject` FOREIGN KEY (`subject_id`)     REFERENCES `subjects`(`id`)     ON DELETE CASCADE,
    CONSTRAINT `fk_lms_faculty` FOREIGN KEY (`faculty_id`)     REFERENCES `users`(`id`)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. ASSESSMENT CONFIGS  (configurable per subject/batch/year)
-- ============================================================
CREATE TABLE IF NOT EXISTS `assessment_configs` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`   BIGINT UNSIGNED NOT NULL,
    `subject_id`       BIGINT UNSIGNED NOT NULL,
    `batch_id`         BIGINT UNSIGNED NULL,
    `academic_year_id` BIGINT UNSIGNED NULL,
    `semester`         TINYINT UNSIGNED NULL,
    `assessment_name`  VARCHAR(100)  NOT NULL,
    `assessment_type`  ENUM('internal','assignment','quiz','lab','project','viva','midterm','final','other') NOT NULL DEFAULT 'internal',
    `max_marks`        DECIMAL(6,2)  NOT NULL DEFAULT 100.00,
    `passing_marks`    DECIMAL(6,2)  NOT NULL DEFAULT 40.00,
    `weightage`        DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
    `sequence`         TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `is_active`        TINYINT(1)    NOT NULL DEFAULT 1,
    `created_by`       BIGINT UNSIGNED NULL,
    `created_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ac_inst`    (`institution_id`),
    KEY `idx_ac_subject` (`subject_id`),
    KEY `idx_ac_batch`   (`batch_id`),
    KEY `idx_ac_ay`      (`academic_year_id`),
    CONSTRAINT `fk_ac_inst`    FOREIGN KEY (`institution_id`)   REFERENCES `institutions`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_ac_subject` FOREIGN KEY (`subject_id`)       REFERENCES `subjects`(`id`)        ON DELETE CASCADE,
    CONSTRAINT `fk_ac_batch`   FOREIGN KEY (`batch_id`)         REFERENCES `batches`(`id`)         ON DELETE SET NULL,
    CONSTRAINT `fk_ac_ay`      FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. ASSESSMENT MARKS  (per-student per-assessment)
-- ============================================================
CREATE TABLE IF NOT EXISTS `assessment_marks` (
    `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assessment_config_id` BIGINT UNSIGNED NOT NULL,
    `student_id`           BIGINT UNSIGNED NOT NULL,
    `institution_id`       BIGINT UNSIGNED NOT NULL,
    `subject_id`           BIGINT UNSIGNED NOT NULL,
    `marks_obtained`       DECIMAL(6,2)  NULL,
    `is_absent`            TINYINT(1)    NOT NULL DEFAULT 0,
    `remarks`              VARCHAR(255)  NULL,
    `entered_by`           BIGINT UNSIGNED NULL,
    `created_at`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_marks` (`assessment_config_id`, `student_id`),
    KEY `idx_am_student`  (`student_id`),
    KEY `idx_am_subject`  (`subject_id`),
    KEY `idx_am_inst`     (`institution_id`),
    CONSTRAINT `fk_am_config`   FOREIGN KEY (`assessment_config_id`) REFERENCES `assessment_configs`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_am_student`  FOREIGN KEY (`student_id`)           REFERENCES `students`(`id`)           ON DELETE CASCADE,
    CONSTRAINT `fk_am_inst`     FOREIGN KEY (`institution_id`)       REFERENCES `institutions`(`id`)       ON DELETE CASCADE,
    CONSTRAINT `fk_am_subject`  FOREIGN KEY (`subject_id`)           REFERENCES `subjects`(`id`)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
