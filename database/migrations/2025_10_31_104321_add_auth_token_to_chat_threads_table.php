<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('chat_threads', function (Blueprint $table) {
            $table->string('auth_token', 64)->nullable()->after('session_id');
            $table->index('auth_token');
        });
    }

    public function down()
    {
        Schema::table('chat_threads', function (Blueprint $table) {
            $table->dropColumn('auth_token');
        });
    }
};