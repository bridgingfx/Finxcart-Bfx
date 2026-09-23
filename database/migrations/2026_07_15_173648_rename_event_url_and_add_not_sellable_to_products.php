<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('event_url', 'external_url');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('not_sellable')->default(false)->after('external_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('not_sellable');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('external_url', 'event_url');
        });
    }
};
