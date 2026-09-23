<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_contracts', function (Blueprint $table) {
            // requested|approved|denied — null means no cancellation has ever been requested.
            $table->string('cancellation_status', 20)->nullable()->after('verdict_at');
            $table->text('cancellation_reason')->nullable()->after('cancellation_status');
            $table->text('cancellation_admin_note')->nullable()->after('cancellation_reason');
            $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_admin_note');
            $table->timestamp('cancellation_decided_at')->nullable()->after('cancellation_requested_at');

            $table->index('cancellation_status');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_contracts', function (Blueprint $table) {
            $table->dropIndex(['cancellation_status']);
            $table->dropColumn([
                'cancellation_status',
                'cancellation_reason',
                'cancellation_admin_note',
                'cancellation_requested_at',
                'cancellation_decided_at',
            ]);
        });
    }
};
