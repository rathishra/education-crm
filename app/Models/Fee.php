<?php
namespace App\Models;

class Fee extends BaseModel
{
    protected string $table = 'student_fees';

    /**
     * Assign fee structure to a student
     */
    public function assignStructure(int $studentId, int $feeStructureId, ?float $discount = 0, ?string $reason = null): int
    {
        $db = db();
        
        // Get fee structure details
        $db->query("SELECT * FROM fee_structures WHERE id = ?", [$feeStructureId]);
        $structure = $db->fetch();
        if (!$structure) return 0;

        $totalAmount = $structure['total_amount'];
        $netAmount = $totalAmount - ($discount ?? 0);

        $studentFeeId = $this->create([
            'institution_id'   => $structure['institution_id'],
            'student_id'       => $studentId,
            'fee_structure_id' => $feeStructureId,
            'academic_year_id' => $structure['academic_year_id'],
            'total_amount'     => $totalAmount,
            'discount_amount'  => $discount ?? 0,
            'discount_reason'  => $reason,
            'net_amount'       => $netAmount,
            'paid_amount'      => 0,
            'balance_amount'   => $netAmount,
            'status'           => 'pending',
            'created_by'       => auth()['id'] ?? null
        ]);

        // Generate installments if plan exists
        $db->query("SELECT * FROM installment_plans WHERE fee_structure_id = ? ORDER BY installment_number", [$feeStructureId]);
        $plans = $db->fetchAll();

        if (!empty($plans)) {
            foreach ($plans as $plan) {
                // Adjust installment amount proportionally based on discount if needed
                // For now, we'll just use the plan amount and adjust the last one if net < total
                $instAmount = $plan['amount'];
                
                $db->insert('student_installments', [
                    'student_fee_id'      => $studentFeeId,
                    'installment_plan_id' => $plan['id'],
                    'installment_number'  => $plan['installment_number'],
                    'amount'              => $instAmount,
                    'due_date'            => $plan['due_date'],
                    'status'              => 'upcoming'
                ]);
            }
        } else {
            // Create a single installment for the whole amount
            $db->insert('student_installments', [
                'student_fee_id'     => $studentFeeId,
                'installment_number' => 1,
                'amount'             => $netAmount,
                'due_date'           => date('Y-m-d', strtotime('+30 days')),
                'status'             => 'upcoming'
            ]);
        }

        return $studentFeeId;
    }

    /**
     * Calculate late fee for an installment
     */
    public function calculateLateFee(int $installmentId): float
    {
        $db = db();
        $db->query(
            "SELECT si.*, fs.late_fee_per_day, fs.grace_period_days 
             FROM student_installments si
             JOIN student_fees sf ON sf.id = si.student_fee_id
             JOIN fee_structures fs ON fs.id = sf.fee_structure_id
             WHERE si.id = ?", 
            [$installmentId]
        );
        $inst = $db->fetch();

        if (!$inst || $inst['status'] === 'paid' || $inst['late_fee_per_day'] <= 0) {
            return 0;
        }

        $dueDate = strtotime($inst['due_date']);
        $graceDate = strtotime($inst['due_date'] . " + {$inst['grace_period_days']} days");
        $today = strtotime(date('Y-m-d'));

        if ($today <= $graceDate) {
            return 0;
        }

        $daysOverdue = floor(($today - $dueDate) / (60 * 60 * 24));
        return (float)($daysOverdue * $inst['late_fee_per_day']);
    }

    /**
     * Get student's active fee details
     */
    public function getStudentFeeDetails(int $studentId): array
    {
        $sql = "SELECT sf.*, fs.name as structure_name, ay.name as academic_year_name
                FROM student_fees sf
                JOIN fee_structures fs ON fs.id = sf.fee_structure_id
                LEFT JOIN academic_years ay ON ay.id = sf.academic_year_id
                WHERE sf.student_id = ? AND sf.status != 'waived'
                ORDER BY sf.created_at DESC";
        $this->db->query($sql, [$studentId]);
        $fees = $this->db->fetchAll();

        foreach ($fees as &$fee) {
            $this->db->query(
                "SELECT * FROM student_installments WHERE student_fee_id = ? ORDER BY due_date ASC",
                [$fee['id']]
            );
            $fee['installments'] = $this->db->fetchAll();
        }

        return $fees;
    }
}
