<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('business_settings')) {
            return;
        }

        if (BusinessSetting::where('type', 'mail_config_brevo')->exists()) {
            return;
        }

        BusinessSetting::create([
            'type' => 'mail_config_brevo',
            'value' => json_encode([
                "status" => 0,
                "name" => "",
                "host" => "",
                "driver" => "brevo",
                "port" => "587",
                "username" => "apikey",
                "email_id" => "",
                "encryption" => "TLS",
                "password" => "",
            ]),
        ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('business_settings')) {
            DB::table('business_settings')->where('type', 'mail_config_brevo')->delete();
        }
    }
};
