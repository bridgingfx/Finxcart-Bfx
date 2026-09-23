<?php

namespace App\Enums\ViewPaths\Vendor;

enum Auth
{
    const VENDOR_LOGIN = [
        URI => 'login',
        VIEW => 'vendor-views.auth.login',
    ];

    const VENDOR_LOGOUT = [
        URI => 'vendor.auth.login',
        VIEW => 'vendor-views.auth.login'
    ];
    const RECAPTURE = [
        URI => 'recaptcha',
    ];
    const VENDOR_REGISTRATION = [
        URI => 'index',
        VIEW => 'seller_registration'
    ];

    const VENDOR_VERIFICATION_FORM = [
        URI => 'index',
        VIEW => 'web-views.seller-view.auth.vendor-verification',
    ];

    const VENDOR_VERIFICATION_UNDER_REVIEW = [
        URI => 'under-review',
        VIEW => 'web-views.seller-view.auth.vendor-verification-under-review',
    ];


}
