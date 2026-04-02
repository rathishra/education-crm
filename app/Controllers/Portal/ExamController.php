<?php
namespace App\Controllers\Portal;

class ExamController extends PortalBaseController
{
    public function index(): void
    {
        $batchId = $this->getStudentBatchId();
        $inst    = $this->institutionId;
        $db      = $this->db;

        $upcoming = [];
        $past     = [];

        if ($batchId) {
            $db->query(
                "SELECT aa.*,
                        sub.subject_name, sub.subject_code,
                        gs.schema_name AS grading_schema
                 FROM academic_assessments aa
                 JOIN subjects sub ON sub.id = aa.subject_id
                 LEFT JOIN grading_schemas gs ON gs.id = aa.grading_schema_id
                 WHERE aa.batch_id = ? AND aa.institution_id = ? AND aa.status = 'active'
                 ORDER BY aa.assessment_date ASC",
                [$batchId, $inst]
            );
            $allExams = $db->fetchAll();

            foreach ($allExams as $ex) {
                if (!empty($ex['assessment_date']) && $ex['assessment_date'] >= date('Y-m-d')) {
                    $upcoming[] = $ex;
                } else {
                    $past[] = $ex;
                }
            }
        }

        $pageTitle = 'Exams & Assessments';
        $this->view('portal/exams/index', compact('upcoming', 'past', 'pageTitle'));
    }

    public function results(): void
    {
        $sid  = $this->studentId;
        $inst = $this->institutionId;
        $db   = $this->db;

        $db->query(
            "SELECT aa.assessment_name, aa.assessment_type, aa.assessment_date,
                    aa.max_marks, aa.passing_marks,
                    sub.subject_name, sub.subject_code,
                    aam.marks_obtained, aam.is_absent, aam.remarks, aam.grade,
                    ROUND(aam.marks_obtained / NULLIF(aa.max_marks,0) * 100, 1) AS percentage
             FROM academic_assessment_marks aam
             JOIN academic_assessments aa ON aa.id = aam.assessment_id
             JOIN subjects sub             ON sub.id = aa.subject_id
             WHERE aam.student_id = ? AND aa.institution_id = ?
             ORDER BY aa.assessment_date DESC, sub.subject_name",
            [$sid, $inst]
        );
        $results = $db->fetchAll();

        // Group by subject for grade card
        $bySubject = [];
        foreach ($results as $row) {
            $subKey = $row['subject_name'];
            if (!isset($bySubject[$subKey])) $bySubject[$subKey] = [];
            $bySubject[$subKey][] = $row;
        }

        $pageTitle = 'Exam Results';
        $this->view('portal/exams/results', compact('results', 'bySubject', 'pageTitle'));
    }
}
