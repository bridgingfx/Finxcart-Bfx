<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_tiers', function (Blueprint $table) {
            $table->id();
            // --- UPDATED: Use seller_id constrained to the 'sellers' table ---
            $table->unsignedInteger('seller_id');
            // -----------------------------------------------------------------
            $table->unsignedInteger('product_tier_id');

            // Store the relevant, current values for auditing/redundancy
            $table->decimal('monthly_fee_usd', 8, 2)->nullable();
            $table->decimal('sales_commission_rate', 5, 4)->default(0.00);

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');

            $table->timestamps();

            // --- UPDATED: Unique constraint uses seller_id ---
            // $table->unique(['seller_id', 'product_tier_id']);
            // ---------------------------------------------------
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_tiers');
    }
};
