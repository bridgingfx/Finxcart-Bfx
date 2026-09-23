<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_contract_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_contract_id')->constrained('freelancer_contracts')->cascadeOnDelete();
            $table->enum('reviewer_type', ['customer', 'seller']);
            $table->unsignedBigInteger('reviewer_id');
            $table->enum('reviewee_type', ['customer', 'seller']);
            $table->unsignedBigInteger('reviewee_id');
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->timestamps();

            $table->unique(['freelancer_contract_id', 'reviewer_type'], 'freelancer_contract_review_reviewer_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_contract_reviews');
    }
};
