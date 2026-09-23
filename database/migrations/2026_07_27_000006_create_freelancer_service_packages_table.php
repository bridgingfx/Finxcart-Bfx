<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_service_id')->constrained('freelancer_services')->cascadeOnDelete();
            $table->enum('tier', ['basic', 'standard', 'premium']);
            $table->boolean('is_enabled')->default(false);
            $table->string('title', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedSmallInteger('delivery_time_days')->nullable();
            $table->unsignedSmallInteger('revisions')->nullable();
            $table->timestamps();

            $table->unique(['freelancer_service_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_service_packages');
    }
};
