<div>
    <h3 class="mb-4 view-mail-title">
        {{$title}}
    </h3>
    <div class="view-mail-body">
        {!! $body !!}
    </div>
    <div class="mt-4 text-center">
        <a href="{{ route('track-order.index') }}" target="_blank"
           class="btn btn-primary view-button-content view-button-link">
            {{ translate('track_Order') }}
        </a>
    </div>
    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>
