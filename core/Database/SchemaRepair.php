<?php
namespace Core\Database;

use Core\Database\Database;

class SchemaRepair
{
    public static function repair(): void
    {
        $db = Database::getInstance();
        
        try {
            self::fixOrganizations($db);
            self::fixSettings($db);
            self::fixSoftDeletes($db);
            self::fixExtensions($db);
        } catch (\Exception $e) {
            // Log the error or handle it silently to avoid breaking the app during bootstrap
            error_log("Schema Repair Error: " . $e->getMessage());
        }
    }

    private static function fixOrganizations(Database $db): void
    {
        $db->query("SHOW COLUMNS FROM organizations LIKE 'organization_name'");
        if (!$db->fetch()) {
            // Check if legacy 'name' exists
            $db->query("SHOW COLUMNS FROM organizations LIKE 'name'");
            if ($db->fetch()) {
                $db->query("ALTER TABLE organizations CHANGE COLUMN name organization_name VARCHAR(255) NOT NULL");
            }
        }

        $db->query("SHOW COLUMNS FROM organizations LIKE 'organization_code'");
        if (!$db->fetch()) {
            $db->query("SHOW COLUMNS FROM organizations LIKE 'code'");
            if ($db->fetch()) {
                $db->query("ALTER TABLE organizations CHANGE COLUMN code organization_code VARCHAR(50) NOT NULL");
            }
        }

        self::ensureColumn($db, 'organizations', 'address', "TEXT DEFAULT NULL AFTER organization_code");
        self::ensureColumn($db, 'organizations', 'max_institutions', "INT NOT NULL DEFAULT 1 AFTER address");
        self::ensureColumn($db, 'organizations', 'deleted_at', "TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
    }

    private static function fixSettings(Database $db): void
    {
        $db->query("SHOW COLUMNS FROM settings LIKE 'group_name'");
        if (!$db->fetch()) {
            $db->query("SHOW COLUMNS FROM settings LIKE 'group'");
            if ($db->fetch()) {
                $db->query("ALTER TABLE settings CHANGE COLUMN `group` group_name VARCHAR(50) NOT NULL");
            }
        }

        $db->query("SHOW COLUMNS FROM settings LIKE 'key_name'");
        if (!$db->fetch()) {
            $db->query("SHOW COLUMNS FROM settings LIKE 'key'");
            if ($db->fetch()) {
                $db->query("ALTER TABLE settings CHANGE COLUMN `key` key_name VARCHAR(100) NOT NULL");
            }
        }
    }

    private static function fixSoftDeletes(Database $db): void
    {
        $tables = [
            'institutions', 'departments', 'academic_years', 
            'courses', 'batches', 'fee_structures', 'subjects',
            'exams', 'library_books', 'transport_routes'
        ];

        foreach ($tables as $table) {
            try {
                // Check if table exists first
                $db->query("SHOW TABLES LIKE '{$table}'");
                if ($db->fetch()) {
                    self::ensureColumn($db, $table, 'deleted_at', "TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
                }
            } catch (\Exception $e) {}
        }
    }

    private static function fixExtensions(Database $db): void
    {
        // Add specific columns that might be missing from extension tables
        // Example: library_books status
        try {
            $db->query("SHOW TABLES LIKE 'library_books'");
            if ($db->fetch()) {
                self::ensureColumn($db, 'library_books', 'status', "ENUM('active', 'inactive', 'disposed') NOT NULL DEFAULT 'active' AFTER available_copies");
            }
        } catch (\Exception $e) {}
    }

    private static function ensureColumn(Database $db, string $table, string $column, string $definition): void
    {
        if (self::columnExists($db, $table, $column)) {
            return;
        }

        $definition = self::stripInvalidAfterClause($db, $table, $definition);

        try {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        } catch (\Exception $e) {
            appLog("SchemaRepair failed to add column {$column} to {$table}: {$e->getMessage()}", 'error');
        }
    }

    private static function columnExists(Database $db, string $table, string $column): bool
    {
        try {
            $pdo    = $db->getPdo();
            $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
            $stmt   = $pdo->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?"
            );
            $stmt->execute([$dbName, $table, $column]);
            return (bool)$stmt->fetchColumn();
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function stripInvalidAfterClause(Database $db, string $table, string $definition): string
    {
        return preg_replace_callback('/\s+AFTER\s+`?([a-zA-Z0-9_]+)`?/i', function ($matches) use ($db, $table) {
            $afterColumn = $matches[1];
            if (self::columnExists($db, $table, $afterColumn)) {
                return $matches[0];
            }
            return '';
        }, $definition);
    }
}
