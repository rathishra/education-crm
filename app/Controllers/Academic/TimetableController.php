<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class TimetableController extends BaseController
{
    // ──────────────────────────────────────────────────────────────
    // INDEX — workflow pipeline dashboard (all sections + step status)
    // ──────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->db->query(
            "SELECT s.id, s.section_name, s.capacity,
                    b.id AS batch_id, b.program_name, b.batch_term,
                    (SELECT COUNT(*) FROM student_section_enrollments sse
                     WHERE sse.section_id=s.id AND sse.status='active') AS enrolled_count,
                    (SELECT COUNT(*) FROM academic_timetable tt
                     WHERE tt.section_id=s.id AND tt.institution_id=s.institution_id) AS timetable_slots,
                    (SELECT COUNT(DISTINCT aas.attendance_date)
                     FROM academic_attendance_sessions aas
                     WHERE aas.section_id=s.id AND aas.status='submitted') AS attendance_days
             FROM academic_sections s
             JOIN academic_batches b ON b.id=s.batch_id
             WHERE s.institution_id=? AND s.status='active'
             ORDER BY b.program_name ASC, s.section_name ASC",
            [$this->institutionId]
        );
        $sections = $this->db->fetchAll();
        $this->view('academic/timetable/index', compact('sections'));
    }

    // ──────────────────────────────────────────────────────────────
    // VIEW — read-only weekly timetable grid
    // ──────────────────────────────────────────────────────────────
    public function viewTimetable(int $id): void
    {
        $sectionId = $id;
        $this->db->query(
            "SELECT s.*, b.program_name, b.batch_term, b.id AS batch_id
             FROM academic_sections s JOIN academic_batches b ON b.id=s.batch_id
             WHERE s.id=? AND s.institution_id=?",
            [$sectionId, $this->institutionId]
        );
        $section = $this->db->fetch();
        if (!$section) {
            $this->redirectWith(url('academic/timetable'), 'error', 'Section not found.'); return;
        }

        $this->db->query(
            "SELECT * FROM academic_timetable_periods WHERE institution_id=? ORDER BY period_number ASC",
            [$this->institutionId]
        );
        $periods = $this->db->fetchAll();

        $this->db->query(
            "SELECT tt.day_of_week, tt.period_id, tt.entry_type,
                    sub.id AS subject_id, sub.subject_name, sub.subject_code,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name
             FROM academic_timetable tt
             JOIN subjects sub ON sub.id=tt.subject_id
             LEFT JOIN users u ON u.id=tt.faculty_id
             WHERE tt.section_id=? AND tt.institution_id=?",
            [$sectionId, $this->institutionId]
        );
        $timetable = [];
        $subjectColors = [];
        $palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16','#ec4899','#6366f1'];
        $ci = 0;
        foreach ($this->db->fetchAll() as $t) {
            $timetable[$t['day_of_week']][$t['period_id']] = $t;
            if (!isset($subjectColors[$t['subject_code']])) {
                $subjectColors[$t['subject_code']] = $palette[$ci++ % count($palette)];
            }
        }
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday'];
        $this->view('academic/timetable/view', compact('section','periods','timetable','subjectColors','days'));
    }

    // ──────────────────────────────────────────────────────────────
    // GENERATOR — matrix editor
    // ──────────────────────────────────────────────────────────────
    public function generator(): void
    {
        $sectionId = (int)($_GET['section_id'] ?? 0);
        if ($sectionId <= 0) {
            $this->redirectWith(url('academic/timetable'), 'error', 'No section specified.'); return;
        }

        $this->db->query(
            "SELECT s.*, b.program_name, b.batch_term, b.id AS batch_id
             FROM academic_sections s JOIN academic_batches b ON b.id=s.batch_id
             WHERE s.id=? AND s.institution_id=?",
            [$sectionId, $this->institutionId]
        );
        $section = $this->db->fetch();
        if (!$section) {
            $this->redirectWith(url('academic/timetable'), 'error', 'Invalid Section.'); return;
        }

        // Ensure periods exist — auto-create defaults if none
        $this->db->query(
            "SELECT * FROM academic_timetable_periods WHERE institution_id=? ORDER BY period_number ASC",
            [$this->institutionId]
        );
        $periods = $this->db->fetchAll();
        if (empty($periods)) {
            $defaults = [
                [1,'Period 1','09:00:00','09:50:00',0,null],
                [2,'Period 2','09:50:00','10:40:00',0,null],
                [3,'Break',   '10:40:00','11:00:00',1,'Short Break'],
                [4,'Period 3','11:00:00','11:50:00',0,null],
                [5,'Period 4','11:50:00','12:40:00',0,null],
                [6,'Lunch',   '12:40:00','13:30:00',1,'Lunch Break'],
                [7,'Period 5','13:30:00','14:20:00',0,null],
                [8,'Period 6','14:20:00','15:10:00',0,null],
            ];
            foreach ($defaults as $p) {
                $this->db->query(
                    "INSERT INTO academic_timetable_periods (institution_id,period_number,period_name,start_time,end_time,is_break,break_name) VALUES (?,?,?,?,?,?,?)",
                    [$this->institutionId,$p[0],$p[1],$p[2],$p[3],$p[4],$p[5]]
                );
            }
            $this->db->query(
                "SELECT * FROM academic_timetable_periods WHERE institution_id=? ORDER BY period_number ASC",
                [$this->institutionId]
            );
            $periods = $this->db->fetchAll();
        }

        // Subjects
        $this->db->query(
            "SELECT id, subject_name, subject_code FROM subjects WHERE institution_id=? AND status='active' ORDER BY subject_code",
            [$this->institutionId]
        );
        $subjects = $this->db->fetchAll();

        // Faculty — try users assigned to this institution via user_roles first
        $this->db->query(
            "SELECT DISTINCT u.id, u.first_name, u.last_name
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             WHERE ur.institution_id = ? AND u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $faculty = $this->db->fetchAll();
        if (empty($faculty)) {
            // Fallback: all active users in the system
            $this->db->query(
                "SELECT id, first_name, last_name FROM users WHERE is_active=1 ORDER BY first_name"
            );
            $faculty = $this->db->fetchAll();
        }

        // Existing timetable
        $this->db->query(
            "SELECT * FROM academic_timetable WHERE section_id=? AND institution_id=?",
            [$sectionId, $this->institutionId]
        );
        $timetable = [];
        foreach ($this->db->fetchAll() as $t) {
            $timetable[$t['day_of_week']][$t['period_id']] = [
                'subject_id' => $t['subject_id'],
                'faculty_id' => $t['faculty_id'],
                'entry_type' => $t['entry_type'],
            ];
        }

        // Subject color map (id → hex color)
        $palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16','#ec4899','#6366f1'];
        $subjectColors = [];
        $ci = 0;
        foreach ($subjects as $sub) {
            $subjectColors[$sub['id']] = $palette[$ci++ % count($palette)];
        }

        $days = ['monday','tuesday','wednesday','thursday','friday','saturday'];
        $this->view('academic/timetable/generator', compact(
            'section','periods','subjects','faculty','timetable','days','subjectColors'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE — save timetable (AJAX POST)
    // ──────────────────────────────────────────────────────────────
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status'=>'error','message'=>'Invalid request']); exit;
        }
        $sectionId = (int)($_POST['section_id'] ?? 0);
        $batchId   = (int)($_POST['batch_id']   ?? 0);
        if ($sectionId <= 0 || $batchId <= 0) {
            http_response_code(422);
            echo json_encode(['status'=>'error','message'=>'Missing section info']); exit;
        }
        $schedule = $_POST['schedule'] ?? [];
        try {
            $this->db->beginTransaction();
            $this->db->query(
                "DELETE FROM academic_timetable WHERE section_id=? AND institution_id=?",
                [$sectionId, $this->institutionId]
            );
            $saved = 0;
            foreach ($schedule as $day => $periodMap) {
                foreach ($periodMap as $periodId => $data) {
                    if (!empty($data['subject_id']) && !empty($data['faculty_id'])) {
                        $this->db->insert('academic_timetable', [
                            'institution_id' => $this->institutionId,
                            'batch_id'       => $batchId,
                            'section_id'     => $sectionId,
                            'day_of_week'    => $day,
                            'period_id'      => (int)$periodId,
                            'subject_id'     => (int)$data['subject_id'],
                            'faculty_id'     => (int)$data['faculty_id'],
                            'entry_type'     => $data['entry_type'] ?? 'lecture',
                            'created_by'     => $_SESSION['user_id'] ?? 1,
                        ]);
                        $saved++;
                    }
                }
            }
            $this->db->commit();
            echo json_encode(['status'=>'success','message'=>"$saved slot(s) saved successfully.",'count'=>$saved]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'Failed: '.$e->getMessage()]);
        }
        exit;
    }
}
