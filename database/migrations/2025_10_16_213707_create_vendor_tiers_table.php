<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_vendor_tiers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('vendor_id');
            $table->unsignedInteger('product_tier_id');

            // Store the relevant, current values for auditing/redundancy
            $table->decimal('monthly_fee_usd', 8, 2)->nullable();
            $table->decimal('sales_commission_rate', 5, 4)->default(0.00); // e.g., 0.15 for 15%

            $table->date('start_date');
            $table->date('end_date')->nullable(); // For future expiration/renewal logic
            $table->string('status')->default('active'); // active, trial, suspended, expired

            $table->timestamps();

            // Ensure a vendor can only have one active tier at a time (if required)
            $table->unique(['vendor_id', 'product_tier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_tiers');
    }
};
