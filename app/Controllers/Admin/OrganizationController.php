<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class OrganizationController extends BaseController
{
    public function index(): void
    {
        $this->authorize('users.manage');

        $search = $this->input('search', '');
        $status = $this->input('status', '');

        $nameColumn = organizationNameColumn();
        $orgSoftDelete = softDeleteCondition('organizations', 'o');
        $instSoftDelete = softDeleteCondition('institutions', 'i');

        $where = '1=1';
        if ($orgSoftDelete) {
            $where .= " AND {$orgSoftDelete}";
        }
        $params = [];

        if ($search) {
            $where .= " AND (o.{$nameColumn} LIKE ? OR o.organization_code LIKE ?)";
            $term = "%{$search}%";
            $params = array_merge($params, [$term, $term]);
        }

        if ($status) {
            $where .= " AND o.status = ?";
            $params[] = $status;
        }

        $instFilter = $instSoftDelete ? " AND {$instSoftDelete}" : "";

        $page = (int)($this->input('page') ?: 1);
        $sql = "
            SELECT o.*, 
                   (SELECT COUNT(i.id) FROM institutions i WHERE i.organization_id = o.id {$instFilter}) as institutions_count
            FROM organizations o 
            WHERE {$where} 
            ORDER BY o.{$nameColumn}
        ";
        
        $organizations = db()->paginate($sql, $params, $page, config('app.per_page', 15));

        $this->view('organizations/index', compact('organizations', 'search', 'status'));
    }

    public function create(): void
    {
        $this->authorize('users.manage');
        $this->view('organizations/create');
    }

    public function store(): void
    {
        $this->authorize('users.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'organization_name' => 'required',
            'organization_code' => 'required',
            'max_institutions'  => 'required|numeric'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        // Check unique code
        $exists = db()->query("SELECT id FROM organizations WHERE organization_code = ? AND deleted_at IS NULL", [$data['organization_code']])->fetch();
        if ($exists) {
            $this->backWithErrors(['organization_code' => 'Organization code already exists.']);
            return;
        }

        $nameColumn = organizationNameColumn();
        $id = db()->insert('organizations', [
            $nameColumn            => sanitize($data['organization_name']),
            'organization_code'  => sanitize($data['organization_code']),
            'email'              => sanitize($data['email'] ?? ''),
            'phone'              => sanitize($data['phone'] ?? ''),
            'address'            => sanitize($data['address'] ?? ''),
            'max_institutions'   => (int)$data['max_institutions'],
            'status'             => $data['status'] ?? 'active'
        ]);

        $this->logAudit('organization_created', 'organizations', $id);
        $this->redirectWith('organizations', 'success', 'Organization created successfully.');
    }

    public function show(int $id): void
    {
        $this->authorize('users.manage');

        $nameCol = organizationNameColumn();
        $org = db()->query(
            "SELECT *, {$nameCol} AS name, organization_code AS code FROM organizations WHERE id = ? AND deleted_at IS NULL",
            [$id]
        )->fetch();

        if (!$org) {
            $this->redirectWith('organizations', 'error', 'Organization not found.');
            return;
        }

        // Fetch institutions belonging to this org
        db()->query(
            "SELECT i.*, i.name, i.code, i.type, i.city, i.status
             FROM institutions i
             WHERE i.organization_id = ? AND i.deleted_at IS NULL
             ORDER BY i.name",
            [$id]
        );
        $org['institutions'] = db()->fetchAll();

        $this->view('organizations/show', compact('org'));
    }

    public function edit(int $id): void
    {
        $this->authorize('users.manage');

        $organization = db()->query("SELECT * FROM organizations WHERE id = ? AND deleted_at IS NULL", [$id])->fetch();
        if (!$organization) {
            $this->redirectWith('organizations', 'error', 'Organization not found.');
            return;
        }

        $this->view('organizations/edit', compact('organization'));
    }

    public function update(int $id): void
    {
        $this->authorize('users.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'organization_name' => 'required',
            'organization_code' => 'required',
            'max_institutions'  => 'required|numeric'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $exists = db()->query("SELECT id FROM organizations WHERE organization_code = ? AND id != ? AND deleted_at IS NULL", [$data['organization_code'], $id])->fetch();
        if ($exists) {
            $this->backWithErrors(['organization_code' => 'Organization code already in use.']);
            return;
        }

        $nameColumn = organizationNameColumn();
        db()->update('organizations', [
            $nameColumn            => sanitize($data['organization_name']),
            'organization_code'  => sanitize($data['organization_code']),
            'email'              => sanitize($data['email'] ?? ''),
            'phone'              => sanitize($data['phone'] ?? ''),
            'address'            => sanitize($data['address'] ?? ''),
            'max_institutions'   => (int)$data['max_institutions'],
            'status'             => $data['status'] ?? 'active'
        ], 'id = ?', [$id]);

        $this->logAudit('organization_updated', 'organizations', $id);
        $this->redirectWith('organizations', 'success', 'Organization updated successfully.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('users.manage');

        $org = db()->query("SELECT id FROM organizations WHERE id = ? AND deleted_at IS NULL", [$id])->fetch();
        if (!$org) {
            $this->redirectWith('organizations', 'error', 'Organization not found.');
            return;
        }

        db()->query("UPDATE organizations SET deleted_at = NOW() WHERE id = ?", [$id]);
        $this->logAudit('organization_deleted', 'organizations', $id);
        $this->redirectWith('organizations', 'success', 'Organization deleted successfully.');
    }

    public function toggleStatus(int $id): void
    {
        $this->authorize('users.manage');

        $org = db()->query("SELECT id, status FROM organizations WHERE id = ? AND deleted_at IS NULL", [$id])->fetch();
        if (!$org) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not found']);
            return;
        }

        $newStatus = $org['status'] === 'active' ? 'inactive' : 'active';
        db()->query("UPDATE organizations SET status = ? WHERE id = ?", [$newStatus, $id]);
        $this->logAudit('organization_status_toggled', 'organizations', $id, ['status' => $newStatus]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'status' => $newStatus]);
    }

    public function export(): void
    {
        $this->authorize('users.manage');
        $nameColumn = organizationNameColumn();
        
        $sql = "SELECT organization_code, {$nameColumn} AS organization_name, email, phone, address, max_institutions, status 
                FROM organizations WHERE deleted_at IS NULL ORDER BY {$nameColumn}";
        db()->query($sql);
        $records = db()->fetchAll();

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="organizations_export_' . date('Ymd_His') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Code', 'Name', 'Email', 'Phone', 'Address', 'Max Institutions', 'Status']);
        
        foreach ($records as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    public function import(): void
    {
        $this->authorize('users.manage');
        $nameColumn = organizationNameColumn();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirectWith('organizations', 'error', 'Please upload a valid CSV file.');
            return;
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle); // Read headers

        $successCount = 0;
        $errorCount = 0;

        db()->beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                // Expected format: Code, Name, Email, Phone, Address, Max Institutions, Status
                if (count($row) < 7) { $errorCount++; continue; }

                $code = sanitize($row[0]);
                $name = sanitize($row[1]);
                $maxInst = (int)$row[5] ?: 1;
                $status = in_array(strtolower($row[6]), ['active', 'inactive']) ? strtolower($row[6]) : 'active';

                if (empty($code) || empty($name)) { $errorCount++; continue; }

                // Check if exists
                $exists = db()->query("SELECT id FROM organizations WHERE organization_code = ? AND deleted_at IS NULL", [$code])->fetch();
                if (!$exists) {
                    db()->insert('organizations', [
                        'organization_code'  => $code,
                        $nameColumn           => $name,
                        'email'              => sanitize($row[2]),
                        'phone'              => sanitize($row[3]),
                        'address'            => sanitize($row[4]),
                        'max_institutions'   => $maxInst,
                        'status'             => $status
                    ]);
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            db()->commit();
            
            $msg = "Import results: {$successCount} inserted successfully.";
            if ($errorCount > 0) { $msg .= " {$errorCount} rows skipped (invalid data or duplicate code)."; }
            
            $this->redirectWith('organizations', 'success', $msg);
        } catch (\Exception $e) {
            db()->rollBack();
            $this->redirectWith('organizations', 'error', 'Import failed: ' . $e->getMessage());
        }
    }

}
