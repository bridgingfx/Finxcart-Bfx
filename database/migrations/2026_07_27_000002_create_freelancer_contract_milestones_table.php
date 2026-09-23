<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_contract_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_contract_id')->constrained('freelancer_contracts')->cascadeOnDelete();
            $table->string('title', 150);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'submitted', 'approved', 'completed'])->default('pending')->index();
            $table->integer('position')->default(0);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_contract_milestones');
    }
};
