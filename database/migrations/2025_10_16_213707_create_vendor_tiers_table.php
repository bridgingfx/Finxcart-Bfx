<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_vendor_tiers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NOTE (fix): an earlier revision of this migration used `vendor_id`;
        // aligned to `seller_id` to match the VendorTier model and the
        // superseding 2025_10_16_220836 migration (which is now a no-op guard
        // because this table already exists by the time it runs).
        Schema::create('vendor_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('seller_id');
            $table->unsignedInteger('product_tier_id');

            // Store the relevant, current values for auditing/redundancy
            $table->decimal('monthly_fee_usd', 8, 2)->nullable();
            $table->decimal('sales_commission_rate', 5, 4)->default(0.00);

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_tiers');
    }
};
