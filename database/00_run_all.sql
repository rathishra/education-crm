-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM
-- Master Script - Run all SQL files in order
-- ============================================================
-- Execute each file in sequence:
--   1. mysql -u root -p < 01_schema_core.sql
--   2. mysql -u root -p < 02_schema_auth.sql
--   3. mysql -u root -p < 03_schema_leads.sql
--   4. mysql -u root -p < 04_schema_followups.sql
--   5. mysql -u root -p < 05_schema_courses.sql
--   6. mysql -u root -p < 06_schema_students.sql
--   7. mysql -u root -p < 07_schema_fees.sql
--   8. mysql -u root -p < 08_schema_communication.sql
--   9. mysql -u root -p < 09_schema_settings.sql
--  10. mysql -u root -p < 10_seed_data.sql
--
-- Or run this file which sources them all:

SOURCE 01_schema_core.sql;
SOURCE 02_schema_auth.sql;
SOURCE 03_schema_leads.sql;
SOURCE 04_schema_followups.sql;
SOURCE 05_schema_courses.sql;
SOURCE 06_schema_students.sql;
SOURCE 07_schema_fees.sql;
SOURCE 08_schema_communication.sql;
SOURCE 09_schema_settings.sql;
SOURCE 10_seed_data.sql;

-- Verification: Show all tables
SELECT TABLE_NAME, TABLE_ROWS, ENGINE
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'education_crm'
ORDER BY TABLE_NAME;
