-- ============================================================
-- FOLLOWUPS TABLE ENHANCEMENT MIGRATION
-- Part 17: Add extended columns for Follow-up Management
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- STEP 1: ADD new columns
-- ============================================================

-- enquiry_id after lead_id
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `enquiry_id` BIGINT UNSIGNED NULL AFTER `lead_id`;

-- followup_date after scheduled_at (extracted date for filtering)
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `followup_date` DATE NULL AFTER `scheduled_at`;

-- followup_time after followup_date (extracted time)
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `followup_time` TIME NULL AFTER `followup_date`;

-- followup_mode after type
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `followup_mode` ENUM('call','whatsapp','email','visit','meeting') NULL AFTER `type`;

-- response after outcome
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `response` ENUM('interested','not_interested','call_back','no_response') NULL AFTER `outcome`;

-- next_followup_date
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `next_followup_date` DATE NULL;

-- next_followup_time
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `next_followup_time` TIME NULL;

-- remarks
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `remarks` TEXT NULL;

-- rescheduled_from — original followup id
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `rescheduled_from` BIGINT UNSIGNED NULL;

-- updated_by
ALTER TABLE `followups`
    ADD COLUMN IF NOT EXISTS `updated_by` BIGINT UNSIGNED NULL;

-- ============================================================
-- STEP 2: Back-fill followup_date, followup_time, followup_mode
--         from existing scheduled_at and type columns
-- ============================================================

UPDATE `followups`
SET
    `followup_date` = DATE(`scheduled_at`),
    `followup_time` = TIME(`scheduled_at`),
    `followup_mode` = CASE `type`
        WHEN 'call'     THEN 'call'
        WHEN 'whatsapp' THEN 'whatsapp'
        WHEN 'email'    THEN 'email'
        WHEN 'visit'    THEN 'visit'
        WHEN 'meeting'  THEN 'meeting'
        ELSE NULL
    END
WHERE `scheduled_at` IS NOT NULL;

-- ============================================================
-- STEP 3: Indexes
-- ============================================================

ALTER TABLE `followups`
    ADD INDEX IF NOT EXISTS `idx_fu_enquiry_id`        (`enquiry_id`),
    ADD INDEX IF NOT EXISTS `idx_fu_followup_date`     (`followup_date`),
    ADD INDEX IF NOT EXISTS `idx_fu_next_followup_date`(`next_followup_date`),
    ADD INDEX IF NOT EXISTS `idx_fu_response`          (`response`);

-- ============================================================
-- STEP 4: Foreign Keys
-- ============================================================

-- FK: enquiry_id -> enquiries(id)
ALTER TABLE `followups`
    ADD CONSTRAINT `fk_fu_enquiry`
    FOREIGN KEY IF NOT EXISTS (`enquiry_id`) REFERENCES `enquiries`(`id`) ON DELETE SET NULL;

-- FK: rescheduled_from -> followups(id)
ALTER TABLE `followups`
    ADD CONSTRAINT `fk_fu_rescheduled_from`
    FOREIGN KEY IF NOT EXISTS (`rescheduled_from`) REFERENCES `followups`(`id`) ON DELETE SET NULL;

-- FK: updated_by -> users(id)
ALTER TABLE `followups`
    ADD CONSTRAINT `fk_fu_updated_by`
    FOREIGN KEY IF NOT EXISTS (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS = 1;
