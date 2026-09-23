<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ENUM value changes are MySQL-specific; on other drivers (e.g.
        // SQLite) enum columns are plain VARCHAR with no value enforcement.
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }
        DB::statement("ALTER TABLE vendor_verifications MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'resubmitted') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }
        DB::statement("ALTER TABLE vendor_verifications MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};