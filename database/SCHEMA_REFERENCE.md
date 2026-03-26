# Education CRM - Database Schema Reference

## Tables Overview (40 Tables)

### Core (4 tables)
| # | Table | Purpose |
|---|-------|---------|
| 1 | `organizations` | Parent company/trust managing multiple institutions |
| 2 | `institutions` | Individual colleges (Engineering, Medical, etc.) |
| 3 | `departments` | Departments within each institution |
| 4 | `academic_years` | Academic year periods per institution |

### Authentication & RBAC (8 tables)
| # | Table | Purpose |
|---|-------|---------|
| 5 | `roles` | System roles (Super Admin, Counselor, etc.) |
| 6 | `permissions` | Granular permissions (85+ permissions) |
| 7 | `role_permissions` | Role-to-permission mapping |
| 8 | `users` | All system users |
| 9 | `user_roles` | User-role-institution mapping (multi-institution support) |
| 10 | `user_sessions` | Active sessions tracking |
| 11 | `password_resets` | Password reset tokens |
| 12 | `audit_logs` | Complete audit trail |

### Lead Management (5 tables)
| # | Table | Purpose |
|---|-------|---------|
| 13 | `lead_sources` | Where leads come from (Website, Walk-in, etc.) |
| 14 | `lead_statuses` | Pipeline stages (New -> Converted/Lost) |
| 15 | `leads` | Lead records with full contact & academic info |
| 16 | `lead_activities` | Timeline of all lead interactions |
| 17 | `enquiries` | Initial enquiries before lead creation |

### Follow-ups & Tasks (2 tables)
| # | Table | Purpose |
|---|-------|---------|
| 18 | `followups` | Scheduled follow-up actions |
| 19 | `tasks` | General tasks linked to any entity |

### Courses & Batches (4 tables)
| # | Table | Purpose |
|---|-------|---------|
| 20 | `courses` | Course catalog per institution |
| 21 | `batches` | Batch/section management |
| 22 | `faculty_profiles` | Faculty teaching profiles |
| 23 | `batch_faculty` | Faculty-batch assignments |

### Students & Admissions (4 tables)
| # | Table | Purpose |
|---|-------|---------|
| 24 | `students` | Complete student profiles (360 view) |
| 25 | `admissions` | Admission applications & tracking |
| 26 | `documents` | Document uploads (polymorphic) |
| 27 | `student_activities` | Student timeline |

### Fee & Finance (6 tables)
| # | Table | Purpose |
|---|-------|---------|
| 28 | `fee_structures` | Fee configuration per course |
| 29 | `fee_components` | Fee breakdown (Tuition, Lab, etc.) |
| 30 | `installment_plans` | Installment schedule templates |
| 31 | `student_fees` | Per-student fee assignments |
| 32 | `student_installments` | Per-student installment tracking |
| 33 | `payments` | Payment transactions & receipts |

### Communication (5 tables)
| # | Table | Purpose |
|---|-------|---------|
| 34 | `communication_templates` | Email/SMS/WhatsApp templates |
| 35 | `communications` | Communication log |
| 36 | `bulk_campaigns` | Bulk messaging campaigns |
| 37 | `notifications` | In-app notifications |
| 38 | `communication_settings` | Channel provider configuration |

### Attendance Management (1 table)
| # | Table | Purpose |
|---|-------|---------|
| 41 | `attendances` | Daily and subject-wise attendance tracking |

### Academics & Timetable (2 tables)
| # | Table | Purpose |
|---|-------|---------|
| 42 | `subjects` | Course subjects/modules |
| 43 | `timetables` | Weekly class schedules |

### Exams & Results (3 tables)
| # | Table | Purpose |
|---|-------|---------|
| 44 | `exams` | Exam configurations (internal/semester) |
| 45 | `exam_schedules` | Subject-wise exam dates and marks |
| 46 | `exam_marks` | Student marks and grades |

### Hostel Management (3 tables)
| # | Table | Purpose |
|---|-------|---------|
| 47 | `hostels` | Hostel facilities configuration |
| 48 | `hostel_rooms` | Room and bed capacity tracking |
| 49 | `hostel_allocations` | Student room assignments |

### Transport Management (3 tables)
| # | Table | Purpose |
|---|-------|---------|
| 50 | `transport_routes` | School/College bus routes |
| 51 | `transport_stops` | Pickup and drop points |
| 52 | `transport_allocations` | Student transport assignments |

### Library Management (2 tables)
| # | Table | Purpose |
|---|-------|---------|
| 53 | `library_books` | Book catalog and inventory |
| 54 | `library_issues` | Book issuance and return logs |

### HR & Payroll (3 tables)
| # | Table | Purpose |
|---|-------|---------|
| 55 | `staff_profiles` | Detailed staff/faculty info |
| 56 | `staff_leave_requests` | Leave management |
| 57 | `payslips` | Monthly salary generation |

### Placement Cell (3 tables)
| # | Table | Purpose |
|---|-------|---------|
| 58 | `placement_companies` | Recruiting company details |
| 59 | `placement_drives` | Campus recruitment events |
| 60 | `placement_selections` | Student placement tracking |

### System (2 tables)
| # | Table | Purpose |
|---|-------|---------|
| 61 | `settings` | System & institution settings |
| 62 | `uploads` | Centralized file upload tracking |

## Key Relationships

```
Organization (1) ──── (N) Institution
Institution  (1) ──── (N) Department
Institution  (1) ──── (N) Course
Institution  (1) ──── (N) Lead
Institution  (1) ──── (N) Student
Institution  (1) ──── (N) Admission

Course       (1) ──── (N) Batch
Course       (1) ──── (N) Fee Structure

Lead         (1) ──── (N) Lead Activity
Lead         (1) ──── (N) Follow-up
Lead         (1) ──── (1) Admission (conversion)

Admission    (1) ──── (1) Student (enrollment)
Student      (1) ──── (N) Student Fee
Student Fee  (1) ──── (N) Student Installment
Student Fee  (1) ──── (N) Payment

User         (N) ──── (N) Role (via user_roles, scoped to institution)
Role         (N) ──── (N) Permission (via role_permissions)
```

## Default Credentials
- **Email:** admin@educrm.com
- **Password:** Admin@123

## Default Roles (9)
1. Super Admin (level 0) - Full system access
2. Organization Admin (level 1) - All institutions in org
3. Institution Admin (level 2) - Single institution
4. Counselor (level 3) - Leads & follow-ups
5. Admission Officer (level 3) - Admissions & students
6. Finance Officer (level 3) - Fees & payments
7. Faculty (level 3) - View students & courses
8. Front Desk (level 3) - Enquiries & walk-ins
9. Report Viewer (level 3) - View-only reports

## Indexing Strategy
- Primary keys on all tables (auto-increment BIGINT)
- Foreign keys with appropriate ON DELETE actions
- Composite unique keys for business rules
- Indexes on: status columns, date columns, lookup fields (email, phone)
- JSON columns for flexible metadata storage
