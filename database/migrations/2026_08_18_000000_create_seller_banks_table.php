<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('holder_name');
            $table->string('account_no');
            $table->string('branch', 100)->nullable();
            $table->string('swift_code', 11)->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_banks');
    }
};
