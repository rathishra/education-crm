-- Academic Year Permissions
USE `education_crm`;
INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES
('View Academic Years', 'academic_years.view', 'institutions'),
('Create Academic Year', 'academic_years.create', 'institutions'),
('Edit Academic Year', 'academic_years.edit', 'institutions'),
('Delete Academic Year', 'academic_years.delete', 'institutions');

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions` WHERE `slug` LIKE 'academic_years.%';

INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions` WHERE `slug` LIKE 'academic_years.%';
