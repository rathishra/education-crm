<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class SectionController extends BaseController
{
    public function index(): void
    {
        $this->authorize('batches.view');
        $page    = max(1, (int)($this->input('page') ?: 1));
        $batchId = $this->input('batch_id', '');
        $search  = $this->input('search', '');

        $where  = 's.deleted_at IS NULL';
        $params = [];

        if ($this->institutionId) {
            $where .= ' AND s.institution_id = ?';
            $params[] = $this->institutionId;
        }
        if ($batchId) {
            $where .= ' AND s.batch_id = ?';
            $params[] = $batchId;
        }
        if ($search) {
            $where .= ' AND s.name LIKE ?';
            $params[] = "%$search%";
        }

        $sql = "SELECT s.*, b.name as batch_name, c.name as course_name
                FROM sections s
                LEFT JOIN batches b ON b.id = s.batch_id
                LEFT JOIN courses c ON c.id = b.course_id
                WHERE $where ORDER BY b.name, s.name";

        $sections = $this->db->paginate($sql, $params, $page, 20);

        $this->db->query("SELECT id, name FROM batches WHERE status='active' AND deleted_at IS NULL ORDER BY name");
        $batches = $this->db->fetchAll();

        $this->view('sections/index', compact('sections', 'batches', 'batchId', 'search'));
    }

    public function store(): void
    {
        $this->authorize('batches.create');
        $data = $this->postData();

        $this->db->query("SELECT institution_id FROM batches WHERE id=?", [(int)($data['batch_id'] ?? 0)]);
        $batch = $this->db->fetch();

        $insert = [
            'institution_id' => $batch['institution_id'] ?? $this->institutionId,
            'batch_id'       => (int)($data['batch_id'] ?? 0),
            'name'           => sanitize($data['name']),
            'code'           => strtoupper(sanitize($data['code'] ?? '')),
            'capacity'       => (int)($data['capacity'] ?? 60),
            'room_number'    => sanitize($data['room_number'] ?? ''),
            'status'         => 'active',
        ];

        $id = $this->db->insert('sections', $insert);
        $this->logAudit('section_created', 'section', $id);
        $this->redirectWith(url('sections'), 'success', 'Section created successfully.');
    }

    public function update(int $id): void
    {
        $this->authorize('batches.edit');
        $data = $this->postData();

        $update = [
            'name'        => sanitize($data['name']),
            'code'        => strtoupper(sanitize($data['code'] ?? '')),
            'capacity'    => (int)($data['capacity'] ?? 60),
            'room_number' => sanitize($data['room_number'] ?? ''),
            'status'      => $data['status'] ?? 'active',
        ];

        $set = implode(', ', array_map(fn($k) => "$k=?", array_keys($update)));
        $this->db->query(
            "UPDATE sections SET $set, updated_at=NOW() WHERE id=?",
            [...array_values($update), $id]
        );
        $this->logAudit('section_updated', 'section', $id);
        $this->redirectWith(url('sections'), 'success', 'Section updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('batches.delete');
        $this->db->query("UPDATE sections SET deleted_at=NOW() WHERE id=?", [$id]);
        $this->logAudit('section_deleted', 'section', $id);
        $this->redirectWith(url('sections'), 'success', 'Section deleted.');
    }

    public function autoGenerate(): void
    {
        $this->authorize('batches.create');
        $postData = $this->postData();
        $batchId  = (int)($postData['batch_id'] ?? 0);
        $count    = (int)($postData['count'] ?? 2);

        $this->db->query("SELECT * FROM batches WHERE id=?", [$batchId]);
        $batch = $this->db->fetch();

        if (!$batch) {
            jsonResponse(['error' => 'Batch not found'], 404);
            return;
        }

        $letters = range('A', 'Z');
        $created = 0;

        for ($i = 0; $i < min($count, 10); $i++) {
            $name = 'Section ' . $letters[$i];
            $code = $letters[$i];

            $this->db->query(
                "SELECT id FROM sections WHERE batch_id=? AND name=? AND deleted_at IS NULL",
                [$batchId, $name]
            );
            if ($this->db->fetch()) {
                continue;
            }

            $this->db->insert('sections', [
                'institution_id' => $batch['institution_id'],
                'batch_id'       => $batchId,
                'name'           => $name,
                'code'           => $code,
                'capacity'       => $batch['max_students'] ?? 60,
                'status'         => 'active',
            ]);
            $created++;
        }

        jsonResponse(['created' => $created, 'message' => "$created section(s) created successfully"]);
    }
}
