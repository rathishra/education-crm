<?php
namespace Core\Database;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private ?PDOStatement $stmt = null;
    private array $columnCache = [];

    private function __construct(array $config)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']} COLLATE {$config['collation']}"
            ]);
        } catch (PDOException $e) {
            if (function_exists('appLog')) {
                appLog('Database connection failed: ' . $e->getMessage(), 'error');
            }
            die('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance(array $config = null): self
    {
        if (self::$instance === null) {
            if ($config === null) {
                throw new \RuntimeException('Database config required for first initialization');
            }
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function hasColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";
        if (array_key_exists($key, $this->columnCache)) {
            return $this->columnCache[$key];
        }

        try {
            // Use INFORMATION_SCHEMA instead of SHOW COLUMNS LIKE ? (prepared params not supported in SHOW)
            $dbName = $this->pdo->query("SELECT DATABASE()")->fetchColumn();
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?"
            );
            $stmt->execute([$dbName, $table, $column]);
            $exists = (bool)$stmt->fetchColumn();
        } catch (\Exception $e) {
            $exists = false;
        }

        $this->columnCache[$key] = $exists;
        return $exists;
    }

    /**
     * Execute a prepared query
     */
    public function query(string $sql, array $params = []): self
    {
        try {
            $this->stmt = $this->pdo->prepare($sql);
            $this->stmt->execute($params);
        } catch (PDOException $e) {
            appLog("SQL Error: {$e->getMessage()} | Query: {$sql}", 'error');
            throw $e;
        }
        return $this;
    }

    /**
     * Fetch single row
     */
    public function fetch(): ?array
    {
        $result = $this->stmt->fetch();
        return $result ?: null;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll(): array
    {
        return $this->stmt->fetchAll();
    }

    /**
     * Fetch single column value
     */
    public function fetchColumn()
    {
        return $this->stmt->fetchColumn();
    }

    /**
     * Get row count
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Insert record
     */
    public function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_map(fn($col) => "`{$col}`", array_keys($data)));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `{$table}` ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        return $this->lastInsertId();
    }

    /**
     * Update records
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(', ', array_map(fn($col) => "`{$col}` = ?", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$set} WHERE {$where}";
        $params = array_merge(array_values($data), $whereParams);
        $this->query($sql, $params);
        return $this->rowCount();
    }

    /**
     * Delete records
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $this->query($sql, $params);
        return $this->rowCount();
    }

    /**
     * Soft delete
     */
    public function softDelete(string $table, string $where, array $params = []): int
    {
        $sql = "UPDATE `{$table}` SET `deleted_at` = NOW() WHERE {$where}";
        $this->query($sql, $params);
        return $this->rowCount();
    }

    /**
     * Count records
     */
    public function count(string $table, string $where = '1=1', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$table}` WHERE {$where}";
        $this->query($sql, $params);
        return (int)$this->fetchColumn();
    }

    /**
     * Check if record exists
     */
    public function exists(string $table, string $where, array $params = []): bool
    {
        return $this->count($table, $where, $params) > 0;
    }

    /**
     * Transaction support
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Execute within transaction
     */
    public function transaction(callable $callback)
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Paginate results
     */
    public function paginate(string $sql, array $params, int $page, int $perPage): array
    {
        // Get total count
        // Remove ORDER BY to avoid issues with subqueries and performance
        $cleanSql = preg_replace('/ORDER\s+BY\s+.*$/is', '', $sql);
        $countSql = "SELECT COUNT(*) as total FROM ({$cleanSql}) as count_table";
        $this->query($countSql, $params);
        $total = (int)$this->fetch()['total'];

        // Get paginated results
        $offset = ($page - 1) * $perPage;
        $paginatedSql = "{$sql} LIMIT {$perPage} OFFSET {$offset}";
        $this->query($paginatedSql, $params);
        $data = $this->fetchAll();

        $lastPage = (int)ceil($total / $perPage);

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $lastPage,
            'from'         => $total > 0 ? $offset + 1 : 0,
            'to'           => min($offset + $perPage, $total),
        ];
    }
}
