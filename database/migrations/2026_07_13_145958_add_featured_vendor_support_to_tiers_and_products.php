<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin_product_tiers', function (Blueprint $table) {
            $table->unsignedInteger('featured_product_quota')->default(0)->after('listings_per_fee');
            $table->boolean('is_featured_vendor')->default(false)->after('featured_product_quota');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('tier_featured')->default(false)->after('featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_product_tiers', function (Blueprint $table) {
            $table->dropColumn(['featured_product_quota', 'is_featured_vendor']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tier_featured');
        });
    }
};
