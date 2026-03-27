<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class AttendanceController extends BaseController {
    
    public function index(): void {
        // Fetch sections for the dropdown
        $sql = "SELECT s.id as section_id, s.section_name, b.program_name, b.batch_term 
                FROM academic_sections s 
                JOIN academic_batches b ON s.batch_id = b.id 
                WHERE s.institution_id = ? AND s.status = 'active'
                ORDER BY b.program_name ASC, s.section_name ASC";
        $this->db->query($sql, [$this->institutionId]);
        $sections = $this->db->fetchAll();

        // Fetch subjects
        $this->db->query("SELECT id, subject_code, subject_name FROM subjects WHERE institution_id = ? AND status = 'active'", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->view('academic/attendance/index', compact('sections', 'subjects'));
    }

    public function mark(): void {
        $section_id = (int)($_GET['section_id'] ?? 0);
        $subject_id = (int)($_GET['subject_id'] ?? 0);
        $date       = trim($_GET['date'] ?? date('Y-m-d'));

        if ($section_id <= 0 || $subject_id <= 0) {
            header("Location: " . url('academic/attendance'));
            exit;
        }

        // Validate Section
        $this->db->query("SELECT s.*, b.program_name, b.batch_term FROM academic_sections s JOIN academic_batches b ON s.batch_id = b.id WHERE s.id = ? AND s.institution_id = ?", [$section_id, $this->institutionId]);
        $section = $this->db->fetch();
        if (!$section) die("Invalid Section");

        // Validate Subject
        $this->db->query("SELECT * FROM subjects WHERE id = ? AND institution_id = ?", [$subject_id, $this->institutionId]);
        $subject = $this->db->fetch();
        if (!$subject) die("Invalid Subject");

        // Check if session already exists for this date/subject/section
        $this->db->query("SELECT id, topic_covered, status FROM academic_attendance_sessions WHERE section_id = ? AND subject_id = ? AND attendance_date = ? AND institution_id = ?", [$section_id, $subject_id, $date, $this->institutionId]);
        $session = $this->db->fetch();

        // Get matching students (assuming standard students table)
        // Note: For full enterprise integration, ensure students have an academic_section_id column. 
        // We will grab active students and pretend they belong here for demonstration if no linkage exists.
        $this->db->query("SELECT id, first_name, last_name, roll_number FROM students WHERE institution_id = ? AND status = 'active' ORDER BY roll_number ASC LIMIT 50", [$this->institutionId]);
        $students = $this->db->fetchAll();

        // Get past attendance records if session exists
        $records = [];
        if ($session) {
            $this->db->query("SELECT student_id, attendance_status, remarks FROM academic_attendance_records WHERE session_id = ?", [$session['id']]);
            foreach($this->db->fetchAll() as $r) {
                $records[$r['student_id']] = $r;
            }
        }

        $this->view('academic/attendance/mark', compact('section', 'subject', 'date', 'students', 'session', 'records'));
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $section_id = (int)($_POST['section_id'] ?? 0);
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $batch_id   = (int)($_POST['batch_id'] ?? 0);
        $date       = trim($_POST['attendance_date'] ?? '');
        $topic      = trim($_POST['topic_covered'] ?? '');
        $status     = trim($_POST['session_status'] ?? 'draft'); // 'draft' or 'submitted'
        
        $attendance = $_POST['attendance'] ?? []; // array of context [student_id => status]
        $remarks    = $_POST['remarks'] ?? []; // array [student_id => remark]

        if ($section_id <= 0 || $subject_id <= 0 || empty($date)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Missing critical parameters']);
            exit;
        }

        try {
            $this->db->beginTransaction();

            // Check if session already exists
            $this->db->query("SELECT id FROM academic_attendance_sessions WHERE section_id = ? AND subject_id = ? AND attendance_date = ? AND institution_id = ?", [$section_id, $subject_id, $date, $this->institutionId]);
            $existing = $this->db->fetch();

            if ($existing) {
                $session_id = $existing['id'];
                $this->db->query("UPDATE academic_attendance_sessions SET topic_covered = ?, status = ? WHERE id = ?", [$topic, $status, $session_id]);
                // Delete existing records to overwrite
                $this->db->query("DELETE FROM academic_attendance_records WHERE session_id = ?", [$session_id]);
            } else {
                $this->db->insert('academic_attendance_sessions', [
                    'institution_id' => $this->institutionId,
                    'batch_id'       => $batch_id,
                    'section_id'     => $section_id,
                    'subject_id'     => $subject_id,
                    'faculty_id'     => $_SESSION['user_id'] ?? 1,
                    'attendance_date'=> $date,
                    'session_type'   => 'lecture',
                    'topic_covered'  => $topic,
                    'status'         => $status,
                    'created_by'     => $_SESSION['user_id'] ?? 1
                ]);
                $session_id = $this->db->lastInsertId();
            }

            // Insert records
            if (!empty($attendance) && is_array($attendance)) {
                foreach ($attendance as $student_id => $att_status) {
                    $this->db->insert('academic_attendance_records', [
                        'session_id'       => $session_id,
                        'student_id'       => $student_id,
                        'institution_id'   => $this->institutionId,
                        'attendance_status'=> $att_status,
                        'remarks'          => $remarks[$student_id] ?? null
                    ]);
                }
            }

            $this->db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Attendance saved successfully as ' . strtoupper($status)]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save attendance: ' . $e->getMessage()]);
        }
        exit;
    }
}
