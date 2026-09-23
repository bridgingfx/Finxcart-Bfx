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
        // products.code has no index despite being validated with unique:products
        // on every single product add/update — that validation query does a full
        // table scan of `products` on every product save, getting slower as the
        // catalog grows. Not a unique index: `code` is nullable and not enforced
        // unique at the DB level, so a duplicate/legacy value already present in
        // production could make a unique index fail to apply.
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'code') && !$this->indexExists('products', 'products_code_index')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('code', 'products_code_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            $this->dropIndexIfExists('products', 'products_code_index');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($result) > 0;
    }
};
