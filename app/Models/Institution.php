<?php
namespace App\Models;

class Institution extends BaseModel
{
    protected string $table = 'institutions';

    public const TYPES = [
        'engineering'  => 'Engineering College',
        'arts_science' => 'Arts & Science College',
        'medical'      => 'Medical College',
        'nursing'      => 'Nursing College',
        'polytechnic'  => 'Polytechnic College',
        'other'        => 'Other',
    ];

    /**
     * Get all with org name and stats
     */
    public function getAllWithStats(array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if (!empty($filters['organization_id'])) {
            $where .= " AND i.organization_id = ?";
            $params[] = $filters['organization_id'];
        }
        if (!empty($filters['type'])) {
            $where .= " AND i.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (i.name LIKE ? OR i.code LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s]);
        }

        $orgNameRef = organizationNameReference('o');
        $sql = "SELECT i.*, {$orgNameRef} as organization_name,
                       COUNT(DISTINCT d.id) as department_count,
                       COUNT(DISTINCT c.id) as course_count,
                       COUNT(DISTINCT s.id) as student_count
                FROM institutions i
                JOIN organizations o ON o.id = i.organization_id
                LEFT JOIN departments d ON d.institution_id = i.id AND d.status = 'active'
                LEFT JOIN courses c ON c.institution_id = i.id AND c.status = 'active'
                LEFT JOIN students s ON s.institution_id = i.id AND s.status = 'active' AND s.deleted_at IS NULL
                WHERE {$where}
                GROUP BY i.id
                ORDER BY {$orgNameRef}, i.name";

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Paginated list
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if (!empty($filters['organization_id'])) {
            $where .= " AND i.organization_id = ?";
            $params[] = $filters['organization_id'];
        }
        if (!empty($filters['type'])) {
            $where .= " AND i.type = ?";
            $params[] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND i.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (i.name LIKE ? OR i.code LIKE ? OR i.city LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s]);
        }

        $orgNameRef = organizationNameReference('o');
        $sql = "SELECT i.*, {$orgNameRef} as organization_name,
                       COUNT(DISTINCT d.id) as department_count,
                       COUNT(DISTINCT c.id) as course_count
                FROM institutions i
                JOIN organizations o ON o.id = i.organization_id
                LEFT JOIN departments d ON d.institution_id = i.id AND d.status = 'active'
                LEFT JOIN courses c ON c.institution_id = i.id AND c.status = 'active'
                WHERE {$where}
                GROUP BY i.id
                ORDER BY {$orgNameRef}, i.name";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Find with full details
     */
    public function findWithDetails(int $id): ?array
    {
        $orgNameRef = organizationNameReference('o');
        $sql = "SELECT i.*, {$orgNameRef} as organization_name
                FROM institutions i
                JOIN organizations o ON o.id = i.organization_id
                WHERE i.id = ?";
        $this->db->query($sql, [$id]);
        $inst = $this->db->fetch();
        if (!$inst) return null;

        // Departments
        $this->db->query("SELECT * FROM departments WHERE institution_id = ? ORDER BY name", [$id]);
        $inst['departments'] = $this->db->fetchAll();

        // Courses
        $this->db->query("SELECT * FROM courses WHERE institution_id = ? ORDER BY name", [$id]);
        $inst['courses'] = $this->db->fetchAll();

        // Stats
        $this->db->query("SELECT COUNT(*) as c FROM students WHERE institution_id = ? AND status='active' AND deleted_at IS NULL", [$id]);
        $inst['student_count'] = (int)$this->db->fetch()['c'];

        $this->db->query("SELECT COUNT(*) as c FROM leads WHERE institution_id = ? AND deleted_at IS NULL", [$id]);
        $inst['lead_count'] = (int)$this->db->fetch()['c'];

        return $inst;
    }

    /**
     * Select options for a given organization
     */
    public function getSelectOptions(?int $organizationId = null): array
    {
        $sql = "SELECT id, name FROM institutions WHERE status = 'active'";
        $params = [];
        if ($organizationId) {
            $sql .= " AND organization_id = ?";
            $params[] = $organizationId;
        }
        $sql .= " ORDER BY name";
        $this->db->query($sql, $params);
        $rows = $this->db->fetchAll();
        $options = [];
        foreach ($rows as $r) {
            $options[$r['id']] = $r['name'];
        }
        return $options;
    }
}
