-- =====================================================
-- ADD PROGRESS COLUMN TO CASE TABLE
-- Allows advocates to update case progress
-- =====================================================
-- 
-- INSTRUCTIONS:
-- 1. Open phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Select the lawfirm_db database
-- 3. Go to "SQL" tab
-- 4. Copy and paste this file
-- 5. Click "Go"
-- 
-- Note: If the column already exists, you'll get an error.
-- That's okay - just ignore it and continue.
-- =====================================================

USE lawfirm_db;

-- Check if column exists before adding (MySQL doesn't support IF NOT EXISTS for ALTER TABLE)
-- If you get an error that the column already exists, you can safely ignore it
ALTER TABLE `CASE`
ADD COLUMN Progress TEXT DEFAULT NULL;
