<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class AttendanceController extends BaseController
{
    // ──────────────────────────────────────────────────────────────
    // INDEX  — faculty portal (today's schedule) + admin selector
    // ──────────────────────────────────────────────────────────────
    public function index(): void
    {
        $userId  = $_SESSION['user_id'] ?? 0;
        $today   = date('Y-m-d');
        $dayName = strtolower(date('l'));

        // Today's timetable for the logged-in user (faculty view)
        $this->db->query(
            "SELECT tt.*, sub.subject_name, sub.subject_code,
                    sec.section_name, b.program_name, b.batch_term,
                    atp.period_name, atp.start_time, atp.end_time, atp.period_number,
                    -- check if attendance already posted today
                    (SELECT id FROM academic_attendance_sessions
                     WHERE section_id=tt.section_id AND subject_id=tt.subject_id
                       AND faculty_id=tt.faculty_id AND attendance_date=?
                     LIMIT 1) AS session_posted_id,
                    (SELECT status FROM academic_attendance_sessions
                     WHERE section_id=tt.section_id AND subject_id=tt.subject_id
                       AND faculty_id=tt.faculty_id AND attendance_date=?
                     LIMIT 1) AS session_status
             FROM academic_timetable tt
             JOIN subjects sub ON sub.id = tt.subject_id
             JOIN academic_sections sec ON sec.id = tt.section_id
             JOIN academic_batches b ON b.id = tt.batch_id
             JOIN academic_timetable_periods atp ON atp.id = tt.period_id
             WHERE tt.faculty_id = ? AND tt.day_of_week = ? AND tt.institution_id = ?
             ORDER BY atp.period_number ASC",
            [$today, $today, $userId, $dayName, $this->institutionId]
        );
        $todayClasses = $this->db->fetchAll();

        // Recent sessions posted by this user (last 10)
        $this->db->query(
            "SELECT aas.*, sub.subject_name, sub.subject_code, sec.section_name,
                    b.program_name, b.batch_term,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id AND attendance_status='present') AS present,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id) AS total
             FROM academic_attendance_sessions aas
             JOIN subjects sub ON sub.id = aas.subject_id
             JOIN academic_sections sec ON sec.id = aas.section_id
             JOIN academic_batches b ON b.id = aas.batch_id
             WHERE aas.faculty_id = ? AND aas.institution_id = ?
             ORDER BY aas.attendance_date DESC, aas.created_at DESC LIMIT 10",
            [$userId, $this->institutionId]
        );
        $recentSessions = $this->db->fetchAll();

        // Stats
        $this->db->query(
            "SELECT
                SUM(attendance_date = ?) AS today_count,
                SUM(YEARWEEK(attendance_date,1) = YEARWEEK(CURDATE(),1)) AS week_count,
                COUNT(*) AS total_count,
                SUM(status='submitted') AS submitted_count
             FROM academic_attendance_sessions
             WHERE faculty_id = ? AND institution_id = ?",
            [$today, $userId, $this->institutionId]
        );
        $stats = $this->db->fetch() ?: [];

        // Admin selector data
        $this->db->query("SELECT s.id AS section_id, s.section_name, b.program_name, b.batch_term FROM academic_sections s JOIN academic_batches b ON b.id=s.batch_id WHERE s.institution_id=? AND s.status='active' ORDER BY b.program_name, s.section_name", [$this->institutionId]);
        $sections = $this->db->fetchAll();

        $this->db->query("SELECT id, subject_code, subject_name FROM subjects WHERE institution_id=? AND status='active'", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->view('academic/attendance/index', compact('todayClasses', 'recentSessions', 'stats', 'sections', 'subjects', 'today'));
    }

    // ──────────────────────────────────────────────────────────────
    // MARK  — attendance register for a class
    // ──────────────────────────────────────────────────────────────
    public function mark(): void
    {
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $subjectId = (int)($_GET['subject_id'] ?? 0);
        $date      = trim($_GET['date'] ?? date('Y-m-d'));

        if ($sectionId <= 0 || $subjectId <= 0) {
            $this->redirectWith(url('academic/attendance'), 'error', 'Section and Subject are required.'); return;
        }

        $this->db->query(
            "SELECT s.*, b.program_name, b.batch_term, b.id AS batch_id FROM academic_sections s JOIN academic_batches b ON b.id=s.batch_id WHERE s.id=? AND s.institution_id=?",
            [$sectionId, $this->institutionId]
        );
        $section = $this->db->fetch();
        if (!$section) { $this->redirectWith(url('academic/attendance'), 'error', 'Invalid Section.'); return; }

        $this->db->query("SELECT * FROM subjects WHERE id=? AND institution_id=?", [$subjectId, $this->institutionId]);
        $subject = $this->db->fetch();
        if (!$subject) { $this->redirectWith(url('academic/attendance'), 'error', 'Invalid Subject.'); return; }

        // Existing session for this date
        $this->db->query(
            "SELECT * FROM academic_attendance_sessions WHERE section_id=? AND subject_id=? AND attendance_date=? AND institution_id=?",
            [$sectionId, $subjectId, $date, $this->institutionId]
        );
        $session = $this->db->fetch();

        // Period info (from timetable if available)
        $dayName = strtolower(date('l', strtotime($date)));
        $this->db->query(
            "SELECT atp.period_name, atp.start_time, atp.end_time, tt.entry_type
             FROM academic_timetable tt
             JOIN academic_timetable_periods atp ON atp.id=tt.period_id
             WHERE tt.section_id=? AND tt.subject_id=? AND tt.day_of_week=? AND tt.institution_id=?
             LIMIT 1",
            [$sectionId, $subjectId, $dayName, $this->institutionId]
        );
        $periodInfo = $this->db->fetch();

        // Load ENROLLED students; fall back to institution students if none enrolled
        $this->db->query(
            "SELECT st.id, st.first_name, st.last_name, st.roll_number, sse.current_semester
             FROM student_section_enrollments sse
             JOIN students st ON st.id=sse.student_id
             WHERE sse.section_id=? AND sse.institution_id=? AND sse.status='active'
             ORDER BY st.roll_number",
            [$sectionId, $this->institutionId]
        );
        $students = $this->db->fetchAll();

        if (empty($students)) {
            // Fallback: all active students of institution
            $this->db->query("SELECT id, first_name, last_name, roll_number FROM students WHERE institution_id=? AND status='active' ORDER BY roll_number LIMIT 200", [$this->institutionId]);
            $students = $this->db->fetchAll();
        }

        // Existing records
        $records = [];
        if ($session) {
            $this->db->query("SELECT student_id, attendance_status, remarks FROM academic_attendance_records WHERE session_id=?", [$session['id']]);
            foreach ($this->db->fetchAll() as $r) {
                $records[$r['student_id']] = $r;
            }
        }

        // Compute summary if records exist
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0, 'total' => count($students)];
        foreach ($records as $r) {
            $summary[$r['attendance_status']] = ($summary[$r['attendance_status']] ?? 0) + 1;
        }

        $this->view('academic/attendance/mark', compact('section', 'subject', 'date', 'students', 'session', 'records', 'summary', 'periodInfo'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE  — save / submit attendance
    // ──────────────────────────────────────────────────────────────
    public function store(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid']); exit;
        }
        $sectionId = (int)($_POST['section_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $batchId   = (int)($_POST['batch_id']   ?? 0);
        $date      = trim($_POST['attendance_date'] ?? '');
        $topic     = trim($_POST['topic_covered']  ?? '');
        $status    = trim($_POST['session_status'] ?? 'draft');
        $sessionType = trim($_POST['session_type'] ?? 'lecture');
        $attendance  = $_POST['attendance'] ?? [];
        $remarks     = $_POST['remarks']    ?? [];

        if ($sectionId <= 0 || $subjectId <= 0 || empty($date)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Missing required parameters.']); exit;
        }

        try {
            $this->db->beginTransaction();

            $this->db->query(
                "SELECT id FROM academic_attendance_sessions WHERE section_id=? AND subject_id=? AND attendance_date=? AND institution_id=?",
                [$sectionId, $subjectId, $date, $this->institutionId]
            );
            $existing = $this->db->fetch();

            if ($existing) {
                $sessionId = $existing['id'];
                $this->db->query(
                    "UPDATE academic_attendance_sessions SET topic_covered=?, status=?, session_type=? WHERE id=?",
                    [$topic, $status, $sessionType, $sessionId]
                );
                $this->db->query("DELETE FROM academic_attendance_records WHERE session_id=?", [$sessionId]);
            } else {
                $this->db->insert('academic_attendance_sessions', [
                    'institution_id'  => $this->institutionId,
                    'batch_id'        => $batchId,
                    'section_id'      => $sectionId,
                    'subject_id'      => $subjectId,
                    'faculty_id'      => $_SESSION['user_id'] ?? 1,
                    'attendance_date' => $date,
                    'session_type'    => $sessionType,
                    'topic_covered'   => $topic,
                    'status'          => $status,
                    'created_by'      => $_SESSION['user_id'] ?? 1,
                ]);
                $sessionId = $this->db->lastInsertId();
            }

            foreach ($attendance as $studentId => $attStatus) {
                $this->db->insert('academic_attendance_records', [
                    'session_id'       => $sessionId,
                    'student_id'       => (int)$studentId,
                    'institution_id'   => $this->institutionId,
                    'attendance_status'=> $attStatus,
                    'remarks'          => $remarks[$studentId] ?? null,
                ]);
            }

            $this->db->commit();
            $verb = $status === 'submitted' ? 'submitted' : 'saved as draft';
            echo json_encode(['status' => 'success', 'message' => "Attendance $verb successfully."]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // HISTORY  — list of attendance sessions with filters
    // ──────────────────────────────────────────────────────────────
    public function history(): void
    {
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $subjectId = (int)($_GET['subject_id'] ?? 0);
        $from      = trim($_GET['from'] ?? date('Y-m-01'));
        $to        = trim($_GET['to']   ?? date('Y-m-d'));

        $where  = "aas.institution_id = ?";
        $params = [$this->institutionId];
        if ($sectionId) { $where .= " AND aas.section_id=?"; $params[] = $sectionId; }
        if ($subjectId) { $where .= " AND aas.subject_id=?"; $params[] = $subjectId; }
        if ($from)      { $where .= " AND aas.attendance_date >= ?"; $params[] = $from; }
        if ($to)        { $where .= " AND aas.attendance_date <= ?"; $params[] = $to; }

        $this->db->query(
            "SELECT aas.*, sub.subject_name, sub.subject_code,
                    sec.section_name, b.program_name, b.batch_term,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id AND attendance_status='present') AS present_count,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id AND attendance_status='absent')  AS absent_count,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id) AS total_count
             FROM academic_attendance_sessions aas
             JOIN subjects sub ON sub.id = aas.subject_id
             JOIN academic_sections sec ON sec.id = aas.section_id
             JOIN academic_batches b ON b.id = aas.batch_id
             JOIN users u ON u.id = aas.faculty_id
             WHERE $where
             ORDER BY aas.attendance_date DESC, aas.created_at DESC
             LIMIT 200",
            $params
        );
        $sessions = $this->db->fetchAll();

        $this->db->query("SELECT s.id AS section_id, s.section_name, b.program_name, b.batch_term FROM academic_sections s JOIN academic_batches b ON b.id=s.batch_id WHERE s.institution_id=? AND s.status='active' ORDER BY b.program_name, s.section_name", [$this->institutionId]);
        $sections = $this->db->fetchAll();

        $this->db->query("SELECT id, subject_code, subject_name FROM subjects WHERE institution_id=? AND status='active'", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->view('academic/attendance/history', compact('sessions', 'sections', 'subjects', 'sectionId', 'subjectId', 'from', 'to'));
    }

    // ──────────────────────────────────────────────────────────────
    // REPORT  — per-student attendance % matrix for a section
    // ──────────────────────────────────────────────────────────────
    public function report(): void
    {
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $from      = trim($_GET['from'] ?? date('Y-m-01'));
        $to        = trim($_GET['to']   ?? date('Y-m-d'));

        $this->db->query(
            "SELECT s.id AS section_id, s.section_name, b.program_name, b.batch_term, b.id AS batch_id
             FROM academic_sections s JOIN academic_batches b ON b.id=s.batch_id
             WHERE s.institution_id=? AND s.status='active' ORDER BY b.program_name, s.section_name",
            [$this->institutionId]
        );
        $sections = $this->db->fetchAll();

        $section = null; $students = []; $subjects = []; $matrix = []; $sessionCounts = [];

        if ($sectionId) {
            foreach ($sections as $sec) {
                if ($sec['section_id'] == $sectionId) { $section = $sec; break; }
            }

            if ($section) {
                // Enrolled students
                $this->db->query(
                    "SELECT st.id, st.first_name, st.last_name, st.roll_number
                     FROM student_section_enrollments sse JOIN students st ON st.id=sse.student_id
                     WHERE sse.section_id=? AND sse.institution_id=? AND sse.status='active'
                     ORDER BY st.roll_number",
                    [$sectionId, $this->institutionId]
                );
                $students = $this->db->fetchAll();

                // Subjects taught in this section (from timetable)
                $this->db->query(
                    "SELECT DISTINCT sub.id, sub.subject_code, sub.subject_name
                     FROM academic_timetable tt JOIN subjects sub ON sub.id=tt.subject_id
                     WHERE tt.section_id=? AND tt.institution_id=?
                     ORDER BY sub.subject_code",
                    [$sectionId, $this->institutionId]
                );
                $subjects = $this->db->fetchAll();

                // Session counts per subject in date range
                $this->db->query(
                    "SELECT subject_id, COUNT(*) AS session_count
                     FROM academic_attendance_sessions
                     WHERE section_id=? AND institution_id=? AND attendance_date BETWEEN ? AND ? AND status='submitted'
                     GROUP BY subject_id",
                    [$sectionId, $this->institutionId, $from, $to]
                );
                foreach ($this->db->fetchAll() as $r) {
                    $sessionCounts[$r['subject_id']] = (int)$r['session_count'];
                }

                // Attendance records — present count per student per subject
                if (!empty($students) && !empty($subjects)) {
                    $subjectIds = array_column($subjects, 'id');
                    $studentIds = array_column($students, 'id');
                    $sPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));
                    $stPlaceholders = implode(',', array_fill(0, count($studentIds), '?'));

                    $this->db->query(
                        "SELECT aar.student_id, aas.subject_id,
                                SUM(aar.attendance_status='present') AS present_count,
                                COUNT(*) AS total_count
                         FROM academic_attendance_records aar
                         JOIN academic_attendance_sessions aas ON aas.id=aar.session_id
                         WHERE aas.section_id=? AND aas.institution_id=?
                           AND aas.attendance_date BETWEEN ? AND ?
                           AND aas.status='submitted'
                           AND aar.student_id IN ($stPlaceholders)
                           AND aas.subject_id IN ($sPlaceholders)
                         GROUP BY aar.student_id, aas.subject_id",
                        array_merge([$sectionId, $this->institutionId, $from, $to], $studentIds, $subjectIds)
                    );
                    foreach ($this->db->fetchAll() as $r) {
                        $matrix[$r['student_id']][$r['subject_id']] = [
                            'present' => (int)$r['present_count'],
                            'total'   => (int)$r['total_count'],
                        ];
                    }
                }
            }
        }

        $this->view('academic/attendance/report', compact('sections', 'section', 'students', 'subjects', 'matrix', 'sessionCounts', 'sectionId', 'from', 'to'));
    }
}
