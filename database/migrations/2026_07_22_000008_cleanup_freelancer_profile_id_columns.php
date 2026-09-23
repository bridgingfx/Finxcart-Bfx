<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite cannot drop foreign keys via ALTER TABLE; dropping the
        // column itself (table rebuild) achieves the same end state.
        $drop = DB::getDriverName() === 'sqlite'
            ? fn (Blueprint $table) => $table->dropColumn('freelancer_profile_id')
            : fn (Blueprint $table) => $table->dropConstrainedForeignId('freelancer_profile_id');

        if (Schema::hasColumn('freelancer_services', 'freelancer_profile_id')) {
            Schema::table('freelancer_services', $drop);
        }

        if (Schema::hasColumn('freelancer_portfolio_items', 'freelancer_profile_id')) {
            Schema::table('freelancer_portfolio_items', $drop);
        }
    }

    public function down(): void
    {
        Schema::table('freelancer_services', function (Blueprint $table) {
            $table->foreignId('freelancer_profile_id')->nullable()->after('seller_id')->constrained('freelancer_profiles')->cascadeOnDelete();
        });

        Schema::table('freelancer_portfolio_items', function (Blueprint $table) {
            $table->foreignId('freelancer_profile_id')->nullable()->after('seller_id')->constrained('freelancer_profiles')->cascadeOnDelete();
        });
    }
};
