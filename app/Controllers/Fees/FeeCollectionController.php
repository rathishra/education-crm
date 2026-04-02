<?php
namespace App\Controllers\Fees;

use App\Controllers\BaseController;

class FeeCollectionController extends BaseController
{
    // ── CASHIER SCREEN ───────────────────────────────────────
    public function index(): void
    {
        // Recent receipts today
        $this->db->query(
            "SELECT fr.*,
                    CONCAT(s.first_name,' ',s.last_name) AS student_name,
                    s.student_id_number AS enrollment_number,
                    CONCAT(u.first_name,' ',u.last_name) AS collected_by_name
             FROM fee_receipts fr
             JOIN students s ON s.id = fr.student_id
             LEFT JOIN users u ON u.id = fr.collected_by
             WHERE fr.institution_id = ? AND DATE(fr.receipt_date) = CURDATE()
               AND fr.status = 'active'
             ORDER BY fr.id DESC LIMIT 50",
            [$this->institutionId]
        );
        $todayReceipts = $this->db->fetchAll();

        // Today's stats
        $this->db->query(
            "SELECT
               COUNT(*) AS receipt_count,
               COALESCE(SUM(net_amount), 0) AS total_collected,
               SUM(CASE WHEN payment_mode='cash' THEN net_amount ELSE 0 END) AS cash_amt,
               SUM(CASE WHEN payment_mode='upi'  THEN net_amount ELSE 0 END) AS upi_amt,
               SUM(CASE WHEN payment_mode='card' THEN net_amount ELSE 0 END) AS card_amt
             FROM fee_receipts
             WHERE institution_id = ? AND DATE(receipt_date) = CURDATE() AND status='active'",
            [$this->institutionId]
        );
        $todayStats = $this->db->fetch();

        $this->db->query("SELECT id, name AS year_name FROM academic_years WHERE institution_id = ? AND is_current=1 ORDER BY start_date DESC LIMIT 1", [$this->institutionId]);
        $currentAy = $this->db->fetch();

        $this->view('fees/collection/index', compact('todayReceipts', 'todayStats', 'currentAy'));
    }

    // ── GET STUDENT FEES (AJAX — for payment modal) ──────────
    public function studentFees(): void
    {
        $studentId = (int)$this->input('student_id', 0);
        $ayId      = (int)$this->input('academic_year_id', 0);
        if (!$studentId) { $this->json(['status' => 'error', 'message' => 'Student required.'], 422); }

        // Student info
        $this->db->query(
            "SELECT s.*, c.name AS course_name, b.name AS batch_name,
                    CONCAT(s.first_name,' ',s.last_name) AS full_name
             FROM students s
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN batches b ON b.id = s.batch_id
             WHERE s.id = ? AND s.institution_id = ?",
            [$studentId, $this->institutionId]
        );
        $student = $this->db->fetch();
        if (!$student) { $this->json(['status' => 'error', 'message' => 'Student not found.'], 404); }

        // Fee assignments
        $query = "SELECT fsa.*,
                         fh.head_name, fh.head_code, fh.category, fh.is_mandatory,
                         ay.name AS year_name
                  FROM fee_student_assignments fsa
                  JOIN fee_heads fh ON fh.id = fsa.fee_head_id
                  LEFT JOIN academic_years ay ON ay.id = fsa.academic_year_id
                  WHERE fsa.student_id = ? AND fsa.institution_id = ?
                    AND fsa.status NOT IN ('paid','waived')";
        $params = [$studentId, $this->institutionId];
        if ($ayId) { $query .= " AND fsa.academic_year_id = ?"; $params[] = $ayId; }
        $query .= " ORDER BY fsa.due_date ASC, fh.category";

        $this->db->query($query, $params);
        $assignments = $this->db->fetchAll();

        // Auto-calculate fine
        foreach ($assignments as &$a) {
            if ($a['due_date'] && strtotime($a['due_date']) < time()) {
                $fine = $this->calculateFine($a);
                if ($fine > $a['fine_amount']) {
                    $a['fine_amount']   = $fine;
                    $a['balance_amount'] = $a['net_amount'] - $a['paid_amount'] + $fine;
                }
            }
        }
        unset($a);

        // Summary
        $totals = [
            'gross'   => array_sum(array_column($assignments, 'gross_amount')),
            'concession' => array_sum(array_column($assignments, 'concession_amount')),
            'net'     => array_sum(array_column($assignments, 'net_amount')),
            'paid'    => array_sum(array_column($assignments, 'paid_amount')),
            'fine'    => array_sum(array_column($assignments, 'fine_amount')),
            'balance' => array_sum(array_column($assignments, 'balance_amount')),
        ];

        $this->json([
            'status'      => 'success',
            'student'     => $student,
            'assignments' => $assignments,
            'totals'      => $totals,
        ]);
    }

    // ── COLLECT PAYMENT ──────────────────────────────────────
    public function collect(): void
    {
        $studentId    = (int)$this->input('student_id', 0);
        $payMode      = $this->input('payment_mode', 'cash');
        $refNo        = trim($this->input('reference_number', ''));
        $chequeNo     = trim($this->input('cheque_number', ''));
        $chequeDate   = $this->input('cheque_date') ?: null;
        $bankName     = trim($this->input('bank_name', ''));
        $remarks      = trim($this->input('remarks', ''));
        $ayId         = (int)$this->input('academic_year_id', 0);
        $assignIds    = (array)$this->input('assignment_ids');
        $payAmounts   = (array)$this->input('pay_amounts');

        if (!$studentId || empty($assignIds)) {
            $this->json(['status' => 'error', 'message' => 'Student and fee selections required.'], 422);
        }

        // Calculate totals
        $totalAmount = 0;
        $fineAmount  = 0;
        $items = [];
        foreach ($assignIds as $i => $aid) {
            $aid = (int)$aid;
            if (!$aid) continue;
            $payAmt = (float)($payAmounts[$i] ?? 0);
            if ($payAmt <= 0) continue;

            $this->db->query("SELECT * FROM fee_student_assignments WHERE id = ? AND student_id = ? AND institution_id = ?", [$aid, $studentId, $this->institutionId]);
            $asgn = $this->db->fetch();
            if (!$asgn) continue;

            $fineAmt = $this->calculateFine($asgn);
            $items[] = [
                'assignment_id' => $aid,
                'fee_head_id'   => $asgn['fee_head_id'],
                'amount'        => $payAmt,
                'fine_amount'   => $fineAmt,
                'asgn'          => $asgn,
            ];
            $totalAmount += $payAmt;
            $fineAmount  += $fineAmt;
        }

        if (empty($items)) {
            $this->json(['status' => 'error', 'message' => 'No valid payment items.'], 422);
        }

        // Generate receipt number
        $receiptNo = $this->generateReceiptNumber();

        // Insert receipt
        $this->db->insert('fee_receipts', [
            'institution_id'   => $this->institutionId,
            'student_id'       => $studentId,
            'receipt_number'   => $receiptNo,
            'receipt_date'     => date('Y-m-d'),
            'academic_year_id' => $ayId ?: null,
            'total_amount'     => $totalAmount,
            'fine_amount'      => $fineAmount,
            'discount_amount'  => 0,
            'net_amount'       => $totalAmount + $fineAmount,
            'payment_mode'     => $payMode,
            'reference_number' => $refNo ?: null,
            'cheque_number'    => $chequeNo ?: null,
            'cheque_date'      => $chequeDate,
            'bank_name'        => $bankName ?: null,
            'remarks'          => $remarks ?: null,
            'status'           => 'active',
            'collected_by'     => $this->user['id'] ?? null,
        ]);
        $receiptId = $this->db->lastInsertId();

        // Insert items + update assignments
        foreach ($items as $item) {
            $this->db->insert('fee_receipt_items', [
                'receipt_id'    => $receiptId,
                'assignment_id' => $item['assignment_id'],
                'fee_head_id'   => $item['fee_head_id'],
                'amount'        => $item['amount'],
                'fine_amount'   => $item['fine_amount'],
            ]);

            $asgn = $item['asgn'];
            $newPaid    = (float)$asgn['paid_amount'] + $item['amount'];
            $newFine    = max((float)$asgn['fine_amount'], $item['fine_amount']);
            $newBalance = (float)$asgn['net_amount'] - $newPaid;
            $newStatus  = $newBalance <= 0 ? 'paid' : 'partial';

            $this->db->query(
                "UPDATE fee_student_assignments SET paid_amount=?, fine_amount=?, balance_amount=?, status=?, updated_at=NOW() WHERE id=?",
                [$newPaid, $newFine, max(0, $newBalance), $newStatus, $item['assignment_id']]
            );
        }

        $this->logAudit('fee_collected', 'fee_receipts', $receiptId, null, ['amount' => $totalAmount]);

        $this->json([
            'status'         => 'success',
            'message'        => 'Payment collected successfully.',
            'receipt_id'     => $receiptId,
            'receipt_number' => $receiptNo,
        ]);
    }

    // ── RECEIPT VIEW ─────────────────────────────────────────
    public function receipt(int $id): void
    {
        $receipt = $this->getReceiptData($id);
        if (!$receipt) { $this->redirectWith(url('fees/collection'), 'error', 'Receipt not found.'); return; }
        $this->view('fees/receipts/view', compact('receipt'), 'main');
    }

    // ── RECEIPT PRINT ────────────────────────────────────────
    public function receiptPrint(int $id): void
    {
        $receipt = $this->getReceiptData($id);
        if (!$receipt) { http_response_code(404); echo "Receipt not found."; exit; }
        $this->view('fees/receipts/print', compact('receipt'), 'blank');
    }

    // ── CANCEL RECEIPT ───────────────────────────────────────
    public function cancel(int $id): void
    {
        $this->db->query(
            "SELECT * FROM fee_receipts WHERE id = ? AND institution_id = ? AND status = 'active'",
            [$id, $this->institutionId]
        );
        $receipt = $this->db->fetch();
        if (!$receipt) { $this->json(['status' => 'error', 'message' => 'Receipt not found or already cancelled.'], 404); }

        $reason = trim($this->input('reason', ''));
        if (!$reason) { $this->json(['status' => 'error', 'message' => 'Cancel reason is required.'], 422); }

        // Reverse assignments
        $this->db->query("SELECT * FROM fee_receipt_items WHERE receipt_id = ?", [$id]);
        foreach ($this->db->fetchAll() as $item) {
            $this->db->query(
                "UPDATE fee_student_assignments
                 SET paid_amount   = GREATEST(0, paid_amount - ?),
                     balance_amount = LEAST(net_amount, balance_amount + ?),
                     status = CASE WHEN (paid_amount - ?) <= 0 THEN 'pending' ELSE 'partial' END,
                     updated_at = NOW()
                 WHERE id = ?",
                [$item['amount'], $item['amount'], $item['amount'], $item['assignment_id']]
            );
        }

        $this->db->query(
            "UPDATE fee_receipts SET status='cancelled', cancel_reason=?, cancelled_by=?, cancelled_at=NOW() WHERE id=?",
            [$reason, $this->user['id'] ?? null, $id]
        );

        $this->json(['status' => 'success', 'message' => 'Receipt cancelled and payment reversed.']);
    }

    // ── RECEIPT DUPLICATE ────────────────────────────────────
    public function duplicate(int $id): void
    {
        $receipt = $this->getReceiptData($id);
        if (!$receipt) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); }
        $this->view('fees/receipts/print', compact('receipt'), 'blank');
    }

    // ── STUDENT RECEIPTS (for refund modal) ─────────────────
    public function studentReceipts(): void
    {
        $studentId = (int)($_GET['student_id'] ?? 0);
        if (!$studentId) { $this->json(['data' => []]); }

        $this->db->query(
            "SELECT id, receipt_number, receipt_date, payment_mode, net_amount AS total_paid
             FROM fee_receipts
             WHERE student_id = ? AND institution_id = ? AND status = 'active'
             ORDER BY receipt_date DESC LIMIT 50",
            [$studentId, $this->institutionId]
        );
        $this->json(['data' => $this->db->fetchAll()]);
    }

    // ── HELPERS ──────────────────────────────────────────────
    private function getReceiptData(int $id): ?array
    {
        $this->db->query(
            "SELECT fr.*,
                    CONCAT(s.first_name,' ',s.last_name) AS student_name,
                    s.student_id_number AS enrollment_number, s.phone,
                    c.name AS course_name, b.name AS batch_name,
                    ay.name AS year_name,
                    CONCAT(u.first_name,' ',u.last_name) AS collected_by_name,
                    i.name AS institution_name, i.address AS institution_address, i.phone AS institution_phone
             FROM fee_receipts fr
             JOIN students s ON s.id = fr.student_id
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN batches b ON b.id = s.batch_id
             LEFT JOIN academic_years ay ON ay.id = fr.academic_year_id
             LEFT JOIN users u ON u.id = fr.collected_by
             LEFT JOIN institutions i ON i.id = fr.institution_id
             WHERE fr.id = ? AND fr.institution_id = ?",
            [$id, $this->institutionId]
        );
        $receipt = $this->db->fetch();
        if (!$receipt) return null;

        $this->db->query(
            "SELECT fri.*, fh.head_name, fh.head_code, fh.category
             FROM fee_receipt_items fri
             JOIN fee_heads fh ON fh.id = fri.fee_head_id
             WHERE fri.receipt_id = ?",
            [$id]
        );
        $receipt['items'] = $this->db->fetchAll();

        // Normalize field names for view compatibility
        $receipt['collector_name'] = $receipt['collected_by_name'] ?? 'Staff';
        $receipt['enrollment_no']  = $receipt['enrollment_number'] ?? '';
        $receipt['academic_year']  = $receipt['year_name'] ?? '';

        return $receipt;
    }

    private function generateReceiptNumber(): string
    {
        $prefix = 'RCP';
        $year   = date('y');
        $this->db->query(
            "SELECT receipt_number FROM fee_receipts WHERE institution_id = ? ORDER BY id DESC LIMIT 1",
            [$this->institutionId]
        );
        $last = $this->db->fetch();
        if ($last) {
            preg_match('/(\d+)$/', $last['receipt_number'], $m);
            $seq = isset($m[1]) ? (int)$m[1] + 1 : 1;
        } else {
            $seq = 1;
        }
        return $prefix . $year . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    private function calculateFine(array $asgn): float
    {
        if (empty($asgn['due_date'])) return 0;
        $dueTs = strtotime($asgn['due_date']);
        if ($dueTs >= time()) return 0;

        // Get applicable fine rule
        $this->db->query(
            "SELECT * FROM fee_fine_rules WHERE institution_id = ? AND is_active = 1
             AND (fee_head_id IS NULL OR fee_head_id = ?)
             ORDER BY fee_head_id DESC LIMIT 1",
            [$this->institutionId, $asgn['fee_head_id']]
        );
        $rule = $this->db->fetch();
        if (!$rule) return 0;

        $daysOverdue = (int)ceil((time() - $dueTs) / 86400);
        if ($daysOverdue <= (int)$rule['grace_days']) return 0;
        $daysOverdue -= (int)$rule['grace_days'];

        $fine = 0;
        if ($rule['fine_type'] === 'per_day') {
            $fine = $daysOverdue * (float)$rule['fine_amount'];
            if ($rule['max_fine']) $fine = min($fine, (float)$rule['max_fine']);
        } elseif ($rule['fine_type'] === 'flat') {
            $fine = (float)$rule['fine_amount'];
        } elseif ($rule['fine_type'] === 'slab' && $rule['slab_config']) {
            $slabs = json_decode($rule['slab_config'], true) ?? [];
            foreach ($slabs as $slab) {
                if ($daysOverdue >= $slab['days_from'] && $daysOverdue <= $slab['days_to']) {
                    $fine = (float)$slab['amount'];
                    break;
                }
            }
        }
        return round($fine, 2);
    }
}
