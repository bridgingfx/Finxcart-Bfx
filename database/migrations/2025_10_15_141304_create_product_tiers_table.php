<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This creates the core table for managing product tier pricing and features,
     * allowing the admin to define the tiers and vendors to select them.
     */
    public function up(): void
    {
        Schema::create('admin_product_tiers', function (Blueprint $table) {
            $table->id();

            // TIER IDENTITY
            $table->string('name', 191)->unique();
            $table->string('target_category', 150)->comment('e.g., One-time sales (low-value), Recurring/monthly rentals');
            $table->text('ideal_product_types')->comment('Examples of products ideal for this tier.');

            // PRICING & FEES
            // Monthly fee, NULL for commission-only tiers (like 'One-Time'). Stored in USD.
            $table->decimal('monthly_fee_usd', 10, 2)->nullable()->default(0.00);
            // Commission rate (e.g., 0.15 for 15%, 0.30 for 30%).
            $table->decimal('sales_commission_rate', 5, 4)->default(0.00);
            // Flag for tiers that charge commission without a monthly fee.
            $table->boolean('is_commission_only')->default(false);

            // ELIGIBILITY & THRESHOLDS (Based on Product Price)
            // Price range for one-time sales eligibility. NULL if recurring-focused.
            $table->decimal('price_threshold_min_usd', 10, 2)->nullable();
            $table->decimal('price_threshold_max_usd', 10, 2)->nullable();
            // Flag for tiers focused on recurring revenue (N/A for price threshold).
            $table->boolean('is_recurring_focus')->default(false);

            // POLICY & FEATURES
            // Policy based on the PDF: "Free First Month Policy"
            $table->boolean('is_free_first_month')->default(false);
            // Stores all detailed features (Search Ranking, Analytics, Billing Tools, etc.) as JSON.
            $table->json('features')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_product_tiers');
    }
};
