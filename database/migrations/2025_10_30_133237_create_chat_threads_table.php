<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chat_threads', function (Blueprint $table) {
            $table->id();
            // For logged-in customers
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            // For guest users
            $table->string('session_id')->nullable()->index();
            // The admin assigned to this chat
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('chat_threads'); }
};
