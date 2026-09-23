@php
    $companyName = getWebConfig(name: 'company_name');
    $companyLogo = getWebConfig(name: 'company_web_logo');
    $companyLogoUrl = is_array($companyLogo) && ($companyLogo['status'] ?? null) == 200 ? $companyLogo['path'] : null;
    $copyrightText = getWebConfig(name: 'copyright_text') ?? ('© ' . date('Y') . ' ' . $companyName);
    $template = ['pages' => [], 'social_media' => []];
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $subject }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('email-templates.partials.style')
</head>
<body>
<div class="main-table">
    <div class="main-table-inner mb-4">
        <div class="d-flex justify-content-center pt-3">
            <img class="mb-4 w-100px h-auto" id="view-mail-logo" src="{{ $companyLogoUrl ?? getStorageImages(path: $companyLogo, type: 'backend-logo') }}" alt="{{ $companyName }}">
        </div>
        <div class="bg-white p-3">
            <h3 class="mb-3">{{ $greeting }}</h3>

            @foreach($introLines as $line)
                <p>{{ $line }}</p>
            @endforeach

            @if($actionText && $actionUrl)
                <div class="mt-2 mb-4">
                    <div class="d-flex justify-content-center mb-4">
                        <a href="{{ $actionUrl }}" target="_blank" class="btn btn-primary m-auto">{{ $actionText }}</a>
                    </div>
                </div>
            @endif

            @foreach($outroLines as $line)
                <p>{{ $line }}</p>
            @endforeach

            <hr>
            <p>{!! $salutation !!}</p>
        </div>
    </div>
    <div class="bg-white rounded-10 px-2 py-4">
        <div class="d-flex justify-content-center mb-4">
            <img width="76" class="mx-auto" id="view-mail-logo" src="{{ $companyLogoUrl ?? getStorageImages(path: $companyLogo, type: 'backend-logo') }}" alt="{{ $companyName }}">
        </div>
    </div>
    @include('admin-views.business-settings.email-template.partials-design.footer-design-without-logo')
</div>
</body>
</html>
