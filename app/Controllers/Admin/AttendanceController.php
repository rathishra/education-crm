<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AttendanceController extends BaseController
{
    public function index(): void
    {
        $this->authorize('attendance.view');

        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM subjects WHERE institution_id = ? AND status = 'active' ORDER BY name", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $courseId = $this->input('course_id');
        $batches  = [];
        $sections = [];
        if ($courseId) {
            $this->db->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name", [$courseId]);
            $batches = $this->db->fetchAll();
        }

        $batchId = $this->input('batch_id');
        if ($batchId) {
            $this->db->query("SELECT id, name FROM sections WHERE batch_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name", [$batchId]);
            $sections = $this->db->fetchAll();
        }

        $date      = $this->input('date', date('Y-m-d'));
        $subjectId = $this->input('subject_id');
        $sectionId = $this->input('section_id');

        $students          = [];
        $existingAttendance = [];

        if ($batchId && $date) {
            $stuWhere  = "batch_id = ? AND status = 'active' AND deleted_at IS NULL";
            $stuParams = [$batchId];
            if ($sectionId) {
                $stuWhere  .= " AND section_id = ?";
                $stuParams[] = $sectionId;
            }
            $students = $this->db->query(
                "SELECT id, student_id_number, first_name, last_name, roll_number
                 FROM students WHERE {$stuWhere}
                 ORDER BY first_name, last_name",
                $stuParams
            )->fetchAll();

            // Load existing attendance for this batch/date/subject
            $attParams = [$batchId, $date];
            if ($subjectId) {
                $attWhere = "AND subject_id = ?";
                $attParams[] = $subjectId;
            } else {
                $attWhere = "AND subject_id IS NULL";
            }
            $attRecords = $this->db->query(
                "SELECT student_id, status, remarks FROM attendances
                 WHERE batch_id = ? AND date = ? {$attWhere}",
                $attParams
            )->fetchAll();

            foreach ($attRecords as $att) {
                $existingAttendance[$att['student_id']] = $att;
            }
        }

        $this->view('attendance/index', compact(
            'courses', 'batches', 'sections', 'subjects', 'students', 'existingAttendance',
            'courseId', 'batchId', 'sectionId', 'date', 'subjectId'
        ));
    }

    public function store(): void
    {
        $this->authorize('attendance.mark');

        $data = $this->postData();

        $batchId        = (int)($data['batch_id'] ?? 0);
        $date           = $data['date'] ?? date('Y-m-d');
        $subjectId      = !empty($data['subject_id']) ? (int)$data['subject_id'] : null;
        $sectionId      = !empty($data['section_id']) ? (int)$data['section_id'] : null;
        $attendanceData = $data['attendance'] ?? [];
        $remarksData    = $data['remarks'] ?? [];

        if (!$batchId || empty($attendanceData)) {
            $this->backWithErrors(['Batch and attendance data are required.']);
            return;
        }

        // Get current academic year for this institution
        $this->db->query(
            "SELECT id FROM academic_years WHERE institution_id = ? AND is_current = 1 LIMIT 1",
            [$this->institutionId]
        );
        $ay = $this->db->fetch();
        $academicYearId = $ay['id'] ?? null;

        if (!$academicYearId) {
            // Fallback: get most recent
            $this->db->query(
                "SELECT id FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC LIMIT 1",
                [$this->institutionId]
            );
            $ay = $this->db->fetch();
            $academicYearId = $ay['id'] ?? null;
        }

        if (!$academicYearId) {
            $this->backWithErrors(['No academic year found. Please create an academic year first.']);
            return;
        }

        // Delete existing attendance for this batch/date/subject to replace
        $delParams = [$batchId, $date];
        if ($subjectId) {
            $subjectClause = "AND subject_id = ?";
            $delParams[]   = $subjectId;
        } else {
            $subjectClause = "AND subject_id IS NULL";
        }
        $this->db->query(
            "DELETE FROM attendances WHERE batch_id = ? AND date = ? {$subjectClause}",
            $delParams
        );

        $studentModel = new \App\Models\Student();
        $insertCount  = 0;

        foreach ($attendanceData as $studentId => $status) {
            $remarks = sanitize($remarksData[$studentId] ?? '');

            $this->db->insert('attendances', [
                'institution_id'   => $this->institutionId,
                'academic_year_id' => $academicYearId,
                'student_id'       => (int)$studentId,
                'batch_id'         => $batchId,
                'section_id'       => $sectionId,
                'subject_id'       => $subjectId,
                'date'             => $date,
                'status'           => $status,
                'remarks'          => $remarks,
                'marked_by'        => $this->user['id'],
            ]);

            if ($status === 'absent') {
                $title = $subjectId
                    ? "Absent for Subject on {$date}"
                    : "Absent on {$date}";
                $studentModel->addActivity((int)$studentId, 'attendance', $title, $this->user['id']);
            }
            $insertCount++;
        }

        $this->logAudit('attendance_marked', 'attendance', $batchId, [
            'date'       => $date,
            'subject_id' => $subjectId,
            'section_id' => $sectionId,
            'count'      => $insertCount,
        ]);

        $redirect = 'attendance?course_id=' . ($data['course_id'] ?? '') .
                    '&batch_id=' . $batchId .
                    '&date=' . $date .
                    ($subjectId ? '&subject_id=' . $subjectId : '') .
                    ($sectionId ? '&section_id=' . $sectionId : '');

        $this->redirectWith(url($redirect), 'success', 'Attendance saved for ' . $insertCount . ' students.');
    }

    public function report(): void
    {
        $this->authorize('attendance.reports');

        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $courseId = $this->input('course_id');
        $batches  = [];
        if ($courseId) {
            $this->db->query("SELECT id, name FROM batches WHERE course_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY name", [$courseId]);
            $batches = $this->db->fetchAll();
        }

        $batchId   = $this->input('batch_id');
        $month     = $this->input('month', date('Y-m'));
        $report    = [];
        $students  = [];
        $totalDays = 0;

        if ($batchId && $month) {
            $dateFrom = $month . '-01';
            $dateTo   = date('Y-m-t', strtotime($dateFrom));

            $students = $this->db->query(
                "SELECT id, student_id_number, first_name, last_name, roll_number
                 FROM students WHERE batch_id = ? AND status = 'active' AND deleted_at IS NULL
                 ORDER BY first_name, last_name",
                [$batchId]
            )->fetchAll();

            // Count working days in the month for this batch
            $this->db->query(
                "SELECT COUNT(DISTINCT date) as days FROM attendances
                 WHERE batch_id = ? AND date BETWEEN ? AND ? AND subject_id IS NULL",
                [$batchId, $dateFrom, $dateTo]
            );
            $totalDays = (int)($this->db->fetch()['days'] ?? 0);

            // Get attendance summary per student
            if (!empty($students)) {
                $studentIds = implode(',', array_column($students, 'id'));
                $attRows    = $this->db->query(
                    "SELECT student_id, status, COUNT(*) as cnt
                     FROM attendances
                     WHERE batch_id = ? AND date BETWEEN ? AND ? AND subject_id IS NULL
                     GROUP BY student_id, status",
                    [$batchId, $dateFrom, $dateTo]
                )->fetchAll();

                foreach ($students as $stu) {
                    $report[$stu['id']] = [
                        'present'  => 0,
                        'absent'   => 0,
                        'late'     => 0,
                        'half_day' => 0,
                    ];
                }
                foreach ($attRows as $row) {
                    if (isset($report[$row['student_id']][$row['status']])) {
                        $report[$row['student_id']][$row['status']] = (int)$row['cnt'];
                    }
                }
            }
        }

        $this->view('attendance/report', compact(
            'courses', 'batches', 'courseId', 'batchId', 'month', 'students', 'report', 'totalDays'
        ));
    }
}
