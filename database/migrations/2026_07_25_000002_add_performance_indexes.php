<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['seller_id', 'order_status', 'created_at'], 'orders_seller_status_created_index');
            $table->index('customer_id', 'orders_customer_id_index');
            $table->index('order_status', 'orders_order_status_index');
            $table->index('created_at', 'orders_created_at_index');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->index('order_id', 'order_details_order_id_index');
            $table->index(['seller_id', 'product_id'], 'order_details_seller_product_index');
            $table->index('product_id', 'order_details_product_id_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'request_status', 'added_by'], 'products_status_request_added_by_index');
            $table->index('user_id', 'products_user_id_index');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->index(['status', 'account_status'], 'sellers_status_account_status_index');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->index(['customer_id', 'is_guest', 'cart_group_id'], 'carts_customer_guest_group_index');
        });

        Schema::table('chattings', function (Blueprint $table) {
            $table->index('seller_id', 'chattings_seller_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_seller_status_created_index');
            $table->dropIndex('orders_customer_id_index');
            $table->dropIndex('orders_order_status_index');
            $table->dropIndex('orders_created_at_index');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropIndex('order_details_order_id_index');
            $table->dropIndex('order_details_seller_product_index');
            $table->dropIndex('order_details_product_id_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_request_added_by_index');
            $table->dropIndex('products_user_id_index');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropIndex('sellers_status_account_status_index');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex('carts_customer_guest_group_index');
        });

        Schema::table('chattings', function (Blueprint $table) {
            $table->dropIndex('chattings_seller_id_index');
        });
    }
}
