<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the monthly promotional-credit allowance to the vendor plan
     * catalog (Starter 0, Professional 20, Business 75, Enterprise 250
     * per the FinXCart monetization plan 2026-10-10).
     */
    public function up(): void
    {
        if (!Schema::hasTable('admin_product_tiers')) {
            return;
        }
        if (Schema::hasColumn('admin_product_tiers', 'monthly_credits')) {
            return;
        }

        Schema::table('admin_product_tiers', function (Blueprint $table) {
            $table->unsignedInteger('monthly_credits')->default(0)->after('monthly_fee_usd')
                ->comment('Promotional credits granted to vendors on this tier each month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('admin_product_tiers', 'monthly_credits')) {
            Schema::table('admin_product_tiers', function (Blueprint $table) {
                $table->dropColumn('monthly_credits');
            });
        }
    }
};
