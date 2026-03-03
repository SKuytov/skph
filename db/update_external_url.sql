-- Add external download URL field to sessions table
-- Run this in phpMyAdmin if you already have the database set up

ALTER TABLE `sessions`
ADD COLUMN `external_download_url` VARCHAR(500) NULL AFTER `downloads_enabled`;
