@extends('layouts.admin.app')

@section('title', translate('freelancer_chats'))

@push('css_or_js')
    <style>
        .fl-chat-col { height: calc(100vh - 260px); min-height: 420px; overflow-y: auto; }
        .fl-chat-list-item { display: flex; gap: 10px; align-items: center; padding: 12px 14px; border-radius: 10px; cursor: pointer; text-decoration: none; color: inherit; }
        .fl-chat-list-item:hover { background: #f5f7fb; }
        .fl-chat-list-item.active { background: #eaf1ff; }
        .fl-chat-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
        .fl-chat-bubble-row { display: flex; margin-bottom: 14px; }
        .fl-chat-bubble { max-width: 70%; padding: 10px 14px; border-radius: 14px; font-size: 14px; line-height: 1.45; }
        .fl-chat-bubble .fl-chat-sender { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px; opacity: .65; }
        .fl-chat-bubble-customer { background: #f1f3f6; color: #1f2937; border-bottom-left-radius: 4px; }
        .fl-chat-bubble-seller { background: #eef6ff; color: #0b3d75; border-bottom-right-radius: 4px; margin-left: auto; }
        .fl-chat-bubble-admin { background: #1a2f5e; color: #fff; border-bottom-right-radius: 4px; margin-left: auto; }
        .fl-chat-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #9ca3af; gap: 8px; }
        .fl-chat-attach-btn { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #d8e0ea; border-radius: 8px; color: #073b74; cursor: pointer; margin-bottom: 0; }
        .fl-chat-attach-btn:hover { background: #eef5ff; border-color: #9bb8dc; }
        .fl-chat-file-preview { font-size: 12px; color: #6b7280; margin-top: 8px; }
        .fl-chat-col::-webkit-scrollbar { width: 6px; }
        .fl-chat-col::-webkit-scrollbar-thumb { background: #c9d5e5; border-radius: 999px; }
        .row.g-3 > [class*="col-"] > .card { border: 1px solid #e4ebf4; border-radius: 8px; overflow: hidden; box-shadow: 0 14px 34px rgba(15, 23, 42, 0.07); }
        .row.g-3 > [class*="col-"] > .card > .card-header { min-height: 62px; border-bottom: 1px solid #e7eef7; background: #ffffff !important; color: #132238; }
        .fl-chat-list-item { border: 1px solid transparent; margin-bottom: 6px; border-radius: 8px; transition: background .18s ease, border-color .18s ease, transform .18s ease; }
        .fl-chat-list-item:hover { background: #f8fbff; border-color: #d9e7f8; transform: translateY(-1px); }
        .fl-chat-list-item.active { background: #eef6ff; border-color: #b9d6f6; color: #0b3d75; box-shadow: inset 3px 0 0 #f58220; }
        .fl-chat-avatar { width: 42px; height: 42px; border: 2px solid #ffffff; box-shadow: 0 0 0 1px #dbe6f2; }
        .fl-chat-bubble-row { margin-bottom: 16px; }
        .fl-chat-bubble { max-width: min(72%, 560px); padding: 11px 14px; border-radius: 8px; box-shadow: 0 8px 22px rgba(15, 23, 42, .07); }
        .fl-chat-bubble-seller, .fl-chat-bubble-customer { background: #ffffff; border: 1px solid #e4ebf4; color: #1f2937; }
        .fl-chat-bubble-admin { background: #0b3d75; color: #fff; }
        #admin-freelancer-chat-messages, #admin-freelancer-support-chat-messages { background: linear-gradient(180deg, #f8fbff 0%, #f3f6fb 100%); padding: 20px; }
        .card-footer.bg-white { border-top: 1px solid #e7eef7; padding: 14px 16px; background: #fbfdff !important; }
        .fl-chat-upload-form .form-control { min-height: 44px; border-radius: 8px; border-color: #d7e1ed; resize: none; box-shadow: none; }
        .fl-chat-upload-form .form-control:focus { border-color: #0b66c3; box-shadow: 0 0 0 3px rgba(11, 102, 195, .12); }
        .fl-chat-attach-btn { background: #ffffff; border-radius: 8px; color: #0b3d75; }
        .fl-chat-send-btn, .fl-chat-upload-form .btn-primary { min-height: 44px; border-radius: 8px; background: #0b3d75 !important; border-color: #0b3d75 !important; color: #fff !important; box-shadow: 0 8px 18px rgba(11, 61, 117, .18); }
        .fl-chat-send-btn:hover, .fl-chat-upload-form .btn-primary:hover { background: #f58220 !important; border-color: #f58220 !important; color: #111827 !important; }
        .fl-chat-empty { background: rgba(255,255,255,.72); border: 1px dashed #cfdae8; border-radius: 8px; padding: 24px; }
        .fl-visible-message-time { font-size: 10.5px !important; line-height: 1.2; opacity: .82; }
        .fl-chat-bubble-admin .fl-visible-message-time,
        .admin-chat-card .outgoing_msg .fl-visible-message-time { color: rgba(255,255,255,.78) !important; }
        .fl-chat-attach-btn i,
        .admin-chat-attach-btn i { font-size: 18px; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize">{{ translate('freelancer_chats') }}</h2>
            <p class="text-muted mb-0">{{ translate('monitor_and_support_conversations_between_freelancers_and_customers') }}</p>
        </div>

        <div class="row g-3">
            <div class="col-lg-3">
                <div class="card h-100">
                    <div class="card-header bg-white fw-semibold">{{ translate('freelancers') }}</div>
                    <div class="card-body p-2 fl-chat-col">
                        @forelse($freelancers as $freelancer)
                            <a href="{{ route('admin.freelancer.chats.index', ['seller_id' => $freelancer->id]) }}"
                               class="fl-chat-list-item {{ $selectedFreelancer && $selectedFreelancer->id === $freelancer->id ? 'active' : '' }}">
                                <img class="fl-chat-avatar" src="{{ getStorageImages(path: $freelancer->image_full_url, type: 'backend-profile') }}" alt="">
                                <span class="text-truncate">{{ $freelancer->f_name }} {{ $freelancer->l_name }}</span>
                            </a>
                        @empty
                            <div class="fl-chat-empty">
                                <i class="fi fi-rr-comment" style="font-size: 28px;"></i>
                                <span>{{ translate('no_conversations_yet') }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="card h-100">
                    <div class="card-header bg-white fw-semibold">{{ translate('customers') }}</div>
                    <div class="card-body p-2 fl-chat-col">
                        @if($selectedFreelancer)
                            @forelse($allChattingUsers as $chatting)
                                @if($chatting->customer)
                                    <a href="{{ route('admin.freelancer.chats.index', ['seller_id' => $selectedFreelancer->id, 'user_id' => $chatting->user_id]) }}"
                                       class="fl-chat-list-item {{ $lastChatUser && $lastChatUser->id === $chatting->user_id ? 'active' : '' }}">
                                        <img class="fl-chat-avatar" src="{{ getStorageImages(path: $chatting->customer->image_full_url, type: 'backend-profile') }}" alt="">
                                        <span class="d-flex flex-column min-w-0">
                                            <span class="text-truncate fw-semibold">{{ $chatting->customer->f_name }} {{ $chatting->customer->l_name }}</span>
                                            <small class="text-muted text-truncate">{{ $chatting->message ?? translate('shared_files') }}</small>
                                        </span>
                                    </a>
                                @endif
                            @empty
                                <div class="fl-chat-empty">
                                    <span>{{ translate('no_data_to_show') }}</span>
                                </div>
                            @endforelse
                        @else
                            <div class="fl-chat-empty">
                                <span>{{ translate('select_a_freelancer_to_view_conversations') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    @if($selectedFreelancer && $lastChatUser)
                        <div class="card-header bg-white d-flex align-items-center gap-2">
                            <img class="fl-chat-avatar" src="{{ getStorageImages(path: $lastChatUser->image_full_url, type: 'backend-profile') }}" alt="">
                            <div>
                                <div class="fw-semibold">{{ $lastChatUser->f_name }} {{ $lastChatUser->l_name }}</div>
                                <small class="text-muted">{{ translate('with') }} {{ $selectedFreelancer->f_name }} {{ $selectedFreelancer->l_name }} | {{ translate('last_login') }} {{ $lastChatUser->updated_at?->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="card-body fl-chat-col d-flex flex-column-reverse" id="admin-freelancer-support-chat-messages">
                            @include('admin-views.freelancer.chats.messages', ['chattingMessages' => $chattingMessages])
                        </div>
                        <div class="card-footer bg-white">
                            <form action="{{ route('admin.freelancer.chats.send') }}" method="POST" enctype="multipart/form-data" class="fl-chat-upload-form" novalidate>
                                @csrf
                                <input type="hidden" name="seller_id" value="{{ $selectedFreelancer->id }}">
                                <input type="hidden" name="user_id" value="{{ $lastChatUser->id }}">
                                <div class="d-flex gap-2 align-items-end">
                                    <label class="fl-chat-attach-btn" title="{{ translate('attachment') }}">
                                        <label class="py-0 cursor-pointer fl-file-picker" title="File">
                                            <svg width="20" height="18" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5.61597 17.2917C4.66813 17.2919 3.7415 17.011 2.95335 16.4845C2.16519 15.958 1.55092 15.2096 1.18827 14.3338C0.825613 13.4581 0.730874 12.4945 0.916037 11.5649C1.1012 10.6353 1.55794 9.78158 2.22847 9.11165L9.2993 2.03999C9.41655 1.92274 9.57557 1.85687 9.74139 1.85687C9.9072 1.85687 10.0662 1.92274 10.1835 2.03999C10.3007 2.15724 10.3666 2.31626 10.3666 2.48207C10.3666 2.64788 10.3007 2.80691 10.1835 2.92415L3.11181 9.99499C2.76945 10.3208 2.49576 10.7118 2.30686 11.145C2.11796 11.5782 2.01768 12.0449 2.01193 12.5175C2.00617 12.99 2.09506 13.459 2.27334 13.8967C2.45163 14.3344 2.71572 14.7319 3.05004 15.066C3.38436 15.4 3.78216 15.6638 4.21999 15.8417C4.65783 16.0196 5.12685 16.1081 5.59941 16.102C6.07198 16.0958 6.53854 15.9951 6.9716 15.8059C7.40465 15.6166 7.79545 15.3426 8.12097 15L17.2543 5.86665C17.6728 5.43446 17.9047 4.85506 17.8999 4.25344C17.895 3.65183 17.6539 3.07623 17.2285 2.65081C16.8031 2.22539 16.2275 1.98425 15.6258 1.97942C15.0242 1.97459 14.4448 2.20645 14.0126 2.62499L6.64764 9.99499C6.45226 10.1904 6.3425 10.4554 6.3425 10.7317C6.3425 11.008 6.45226 11.2729 6.64764 11.4683C6.84301 11.6637 7.108 11.7735 7.3843 11.7735C7.66061 11.7735 7.9256 11.6637 8.12097 11.4683L12.8335 6.75499C12.8911 6.69527 12.96 6.64762 13.0363 6.61483C13.1125 6.58204 13.1945 6.56476 13.2775 6.564C13.3605 6.56324 13.4428 6.57901 13.5196 6.6104C13.5964 6.64179 13.6663 6.68817 13.725 6.74682C13.7837 6.80548 13.8301 6.87524 13.8616 6.95203C13.893 7.02883 13.9089 7.11112 13.9082 7.19411C13.9075 7.27709 13.8903 7.35911 13.8576 7.43538C13.8249 7.51165 13.7773 7.58064 13.7176 7.63832L9.0043 12.3525C8.57454 12.7824 7.99162 13.0239 7.38377 13.024C6.77591 13.0241 6.19293 12.7827 5.76305 12.3529C5.33318 11.9231 5.09164 11.3402 5.09156 10.7324C5.09148 10.1245 5.33288 9.54153 5.76264 9.11165L13.1293 1.74999C13.7935 1.08573 14.6943 0.712511 15.6336 0.712433C16.5729 0.712355 17.4738 1.08542 18.1381 1.74957C18.8023 2.41372 19.1755 3.31454 19.1756 4.25386C19.1757 5.19318 18.8026 6.09406 18.1385 6.75832L9.00514 15.8883C8.56103 16.3347 8.03283 16.6885 7.45109 16.9294C6.86934 17.1703 6.24561 17.2934 5.61597 17.2917Z" fill="#46A046"></path>
                                            </svg>
                                        </label>
                                        <input type="file" name="file[]" class="fl-chat-file-input d-none" multiple accept=".doc,.docx,.pdf,.zip,.txt">
                                    </label>
                                    <textarea name="message" rows="1" class="form-control" placeholder="{{ translate('reply_as_support') }}"></textarea>
                                    <button type="submit" class="btn btn-primary flex-shrink-0">{{ translate('send') }}</button>
                                </div>
                                <div class="fl-chat-file-preview"></div>
                            </form>
                        </div>
                    @else
                        <div class="card-body">
                            <div class="fl-chat-empty" style="height: 100%;">
                                <i class="fi fi-rr-comment" style="font-size: 32px;"></i>
                                <span>{{ translate('select_a_conversation_to_view_messages') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function () {
            const chatBox = document.getElementById('admin-freelancer-support-chat-messages');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            document.querySelectorAll('.fl-chat-file-input').forEach((input) => {
                input.addEventListener('change', function () {
                    const maxFileSize = 2 * 1024 * 1024;
                    const files = Array.from(this.files || []);
                    const invalidFile = files.find((file) => file.size > maxFileSize);
                    const preview = this.closest('form').querySelector('.fl-chat-file-preview');

                    if (invalidFile) {
                        this.value = '';
                        if (preview) preview.textContent = '';
                        const message = `"${invalidFile.name}" {{ translate('file_maximum_size') }} 2 MB`;
                        window.toastMagic ? toastMagic.warning(message) : alert(message);
                        return;
                    }

                    if (preview) {
                        preview.textContent = files.map((file) => file.name).join(', ');
                    }
                });
            });
        })();
    </script>
@endpush
