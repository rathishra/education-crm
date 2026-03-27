<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class ClassroomController extends BaseController {

    public function index(): void {
        $this->db->query("SELECT * FROM classrooms WHERE institution_id = ? ORDER BY room_number ASC", [$this->institutionId]);
        $classrooms = $this->db->fetchAll();
        $this->view('academic/classrooms/index', compact('classrooms'));
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $room_number = trim($_POST['room_number'] ?? '');
        $room_name   = trim($_POST['room_name'] ?? '');
        $capacity    = (int)($_POST['capacity'] ?? 60);
        $is_active   = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if (empty($room_number)) $errors['room_number'] = "Room Number is required.";
        if ($capacity < 1) $errors['capacity'] = "Capacity must be greater than 0.";

        $this->db->query("SELECT id FROM classrooms WHERE institution_id = ? AND room_number = ?", [$this->institutionId, $room_number]);
        if ($this->db->fetch()) {
            $errors['room_number'] = "This Room Number already exists in your institution.";
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        try {
            $this->db->insert('classrooms', [
                'institution_id' => $this->institutionId,
                'room_number'    => $room_number,
                'room_name'      => $room_name,
                'capacity'       => $capacity,
                'is_active'      => $is_active
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Classroom added successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to add classroom.']);
        }
        exit;
    }
}
