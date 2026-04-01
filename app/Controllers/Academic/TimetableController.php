<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class TimetableController extends BaseController
{
    private function ensureSchema(): void
    {
        // academic_timetable — add room_id column if not present
        try {
            $this->db->query("ALTER TABLE academic_timetable ADD COLUMN room_id INT UNSIGNED NULL AFTER faculty_id");
        } catch (\Exception $e) {}
        try {
            $this->db->query("ALTER TABLE academic_timetable ADD COLUMN notes VARCHAR(255) NULL AFTER entry_type");
        } catch (\Exception $e) {}
    }

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
                     WHERE aas.section_id=s.id AND aas.status='submitted') AS attendance_days,
                    (SELECT COUNT(*) FROM batch_subjects bs
                     WHERE bs.batch_id=b.id AND bs.institution_id=s.institution_id) AS subject_count
             FROM academic_sections s
             JOIN academic_batches b ON b.id=s.batch_id
             WHERE s.institution_id=? AND s.status='active'
             ORDER BY b.program_name ASC, s.section_name ASC",
            [$this->institutionId]
        );
        $sections = $this->db->fetchAll();

        // Stats
        $totalSections  = count($sections);
        $withTimetable  = count(array_filter($sections, fn($s) => (int)$s['timetable_slots'] > 0));
        $withAttendance = count(array_filter($sections, fn($s) => (int)$s['attendance_days'] > 0));

        $this->view('academic/timetable/index', compact('sections', 'totalSections', 'withTimetable', 'withAttendance'));
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
            "SELECT tt.day_of_week, tt.period_id, tt.entry_type, tt.notes,
                    tt.room_id,
                    sub.id AS subject_id, sub.subject_name, sub.subject_code,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    r.room_number, r.room_name
             FROM academic_timetable tt
             JOIN subjects sub ON sub.id=tt.subject_id
             LEFT JOIN users u ON u.id=tt.faculty_id
             LEFT JOIN classrooms r ON r.id=tt.room_id
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
    // GENERATOR — matrix editor (enterprise-enhanced)
    // ──────────────────────────────────────────────────────────────
    public function generator(): void
    {
        $this->ensureSchema();

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

        $batchId = (int)$section['batch_id'];

        // ── Periods ─────────────────────────────────────────────
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

        // ── Subjects: batch-allocated first, fallback to all ────
        $this->db->query(
            "SELECT s.id, s.subject_name, s.subject_code, s.subject_type,
                    bs.semester, bs.credits AS alloc_credits, bs.is_compulsory
             FROM batch_subjects bs
             JOIN subjects s ON s.id = bs.subject_id
             WHERE bs.batch_id=? AND bs.institution_id=? AND s.status='active'
             ORDER BY bs.semester, s.subject_code",
            [$batchId, $this->institutionId]
        );
        $subjects = $this->db->fetchAll();
        $batchSubjectAllocated = !empty($subjects); // flag for view hint

        if (empty($subjects)) {
            // Fallback — show all institution subjects
            $this->db->query(
                "SELECT id, subject_name, subject_code, subject_type, NULL AS semester,
                        credits AS alloc_credits, 1 AS is_compulsory
                 FROM subjects WHERE institution_id=? AND status='active' ORDER BY subject_code",
                [$this->institutionId]
            );
            $subjects = $this->db->fetchAll();
        }

        // ── Faculty allocation map: subject_id → [{faculty_id, name, hours}] ──
        $this->db->query(
            "SELECT fa.subject_id, fa.faculty_id, fa.hours_per_week, fa.allocation_type,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name
             FROM faculty_subject_allocations fa
             JOIN users u ON u.id = fa.faculty_id
             WHERE fa.batch_id=? AND fa.institution_id=? AND fa.status='active'",
            [$batchId, $this->institutionId]
        );
        $facultyAllocRaw = $this->db->fetchAll();
        $facultyBySubject = []; // subject_id => [{id, name, allocation_type}]
        foreach ($facultyAllocRaw as $fa) {
            $facultyBySubject[$fa['subject_id']][] = [
                'id'              => $fa['faculty_id'],
                'name'            => $fa['faculty_name'],
                'hours_per_week'  => $fa['hours_per_week'],
                'allocation_type' => $fa['allocation_type'],
            ];
        }

        // ── Full faculty list (fallback + for cells with no allocation) ──
        $this->db->query(
            "SELECT DISTINCT u.id, u.first_name, u.last_name,
                    CONCAT(u.first_name,' ',u.last_name) AS full_name
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             WHERE ur.institution_id = ? AND u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $allFaculty = $this->db->fetchAll();
        if (empty($allFaculty)) {
            $this->db->query("SELECT id, first_name, last_name, CONCAT(first_name,' ',last_name) AS full_name FROM users WHERE is_active=1 ORDER BY first_name");
            $allFaculty = $this->db->fetchAll();
        }

        // ── Classrooms ──────────────────────────────────────────
        $this->db->query(
            "SELECT id, room_number, room_name, room_type, capacity FROM classrooms WHERE institution_id=? AND is_active=1 ORDER BY room_number",
            [$this->institutionId]
        );
        $rooms = $this->db->fetchAll();

        // ── Existing timetable ──────────────────────────────────
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
                'room_id'    => $t['room_id'] ?? null,
                'notes'      => $t['notes']    ?? '',
            ];
        }

        // ── Conflict map: other sections' timetable for same batch ──
        // Structure: [day][period_id] => [{section_name, faculty_id, subject_code}]
        $this->db->query(
            "SELECT tt.day_of_week, tt.period_id, tt.faculty_id, tt.subject_id,
                    sec.section_name,
                    sub.subject_code
             FROM academic_timetable tt
             JOIN academic_sections sec ON sec.id = tt.section_id
             JOIN subjects sub ON sub.id = tt.subject_id
             WHERE tt.batch_id=? AND tt.section_id != ? AND tt.institution_id=?",
            [$batchId, $sectionId, $this->institutionId]
        );
        $conflictMap = []; // [faculty_id][day][period_id] = section_name
        foreach ($this->db->fetchAll() as $row) {
            if ($row['faculty_id']) {
                $conflictMap[$row['faculty_id']][$row['day_of_week']][$row['period_id']] = $row['section_name'];
            }
        }

        // ── Subject colors ───────────────────────────────────────
        $palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16','#ec4899','#6366f1'];
        $subjectColors = [];
        $ci = 0;
        foreach ($subjects as $sub) {
            $subjectColors[$sub['id']] = $palette[$ci++ % count($palette)];
        }

        $days = ['monday','tuesday','wednesday','thursday','friday','saturday'];
        $this->view('academic/timetable/generator', compact(
            'section', 'periods', 'subjects', 'allFaculty', 'rooms',
            'timetable', 'days', 'subjectColors',
            'facultyBySubject', 'conflictMap', 'batchSubjectAllocated'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE — save timetable (AJAX POST) with conflict detection
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
        $conflicts = [];

        // Build conflict check: faculty already busy in other sections
        // Load ALL other timetable entries for this institution (except this section)
        $this->db->query(
            "SELECT tt.day_of_week, tt.period_id, tt.faculty_id,
                    sec.section_name,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name
             FROM academic_timetable tt
             JOIN academic_sections sec ON sec.id = tt.section_id
             JOIN users u ON u.id = tt.faculty_id
             WHERE tt.section_id != ? AND tt.institution_id=? AND tt.faculty_id IS NOT NULL",
            [$sectionId, $this->institutionId]
        );
        $busySlots = []; // [faculty_id][day][period_id] = section_name
        foreach ($this->db->fetchAll() as $row) {
            $busySlots[$row['faculty_id']][$row['day_of_week']][$row['period_id']] = [
                'section' => $row['section_name'],
                'name'    => $row['faculty_name'],
            ];
        }

        try {
            $this->db->beginTransaction();
            $this->db->query(
                "DELETE FROM academic_timetable WHERE section_id=? AND institution_id=?",
                [$sectionId, $this->institutionId]
            );
            $saved = 0;
            foreach ($schedule as $day => $periodMap) {
                foreach ($periodMap as $periodId => $data) {
                    $subjectId  = (int)($data['subject_id'] ?? 0);
                    $facultyId  = (int)($data['faculty_id'] ?? 0);
                    $entryType  = $data['entry_type'] ?? 'lecture';
                    $roomId     = !empty($data['room_id']) ? (int)$data['room_id'] : null;
                    $notes      = trim($data['notes'] ?? '');

                    if (!$subjectId || !$facultyId) continue;

                    // Check conflict
                    if (isset($busySlots[$facultyId][$day][$periodId])) {
                        $info = $busySlots[$facultyId][$day][$periodId];
                        $conflicts[] = [
                            'day'     => $day,
                            'period'  => $periodId,
                            'faculty' => $info['name'],
                            'section' => $info['section'],
                        ];
                    }

                    $this->db->insert('academic_timetable', [
                        'institution_id' => $this->institutionId,
                        'batch_id'       => $batchId,
                        'section_id'     => $sectionId,
                        'day_of_week'    => $day,
                        'period_id'      => (int)$periodId,
                        'subject_id'     => $subjectId,
                        'faculty_id'     => $facultyId,
                        'entry_type'     => $entryType,
                        'room_id'        => $roomId,
                        'notes'          => $notes ?: null,
                        'created_by'     => $_SESSION['user_id'] ?? 1,
                    ]);
                    $saved++;
                }
            }
            $this->db->commit();
            $this->logAudit('timetable_save', 'academic_timetable', $sectionId);

            $msg = "$saved slot(s) saved successfully.";
            if (!empty($conflicts)) {
                $msg .= ' ' . count($conflicts) . ' conflict(s) detected (same faculty in multiple sections).';
            }
            echo json_encode([
                'status'    => 'success',
                'message'   => $msg,
                'count'     => $saved,
                'conflicts' => $conflicts,
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'Failed: '.$e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // AJAX — get faculty for a subject+batch (for dynamic dropdown)
    // GET /academic/timetable/ajax/faculty?subject_id=&batch_id=
    // ──────────────────────────────────────────────────────────────
    public function ajaxFaculty(): void
    {
        $subjectId = (int)($_GET['subject_id'] ?? 0);
        $batchId   = (int)($_GET['batch_id']   ?? 0);

        if ($subjectId && $batchId) {
            $this->db->query(
                "SELECT fa.faculty_id AS id, CONCAT(u.first_name,' ',u.last_name) AS name,
                        fa.hours_per_week, fa.allocation_type
                 FROM faculty_subject_allocations fa
                 JOIN users u ON u.id = fa.faculty_id
                 WHERE fa.subject_id=? AND fa.batch_id=? AND fa.institution_id=? AND fa.status='active'",
                [$subjectId, $batchId, $this->institutionId]
            );
            $faculty = $this->db->fetchAll();
        } else {
            $faculty = [];
        }

        // Fallback: if no specific allocation, return all institution faculty
        if (empty($faculty)) {
            $this->db->query(
                "SELECT DISTINCT u.id, CONCAT(u.first_name,' ',u.last_name) AS name,
                        0 AS hours_per_week, 'both' AS allocation_type
                 FROM users u
                 JOIN user_roles ur ON ur.user_id = u.id
                 WHERE ur.institution_id=? AND u.is_active=1
                 ORDER BY u.first_name",
                [$this->institutionId]
            );
            $faculty = $this->db->fetchAll();
        }

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'faculty' => $faculty]);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // COPY — copy timetable from one section to another (AJAX POST)
    // ──────────────────────────────────────────────────────────────
    public function copyTimetable(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status'=>'error','message'=>'Invalid request']); exit;
        }

        $sourceSectionId = (int)($_POST['source_section_id'] ?? 0);
        $targetSectionId = (int)($_POST['target_section_id'] ?? 0);

        if (!$sourceSectionId || !$targetSectionId || $sourceSectionId === $targetSectionId) {
            echo json_encode(['status'=>'error','message'=>'Valid source and target sections are required.']); exit;
        }

        // Verify both belong to institution
        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM academic_sections WHERE id IN (?,?) AND institution_id=?",
            [$sourceSectionId, $targetSectionId, $this->institutionId]
        );
        if (($this->db->fetch()['cnt'] ?? 0) < 2) {
            echo json_encode(['status'=>'error','message'=>'Invalid section selection.']); exit;
        }

        // Get target batch id
        $this->db->query("SELECT batch_id FROM academic_sections WHERE id=? AND institution_id=?", [$targetSectionId, $this->institutionId]);
        $targetSection = $this->db->fetch();
        $targetBatchId = $targetSection['batch_id'] ?? 0;

        $this->db->query(
            "SELECT * FROM academic_timetable WHERE section_id=? AND institution_id=?",
            [$sourceSectionId, $this->institutionId]
        );
        $sourceSlots = $this->db->fetchAll();

        try {
            $this->db->beginTransaction();
            $this->db->query(
                "DELETE FROM academic_timetable WHERE section_id=? AND institution_id=?",
                [$targetSectionId, $this->institutionId]
            );
            $copied = 0;
            foreach ($sourceSlots as $slot) {
                $this->db->insert('academic_timetable', [
                    'institution_id' => $this->institutionId,
                    'batch_id'       => $targetBatchId,
                    'section_id'     => $targetSectionId,
                    'day_of_week'    => $slot['day_of_week'],
                    'period_id'      => $slot['period_id'],
                    'subject_id'     => $slot['subject_id'],
                    'faculty_id'     => $slot['faculty_id'],
                    'entry_type'     => $slot['entry_type'],
                    'room_id'        => $slot['room_id'] ?? null,
                    'notes'          => $slot['notes'] ?? null,
                    'created_by'     => $_SESSION['user_id'] ?? 1,
                ]);
                $copied++;
            }
            $this->db->commit();
            $this->logAudit('timetable_copy', 'academic_timetable', $targetSectionId);
            echo json_encode([
                'status'  => 'success',
                'message' => "Copied $copied slot(s) to target section.",
                'count'   => $copied,
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status'=>'error','message'=>'Failed: '.$e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // AJAX — get sections for a batch (used by copy-from-section modal)
    // GET /academic/timetable/ajax/sections?batch_id=
    // ──────────────────────────────────────────────────────────────
    public function ajaxSections(): void
    {
        $batchId = (int)($_GET['batch_id'] ?? 0);

        if ($batchId) {
            $this->db->query(
                "SELECT s.id, s.section_name,
                        (SELECT COUNT(*) FROM academic_timetable tt WHERE tt.section_id=s.id AND tt.institution_id=s.institution_id) AS timetable_slots
                 FROM academic_sections s
                 WHERE s.batch_id=? AND s.institution_id=? AND s.status='active'
                 ORDER BY s.section_name",
                [$batchId, $this->institutionId]
            );
            $sections = $this->db->fetchAll();
        } else {
            // Return all sections if no batch specified
            $this->db->query(
                "SELECT s.id, s.section_name, b.program_name, b.batch_term,
                        (SELECT COUNT(*) FROM academic_timetable tt WHERE tt.section_id=s.id AND tt.institution_id=s.institution_id) AS timetable_slots
                 FROM academic_sections s
                 JOIN academic_batches b ON b.id=s.batch_id
                 WHERE s.institution_id=? AND s.status='active'
                 ORDER BY b.program_name, s.section_name",
                [$this->institutionId]
            );
            $sections = $this->db->fetchAll();
        }

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'sections' => $sections]);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // CLEAR — delete all timetable slots for a section (AJAX POST)
    // ──────────────────────────────────────────────────────────────
    public function clearSection(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status'=>'error','message'=>'Invalid request']); exit;
        }
        $sectionId = (int)($_POST['section_id'] ?? 0);
        if (!$sectionId) {
            echo json_encode(['status'=>'error','message'=>'Section required.']); exit;
        }

        $this->db->query(
            "SELECT id FROM academic_sections WHERE id=? AND institution_id=?",
            [$sectionId, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            echo json_encode(['status'=>'error','message'=>'Section not found.']); exit;
        }

        $this->db->query(
            "DELETE FROM academic_timetable WHERE section_id=? AND institution_id=?",
            [$sectionId, $this->institutionId]
        );
        $this->logAudit('timetable_clear', 'academic_timetable', $sectionId);
        echo json_encode(['status'=>'success','message'=>'Timetable cleared.']);
        exit;
    }
}
