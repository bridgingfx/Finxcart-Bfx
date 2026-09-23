<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddIdColOrderTran extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The 2021_09_28 create migration already defines an `id` column, so
        // skip when it is present (e.g. databases built before the 2021_10_14
        // drop was introduced).
        if (Schema::hasColumn('order_transactions', 'id')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            // SQLite cannot ADD a PRIMARY KEY column to an existing table,
            // so rebuild it with the auto-increment id (mirrors the MySQL
            // ALTER below). Column list matches the table as of 2021_10_15.
            Schema::create('order_transactions_new', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('seller_id');
                $table->bigInteger('order_id');
                $table->decimal('order_amount')->default(0);
                $table->decimal('seller_amount')->default(0);
                $table->decimal('admin_commission')->default(0);
                $table->string('received_by');
                $table->string('status')->nullable();
                $table->decimal('delivery_charge')->default(0);
                $table->decimal('tax')->default(0);
                $table->timestamps();
                $table->bigInteger('customer_id')->nullable();
                $table->string('seller_is')->nullable();
                $table->string('delivered_by')->default('admin');
                $table->string('payment_method')->nullable();
                $table->string('transaction_id')->nullable();
            });

            $columns = ['seller_id', 'order_id', 'order_amount', 'seller_amount', 'admin_commission', 'received_by', 'status', 'delivery_charge', 'tax', 'created_at', 'updated_at', 'customer_id', 'seller_is', 'delivered_by', 'payment_method', 'transaction_id'];
            $list = implode(',', $columns);
            DB::statement("INSERT INTO order_transactions_new ($list) SELECT $list FROM order_transactions");
            Schema::drop('order_transactions');
            Schema::rename('order_transactions_new', 'order_transactions');
            return;
        }

        Schema::table('order_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            //
        });
    }
}
