<?php
namespace App\Controllers\Portal;

class AttendanceController extends PortalBaseController
{
    public function index(): void
    {
        $sid  = $this->studentId;
        $inst = $this->institutionId;
        $db   = $this->db;

        $selMonth = max(1, min(12, (int)($this->input('month') ?: date('n'))));
        $selYear  = max(2020, min((int)date('Y') + 1, (int)($this->input('year') ?: date('Y'))));

        // Overall summary
        $db->query(
            "SELECT
                COUNT(*)                                              AS total,
                SUM(aar.attendance_status = 'present')               AS present,
                SUM(aar.attendance_status = 'absent')                AS absent,
                SUM(aar.attendance_status = 'late')                  AS late,
                SUM(aar.attendance_status IN ('present','late'))      AS attended,
                ROUND(SUM(aar.attendance_status IN ('present','late')) * 100.0 / NULLIF(COUNT(*),0), 2) AS percentage
             FROM academic_attendance_records aar
             JOIN academic_attendance_sessions aas ON aas.id = aar.session_id
             WHERE aar.student_id = ? AND aas.institution_id = ?",
            [$sid, $inst]
        );
        $overall = $db->fetch() ?: ['total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'attended' => 0, 'percentage' => 0];

        // Subject-wise breakdown
        $db->query(
            "SELECT sub.subject_name, sub.subject_code,
                    COUNT(*)                                              AS total,
                    SUM(aar.attendance_status IN ('present','late'))      AS attended,
                    SUM(aar.attendance_status = 'absent')                 AS absent,
                    ROUND(SUM(aar.attendance_status IN ('present','late')) * 100.0 / NULLIF(COUNT(*),0), 2) AS percentage
             FROM academic_attendance_records aar
             JOIN academic_attendance_sessions aas ON aas.id = aar.session_id
             JOIN subjects sub ON sub.id = aas.subject_id
             WHERE aar.student_id = ? AND aas.institution_id = ?
             GROUP BY sub.id, sub.subject_name, sub.subject_code
             ORDER BY sub.subject_name",
            [$sid, $inst]
        );
        $subjectWise = $db->fetchAll();

        // Monthly calendar data
        $db->query(
            "SELECT DATE(aas.attendance_date) AS att_date,
                    sub.subject_name,
                    aar.attendance_status
             FROM academic_attendance_records aar
             JOIN academic_attendance_sessions aas ON aas.id = aar.session_id
             JOIN subjects sub ON sub.id = aas.subject_id
             WHERE aar.student_id = ?
               AND YEAR(aas.attendance_date) = ?
               AND MONTH(aas.attendance_date) = ?
             ORDER BY aas.attendance_date, aas.id",
            [$sid, $selYear, $selMonth]
        );
        $calendarRaw = $db->fetchAll();

        // Group by date
        $calendarDays = [];
        foreach ($calendarRaw as $row) {
            $date = $row['att_date'];
            if (!isset($calendarDays[$date])) {
                $calendarDays[$date] = ['sessions' => [], 'has_absent' => false, 'has_present' => false];
            }
            $calendarDays[$date]['sessions'][] = $row;
            if ($row['attendance_status'] === 'absent')  $calendarDays[$date]['has_absent']  = true;
            if (in_array($row['attendance_status'], ['present','late'])) $calendarDays[$date]['has_present'] = true;
        }

        // Monthly days array for calendar grid
        $firstDay  = mktime(0, 0, 0, $selMonth, 1, $selYear);
        $daysInMonth = (int)date('t', $firstDay);
        $startDow    = (int)date('N', $firstDay); // 1=Mon, 7=Sun

        $pageTitle = 'Attendance';
        $this->view('portal/attendance/index', compact(
            'overall', 'subjectWise', 'calendarDays',
            'selMonth', 'selYear', 'daysInMonth', 'startDow', 'pageTitle'
        ));
    }
}
