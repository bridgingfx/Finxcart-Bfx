<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_contract_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_contract_id')->constrained('freelancer_contracts')->cascadeOnDelete();
            $table->enum('sender_type', ['customer', 'seller']);
            $table->unsignedBigInteger('sender_id');
            $table->text('body')->nullable();
            $table->timestamps();

            $table->index(['freelancer_contract_id', 'created_at'], 'fc_messages_contract_created_idx');
        });

        Schema::create('freelancer_contract_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('freelancer_contract_messages')->cascadeOnDelete();
            $table->string('disk_path', 255);
            $table->string('original_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_contract_message_attachments');
        Schema::dropIfExists('freelancer_contract_messages');
    }
};
