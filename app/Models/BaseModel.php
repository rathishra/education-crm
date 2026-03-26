<?php
namespace App\Models;

use Core\Database\Database;

abstract class BaseModel
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected bool $softDeletes = false;
    protected ?int $institutionScope = null;

    public function __construct()
    {
        $this->db = db();
        $this->institutionScope = currentInstitutionId();
    }

    /**
     * Find by ID
     */
    public function find(int $id): ?array
    {
        $where = "`{$this->primaryKey}` = ?";
        $params = [$id];

        if ($this->softDeletes) {
            $where .= " AND `deleted_at` IS NULL";
        }

        $this->db->query("SELECT * FROM `{$this->table}` WHERE {$where} LIMIT 1", $params);
        return $this->db->fetch();
    }

    /**
     * Find by ID within institution scope
     */
    public function findScoped(int $id): ?array
    {
        $where = "`{$this->primaryKey}` = ? AND `institution_id` = ?";
        $params = [$id, $this->institutionScope];

        if ($this->softDeletes) {
            $where .= " AND `deleted_at` IS NULL";
        }

        $this->db->query("SELECT * FROM `{$this->table}` WHERE {$where} LIMIT 1", $params);
        return $this->db->fetch();
    }

    /**
     * Get all records
     */
    public function all(string $orderBy = 'id DESC'): array
    {
        $where = "1=1";
        $params = [];

        if ($this->institutionScope && $this->hasColumn('institution_id')) {
            $where .= " AND `institution_id` = ?";
            $params[] = $this->institutionScope;
        }

        if ($this->softDeletes) {
            $where .= " AND `deleted_at` IS NULL";
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE {$where} ORDER BY {$orderBy}";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Get records with conditions
     */
    public function where(string $conditions, array $params = [], string $orderBy = 'id DESC'): array
    {
        $where = $conditions;

        if ($this->institutionScope && $this->hasColumn('institution_id')) {
            $where = "`institution_id` = ? AND ({$where})";
            array_unshift($params, $this->institutionScope);
        }

        if ($this->softDeletes) {
            $where .= " AND `deleted_at` IS NULL";
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE {$where} ORDER BY {$orderBy}";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Get first matching record
     */
    public function first(string $conditions, array $params = []): ?array
    {
        $where = $conditions;

        if ($this->institutionScope && $this->hasColumn('institution_id')) {
            $where = "`institution_id` = ? AND ({$where})";
            array_unshift($params, $this->institutionScope);
        }

        if ($this->softDeletes) {
            $where .= " AND `deleted_at` IS NULL";
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE {$where} LIMIT 1";
        $this->db->query($sql, $params);
        return $this->db->fetch();
    }

    /**
     * Create record
     */
    public function create(array $data): int
    {
        if ($this->institutionScope && $this->hasColumn('institution_id') && !isset($data['institution_id'])) {
            $data['institution_id'] = $this->institutionScope;
        }

        return (int)$this->db->insert($this->table, $data);
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, "`{$this->primaryKey}` = ?", [$id]);
    }

    /**
     * Delete record
     */
    public function delete(int $id): int
    {
        if ($this->softDeletes) {
            return $this->db->softDelete($this->table, "`{$this->primaryKey}` = ?", [$id]);
        }
        return $this->db->delete($this->table, "`{$this->primaryKey}` = ?", [$id]);
    }

    /**
     * Count records
     */
    public function count(string $conditions = '1=1', array $params = []): int
    {
        $where = $conditions;

        if ($this->institutionScope && $this->hasColumn('institution_id')) {
            $where = "`institution_id` = ? AND ({$where})";
            array_unshift($params, $this->institutionScope);
        }

        if ($this->softDeletes) {
            $where .= " AND `deleted_at` IS NULL";
        }

        return $this->db->count($this->table, $where, $params);
    }

    /**
     * Check existence
     */
    public function exists(string $conditions, array $params = []): bool
    {
        return $this->count($conditions, $params) > 0;
    }

    /**
     * Paginate results
     */
    public function paginate(int $page = 1, int $perPage = 15, string $conditions = '1=1', array $params = [], string $orderBy = 'id DESC', string $select = '*'): array
    {
        $where = $conditions;

        if ($this->institutionScope && $this->hasColumn('institution_id')) {
            $where = "`institution_id` = ? AND ({$where})";
            array_unshift($params, $this->institutionScope);
        }

        if ($this->softDeletes) {
            $where .= " AND `deleted_at` IS NULL";
        }

        $sql = "SELECT {$select} FROM `{$this->table}` WHERE {$where} ORDER BY {$orderBy}";
        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Custom query with pagination
     */
    public function paginateQuery(string $sql, array $params, int $page, int $perPage): array
    {
        return $this->db->paginate($sql, $params, $page, $perPage);
    }

    /**
     * Execute raw query
     */
    public function raw(string $sql, array $params = []): array
    {
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Get select options (id => name)
     */
    public function selectOptions(string $valueColumn = 'name', string $conditions = '1=1', array $params = []): array
    {
        $records = $this->where($conditions, $params, $valueColumn . ' ASC');
        $options = [];
        foreach ($records as $record) {
            $options[$record[$this->primaryKey]] = $record[$valueColumn];
        }
        return $options;
    }

    /**
     * Check if table has column (basic check)
     */
    protected function hasColumn(string $column): bool
    {
        static $columns = [];
        $key = $this->table;

        if (!isset($columns[$key])) {
            $this->db->query("SHOW COLUMNS FROM `{$this->table}`");
            $cols = $this->db->fetchAll();
            $columns[$key] = array_column($cols, 'Field');
        }

        return in_array($column, $columns[$key]);
    }

    /**
     * Set institution scope
     */
    public function setInstitutionScope(?int $id): self
    {
        $this->institutionScope = $id;
        return $this;
    }

    /**
     * Remove institution scope (for cross-institution queries)
     */
    public function withoutScope(): self
    {
        $this->institutionScope = null;
        return $this;
    }
}
