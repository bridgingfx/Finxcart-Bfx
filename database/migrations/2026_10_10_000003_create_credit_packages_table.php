<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Purchasable promotional-credit bundles (10/$20, 30/$50, 75/$100,
     * 200/$250 per the FinXCart monetization plan 2026-10-10).
     */
    public function up(): void
    {
        if (Schema::hasTable('credit_packages')) {
            return;
        }

        Schema::create('credit_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->unsignedInteger('credits');
            $table->decimal('price_usd', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_packages');
    }
};
