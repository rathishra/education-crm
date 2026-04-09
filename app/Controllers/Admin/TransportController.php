<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TransportController extends BaseController
{
    private bool $tablesReady = false;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->db->query("SELECT 1 FROM transport_routes LIMIT 0");
            $this->tablesReady = true;
        } catch (\Exception $e) {
            $this->tablesReady = false;
        }
    }

    /** Redirect back with setup notice if tables are missing */
    private function requireTables(): bool
    {
        if (!$this->tablesReady) {
            $this->redirectWith(url('transport'), 'error',
                'Transport tables are not set up. Please run database/41_transport_management.sql in phpMyAdmin.');
            return false;
        }
        return true;
    }

    // =========================================================
    // ROUTES — INDEX
    // =========================================================

    public function index(): void
    {
        $this->authorize('transport.view');

        $routes       = [];
        $tablesMissing = !$this->tablesReady;

        if ($this->tablesReady) {
            $this->db->query(
                "SELECT r.*, COUNT(s.id) AS total_stops
                 FROM transport_routes r
                 LEFT JOIN transport_stops s ON s.route_id = r.id
                 WHERE r.institution_id = ?
                 GROUP BY r.id
                 ORDER BY r.name",
                [$this->institutionId]
            );
            $routes = $this->db->fetchAll();
        }

        $this->view('transport/index', compact('routes', 'tablesMissing'));
    }

    // =========================================================
    // ROUTES — STORE
    // =========================================================

    public function store(): void
    {
        $this->authorize('transport.manage');
        if (!$this->requireTables()) return;

        $data   = $this->postData();
        $errors = $this->validate($data, ['name' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $id = $this->db->insert('transport_routes', [
            'institution_id' => $this->institutionId,
            'name'           => sanitize($data['name']),
            'start_point'    => sanitize($data['start_point'] ?? ''),
            'end_point'      => sanitize($data['end_point'] ?? ''),
            'description'    => sanitize($data['description'] ?? ''),
            'fare'           => (float)($data['fare'] ?? 0),
            'status'         => in_array($data['status'] ?? '', ['active','inactive']) ? $data['status'] : 'active',
        ]);

        $this->logAudit('transport_route_created', 'transport_route', $id);
        $this->redirectWith(url('transport'), 'success', 'Route added successfully.');
    }

    // =========================================================
    // ROUTES — TOGGLE STATUS (AJAX)
    // =========================================================

    public function toggleStatus(int $id): void
    {
        $this->authorize('transport.manage');
        if (!$this->tablesReady) { $this->json(['status' => 'error', 'message' => 'Tables not set up'], 500); return; }

        $this->db->query(
            "SELECT id, status FROM transport_routes WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $route = $this->db->fetch();
        if (!$route) { $this->json(['status' => 'error', 'message' => 'Not found'], 404); return; }

        $newStatus = $route['status'] === 'active' ? 'inactive' : 'active';
        $this->db->query("UPDATE transport_routes SET status = ? WHERE id = ?", [$newStatus, $id]);

        $this->json(['status' => 'ok', 'new_status' => $newStatus]);
    }

    // =========================================================
    // ROUTES — DELETE
    // =========================================================

    public function deleteRoute(int $id): void
    {
        $this->authorize('transport.manage');
        if (!$this->requireTables()) return;

        $this->db->query(
            "DELETE FROM transport_routes WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->redirectWith(url('transport'), 'success', 'Route deleted.');
    }

    // =========================================================
    // STOPS — LIST
    // =========================================================

    public function stops(int $routeId): void
    {
        $this->authorize('transport.view');
        if (!$this->requireTables()) return;

        $this->db->query(
            "SELECT * FROM transport_routes WHERE id = ? AND institution_id = ?",
            [$routeId, $this->institutionId]
        );
        $route = $this->db->fetch();
        if (!$route) {
            $this->redirectWith(url('transport'), 'error', 'Route not found.');
            return;
        }

        $this->db->query(
            "SELECT * FROM transport_stops WHERE route_id = ? ORDER BY sort_order ASC, pickup_time ASC",
            [$routeId]
        );
        $stops = $this->db->fetchAll();

        $this->view('transport/stops', compact('route', 'stops'));
    }

    // =========================================================
    // STOPS — STORE
    // =========================================================

    public function storeStop(int $routeId): void
    {
        $this->authorize('transport.manage');
        if (!$this->requireTables()) return;

        $this->db->query(
            "SELECT id FROM transport_routes WHERE id = ? AND institution_id = ?",
            [$routeId, $this->institutionId]
        );
        if (!$this->db->fetch()) {
            $this->redirectWith(url('transport'), 'error', 'Route not found.');
            return;
        }

        $data   = $this->postData();
        $errors = $this->validate($data, ['name' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $id = $this->db->insert('transport_stops', [
            'route_id'    => $routeId,
            'name'        => sanitize($data['name']),
            'landmark'    => sanitize($data['landmark'] ?? ''),
            'pickup_time' => $data['pickup_time'] ?: null,
            'drop_time'   => $data['drop_time'] ?: null,
            'fare'        => (float)($data['fare'] ?? 0),
            'sort_order'  => (int)($data['sort_order'] ?? 0),
        ]);

        $this->logAudit('transport_stop_added', 'transport_route', $routeId, ['stop_id' => $id]);
        $this->redirectWith(url("transport/{$routeId}/stops"), 'success', 'Stop added successfully.');
    }

    // =========================================================
    // STOPS — DELETE
    // =========================================================

    public function deleteStop(int $routeId, int $stopId): void
    {
        $this->authorize('transport.manage');
        if (!$this->requireTables()) return;

        $this->db->query(
            "DELETE ts FROM transport_stops ts
             INNER JOIN transport_routes tr ON tr.id = ts.route_id AND tr.institution_id = ?
             WHERE ts.id = ? AND ts.route_id = ?",
            [$this->institutionId, $stopId, $routeId]
        );
        $this->redirectWith(url("transport/{$routeId}/stops"), 'success', 'Stop deleted.');
    }

    // =========================================================
    // ALLOCATIONS — LIST
    // =========================================================

    public function allocations(): void
    {
        $this->authorize('transport.allocate');
        if (!$this->requireTables()) return;

        $this->db->query(
            "SELECT id FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC LIMIT 1",
            [$this->institutionId]
        );
        $ay = $this->db->fetch();
        $academicYearId = $ay['id'] ?? 0;

        $this->db->query(
            "SELECT ta.*,
                    s.first_name, s.last_name, s.student_id_number,
                    r.name AS route_name, st.name AS stop_name
             FROM transport_allocations ta
             JOIN students s  ON s.id  = ta.student_id
             JOIN transport_routes r ON r.id = ta.route_id
             JOIN transport_stops st ON st.id = ta.stop_id
             WHERE r.institution_id = ? AND ta.academic_year_id = ?
             ORDER BY ta.created_at DESC",
            [$this->institutionId, $academicYearId]
        );
        $allocations = $this->db->fetchAll();

        $this->db->query(
            "SELECT * FROM transport_routes WHERE institution_id = ? AND status = 'active' ORDER BY name",
            [$this->institutionId]
        );
        $routes = $this->db->fetchAll();

        $this->view('transport/allocations', compact('allocations', 'routes', 'academicYearId'));
    }

    // =========================================================
    // ALLOCATIONS — CREATE
    // =========================================================

    public function createAllocation(): void
    {
        $this->authorize('transport.allocate');
        if (!$this->requireTables()) return;

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'student_id' => 'required|numeric',
            'stop_id'    => 'required|numeric',
        ]);
        if ($errors) { $this->backWithErrors($errors); return; }

        $this->db->query(
            "SELECT ts.route_id FROM transport_stops ts
             INNER JOIN transport_routes tr ON tr.id = ts.route_id AND tr.institution_id = ?
             WHERE ts.id = ?",
            [$this->institutionId, (int)$data['stop_id']]
        );
        $stop = $this->db->fetch();
        if (!$stop) { $this->backWithErrors(['Invalid stop selected.']); return; }

        $this->db->query(
            "SELECT id FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC LIMIT 1",
            [$this->institutionId]
        );
        $ay = $this->db->fetch();
        $academicYearId = $ay['id'] ?? 0;

        $id = $this->db->insert('transport_allocations', [
            'student_id'       => (int)$data['student_id'],
            'route_id'         => $stop['route_id'],
            'stop_id'          => (int)$data['stop_id'],
            'academic_year_id' => $academicYearId,
            'status'           => 'active',
        ]);

        $this->logAudit('transport_allocated', 'transport_allocation', $id);
        $this->redirectWith(url('transport/allocations'), 'success', 'Transport allocated successfully.');
    }

    // =========================================================
    // AJAX — stops by route
    // =========================================================

    public function ajaxStops(int $routeId): void
    {
        if (!$this->tablesReady) { $this->json([]); return; }

        $this->db->query(
            "SELECT ts.id, ts.name, ts.pickup_time, ts.fare
             FROM transport_stops ts
             INNER JOIN transport_routes tr ON tr.id = ts.route_id AND tr.institution_id = ?
             WHERE ts.route_id = ?
             ORDER BY ts.sort_order, ts.name",
            [$this->institutionId, $routeId]
        );
        $this->json($this->db->fetchAll());
    }
}
