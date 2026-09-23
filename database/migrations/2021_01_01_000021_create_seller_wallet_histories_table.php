<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `seller_wallet_histories` table.
 *
 * This migration was missing from the repository: the migration history only
 * contains alterations of this table, so a fresh `php artisan migrate` could
 * never build the database. It is dated 2021-01-01 so it runs before every
 * later `seller_wallet_histories` alteration. Columns mirror the corresponding Eloquent model;
 * columns introduced by later dated migrations are left to those migrations.
 */
class CreateSellerWalletHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->float('amount')->nullable();
            $table->string('product_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('delivery_man_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('comment')->nullable();
            $table->text('attachment')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('status')->nullable();
            $table->boolean('is_saved')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seller_wallet_histories');
    }
}
