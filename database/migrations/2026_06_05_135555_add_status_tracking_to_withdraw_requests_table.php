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
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('approved');
            $table->timestamp('denied_at')->nullable()->after('approved_at');
            // money delivery: pending_transfer → in_transfer → transferred | failed
            $table->string('delivery_status', 30)->default('pending_transfer')->after('denied_at');
            $table->timestamp('delivery_status_at')->nullable()->after('delivery_status');
            $table->text('delivery_note')->nullable()->after('delivery_status_at');
        });
    }

    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'denied_at', 'delivery_status', 'delivery_status_at', 'delivery_note']);
        });
    }
};
