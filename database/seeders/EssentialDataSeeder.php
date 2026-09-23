<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\Currency;
use Illuminate\Database\Seeder;

/**
 * Seeds the minimum data the application needs to boot and render pages on a
 * fresh database built from the migration chain (i.e. when the installer's
 * bundled SQL dump is not available).
 *
 * Every insert is idempotent (firstOrCreate): it never overwrites existing
 * rows, so it is safe to run on databases that already have data.
 */
class EssentialDataSeeder extends Seeder
{
    public function run(): void
    {
        $usd = Currency::firstOrCreate(
            ['code' => 'USD'],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'exchange_rate' => 1,
                'status' => 1,
            ]
        );

        $settings = [
            'company_name' => 'FinXCart',
            'system_default_currency' => (string) $usd->id,
            'currency_model' => 'multi_currency',
            'business_mode' => 'multi_vendor',
            'language' => json_encode([['code' => 'en', 'default' => true, 'direction' => 'ltr', 'status' => 1]]),
            'decimal_point_settings' => '2',
            // Mirrors InstallController defaults so fresh installs render without null-config crashes
            'temporary_close' => json_encode(['status' => 0]),
            'vacation_add' => json_encode(['status' => 0, 'vacation_start_date' => null, 'vacation_end_date' => null, 'vacation_note' => null]),
            'cookie_setting' => json_encode(['status' => 0, 'cookie_text' => null]),
            'guest_checkout' => 0,
            'minimum_order_amount' => 0,
            'minimum_order_amount_by_seller' => 0,
            'minimum_order_amount_status' => 0,
            'product_brand' => 1,
            'digital_product' => 1,
            'ref_earning_status' => 0,
            'ref_earning_exchange_rate' => 0,
            'add_funds_to_wallet' => 0,
            'minimum_add_fund_amount' => 0,
            'free_delivery_status' => 0,
            'free_delivery_responsibility' => 'admin',
            'free_delivery_over_amount' => 0,
            'free_delivery_over_amount_seller' => 0,
            'admin_login_url' => 'admin',
            'employee_login_url' => 'employee',
            'offline_payment' => json_encode(['status' => 0]),
            'whatsapp' => json_encode(['status' => 1, 'phone' => '00000000000']),
            'delivery_boy_expected_delivery_date_message' => json_encode(['status' => 0, 'message' => '']),
            'order_canceled' => json_encode(['status' => 0, 'message' => '']),
            'apple_login' => json_encode([['login_medium' => 'apple', 'client_id' => '', 'client_secret' => '', 'status' => 0, 'team_id' => '', 'key_id' => '', 'service_file' => '', 'redirect_url' => '']]),
        ];

        foreach ($settings as $type => $value) {
            BusinessSetting::firstOrCreate(['type' => $type], ['value' => $value]);
        }
        // Login setup defaults (mirror the 14.8 updater canonical values)
        DB::table('login_setups')->updateOrInsert(
            ['key' => 'login_options'],
            ['value' => json_encode(['manual_login' => 1, 'otp_login' => 0, 'social_login' => 1])]
        );
        DB::table('login_setups')->updateOrInsert(
            ['key' => 'social_media_for_login'],
            ['value' => json_encode(['google' => 0, 'facebook' => 0, 'apple' => 0])]
        );
    }
}
