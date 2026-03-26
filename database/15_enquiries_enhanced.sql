-- ============================================================
-- ENQUIRY TABLE ENHANCEMENT MIGRATION
-- Part 15: Add extended columns for Enquiry Management
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- Add gender column after last_name
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `gender` ENUM('male','female','other') NULL AFTER `last_name`;

-- Add date_of_birth after gender
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `date_of_birth` DATE NULL AFTER `gender`;

-- Add department_id after course_interested_id
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `department_id` BIGINT UNSIGNED NULL AFTER `course_interested_id`;

-- Add academic_year after department_id
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `academic_year` VARCHAR(20) NULL AFTER `department_id`;

-- Add preferred_mode after academic_year
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `preferred_mode` ENUM('online','offline','hybrid') NOT NULL DEFAULT 'offline' AFTER `academic_year`;

-- Add campaign_name after source
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `campaign_name` VARCHAR(150) NULL AFTER `source`;

-- Add reference_name after campaign_name
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `reference_name` VARCHAR(150) NULL AFTER `campaign_name`;

-- Add counselor_id after reference_name
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `counselor_id` BIGINT UNSIGNED NULL AFTER `reference_name`;

-- Add remarks after message
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `remarks` TEXT NULL AFTER `message`;

-- Add priority after status
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `priority` ENUM('hot','warm','cold') NOT NULL DEFAULT 'warm' AFTER `status`;

-- Add next_followup_date after priority
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `next_followup_date` DATE NULL AFTER `priority`;

-- Add followup_mode after next_followup_date
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `followup_mode` ENUM('call','whatsapp','visit','email') NULL AFTER `next_followup_date`;

-- Add hostel_required after followup_mode
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `hostel_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `followup_mode`;

-- Add transport_required after hostel_required
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `transport_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `hostel_required`;

-- Add scholarship_required after transport_required
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `scholarship_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `transport_required`;

-- Add deleted_at for soft deletes
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `deleted_at` DATETIME NULL AFTER `updated_at`;

-- Add created_by column
ALTER TABLE `enquiries`
    ADD COLUMN IF NOT EXISTS `created_by` BIGINT UNSIGNED NULL AFTER `assigned_to`;

-- Modify status ENUM to include new values
-- MySQL does not support ADD COLUMN IF NOT EXISTS for MODIFY, so use direct MODIFY
ALTER TABLE `enquiries`
    MODIFY COLUMN `status` ENUM('new','contacted','interested','not_interested','converted','closed') NOT NULL DEFAULT 'new';

-- ============================================================
-- Foreign Keys
-- ============================================================

-- FK: counselor_id -> users(id)
ALTER TABLE `enquiries`
    ADD CONSTRAINT `fk_enquiries_counselor`
    FOREIGN KEY IF NOT EXISTS (`counselor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;

-- FK: department_id -> departments(id)
ALTER TABLE `enquiries`
    ADD CONSTRAINT `fk_enquiries_department`
    FOREIGN KEY IF NOT EXISTS (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL;

-- ============================================================
-- Indexes
-- ============================================================

-- Index: phone
ALTER TABLE `enquiries`
    ADD INDEX IF NOT EXISTS `idx_enquiries_phone` (`phone`);

-- Index: email
ALTER TABLE `enquiries`
    ADD INDEX IF NOT EXISTS `idx_enquiries_email` (`email`);

-- Index: priority
ALTER TABLE `enquiries`
    ADD INDEX IF NOT EXISTS `idx_enquiries_priority` (`priority`);

-- Index: next_followup_date
ALTER TABLE `enquiries`
    ADD INDEX IF NOT EXISTS `idx_enquiries_followup_date` (`next_followup_date`);

-- Index: department_id
ALTER TABLE `enquiries`
    ADD INDEX IF NOT EXISTS `idx_enquiries_department_id` (`department_id`);

-- Index: deleted_at (for soft delete queries)
ALTER TABLE `enquiries`
    ADD INDEX IF NOT EXISTS `idx_enquiries_deleted_at` (`deleted_at`);

SET FOREIGN_KEY_CHECKS = 1;
