<?php
namespace App\Controllers\Portal;

class FeeController extends PortalBaseController
{
    public function index(): void
    {
        $sid = $this->studentId;
        $db  = $this->db;

        // Fee structures assigned to student
        $db->query(
            "SELECT sf.*,
                    fs.name  AS structure_name,
                    ay.name  AS year_name
             FROM student_fees sf
             LEFT JOIN fee_structures  fs ON fs.id = sf.fee_structure_id
             LEFT JOIN academic_years  ay ON ay.id = sf.academic_year_id
             WHERE sf.student_id = ?
             ORDER BY sf.created_at DESC",
            [$sid]
        );
        $feeStructures = $db->fetchAll();

        // Per-head assignments
        $db->query(
            "SELECT fsa.*,
                    fh.head_name, fh.head_code, fh.category, fh.is_mandatory
             FROM fee_student_assignments fsa
             JOIN fee_heads fh ON fh.id = fsa.fee_head_id
             WHERE fsa.student_id = ?
             ORDER BY fh.category, fsa.due_date",
            [$sid]
        );
        $feeHeads = $db->fetchAll();

        // Installment schedule
        $db->query(
            "SELECT si.*, sf.net_amount AS sf_net
             FROM student_installments si
             JOIN student_fees sf ON sf.id = si.student_fee_id
             WHERE sf.student_id = ?
             ORDER BY si.due_date ASC",
            [$sid]
        );
        $installments = $db->fetchAll();

        // Fee receipts
        $db->query(
            "SELECT * FROM fee_receipts
             WHERE student_id = ? AND status = 'active'
             ORDER BY receipt_date DESC",
            [$sid]
        );
        $receipts = $db->fetchAll();

        // Summary
        $db->query(
            "SELECT COALESCE(SUM(net_amount),0)     AS total_fees,
                    COALESCE(SUM(paid_amount),0)    AS total_paid,
                    COALESCE(SUM(balance_amount),0) AS total_balance
             FROM student_fees WHERE student_id = ?",
            [$sid]
        );
        $summary = $db->fetch() ?: ['total_fees' => 0, 'total_paid' => 0, 'total_balance' => 0];

        $pageTitle = 'Fees & Payments';
        $this->view('portal/fees/index', compact('feeStructures', 'feeHeads', 'installments', 'receipts', 'summary', 'pageTitle'));
    }

    public function receipt(int $id): void
    {
        $this->_loadReceipt($id, 'portal/fees/receipt');
    }

    public function printReceipt(int $id): void
    {
        $this->_loadReceipt($id, 'portal/fees/receipt-print', 'blank');
    }

    private function _loadReceipt(int $id, string $viewName, string $layout = 'portal'): void
    {
        $this->db->query(
            "SELECT fr.*,
                    CONCAT(s.first_name,' ',COALESCE(s.last_name,'')) AS student_name,
                    s.student_id_number, s.admission_number,
                    c.name  AS course_name,
                    b.name  AS batch_name,
                    i.name  AS institution_name,
                    i.address AS institution_address,
                    i.phone   AS institution_phone,
                    i.email   AS institution_email
             FROM fee_receipts fr
             JOIN students     s ON s.id = fr.student_id
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN batches b ON b.id = s.batch_id
             JOIN institutions i ON i.id = fr.institution_id
             WHERE fr.id = ? AND fr.student_id = ? AND fr.status = 'active'
             LIMIT 1",
            [$id, $this->studentId]
        );
        $receipt = $this->db->fetch();

        if (!$receipt) {
            flash('errors', ['Receipt not found.']);
            redirect(url('portal/student/fees'));
            return;
        }

        // Receipt line items
        $this->db->query(
            "SELECT fri.*, fh.head_name, fh.category
             FROM fee_receipt_items fri
             JOIN fee_heads fh ON fh.id = fri.fee_head_id
             WHERE fri.receipt_id = ?",
            [$id]
        );
        $items = $this->db->fetchAll();

        $pageTitle = 'Receipt #' . e($receipt['receipt_number'] ?? $id);
        $this->view($viewName, compact('receipt', 'items', 'pageTitle'), $layout);
    }
}
