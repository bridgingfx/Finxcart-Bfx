<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('subject_type', 40);
            $table->unsignedBigInteger('subject_id');
            $table->string('action', 60);
            $table->json('before_state')->nullable();
            $table->json('after_state')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['subject_type', 'subject_id']);
            $table->index(['actor_type', 'actor_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_audit_logs');
    }
};
