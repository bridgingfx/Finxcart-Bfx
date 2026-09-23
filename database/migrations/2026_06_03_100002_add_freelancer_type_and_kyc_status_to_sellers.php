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

        if (!$hasSellerFreelancers && Schema::hasColumn('sellers', 'seller_type')) {
            DB::statement("ALTER TABLE sellers MODIFY COLUMN seller_type ENUM('company','individual') DEFAULT NULL");
        }

        if (!$hasVerificationFreelancers && Schema::hasColumn('vendor_verifications', 'seller_type')) {
            DB::statement("ALTER TABLE vendor_verifications MODIFY COLUMN seller_type ENUM('company','individual') DEFAULT NULL");
        }
    }

    private function addFreelancerToSellerTypeEnums(): void
    {
        if (Schema::hasColumn('sellers', 'seller_type') && !$this->enumContains('sellers', 'seller_type', 'freelancer')) {
            DB::statement("ALTER TABLE sellers MODIFY COLUMN seller_type ENUM('company','individual','freelancer') DEFAULT NULL");
        }

        if (
            Schema::hasTable('vendor_verifications')
            && Schema::hasColumn('vendor_verifications', 'seller_type')
            && !$this->enumContains('vendor_verifications', 'seller_type', 'freelancer')
        ) {
            DB::statement("ALTER TABLE vendor_verifications MODIFY COLUMN seller_type ENUM('company','individual','freelancer') DEFAULT NULL");
        }
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
        $columnInfo = DB::selectOne(
            'SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1',
            [$table, $column]
        );

        return $columnInfo && str_contains((string) $columnInfo->COLUMN_TYPE, "'{$value}'");
    }
};
