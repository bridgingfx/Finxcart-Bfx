<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Your FinXCart Vendor Registration OTP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/email-basic.css') }}">
</head>
<body>
<?php
$companyPhone = getWebConfig(name: 'company_phone');
$companyEmail = getWebConfig(name: 'company_email');
$companyName = getWebConfig(name: 'company_name');
$companyLogo = getWebConfig(name: 'company_web_logo');
?>

<div class="d-flex justify-content-center align-items-center m-auto vh-100">
    <div class="card" style="max-width: 560px; width: 100%;">
        <div class="m-auto bg-white pt-40px pb-40px text-center">
            <div class="d-block">
                <div class="d-flex justify-content-center align-items-center gap-1">
                    <img src="{{ getStorageImages(path: $companyLogo, type: 'backend-logo') }}" alt="{{ $companyName }}"
                         class="width-auto h-50px">
                    {{ $companyName }}
                </div>
            </div>
        </div>
        <div class="card-header mb-3 text-center">
            Email Verification
        </div>
        <div class="card-body text-center">
            <p class="mb-2">Your OTP code is:</p>
            <h1 style="color:#1a2f5e;letter-spacing:8px;margin:0 0 12px 0;">{{ $otp }}</h1>
            <p class="mb-2">This code is valid for 5 minutes.</p>
            <p class="mb-0">Do not share this code with anyone.</p>
        </div>
    </div>
</div>
</body>
</html>
