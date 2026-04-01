<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class FeeController extends BaseController
{
    public function index(): void
    {
        $this->authorize('fees.view');

        $where  = "fs.deleted_at IS NULL AND fs.institution_id = ?";
        $params = [$this->institutionId];

        $search = $this->input('search');
        if ($search) {
            $where .= " AND (fs.name LIKE ? OR c.name LIKE ?)";
            $s = '%' . $search . '%';
            $params = array_merge($params, [$s, $s]);
        }

        $page = max(1, (int)($this->input('page') ?: 1));
        $sql  = "SELECT fs.*, c.name as course_name, b.name as batch_name,
                        ay.name as academic_year_name,
                        COUNT(DISTINCT fc.id) as component_count
                 FROM fee_structures fs
                 LEFT JOIN courses c ON c.id = fs.course_id
                 LEFT JOIN batches b ON b.id = fs.batch_id
                 LEFT JOIN academic_years ay ON ay.id = fs.academic_year_id
                 LEFT JOIN fee_components fc ON fc.fee_structure_id = fs.id
                 WHERE {$where}
                 GROUP BY fs.id
                 ORDER BY fs.created_at DESC";

        $feeStructures = $this->db->paginate($sql, $params, $page, config('app.per_page', 15));

        $this->view('fees/index', compact('feeStructures', 'search'));
    }

    public function create(): void
    {
        $this->authorize('fees.create');

        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();
        $this->db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->view('fees/create', compact('courses', 'academicYears'));
    }

    public function store(): void
    {
        $this->authorize('fees.create');

        $data   = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'course_id' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $totalAmount = 0;
        $components  = [];
        if (!empty($data['component_name'])) {
            foreach ($data['component_name'] as $i => $cname) {
                if (!empty($cname)) {
                    $amt = (float)($data['component_amount'][$i] ?? 0);
                    $totalAmount += $amt;
                    $components[] = [
                        'name'        => sanitize($cname),
                        'amount'      => $amt,
                        'is_optional' => isset($data['component_optional'][$i]) ? 1 : 0,
                    ];
                }
            }
        }

        $feeStructureId = $this->db->insert('fee_structures', [
            'institution_id'   => $this->institutionId,
            'name'             => sanitize($data['name']),
            'course_id'        => (int)$data['course_id'],
            'batch_id'         => $data['batch_id'] ?: null,
            'academic_year_id' => $data['academic_year_id'] ?: null,
            'total_amount'     => $totalAmount,
            'description'      => sanitize($data['description'] ?? ''),
            'status'           => 'active',
            'created_by'       => $this->user['id'],
        ]);

        foreach ($components as $comp) {
            $comp['fee_structure_id'] = $feeStructureId;
            $this->db->insert('fee_components', $comp);
        }

        $this->logAudit('fee_structure_created', 'fee_structure', $feeStructureId);
        $this->redirectWith(url('fees'), 'success', 'Fee structure created successfully.');
    }

    public function show(int $id): void
    {
        $this->authorize('fees.view');

        $this->db->query(
            "SELECT fs.*, c.name as course_name, b.name as batch_name, ay.name as academic_year_name
             FROM fee_structures fs
             LEFT JOIN courses c ON c.id = fs.course_id
             LEFT JOIN batches b ON b.id = fs.batch_id
             LEFT JOIN academic_years ay ON ay.id = fs.academic_year_id
             WHERE fs.id = ? AND fs.institution_id = ? AND fs.deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        $feeStructure = $this->db->fetch();
        if (!$feeStructure) {
            $this->redirectWith(url('fees'), 'error', 'Fee structure not found.');
            return;
        }

        $this->db->query("SELECT * FROM fee_components WHERE fee_structure_id = ? ORDER BY name", [$id]);
        $feeStructure['components'] = $this->db->fetchAll();

        $this->view('fees/show', compact('feeStructure'));
    }

    public function edit(int $id): void
    {
        $this->authorize('fees.edit');

        $this->db->query("SELECT * FROM fee_structures WHERE id = ? AND institution_id = ? AND deleted_at IS NULL", [$id, $this->institutionId]);
        $feeStructure = $this->db->fetch();
        if (!$feeStructure) {
            $this->redirectWith(url('fees'), 'error', 'Fee structure not found.');
            return;
        }

        $this->db->query("SELECT * FROM fee_components WHERE fee_structure_id = ? ORDER BY name", [$id]);
        $feeStructure['components'] = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM courses WHERE institution_id = ? AND deleted_at IS NULL ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();
        $this->db->query("SELECT id, name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->view('fees/edit', compact('feeStructure', 'courses', 'academicYears'));
    }

    public function update(int $id): void
    {
        $this->authorize('fees.edit');

        $data   = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'course_id' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $totalAmount = 0;
        $components  = [];
        if (!empty($data['component_name'])) {
            foreach ($data['component_name'] as $i => $cname) {
                if (!empty($cname)) {
                    $amt = (float)($data['component_amount'][$i] ?? 0);
                    $totalAmount += $amt;
                    $components[] = [
                        'fee_structure_id' => $id,
                        'name'             => sanitize($cname),
                        'amount'           => $amt,
                        'is_optional'      => isset($data['component_optional'][$i]) ? 1 : 0,
                    ];
                }
            }
        }

        $this->db->update('fee_structures', [
            'name'             => sanitize($data['name']),
            'course_id'        => (int)$data['course_id'],
            'batch_id'         => $data['batch_id'] ?: null,
            'academic_year_id' => $data['academic_year_id'] ?: null,
            'total_amount'     => $totalAmount,
            'description'      => sanitize($data['description'] ?? ''),
        ], '`id` = ? AND `institution_id` = ?', [$id, $this->institutionId]);

        $this->db->query("DELETE FROM fee_components WHERE fee_structure_id = ?", [$id]);
        foreach ($components as $comp) {
            $this->db->insert('fee_components', $comp);
        }

        $this->logAudit('fee_structure_updated', 'fee_structure', $id);
        $this->redirectWith(url('fees/' . $id), 'success', 'Fee structure updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('fees.delete');

        $this->db->update('fee_structures', ['deleted_at' => date('Y-m-d H:i:s')], '`id` = ? AND `institution_id` = ?', [$id, $this->institutionId]);

        $this->logAudit('fee_structure_deleted', 'fee_structure', $id);
        $this->redirectWith(url('fees'), 'success', 'Fee structure deleted successfully.');
    }

    /**
     * Assign fee structure to a student (AJAX/inline)
     */
    public function assignToStudent(): void
    {
        $this->authorize('fees.create');

        $data = $this->postData();
        $studentId = (int)($data['student_id'] ?? 0);
        $structureId = (int)($data['fee_structure_id'] ?? 0);
        $discount = (float)($data['discount'] ?? 0);

        if (!$studentId || !$structureId) {
            $this->redirectWith(url('fees'), 'error', 'Invalid student or fee structure.');
            return;
        }

        $feeModel = new \App\Models\Fee();
        $feeModel->assignStructure($studentId, $structureId, $discount);

        $this->logAudit('fee_assigned', 'student', $studentId, ['structure_id' => $structureId]);
        $this->redirectWith(url('students/' . $studentId), 'success', 'Fee structure assigned successfully.');
    }
}
