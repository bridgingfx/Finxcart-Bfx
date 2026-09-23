<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained('sellers')->cascadeOnDelete();
            $table->string('company_website');
            $table->string('company_no');
            $table->string('company_license');
            $table->string('personal_name');
            $table->string('personal_email');
            $table->string('personal_contact');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_verifications');
    }
};
