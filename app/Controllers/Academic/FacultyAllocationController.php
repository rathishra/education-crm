<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class FacultyAllocationController extends BaseController
{
    public function index(): void
    {
        $batchId   = (int)($_GET['batch_id']   ?? 0);
        $subjectId = (int)($_GET['subject_id'] ?? 0);

        $where  = "fa.institution_id = ? AND fa.status = 'active'";
        $params = [$this->institutionId];
        if ($batchId)   { $where .= " AND fa.batch_id = ?";   $params[] = $batchId; }
        if ($subjectId) { $where .= " AND fa.subject_id = ?"; $params[] = $subjectId; }

        $this->db->query(
            "SELECT fa.*,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    u.email AS faculty_email,
                    s.subject_name, s.subject_code, s.credits,
                    b.program_name, b.batch_term,
                    sec.section_name
             FROM faculty_subject_allocations fa
             JOIN users u ON u.id = fa.faculty_id
             JOIN subjects s ON s.id = fa.subject_id
             LEFT JOIN academic_batches b ON b.id = fa.batch_id
             LEFT JOIN academic_sections sec ON sec.id = fa.section_id
             WHERE {$where}
             ORDER BY b.program_name, s.subject_name",
            $params
        );
        $allocations = $this->db->fetchAll();

        // Workload summary per faculty
        $this->db->query(
            "SELECT fa.faculty_id,
                    CONCAT(u.first_name,' ',u.last_name) AS faculty_name,
                    SUM(fa.hours_per_week) AS total_hours,
                    COUNT(*) AS subject_count
             FROM faculty_subject_allocations fa
             JOIN users u ON u.id = fa.faculty_id
             WHERE fa.institution_id = ? AND fa.status = 'active'
             GROUP BY fa.faculty_id ORDER BY total_hours DESC",
            [$this->institutionId]
        );
        $workload = $this->db->fetchAll();

        $this->db->query("SELECT id, program_name, batch_term FROM academic_batches WHERE institution_id = ? AND status = 'active' ORDER BY program_name", [$this->institutionId]);
        $batches = $this->db->fetchAll();

        $this->db->query("SELECT id, subject_code, subject_name FROM subjects WHERE institution_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY subject_name", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->view('academic/faculty-allocation/index', compact('allocations', 'workload', 'batches', 'subjects', 'batchId', 'subjectId'));
    }

    public function create(): void
    {
        $this->db->query(
            "SELECT u.id, CONCAT(u.first_name,' ',u.last_name) AS name, u.email
             FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
             WHERE u.is_active = 1
             ORDER BY u.first_name",
            [$this->institutionId]
        );
        $faculty = $this->db->fetchAll();

        $this->db->query("SELECT id, subject_code, subject_name, subject_type, credits FROM subjects WHERE institution_id = ? AND status = 'active' AND deleted_at IS NULL ORDER BY subject_name", [$this->institutionId]);
        $subjects = $this->db->fetchAll();

        $this->db->query("SELECT id, program_name, batch_term, total_semesters FROM academic_batches WHERE institution_id = ? AND status = 'active' ORDER BY program_name", [$this->institutionId]);
        $batches = $this->db->fetchAll();

        $this->db->query("SELECT id, section_name, batch_id FROM academic_sections WHERE institution_id = ? AND status = 'active' ORDER BY section_name", [$this->institutionId]);
        $sections = $this->db->fetchAll();

        $this->view('academic/faculty-allocation/create', compact('faculty', 'subjects', 'batches', 'sections'));
    }

    public function store(): void
    {
        verifyCsrf();

        $facultyId = (int)$this->input('faculty_id');
        $subjectId = (int)$this->input('subject_id');
        $batchId   = (int)$this->input('batch_id') ?: null;
        $sectionId = (int)$this->input('section_id') ?: null;
        $semester  = (int)$this->input('semester') ?: null;
        $type      = $this->input('allocation_type', 'theory');
        $labBatch  = (int)$this->input('lab_batch_number') ?: null;
        $hours     = (int)$this->input('hours_per_week', 0);

        if (!$facultyId || !$subjectId) {
            $this->backWithErrors(['faculty_id' => 'Faculty and Subject are required.']);
            return;
        }

        // Duplicate check
        $this->db->query(
            "SELECT id FROM faculty_subject_allocations
             WHERE faculty_id=? AND subject_id=? AND batch_id<=>? AND section_id<=>? AND allocation_type=? AND status='active'",
            [$facultyId, $subjectId, $batchId, $sectionId, $type]
        );
        if ($this->db->fetch()) {
            $this->backWithErrors(['faculty_id' => 'This allocation already exists.']);
            return;
        }

        $this->db->insert('faculty_subject_allocations', [
            'institution_id'   => $this->institutionId,
            'faculty_id'       => $facultyId,
            'subject_id'       => $subjectId,
            'batch_id'         => $batchId,
            'section_id'       => $sectionId,
            'semester'         => $semester,
            'allocation_type'  => $type,
            'lab_batch_number' => $labBatch,
            'hours_per_week'   => $hours,
            'status'           => 'active',
            'allocated_by'     => $this->user['id'],
        ]);

        $this->logAudit('faculty_allocation_create', 'faculty_subject_allocations', $this->db->lastInsertId());
        $this->redirectWith(url('academic/faculty-allocation'), 'success', 'Faculty allocated successfully.');
    }

    public function destroy(int $id): void
    {
        verifyCsrf();
        $this->db->query(
            "UPDATE faculty_subject_allocations SET status='inactive' WHERE id=? AND institution_id=?",
            [$id, $this->institutionId]
        );
        $this->logAudit('faculty_allocation_remove', 'faculty_subject_allocations', $id);
        $this->redirectWith(url('academic/faculty-allocation'), 'success', 'Allocation removed.');
    }

    // AJAX: sections by batch
    public function ajaxSections(): void
    {
        $batchId = (int)($_GET['batch_id'] ?? 0);
        $this->db->query(
            "SELECT id, section_name FROM academic_sections WHERE batch_id=? AND institution_id=? AND status='active' ORDER BY section_name",
            [$batchId, $this->institutionId]
        );
        header('Content-Type: application/json');
        echo json_encode($this->db->fetchAll());
        exit;
    }
}
