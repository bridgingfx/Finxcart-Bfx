<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_contracts', function (Blueprint $table) {
            $table->foreignId('freelancer_quote_request_id')->nullable()->after('freelancer_service_id')
                ->constrained('freelancer_quote_requests')->nullOnDelete();
            $table->string('delivery_status', 30)->default('pending')->after('status');
            $table->string('verdict_status', 20)->default('pending')->after('delivery_status');
            $table->text('rejection_reason')->nullable()->after('verdict_status');
            $table->timestamp('delivered_at')->nullable()->after('rejection_reason');
            $table->timestamp('verdict_at')->nullable()->after('delivered_at');

            $table->index('delivery_status');
            $table->index('verdict_status');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('freelancer_quote_request_id');
            $table->dropIndex(['delivery_status']);
            $table->dropIndex(['verdict_status']);
            $table->dropColumn(['delivery_status', 'verdict_status', 'rejection_reason', 'delivered_at', 'verdict_at']);
        });
    }
};
