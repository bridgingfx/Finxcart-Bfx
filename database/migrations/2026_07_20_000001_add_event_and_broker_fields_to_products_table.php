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
            $table->string('book_tickets_url', 500)->nullable()->after('external_url');
            $table->string('view_floorplan_url', 500)->nullable()->after('book_tickets_url');
            $table->string('sponsor_exhibit_url', 500)->nullable()->after('view_floorplan_url');
            $table->string('open_account_url', 500)->nullable()->after('sponsor_exhibit_url');
            $table->string('contact_broker_url', 500)->nullable()->after('open_account_url');
            $table->string('brochure', 191)->nullable()->after('contact_broker_url');
            $table->string('brochure_storage_type', 10)->nullable()->default('public')->after('brochure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'book_tickets_url',
                'view_floorplan_url',
                'sponsor_exhibit_url',
                'open_account_url',
                'contact_broker_url',
                'brochure',
                'brochure_storage_type',
            ]);
        });
    }
};
