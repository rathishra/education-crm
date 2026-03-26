<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CampusController extends BaseController
{
    public function index(): void
    {
        $this->authorize('institutions.view');
        $page    = max(1, (int)($this->input('page') ?: 1));
        $search  = $this->input('search', '');
        $instId  = $this->input('institution_id', '');
        $status  = $this->input('status', '');

        $where  = 'c.deleted_at IS NULL';
        $params = [];

        if ($this->institutionId) {
            $where .= ' AND c.institution_id = ?';
            $params[] = $this->institutionId;
        }
        if ($instId) { $where .= ' AND c.institution_id = ?'; $params[] = $instId; }
        if ($status) { $where .= ' AND c.status = ?'; $params[] = $status; }
        if ($search) {
            $where .= ' AND (c.name LIKE ? OR c.code LIKE ? OR c.city LIKE ?)';
            $s = "%$search%"; $params = array_merge($params, [$s, $s, $s]);
        }

        $sql = "SELECT c.*, i.name as institution_name
                FROM campuses c
                LEFT JOIN institutions i ON i.id = c.institution_id
                WHERE $where ORDER BY i.name, c.name";

        $campuses = $this->db->paginate($sql, $params, $page, 15);

        $this->db->query("SELECT id, name FROM institutions WHERE status='active' AND deleted_at IS NULL ORDER BY name");
        $institutions = $this->db->fetchAll();

        $this->view('campuses/index', compact('campuses', 'institutions', 'search', 'instId', 'status'));
    }

    public function create(): void
    {
        $this->authorize('institutions.create');
        $this->db->query("SELECT id, name FROM institutions WHERE status='active' AND deleted_at IS NULL ORDER BY name");
        $institutions = $this->db->fetchAll();
        $this->view('campuses/create', compact('institutions'));
    }

    public function store(): void
    {
        $this->authorize('institutions.create');
        $data = $this->postData();

        $institutionId = $data['institution_id'] ?? $this->institutionId;
        $this->db->query("SELECT organization_id FROM institutions WHERE id = ?", [$institutionId]);
        $inst = $this->db->fetch();

        $insert = [
            'organization_id' => $inst['organization_id'] ?? null,
            'institution_id'  => $institutionId,
            'name'            => sanitize($data['name']),
            'code'            => strtoupper(sanitize($data['code'])),
            'address_line1'   => sanitize($data['address_line1'] ?? ''),
            'city'            => sanitize($data['city'] ?? ''),
            'state'           => sanitize($data['state'] ?? ''),
            'pincode'         => sanitize($data['pincode'] ?? ''),
            'phone'           => sanitize($data['phone'] ?? ''),
            'email'           => sanitize($data['email'] ?? ''),
            'principal_name'  => sanitize($data['principal_name'] ?? ''),
            'capacity'        => (int)($data['capacity'] ?? 0),
            'status'          => $data['status'] ?? 'active',
        ];

        $id = $this->db->insert('campuses', $insert);
        $this->logAudit('campus_created', 'campus', $id);
        $this->redirectWith(url('campuses'), 'success', 'Campus created successfully.');
    }

    public function edit(int $id): void
    {
        $this->authorize('institutions.edit');
        $this->db->query(
            "SELECT c.*, i.name as institution_name FROM campuses c LEFT JOIN institutions i ON i.id = c.institution_id WHERE c.id = ? AND c.deleted_at IS NULL",
            [$id]
        );
        $campus = $this->db->fetch();
        if (!$campus) {
            $this->redirectWith(url('campuses'), 'error', 'Campus not found.');
            return;
        }
        $this->db->query("SELECT id, name FROM institutions WHERE status='active' AND deleted_at IS NULL ORDER BY name");
        $institutions = $this->db->fetchAll();
        $this->view('campuses/edit', compact('campus', 'institutions'));
    }

    public function update(int $id): void
    {
        $this->authorize('institutions.edit');
        $data = $this->postData();
        $update = [
            'name'           => sanitize($data['name']),
            'code'           => strtoupper(sanitize($data['code'])),
            'address_line1'  => sanitize($data['address_line1'] ?? ''),
            'city'           => sanitize($data['city'] ?? ''),
            'state'          => sanitize($data['state'] ?? ''),
            'pincode'        => sanitize($data['pincode'] ?? ''),
            'phone'          => sanitize($data['phone'] ?? ''),
            'email'          => sanitize($data['email'] ?? ''),
            'principal_name' => sanitize($data['principal_name'] ?? ''),
            'capacity'       => (int)($data['capacity'] ?? 0),
            'status'         => $data['status'] ?? 'active',
        ];
        $set = implode(', ', array_map(fn($k) => "$k=?", array_keys($update)));
        $this->db->query(
            "UPDATE campuses SET $set, updated_at=NOW() WHERE id = ?",
            [...array_values($update), $id]
        );
        $this->logAudit('campus_updated', 'campus', $id);
        $this->redirectWith(url('campuses'), 'success', 'Campus updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('institutions.delete');
        $this->db->query("UPDATE campuses SET deleted_at=NOW() WHERE id = ?", [$id]);
        $this->logAudit('campus_deleted', 'campus', $id);
        $this->redirectWith(url('campuses'), 'success', 'Campus deleted.');
    }

    public function toggleStatus(int $id): void
    {
        $this->authorize('institutions.edit');
        $this->db->query("SELECT status FROM campuses WHERE id = ?", [$id]);
        $campus = $this->db->fetch();
        $newStatus = ($campus['status'] ?? 'active') === 'active' ? 'inactive' : 'active';
        $this->db->query("UPDATE campuses SET status=?, updated_at=NOW() WHERE id=?", [$newStatus, $id]);
        jsonResponse(['status' => $newStatus, 'message' => 'Status updated']);
    }
}
