<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class SectionController extends BaseController {
    
    public function index(): void {
        $sql = "SELECT s.*, b.program_name, b.batch_term, c.room_number 
                FROM academic_sections s 
                JOIN academic_batches b ON s.batch_id = b.id 
                LEFT JOIN classrooms c ON s.default_classroom_id = c.id
                WHERE s.institution_id = ? 
                ORDER BY b.program_name ASC, s.section_name ASC";
                
        $this->db->query($sql, [$this->institutionId]);
        $sections = $this->db->fetchAll();
        $this->view('academic/sections/index', compact('sections'));
    }

    public function create(): void {
        $this->db->query("SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id = ? AND status = 'active' ORDER BY start_date DESC", [$this->institutionId]);
        $batches = $this->db->fetchAll();
        
        $this->db->query("SELECT id, room_number, room_name FROM classrooms WHERE institution_id = ? AND is_active = 1", [$this->institutionId]);
        $classrooms = $this->db->fetchAll();

        $this->view('academic/sections/create', compact('batches', 'classrooms'));
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $batch_id     = (int)($_POST['batch_id'] ?? 0);
        $section_name = trim($_POST['section_name'] ?? '');
        $classroom_id = (int)($_POST['default_classroom_id'] ?? 0);
        $capacity     = (int)($_POST['capacity'] ?? 30);

        if ($batch_id <= 0 || empty($section_name)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Batch and Section Name are required']);
            exit;
        }

        try {
            $this->db->insert('academic_sections', [
                'institution_id'       => $this->institutionId,
                'batch_id'             => $batch_id,
                'section_name'         => $section_name,
                'default_classroom_id' => $classroom_id > 0 ? $classroom_id : null,
                'capacity'             => $capacity,
                'status'               => 'active',
                'created_by'           => $_SESSION['user_id'] ?? 1
            ]);
            
            echo json_encode(['status' => 'success', 'message' => 'Section created successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create section: ' . $e->getMessage()]);
        }
        exit;
    }
}
