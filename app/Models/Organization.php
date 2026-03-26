<?php
namespace App\Models;

class Organization extends BaseModel
{
    protected string $table = 'organizations';

    /**
     * Get all organizations with institution count
     */
    public function getAllWithStats(): array
    {
        $sql = "SELECT o.*,
                       COUNT(DISTINCT i.id) as institution_count,
                       COUNT(DISTINCT CASE WHEN i.status = 'active' THEN i.id END) as active_institutions
                FROM organizations o
                LEFT JOIN institutions i ON i.organization_id = o.id
                WHERE o.deleted_at IS NULL
                GROUP BY o.id
                ORDER BY o.organization_name";
        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    /**
     * Get organization with institutions
     */
    public function findWithInstitutions(int $id): ?array
    {
        $org = $this->find($id);
        if (!$org) return null;

        $this->db->query(
            "SELECT * FROM institutions WHERE organization_id = ? AND deleted_at IS NULL ORDER BY name",
            [$id]
        );
        $org['institutions'] = $this->db->fetchAll();
        return $org;
    }

    /**
     * Paginated list with filters
     */
    public function getListPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $where = "1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $where .= " AND (o.organization_name LIKE ? OR o.organization_code LIKE ? OR o.email LIKE ?)";
            $s = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$s, $s, $s]);
        }

        if (!empty($filters['status'])) {
            $where .= " AND o.status = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT o.*,
                       COUNT(DISTINCT i.id) as institution_count
                FROM organizations o
                LEFT JOIN institutions i ON i.organization_id = o.id
                WHERE {$where} AND o.deleted_at IS NULL
                GROUP BY o.id
                ORDER BY o.organization_name";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Select options
     */
    public function getSelectOptions(): array
    {
        $this->db->query("SELECT id, organization_name as name FROM organizations WHERE status = 'active' AND deleted_at IS NULL ORDER BY organization_name");
        $rows = $this->db->fetchAll();
        $options = [];
        foreach ($rows as $r) {
            $options[$r['id']] = $r['name'];
        }
        return $options;
    }
}
