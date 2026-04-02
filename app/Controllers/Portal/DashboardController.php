<?php
namespace App\Controllers\Portal;

class DashboardController extends PortalBaseController
{
    public function index(): void
    {
        $sid  = $this->studentId;
        $inst = $this->institutionId;
        $db   = $this->db;

        // Fee summary
        $db->query(
            "SELECT COALESCE(SUM(net_amount),0)    AS total_fees,
                    COALESCE(SUM(paid_amount),0)   AS total_paid,
                    COALESCE(SUM(balance_amount),0) AS total_balance
             FROM student_fees WHERE student_id = ?",
            [$sid]
        );
        $feeSummary = $db->fetch() ?: ['total_fees' => 0, 'total_paid' => 0, 'total_balance' => 0];

        // Next due installment
        $db->query(
            "SELECT si.due_date, si.amount, si.status
             FROM student_installments si
             JOIN student_fees sf ON sf.id = si.student_fee_id
             WHERE sf.student_id = ? AND si.status IN ('upcoming','due','overdue')
             ORDER BY si.due_date ASC LIMIT 1",
            [$sid]
        );
        $nextInstallment = $db->fetch();

        // Overall attendance
        $db->query(
            "SELECT COUNT(*) AS total,
                    SUM(aar.attendance_status = 'present') AS present,
                    SUM(aar.attendance_status = 'absent')  AS absent
             FROM academic_attendance_records aar
             JOIN academic_attendance_sessions aas ON aas.id = aar.session_id
             WHERE aar.student_id = ? AND aas.institution_id = ?",
            [$sid, $inst]
        );
        $attRow   = $db->fetch();
        $attTotal = (int)($attRow['total'] ?? 0);
        $attPct   = $attTotal > 0 ? round((int)($attRow['present'] ?? 0) / $attTotal * 100, 1) : 0;

        // Upcoming exams (next 14 days)
        $batchId = $this->getStudentBatchId();
        $upcomingExams = [];
        if ($batchId) {
            $db->query(
                "SELECT aa.assessment_name, aa.assessment_type, aa.assessment_date,
                        aa.max_marks, s.subject_name
                 FROM academic_assessments aa
                 JOIN subjects s ON s.id = aa.subject_id
                 WHERE aa.batch_id = ? AND aa.institution_id = ?
                   AND aa.assessment_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                   AND aa.status = 'active'
                 ORDER BY aa.assessment_date ASC LIMIT 5",
                [$batchId, $inst]
            );
            $upcomingExams = $db->fetchAll();
        }

        // Recent notifications
        $db->query(
            "SELECT id, title, message, type, is_read, created_at
             FROM notifications
             WHERE student_id = ?
             ORDER BY created_at DESC LIMIT 5",
            [$sid]
        );
        $notifications = $db->fetchAll();

        // Student profile summary
        $profile = $this->getStudentProfile();

        $pageTitle = 'Dashboard';
        $this->view('portal/dashboard/index', compact(
            'feeSummary', 'nextInstallment', 'attPct', 'attTotal', 'attRow',
            'upcomingExams', 'notifications', 'profile', 'pageTitle'
        ));
    }
}
