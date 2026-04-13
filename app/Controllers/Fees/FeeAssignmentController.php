<?php
namespace App\Controllers\Fees;

use App\Controllers\BaseController;

class FeeAssignmentController extends BaseController
{
    // ── INDEX ────────────────────────────────────────────────
    public function index(): void
    {
        $search   = trim($_GET['search'] ?? $_GET['q'] ?? '');
        $ayId     = (int)($_GET['academic_year_id'] ?? 0);
        $courseId = (int)($_GET['course_id'] ?? 0);
        $statusF  = $_GET['status'] ?? '';

        $where  = "fsa.institution_id = ?";
        $params = [$this->institutionId];

        if ($search) {
            $where   .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id_number LIKE ?)";
            $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
        }
        if ($ayId)    { $where .= " AND fsa.academic_year_id = ?"; $params[] = $ayId; }
        if ($courseId){ $where .= " AND c.id = ?";                 $params[] = $courseId; }
        if ($statusF) { $where .= " AND fsa.status = ?";            $params[] = $statusF; }

        $this->db->query(
            "SELECT fsa.*,
                    CONCAT(s.first_name,' ',s.last_name) AS student_name,
                    s.student_id_number AS enrollment_number,
                    fh.head_name, fh.head_code, fh.category,
                    ay.name AS year_name,
                    c.name AS course_name
             FROM fee_student_assignments fsa
             JOIN students s  ON s.id  = fsa.student_id
             JOIN fee_heads fh ON fh.id = fsa.fee_head_id
             LEFT JOIN academic_years ay ON ay.id = fsa.academic_year_id
             LEFT JOIN fee_structures fs ON fs.id = fsa.structure_id
             LEFT JOIN courses c ON c.id = fs.course_id
             WHERE $where
             ORDER BY fsa.created_at DESC
             LIMIT 500",
            $params
        );
        $assignments = $this->db->fetchAll();

        // Summary
        $this->db->query(
            "SELECT
               COUNT(*) AS total,
               SUM(net_amount)     AS total_net,
               SUM(paid_amount)    AS total_paid,
               SUM(balance_amount) AS total_balance,
               SUM(fine_amount)    AS total_fine
             FROM fee_student_assignments
             WHERE institution_id = ?" . ($ayId ? " AND academic_year_id = ?" : ''),
            $ayId ? [$this->institutionId, $ayId] : [$this->institutionId]
        );
        $summary = $this->db->fetch();

        $this->db->query("SELECT id, name AS year_name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->db->query("SELECT id, name AS course_name FROM courses WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, name, total_amount FROM fee_structures WHERE institution_id = ? AND status='active' ORDER BY name", [$this->institutionId]);
        $structures = $this->db->fetchAll();

        $stats = [
            'total'   => $summary['total'] ?? 0,
            'pending' => count(array_filter($assignments, fn($a) => $a['status'] === 'pending')),
            'paid'    => count(array_filter($assignments, fn($a) => $a['status'] === 'paid')),
            'overdue' => count(array_filter($assignments, fn($a) => $a['status'] === 'overdue' || (!empty($a['due_date']) && strtotime($a['due_date']) < time() && !in_array($a['status'], ['paid','waived'])))),
        ];

        $filters = [
            'academic_year_id' => $ayId,
            'course_id'        => $courseId,
            'status'           => $statusF,
            'search'           => $search,
        ];

        $this->view('fees/assignment/index', compact('assignments', 'stats', 'filters', 'academicYears', 'courses', 'structures'));
    }

    // ── ASSIGN (single student, multiple heads) ──────────────
    public function assign(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $studentId  = (int)$this->input('student_id', 0);
        $structureId = (int)$this->input('structure_id', 0);
        $ayId        = (int)$this->input('academic_year_id', 0);
        $headIds     = (array)$this->input('head_ids');
        $headAmts    = (array)$this->input('head_amounts');
        $headDates   = (array)$this->input('head_due_dates');

        if (!$studentId) {
            $this->json(['status' => 'error', 'message' => 'Student is required.'], 422);
        }

        $created = 0;
        foreach ($headIds as $i => $hid) {
            if (!$hid) continue;
            $gross = (float)($headAmts[$i] ?? 0);
            if ($gross <= 0) continue;

            // Avoid duplicate assignment for same AY + head
            $this->db->query(
                "SELECT id FROM fee_student_assignments WHERE student_id=? AND fee_head_id=? AND academic_year_id=? AND institution_id=?",
                [$studentId, (int)$hid, $ayId ?: null, $this->institutionId]
            );
            if ($this->db->fetch()) continue;

            $this->db->insert('fee_student_assignments', [
                'institution_id'   => $this->institutionId,
                'student_id'       => $studentId,
                'academic_year_id' => $ayId ?: null,
                'structure_id'     => $structureId ?: null,
                'fee_head_id'      => (int)$hid,
                'gross_amount'     => $gross,
                'concession_amount'=> 0,
                'net_amount'       => $gross,
                'paid_amount'      => 0,
                'fine_amount'      => 0,
                'balance_amount'   => $gross,
                'due_date'         => ($headDates[$i] ?? null) ?: null,
                'status'           => 'pending',
                'created_by'       => $this->user['id'] ?? null,
            ]);
            $created++;
        }

        $this->json(['status' => 'success', 'message' => "$created fee head(s) assigned successfully."]);
    }

    // ── BULK ASSIGN (from fee structure to course/batch) ─────
    public function bulkAssign(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $structureId = (int)$this->input('structure_id', 0);
        $ayId        = (int)$this->input('academic_year_id', 0);
        $courseId    = (int)$this->input('course_id', 0);
        $batchId     = (int)$this->input('batch_id', 0);
        $studentIds  = (array)$this->input('student_ids');

        if (!$structureId) {
            $this->json(['status' => 'error', 'message' => 'Structure is required.'], 422);
        }

        // If no student_ids passed, look up from course/batch
        if (empty($studentIds)) {
            $where  = "s.institution_id = ? AND s.status = 'active'";
            $params = [$this->institutionId];
            if ($courseId) { $where .= " AND s.course_id = ?"; $params[] = $courseId; }
            if ($batchId)  { $where .= " AND s.batch_id = ?";  $params[] = $batchId; }
            $this->db->query("SELECT id FROM students WHERE $where LIMIT 500", $params);
            $rows       = $this->db->fetchAll();
            $studentIds = array_column($rows, 'id');
        }

        if (empty($studentIds)) {
            $this->json(['status' => 'error', 'message' => 'No students found for the selected criteria.'], 422);
        }

        // Get structure heads
        $this->db->query(
            "SELECT fsd.*, fh.head_name FROM fee_structure_details fsd JOIN fee_heads fh ON fh.id = fsd.fee_head_id WHERE fsd.structure_id = ?",
            [$structureId]
        );
        $heads = $this->db->fetchAll();
        if (empty($heads)) {
            $this->json(['status' => 'error', 'message' => 'No fee heads in this structure.'], 422);
        }

        $created = 0;
        foreach ($studentIds as $sid) {
            $sid = (int)$sid;
            if (!$sid) continue;
            foreach ($heads as $h) {
                $this->db->query(
                    "SELECT id FROM fee_student_assignments WHERE student_id=? AND fee_head_id=? AND academic_year_id=? AND institution_id=?",
                    [$sid, $h['fee_head_id'], $ayId ?: null, $this->institutionId]
                );
                if ($this->db->fetch()) continue;
                $this->db->insert('fee_student_assignments', [
                    'institution_id'   => $this->institutionId,
                    'student_id'       => $sid,
                    'academic_year_id' => $ayId ?: null,
                    'structure_id'     => $structureId,
                    'fee_head_id'      => $h['fee_head_id'],
                    'gross_amount'     => $h['amount'],
                    'concession_amount'=> 0,
                    'net_amount'       => $h['amount'],
                    'paid_amount'      => 0,
                    'fine_amount'      => 0,
                    'balance_amount'   => $h['amount'],
                    'due_date'         => $h['due_date'],
                    'status'           => 'pending',
                    'created_by'       => $this->user['id'] ?? null,
                ]);
                $created++;
            }
        }

        $this->json(['status' => 'success', 'message' => "$created assignment(s) created for " . count($studentIds) . " student(s)."]);
    }

    // ── WAIVE / ADJUST ───────────────────────────────────────
    public function waive(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->db->query("SELECT * FROM fee_student_assignments WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $row = $this->db->fetch();
        if (!$row) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); }

        $this->db->query(
            "UPDATE fee_student_assignments SET status='waived', balance_amount=0, remarks=?, updated_at=NOW() WHERE id=?",
            [trim($this->input('remarks', 'Waived')), $id]
        );
        $this->json(['status' => 'success', 'message' => 'Fee waived.']);
    }

    // ── AJAX: student search ─────────────────────────────────
    public function ajaxSearch(): void
    {
        $q = trim($_GET['term'] ?? $this->input('q', '') ?? '');
        if (strlen($q) < 2) { $this->json(['results' => []]); }

        $this->db->query(
            "SELECT s.id,
                    CONCAT(s.first_name,' ',s.last_name) AS text,
                    s.student_id_number AS enrollment_number,
                    c.name AS course_name
             FROM students s
             LEFT JOIN courses c ON c.id = s.course_id
             WHERE s.institution_id = ?
               AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id_number LIKE ?)
             ORDER BY s.first_name LIMIT 20",
            [$this->institutionId, "%$q%", "%$q%", "%$q%"]
        );
        $rows = $this->db->fetchAll();
        $results = array_map(fn($r) => [
            'id'   => $r['id'],
            'text' => $r['text'] . ' (' . $r['enrollment_number'] . ')',
            'enrollment' => $r['enrollment_number'],
            'course' => $r['course_name'],
        ], $rows);
        $this->json(['results' => $results]);
    }

    // ── AJAX: get student assignments (for concession/refund modal) ──
    public function ajaxStudentAssignments(): void
    {
        $studentId = (int)($_GET['student_id'] ?? 0);
        if (!$studentId) { $this->json(['data' => []]); }

        $this->db->query(
            "SELECT fsa.id, fh.head_name, fsa.net_amount, fsa.balance_amount, fsa.status
             FROM fee_student_assignments fsa
             JOIN fee_heads fh ON fh.id = fsa.fee_head_id
             WHERE fsa.student_id = ? AND fsa.institution_id = ? AND fsa.status NOT IN ('paid','waived')
             ORDER BY fh.head_name",
            [$studentId, $this->institutionId]
        );
        $this->json(['data' => $this->db->fetchAll()]);
    }

    // ── AJAX: get structures for course ─────────────────────
    public function ajaxStructures(): void
    {
        $courseId = (int)$this->input('course_id', 0);
        $ayId     = (int)$this->input('academic_year_id', 0);
        $sql    = "SELECT fs.id, fs.name, fs.total_amount FROM fee_structures fs
             WHERE fs.institution_id = ? AND fs.course_id = ? AND fs.status='active'"
            . ($ayId ? " AND fs.academic_year_id = ?" : "")
            . " ORDER BY fs.name";
        $params = $ayId ? [$this->institutionId, $courseId, $ayId] : [$this->institutionId, $courseId];
        $this->db->query($sql, $params);
        $this->json(['status' => 'success', 'data' => $this->db->fetchAll()]);
    }
}
