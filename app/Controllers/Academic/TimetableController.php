<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class TimetableController extends BaseController {
    
    public function index(): void {
        $sql = "SELECT s.id, s.section_name, b.program_name, b.batch_term 
                FROM academic_sections s 
                JOIN academic_batches b ON s.batch_id = b.id 
                WHERE s.institution_id = ? AND s.status = 'active'
                ORDER BY b.program_name ASC, s.section_name ASC";
        $this->db->query($sql, [$this->institutionId]);
        $sections = $this->db->fetchAll();
        
        $this->view('academic/timetable/index', compact('sections'));
    }

    public function generator(): void {
        $section_id = (int)($_GET['section_id'] ?? 0);
        if ($section_id <= 0) {
            header("Location: " . url('academic/timetable'));
            exit;
        }

        // Get Section Info
        $this->db->query("SELECT s.*, b.program_name, b.batch_term FROM academic_sections s JOIN academic_batches b ON s.batch_id = b.id WHERE s.id = ? AND s.institution_id = ?", [$section_id, $this->institutionId]);
        $section = $this->db->fetch();
        if (!$section) {
            die("Invalid Section");
        }

        // Ensure Periods exist
        $this->db->query("SELECT * FROM academic_timetable_periods WHERE institution_id = ? ORDER BY period_number ASC", [$this->institutionId]);
        $periods = $this->db->fetchAll();
        
        if (empty($periods)) {
            // Auto inject 8 common periods
            $defaultPeriods = [
                [1, 'Period 1', '09:00:00', '09:50:00', 0],
                [2, 'Period 2', '09:50:00', '10:40:00', 0],
                [3, 'Period 3', '10:40:00', '11:00:00', 1], // Break
                [4, 'Period 4', '11:00:00', '11:50:00', 0],
                [5, 'Period 5', '11:50:00', '12:40:00', 0],
                [6, 'Period 6', '12:40:00', '13:30:00', 1], // Lunch
                [7, 'Period 7', '13:30:00', '14:20:00', 0],
                [8, 'Period 8', '14:20:00', '15:10:00', 0]
            ];
            foreach ($defaultPeriods as $p) {
                $this->db->query("INSERT INTO academic_timetable_periods (institution_id, period_number, period_name, start_time, end_time, is_break) VALUES (?, ?, ?, ?, ?, ?)", 
                    [$this->institutionId, $p[0], $p[1], $p[2], $p[3], $p[4]]);
            }
            $this->db->query("SELECT * FROM academic_timetable_periods WHERE institution_id = ? ORDER BY period_number ASC", [$this->institutionId]);
            $periods = $this->db->fetchAll();
        }

        // Get Subjects
        $this->db->query("SELECT id, subject_name, subject_code FROM subjects WHERE institution_id = ? AND status = 'active'", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        // Get Faculty
        $this->db->query("SELECT id, first_name, last_name FROM users WHERE institution_id = ? AND role_id IN (SELECT id FROM roles WHERE role_name LIKE '%faculty%' OR role_name LIKE '%teacher%') AND is_active = 1", [$this->institutionId]);
        $faculty = $this->db->fetchAll();

        // Get Existing Timetable
        $this->db->query("SELECT * FROM academic_timetable WHERE section_id = ? AND institution_id = ?", [$section_id, $this->institutionId]);
        $ttData = $this->db->fetchAll();
        
        $timetable = [];
        foreach($ttData as $t) {
            $timetable[$t['day_of_week']][$t['period_id']] = [
                'subject_id' => $t['subject_id'],
                'faculty_id' => $t['faculty_id'],
                'entry_type' => $t['entry_type']
            ];
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        $this->view('academic/timetable/generator', compact('section', 'periods', 'subjects', 'faculty', 'timetable', 'days'));
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $section_id = (int)($_POST['section_id'] ?? 0);
        $batch_id   = (int)($_POST['batch_id'] ?? 0);
        
        if ($section_id <= 0 || $batch_id <= 0) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Missing Section Info']);
            exit;
        }

        $schedule = $_POST['schedule'] ?? [];

        try {
            $this->db->beginTransaction();
            
            // Delete old timetable for this section
            $this->db->query("DELETE FROM academic_timetable WHERE section_id = ? AND institution_id = ?", [$section_id, $this->institutionId]);
            
            // Insert new layout
            if(!empty($schedule) && is_array($schedule)) {
                foreach($schedule as $day => $periods) {
                    foreach($periods as $period_id => $data) {
                        if(!empty($data['subject_id']) && !empty($data['faculty_id'])) {
                            $this->db->insert('academic_timetable', [
                                'institution_id' => $this->institutionId,
                                'batch_id'       => $batch_id,
                                'section_id'     => $section_id,
                                'day_of_week'    => $day,
                                'period_id'      => $period_id,
                                'subject_id'     => $data['subject_id'],
                                'faculty_id'     => $data['faculty_id'],
                                'entry_type'     => $data['entry_type'] ?? 'lecture',
                                'created_by'     => $_SESSION['user_id'] ?? 1
                            ]);
                        }
                    }
                }
            }

            $this->db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Timetable generated and saved successfully.']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Scheduling failed: ' . $e->getMessage()]);
        }
        exit;
    }
}
