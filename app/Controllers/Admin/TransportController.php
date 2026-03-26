<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TransportController extends BaseController
{
    public function index(): void
    {
        $this->authorize('transport.view');

        $institutionId = session('institution_id');
        $routes = db()->query("
            SELECT r.*, COUNT(s.id) as total_stops
            FROM transport_routes r
            LEFT JOIN transport_stops s ON s.route_id = r.id
            WHERE r.institution_id = ?
            GROUP BY r.id
            ORDER BY r.name
        ", [$institutionId])->fetchAll();

        $this->view('transport/index', compact('routes'));
    }

    public function store(): void
    {
        $this->authorize('transport.manage');
        
        $data = $this->postData();
        $errors = $this->validate($data, [
            'name' => 'required'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $id = db()->insert('transport_routes', [
            'institution_id' => session('institution_id'),
            'name'           => sanitize($data['name']),
            'start_point'    => sanitize($data['start_point'] ?? ''),
            'end_point'      => sanitize($data['end_point'] ?? ''),
            'status'         => $data['status'] ?? 'active'
        ]);

        $this->logAudit('transport_route_created', 'transport_route', $id);
        $this->redirectWith('transport', 'Route added successfully.', 'success');
    }

    public function stops(int $routeId): void
    {
        $this->authorize('transport.view');
        
        $route = db()->query("SELECT * FROM transport_routes WHERE id = ? AND institution_id = ?", [$routeId, session('institution_id')])->fetch();
        if (!$route) {
            $this->redirectWith('transport', 'Route not found.', 'error');
            return;
        }

        $stops = db()->query("SELECT * FROM transport_stops WHERE route_id = ? ORDER BY sort_order ASC, pickup_time ASC", [$routeId])->fetchAll();

        $this->view('transport/stops', compact('route', 'stops'));
    }

    public function storeStop(int $routeId): void
    {
        $this->authorize('transport.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'name' => 'required'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $id = db()->insert('transport_stops', [
            'route_id'    => $routeId,
            'name'        => sanitize($data['name']),
            'pickup_time' => $data['pickup_time'] ? $data['pickup_time'] : null,
            'drop_time'   => $data['drop_time'] ? $data['drop_time'] : null,
            'sort_order'  => (int)($data['sort_order'] ?? 0)
        ]);

        $this->logAudit('transport_stop_added', 'transport_route', $routeId, ['stop_id' => $id]);
        $this->redirectWith("transport/{$routeId}/stops", 'Stop added successfully.', 'success');
    }

    public function allocations(): void
    {
        $this->authorize('transport.allocate');

        $institutionId = session('institution_id');
        $academicYearId = session('academic_year_id');

        $allocations = db()->query("
            SELECT ta.*, 
                   s.first_name, s.last_name, s.student_id_number,
                   r.name as route_name, st.name as stop_name
            FROM transport_allocations ta
            JOIN students s ON s.id = ta.student_id
            JOIN transport_routes r ON r.id = ta.route_id
            JOIN transport_stops st ON st.id = ta.stop_id
            WHERE r.institution_id = ? AND ta.academic_year_id = ?
            ORDER BY ta.created_at DESC
        ", [$institutionId, $academicYearId])->fetchAll();

        $routes = db()->query("SELECT * FROM transport_routes WHERE institution_id = ? AND status='active'", [$institutionId])->fetchAll();

        $this->view('transport/allocations', compact('allocations', 'routes'));
    }

    public function createAllocation(): void
    {
        $this->authorize('transport.allocate');
        
        $data = $this->postData();
        $institutionId = session('institution_id');
        $academicYearId = session('academic_year_id');

        $errors = $this->validate($data, [
            'student_id' => 'required|numeric',
            'stop_id' => 'required|numeric'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        // Must find route id from stop id
        $stop = db()->query("SELECT route_id FROM transport_stops WHERE id = ?", [$data['stop_id']])->fetch();
        if (!$stop) {
            $this->backWithErrors(['error' => 'Invalid stop selected.']);
            return;
        }

        $id = db()->insert('transport_allocations', [
            'student_id'       => $data['student_id'],
            'route_id'         => $stop['route_id'],
            'stop_id'          => $data['stop_id'],
            'academic_year_id' => $academicYearId,
            'status'           => 'active'
        ]);

        $this->logAudit('transport_allocated', 'transport_allocation', $id);
        $this->redirectWith('transport/allocations', 'Transport allocated successfully.', 'success');
    }
}
