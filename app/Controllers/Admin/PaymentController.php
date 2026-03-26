<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PaymentController extends BaseController
{
    public function index(): void
    {
        $this->authorize('payments.view');

        $where = "1=1";
        $params = [];
        $institutionId = session('institution_id');
        if ($institutionId) { $where .= " AND p.institution_id = ?"; $params[] = $institutionId; }

        $search = $this->input('search');
        $dateFrom = $this->input('date_from');
        $dateTo   = $this->input('date_to');
        $mode     = $this->input('mode');

        if ($search) {
            $where .= " AND (p.receipt_number LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR s.student_id_number LIKE ?)";
            $s = '%' . $search . '%';
            $params = array_merge($params, [$s, $s, $s, $s]);
        }
        if ($dateFrom) { $where .= " AND DATE(p.payment_date) >= ?"; $params[] = $dateFrom; }
        if ($dateTo)   { $where .= " AND DATE(p.payment_date) <= ?"; $params[] = $dateTo; }
        if ($mode)     { $where .= " AND p.payment_mode = ?"; $params[] = $mode; }

        $page = (int)($this->input('page') ?: 1);
        $sql = "SELECT p.*,
                       CONCAT(s.first_name, ' ', s.last_name) as student_name,
                       s.student_id_number,
                       c.name as course_name,
                       CONCAT(u.first_name, ' ', u.last_name) as collected_by_name
                FROM payments p
                LEFT JOIN students s ON s.id = p.student_id
                LEFT JOIN courses c ON c.id = s.course_id
                LEFT JOIN users u ON u.id = p.collected_by
                WHERE {$where}
                ORDER BY p.payment_date DESC";

        $payments = db()->paginate($sql, $params, $page, config('app.per_page', 15));

        // Today's collection
        $todayWhere = $institutionId ? "institution_id = {$institutionId} AND" : "";
        db()->query("SELECT COALESCE(SUM(amount),0) as total FROM payments WHERE {$todayWhere} DATE(payment_date) = CURDATE() AND status = 'success'");
        $todayCollection = db()->fetch()['total'] ?? 0;

        $this->view('payments/index', compact('payments', 'search', 'dateFrom', 'dateTo', 'mode', 'todayCollection'));
    }

    public function collect(int $studentId): void
    {
        $this->authorize('payments.collect');

        db()->query(
            "SELECT s.*, c.name as course_name
             FROM students s LEFT JOIN courses c ON c.id = s.course_id
             WHERE s.id = ? AND s.deleted_at IS NULL",
            [$studentId]
        );
        $student = db()->fetch();
        if (!$student) { $this->redirectWith('payments', 'Student not found.', 'error'); return; }

        // Get pending installments
        db()->query(
            "SELECT si.*, sf.name as fee_name
             FROM student_installments si
             JOIN student_fees sf ON sf.id = si.student_fee_id
             WHERE sf.student_id = ? AND si.status IN ('pending','overdue')
             ORDER BY si.due_date ASC",
            [$studentId]
        );
        $installments = db()->fetchAll();

        $this->view('payments/collect', compact('student', 'installments'));
    }

    public function store(): void
    {
        $this->authorize('payments.collect');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'student_id' => 'required',
            'amount'     => 'required|numeric',
        ]);
        if ($errors) { $this->backWithErrors($errors); return; }

        $institutionId = session('institution_id');
        $user = auth();

        // Generate receipt number
        $receiptNumber = 'RCP-' . date('ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $paymentId = db()->insert('payments', [
            'institution_id'      => $institutionId,
            'student_id'             => $data['student_id'],
            'student_fee_id'         => $data['student_fee_id'],
            'student_installment_id' => $data['installment_id'] ?: null,
            'receipt_number'         => $receiptNumber,
            'amount'                 => (float)$data['amount'],
            'payment_method'         => $data['payment_mode'] ?? 'cash',
            'payment_date'           => $data['payment_date'] ?: date('Y-m-d'),
            'transaction_id'      => sanitize($data['transaction_id'] ?? ''),
            'bank_name'           => sanitize($data['bank_name'] ?? ''),
            'cheque_number'       => sanitize($data['cheque_number'] ?? ''),
            'remarks'             => sanitize($data['remarks'] ?? ''),
            'status'              => 'success',
            'collected_by'        => $user['id'],
        ]);

        // Update installment status if linked
        if (!empty($data['installment_id'])) {
            db()->query(
                "UPDATE student_installments SET status = 'paid', paid_date = NOW(), paid_amount = ? WHERE id = ?",
                [(float)$data['amount'], $data['installment_id']]
            );
        }

        // Update student_fees paid amount
        if (!empty($data['student_fee_id'])) {
            db()->query(
                "UPDATE student_fees SET paid_amount = paid_amount + ?, balance_amount = balance_amount - ? WHERE id = ?",
                [(float)$data['amount'], (float)$data['amount'], $data['student_fee_id']]
            );
        }

        $this->logAudit('payment_collected', 'payment', $paymentId);
        $this->redirectWith('payments/' . $paymentId . '/receipt', 'Payment recorded. Receipt #' . $receiptNumber, 'success');
    }

    public function receipt(int $id): void
    {
        $this->authorize('payments.view');

        db()->query(
            "SELECT p.*,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    s.student_id_number, s.phone as student_phone,
                    c.name as course_name,
                    b.name as batch_name,
                    i.name as institution_name, i.address_line1 as inst_address,
                    i.phone as inst_phone,
                    CONCAT(u.first_name, ' ', u.last_name) as collected_by_name
             FROM payments p
             LEFT JOIN students s ON s.id = p.student_id
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN batches b ON b.id = s.batch_id
             LEFT JOIN institutions i ON i.id = p.institution_id
             LEFT JOIN users u ON u.id = p.collected_by
             WHERE p.id = ?",
            [$id]
        );
        $payment = db()->fetch();
        if (!$payment) { $this->redirectWith('payments', 'Payment not found.', 'error'); return; }

        $this->view('payments/receipt', compact('payment'));
    }

    public function dueList(): void
    {
        $this->authorize('payments.view');

        $where = "s.deleted_at IS NULL";
        $params = [];
        $institutionId = session('institution_id');
        if ($institutionId) { $where .= " AND s.institution_id = ?"; $params[] = $institutionId; }

        db()->query(
            "SELECT s.id, s.student_id_number,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    s.phone, c.name as course_name, b.name as batch_name,
                    COALESCE(SUM(sf.balance_amount), 0) as total_due
             FROM students s
             LEFT JOIN courses c ON c.id = s.course_id
             LEFT JOIN batches b ON b.id = s.batch_id
             LEFT JOIN student_fees sf ON sf.student_id = s.id
             WHERE {$where} AND s.status = 'active'
             GROUP BY s.id
             HAVING total_due > 0
             ORDER BY total_due DESC",
            $params
        );
        $dueList = db()->fetchAll();

        $this->view('payments/due_list', compact('dueList'));
    }
}
