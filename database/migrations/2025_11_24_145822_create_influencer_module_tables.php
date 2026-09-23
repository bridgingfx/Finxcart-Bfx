<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('influencers', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('bio')->nullable();
        $table->string('profile_image')->nullable();
        $table->bigInteger('instagram_followers')->default(0);
        $table->bigInteger('youtube_subscribers')->default(0);
        $table->bigInteger('tiktok_followers')->default(0);
        $table->bigInteger('total_reach')->default(0);
        $table->decimal('avg_rating', 3, 2)->default(0.00);
        $table->integer('rating_count')->default(0);
        $table->timestamps();
    });

    Schema::create('influencer_inquiries', function (Blueprint $table) {
        $table->id();
        $table->foreignId('influencer_id')->constrained()->onDelete('cascade');
        $table->string('customer_name');
        $table->string('customer_email');
        $table->string('phone')->nullable();
        $table->text('message');
        $table->boolean('is_read')->default(false);
        $table->timestamps();
    });

    Schema::create('influencer_ratings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('influencer_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->integer('rating');
        $table->text('comment')->nullable();
        $table->timestamps();
    });
}
};
