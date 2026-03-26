<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ExamController extends BaseController
{
    public function index(): void
    {
        $this->authorize('exams.view');

        // Self-healing migration
        try {
            $db = db();
            $db->query("SHOW COLUMNS FROM students LIKE 'roll_number'");
            if (!$db->fetch()) {
                $db->query("ALTER TABLE students ADD COLUMN roll_number VARCHAR(50) DEFAULT NULL AFTER student_id_number");
            }
        } catch (\Exception $e) {}

        $institutionId = session('institution_id');
        $academicYearId = session('academic_year_id');

        $where = "institution_id = ?";
        $params = [$institutionId];

        if ($academicYearId) {
            $where .= " AND academic_year_id = ?";
            $params[] = $academicYearId;
        }

        $page = (int)($this->input('page') ?: 1);
        $sql = "SELECT * FROM exams WHERE {$where} ORDER BY start_date DESC";
        $exams = db()->paginate($sql, $params, $page, config('app.per_page', 15));

        $this->view('exams/index', compact('exams'));
    }

    public function create(): void
    {
        $this->authorize('exams.manage');
        $this->view('exams/create');
    }

    public function store(): void
    {
        $this->authorize('exams.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'name' => 'required',
            'type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $institutionId = session('institution_id');
        $academicYearId = session('academic_year_id');

        if (!$academicYearId) {
            $this->backWithErrors(['error' => 'No active academic year found.']);
            return;
        }

        $id = db()->insert('exams', [
            'institution_id'   => $institutionId,
            'academic_year_id' => $academicYearId,
            'name'             => sanitize($data['name']),
            'type'             => $data['type'],
            'start_date'       => $data['start_date'],
            'end_date'         => $data['end_date'],
            'status'           => $data['status'] ?? 'upcoming'
        ]);

        $this->logAudit('exam_created', 'exam', $id);
        $this->redirectWith('exams', 'Exam created successfully.', 'success');
    }

    public function show(int $id): void
    {
        $this->authorize('exams.view');

        $exam = db()->query("SELECT * FROM exams WHERE id = ? AND institution_id = ?", [$id, session('institution_id')])->fetch();
        if (!$exam) {
            $this->redirectWith('exams', 'Exam not found.', 'error');
            return;
        }

        // Get schedules
        $sql = "SELECT es.*, s.name as subject_name, s.code as subject_code 
                FROM exam_schedules es
                JOIN subjects s ON s.id = es.subject_id
                WHERE es.exam_id = ?
                ORDER BY es.date ASC, es.start_time ASC";
        $schedules = db()->query($sql, [$id])->fetchAll();

        // Get subjects for new schedule form
        $subjects = db()->query("SELECT id, name, code FROM subjects WHERE institution_id = ? AND status = 'active' ORDER BY name", [session('institution_id')])->fetchAll();

        $this->view('exams/show', compact('exam', 'schedules', 'subjects'));
    }

    public function addSchedule(int $examId): void
    {
        $this->authorize('exams.manage');

        $exam = db()->query("SELECT * FROM exams WHERE id = ? AND institution_id = ?", [$examId, session('institution_id')])->fetch();
        if (!$exam) {
            $this->backWithErrors(['error' => 'Exam not found.']);
            return;
        }

        $data = $this->postData();
        $errors = $this->validate($data, [
            'subject_id' => 'required|numeric',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'max_marks' => 'required|numeric',
            'min_marks' => 'required|numeric'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $id = db()->insert('exam_schedules', [
            'exam_id'     => $examId,
            'subject_id'  => $data['subject_id'],
            'date'        => $data['date'],
            'start_time'  => $data['start_time'],
            'end_time'    => $data['end_time'],
            'max_marks'   => $data['max_marks'],
            'min_marks'   => $data['min_marks'],
            'room_number' => sanitize($data['room_number'] ?? '')
        ]);

        $this->logAudit('exam_schedule_added', 'exam', $examId, ['schedule_id' => $id]);
        $this->redirectWith("exams/{$examId}", 'Schedule added.', 'success');
    }

    public function deleteSchedule(int $examId, int $scheduleId): void
    {
        $this->authorize('exams.manage');
        
        $schedule = db()->query("SELECT id FROM exam_schedules WHERE id = ? AND exam_id = ?", [$scheduleId, $examId])->fetch();
        if ($schedule) {
            db()->query("DELETE FROM exam_schedules WHERE id = ?", [$scheduleId]);
            $this->logAudit('exam_schedule_deleted', 'exam', $examId, ['schedule_id' => $scheduleId]);
            $this->backWithSuccess('Schedule deleted.');
        } else {
            $this->backWithErrors(['error' => 'Schedule not found.']);
        }
    }

    public function marks(int $examId, int $scheduleId): void
    {
        $this->authorize('exams.enter_marks');

        $exam = db()->query("SELECT * FROM exams WHERE id = ? AND institution_id = ?", [$examId, session('institution_id')])->fetch();
        if (!$exam) {
            $this->redirectWith('exams', 'Exam not found.', 'error');
            return;
        }

        $schedule = db()->query("
            SELECT es.*, s.name as subject_name, s.code as subject_code 
            FROM exam_schedules es
            JOIN subjects s ON s.id = es.subject_id
            WHERE es.id = ? AND es.exam_id = ?
        ", [$scheduleId, $examId])->fetch();

        if (!$schedule) {
            $this->redirectWith("exams/{$examId}", 'Schedule not found.', 'error');
            return;
        }

        // Get batches
        $courseId = $this->input('course_id');
        $batchId = $this->input('batch_id');

        $courses = db()->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [session('institution_id')])->fetchAll();
        $batches = [];
        if ($courseId) {
            $batches = db()->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' ORDER BY name", [$courseId])->fetchAll();
        }

        $students = [];
        $marks = [];
        if ($batchId) {
            $students = db()->query("
                SELECT id, student_id_number, first_name, last_name, roll_number 
                FROM students 
                WHERE batch_id = ? AND status = 'active' AND deleted_at IS NULL
                ORDER BY first_name, last_name
            ", [$batchId])->fetchAll();

            $marksRecords = db()->query("SELECT * FROM exam_marks WHERE exam_schedule_id = ?", [$scheduleId])->fetchAll();
            foreach ($marksRecords as $m) {
                $marks[$m['student_id']] = $m;
            }
        }

        $this->view('exams/marks', compact('exam', 'schedule', 'courses', 'batches', 'courseId', 'batchId', 'students', 'marks'));
    }

    public function storeMarks(int $examId, int $scheduleId): void
    {
        $this->authorize('exams.enter_marks');
        $data = $this->postData();

        $batchId = $data['batch_id'] ?? null;
        $marksData = $data['marks'] ?? [];
        $isAbsentData = $data['is_absent'] ?? [];
        $remarksData = $data['remarks'] ?? [];

        if (!$batchId || empty($marksData)) {
            $this->backWithErrors(['error' => 'Invalid data.']);
            return;
        }

        // For simplicity, handle per batch
        foreach ($marksData as $studentId => $mark) {
            // Check if record exists
            $existing = db()->query("SELECT id FROM exam_marks WHERE exam_schedule_id = ? AND student_id = ?", [$scheduleId, $studentId])->fetch();

            $isAbsent = isset($isAbsentData[$studentId]) ? 1 : 0;
            $markValue = $isAbsent ? null : (is_numeric($mark) ? (float)$mark : null);
            $remarks = sanitize($remarksData[$studentId] ?? '');

            if ($existing) {
                db()->update('exam_marks', [
                    'marks_obtained' => $markValue,
                    'is_absent'      => $isAbsent,
                    'remarks'        => $remarks
                ], '`id` = ?', [$existing['id']]);
            } else {
                db()->insert('exam_marks', [
                    'exam_schedule_id' => $scheduleId,
                    'student_id'       => $studentId,
                    'marks_obtained'   => $markValue,
                    'is_absent'        => $isAbsent,
                    'remarks'          => $remarks
                ]);
            }

            // Sync with student timeline
            try {
                if ($isAbsent) {
                    (new \App\Models\Student())->addActivity($studentId, 'grade', 'Absent for exam schedule ID: ' . $scheduleId, auth()['id']);
                } else if ($markValue !== null) {
                    (new \App\Models\Student())->addActivity($studentId, 'grade', 'Marks updated: ' . $markValue, auth()['id'], ['exam_schedule_id' => $scheduleId]);
                }
            } catch (\Exception $e) {}
        }

        $this->logAudit('exam_marks_entered', 'exam', $examId, ['schedule_id' => $scheduleId, 'batch_id' => $batchId]);
        $this->backWithSuccess('Marks saved successfully.');
    }
}
