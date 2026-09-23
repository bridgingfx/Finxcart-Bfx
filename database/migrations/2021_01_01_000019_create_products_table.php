<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `products` table.
 *
 * This migration was missing from the repository: the migration history only
 * contains alterations of this table, so a fresh `php artisan migrate` could
 * never build the database. It is dated 2021-01-01 so it runs before every
 * later `products` alteration. Columns mirror the corresponding Eloquent model;
 * columns introduced by later dated migrations are left to those migrations.
 */
class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('added_by')->nullable();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->integer('brand_id')->nullable();
            $table->string('unit')->nullable();
            $table->string('details')->nullable();
            $table->integer('min_qty')->nullable();
            $table->integer('published')->nullable();
            $table->float('tax')->nullable();
            $table->string('tax_type')->nullable();
            $table->float('unit_price')->nullable();
            $table->integer('status')->nullable();
            $table->float('discount')->nullable();
            $table->integer('current_stock')->nullable();
            $table->integer('free_shipping')->nullable();
            $table->integer('featured_status')->nullable();
            $table->integer('refundable')->nullable();
            $table->integer('featured')->nullable();
            $table->integer('flash_deal')->nullable();
            $table->integer('seller_id')->nullable();
            $table->float('purchase_price')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('preview_url')->nullable();
            $table->string('category_ids')->nullable();
            $table->string('colors')->nullable();
            $table->string('choice_options')->nullable();
            $table->string('variation')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('attributes')->nullable();
            $table->string('video_provider')->nullable();
            $table->string('video_url')->nullable();
            $table->string('external_url')->nullable();
            $table->string('images')->nullable();
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
        Schema::dropIfExists('products');
    }
}
