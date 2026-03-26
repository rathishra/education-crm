<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    /**
     * Dashboard page
     */
    public function index(): void
    {
        $instId = $this->institutionId;
        $instFilter = $instId ? "AND institution_id = ?" : "";
        $params = $instId ? [$instId] : [];

        // KPI Stats
        $stats = $this->getKpiStats($instFilter, $params);

        // Recent leads
        $recentLeads = $this->getRecentLeads($instFilter, $params);

        // Upcoming follow-ups
        $upcomingFollowups = $this->getUpcomingFollowups($instFilter, $params);

        // Lead pipeline data
        $pipeline = $this->getLeadPipeline($instFilter, $params);

        // Monthly lead trend (last 6 months)
        $leadTrend = $this->getLeadTrend($instFilter, $params);

        // Lead source distribution
        $sourceDistribution = $this->getSourceDistribution($instFilter, $params);

        // Recent activities
        $recentActivities = $this->getRecentActivities($instFilter, $params);

        $this->view('dashboard/index', [
            'pageTitle'          => 'Dashboard',
            'stats'              => $stats,
            'recentLeads'        => $recentLeads,
            'upcomingFollowups'  => $upcomingFollowups,
            'pipeline'           => $pipeline,
            'leadTrend'          => $leadTrend,
            'sourceDistribution' => $sourceDistribution,
            'recentActivities'   => $recentActivities,
        ]);
    }

    /**
     * AJAX stats endpoint
     */
    public function stats(): void
    {
        $instId = $this->institutionId;
        $instFilter = $instId ? "AND institution_id = ?" : "";
        $params = $instId ? [$instId] : [];

        $stats = $this->getKpiStats($instFilter, $params);
        $pipeline = $this->getLeadPipeline($instFilter, $params);

        $this->json([
            'success'  => true,
            'stats'    => $stats,
            'pipeline' => $pipeline,
        ]);
    }

    private function getKpiStats(string $instFilter, array $params): array
    {
        // Total leads
        $this->db->query("SELECT COUNT(*) as total FROM leads WHERE deleted_at IS NULL {$instFilter}", $params);
        $totalLeads = (int)$this->db->fetch()['total'];

        // New leads today
        $this->db->query("SELECT COUNT(*) as total FROM leads WHERE deleted_at IS NULL AND DATE(created_at) = CURDATE() {$instFilter}", $params);
        $newLeadsToday = (int)$this->db->fetch()['total'];

        // New leads this month
        $this->db->query("SELECT COUNT(*) as total FROM leads WHERE deleted_at IS NULL AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) {$instFilter}", $params);
        $newLeadsMonth = (int)$this->db->fetch()['total'];

        // Converted leads
        $this->db->query(
            "SELECT COUNT(*) as total FROM leads l
             JOIN lead_statuses ls ON ls.id = l.lead_status_id
             WHERE l.deleted_at IS NULL AND ls.is_won = 1 {$instFilter}",
            $params
        );
        $convertedLeads = (int)$this->db->fetch()['total'];

        // Conversion rate
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Total students
        $this->db->query("SELECT COUNT(*) as total FROM students WHERE deleted_at IS NULL AND status = 'active' {$instFilter}", $params);
        $totalStudents = (int)$this->db->fetch()['total'];

        $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments
             WHERE status = 'success' AND MONTH(payment_date) = MONTH(CURDATE())
             AND YEAR(payment_date) = YEAR(CURDATE()) " . ($this->institutionId ? "AND institution_id = ?" : ""),
            $params
        );
        $revenueMonth = (float)$this->db->fetch()['total'];

        // Pending followups today
        $this->db->query(
            "SELECT COUNT(*) as total FROM followups
             WHERE status = 'pending' AND DATE(scheduled_at) = CURDATE() {$instFilter}",
            $params
        );
        $pendingFollowups = (int)$this->db->fetch()['total'];

        // Pending admissions
        $this->db->query(
            "SELECT COUNT(*) as total FROM admissions
             WHERE status IN ('applied', 'under_review', 'documents_pending') {$instFilter}",
            $params
        );
        $pendingAdmissions = (int)$this->db->fetch()['total'];

        // Fee dues
        $this->db->query(
            "SELECT COALESCE(SUM(balance_amount), 0) as total FROM student_fees
             WHERE status IN ('pending', 'partial', 'overdue') {$instFilter}",
            $params
        );
        $totalDues = (float)$this->db->fetch()['total'];

        // Enquiries this month
        $this->db->query(
            "SELECT COUNT(*) as total FROM enquiries
             WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) {$instFilter}",
            $params
        );
        $enquiriesMonth = (int)$this->db->fetch()['total'];

        return [
            'total_leads'        => $totalLeads,
            'new_leads_today'    => $newLeadsToday,
            'new_leads_month'    => $newLeadsMonth,
            'converted_leads'    => $convertedLeads,
            'conversion_rate'    => $conversionRate,
            'total_students'     => $totalStudents,
            'revenue_month'      => $revenueMonth,
            'pending_followups'  => $pendingFollowups,
            'pending_admissions' => $pendingAdmissions,
            'total_dues'         => $totalDues,
            'enquiries_month'    => $enquiriesMonth,
        ];
    }

    private function getRecentLeads(string $instFilter, array $params): array
    {
        $sql = "SELECT l.*, ls.name as status_name, ls.color as status_color,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_name,
                       src.name as source_name
                FROM leads l
                LEFT JOIN lead_statuses ls ON ls.id = l.lead_status_id
                LEFT JOIN users u ON u.id = l.assigned_to
                LEFT JOIN lead_sources src ON src.id = l.lead_source_id
                WHERE l.deleted_at IS NULL {$instFilter}
                ORDER BY l.created_at DESC
                LIMIT 10";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    private function getUpcomingFollowups(string $instFilter, array $params): array
    {
        $sql = "SELECT f.*, l.first_name as lead_first_name, l.last_name as lead_last_name,
                       l.phone as lead_phone,
                       CONCAT(u.first_name, ' ', u.last_name) as assigned_name
                FROM followups f
                LEFT JOIN leads l ON l.id = f.lead_id
                LEFT JOIN users u ON u.id = f.assigned_to
                WHERE f.status = 'pending'
                  AND f.scheduled_at >= NOW()
                  AND f.scheduled_at <= DATE_ADD(NOW(), INTERVAL 7 DAY)
                  AND (? IS NULL OR f.institution_id = ?)
                ORDER BY f.scheduled_at ASC
                LIMIT 10";
        $instId = $this->institutionId;
        $this->db->query($sql, [$instId, $instId]);
        return $this->db->fetchAll();
    }

    private function getLeadPipeline(string $instFilter, array $params): array
    {
        $instId = $this->institutionId;
        $onClause = $instId ? "AND l.institution_id = {$instId}" : "";
        $sql = "SELECT ls.name, ls.slug, ls.color, COUNT(l.id) as count
                FROM lead_statuses ls
                LEFT JOIN leads l ON l.lead_status_id = ls.id AND l.deleted_at IS NULL {$onClause}
                GROUP BY ls.id, ls.name, ls.slug, ls.color
                ORDER BY ls.sort_order";

        $this->db->query($sql, []);
        return $this->db->fetchAll();
    }

    private function getLeadTrend(string $instFilter, array $params): array
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                       DATE_FORMAT(created_at, '%b %Y') as label,
                       COUNT(*) as total
                FROM leads
                WHERE deleted_at IS NULL
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                  {$instFilter}
                GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
                ORDER BY month";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    private function getSourceDistribution(string $instFilter, array $params): array
    {
        $instId = $this->institutionId;
        $onClause = $instId ? "AND l.institution_id = {$instId}" : "";
        $sql = "SELECT src.name, COUNT(l.id) as count
                FROM lead_sources src
                LEFT JOIN leads l ON l.lead_source_id = src.id AND l.deleted_at IS NULL {$onClause}
                GROUP BY src.id, src.name
                HAVING count > 0
                ORDER BY count DESC
                LIMIT 8";
        $this->db->query($sql, []);
        return $this->db->fetchAll();
    }

    private function getRecentActivities(string $instFilter, array $params): array
    {
        $instWhere = '';
        if ($instFilter) {
            $instWhere = str_replace('institution_id', 'l.institution_id', $instFilter);
        }
        $sql = "SELECT la.*, l.first_name as lead_first_name, l.last_name as lead_last_name,
                       CONCAT(u.first_name, ' ', u.last_name) as user_name
                FROM lead_activities la
                JOIN leads l ON l.id = la.lead_id AND l.deleted_at IS NULL {$instWhere}
                LEFT JOIN users u ON u.id = la.user_id
                ORDER BY la.created_at DESC
                LIMIT 15";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }
}
