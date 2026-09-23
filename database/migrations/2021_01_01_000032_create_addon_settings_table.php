<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddonSettingsTable extends Migration
{
    /**
     * Base table for the Setting model (App\Models\Setting).
     *
     * NOTE: This project ships without the original base migrations (the
     * historical installation/backup/database.sql is absent), so this base
     * schema is reconstructed from the model definition and the installer
     * SQL (database/migrations/addon_settings.sql). Columns introduced
     * by later migrations are intentionally excluded here.
     *
     * Run the migrations in order.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addon_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key_name')->nullable()->unique();
            $table->text('live_values')->nullable();
            $table->text('test_values')->nullable();
            $table->string('settings_type')->nullable();
            $table->string('mode')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('additional_data')->nullable();
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
        Schema::dropIfExists('addon_settings');
    }
}
