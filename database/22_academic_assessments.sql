-- Academic Assessments & Marks Schema Updates
CREATE TABLE IF NOT EXISTS `academic_assessments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `batch_id` BIGINT UNSIGNED NOT NULL,
    `subject_id` BIGINT UNSIGNED NOT NULL,
    `assessment_name` VARCHAR(100) NOT NULL,
    `assessment_type` ENUM('internal','assignment','quiz','lab','project','viva','midterm','final','other') NOT NULL DEFAULT 'internal',
    `max_marks` DECIMAL(6,2) NOT NULL DEFAULT 100.00,
    `passing_marks` DECIMAL(6,2) NOT NULL DEFAULT 40.00,
    `weightage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `assessment_date` DATE NULL,
    `status` ENUM('active','completed','archived') NOT NULL DEFAULT 'active',
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_acad_ass_inst` (`institution_id`),
    KEY `idx_acad_ass_batch` (`batch_id`),
    KEY `idx_acad_ass_sub` (`subject_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`batch_id`) REFERENCES `academic_batches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academic_assessment_marks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assessment_id` BIGINT UNSIGNED NOT NULL,
    `student_id` BIGINT UNSIGNED NOT NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `marks_obtained` DECIMAL(6,2) NULL,
    `is_absent` TINYINT(1) NOT NULL DEFAULT 0,
    `remarks` VARCHAR(255) NULL,
    `entered_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_acad_ass_marks` (`assessment_id`, `student_id`),
    KEY `idx_acad_ass_marks_stu` (`student_id`),
    FOREIGN KEY (`assessment_id`) REFERENCES `academic_assessments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
