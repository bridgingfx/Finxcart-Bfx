<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_tier_payments', function (Blueprint $table) {
            $table->id();
           $table->unsignedBigInteger('vendor_tier_id')->nullable();


            // Payment period this payment covers
            $table->date('billing_start_date');
            $table->date('billing_end_date');

            // Financial details
            $table->decimal('amount_paid', 8, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_method')->nullable();
            $table->string('transaction_id', 100)->nullable()->unique();
            $table->string('status')->default('pending'); // completed, failed, refunded, pending
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['billing_start_date', 'billing_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_tier_payments');
    }
};
