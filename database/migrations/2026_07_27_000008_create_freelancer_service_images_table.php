<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_service_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_service_id')->constrained('freelancer_services')->cascadeOnDelete();
            $table->string('image', 250);
            $table->string('image_storage_type', 10)->default('public');
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_service_images');
    }
};
