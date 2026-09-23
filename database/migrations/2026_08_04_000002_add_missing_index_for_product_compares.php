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
        // product_compares: only had a PRIMARY key index. Every storefront compare-list
        // action (ProductCompareController::index/add/delete/deleteAllCompareProduct,
        // plus the legacy Web\CompareController) filters `where user_id = ?`, and
        // `product_id` is matched on add/remove and on the whereHas('product') existence
        // check — both columns are FK-shaped (foreignId) but were never given a real FK
        // constraint or a supporting index.
        if (Schema::hasTable('product_compares')) {
            Schema::table('product_compares', function (Blueprint $table) {
                $table->index('user_id', 'product_compares_user_id_index');
                $table->index('product_id', 'product_compares_product_id_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_compares')) {
            $this->dropIndexIfExists('product_compares', 'product_compares_user_id_index');
            $this->dropIndexIfExists('product_compares', 'product_compares_product_id_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
