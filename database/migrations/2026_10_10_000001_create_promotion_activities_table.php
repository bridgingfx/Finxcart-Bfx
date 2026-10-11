<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Catalog of promotional activities vendors can spend promo credits on.
     * Credit costs and durations follow the FinXCart monetization plan
     * (2026-10-10): credits buy placement/delivery of a promotion, never
     * guaranteed views, inquiries or sales.
     */
    public function up(): void
    {
        if (Schema::hasTable('promotion_activities')) {
            return;
        }

        Schema::create('promotion_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('credit_cost')->comment('Credits charged per activation');
            $table->unsignedInteger('duration_days')->nullable()->comment('NULL for one-shot placements (edition/post)');
            $table->string('duration_label', 60)->comment('e.g. "3 days", "1 edition", "1 post"');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_activities');
    }
};
