<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ Session::get('direction') ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        try { $errorFavIcon = getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type: 'backend-logo'); }
        catch (\Throwable $e) { $errorFavIcon = null; }
    @endphp
    @if($errorFavIcon)
    <link rel="shortcut icon" href="{{ $errorFavIcon }}">
    @endif
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/front-end/css/fx-redesign.css') }}">
    <style>
        html, body { height: 100%; }
        body.fx-error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px calc(24px + env(safe-area-inset-bottom, 0px));
        }
        .fx-error-wrap { width: 100%; max-width: 560px; }
        .fx-error-code {
            font-size: 88px;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: #203F60;
            line-height: 1;
            margin: 0 0 8px;
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body class="fx fx-error-page">
    <div class="fx-error-wrap">
        @yield('message')
    </div>
</body>
</html>
