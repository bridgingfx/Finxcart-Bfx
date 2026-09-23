<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->foreignId('freelancer_service_id')->nullable()->constrained('freelancer_services')->nullOnDelete();
            $table->text('description');
            $table->json('attachment')->nullable();
            $table->string('delivery_preference', 20)->nullable();
            $table->string('custom_delivery_text', 100)->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('status', 20)->default('pending');
            $table->decimal('quoted_price', 12, 2)->nullable();
            $table->unsignedSmallInteger('quoted_delivery_days')->nullable();
            $table->text('reply_message')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();

            $table->index(['seller_id', 'status']);
            $table->index(['customer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_quote_requests');
    }
};
