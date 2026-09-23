<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessSettingsTable extends Migration
{
    /**
     * Base table for the BusinessSetting model.
     *
     * NOTE: This project ships without the original base migrations (the
     * historical installation/backup/database.sql is absent), so this base
     * schema is reconstructed from the model definition. Columns introduced
     * by later migrations are intentionally excluded here.
     *
     * Run the migrations in order.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->text('value')->nullable();
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
        Schema::dropIfExists('business_settings');
    }
}
