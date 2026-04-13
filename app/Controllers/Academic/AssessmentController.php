<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class AssessmentController extends BaseController
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->db->query(
            "SELECT a.*, b.program_name, b.batch_term, s.subject_name, s.subject_code,
                    gs.name AS schema_name, gs.code AS schema_code
             FROM academic_assessments a
             JOIN academic_batches b ON a.batch_id = b.id
             JOIN subjects s ON a.subject_id = s.id
             LEFT JOIN grading_schemas gs ON gs.id = a.grading_schema_id
             WHERE a.institution_id = ?
             ORDER BY a.created_at DESC",
            [$this->institutionId]
        );
        $assessments = $this->db->fetchAll();

        $this->view('academic/assessments/index', compact('assessments'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->db->query(
            "SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id = ? AND status = 'active'",
            [$this->institutionId]
        );
        $batches = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, subject_code, subject_name FROM subjects WHERE institution_id = ? AND status = 'active'",
            [$this->institutionId]
        );
        $subjects = $this->db->fetchAll();

        $this->db->query(
            "SELECT gs.*,
                    (SELECT COUNT(*) FROM grading_mark_components WHERE schema_id = gs.id) AS component_count,
                    (SELECT COUNT(*) FROM grading_grade_rules       WHERE schema_id = gs.id) AS rule_count
             FROM grading_schemas gs
             WHERE gs.institution_id = ? AND gs.status = 'active'
             ORDER BY gs.code",
            [$this->institutionId]
        );
        $schemas = $this->db->fetchAll();

        $this->view('academic/assessments/create', compact('batches', 'subjects', 'schemas'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $batchId        = (int)($_POST['batch_id']        ?? 0);
        $subjectId      = (int)($_POST['subject_id']      ?? 0);
        $assessmentName = trim($_POST['assessment_name']  ?? '');
        $assessmentType = trim($_POST['assessment_type']  ?? 'internal');
        $maxMarks       = (float)($_POST['max_marks']     ?? 100);
        $passingMarks   = (float)($_POST['passing_marks'] ?? 40);
        $weightage      = (float)($_POST['weightage']     ?? 0);
        $assessmentDate = trim($_POST['assessment_date']  ?? '');
        $schemaId       = (int)($_POST['grading_schema_id'] ?? 0) ?: null;
        $evalMode       = trim($_POST['evaluation_mode']  ?? 'direct');
        $intMax         = ($evalMode === 'internal_external') ? (float)($_POST['internal_max_marks'] ?? 0) : null;
        $extMax         = ($evalMode === 'internal_external') ? (float)($_POST['external_max_marks'] ?? 0) : null;
        $intMin         = ($evalMode === 'internal_external') ? (float)($_POST['internal_min_marks'] ?? 0) : null;
        $extMin         = ($evalMode === 'internal_external') ? (float)($_POST['external_min_marks'] ?? 0) : null;

        if ($batchId <= 0 || $subjectId <= 0 || empty($assessmentName)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Batch, Subject, and Name are required.']);
            exit;
        }

        try {
            $this->db->insert('academic_assessments', [
                'institution_id'     => $this->institutionId,
                'batch_id'           => $batchId,
                'subject_id'         => $subjectId,
                'assessment_name'    => $assessmentName,
                'assessment_type'    => $assessmentType,
                'max_marks'          => $maxMarks,
                'passing_marks'      => $passingMarks,
                'weightage'          => $weightage,
                'assessment_date'    => empty($assessmentDate) ? null : $assessmentDate,
                'grading_schema_id'  => $schemaId,
                'evaluation_mode'    => $evalMode,
                'internal_max_marks' => $intMax,
                'external_max_marks' => $extMax,
                'internal_min_marks' => $intMin,
                'external_min_marks' => $extMin,
                'status'             => 'active',
                'created_by'         => $_SESSION['user_id'] ?? 1,
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Assessment created successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // MARKS ENTRY
    // ──────────────────────────────────────────────────────────────
    public function marks(): void
    {
        $assessmentId = (int)($_GET['id'] ?? 0);

        $this->db->query(
            "SELECT a.*, b.program_name, b.batch_term, s.subject_name, s.subject_code,
                    gs.name AS schema_name, gs.code AS schema_code, gs.max_mark AS schema_max
             FROM academic_assessments a
             JOIN academic_batches b ON a.batch_id = b.id
             JOIN subjects s ON a.subject_id = s.id
             LEFT JOIN grading_schemas gs ON gs.id = a.grading_schema_id
             WHERE a.id = ? AND a.institution_id = ?",
            [$assessmentId, $this->institutionId]
        );
        $assessment = $this->db->fetch();
        if (!$assessment) { die('Invalid Assessment'); }

        // Load grade rules for the linked schema (for client-side grade preview)
        $gradeRules = [];
        if ($assessment['grading_schema_id']) {
            $this->db->query(
                "SELECT grade_label, grade_point, min_percentage, max_percentage, is_pass
                 FROM grading_grade_rules WHERE schema_id = ? ORDER BY min_percentage DESC",
                [$assessment['grading_schema_id']]
            );
            $gradeRules = $this->db->fetchAll();
        }

        $this->db->query(
            "SELECT id, first_name, last_name, roll_number FROM students
             WHERE institution_id = ? AND status = 'active' ORDER BY roll_number ASC LIMIT 200",
            [$this->institutionId]
        );
        $students = $this->db->fetchAll();

        $records = [];
        $this->db->query(
            "SELECT student_id, marks_obtained, internal_marks, external_marks, consolidated_marks,
                    consolidated_percentage, grade_label, grade_point, is_pass, is_absent, remarks
             FROM academic_assessment_marks WHERE assessment_id = ?",
            [$assessmentId]
        );
        foreach ($this->db->fetchAll() as $r) {
            $records[$r['student_id']] = $r;
        }

        $this->view('academic/assessments/marks', compact('assessment', 'students', 'records', 'gradeRules'));
    }

    // ──────────────────────────────────────────────────────────────
    // STORE MARKS  (with grade computation)
    // ──────────────────────────────────────────────────────────────
    public function storeMarks(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $assessmentId = (int)($_POST['assessment_id'] ?? 0);
        if ($assessmentId <= 0) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Assessment']);
            exit;
        }

        // Load assessment to know mode + schema
        $this->db->query(
            "SELECT * FROM academic_assessments WHERE id = ? AND institution_id = ?",
            [$assessmentId, $this->institutionId]
        );
        $assessment = $this->db->fetch();
        if (!$assessment) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Assessment not found']);
            exit;
        }

        // Load grade rules for computation
        $gradeRules = [];
        if ($assessment['grading_schema_id']) {
            $this->db->query(
                "SELECT grade_label, grade_point, min_percentage, max_percentage, is_pass
                 FROM grading_grade_rules WHERE schema_id = ? ORDER BY min_percentage DESC",
                [$assessment['grading_schema_id']]
            );
            $gradeRules = $this->db->fetchAll();
        }

        $marks   = $_POST['marks']   ?? [];
        $intMarks= $_POST['int_marks'] ?? [];
        $extMarks= $_POST['ext_marks'] ?? [];
        $absents = $_POST['absents'] ?? [];
        $remarks = $_POST['remarks'] ?? [];
        $isIntExt = ($assessment['evaluation_mode'] === 'internal_external');

        try {
            $this->db->beginTransaction();
            $this->db->query("DELETE FROM academic_assessment_marks WHERE assessment_id = ?", [$assessmentId]);

            foreach (($isIntExt ? $intMarks : $marks) as $studentId => $val) {
                $isAbsent = isset($absents[$studentId]) ? 1 : 0;
                $remark   = $remarks[$studentId] ?? null;

                if ($isIntExt) {
                    $im = $isAbsent ? 0 : (float)($intMarks[$studentId] ?? 0);
                    $em = $isAbsent ? 0 : (float)($extMarks[$studentId] ?? 0);
                    $consolidated = $im + $em;
                    $maxMark = (float)$assessment['max_marks'];
                    $pct = $maxMark > 0 ? round(($consolidated / $maxMark) * 100, 2) : 0;
                    $marksObtained = $consolidated;
                } else {
                    $im = null;
                    $em = null;
                    $marksObtained = $isAbsent ? 0 : (float)$val;
                    $consolidated  = $marksObtained;
                    $maxMark = (float)$assessment['max_marks'];
                    $pct = $maxMark > 0 ? round(($consolidated / $maxMark) * 100, 2) : 0;
                }

                // Compute grade from rules
                $gradeLabel = null; $gradePoint = null; $isPass = null;
                if (!$isAbsent && !empty($gradeRules)) {
                    foreach ($gradeRules as $rule) {
                        if ($pct >= (float)$rule['min_percentage'] && $pct <= (float)$rule['max_percentage']) {
                            $gradeLabel = $rule['grade_label'];
                            $gradePoint = (float)$rule['grade_point'];
                            $isPass     = (int)$rule['is_pass'];
                            break;
                        }
                    }
                }

                $this->db->insert('academic_assessment_marks', [
                    'assessment_id'          => $assessmentId,
                    'student_id'             => (int)$studentId,
                    'institution_id'         => $this->institutionId,
                    'marks_obtained'         => $marksObtained,
                    'internal_marks'         => $im,
                    'external_marks'         => $em,
                    'consolidated_marks'     => $isIntExt ? $consolidated : null,
                    'consolidated_percentage'=> $pct,
                    'grade_label'            => $gradeLabel,
                    'grade_point'            => $gradePoint,
                    'is_pass'                => $isPass,
                    'is_absent'              => $isAbsent,
                    'remarks'                => $remark,
                    'entered_by'             => $_SESSION['user_id'] ?? 1,
                ]);
            }

            if (isset($_POST['finalize']) && $_POST['finalize'] == '1') {
                $this->db->query(
                    "UPDATE academic_assessments SET status = 'completed' WHERE id = ?",
                    [$assessmentId]
                );
            }

            $this->db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Marks saved successfully.']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save marks: ' . $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->db->query(
            "SELECT a.*, b.program_name, b.batch_term, s.subject_name
             FROM academic_assessments a
             JOIN academic_batches b ON a.batch_id = b.id
             JOIN subjects s ON a.subject_id = s.id
             WHERE a.id = ? AND a.institution_id = ?",
            [$id, $this->institutionId]
        );
        $assessment = $this->db->fetch();
        if (!$assessment) {
            $this->redirectWith(url('academic/assessments'), 'error', 'Assessment not found.');
            return;
        }

        $this->db->query("SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id = ? AND status = 'active'", [$this->institutionId]);
        $batches = $this->db->fetchAll();

        $this->db->query("SELECT id, subject_code, subject_name FROM subjects WHERE institution_id = ? AND status = 'active'", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->db->query("SELECT gs.*, (SELECT COUNT(*) FROM grading_mark_components WHERE schema_id = gs.id) AS component_count FROM grading_schemas gs WHERE gs.institution_id = ? AND gs.status = 'active' ORDER BY gs.code", [$this->institutionId]);
        $schemas = $this->db->fetchAll();

        $this->view('academic/assessments/edit', compact('assessment', 'batches', 'subjects', 'schemas'));
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $this->db->query("SELECT * FROM academic_assessments WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $assessment = $this->db->fetch();
        if (!$assessment || $assessment['status'] === 'published') {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Cannot update a published assessment.']);
            exit;
        }

        $batchId        = (int)($_POST['batch_id']        ?? 0);
        $subjectId      = (int)($_POST['subject_id']      ?? 0);
        $assessmentName = trim($_POST['assessment_name']  ?? '');
        $assessmentType = trim($_POST['assessment_type']  ?? 'internal');
        $maxMarks       = (float)($_POST['max_marks']     ?? 100);
        $passingMarks   = (float)($_POST['passing_marks'] ?? 40);
        $weightage      = (float)($_POST['weightage']     ?? 0);
        $assessmentDate = trim($_POST['assessment_date']  ?? '');
        $schemaId       = (int)($_POST['grading_schema_id'] ?? 0) ?: null;

        if ($batchId <= 0 || $subjectId <= 0 || empty($assessmentName)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Batch, Subject, and Name are required.']);
            exit;
        }

        try {
            $this->db->query(
                "UPDATE academic_assessments SET
                 batch_id=?, subject_id=?, assessment_name=?, assessment_type=?,
                 max_marks=?, passing_marks=?, weightage=?, assessment_date=?, grading_schema_id=?
                 WHERE id=? AND institution_id=?",
                [$batchId, $subjectId, $assessmentName, $assessmentType,
                 $maxMarks, $passingMarks, $weightage,
                 empty($assessmentDate) ? null : $assessmentDate, $schemaId,
                 $id, $this->institutionId]
            );
            echo json_encode(['status' => 'success', 'message' => 'Assessment updated.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // PUBLISH / LOCK MARKS
    // ──────────────────────────────────────────────────────────────
    public function publish(int $id): void
    {
        $this->db->query(
            "SELECT * FROM academic_assessments WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $assessment = $this->db->fetch();
        if (!$assessment) {
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }

        // Check marks exist
        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM academic_assessment_marks WHERE assessment_id = ?",
            [$id]
        );
        $marksCount = ($this->db->fetch()['cnt'] ?? 0);
        if ($marksCount === 0) {
            echo json_encode(['status' => 'error', 'message' => 'No marks entered yet. Enter marks before publishing.']);
            exit;
        }

        $newStatus = ($assessment['status'] === 'published') ? 'completed' : 'published';
        $this->db->query(
            "UPDATE academic_assessments SET status = ? WHERE id = ?",
            [$newStatus, $id]
        );
        $this->logAudit('assessment_publish', 'academic_assessments', $id);
        echo json_encode([
            'status'  => 'success',
            'new_status' => $newStatus,
            'message' => $newStatus === 'published' ? 'Marks published & locked.' : 'Marks unlocked.',
        ]);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->db->query(
            "SELECT status FROM academic_assessments WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $a = $this->db->fetch();
        if (!$a) {
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }
        if ($a['status'] === 'published') {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete a published assessment.']);
            exit;
        }

        try {
            $this->db->beginTransaction();
            $this->db->query("DELETE FROM academic_assessment_marks WHERE assessment_id = ?", [$id]);
            $this->db->query("DELETE FROM academic_assessments WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
            $this->db->commit();
            $this->logAudit('assessment_delete', 'academic_assessments', $id);
            echo json_encode(['status' => 'success', 'message' => 'Assessment deleted.']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW (results/report)
    // ──────────────────────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->db->query(
            "SELECT a.*, b.program_name, b.batch_term, s.subject_name, s.subject_code,
                    gs.name AS schema_name, gs.code AS schema_code
             FROM academic_assessments a
             JOIN academic_batches b ON a.batch_id = b.id
             JOIN subjects s ON a.subject_id = s.id
             LEFT JOIN grading_schemas gs ON gs.id = a.grading_schema_id
             WHERE a.id = ? AND a.institution_id = ?",
            [$id, $this->institutionId]
        );
        $assessment = $this->db->fetch();
        if (!$assessment) {
            $this->redirectWith(url('academic/assessments'), 'error', 'Assessment not found.');
            return;
        }

        // Marks with student info
        $this->db->query(
            "SELECT am.*, CONCAT(st.first_name,' ',st.last_name) AS student_name, st.roll_number
             FROM academic_assessment_marks am
             JOIN students st ON st.id = am.student_id
             WHERE am.assessment_id = ?
             ORDER BY st.roll_number ASC",
            [$id]
        );
        $marks = $this->db->fetchAll();

        // Summary stats
        $marksArr = array_filter(array_column($marks, 'marks_obtained'), fn($v) => $v !== null);
        $summary = [
            'total_students' => count($marks),
            'present'        => count(array_filter($marks, fn($m) => !$m['is_absent'])),
            'absent'         => count(array_filter($marks, fn($m) => $m['is_absent'])),
            'pass'           => count(array_filter($marks, fn($m) => $m['is_pass'])),
            'fail'           => count(array_filter($marks, fn($m) => !$m['is_absent'] && !$m['is_pass'])),
            'avg'            => count($marksArr) > 0 ? round(array_sum($marksArr) / count($marksArr), 2) : 0,
            'highest'        => count($marksArr) > 0 ? max($marksArr) : 0,
            'lowest'         => count($marksArr) > 0 ? min($marksArr) : 0,
        ];

        $this->view('academic/assessments/show', compact('assessment', 'marks', 'summary'));
    }
}
