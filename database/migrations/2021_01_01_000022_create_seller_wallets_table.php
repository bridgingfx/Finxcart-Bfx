<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `seller_wallets` table.
 *
 * This migration was missing from the repository: the migration history only
 * contains alterations of this table, so a fresh `php artisan migrate` could
 * never build the database. It is dated 2021-01-01 so it runs before every
 * later `seller_wallets` alteration. Columns mirror the corresponding Eloquent model;
 * columns introduced by later dated migrations are left to those migrations.
 */
class CreateSellerWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seller_wallets', function (Blueprint $table) {
            $table->id();
            $table->string('balance')->nullable();
            $table->integer('seller_id')->nullable();
            $table->float('withdrawn')->nullable();
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
        Schema::dropIfExists('seller_wallets');
    }
}
