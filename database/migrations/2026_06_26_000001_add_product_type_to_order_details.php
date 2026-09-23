<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('order_details', 'product_type')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->string('product_type', 20)->nullable()->after('payment_status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });
    }
};
