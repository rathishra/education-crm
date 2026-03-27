<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class SectionController extends BaseController
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->db->query(
            "SELECT s.*, b.program_name, b.batch_term, c.room_number,
                    (SELECT COUNT(*) FROM student_section_enrollments sse WHERE sse.section_id=s.id AND sse.status='active') AS enrolled_count,
                    (SELECT COUNT(*) FROM academic_timetable tt WHERE tt.section_id=s.id) AS timetable_slots
             FROM academic_sections s
             JOIN academic_batches b ON b.id = s.batch_id
             LEFT JOIN classrooms c ON c.id = s.default_classroom_id
             WHERE s.institution_id = ?
             ORDER BY b.program_name, s.section_name",
            [$this->institutionId]
        );
        $sections = $this->db->fetchAll();
        $this->view('academic/sections/index', compact('sections'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE / STORE
    // ──────────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->db->query(
            "SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id = ? AND status='active' ORDER BY start_date DESC",
            [$this->institutionId]
        );
        $batches = $this->db->fetchAll();
        $this->db->query("SELECT id, room_number, room_name FROM classrooms WHERE institution_id = ? AND is_active=1", [$this->institutionId]);
        $classrooms = $this->db->fetchAll();
        $this->view('academic/sections/create', compact('batches', 'classrooms'));
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid']); exit;
        }
        $batchId     = (int)($_POST['batch_id'] ?? 0);
        $sectionName = trim($_POST['section_name'] ?? '');
        if ($batchId <= 0 || empty($sectionName)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Batch and Section Name are required']); exit;
        }
        try {
            $this->db->insert('academic_sections', [
                'institution_id'       => $this->institutionId,
                'batch_id'             => $batchId,
                'section_name'         => $sectionName,
                'default_classroom_id' => (int)($_POST['default_classroom_id'] ?? 0) ?: null,
                'class_advisor_id'     => (int)($_POST['class_advisor_id'] ?? 0) ?: null,
                'capacity'             => (int)($_POST['capacity'] ?? 30),
                'status'               => 'active',
                'created_by'           => $_SESSION['user_id'] ?? 1,
            ]);
            $newId = $this->db->lastInsertId();
            echo json_encode(['status' => 'success', 'message' => 'Section created.', 'id' => $newId]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW  — section detail + enrolled students + attendance summary
    // ──────────────────────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->db->query(
            "SELECT s.*, b.program_name, b.batch_term, b.id AS batch_id,
                    c.room_number, c.room_name,
                    CONCAT(u.first_name,' ',u.last_name) AS advisor_name
             FROM academic_sections s
             JOIN academic_batches b ON b.id = s.batch_id
             LEFT JOIN classrooms c ON c.id = s.default_classroom_id
             LEFT JOIN users u ON u.id = s.class_advisor_id
             WHERE s.id = ? AND s.institution_id = ?",
            [$id, $this->institutionId]
        );
        $section = $this->db->fetch();
        if (!$section) { $this->redirectWith(url('academic/sections'), 'error', 'Section not found.'); return; }

        // Enrolled students with attendance summary
        $this->db->query(
            "SELECT st.id, st.first_name, st.last_name, st.roll_number, st.email,
                    sse.enrollment_date, sse.current_semester, sse.status AS enroll_status, sse.id AS enrollment_id,
                    (SELECT COUNT(*) FROM academic_attendance_records aar
                     JOIN academic_attendance_sessions aas ON aas.id=aar.session_id
                     WHERE aar.student_id=st.id AND aas.section_id=? AND aar.attendance_status='present') AS present_count,
                    (SELECT COUNT(*) FROM academic_attendance_records aar
                     JOIN academic_attendance_sessions aas ON aas.id=aar.session_id
                     WHERE aar.student_id=st.id AND aas.section_id=?) AS total_marked
             FROM student_section_enrollments sse
             JOIN students st ON st.id = sse.student_id
             WHERE sse.section_id = ? AND sse.institution_id = ?
             ORDER BY sse.status, st.roll_number",
            [$id, $id, $id, $this->institutionId]
        );
        $enrolled = $this->db->fetchAll();

        // Unenrolled students (for add form)
        $enrolledIds = array_column($enrolled, 'id');
        $excludeClause = '';
        $params = [$this->institutionId];
        if (!empty($enrolledIds)) {
            $placeholders = implode(',', array_fill(0, count($enrolledIds), '?'));
            $excludeClause = "AND id NOT IN ($placeholders)";
            $params = array_merge($params, $enrolledIds);
        }
        $this->db->query(
            "SELECT id, first_name, last_name, roll_number FROM students
             WHERE institution_id = ? AND status='active' $excludeClause
             ORDER BY roll_number LIMIT 300",
            $params
        );
        $available = $this->db->fetchAll();

        // Today's timetable for this section
        $todayName = strtolower(date('l'));
        $this->db->query(
            "SELECT tt.*, sub.subject_name, sub.subject_code,
                    atp.period_name, atp.start_time, atp.end_time,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name
             FROM academic_timetable tt
             JOIN subjects sub ON sub.id = tt.subject_id
             JOIN academic_timetable_periods atp ON atp.id = tt.period_id
             LEFT JOIN users u ON u.id = tt.faculty_id
             WHERE tt.section_id = ? AND tt.day_of_week = ? AND tt.institution_id = ?
             ORDER BY atp.period_number",
            [$id, $todayName, $this->institutionId]
        );
        $todaySchedule = $this->db->fetchAll();

        // Recent attendance sessions
        $this->db->query(
            "SELECT aas.*, sub.subject_name, sub.subject_code,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id AND attendance_status='present') AS present_count,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id) AS total_count
             FROM academic_attendance_sessions aas
             JOIN subjects sub ON sub.id = aas.subject_id
             JOIN users u ON u.id = aas.faculty_id
             WHERE aas.section_id = ? AND aas.institution_id = ?
             ORDER BY aas.attendance_date DESC LIMIT 8",
            [$id, $this->institutionId]
        );
        $recentSessions = $this->db->fetchAll();

        $this->view('academic/sections/show', compact('section', 'enrolled', 'available', 'todaySchedule', 'recentSessions'));
    }

    // ──────────────────────────────────────────────────────────────
    // ENROLL STUDENTS
    // ──────────────────────────────────────────────────────────────
    public function enroll(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid']); exit;
        }
        $this->db->query(
            "SELECT s.*, b.id AS batch_id FROM academic_sections s JOIN academic_batches b ON b.id=s.batch_id WHERE s.id=? AND s.institution_id=?",
            [$id, $this->institutionId]
        );
        $section = $this->db->fetch();
        if (!$section) { http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Section not found']); exit; }

        $studentIds    = array_filter(array_map('intval', $_POST['student_ids'] ?? []));
        $semester      = (int)($_POST['current_semester'] ?? 1);
        $academicYear  = trim($_POST['academic_year'] ?? date('Y').'-'.((int)date('Y')+1));
        $enrollDate    = trim($_POST['enrollment_date'] ?? date('Y-m-d'));

        $added = 0;
        foreach ($studentIds as $sid) {
            // Skip duplicates silently
            $this->db->query(
                "SELECT id FROM student_section_enrollments WHERE student_id=? AND section_id=?",
                [$sid, $id]
            );
            if ($this->db->fetch()) continue;
            $this->db->insert('student_section_enrollments', [
                'institution_id'   => $this->institutionId,
                'student_id'       => $sid,
                'batch_id'         => $section['batch_id'],
                'section_id'       => $id,
                'academic_year'    => $academicYear,
                'current_semester' => $semester,
                'enrollment_date'  => $enrollDate,
                'status'           => 'active',
                'created_by'       => $_SESSION['user_id'] ?? 1,
            ]);
            $added++;
        }
        echo json_encode(['status' => 'success', 'message' => "$added student(s) enrolled.", 'added' => $added]);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // UNENROLL STUDENT
    // ──────────────────────────────────────────────────────────────
    public function unenroll(int $enrollmentId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid']); exit;
        }
        $this->db->query(
            "UPDATE student_section_enrollments SET status='dropped' WHERE id=? AND institution_id=?",
            [$enrollmentId, $this->institutionId]
        );
        echo json_encode(['status' => 'success', 'message' => 'Student unenrolled.']);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT / UPDATE
    // ──────────────────────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->db->query("SELECT s.*, b.program_name, b.batch_term FROM academic_sections s JOIN academic_batches b ON b.id=s.batch_id WHERE s.id=? AND s.institution_id=?", [$id, $this->institutionId]);
        $section = $this->db->fetch();
        if (!$section) { $this->redirectWith(url('academic/sections'), 'error', 'Section not found.'); return; }
        $this->db->query("SELECT id, room_number, room_name FROM classrooms WHERE institution_id=? AND is_active=1", [$this->institutionId]);
        $classrooms = $this->db->fetchAll();
        $this->view('academic/sections/edit', compact('section', 'classrooms'));
    }

    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid']); exit;
        }
        $this->db->query(
            "UPDATE academic_sections SET section_name=?, default_classroom_id=?, capacity=?, status=? WHERE id=? AND institution_id=?",
            [
                trim($_POST['section_name'] ?? ''),
                (int)($_POST['default_classroom_id'] ?? 0) ?: null,
                (int)($_POST['capacity'] ?? 30),
                trim($_POST['status'] ?? 'active'),
                $id, $this->institutionId,
            ]
        );
        echo json_encode(['status' => 'success', 'message' => 'Section updated.']);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(int $id): void
    {
        $this->db->query("UPDATE academic_sections SET status='inactive' WHERE id=? AND institution_id=?", [$id, $this->institutionId]);
        $this->redirectWith(url('academic/sections'), 'success', 'Section deactivated.');
    }
}
