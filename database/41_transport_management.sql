-- ============================================================
-- Migration 41: Transport Management Tables
-- Creates transport_routes, transport_stops, transport_allocations
-- Safe to run: uses CREATE TABLE IF NOT EXISTS
-- ============================================================

CREATE TABLE IF NOT EXISTS `transport_routes` (
  `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `name`           VARCHAR(255) NOT NULL,
  `start_point`    VARCHAR(255) DEFAULT NULL,
  `end_point`      VARCHAR(255) DEFAULT NULL,
  `description`    TEXT DEFAULT NULL,
  `fare`           DECIMAL(10,2) DEFAULT 0.00,
  `status`         ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_route_inst` (`institution_id`),
  CONSTRAINT `fk_route_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_stops` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `route_id`    BIGINT UNSIGNED NOT NULL,
  `name`        VARCHAR(255) NOT NULL,
  `landmark`    VARCHAR(255) DEFAULT NULL,
  `pickup_time` TIME DEFAULT NULL,
  `drop_time`   TIME DEFAULT NULL,
  `fare`        DECIMAL(10,2) DEFAULT 0.00,
  `sort_order`  INT DEFAULT 0,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stop_route` (`route_id`),
  CONSTRAINT `fk_stop_route` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transport_allocations` (
  `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id`       BIGINT UNSIGNED NOT NULL,
  `route_id`         BIGINT UNSIGNED NOT NULL,
  `stop_id`          BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `status`           ENUM('active','cancelled') NOT NULL DEFAULT 'active',
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ta_student` (`student_id`),
  CONSTRAINT `fk_ta_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ta_route`   FOREIGN KEY (`route_id`)   REFERENCES `transport_routes` (`id`),
  CONSTRAINT `fk_ta_stop`    FOREIGN KEY (`stop_id`)    REFERENCES `transport_stops` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
