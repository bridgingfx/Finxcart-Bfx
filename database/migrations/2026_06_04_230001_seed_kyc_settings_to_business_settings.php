<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ([
            'kyc_method' => 'manual',
            'sumsub_app_token' => '',
            'sumsub_secret_key' => '',
            'sumsub_flow_name' => 'basic-kyc',
            'kyc_required_for_payout' => '1',
        ] as $type => $value) {
            DB::table('business_settings')->updateOrInsert(
                ['type' => $type],
                [
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('business_settings')
            ->whereIn('type', [
                'kyc_method',
                'sumsub_app_token',
                'sumsub_secret_key',
                'sumsub_flow_name',
                'kyc_required_for_payout',
            ])
            ->delete();
    }
};
