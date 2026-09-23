<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_thread_id')->constrained()->onDelete('cascade');
            // Who sent it? 'customer' or 'admin'
            $table->enum('sender_type', ['customer', 'admin']);
            $table->text('body');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('chat_messages'); }
};
