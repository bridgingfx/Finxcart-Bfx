<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_profile_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_profile_id');
            $table->foreign('freelancer_profile_id', 'freelancer_profile_verif_logs_profile_fk')
                ->references('id')->on('freelancer_profiles')->restrictOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('action', 80);
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50)->nullable();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['freelancer_profile_id', 'action'], 'freelancer_profile_verification_logs_profile_action_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_profile_verification_logs');
    }
};
