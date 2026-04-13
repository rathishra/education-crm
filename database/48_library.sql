-- ============================================================
-- Migration 48: Library Management
-- Tables: books, issued_books
-- ============================================================

-- ── Books ─────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `books` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `institution_id`     INT UNSIGNED NOT NULL DEFAULT 0,
    `accession_number`   VARCHAR(30)  NOT NULL,
    `title`              VARCHAR(255) NOT NULL,
    `author`             VARCHAR(255) NOT NULL,
    `isbn`               VARCHAR(20)  DEFAULT NULL,
    `category`           VARCHAR(100) DEFAULT NULL,
    `publisher`          VARCHAR(150) DEFAULT NULL,
    `edition`            VARCHAR(50)  DEFAULT NULL,
    `publication_year`   YEAR         DEFAULT NULL,
    `quantity`           SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `available_quantity` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `shelf_location`     VARCHAR(50)  DEFAULT NULL,
    `language`           VARCHAR(30)  NOT NULL DEFAULT 'English',
    `price`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `cover_image`        VARCHAR(255) DEFAULT NULL,
    `description`        TEXT         DEFAULT NULL,
    `status`             ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `deleted_at`         DATETIME     DEFAULT NULL,
    `created_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_accession` (`institution_id`, `accession_number`),
    INDEX `idx_books_institution` (`institution_id`),
    INDEX `idx_books_category`    (`category`),
    INDEX `idx_books_status`      (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Issued Books ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `issued_books` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `institution_id`   INT UNSIGNED NOT NULL DEFAULT 0,
    `book_id`          INT UNSIGNED NOT NULL,
    `student_id`       INT UNSIGNED NOT NULL,
    `issued_by`        INT UNSIGNED NOT NULL DEFAULT 0,
    `issued_date`      DATE         NOT NULL,
    `due_date`         DATE         NOT NULL,
    `return_date`      DATE         DEFAULT NULL,
    `fine_per_day`     DECIMAL(6,2) NOT NULL DEFAULT 1.00,
    `fine_amount`      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `fine_paid`        TINYINT(1)   NOT NULL DEFAULT 0,
    `remarks`          TEXT         DEFAULT NULL,
    `status`           ENUM('issued','returned','overdue','lost') NOT NULL DEFAULT 'issued',
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_issued_institution` (`institution_id`),
    INDEX `idx_issued_book`        (`book_id`),
    INDEX `idx_issued_student`     (`student_id`),
    INDEX `idx_issued_status`      (`status`),
    INDEX `idx_issued_due`         (`due_date`),
    CONSTRAINT `fk_issued_book`
        FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
