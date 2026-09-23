@extends('layouts.freelancer.app')

@section('title',translate('chatting_Page'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ dynamicAsset('public/assets/back-end/css/owl.min.css') }}"/>
    <style>
        .fl-chat-page .chatSel .card,
        .fl-chat-page .card-chat {
            border: 1px solid #e8edf3; border-radius: 14px; box-shadow: 0 10px 30px rgba(23, 57, 94, 0.06);
        }
        .fl-chat-page .search-input-group {
            display: flex; align-items: center; gap: 8px; background: #f6f8fb; border-radius: 999px; padding: 8px 14px;
        }
        .fl-chat-page .search-input-group input {
            border: none; background: transparent; outline: none; width: 100%; font-size: 13px;
        }
        .fl-chat-page .search-icon { color: #9ca3af; }

        .fl-chat-page .chat_list {
            border-radius: 10px; margin: 2px 10px; transition: background .15s ease; cursor: pointer;
        }
        .fl-chat-page .chat_list:hover { background: #f6f8fb !important; }
        .fl-chat-page .chat_list.active,
        .fl-chat-page .chat_list.bg-soft-secondary { background: #eef2ff !important; }
        .fl-chat-page .chat_list h5 { font-size: 13.5px; font-weight: 700; color: #1f2937; }
        .fl-chat-page .chat_list .lead.small { font-size: 11px !important; color: #9ca3af; font-weight: 500; }
        .fl-chat-page .chat_list p.line--limit-1 { color: #6b7280; }
        .fl-chat-page .chat_img img { border: 2px solid #fff; box-shadow: 0 0 0 1px #e7ebf0; }

        .fl-chat-page .inbox_msg_header {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%) !important; border: none !important;
            color: #fff; border-radius: 12px !important;
        }
        .fl-chat-page .inbox_msg_header .profile-name { color: #fff !important; }
        .fl-chat-page .inbox_msg_header .fs-12 { color: rgba(255,255,255,.75) !important; }
        .fl-chat-page .inbox_msg_header .avatar-status-success { border-color: rgba(255,255,255,.6); }

        .fl-chat-page .msg_history { background: #f9fafc; border-radius: 12px; }
        .fl-chat-page .message-text-section {
            border-radius: 14px !important; padding: 10px 14px !important; font-size: 14px !important;
            line-height: 1.5; max-width: 420px; box-shadow: 0 1px 2px rgba(23, 57, 94, 0.06);
        }
        .fl-chat-page .incoming_msg .message-text-section {
            background: #fff !important; border: 1px solid #e8edf3; border-bottom-left-radius: 4px !important;
        }
        .fl-chat-page .outgoing_msg .message-text-section {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%) !important; color: #fff !important;
            border-bottom-right-radius: 4px !important;
        }

        .fl-chat-page .type_msg textarea {
            border-radius: 22px !important; border-color: #e0e5eb !important;
        }
        .fl-chat-page .type_msg textarea:focus {
            border-color: #17395e !important; box-shadow: 0 0 0 3px rgba(23, 57, 94, 0.08) !important;
        }
        .fl-chat-page .send-btn img { transition: transform .15s ease; }
        .fl-chat-page .send-btn:hover img { transform: translateX(2px); }
        .fl-chat-page .chatSel .card,
        .fl-chat-page .card-chat { border-radius: 8px; border-color: #e4ebf4; box-shadow: 0 16px 38px rgba(15, 23, 42, .08); overflow: hidden; }
        .fl-chat-page .chatSel .card { background: #ffffff; }
        .fl-chat-page .search-input-group { border-radius: 8px; border: 1px solid #dce5f0; background: #fbfdff; }
        .fl-chat-page .chat_list { border: 1px solid transparent; border-radius: 8px; margin: 4px 10px; }
        .fl-chat-page .chat_list:hover { background: #f8fbff !important; border-color: #d9e7f8; transform: translateY(-1px); }
        .fl-chat-page .chat_list.active,
        .fl-chat-page .chat_list.bg-soft-secondary { background: #eef6ff !important; border-color: #b9d6f6; box-shadow: inset 3px 0 0 #f58220; }
        .fl-chat-page .inbox_msg_header { background: #0b3d75 !important; border-bottom: 3px solid #f58220 !important; border-radius: 8px !important; padding: 16px 18px !important; }
        .fl-chat-page .msg_history { min-height: 470px; background: linear-gradient(180deg, #f8fbff 0%, #f3f6fb 100%); border-radius: 8px; padding: 20px !important; }
        .fl-chat-page .msg_history::-webkit-scrollbar { width: 6px; }
        .fl-chat-page .msg_history::-webkit-scrollbar-thumb { background: #c9d5e5; border-radius: 999px; }
        .fl-chat-page .message-text-section { border-radius: 8px !important; max-width: min(72%, 560px); box-shadow: 0 8px 22px rgba(15, 23, 42, .07); }
        .fl-chat-page .incoming_msg .message-text-section { background: #ffffff !important; border-color: #e4ebf4; }
        .fl-chat-page .outgoing_msg .message-text-section { background: #0b3d75 !important; color: #fff !important; }
        .fl-chat-page .type_msg { padding-top: 14px; }
        .fl-chat-page .input_msg_write form > .position-relative { background: #ffffff; border: 1px solid #d7e1ed; border-radius: 8px; padding: 8px; box-shadow: 0 10px 24px rgba(15, 23, 42, .05); }
        .fl-chat-page .type_msg textarea { border: 0 !important; border-radius: 8px !important; min-height: 46px; padding-top: 12px !important; }
        .fl-chat-page .type_msg textarea:focus { box-shadow: none !important; }
        .fl-chat-page .radius-right-button { border-radius: 8px !important; background: #0b3d75 !important; width: 50px; flex-shrink: 0; }
        .fl-chat-page .send-btn img { filter: brightness(0) invert(1); }
        .fl-chat-page .send-btn:hover img { transform: translateX(2px); }
        .fl-chat-page .top-3 label { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: #f8fbff; border: 1px solid #e1e9f3; }
        .fl-chat-page .top-3 label:hover { background: #eef6ff; border-color: #b9d6f6; }
        .fl-chat-page .fl-visible-message-time { font-size: 10.5px !important; line-height: 1.2; opacity: .8; }
        .fl-chat-page .outgoing_msg .fl-visible-message-time { color: rgba(255,255,255,.78) !important; }
        .fl-chat-page .incoming_msg .fl-visible-message-time { color: #7b8794 !important; }
        .fl-chat-page .fl-media-picker > svg,
        .fl-chat-page .fl-file-picker > svg,
        .fl-chat-page .fl-emoji-picker > svg { display: none; }
        .fl-chat-page .fl-media-picker i,
        .fl-chat-page .fl-file-picker i,
        .fl-chat-page .fl-emoji-picker i { color: #0b3d75; font-size: 18px; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid fl-chat-page">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset('/public/assets/back-end/img/support-ticket.png')}}" alt="">
                {{translate('chatting_list')}}
            </h2>
        </div>

        <div class="row">
            <div class="col-xl-3 col-lg-4 chatSel">
                <div class="card card-body px-0 h-100 position-relative max-h-100vh-150px">
                    <div class="inbox_people">
                        <form class="search-form mb-4 px-20" id="chat-search-form" novalidate>
                            <div class="search-input-group">
                                <i class="tio-search search-icon" aria-hidden="true"></i>
                                <input id="myInput" type="text" aria-label="Search customers..."
                                       placeholder="{{ translate('search_customers') }}...">
                            </div>
                        </form>

                        <div class="tab-content max-h-100vh-300px overflow-y-auto">
                            <div class="tab-pane fade show active" id="customers" role="tabpanel">
                                <div class="inbox_chat d-flex flex-column">
                                    @if(isset($allChattingUsers) && count($allChattingUsers) > 0)
                                        @foreach($allChattingUsers as $key => $chatting)
                                            @if($chatting->user_id && $chatting->customer)
                                                <div class="list_filter">
                                                    <div
                                                        class="chat_list p-3 d-flex gap-2 {{ $key == 0 ? 'bg-soft-secondary' : '' }} get-ajax-message-view {{ $chatting->user_id == $lastChatUser->id ? 'active' : '' }}"
                                                        data-user-id="{{ $chatting->user_id }}">
                                                        <div class="chat_people media gap-10 w-100" id="chat_people">
                                                            <div class="chat_img avatar avatar-sm avatar-circle">
                                                                <img
                                                                    src="{{ getStorageImages(path:$chatting->customer->image_full_url,type: 'backend-profile') }}"
                                                                    id="{{$chatting->user_id}}"
                                                                    class="avatar-img avatar-circle" alt="">
                                                                <span
                                                                    class="avatar-status avatar-sm-status avatar-status-success"></span>
                                                            </div>
                                                            <div class="chat_ib media-body">
                                                                <h5 class="mb-1 seller {{ $key != 0 ?'font-weight-normal' :'' }}"
                                                                    id="{{ $chatting->user_id }}"
                                                                    data-name="{{ $chatting->customer->f_name.' '.$chatting->customer->l_name }}"
                                                                    data-phone="{{ $chatting->customer->phone }}">
                                                                    {{ $chatting->customer->f_name .' '. $chatting->customer->l_name }}

                                                                    <span
                                                                        class="lead small float-end">{{ $chatting->created_at->diffForHumans() }}</span>
                                                                </h5>
                                                                <span
                                                                    class="mt-2 font-weight-normal text-muted d-block"
                                                                    id="{{ $chatting->user_id }}"
                                                                    data-name="{{ $chatting->customer->f_name .' '. $chatting->customer->l_name}}"
                                                                    data-phone="{{ $chatting->customer->phone }}">{{ $chatting->customer->phone }}
                                                                </span>
                                                                <div
                                                                    class="d-flex gap-2 justify-content-between align-items-center">
                                                                    <p class="fs-12 line--limit-1 mb-0">{{ $chatting?->message ?? 'Shared files' }}</p>
                                                                    @if(array_key_exists($chatting->user_id, $countUnreadMessages))
                                                                        <span
                                                                            id="count-unread-messages-{{ $chatting->user_id }}"
                                                                            class="bg-c1 text-white fs-12 lh-1 rounded-circle aspect-1 min-w-20px p-1 d-flex justify-content-center align-items-center flex-shrink-0">
                                                                            {{ $countUnreadMessages[$chatting->user_id] }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(!$chatting->seen_by_seller && !($key == 0))
                                                            <div
                                                                class="message-status bg-danger notify-alert-{{ $chatting->user_id }}"></div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        <div class="justify-content-center align-items-center h-100 min-h-300 d-none empty-state-for-chatting-msg">
                                            <div class="d-flex flex-column align-items-center gap-3">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/empty-state-icon/no-customer-found.svg') }}"
                                                     alt="">
                                                <p>{{ translate('No_Customer_Found') }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="d-flex justify-content-center align-items-center h-100 min-h-300">
                                            <div class="d-flex flex-column align-items-center gap-3">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/empty-state-icon/no-customer-found.svg') }}"
                                                     alt="">
                                                <p>{{ translate('No_Customer_Found') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section class="col-xl-9 col-lg-8 mt-4 mt-lg-0">
                <div class="card card-body card-chat justify-content-center Chat" id="">
                    @if(isset($lastChatUser))
                        <div
                            class="inbox_msg_header d-flex flex-wrap gap-3 justify-content-between align-items-center border px-3 py-2 rounded mb-4">
                            <div class="media align-items-center gap-3">
                                <div class="avatar avatar-sm avatar-circle border">
                                    <img class="avatar-img user-avatar-image" id="profile_image"
                                         src="{{  getStorageImages(path: $lastChatUser->image_full_url,type: 'backend-profile')}}"
                                         alt="Image Description">
                                    <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                                <div class="media-body">
                                    <h5 class="profile-name mb-1"
                                        id="profile_name">{{ $lastChatUser['f_name'].' '.$lastChatUser['l_name'] }}</h5>
                                    <span class="fs-12"
                                          id="profile_phone">{{ $lastChatUser['country_code'] }} {{ $lastChatUser['phone'] }}</span>
                                    <span class="fs-12 d-block" id="profile_last_active">{{ translate('last_login') }} {{ $lastChatUser->updated_at?->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="card-body p-3 overflow-y-auto height-220 flex-grow-1 msg_history d-flex flex-column-reverse"
                            id="chatting-messages-section">
                            @include('freelancer-views.chatting.messages', ['lastChatUser'=>$lastChatUser, 'chattingMessages'=>$chattingMessages])
                        </div>

                        <div class="px-3 py-1 small text-muted d-none" id="chat-typing-indicator" data-suffix="{{ translate('is_typing') }}..."></div>

                        <div class="type_msg">
                            <div class="input_msg_write">
                                <form class="mt-4 chatting-messages-ajax-form" enctype="multipart/form-data" novalidate>
                                    @csrf
                                    <input type="hidden" id="current-user-hidden-id" value="{{ $lastChatUser->id }}"
                                           name="user_id">
                                    <div class="position-relative d-flex">
                                        <div class="d-flex align-items-center m-0 position-absolute top-3 px-3 gap-2">
                                            <label class="py-0 cursor-pointer fl-file-picker" title="{{ translate('file') }}"><i class="tio-file-add-outlined"></i>
                                                <svg width="20" height="18" viewBox="0 0 20 18" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M5.61597 17.2917C4.66813 17.2919 3.7415 17.011 2.95335 16.4845C2.16519 15.958 1.55092 15.2096 1.18827 14.3338C0.825613 13.4581 0.730874 12.4945 0.916037 11.5649C1.1012 10.6353 1.55794 9.78158 2.22847 9.11165L9.2993 2.03999C9.41655 1.92274 9.57557 1.85687 9.74139 1.85687C9.9072 1.85687 10.0662 1.92274 10.1835 2.03999C10.3007 2.15724 10.3666 2.31626 10.3666 2.48207C10.3666 2.64788 10.3007 2.80691 10.1835 2.92415L3.11181 9.99499C2.76945 10.3208 2.49576 10.7118 2.30686 11.145C2.11796 11.5782 2.01768 12.0449 2.01193 12.5175C2.00617 12.99 2.09506 13.459 2.27334 13.8967C2.45163 14.3344 2.71572 14.7319 3.05004 15.066C3.38436 15.4 3.78216 15.6638 4.21999 15.8417C4.65783 16.0196 5.12685 16.1081 5.59941 16.102C6.07198 16.0958 6.53854 15.9951 6.9716 15.8059C7.40465 15.6166 7.79545 15.3426 8.12097 15L17.2543 5.86665C17.6728 5.43446 17.9047 4.85506 17.8999 4.25344C17.895 3.65183 17.6539 3.07623 17.2285 2.65081C16.8031 2.22539 16.2275 1.98425 15.6258 1.97942C15.0242 1.97459 14.4448 2.20645 14.0126 2.62499L6.64764 9.99499C6.45226 10.1904 6.3425 10.4554 6.3425 10.7317C6.3425 11.008 6.45226 11.2729 6.64764 11.4683C6.84301 11.6637 7.108 11.7735 7.3843 11.7735C7.66061 11.7735 7.9256 11.6637 8.12097 11.4683L12.8335 6.75499C12.8911 6.69527 12.96 6.64762 13.0363 6.61483C13.1125 6.58204 13.1945 6.56476 13.2775 6.564C13.3605 6.56324 13.4428 6.57901 13.5196 6.6104C13.5964 6.64179 13.6663 6.68817 13.725 6.74682C13.7837 6.80548 13.8301 6.87524 13.8616 6.95203C13.893 7.02883 13.9089 7.11112 13.9082 7.19411C13.9075 7.27709 13.8903 7.35911 13.8576 7.43538C13.8249 7.51165 13.7773 7.58064 13.7176 7.63832L9.0043 12.3525C8.57454 12.7824 7.99162 13.0239 7.38377 13.024C6.77591 13.0241 6.19293 12.7827 5.76305 12.3529C5.33318 11.9231 5.09164 11.3402 5.09156 10.7324C5.09148 10.1245 5.33288 9.54153 5.76264 9.11165L13.1293 1.74999C13.7935 1.08573 14.6943 0.712511 15.6336 0.712433C16.5729 0.712355 17.4738 1.08542 18.1381 1.74957C18.8023 2.41372 19.1755 3.31454 19.1756 4.25386C19.1757 5.19318 18.8026 6.09406 18.1385 6.75832L9.00514 15.8883C8.56103 16.3347 8.03283 16.6885 7.45109 16.9294C6.86934 17.1703 6.24561 17.2934 5.61597 17.2917Z"
                                                        fill="#46A046"/>
                                                </svg>
                                                <input type="file" id="select-file"
                                                       class="h-100 position-absolute w-100 " hidden multiple
                                                       accept=".doc, .docx, .pdf, .zip">
                                            </label>
                                        </div>
                                        <label class="w-0 flex-grow-1 uploaded-file-container">
                                            <textarea class="form-control pt-3 radius-left-button pl-105px"
                                                      id="msgInputValue" name="message" type="text"
                                                      placeholder="{{translate('send_a_message')}}"
                                                      aria-label="Search"></textarea>
                                            <div class="d-flex justify-content-between items-container">
                                                <div class="overflow-x-auto pt-3 pb-2">
                                                    <div>
                                                        <div class="d-flex gap-3">
                                                            <div class="d-flex gap-3 image-array"></div>
                                                            <div class="d-flex gap-3 file-array"></div>
                                                            <div class="d-flex gap-3 input-uploaded-file">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="selected-files-container"></div>
                                                    <div id="selected-media-container"></div>
                                                </div>
                                            </div>
                                        </label>
                                        <div
                                            class="d-flex align-items-center justify-content-center bg-F1F7FF radius-right-button">
                                            <button
                                                class="aSend bg-transparent outline-0 border-0 shadow-0 px-0 h-100 send-btn"
                                                type="submit" id="msgSendBtn">
                                                <img
                                                    src="{{dynamicAsset(path: 'public/assets/back-end/img/send-icon.png')}}"
                                                    alt="">
                                            </button>
                                        </div>
                                        <div class="circle-progress ml-auto collapse">
                                            <div class="inner">
                                                <div class="text"></div>
                                                <svg id="svg" width="24" height="24" viewPort="0 0 12 12" version="1.1"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <circle id="bar" r="10" cx="12" cy="12" fill="transparent"
                                                            stroke-dasharray="100" stroke-dashoffset="100"></circle>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="d-flex flex-column align-items-center gap-3">
                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/empty-message.png') }}"
                                     alt="">
                                <p>{{ translate('you_have_not_any_conversation_yet') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        </div>
        <span id="chatting-post-url"
              data-url="{{ route('freelancer.messages.message').'?user_id=' }}"></span>
        <span id="chatting-typing-url"
              data-url="{{ route('freelancer.messages.typing').'?user_id=' }}"></span>
        <span id="chatting-typing-status-url"
              data-url="{{ route('freelancer.messages.typing-status').'?user_id=' }}"></span>
        <span id="image-url" data-url="{{ dynamicAsset('storage/app/public/chatting') }}"></span>
    </div>
    <span id="get-file-icon" data-default-icon="{{dynamicAsset("public/assets/back-end/img/default-icon.png")}}"
          data-word-icon="{{dynamicAsset("public/assets/back-end/img/default-icon.png")}}"></span>
    <span id="message-media-error" data-text="{{ translate('File_size_is_too_large') }} {{ translate('Please_upload_a_smaller_file') }}"></span>
    <span id="get-video-preview-icon"
          data-video-icon="{{ dynamicAsset('public/assets/back-end/img/icons/carbon_play-filled.svg') }}"></span>
@endsection

@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/chatting.js')}}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/picmo-emoji.js')}}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/emoji.js')}}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/select-multiple-file.js')}}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/select-multiple-image-for-message.js')}}"></script>
    <script src="{{ dynamicAsset('public/assets/back-end/js/owl.min.js') }}"></script>
@endpush
