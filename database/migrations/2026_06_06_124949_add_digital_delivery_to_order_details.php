<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->string('digital_delivery_type')->nullable()->after('digital_file_sent_at');
            $table->string('digital_delivery_value')->nullable()->after('digital_delivery_type');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['digital_delivery_type', 'digital_delivery_value']);
        });
    }
};
