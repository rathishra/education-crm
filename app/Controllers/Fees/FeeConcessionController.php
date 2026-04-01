<?php
namespace App\Controllers\Fees;

use App\Controllers\BaseController;

class FeeConcessionController extends BaseController
{
    public function index(): void
    {
        $statusF = $_GET['status'] ?? '';
        $where  = "fc.institution_id = ?";
        $params = [$this->institutionId];
        if ($statusF) { $where .= " AND fc.status = ?"; $params[] = $statusF; }

        $this->db->query(
            "SELECT fc.*,
                    CONCAT(s.first_name,' ',s.last_name) AS student_name,
                    s.enrollment_number,
                    fh.head_name,
                    ay.year_name,
                    CONCAT(u.first_name,' ',u.last_name) AS approver_name
             FROM fee_concessions fc
             JOIN students s ON s.id = fc.student_id
             LEFT JOIN fee_heads fh ON fh.id = fc.fee_head_id
             LEFT JOIN academic_years ay ON ay.id = fc.academic_year_id
             LEFT JOIN users u ON u.id = fc.approved_by
             WHERE $where
             ORDER BY fc.created_at DESC LIMIT 500",
            $params
        );
        $concessions = $this->db->fetchAll();

        $stats = [
            'total'        => count($concessions),
            'pending'      => count(array_filter($concessions, fn($c) => $c['status'] === 'pending')),
            'approved'     => count(array_filter($concessions, fn($c) => $c['status'] === 'approved')),
            'rejected'     => count(array_filter($concessions, fn($c) => $c['status'] === 'rejected')),
            'total_amount' => array_sum(array_column(array_filter($concessions, fn($c) => $c['status'] === 'approved'), 'final_discount')),
        ];

        $this->db->query("SELECT id, head_name FROM fee_heads WHERE institution_id = ? AND is_active=1 ORDER BY head_name", [$this->institutionId]);
        $feeHeads = $this->db->fetchAll();

        $this->db->query("SELECT id, year_name FROM academic_years WHERE institution_id = ? ORDER BY start_date DESC", [$this->institutionId]);
        $academicYears = $this->db->fetchAll();

        $this->view('fees/concessions/index', compact('concessions', 'stats', 'feeHeads', 'academicYears'));
    }

    public function store(): void
    {
        $studentId = (int)$this->input('student_id', 0);
        $type      = $this->input('concession_type', 'fixed');
        $value     = (float)$this->input('concession_value', 0);
        $name      = trim($this->input('concession_name', ''));

        if (!$studentId || !$name || $value <= 0) {
            $this->json(['status' => 'error', 'message' => 'Student, name and value are required.'], 422);
        }

        $assignId = $this->input('assignment_id') ?: null;
        $headId   = $this->input('fee_head_id') ?: null;

        // Calculate final discount amount
        $finalDiscount = $value;
        if ($type === 'percentage' && $assignId) {
            $this->db->query("SELECT net_amount FROM fee_student_assignments WHERE id = ?", [$assignId]);
            $asgn = $this->db->fetch();
            $finalDiscount = round((float)($asgn['net_amount'] ?? 0) * $value / 100, 2);
        }

        $this->db->insert('fee_concessions', [
            'institution_id'   => $this->institutionId,
            'student_id'       => $studentId,
            'academic_year_id' => $this->input('academic_year_id') ?: null,
            'assignment_id'    => $assignId,
            'fee_head_id'      => $headId,
            'concession_name'  => $name,
            'concession_type'  => $type,
            'concession_value' => $value,
            'final_discount'   => $finalDiscount,
            'reason'           => trim($this->input('reason', '')),
            'category'         => $this->input('category', 'other'),
            'status'           => 'pending',
            'created_by'       => $this->user['id'] ?? null,
        ]);

        $this->json(['status' => 'success', 'message' => 'Concession request submitted for approval.']);
    }

    public function approve(int $id): void
    {
        $this->db->query(
            "SELECT * FROM fee_concessions WHERE id = ? AND institution_id = ? AND status = 'pending'",
            [$id, $this->institutionId]
        );
        $con = $this->db->fetch();
        if (!$con) { $this->json(['status' => 'error', 'message' => 'Not found or already processed.'], 404); }

        $this->db->query(
            "UPDATE fee_concessions SET status='approved', approved_by=?, approved_at=NOW() WHERE id=?",
            [$this->user['id'] ?? null, $id]
        );

        // Apply discount to assignment
        if ($con['assignment_id']) {
            $this->db->query(
                "UPDATE fee_student_assignments
                 SET concession_amount = concession_amount + ?,
                     net_amount        = GREATEST(0, net_amount - ?),
                     balance_amount    = GREATEST(0, balance_amount - ?),
                     updated_at = NOW()
                 WHERE id = ?",
                [$con['final_discount'], $con['final_discount'], $con['final_discount'], $con['assignment_id']]
            );
        }

        $this->logAudit('concession_approved', 'fee_concessions', $id);
        $this->json(['status' => 'success', 'message' => 'Concession approved and applied.']);
    }

    public function reject(int $id): void
    {
        $reason = trim($this->input('reason', ''));
        if (!$reason) { $this->json(['status' => 'error', 'message' => 'Rejection reason is required.'], 422); }

        $this->db->query(
            "UPDATE fee_concessions SET status='rejected', rejected_reason=?, approved_by=?, approved_at=NOW() WHERE id=? AND institution_id=?",
            [$reason, $this->user['id'] ?? null, $id, $this->institutionId]
        );
        $this->json(['status' => 'success', 'message' => 'Concession rejected.']);
    }

    public function destroy(int $id): void
    {
        $this->db->query(
            "SELECT status FROM fee_concessions WHERE id = ? AND institution_id = ?",
            [$id, $this->institutionId]
        );
        $row = $this->db->fetch();
        if (!$row) { $this->json(['status' => 'error', 'message' => 'Not found.'], 404); }
        if ($row['status'] === 'approved') {
            $this->json(['status' => 'error', 'message' => 'Cannot delete an approved concession.'], 422);
        }
        $this->db->query("DELETE FROM fee_concessions WHERE id = ? AND institution_id = ?", [$id, $this->institutionId]);
        $this->json(['status' => 'success', 'message' => 'Concession deleted.']);
    }
}
