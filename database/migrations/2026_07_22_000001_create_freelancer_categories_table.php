<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('image', 250)->nullable()->default('def.png');
            $table->string('image_storage_type', 10)->nullable()->default('public');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->tinyInteger('position')->default(0);
            $table->integer('priority')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on('freelancer_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_categories');
    }
};
