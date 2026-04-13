<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PlacementController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->ensurePlacementTables();
    }

    private function ensurePlacementTables(): void
    {
        $db = db();
        // Check if placement_applications exists
        $db->query("SHOW TABLES LIKE 'placement_applications'");
        if (!$db->fetch()) {
            // Check if legacy placement_selections exists to rename it
            $db->query("SHOW TABLES LIKE 'placement_selections'");
            if ($db->fetch()) {
                $db->query("RENAME TABLE placement_selections TO placement_applications");
            } else {
                // Create from scratch if neither exists (fallback - normally handled by schema files)
                // However, our standard is to handle renaming here
            }
        }
    }

    public function companies(): void
    {
        $this->authorize('placements.view');

        $institutionId = session('institution_id');
        $companies = db()->query("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM placement_drives WHERE company_id = c.id) as total_drives
            FROM placement_companies c
            WHERE c.institution_id = ?
            ORDER BY c.name
        ", [$institutionId])->fetchAll();

        $this->view('placement/companies', compact('companies'));
    }

    public function storeCompany(): void
    {
        $this->authorize('placements.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'name' => 'required'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $id = db()->insert('placement_companies', [
            'institution_id' => session('institution_id'),
            'name'           => sanitize($data['name']),
            'industry'       => sanitize($data['industry'] ?? ''),
            'contact_person' => sanitize($data['contact_person'] ?? ''),
            'contact_email'  => sanitize($data['contact_email'] ?? ''),
            'contact_phone'  => sanitize($data['contact_phone'] ?? ''),
            'website'        => sanitize($data['website'] ?? ''),
            'status'         => $data['status'] ?? 'active'
        ]);

        $this->logAudit('placement_company_added', 'placement', $id);
        $this->redirectWith('placement/companies', 'Company added successfully.', 'success');
    }

    public function drives(): void
    {
        $this->authorize('placements.view');

        $institutionId = session('institution_id');
        $academicYearId = session('academic_year_id');

        $drives = db()->query("
            SELECT d.*, c.name as company_name,
                   (SELECT COUNT(*) FROM placement_applications WHERE drive_id = d.id) as total_applications,
                   (SELECT COUNT(*) FROM placement_applications WHERE drive_id = d.id AND status = 'selected') as total_selected
            FROM placement_drives d
            JOIN placement_companies c ON c.id = d.company_id
            WHERE d.institution_id = ? AND d.academic_year_id = ?
            ORDER BY d.drive_date DESC
        ", [$institutionId, $academicYearId])->fetchAll();

        $companies = db()->query("SELECT id, name FROM placement_companies WHERE institution_id = ? AND status = 'active' ORDER BY name", [$institutionId])->fetchAll();

        $this->view('placement/drives', compact('drives', 'companies'));
    }

    public function storeDrive(): void
    {
        $this->authorize('placements.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'company_id' => 'required|numeric',
            'title'      => 'required',
            'drive_date' => 'required|date'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        $id = db()->insert('placement_drives', [
            'institution_id'   => session('institution_id'),
            'company_id'       => $data['company_id'],
            'academic_year_id' => session('academic_year_id'),
            'title'            => sanitize($data['title']),
            'drive_date'       => $data['drive_date'],
            'eligible_courses' => json_encode($data['eligible_courses'] ?? []),
            'min_cgpa'         => $data['min_cgpa'] ? (float)$data['min_cgpa'] : null,
            'status'           => $data['status'] ?? 'upcoming'
        ]);

        $this->logAudit('placement_drive_created', 'placement', $id);
        $this->redirectWith('placement/drives', 'Drive created successfully.', 'success');
    }

    public function applications(int $driveId): void
    {
        $this->authorize('placements.view');
        
        $institutionId = session('institution_id');
        
        $drive = db()->query("
            SELECT d.*, c.name as company_name 
            FROM placement_drives d
            JOIN placement_companies c ON c.id = d.company_id
            WHERE d.id = ? AND d.institution_id = ?
        ", [$driveId, $institutionId])->fetch();

        if (!$drive) {
            $this->redirectWith('placement/drives', 'Drive not found.', 'error');
            return;
        }

        $applications = db()->query("
            SELECT a.*, s.first_name, s.last_name, s.student_id_number,
                   co.name as course_name, b.name as batch_name
            FROM placement_applications a
            JOIN students s ON s.id = a.student_id
            LEFT JOIN batches b ON b.id = s.batch_id
            LEFT JOIN courses co ON co.id = b.course_id
            WHERE a.drive_id = ?
            ORDER BY a.created_at DESC
        ", [$driveId])->fetchAll();

        $this->view('placement/applications', compact('drive', 'applications'));
    }

    public function storeApplication(int $driveId): void
    {
        $this->authorize('placements.manage');

        $data = $this->postData();
        $errors = $this->validate($data, [
            'student_id' => 'required|numeric'
        ]);

        if ($errors) {
            $this->backWithErrors($errors);
            return;
        }

        // Check if student exists and belongs to institution (via batch/course logic simplified)
        // Check if already applied
        $existing = db()->query("SELECT id FROM placement_applications WHERE drive_id = ? AND student_id = ?", [$driveId, $data['student_id']])->fetch();
        if ($existing) {
            $this->backWithErrors(['error' => 'Student has already applied for this drive.']);
            return;
        }

        $id = db()->insert('placement_applications', [
            'drive_id'   => $driveId,
            'student_id' => $data['student_id'],
            'status'     => 'applied',
            'remarks'    => sanitize($data['remarks'] ?? '')
        ]);

        $this->logAudit('placement_application_added', 'placement', $id);
        $this->backWithSuccess('Student application added.');
    }

    public function updateApplication(int $driveId, int $applicationId): void
    {
        $this->authorize('placements.manage');

        $data = $this->postData();
        $status = $data['status'] ?? null;
        $package = $data['offer_package'] ?? null;

        if (!$status) {
            $this->backWithErrors(['error' => 'Status is required.']);
            return;
        }

        db()->update('placement_applications', [
            'status'        => $status,
            'offer_package' => $package ? (float)$package : null,
            'remarks'       => sanitize($data['remarks'] ?? '')
        ], '`id` = ? AND `drive_id` = ?', [$applicationId, $driveId]);

        $this->backWithSuccess('Application status updated.');
    }
}
