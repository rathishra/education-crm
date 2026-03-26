<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Student;

class StudentController extends BaseController
{
    private Student $student;

    public function __construct()
    {
        $this->student = new Student();
    }

    public function index(): void
    {
        $this->authorize('students.view');

        $db = db();
        // Self-healing for students table
        try {
            $db->query("SHOW COLUMNS FROM students LIKE 'aadhar_number'");
            if (!$db->fetch()) {
                $db->query("ALTER TABLE students ADD COLUMN aadhar_number VARCHAR(12) DEFAULT NULL AFTER gender");
            }
            $db->query("SHOW COLUMNS FROM students LIKE 'admission_type'");
            if (!$db->fetch()) {
                $db->query("ALTER TABLE students ADD COLUMN admission_type ENUM('regular','lateral','management','scholarship','other') DEFAULT 'regular' AFTER admission_date");
            }
            $db->query("SHOW COLUMNS FROM students LIKE 'roll_number'");
            if (!$db->fetch()) {
                $db->query("ALTER TABLE students ADD COLUMN roll_number VARCHAR(50) DEFAULT NULL AFTER student_id_number");
            }
        } catch (\Exception $e) {}

        $filters = [
            'search'        => $this->input('search'),
            'status'        => $this->input('status'),
            'course_id'     => $this->input('course_id'),
            'batch_id'      => $this->input('batch_id'),
            'department_id' => $this->input('department_id'),
            'gender'        => $this->input('gender'),
            'admission_year'=> $this->input('admission_year'),
        ];

        $page = (int)($this->input('page') ?: 1);
        $students = $this->student->getListPaginated($page, config('app.per_page', 15), $filters);
        $stats = $this->student->getStats();

        $db = db();
        $db->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name");
        $departments = $db->fetchAll();

        $this->view('students/index', compact('students', 'filters', 'stats', 'courses', 'departments'));
    }

    public function create(): void
    {
        $this->authorize('students.create');

        $db = db();
        $db->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name");
        $departments = $db->fetchAll();

        $this->view('students/create', compact('courses', 'departments'));
    }

    public function store(): void
    {
        $this->authorize('students.create');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'first_name' => 'required',
            'phone'      => 'required|phone',
            'course_id'  => 'required',
        ]);

        if ($errors) { $this->backWithErrors($errors); return; }

        $institutionId = session('institution_id');

        $id = $this->student->create([
            'institution_id'          => $institutionId,
            'student_id_number'       => $this->student->generateStudentId($institutionId),
            'first_name'              => sanitize($data['first_name']),
            'last_name'               => sanitize($data['last_name'] ?? ''),
            'email'                   => sanitize($data['email'] ?? ''),
            'phone'                   => sanitize($data['phone']),
            'date_of_birth'           => $data['date_of_birth'] ?: null,
            'gender'                  => $data['gender'] ?? null,
            'category'                => $data['category'] ?? null,
            'father_name'             => sanitize($data['father_name'] ?? ''),
            'father_phone'            => sanitize($data['father_phone'] ?? ''),
            'mother_name'             => sanitize($data['mother_name'] ?? ''),
            'guardian_name'           => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'          => sanitize($data['guardian_phone'] ?? ''),
            'address_line1'           => sanitize($data['address_line1'] ?? ''),
            'city'                    => sanitize($data['city'] ?? ''),
            'state'                   => sanitize($data['state'] ?? ''),
            'pincode'                 => sanitize($data['pincode'] ?? ''),
            'previous_qualification'  => sanitize($data['previous_qualification'] ?? ''),
            'previous_percentage'     => $data['previous_percentage'] ?: null,
            'previous_institution'    => sanitize($data['previous_institution'] ?? ''),
            'course_id'               => $data['course_id'],
            'batch_id'                => $data['batch_id'] ?: null,
            'department_id'           => $data['department_id'] ?: null,
            'admission_date'          => $data['admission_date'] ?: date('Y-m-d'),
            'admission_type'          => $data['admission_type'] ?? 'regular',
            'status'                  => 'active',
            'created_by'              => auth()['id'],
        ]);

        $this->logAudit('student_created', 'student', $id);
        $this->redirectWith('students/' . $id, 'Student added successfully.', 'success');
    }

    public function show(int $id): void
    {
        $this->authorize('students.view');

        $student = $this->student->getProfile360($id);
        if (!$student) {
            $this->redirectWith('students', 'Student not found.', 'error');
            return;
        }

        $this->view('students/show', compact('student'));
    }

    public function edit(int $id): void
    {
        $this->authorize('students.edit');

        $student = $this->student->find($id);
        if (!$student) {
            $this->redirectWith('students', 'Student not found.', 'error');
            return;
        }

        $db = db();
        $db->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM batches WHERE course_id = ? AND deleted_at IS NULL ORDER BY name", [$student['course_id']]);
        $batches = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE deleted_at IS NULL ORDER BY name");
        $departments = $db->fetchAll();

        $this->view('students/edit', compact('student', 'courses', 'batches', 'departments'));
    }

    public function update(int $id): void
    {
        $this->authorize('students.edit');

        $student = $this->student->find($id);
        if (!$student) { $this->redirectWith('students', 'Student not found.', 'error'); return; }

        $data = $this->postData();
        $errors = $this->validate($data, ['first_name' => 'required', 'phone' => 'required|phone']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $this->student->update($id, [
            'first_name'              => sanitize($data['first_name']),
            'last_name'               => sanitize($data['last_name'] ?? ''),
            'email'                   => sanitize($data['email'] ?? ''),
            'phone'                   => sanitize($data['phone']),
            'date_of_birth'           => $data['date_of_birth'] ?: null,
            'gender'                  => $data['gender'] ?? null,
            'aadhar_number'           => sanitize($data['aadhar_number'] ?? ''),
            'category'                => $data['category'] ?? null,
            'address_line1'           => sanitize($data['address_line1'] ?? ''),
            'address_line2'           => sanitize($data['address_line2'] ?? ''),
            'city'                    => sanitize($data['city'] ?? ''),
            'state'                   => sanitize($data['state'] ?? ''),
            'pincode'                 => sanitize($data['pincode'] ?? ''),
            'father_name'             => sanitize($data['father_name'] ?? ''),
            'father_phone'            => sanitize($data['father_phone'] ?? ''),
            'mother_name'             => sanitize($data['mother_name'] ?? ''),
            'guardian_name'           => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'          => sanitize($data['guardian_phone'] ?? ''),
            'previous_qualification'  => sanitize($data['previous_qualification'] ?? ''),
            'previous_percentage'     => $data['previous_percentage'] ?: null,
            'previous_institution'    => sanitize($data['previous_institution'] ?? ''),
            'course_id'               => $data['course_id'] ?: null,
            'batch_id'                => $data['batch_id'] ?: null,
            'department_id'           => $data['department_id'] ?: null,
            'status'                  => $data['status'] ?? $student['status'],
        ]);

        if (isset($data['status']) && $data['status'] !== $student['status']) {
            $this->student->addActivity($id, 'status_change', "Status updated to {$data['status']}", auth()['id']);
        }

        $this->logAudit('student_updated', 'student', $id);
        $this->redirectWith('students/' . $id, 'Student updated.', 'success');
    }

    public function destroy(int $id): void
    {
        $this->authorize('students.delete');
        $this->student->delete($id);
        $this->logAudit('student_deleted', 'student', $id);
        $this->redirectWith('students', 'Student deleted.', 'success');
    }

    public function addNote(int $id): void
    {
        $this->authorize('students.edit');
        
        $note = sanitize($this->input('note'));
        $type = $this->input('type', 'note');

        if (empty($note)) {
            $this->error('Note content is required.');
            return;
        }

        $this->student->addActivity($id, $type, $note, $this->user['id']);
        
        $this->success('Note added successfully.');
    }

    public function export(): void
    {
        $this->authorize('students.export');

        $rows = $this->student->getExportData([
            'status'     => $this->input('status'),
            'course_id'  => $this->input('course_id'),
            'batch_id'   => $this->input('batch_id'),
            'search'     => $this->input('search'),
        ]);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=students_' . date('Ymd') . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Student ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Gender', 'Course', 'Batch', 'Department', 'Status', 'Admission Date']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['student_id_number'], $r['first_name'], $r['last_name'], $r['email'], $r['phone'], $r['gender'], $r['course_name'], $r['batch_name'], $r['department_name'], $r['status'], $r['admission_date']]);
        }
        fclose($out);
    }
}
