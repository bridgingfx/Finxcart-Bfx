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
        // sellers.email was globally unique across ALL seller_type values, so a
        // Vendor (seller_type: NULL) and a Freelancer (seller_type: 'freelancer')
        // could never share an email even though they're conceptually separate
        // account types. Replace with a composite unique so the same email can be
        // used once per seller_type.
        //
        // Note: MySQL treats NULL as distinct from NULL in a unique index, so this
        // composite index alone does not block two NULL-seller_type (Vendor) rows
        // from sharing an email — that's enforced at the application/validation
        // layer instead (see VendorAddRequest), matching MySQL's own NULL semantics
        // rather than fighting them.
        if (Schema::hasTable('sellers')) {
            $this->dropIndexIfExists('sellers', 'sellers_email_unique');

            Schema::table('sellers', function (Blueprint $table) {
                $table->unique(['email', 'seller_type'], 'sellers_email_seller_type_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sellers')) {
            $this->dropIndexIfExists('sellers', 'sellers_email_seller_type_unique');

            Schema::table('sellers', function (Blueprint $table) {
                $table->unique('email', 'sellers_email_unique');
            });
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("DROP INDEX IF EXISTS \"{$indexName}\"");
            return;
        }
        DB::statement("ALTER TABLE `{$table}` DROP INDEX IF EXISTS `{$indexName}`");
    }
};
