-- ============================================================
-- SEED FACULTY TEST USER
-- Run this once in your MySQL client / phpMyAdmin
-- Password for all users below: Faculty@123
-- Bcrypt hash of "Faculty@123" generated with PHP cost=10
-- ============================================================

USE `education_crm`;

-- ── 1. Insert three demo faculty users ──────────────────────
INSERT INTO `users`
    (`employee_id`, `first_name`, `last_name`, `email`, `phone`, `password`, `email_verified_at`, `is_active`)
VALUES
    ('FAC001', 'Rajesh',  'Kumar',   'rajesh.kumar@educrm.com',   '9876543210',
     '$2y$10$TKh8H1.PfuBkoRrEE3K/UeA7LcKR6D.8gVAlgBKUkh3WbVGQbMzHm', NOW(), 1),

    ('FAC002', 'Priya',   'Sharma',  'priya.sharma@educrm.com',   '9876543211',
     '$2y$10$TKh8H1.PfuBkoRrEE3K/UeA7LcKR6D.8gVAlgBKUkh3WbVGQbMzHm', NOW(), 1),

    ('FAC003', 'Anil',    'Verma',   'anil.verma@educrm.com',     '9876543212',
     '$2y$10$TKh8H1.PfuBkoRrEE3K/UeA7LcKR6D.8gVAlgBKUkh3WbVGQbMzHm', NOW(), 1);

-- ── 2. Assign Faculty role (role_id=7) to institution_id=1 ──
-- Adjust institution_id to match your setup if different
INSERT INTO `user_roles` (`user_id`, `role_id`, `organization_id`, `institution_id`)
SELECT u.id, 7, 1, 1
FROM `users` u
WHERE u.email IN (
    'rajesh.kumar@educrm.com',
    'priya.sharma@educrm.com',
    'anil.verma@educrm.com'
);

-- ── 3. Create staff profiles ──────────────────────────────────
INSERT INTO `staff_profiles`
    (`user_id`, `institution_id`, `department_id`, `designation`,
     `joining_date`, `qualification`, `total_experience_months`, `salary_package`, `status`)
VALUES
    ((SELECT id FROM users WHERE email='rajesh.kumar@educrm.com'),
     1, 1, 'Associate Professor', '2020-07-01', 'M.Tech Computer Science', 72, 720000.00, 'working'),

    ((SELECT id FROM users WHERE email='priya.sharma@educrm.com'),
     1, 1, 'Assistant Professor', '2022-01-15', 'M.Sc Mathematics', 38, 560000.00, 'working'),

    ((SELECT id FROM users WHERE email='anil.verma@educrm.com'),
     1, 1, 'Senior Lecturer', '2019-06-01', 'MBA', 84, 650000.00, 'working');

-- ── Done ─────────────────────────────────────────────────────
SELECT
    u.id,
    CONCAT(u.first_name,' ',u.last_name) AS name,
    u.email,
    'Faculty@123' AS password,
    r.name AS role
FROM users u
JOIN user_roles ur ON ur.user_id = u.id
JOIN roles r ON r.id = ur.role_id
WHERE u.email IN (
    'rajesh.kumar@educrm.com',
    'priya.sharma@educrm.com',
    'anil.verma@educrm.com'
);
