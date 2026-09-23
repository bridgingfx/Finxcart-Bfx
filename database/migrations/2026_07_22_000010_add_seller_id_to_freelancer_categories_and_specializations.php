<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_categories', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('id')->constrained('sellers')->cascadeOnDelete();
        });

        Schema::table('freelancer_specializations', function (Blueprint $table) {
            $table->foreignId('seller_id')->nullable()->after('id')->constrained('sellers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
        });

        Schema::table('freelancer_specializations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seller_id');
        });
    }
};
