<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that will be permanently removed.
     *
     * Child tables are placed before freelancer_profiles.
     */
    private const TARGET_TABLES = [
        'freelancer_profile_verification_logs',
        'freelancer_certificates',
        'freelancer_work_experiences',
        'freelancer_profile_skills',
        'freelancer_profiles',
    ];

    /**
     * Model classes that must not be referenced externally.
     */
    private const REFERENCE_TYPES = [
        'App\\Models\\FreelancerProfile',
        'App\\Models\\FreelancerCertificate',
        'App\\Models\\FreelancerProfileSkill',
        'App\\Models\\FreelancerProfileVerificationLog',
        'App\\Models\\FreelancerWorkExperience',
    ];

    /**
     * Tables that may contain model or table names as text.
     */
    private const TABLES_TO_SCAN = [
        'notifications',
        'activity_logs',
        'attachments',
        'favorites',
        'media',
        'orders',
        'audit_logs',
    ];

    /**
     * Run the migration.
     */
    public function up(): void
    {
        /*
         * freelancer_services must remain active.
         *
         * Remove only its foreign key and freelancer_profile_id column
         * before deleting the freelancer_profiles table.
         */
        $this->removeFreelancerServicesProfileReference();

        /*
         * Stop the migration if any other active table still references
         * one of the tables that will be deleted.
         */
        $this->assertNoUnexpectedForeignKeys();
        $this->assertNoExternalPolymorphicReferences();
        $this->assertNoExternalStringReferences();

        /*
         * Drop the profile-related tables.
         */
        Schema::disableForeignKeyConstraints();

        try {
            foreach (self::TARGET_TABLES as $table) {
                Schema::dropIfExists($table);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * This destructive migration cannot be rolled back automatically.
     */
    public function down(): void
    {
        throw new \RuntimeException(
            'Freelancer profile tables and data cannot be restored by rollback. Restore the database and uploaded files from backups.'
        );
    }

    /**
     * Remove freelancer_services.freelancer_profile_id and its foreign key.
     */
    private function removeFreelancerServicesProfileReference(): void
    {
        $tableName = 'freelancer_services';
        $columnName = 'freelancer_profile_id';

        if (!Schema::hasTable($tableName)) {
            return;
        }

        if (!Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            /*
             * Find every foreign key attached to freelancer_profile_id.
             *
             * Reading the real key name from information_schema avoids
             * depending on Laravel's default constraint naming.
             */
            $foreignKeys = DB::select(
                "SELECT CONSTRAINT_NAME AS constraint_name
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$tableName, $columnName]
            );

            foreach ($foreignKeys as $foreignKey) {
                $constraintName = str_replace(
                    '`',
                    '``',
                    (string) $foreignKey->constraint_name
                );

                DB::statement(
                    "ALTER TABLE `freelancer_services`
                     DROP FOREIGN KEY `{$constraintName}`"
                );
            }
        } else {
            /*
             * Fallback for other database drivers.
             */
            try {
                Schema::table($tableName, function (Blueprint $table): void {
                    $table->dropForeign(
                        'freelancer_services_freelancer_profile_id_foreign'
                    );
                });
            } catch (\Throwable $exception) {
                /*
                 * Continue when the foreign key has already been removed.
                 */
            }
        }

        /*
         * Remove the column after removing its foreign key.
         */
        if (Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn('freelancer_profile_id');
            });
        }
    }

    /**
     * Verify that no external foreign keys reference the target tables.
     */
    private function assertNoUnexpectedForeignKeys(): void
    {
        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        $placeholders = implode(
            ',',
            array_fill(0, count(self::TARGET_TABLES), '?')
        );

        $references = DB::select(
            "SELECT
                TABLE_NAME AS table_name,
                CONSTRAINT_NAME AS constraint_name,
                REFERENCED_TABLE_NAME AS referenced_table_name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND REFERENCED_TABLE_NAME IN ({$placeholders})",
            self::TARGET_TABLES
        );

        $unexpected = array_filter(
            $references,
            fn (object $reference): bool =>
                !in_array(
                    $reference->table_name,
                    self::TARGET_TABLES,
                    true
                )
        );

        if (empty($unexpected)) {
            return;
        }

        $details = collect($unexpected)
            ->map(
                fn (object $reference): string =>
                    "{$reference->table_name}."
                    . "{$reference->constraint_name} -> "
                    . "{$reference->referenced_table_name}"
            )
            ->implode('; ');

        throw new \RuntimeException(
            'Unexpected external foreign keys reference freelancer profile tables: '
            . $details
        );
    }

    /**
     * Verify that no polymorphic relations reference removed models.
     */
    private function assertNoExternalPolymorphicReferences(): void
    {
        foreach ($this->existingTablesExceptTargets() as $table) {
            foreach (
                $this->polymorphicColumns($table)
                as $typeColumn => $idColumn
            ) {
                $count = DB::table($table)
                    ->whereIn($typeColumn, self::REFERENCE_TYPES)
                    ->whereNotNull($idColumn)
                    ->count();

                if ($count > 0) {
                    throw new \RuntimeException(
                        "Found {$count} polymorphic reference(s) in "
                        . "{$table}.{$typeColumn}/{$idColumn}."
                    );
                }
            }
        }
    }

    /**
     * Verify selected tables contain no string references to removed models.
     */
    private function assertNoExternalStringReferences(): void
    {
        $needles = array_merge(
            self::REFERENCE_TYPES,
            self::TARGET_TABLES
        );

        foreach (self::TABLES_TO_SCAN as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (in_array($table, self::TARGET_TABLES, true)) {
                continue;
            }

            foreach ($this->searchableColumns($table) as $column) {
                foreach ($needles as $needle) {
                    $count = DB::table($table)
                        ->where($column, 'like', '%' . $needle . '%')
                        ->count();

                    if ($count > 0) {
                        throw new \RuntimeException(
                            "Found {$count} string reference(s) to "
                            . "{$needle} in {$table}.{$column}."
                        );
                    }
                }
            }
        }
    }

    /**
     * Return existing tables except the tables being deleted.
     */
    private function existingTablesExceptTargets(): array
    {
        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return [];
        }

        $rows = DB::select(
            "SELECT TABLE_NAME AS table_name
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_TYPE = 'BASE TABLE'"
        );

        $tables = array_map(
            fn (object $row): string => $row->table_name,
            $rows
        );

        return array_values(
            array_filter(
                $tables,
                fn (string $table): bool =>
                    !in_array($table, self::TARGET_TABLES, true)
            )
        );
    }

    /**
     * Find polymorphic type and ID column pairs.
     */
    private function polymorphicColumns(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        $pairs = [];

        foreach ($columns as $column) {
            if (!str_ends_with($column, '_type')) {
                continue;
            }

            $idColumn = substr($column, 0, -5) . '_id';

            if (in_array($idColumn, $columns, true)) {
                $pairs[$column] = $idColumn;
            }
        }

        return $pairs;
    }

    /**
     * Return text-based columns that can contain string references.
     */
    private function searchableColumns(string $table): array
    {
        $driver = DB::getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return [];
        }

        $columns = DB::select(
            "SELECT COLUMN_NAME AS column_name
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND DATA_TYPE IN (
                   'char',
                   'varchar',
                   'tinytext',
                   'text',
                   'mediumtext',
                   'longtext',
                   'json'
               )",
            [$table]
        );

        return array_map(
            fn (object $column): string => $column->column_name,
            $columns
        );
    }
};