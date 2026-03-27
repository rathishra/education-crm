<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class BatchController extends BaseController {
    
    public function index(): void {
        $this->db->query("SELECT * FROM academic_batches WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $batches = $this->db->fetchAll();
        $this->view('academic/batches/index', compact('batches'));
    }

    public function create(): void {
        $this->view('academic/batches/create');
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $program_name                = trim($_POST['program_name'] ?? '');
        $batch_term                  = trim($_POST['batch_term'] ?? '');
        $start_date                  = trim($_POST['start_date'] ?? '');
        $end_date                    = trim($_POST['end_date'] ?? null);
        $max_intake                  = (int)($_POST['max_intake'] ?? 60);
        $graduation_credits_required = (float)($_POST['graduation_credits'] ?? 0.0);
        $total_semesters             = (int)($_POST['total_semesters'] ?? 8);

        if (empty($program_name) || empty($batch_term) || empty($start_date)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Program Name, Term, and Start Date are required']);
            exit;
        }

        try {
            $this->db->insert('academic_batches', [
                'institution_id'              => $this->institutionId,
                'program_name'                => $program_name,
                'batch_term'                  => $batch_term,
                'start_date'                  => $start_date,
                'end_date'                    => empty($end_date) ? null : $end_date,
                'max_intake'                  => $max_intake,
                'graduation_credits_required' => $graduation_credits_required,
                'total_semesters'             => $total_semesters,
                'status'                      => 'active',
                'created_by'                  => $_SESSION['user_id'] ?? 1
            ]);
            
            echo json_encode(['status' => 'success', 'message' => 'Batch created successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create batch: ' . $e->getMessage()]);
        }
        exit;
    }
}
