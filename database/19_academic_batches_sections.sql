-- Academic Batches & Sections Schema Update
-- Run this in your database

CREATE TABLE IF NOT EXISTS `academic_batches` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `program_name` VARCHAR(200) NOT NULL,
    `batch_term` VARCHAR(100) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `max_intake` SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    `graduation_credits_required` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `total_semesters` TINYINT UNSIGNED NOT NULL DEFAULT 8,
    `status` ENUM('active', 'inactive', 'graduated') NOT NULL DEFAULT 'active',
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_acad_batch_inst` (`institution_id`),
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academic_sections` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `batch_id` BIGINT UNSIGNED NOT NULL,
    `institution_id` BIGINT UNSIGNED NOT NULL,
    `section_name` VARCHAR(50) NOT NULL,
    `default_classroom_id` BIGINT UNSIGNED NULL,
    `class_advisor_id` BIGINT UNSIGNED NULL,
    `capacity` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_acad_sec_batch` (`batch_id`),
    KEY `idx_acad_sec_inst` (`institution_id`),
    FOREIGN KEY (`batch_id`) REFERENCES `academic_batches`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
