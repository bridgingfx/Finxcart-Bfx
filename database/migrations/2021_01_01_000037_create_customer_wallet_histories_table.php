<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerWalletHistoriesTable extends Migration
{
    /**
     * Base table for the CustomerWalletHistory model.
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
        Schema::create('customer_wallet_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->double('transaction_amount')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('transaction_method')->nullable();
            $table->string('transaction_id')->nullable();
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
        Schema::dropIfExists('customer_wallet_histories');
    }
}
