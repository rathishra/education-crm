<?php
namespace App\Models;

class Department extends BaseModel
{
    protected string $table = 'departments';

    /**
     * Get departments with institution name
     */
    public function getListPaginated(int $page, int $perPage, array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if (!empty($filters['institution_id'])) {
            $where .= " AND d.institution_id = ?";
            $params[] = $filters['institution_id'];
        }
        if (!empty($filters['status'])) {
            $where .= " AND d.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where .= " AND (d.name LIKE ? OR d.code LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s]);
        }

        $sql = "SELECT d.*, i.name as institution_name,
                       COUNT(DISTINCT c.id) as course_count,
                       COUNT(DISTINCT f.id) as faculty_count
                FROM departments d
                JOIN institutions i ON i.id = d.institution_id
                LEFT JOIN courses c ON c.department_id = d.id AND c.status = 'active'
                LEFT JOIN faculty_profiles f ON f.department_id = d.id AND f.status = 'active'
                WHERE {$where}
                GROUP BY d.id
                ORDER BY i.name, d.name";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Get departments for an institution
     */
    public function getByInstitution(int $institutionId): array
    {
        $this->db->query(
            "SELECT * FROM departments WHERE institution_id = ? AND status = 'active' ORDER BY name",
            [$institutionId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Select options
     */
    public function getSelectOptions(?int $institutionId = null): array
    {
        $sql = "SELECT id, name FROM departments WHERE status = 'active'";
        $params = [];
        if ($institutionId) {
            $sql .= " AND institution_id = ?";
            $params[] = $institutionId;
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
