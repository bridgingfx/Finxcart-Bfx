<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_certificates', function (Blueprint $table) {
            if (!Schema::hasColumn('freelancer_certificates', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verification_status');
            }
            if (!Schema::hasColumn('freelancer_certificates', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('freelancer_certificates', 'verification_note')) {
                $table->text('verification_note')->nullable()->after('verified_by');
            }
            if (!Schema::hasColumn('freelancer_certificates', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('verification_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_certificates', function (Blueprint $table) {
            foreach (['verified_at', 'verified_by', 'verification_note', 'rejection_reason'] as $column) {
                if (Schema::hasColumn('freelancer_certificates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
