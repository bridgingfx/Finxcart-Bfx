<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategoryProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        $now = now();

        DB::table('products')
            ->select('id', 'category_id', 'sub_category_id', 'sub_sub_category_id')
            ->whereNotNull('category_id')
            ->orWhereNotNull('sub_category_id')
            ->orWhereNotNull('sub_sub_category_id')
            ->orderBy('id')
            ->chunk(500, function ($products) use ($now) {
                $rows = [];
                foreach ($products as $product) {
                    foreach ([$product->category_id, $product->sub_category_id, $product->sub_sub_category_id] as $categoryId) {
                        if ($categoryId) {
                            $rows[$product->id . '-' . $categoryId] = [
                                'product_id' => $product->id,
                                'category_id' => $categoryId,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }

                if (!empty($rows)) {
                    DB::table('category_product')->insert(array_values($rows));
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_product');
    }
}
