<?php
namespace App\Controllers\Portal;

class TimetableController extends PortalBaseController
{
    public function index(): void
    {
        $sectionId = $this->getSectionId();
        $inst      = $this->institutionId;
        $db        = $this->db;

        $timetable = [];
        $periods   = [];

        if ($sectionId) {
            // Distinct periods for this institution
            $db->query(
                "SELECT DISTINCT atp.id, atp.period_name, atp.start_time, atp.end_time,
                        atp.period_number, atp.is_break
                 FROM academic_timetable_periods atp
                 WHERE atp.institution_id = ?
                 ORDER BY atp.period_number",
                [$inst]
            );
            $periods = $db->fetchAll();

            // Full timetable for section
            $db->query(
                "SELECT tt.*,
                        sub.subject_name, sub.subject_code,
                        CONCAT(u.first_name,' ',COALESCE(u.last_name,'')) AS faculty_name,
                        atp.period_name, atp.start_time, atp.end_time,
                        atp.period_number, atp.is_break
                 FROM academic_timetable tt
                 JOIN subjects sub                    ON sub.id = tt.subject_id
                 JOIN users    u                      ON u.id   = tt.faculty_id
                 JOIN academic_timetable_periods atp  ON atp.id = tt.period_id
                 WHERE tt.section_id = ? AND tt.institution_id = ? AND tt.is_active = 1
                 ORDER BY
                   FIELD(tt.day_of_week,'monday','tuesday','wednesday','thursday','friday','saturday','sunday'),
                   atp.period_number",
                [$sectionId, $inst]
            );
            $rows = $db->fetchAll();

            // Group by day
            foreach ($rows as $row) {
                $day = strtolower($row['day_of_week'] ?? 'monday');
                $pid = $row['period_id'];
                $timetable[$day][$pid] = $row;
            }
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        $pageTitle = 'Timetable';
        $this->view('portal/timetable/index', compact('timetable', 'periods', 'days', 'sectionId', 'pageTitle'));
    }
}
