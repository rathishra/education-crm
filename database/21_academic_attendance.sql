-- Academic Attendance Schema Updates
CREATE TABLE IF NOT EXISTS `academic_attendance_sessions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `batch_id` BIGINT UNSIGNED NOT NULL,
    `section_id` BIGINT UNSIGNED NULL,
    `subject_id` BIGINT UNSIGNED NOT NULL,
    `faculty_id` BIGINT UNSIGNED NOT NULL,
    `attendance_date` DATE NOT NULL,
    `session_type` ENUM('lecture','lab','tutorial','extra') NOT NULL DEFAULT 'lecture',
    `topic_covered` VARCHAR(500) NULL,
    `status` ENUM('draft','submitted') NOT NULL DEFAULT 'submitted',
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_acad_att_ses_inst` (`institution_id`),
    KEY `idx_acad_att_ses_sec` (`section_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`batch_id`) REFERENCES `academic_batches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`section_id`) REFERENCES `academic_sections`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`faculty_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academic_attendance_records` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `session_id` BIGINT UNSIGNED NOT NULL,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `attendance_status` ENUM('present','absent','late','excused') NOT NULL DEFAULT 'absent',
    `remarks` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_acad_att_record` (`session_id`, `student_id`),
    KEY `idx_acad_att_rec_inst` (`institution_id`),
    FOREIGN KEY (`session_id`) REFERENCES `academic_attendance_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
