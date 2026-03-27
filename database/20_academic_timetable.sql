-- Academic Timetable Schema (Isolated for Enterprise Module)

CREATE TABLE IF NOT EXISTS `academic_timetable_periods` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `period_number` TINYINT UNSIGNED NOT NULL,
    `period_name` VARCHAR(50) NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `is_break` TINYINT(1) NOT NULL DEFAULT 0,
    `break_name` VARCHAR(50) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_acad_tp_inst` (`institution_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academic_timetable` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `batch_id` BIGINT UNSIGNED NOT NULL,
    `section_id` BIGINT UNSIGNED NULL,
    `day_of_week` ENUM('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
    `period_id` BIGINT UNSIGNED NOT NULL,
    `subject_id` BIGINT UNSIGNED NOT NULL,
    `faculty_id` BIGINT UNSIGNED NOT NULL,
    `entry_type` ENUM('lecture','lab','tutorial','activity') NOT NULL DEFAULT 'lecture',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_acad_tt_inst` (`institution_id`),
    KEY `idx_acad_tt_batch` (`batch_id`),
    KEY `idx_acad_tt_period` (`period_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`batch_id`) REFERENCES `academic_batches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`section_id`) REFERENCES `academic_sections`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`period_id`) REFERENCES `academic_timetable_periods`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`faculty_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
