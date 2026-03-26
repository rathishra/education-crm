<?php
namespace App\Models;

class AcademicYear extends BaseModel
{
    protected string $table = 'academic_years';

    /**
     * Get list of academic years for a specific institution
     */
    public function getListPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $where = "inst.id IS NOT NULL";
        $params = [];

        if ($this->institutionScope) {
            $where .= " AND ay.institution_id = ?";
            $params[] = $this->institutionScope;
        } elseif (!empty($filters['institution_id'])) {
            $where .= " AND ay.institution_id = ?";
            $params[] = $filters['institution_id'];
        }

        if (!empty($filters['status'])) {
            $where .= " AND ay.status = ?";
            $params[] = $filters['status'];
        }

        $sql = "SELECT ay.*, inst.name as institution_name 
                FROM academic_years ay
                JOIN institutions inst ON inst.id = ay.institution_id
                WHERE {$where}
                ORDER BY ay.start_date DESC";

        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Set current academic year and unset others for the institution
     */
    public function setCurrent(int $id, int $institutionId): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Unset all as current
            $this->db->update($this->table, ['is_current' => 0], 'institution_id = ?', [$institutionId]);
            
            // Set this one as current
            $this->db->update($this->table, ['is_current' => 1], 'id = ?', [$id]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
