-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - COMPLETE DATABASE SCHEMA
-- Part 1: Core Tables (Organizations, Institutions, Departments)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `education_crm`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `education_crm`;

-- ============================================================
-- 1. ORGANIZATIONS
-- ============================================================
CREATE TABLE `organizations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_name` VARCHAR(255) NOT NULL,
  `organization_code` VARCHAR(50) NOT NULL,
  `logo` VARCHAR(500) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `max_institutions` INT NOT NULL DEFAULT 1,
  `gst_number` VARCHAR(20) DEFAULT NULL,
  `pan_number` VARCHAR(20) DEFAULT NULL,
  `established_year` YEAR DEFAULT NULL,
  `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `settings` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_org_code` (`organization_code`),
  KEY `idx_org_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. INSTITUTIONS
-- ============================================================
CREATE TABLE `institutions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `type` ENUM('engineering','arts_science','medical','nursing','polytechnic','other') NOT NULL,
  `logo` VARCHAR(500) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `address_line1` VARCHAR(255) DEFAULT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'India',
  `pincode` VARCHAR(10) DEFAULT NULL,
  `affiliation_number` VARCHAR(100) DEFAULT NULL,
  `affiliation_body` VARCHAR(255) DEFAULT NULL,
  `established_year` YEAR DEFAULT NULL,
  `principal_name` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `settings` JSON DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inst_code` (`code`),
  KEY `idx_inst_org` (`organization_id`),
  KEY `idx_inst_type` (`type`),
  KEY `idx_inst_status` (`status`),
  CONSTRAINT `fk_inst_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. DEPARTMENTS
-- ============================================================
CREATE TABLE `departments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `hod_name` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dept_inst_code` (`institution_id`, `code`),
  KEY `idx_dept_status` (`status`),
  CONSTRAINT `fk_dept_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. ACADEMIC YEARS
-- ============================================================
CREATE TABLE `academic_years` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `is_current` TINYINT(1) NOT NULL DEFAULT 0,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ay_inst` (`institution_id`),
  KEY `idx_ay_current` (`is_current`),
  CONSTRAINT `fk_ay_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
