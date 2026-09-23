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
        // cart_shippings: only had a PRIMARY key index. `cart_group_id` is the sole
        // lookup/delete column used throughout checkout (CartManager::get_shipping_cost,
        // CartController::removeFromCart/updateCheckedCartItems, checkout validation in
        // WebController) via CartShipping::where(['cart_group_id' => ...]) and
        // ::whereIn('cart_group_id', ...)->delete() — every call was a full table scan.
        // No FK constraint exists on this column (verified via information_schema), so
        // a plain index/drop is safe.
        if (Schema::hasTable('cart_shippings')) {
            Schema::table('cart_shippings', function (Blueprint $table) {
                $table->index('cart_group_id', 'cart_shippings_cart_group_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cart_shippings')) {
            $this->dropIndexIfExists('cart_shippings', 'cart_shippings_cart_group_id_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
