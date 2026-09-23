<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('email_templates')
            ->where('template_name', 'product-approved')
            ->where('user_type', 'vendor')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('email_templates')->insert([
            'template_name'            => 'product-approved',
            'user_type'                => 'vendor',
            'template_design_name'     => 'registration-approved',
            'title'                    => 'Your Product is Now Live on Finxcart',
            'body'                     => '<div><b>Hi {vendorName},</b></div><div><br></div><div>Great news! Your product <b>{productName}</b> has been reviewed and is now live on Finxcart. Customers can now find and purchase your product.</div><div><br></div><div>Login to your vendor dashboard to view your product listing and track performance.</div>',
            'footer_text'              => 'Please contact us for any queries, we are always happy to help.',
            'copyright_text'           => 'Copyright 2025 . All right reserved.',
            'hide_field'               => '["product_information","order_information","button_content","banner_image"]',
            'button_content_status'    => 0,
            'product_information_status' => 0,
            'order_information_status' => 0,
            'status'                   => 1,
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->where('template_name', 'product-approved')
            ->where('user_type', 'vendor')
            ->delete();
    }
};
