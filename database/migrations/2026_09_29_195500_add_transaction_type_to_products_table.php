<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADDITIVE + REVERSIBLE: explicit per-listing transaction-type override.
 *
 * Adds `products.transaction_type` (nullable string). NULL (default) means
 * "derive from product_type / digital_product_type via
 * App\Enums\TransactionType::forProduct()". A non-null value holding one of
 * the TransactionType values wins over the derived mapping.
 *
 * Why: most listings derive their commercial type reliably from existing
 * fields, but a handful of listings are semantically mislabeled (e.g. a
 * fixed-scope service sold as a digital download product). Rather than
 * guessing from names, an admin can pin the honest type here.
 *
 * ROLLBACK: `php artisan migrate:rollback` drops the column. No data loss:
 * the column is nullable, never backfilled, and nothing reads it except
 * TransactionType::forProduct(), which treats a missing column as "derive".
 * If the column is dropped manually, forProduct() still works because it
 * uses `$product->transaction_type ?? null`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('transaction_type', 30)->nullable()->after('product_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
    }
};
