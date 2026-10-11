<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') }}">

<head>
    <meta charset="utf-8">
    <title>{{ translate('maintenance_Mode_On') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path']}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/front-end/css/fx-redesign.css') }}">
    <style>
        html, body { height: 100%; }
        body.fx-maintenance {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px calc(24px + env(safe-area-inset-bottom, 0px));
        }
        .fx-maintenance-wrap { width: 100%; max-width: 600px; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>

<body class="fx fx-maintenance">
    <div class="fx-maintenance-wrap">
        <div class="fx-empty">
            <p class="fx-empty__title" style="font-size:24px;">
                @if($maintenanceMessages['maintenance_message'])
                    {{ $maintenanceMessages['maintenance_message'] }}
                @else
                    {{ translate('we_are_working_on_something_special') }} !
                @endif
            </p>
            <p class="fx-empty__body">
                @if($maintenanceMessages['message_body'])
                    {{ $maintenanceMessages['message_body'] }}
                @else
                    {{ translate('we_apologize_for_any_inconvenience.') }}
                    {{ translate('for_immediate_assistance_please_contact_with_our_support_team') }}
                @endif
            </p>

            @if($maintenanceMessages['business_number'] == 1 || $maintenanceMessages['business_email'] == 1)
                <p class="fx-empty__body">
                    @if($maintenanceMessages['business_number'] == 1 && $maintenanceMessages['business_email'] == 1)
                        {{ translate('Any_query') }}? {{ translate('Feel_free_to_call_or_mail_Us.') }}
                    @elseif($maintenanceMessages['business_number'] == 1)
                        {{ translate('Any_query') }}? {{ translate('Feel_free_to_call_Us.') }}
                    @elseif($maintenanceMessages['business_email'] == 1)
                        {{ translate('Any_query') }}? {{ translate('Feel_free_to_mail_Us.') }}
                    @endif
                </p>
                <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:20px;">
                    @if($maintenanceMessages['business_number'] == 1)
                        <a class="fx-btn fx-btn-outline" href="tel:{{ getWebConfig(name: 'company_phone')}}">{{ getWebConfig(name: 'company_phone') }}</a>
                    @endif
                    @if($maintenanceMessages['business_email'] == 1)
                        <a class="fx-btn fx-btn-outline" href="mailto:{{ getWebConfig(name: 'company_email')}}">{{ getWebConfig(name: 'company_email') }}</a>
                    @endif
                </div>
            @endif

            <a class="fx-btn" href="javascript:location.reload()">{{ translate('try_again') }}</a>
        </div>
    </div>
</body>

</html>
