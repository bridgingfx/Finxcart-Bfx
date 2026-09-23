<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->unsignedBigInteger('order_detail_id');
            $table->string('type', 20);
            $table->string('status', 20)->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index('shipment_id');
            $table->unique('order_detail_id');

            $table->foreign('shipment_id')->references('id')->on('shipments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
