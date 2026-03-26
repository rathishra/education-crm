<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PayrollController extends BaseController
{
    public function index(): void
    {
        $this->authorize('users.manage');

        $db = db();
        $institutionId = session('institution_id');

        // Self-healing migration for payslips table
        try {
            $db->query("SHOW TABLES LIKE 'payslips'");
            if (!$db->fetch()) {
                $db->query("CREATE TABLE `payslips` (
                    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` BIGINT UNSIGNED NOT NULL,
                    `institution_id` BIGINT UNSIGNED NOT NULL,
                    `month` TINYINT UNSIGNED NOT NULL,
                    `year` YEAR NOT NULL,
                    `basic_salary` DECIMAL(12,2) NOT NULL,
                    `allowances` DECIMAL(12,2) DEFAULT 0.00,
                    `deductions` DECIMAL(12,2) DEFAULT 0.00,
                    `net_salary` DECIMAL(12,2) NOT NULL,
                    `status` ENUM('generated', 'processed', 'paid', 'cancelled') NOT NULL DEFAULT 'generated',
                    `payment_method` VARCHAR(50) DEFAULT NULL,
                    `payment_date` DATE DEFAULT NULL,
                    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_payslip_user_date` (`user_id`, `month`, `year`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            } else {
                // Check if old column 'payment_status' exists and rename it
                $db->query("SHOW COLUMNS FROM `payslips` LIKE 'payment_status'");
                if ($db->fetch()) {
                    $db->query("ALTER TABLE `payslips` CHANGE COLUMN `payment_status` `status` ENUM('generated', 'processed', 'paid', 'cancelled') NOT NULL DEFAULT 'generated'");
                }
                
                // Ensure payment_method exists
                $db->query("SHOW COLUMNS FROM `payslips` LIKE 'payment_method'");
                if (!$db->fetch()) {
                    $db->query("ALTER TABLE `payslips` ADD COLUMN `payment_method` VARCHAR(50) DEFAULT NULL AFTER `status` ");
                }
            }
        } catch (\Exception $e) {}

        $monthStr = $this->input('month', date('Y-m'));
        [$year, $month] = explode('-', $monthStr);
        
        $records = $db->query("
            SELECT p.*, u.first_name, u.last_name, sp.designation
            FROM payslips p
            JOIN staff_profiles sp ON sp.user_id = p.user_id
            JOIN users u ON u.id = sp.user_id
            WHERE p.institution_id = ? AND p.month = ? AND p.year = ?
            ORDER BY u.first_name
        ", [$institutionId, (int)$month, (int)$year])->fetchAll();

        $this->view('hr/payroll/index', ['records' => $records, 'month' => $monthStr]);
    }

    public function generate(): void
    {
        $this->authorize('users.manage');

        $data = $this->postData();
        $monthStr = $data['month'] ?? date('Y-m');
        [$year, $month] = explode('-', $monthStr);
        $institutionId = session('institution_id');

        $staffs = db()->query("
            SELECT sp.user_id, sp.salary_package
            FROM staff_profiles sp
            WHERE sp.institution_id = ? AND sp.salary_package > 0
            AND NOT EXISTS (
                SELECT 1 FROM payslips p WHERE p.user_id = sp.user_id AND p.month = ? AND p.year = ?
            )
        ", [$institutionId, (int)$month, (int)$year])->fetchAll();

        $count = 0;
        foreach ($staffs as $s) {
            $basicMonth = round($s['salary_package'] / 12, 2);

            db()->insert('payslips', [
                'user_id'        => $s['user_id'],
                'institution_id' => $institutionId,
                'month'          => (int)$month,
                'year'           => (int)$year,
                'basic_salary'   => $basicMonth,
                'allowances'     => 0.00,
                'deductions'     => 0.00,
                'net_salary'     => $basicMonth,
                'status'         => 'generated'
            ]);
            $count++;
        }

        $this->backWithSuccess("Payroll generated for {$count} staff members for {$monthStr}.");
    }

    public function process(int $id): void
    {
        $this->authorize('users.manage');

        $data = $this->postData();
        $allowances = (float)($data['allowances'] ?? 0);
        $deductions = (float)($data['deductions'] ?? 0);
        $status = $data['status'] ?? 'processed';

        $record = db()->query("SELECT basic_salary FROM payslips WHERE id = ?", [$id])->fetch();
        if (!$record) {
            $this->backWithErrors(['error' => 'Payroll record not found.']);
            return;
        }

        $net = $record['basic_salary'] + $allowances - $deductions;

        db()->update('payslips', [
            'allowances'     => $allowances,
            'deductions'     => $deductions,
            'net_salary'     => $net,
            'payment_date'   => $status === 'paid' ? date('Y-m-d') : null,
            'payment_method' => $status === 'paid' ? sanitize($data['payment_method']) : null,
            'status'         => $status
        ], 'id = ?', [$id]);

        $this->backWithSuccess('Payroll record updated successfully.');
    }
}
