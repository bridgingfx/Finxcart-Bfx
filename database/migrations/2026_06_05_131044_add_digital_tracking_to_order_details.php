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
        Schema::table('order_details', function (Blueprint $table) {
            $table->timestamp('digital_file_sent_at')->nullable()->after('digital_file_after_sell');
            $table->timestamp('digital_file_first_accessed_at')->nullable()->after('digital_file_sent_at');
            $table->string('digital_access_ip', 45)->nullable()->after('digital_file_first_accessed_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['digital_file_sent_at', 'digital_file_first_accessed_at', 'digital_access_ip']);
        });
    }
};
