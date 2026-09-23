<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellerContractsTable extends Migration
{
    /**
     * Base table for the SellerContract model.
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
        Schema::create('seller_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->string('tier_name')->nullable();
            $table->string('seller_full_name')->nullable();
            $table->string('seller_entity')->nullable();
            $table->date('agreement_date')->nullable();
            $table->string('signature_image_path')->nullable();
            $table->string('contract_pdf_path')->nullable();
            $table->boolean('agreed_to_terms')->default(false);
            $table->unsignedBigInteger('vendor_tier_id')->nullable();
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
        Schema::dropIfExists('seller_contracts');
    }
}
