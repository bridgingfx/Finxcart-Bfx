<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('swift_code', 11)->nullable()->after('account_no');
            $table->string('iban', 34)->nullable()->after('swift_code');
            $table->string('account_type', 20)->nullable()->after('iban');
            $table->string('bank_country', 60)->nullable()->after('account_type');
            $table->text('bank_address')->nullable()->after('bank_country');
            $table->string('currency_preference', 10)->nullable()->after('bank_address');
            $table->string('branch_code', 20)->nullable()->after('branch');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn([
                'swift_code', 'iban', 'account_type',
                'bank_country', 'bank_address', 'currency_preference', 'branch_code',
            ]);
        });
    }
};
