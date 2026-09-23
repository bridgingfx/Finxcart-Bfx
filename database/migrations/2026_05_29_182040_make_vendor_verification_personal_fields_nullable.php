<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->string('personal_name')->nullable()->change();
            $table->string('personal_email')->nullable()->change();
            $table->string('personal_contact')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->string('personal_name')->nullable(false)->change();
            $table->string('personal_email')->nullable(false)->change();
            $table->string('personal_contact')->nullable(false)->change();
        });
    }
};
