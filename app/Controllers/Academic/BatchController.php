<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class BatchController extends BaseController
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->db->query(
            "SELECT b.*,
                    (SELECT COUNT(*) FROM academic_sections WHERE batch_id=b.id AND status='active') AS section_count,
                    (SELECT COUNT(*) FROM student_section_enrollments WHERE batch_id=b.id AND status='active') AS enrolled_count
             FROM academic_batches b
             WHERE b.institution_id = ?
             ORDER BY b.start_date DESC",
            [$this->institutionId]
        );
        $batches = $this->db->fetchAll();
        $this->view('academic/batches/index', compact('batches'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE / STORE
    // ──────────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->db->query(
            "SELECT id, name, total_semesters FROM courses WHERE institution_id = ? AND status = 'active' ORDER BY name",
            [$this->institutionId]
        );
        $courses = $this->db->fetchAll();
        $this->view('academic/batches/create', compact('courses'));
    }

    public function store(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']); exit;
        }
        $programName = trim($_POST['program_name'] ?? '');
        $batchTerm   = trim($_POST['batch_term']   ?? '');
        $startDate   = trim($_POST['start_date']   ?? '');

        if (empty($programName) || empty($batchTerm) || empty($startDate)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Program Name, Term, and Start Date are required']); exit;
        }
        try {
            $this->db->insert('academic_batches', [
                'institution_id'              => $this->institutionId,
                'program_name'                => $programName,
                'batch_term'                  => $batchTerm,
                'start_date'                  => $startDate,
                'end_date'                    => trim($_POST['end_date'] ?? '') ?: null,
                'max_intake'                  => (int)($_POST['max_intake'] ?? 60),
                'graduation_credits_required' => (float)($_POST['graduation_credits'] ?? 0),
                'total_semesters'             => (int)($_POST['total_semesters'] ?? 8),
                'status'                      => 'active',
                'created_by'                  => $_SESSION['user_id'] ?? 1,
            ]);
            $newId = $this->db->lastInsertId();
            echo json_encode(['status' => 'success', 'message' => 'Batch created.', 'id' => $newId]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->db->query(
            "SELECT * FROM academic_batches WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $batch = $this->db->fetch();
        if (!$batch) { $this->redirectWith(url('academic/batches'), 'error', 'Batch not found.'); return; }

        // Sections with student counts
        $this->db->query(
            "SELECT s.*,
                    c.room_number,
                    CONCAT(u.first_name,' ',u.last_name) AS advisor_name,
                    (SELECT COUNT(*) FROM student_section_enrollments sse WHERE sse.section_id=s.id AND sse.status='active') AS enrolled_count,
                    (SELECT COUNT(*) FROM academic_timetable tt WHERE tt.section_id=s.id) AS timetable_slots
             FROM academic_sections s
             LEFT JOIN classrooms c ON c.id = s.default_classroom_id
             LEFT JOIN users u ON u.id = s.class_advisor_id
             WHERE s.batch_id = ? AND s.institution_id = ?
             ORDER BY s.section_name",
            [$id, $this->institutionId]
        );
        $sections = $this->db->fetchAll();

        // Recent attendance sessions for this batch
        $this->db->query(
            "SELECT aas.*, s.subject_name, s.subject_code, sec.section_name,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id AND attendance_status='present') AS present_count,
                    (SELECT COUNT(*) FROM academic_attendance_records WHERE session_id=aas.id) AS total_count
             FROM academic_attendance_sessions aas
             JOIN subjects s ON s.id = aas.subject_id
             JOIN academic_sections sec ON sec.id = aas.section_id
             JOIN users u ON u.id = aas.faculty_id
             WHERE aas.batch_id = ? AND aas.institution_id = ?
             ORDER BY aas.attendance_date DESC, aas.created_at DESC
             LIMIT 10",
            [$id, $this->institutionId]
        );
        $recentSessions = $this->db->fetchAll();

        $this->view('academic/batches/show', compact('batch', 'sections', 'recentSessions'));
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT / UPDATE
    // ──────────────────────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->db->query("SELECT * FROM academic_batches WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $batch = $this->db->fetch();
        if (!$batch) { $this->redirectWith(url('academic/batches'), 'error', 'Batch not found.'); return; }

        $this->db->query(
            "SELECT id, name, total_semesters FROM courses WHERE institution_id = ? AND status = 'active' ORDER BY name",
            [$this->institutionId]
        );
        $courses = $this->db->fetchAll();
        $this->view('academic/batches/edit', compact('batch', 'courses'));
    }

    public function update(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid']); exit;
        }
        $this->db->query("SELECT id FROM academic_batches WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        if (!$this->db->fetch()) {
            http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Not found']); exit;
        }
        $this->db->query(
            "UPDATE academic_batches SET program_name=?, batch_term=?, start_date=?, end_date=?, max_intake=?, total_semesters=?, status=? WHERE id=?",
            [
                trim($_POST['program_name'] ?? ''),
                trim($_POST['batch_term']   ?? ''),
                trim($_POST['start_date']   ?? ''),
                trim($_POST['end_date']     ?? '') ?: null,
                (int)($_POST['max_intake']      ?? 60),
                (int)($_POST['total_semesters'] ?? 8),
                trim($_POST['status'] ?? 'active'),
                $id,
            ]
        );
        echo json_encode(['status' => 'success', 'message' => 'Batch updated.']);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->db->query("SELECT id FROM academic_batches WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        if (!$this->db->fetch()) {
            $this->redirectWith(url('academic/batches'), 'error', 'Not found.'); return;
        }
        $this->db->query("UPDATE academic_batches SET status='inactive' WHERE id=?", [$id]);
        $this->redirectWith(url('academic/batches'), 'success', 'Batch deactivated.');
    }
}
