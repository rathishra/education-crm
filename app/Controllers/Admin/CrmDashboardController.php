<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CrmDashboardController extends BaseController
{
    public function index(): void
    {
        $this->authorize('leads.view');

        $institutionId = $this->institutionId;

        // 1. Funnel counts
        $this->db->query("SELECT COUNT(*) AS cnt FROM enquiries WHERE institution_id=? AND deleted_at IS NULL", [$institutionId]);
        $totalEnquiries = (int)($this->db->fetch()['cnt'] ?? 0);

        $this->db->query("SELECT COUNT(*) AS cnt FROM leads WHERE institution_id=?", [$institutionId]);
        $totalLeads = (int)($this->db->fetch()['cnt'] ?? 0);

        $this->db->query("SELECT COUNT(*) AS cnt FROM admissions WHERE institution_id=?", [$institutionId]);
        $totalAdmissions = (int)($this->db->fetch()['cnt'] ?? 0);

        $this->db->query("SELECT COUNT(*) AS cnt FROM admissions WHERE institution_id=? AND status='enrolled'", [$institutionId]);
        $totalEnrolled = (int)($this->db->fetch()['cnt'] ?? 0);

        // 2. Enquiry status breakdown
        $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM enquiries WHERE institution_id=? AND deleted_at IS NULL GROUP BY status",
            [$institutionId]
        );
        $enqByStatus = [];
        foreach ($this->db->fetchAll() as $r) {
            $enqByStatus[$r['status']] = (int)$r['cnt'];
        }

        // 3. Lead priority breakdown
        $this->db->query(
            "SELECT priority, COUNT(*) AS cnt FROM leads WHERE institution_id=? GROUP BY priority",
            [$institutionId]
        );
        $leadByPriority = [];
        foreach ($this->db->fetchAll() as $r) {
            $leadByPriority[$r['priority']] = (int)$r['cnt'];
        }

        // 4. Admission status breakdown
        $this->db->query(
            "SELECT status, COUNT(*) AS cnt FROM admissions WHERE institution_id=? GROUP BY status",
            [$institutionId]
        );
        $admByStatus = [];
        foreach ($this->db->fetchAll() as $r) {
            $admByStatus[$r['status']] = (int)$r['cnt'];
        }

        // 5. Today's follow-ups (leads with next_followup_date = today or overdue)
        $this->db->query(
            "SELECT l.id, l.lead_number, l.first_name, l.last_name, l.phone, l.next_followup_date, l.followup_mode,
                    ls.name AS status_name, ls.color AS status_color,
                    CONCAT(u.first_name,' ',u.last_name) AS assigned_name
             FROM leads l
             LEFT JOIN lead_statuses ls ON ls.id = l.lead_status_id
             LEFT JOIN users u ON u.id = l.assigned_to
             WHERE l.institution_id=? AND l.next_followup_date <= CURDATE() AND (ls.is_won IS NULL OR ls.is_won=0)
             ORDER BY l.next_followup_date ASC
             LIMIT 20",
            [$institutionId]
        );
        $todayFollowups = $this->db->fetchAll();

        // 6. Hot leads
        $this->db->query(
            "SELECT l.id, l.lead_number, l.first_name, l.last_name, l.phone, l.lead_score,
                    ls.name AS status_name, ls.color AS status_color,
                    c.name AS course_name,
                    CONCAT(u.first_name,' ',u.last_name) AS assigned_name
             FROM leads l
             LEFT JOIN lead_statuses ls ON ls.id = l.lead_status_id
             LEFT JOIN courses c ON c.id = l.course_interested_id
             LEFT JOIN users u ON u.id = l.assigned_to
             WHERE l.institution_id=? AND l.priority='hot' AND (ls.is_won IS NULL OR ls.is_won=0)
             ORDER BY l.lead_score DESC, l.created_at DESC
             LIMIT 10",
            [$institutionId]
        );
        $hotLeads = $this->db->fetchAll();

        // 7. Source attribution (leads by source)
        $this->db->query(
            "SELECT ls.name AS source_name, COUNT(l.id) AS cnt
             FROM leads l
             LEFT JOIN lead_sources ls ON ls.id = l.lead_source_id
             WHERE l.institution_id=?
             GROUP BY l.lead_source_id, ls.name
             ORDER BY cnt DESC
             LIMIT 8",
            [$institutionId]
        );
        $sourceStats = $this->db->fetchAll();

        // 8. Monthly trend (last 6 months — enquiries + leads)
        $this->db->query(
            "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month, COUNT(*) AS cnt
             FROM enquiries
             WHERE institution_id=? AND deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month ASC",
            [$institutionId]
        );
        $enqTrend = [];
        foreach ($this->db->fetchAll() as $r) { $enqTrend[$r['month']] = (int)$r['cnt']; }

        $this->db->query(
            "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month, COUNT(*) AS cnt
             FROM leads
             WHERE institution_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month ASC",
            [$institutionId]
        );
        $leadTrend = [];
        foreach ($this->db->fetchAll() as $r) { $leadTrend[$r['month']] = (int)$r['cnt']; }

        // Build months array for last 6 months
        $trendMonths = [];
        for ($i = 5; $i >= 0; $i--) {
            $trendMonths[] = date('Y-m', strtotime("-$i months"));
        }

        // 9. Counselor leaderboard (top 8 by leads handled)
        $this->db->query(
            "SELECT CONCAT(u.first_name,' ',u.last_name) AS counselor,
                    COUNT(l.id) AS total_leads,
                    SUM(CASE WHEN ls.is_won=1 THEN 1 ELSE 0 END) AS converted,
                    SUM(CASE WHEN l.priority='hot' THEN 1 ELSE 0 END) AS hot_leads
             FROM users u
             JOIN leads l ON l.assigned_to = u.id AND l.institution_id = ?
             LEFT JOIN lead_statuses ls ON ls.id = l.lead_status_id
             GROUP BY u.id, u.first_name, u.last_name
             ORDER BY total_leads DESC
             LIMIT 8",
            [$institutionId]
        );
        $leaderboard = $this->db->fetchAll();

        // 10. Recent activity (last 10 admissions)
        $this->db->query(
            "SELECT a.id, a.admission_number, a.first_name, a.last_name, a.status, a.created_at,
                    c.name AS course_name
             FROM admissions a
             LEFT JOIN courses c ON c.id = a.course_id
             WHERE a.institution_id=?
             ORDER BY a.created_at DESC LIMIT 8",
            [$institutionId]
        );
        $recentAdmissions = $this->db->fetchAll();

        $this->view('crm/dashboard', compact(
            'totalEnquiries', 'totalLeads', 'totalAdmissions', 'totalEnrolled',
            'enqByStatus', 'leadByPriority', 'admByStatus',
            'todayFollowups', 'hotLeads', 'sourceStats',
            'trendMonths', 'enqTrend', 'leadTrend',
            'leaderboard', 'recentAdmissions'
        ));
    }
}
