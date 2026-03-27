<?php
namespace App\Controllers\Academic;

use App\Controllers\BaseController;

class SubjectController extends BaseController
{
    // ──────────────────────────────────────────────────────────────
    // LIST
    // ──────────────────────────────────────────────────────────────
    public function index(): void
    {
        $search  = trim($_GET['search']       ?? '');
        $type    = trim($_GET['type']         ?? '');
        $sem     = trim($_GET['semester']     ?? '');
        $status  = trim($_GET['status']       ?? '');
        $deptId  = (int)($_GET['dept_id']     ?? 0);
        $courseId= (int)($_GET['course_id']   ?? 0);
        $elective= trim($_GET['elective']     ?? '');
        $groupBy = trim($_GET['group_by']     ?? '');   // 'semester'

        $where  = "s.deleted_at IS NULL AND s.institution_id = ?";
        $params = [$this->institutionId];

        if ($search  !== '') { $like = '%'.$search.'%'; $where .= " AND (s.subject_name LIKE ? OR s.subject_code LIKE ? OR s.short_name LIKE ?)"; $params = array_merge($params, [$like,$like,$like]); }
        if ($type    !== '') { $where .= " AND s.subject_type = ?";    $params[] = $type; }
        if ($sem     !== '') { $where .= " AND s.semester = ?";        $params[] = $sem; }
        if ($status  !== '') { $where .= " AND s.status = ?";          $params[] = $status; }
        if ($deptId)         { $where .= " AND s.department_id = ?";   $params[] = $deptId; }
        if ($courseId)       { $where .= " AND s.course_id = ?";       $params[] = $courseId; }
        if ($elective !== '') { $where .= " AND s.is_elective = ?";    $params[] = (int)$elective; }

        $this->db->query(
            "SELECT s.*,
                    d.name AS dept_name,
                    c.name AS course_name, c.code AS course_code,
                    (SELECT COUNT(*) FROM faculty_subject_allocations fa WHERE fa.subject_id=s.id AND fa.status='active') AS faculty_count,
                    (SELECT COUNT(*) FROM academic_assessments aa WHERE aa.subject_id=s.id) AS assessment_count,
                    (SELECT COUNT(*) FROM lms_materials lm WHERE lm.subject_id=s.id AND lm.deleted_at IS NULL) AS material_count
             FROM subjects s
             LEFT JOIN departments d ON d.id = s.department_id
             LEFT JOIN courses     c ON c.id = s.course_id
             WHERE {$where}
             ORDER BY COALESCE(s.semester,99) ASC, s.subject_code ASC",
            $params
        );
        $subjects = $this->db->fetchAll();

        // Group by semester if requested
        $grouped = [];
        if ($groupBy === 'semester') {
            foreach ($subjects as $s) {
                $key = $s['semester'] ? 'Semester '.$s['semester'] : 'Unassigned';
                $grouped[$key][] = $s;
            }
        }

        // Stats
        $this->db->query(
            "SELECT
                COUNT(*)                             AS total,
                SUM(status='active')                 AS active,
                SUM(status='inactive')               AS inactive,
                SUM(subject_type='theory')           AS theory,
                SUM(subject_type='lab')              AS lab,
                SUM(subject_type='tutorial')         AS tutorial,
                SUM(subject_type='project')          AS project,
                SUM(is_elective=1)                   AS elective,
                ROUND(SUM(COALESCE(credits,0)),1)    AS total_credits,
                ROUND(AVG(COALESCE(credits,0)),1)    AS avg_credits
             FROM subjects WHERE deleted_at IS NULL AND institution_id = ?",
            [$this->institutionId]
        );
        $stats = $this->db->fetch();

        // Credits per semester breakdown
        $this->db->query(
            "SELECT semester, COUNT(*) AS cnt, SUM(COALESCE(credits,0)) AS credits
             FROM subjects WHERE deleted_at IS NULL AND institution_id = ? AND semester IS NOT NULL
             GROUP BY semester ORDER BY semester",
            [$this->institutionId]
        );
        $semBreakdown = $this->db->fetchAll();

        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM courses WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->view('academic/subjects/index', compact(
            'subjects','grouped','stats','semBreakdown',
            'departments','courses',
            'search','type','sem','status','deptId','courseId','elective','groupBy'
        ));
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(int $id): void
    {
        $this->db->query(
            "SELECT s.*, d.name AS dept_name, c.name AS course_name, c.code AS course_code,
                    u.first_name AS creator_first, u.last_name AS creator_last
             FROM subjects s
             LEFT JOIN departments d ON d.id = s.department_id
             LEFT JOIN courses     c ON c.id = s.course_id
             LEFT JOIN users       u ON u.id = s.created_by
             WHERE s.id = ? AND s.institution_id = ? AND s.deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        $subject = $this->db->fetch();
        if (!$subject) {
            $this->redirectWith(url('academic/subjects'), 'error', 'Subject not found.');
            return;
        }

        // Faculty allocations
        $this->db->query(
            "SELECT fa.*, CONCAT(u.first_name,' ',u.last_name) AS faculty_name, u.email AS faculty_email,
                    b.program_name, b.batch_term, sec.section_name
             FROM faculty_subject_allocations fa
             JOIN users u ON u.id = fa.faculty_id
             LEFT JOIN academic_batches b ON b.id = fa.batch_id
             LEFT JOIN academic_sections sec ON sec.id = fa.section_id
             WHERE fa.subject_id = ? AND fa.institution_id = ? AND fa.status = 'active'",
            [$id, $this->institutionId]
        );
        $facultyList = $this->db->fetchAll();

        // Assessment configs
        $this->db->query(
            "SELECT aa.*, b.program_name, b.batch_term
             FROM academic_assessments aa
             LEFT JOIN academic_batches b ON b.id = aa.batch_id
             WHERE aa.subject_id = ? AND aa.institution_id = ?
             ORDER BY aa.created_at DESC LIMIT 20",
            [$id, $this->institutionId]
        );
        $assessments = $this->db->fetchAll();

        // LMS materials
        $this->db->query(
            "SELECT lm.*, CONCAT(u.first_name,' ',u.last_name) AS faculty_name
             FROM lms_materials lm
             JOIN users u ON u.id = lm.faculty_id
             WHERE lm.subject_id = ? AND lm.institution_id = ? AND lm.deleted_at IS NULL
             ORDER BY lm.created_at DESC LIMIT 10",
            [$id, $this->institutionId]
        );
        $materials = $this->db->fetchAll();

        // Attendance summary
        $this->db->query(
            "SELECT COUNT(*) AS sessions,
                    SUM(aas.present_count) AS total_present,
                    SUM(aas.absent_count)  AS total_absent
             FROM academic_attendance_sessions aas
             WHERE aas.subject_id = ? AND aas.institution_id = ?",
            [$id, $this->institutionId]
        );
        $attSummary = $this->db->fetch();

        $this->view('academic/subjects/show', compact('subject','facultyList','assessments','materials','attSummary'));
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE / STORE
    // ──────────────────────────────────────────────────────────────
    public function create(): void
    {
        $this->db->query("SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM courses WHERE institution_id = ? ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, subject_code, subject_name FROM subjects
             WHERE institution_id = ? AND deleted_at IS NULL AND status = 'active'
             ORDER BY subject_code",
            [$this->institutionId]
        );
        $allSubjects = $this->db->fetchAll();

        $this->view('academic/subjects/create', compact('departments', 'courses', 'allSubjects'));
    }

    public function store(): void
    {
        verifyCsrf();

        $code = strtoupper(trim($this->input('subject_code', '')));
        $name = trim($this->input('subject_name', ''));

        $errors = [];
        if (empty($code)) $errors['subject_code'] = 'Subject code is required.';
        if (empty($name)) $errors['subject_name'] = 'Subject name is required.';

        if (empty($errors)) {
            $this->db->query(
                "SELECT id FROM subjects WHERE institution_id=? AND subject_code=? AND deleted_at IS NULL",
                [$this->institutionId, $code]
            );
            if ($this->db->fetch()) $errors['subject_code'] = 'Subject code already exists.';
        }

        if (!empty($errors)) { $this->backWithErrors($errors); return; }

        $theoryH  = (int)$this->input('theory_hours', 3);
        $labH     = (int)$this->input('lab_hours', 0);
        $tutH     = (int)$this->input('tutorial_hours', 0);
        $totalH   = (int)$this->input('hours_per_week', $theoryH + $labH + $tutH);

        $this->db->insert('subjects', array_merge($this->_subjectPayload(), [
            'institution_id' => $this->institutionId,
            'subject_code'   => $code,
            'subject_name'   => $name,
            'status'         => 'active',
            'created_by'     => $this->user['id'],
        ]));

        $newId = $this->db->lastInsertId();
        $this->logAudit('subject_create', 'subjects', $newId);
        $this->redirectWith(url('academic/subjects/'.$newId), 'success', 'Subject "'.$name.'" created successfully.');
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT / UPDATE
    // ──────────────────────────────────────────────────────────────
    public function edit(int $id): void
    {
        $this->db->query(
            "SELECT * FROM subjects WHERE id=? AND institution_id=? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        $subject = $this->db->fetch();
        if (!$subject) { $this->redirectWith(url('academic/subjects'),'error','Subject not found.'); return; }

        $this->db->query("SELECT id, name FROM departments WHERE institution_id=? ORDER BY name", [$this->institutionId]);
        $departments = $this->db->fetchAll();

        $this->db->query("SELECT id, name, code FROM courses WHERE institution_id=? ORDER BY name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query(
            "SELECT id, subject_code, subject_name FROM subjects
             WHERE institution_id=? AND deleted_at IS NULL AND status='active' AND id != ?
             ORDER BY subject_code",
            [$this->institutionId, $id]
        );
        $allSubjects = $this->db->fetchAll();

        $this->view('academic/subjects/edit', compact('subject','departments','courses','allSubjects'));
    }

    public function update(int $id): void
    {
        verifyCsrf();

        $this->db->query("SELECT id FROM subjects WHERE id=? AND institution_id=? AND deleted_at IS NULL", [$id,$this->institutionId]);
        if (!$this->db->fetch()) { $this->redirectWith(url('academic/subjects'),'error','Subject not found.'); return; }

        $code = strtoupper(trim($this->input('subject_code','')));
        $name = trim($this->input('subject_name',''));

        $errors = [];
        if (empty($code)) $errors['subject_code'] = 'Subject code is required.';
        if (empty($name)) $errors['subject_name'] = 'Subject name is required.';
        if (empty($errors)) {
            $this->db->query(
                "SELECT id FROM subjects WHERE institution_id=? AND subject_code=? AND deleted_at IS NULL AND id!=?",
                [$this->institutionId, $code, $id]
            );
            if ($this->db->fetch()) $errors['subject_code'] = 'Subject code already in use.';
        }
        if (!empty($errors)) { $this->backWithErrors($errors); return; }

        $this->db->update('subjects',
            array_merge($this->_subjectPayload(), [
                'subject_code' => $code,
                'subject_name' => $name,
                'status'       => $this->input('status','active'),
            ]),
            'id = ?', [$id]
        );

        $this->logAudit('subject_update','subjects',$id);
        $this->redirectWith(url('academic/subjects/'.$id),'success','Subject updated successfully.');
    }

    // ──────────────────────────────────────────────────────────────
    // DELETE
    // ──────────────────────────────────────────────────────────────
    public function delete(int $id): void
    {
        verifyCsrf();
        $this->db->query("UPDATE subjects SET deleted_at=NOW() WHERE id=? AND institution_id=?", [$id,$this->institutionId]);
        $this->logAudit('subject_delete','subjects',$id);
        $this->redirectWith(url('academic/subjects'),'success','Subject deleted.');
    }

    // ──────────────────────────────────────────────────────────────
    // TOGGLE STATUS (AJAX)
    // ──────────────────────────────────────────────────────────────
    public function toggleStatus(int $id): void
    {
        verifyCsrf();
        $this->db->query(
            "SELECT id, status FROM subjects WHERE id=? AND institution_id=? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        $s = $this->db->fetch();
        if (!$s) { http_response_code(404); echo json_encode(['error'=>'Not found']); exit; }

        $newStatus = $s['status']==='active' ? 'inactive' : 'active';
        $this->db->query("UPDATE subjects SET status=? WHERE id=?", [$newStatus,$id]);
        $this->logAudit('subject_toggle_status','subjects',$id);
        header('Content-Type: application/json');
        echo json_encode(['status'=>$newStatus,'message'=>'Status updated to '.ucfirst($newStatus)]);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // DUPLICATE
    // ──────────────────────────────────────────────────────────────
    public function duplicate(int $id): void
    {
        verifyCsrf();
        $this->db->query(
            "SELECT * FROM subjects WHERE id=? AND institution_id=? AND deleted_at IS NULL",
            [$id, $this->institutionId]
        );
        $s = $this->db->fetch();
        if (!$s) { $this->redirectWith(url('academic/subjects'),'error','Subject not found.'); return; }

        unset($s['id'], $s['created_at'], $s['updated_at'], $s['deleted_at']);
        $s['subject_code'] = $s['subject_code'].'-COPY';
        $s['subject_name'] = $s['subject_name'].' (Copy)';
        $s['status']       = 'inactive';
        $s['created_by']   = $this->user['id'];

        $this->db->insert('subjects', $s);
        $newId = $this->db->lastInsertId();
        $this->logAudit('subject_duplicate','subjects',$newId);
        $this->redirectWith(url('academic/subjects/'.$newId.'/edit'),'success','Subject duplicated. Update code and name before activating.');
    }

    // ──────────────────────────────────────────────────────────────
    // BULK ACTIONS
    // ──────────────────────────────────────────────────────────────
    public function bulkAction(): void
    {
        verifyCsrf();
        $action = $this->input('bulk_action','');
        $ids    = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])));

        if (empty($ids)) { $this->redirectWith(url('academic/subjects'),'warning','No subjects selected.'); return; }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'activate':
                $this->db->query(
                    "UPDATE subjects SET status='active' WHERE id IN ({$placeholders}) AND institution_id=?",
                    array_merge($ids, [$this->institutionId])
                );
                $msg = count($ids).' subject(s) activated.';
                break;
            case 'deactivate':
                $this->db->query(
                    "UPDATE subjects SET status='inactive' WHERE id IN ({$placeholders}) AND institution_id=?",
                    array_merge($ids, [$this->institutionId])
                );
                $msg = count($ids).' subject(s) deactivated.';
                break;
            case 'delete':
                $this->db->query(
                    "UPDATE subjects SET deleted_at=NOW() WHERE id IN ({$placeholders}) AND institution_id=?",
                    array_merge($ids, [$this->institutionId])
                );
                $msg = count($ids).' subject(s) deleted.';
                break;
            default:
                $this->redirectWith(url('academic/subjects'),'error','Unknown action.'); return;
        }

        $this->redirectWith(url('academic/subjects'),'success',$msg);
    }

    // ──────────────────────────────────────────────────────────────
    // EXPORT CSV
    // ──────────────────────────────────────────────────────────────
    public function export(): void
    {
        $this->db->query(
            "SELECT s.subject_code, s.subject_name, s.short_name, s.subject_type,
                    s.semester, s.credits, s.hours_per_week, s.theory_hours, s.lab_hours,
                    s.tutorial_hours, s.is_elective, s.regulation, s.status,
                    d.name AS department, c.name AS course
             FROM subjects s
             LEFT JOIN departments d ON d.id=s.department_id
             LEFT JOIN courses c ON c.id=s.course_id
             WHERE s.institution_id=? AND s.deleted_at IS NULL
             ORDER BY s.semester, s.subject_code",
            [$this->institutionId]
        );
        $rows = $this->db->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="subjects_'.date('Y-m-d').'.csv"');
        $out = fopen('php://output','w');
        fputcsv($out,['Code','Name','Short Name','Type','Semester','Credits','Hrs/Wk','Theory Hrs','Lab Hrs','Tutorial Hrs','Elective','Regulation','Status','Department','Course']);
        foreach ($rows as $r) {
            fputcsv($out,[
                $r['subject_code'],$r['subject_name'],$r['short_name']??'',
                $r['subject_type'],$r['semester']??'',$r['credits'],
                $r['hours_per_week'],$r['theory_hours'],$r['lab_hours'],$r['tutorial_hours'],
                $r['is_elective']?'Yes':'No',$r['regulation']??'',$r['status'],
                $r['department']??'',$r['course']??''
            ]);
        }
        fclose($out);
        exit;
    }

    // ──────────────────────────────────────────────────────────────
    // SHARED PAYLOAD (all DB fields from form)
    // ──────────────────────────────────────────────────────────────
    private function _subjectPayload(): array
    {
        $theoryH = (int)$this->input('theory_hours', 3);
        $labH    = (int)$this->input('lab_hours', 0);
        $tutH    = (int)$this->input('tutorial_hours', 0);
        $totalH  = (int)$this->input('hours_per_week', $theoryH + $labH + $tutH);

        return [
            'department_id'      => (int)$this->input('department_id') ?: null,
            'course_id'          => (int)$this->input('course_id') ?: null,
            'short_name'         => trim($this->input('short_name','')) ?: null,
            'subject_type'       => $this->input('subject_type','theory'),
            'is_elective'        => (int)(bool)$this->input('is_elective'),
            'credits'            => (float)$this->input('credits', 3),
            'hours_per_week'     => $totalH,
            'theory_hours'       => $theoryH,
            'lab_hours'          => $labH,
            'tutorial_hours'     => $tutH,
            'semester'           => (int)$this->input('semester') ?: null,
            'regulation'         => trim($this->input('regulation','')) ?: null,
            'syllabus_url'       => trim($this->input('syllabus_url','')) ?: null,
            'description'        => trim($this->input('description','')) ?: null,
            // Extended columns
            'short_label'        => trim($this->input('short_label','')) ?: null,
            'delivery_mode'      => trim($this->input('delivery_mode','')) ?: null,
            'priority_level'     => trim($this->input('priority_level','')) ?: null,
            'curriculum_stream'  => trim($this->input('curriculum_stream','')) ?: null,
            'architecture'       => trim($this->input('architecture','')) ?: null,
            'governing_body'     => trim($this->input('governing_body','')) ?: null,
            'is_sub_module'      => (int)(bool)$this->input('is_sub_module'),
            'local_language'     => (int)(bool)$this->input('local_language'),
            'secondary_language' => (int)(bool)$this->input('secondary_language'),
            'valid_from'         => trim($this->input('valid_from','')) ?: null,
            'valid_until'        => trim($this->input('valid_until','')) ?: null,
            'grading_scale'      => trim($this->input('grading_scale','')) ?: null,
            'external_exam_code' => trim($this->input('external_exam_code','')) ?: null,
            'affects_gpa'        => (int)(bool)$this->input('affects_gpa'),
            'review_authority'   => trim($this->input('review_authority','')) ?: null,
            'attach_syllabus'    => (int)(bool)$this->input('attach_syllabus'),
            'track_sessions'     => (int)(bool)$this->input('track_sessions'),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // AJAX helpers
    // ──────────────────────────────────────────────────────────────
    public function ajaxByCourse(): void
    {
        $courseId = (int)($_GET['course_id'] ?? 0);
        $semester = (int)($_GET['semester']  ?? 0);
        $w = "institution_id=? AND status='active' AND deleted_at IS NULL";
        $p = [$this->institutionId];
        if ($courseId) { $w .= " AND course_id=?"; $p[] = $courseId; }
        if ($semester) { $w .= " AND semester=?";  $p[] = $semester; }
        $this->db->query("SELECT id, subject_code, subject_name, credits, subject_type FROM subjects WHERE {$w} ORDER BY semester, subject_code", $p);
        header('Content-Type: application/json');
        echo json_encode($this->db->fetchAll());
        exit;
    }
}
