<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AttendanceController extends BaseController
{
    public function index(): void
    {
        $this->authorize('attendance.view');

        $institutionId = session('institution_id');
        
        $courses = [];
        $batches = [];
        $subjects = [];
        
        if ($institutionId) {
            $courses = db()->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$institutionId])->fetchAll();
            $subjects = db()->query("SELECT id, name, code FROM subjects WHERE institution_id = ? AND status = 'active' ORDER BY name", [$institutionId])->fetchAll();
        }

        $courseId = $this->input('course_id');
        if ($courseId) {
            $batches = db()->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' ORDER BY name", [$courseId])->fetchAll();
        }

        $batchId = $this->input('batch_id');
        $date = $this->input('date', date('Y-m-d'));
        $subjectId = $this->input('subject_id');

        $students = [];
        $existingAttendance = [];

        if ($batchId && $date) {
            // Get students in the batch
            $students = db()->query(
                "SELECT id, student_id_number, first_name, last_name, roll_number 
                 FROM students 
                 WHERE batch_id = ? AND status = 'active' AND deleted_at IS NULL 
                 ORDER BY first_name, last_name", 
                [$batchId]
            )->fetchAll();

            // Get existing attendance
            $subjectClause = $subjectId ? "AND subject_id = " . (int)$subjectId : "AND subject_id IS NULL";
            $attRecords = db()->query(
                "SELECT student_id, status, remarks FROM attendances 
                 WHERE batch_id = ? AND date = ? {$subjectClause}",
                [$batchId, $date]
            )->fetchAll();

            foreach ($attRecords as $att) {
                $existingAttendance[$att['student_id']] = $att;
            }
        }

        $this->view('attendance/index', compact(
            'courses', 'batches', 'subjects', 'students', 'existingAttendance',
            'courseId', 'batchId', 'date', 'subjectId'
        ));
    }

    public function store(): void
    {
        $this->authorize('attendance.mark');

        $data = $this->postData();
        $institutionId = session('institution_id');
        $academicYearId = session('academic_year_id');

        if (!$institutionId) {
            $this->error('Please select an institution first.');
            return;
        }

        if (!$academicYearId) {
            // fallback generic error or try to find active academic year
            $this->error('No active academic year found for institution.');
            return;
        }

        $batchId = $data['batch_id'] ?? null;
        $date = $data['date'] ?? date('Y-m-d');
        $subjectId = !empty($data['subject_id']) ? $data['subject_id'] : null;
        $attendanceData = $data['attendance'] ?? [];
        $remarksData = $data['remarks'] ?? [];

        if (!$batchId || empty($attendanceData)) {
            $this->backWithErrors(['error' => 'Invalid data.']);
            return;
        }

        $subjectClause = $subjectId ? "AND subject_id = " . (int)$subjectId : "AND subject_id IS NULL";
        
        // Delete existing for this bath/date/subject to replace
        db()->query(
            "DELETE FROM attendances WHERE batch_id = ? AND date = ? {$subjectClause}",
            [$batchId, $date]
        );

        $insertCount = 0;
        foreach ($attendanceData as $studentId => $status) {
            $remarks = sanitize($remarksData[$studentId] ?? '');
            
            $id = db()->insert('attendances', [
                'institution_id'   => $institutionId,
                'academic_year_id' => $academicYearId,
                'student_id'       => $studentId,
                'batch_id'         => $batchId,
                'subject_id'       => $subjectId,
                'date'             => $date,
                'status'           => $status,
                'remarks'          => $remarks,
                'marked_by'        => auth()['id'],
            ]);

            // Add to student timeline if absent
            if ($status === 'absent') {
                $studentModel = new \App\Models\Student();
                $title = $subjectId ? "Absent for Subject on {$date}" : "Absent on {$date}";
                $studentModel->addActivity($studentId, 'attendance', $title, auth()['id']);
            }
            $insertCount++;
        }

        $this->logAudit('attendance_marked', 'attendance', $batchId, [
            'date' => $date, 'subject_id' => $subjectId, 'count' => $insertCount
        ]);

        $this->redirectWith('attendance?course_id='.$data['course_id'].'&batch_id='.$batchId.'&date='.$date.'&subject_id='.$subjectId, 'Attendance saved successfully.', 'success');
    }

    public function report(): void
    {
        $this->authorize('attendance.reports');

        $institutionId = session('institution_id');
        $courses = [];
        if ($institutionId) {
            $courses = db()->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$institutionId])->fetchAll();
        }

        $this->view('attendance/report', compact('courses'));
    }
}
