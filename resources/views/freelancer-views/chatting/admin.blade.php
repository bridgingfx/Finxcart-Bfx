@extends('layouts.freelancer.app')

@section('title', translate('messages_from_admin'))

@push('css_or_js')
    <style>
        .admin-chat-wrap { max-width: 820px; margin: 0 auto; }

        .admin-chat-card {
            background: #fff; border: 1px solid #e8edf3; border-radius: 14px; overflow: hidden;
            box-shadow: 0 10px 30px rgba(23, 57, 94, 0.06);
        }

        .admin-chat-header {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%); color: #fff;
            padding: 18px 22px; display: flex; align-items: center; gap: 14px;
        }
        .admin-chat-header .avatar-circle {
            width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            border: 2px solid rgba(255,255,255,.35); font-size: 20px;
        }
        .admin-chat-header .profile-name { color: #fff; font-weight: 800; font-size: 16px; margin-bottom: 2px; }
        .admin-chat-header .admin-chat-subtitle { font-size: 12px; color: rgba(255,255,255,.75); display: flex; align-items: center; gap: 6px; }
        .admin-chat-header .admin-chat-subtitle .dot { width: 7px; height: 7px; border-radius: 50%; background: #34d399; display: inline-block; }

        .admin-chat-body {
            background: #f6f8fb;
            padding: 20px !important;
        }

        .admin-chat-empty { color: #9ca3af; }
        .admin-chat-empty p { margin-top: 10px; font-size: 13px; }

        .admin-chat-card .message-text-section {
            border-radius: 14px !important;
            padding: 10px 14px !important;
            font-size: 14px !important;
            line-height: 1.5;
            max-width: 420px;
            box-shadow: 0 1px 2px rgba(23, 57, 94, 0.06);
        }
        .admin-chat-card .incoming_msg .message-text-section {
            background: #fff !important;
            border: 1px solid #e8edf3;
            border-bottom-left-radius: 4px !important;
        }
        .admin-chat-card .outgoing_msg .message-text-section {
            background: linear-gradient(135deg, #17395e 0%, #1f4a78 100%) !important;
            color: #fff !important;
            border-bottom-right-radius: 4px !important;
        }
        .admin-chat-card .badge.badge-soft-info {
            background: #eef2ff; color: #3730a3; font-weight: 700;
        }

        .admin-chat-input {
            background: #fff; border-top: 1px solid #eef1f5; padding: 16px 20px;
        }
        .admin-chat-input form { align-items: flex-end; }
        .admin-chat-input textarea {
            border-radius: 20px !important; resize: none; border-color: #e0e5eb;
        }
        .admin-chat-input textarea:focus { border-color: #17395e; box-shadow: 0 0 0 3px rgba(23, 57, 94, 0.08); }
        .admin-chat-input button {
            border-radius: 20px; padding: 10px 22px; font-weight: 700;
        }
        .admin-chat-attach-btn { width: 42px; height: 42px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #d8e0ea; border-radius: 20px; color: #17395e; cursor: pointer; margin-bottom: 0; }
        .admin-chat-attach-btn:hover { background: #eef5ff; border-color: #9bb8dc; }
        .admin-chat-file-preview { color: #6b7280; font-size: 12px; margin-top: 8px; }
        .admin-chat-wrap { max-width: 980px; }
        .admin-chat-card { border-radius: 8px; border-color: #e4ebf4; box-shadow: 0 16px 38px rgba(15, 23, 42, .08); }
        .admin-chat-header { background: #0b3d75; border-bottom: 3px solid #f58220; padding: 20px 24px; }
        .admin-chat-header .avatar-circle { background: rgba(255,255,255,.14); border-color: rgba(255,255,255,.45); }
        .admin-chat-body { min-height: 460px; background: linear-gradient(180deg, #f8fbff 0%, #f3f6fb 100%); padding: 22px !important; }
        .admin-chat-body::-webkit-scrollbar { width: 6px; }
        .admin-chat-body::-webkit-scrollbar-thumb { background: #c9d5e5; border-radius: 999px; }
        .admin-chat-card .message-text-section { border-radius: 8px !important; max-width: min(72%, 560px); box-shadow: 0 8px 22px rgba(15, 23, 42, .07); }
        .admin-chat-card .incoming_msg .message-text-section { background: #ffffff !important; border-color: #e4ebf4; }
        .admin-chat-card .outgoing_msg .message-text-section { background: #0b3d75 !important; color: #fff !important; }
        .admin-chat-input { padding: 16px 18px; background: #fbfdff; }
        .admin-chat-input form > .d-flex { background: #fff; border: 1px solid #d7e1ed; border-radius: 8px; padding: 8px; box-shadow: 0 10px 24px rgba(15, 23, 42, .05); }
        .admin-chat-input textarea { border: 0 !important; border-radius: 8px !important; min-height: 42px; padding-top: 10px; }
        .admin-chat-input textarea:focus { box-shadow: none !important; }
        .admin-chat-attach-btn { border-radius: 8px; border-color: #d7e1ed; background: #f8fbff; color: #0b3d75; }
        .admin-chat-input button { min-height: 42px; border-radius: 8px; background: #0b3d75; border-color: #0b3d75; box-shadow: 0 8px 18px rgba(11, 61, 117, .18); }
        .admin-chat-input button:hover { background: #f58220; border-color: #f58220; color: #111827; }
        .admin-chat-file-preview { padding-left: 8px; }
        .fl-visible-message-time { font-size: 10.5px !important; line-height: 1.2; opacity: .82; }
        .fl-chat-bubble-admin .fl-visible-message-time,
        .admin-chat-card .outgoing_msg .fl-visible-message-time { color: rgba(255,255,255,.78) !important; }
        .fl-chat-attach-btn i,
        .admin-chat-attach-btn i { font-size: 18px; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset('/public/assets/back-end/img/support-ticket.png')}}" alt="">
                {{ translate('messages_from_admin') }}
            </h2>
        </div>

        <div class="admin-chat-wrap">
            <div class="admin-chat-card">
                <div class="admin-chat-header">
                    <div class="avatar-circle">
                        <i class="tio-verified"></i>
                    </div>
                    <div class="media-body">
                        <h5 class="profile-name mb-0">{{ translate('support_team') }}</h5>
                        <div class="admin-chat-subtitle"><span class="dot"></span> {{ translate('last_login') }} {{ translate('recently') }}</div>
                    </div>
                </div>

                <div class="card-body p-3 overflow-y-auto height-220 flex-grow-1 msg_history admin-chat-body"
                     id="chatting-messages-section">
                    @if($chattingMessages->isEmpty())
                        <div class="d-flex justify-content-center align-items-center h-100">
                            <div class="d-flex flex-column align-items-center gap-3 admin-chat-empty">
                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/empty-message.png') }}" alt="">
                                <p class="mb-0">{{ translate('you_have_not_any_conversation_yet') }}</p>
                            </div>
                        </div>
                    @else
                        @include('freelancer-views.chatting.messages', ['lastChatUser' => null, 'chattingMessages' => $chattingMessages])
                    @endif
                </div>

                <div class="admin-chat-typing-indicator px-3 py-1 small text-muted d-none" id="admin-chat-typing-indicator">
                    {{ translate('support_team') }} {{ translate('is_typing') }}...
                </div>

                <div class="admin-chat-input">
                    <form method="POST" action="{{ route('freelancer.messages.admin.send') }}" enctype="multipart/form-data" id="freelancer-admin-chat-form" novalidate>
                        @csrf
                        <div class="d-flex gap-2 align-items-end">
                            <label class="admin-chat-attach-btn" title="{{ translate('attachment') }}">
                                <label class="py-0 cursor-pointer fl-file-picker" title="{{ translate('file') }}">
                                    <svg width="20" height="18" viewBox="0 0 20 18" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M5.61597 17.2917C4.66813 17.2919 3.7415 17.011 2.95335 16.4845C2.16519 15.958 1.55092 15.2096 1.18827 14.3338C0.825613 13.4581 0.730874 12.4945 0.916037 11.5649C1.1012 10.6353 1.55794 9.78158 2.22847 9.11165L9.2993 2.03999C9.41655 1.92274 9.57557 1.85687 9.74139 1.85687C9.9072 1.85687 10.0662 1.92274 10.1835 2.03999C10.3007 2.15724 10.3666 2.31626 10.3666 2.48207C10.3666 2.64788 10.3007 2.80691 10.1835 2.92415L3.11181 9.99499C2.76945 10.3208 2.49576 10.7118 2.30686 11.145C2.11796 11.5782 2.01768 12.0449 2.01193 12.5175C2.00617 12.99 2.09506 13.459 2.27334 13.8967C2.45163 14.3344 2.71572 14.7319 3.05004 15.066C3.38436 15.4 3.78216 15.6638 4.21999 15.8417C4.65783 16.0196 5.12685 16.1081 5.59941 16.102C6.07198 16.0958 6.53854 15.9951 6.9716 15.8059C7.40465 15.6166 7.79545 15.3426 8.12097 15L17.2543 5.86665C17.6728 5.43446 17.9047 4.85506 17.8999 4.25344C17.895 3.65183 17.6539 3.07623 17.2285 2.65081C16.8031 2.22539 16.2275 1.98425 15.6258 1.97942C15.0242 1.97459 14.4448 2.20645 14.0126 2.62499L6.64764 9.99499C6.45226 10.1904 6.3425 10.4554 6.3425 10.7317C6.3425 11.008 6.45226 11.2729 6.64764 11.4683C6.84301 11.6637 7.108 11.7735 7.3843 11.7735C7.66061 11.7735 7.9256 11.6637 8.12097 11.4683L12.8335 6.75499C12.8911 6.69527 12.96 6.64762 13.0363 6.61483C13.1125 6.58204 13.1945 6.56476 13.2775 6.564C13.3605 6.56324 13.4428 6.57901 13.5196 6.6104C13.5964 6.64179 13.6663 6.68817 13.725 6.74682C13.7837 6.80548 13.8301 6.87524 13.8616 6.95203C13.893 7.02883 13.9089 7.11112 13.9082 7.19411C13.9075 7.27709 13.8903 7.35911 13.8576 7.43538C13.8249 7.51165 13.7773 7.58064 13.7176 7.63832L9.0043 12.3525C8.57454 12.7824 7.99162 13.0239 7.38377 13.024C6.77591 13.0241 6.19293 12.7827 5.76305 12.3529C5.33318 11.9231 5.09164 11.3402 5.09156 10.7324C5.09148 10.1245 5.33288 9.54153 5.76264 9.11165L13.1293 1.74999C13.7935 1.08573 14.6943 0.712511 15.6336 0.712433C16.5729 0.712355 17.4738 1.08542 18.1381 1.74957C18.8023 2.41372 19.1755 3.31454 19.1756 4.25386C19.1757 5.19318 18.8026 6.09406 18.1385 6.75832L9.00514 15.8883C8.56103 16.3347 8.03283 16.6885 7.45109 16.9294C6.86934 17.1703 6.24561 17.2934 5.61597 17.2917Z"
                                            fill="#46A046"/>
                                    </svg>
                                </label>
                                <input type="file" name="file[]" class="admin-chat-file-input d-none" multiple accept=".doc,.docx,.pdf,.zip,.txt">
                            </label>
                            <textarea class="form-control" name="message" rows="1" id="freelancer-admin-chat-input"
                                      placeholder="{{ translate('send_a_message') }}"></textarea>
                            <button type="submit" class="btn btn--primary flex-shrink-0" id="freelancer-admin-chat-send-btn">
                                <i class="tio-send-outlined"></i> {{ translate('send') }}
                            </button>
                        </div>
                        <div class="admin-chat-file-preview"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function () {
            const chatBox = document.getElementById('chatting-messages-section');
            const form = document.getElementById('freelancer-admin-chat-form');
            const textarea = document.getElementById('freelancer-admin-chat-input');
            const sendBtn = document.getElementById('freelancer-admin-chat-send-btn');
            const typingIndicator = document.getElementById('admin-chat-typing-indicator');
            let sendInFlight = false;

            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }

            // Live chat: re-fetch this thread roughly every second so admin replies
            // show up without a reload. Only swap while already near the bottom, so
            // scrolling up into history isn't interrupted mid-read, and skip while a
            // send is in flight so the two responses can't race and clobber the DOM.
            if (chatBox) {
                const pollUrl = @json(route('freelancer.messages.admin.poll'));
                const pollThread = () => {
                    if (document.hidden || sendInFlight) {
                        return;
                    }
                    const nearBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 60;
                    if (!nearBottom) {
                        return;
                    }
                    fetch(pollUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    })
                        .then((response) => response.ok ? response.json() : null)
                        .then((data) => {
                            if (data && data.html) {
                                chatBox.innerHTML = data.html;
                                chatBox.scrollTop = chatBox.scrollHeight;
                            }
                        })
                        .catch(() => {});
                };
                setInterval(pollThread, 1000);
                document.addEventListener('visibilitychange', function () {
                    if (document.visibilityState === 'visible') pollThread();
                });
            }

            // Typing indicator: ping the server (throttled) on every keystroke, and
            // poll (fast, cheap cache lookup) to see if the admin is typing back.
            if (textarea) {
                const typingUrl = @json(route('freelancer.messages.admin.typing'));
                let lastPing = 0;
                textarea.addEventListener('input', function () {
                    const now = Date.now();
                    if (now - lastPing < 1000) {
                        return;
                    }
                    lastPing = now;
                    fetch(typingUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    }).catch(() => {});
                });
            }

            if (typingIndicator) {
                const typingStatusUrl = @json(route('freelancer.messages.admin.typing-status'));
                const pollTypingStatus = () => {
                    if (document.hidden) {
                        return;
                    }
                    fetch(typingStatusUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    })
                        .then((response) => response.ok ? response.json() : null)
                        .then((data) => {
                            typingIndicator.classList.toggle('d-none', !(data && data.typing));
                        })
                        .catch(() => {});
                };
                setInterval(pollTypingStatus, 1000);
            }

            if (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    if (sendInFlight) {
                        return;
                    }
                    sendInFlight = true;
                    if (sendBtn) sendBtn.disabled = true;

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data && data.html && chatBox) {
                                chatBox.innerHTML = data.html;
                                chatBox.scrollTop = chatBox.scrollHeight;
                            }
                            textarea.value = '';
                            const preview = form.querySelector('.admin-chat-file-preview');
                            if (preview) preview.textContent = '';
                            document.querySelectorAll('.admin-chat-file-input').forEach((input) => { input.value = ''; });
                        })
                        .catch(() => {
                            window.toastMagic ? toastMagic.error('{{ translate('something_went_wrong') }}') : alert('{{ translate('something_went_wrong') }}');
                        })
                        .finally(() => {
                            sendInFlight = false;
                            if (sendBtn) sendBtn.disabled = false;
                        });
                });
            }

            document.querySelectorAll('.admin-chat-file-input').forEach((input) => {
                input.addEventListener('change', function () {
                    const maxFileSize = 2 * 1024 * 1024;
                    const files = Array.from(this.files || []);
                    const invalidFile = files.find((file) => file.size > maxFileSize);
                    const preview = this.closest('form').querySelector('.admin-chat-file-preview');

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
