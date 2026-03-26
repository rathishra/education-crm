<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class StaffController extends BaseController
{
    public function index(): void
    {
        $this->authorize('users.manage');

        $db = db();
        $institutionId = session('institution_id');
        
        // Self-healing migration for bank details
        try {
            $db->query("SHOW COLUMNS FROM staff_profiles LIKE 'bank_name'");
            if (!$db->fetch()) {
                $db->query("ALTER TABLE staff_profiles ADD COLUMN bank_name VARCHAR(100) DEFAULT NULL AFTER bank_account_number");
            }
            $db->query("SHOW COLUMNS FROM staff_profiles LIKE 'ifsc_code'");
            if ($db->fetch()) {
                $db->query("ALTER TABLE staff_profiles CHANGE COLUMN ifsc_code bank_ifsc VARCHAR(20) DEFAULT NULL");
            } else {
                $db->query("SHOW COLUMNS FROM staff_profiles LIKE 'bank_ifsc'");
                if (!$db->fetch()) {
                    $db->query("ALTER TABLE staff_profiles ADD COLUMN bank_ifsc VARCHAR(20) DEFAULT NULL AFTER bank_name");
                }
            }
        } catch (\Exception $e) {}

        $staff = db()->query("
            SELECT u.id as user_id, u.first_name, u.last_name, u.email, u.phone, u.status,
                   sp.id as profile_id, sp.designation, sp.salary_package, sp.joining_date,
                   d.name as department_name, 
                   GROUP_CONCAT(r.name SEPARATOR ', ') as roles
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id AND ur.institution_id = ?
            JOIN roles r ON r.id = ur.role_id
            LEFT JOIN staff_profiles sp ON sp.user_id = u.id AND sp.institution_id = ?
            LEFT JOIN departments d ON d.id = sp.department_id
            WHERE r.name != 'Student' AND r.name != 'Parent'
            GROUP BY u.id
            ORDER BY u.first_name
        ", [$institutionId, $institutionId])->fetchAll();

        $this->view('hr/staff/index', compact('staff'));
    }

    public function edit(int $userId): void
    {
        $this->authorize('users.manage');

        $institutionId = session('institution_id');
        $user = db()->query("SELECT id, first_name, last_name, email FROM users WHERE id = ?", [$userId])->fetch();
        
        if (!$user) {
            $this->redirectWith('hr/staff', 'User not found.', 'error');
            return;
        }

        $profile = db()->query("SELECT * FROM staff_profiles WHERE user_id = ? AND institution_id = ?", [$userId, $institutionId])->fetch();
        $departments = db()->query("SELECT id, name FROM departments WHERE institution_id = ? ORDER BY name", [$institutionId])->fetchAll();

        $this->view('hr/staff/edit', compact('user', 'profile', 'departments'));
    }

    public function update(int $userId): void
    {
        $this->authorize('users.manage');

        $data = $this->postData();
        $institutionId = session('institution_id');

        $profile = db()->query("SELECT id FROM staff_profiles WHERE user_id = ? AND institution_id = ?", [$userId, $institutionId])->fetch();

        $upsertData = [
            'department_id'           => $data['department_id'] ?: null,
            'designation'             => sanitize($data['designation'] ?? ''),
            'joining_date'            => $data['joining_date'] ?: null,
            'qualification'           => sanitize($data['qualification'] ?? ''),
            'total_experience_months' => (int)($data['total_experience_months'] ?? 0),
            'salary_package'          => $data['salary_package'] ? (float)$data['salary_package'] : null,
            'bank_account_number'     => sanitize($data['bank_account_number'] ?? ''),
            'bank_name'               => sanitize($data['bank_name'] ?? ''),
            'bank_ifsc'               => sanitize($data['bank_ifsc'] ?? $data['ifsc_code'] ?? '')
        ];

        if ($profile) {
            db()->update('staff_profiles', $upsertData, 'id = ?', [$profile['id']]);
        } else {
            $upsertData['user_id'] = $userId;
            $upsertData['institution_id'] = $institutionId;
            db()->insert('staff_profiles', $upsertData);
        }

        $this->redirectWith('hr/staff', 'Staff profile updated successfully.', 'success');
    }
}
