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
        Schema::table('sellers', function (Blueprint $table) {
            // 1 = already seen (default for existing sellers so they don't get a retroactive popup)
            // 0 = KYC approved but vendor hasn't dismissed the notification yet
            $table->tinyInteger('kyc_notification_seen')->default(1)->after('kyc_status');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('kyc_notification_seen');
        });
    }
};
