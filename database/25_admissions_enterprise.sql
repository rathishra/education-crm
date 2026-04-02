-- ============================================================
-- ADMISSIONS ENTERPRISE ENHANCEMENT
-- Part 25: Interview scheduling, offer letters, merit ranking,
--           hostel/transport, enhanced workflow columns
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- Interview scheduling columns
ALTER TABLE `admissions`
    ADD COLUMN IF NOT EXISTS `interview_date`    DATE         NULL AFTER `remarks`,
    ADD COLUMN IF NOT EXISTS `interview_time`    TIME         NULL AFTER `interview_date`,
    ADD COLUMN IF NOT EXISTS `interview_mode`    ENUM('in_person','online','phone') DEFAULT 'in_person' AFTER `interview_time`,
    ADD COLUMN IF NOT EXISTS `interview_venue`   VARCHAR(255) NULL AFTER `interview_mode`,
    ADD COLUMN IF NOT EXISTS `interview_panel`   VARCHAR(255) NULL AFTER `interview_venue`,
    ADD COLUMN IF NOT EXISTS `interview_result`  ENUM('pending','passed','failed','on_hold') DEFAULT 'pending' AFTER `interview_panel`,
    ADD COLUMN IF NOT EXISTS `interview_score`   DECIMAL(5,2) NULL AFTER `interview_result`,
    ADD COLUMN IF NOT EXISTS `interview_notes`   TEXT         NULL AFTER `interview_score`;

-- Merit & ranking
ALTER TABLE `admissions`
    ADD COLUMN IF NOT EXISTS `merit_rank`        INT UNSIGNED NULL AFTER `interview_notes`,
    ADD COLUMN IF NOT EXISTS `merit_score`       DECIMAL(5,2) NULL AFTER `merit_rank`;

-- Hostel / transport
ALTER TABLE `admissions`
    ADD COLUMN IF NOT EXISTS `hostel_required`   TINYINT(1)   NOT NULL DEFAULT 0 AFTER `merit_score`,
    ADD COLUMN IF NOT EXISTS `transport_required` TINYINT(1)  NOT NULL DEFAULT 0 AFTER `hostel_required`;

-- Rejection / cancellation reasons
ALTER TABLE `admissions`
    ADD COLUMN IF NOT EXISTS `rejection_reason`    TEXT NULL AFTER `transport_required`,
    ADD COLUMN IF NOT EXISTS `cancellation_reason` TEXT NULL AFTER `rejection_reason`;

-- Offer / admission letter tracking
ALTER TABLE `admissions`
    ADD COLUMN IF NOT EXISTS `offer_letter_sent_at`     TIMESTAMP NULL AFTER `cancellation_reason`,
    ADD COLUMN IF NOT EXISTS `admission_letter_sent_at` TIMESTAMP NULL AFTER `offer_letter_sent_at`;

-- Indexes for new columns
ALTER TABLE `admissions`
    ADD INDEX IF NOT EXISTS `idx_adm_interview_date`   (`interview_date`),
    ADD INDEX IF NOT EXISTS `idx_adm_interview_result` (`interview_result`),
    ADD INDEX IF NOT EXISTS `idx_adm_merit_rank`       (`merit_rank`);

SET FOREIGN_KEY_CHECKS = 1;
