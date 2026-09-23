<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_portfolio_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_portfolio_item_id');
            $table->foreign('freelancer_portfolio_item_id', 'freelancer_portfolio_galleries_item_fk')
                ->references('id')->on('freelancer_portfolio_items')->cascadeOnDelete();
            $table->string('image', 250);
            $table->string('image_storage_type', 10)->default('public');
            $table->string('url')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_portfolio_galleries');
    }
};
