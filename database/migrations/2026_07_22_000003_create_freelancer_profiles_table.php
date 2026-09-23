<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained('sellers')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('freelancer_category_id')->constrained('freelancer_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('freelancer_specialization_id')->constrained('freelancer_specializations')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('professional_title', 150);
            $table->string('slug', 180)->unique();
            $table->string('short_description', 300);
            $table->text('description');
            $table->string('profile_image', 250)->nullable()->default('def.png');
            $table->string('profile_image_storage_type', 10)->nullable()->default('public');
            $table->string('cover_image', 250)->nullable();
            $table->string('cover_image_storage_type', 10)->nullable()->default('public');
            $table->unsignedTinyInteger('years_of_experience')->default(0);
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('languages')->nullable();
            $table->string('availability_status', 50)->default('available');
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('website')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->boolean('is_profile_complete')->default(false)->index();
            $table->string('profile_status', 30)->default('draft')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['freelancer_category_id', 'freelancer_specialization_id'], 'freelancer_profiles_cat_spec_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_profiles');
    }
};
