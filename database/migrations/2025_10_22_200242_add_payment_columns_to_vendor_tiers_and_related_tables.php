<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_tiers', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_tiers', 'last_payment_method')) {
                $table->string('last_payment_method')->nullable()->after('status');
            }

            // Auto-renewal flag
            if (! Schema::hasColumn('vendor_tiers', 'is_auto_renew')) {
                $table->boolean('is_auto_renew')->default(true)->after('last_payment_method');
            }

            // Optional: if you want to track renewal date
            if (! Schema::hasColumn('vendor_tiers', 'renewal_date')) {
                $table->date('renewal_date')->nullable()->after('is_auto_renew');
            }
        });

        if (Schema::hasTable('vendor_tier_payments')) {
            Schema::table('vendor_tier_payments', function (Blueprint $table) {
                $table->string('payment_status')->default('completed')->after('status'); // completed, pending, failed, refunded

                // Optional JSON metadata (gateway response, invoice id, etc.)
                $table->text('payment_meta')->nullable();
            });
        }

        if (Schema::hasTable('admin_product_tiers') && ! Schema::hasColumn('admin_product_tiers', 'display_order')) {
            Schema::table('admin_product_tiers', function (Blueprint $table) {
                $table->integer('display_order')->default(0)->after('listings_per_fee');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_tiers', function (Blueprint $table) {
            $table->dropColumn(['last_payment_method', 'is_auto_renew', 'renewal_date']);
        });

        Schema::table('vendor_tier_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_meta']);
        });

        Schema::table('admin_product_tiers', function (Blueprint $table) {
            $table->dropColumn('display_order');
        });
    }
};
