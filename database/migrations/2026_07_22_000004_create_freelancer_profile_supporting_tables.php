<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_profile_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_profile_id')->constrained('freelancer_profiles')->cascadeOnDelete();
            $table->string('skill_name', 100);
            $table->string('skill_level', 50)->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('freelancer_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_profile_id')->constrained('freelancer_profiles')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('job_title');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('freelancer_portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_profile_id')->constrained('freelancer_profiles')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image', 250)->nullable();
            $table->string('image_storage_type', 10)->nullable()->default('public');
            $table->string('project_url')->nullable();
            $table->date('completed_at')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('freelancer_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_profile_id')->constrained('freelancer_profiles')->cascadeOnDelete();
            $table->string('certificate_name');
            $table->string('issuing_organization')->nullable();
            $table->string('certificate_file', 250)->nullable();
            $table->string('certificate_file_storage_type', 10)->nullable()->default('public');
            $table->string('certificate_url')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('verification_status', 30)->default('pending');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_certificates');
        Schema::dropIfExists('freelancer_portfolio_items');
        Schema::dropIfExists('freelancer_work_experiences');
        Schema::dropIfExists('freelancer_profile_skills');
    }
};
