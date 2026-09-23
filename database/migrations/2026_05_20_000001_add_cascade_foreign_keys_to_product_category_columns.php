<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCascadeForeignKeysToProductCategoryColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            DELETE p
            FROM products p
            LEFT JOIN categories c1 ON c1.id = CAST(p.category_id AS UNSIGNED)
            LEFT JOIN categories c2 ON c2.id = CAST(p.sub_category_id AS UNSIGNED)
            LEFT JOIN categories c3 ON c3.id = CAST(p.sub_sub_category_id AS UNSIGNED)
            WHERE (p.category_id IS NOT NULL AND p.category_id <> '' AND p.category_id <> '0' AND c1.id IS NULL)
               OR (p.sub_category_id IS NOT NULL AND p.sub_category_id <> '' AND p.sub_category_id <> '0' AND c2.id IS NULL)
               OR (p.sub_sub_category_id IS NOT NULL AND p.sub_sub_category_id <> '' AND p.sub_sub_category_id <> '0' AND c3.id IS NULL)
        ");

        DB::statement("UPDATE products SET category_id = NULL WHERE category_id IN ('', '0')");
        DB::statement("UPDATE products SET sub_category_id = NULL WHERE sub_category_id IN ('', '0')");
        DB::statement("UPDATE products SET sub_sub_category_id = NULL WHERE sub_sub_category_id IN ('', '0')");

        $this->dropForeignIfExists('products', 'products_category_id_foreign');
        $this->dropForeignIfExists('products', 'products_sub_category_id_foreign');
        $this->dropForeignIfExists('products', 'products_sub_sub_category_id_foreign');

        DB::statement("
            ALTER TABLE products
            MODIFY category_id BIGINT UNSIGNED NULL,
            MODIFY sub_category_id BIGINT UNSIGNED NULL,
            MODIFY sub_sub_category_id BIGINT UNSIGNED NULL
        ");

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('sub_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('sub_sub_category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropForeignIfExists('products', 'products_category_id_foreign');
        $this->dropForeignIfExists('products', 'products_sub_category_id_foreign');
        $this->dropForeignIfExists('products', 'products_sub_sub_category_id_foreign');

        DB::statement("
            ALTER TABLE products
            MODIFY category_id VARCHAR(191) NULL,
            MODIFY sub_category_id VARCHAR(191) NULL,
            MODIFY sub_sub_category_id VARCHAR(191) NULL
        ");
    }

    private function dropForeignIfExists(string $tableName, string $constraintName): void
    {
        $databaseName = DB::getDatabaseName();

        $constraintExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $databaseName)
            ->where('TABLE_NAME', $tableName)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->exists();

        if ($constraintExists) {
            DB::statement("ALTER TABLE {$tableName} DROP FOREIGN KEY {$constraintName}");
        }
    }
}
