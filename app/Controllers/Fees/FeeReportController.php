<?php
namespace App\Controllers\Fees;

use App\Controllers\BaseController;

class FeeReportController extends BaseController
{
    // ── REPORT HUB ───────────────────────────────────────────
    public function index(): void
    {
        // Quick stats
        $this->db->query(
            "SELECT
               COALESCE(SUM(net_amount),0) AS total_collected,
               COUNT(*) AS receipt_count
             FROM fee_receipts
             WHERE institution_id = ? AND MONTH(receipt_date)=MONTH(CURDATE()) AND YEAR(receipt_date)=YEAR(CURDATE()) AND status='active'",
            [$this->institutionId]
        );
        $monthStats = $this->db->fetch();

        $this->db->query(
            "SELECT
               COALESCE(SUM(balance_amount),0) AS total_pending,
               COUNT(*) AS pending_count
             FROM fee_student_assignments
             WHERE institution_id = ? AND status NOT IN ('paid','waived')",
            [$this->institutionId]
        );
        $pendingStats = $this->db->fetch();

        $this->db->query("SELECT id, year_name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        // Last 7 days for chart
        $this->db->query(
            "SELECT DATE(receipt_date) AS day, COALESCE(SUM(net_amount),0) AS total
             FROM fee_receipts
             WHERE institution_id = ? AND receipt_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status='active'
             GROUP BY DATE(receipt_date) ORDER BY day",
            [$this->institutionId]
        );
        $dailyCollection = $this->db->fetchAll();

        $statsForView = [
            'collection'       => $monthStats['total_collected'] ?? 0,
            'pending'          => $pendingStats['total_pending'] ?? 0,
            'overdue_students' => 0,
            'concessions'      => 0,
        ];

        $this->view('fees/reports/index', [
            'monthStats'      => $statsForView,
            'academicYears'   => $academicYears,
            'dailyCollection' => $dailyCollection,
        ]);
    }

    // ── FEE DASHBOARD (landing) ───────────────────────────────
    public function dashboard(): void
    {
        // Quick stats for dashboard
        $this->db->query(
            "SELECT
               COALESCE(SUM(CASE WHEN status='active' THEN net_amount ELSE 0 END),0) AS month_collection,
               COUNT(CASE WHEN status='active' THEN 1 END) AS month_receipts
             FROM fee_receipts
             WHERE institution_id = ? AND MONTH(receipt_date)=MONTH(CURDATE()) AND YEAR(receipt_date)=YEAR(CURDATE())",
            [$this->institutionId]
        );
        $monthCollection = $this->db->fetch();

        $this->db->query(
            "SELECT
               COUNT(*) AS total_pending,
               COALESCE(SUM(balance_amount),0) AS pending_amount,
               COUNT(CASE WHEN status='overdue' OR (due_date < CURDATE() AND status NOT IN ('paid','waived')) THEN 1 END) AS overdue_count
             FROM fee_student_assignments
             WHERE institution_id = ? AND status NOT IN ('paid','waived')",
            [$this->institutionId]
        );
        $pendingInfo = $this->db->fetch();

        $this->db->query(
            "SELECT id, year_name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC LIMIT 5",
            [$this->institutionId]
        );
        $academicYears = $this->db->fetchAll();

        $this->db->query(
            "SELECT DATE(receipt_date) AS day, COALESCE(SUM(net_amount),0) AS total
             FROM fee_receipts
             WHERE institution_id = ? AND receipt_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND status='active'
             GROUP BY DATE(receipt_date) ORDER BY day",
            [$this->institutionId]
        );
        $dailyCollection = $this->db->fetchAll();

        $this->view('fees/reports/index', [
            'monthStats' => [
                'collection'       => $monthCollection['month_collection'] ?? 0,
                'pending'          => $pendingInfo['pending_amount'] ?? 0,
                'overdue_students' => $pendingInfo['overdue_count'] ?? 0,
                'concessions'      => 0,
            ],
            'academicYears'   => $academicYears,
            'dailyCollection' => $dailyCollection,
        ]);
    }

    // ── COLLECTION REPORT ────────────────────────────────────
    public function collection(): void
    {
        $from    = $_GET['date_from'] ?? $_GET['from'] ?? date('Y-m-01');
        $to      = $_GET['date_to']   ?? $_GET['to']   ?? date('Y-m-d');
        $mode    = $_GET['payment_mode'] ?? $_GET['mode'] ?? '';
        $groupBy = $_GET['group_by'] ?? 'day';

        $where  = "fr.institution_id = ? AND fr.receipt_date BETWEEN ? AND ? AND fr.status='active'";
        $params = [$this->institutionId, $from, $to];
        if ($mode) { $where .= " AND fr.payment_mode = ?"; $params[] = $mode; }

        // Summary by group
        $groupExpr = match($groupBy) {
            'course'    => "c.course_name",
            'mode'      => "fr.payment_mode",
            'head'      => "fh.head_name",
            default     => "DATE(fr.receipt_date)",
        };

        if ($groupBy === 'head') {
            $this->db->query(
                "SELECT fh.head_name AS grp_label,
                        COUNT(DISTINCT fr.id) AS receipt_count,
                        COALESCE(SUM(fri.amount),0) AS total_amount,
                        COALESCE(SUM(fri.fine_amount),0) AS fine_amount
                 FROM fee_receipts fr
                 JOIN fee_receipt_items fri ON fri.receipt_id = fr.id
                 JOIN fee_heads fh ON fh.id = fri.fee_head_id
                 WHERE $where
                 GROUP BY fh.id ORDER BY total_amount DESC",
                $params
            );
        } elseif ($groupBy === 'course') {
            $this->db->query(
                "SELECT COALESCE(c.course_name,'Unknown') AS grp_label,
                        COUNT(DISTINCT fr.id) AS receipt_count,
                        COALESCE(SUM(fr.net_amount),0) AS total_amount,
                        COALESCE(SUM(fr.fine_amount),0) AS fine_amount
                 FROM fee_receipts fr
                 JOIN students s ON s.id = fr.student_id
                 LEFT JOIN courses c ON c.id = s.course_id
                 WHERE $where
                 GROUP BY c.id ORDER BY total_amount DESC",
                $params
            );
        } elseif ($groupBy === 'mode') {
            $this->db->query(
                "SELECT fr.payment_mode AS grp_label,
                        COUNT(*) AS receipt_count,
                        COALESCE(SUM(fr.net_amount),0) AS total_amount,
                        COALESCE(SUM(fr.fine_amount),0) AS fine_amount
                 FROM fee_receipts fr
                 WHERE $where
                 GROUP BY fr.payment_mode ORDER BY total_amount DESC",
                $params
            );
        } else {
            $this->db->query(
                "SELECT DATE(fr.receipt_date) AS grp_label,
                        COUNT(*) AS receipt_count,
                        COALESCE(SUM(fr.net_amount),0) AS total_amount,
                        COALESCE(SUM(fr.fine_amount),0) AS fine_amount
                 FROM fee_receipts fr
                 WHERE $where
                 GROUP BY DATE(fr.receipt_date) ORDER BY grp_label",
                $params
            );
        }
        $grouped = $this->db->fetchAll();

        // Detail rows
        $this->db->query(
            "SELECT fr.*,
                    CONCAT(s.first_name,' ',s.last_name) AS student_name,
                    s.enrollment_number,
                    c.course_name,
                    CONCAT(u.first_name,' ',u.last_name) AS collected_by_name
             FROM fee_receipts fr
             JOIN students s ON s.id = fr.student_id
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN users u ON u.id = fr.collected_by
             WHERE $where
             ORDER BY fr.receipt_date DESC, fr.id DESC LIMIT 1000",
            $params
        );
        $receipts = $this->db->fetchAll();

        $totals = [
            'amount' => array_sum(array_column($receipts, 'net_amount')),
            'fine'   => array_sum(array_column($receipts, 'fine_amount')),
            'count'  => count($receipts),
        ];

        $this->db->query("SELECT id, year_name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $summary = [
            'total_collected' => array_sum(array_column($grouped, 'total_amount')),
            'receipt_count'   => array_sum(array_column($grouped, 'receipt_count')),
            'total_fine'      => array_sum(array_column($grouped, 'fine_amount')),
            'student_count'   => count(array_unique(array_column($receipts, 'student_id'))),
        ];

        // Normalize rows for the view (unify column names)
        $rows = array_map(function($r) use ($groupBy) {
            if ($groupBy === 'day') {
                return array_merge($r, ['day' => $r['grp_label'], 'total' => $r['total_amount']]);
            } elseif ($groupBy === 'mode') {
                return array_merge($r, ['payment_mode' => $r['grp_label'], 'amount' => $r['total_amount']]);
            } elseif ($groupBy === 'course') {
                return array_merge($r, ['course_name' => $r['grp_label'], 'collected' => $r['total_amount'], 'gross' => $r['total_amount'], 'pending' => 0, 'students' => $r['receipt_count']]);
            } else {
                return array_merge($r, ['head_name' => $r['grp_label'], 'amount' => $r['total_amount']]);
            }
        }, $grouped);

        $filters = [
            'date_from'        => $from,
            'date_to'          => $to,
            'group_by'         => $groupBy,
            'payment_mode'     => $mode,
            'academic_year_id' => $_GET['academic_year_id'] ?? '',
        ];

        $this->view('fees/reports/collection', compact('rows', 'summary', 'filters', 'academicYears'));
    }

    // ── PENDING FEES REPORT ──────────────────────────────────
    public function pending(): void
    {
        $ayId     = (int)($_GET['academic_year_id'] ?? 0);
        $courseId = (int)($_GET['course_id'] ?? 0);
        $headId   = (int)($_GET['fee_head_id'] ?? 0);
        $overdue  = isset($_GET['overdue_only']) || isset($_GET['overdue']) ? 1 : 0;

        $where  = "fsa.institution_id = ? AND fsa.status NOT IN ('paid','waived')";
        $params = [$this->institutionId];
        if ($ayId)     { $where .= " AND fsa.academic_year_id = ?"; $params[] = $ayId; }
        if ($courseId) { $where .= " AND c.id = ?";                  $params[] = $courseId; }
        if ($headId)   { $where .= " AND fsa.fee_head_id = ?";       $params[] = $headId; }
        if ($overdue)  { $where .= " AND fsa.due_date < CURDATE()"; }

        $this->db->query(
            "SELECT fsa.*,
                    CONCAT(s.first_name,' ',s.last_name) AS student_name,
                    s.enrollment_number, s.phone,
                    fh.head_name, fh.head_code, fh.category,
                    c.course_name, b.batch_name,
                    ay.year_name
             FROM fee_student_assignments fsa
             JOIN students s  ON s.id  = fsa.student_id
             JOIN fee_heads fh ON fh.id = fsa.fee_head_id
             LEFT JOIN fee_structures fs ON fs.id = fsa.structure_id
             LEFT JOIN courses c ON c.id = fs.course_id
             LEFT JOIN batches b ON b.id = fs.batch_id
             LEFT JOIN academic_years ay ON ay.id = fsa.academic_year_id
             WHERE $where
             ORDER BY fsa.due_date ASC, s.first_name LIMIT 1000",
            $params
        );
        $pending = $this->db->fetchAll();

        $totals = [
            'net'     => array_sum(array_column($pending, 'net_amount')),
            'paid'    => array_sum(array_column($pending, 'paid_amount')),
            'balance' => array_sum(array_column($pending, 'balance_amount')),
            'fine'    => array_sum(array_column($pending, 'fine_amount')),
            'count'   => count($pending),
        ];

        $this->db->query("SELECT id, year_name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->db->query("SELECT id, course_name FROM courses WHERE institution_id = ? ORDER BY course_name", [$this->institutionId]);
        $courses = $this->db->fetchAll();

        $this->db->query("SELECT id, head_name FROM fee_heads WHERE institution_id = ? AND is_active=1 ORDER BY head_name", [$this->institutionId]);
        $feeHeads = $this->db->fetchAll();

        $filters = [
            'academic_year_id' => $ayId,
            'course_id'        => $courseId,
            'fee_head_id'      => $headId,
            'overdue_only'     => $overdue,
            'status'           => $_GET['status'] ?? '',
        ];

        $summary = [
            'total_pending'  => array_sum(array_column($pending, 'balance_amount')),
            'overdue_amount' => array_sum(array_filter(array_map(fn($r) => !empty($r['due_date']) && strtotime($r['due_date']) < time() ? $r['balance_amount'] : 0, $pending))),
            'student_count'  => count(array_unique(array_column($pending, 'student_id'))),
            'avg_pending'    => count($pending) > 0 ? array_sum(array_column($pending, 'balance_amount')) / count($pending) : 0,
        ];

        $rows = $pending;

        $this->view('fees/reports/pending', compact('rows', 'summary', 'filters', 'academicYears', 'courses', 'feeHeads'));
    }

    // ── STUDENT LEDGER ───────────────────────────────────────
    public function ledger(int $studentId): void
    {
        $this->db->query(
            "SELECT s.*, CONCAT(s.first_name,' ',s.last_name) AS full_name,
                    c.course_name, b.batch_name
             FROM students s
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN batches b ON b.id = s.batch_id
             WHERE s.id = ? AND s.institution_id = ?",
            [$studentId, $this->institutionId]
        );
        $student = $this->db->fetch();
        if (!$student) { $this->redirectWith(url('fees/reports'), 'error', 'Student not found.'); return; }

        // All assignments
        $this->db->query(
            "SELECT fsa.*, fh.head_name, fh.head_code, fh.category, ay.year_name
             FROM fee_student_assignments fsa
             JOIN fee_heads fh ON fh.id = fsa.fee_head_id
             LEFT JOIN academic_years ay ON ay.id = fsa.academic_year_id
             WHERE fsa.student_id = ? AND fsa.institution_id = ?
             ORDER BY fsa.academic_year_id DESC, fh.category",
            [$studentId, $this->institutionId]
        );
        $assignments = $this->db->fetchAll();

        // All receipts
        $this->db->query(
            "SELECT fr.*, ay.year_name
             FROM fee_receipts fr
             LEFT JOIN academic_years ay ON ay.id = fr.academic_year_id
             WHERE fr.student_id = ? AND fr.institution_id = ? AND fr.status='active'
             ORDER BY fr.receipt_date DESC",
            [$studentId, $this->institutionId]
        );
        $receipts = $this->db->fetchAll();

        // Normalize student name for view
        $student['name'] = $student['full_name'] ?? trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

        // Ledger summary totals
        $ledgerSummary = [
            'total_assigned'  => array_sum(array_column($assignments, 'net_amount')),
            'total_paid'      => array_sum(array_column($assignments, 'paid_amount')),
            'total_balance'   => array_sum(array_column($assignments, 'balance_amount')),
            'total_fine'      => array_sum(array_column($assignments, 'fine_amount')),
            'total_concession'=> array_sum(array_column($assignments, 'concession_amount')),
        ];

        // Normalize receipt total_paid
        $receipts = array_map(function($r) {
            $r['total_paid'] = $r['net_amount'] ?? 0;
            return $r;
        }, $receipts);

        // Concessions for student
        $this->db->query(
            "SELECT fc.*, fh.head_name, CONCAT(u.first_name,' ',u.last_name) AS approver_name
             FROM fee_concessions fc
             LEFT JOIN fee_heads fh ON fh.id = fc.fee_head_id
             LEFT JOIN users u ON u.id = fc.approved_by
             WHERE fc.student_id = ? AND fc.institution_id = ?
             ORDER BY fc.created_at DESC",
            [$studentId, $this->institutionId]
        );
        $concessions = $this->db->fetchAll();

        // Refunds for student
        $this->db->query(
            "SELECT fr.*, frec.receipt_number
             FROM fee_refunds fr
             LEFT JOIN fee_receipts frec ON frec.id = fr.receipt_id
             WHERE fr.student_id = ? AND fr.institution_id = ?
             ORDER BY fr.created_at DESC",
            [$studentId, $this->institutionId]
        );
        $refunds = $this->db->fetchAll();

        $this->view('fees/reports/ledger', compact('student', 'assignments', 'receipts', 'ledgerSummary', 'concessions', 'refunds'));
    }

    // ── CSV EXPORT ───────────────────────────────────────────
    public function exportCollection(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        $this->db->query(
            "SELECT fr.receipt_number, fr.receipt_date, fr.payment_mode, fr.net_amount, fr.fine_amount,
                    CONCAT(s.first_name,' ',s.last_name) AS student_name, s.enrollment_number,
                    c.course_name,
                    CONCAT(u.first_name,' ',u.last_name) AS collected_by
             FROM fee_receipts fr
             JOIN students s ON s.id = fr.student_id
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN users u ON u.id = fr.collected_by
             WHERE fr.institution_id = ? AND fr.receipt_date BETWEEN ? AND ? AND fr.status='active'
             ORDER BY fr.receipt_date, fr.id",
            [$this->institutionId, $from, $to]
        );
        $rows = $this->db->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="fee_collection_' . $from . '_to_' . $to . '.csv"');
        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['Receipt No', 'Date', 'Student', 'Enrollment', 'Course', 'Mode', 'Amount', 'Fine', 'Collected By']);
        foreach ($rows as $r) {
            fputcsv($fp, [$r['receipt_number'], $r['receipt_date'], $r['student_name'], $r['enrollment_number'], $r['course_name'], $r['payment_mode'], $r['net_amount'], $r['fine_amount'], $r['collected_by']]);
        }
        fclose($fp);
        exit;
    }

    // ── CSV EXPORT PENDING ───────────────────────────────────
    public function exportPending(): void
    {
        $this->db->query(
            "SELECT CONCAT(s.first_name,' ',s.last_name) AS student_name, s.enrollment_number,
                    s.phone, c.course_name, fh.head_name,
                    fsa.gross_amount, fsa.concession_amount, fsa.net_amount, fsa.paid_amount,
                    fsa.balance_amount, fsa.due_date, fsa.status
             FROM fee_student_assignments fsa
             JOIN students s ON s.id = fsa.student_id
             JOIN fee_heads fh ON fh.id = fsa.fee_head_id
             LEFT JOIN fee_structures fs ON fs.id = fsa.structure_id
             LEFT JOIN courses c ON c.id = fs.course_id
             WHERE fsa.institution_id = ? AND fsa.status NOT IN ('paid','waived')
             ORDER BY s.first_name, fh.head_name",
            [$this->institutionId]
        );
        $rows = $this->db->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="pending_fees_' . date('Y-m-d') . '.csv"');
        $fp = fopen('php://output', 'w');
        fputcsv($fp, ['Student', 'Enrollment', 'Phone', 'Course', 'Fee Head', 'Gross', 'Discount', 'Net', 'Paid', 'Balance', 'Due Date', 'Status']);
        foreach ($rows as $r) {
            fputcsv($fp, array_values($r));
        }
        fclose($fp);
        exit;
    }
}
