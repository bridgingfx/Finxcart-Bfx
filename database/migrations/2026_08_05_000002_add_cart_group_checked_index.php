<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // carts: checkout validation (WebController::checkValidationForCheckoutPages) filters
        // by cart_group_id + is_checked, and other call sites (CartManager) filter by bare
        // cart_group_id — neither was covered by the existing customer_id-leading composite
        // index, forcing a full table scan on every checkout page view.
        if (Schema::hasTable('carts')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->index(['cart_group_id', 'is_checked'], 'carts_cart_group_checked_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('carts')) {
            DB::statement("ALTER TABLE `carts` DROP INDEX IF EXISTS `carts_cart_group_checked_index`");
        }
    }
};
