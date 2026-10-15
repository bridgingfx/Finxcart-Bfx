<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_tiers', function (Blueprint $table) {
            // Adds the new column to store the special commission rate during a trial
            $table->decimal('trial_commission_rate', 8, 2)->nullable()->after('sales_commission_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_tiers', function (Blueprint $table) {
            $table->dropColumn('trial_commission_rate');
        });
    }
};