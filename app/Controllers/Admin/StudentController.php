<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Student;

class StudentController extends BaseController
{
    private Student $student;

    public function __construct()
    {
        parent::__construct();
        $this->student = new Student();
    }

    public function index(): void
    {
        $this->authorize('students.view');

        $filters = [
            'search'        => $this->input('search'),
            'status'        => $this->input('status'),
            'course_id'     => $this->input('course_id'),
            'batch_id'      => $this->input('batch_id'),
            'department_id' => $this->input('department_id'),
            'gender'        => $this->input('gender'),
            'admission_year'=> $this->input('admission_year'),
        ];

        $page     = max(1, (int)($this->input('page') ?: 1));
        $students = $this->student->getListPaginated($page, config('app.per_page', 15), $filters);
        $stats    = $this->student->getStats();

        $db = $this->db;
        $db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $db->fetchAll();
        $db->query("SELECT id, name FROM batches WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $batches = $db->fetchAll();

        $this->view('students/index', compact('students', 'filters', 'stats', 'courses', 'departments', 'batches'));
    }

    public function create(): void
    {
        $this->authorize('students.create');

        $db = $this->db;
        $db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $db->fetchAll();
        $db->query("SELECT id, name FROM batches WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $batches = $db->fetchAll();
        $db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY is_current DESC, start_date DESC LIMIT 5", [$this->institutionId]);
        $academicYears = $db->fetchAll();

        $this->view('students/create', compact('courses', 'departments', 'batches', 'academicYears'));
    }

    public function store(): void
    {
        $this->authorize('students.create');

        if (!verifyCsrf()) { $this->backWithErrors(['Session expired. Please try again.']); return; }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'gender'     => 'required',
            'course_id'  => 'required|numeric',
        ]);

        if ($errors) { $this->backWithErrors(array_values($errors), $data); return; }

        $institutionId = $this->institutionId;
        // Get organization_id from the institution record
        $this->db->query("SELECT organization_id FROM institutions WHERE id = ? LIMIT 1", [$institutionId]);
        $instRow = $this->db->fetch();
        $orgId   = $instRow['organization_id'] ?? 1;

        try {
            $id = $this->student->withoutScope()->create([
                'organization_id'        => $orgId,
                'institution_id'         => $institutionId,
                'student_id_number'      => $this->student->generateStudentId($institutionId),
                'admission_number'       => $this->student->generateAdmissionNumber($institutionId),
                'first_name'             => sanitize($data['first_name']),
                'middle_name'            => sanitize($data['middle_name'] ?? ''),
                'last_name'              => sanitize($data['last_name'] ?? ''),
                'email'                  => sanitize($data['email'] ?? ''),
                'phone'                  => sanitize($data['phone'] ?? ''),
                'mobile_number'          => sanitize($data['mobile_number'] ?? $data['phone'] ?? ''),
                'date_of_birth'          => $data['date_of_birth'] ?: null,
                'gender'                 => $data['gender'],
                'blood_group'            => $data['blood_group'] ?: null,
                'category'               => $data['category'] ?: null,
                'religion'               => sanitize($data['religion'] ?? ''),
                'nationality'            => sanitize($data['nationality'] ?? 'Indian'),
                'aadhar_number'          => sanitize($data['aadhar_number'] ?? ''),
                'address_line1'          => sanitize($data['address_line1'] ?? ''),
                'address_line2'          => sanitize($data['address_line2'] ?? ''),
                'city'                   => sanitize($data['city'] ?? ''),
                'state'                  => sanitize($data['state'] ?? ''),
                'pincode'                => sanitize($data['pincode'] ?? ''),
                'father_name'            => sanitize($data['father_name'] ?? ''),
                'father_phone'           => sanitize($data['father_phone'] ?? ''),
                'father_occupation'      => sanitize($data['father_occupation'] ?? ''),
                'mother_name'            => sanitize($data['mother_name'] ?? ''),
                'mother_phone'           => sanitize($data['mother_phone'] ?? ''),
                'guardian_name'          => sanitize($data['guardian_name'] ?? ''),
                'guardian_phone'         => sanitize($data['guardian_phone'] ?? ''),
                'annual_income'          => $data['annual_income'] ?: null,
                'previous_qualification' => sanitize($data['previous_qualification'] ?? ''),
                'previous_percentage'    => $data['previous_percentage'] ?: null,
                'previous_institution'   => sanitize($data['previous_institution'] ?? ''),
                'course_id'              => (int)$data['course_id'],
                'batch_id'               => $data['batch_id'] ? (int)$data['batch_id'] : null,
                'department_id'          => $data['department_id'] ? (int)$data['department_id'] : null,
                'academic_year_id'       => $data['academic_year_id'] ? (int)$data['academic_year_id'] : null,
                'admission_date'         => $data['admission_date'] ?: date('Y-m-d'),
                'admission_type'         => $data['admission_type'] ?? 'regular',
                'student_type'           => $data['student_type'] ?? 'day_scholar',
                'status'                 => 'active',
                'notes'                  => sanitize($data['notes'] ?? ''),
                'created_by'             => $this->user['id'] ?? null,
            ]);

            $this->logAudit('create', 'student', $id);
            $this->redirectWith(url('students/' . $id), 'success', 'Student added successfully.');
        } catch (\Exception $e) {
            appLog('Student create failed: ' . $e->getMessage(), 'error');
            $this->backWithErrors(['Failed to create student: ' . $e->getMessage()], $data);
        }
    }

    public function show(int $id): void
    {
        $this->authorize('students.view');

        $student = $this->student->getProfile360($id);
        if (!$student) {
            $this->redirectWith(url('students'), 'error', 'Student not found.');
            return;
        }

        // Load available sections for the student's batch (for section allotment)
        $sections = [];
        if (!empty($student['batch_id'])) {
            $sections = $this->db->query(
                "SELECT id, name, code, capacity FROM sections
                 WHERE batch_id = ? AND status = 'active' AND deleted_at IS NULL
                 ORDER BY name",
                [(int)$student['batch_id']]
            )->fetchAll();
        }

        $this->view('students/show', compact('student', 'sections'));
    }

    public function edit(int $id): void
    {
        $this->authorize('students.edit');

        $student = $this->student->find($id);
        if (!$student) {
            $this->redirectWith(url('students'), 'error', 'Student not found.');
            return;
        }

        $db = $this->db;
        $db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $db->fetchAll();
        $db->query("SELECT id, name FROM batches WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $batches = $db->fetchAll();
        $db->query("SELECT id, name FROM departments WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $departments = $db->fetchAll();
        $db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY is_current DESC, start_date DESC LIMIT 5", [$this->institutionId]);
        $academicYears = $db->fetchAll();

        $this->view('students/edit', compact('student', 'courses', 'batches', 'departments', 'academicYears'));
    }

    public function update(int $id): void
    {
        $this->authorize('students.edit');

        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }

        $student = $this->student->find($id);
        if (!$student) { $this->redirectWith(url('students'), 'error', 'Student not found.'); return; }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'first_name' => 'required|max:100',
            'gender'     => 'required',
        ]);
        if ($errors) { $this->backWithErrors(array_values($errors), $data); return; }

        $this->student->update($id, [
            'first_name'             => sanitize($data['first_name']),
            'middle_name'            => sanitize($data['middle_name'] ?? ''),
            'last_name'              => sanitize($data['last_name'] ?? ''),
            'email'                  => sanitize($data['email'] ?? ''),
            'phone'                  => sanitize($data['phone'] ?? ''),
            'mobile_number'          => sanitize($data['mobile_number'] ?? $data['phone'] ?? ''),
            'date_of_birth'          => $data['date_of_birth'] ?: null,
            'gender'                 => $data['gender'],
            'blood_group'            => $data['blood_group'] ?: null,
            'category'               => $data['category'] ?: null,
            'religion'               => sanitize($data['religion'] ?? ''),
            'aadhar_number'          => sanitize($data['aadhar_number'] ?? ''),
            'address_line1'          => sanitize($data['address_line1'] ?? ''),
            'address_line2'          => sanitize($data['address_line2'] ?? ''),
            'city'                   => sanitize($data['city'] ?? ''),
            'state'                  => sanitize($data['state'] ?? ''),
            'pincode'                => sanitize($data['pincode'] ?? ''),
            'father_name'            => sanitize($data['father_name'] ?? ''),
            'father_phone'           => sanitize($data['father_phone'] ?? ''),
            'father_occupation'      => sanitize($data['father_occupation'] ?? ''),
            'mother_name'            => sanitize($data['mother_name'] ?? ''),
            'mother_phone'           => sanitize($data['mother_phone'] ?? ''),
            'guardian_name'          => sanitize($data['guardian_name'] ?? ''),
            'guardian_phone'         => sanitize($data['guardian_phone'] ?? ''),
            'annual_income'          => $data['annual_income'] ?: null,
            'previous_qualification' => sanitize($data['previous_qualification'] ?? ''),
            'previous_percentage'    => $data['previous_percentage'] ?: null,
            'previous_institution'   => sanitize($data['previous_institution'] ?? ''),
            'course_id'              => $data['course_id'] ? (int)$data['course_id'] : null,
            'batch_id'               => $data['batch_id'] ? (int)$data['batch_id'] : null,
            'department_id'          => $data['department_id'] ? (int)$data['department_id'] : null,
            'academic_year_id'       => $data['academic_year_id'] ? (int)$data['academic_year_id'] : null,
            'admission_type'         => $data['admission_type'] ?? $student['admission_type'],
            'student_type'           => $data['student_type'] ?? $student['student_type'],
            'status'                 => $data['status'] ?? $student['status'],
            'notes'                  => sanitize($data['notes'] ?? ''),
        ]);

        $this->logAudit('update', 'student', $id);
        $this->redirectWith(url('students/' . $id), 'success', 'Student updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('students.delete');

        if (!verifyCsrf()) { $this->redirectWith(url('students'), 'error', 'Session expired.'); return; }

        $this->student->delete($id);
        $this->logAudit('delete', 'student', $id);
        $this->redirectWith(url('students'), 'success', 'Student deleted.');
    }

    public function addNote(int $id): void
    {
        $this->authorize('students.edit');

        if (!verifyCsrf()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Session expired.']);
            return;
        }

        $note = sanitize($this->input('note') ?? '');
        $type = $this->input('type') ?: 'note';

        if (empty($note)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Note content is required.']);
            return;
        }

        $this->student->addActivity($id, $type, $note, $this->user['id'] ?? null);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Note added successfully.']);
    }

    public function assignSection(int $id): void
    {
        $this->authorize('students.edit');

        if (!verifyCsrf()) {
            $this->redirectWith(url('students/' . $id), 'error', 'Session expired.');
            return;
        }

        $sectionId = (int)($this->postData()['section_id'] ?? 0);

        // Verify section belongs to same institution
        $this->db->query(
            "SELECT id, batch_id, capacity FROM sections WHERE id = ? AND institution_id = ? AND deleted_at IS NULL",
            [$sectionId, $this->institutionId]
        );
        $section = $this->db->fetch();

        if (!$section) {
            $this->redirectWith(url('students/' . $id), 'error', 'Section not found.');
            return;
        }

        $this->db->query("UPDATE students SET section_id = ?, updated_at = NOW() WHERE id = ?", [$sectionId, $id]);
        $this->logAudit('section_assigned', 'student', $id, ['section_id' => $sectionId]);
        $this->redirectWith(url('students/' . $id), 'success', 'Section assigned successfully.');
    }

    public function export(): void
    {
        $this->authorize('students.view');

        $rows = $this->student->getExportData([
            'status'    => $this->input('status'),
            'course_id' => $this->input('course_id'),
            'batch_id'  => $this->input('batch_id'),
            'search'    => $this->input('search'),
        ]);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=students_' . date('Ymd_His') . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Student ID', 'Admission No', 'First Name', 'Last Name', 'Email', 'Phone', 'Gender', 'DOB', 'Course', 'Batch', 'Department', 'Status', 'Admission Date']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['student_id_number'], $r['admission_number'] ?? '',
                $r['first_name'], $r['last_name'] ?? '',
                $r['email'] ?? '', $r['phone'] ?? '',
                $r['gender'], $r['date_of_birth'] ?? '',
                $r['course_name'] ?? '', $r['batch_name'] ?? '',
                $r['department_name'] ?? '', $r['status'],
                $r['admission_date'] ?? '',
            ]);
        }
        fclose($out);
    }
}
