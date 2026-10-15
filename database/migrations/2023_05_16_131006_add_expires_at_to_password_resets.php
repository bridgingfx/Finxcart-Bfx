<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiresAtToPasswordResets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('password_resets', function (Blueprint $table) {
            // The base migration already defines the `id` primary key; only
            // add it when missing (e.g. databases created before it existed).
            // Note: SQLite cannot ADD a primary key column via ALTER.
            if (!Schema::hasColumn('password_resets', 'id')) {
                $table->id();
            }
            $table->timestamp('expires_at')->after('token')->nullable();
            if (!Schema::hasColumn('password_resets', 'updated_at')) {
                $table->timestamp('updated_at')->after('created_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('password_resets', function (Blueprint $table) {
            $table->dropIfExists('id');
            $table->dropIfExists('expires_at');
            $table->dropIfExists('updated_at');
        });
    }
}
