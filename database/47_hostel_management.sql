-- ============================================================
-- Migration 47: Hostel Management
-- Tables: hostels, hostel_rooms, hostel_allocations
-- ============================================================

-- ── Hostels ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hostels` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `institution_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `name`           VARCHAR(150) NOT NULL,
    `type`           ENUM('boys','girls','co-ed','staff') NOT NULL DEFAULT 'boys',
    `warden_name`    VARCHAR(100) DEFAULT NULL,
    `warden_phone`   VARCHAR(20)  DEFAULT NULL,
    `address`        TEXT         DEFAULT NULL,
    `capacity`       INT UNSIGNED NOT NULL DEFAULT 0,
    `status`         ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_hostels_institution` (`institution_id`),
    INDEX `idx_hostels_status`      (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Hostel Rooms ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hostel_rooms` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `hostel_id`      INT UNSIGNED NOT NULL,
    `room_number`    VARCHAR(20)  NOT NULL,
    `floor`          VARCHAR(20)  DEFAULT NULL,
    `room_type`      ENUM('single','double','triple','dormitory') NOT NULL DEFAULT 'double',
    `capacity`       TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `available_beds` TINYINT UNSIGNED NOT NULL DEFAULT 2,
    `monthly_fee`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status`         ENUM('active','inactive','maintenance') NOT NULL DEFAULT 'active',
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_hostel_rooms_hostel`  (`hostel_id`),
    INDEX `idx_hostel_rooms_status`  (`status`),
    CONSTRAINT `fk_hostel_rooms_hostel`
        FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Hostel Allocations ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `hostel_allocations` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `student_id`       INT UNSIGNED NOT NULL,
    `hostel_room_id`   INT UNSIGNED NOT NULL,
    `academic_year_id` INT UNSIGNED NOT NULL DEFAULT 0,
    `start_date`       DATE         NOT NULL,
    `end_date`         DATE         DEFAULT NULL,
    `status`           ENUM('active','vacated','transferred') NOT NULL DEFAULT 'active',
    `remarks`          TEXT DEFAULT NULL,
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_hostel_alloc_student`  (`student_id`),
    INDEX `idx_hostel_alloc_room`     (`hostel_room_id`),
    INDEX `idx_hostel_alloc_ay`       (`academic_year_id`),
    INDEX `idx_hostel_alloc_status`   (`status`),
    CONSTRAINT `fk_hostel_alloc_room`
        FOREIGN KEY (`hostel_room_id`) REFERENCES `hostel_rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
