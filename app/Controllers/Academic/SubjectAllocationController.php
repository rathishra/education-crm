<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

/**
 * Subject Allocation
 * Shows which subjects are assigned to which batches (batch_subjects table).
 * Complements FacultyAllocationController (faculty → subject → batch).
 */
class SubjectAllocationController extends BaseController
{
    private function ensureSchema(): void
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS batch_subjects (
                id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                institution_id INT UNSIGNED NOT NULL,
                batch_id       INT UNSIGNED NOT NULL,
                subject_id     INT UNSIGNED NOT NULL,
                semester       TINYINT UNSIGNED NULL,
                is_compulsory  TINYINT(1) NOT NULL DEFAULT 1,
                credits        DECIMAL(4,1) NULL,
                max_marks      SMALLINT UNSIGNED NULL DEFAULT 100,
                passing_marks  SMALLINT UNSIGNED NULL DEFAULT 40,
                created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_batch_subject (batch_id, subject_id),
                INDEX idx_institution (institution_id),
                INDEX idx_batch (batch_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {}
    }

    // ─── INDEX ─────────────────────────────────────────────────
    public function index(): void
    {
        $this->ensureSchema();

        $batchId  = (int)($_GET['batch_id']  ?? 0);
        $semester = (int)($_GET['semester']  ?? 0);

        $this->db->query(
            "SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id=? AND status='active' ORDER BY program_name",
            [$this->institutionId]
        );
        $batches = $this->db->fetchAll();

        // Build subject list for selected batch (or all)
        if ($batchId) {
            $this->db->query(
                "SELECT bs.*, s.subject_code, s.subject_name, s.subject_type, s.is_elective,
                        COALESCE(bs.credits, s.credits) AS eff_credits,
                        (SELECT COUNT(*) FROM faculty_subject_allocations fa
                         WHERE fa.subject_id=s.id AND fa.batch_id=? AND fa.institution_id=? AND fa.status='active') AS faculty_count
                 FROM batch_subjects bs
                 JOIN subjects s ON s.id = bs.subject_id
                 WHERE bs.batch_id=? AND bs.institution_id=?"
                    . ($semester ? " AND bs.semester=?" : "")
                    . " ORDER BY COALESCE(bs.semester,99), s.subject_code",
                $semester
                    ? [$batchId, $this->institutionId, $batchId, $this->institutionId, $semester]
                    : [$batchId, $this->institutionId, $batchId, $this->institutionId]
            );
            $allocated = $this->db->fetchAll();

            // Subjects NOT yet allocated to this batch
            $allocatedIds = array_column($allocated, 'subject_id');
            $placeholders = $allocatedIds ? implode(',', array_fill(0, count($allocatedIds), '?')) : '0';
            $this->db->query(
                "SELECT id, subject_code, subject_name, subject_type, credits, is_elective, semester
                 FROM subjects WHERE institution_id=? AND status='active' AND deleted_at IS NULL
                 AND id NOT IN ($placeholders) ORDER BY subject_code",
                array_merge([$this->institutionId], $allocatedIds)
            );
            $available = $this->db->fetchAll();

            // Current batch info
            $this->db->query("SELECT * FROM academic_batches WHERE id=? AND institution_id=?", [$batchId, $this->institutionId]);
            $currentBatch = $this->db->fetch();

            // Semester list for this batch
            $this->db->query("SELECT DISTINCT semester FROM batch_subjects WHERE batch_id=? AND semester IS NOT NULL ORDER BY semester", [$batchId]);
            $semesters = array_column($this->db->fetchAll(), 'semester');
        } else {
            $allocated    = [];
            $available    = [];
            $currentBatch = null;
            $semesters    = [];
        }

        $this->view('academic/subject-allocation/index', compact(
            'batches', 'allocated', 'available', 'currentBatch',
            'batchId', 'semester', 'semesters'
        ));
    }

    // ─── ASSIGN (AJAX POST) ───────────────────────────────────
    public function assign(): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $batchId    = (int)($_POST['batch_id']    ?? 0);
        $subjectId  = (int)($_POST['subject_id']  ?? 0);
        $semester   = (int)($_POST['semester']    ?? 0) ?: null;
        $compulsory = isset($_POST['is_compulsory']) ? 1 : 0;
        $credits    = trim($_POST['credits'] ?? '') !== '' ? (float)$_POST['credits'] : null;
        $maxMarks   = (int)($_POST['max_marks']    ?? 100);
        $passMarks  = (int)($_POST['passing_marks'] ?? 40);

        if (!$batchId || !$subjectId) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Batch and Subject are required.']);
            exit;
        }

        // Verify batch belongs to institution
        $this->db->query("SELECT id FROM academic_batches WHERE id=? AND institution_id=?", [$batchId, $this->institutionId]);
        if (!$this->db->fetch()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Invalid batch.']);
            exit;
        }

        // Check duplicate
        $this->db->query(
            "SELECT id FROM batch_subjects WHERE batch_id=? AND subject_id=? AND institution_id=?",
            [$batchId, $subjectId, $this->institutionId]
        );
        if ($this->db->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Subject is already assigned to this batch.']);
            exit;
        }

        try {
            $this->db->insert('batch_subjects', [
                'institution_id' => $this->institutionId,
                'batch_id'       => $batchId,
                'subject_id'     => $subjectId,
                'semester'       => $semester,
                'is_compulsory'  => $compulsory,
                'credits'        => $credits,
                'max_marks'      => $maxMarks,
                'passing_marks'  => $passMarks,
            ]);
            $this->logAudit('subject_allocation_assign', 'batch_subjects', $this->db->lastInsertId());
            echo json_encode(['status' => 'success', 'message' => 'Subject assigned to batch.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ─── REMOVE (AJAX POST) ───────────────────────────────────
    public function remove(int $id): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        $this->db->query(
            "SELECT id FROM batch_subjects WHERE id=? AND institution_id=?",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }

        $this->db->query(
            "DELETE FROM batch_subjects WHERE id=? AND institution_id=?",
            [$id, $this->institutionId]
        );
        $this->logAudit('subject_allocation_remove', 'batch_subjects', $id);
        echo json_encode(['status' => 'success', 'message' => 'Subject removed from batch.']);
        exit;
    }

    // ─── UPDATE (inline edit) ─────────────────────────────────
    public function updateRow(int $id): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid.']);
            exit;
        }

        $this->db->query(
            "SELECT id FROM batch_subjects WHERE id=? AND institution_id=?",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }

        $semester   = (int)($_POST['semester']    ?? 0) ?: null;
        $compulsory = isset($_POST['is_compulsory']) ? 1 : 0;
        $credits    = trim($_POST['credits'] ?? '') !== '' ? (float)$_POST['credits'] : null;
        $maxMarks   = (int)($_POST['max_marks']    ?? 100);
        $passMarks  = (int)($_POST['passing_marks'] ?? 40);

        $this->db->query(
            "UPDATE batch_subjects SET semester=?, is_compulsory=?, credits=?, max_marks=?, passing_marks=? WHERE id=?",
            [$semester, $compulsory, $credits, $maxMarks, $passMarks, $id]
        );
        echo json_encode(['status' => 'success', 'message' => 'Updated.']);
        exit;
    }

    // ─── BULK ASSIGN (copy subjects from another batch) ───────
    public function bulkCopy(): void
    {
        if (!verifyCsrf()) { jsonResponse(['success' => false, 'message' => 'Session expired.'], 403); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid.']);
            exit;
        }

        $sourceBatchId = (int)($_POST['source_batch_id'] ?? 0);
        $targetBatchId = (int)($_POST['target_batch_id'] ?? 0);

        if (!$sourceBatchId || !$targetBatchId || $sourceBatchId === $targetBatchId) {
            echo json_encode(['status' => 'error', 'message' => 'Valid source and target batches are required.']);
            exit;
        }

        // Verify both batches belong to institution
        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM academic_batches WHERE id IN (?,?) AND institution_id=?",
            [$sourceBatchId, $targetBatchId, $this->institutionId]
        );
        if (($this->db->fetch()['cnt'] ?? 0) < 2) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid batch selection.']);
            exit;
        }

        $this->db->query(
            "SELECT * FROM batch_subjects WHERE batch_id=? AND institution_id=?",
            [$sourceBatchId, $this->institutionId]
        );
        $sourceSubjects = $this->db->fetchAll();

        $copied = 0;
        $skipped = 0;
        foreach ($sourceSubjects as $ss) {
            // Check not already in target
            $this->db->query(
                "SELECT id FROM batch_subjects WHERE batch_id=? AND subject_id=? AND institution_id=?",
                [$targetBatchId, $ss['subject_id'], $this->institutionId]
            );
            if ($this->db->fetch()) { $skipped++; continue; }

            try {
                $this->db->insert('batch_subjects', [
                    'institution_id' => $this->institutionId,
                    'batch_id'       => $targetBatchId,
                    'subject_id'     => $ss['subject_id'],
                    'semester'       => $ss['semester'],
                    'is_compulsory'  => $ss['is_compulsory'],
                    'credits'        => $ss['credits'],
                    'max_marks'      => $ss['max_marks'],
                    'passing_marks'  => $ss['passing_marks'],
                ]);
                $copied++;
            } catch (\Exception $e) { $skipped++; }
        }

        echo json_encode([
            'status'  => 'success',
            'message' => "Copied $copied subject(s). Skipped $skipped (already exist).",
        ]);
        exit;
    }
}
