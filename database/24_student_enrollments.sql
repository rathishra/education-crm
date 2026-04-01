-- ============================================================
-- Student Section Enrollments — links students to sections
-- ============================================================
CREATE TABLE IF NOT EXISTS `student_section_enrollments` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id`  BIGINT UNSIGNED NOT NULL,
    `student_id`      BIGINT UNSIGNED NOT NULL,
    `batch_id`        BIGINT UNSIGNED NOT NULL,
    `section_id`      BIGINT UNSIGNED NOT NULL,
    `academic_year`   VARCHAR(20)  NOT NULL DEFAULT '',
    `current_semester`TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `enrollment_date` DATE NOT NULL,
    `status`          ENUM('active','dropped','completed') NOT NULL DEFAULT 'active',
    `created_by`      BIGINT UNSIGNED NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_sse_student_section` (`student_id`, `section_id`),
    KEY `idx_sse_inst`    (`institution_id`),
    KEY `idx_sse_section` (`section_id`),
    KEY `idx_sse_batch`   (`batch_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`)     REFERENCES `students`(`id`)     ON DELETE CASCADE,
    FOREIGN KEY (`batch_id`)       REFERENCES `academic_batches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`section_id`)     REFERENCES `academic_sections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
