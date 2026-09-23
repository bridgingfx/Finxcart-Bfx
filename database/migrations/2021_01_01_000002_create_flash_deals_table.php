<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the base `flash_deals` table.
 *
 * This migration was missing from the repository: the first migration that
 * touches the table (2021_02_24_154706_add_deal_type_to_flash_deals) only
 * alters it, so a fresh `php artisan migrate` could never build the database.
 * It is dated 2021-01-01 so it runs before every later `flash_deals`
 * alteration. Columns mirror the FlashDeal model's fillable attributes; the
 * `deal_type` column is intentionally left to the 2021-02-24 migration.
 */
class CreateFlashDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flash_deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('featured')->default(0);
            $table->string('background_color')->nullable();
            $table->string('text_color')->nullable();
            $table->string('banner')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
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
        Schema::dropIfExists('flash_deals');
    }
}
