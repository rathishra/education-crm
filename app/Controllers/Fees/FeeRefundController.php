<?php
namespace App\Controllers\Fees;

use App\Controllers\BaseController;

class FeeRefundController extends BaseController
{
    public function index(): void
    {
        $statusF = $_GET['status'] ?? '';
        $where  = "fr.institution_id = ?";
        $params = [$this->institutionId];
        if ($statusF) { $where .= " AND fr.status = ?"; $params[] = $statusF; }

        $this->db->query(
            "SELECT fr.*,
                    CONCAT(s.first_name,' ',s.last_name) AS student_name,
                    s.student_id_number AS enrollment_number,
                    CONCAT(u.first_name,' ',u.last_name) AS approved_by_name,
                    rec.receipt_number
             FROM fee_refunds fr
             JOIN students s ON s.id = fr.student_id
             LEFT JOIN users u ON u.id = fr.approved_by
             LEFT JOIN fee_receipts rec ON rec.id = fr.receipt_id
             WHERE $where
             ORDER BY fr.created_at DESC LIMIT 300",
            $params
        );
        $refunds = $this->db->fetchAll();

        $stats = [
            'total'     => count($refunds),
            'pending'   => count(array_filter($refunds, fn($r) => $r['status'] === 'pending')),
            'approved'  => count(array_filter($refunds, fn($r) => $r['status'] === 'approved')),
            'processed' => count(array_filter($refunds, fn($r) => $r['status'] === 'processed')),
            'processed_amount' => array_sum(array_column(array_filter($refunds, fn($r) => $r['status'] === 'processed'), 'refund_amount')),
        ];

        $this->view('fees/refunds/index', compact('refunds', 'stats'));
    }

    public function store(): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $studentId = (int)$this->input('student_id', 0);
        $receiptId = $this->input('receipt_id') ? (int)$this->input('receipt_id') : null;
        $amount    = (float)$this->input('refund_amount', 0);
        $reason    = trim($this->input('reason', ''));

        if (!$studentId || $amount <= 0 || !$reason) {
            $this->json(['status' => 'error', 'message' => 'Student, amount and reason are required.'], 422);
        }

        // If receipt provided, validate amount doesn't exceed receipt
        if ($receiptId) {
            $this->db->query(
                "SELECT net_amount FROM fee_receipts WHERE id = ? AND student_id = ? AND status='active'",
                [$receiptId, $studentId]
            );
            $rec = $this->db->fetch();
            if (!$rec) { $this->json(['status' => 'error', 'message' => 'Receipt not found.'], 404); }
            if ($amount > (float)$rec['net_amount']) {
                $this->json(['status' => 'error', 'message' => 'Refund amount exceeds receipt amount.'], 422);
            }
        }

        $this->db->insert('fee_refunds', [
            'institution_id'   => $this->institutionId,
            'student_id'       => $studentId,
            'receipt_id'       => $receiptId,
            'refund_amount'    => $amount,
            'refund_mode'      => $this->input('refund_mode', 'cash'),
            'reference_number' => trim($this->input('reference_number', '')) ?: null,
            'reason'           => $reason,
            'remarks'          => trim($this->input('remarks', '')),
            'status'           => 'pending',
            'created_by'       => $this->user['id'] ?? null,
        ]);

        $this->json(['status' => 'success', 'message' => 'Refund request submitted.']);
    }

    public function approve(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->db->query(
            "SELECT * FROM fee_refunds WHERE id = ? AND institution_id = ? AND status='pending'",
            [$id, $this->institutionId]
        );
        if (!$this->db->fetch()) { $this->json(['status' => 'error', 'message' => 'Not found or already processed.'], 404); }

        $this->db->query(
            "UPDATE fee_refunds SET status='approved', approved_by=?, approved_at=NOW() WHERE id=?",
            [$this->user['id'] ?? null, $id]
        );
        $this->json(['status' => 'success', 'message' => 'Refund approved.']);
    }

    public function process(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $this->db->query(
            "SELECT * FROM fee_refunds WHERE id = ? AND institution_id = ? AND status='approved'",
            [$id, $this->institutionId]
        );
        $refund = $this->db->fetch();
        if (!$refund) { $this->json(['status' => 'error', 'message' => 'Not found or not approved.'], 404); }

        $mode  = $this->input('refund_mode', 'cash');
        $refNo = trim($this->input('reference_number', ''));

        $this->db->query(
            "UPDATE fee_refunds SET status='processed', refund_mode=?, reference_number=?, processed_at=NOW() WHERE id=?",
            [$mode, $refNo ?: null, $id]
        );

        // Reverse the assignment balance if linked
        if ($refund['receipt_id']) {
            $this->db->query("SELECT assignment_id, amount FROM fee_receipt_items WHERE receipt_id = ?", [$refund['receipt_id']]);
            foreach ($this->db->fetchAll() as $item) {
                $ratio = (float)$item['amount'] / max(1, (float)$refund['refund_amount']);
                $reverseAmt = round($refund['refund_amount'] * $ratio, 2);
                $this->db->query(
                    "UPDATE fee_student_assignments
                     SET paid_amount = GREATEST(0, paid_amount - ?),
                         balance_amount = balance_amount + ?,
                         status = CASE WHEN (paid_amount - ?) <= 0 THEN 'pending' ELSE 'partial' END,
                         updated_at = NOW()
                     WHERE id = ?",
                    [$reverseAmt, $reverseAmt, $reverseAmt, $item['assignment_id']]
                );
            }
        }

        $this->logAudit('fee_refund_processed', 'fee_refunds', $id);
        $this->json(['status' => 'success', 'message' => 'Refund processed.']);
    }

    public function reject(int $id): void
    {
        if (!verifyCsrf()) { $this->backWithErrors(['Session expired.']); return; }
        $reason = trim($this->input('reason', ''));
        if (!$reason) { $this->json(['status' => 'error', 'message' => 'Reason required.'], 422); }

        $this->db->query(
            "UPDATE fee_refunds SET status='rejected', rejected_reason=?, rejected_by=?, rejected_at=NOW() WHERE id=? AND institution_id=?",
            [$reason, $this->user['id'] ?? null, $id, $this->institutionId]
        );
        $this->json(['status' => 'success', 'message' => 'Refund rejected.']);
    }
}
