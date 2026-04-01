<?php
// Run this directly in your local environment or via phpMyAdmin
$sql = "
ALTER TABLE subjects 
ADD COLUMN short_label VARCHAR(100) NULL AFTER subject_name,
ADD COLUMN delivery_mode VARCHAR(100) NULL AFTER short_label,
ADD COLUMN priority_level VARCHAR(100) NULL AFTER delivery_mode,
ADD COLUMN curriculum_stream VARCHAR(100) NULL AFTER priority_level,
ADD COLUMN architecture VARCHAR(100) NULL AFTER curriculum_stream,
ADD COLUMN governing_body VARCHAR(100) NULL AFTER architecture,
ADD COLUMN is_sub_module TINYINT(1) DEFAULT 0 AFTER governing_body,
ADD COLUMN local_language TINYINT(1) DEFAULT 0 AFTER is_sub_module,
ADD COLUMN secondary_language TINYINT(1) DEFAULT 0 AFTER local_language,
ADD COLUMN valid_from DATE NULL AFTER secondary_language,
ADD COLUMN valid_until DATE NULL AFTER valid_from,
ADD COLUMN grading_scale VARCHAR(100) NULL AFTER valid_until,
ADD COLUMN external_exam_code VARCHAR(100) NULL AFTER grading_scale,
ADD COLUMN affects_gpa TINYINT(1) DEFAULT 0 AFTER external_exam_code,
ADD COLUMN review_authority VARCHAR(150) NULL AFTER affects_gpa,
ADD COLUMN attach_syllabus TINYINT(1) DEFAULT 0 AFTER review_authority,
ADD COLUMN track_sessions TINYINT(1) DEFAULT 0 AFTER attach_syllabus,

/* Advanced Enterprise Features */
ADD COLUMN credits DECIMAL(5,2) DEFAULT 0 AFTER track_sessions,
ADD COLUMN internal_marks DECIMAL(5,2) DEFAULT 0 AFTER credits,
ADD COLUMN external_marks DECIMAL(5,2) DEFAULT 0 AFTER internal_marks,
ADD COLUMN passing_marks DECIMAL(5,2) DEFAULT 0 AFTER external_marks,
ADD COLUMN passing_percentage DECIMAL(5,2) DEFAULT 0 AFTER passing_marks,
ADD COLUMN base_fee DECIMAL(10,2) DEFAULT 0 AFTER passing_percentage,
ADD COLUMN is_fee_refundable TINYINT(1) DEFAULT 0 AFTER base_fee,
ADD COLUMN min_attendance_percent INT DEFAULT 0 AFTER is_fee_refundable,
ADD COLUMN elearning_enabled TINYINT(1) DEFAULT 0 AFTER min_attendance_percent,
ADD COLUMN has_prerequisites TINYINT(1) DEFAULT 0 AFTER elearning_enabled;
";
