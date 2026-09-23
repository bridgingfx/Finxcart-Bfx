<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_product_tiers', function (Blueprint $table) {
            // New columns based on the image:
            $table->string('images_videos_allowed')->default('1 image');
            $table->string('search_ranking')->default('Standard');
            $table->string('buyer_interaction')->default('Email');
            $table->string('analytics')->default('Views only');
            $table->string('billing_tools')->default('None');
            $table->boolean('api_integrations')->default(false); // Yes/No -> Boolean
            $table->string('listings_per_fee')->default('1 product');

            // You can remove the generic 'features' column if it exists and is no longer needed
            // $table->dropColumn('features');
        });
    }

    public function down(): void
    {
        Schema::table('admin_product_tiers', function (Blueprint $table) {
            $table->dropColumn([
                'images_videos_allowed',
                'search_ranking',
                'buyer_interaction',
                'analytics',
                'billing_tools',
                'api_integrations',
                'listings_per_fee',
            ]);
            // Re-add the features column if you need to rollback completely
            // $table->json('features')->nullable();
        });
    }
};
