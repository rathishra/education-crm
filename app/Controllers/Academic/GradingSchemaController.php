<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class GradingSchemaController extends BaseController
{
    // ──────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────
    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // MAIN PAGE
    // ──────────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->db->query(
            "SELECT gs.*,
                    (SELECT COUNT(*) FROM grading_mark_components  WHERE schema_id = gs.id) AS component_count,
                    (SELECT COUNT(*) FROM grading_grade_rules       WHERE schema_id = gs.id) AS rule_count,
                    (SELECT COUNT(*) FROM academic_assessments      WHERE grading_schema_id = gs.id) AS assessment_count
             FROM grading_schemas gs
             WHERE gs.institution_id = ?
             ORDER BY gs.code ASC",
            [$this->institutionId]
        );
        $schemas = $this->db->fetchAll();

        // Load all components for the tree
        $components = [];
        if (!empty($schemas)) {
            $ids = array_column($schemas, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $this->db->query(
                "SELECT * FROM grading_mark_components WHERE schema_id IN ($placeholders) ORDER BY sort_order, id",
                $ids
            );
            foreach ($this->db->fetchAll() as $c) {
                $components[$c['schema_id']][] = $c;
            }
        }

        $this->view('academic/grading-schemas/index', compact('schemas', 'components'));
    }

    // ──────────────────────────────────────────────────────────────
    // DETAIL  (AJAX — returns full JSON for one schema)
    // ──────────────────────────────────────────────────────────────
    public function detail(int $id): void
    {
        $this->db->query(
            "SELECT * FROM grading_schemas WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $schema = $this->db->fetch();
        if (!$schema) { $this->json(['status' => 'error', 'message' => 'Not found'], 404); }

        $this->db->query(
            "SELECT * FROM grading_schema_categories WHERE schema_id = ? ORDER BY sort_order, id",
            [$id]
        );
        $categories = $this->db->fetchAll();

        $this->db->query(
            "SELECT * FROM grading_mark_components WHERE schema_id = ? ORDER BY sort_order, id",
            [$id]
        );
        $components = $this->db->fetchAll();

        $this->db->query(
            "SELECT * FROM grading_sub_components WHERE schema_id = ? ORDER BY component_id, sort_order, id",
            [$id]
        );
        $subComponents = $this->db->fetchAll();

        $this->db->query(
            "SELECT * FROM grading_grade_rules WHERE schema_id = ? ORDER BY min_percentage DESC",
            [$id]
        );
        $rules = $this->db->fetchAll();

        $this->json([
            'status'         => 'success',
            'schema'         => $schema,
            'categories'     => $categories,
            'components'     => $components,
            'sub_components' => $subComponents,
            'rules'          => $rules,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // SCHEMA CRUD
    // ──────────────────────────────────────────────────────────────
    public function store(): void
    {
        $code = strtoupper(trim($this->input('code', '')));
        $name = trim($this->input('name', ''));
        if (!$code || !$name) {
            $this->json(['status' => 'error', 'message' => 'Code and Name are required.'], 422);
        }
        $this->db->query(
            "SELECT id FROM grading_schemas WHERE institution_id = ? AND code = ?",
            [$this->institutionId, $code]
        );
        if ($this->db->fetch()) {
            $this->json(['status' => 'error', 'message' => 'A scheme with this code already exists.'], 422);
        }
        $this->db->insert('grading_schemas', [
            'institution_id' => $this->institutionId,
            'code'           => $code,
            'name'           => $name,
            'min_mark'       => (float)$this->input('min_mark', 50),
            'max_mark'       => (float)$this->input('max_mark', 100),
            'is_embedded'    => (int)(bool)$this->input('is_embedded'),
            'max_ratio_mark' => (int)(bool)$this->input('max_ratio_mark'),
            'status'         => 'active',
            'created_by'     => $_SESSION['user_id'] ?? null,
        ]);
        $newId = $this->db->lastInsertId();
        $this->db->query("SELECT * FROM grading_schemas WHERE id = ?", [$newId]);
        $this->json(['status' => 'success', 'message' => 'Exam scheme created.', 'schema' => $this->db->fetch()]);
    }

    public function update(int $id): void
    {
        $this->db->query(
            "SELECT id FROM grading_schemas WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) { $this->json(['status' => 'error', 'message' => 'Not found'], 404); }

        $code = strtoupper(trim($this->input('code', '')));
        $name = trim($this->input('name', ''));
        if (!$code || !$name) {
            $this->json(['status' => 'error', 'message' => 'Code and Name are required.'], 422);
        }
        $this->db->query(
            "SELECT id FROM grading_schemas WHERE institution_id = ? AND code = ? AND id != ?",
            [$this->institutionId, $code, $id]
        );
        if ($this->db->fetch()) {
            $this->json(['status' => 'error', 'message' => 'Another scheme with this code exists.'], 422);
        }
        $this->db->query(
            "UPDATE grading_schemas SET code=?, name=?, min_mark=?, max_mark=?, is_embedded=?, max_ratio_mark=? WHERE id=?",
            [
                $code, $name,
                (float)$this->input('min_mark', 50),
                (float)$this->input('max_mark', 100),
                (int)(bool)$this->input('is_embedded'),
                (int)(bool)$this->input('max_ratio_mark'),
                $id
            ]
        );
        $this->db->query("SELECT * FROM grading_schemas WHERE id = ?", [$id]);
        $this->json(['status' => 'success', 'message' => 'Scheme updated.', 'schema' => $this->db->fetch()]);
    }

    public function destroy(int $id): void
    {
        $this->db->query(
            "SELECT id FROM grading_schemas WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) { $this->json(['status' => 'error', 'message' => 'Not found'], 404); }

        $this->db->query(
            "SELECT COUNT(*) AS cnt FROM academic_assessments WHERE grading_schema_id = ?",
            [$id]
        );
        $r = $this->db->fetch();
        if (($r['cnt'] ?? 0) > 0) {
            $this->json([
                'status'  => 'error',
                'message' => 'Cannot delete: scheme is linked to ' . $r['cnt'] . ' assessment(s).'
            ], 422);
        }
        $this->db->query("DELETE FROM grading_schemas WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $this->json(['status' => 'success', 'message' => 'Scheme deleted.', 'id' => $id]);
    }

    // ──────────────────────────────────────────────────────────────
    // CATEGORY CRUD
    // ──────────────────────────────────────────────────────────────
    public function storeCategory(): void
    {
        $schemaId = (int)$this->input('schema_id');
        $code     = strtoupper(trim($this->input('code', '')));
        $name     = trim($this->input('name', ''));
        if (!$schemaId || !$code || !$name) {
            $this->json(['status' => 'error', 'message' => 'Schema, Code and Name required.'], 422);
        }
        $this->db->insert('grading_schema_categories', [
            'schema_id'      => $schemaId,
            'institution_id' => $this->institutionId,
            'code'           => $code,
            'name'           => $name,
            'sort_order'     => (int)$this->input('sort_order', 0),
        ]);
        $newId = $this->db->lastInsertId();
        $this->db->query("SELECT * FROM grading_schema_categories WHERE id = ?", [$newId]);
        $this->json(['status' => 'success', 'message' => 'Category saved.', 'category' => $this->db->fetch()]);
    }

    public function destroyCategory(int $id): void
    {
        $this->db->query(
            "DELETE FROM grading_schema_categories WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->json(['status' => 'success', 'message' => 'Category deleted.', 'id' => $id]);
    }

    // ──────────────────────────────────────────────────────────────
    // COMPONENT CRUD
    // ──────────────────────────────────────────────────────────────
    public function storeComponent(): void
    {
        $schemaId   = (int)$this->input('schema_id');
        $categoryId = (int)$this->input('category_id') ?: null;
        $code       = strtoupper(trim($this->input('code', '')));
        $name       = trim($this->input('name', ''));
        if (!$schemaId || !$code || !$name) {
            $this->json(['status' => 'error', 'message' => 'Schema, Code and Name required.'], 422);
        }
        $this->db->insert('grading_mark_components', [
            'schema_id'      => $schemaId,
            'category_id'    => $categoryId,
            'institution_id' => $this->institutionId,
            'component_type' => $this->input('component_type', 'internal'),
            'code'           => $code,
            'name'           => $name,
            'min_mark'       => (float)$this->input('min_mark', 0),
            'max_mark'       => (float)$this->input('max_mark', 40),
            'sort_order'     => (int)$this->input('sort_order', 0),
        ]);
        $newId = $this->db->lastInsertId();
        $this->db->query("SELECT * FROM grading_mark_components WHERE id = ?", [$newId]);
        $this->json(['status' => 'success', 'message' => 'Component saved.', 'component' => $this->db->fetch()]);
    }

    public function updateComponent(int $id): void
    {
        $this->db->query(
            "SELECT id FROM grading_mark_components WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) { $this->json(['status' => 'error', 'message' => 'Not found'], 404); }

        $this->db->query(
            "UPDATE grading_mark_components SET component_type=?, code=?, name=?, min_mark=?, max_mark=?, sort_order=? WHERE id=?",
            [
                $this->input('component_type', 'internal'),
                strtoupper(trim($this->input('code', ''))),
                trim($this->input('name', '')),
                (float)$this->input('min_mark', 0),
                (float)$this->input('max_mark', 40),
                (int)$this->input('sort_order', 0),
                $id
            ]
        );
        $this->db->query("SELECT * FROM grading_mark_components WHERE id = ?", [$id]);
        $this->json(['status' => 'success', 'message' => 'Component updated.', 'component' => $this->db->fetch()]);
    }

    public function destroyComponent(int $id): void
    {
        $this->db->query(
            "DELETE FROM grading_mark_components WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->json(['status' => 'success', 'message' => 'Component deleted.', 'id' => $id]);
    }

    // ──────────────────────────────────────────────────────────────
    // SUB-COMPONENT CRUD
    // ──────────────────────────────────────────────────────────────
    public function storeSubComponent(): void
    {
        $componentId = (int)$this->input('component_id');
        $schemaId    = (int)$this->input('schema_id');
        $code        = strtoupper(trim($this->input('code', '')));
        $name        = trim($this->input('name', ''));
        if (!$componentId || !$code || !$name) {
            $this->json(['status' => 'error', 'message' => 'Component, Code and Name required.'], 422);
        }
        $this->db->insert('grading_sub_components', [
            'component_id'   => $componentId,
            'schema_id'      => $schemaId,
            'institution_id' => $this->institutionId,
            'code'           => $code,
            'name'           => $name,
            'max_mark'       => (float)$this->input('max_mark', 0),
            'sort_order'     => (int)$this->input('sort_order', 0),
        ]);
        $newId = $this->db->lastInsertId();
        $this->db->query("SELECT * FROM grading_sub_components WHERE id = ?", [$newId]);
        $this->json(['status' => 'success', 'message' => 'Sub-component saved.', 'sub_component' => $this->db->fetch()]);
    }

    public function destroySubComponent(int $id): void
    {
        $this->db->query(
            "DELETE FROM grading_sub_components WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->json(['status' => 'success', 'message' => 'Sub-component deleted.', 'id' => $id]);
    }

    // ──────────────────────────────────────────────────────────────
    // GRADE RULES CRUD
    // ──────────────────────────────────────────────────────────────
    public function storeRule(): void
    {
        $schemaId = (int)$this->input('schema_id');
        $label    = strtoupper(trim($this->input('grade_label', '')));
        if (!$schemaId || !$label) {
            $this->json(['status' => 'error', 'message' => 'Schema and Grade Label are required.'], 422);
        }
        $this->db->insert('grading_grade_rules', [
            'schema_id'      => $schemaId,
            'institution_id' => $this->institutionId,
            'grade_label'    => $label,
            'grade_point'    => (float)$this->input('grade_point', 0),
            'min_percentage' => (float)$this->input('min_percentage', 0),
            'max_percentage' => (float)$this->input('max_percentage', 100),
            'description'    => trim($this->input('description', '')) ?: null,
            'is_pass'        => (int)(bool)$this->input('is_pass', 1),
            'sort_order'     => (int)$this->input('sort_order', 0),
        ]);
        $newId = $this->db->lastInsertId();
        $this->db->query("SELECT * FROM grading_grade_rules WHERE id = ?", [$newId]);
        $this->json(['status' => 'success', 'message' => 'Grade rule saved.', 'rule' => $this->db->fetch()]);
    }

    public function destroyRule(int $id): void
    {
        $this->db->query(
            "DELETE FROM grading_grade_rules WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $this->json(['status' => 'success', 'message' => 'Grade rule deleted.', 'id' => $id]);
    }
}
