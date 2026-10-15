<div>
    <div class="text-center">
        <img width="100" class="mb-4" id="view-mail-icon"
             src="{{ $template->logo_full_url['path'] ?? getStorageImages(path: $companyLogo, type: 'backend-logo')}}"
             alt="">
        <h3 class="mb-3 view-mail-title text-capitalize">
            {{$title}}
        </h3>
    </div>
    <div class="view-mail-body">
        {!! $body !!}
    </div>
    <h3 class="mb-3 text-center">
        {{ $data['message'] ?? '123456' }}
    </h3>
    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>
