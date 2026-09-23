<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeatureDealsTable extends Migration
{
    /**
     * Base table for the FeatureDeal model.
     *
     * NOTE: This project ships without the original base migrations (the
     * historical installation/backup/database.sql is absent), so this base
     * schema is reconstructed from the model definition.
     *
     * Run the migrations in order.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_deals', function (Blueprint $table) {
            $table->id();
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
        Schema::dropIfExists('feature_deals');
    }
}
