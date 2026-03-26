<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class HostelController extends BaseController
{
    public function index(): void
    {
        $this->authorize('hostel.view');

        $institutionId = session('institution_id');
        $hostels = db()->query("
            SELECT h.*, 
                   COUNT(r.id) as total_rooms,
                   SUM(r.capacity) as total_capacity,
                   SUM(r.available_beds) as total_available_beds
            FROM hostels h
            LEFT JOIN hostel_rooms r ON r.hostel_id = h.id
            WHERE h.institution_id = ?
            GROUP BY h.id
            ORDER BY h.name
        ", [$institutionId])->fetchAll();

        $this->view('hostel/index', compact('hostels'));
    }

    public function store(): void
    {
        $this->authorize('hostel.manage');
        
        $data = $this->postData();
        $errors = $this->validate($data, [
            'name' => 'required',
            'type' => 'required'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $id = db()->insert('hostels', [
            'institution_id' => session('institution_id'),
            'name'           => sanitize($data['name']),
            'type'           => $data['type'],
            'warden_name'    => sanitize($data['warden_name'] ?? ''),
            'warden_phone'   => sanitize($data['warden_phone'] ?? ''),
            'status'         => $data['status'] ?? 'active'
        ]);

        $this->logAudit('hostel_created', 'hostel', $id);
        $this->redirectWith('hostels', 'Hostel added successfully.', 'success');
    }

    public function rooms(int $hostelId): void
    {
        $this->authorize('hostel.view');
        
        $hostel = db()->query("SELECT * FROM hostels WHERE id = ? AND institution_id = ?", [$hostelId, session('institution_id')])->fetch();
        if (!$hostel) {
            $this->redirectWith('hostels', 'Hostel not found.', 'error');
            return;
        }

        $rooms = db()->query("SELECT * FROM hostel_rooms WHERE hostel_id = ? ORDER BY room_number", [$hostelId])->fetchAll();

        $this->view('hostel/rooms', compact('hostel', 'rooms'));
    }

    public function storeRoom(int $hostelId): void
    {
        $this->authorize('hostel.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'room_number' => 'required',
            'capacity' => 'required|numeric'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $capacity = (int)$data['capacity'];

        $id = db()->insert('hostel_rooms', [
            'hostel_id'      => $hostelId,
            'room_number'    => sanitize($data['room_number']),
            'floor'          => sanitize($data['floor'] ?? ''),
            'capacity'       => $capacity,
            'available_beds' => $capacity,
            'status'         => $data['status'] ?? 'active'
        ]);

        $this->logAudit('hostel_room_added', 'hostel', $hostelId, ['room_id' => $id]);
        $this->redirectWith("hostels/{$hostelId}/rooms", 'Room added successfully.', 'success');
    }

    public function allocations(): void
    {
        $this->authorize('hostel.allocate');

        $institutionId = session('institution_id');
        $academicYearId = session('academic_year_id');

        $allocations = db()->query("
            SELECT ha.*, 
                   s.first_name, s.last_name, s.student_id_number,
                   hr.room_number, h.name as hostel_name
            FROM hostel_allocations ha
            JOIN students s ON s.id = ha.student_id
            JOIN hostel_rooms hr ON hr.id = ha.hostel_room_id
            JOIN hostels h ON h.id = hr.hostel_id
            WHERE h.institution_id = ? AND ha.academic_year_id = ?
            ORDER BY ha.created_at DESC
        ", [$institutionId, $academicYearId])->fetchAll();

        $hostels = db()->query("SELECT * FROM hostels WHERE institution_id = ? AND status='active'", [$institutionId])->fetchAll();

        $this->view('hostel/allocations', compact('allocations', 'hostels'));
    }

    public function createAllocation(): void
    {
        $this->authorize('hostel.allocate');
        
        $data = $this->postData();
        $institutionId = session('institution_id');
        $academicYearId = session('academic_year_id');

        $errors = $this->validate($data, [
            'student_id' => 'required|numeric',
            'hostel_room_id' => 'required|numeric',
            'start_date' => 'required|date'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $roomId = (int)$data['hostel_room_id'];
        
        // Ensure room has available beds
        $room = db()->query("SELECT available_beds FROM hostel_rooms WHERE id = ?", [$roomId])->fetch();
        if (!$room || $room['available_beds'] <= 0) {
            $this->backWithErrors(['error' => 'Selected room has no available beds.']);
            return;
        }

        // Subtract 1 available bed
        db()->query("UPDATE hostel_rooms SET available_beds = available_beds - 1 WHERE id = ?", [$roomId]);

        $id = db()->insert('hostel_allocations', [
            'student_id'       => $data['student_id'],
            'hostel_room_id'   => $roomId,
            'academic_year_id' => $academicYearId,
            'start_date'       => $data['start_date'],
            'status'           => 'active'
        ]);

        $this->logAudit('hostel_allocated', 'hostel_allocation', $id);
        $this->redirectWith('hostels/allocations', 'Room allocated successfully.', 'success');
    }
}
