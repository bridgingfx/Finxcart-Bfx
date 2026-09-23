<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->string('company_registered_country')->nullable()->after('company_license');
            $table->enum('seller_type', ['company', 'individual'])->nullable()->after('seller_id');
            $table->string('personal_id_document')->nullable()->after('company_registered_country');
            $table->text('sell_description')->nullable()->after('personal_id_document');
            $table->string('company_website')->nullable()->change();
            $table->string('company_no')->nullable()->change();
            $table->string('company_license')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_verifications', function (Blueprint $table) {
            $table->string('company_website')->nullable(false)->change();
            $table->string('company_no')->nullable(false)->change();
            $table->string('company_license')->nullable(false)->change();
            $table->dropColumn(['seller_type', 'personal_id_document', 'sell_description', 'company_registered_country']);
        });
    }
};
