<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatch2PerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->index('seller_id', 'shops_seller_id_index');
        });

        Schema::table('shipping_types', function (Blueprint $table) {
            $table->index('seller_id', 'shipping_types_seller_id_index');
        });

        Schema::table('refund_requests', function (Blueprint $table) {
            $table->index(['status', 'order_id'], 'refund_requests_status_order_id_index');
        });

        Schema::table('translations', function (Blueprint $table) {
            // Narrows the translated-name search (translatable_type + key equality)
            // before the unavoidable leading-wildcard LIKE on `value`.
            $table->index(['translationable_type', 'key'], 'translations_type_key_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex('shops_seller_id_index');
        });

        Schema::table('shipping_types', function (Blueprint $table) {
            $table->dropIndex('shipping_types_seller_id_index');
        });

        Schema::table('refund_requests', function (Blueprint $table) {
            $table->dropIndex('refund_requests_status_order_id_index');
        });

        Schema::table('translations', function (Blueprint $table) {
            $table->dropIndex('translations_type_key_index');
        });
    }
}
