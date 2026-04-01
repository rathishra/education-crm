<?php
namespace App\Controllers\Fees;

use App\Controllers\BaseController;

class FeeStructureController extends BaseController
{
    private function ensureSchema(): void
    {
        foreach (['batch_id INT UNSIGNED NULL', 'semester TINYINT UNSIGNED NULL', 'description TEXT NULL', 'created_by BIGINT UNSIGNED NULL'] as $col) {
            $colName = explode(' ', $col)[0];
            try {
                $this->db->query("SHOW COLUMNS FROM fee_structures LIKE '$colName'");
                if (!$this->db->fetch()) {
                    $this->db->query("ALTER TABLE fee_structures ADD COLUMN $col");
                }
            } catch (\Exception $e) {}
        }
        try { $this->db->query("SELECT id FROM fee_structure_details LIMIT 1"); }
        catch (\Exception $e) { /* already handled by migration */ }
    }

    // ── INDEX ────────────────────────────────────────────────
    public function index(): void
    {
        $this->ensureSchema();

        $this->db->query(
            "SELECT fs.*,
                    ay.name,
                    c.name, c.code,
                    b.name,
                    (SELECT COUNT(*) FROM fee_structure_details WHERE structure_id = fs.id) AS head_count,
                    (SELECT COALESCE(SUM(amount),0) FROM fee_structure_details WHERE structure_id = fs.id) AS total_amount
             FROM fee_structures fs
             LEFT JOIN academic_years ay ON ay.id = fs.academic_year_id
             LEFT JOIN courses c  ON c.id  = fs.course_id
             LEFT JOIN batches b  ON b.id  = fs.batch_id
             WHERE fs.institution_id = ?
             ORDER BY fs.id DESC",
            [$this->institutionId]
        );
        $structures = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM academic_years WHERE institution_id = ? AND is_current = 1 ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $stats = [
            'total'  => count($structures),
            'active' => count(array_filter($structures, fn($s) => $s['status'] === 'active')),
        ];

        $this->view('fees/structure/index', compact('structures', 'academicYears', 'stats'));
    }

    // ── CREATE ───────────────────────────────────────────────
    public function create(): void
    {
        $this->db->query("SELECT id, name FROM academic_years WHERE institution_id = ? AND is_current = 1 ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM courses WHERE institution_id = ? AND status = 'active' ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, head_name, head_code, category, is_mandatory FROM fee_heads WHERE institution_id = ? AND is_active = 1 ORDER BY category, head_name", [$this->institutionId]);
        $feeHeads = $this->db->fetchAll();

        $this->view('fees/structure/create', compact('academicYears', 'courses', 'feeHeads'));
    }

    // ── STORE ────────────────────────────────────────────────
    public function store(): void
    {
        $name = trim($this->input('name', ''));
        $courseId = (int)$this->input('course_id', 0);
        $ayId     = (int)$this->input('academic_year_id', 0);

        if (!$name || !$courseId) {
            $this->redirectWith(url('fees/structures/create'), 'error', 'Structure name and course are required.');
            return;
        }

        $this->db->insert('fee_structures', [
            'institution_id'       => $this->institutionId,
            'name'                 => $name,
            'course_id'            => $courseId,
            'batch_id'             => ($this->input('batch_id') ?: null),
            'academic_year_id'     => ($ayId ?: null),
            'semester'             => ($this->input('semester') ?: null),
            'admission_type'       => $this->input('admission_type', 'regular'),
            'total_amount'         => 0,
            'installments_allowed' => (int)(bool)$this->input('installments_allowed', 1),
            'max_installments'     => (int)$this->input('max_installments', 4),
            'late_fee_per_day'     => (float)$this->input('late_fee_per_day', 0),
            'grace_period_days'    => (int)$this->input('grace_period_days', 7),
            'description'          => trim($this->input('description', '')),
            'status'               => 'active',
            'created_by'           => $this->user['id'] ?? null,
        ]);
        $structId = $this->db->lastInsertId();

        // Store fee head details
        $headIds    = (array)$this->input('head_ids');
        $headAmts   = (array)$this->input('head_amounts');
        $headDates  = (array)$this->input('head_due_dates');
        $totalAmount = 0;
        foreach ($headIds as $i => $hid) {
            if (!$hid) continue;
            $amt = (float)($headAmts[$i] ?? 0);
            $totalAmount += $amt;
            $this->db->insert('fee_structure_details', [
                'structure_id' => $structId,
                'fee_head_id'  => (int)$hid,
                'amount'       => $amt,
                'due_date'     => ($headDates[$i] ?? null) ?: null,
                'sort_order'   => $i,
            ]);
        }
        // Update total
        $this->db->query("UPDATE fee_structures SET total_amount = ? WHERE id = ?", [$totalAmount, $structId]);

        $this->logAudit('fee_structure_created', 'fee_structures', $structId);
        $this->redirectWith(url('fees/structures'), 'success', 'Fee structure created successfully.');
    }

    // ── EDIT ─────────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->db->query("SELECT * FROM fee_structures WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $structure = $this->db->fetch();
        if (!$structure) { $this->redirectWith(url('fees/structures'), 'error', 'Structure not found.'); return; }

        $this->db->query("SELECT fsd.*, fh.head_name, fh.head_code, fh.category FROM fee_structure_details fsd JOIN fee_heads fh ON fh.id = fsd.fee_head_id WHERE fsd.structure_id = ? ORDER BY fsd.sort_order", [$id]);
        $details = $this->db->fetchAll();

        $this->db->query("SELECT id, year_name AS name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->db->query("SELECT id, course_name AS name, course_code AS code FROM courses WHERE institution_id = ? ORDER BY course_name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, head_name, head_code, category, is_mandatory FROM fee_heads WHERE institution_id = ? AND is_active = 1 ORDER BY category, head_name", [$this->institutionId]);
        $feeHeads = $this->db->fetchAll();

        $this->view('fees/structure/edit', compact('structure', 'details', 'academicYears', 'courses', 'feeHeads'));
    }

    // ── UPDATE ───────────────────────────────────────────────
    public function update(int $id): void
    {
        $this->db->query("SELECT id FROM fee_structures WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        if (!$this->db->fetch()) { $this->redirectWith(url('fees/structures'), 'error', 'Not found.'); return; }

        $this->db->query(
            "UPDATE fee_structures SET name=?, course_id=?, batch_id=?, academic_year_id=?,
             semester=?, admission_type=?, installments_allowed=?, max_installments=?,
             late_fee_per_day=?, grace_period_days=?, description=?, updated_at=NOW()
             WHERE id = ?",
            [
                trim($this->input('name', '')),
                (int)$this->input('course_id', 0),
                $this->input('batch_id') ?: null,
                $this->input('academic_year_id') ?: null,
                $this->input('semester') ?: null,
                $this->input('admission_type', 'regular'),
                (int)(bool)$this->input('installments_allowed', 1),
                (int)$this->input('max_installments', 4),
                (float)$this->input('late_fee_per_day', 0),
                (int)$this->input('grace_period_days', 7),
                trim($this->input('description', '')),
                $id,
            ]
        );

        // Replace details
        $this->db->query("DELETE FROM fee_structure_details WHERE structure_id = ?", [$id]);
        $headIds   = (array)$this->input('head_ids');
        $headAmts  = (array)$this->input('head_amounts');
        $headDates = (array)$this->input('head_due_dates');
        $totalAmount = 0;
        foreach ($headIds as $i => $hid) {
            if (!$hid) continue;
            $amt = (float)($headAmts[$i] ?? 0);
            $totalAmount += $amt;
            $this->db->insert('fee_structure_details', [
                'structure_id' => $id,
                'fee_head_id'  => (int)$hid,
                'amount'       => $amt,
                'due_date'     => ($headDates[$i] ?? null) ?: null,
                'sort_order'   => $i,
            ]);
        }
        $this->db->query("UPDATE fee_structures SET total_amount = ? WHERE id = ?", [$totalAmount, $id]);
        $this->redirectWith(url('fees/structures'), 'success', 'Fee structure updated successfully.');
    }

    // ── TOGGLE STATUS ────────────────────────────────────────
    public function toggleStatus(int $id): void
    {
        $this->db->query("SELECT status FROM fee_structures WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $row = $this->db->fetch();
        if (!$row) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); }
        $new = $row['status'] === 'active' ? 'inactive' : 'active';
        $this->db->query("UPDATE fee_structures SET status = ? WHERE id = ?", [$new, $id]);
        $this->json(['status' => 'success', 'new_status' => $new]);
    }

    // ── DESTROY ──────────────────────────────────────────────
    public function destroy(int $id): void
    {
        $this->db->query("SELECT COUNT(*) AS cnt FROM fee_student_assignments WHERE structure_id = ?", [$id]);
        if (($this->db->fetch()['cnt'] ?? 0) > 0) {
            $this->json(['status' => 'error', 'message' => 'Cannot delete: students are assigned to this structure.'], 422);
            return;
        }
        $this->db->query("DELETE FROM fee_structure_details WHERE structure_id = ?", [$id]);
        $this->db->query("DELETE FROM fee_structures WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $this->json(['status' => 'success', 'message' => 'Fee structure deleted.']);
    }

    // ── AJAX: courses → batches ──────────────────────────────
    public function ajaxBatches(): void
    {
        $courseId = (int)$this->input('course_id', 0);
        $this->db->query(
            "SELECT id, name FROM batches WHERE course_id = ? AND institution_id = ? ORDER BY name",
            [$courseId, $this->institutionId]
        );
        $this->json(['status' => 'success', 'data' => $this->db->fetchAll()]);
    }

    // ── COPY STRUCTURE ───────────────────────────────────────
    public function copy(int $id): void
    {
        $this->db->query("SELECT * FROM fee_structures WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $src = $this->db->fetch();
        if (!$src) { $this->json(['status' => 'error', 'message' => 'Source not found.'], 404); }

        $newAyId = (int)$this->input('academic_year_id', 0);
        $this->db->insert('fee_structures', [
            'institution_id'       => $this->institutionId,
            'name'                 => $src['name'] . ' (Copy)',
            'course_id'            => $src['course_id'],
            'batch_id'             => $src['batch_id'],
            'academic_year_id'     => $newAyId ?: $src['academic_year_id'],
            'semester'             => $src['semester'],
            'admission_type'       => $src['admission_type'],
            'total_amount'         => $src['total_amount'],
            'installments_allowed' => $src['installments_allowed'],
            'max_installments'     => $src['max_installments'],
            'late_fee_per_day'     => $src['late_fee_per_day'],
            'grace_period_days'    => $src['grace_period_days'],
            'status'               => 'active',
            'created_by'           => $this->user['id'] ?? null,
        ]);
        $newId = $this->db->lastInsertId();

        $this->db->query("SELECT * FROM fee_structure_details WHERE structure_id = ?", [$id]);
        foreach ($this->db->fetchAll() as $d) {
            $this->db->insert('fee_structure_details', [
                'structure_id' => $newId,
                'fee_head_id'  => $d['fee_head_id'],
                'amount'       => $d['amount'],
                'due_date'     => $d['due_date'],
                'sort_order'   => $d['sort_order'],
            ]);
        }
        $this->json(['status' => 'success', 'message' => 'Structure copied successfully.', 'new_id' => $newId]);
    }
}
