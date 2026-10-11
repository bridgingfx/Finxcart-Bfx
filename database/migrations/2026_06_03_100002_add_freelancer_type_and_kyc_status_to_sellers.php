<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addFreelancerToSellerTypeEnums();
        $this->addSellerKycStatus();
        $this->seedFreelancerMaxListings();
    }

    public function down(): void
    {
        if (Schema::hasColumn('sellers', 'kyc_status')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->dropColumn('kyc_status');
            });
        }

        if (Schema::hasTable('business_settings')) {
            DB::table('business_settings')->where('type', 'freelancer_max_listings')->delete();
        }

        $hasSellerFreelancers = Schema::hasTable('sellers')
            && DB::table('sellers')->where('seller_type', 'freelancer')->exists();
        $hasVerificationFreelancers = Schema::hasTable('vendor_verifications')
            && DB::table('vendor_verifications')->where('seller_type', 'freelancer')->exists();

        if (!$hasSellerFreelancers && Schema::hasColumn('sellers', 'seller_type') && in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE sellers MODIFY COLUMN seller_type ENUM('company','individual') DEFAULT NULL");
        }

        if (!$hasVerificationFreelancers && Schema::hasColumn('vendor_verifications', 'seller_type') && in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE vendor_verifications MODIFY COLUMN seller_type ENUM('company','individual') DEFAULT NULL");
        }
    }

    private function addFreelancerToSellerTypeEnums(): void
    {
        $this->widenSellerTypeEnum('sellers');
        $this->widenSellerTypeEnum('vendor_verifications');
    }

    private function widenSellerTypeEnum(string $table): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'seller_type')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            // SQLite emulates ENUM with a CHECK constraint, which cannot be
            // altered in place. Swap the column via rename/add/copy/drop
            // (pure SQL, no doctrine/dbal introspection involved).
            DB::statement('ALTER TABLE "' . $table . '" RENAME COLUMN "seller_type" TO "seller_type_legacy"');
            DB::statement('ALTER TABLE "' . $table . '" ADD COLUMN "seller_type" varchar check ("seller_type" in (\'company\',\'individual\',\'freelancer\'))');
            DB::statement('UPDATE "' . $table . '" SET "seller_type" = "seller_type_legacy"');
            DB::statement('ALTER TABLE "' . $table . '" DROP COLUMN "seller_type_legacy"');

            return;
        }

        if ($this->enumContains($table, 'seller_type', 'freelancer')) {
            return;
        }

        DB::statement("ALTER TABLE {$table} MODIFY COLUMN seller_type ENUM('company','individual','freelancer') DEFAULT NULL");
    }

    private function addSellerKycStatus(): void
    {
        if (!Schema::hasColumn('sellers', 'kyc_status')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->enum('kyc_status', ['unsubmitted', 'pending', 'approved', 'rejected'])
                    ->default('unsubmitted')
                    ->after('seller_type')
                    ->comment('KYC verification lifecycle state');
            });
        }
    }

    private function seedFreelancerMaxListings(): void
    {
        if (!Schema::hasTable('business_settings')) {
            return;
        }

        $existing = DB::table('business_settings')->where('type', 'freelancer_max_listings')->first();

        if ($existing) {
            DB::table('business_settings')
                ->where('type', 'freelancer_max_listings')
                ->update([
                    'value' => $existing->value ?: '5',
                    'updated_at' => now(),
                ]);

            return;
        }

        DB::table('business_settings')->insert([
            'type' => 'freelancer_max_listings',
            'value' => '5',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function enumContains(string $table, string $column, string $value): bool
    {
        // information_schema is MySQL-specific; on other drivers the ENUM
        // value set cannot be inspected, so report it as present to skip the
        // MySQL-only MODIFY statements (SQLite stores enums as VARCHAR).
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return true;
        }

        $columnInfo = DB::selectOne(
            'SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
            [$table, $column]
        );

        return $columnInfo && str_contains((string) $columnInfo->COLUMN_TYPE, "'{$value}'");
    }
};
