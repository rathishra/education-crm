-- Organizations
CREATE TABLE `organizations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_name` VARCHAR(255) NOT NULL,
  `organization_code` VARCHAR(50) NOT NULL UNIQUE,
  `logo` VARCHAR(255) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(20) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `timezone` VARCHAR(100) DEFAULT 'UTC',
  `currency` VARCHAR(10) DEFAULT 'USD',
  `subscription_plan` VARCHAR(100) DEFAULT NULL,
  `subscription_start` DATE DEFAULT NULL,
  `subscription_end` DATE DEFAULT NULL,
  `max_institutions` INT DEFAULT 1,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- (To be applied to institutions)
ALTER TABLE `institutions`
ADD COLUMN `organization_id` BIGINT UNSIGNED NOT NULL AFTER `id`,
ADD COLUMN `institution_type` VARCHAR(100) DEFAULT NULL AFTER `code`,
ADD COLUMN `affiliation` VARCHAR(255) DEFAULT NULL AFTER `institution_type`,
ADD COLUMN `university` VARCHAR(255) DEFAULT NULL AFTER `affiliation`,
ADD COLUMN `establishment_year` YEAR DEFAULT NULL AFTER `university`,
ADD COLUMN `principal_name` VARCHAR(255) DEFAULT NULL AFTER `website`,
ADD COLUMN `principal_contact` VARCHAR(50) DEFAULT NULL AFTER `principal_name`,
ADD CONSTRAINT `fk_inst_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`);

-- Campuses
CREATE TABLE `campuses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `campus_name` VARCHAR(255) NOT NULL,
  `campus_code` VARCHAR(50) NOT NULL UNIQUE,
  `address` TEXT DEFAULT NULL,
  `contact` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_campus_org` FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`),
  CONSTRAINT `fk_campus_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ... (Departments, Courses, Batches, Sections - adding organization_id where needed constraints)
