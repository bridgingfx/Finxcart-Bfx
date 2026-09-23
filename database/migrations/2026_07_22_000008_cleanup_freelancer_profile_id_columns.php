<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('freelancer_services', 'freelancer_profile_id')) {
            Schema::table('freelancer_services', function (Blueprint $table) {
                $table->dropConstrainedForeignId('freelancer_profile_id');
            });
        }

        if (Schema::hasColumn('freelancer_portfolio_items', 'freelancer_profile_id')) {
            Schema::table('freelancer_portfolio_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('freelancer_profile_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('freelancer_services', function (Blueprint $table) {
            $table->foreignId('freelancer_profile_id')->nullable()->after('seller_id')->constrained('freelancer_profiles')->cascadeOnDelete();
        });

        Schema::table('freelancer_portfolio_items', function (Blueprint $table) {
            $table->foreignId('freelancer_profile_id')->nullable()->after('seller_id')->constrained('freelancer_profiles')->cascadeOnDelete();
        });
    }
};
