<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->decimal('freelancer_rating_avg', 3, 2)->nullable()->after('seller_type');
            $table->unsignedInteger('freelancer_rating_count')->default(0)->after('freelancer_rating_avg');
            $table->unsignedInteger('freelancer_jobs_completed')->default(0)->after('freelancer_rating_count');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['freelancer_rating_avg', 'freelancer_rating_count', 'freelancer_jobs_completed']);
        });
    }
};
