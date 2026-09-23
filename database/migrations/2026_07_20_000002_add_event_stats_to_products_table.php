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
        Schema::table('products', function (Blueprint $table) {
            $table->string('event_days', 50)->nullable()->after('brochure_storage_type');
            $table->string('event_industry_brands', 50)->nullable()->after('event_days');
            $table->string('event_speakers_count', 50)->nullable()->after('event_industry_brands');
            $table->string('event_audience', 50)->nullable()->after('event_speakers_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'event_days',
                'event_industry_brands',
                'event_speakers_count',
                'event_audience',
            ]);
        });
    }
};
