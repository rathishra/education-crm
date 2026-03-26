<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class FeeController extends BaseController
{
    public function index(): void
    {
        $this->authorize('fees.view');

        $where = "fs.deleted_at IS NULL";
        $params = [];
        $institutionId = session('institution_id');
        if ($institutionId) { $where .= " AND fs.institution_id = ?"; $params[] = $institutionId; }

        $search = $this->input('search');
        if ($search) {
            $where .= " AND (fs.name LIKE ? OR c.name LIKE ?)";
            $s = '%' . $search . '%';
            $params = array_merge($params, [$s, $s]);
        }

        $page = (int)($this->input('page') ?: 1);
        $sql = "SELECT fs.*, c.name as course_name, b.name as batch_name,
                       COUNT(DISTINCT fc.id) as component_count
                FROM fee_structures fs
                LEFT JOIN courses c ON c.id = fs.course_id
                LEFT JOIN batches b ON b.id = fs.batch_id
                LEFT JOIN fee_components fc ON fc.fee_structure_id = fs.id
                WHERE {$where}
                GROUP BY fs.id
                ORDER BY fs.created_at DESC";

        $feeStructures = db()->paginate($sql, $params, $page, config('app.per_page', 15));

        $this->view('fees/index', compact('feeStructures', 'search'));
    }

    public function create(): void
    {
        $this->authorize('fees.create');

        db()->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = db()->fetchAll();
        db()->query("SELECT id, name FROM academic_years ORDER BY start_date DESC");
        $academicYears = db()->fetchAll();

        $this->view('fees/create', compact('courses', 'academicYears'));
    }

    public function store(): void
    {
        $this->authorize('fees.create');

        $data = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'course_id' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $institutionId = session('institution_id');
        $totalAmount = 0;

        $components = [];
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

        $feeStructureId = db()->insert('fee_structures', [
            'institution_id'  => $institutionId,
            'name'            => sanitize($data['name']),
            'course_id'       => $data['course_id'],
            'batch_id'        => $data['batch_id'] ?: null,
            'academic_year_id'=> $data['academic_year_id'] ?: null,
            'total_amount'    => $totalAmount,
            'description'     => sanitize($data['description'] ?? ''),
            'status'          => 'active',
            'created_by'      => auth()['id'],
        ]);

        foreach ($components as $comp) {
            $comp['fee_structure_id'] = $feeStructureId;
            db()->insert('fee_components', $comp);
        }

        $this->logAudit('fee_structure_created', 'fee_structure', $feeStructureId);
        $this->redirectWith('fees', 'Fee structure created.', 'success');
    }

    public function show(int $id): void
    {
        $this->authorize('fees.view');

        db()->query(
            "SELECT fs.*, c.name as course_name, b.name as batch_name, ay.name as academic_year_name
             FROM fee_structures fs
             LEFT JOIN courses c ON c.id = fs.course_id
             LEFT JOIN batches b ON b.id = fs.batch_id
             LEFT JOIN academic_years ay ON ay.id = fs.academic_year_id
             WHERE fs.id = ? AND fs.deleted_at IS NULL",
            [$id]
        );
        $feeStructure = db()->fetch();
        if (!$feeStructure) { $this->redirectWith('fees', 'Fee structure not found.', 'error'); return; }

        db()->query("SELECT * FROM fee_components WHERE fee_structure_id = ? ORDER BY name", [$id]);
        $feeStructure['components'] = db()->fetchAll();

        $this->view('fees/show', compact('feeStructure'));
    }

    public function edit(int $id): void
    {
        $this->authorize('fees.edit');

        db()->query("SELECT * FROM fee_structures WHERE id = ? AND deleted_at IS NULL", [$id]);
        $feeStructure = db()->fetch();
        if (!$feeStructure) { $this->redirectWith('fees', 'Not found.', 'error'); return; }

        db()->query("SELECT * FROM fee_components WHERE fee_structure_id = ? ORDER BY name", [$id]);
        $feeStructure['components'] = db()->fetchAll();

        db()->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = db()->fetchAll();
        db()->query("SELECT id, name FROM academic_years ORDER BY start_date DESC");
        $academicYears = db()->fetchAll();

        $this->view('fees/edit', compact('feeStructure', 'courses', 'academicYears'));
    }

    public function update(int $id): void
    {
        $this->authorize('fees.edit');

        $data = $this->postData();
        $errors = $this->validate($data, ['name' => 'required', 'course_id' => 'required']);
        if ($errors) { $this->backWithErrors($errors); return; }

        $totalAmount = 0;
        $components = [];
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

        db()->update('fee_structures', [
            'name'            => sanitize($data['name']),
            'course_id'       => $data['course_id'],
            'batch_id'        => $data['batch_id'] ?: null,
            'academic_year_id'=> $data['academic_year_id'] ?: null,
            'total_amount'    => $totalAmount,
            'description'     => sanitize($data['description'] ?? ''),
        ], '`id` = ?', [$id]);

        // Replace components
        db()->query("DELETE FROM fee_components WHERE fee_structure_id = ?", [$id]);
        foreach ($components as $comp) {
            db()->insert('fee_components', $comp);
        }

        $this->logAudit('fee_structure_updated', 'fee_structure', $id);
        $this->redirectWith('fees/' . $id, 'Fee structure updated.', 'success');
    }

    public function destroy(int $id): void
    {
        $this->authorize('fees.delete');
        db()->update('fee_structures', ['deleted_at' => date('Y-m-d H:i:s')], '`id` = ?', [$id]);
        $this->logAudit('fee_structure_deleted', 'fee_structure', $id);
        $this->redirectWith('fees', 'Fee structure deleted.', 'success');
    }
}
