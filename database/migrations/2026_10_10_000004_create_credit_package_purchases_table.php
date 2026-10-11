<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Records of vendors buying credit packages. A completed purchase should
     * append a matching "purchase" row to vendor_credit_ledgers.
     */
    public function up(): void
    {
        if (Schema::hasTable('credit_package_purchases')) {
            return;
        }

        Schema::create('credit_package_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->index();
            $table->unsignedBigInteger('credit_package_id')->index();
            $table->unsignedInteger('credits')->comment('Credits granted by this purchase');
            $table->decimal('amount_paid_usd', 10, 2);
            $table->string('payment_method', 60)->nullable();
            $table->string('transaction_id', 191)->nullable();
            $table->string('status', 30)->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
            $table->foreign('credit_package_id')->references('id')->on('credit_packages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_package_purchases');
    }
};
