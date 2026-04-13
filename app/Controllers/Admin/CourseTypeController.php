<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CourseTypeController extends BaseController
{
    // =========================================================
    // INDEX
    // =========================================================
    public function index(): void
    {
        $this->authorize('courses.view');

        $search = $this->input('search', '');
        $status = $this->input('status', '');

        $where  = 'ct.institution_id = ? AND ct.deleted_at IS NULL';
        $params = [$this->institutionId];

        if ($search) {
            $where .= ' AND (ct.code LIKE ? OR ct.description LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where .= ' AND ct.status = ?';
            $params[] = $status;
        }

        $this->db->query(
            "SELECT ct.*,
                    (SELECT COUNT(*) FROM courses c WHERE c.course_type_id = ct.id AND c.deleted_at IS NULL) AS course_count
             FROM course_types ct
             WHERE {$where}
             ORDER BY ct.code",
            $params
        );
        $courseTypes = $this->db->fetchAll();

        $this->view('course_types/index', compact('courseTypes', 'search', 'status'));
    }

    // =========================================================
    // CREATE
    // =========================================================
    public function create(): void
    {
        $this->authorize('courses.manage');
        $this->view('course_types/form', ['courseType' => null, 'years' => []]);
    }

    // =========================================================
    // STORE
    // =========================================================
    public function store(): void
    {
        $this->authorize('courses.manage');
        $data = $this->postData();

        $errors = $this->validate($data, [
            'code'            => 'required',
            'description'     => 'required',
            'course_category' => 'required',
            'degree_type'     => 'required',
            'duration'        => 'required',
            'no_of_semester'  => 'required',
        ]);
        if ($errors) { $this->backWithErrors($errors); return; }

        // Duplicate code check
        $this->db->query(
            "SELECT id FROM course_types WHERE institution_id = ? AND code = ? AND deleted_at IS NULL",
            [$this->institutionId, strtoupper(trim($data['code']))]
        );
        if ($this->db->fetch()) {
            $this->backWithErrors(['code' => 'Course type code already exists.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            $id = $this->db->insert('course_types', [
                'institution_id'    => $this->institutionId,
                'code'              => strtoupper(sanitize($data['code'])),
                'description'       => sanitize($data['description']),
                'short_description' => sanitize($data['short_description'] ?? ''),
                'course_category'   => $data['course_category'],
                'degree_type'       => $data['degree_type'],
                'duration'          => (int)$data['duration'],
                'duration_unit'     => $data['duration_unit'] ?? 'year',
                'no_of_semester'    => (int)$data['no_of_semester'],
                'status'            => 'active',
                'created_by'        => $this->user['id'],
            ]);

            $this->_saveYears($id, (int)$data['duration'], $data['duration_unit'] ?? 'year', (int)$data['no_of_semester'], $data['year_semesters'] ?? []);

            $this->logAudit('course_type_created', 'course_types', $id);
            $this->db->commit();

            $this->redirectWith(url('course-types'), 'success', 'Course type created successfully.');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->redirectWith(url('course-types/create'), 'error', 'Failed: ' . $e->getMessage());
        }
    }

    // =========================================================
    // EDIT
    // =========================================================
    public function edit(int $id): void
    {
        $this->authorize('courses.manage');
        $courseType = $this->_find($id);
        if (!$courseType) { $this->redirectWith(url('course-types'), 'error', 'Not found.'); return; }

        $this->db->query("SELECT * FROM course_type_years WHERE course_type_id = ? ORDER BY year_code", [$id]);
        $years = $this->db->fetchAll();

        $this->view('course_types/form', compact('courseType', 'years'));
    }

    // =========================================================
    // UPDATE
    // =========================================================
    public function update(int $id): void
    {
        $this->authorize('courses.manage');
        $courseType = $this->_find($id);
        if (!$courseType) { $this->redirectWith(url('course-types'), 'error', 'Not found.'); return; }

        $data   = $this->postData();
        $errors = $this->validate($data, [
            'code'            => 'required',
            'description'     => 'required',
            'course_category' => 'required',
            'degree_type'     => 'required',
            'duration'        => 'required',
            'no_of_semester'  => 'required',
        ]);
        if ($errors) { $this->backWithErrors($errors); return; }

        // Duplicate code check (exclude self)
        $this->db->query(
            "SELECT id FROM course_types WHERE institution_id = ? AND code = ? AND id != ? AND deleted_at IS NULL",
            [$this->institutionId, strtoupper(trim($data['code'])), $id]
        );
        if ($this->db->fetch()) {
            $this->backWithErrors(['code' => 'Course type code already in use.']);
            return;
        }

        try {
            $this->db->beginTransaction();

            $this->db->update('course_types', [
                'code'              => strtoupper(sanitize($data['code'])),
                'description'       => sanitize($data['description']),
                'short_description' => sanitize($data['short_description'] ?? ''),
                'course_category'   => $data['course_category'],
                'degree_type'       => $data['degree_type'],
                'duration'          => (int)$data['duration'],
                'duration_unit'     => $data['duration_unit'] ?? 'year',
                'no_of_semester'    => (int)$data['no_of_semester'],
            ], 'id = ?', [$id]);

            // Rebuild year rows
            $this->db->delete('course_type_years', 'course_type_id = ?', [$id]);
            $this->_saveYears($id, (int)$data['duration'], $data['duration_unit'] ?? 'year', (int)$data['no_of_semester'], $data['year_semesters'] ?? []);

            $this->logAudit('course_type_updated', 'course_types', $id);
            $this->db->commit();

            $this->redirectWith(url('course-types'), 'success', 'Course type updated successfully.');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->redirectWith(url("course-types/{$id}/edit"), 'error', 'Failed: ' . $e->getMessage());
        }
    }

    // =========================================================
    // DELETE
    // =========================================================
    public function delete(int $id): void
    {
        $this->authorize('courses.manage');
        $courseType = $this->_find($id);
        if (!$courseType) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); return; }

        // Check if used by any course
        $this->db->query("SELECT COUNT(*) AS c FROM courses WHERE course_type_id = ? AND deleted_at IS NULL", [$id]);
        $cnt = $this->db->fetch();
        if (($cnt['c'] ?? 0) > 0) {
            $this->json(['status' => 'error', 'message' => 'Cannot delete — this type is used by ' . $cnt['c'] . ' course(s).'], 422);
            return;
        }

        $this->db->query("UPDATE course_types SET deleted_at = NOW() WHERE id = ?", [$id]);
        $this->logAudit('course_type_deleted', 'course_types', $id);
        $this->json(['status' => 'success', 'message' => 'Course type deleted.']);
    }

    // =========================================================
    // TOGGLE STATUS
    // =========================================================
    public function toggleStatus(int $id): void
    {
        $this->authorize('courses.manage');
        $ct = $this->_find($id);
        if (!$ct) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); return; }

        $new = $ct['status'] === 'active' ? 'inactive' : 'active';
        $this->db->query("UPDATE course_types SET status = ? WHERE id = ?", [$new, $id]);
        $this->json(['status' => 'success', 'new_status' => $new]);
    }

    // =========================================================
    // AJAX — get course types for dropdowns
    // =========================================================
    public function ajaxList(): void
    {
        $this->db->query(
            "SELECT id, code, description FROM course_types
             WHERE institution_id = ? AND status = 'active' AND deleted_at IS NULL
             ORDER BY code",
            [$this->institutionId]
        );
        $this->json($this->db->fetchAll());
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================
    private function _find(int $id): ?array
    {
        $this->db->query(
            "SELECT * FROM course_types WHERE id = ? AND institution_id = ? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        return $this->db->fetch() ?: null;
    }

    private function _saveYears(int $ctId, int $duration, string $unit, int $semPerYear, array $overrides): void
    {
        // Only generate year rows if duration unit is 'year'
        if ($unit !== 'year') return;

        for ($y = 1; $y <= $duration; $y++) {
            $sem = isset($overrides[$y]) ? (int)$overrides[$y] : $semPerYear;
            $this->db->insert('course_type_years', [
                'course_type_id' => $ctId,
                'year_code'      => $y,
                'no_of_semester' => $sem > 0 ? $sem : $semPerYear,
            ]);
        }
    }
}
