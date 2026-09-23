<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_verifications', 'payout_kyc_document')) {
                $table->string('payout_kyc_document')->nullable()->after('sumsub_review_status');
            }

            if (!Schema::hasColumn('vendor_verifications', 'payout_kyc_note')) {
                $table->text('payout_kyc_note')->nullable()->after('payout_kyc_document');
            }

            if (!Schema::hasColumn('vendor_verifications', 'payout_kyc_submitted_at')) {
                $table->timestamp('payout_kyc_submitted_at')->nullable()->after('payout_kyc_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            foreach (['payout_kyc_submitted_at', 'payout_kyc_note', 'payout_kyc_document'] as $column) {
                if (Schema::hasColumn('vendor_verifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
