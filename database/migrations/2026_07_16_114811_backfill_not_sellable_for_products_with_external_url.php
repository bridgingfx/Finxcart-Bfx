<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('products')
            ->whereNotNull('external_url')
            ->where('external_url', '!=', '')
            ->where('not_sellable', false)
            ->update(['not_sellable' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way data correction; not reversible.
    }
};
