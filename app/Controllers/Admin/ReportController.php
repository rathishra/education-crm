<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ReportController extends BaseController
{
    public function index(): void
    {
        $this->authorize('reports.view');
        $this->view('reports/index');
    }

    public function leads(): void
    {
        $this->authorize('reports.view');

        $filters = [
            'date_from'  => $this->input('date_from') ?: date('Y-m-01'),
            'date_to'    => $this->input('date_to') ?: date('Y-m-d'),
            'source_id'  => $this->input('source_id'),
            'status_id'  => $this->input('status_id'),
            'assigned_to'=> $this->input('assigned_to'),
        ];

        $institutionId = session('institution_id');
        $where = "l.deleted_at IS NULL";
        $params = [];
        if ($institutionId) { $where .= " AND l.institution_id = ?"; $params[] = $institutionId; }
        if ($filters['date_from']) { $where .= " AND DATE(l.created_at) >= ?"; $params[] = $filters['date_from']; }
        if ($filters['date_to'])   { $where .= " AND DATE(l.created_at) <= ?"; $params[] = $filters['date_to']; }
        if ($filters['source_id']) { $where .= " AND l.lead_source_id = ?"; $params[] = $filters['source_id']; }
        if ($filters['status_id']) { $where .= " AND l.lead_status_id = ?"; $params[] = $filters['status_id']; }
        if ($filters['assigned_to']) { $where .= " AND l.assigned_to = ?"; $params[] = $filters['assigned_to']; }

        // Leads by day
        db()->query(
            "SELECT DATE(l.created_at) as date, COUNT(*) as count
             FROM leads l WHERE {$where} GROUP BY DATE(l.created_at) ORDER BY date",
            $params
        );
        $byDay = db()->fetchAll();

        // By source
        db()->query(
            "SELECT ls.name as source, COUNT(l.id) as count
             FROM leads l LEFT JOIN lead_sources ls ON ls.id = l.lead_source_id
             WHERE {$where} GROUP BY l.lead_source_id ORDER BY count DESC",
            $params
        );
        $bySource = db()->fetchAll();

        // By status
        db()->query(
            "SELECT lst.name as status, COUNT(l.id) as count
             FROM leads l LEFT JOIN lead_statuses lst ON lst.id = l.lead_status_id
             WHERE {$where} GROUP BY l.lead_status_id ORDER BY count DESC",
            $params
        );
        $byStatus = db()->fetchAll();

        // By counselor
        db()->query(
            "SELECT CONCAT(u.first_name, ' ', u.last_name) as counselor, COUNT(l.id) as count,
                    SUM(CASE WHEN l.converted_at IS NOT NULL THEN 1 ELSE 0 END) as converted
             FROM leads l LEFT JOIN users u ON u.id = l.assigned_to
             WHERE {$where} GROUP BY l.assigned_to ORDER BY count DESC",
            $params
        );
        $byCounselor = db()->fetchAll();

        // Totals
        db()->query("SELECT COUNT(*) as total, SUM(CASE WHEN converted_at IS NOT NULL THEN 1 ELSE 0 END) as converted FROM leads l WHERE {$where}", $params);
        $totals = db()->fetch();

        db()->query("SELECT id, name FROM lead_sources ORDER BY name");
        $sources = db()->fetchAll();
        db()->query("SELECT id, name FROM lead_statuses ORDER BY sort_order");
        $statuses = db()->fetchAll();

        $this->view('reports/leads', compact('byDay', 'bySource', 'byStatus', 'byCounselor', 'totals', 'filters', 'sources', 'statuses'));
    }

    public function admissions(): void
    {
        $this->authorize('reports.view');

        $filters = [
            'date_from' => $this->input('date_from') ?: date('Y-m-01'),
            'date_to'   => $this->input('date_to') ?: date('Y-m-d'),
            'course_id' => $this->input('course_id'),
            'status'    => $this->input('status'),
        ];

        $institutionId = session('institution_id');
        $where = "1=1";
        $params = [];
        if ($institutionId) { $where .= " AND a.institution_id = ?"; $params[] = $institutionId; }
        if ($filters['date_from']) { $where .= " AND DATE(a.created_at) >= ?"; $params[] = $filters['date_from']; }
        if ($filters['date_to'])   { $where .= " AND DATE(a.created_at) <= ?"; $params[] = $filters['date_to']; }
        if ($filters['course_id']) { $where .= " AND a.course_id = ?"; $params[] = $filters['course_id']; }
        if ($filters['status'])    { $where .= " AND a.status = ?"; $params[] = $filters['status']; }

        db()->query(
            "SELECT a.status, COUNT(*) as count FROM admissions a WHERE {$where} GROUP BY a.status",
            $params
        );
        $byStatus = db()->fetchAll();

        db()->query(
            "SELECT c.name as course, COUNT(a.id) as count
             FROM admissions a LEFT JOIN courses c ON c.id = a.course_id
             WHERE {$where} GROUP BY a.course_id ORDER BY count DESC",
            $params
        );
        $byCourse = db()->fetchAll();

        db()->query(
            "SELECT DATE(a.created_at) as date, COUNT(*) as count
             FROM admissions a WHERE {$where} GROUP BY DATE(a.created_at) ORDER BY date",
            $params
        );
        $byDay = db()->fetchAll();

        db()->query("SELECT id, name FROM courses WHERE deleted_at IS NULL ORDER BY name");
        $courses = db()->fetchAll();

        $this->view('reports/admissions', compact('byStatus', 'byCourse', 'byDay', 'filters', 'courses'));
    }

    public function revenue(): void
    {
        $this->authorize('reports.view');

        $filters = [
            'date_from' => $this->input('date_from') ?: date('Y-m-01'),
            'date_to'   => $this->input('date_to') ?: date('Y-m-d'),
            'mode'      => $this->input('mode'),
        ];

        $institutionId = session('institution_id');
        $where = "p.status = 'success'";
        $params = [];
        if ($institutionId) { $where .= " AND p.institution_id = ?"; $params[] = $institutionId; }
        if ($filters['date_from']) { $where .= " AND DATE(p.payment_date) >= ?"; $params[] = $filters['date_from']; }
        if ($filters['date_to'])   { $where .= " AND DATE(p.payment_date) <= ?"; $params[] = $filters['date_to']; }
        if ($filters['mode'])      { $where .= " AND p.payment_mode = ?"; $params[] = $filters['mode']; }

        db()->query("SELECT COALESCE(SUM(p.amount), 0) as total FROM payments p WHERE {$where}", $params);
        $totalRevenue = db()->fetch()['total'] ?? 0;

        db()->query(
            "SELECT DATE(p.payment_date) as date, SUM(p.amount) as amount FROM payments p WHERE {$where} GROUP BY DATE(p.payment_date) ORDER BY date",
            $params
        );
        $byDay = db()->fetchAll();

        db()->query(
            "SELECT p.payment_mode, SUM(p.amount) as amount, COUNT(*) as count FROM payments p WHERE {$where} GROUP BY p.payment_mode",
            $params
        );
        $byMode = db()->fetchAll();

        db()->query(
            "SELECT c.name as course, SUM(p.amount) as amount
             FROM payments p LEFT JOIN students s ON s.id = p.student_id LEFT JOIN courses c ON c.id = s.course_id
             WHERE {$where} GROUP BY s.course_id ORDER BY amount DESC",
            $params
        );
        $byCourse = db()->fetchAll();

        $this->view('reports/revenue', compact('totalRevenue', 'byDay', 'byMode', 'byCourse', 'filters'));
    }

    public function counselorPerformance(): void
    {
        $this->authorize('reports.view');

        $filters = [
            'date_from' => $this->input('date_from') ?: date('Y-m-01'),
            'date_to'   => $this->input('date_to') ?: date('Y-m-d'),
        ];

        $institutionId = session('institution_id');
        $instWhere = $institutionId ? "AND l.institution_id = ?" : '';
        $instParams = $institutionId ? [$institutionId] : [];

        db()->query(
            "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as counselor,
                    COUNT(l.id) as total_leads,
                    SUM(CASE WHEN l.converted_at IS NOT NULL THEN 1 ELSE 0 END) as converted,
                    SUM(CASE WHEN ls.name = 'Lost' THEN 1 ELSE 0 END) as lost,
                    COUNT(f.id) as followups_done
             FROM users u
             LEFT JOIN leads l ON l.assigned_to = u.id AND DATE(l.created_at) BETWEEN ? AND ? {$instWhere}
             LEFT JOIN lead_statuses ls ON ls.id = l.lead_status_id
             LEFT JOIN followups f ON f.assigned_to = u.id AND DATE(f.created_at) BETWEEN ? AND ? AND f.status = 'completed'
             GROUP BY u.id
             HAVING total_leads > 0
             ORDER BY total_leads DESC",
            array_merge([$filters['date_from'], $filters['date_to']], $instParams, [$filters['date_from'], $filters['date_to']])
        );
        $data = db()->fetchAll();

        $this->view('reports/counselor', compact('data', 'filters'));
    }

    public function institutionWise(): void
    {
        $this->authorize('reports.view');

        db()->query(
            "SELECT i.id, i.name, i.code, i.type,
                    COUNT(DISTINCT l.id) as total_leads,
                    COUNT(DISTINCT a.id) as total_admissions,
                    COUNT(DISTINCT s.id) as total_students,
                    COALESCE(SUM(p.amount), 0) as total_revenue
             FROM institutions i
             LEFT JOIN leads l ON l.institution_id = i.id AND l.deleted_at IS NULL
             LEFT JOIN admissions a ON a.institution_id = i.id
             LEFT JOIN students s ON s.institution_id = i.id AND s.deleted_at IS NULL AND s.status = 'active'
             LEFT JOIN payments p ON p.institution_id = i.id AND p.status = 'success'
             WHERE i.deleted_at IS NULL
             GROUP BY i.id
             ORDER BY total_students DESC"
        );
        $data = db()->fetchAll();

        $this->view('reports/institution_wise', compact('data'));
    }
}
