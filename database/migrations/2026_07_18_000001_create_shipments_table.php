<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('source_order_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('vendor_type', 20);
            $table->boolean('has_digital')->default(false);
            $table->boolean('has_physical')->default(false);
            $table->string('digital_status', 20)->nullable();
            $table->timestamp('digital_delivered_at')->nullable();
            $table->string('physical_status', 30)->nullable();
            $table->timestamp('physical_delivered_at')->nullable();
            $table->string('status', 20)->default('processing');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->unique('source_order_id');
            $table->index(['vendor_id', 'vendor_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
