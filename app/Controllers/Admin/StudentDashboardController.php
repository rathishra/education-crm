<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Student;

class StudentDashboardController extends BaseController
{
    public function index(): void
    {
        $this->authorize('students.view');

        $studentModel = new Student();
        $stats = $studentModel->getStats();

        // Let's also fetch department wise count
        $instWhere = "";
        $params = [];
        if ($this->institutionId) {
            $instWhere = " AND s.institution_id = ?";
            $params[] = $this->institutionId;
        }

        $db = db();
        $db->query(
            "SELECT d.name as department_name, COUNT(s.id) as cnt
             FROM students s
             JOIN departments d ON d.id = s.department_id
             WHERE s.deleted_at IS NULL {$instWhere}
             GROUP BY s.department_id, d.name
             ORDER BY cnt DESC",
            $params
        );
        $departmentWise = $db->fetchAll();

        // Get Recent Admissions
        $db->query(
            "SELECT id, first_name, last_name, student_id_number, admission_date, course_id
             FROM students s
             WHERE deleted_at IS NULL {$instWhere}
             ORDER BY created_at DESC LIMIT 5",
            $params
        );
        $recentAdmissions = $db->fetchAll();

        $this->view('students/dashboard', compact('stats', 'departmentWise', 'recentAdmissions'));
    }
}
