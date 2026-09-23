<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `flash_deal_products` table.
 *
 * This migration was missing from the repository: the migration history only
 * contains alterations of this table, so a fresh `php artisan migrate` could
 * never build the database. It is dated 2021-01-01 so it runs before every
 * later `flash_deal_products` alteration. Columns mirror the corresponding Eloquent model;
 * columns introduced by later dated migrations are left to those migrations.
 */
class CreateFlashDealProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flash_deal_products', function (Blueprint $table) {
            $table->id();
            $table->integer('flash_deal_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->decimal('discount', 2)->nullable();
            $table->string('discount_type')->nullable();
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
        Schema::dropIfExists('flash_deal_products');
    }
}
