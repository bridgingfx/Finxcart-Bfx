<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $exists = \DB::table('email_templates')
            ->where('template_name', 'order-status-update')
            ->where('user_type', 'customer')
            ->exists();

        if (!$exists) {
            \DB::table('email_templates')->insert([
                'template_name'            => 'order-status-update',
                'user_type'                => 'customer',
                'template_design_name'     => 'order-status-update',
                'title'                    => 'Your Order #{orderId} Status Has Been Updated',
                'body'                     => '<p><b>Hi {userName},</b></p><p>Your order <b>#{orderId}</b> status has been updated to: <b>{message}</b>.</p><p>Click the button below to track your order.</p>',
                'hide_field'               => json_encode(['product_information', 'banner_image']),
                'button_content_status'    => 0,
                'product_information_status' => 0,
                'order_information_status' => 0,
                'status'                   => 1,
                'footer_text'              => 'Please contact us for any queries, we are always happy to help.',
                'copyright_text'           => 'Copyright ' . date('Y') . '. All right reserved.',
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);
        }
    }

    public function down(): void
    {
        \DB::table('email_templates')
            ->where('template_name', 'order-status-update')
            ->where('user_type', 'customer')
            ->delete();
    }
};
