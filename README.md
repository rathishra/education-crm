# Education CRM — Multi-Tenant SaaS Platform

A production-ready **Education CRM & Student Information System** built on a custom PHP 8.1 MVC framework, supporting the full student lifecycle from enquiry to alumni.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.1, Custom MVC Framework |
| Database | MySQL 8 / MariaDB 10.6 |
| Frontend | Bootstrap 5, Font Awesome 6, Vanilla JS |
| Auth | Session-based with CSRF protection |
| Server | Apache / PHP Dev Server |

---

## Multi-Tenant Hierarchy

```
Organization
  └── Institution
        ├── Campus
        ├── Department
        │     └── Course → Batch → Section
        └── Academic Year
```

Each user is scoped to their institution automatically — all queries are filtered by `institution_id` at the model layer.

---

## Modules

| Module | Features |
|---|---|
| **Organizations** | Multi-org management, CSV import/export, status toggle |
| **Institutions** | Per-org institutions, branding, principal info |
| **Departments** | Code auto-generation, HOD assignment |
| **Courses** | Degree types, duration, seat capacity |
| **Batches** | Academic year linkage, strength tracking |
| **Sections** | Auto-generate A/B/C, class teacher assignment |
| **Campuses** | Multi-campus support, capacity management |
| **Leads** | Pipeline (New → Contacted → Demo → Enrolled), source tracking |
| **Enquiries** | Walk-in enquiry capture, convert to lead |
| **Follow-ups** | Calendar view, due reminders |
| **Admissions** | Apply → Review → Approve → Enroll workflow |
| **Students** | 360° profile, parents, documents, behaviour log, timeline |
| **Fees** | Structure builder, installment plans, receipts |
| **Payments** | Collect, receipt generation, due-list |
| **Attendance** | Daily marking, monthly reports |
| **Exams** | Schedule, marks entry, result computation |
| **Subjects** | Course-wise subject mapping |
| **Timetable** | Period-wise scheduling |
| **Communication** | SMS/Email templates, bulk campaigns |
| **HR & Payroll** | Staff management, payslip generation |
| **Hostel** | Room allocation, occupancy tracking |
| **Transport** | Route/stop management, student allocation |
| **Library** | Book catalogue, issue/return tracking |
| **Placement** | Company drives, application tracking |
| **Reports** | Leads, admissions, revenue, counselor performance |
| **Audit Logs** | Full action trail with user, IP, timestamp |
| **Settings** | Institution branding, communication gateway config |

---

## Quick Start

### 1. Prerequisites
- PHP 8.1+
- MySQL 8 / MariaDB 10.6+
- Apache with `mod_rewrite` (or PHP built-in server)

### 2. Database Setup
```bash
mysql -u root -p -e "CREATE DATABASE education_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p education_crm < database/00_run_all.sql
```

### 3. Environment
```bash
cp .env.example .env
# Edit .env with your DB credentials and APP_URL
```

### 4. Run
```bash
# PHP dev server
php -S localhost:8000 -t public/

# Or configure Apache/Nginx to point document root to public/
```

### 5. Login
```
URL:      http://localhost:8000
Email:    admin@educrm.com
Password: Admin@123
```

---

## Project Structure

```
education-crm/
├── app/
│   ├── Controllers/
│   │   ├── Admin/          # All admin module controllers
│   │   ├── Auth/           # Login, logout, profile
│   │   ├── Api/            # REST API endpoints
│   │   └── Front/          # Public admission form
│   ├── Models/             # BaseModel + entity models
│   ├── Views/              # PHP templates (Bootstrap 5)
│   └── Middleware/         # Auth, CSRF, Permission guards
├── core/
│   ├── App.php             # Bootstrap, DI container
│   ├── Router/Router.php   # PSR-4 router
│   ├── Database/
│   │   ├── Database.php    # PDO wrapper + query builder
│   │   └── SchemaRepair.php# Auto-migration on boot
│   ├── Session/Session.php
│   └── helpers.php         # Global helper functions
├── config/                 # app.php, database.php
├── database/               # SQL schema files + seed data
├── routes/
│   ├── web.php             # All web routes
│   └── api.php             # API routes
├── public/                 # Document root (index.php, assets)
└── storage/                # Logs, cache, sessions
```

---

## Key Design Decisions

- **No Composer / No Laravel** — Zero external dependencies, pure PHP 8.1
- **Institution Scoping** — `BaseModel::$institutionId` automatically filters all queries
- **SchemaRepair** — Runs on every boot, adds missing columns using `INFORMATION_SCHEMA` (MariaDB-compatible)
- **CSRF Protection** — Token stored in session, verified on every POST
- **Soft Deletes** — `deleted_at` timestamp on all major tables
- **Audit Trail** — Every create/update/delete is logged to `audit_logs`

---

## Default Credentials

| Role | Email | Password |
|---|---|---|
| Super Admin | admin@educrm.com | Admin@123 |

---

## License

Proprietary — All rights reserved.
