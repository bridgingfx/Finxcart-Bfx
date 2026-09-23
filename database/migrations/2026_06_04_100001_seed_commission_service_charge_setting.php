<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('business_settings')) {
            return;
        }

        BusinessSetting::updateOrCreate(
            ['type' => 'commission_service_charge_amount'],
            ['value' => '0']
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('business_settings')) {
            DB::table('business_settings')->where('type', 'commission_service_charge_amount')->delete();
        }
    }
};
