<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // vendor_tiers: only had a PRIMARY key index. `seller_id` (+ `status`) is
        // filtered on nearly every vendor page load — the dashboard tier-expiry
        // banner (vendor-views/dashboard/index.blade.php), the "add product" tier
        // gate and VendorTierService::getActiveTierForSeller (ProductController),
        // and the tier selection/cancel pages (Vendor\Tier\TierController) all run
        // `where seller_id = ? [whereIn status] latest()` with no supporting index.
        if (Schema::hasTable('vendor_tiers')) {
            Schema::table('vendor_tiers', function (Blueprint $table) {
                $table->index(['seller_id', 'status', 'created_at'], 'vendor_tiers_seller_status_created_index');
            });
        }

        // vendor_tier_payments: the withdraw page (vendor-views/withdraw/index.blade.php)
        // runs a whereHas('vendorTier', seller_id = ?)->where('payment_method', ...) on
        // every visit with no index on vendor_tier_id (no FK either) or payment_method.
        if (Schema::hasTable('vendor_tier_payments')) {
            Schema::table('vendor_tier_payments', function (Blueprint $table) {
                $table->index('vendor_tier_id', 'vendor_tier_payments_vendor_tier_id_index');
                $table->index('payment_method', 'vendor_tier_payments_payment_method_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('vendor_tiers')) {
            $this->dropIndexIfExists('vendor_tiers', 'vendor_tiers_seller_status_created_index');
        }

        if (Schema::hasTable('vendor_tier_payments')) {
            $this->dropIndexIfExists('vendor_tier_payments', 'vendor_tier_payments_vendor_tier_id_index');
            $this->dropIndexIfExists('vendor_tier_payments', 'vendor_tier_payments_payment_method_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
