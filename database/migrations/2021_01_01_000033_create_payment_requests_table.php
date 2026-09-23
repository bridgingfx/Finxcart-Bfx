<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentRequestsTable extends Migration
{
    /**
     * Base table for the PaymentRequest model (App\Models\PaymentRequest).
     *
     * NOTE: This project ships without the original base migrations (the
     * historical installation/backup/database.sql is absent), so this base
     * schema is reconstructed from the model definition and the installer
     * SQL (database/migrations/payment_requests.sql). Columns introduced
     * by later migrations are intentionally excluded here.
     *
     * Run the migrations in order.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('is_paid')->default(false);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('payment_amount', 24, 3)->default(0);
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
        Schema::dropIfExists('payment_requests');
    }
}
