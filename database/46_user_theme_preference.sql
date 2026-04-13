-- ============================================================
-- Migration 46: User Theme Preferences
-- Adds theme_preference, font_size, sidebar_style to users table
-- ============================================================

ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `theme_preference` ENUM('default','dark','blue','green','rose','purple') NOT NULL DEFAULT 'default' AFTER `avatar`,
    ADD COLUMN IF NOT EXISTS `font_size`         ENUM('small','default','large')                      NOT NULL DEFAULT 'default' AFTER `theme_preference`,
    ADD COLUMN IF NOT EXISTS `sidebar_style`     ENUM('expanded','compact')                           NOT NULL DEFAULT 'expanded' AFTER `font_size`;
