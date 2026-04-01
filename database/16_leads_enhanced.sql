-- ============================================================
-- LEADS TABLE ENHANCEMENT MIGRATION
-- Part 16: Add extended columns, new priority values, and lead_followups table
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- STEP 1: Remap existing priority values before MODIFY COLUMN
-- Maps old 4-value ENUM to new 3-value ENUM safely
-- ============================================================
UPDATE `leads` SET `priority` = 'warm' WHERE `priority` IN ('low', 'medium');
UPDATE `leads` SET `priority` = 'hot'  WHERE `priority` IN ('high', 'urgent');

-- ============================================================
-- STEP 2: MODIFY priority column to new ENUM
-- ============================================================
ALTER TABLE `leads`
    MODIFY COLUMN `priority` ENUM('hot', 'warm', 'cold') NOT NULL DEFAULT 'warm';

-- ============================================================
-- STEP 3: ADD new columns
-- Academic section
-- ============================================================

-- department_id after course_interested_id
ALTER TABLE `leads`
    ADD COLUMN `department_id` BIGINT UNSIGNED NULL AFTER `course_interested_id`;

-- academic_year after department_id
ALTER TABLE `leads`
    ADD COLUMN `academic_year` VARCHAR(20) NULL AFTER `department_id`;

-- preferred_mode after academic_year
ALTER TABLE `leads`
    ADD COLUMN `preferred_mode` ENUM('online', 'offline', 'hybrid') NOT NULL DEFAULT 'offline' AFTER `academic_year`;

-- ============================================================
-- Qualification / Scoring section
-- ============================================================

-- lead_score after priority
ALTER TABLE `leads`
    ADD COLUMN `lead_score` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0-100 score' AFTER `priority`;

-- expected_join_date after lead_score
ALTER TABLE `leads`
    ADD COLUMN `expected_join_date` DATE NULL AFTER `lead_score`;

-- budget after expected_join_date
ALTER TABLE `leads`
    ADD COLUMN `budget` DECIMAL(10, 2) NULL AFTER `expected_join_date`;

-- ============================================================
-- Source attribution section
-- ============================================================

-- campaign_name after notes
ALTER TABLE `leads`
    ADD COLUMN `campaign_name` VARCHAR(150) NULL AFTER `notes`;

-- reference_name after campaign_name
ALTER TABLE `leads`
    ADD COLUMN `reference_name` VARCHAR(150) NULL AFTER `campaign_name`;

-- ============================================================
-- Follow-up scheduling section
-- ============================================================

-- next_followup_date after last_contacted_at
ALTER TABLE `leads`
    ADD COLUMN `next_followup_date` DATE NULL AFTER `last_contacted_at`;

-- followup_mode after next_followup_date
ALTER TABLE `leads`
    ADD COLUMN `followup_mode` ENUM('call', 'whatsapp', 'visit', 'meeting', 'email') NULL AFTER `next_followup_date`;

-- last_followup_date after followup_mode
ALTER TABLE `leads`
    ADD COLUMN `last_followup_date` DATE NULL AFTER `followup_mode`;

-- ============================================================
-- Additional requirements section
-- ============================================================

-- hostel_required after lost_reason
ALTER TABLE `leads`
    ADD COLUMN `hostel_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `lost_reason`;

-- transport_required after hostel_required
ALTER TABLE `leads`
    ADD COLUMN `transport_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `hostel_required`;

-- scholarship_required after transport_required
ALTER TABLE `leads`
    ADD COLUMN `scholarship_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `transport_required`;

-- ============================================================
-- STEP 4: ADD indexes for new columns
-- ============================================================
ALTER TABLE `leads`
    ADD KEY `idx_lead_dept`         (`department_id`),
    ADD KEY `idx_lead_next_followup` (`next_followup_date`),
    ADD KEY `idx_lead_score`        (`lead_score`);

-- ============================================================
-- STEP 5: ADD foreign key — department_id → departments(id)
-- ============================================================
ALTER TABLE `leads`
    ADD CONSTRAINT `fk_lead_dept`
        FOREIGN KEY (`department_id`)
        REFERENCES `departments` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE;

-- ============================================================
-- STEP 6: CREATE lead_followups table
-- Dedicated followup log for the leads pipeline
-- ============================================================
CREATE TABLE IF NOT EXISTS `lead_followups` (
  `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id`            BIGINT UNSIGNED NOT NULL,
  `institution_id`     BIGINT UNSIGNED NOT NULL,
  `counselor_id`       BIGINT UNSIGNED NULL,
  `followup_date`      DATE NOT NULL,
  `followup_mode`      ENUM('call', 'whatsapp', 'visit', 'meeting', 'email') NOT NULL DEFAULT 'call',
  `duration_minutes`   SMALLINT UNSIGNED NULL COMMENT 'Call/meeting duration',
  `status`             ENUM('scheduled', 'completed', 'missed', 'cancelled') NOT NULL DEFAULT 'scheduled',
  `outcome`            ENUM('interested', 'not_interested', 'callback', 'converted', 'no_response') NULL,
  `notes`              TEXT NULL,
  `next_followup_date` DATE NULL,
  `next_followup_mode` ENUM('call', 'whatsapp', 'visit', 'meeting', 'email') NULL,
  `created_by`         BIGINT UNSIGNED NULL,
  `created_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lf_lead`     (`lead_id`),
  KEY `idx_lf_inst`     (`institution_id`),
  KEY `idx_lf_counselor` (`counselor_id`),
  KEY `idx_lf_date`     (`followup_date`),
  KEY `idx_lf_status`   (`status`),
  CONSTRAINT `fk_lf_lead`
      FOREIGN KEY (`lead_id`)       REFERENCES `leads`        (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lf_inst`
      FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lf_counselor`
      FOREIGN KEY (`counselor_id`)  REFERENCES `users`        (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_lf_created_by`
      FOREIGN KEY (`created_by`)    REFERENCES `users`        (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
