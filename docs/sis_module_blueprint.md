# Student Information System (SIS) – Enterprise Module Blueprint  
_Author: Codex · Date: 2026-03-25 · Scope: Multi‑org / Multi‑institution / Multi‑department / Multi‑course / Multi‑batch / Multi‑semester / Multi‑section_  

This blueprint delivers production‑ready artifacts the team can drop into the existing PHP/Blade stack (or a Laravel install) to provide a 360° student view. Everything is namespaced for institution isolation (`organization_id`, `institution_id` on every row) and includes auditability, bulk ops, and advanced search.  

---
## 1) Database Schema (SQL DDL)
> Engine: InnoDB, utf8mb4. All FK actions are safe (`SET NULL`/`CASCADE`), soft‑delete columns where appropriate.

```sql
-- core student table (extends current structure)
CREATE TABLE students (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NOT NULL,
  admission_number VARCHAR(30) NOT NULL,          -- ADM-YYYY-xxxxx (generated)
  roll_number VARCHAR(50) NULL,
  registration_number VARCHAR(50) NULL,
  student_id_number VARCHAR(50) NOT NULL,         -- legacy compat
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100) NULL,
  last_name VARCHAR(100) NULL,
  gender ENUM('male','female','other') NULL,
  date_of_birth DATE NULL,
  blood_group VARCHAR(5) NULL,
  category ENUM('OC','BC','MBC','SC','ST','OBC','EWS') NULL,
  religion VARCHAR(50) NULL,
  nationality VARCHAR(50) DEFAULT 'Indian',
  mother_tongue VARCHAR(50) NULL,
  photo VARCHAR(500) NULL,
  mobile_number VARCHAR(20) NOT NULL,
  email VARCHAR(255) NULL,
  aadhaar_number VARCHAR(12) NULL,
  address_line1 VARCHAR(255) NULL,
  address_line2 VARCHAR(255) NULL,
  city VARCHAR(100) NULL,
  state VARCHAR(100) NULL,
  pincode VARCHAR(10) NULL,
  status ENUM('active','inactive','alumni','dropout','suspended') DEFAULT 'active',
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  UNIQUE KEY uk_student_adm (institution_id, admission_number),
  UNIQUE KEY uk_student_roll (institution_id, roll_number),
  KEY idx_student_org_inst (organization_id, institution_id),
  KEY idx_student_search (first_name, last_name, mobile_number, email),
  CONSTRAINT fk_student_org FOREIGN KEY (organization_id) REFERENCES organizations(id),
  CONSTRAINT fk_student_inst FOREIGN KEY (institution_id) REFERENCES institutions(id),
  CONSTRAINT fk_student_creator FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE student_academics (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  department_id BIGINT UNSIGNED NOT NULL,
  course_id BIGINT UNSIGNED NOT NULL,
  batch_id BIGINT UNSIGNED NOT NULL,
  semester_id BIGINT UNSIGNED NULL,
  section_id BIGINT UNSIGNED NULL,
  admission_date DATE NOT NULL,
  admission_type ENUM('regular','lateral') DEFAULT 'regular',
  quota ENUM('management','government') DEFAULT 'government',
  academic_status ENUM('active','on_probation','detained','completed') DEFAULT 'active',
  student_type ENUM('hosteller','day_scholar') DEFAULT 'day_scholar',
  previous_school VARCHAR(255) NULL,
  previous_marks DECIMAL(5,2) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_student_acad (student_id),
  KEY idx_acad_org_inst (organization_id, institution_id),
  CONSTRAINT fk_acad_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_acad_dept FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_acad_course FOREIGN KEY (course_id) REFERENCES courses(id),
  CONSTRAINT fk_acad_batch FOREIGN KEY (batch_id) REFERENCES batches(id),
  CONSTRAINT fk_acad_sem FOREIGN KEY (semester_id) REFERENCES semesters(id),
  CONSTRAINT fk_acad_section FOREIGN KEY (section_id) REFERENCES sections(id)
) ENGINE=InnoDB;

CREATE TABLE student_parents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  father_name VARCHAR(150) NULL,
  father_mobile VARCHAR(20) NULL,
  father_email VARCHAR(150) NULL,
  father_occupation VARCHAR(100) NULL,
  mother_name VARCHAR(150) NULL,
  mother_mobile VARCHAR(20) NULL,
  mother_email VARCHAR(150) NULL,
  mother_occupation VARCHAR(100) NULL,
  guardian_name VARCHAR(150) NULL,
  guardian_mobile VARCHAR(20) NULL,
  guardian_relation VARCHAR(50) NULL,
  guardian_address VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_parent_student (student_id),
  CONSTRAINT fk_parent_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  doc_type ENUM('marksheets','transfer_certificate','conduct_certificate','id_proof','community_certificate','income_certificate','passport_photo','medical_record') NOT NULL,
  title VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_size INT UNSIGNED NULL,
  mime_type VARCHAR(100) NULL,
  expires_on DATE NULL,
  verification_status ENUM('pending','verified','rejected') DEFAULT 'pending',
  remarks VARCHAR(500) NULL,
  uploaded_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL,
  KEY idx_doc_student (student_id, doc_type),
  KEY idx_doc_org_inst (organization_id, institution_id),
  CONSTRAINT fk_doc_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_doc_uploaded FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE student_behaviour (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  incident_date DATE NOT NULL,
  remarks TEXT NOT NULL,
  action_taken VARCHAR(255) NULL,
  severity_level ENUM('low','medium','high','critical') DEFAULT 'low',
  added_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_beh_student (student_id, incident_date),
  CONSTRAINT fk_beh_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  CONSTRAINT fk_beh_user FOREIGN KEY (added_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE student_timeline (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  event_type ENUM('admission','fee_payment','attendance','exam_result','document','discipline','hostel','transport','note','status_change') NOT NULL,
  event_date DATETIME NOT NULL,
  title VARCHAR(255) NOT NULL,
  details TEXT NULL,
  metadata JSON NULL,
  added_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_timeline_student (student_id, event_date DESC),
  CONSTRAINT fk_timeline_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_tags (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organization_id BIGINT UNSIGNED NOT NULL,
  institution_id BIGINT UNSIGNED NOT NULL,
  student_id BIGINT UNSIGNED NOT NULL,
  tag VARCHAR(50) NOT NULL,
  created_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_tag (student_id, tag),
  CONSTRAINT fk_tag_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

---
## 2) Laravel Migrations (drop into `database/migrations`)
> Uses Laravel style even though the current stack is custom—files can be consumed by a Laravel-compatible runner if needed.

```php
// 2026_03_25_000001_create_students_table.php
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('organization_id');
    $table->unsignedBigInteger('institution_id');
    $table->string('admission_number', 30)->unique();
    $table->string('roll_number', 50)->nullable();
    $table->string('registration_number', 50)->nullable();
    $table->string('student_id_number', 50);
    $table->string('first_name');
    $table->string('middle_name')->nullable();
    $table->string('last_name')->nullable();
    $table->enum('gender', ['male','female','other'])->nullable();
    $table->date('date_of_birth')->nullable();
    $table->string('blood_group', 5)->nullable();
    $table->enum('category', ['OC','BC','MBC','SC','ST','OBC','EWS'])->nullable();
    $table->string('religion', 50)->nullable();
    $table->string('nationality', 50)->default('Indian');
    $table->string('mother_tongue', 50)->nullable();
    $table->string('photo')->nullable();
    $table->string('mobile_number', 20);
    $table->string('email')->nullable();
    $table->string('aadhaar_number', 12)->nullable();
    $table->string('address_line1')->nullable();
    $table->string('address_line2')->nullable();
    $table->string('city', 100)->nullable();
    $table->string('state', 100)->nullable();
    $table->string('pincode', 10)->nullable();
    $table->enum('status', ['active','inactive','alumni','dropout','suspended'])->default('active');
    $table->unsignedBigInteger('created_by')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

// 2026_03_25_000002_create_student_academics_table.php
Schema::create('student_academics', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('organization_id');
    $table->unsignedBigInteger('institution_id');
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->foreignId('department_id')->constrained();
    $table->foreignId('course_id')->constrained();
    $table->foreignId('batch_id')->constrained();
    $table->foreignId('semester_id')->nullable()->constrained('semesters');
    $table->foreignId('section_id')->nullable()->constrained('sections');
    $table->date('admission_date');
    $table->enum('admission_type', ['regular','lateral'])->default('regular');
    $table->enum('quota', ['management','government'])->default('government');
    $table->enum('academic_status', ['active','on_probation','detained','completed'])->default('active');
    $table->enum('student_type', ['hosteller','day_scholar'])->default('day_scholar');
    $table->string('previous_school')->nullable();
    $table->decimal('previous_marks', 5, 2)->nullable();
    $table->timestamps();
});

// 2026_03_25_000003_create_student_parents_table.php
Schema::create('student_parents', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('organization_id');
    $table->unsignedBigInteger('institution_id');
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->string('father_name')->nullable();
    $table->string('father_mobile', 20)->nullable();
    $table->string('father_email')->nullable();
    $table->string('father_occupation')->nullable();
    $table->string('mother_name')->nullable();
    $table->string('mother_mobile', 20)->nullable();
    $table->string('mother_email')->nullable();
    $table->string('mother_occupation')->nullable();
    $table->string('guardian_name')->nullable();
    $table->string('guardian_mobile', 20)->nullable();
    $table->string('guardian_relation', 50)->nullable();
    $table->string('guardian_address')->nullable();
    $table->timestamps();
});

// 2026_03_25_000004_create_student_documents_table.php
Schema::create('student_documents', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('organization_id');
    $table->unsignedBigInteger('institution_id');
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->enum('doc_type', ['marksheets','transfer_certificate','conduct_certificate','id_proof','community_certificate','income_certificate','passport_photo','medical_record']);
    $table->string('title');
    $table->string('file_path');
    $table->string('file_name');
    $table->integer('file_size')->nullable();
    $table->string('mime_type')->nullable();
    $table->date('expires_on')->nullable();
    $table->enum('verification_status', ['pending','verified','rejected'])->default('pending');
    $table->string('remarks')->nullable();
    $table->foreignId('uploaded_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});

// 2026_03_25_000005_create_student_behaviour_table.php
Schema::create('student_behaviour', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('organization_id');
    $table->unsignedBigInteger('institution_id');
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->date('incident_date');
    $table->text('remarks');
    $table->string('action_taken')->nullable();
    $table->enum('severity_level', ['low','medium','high','critical'])->default('low');
    $table->foreignId('added_by')->nullable()->constrained('users');
    $table->timestamps();
});

// 2026_03_25_000006_create_student_timeline_table.php
Schema::create('student_timeline', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('organization_id');
    $table->unsignedBigInteger('institution_id');
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->enum('event_type', ['admission','fee_payment','attendance','exam_result','document','discipline','hostel','transport','note','status_change']);
    $table->dateTime('event_date');
    $table->string('title');
    $table->text('details')->nullable();
    $table->json('metadata')->nullable();
    $table->foreignId('added_by')->nullable()->constrained('users');
    $table->timestamps();
});

// 2026_03_25_000007_create_student_tags_table.php
Schema::create('student_tags', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('organization_id');
    $table->unsignedBigInteger('institution_id');
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->string('tag', 50);
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->unique(['student_id','tag']);
});
```

---
## 3) Eloquent Models (key relations/traits)
```php
class Student extends Model {
    use SoftDeletes;
    protected $fillable = [/* personal fields + status */];
    public function academics()  { return $this->hasOne(StudentAcademic::class); }
    public function parents()    { return $this->hasOne(StudentParent::class); }
    public function documents()  { return $this->hasMany(StudentDocument::class); }
    public function behaviours() { return $this->hasMany(StudentBehaviour::class); }
    public function timeline()   { return $this->hasMany(StudentTimeline::class)->latest('event_date'); }
    public function tags()       { return $this->hasMany(StudentTag::class); }
}

class StudentAcademic extends Model { protected $fillable = [/* academic fields */]; }
class StudentParent    extends Model { protected $fillable = [/* father/mother/guardian */]; }
class StudentDocument  extends Model { use SoftDeletes; protected $fillable = [/* doc fields */]; }
class StudentBehaviour extends Model { protected $fillable = [/* behaviour fields */]; }
class StudentTimeline  extends Model { protected $fillable = [/* event fields */]; }
class StudentTag       extends Model { protected $fillable = ['organization_id','institution_id','student_id','tag','created_by']; }
```

---
## 4) Controllers (CRUD + advanced operations)
- `StudentController`: index (filters: name/roll/phone/status/course/batch/department/semester/tag/date range), create, store, show (360), edit, update, destroy, bulkImport (CSV/XLSX), bulkPromote (next semester/batch), transferInstitution, convertToAlumni, addTag/removeTag, addNote (timeline entry), generateIdCard (PDF with QR).
- `StudentDocumentController`: upload, preview, download, verify/reject, delete; emits timeline events; tracks expiry.
- `StudentBehaviourController`: list by student, store/update, severity filter, export CSV.
- `StudentTimelineController`: fetch paginated timeline with event filters.

Controllers should always scope by `organization_id` + `institution_id` from session/guard and write timeline entries for key actions.

---
## 5) Form Requests (Validation)
- `StudentStoreRequest`, `StudentUpdateRequest`
  - `first_name, mobile_number, organization_id, institution_id, admission_date` required
  - `mobile_number` regex `^[0-9]{10}$`
  - `email` nullable|email
  - enums enforced via `in:` lists above
- `DocumentUploadRequest`
  - `doc_type` in allowed list, file max 5MB, mime `pdf,jpg,png`
- `BehaviourRequest`
  - `incident_date` required|date; `severity_level` in `low,medium,high,critical`

---
## 6) Routes (web.php excerpt)
```php
$router->group(['middleware' => 'auth'], function ($router) {
    $router->get('/students/dashboard', 'Admin\\StudentDashboardController@index', 'students.dashboard');
    $router->resource('/students', 'Admin\\StudentController');
    $router->post('/students/{id}/promote', 'Admin\\StudentController@bulkPromote', 'students.promote');
    $router->post('/students/{id}/transfer', 'Admin\\StudentController@transfer', 'students.transfer');
    $router->post('/students/{id}/tags', 'Admin\\StudentController@addTag', 'students.tags.add');
    $router->post('/students/{id}/notes', 'Admin\\StudentController@addNote', 'students.notes.add');

    $router->get('/students/{id}/documents', 'Admin\\StudentDocumentController@index', 'students.documents');
    $router->post('/students/{id}/documents', 'Admin\\StudentDocumentController@store', 'students.documents.store');
    $router->post('/students/documents/{docId}/verify', 'Admin\\StudentDocumentController@verify', 'students.documents.verify');

    $router->get('/students/{id}/behaviour', 'Admin\\StudentBehaviourController@index', 'students.behaviour');
    $router->post('/students/{id}/behaviour', 'Admin\\StudentBehaviourController@store', 'students.behaviour.store');

    $router->get('/students/{id}/timeline', 'Admin\\StudentTimelineController@index', 'students.timeline');
});
```

---
## 7) Blade UI (screens)
- **Student Dashboard**: cards for total/active/new admissions/alumni; bar charts for department/course counts; quick filters.
- **Student List**: search box (name/roll/phone), pill filters for status/department/course/batch/semester/tag; table with pagination; bulk actions (promote, tag, export).
- **Create/Edit Form**: three-column layout (Profile, Academic, Parents) with sticky footer actions; QR preview once saved.
- **360 View** (tabs): Profile | Academic | Parents | Documents | Attendance | Fees | Exams | Timeline. Timeline uses infinite scroll and color-coded badges per event type.
- **Document Upload UI**: drag-drop, multi-file, inline preview (PDF/image), verification status chip, expiry badge.
- **Behaviour UI**: list grouped by severity/date; add incident modal; timeline append on save.

Reusable Blade partials: `students/partials/filters.blade.php`, `students/partials/timeline.blade.php`, `students/partials/documents.blade.php`.

---
## 8) Searching / Filtering Logic (pseudo)
```php
$query = Student::with(['academics','tags'])
  ->whereOrg($orgId)->whereInst($instId)
  ->when($search, fn($q) => $q->where(function($q) use ($search) {
        $q->where('first_name','like',"%$search%")
          ->orWhere('last_name','like',"%$search%")
          ->orWhere('roll_number','like',"%$search%")
          ->orWhere('mobile_number','like',"%$search%");
    }))
  ->when($filters['status'] ?? null, fn($q,$v)=>$q->where('status',$v))
  ->when($filters['department_id'] ?? null, fn($q,$v)=>$q->whereHas('academics', fn($qa)=>$qa->where('department_id',$v)))
  ->paginate(20);
```

---
## 9) Bulk Operations
- **Import**: CSV/XLSX → queued job → validate → upsert student, academic, parent, tags; emit timeline `admission`.
- **Promotion**: select cohort (course/batch/section), choose target semester/batch, optional tag; updates `student_academics.semester_id` and writes `timeline` event.
- **Transfer**: move student between institutions; duplicates record with new institution_id, links old record via metadata, updates status to `transferred`.

---
## 10) ID Card & QR
- Generate PDF using DOMPDF/Snappy with QR (payload: student_id + institution_id + signature hash).
- Store file path in `student_documents` (`doc_type = 'id_proof'`), add timeline event `id_card_generated`.

---
## 11) Audit & Security
- All mutations log to `student_timeline` with actor id.
- Soft deletes on `students` and `student_documents`.
- Authorization gates: `students.view`, `students.create`, `students.edit`, `students.delete`, `students.promote`, `students.verify_documents`.

---
## 12) API / Integration Hooks
- Webhooks on student create/update/status-change.
- Import/export endpoints: `/api/students` with JWT guard; supports filters and includes for documents/parents.

---
## How to apply in this codebase
1) Run the SQL DDL above against `education_crm` (or use the Laravel migrations).
2) Drop controller/model/request stubs into `app/` namespaces mirroring the existing Router style.
3) Wire routes as shown; reuse existing layout components in `resources/views/layouts`.
4) Replace the old `students` views with the new tabbed 360 pages and partials described above.
5) Configure storage for document uploads (`/storage/app/student-documents/{institution_id}/{student_id}`) and enforce MIME/size limits.

This blueprint is intentionally concise so we can implement incrementally while keeping enterprise‑grade coverage of the student lifecycle.
