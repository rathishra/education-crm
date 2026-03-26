-- ============================================================
-- Demo seed data for Education CRM - Client Demo
-- Database: education_crm (MySQL/MariaDB)
-- Safe to run multiple times (uses INSERT IGNORE where possible)
-- Generated: 2026-03-26
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

-- ============================================================
-- CLEAR EXISTING DEMO DATA
-- ============================================================
DELETE FROM payments WHERE id > 0;
DELETE FROM student_installments WHERE id > 0;
DELETE FROM student_fees WHERE id > 0;
DELETE FROM admissions WHERE id > 0;
DELETE FROM enquiries WHERE id > 0;
DELETE FROM lead_activities WHERE id > 0;
DELETE FROM leads WHERE id > 0;
DELETE FROM students WHERE id > 0;
DELETE FROM fee_structures WHERE id > 0;

-- Reset auto_increment
ALTER TABLE students AUTO_INCREMENT = 1;
ALTER TABLE leads AUTO_INCREMENT = 1;
ALTER TABLE enquiries AUTO_INCREMENT = 1;
ALTER TABLE admissions AUTO_INCREMENT = 1;
ALTER TABLE student_fees AUTO_INCREMENT = 1;
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE fee_structures AUTO_INCREMENT = 1;

-- ============================================================
-- 1. LEAD SOURCES (use INSERT IGNORE - already populated)
-- ============================================================
INSERT IGNORE INTO lead_sources (name, slug, description, is_active) VALUES
('Website', 'website', 'Leads from the institutional website', 1),
('Walk-in', 'walk_in', 'Prospective students who walked in directly', 1),
('Social Media', 'social_media', 'Leads from Facebook, Instagram, LinkedIn', 1),
('Newspaper Ad', 'newspaper_ad', 'Leads from newspaper advertisements', 1),
('Referral', 'referral', 'Referred by existing students or alumni', 1),
('Education Fair', 'education_fair', 'Leads from education fairs and expos', 1);

-- ============================================================
-- 2. LEAD STATUSES (use INSERT IGNORE - already populated)
-- ============================================================
INSERT IGNORE INTO lead_statuses (name, slug, color, sort_order, is_default, is_won, is_lost) VALUES
('New', 'new', '#3498db', 1, 1, 0, 0),
('Contacted', 'contacted', '#f39c12', 2, 0, 0, 0),
('Interested', 'interested', '#9b59b6', 3, 0, 0, 0),
('Demo Scheduled', 'demo_scheduled', '#1abc9c', 4, 0, 0, 0),
('Applied', 'applied', '#e67e22', 5, 0, 0, 0),
('Enrolled', 'enrolled', '#27ae60', 6, 0, 1, 0),
('Not Interested', 'not_interested', '#e74c3c', 7, 0, 0, 1);

-- ============================================================
-- 3. FEE STRUCTURES (needed for student_fees foreign key)
-- ============================================================
INSERT INTO fee_structures (institution_id, course_id, academic_year_id, name, admission_type, total_amount, currency, installments_allowed, max_installments, status) VALUES
(1, 1, 1, 'BCA 2025-26 Fee Structure', 'regular', 55000.00, 'INR', 1, 4, 'active'),
(1, 2, 1, 'BBA 2025-26 Fee Structure', 'regular', 50000.00, 'INR', 1, 4, 'active'),
(1, 3, 1, 'MCA 2025-26 Fee Structure', 'regular', 75000.00, 'INR', 1, 4, 'active'),
(1, 4, 1, 'B.Sc CS 2025-26 Fee Structure', 'regular', 48000.00, 'INR', 1, 4, 'active'),
(1, 6, 1, 'B.Com 2025-26 Fee Structure', 'regular', 45000.00, 'INR', 1, 4, 'active'),
(1, 7, 1, 'MBA 2025-26 Fee Structure', 'regular', 85000.00, 'INR', 1, 4, 'active'),
(1, 8, 1, 'B.Tech IT 2025-26 Fee Structure', 'regular', 80000.00, 'INR', 1, 4, 'active');

-- ============================================================
-- 4. 40 REALISTIC LEADS
-- ============================================================
INSERT INTO leads (institution_id, lead_number, first_name, last_name, email, phone, alternate_phone, date_of_birth, gender, city, state, qualification, percentage, passing_year, lead_source_id, lead_status_id, assigned_to, course_interested_id, priority, notes, created_at, updated_at) VALUES

-- New leads (status 1 = New)
(1, 'LEAD-2025-0001', 'Aarav', 'Sharma', 'aarav.sharma@gmail.com', '9841234567', NULL, '2006-03-15', 'male', 'Chennai', 'Tamil Nadu', 'HSC', 87.50, 2024, 1, 1, 1, 1, 'high', 'Interested in BCA program', DATE_SUB(NOW(), INTERVAL 85 DAY), DATE_SUB(NOW(), INTERVAL 85 DAY)),
(1, 'LEAD-2025-0002', 'Priya', 'Venkatesh', 'priya.venkatesh@gmail.com', '9876543210', NULL, '2005-07-22', 'female', 'Coimbatore', 'Tamil Nadu', 'HSC', 91.20, 2024, 2, 1, 1, 2, 'medium', 'Walk-in enquiry about BBA', DATE_SUB(NOW(), INTERVAL 82 DAY), DATE_SUB(NOW(), INTERVAL 82 DAY)),
(1, 'LEAD-2025-0003', 'Karthik', 'Rajan', 'karthik.rajan@yahoo.com', '8754321098', NULL, '2005-11-08', 'male', 'Madurai', 'Tamil Nadu', 'HSC', 78.00, 2024, 5, 1, 1, 4, 'medium', 'Referred by cousin studying here', DATE_SUB(NOW(), INTERVAL 80 DAY), DATE_SUB(NOW(), INTERVAL 80 DAY)),
(1, 'LEAD-2025-0004', 'Divya', 'Krishnamurthy', 'divya.krish@gmail.com', '9965432109', NULL, '2006-01-30', 'female', 'Salem', 'Tamil Nadu', 'HSC', 82.80, 2024, 1, 1, 1, 6, 'high', 'Interested in B.Com honors', DATE_SUB(NOW(), INTERVAL 78 DAY), DATE_SUB(NOW(), INTERVAL 78 DAY)),
(1, 'LEAD-2025-0005', 'Vikram', 'Sundaram', 'vikram.sundaram@gmail.com', '7845678901', NULL, '2005-05-14', 'male', 'Trichy', 'Tamil Nadu', 'HSC', 93.40, 2024, 6, 1, 1, 8, 'high', 'Top scorer, interested in B.Tech IT', DATE_SUB(NOW(), INTERVAL 75 DAY), DATE_SUB(NOW(), INTERVAL 75 DAY)),

-- Contacted leads (status 2 = Contacted)
(1, 'LEAD-2025-0006', 'Meena', 'Subramaniam', 'meena.sub@gmail.com', '9543210987', NULL, '2006-04-19', 'female', 'Erode', 'Tamil Nadu', 'HSC', 75.60, 2024, 3, 2, 1, 1, 'medium', 'Called twice, seems interested in BCA', DATE_SUB(NOW(), INTERVAL 72 DAY), DATE_SUB(NOW(), INTERVAL 68 DAY)),
(1, 'LEAD-2025-0007', 'Suresh', 'Babu', 'suresh.babu@gmail.com', '8934567890', NULL, '2005-09-25', 'male', 'Vellore', 'Tamil Nadu', 'HSC', 68.00, 2024, 2, 2, 1, 2, 'low', 'Walk-in, wants to know about BBA fees', DATE_SUB(NOW(), INTERVAL 70 DAY), DATE_SUB(NOW(), INTERVAL 66 DAY)),
(1, 'LEAD-2025-0008', 'Anitha', 'Rajendran', 'anitha.raj@gmail.com', '9632145870', NULL, '2006-02-11', 'female', 'Tirunelveli', 'Tamil Nadu', 'HSC', 85.20, 2024, 5, 2, 1, 3, 'medium', 'Referred by alumna, interested in MCA', DATE_SUB(NOW(), INTERVAL 68 DAY), DATE_SUB(NOW(), INTERVAL 64 DAY)),
(1, 'LEAD-2025-0009', 'Rajesh', 'Mohan', 'rajesh.mohan@gmail.com', '7798765432', NULL, '2005-12-05', 'male', 'Tiruppur', 'Tamil Nadu', 'HSC', 71.40, 2024, 8, 2, 1, 4, 'medium', 'Saw newspaper ad, called to inquire', DATE_SUB(NOW(), INTERVAL 65 DAY), DATE_SUB(NOW(), INTERVAL 61 DAY)),
(1, 'LEAD-2025-0010', 'Lakshmi', 'Narayanan', 'lakshmi.n@gmail.com', '9874563210', NULL, '2006-06-28', 'female', 'Chennai', 'Tamil Nadu', 'HSC', 88.60, 2024, 1, 2, 1, 8, 'high', 'Website enquiry, very interested in B.Tech', DATE_SUB(NOW(), INTERVAL 62 DAY), DATE_SUB(NOW(), INTERVAL 58 DAY)),

-- Interested leads (status 3 = Interested)
(1, 'LEAD-2025-0011', 'Ganesh', 'Prabhu', 'ganesh.prabhu@gmail.com', '8812345678', NULL, '2005-08-17', 'male', 'Coimbatore', 'Tamil Nadu', 'HSC', 79.80, 2024, 2, 3, 1, 1, 'high', 'Very interested, asked for brochure', DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 55 DAY)),
(1, 'LEAD-2025-0012', 'Kavitha', 'Selvaraj', 'kavitha.sel@gmail.com', '9921345678', NULL, '2006-03-03', 'female', 'Madurai', 'Tamil Nadu', 'HSC', 92.00, 2024, 5, 3, 1, 7, 'high', 'Scholarship enquiry for MBA', DATE_SUB(NOW(), INTERVAL 58 DAY), DATE_SUB(NOW(), INTERVAL 53 DAY)),
(1, 'LEAD-2025-0013', 'Murugan', 'Pillai', 'murugan.pillai@gmail.com', '8765432190', NULL, '2005-10-20', 'male', 'Nagercoil', 'Tamil Nadu', 'HSC', 74.20, 2024, 6, 3, 1, 2, 'medium', 'Education fair contact, interested in BBA', DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 50 DAY)),
(1, 'LEAD-2025-0014', 'Saranya', 'Devi', 'saranya.devi@gmail.com', '9543876210', NULL, '2006-07-12', 'female', 'Salem', 'Tamil Nadu', 'HSC', 86.40, 2024, 1, 3, 1, 4, 'high', 'Campus visit confirmed interest in B.Sc CS', DATE_SUB(NOW(), INTERVAL 52 DAY), DATE_SUB(NOW(), INTERVAL 47 DAY)),
(1, 'LEAD-2025-0015', 'Praveen', 'Kumar', 'praveen.kumar@gmail.com', '7812345698', NULL, '2005-11-30', 'male', 'Trichy', 'Tamil Nadu', 'HSC', 81.60, 2024, 9, 3, 1, 8, 'medium', 'Referral from Professor, B.Tech IT interest', DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),

-- Campus Visit / Demo Scheduled (status 4 = Campus Visit)
(1, 'LEAD-2025-0016', 'Nandhini', 'Balaji', 'nandhini.b@gmail.com', '9867543210', NULL, '2006-02-25', 'female', 'Chennai', 'Tamil Nadu', 'HSC', 89.20, 2024, 3, 4, 1, 3, 'high', 'Campus visit scheduled next week', DATE_SUB(NOW(), INTERVAL 48 DAY), DATE_SUB(NOW(), INTERVAL 42 DAY)),
(1, 'LEAD-2025-0017', 'Senthil', 'Kumar', 'senthil.k@gmail.com', '9123456789', NULL, '2005-06-15', 'male', 'Erode', 'Tamil Nadu', 'HSC', 76.00, 2024, 2, 4, 1, 1, 'medium', 'Demo session scheduled for BCA overview', DATE_SUB(NOW(), INTERVAL 46 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),
(1, 'LEAD-2025-0018', 'Mythili', 'Chandran', 'mythili.c@gmail.com', '8956781234', NULL, '2006-09-08', 'female', 'Vellore', 'Tamil Nadu', 'HSC', 83.40, 2024, 1, 4, 1, 6, 'high', 'Website lead, demo for B.Com', DATE_SUB(NOW(), INTERVAL 43 DAY), DATE_SUB(NOW(), INTERVAL 37 DAY)),
(1, 'LEAD-2025-0019', 'Arjun', 'Ramesh', 'arjun.ramesh@gmail.com', '7765432198', NULL, '2005-04-20', 'male', 'Tiruppur', 'Tamil Nadu', 'HSC', 94.60, 2024, 8, 4, 1, 7, 'high', 'Newspaper enquiry, MBA demo scheduled', DATE_SUB(NOW(), INTERVAL 41 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),
(1, 'LEAD-2025-0020', 'Sathya', 'Moorthy', 'sathya.m@gmail.com', '9832145670', NULL, '2006-01-17', 'female', 'Madurai', 'Tamil Nadu', 'HSC', 77.80, 2024, 5, 4, 1, 2, 'medium', 'Referral, demo for BBA scheduled', DATE_SUB(NOW(), INTERVAL 38 DAY), DATE_SUB(NOW(), INTERVAL 32 DAY)),

-- Application Submitted (status 5 = Application Submitted)
(1, 'LEAD-2025-0021', 'Dinesh', 'Murugesan', 'dinesh.muru@gmail.com', '9765432108', NULL, '2005-07-04', 'male', 'Chennai', 'Tamil Nadu', 'HSC', 85.00, 2024, 1, 5, 1, 8, 'high', 'Application submitted for B.Tech IT', DATE_SUB(NOW(), INTERVAL 35 DAY), DATE_SUB(NOW(), INTERVAL 28 DAY)),
(1, 'LEAD-2025-0022', 'Revathi', 'Annamalai', 'revathi.a@gmail.com', '8843219876', NULL, '2006-05-30', 'female', 'Coimbatore', 'Tamil Nadu', 'HSC', 90.40, 2024, 2, 5, 1, 7, 'high', 'MBA application submitted', DATE_SUB(NOW(), INTERVAL 33 DAY), DATE_SUB(NOW(), INTERVAL 26 DAY)),
(1, 'LEAD-2025-0023', 'Balamurugan', 'Velu', 'bala.velu@gmail.com', '9543678901', NULL, '2005-10-12', 'male', 'Salem', 'Tamil Nadu', 'HSC', 72.60, 2024, 6, 5, 1, 1, 'medium', 'Applied for BCA through education fair contact', DATE_SUB(NOW(), INTERVAL 31 DAY), DATE_SUB(NOW(), INTERVAL 24 DAY)),
(1, 'LEAD-2025-0024', 'Pavithra', 'Sundar', 'pavithra.s@gmail.com', '7812349876', NULL, '2006-08-21', 'female', 'Trichy', 'Tamil Nadu', 'HSC', 87.20, 2024, 5, 5, 1, 4, 'high', 'Applied for B.Sc CS', DATE_SUB(NOW(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 22 DAY)),
(1, 'LEAD-2025-0025', 'Ashwin', 'Natarajan', 'ashwin.nat@gmail.com', '9876123450', NULL, '2005-12-28', 'male', 'Tirunelveli', 'Tamil Nadu', 'HSC', 80.00, 2024, 9, 5, 1, 2, 'medium', 'Referral from teacher, BBA application submitted', DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 19 DAY)),

-- Converted/Enrolled leads (status 6 = Converted)
(1, 'LEAD-2025-0026', 'Nithya', 'Kalaichelvan', 'nithya.k@gmail.com', '9543219870', NULL, '2005-03-09', 'female', 'Chennai', 'Tamil Nadu', 'HSC', 91.80, 2024, 1, 6, 1, 3, 'high', 'Enrolled in MCA 2025-26', DATE_SUB(NOW(), INTERVAL 80 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 'LEAD-2025-0027', 'Gowtham', 'Selvakumar', 'gowtham.s@gmail.com', '8812349870', NULL, '2005-05-22', 'male', 'Coimbatore', 'Tamil Nadu', 'HSC', 84.40, 2024, 2, 6, 1, 1, 'high', 'Enrolled in BCA 2025-26', DATE_SUB(NOW(), INTERVAL 75 DAY), DATE_SUB(NOW(), INTERVAL 55 DAY)),
(1, 'LEAD-2025-0028', 'Keerthana', 'Ramachandran', 'keerthi.r@gmail.com', '9632541870', NULL, '2006-01-05', 'female', 'Madurai', 'Tamil Nadu', 'HSC', 88.00, 2024, 5, 6, 1, 7, 'high', 'Enrolled in MBA 2025-26', DATE_SUB(NOW(), INTERVAL 70 DAY), DATE_SUB(NOW(), INTERVAL 50 DAY)),
(1, 'LEAD-2025-0029', 'Saravanan', 'Thiyagarajan', 'sara.thiyaga@gmail.com', '7721345690', NULL, '2005-08-30', 'male', 'Salem', 'Tamil Nadu', 'HSC', 76.80, 2024, 6, 6, 1, 2, 'medium', 'Enrolled in BBA 2025-26', DATE_SUB(NOW(), INTERVAL 65 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),
(1, 'LEAD-2025-0030', 'Pooja', 'Arunachalam', 'pooja.aruna@gmail.com', '9874561230', NULL, '2006-04-14', 'female', 'Trichy', 'Tamil Nadu', 'HSC', 82.20, 2024, 8, 6, 1, 6, 'high', 'Enrolled in B.Com 2025-26', DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),

-- Not Interested leads (status 7 = Not Interested)
(1, 'LEAD-2025-0031', 'Rajan', 'Palaniappan', 'rajan.palani@gmail.com', '9854321678', NULL, '2005-09-18', 'male', 'Erode', 'Tamil Nadu', 'HSC', 65.40, 2024, 3, 7, 1, 4, 'low', 'Chose another college closer to home', DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 50 DAY)),
(1, 'LEAD-2025-0032', 'Brindha', 'Sivakumar', 'brindha.s@gmail.com', '8965432107', NULL, '2006-02-07', 'female', 'Vellore', 'Tamil Nadu', 'HSC', 70.00, 2024, 1, 7, 1, 1, 'low', 'Parents decided against BCA', DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),
(1, 'LEAD-2025-0033', 'Madhan', 'Kumaran', 'madhan.k@gmail.com', '9741235678', NULL, '2005-11-12', 'male', 'Nagercoil', 'Tamil Nadu', 'HSC', 62.60, 2024, 2, 7, 1, 2, 'low', 'Fees too high for family budget', DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),

-- Mix of more leads
(1, 'LEAD-2025-0034', 'Eswari', 'Murugesan', 'eswari.m@gmail.com', '9865432170', NULL, '2006-06-01', 'female', 'Chennai', 'Tamil Nadu', 'HSC', 89.60, 2024, 1, 3, 1, 8, 'high', 'High scorer, very interested in B.Tech IT', DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),
(1, 'LEAD-2025-0035', 'Vinoth', 'Sekar', 'vinoth.sekar@gmail.com', '8712345679', NULL, '2005-07-25', 'male', 'Coimbatore', 'Tamil Nadu', 'HSC', 73.80, 2024, 6, 2, 1, 1, 'medium', 'Education fair lead, interested in BCA', DATE_SUB(NOW(), INTERVAL 38 DAY), DATE_SUB(NOW(), INTERVAL 33 DAY)),
(1, 'LEAD-2025-0036', 'Bharathi', 'Ponnusamy', 'bharathi.p@gmail.com', '9632547810', NULL, '2006-03-18', 'female', 'Salem', 'Tamil Nadu', 'HSC', 84.80, 2024, 5, 3, 1, 4, 'high', 'Referral, strong interest in B.Sc CS', DATE_SUB(NOW(), INTERVAL 35 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
(1, 'LEAD-2025-0037', 'Manikandan', 'Venkatesan', 'manik.v@gmail.com', '7712345698', NULL, '2005-10-08', 'male', 'Trichy', 'Tamil Nadu', 'HSC', 78.40, 2024, 8, 4, 1, 2, 'medium', 'Newspaper ad, demo for BBA scheduled', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(1, 'LEAD-2025-0038', 'Sudha', 'Vaidyanathan', 'sudha.v@gmail.com', '9876542310', NULL, '2006-08-14', 'female', 'Tiruppur', 'Tamil Nadu', 'HSC', 91.40, 2024, 1, 5, 1, 7, 'high', 'Applied for MBA, excellent profile', DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY)),
(1, 'LEAD-2025-0039', 'Thirumalai', 'Raja', 'thirumalai.r@gmail.com', '8843210987', NULL, '2005-05-30', 'male', 'Madurai', 'Tamil Nadu', 'HSC', 69.20, 2024, 2, 2, 1, 6, 'low', 'Walk-in, asking about B.Com fees', DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 'LEAD-2025-0040', 'Yamini', 'Krishnan', 'yamini.k@gmail.com', '9543214780', NULL, '2006-09-22', 'female', 'Chennai', 'Tamil Nadu', 'HSC', 95.20, 2024, 9, 3, 1, 3, 'high', 'Referral from Professor, MCA interest', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 10 DAY));

-- ============================================================
-- 5. LEAD ACTIVITIES for some leads
-- ============================================================
INSERT INTO lead_activities (lead_id, user_id, type, title, description, created_at) VALUES
(1, 1, 'call', 'Initial contact call', 'Spoke with student, very interested in BCA', DATE_SUB(NOW(), INTERVAL 83 DAY)),
(1, 1, 'note', 'Follow-up note', 'Sent brochure via email', DATE_SUB(NOW(), INTERVAL 80 DAY)),
(6, 1, 'call', 'First call made', 'Called and explained BCA course details', DATE_SUB(NOW(), INTERVAL 70 DAY)),
(6, 1, 'email', 'Fee structure sent', 'Emailed fee structure and scholarship details', DATE_SUB(NOW(), INTERVAL 66 DAY)),
(11, 1, 'call', 'Interest confirmed', 'Student confirmed strong interest', DATE_SUB(NOW(), INTERVAL 58 DAY)),
(11, 1, 'meeting', 'Campus tour', 'Arranged campus tour for student and parents', DATE_SUB(NOW(), INTERVAL 54 DAY)),
(16, 1, 'call', 'Demo scheduled', 'Campus visit and demo scheduled', DATE_SUB(NOW(), INTERVAL 45 DAY)),
(21, 1, 'system', 'Application submitted', 'Application form submitted online', DATE_SUB(NOW(), INTERVAL 30 DAY)),
(26, 1, 'status_change', 'Lead converted', 'Student enrolled in MCA 2025-26', DATE_SUB(NOW(), INTERVAL 60 DAY)),
(27, 1, 'status_change', 'Lead converted', 'Student enrolled in BCA 2025-26', DATE_SUB(NOW(), INTERVAL 55 DAY));

-- ============================================================
-- 6. 20 ENQUIRIES
-- ============================================================
INSERT INTO enquiries (institution_id, enquiry_number, first_name, last_name, email, phone, message, course_interested_id, source, status, assigned_to, created_at, updated_at) VALUES
(1, 'ENQ-2025-0001', 'Akash', 'Sharma', 'akash.sharma@gmail.com', '9812345670', 'Interested in BCA program, need fee details', 1, 'Website', 'new', 1, DATE_SUB(NOW(), INTERVAL 58 DAY), DATE_SUB(NOW(), INTERVAL 58 DAY)),
(1, 'ENQ-2025-0002', 'Preethi', 'Suresh', 'preethi.s@gmail.com', '9743215678', 'Want to know about MBA admissions 2025', 7, 'Walk-in', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 52 DAY)),
(1, 'ENQ-2025-0003', 'Vignesh', 'Arumugam', 'vignesh.a@gmail.com', '8812340987', 'B.Tech IT admission eligibility query', 8, 'Phone Call', 'new', 1, DATE_SUB(NOW(), INTERVAL 52 DAY), DATE_SUB(NOW(), INTERVAL 52 DAY)),
(1, 'ENQ-2025-0004', 'Deepika', 'Ravi', 'deepika.r@gmail.com', '9654321870', 'Looking for B.Com details and placements', 6, 'Website', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 46 DAY)),
(1, 'ENQ-2025-0005', 'Kathirvel', 'Pandian', 'kathi.p@gmail.com', '9876312450', 'MCA course duration and fee enquiry', 3, 'Social Media - Facebook', 'new', 1, DATE_SUB(NOW(), INTERVAL 48 DAY), DATE_SUB(NOW(), INTERVAL 48 DAY)),
(1, 'ENQ-2025-0006', 'Ramya', 'Subramanian', 'ramya.sub@gmail.com', '8743298760', 'BBA syllabus and career prospects', 2, 'Education Fair', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 46 DAY), DATE_SUB(NOW(), INTERVAL 42 DAY)),
(1, 'ENQ-2025-0007', 'Harish', 'Babu', 'harish.b@gmail.com', '9543298760', 'Hostel facilities for B.Sc CS students', 4, 'Referral', 'new', 1, DATE_SUB(NOW(), INTERVAL 44 DAY), DATE_SUB(NOW(), INTERVAL 44 DAY)),
(1, 'ENQ-2025-0008', 'Swetha', 'Kannan', 'swetha.k@gmail.com', '7812564390', 'Scholarship options for MBA students', 7, 'Website', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 42 DAY), DATE_SUB(NOW(), INTERVAL 38 DAY)),
(1, 'ENQ-2025-0009', 'Balachandran', 'Nair', 'bala.nair@gmail.com', '9721456830', 'BCA lateral entry admission query', 1, 'Phone Call', 'converted', 1, DATE_SUB(NOW(), INTERVAL 40 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),
(1, 'ENQ-2025-0010', 'Indhu', 'Prakash', 'indhu.p@gmail.com', '8912345670', 'B.Tech IT vs BCA - which is better?', 8, 'Walk-in', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 38 DAY), DATE_SUB(NOW(), INTERVAL 34 DAY)),
(1, 'ENQ-2025-0011', 'Selvam', 'Muthu', 'selvam.m@gmail.com', '9632147850', 'MBA admission requirements and GMAT', 7, 'Newspaper Ad', 'new', 1, DATE_SUB(NOW(), INTERVAL 35 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),
(1, 'ENQ-2025-0012', 'Charulatha', 'Venkat', 'charu.v@gmail.com', '9874561230', 'B.Com with accounts specialization details', 6, 'Social Media - Instagram', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 33 DAY), DATE_SUB(NOW(), INTERVAL 29 DAY)),
(1, 'ENQ-2025-0013', 'Dinesh', 'Periyasamy', 'dinesh.peri@gmail.com', '8921345670', 'MCA placements and company tie-ups', 3, 'Referral', 'new', 1, DATE_SUB(NOW(), INTERVAL 31 DAY), DATE_SUB(NOW(), INTERVAL 31 DAY)),
(1, 'ENQ-2025-0014', 'Kiruthika', 'Selvakumar', 'kiru.s@gmail.com', '9765432180', 'BBA course fee installment options', 2, 'Walk-in', 'converted', 1, DATE_SUB(NOW(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 23 DAY)),
(1, 'ENQ-2025-0015', 'Jayakumar', 'Srinivasan', 'jaya.s@gmail.com', '9543217890', 'B.Sc CS admission last date query', 4, 'Website', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 26 DAY), DATE_SUB(NOW(), INTERVAL 22 DAY)),
(1, 'ENQ-2025-0016', 'Nivetha', 'Gopalan', 'nivetha.g@gmail.com', '8812349870', 'BCA fees and hostel accommodation', 1, 'Education Fair', 'new', 1, DATE_SUB(NOW(), INTERVAL 24 DAY), DATE_SUB(NOW(), INTERVAL 24 DAY)),
(1, 'ENQ-2025-0017', 'Anbarasan', 'Mani', 'anba.mani@gmail.com', '9876543109', 'B.Tech IT cut-off marks inquiry', 8, 'Google Ads', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 21 DAY), DATE_SUB(NOW(), INTERVAL 17 DAY)),
(1, 'ENQ-2025-0018', 'Subashini', 'Moorthy', 'subash.m@gmail.com', '9654319870', 'MBA weekend program availability', 7, 'Website', 'closed', 1, DATE_SUB(NOW(), INTERVAL 18 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY)),
(1, 'ENQ-2025-0019', 'Kumaresan', 'Natarajan', 'kumar.nat@gmail.com', '7812456390', 'BBA entrepreneurship course query', 2, 'Phone Call', 'new', 1, DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY)),
(1, 'ENQ-2025-0020', 'Aarthi', 'Pillai', 'aarthi.p@gmail.com', '9876312450', 'B.Com taxation course information', 6, 'Walk-in', 'contacted', 1, DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY));

-- ============================================================
-- 7. 60 STUDENTS
-- ============================================================
INSERT INTO students (organization_id, institution_id, admission_number, student_id_number, roll_number, first_name, last_name, email, mobile_number, phone, date_of_birth, gender, blood_group, nationality, category, father_name, father_phone, father_occupation, mother_name, mother_occupation, address_line1, city, state, pincode, country, course_id, batch_id, department_id, academic_year_id, current_semester, admission_date, admission_type, previous_qualification, previous_percentage, previous_year_of_passing, status, student_type, notes, created_by, created_at, updated_at) VALUES

-- BCA Students (course_id=1, batch_id=1, dept_id=1)
(1, 1, 'ADM-2025-00001', 'STU-EGSPEC-2025-0001', 'BCA-001', 'Aarav', 'Sharma', 'aarav.sharma@egspec.edu', '9841234567', '9841234567', '2005-03-15', 'male', 'B+', 'Indian', 'OC', 'Ramesh Sharma', '9841234560', 'Engineer', 'Sujatha Sharma', 'Teacher', '12, Anna Nagar', 'Chennai', 'Tamil Nadu', '600040', 'India', 1, 1, 1, 1, 1, '2023-07-15', 'regular', 'HSC', 87.50, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00002', 'STU-EGSPEC-2025-0002', 'BCA-002', 'Priya', 'Venkatesh', 'priya.v@egspec.edu', '9876543210', '9876543210', '2005-07-22', 'female', 'A+', 'Indian', 'BC', 'Venkatesh Raja', '9876543200', 'Business', 'Meenakshi Venkatesh', 'Homemaker', '45, Nehru Street', 'Coimbatore', 'Tamil Nadu', '641001', 'India', 1, 1, 1, 1, 1, '2023-07-15', 'regular', 'HSC', 91.20, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00003', 'STU-EGSPEC-2025-0003', 'BCA-003', 'Karthik', 'Rajan', 'karthik.r@egspec.edu', '8754321098', '8754321098', '2005-11-08', 'male', 'O+', 'Indian', 'MBC', 'Rajan Kumar', '8754321090', 'Farmer', 'Valli Rajan', 'Homemaker', '78, GN Mills Road', 'Coimbatore', 'Tamil Nadu', '641029', 'India', 1, 1, 1, 1, 1, '2023-07-17', 'regular', 'HSC', 78.00, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00004', 'STU-EGSPEC-2025-0004', 'BCA-004', 'Divya', 'Krishnamurthy', 'divya.k@egspec.edu', '9965432109', '9965432109', '2006-01-30', 'female', 'AB+', 'Indian', 'SC', 'Krishnamurthy S', '9965432100', 'Govt Employee', 'Selvi Krishnamurthy', 'Teacher', '23, Market Street', 'Salem', 'Tamil Nadu', '636001', 'India', 1, 1, 1, 1, 1, '2023-07-17', 'regular', 'HSC', 82.80, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00005', 'STU-EGSPEC-2025-0005', 'BCA-005', 'Ganesh', 'Prabhu', 'ganesh.p@egspec.edu', '8812345678', '8812345678', '2005-08-17', 'male', 'B-', 'Indian', 'OC', 'Prabhu Nathan', '8812345670', 'Doctor', 'Kamala Prabhu', 'Nurse', '56, RS Puram', 'Coimbatore', 'Tamil Nadu', '641002', 'India', 1, 1, 1, 1, 1, '2023-07-20', 'regular', 'HSC', 79.80, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 725 DAY), DATE_SUB(NOW(), INTERVAL 725 DAY)),
(1, 1, 'ADM-2025-00006', 'STU-EGSPEC-2025-0006', 'BCA-006', 'Meena', 'Subramaniam', 'meena.s@egspec.edu', '9543210987', '9543210987', '2006-04-19', 'female', 'A-', 'Indian', 'BC', 'Subramaniam P', '9543210980', 'Business', 'Lakshmi Subramaniam', 'Homemaker', '34, Racecourse Road', 'Coimbatore', 'Tamil Nadu', '641018', 'India', 1, 1, 1, 1, 1, '2023-07-20', 'regular', 'HSC', 75.60, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 725 DAY), DATE_SUB(NOW(), INTERVAL 725 DAY)),
(1, 1, 'ADM-2025-00007', 'STU-EGSPEC-2025-0007', 'BCA-007', 'Senthil', 'Kumar', 'senthil.k@egspec.edu', '9123456789', '9123456789', '2005-06-15', 'male', 'O-', 'Indian', 'MBC', 'Kumar Selvam', '9123456780', 'Weaver', 'Sumathi Kumar', 'Homemaker', '67, EVR Salai', 'Erode', 'Tamil Nadu', '638001', 'India', 1, 1, 1, 1, 1, '2023-07-22', 'regular', 'HSC', 76.00, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 723 DAY), DATE_SUB(NOW(), INTERVAL 723 DAY)),
(1, 1, 'ADM-2025-00008', 'STU-EGSPEC-2025-0008', 'BCA-008', 'Kavitha', 'Selvaraj', 'kavitha.sel@egspec.edu', '9921345678', '9921345678', '2006-03-03', 'female', 'B+', 'Indian', 'BC', 'Selvaraj D', '9921345670', 'Trader', 'Mallika Selvaraj', 'Teacher', '89, West Masi Street', 'Madurai', 'Tamil Nadu', '625001', 'India', 1, 1, 1, 1, 1, '2023-07-22', 'regular', 'HSC', 92.00, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 723 DAY), DATE_SUB(NOW(), INTERVAL 723 DAY)),
(1, 1, 'ADM-2025-00009', 'STU-EGSPEC-2025-0009', 'BCA-009', 'Rajesh', 'Mohan', 'rajesh.m@egspec.edu', '7798765432', '7798765432', '2005-12-05', 'male', 'AB-', 'Indian', 'OC', 'Mohan Raj', '7798765430', 'Business', 'Revathi Mohan', 'Homemaker', '12, Vaigai Bridge Road', 'Madurai', 'Tamil Nadu', '625009', 'India', 1, 1, 1, 1, 1, '2023-07-25', 'regular', 'HSC', 71.40, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 720 DAY)),
(1, 1, 'ADM-2025-00010', 'STU-EGSPEC-2025-0010', 'BCA-010', 'Saranya', 'Devi', 'saranya.d@egspec.edu', '9543876210', '9543876210', '2006-07-12', 'female', 'A+', 'Indian', 'ST', 'Devi Rajan', '9543876200', 'Govt Employee', 'Parvathi Devi', 'Nurse', '45, Omalur Road', 'Salem', 'Tamil Nadu', '636009', 'India', 1, 1, 1, 1, 1, '2023-07-25', 'regular', 'HSC', 86.40, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 720 DAY)),

-- BBA Students (course_id=2, batch_id=3, dept_id=4)
(1, 1, 'ADM-2025-00011', 'STU-EGSPEC-2025-0011', 'BBA-001', 'Suresh', 'Babu', 'suresh.b@egspec.edu', '8934567890', '8934567890', '2005-09-25', 'male', 'B+', 'Indian', 'OC', 'Babu Krishnan', '8934567880', 'Businessman', 'Radha Babu', 'Homemaker', '23, Bazaar Street', 'Vellore', 'Tamil Nadu', '632001', 'India', 2, 3, 4, 1, 1, '2023-07-15', 'regular', 'HSC', 68.00, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00012', 'STU-EGSPEC-2025-0012', 'BBA-002', 'Anitha', 'Rajendran', 'anitha.r@egspec.edu', '9632145870', '9632145870', '2006-02-11', 'female', 'O+', 'Indian', 'BC', 'Rajendran M', '9632145860', 'Farmer', 'Saraswathi Rajendran', 'Homemaker', '56, Gandhi Road', 'Tirunelveli', 'Tamil Nadu', '627001', 'India', 2, 3, 4, 1, 1, '2023-07-15', 'regular', 'HSC', 85.20, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00013', 'STU-EGSPEC-2025-0013', 'BBA-003', 'Murugan', 'Pillai', 'murugan.p@egspec.edu', '8765432190', '8765432190', '2005-10-20', 'male', 'A-', 'Indian', 'MBC', 'Pillai Sundaram', '8765432180', 'Trader', 'Kamakshi Pillai', 'Teacher', '34, Beach Road', 'Nagercoil', 'Tamil Nadu', '629001', 'India', 2, 3, 4, 1, 1, '2023-07-17', 'regular', 'HSC', 74.20, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00014', 'STU-EGSPEC-2025-0014', 'BBA-004', 'Nandhini', 'Balaji', 'nandhini.b@egspec.edu', '9867543210', '9867543210', '2006-02-25', 'female', 'B-', 'Indian', 'OC', 'Balaji Raman', '9867543200', 'Engineer', 'Gomathi Balaji', 'Teacher', '78, Anna Salai', 'Chennai', 'Tamil Nadu', '600002', 'India', 2, 3, 4, 1, 1, '2023-07-17', 'regular', 'HSC', 89.20, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00015', 'STU-EGSPEC-2025-0015', 'BBA-005', 'Praveen', 'Kumar', 'praveen.k@egspec.edu', '7812345698', '7812345698', '2005-11-30', 'male', 'AB+', 'Indian', 'SC', 'Kumar Annamalai', '7812345688', 'Govt Employee', 'Ponni Kumar', 'Homemaker', '12, Salai Road', 'Trichy', 'Tamil Nadu', '620001', 'India', 2, 3, 4, 1, 1, '2023-07-20', 'regular', 'HSC', 81.60, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 725 DAY), DATE_SUB(NOW(), INTERVAL 725 DAY)),
(1, 1, 'ADM-2025-00016', 'STU-EGSPEC-2025-0016', 'BBA-006', 'Mythili', 'Chandran', 'mythili.c@egspec.edu', '8956781234', '8956781234', '2006-09-08', 'female', 'O+', 'Indian', 'BC', 'Chandran Vel', '8956781224', 'Business', 'Indira Chandran', 'Homemaker', '34, CMC Hospital Road', 'Vellore', 'Tamil Nadu', '632004', 'India', 2, 3, 4, 1, 1, '2023-07-20', 'regular', 'HSC', 83.40, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 725 DAY), DATE_SUB(NOW(), INTERVAL 725 DAY)),
(1, 1, 'ADM-2025-00017', 'STU-EGSPEC-2025-0017', 'BBA-007', 'Sathya', 'Moorthy', 'sathya.m@egspec.edu', '9832145670', '9832145670', '2006-01-17', 'female', 'A+', 'Indian', 'MBC', 'Moorthy Vel', '9832145660', 'Farmer', 'Vennila Moorthy', 'Homemaker', '89, Teppakulam', 'Madurai', 'Tamil Nadu', '625001', 'India', 2, 3, 4, 1, 1, '2023-07-22', 'regular', 'HSC', 77.80, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 723 DAY), DATE_SUB(NOW(), INTERVAL 723 DAY)),
(1, 1, 'ADM-2025-00018', 'STU-EGSPEC-2025-0018', 'BBA-008', 'Vinoth', 'Sekar', 'vinoth.s@egspec.edu', '8712345679', '8712345679', '2005-07-25', 'male', 'B+', 'Indian', 'OC', 'Sekar Palani', '8712345669', 'Businessman', 'Malathi Sekar', 'Teacher', '23, Peelamedu', 'Coimbatore', 'Tamil Nadu', '641004', 'India', 2, 3, 4, 1, 1, '2023-07-22', 'regular', 'HSC', 73.80, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 723 DAY), DATE_SUB(NOW(), INTERVAL 723 DAY)),
(1, 1, 'ADM-2025-00019', 'STU-EGSPEC-2025-0019', 'BBA-009', 'Manikandan', 'Venkatesan', 'manik.v@egspec.edu', '7712345698', '7712345698', '2005-10-08', 'male', 'O-', 'Indian', 'BC', 'Venkatesan A', '7712345688', 'Driver', 'Mangayarkarasi Venkatesan', 'Homemaker', '67, Srirangam', 'Trichy', 'Tamil Nadu', '621005', 'India', 2, 3, 4, 1, 1, '2023-07-25', 'regular', 'HSC', 78.40, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 720 DAY)),
(1, 1, 'ADM-2025-00020', 'STU-EGSPEC-2025-0020', 'BBA-010', 'Bharathi', 'Ponnusamy', 'bharathi.p@egspec.edu', '9632547810', '9632547810', '2006-03-18', 'female', 'AB-', 'Indian', 'SC', 'Ponnusamy K', '9632547800', 'Govt Employee', 'Thenmozhi Ponnusamy', 'Teacher', '45, Omalur Main Road', 'Salem', 'Tamil Nadu', '636004', 'India', 2, 3, 4, 1, 1, '2023-07-25', 'regular', 'HSC', 84.80, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 720 DAY)),

-- MCA Students (course_id=3, batch_id=2, dept_id=1)
(1, 1, 'ADM-2025-00021', 'STU-EGSPEC-2025-0021', 'MCA-001', 'Lakshmi', 'Narayanan', 'lakshmi.n@egspec.edu', '9874563210', '9874563210', '2002-06-28', 'female', 'A+', 'Indian', 'OC', 'Narayanan S', '9874563200', 'Engineer', 'Vijayalakshmi Narayanan', 'Teacher', '12, Teynampet', 'Chennai', 'Tamil Nadu', '600018', 'India', 3, 2, 1, 1, 1, '2024-07-15', 'regular', 'B.Sc', 88.60, 2024, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 365 DAY), DATE_SUB(NOW(), INTERVAL 365 DAY)),
(1, 1, 'ADM-2025-00022', 'STU-EGSPEC-2025-0022', 'MCA-002', 'Arjun', 'Ramesh', 'arjun.r@egspec.edu', '7765432198', '7765432198', '2001-04-20', 'male', 'B+', 'Indian', 'BC', 'Ramesh Kumar', '7765432188', 'Businessman', 'Umamaheswari Ramesh', 'Homemaker', '34, Thousand Lights', 'Chennai', 'Tamil Nadu', '600006', 'India', 3, 2, 1, 1, 1, '2024-07-15', 'regular', 'BCA', 94.60, 2024, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 365 DAY), DATE_SUB(NOW(), INTERVAL 365 DAY)),
(1, 1, 'ADM-2025-00023', 'STU-EGSPEC-2025-0023', 'MCA-003', 'Nithya', 'Kalaichelvan', 'nithya.k@egspec.edu', '9543219870', '9543219870', '2002-03-09', 'female', 'O+', 'Indian', 'MBC', 'Kalaichelvan R', '9543219860', 'Farmer', 'Eswari Kalaichelvan', 'Homemaker', '78, Nandanam', 'Chennai', 'Tamil Nadu', '600035', 'India', 3, 2, 1, 1, 1, '2024-07-17', 'regular', 'B.Sc CS', 91.80, 2024, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 363 DAY), DATE_SUB(NOW(), INTERVAL 363 DAY)),
(1, 1, 'ADM-2025-00024', 'STU-EGSPEC-2025-0024', 'MCA-004', 'Yamini', 'Krishnan', 'yamini.k@egspec.edu', '9543214780', '9543214780', '2002-09-22', 'female', 'A-', 'Indian', 'OC', 'Krishnan V', '9543214770', 'Doctor', 'Jeyanthi Krishnan', 'Nurse', '23, Adyar', 'Chennai', 'Tamil Nadu', '600020', 'India', 3, 2, 1, 1, 1, '2024-07-17', 'regular', 'BCA', 95.20, 2024, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 363 DAY), DATE_SUB(NOW(), INTERVAL 363 DAY)),
(1, 1, 'ADM-2025-00025', 'STU-EGSPEC-2025-0025', 'MCA-005', 'Dinesh', 'Murugesan', 'dinesh.m@egspec.edu', '9765432108', '9765432108', '2001-07-04', 'male', 'B-', 'Indian', 'BC', 'Murugesan K', '9765432098', 'Trader', 'Padmavathi Murugesan', 'Homemaker', '56, Velachery', 'Chennai', 'Tamil Nadu', '600042', 'India', 3, 2, 1, 1, 1, '2024-07-20', 'regular', 'BCA', 85.00, 2024, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 360 DAY), DATE_SUB(NOW(), INTERVAL 360 DAY)),

-- B.Sc CS Students (course_id=4, batch_id=1, dept_id=1)
(1, 1, 'ADM-2025-00026', 'STU-EGSPEC-2025-0026', 'BSC-001', 'Vikram', 'Sundaram', 'vikram.s@egspec.edu', '7845678901', '7845678901', '2005-05-14', 'male', 'O+', 'Indian', 'OC', 'Sundaram N', '7845678891', 'Engineer', 'Vasantha Sundaram', 'Teacher', '34, Chetpet', 'Chennai', 'Tamil Nadu', '600031', 'India', 4, 1, 1, 1, 1, '2023-07-15', 'regular', 'HSC', 93.40, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00027', 'STU-EGSPEC-2025-0027', 'BSC-002', 'Pavithra', 'Sundar', 'pavithra.s@egspec.edu', '7812349876', '7812349876', '2006-08-21', 'female', 'AB+', 'Indian', 'BC', 'Sundar Vel', '7812349866', 'Business', 'Rajalakshmi Sundar', 'Homemaker', '78, Tambaram', 'Chennai', 'Tamil Nadu', '600045', 'India', 4, 1, 1, 1, 1, '2023-07-15', 'regular', 'HSC', 87.20, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00028', 'STU-EGSPEC-2025-0028', 'BSC-003', 'Eswari', 'Murugesan', 'eswari.m@egspec.edu', '9865432170', '9865432170', '2006-06-01', 'female', 'A+', 'Indian', 'MBC', 'Murugesan T', '9865432160', 'Farmer', 'Meenakshi Murugesan', 'Homemaker', '23, Guduvancheri', 'Chennai', 'Tamil Nadu', '603202', 'India', 4, 1, 1, 1, 1, '2023-07-17', 'regular', 'HSC', 89.60, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00029', 'STU-EGSPEC-2025-0029', 'BSC-004', 'Ashwin', 'Natarajan', 'ashwin.n@egspec.edu', '9876123450', '9876123450', '2005-12-28', 'male', 'B+', 'Indian', 'SC', 'Natarajan P', '9876123440', 'Govt Employee', 'Chitra Natarajan', 'Teacher', '56, Perungudi', 'Chennai', 'Tamil Nadu', '600096', 'India', 4, 1, 1, 1, 1, '2023-07-17', 'regular', 'HSC', 80.00, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00030', 'STU-EGSPEC-2025-0030', 'BSC-005', 'Sudha', 'Vaidyanathan', 'sudha.v@egspec.edu', '9876542310', '9876542310', '2006-08-14', 'female', 'O-', 'Indian', 'OC', 'Vaidyanathan S', '9876542300', 'Professor', 'Meenakshi Vaidyanathan', 'Lecturer', '12, Madhavaram', 'Chennai', 'Tamil Nadu', '600060', 'India', 4, 1, 1, 1, 1, '2023-07-20', 'regular', 'HSC', 91.40, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 725 DAY), DATE_SUB(NOW(), INTERVAL 725 DAY)),

-- B.Com Students (course_id=6, batch_id=3, dept_id=4)
(1, 1, 'ADM-2025-00031', 'STU-EGSPEC-2025-0031', 'BCOM-001', 'Pooja', 'Arunachalam', 'pooja.a@egspec.edu', '9874561230', '9874561230', '2006-04-14', 'female', 'A+', 'Indian', 'BC', 'Arunachalam S', '9874561220', 'Business', 'Veni Arunachalam', 'Homemaker', '34, Katpadi Road', 'Vellore', 'Tamil Nadu', '632007', 'India', 6, 3, 4, 1, 1, '2023-07-15', 'regular', 'HSC', 82.20, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00032', 'STU-EGSPEC-2025-0032', 'BCOM-002', 'Thirumalai', 'Raja', 'thirumalai.r@egspec.edu', '8843210987', '8843210987', '2005-05-30', 'male', 'B+', 'Indian', 'MBC', 'Raja Perumal', '8843210977', 'Farmer', 'Sakunthala Raja', 'Homemaker', '78, KK Nagar', 'Madurai', 'Tamil Nadu', '625020', 'India', 6, 3, 4, 1, 1, '2023-07-15', 'regular', 'HSC', 69.20, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00033', 'STU-EGSPEC-2025-0033', 'BCOM-003', 'Aarthi', 'Pillai', 'aarthi.p@egspec.edu', '9876312450', '9876312450', '2006-09-22', 'female', 'O+', 'Indian', 'OC', 'Pillai Raman', '9876312440', 'Engineer', 'Saradha Pillai', 'Teacher', '23, AH Road', 'Nagercoil', 'Tamil Nadu', '629002', 'India', 6, 3, 4, 1, 1, '2023-07-17', 'regular', 'HSC', 84.00, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00034', 'STU-EGSPEC-2025-0034', 'BCOM-004', 'Kumaresan', 'Natarajan', 'kumar.n@egspec.edu', '7812456390', '7812456390', '2005-11-14', 'male', 'A-', 'Indian', 'BC', 'Natarajan Kumar', '7812456380', 'Trader', 'Sasikala Natarajan', 'Homemaker', '56, Palayamkottai', 'Tirunelveli', 'Tamil Nadu', '627002', 'India', 6, 3, 4, 1, 1, '2023-07-17', 'regular', 'HSC', 71.60, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00035', 'STU-EGSPEC-2025-0035', 'BCOM-005', 'Nivetha', 'Gopalan', 'nivetha.g@egspec.edu', '8812349870', '8812349870', '2006-02-15', 'female', 'AB+', 'Indian', 'SC', 'Gopalan K', '8812349860', 'Govt Employee', 'Geetha Gopalan', 'Teacher', '12, Nilakottai Road', 'Dindigul', 'Tamil Nadu', '624001', 'India', 6, 3, 4, 1, 1, '2023-07-20', 'regular', 'HSC', 78.40, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 725 DAY), DATE_SUB(NOW(), INTERVAL 725 DAY)),

-- MBA Students (course_id=7, batch_id=2, dept_id=4)
(1, 1, 'ADM-2025-00036', 'STU-EGSPEC-2025-0036', 'MBA-001', 'Revathi', 'Annamalai', 'revathi.a@egspec.edu', '8843219876', '8843219876', '2000-05-30', 'female', 'A+', 'Indian', 'OC', 'Annamalai K', '8843219866', 'Business', 'Selvi Annamalai', 'Homemaker', '34, Saibaba Colony', 'Coimbatore', 'Tamil Nadu', '641011', 'India', 7, 2, 4, 1, 1, '2024-07-15', 'regular', 'BBA', 90.40, 2024, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 365 DAY), DATE_SUB(NOW(), INTERVAL 365 DAY)),
(1, 1, 'ADM-2025-00037', 'STU-EGSPEC-2025-0037', 'MBA-002', 'Keerthana', 'Ramachandran', 'keerthi.r@egspec.edu', '9632541870', '9632541870', '2001-01-05', 'female', 'B+', 'Indian', 'BC', 'Ramachandran V', '9632541860', 'Trader', 'Ambika Ramachandran', 'Teacher', '78, Singanallur', 'Coimbatore', 'Tamil Nadu', '641005', 'India', 7, 2, 4, 1, 1, '2024-07-15', 'regular', 'B.Com', 88.00, 2024, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 365 DAY), DATE_SUB(NOW(), INTERVAL 365 DAY)),
(1, 1, 'ADM-2025-00038', 'STU-EGSPEC-2025-0038', 'MBA-003', 'Sudha', 'Krishnaswamy', 'sudha.kri@egspec.edu', '9543217654', '9543217654', '2000-08-12', 'female', 'O+', 'Indian', 'MBC', 'Krishnaswamy R', '9543217644', 'Farmer', 'Selvarani Krishnaswamy', 'Homemaker', '23, Ukkadam', 'Coimbatore', 'Tamil Nadu', '641008', 'India', 7, 2, 4, 1, 1, '2024-07-17', 'regular', 'BBA', 82.60, 2024, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 363 DAY), DATE_SUB(NOW(), INTERVAL 363 DAY)),
(1, 1, 'ADM-2025-00039', 'STU-EGSPEC-2025-0039', 'MBA-004', 'Gowtham', 'Selvakumar', 'gowtham.s@egspec.edu', '8812349870', '8812349870', '2001-05-22', 'male', 'A-', 'Indian', 'OC', 'Selvakumar P', '8812349860', 'Doctor', 'Kanaga Selvakumar', 'Nurse', '56, Gandhipuram', 'Coimbatore', 'Tamil Nadu', '641012', 'India', 7, 2, 4, 1, 1, '2024-07-17', 'regular', 'BBA', 84.40, 2024, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 363 DAY), DATE_SUB(NOW(), INTERVAL 363 DAY)),
(1, 1, 'ADM-2025-00040', 'STU-EGSPEC-2025-0040', 'MBA-005', 'Saravanan', 'Thiyagarajan', 'sara.t@egspec.edu', '7721345690', '7721345690', '2000-08-30', 'male', 'B-', 'Indian', 'BC', 'Thiyagarajan M', '7721345680', 'Business', 'Gowri Thiyagarajan', 'Homemaker', '12, Avanashi Road', 'Coimbatore', 'Tamil Nadu', '641006', 'India', 7, 2, 4, 1, 1, '2024-07-20', 'regular', 'B.Com', 76.80, 2024, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 360 DAY), DATE_SUB(NOW(), INTERVAL 360 DAY)),

-- B.Tech IT Students (course_id=8, batch_id=1, dept_id=5)
(1, 1, 'ADM-2025-00041', 'STU-EGSPEC-2025-0041', 'BTIT-001', 'Ravi', 'Shankar', 'ravi.s@egspec.edu', '9543212345', '9543212345', '2005-02-18', 'male', 'O+', 'Indian', 'OC', 'Shankar N', '9543212335', 'Engineer', 'Priya Shankar', 'Teacher', '34, Korattur', 'Chennai', 'Tamil Nadu', '600080', 'India', 8, 1, 5, 1, 1, '2023-07-15', 'regular', 'HSC', 92.00, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00042', 'STU-EGSPEC-2025-0042', 'BTIT-002', 'Kavya', 'Ramanan', 'kavya.r@egspec.edu', '8912345670', '8912345670', '2005-06-12', 'female', 'A+', 'Indian', 'BC', 'Ramanan S', '8912345660', 'Business', 'Padma Ramanan', 'Homemaker', '78, Ambattur', 'Chennai', 'Tamil Nadu', '600053', 'India', 8, 1, 5, 1, 1, '2023-07-15', 'regular', 'HSC', 88.40, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 730 DAY), DATE_SUB(NOW(), INTERVAL 730 DAY)),
(1, 1, 'ADM-2025-00043', 'STU-EGSPEC-2025-0043', 'BTIT-003', 'Arun', 'Muruganantham', 'arun.m@egspec.edu', '9876312460', '9876312460', '2005-09-25', 'male', 'B+', 'Indian', 'MBC', 'Muruganantham R', '9876312450', 'Farmer', 'Vanitha Muruganantham', 'Homemaker', '23, Poonamallee', 'Chennai', 'Tamil Nadu', '600056', 'India', 8, 1, 5, 1, 1, '2023-07-17', 'regular', 'HSC', 79.20, 2023, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00044', 'STU-EGSPEC-2025-0044', 'BTIT-004', 'Sangeetha', 'Anand', 'sangeetha.a@egspec.edu', '9543298761', '9543298761', '2006-01-08', 'female', 'O-', 'Indian', 'SC', 'Anand Kumar', '9543298751', 'Govt Employee', 'Malliga Anand', 'Teacher', '56, Avadi', 'Chennai', 'Tamil Nadu', '600054', 'India', 8, 1, 5, 1, 1, '2023-07-17', 'regular', 'HSC', 85.80, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 728 DAY), DATE_SUB(NOW(), INTERVAL 728 DAY)),
(1, 1, 'ADM-2025-00045', 'STU-EGSPEC-2025-0045', 'BTIT-005', 'Surya', 'Krishnamoorthi', 'surya.k@egspec.edu', '7865432190', '7865432190', '2005-11-20', 'male', 'AB+', 'Indian', 'OC', 'Krishnamoorthi V', '7865432180', 'Professor', 'Saraswathi Krishnamoorthi', 'Lecturer', '12, Chromepet', 'Chennai', 'Tamil Nadu', '600044', 'India', 8, 1, 5, 1, 1, '2023-07-20', 'regular', 'HSC', 96.00, 2023, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 725 DAY), DATE_SUB(NOW(), INTERVAL 725 DAY)),

-- Additional mixed students (2025 batch)
(1, 1, 'ADM-2025-00046', 'STU-EGSPEC-2025-0046', 'BCA-011', 'Janani', 'Selvam', 'janani.s@egspec.edu', '9812346790', '9812346790', '2007-03-10', 'female', 'A+', 'Indian', 'BC', 'Selvam K', '9812346780', 'Trader', 'Chandra Selvam', 'Homemaker', '34, Gandhipuram', 'Coimbatore', 'Tamil Nadu', '641012', 'India', 1, 1, 1, 1, 1, '2025-07-15', 'regular', 'HSC', 88.00, 2025, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 250 DAY), DATE_SUB(NOW(), INTERVAL 250 DAY)),
(1, 1, 'ADM-2025-00047', 'STU-EGSPEC-2025-0047', 'BCA-012', 'Logesh', 'Kannan', 'logesh.k@egspec.edu', '8765109876', '8765109876', '2007-07-22', 'male', 'B+', 'Indian', 'MBC', 'Kannan S', '8765109866', 'Farmer', 'Poomalar Kannan', 'Homemaker', '78, Saibaba Colony', 'Coimbatore', 'Tamil Nadu', '641011', 'India', 1, 1, 1, 1, 1, '2025-07-15', 'regular', 'HSC', 75.40, 2025, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 250 DAY), DATE_SUB(NOW(), INTERVAL 250 DAY)),
(1, 1, 'ADM-2025-00048', 'STU-EGSPEC-2025-0048', 'BSC-006', 'Tharani', 'Mohan', 'tharani.m@egspec.edu', '9965341208', '9965341208', '2007-11-15', 'female', 'O+', 'Indian', 'OC', 'Mohan Vel', '9965341198', 'Engineer', 'Poornima Mohan', 'Teacher', '23, Sulur', 'Coimbatore', 'Tamil Nadu', '641402', 'India', 4, 1, 1, 1, 1, '2025-07-17', 'regular', 'HSC', 91.80, 2025, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 248 DAY), DATE_SUB(NOW(), INTERVAL 248 DAY)),
(1, 1, 'ADM-2025-00049', 'STU-EGSPEC-2025-0049', 'BCOM-006', 'Ramkumar', 'Palani', 'ramkumar.p@egspec.edu', '8745610987', '8745610987', '2007-05-20', 'male', 'A-', 'Indian', 'BC', 'Palani Kumar', '8745610977', 'Business', 'Thilaga Palani', 'Homemaker', '56, Trichy Road', 'Coimbatore', 'Tamil Nadu', '641019', 'India', 6, 3, 4, 1, 1, '2025-07-17', 'regular', 'HSC', 72.60, 2025, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 248 DAY), DATE_SUB(NOW(), INTERVAL 248 DAY)),
(1, 1, 'ADM-2025-00050', 'STU-EGSPEC-2025-0050', 'BBA-011', 'Anbarasi', 'Sundaram', 'anbarasi.s@egspec.edu', '9632518760', '9632518760', '2007-08-04', 'female', 'AB-', 'Indian', 'SC', 'Sundaram P', '9632518750', 'Govt Employee', 'Kalaivani Sundaram', 'Teacher', '12, Vadavalli', 'Coimbatore', 'Tamil Nadu', '641041', 'India', 2, 3, 4, 1, 1, '2025-07-20', 'regular', 'HSC', 80.20, 2025, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 245 DAY), DATE_SUB(NOW(), INTERVAL 245 DAY)),

-- A few inactive/alumni students
(1, 1, 'ADM-2025-00051', 'STU-EGSPEC-2025-0051', 'BCA-013', 'Muthu', 'Raman', 'muthu.r@egspec.edu', '9812456780', '9812456780', '2002-06-14', 'male', 'B+', 'Indian', 'OC', 'Raman Vel', '9812456770', 'Business', 'Malathi Raman', 'Homemaker', '34, Race Course', 'Coimbatore', 'Tamil Nadu', '641018', 'India', 1, 1, 1, 1, 6, '2020-07-15', 'regular', 'HSC', 68.40, 2020, 'alumni', 'day_scholar', 'Completed BCA in 2023', 1, DATE_SUB(NOW(), INTERVAL 1825 DAY), DATE_SUB(NOW(), INTERVAL 90 DAY)),
(1, 1, 'ADM-2025-00052', 'STU-EGSPEC-2025-0052', 'BBA-012', 'Kalaiselvi', 'Raj', 'kalai.r@egspec.edu', '9743218760', '9743218760', '2002-09-28', 'female', 'A+', 'Indian', 'BC', 'Raj Kumar', '9743218750', 'Farmer', 'Kaveri Raj', 'Homemaker', '78, Maruthamalai Road', 'Coimbatore', 'Tamil Nadu', '641046', 'India', 2, 3, 4, 1, 6, '2020-07-15', 'regular', 'HSC', 74.00, 2020, 'alumni', 'day_scholar', 'Completed BBA in 2023', 1, DATE_SUB(NOW(), INTERVAL 1825 DAY), DATE_SUB(NOW(), INTERVAL 90 DAY)),
(1, 1, 'ADM-2025-00053', 'STU-EGSPEC-2025-0053', 'BCA-014', 'Muthukumar', 'Selvam', 'muthuk.s@egspec.edu', '8956123490', '8956123490', '2004-04-10', 'male', 'O-', 'Indian', 'MBC', 'Selvam Raj', '8956123480', 'Weaver', 'Kanagavalli Selvam', 'Homemaker', '23, TVS Nagar', 'Coimbatore', 'Tamil Nadu', '641010', 'India', 1, 1, 1, 1, 3, '2022-07-15', 'regular', 'HSC', 62.80, 2022, 'inactive', 'hosteller', 'On medical leave', 1, DATE_SUB(NOW(), INTERVAL 1095 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 1, 'ADM-2025-00054', 'STU-EGSPEC-2025-0054', 'BBA-013', 'Sangeetha', 'Pillai', 'sangeetha.p@egspec.edu', '9654310987', '9654310987', '2004-12-05', 'female', 'B-', 'Indian', 'OC', 'Pillai Sundaram', '9654310977', 'Doctor', 'Jeyanthi Pillai', 'Nurse', '56, Peelamedu', 'Coimbatore', 'Tamil Nadu', '641004', 'India', 2, 3, 4, 1, 3, '2022-07-15', 'regular', 'HSC', 71.20, 2022, 'inactive', 'day_scholar', 'On semester break', 1, DATE_SUB(NOW(), INTERVAL 1095 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 1, 'ADM-2025-00055', 'STU-EGSPEC-2025-0055', 'BTIT-006', 'Vijayakumar', 'Narayanan', 'vijay.n@egspec.edu', '9876432107', '9876432107', '2003-03-18', 'male', 'AB+', 'Indian', 'BC', 'Narayanan G', '9876432097', 'Businessman', 'Pavithra Narayanan', 'Teacher', '12, Kovaipudur', 'Coimbatore', 'Tamil Nadu', '641042', 'India', 8, 1, 5, 1, 3, '2022-07-17', 'regular', 'HSC', 77.40, 2022, 'inactive', 'day_scholar', 'Transferred to another college', 1, DATE_SUB(NOW(), INTERVAL 1093 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),

-- More active students to complete 60
(1, 1, 'ADM-2025-00056', 'STU-EGSPEC-2025-0056', 'BCA-015', 'Deepa', 'Ramamoorthy', 'deepa.r@egspec.edu', '9543109876', '9543109876', '2007-01-25', 'female', 'A+', 'Indian', 'OC', 'Ramamoorthy K', '9543109866', 'Engineer', 'Santha Ramamoorthy', 'Teacher', '34, Kuniyamuthur', 'Coimbatore', 'Tamil Nadu', '641008', 'India', 1, 1, 1, 1, 1, '2025-07-22', 'regular', 'HSC', 86.20, 2025, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 243 DAY), DATE_SUB(NOW(), INTERVAL 243 DAY)),
(1, 1, 'ADM-2025-00057', 'STU-EGSPEC-2025-0057', 'BSC-007', 'Ponraj', 'Murugan', 'ponraj.m@egspec.edu', '8712349870', '8712349870', '2007-04-14', 'male', 'O+', 'Indian', 'SC', 'Murugan Durai', '8712349860', 'Farmer', 'Mangalam Murugan', 'Homemaker', '78, Velandipalayam', 'Coimbatore', 'Tamil Nadu', '641025', 'India', 4, 1, 1, 1, 1, '2025-07-22', 'regular', 'HSC', 74.80, 2025, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 243 DAY), DATE_SUB(NOW(), INTERVAL 243 DAY)),
(1, 1, 'ADM-2025-00058', 'STU-EGSPEC-2025-0058', 'MBA-006', 'Harini', 'Venkataraman', 'harini.v@egspec.edu', '9874512340', '9874512340', '1999-10-08', 'female', 'B+', 'Indian', 'BC', 'Venkataraman S', '9874512330', 'Business', 'Saraswathi Venkataraman', 'Homemaker', '23, Vilankurichi', 'Coimbatore', 'Tamil Nadu', '641035', 'India', 7, 2, 4, 1, 1, '2025-07-15', 'regular', 'BBA', 85.60, 2025, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 250 DAY), DATE_SUB(NOW(), INTERVAL 250 DAY)),
(1, 1, 'ADM-2025-00059', 'STU-EGSPEC-2025-0059', 'BTIT-007', 'Subramani', 'Pandi', 'subra.p@egspec.edu', '9632547809', '9632547809', '2007-08-30', 'male', 'A-', 'Indian', 'MBC', 'Pandi Vel', '9632547799', 'Farmer', 'Parimala Pandi', 'Homemaker', '56, Sundarapuram', 'Coimbatore', 'Tamil Nadu', '641024', 'India', 8, 1, 5, 1, 1, '2025-07-25', 'regular', 'HSC', 81.00, 2025, 'active', 'hosteller', NULL, 1, DATE_SUB(NOW(), INTERVAL 240 DAY), DATE_SUB(NOW(), INTERVAL 240 DAY)),
(1, 1, 'ADM-2025-00060', 'STU-EGSPEC-2025-0060', 'BCOM-007', 'Priyadharshini', 'Arumugam', 'priya.aru@egspec.edu', '9876543100', '9876543100', '2007-05-05', 'female', 'AB+', 'Indian', 'OC', 'Arumugam P', '9876543090', 'Professor', 'Kalpana Arumugam', 'Lecturer', '12, Ondipudur', 'Coimbatore', 'Tamil Nadu', '641016', 'India', 6, 3, 4, 1, 1, '2025-07-25', 'regular', 'HSC', 92.60, 2025, 'active', 'day_scholar', NULL, 1, DATE_SUB(NOW(), INTERVAL 240 DAY), DATE_SUB(NOW(), INTERVAL 240 DAY));

-- ============================================================
-- 8. 10 ADMISSIONS
-- ============================================================
INSERT INTO admissions (institution_id, admission_number, lead_id, student_id, course_id, batch_id, academic_year_id, first_name, last_name, email, phone, date_of_birth, gender, application_date, admission_date, admission_type, status, remarks, approved_by, approved_at, created_by, created_at, updated_at) VALUES
(1, 'ADM-APP-2025-001', 26, 23, 3, 2, 1, 'Nithya', 'Kalaichelvan', 'nithya.k@egspec.edu', '9543219870', '2002-03-09', 'female', DATE_SUB(CURDATE(), INTERVAL 65 DAY), DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'regular', 'enrolled', 'Enrolled in MCA 2025-26', 1, DATE_SUB(NOW(), INTERVAL 60 DAY), 1, DATE_SUB(NOW(), INTERVAL 65 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 'ADM-APP-2025-002', 27, 39, 1, 1, 1, 'Gowtham', 'Selvakumar', 'gowtham.s@egspec.edu', '8812349870', '2005-05-22', 'male', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 55 DAY), 'regular', 'enrolled', 'Enrolled in BCA 2025-26', 1, DATE_SUB(NOW(), INTERVAL 55 DAY), 1, DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 55 DAY)),
(1, 'ADM-APP-2025-003', 28, 37, 7, 2, 1, 'Keerthana', 'Ramachandran', 'keerthi.r@egspec.edu', '9632541870', '2001-01-05', 'female', DATE_SUB(CURDATE(), INTERVAL 55 DAY), DATE_SUB(CURDATE(), INTERVAL 50 DAY), 'regular', 'enrolled', 'Enrolled in MBA 2025-26', 1, DATE_SUB(NOW(), INTERVAL 50 DAY), 1, DATE_SUB(NOW(), INTERVAL 55 DAY), DATE_SUB(NOW(), INTERVAL 50 DAY)),
(1, 'ADM-APP-2025-004', 29, 19, 2, 3, 1, 'Saravanan', 'Thiyagarajan', 'sara.t@egspec.edu', '7721345690', '2000-08-30', 'male', DATE_SUB(CURDATE(), INTERVAL 50 DAY), DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'regular', 'enrolled', 'Enrolled in BBA 2025-26', 1, DATE_SUB(NOW(), INTERVAL 45 DAY), 1, DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),
(1, 'ADM-APP-2025-005', 30, 31, 6, 3, 1, 'Pooja', 'Arunachalam', 'pooja.a@egspec.edu', '9874561230', '2006-04-14', 'female', DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_SUB(CURDATE(), INTERVAL 40 DAY), 'regular', 'enrolled', 'Enrolled in B.Com 2025-26', 1, DATE_SUB(NOW(), INTERVAL 40 DAY), 1, DATE_SUB(NOW(), INTERVAL 45 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),
(1, 'ADM-APP-2025-006', 21, NULL, 8, 1, 1, 'Dinesh', 'Murugesan', 'dinesh.muru@gmail.com', '9765432108', '2005-07-04', 'male', DATE_SUB(CURDATE(), INTERVAL 30 DAY), NULL, 'regular', 'approved', 'Documents verified, awaiting enrollment', 1, DATE_SUB(NOW(), INTERVAL 25 DAY), 1, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY)),
(1, 'ADM-APP-2025-007', 22, NULL, 7, 2, 1, 'Revathi', 'Annamalai', 'revathi.a@gmail.com', '8843219876', '2000-05-30', 'female', DATE_SUB(CURDATE(), INTERVAL 28 DAY), NULL, 'regular', 'approved', 'MBA application approved pending fee payment', 1, DATE_SUB(NOW(), INTERVAL 22 DAY), 1, DATE_SUB(NOW(), INTERVAL 28 DAY), DATE_SUB(NOW(), INTERVAL 22 DAY)),
(1, 'ADM-APP-2025-008', 23, NULL, 1, 1, 1, 'Balamurugan', 'Velu', 'bala.velu@gmail.com', '9543678901', '2005-10-12', 'male', DATE_SUB(CURDATE(), INTERVAL 25 DAY), NULL, 'regular', 'under_review', 'Documents being reviewed', NULL, NULL, 1, DATE_SUB(NOW(), INTERVAL 25 DAY), DATE_SUB(NOW(), INTERVAL 20 DAY)),
(1, 'ADM-APP-2025-009', 24, NULL, 4, 1, 1, 'Pavithra', 'Sundar', 'pavithra.s@gmail.com', '7812349876', '2006-08-21', 'female', DATE_SUB(CURDATE(), INTERVAL 22 DAY), NULL, 'regular', 'documents_pending', 'Waiting for original mark sheets', NULL, NULL, 1, DATE_SUB(NOW(), INTERVAL 22 DAY), DATE_SUB(NOW(), INTERVAL 18 DAY)),
(1, 'ADM-APP-2025-010', 25, NULL, 2, 3, 1, 'Ashwin', 'Natarajan', 'ashwin.nat@gmail.com', '9876123450', '2005-12-28', 'male', DATE_SUB(CURDATE(), INTERVAL 18 DAY), NULL, 'regular', 'applied', 'Application received, pending review', NULL, NULL, 1, DATE_SUB(NOW(), INTERVAL 18 DAY), DATE_SUB(NOW(), INTERVAL 15 DAY));

-- ============================================================
-- 9. STUDENT FEES (for 20 students)
-- ============================================================
INSERT INTO student_fees (institution_id, student_id, fee_structure_id, academic_year_id, total_amount, discount_amount, discount_reason, scholarship_amount, net_amount, paid_amount, balance_amount, status, created_by, created_at, updated_at) VALUES
-- BCA students (fee_structure_id=1, total=55000)
(1, 1, 1, 1, 55000.00, 0.00, NULL, 0.00, 55000.00, 55000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 90 DAY)),
(1, 2, 1, 1, 55000.00, 5000.00, 'Merit scholarship', 0.00, 50000.00, 50000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 120 DAY)),
(1, 3, 1, 1, 55000.00, 0.00, NULL, 0.00, 55000.00, 30000.00, 25000.00, 'partial', 1, DATE_SUB(NOW(), INTERVAL 718 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 4, 1, 1, 55000.00, 0.00, NULL, 5500.00, 49500.00, 49500.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 718 DAY), DATE_SUB(NOW(), INTERVAL 100 DAY)),
(1, 5, 1, 1, 55000.00, 0.00, NULL, 0.00, 55000.00, 25000.00, 30000.00, 'partial', 1, DATE_SUB(NOW(), INTERVAL 715 DAY), DATE_SUB(NOW(), INTERVAL 45 DAY)),
-- BBA students (fee_structure_id=2, total=50000)
(1, 11, 2, 1, 50000.00, 0.00, NULL, 0.00, 50000.00, 50000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 150 DAY)),
(1, 12, 2, 1, 50000.00, 5000.00, 'BC scholarship', 0.00, 45000.00, 45000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 130 DAY)),
(1, 13, 2, 1, 50000.00, 0.00, NULL, 0.00, 50000.00, 20000.00, 30000.00, 'partial', 1, DATE_SUB(NOW(), INTERVAL 718 DAY), DATE_SUB(NOW(), INTERVAL 40 DAY)),
(1, 14, 2, 1, 50000.00, 0.00, NULL, 0.00, 50000.00, 50000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 718 DAY), DATE_SUB(NOW(), INTERVAL 110 DAY)),
(1, 15, 2, 1, 50000.00, 0.00, NULL, 0.00, 50000.00, 0.00, 50000.00, 'pending', 1, DATE_SUB(NOW(), INTERVAL 715 DAY), DATE_SUB(NOW(), INTERVAL 715 DAY)),
-- MCA students (fee_structure_id=3, total=75000)
(1, 21, 3, 1, 75000.00, 0.00, NULL, 0.00, 75000.00, 75000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 355 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 22, 3, 1, 75000.00, 7500.00, 'Top scorer discount', 0.00, 67500.00, 67500.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 355 DAY), DATE_SUB(NOW(), INTERVAL 80 DAY)),
(1, 23, 3, 1, 75000.00, 0.00, NULL, 0.00, 75000.00, 37500.00, 37500.00, 'partial', 1, DATE_SUB(NOW(), INTERVAL 353 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY)),
-- B.Sc CS students (fee_structure_id=4, total=48000)
(1, 26, 4, 1, 48000.00, 0.00, NULL, 0.00, 48000.00, 48000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 200 DAY)),
(1, 27, 4, 1, 48000.00, 0.00, NULL, 0.00, 48000.00, 24000.00, 24000.00, 'partial', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 50 DAY)),
-- B.Com students (fee_structure_id=5, total=45000)
(1, 31, 5, 1, 45000.00, 0.00, NULL, 0.00, 45000.00, 45000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 180 DAY)),
(1, 32, 5, 1, 45000.00, 0.00, NULL, 4500.00, 40500.00, 20250.00, 20250.00, 'partial', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 35 DAY)),
-- MBA students (fee_structure_id=6, total=85000)
(1, 36, 6, 1, 85000.00, 0.00, NULL, 0.00, 85000.00, 85000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 355 DAY), DATE_SUB(NOW(), INTERVAL 70 DAY)),
-- B.Tech IT students (fee_structure_id=7, total=80000)
(1, 41, 7, 1, 80000.00, 0.00, NULL, 0.00, 80000.00, 80000.00, 0.00, 'paid', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 160 DAY)),
(1, 42, 7, 1, 80000.00, 0.00, NULL, 0.00, 80000.00, 40000.00, 40000.00, 'partial', 1, DATE_SUB(NOW(), INTERVAL 720 DAY), DATE_SUB(NOW(), INTERVAL 25 DAY));

-- ============================================================
-- 10. PAYMENTS (10 payments linked to students)
-- ============================================================
INSERT INTO payments (institution_id, student_id, student_fee_id, receipt_number, amount, payment_date, payment_method, transaction_reference, remarks, status, collected_by, created_at, updated_at) VALUES
(1, 1, 1, 'RCP-2025-001', 25000.00, DATE_SUB(CURDATE(), INTERVAL 170 DAY), 'upi', 'UPI-TXN-9823471234', 'First installment BCA fees', 'success', 1, DATE_SUB(NOW(), INTERVAL 170 DAY), DATE_SUB(NOW(), INTERVAL 170 DAY)),
(1, 1, 1, 'RCP-2025-002', 30000.00, DATE_SUB(CURDATE(), INTERVAL 90 DAY), 'bank_transfer', 'NEFT-TXN-5612349876', 'Second installment BCA fees', 'success', 1, DATE_SUB(NOW(), INTERVAL 90 DAY), DATE_SUB(NOW(), INTERVAL 90 DAY)),
(1, 3, 3, 'RCP-2025-003', 15000.00, DATE_SUB(CURDATE(), INTERVAL 130 DAY), 'cash', NULL, 'First installment BCA fees', 'success', 1, DATE_SUB(NOW(), INTERVAL 130 DAY), DATE_SUB(NOW(), INTERVAL 130 DAY)),
(1, 3, 3, 'RCP-2025-004', 15000.00, DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'upi', 'UPI-TXN-7634519872', 'Second installment', 'success', 1, DATE_SUB(NOW(), INTERVAL 60 DAY), DATE_SUB(NOW(), INTERVAL 60 DAY)),
(1, 13, 8, 'RCP-2025-005', 20000.00, DATE_SUB(CURDATE(), INTERVAL 110 DAY), 'cheque', 'CHQ-001234', 'BBA first installment', 'success', 1, DATE_SUB(NOW(), INTERVAL 110 DAY), DATE_SUB(NOW(), INTERVAL 110 DAY)),
(1, 21, 11, 'RCP-2025-006', 25000.00, DATE_SUB(CURDATE(), INTERVAL 180 DAY), 'bank_transfer', 'NEFT-TXN-8823456712', 'MCA first installment', 'success', 1, DATE_SUB(NOW(), INTERVAL 180 DAY), DATE_SUB(NOW(), INTERVAL 180 DAY)),
(1, 21, 11, 'RCP-2025-007', 25000.00, DATE_SUB(CURDATE(), INTERVAL 120 DAY), 'upi', 'UPI-TXN-9912345678', 'MCA second installment', 'success', 1, DATE_SUB(NOW(), INTERVAL 120 DAY), DATE_SUB(NOW(), INTERVAL 120 DAY)),
(1, 23, 13, 'RCP-2025-008', 37500.00, DATE_SUB(CURDATE(), INTERVAL 80 DAY), 'bank_transfer', 'NEFT-TXN-7712345698', 'MCA first installment', 'success', 1, DATE_SUB(NOW(), INTERVAL 80 DAY), DATE_SUB(NOW(), INTERVAL 80 DAY)),
(1, 27, 15, 'RCP-2025-009', 24000.00, DATE_SUB(CURDATE(), INTERVAL 50 DAY), 'upi', 'UPI-TXN-8834561234', 'B.Sc CS first installment', 'success', 1, DATE_SUB(NOW(), INTERVAL 50 DAY), DATE_SUB(NOW(), INTERVAL 50 DAY)),
(1, 36, 18, 'RCP-2025-010', 25000.00, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'online', 'ONLINE-TXN-99234567', 'MBA installment payment via payment gateway', 'success', 1, DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 30 DAY));

-- ============================================================
-- RE-ENABLE FOREIGN KEY CHECKS
-- ============================================================
SET FOREIGN_KEY_CHECKS=1;

-- ============================================================
-- SUMMARY VERIFICATION QUERY
-- ============================================================
SELECT
  (SELECT COUNT(*) FROM leads) AS total_leads,
  (SELECT COUNT(*) FROM enquiries) AS total_enquiries,
  (SELECT COUNT(*) FROM students) AS total_students,
  (SELECT COUNT(*) FROM admissions) AS total_admissions,
  (SELECT COUNT(*) FROM student_fees) AS total_student_fees,
  (SELECT COUNT(*) FROM payments) AS total_payments,
  (SELECT COUNT(*) FROM fee_structures) AS total_fee_structures;
