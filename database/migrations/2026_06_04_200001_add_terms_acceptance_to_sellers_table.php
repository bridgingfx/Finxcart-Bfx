<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->timestamp('terms_agreed_at')->nullable()->after('status');
            $table->string('terms_agreed_ip', 45)->nullable()->after('terms_agreed_at');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['terms_agreed_at', 'terms_agreed_ip']);
        });
    }
};
