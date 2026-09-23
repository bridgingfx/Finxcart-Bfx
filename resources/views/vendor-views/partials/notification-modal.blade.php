<div class="modal-header border-0 pb-0 d-flex justify-content-end">
    <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close"><i
            class="tio-clear"></i></button>
</div>

<div class="modal-body px-4 px-sm-5 text-center">
    
    {{-- 1. IMAGE ICON LOGIC --}}
    <div class="mb-3 text-center">
        @if(isset($data->link) || isset($data['link']))
            {{-- Use a Warning/Alert icon for Plan Expiry --}}
            <img width="75" src="{{dynamicAsset(path: 'public/assets/back-end/img/warning-2.png')}}" alt="">
        @else
            {{-- Keep the 'shift' icon for standard System notifications (like Theme Change) --}}
            <img width="75" src="{{dynamicAsset(path: 'public/assets/back-end/img/shift.png')}}" alt="">
        @endif
    </div>

    {{-- 2. DYNAMIC TITLE --}}
    {{-- Fallback logic: Check object ($data->title) or array ($data['title']) --}}
    <h3>{{ $data->title ?? $data['title'] }}</h3>

    {{-- 3. TIME --}}
    <p class="text-muted">
        {{ translate('at') }} 
        {{ \Carbon\Carbon::parse($data->created_at ?? $data['created_at'])->diffForHumans() }}
    </p>

    {{-- 4. DYNAMIC MESSAGE BODY --}}
    <p class="p-3">
        {{-- 
           If it's your custom notification, it has a 'message' field.
           If it's the old system notification, it has a 'description' field.
           If it's the hardcoded Theme Update, we detect that by the title.
        --}}
        @if(isset($data->message) || isset($data['message']))
            {{ $data->message ?? $data['message'] }}
        @elseif(isset($data->description) || isset($data['description']))
            {{ $data->description ?? $data['description'] }}
        @else
            {{-- Fallback for the Legacy Theme Update Message if no description exists in DB --}}
            {{ translate('hello') }} {{ translate('sir') }}/{{ translate('mam') }}
            {{ translate('we_have_updated_our_website_theme') }}! {{ translate('please_take_a_moment_to_review_the_changes_in_your_shop.') }}
        @endif
    </p>

    {{-- 5. BUTTONS --}}
    <div class="d-flex flex-column gap-2 justify-content-center align-items-center" id="notify_all_the_sellers_area">
        
        {{-- A. RENEW BUTTON (For Plan Expiry) --}}
        @if(isset($data->link) && !empty($data->link))
            <a class="fs-16 btn btn--primary px-sm-5 w-fit-content" href="{{ $data->link }}">
                {{ translate('Renew_Plan') }} / {{ translate('View_Details') }}
            </a>
        
        @elseif(isset($data['link']) && !empty($data['link']))
            <a class="fs-16 btn btn--primary px-sm-5 w-fit-content" href="{{ $data['link'] }}">
                {{ translate('Renew_Plan') }} / {{ translate('View_Details') }}
            </a>

        {{-- B. VISIT STORE BUTTON (Legacy/Default) --}}
        @elseif(isset($shop))
            <a class="fs-16 btn btn--primary px-sm-5 w-fit-content" target="_blank" href="{{ route('shopView',['id'=>$shop->id]) }}">
                {{ translate('visit_store') }}
            </a>
        @endif

    </div>
</div>