<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class ClassroomController extends BaseController
{
    private function ensureSchema(): void
    {
        $extras = [
            'room_type'   => "ENUM('classroom','lab','seminar_hall','auditorium','library','staff_room','office','other') NOT NULL DEFAULT 'classroom' AFTER room_name",
            'floor'       => "VARCHAR(20) NULL AFTER room_type",
            'location'    => "VARCHAR(100) NULL AFTER floor",
            'amenities'   => "TEXT NULL AFTER location",
            'description' => "TEXT NULL AFTER amenities",
        ];
        foreach ($extras as $col => $def) {
            try {
                $this->db->query("SHOW COLUMNS FROM classrooms LIKE '$col'");
                if (!$this->db->fetch()) {
                    $this->db->query("ALTER TABLE classrooms ADD COLUMN $col $def");
                }
            } catch (\Exception $e) {}
        }
    }

    // ─── INDEX ─────────────────────────────────────────────────
    public function index(): void
    {
        $this->ensureSchema();

        $search       = trim($_GET['q'] ?? '');
        $typeFilter   = trim($_GET['room_type'] ?? '');
        $statusFilter = $_GET['status'] ?? '';

        $where  = "institution_id = ?";
        $params = [$this->institutionId];

        if ($search) {
            $where   .= " AND (room_number LIKE ? OR room_name LIKE ? OR location LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($typeFilter) {
            $where   .= " AND room_type = ?";
            $params[] = $typeFilter;
        }
        if ($statusFilter !== '') {
            $where   .= " AND is_active = ?";
            $params[] = (int)$statusFilter;
        }

        $this->db->query("SELECT * FROM classrooms WHERE $where ORDER BY room_number ASC", $params);
        $classrooms = $this->db->fetchAll();

        // Summary stats
        $this->db->query(
            "SELECT COUNT(*) AS total, COALESCE(SUM(is_active),0) AS active,
                    COALESCE(SUM(capacity),0) AS total_capacity
             FROM classrooms WHERE institution_id = ?",
            [$this->institutionId]
        );
        $stats = $this->db->fetch() ?: ['total' => 0, 'active' => 0, 'total_capacity' => 0];

        // Type breakdown
        $this->db->query(
            "SELECT room_type, COUNT(*) AS cnt FROM classrooms WHERE institution_id = ? GROUP BY room_type ORDER BY cnt DESC",
            [$this->institutionId]
        );
        $typeStats = [];
        foreach ($this->db->fetchAll() as $r) {
            $typeStats[$r['room_type']] = $r['cnt'];
        }

        $this->view('academic/classrooms/index', compact(
            'classrooms', 'stats', 'typeStats', 'search', 'typeFilter', 'statusFilter'
        ));
    }

    // ─── STORE (AJAX POST) ─────────────────────────────────────
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $room_number = trim($_POST['room_number'] ?? '');
        $room_name   = trim($_POST['room_name']   ?? '');
        $room_type   = trim($_POST['room_type']   ?? 'classroom');
        $capacity    = max(1, (int)($_POST['capacity'] ?? 60));
        $floor       = trim($_POST['floor']       ?? '') ?: null;
        $location    = trim($_POST['location']    ?? '') ?: null;
        $amenities   = trim($_POST['amenities']   ?? '') ?: null;
        $description = trim($_POST['description'] ?? '') ?: null;
        $is_active   = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if (empty($room_number)) $errors['room_number'] = 'Room Number is required.';
        if ($capacity < 1)       $errors['capacity']    = 'Capacity must be at least 1.';

        if (empty($errors)) {
            $this->db->query(
                "SELECT id FROM classrooms WHERE institution_id = ? AND room_number = ?",
                [$this->institutionId, $room_number]
            );
            if ($this->db->fetch()) {
                $errors['room_number'] = 'This Room Number already exists.';
            }
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
                'room_type'      => $room_type,
                'capacity'       => $capacity,
                'floor'          => $floor,
                'location'       => $location,
                'amenities'      => $amenities,
                'description'    => $description,
                'is_active'      => $is_active,
            ]);
            $this->logAudit('classroom_create', 'classrooms', $this->db->lastInsertId());
            echo json_encode(['status' => 'success', 'message' => 'Classroom added successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ─── GET SINGLE (AJAX for edit modal) ─────────────────────
    public function getOne(int $id): void
    {
        $this->db->query(
            "SELECT * FROM classrooms WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $room = $this->db->fetch();
        if (!$room) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $room]);
        exit;
    }

    // ─── UPDATE (AJAX POST) ────────────────────────────────────
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
            exit;
        }

        $this->db->query(
            "SELECT id FROM classrooms WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }

        $room_number = trim($_POST['room_number'] ?? '');
        $room_name   = trim($_POST['room_name']   ?? '');
        $room_type   = trim($_POST['room_type']   ?? 'classroom');
        $capacity    = max(1, (int)($_POST['capacity'] ?? 60));
        $floor       = trim($_POST['floor']       ?? '') ?: null;
        $location    = trim($_POST['location']    ?? '') ?: null;
        $amenities   = trim($_POST['amenities']   ?? '') ?: null;
        $description = trim($_POST['description'] ?? '') ?: null;
        $is_active   = isset($_POST['is_active']) ? 1 : 0;

        $errors = [];
        if (empty($room_number)) $errors['room_number'] = 'Room Number is required.';

        if (empty($errors)) {
            $this->db->query(
                "SELECT id FROM classrooms WHERE institution_id = ? AND room_number = ? AND id != ?",
                [$this->institutionId, $room_number, $id]
            );
            if ($this->db->fetch()) {
                $errors['room_number'] = 'This Room Number already exists.';
            }
        }

        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'errors' => $errors]);
            exit;
        }

        try {
            $this->db->query(
                "UPDATE classrooms SET room_number=?, room_name=?, room_type=?, capacity=?,
                 floor=?, location=?, amenities=?, description=?, is_active=?
                 WHERE id=? AND institution_id=?",
                [$room_number, $room_name, $room_type, $capacity,
                 $floor, $location, $amenities, $description, $is_active,
                 $id, $this->institutionId]
            );
            $this->logAudit('classroom_update', 'classrooms', $id);
            echo json_encode(['status' => 'success', 'message' => 'Classroom updated successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        exit;
    }

    // ─── TOGGLE ACTIVE (AJAX) ─────────────────────────────────
    public function toggle(int $id): void
    {
        $this->db->query(
            "SELECT is_active FROM classrooms WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $room = $this->db->fetch();
        if (!$room) {
            echo json_encode(['status' => 'error', 'message' => 'Not found.']);
            exit;
        }
        $newStatus = $room['is_active'] ? 0 : 1;
        $this->db->query("UPDATE classrooms SET is_active = ? WHERE id = ?", [$newStatus, $id]);
        echo json_encode(['status' => 'success', 'is_active' => $newStatus]);
        exit;
    }

    // ─── DESTROY ──────────────────────────────────────────────
    public function destroy(int $id): void
    {
        // Check usage in timetable
        try {
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM academic_timetable WHERE classroom_id = ? AND institution_id = ?",
                [$id, $this->institutionId]
            );
            $used = ($this->db->fetch()['cnt'] ?? 0);
        } catch (\Exception $e) {
            $used = 0;
        }

        if ($used > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete: Room is used in timetable.']);
            exit;
        }

        $this->db->query(
            "DELETE FROM classrooms WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->logAudit('classroom_delete', 'classrooms', $id);
        echo json_encode(['status' => 'success', 'message' => 'Classroom deleted.']);
        exit;
    }
}
