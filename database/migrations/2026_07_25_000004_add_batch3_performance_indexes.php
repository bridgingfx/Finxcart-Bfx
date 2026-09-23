<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatch3PerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('brand_id', 'products_brand_id_index');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->index('kyc_status', 'sellers_kyc_status_index');
        });

        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->index('status', 'vendor_verifications_status_index');
        });

        Schema::table('delivery_men', function (Blueprint $table) {
            $table->index('seller_id', 'delivery_men_seller_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_brand_id_index');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropIndex('sellers_kyc_status_index');
        });

        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->dropIndex('vendor_verifications_status_index');
        });

        Schema::table('delivery_men', function (Blueprint $table) {
            $table->dropIndex('delivery_men_seller_id_index');
        });
    }
}
