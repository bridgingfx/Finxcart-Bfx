<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `admin_wallet_histories` table.
 *
 * This migration was missing from the repository: the migration history only
 * contains alterations of this table, so a fresh `php artisan migrate` could
 * never build the database. It is dated 2021-01-01 so it runs before every
 * later `admin_wallet_histories` alteration. Columns mirror the corresponding Eloquent model;
 * columns introduced by later dated migrations are left to those migrations.
 */
class CreateAdminWalletHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('admin_id')->nullable();
            $table->float('amount')->nullable();
            $table->integer('order_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('payment')->nullable();
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
        Schema::dropIfExists('admin_wallet_histories');
    }
}
