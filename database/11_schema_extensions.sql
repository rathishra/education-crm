-- ============================================================
-- MULTI-INSTITUTION EDUCATION CRM - DATABASE SCHEMA
-- Part 11: Schema Extensions (Attendance, Academics, Exams, Hostel, Transport, Library, HR)
-- ============================================================

USE `education_crm`;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. ATTENDANCE MANAGEMENT
-- ============================================================
CREATE TABLE `attendances` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `batch_id` BIGINT UNSIGNED NOT NULL,
  `subject_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'NULL for daily attendance',
  `date` DATE NOT NULL,
  `status` ENUM('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `remarks` VARCHAR(255) DEFAULT NULL,
  `marked_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_attendance_daily` (`student_id`, `date`, `subject_id`),
  KEY `idx_att_inst` (`institution_id`),
  KEY `idx_att_batch` (`batch_id`),
  KEY `idx_att_date` (`date`),
  CONSTRAINT `fk_att_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_att_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_att_marked` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. ACADEMICS & TIMETABLE
-- ============================================================
CREATE TABLE `subjects` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `department_id` BIGINT UNSIGNED NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('theory','practical','project','extra_curricular') NOT NULL DEFAULT 'theory',
  `credits` DECIMAL(4,2) DEFAULT 0.00,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_subject_code` (`institution_id`, `code`),
  CONSTRAINT `fk_sub_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_sub_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `timetables` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `batch_id` BIGINT UNSIGNED NOT NULL,
  `subject_id` BIGINT UNSIGNED NOT NULL,
  `faculty_id` BIGINT UNSIGNED NOT NULL COMMENT 'user_id of faculty',
  `day_of_week` ENUM('monday','tuesday','wednesday','thursday','friday','saturday','sunday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `room_number` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tt_batch` (`batch_id`),
  KEY `idx_tt_faculty` (`faculty_id`),
  CONSTRAINT `fk_tt_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`),
  CONSTRAINT `fk_tt_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`id`),
  CONSTRAINT `fk_tt_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `fk_tt_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. EXAMS & RESULTS
-- ============================================================
CREATE TABLE `exams` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL COMMENT 'Mid-term, Semester End, etc.',
  `type` ENUM('internal','semester','practical','entrance') NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('upcoming','ongoing','completed','published') NOT NULL DEFAULT 'upcoming',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_exam_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`),
  CONSTRAINT `fk_exam_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `exam_schedules` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_id` BIGINT UNSIGNED NOT NULL,
  `subject_id` BIGINT UNSIGNED NOT NULL,
  `date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `max_marks` DECIMAL(6,2) NOT NULL,
  `min_marks` DECIMAL(6,2) NOT NULL,
  `room_number` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_es_exam` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`),
  CONSTRAINT `fk_es_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `exam_marks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `exam_schedule_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `marks_obtained` DECIMAL(6,2) DEFAULT NULL,
  `grade` VARCHAR(5) DEFAULT NULL,
  `is_absent` TINYINT(1) NOT NULL DEFAULT 0,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_marks_student` (`exam_schedule_id`, `student_id`),
  CONSTRAINT `fk_marks_es` FOREIGN KEY (`exam_schedule_id`) REFERENCES `exam_schedules` (`id`),
  CONSTRAINT `fk_marks_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. HOSTEL MANAGEMENT
-- ============================================================
CREATE TABLE `hostels` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `type` ENUM('boys','girls','mixed') NOT NULL,
  `warden_name` VARCHAR(255) DEFAULT NULL,
  `warden_phone` VARCHAR(20) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_hostel_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hostel_rooms` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `hostel_id` BIGINT UNSIGNED NOT NULL,
  `room_number` VARCHAR(20) NOT NULL,
  `floor` VARCHAR(20) DEFAULT NULL,
  `capacity` TINYINT NOT NULL DEFAULT 1,
  `available_beds` TINYINT NOT NULL,
  `status` ENUM('active','maintenance','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_room_hostel` (`hostel_id`, `room_number`),
  CONSTRAINT `fk_room_hostel` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hostel_allocations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `hostel_room_id` BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `status` ENUM('active','vacated','cancelled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ha_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `fk_ha_room` FOREIGN KEY (`hostel_room_id`) REFERENCES `hostel_rooms` (`id`),
  CONSTRAINT `fk_ha_ay` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. TRANSPORT MANAGEMENT
-- ============================================================
CREATE TABLE `transport_routes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `start_point` VARCHAR(255) DEFAULT NULL,
  `end_point` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_route_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transport_stops` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `route_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `pickup_time` TIME DEFAULT NULL,
  `drop_time` TIME DEFAULT NULL,
  `sort_order` INT DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_stop_route` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transport_allocations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `route_id` BIGINT UNSIGNED NOT NULL,
  `stop_id` BIGINT UNSIGNED NOT NULL,
  `academic_year_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('active','cancelled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ta_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `fk_ta_route` FOREIGN KEY (`route_id`) REFERENCES `transport_routes` (`id`),
  CONSTRAINT `fk_ta_stop` FOREIGN KEY (`stop_id`) REFERENCES `transport_stops` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. LIBRARY MANAGEMENT
-- ============================================================
CREATE TABLE `library_books` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(255) DEFAULT NULL,
  `publisher` VARCHAR(255) DEFAULT NULL,
  `isbn` VARCHAR(50) DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `total_copies` INT NOT NULL DEFAULT 1,
  `available_copies` INT NOT NULL DEFAULT 1,
  `status` ENUM('active', 'inactive', 'disposed') NOT NULL DEFAULT 'active',
  `book_location` VARCHAR(100) DEFAULT NULL COMMENT 'Shelf/Rack no.',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_book_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `library_issues` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `book_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `fine_amount` DECIMAL(10,2) DEFAULT 0.00,
  `status` ENUM('issued','returned','lost','damaged') NOT NULL DEFAULT 'issued',
  `issued_by` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_issue_book` FOREIGN KEY (`book_id`) REFERENCES `library_books` (`id`),
  CONSTRAINT `fk_issue_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `fk_issue_by` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. HR & PAYROLL
-- ============================================================
CREATE TABLE `staff_profiles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `department_id` BIGINT UNSIGNED DEFAULT NULL,
  `designation` VARCHAR(100) DEFAULT NULL,
  `joining_date` DATE DEFAULT NULL,
  `qualification` VARCHAR(255) DEFAULT NULL,
  `total_experience_months` INT DEFAULT 0,
  `salary_package` DECIMAL(15,2) DEFAULT NULL,
  `bank_name` VARCHAR(100) DEFAULT NULL,
  `bank_account_number` VARCHAR(50) DEFAULT NULL,
  `bank_ifsc` VARCHAR(20) DEFAULT NULL,
  `pf_number` VARCHAR(50) DEFAULT NULL,
  `esi_number` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('working','on_leave','resigned','terminated') NOT NULL DEFAULT 'working',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_staff_user` (`user_id`),
  CONSTRAINT `fk_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_staff_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`),
  CONSTRAINT `fk_staff_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `staff_leave_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `leave_type` ENUM('casual','sick','earned','maternity','paternity','other') NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `reason` TEXT DEFAULT NULL,
  `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_lr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_lr_approved` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payslips` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `month` TINYINT UNSIGNED NOT NULL,
  `year` YEAR NOT NULL,
  `basic_salary` DECIMAL(12,2) NOT NULL,
  `allowances` DECIMAL(12,2) DEFAULT 0.00,
  `deductions` DECIMAL(12,2) DEFAULT 0.00,
  `net_salary` DECIMAL(12,2) NOT NULL,
  `status` ENUM('generated', 'processed', 'paid', 'cancelled') NOT NULL DEFAULT 'generated',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `payment_date` DATE DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payslip_user_date` (`user_id`, `month`, `year`),
  CONSTRAINT `fk_ps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ps_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. PLACEMENT CELL
-- ============================================================
CREATE TABLE `placement_companies` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `contact_person` VARCHAR(255) DEFAULT NULL,
  `contact_email` VARCHAR(255) DEFAULT NULL,
  `contact_phone` VARCHAR(20) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `placement_drives` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `institution_id` BIGINT UNSIGNED NOT NULL,
  `company_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `job_role` VARCHAR(255) NOT NULL,
  `salary_package` VARCHAR(100) DEFAULT NULL,
  `drive_date` DATE NOT NULL,
  `venue` VARCHAR(255) DEFAULT NULL,
  `eligibility_criteria` TEXT DEFAULT NULL,
  `status` ENUM('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pd_inst` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`),
  CONSTRAINT `fk_pd_company` FOREIGN KEY (`company_id`) REFERENCES `placement_companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 27. PLACEMENT APPLICATIONS (Renamed from selections)
-- ============================================================
CREATE TABLE `placement_applications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `drive_id` BIGINT UNSIGNED NOT NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('applied', 'shortlisted', 'interviewed', 'selected', 'rejected', 'on_hold') NOT NULL DEFAULT 'applied',
  `offer_package` DECIMAL(12,2) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ps_drive` (`drive_id`),
  KEY `idx_ps_student` (`student_id`),
  CONSTRAINT `fk_ps_drive` FOREIGN KEY (`drive_id`) REFERENCES `placement_drives` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ps_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
