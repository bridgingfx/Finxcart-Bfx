<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_services', function (Blueprint $table) {
            $table->boolean('offers_subscription')->default(false)->after('delivery_time_days')->index();
            $table->boolean('offers_video_consultation')->default(false)->after('offers_subscription')->index();
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_services', function (Blueprint $table) {
            $table->dropColumn(['offers_subscription', 'offers_video_consultation']);
        });
    }
};
