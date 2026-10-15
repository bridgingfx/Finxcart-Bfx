<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->text('approval_note')->nullable()->after('rejection_reason');
            $table->string('reviewed_by_name', 100)->nullable()->after('approval_note');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->dropColumn(['approval_note', 'reviewed_by_name', 'reviewed_at']);
        });
    }
};
