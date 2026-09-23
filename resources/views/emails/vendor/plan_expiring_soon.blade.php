<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $isTrial ? translate('Trial Ending Soon') : translate('Plan Expiring Soon') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- FIX: Use asset() instead of dynamicAsset() to avoid Console Error --}}
    <link rel="stylesheet" href="{{ asset('public/assets/back-end/css/email-basic.css') }}">
    
    <style>
        /* Fallback styles if the CSS file doesn't load in email client */
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            font-family: sans-serif;
        }
        .btn--primary {
            background-color: #0177cd;
            color: #fff !important;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .text-center { text-align: center; }
    </style>
</head>
<body style="background-color: #f3f3f3; padding: 20px;">

<?php
    // Safer way to get config in Console mode
    $companyName = \App\Models\BusinessSetting::where('type', 'company_name')->first()->value ?? 'FinXCart';
    
    // Try to get logo, fallback to simple text if helper fails in console
    $companyLogo = \App\Models\BusinessSetting::where('type', 'company_web_logo')->first()->value ?? '';
    $logoUrl = asset('storage/app/public/company/' . $companyLogo);
?>

<div class="d-flex justify-content-center align-items-center m-auto">
    <div class="card">
        <div class="m-auto bg-white pt-40px pb-40px text-center">
            <div class="d-block">
                <div class="d-flex justify-content-center align-items-center gap-1">
                    {{-- Attempt to show logo, fallback to Name --}}
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="width-auto h-50px" style="max-height: 50px; width: auto;">
                    <br/>
                    <h3 style="margin-top: 10px;">{{ $companyName }}</h3>
                </div>
            </div>
        </div>

        <div class="card-header mb-3 text-center" style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; font-size: 18px; font-weight: bold;">
            @if($isTrial)
                {{ translate('Action_Required:_Your_Free_Trial_is_Ending') }}
            @else
                {{ translate('Reminder:_Your_Seller_Plan_is_Expiring') }}
            @endif
        </div>

        <div class="card-body">

            <p style="text-align: left; margin-bottom: 20px;">
                Hello {{ $sellerName ?? translate('Seller') }},
            </p>

            <p style="text-align: left; margin-bottom: 10px;">
                @if($isTrial)
                    {{ translate('We_hope_you_are_enjoying_your_free_trial_of') }} <strong>{{ $planName ?? translate('N/A') }}</strong>. 
                    {{ translate('This_trial_period_will_end_in') }} <span style="color:red; font-weight:bold;">{{ $daysLeft }} {{ translate('days') }}</span>.
                @else
                    {{ translate('This_is_a_reminder_that_your_subscription_for') }} <strong>{{ $planName ?? translate('N/A') }}</strong> 
                    {{ translate('will_expire_in') }} <span style="color:red; font-weight:bold;">{{ $daysLeft }} {{ translate('days') }}</span>.
                @endif
            </p>

            <div style="text-align: left; list-style-type: none; padding-left: 0; margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px;">
                <div style="margin-bottom: 5px;"><strong>{{ translate('Plan_Name') }}:</strong> {{ $planName ?? translate('N/A') }}</div>
                <div style="margin-bottom: 5px;"><strong>{{ translate('Expiration_Date') }}:</strong> {{ $endDate ?? translate('N/A') }}</div>
                <div><strong>{{ translate('Time_Remaining') }}:</strong> {{ $daysLeft }} {{ translate('Days') }}</div>
            </div>

            <p style="text-align: left; margin-bottom: 25px;">
                @if($isTrial)
                    {{ translate('To_keep_your_features_active_and_avoid_losing_access_to_your_product_listings_please_upgrade_to_a_paid_plan_before_the_trial_ends') }}.
                @else
                    {{ translate('To_avoid_service_interruption_and_deactivation_of_your_listings_please_renew_your_subscription_today') }}.
                @endif
            </p>

            <div class="text-center">
                <a class="btn btn--primary" href="{{ $paymentLink ?? route('vendor.tier.index') }}">
                    @if($isTrial)
                        {{ translate('Upgrade_My_Plan') }}
                    @else
                        {{ translate('Renew_Subscription') }}
                    @endif
                </a>
            </div>
            
            <p style="margin-top: 20px; font-size: 12px; color: #777; text-align: center;">
                {{ translate('If_you_have_already_renewed_please_ignore_this_message') }}.
            </p>
        </div>
    </div>
</div>
</body>
</html>