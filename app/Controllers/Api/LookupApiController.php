<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;

class LookupApiController extends BaseController
{
    public function institutions(): void
    {
        $institutions = session('user_institutions', []);
        $this->json(['success' => true, 'data' => $institutions]);
    }

    public function departments(string $institutionId): void
    {
        $this->db->query(
            "SELECT id, name, code FROM departments WHERE institution_id = ? AND status = 'active' ORDER BY name",
            [(int)$institutionId]
        );
        $this->json(['success' => true, 'data' => $this->db->fetchAll()]);
    }

    public function courses(string $institutionId): void
    {
        $this->db->query(
            "SELECT id, name, code, degree_type FROM courses WHERE institution_id = ? AND status = 'active' ORDER BY name",
            [(int)$institutionId]
        );
        $this->json(['success' => true, 'data' => $this->db->fetchAll()]);
    }

    public function batches(string $courseId): void
    {
        $this->db->query(
            "SELECT id, name, code, section FROM batches WHERE course_id = ? AND status = 'active' ORDER BY name",
            [(int)$courseId]
        );
        $this->json(['success' => true, 'data' => $this->db->fetchAll()]);
    }

    public function counselors(string $institutionId): void
    {
        $this->db->query(
            "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
             FROM users u
             JOIN user_roles ur ON ur.user_id = u.id
             JOIN roles r ON r.id = ur.role_id
             WHERE r.slug IN ('counselor', 'inst_admin', 'org_admin', 'super_admin')
               AND u.is_active = 1
               AND (ur.institution_id = ? OR ur.institution_id IS NULL)
             ORDER BY u.first_name",
            [(int)$institutionId]
        );
        $this->json(['success' => true, 'data' => $this->db->fetchAll()]);
    }

    public function leadSources(): void
    {
        $this->db->query("SELECT id, name, slug FROM lead_sources WHERE is_active = 1 ORDER BY name");
        $this->json(['success' => true, 'data' => $this->db->fetchAll()]);
    }

    public function leadStatuses(): void
    {
        $this->db->query("SELECT id, name, slug, color FROM lead_statuses ORDER BY sort_order");
        $this->json(['success' => true, 'data' => $this->db->fetchAll()]);
    }
}
