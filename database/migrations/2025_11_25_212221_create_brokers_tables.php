<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Brokers Table
        Schema::create('brokers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            
            // Affiliate / Referral Link (Crucial)
            $table->string('affiliate_url'); 
            
            // Broker Details
            $table->string('regulations')->nullable(); // e.g. FCA, ASIC
            $table->string('min_deposit')->nullable();
            $table->string('max_leverage')->nullable();
            $table->string('platforms')->nullable();
            $table->text('description')->nullable();

            // RATING SYSTEM 1: WIKI FX STYLE (System Calculated)
            // Scores from 0 to 10
            $table->decimal('score_license', 3, 1)->default(0); 
            $table->decimal('score_software', 3, 1)->default(0); 
            $table->decimal('score_stability', 3, 1)->default(0); 
            $table->decimal('system_rating', 3, 2)->default(0.00); // Final Weighted Score

            // RATING SYSTEM 2: USER REVIEWS
            $table->decimal('user_rating', 3, 2)->default(0.00);
            $table->integer('review_count')->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Broker Reviews Table
        Schema::create('broker_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable(); // Can be nullable if guest reviews allowed, else constrained
            $table->string('reviewer_name')->nullable(); // Fallback if no user_id
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false); // Admin Approval Required
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('broker_reviews');
        Schema::dropIfExists('brokers');
    }
};