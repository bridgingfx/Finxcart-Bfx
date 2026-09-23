<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->enum('reviewable_type', ['service', 'portfolio']);
            $table->unsignedBigInteger('reviewable_id');
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->timestamps();

            $table->index(['reviewable_type', 'reviewable_id']);
            $table->unique(['customer_id', 'reviewable_type', 'reviewable_id'], 'freelancer_review_customer_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_reviews');
    }
};
