<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_profile_id')->constrained('freelancer_profiles')->cascadeOnDelete();
            $table->foreignId('freelancer_category_id')->constrained('freelancer_categories')->restrictOnDelete();
            $table->foreignId('freelancer_specialization_id')->constrained('freelancer_specializations')->restrictOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedSmallInteger('delivery_time_days')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_services');
    }
};
