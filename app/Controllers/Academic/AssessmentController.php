<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class AssessmentController extends BaseController {
    
    public function index(): void {
        $sql = "SELECT a.*, b.program_name, b.batch_term, s.subject_name, s.subject_code 
                FROM academic_assessments a 
                JOIN academic_batches b ON a.batch_id = b.id 
                JOIN subjects s ON a.subject_id = s.id 
                WHERE a.institution_id = ? 
                ORDER BY a.created_at DESC";
        $this->db->query($sql, [$this->institutionId]);
        $assessments = $this->db->fetchAll();

        $this->view('academic/assessments/index', compact('assessments'));
    }

    public function create(): void {
        $this->db->query("SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id = ? AND status = 'active'", [$this->institutionId]);
        $batches = $this->db->fetchAll();

        $this->db->query("SELECT id, subject_code, subject_name FROM subjects WHERE institution_id = ? AND status = 'active'", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->view('academic/assessments/create', compact('batches', 'subjects'));
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $batch_id        = (int)($_POST['batch_id'] ?? 0);
        $subject_id      = (int)($_POST['subject_id'] ?? 0);
        $assessment_name = trim($_POST['assessment_name'] ?? '');
        $assessment_type = trim($_POST['assessment_type'] ?? 'internal');
        $max_marks       = (float)($_POST['max_marks'] ?? 100);
        $passing_marks   = (float)($_POST['passing_marks'] ?? 40);
        $weightage       = (float)($_POST['weightage'] ?? 0);
        $assessment_date = trim($_POST['assessment_date'] ?? '');

        if ($batch_id <= 0 || $subject_id <= 0 || empty($assessment_name)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Batch, Subject, and Assessment Name are required']);
            exit;
        }

        try {
            $this->db->insert('academic_assessments', [
                'institution_id'  => $this->institutionId,
                'batch_id'        => $batch_id,
                'subject_id'      => $subject_id,
                'assessment_name' => $assessment_name,
                'assessment_type' => $assessment_type,
                'max_marks'       => $max_marks,
                'passing_marks'   => $passing_marks,
                'weightage'       => $weightage,
                'assessment_date' => empty($assessment_date) ? null : $assessment_date,
                'status'          => 'active',
                'created_by'      => $_SESSION['user_id'] ?? 1
            ]);
            
            echo json_encode(['status' => 'success', 'message' => 'Assessment created successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create assessment: ' . $e->getMessage()]);
        }
        exit;
    }

    public function marks(): void {
        $assessment_id = (int)($_GET['id'] ?? 0);
        
        $sql = "SELECT a.*, b.program_name, b.batch_term, s.subject_name, s.subject_code 
                FROM academic_assessments a 
                JOIN academic_batches b ON a.batch_id = b.id 
                JOIN subjects s ON a.subject_id = s.id 
                WHERE a.id = ? AND a.institution_id = ?";
        $this->db->query($sql, [$assessment_id, $this->institutionId]);
        $assessment = $this->db->fetch();
        
        if (!$assessment) {
            die("Invalid Assessment");
        }

        // Get matching active students 
        // (Assuming standard students table - if using academic sections later, join appropriately)
        $this->db->query("SELECT id, first_name, last_name, roll_number FROM students WHERE institution_id = ? AND status = 'active' ORDER BY roll_number ASC LIMIT 50", [$this->institutionId]);
        $students = $this->db->fetchAll();

        // Get existing marks
        $records = [];
        $this->db->query("SELECT student_id, marks_obtained, is_absent, remarks FROM academic_assessment_marks WHERE assessment_id = ?", [$assessment_id]);
        foreach($this->db->fetchAll() as $r) {
            $records[$r['student_id']] = $r;
        }

        $this->view('academic/assessments/marks', compact('assessment', 'students', 'records'));
    }

    public function storeMarks(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $assessment_id = (int)($_POST['assessment_id'] ?? 0);
        $marks         = $_POST['marks'] ?? [];
        $absents       = $_POST['absents'] ?? [];
        $remarks       = $_POST['remarks'] ?? [];

        if ($assessment_id <= 0) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Assessment']);
            exit;
        }

        try {
            $this->db->beginTransaction();
            
            // Delete old marks
            $this->db->query("DELETE FROM academic_assessment_marks WHERE assessment_id = ?", [$assessment_id]);

            // Setup inserts
            if (!empty($marks) && is_array($marks)) {
                foreach ($marks as $student_id => $val) {
                    $is_absent = isset($absents[$student_id]) ? 1 : 0;
                    $m         = $is_absent ? 0 : (float)$val;
                    $remark    = $remarks[$student_id] ?? null;

                    $this->db->insert('academic_assessment_marks', [
                        'assessment_id'  => $assessment_id,
                        'student_id'     => $student_id,
                        'institution_id' => $this->institutionId,
                        'marks_obtained' => $m,
                        'is_absent'      => $is_absent,
                        'remarks'        => $remark,
                        'entered_by'     => $_SESSION['user_id'] ?? 1
                    ]);
                }
            }
            
            // Mark assessment as completed if requested
            if(isset($_POST['finalize']) && $_POST['finalize'] == '1') {
                $this->db->query("UPDATE academic_assessments SET status = 'completed' WHERE id = ?", [$assessment_id]);
            }

            $this->db->commit();
            echo json_encode(['status' => 'success', 'message' => 'Marks saved successfully']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save marks: ' . $e->getMessage()]);
        }
        exit;
    }
}
