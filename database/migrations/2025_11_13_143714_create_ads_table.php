<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_ads_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_group_id')->nullable()->constrained('ad_groups')->nullOnDelete();
            $table->string('name');
            $table->enum('type', ['image', 'code', 'text'])->default('image');
            $table->text('content')->nullable(); // For code/text ads
            $table->string('image_path')->nullable(); // For image ads
            $table->string('destination_url')->nullable(); // Click-through URL
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
