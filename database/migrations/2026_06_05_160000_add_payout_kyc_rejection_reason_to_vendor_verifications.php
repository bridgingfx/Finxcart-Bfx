<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_verifications', 'payout_kyc_rejection_reason')) {
                $table->text('payout_kyc_rejection_reason')->nullable()->after('payout_kyc_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_verifications', 'payout_kyc_rejection_reason')) {
                $table->dropColumn('payout_kyc_rejection_reason');
            }
        });
    }
};
