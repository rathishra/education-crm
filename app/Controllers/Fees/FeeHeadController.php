<?php
namespace App\Controllers\Fees;

use App\Controllers\BaseController;

class FeeHeadController extends BaseController
{
    private function ensureSchema(): void
    {
        // fee_heads created by migration; ensure columns exist
        try {
            $this->db->query("SELECT description FROM fee_heads LIMIT 1");
        } catch (\Exception $e) {
            try { $this->db->query("ALTER TABLE fee_heads ADD COLUMN description TEXT NULL AFTER is_active"); } catch (\Exception $e2) {}
        }
    }

    // ── INDEX ────────────────────────────────────────────────
    public function index(): void
    {
        $this->ensureSchema();

        $this->db->query(
            "SELECT fh.*,
                    (SELECT COUNT(*) FROM fee_structure_details WHERE fee_head_id = fh.id) AS structure_count,
                    (SELECT COUNT(*) FROM fee_student_assignments WHERE fee_head_id = fh.id) AS assignment_count
             FROM fee_heads fh
             WHERE fh.institution_id = ?
             ORDER BY fh.category, fh.head_name",
            [$this->institutionId]
        );
        $heads = $this->db->fetchAll();

        // Stats
        $stats = [
            'total'     => count($heads),
            'active'    => count(array_filter($heads, fn($h) => $h['is_active'])),
            'mandatory' => count(array_filter($heads, fn($h) => $h['is_mandatory'])),
            'refundable'=> count(array_filter($heads, fn($h) => $h['is_refundable'])),
        ];

        $this->view('fees/heads/index', compact('heads', 'stats'));
    }

    // ── STORE ────────────────────────────────────────────────
    public function store(): void
    {
        $data = [
            'head_name'     => trim($this->input('head_name', '')),
            'head_code'     => strtoupper(trim($this->input('head_code', ''))),
            'fee_type'      => $this->input('fee_type', 'annual'),
            'category'      => $this->input('category', 'other'),
            'is_mandatory'  => (int)(bool)$this->input('is_mandatory'),
            'is_refundable' => (int)(bool)$this->input('is_refundable'),
            'description'   => trim($this->input('description', '')),
        ];

        if (!$data['head_name'] || !$data['head_code']) {
            $this->json(['status' => 'error', 'message' => 'Head name and code are required.'], 422);
        }

        // Duplicate code check
        $this->db->query(
            "SELECT id FROM fee_heads WHERE institution_id = ? AND head_code = ?",
            [$this->institutionId, $data['head_code']]
        );
        if ($this->db->fetch()) {
            $this->json(['status' => 'error', 'message' => 'Fee head code already exists.'], 422);
        }

        $this->db->insert('fee_heads', array_merge($data, [
            'institution_id' => $this->institutionId,
            'is_active'      => 1,
            'created_by'     => $this->user['id'] ?? null,
        ]));
        $newId = $this->db->lastInsertId();
        $this->db->query("SELECT * FROM fee_heads WHERE id = ?", [$newId]);

        $this->json(['status' => 'success', 'message' => 'Fee head created.', 'data' => $this->db->fetch()]);
    }

    // ── GET ONE (JSON for edit modal) ────────────────────────
    public function getOne(int $id): void
    {
        $this->db->query(
            "SELECT * FROM fee_heads WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $head = $this->db->fetch();
        if (!$head) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); }
        $this->json(['status' => 'success', 'data' => $head]);
    }

    // ── UPDATE ───────────────────────────────────────────────
    public function update(int $id): void
    {
        $this->db->query(
            "SELECT id FROM fee_heads WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); }

        $code = strtoupper(trim($this->input('head_code', '')));
        $this->db->query(
            "SELECT id FROM fee_heads WHERE institution_id = ? AND head_code = ? AND id != ?",
            [$this->institutionId, $code, $id]
        );
        if ($this->db->fetch()) {
            $this->json(['status' => 'error', 'message' => 'Fee head code already used by another head.'], 422);
        }

        $this->db->query(
            "UPDATE fee_heads SET head_name=?, head_code=?, fee_type=?, category=?,
             is_mandatory=?, is_refundable=?, description=?, updated_at=NOW()
             WHERE id = ? AND institution_id = ?",
            [
                trim($this->input('head_name', '')),
                $code,
                $this->input('fee_type', 'annual'),
                $this->input('category', 'other'),
                (int)(bool)$this->input('is_mandatory'),
                (int)(bool)$this->input('is_refundable'),
                trim($this->input('description', '')),
                $id, $this->institutionId,
            ]
        );
        $this->db->query("SELECT * FROM fee_heads WHERE id = ?", [$id]);
        $this->json(['status' => 'success', 'message' => 'Fee head updated.', 'data' => $this->db->fetch()]);
    }

    // ── TOGGLE ───────────────────────────────────────────────
    public function toggle(int $id): void
    {
        $this->db->query(
            "SELECT is_active FROM fee_heads WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $row = $this->db->fetch();
        if (!$row) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); }

        $new = $row['is_active'] ? 0 : 1;
        $this->db->query(
            "UPDATE fee_heads SET is_active = ? WHERE id = ?",
            [$new, $id]
        );
        $this->json(['status' => 'success', 'is_active' => $new]);
    }

    // ── DESTROY ──────────────────────────────────────────────
    public function destroy(int $id): void
    {
        // Check if used in structures
        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM fee_structure_details WHERE fee_head_id = ?",
            [$id]
        );
        if (($this->db->fetch()['cnt'] ?? 0) > 0) {
            $this->json(['status' => 'error', 'message' => 'Cannot delete: fee head is used in fee structures.'], 422);
        }
        $this->db->query(
            "DELETE FROM fee_heads WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->json(['status' => 'success', 'message' => 'Fee head deleted.']);
    }
}
