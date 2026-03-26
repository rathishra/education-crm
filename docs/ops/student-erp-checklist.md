# Student ERP + CRM Operations Checklist

This runbook is for the current project in `D:\CRM` (custom PHP MVC app with SQL schema files).

## 1. Pre-Deployment

1. Confirm environment values in `.env`:
   - `APP_URL`
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
2. Verify writable paths:
   - `storage/logs`
   - `public/uploads`
3. Ensure PHP extensions required by the app are enabled:
   - `pdo_mysql`
   - `mbstring`
   - `openssl`

## 2. Database Setup / Upgrade

The app uses SQL files in `database/`.

1. Create database (if needed): `education_crm`.
2. Import schema files in order (via phpMyAdmin or MySQL `SOURCE`):
   - `01_schema_core.sql`
   - `02_schema_auth.sql`
   - `03_schema_leads.sql`
   - `04_schema_followups.sql`
   - `05_schema_courses.sql`
   - `06_schema_students.sql`
   - `07_schema_fees.sql`
   - `08_schema_communication.sql`
   - `09_schema_settings.sql`
   - `10_seed_data.sql`
   - `11_schema_extensions.sql`
   - `13_organization_schema.sql`
3. Quick validation queries:
   - `SHOW TABLES LIKE 'students';`
   - `SHOW TABLES LIKE 'student_documents';`
   - `SHOW TABLES LIKE 'subjects';`
   - `SHOW COLUMNS FROM organizations LIKE 'organization_name';`
   - `SHOW COLUMNS FROM organizations LIKE 'deleted_at';`

## 3. App Runtime

1. Start web server (XAMPP Apache recommended) with document root pointing to:
   - `D:\CRM\public`
2. Verify entry endpoint:
   - `/public/index.php` loads without PHP fatal errors.
3. Login and test critical pages:
   - Dashboard
   - Leads
   - Admissions
   - Students
   - Organizations

## 4. Student ERP Smoke Test

1. Create a student.
2. Edit student profile fields.
3. Open student detail/360 view.
4. Upload at least one document and verify download.
5. Add behaviour/timeline entries (if enabled in UI).
6. Confirm list filters:
   - name
   - roll number
   - mobile
   - status
   - course/batch/semester

## 5. CRM to SIS Flow Validation

1. Create a lead.
2. Move lead through statuses to conversion/won.
3. Run conversion action.
4. Confirm:
   - lead status updated
   - student/admission linkage created (as implemented in current module)
   - audit log row inserted

## 6. Logs & Monitoring

1. Application log path:
   - `storage/logs/YYYY-MM-DD.log`
2. Check for these errors after deployment:
   - `SQLSTATE`
   - `Unknown column`
   - `Base table or view not found`
   - `Call to undefined`
3. Keep a daily log review for first 7 days post-release.

## 7. Rollback Plan

1. Back up database before schema imports.
2. If release fails:
   - restore DB backup
   - revert changed PHP files to previous release package
   - clear browser/opcache if enabled
3. Re-run smoke test on rolled-back version.

## 8. Future Laravel Module Notes

The folder `laravel_org_module` currently contains app code fragments only.
It does **not** yet include full Laravel runtime artifacts (`artisan`, `composer.json`, vendor tree).
Do not run Laravel deployment commands in this repository until that runtime is added.
