@extends('layouts.admin.app')

@section('title', translate('freelancer_details'))

@push('css_or_js')
    <style>
        .fl-cover {
            background: linear-gradient(135deg, #1a2f5e 0%, #2f4a8c 100%);
            border-radius: 16px;
            padding: 28px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .fl-cover::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 85% 0%, rgba(255,255,255,.12), transparent 55%);
        }
        .fl-cover-avatar {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,.35);
            flex-shrink: 0;
        }
        .fl-stat-pill {
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 12px;
            padding: 10px 16px;
            min-width: 110px;
        }
        .fl-stat-pill .fl-stat-value { font-size: 20px; font-weight: 800; line-height: 1.1; }
        .fl-stat-pill .fl-stat-label { font-size: 11px; opacity: .8; text-transform: uppercase; letter-spacing: .04em; }

        .fl-tabs { display: flex; gap: 6px; flex-wrap: wrap; border-bottom: 1px solid #e5e7eb; margin: 24px 0 20px; }
        .fl-tab-link {
            padding: 10px 18px;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
            font-size: 14px;
            color: #6b7280;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .fl-tab-link:hover { color: #1a2f5e; }
        .fl-tab-link.active {
            color: #1a2f5e;
            background: #fff;
            border-color: #e5e7eb #e5e7eb #fff;
            margin-bottom: -1px;
        }

        .fl-service-card, .fl-portfolio-card {
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fff;
            overflow: hidden;
            transition: box-shadow .2s ease, transform .2s ease;
            height: 100%;
        }
        .fl-service-card:hover, .fl-portfolio-card:hover {
            box-shadow: 0 10px 24px rgba(0,0,0,.08);
            transform: translateY(-2px);
        }
        .fl-portfolio-thumb { height: 160px; background: #f8f9fa; overflow: hidden; }
        .fl-portfolio-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .fl-tag { display: inline-block; font-size: 11px; background: #eef2ff; color: #4338ca; border-radius: 999px; padding: 2px 10px; margin: 2px 3px 0 0; }
        .fl-empty-state { text-align: center; padding: 60px 20px; color: #9ca3af; }
        .fl-empty-state .fi { font-size: 40px; display: block; margin-bottom: 10px; }
        .fl-document-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .fl-document-card { display: block; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff; color: #111827; text-decoration: none; height: 100%; }
        .fl-document-card:hover { border-color: #2563eb; color: #111827; box-shadow: 0 8px 20px rgba(15,23,42,.08); }
        .fl-document-preview { height: 118px; background: #f8fafc; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .fl-document-preview img { width: 100%; height: 100%; object-fit: cover; }
        .fl-document-preview .fi { font-size: 34px; color: #1d4ed8; }
        .fl-document-meta { padding: 10px 12px; }
        .fl-document-meta .label { font-size: 12px; color: #6b7280; margin-bottom: 2px; }
        .fl-document-meta .value { font-weight: 700; font-size: 13px; }
        .fl-document-missing { border-style: dashed; color: #9ca3af; pointer-events: none; }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('freelancer_details') }}</h2>
            <a href="{{ route('admin.freelancer.accounts.index') }}" class="btn btn-secondary">{{ translate('back') }}</a>
        </div>

        @php($verificationStatus = $freelancer->vendorVerification?->status ?? 'unsubmitted')
        <div class="fl-cover">
            <div class="d-flex flex-wrap gap-4 justify-content-between align-items-center position-relative">
                <div class="d-flex align-items-center gap-3">
                    <img class="fl-cover-avatar" src="{{ getStorageImages(path: $freelancer->image_full_url, type: 'backend-profile') }}" alt="">
                    <div>
                        <h4 class="mb-1 text-white">{{ $freelancer->f_name }} {{ $freelancer->l_name }}</h4>
                        <div class="fs-13 opacity-75">{{ $freelancer->email }}</div>
                        <div class="fs-13 opacity-75">{{ $freelancer->phone }}</div>
                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <span class="badge text-capitalize {{ $verificationStatus === 'approved' ? 'bg-success' : ($verificationStatus === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                {{ translate('kyc') }}: {{ $verificationStatus }}
                            </span>
                            <span class="badge {{ $freelancer->account_status === 'inactive' ? 'bg-danger' : 'bg-success' }}">
                                {{ $freelancer->account_status === 'inactive' ? translate('suspended') : translate('active') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="fl-stat-pill text-center">
                        <div class="fl-stat-value">{{ $servicesCount }}</div>
                        <div class="fl-stat-label">{{ translate('services') }}</div>
                    </div>
                    <div class="fl-stat-pill text-center">
                        <div class="fl-stat-value">{{ $portfolioCount }}</div>
                        <div class="fl-stat-label">{{ translate('portfolio') }}</div>
                    </div>
                    <div class="fl-stat-pill text-center">
                        <div class="fl-stat-value">{{ $contracts->count() }}</div>
                        <div class="fl-stat-label">{{ translate('contracts') }}</div>
                    </div>
                    <div class="fl-stat-pill text-center">
                        <div class="fl-stat-value">{{ $chattingCustomers->count() }}</div>
                        <div class="fl-stat-label">{{ translate('chats') }}</div>
                    </div>
                </div>
            </div>
        </div>

        @php($activeTab = request('tab', 'overview'))
        <div class="fl-tabs">
            <a class="fl-tab-link {{ $activeTab === 'overview' ? 'active' : '' }}" href="{{ route('admin.freelancer.accounts.view', ['id' => $freelancer->id]) }}">{{ translate('overview') }}</a>
            <a class="fl-tab-link {{ $activeTab === 'services' ? 'active' : '' }}" href="{{ route('admin.freelancer.accounts.view', ['id' => $freelancer->id, 'tab' => 'services']) }}">{{ translate('services') }} ({{ $servicesCount }})</a>
            <a class="fl-tab-link {{ $activeTab === 'portfolio' ? 'active' : '' }}" href="{{ route('admin.freelancer.accounts.view', ['id' => $freelancer->id, 'tab' => 'portfolio']) }}">{{ translate('portfolio') }} ({{ $portfolioCount }})</a>
            <a class="fl-tab-link {{ $activeTab === 'contracts' ? 'active' : '' }}" href="{{ route('admin.freelancer.accounts.view', ['id' => $freelancer->id, 'tab' => 'contracts']) }}">{{ translate('contracts') }} ({{ $contracts->count() }})</a>
            <a class="fl-tab-link {{ $activeTab === 'chat' ? 'active' : '' }}" href="{{ route('admin.freelancer.accounts.view', ['id' => $freelancer->id, 'tab' => 'chat']) }}">{{ translate('chat') }} ({{ $chattingCustomers->count() }})</a>
        </div>

        @if($activeTab === 'overview')
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">{{ translate('basic_information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="text-muted small">{{ translate('name') }}</div>
                                    <div class="fw-bold">{{ $freelancer->f_name }} {{ $freelancer->l_name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">{{ translate('email') }}</div>
                                    <div class="fw-bold">{{ $freelancer->email }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">{{ translate('phone') }}</div>
                                    <div class="fw-bold">{{ $freelancer->phone }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-muted small">{{ translate('joined_at') }}</div>
                                    <div class="fw-bold">{{ $freelancer->created_at?->format('d M, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($freelancer->vendorVerification)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">{{ translate('verification') }}</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="text-muted small">{{ translate('personal_email') }}</div>
                                        <div class="fw-bold">{{ $freelancer->vendorVerification->personal_email ?? '-' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-muted small">{{ translate('personal_contact') }}</div>
                                        <div class="fw-bold">{{ $freelancer->vendorVerification->personal_contact ?? '-' }}</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="text-muted small mb-2">{{ translate('submitted_documents') }}</div>
                                        <div class="fl-document-grid">
                                            @foreach($submittedDocuments as $document)
                                                @if(!empty($document['filename']) && !empty($document['url']))
                                                    <a class="fl-document-card" href="{{ $document['url'] }}" target="_blank" rel="noopener noreferrer">
                                                        <div class="fl-document-preview">
                                                            @if($document['is_pdf'])
                                                                <i class="fi fi-rr-file-pdf"></i>
                                                            @else
                                                                <img src="{{ $document['url'] }}" alt="{{ $document['label'] }}">
                                                            @endif
                                                        </div>
                                                        <div class="fl-document-meta">
                                                            <div class="label">{{ $document['label'] }}</div>
                                                            <div class="value text-truncate">{{ translate('view_document') }}</div>
                                                        </div>
                                                    </a>
                                                @else
                                                    <div class="fl-document-card fl-document-missing">
                                                        <div class="fl-document-preview"><i class="fi fi-rr-file"></i></div>
                                                        <div class="fl-document-meta">
                                                            <div class="label">{{ $document['label'] }}</div>
                                                            <div class="value text-truncate">{{ !empty($document['filename']) ? translate('file_not_found_on_storage') : translate('not_uploaded') }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    @if($freelancer->vendorVerification->status === 'rejected' && $freelancer->vendorVerification->rejection_reason)
                                        <div class="col-12">
                                            <div class="alert alert-danger mb-0">
                                                <strong>{{ translate('rejection_reason') }}:</strong> {{ $freelancer->vendorVerification->rejection_reason }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ translate('kyc_status') }}</span>
                                <span class="badge text-capitalize {{ $verificationStatus === 'approved' ? 'bg-success' : ($verificationStatus === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                    {{ $verificationStatus }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>{{ translate('account_status') }}</span>
                                <span class="badge {{ $freelancer->account_status === 'inactive' ? 'bg-danger' : 'bg-success' }}">
                                    {{ $freelancer->account_status === 'inactive' ? translate('suspended') : translate('active') }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ translate('total_services') }}</span>
                                <span class="fw-bold">{{ $servicesCount }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ translate('portfolio_items') }}</span>
                                <span class="fw-bold">{{ $portfolioCount }}</span>
                            </div>
                        </div>
                    </div>

                    @if($freelancer->vendorVerification)
                        <div class="d-flex gap-2">
                            @if($verificationStatus !== 'approved')
                                <button type="button" class="btn btn-success flex-grow-1" data-bs-toggle="modal" data-bs-target="#approveVerificationModal">
                                    {{ translate('approve') }}
                                </button>
                            @endif
                            @if($verificationStatus !== 'rejected')
                                <button type="button" class="btn btn-danger flex-grow-1" data-bs-toggle="modal" data-bs-target="#rejectVerificationModal">
                                    {{ translate('reject') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($freelancer->vendorVerification)
            <div class="modal fade" id="approveVerificationModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('admin.freelancer.verifications.approve', $freelancer->vendorVerification->id) }}" method="POST" novalidate>
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">{{ translate('want_to_approve_this_freelancer') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">{{ translate('approval_note') }}</label>
                                <textarea name="approval_note" class="form-control" rows="4" maxlength="1000" placeholder="{{ translate('optional_note_for_vendor') }}"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                                <button type="submit" class="btn btn-success">{{ translate('approve') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rejectVerificationModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form action="{{ route('admin.freelancer.verifications.reject', $freelancer->vendorVerification->id) }}" method="POST" novalidate>
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">{{ translate('want_to_reject_this_freelancer') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">{{ translate('rejection_reason') }}</label>
                                <textarea name="rejection_reason" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('cancel') }}</button>
                                <button type="submit" class="btn btn-danger">{{ translate('reject') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @if($activeTab === 'services')
            <div class="row g-3">
                @forelse($services as $service)
                    <div class="col-md-6 col-xl-4">
                        <div class="fl-service-card p-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h6 class="mb-0">{{ $service->title }}</h6>
                                <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $service->is_active ? translate('active') : translate('inactive') }}
                                </span>
                            </div>
                            <p class="text-muted small mb-2 text-truncate">{{ $service->description }}</p>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @if($service->category)
                                    <span class="fl-tag">{{ $service->category->name ?? '' }}</span>
                                @endif
                                @if($service->specialization)
                                    <span class="fl-tag">{{ $service->specialization->name ?? '' }}</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                <span class="fw-bold">{{ currencyConverter($service->price) }}</span>
                                <span class="text-muted small">{{ $service->delivery_time_days }} {{ translate('days') }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="fl-empty-state">
                            <i class="fi fi-sr-briefcase"></i>
                            {{ translate('no_services_added_yet') }}
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        @if($activeTab === 'portfolio')
            <div class="row g-3">
                @forelse($portfolioItems as $item)
                    <div class="col-md-6 col-xl-4">
                        <div class="fl-portfolio-card">
                            <div class="fl-portfolio-thumb">
                                <img src="{{ $item->image_full_url['path'] ?? '' }}" alt="{{ $item->title }}">
                            </div>
                            <div class="p-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h6 class="mb-1">{{ $item->title }}</h6>
                                    <span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $item->is_active ? translate('active') : translate('inactive') }}
                                    </span>
                                </div>
                                <p class="text-muted small mb-2 text-truncate">{{ $item->description }}</p>
                                @if(!empty($item->tags))
                                    <div>
                                        @foreach($item->tags as $tag)
                                            <span class="fl-tag">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($item->project_url)
                                    <a href="{{ $item->project_url }}" target="_blank" class="d-inline-block mt-2 small">{{ translate('view_project') }} &rarr;</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="fl-empty-state">
                            <i class="fi fi-sr-picture"></i>
                            {{ translate('no_portfolio_items_added_yet') }}
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        @if($activeTab === 'contracts')
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>{{ translate('client') }}</th>
                            <th>{{ translate('service') }}</th>
                            <th>{{ translate('amount') }}</th>
                            <th>{{ translate('status') }}</th>
                            <th>{{ translate('created_at') }}</th>
                            <th class="text-end">{{ translate('action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($contracts as $contract)
                            <tr>
                                <td>{{ $contract->id }}</td>
                                <td>{{ $contract->customer?->f_name }} {{ $contract->customer?->l_name }}</td>
                                <td>{{ $contract->service?->title }}</td>
                                <td>{{ currencyConverter($contract->total_amount) }}</td>
                                <td><span class="badge badge-soft--primary text-capitalize">{{ str_replace('_', ' ', $contract->status) }}</span></td>
                                <td>{{ $contract->created_at->format('d M, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.freelancer.contracts.view', $contract->id) }}" class="btn btn-sm btn-outline-primary">
                                        {{ translate('view') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">{{ translate('no_data_to_show') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($activeTab === 'chat')
            <div class="card">
                <div class="card-body">
                    @forelse($chattingCustomers as $chatting)
                        @if($chatting->customer)
                            <a href="{{ route('admin.freelancer.chats.index', ['seller_id' => $freelancer->id, 'user_id' => $chatting->user_id]) }}"
                               class="d-flex align-items-center gap-3 p-2 rounded text-decoration-none text-body {{ !$loop->last ? 'border-bottom' : '' }}">
                                <img class="rounded-circle" width="42" height="42" style="object-fit:cover;"
                                     src="{{ getStorageImages(path: $chatting->customer->image_full_url, type: 'backend-profile') }}" alt="">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold">{{ $chatting->customer->f_name }} {{ $chatting->customer->l_name }}</div>
                                    <div class="text-muted small text-truncate">{{ $chatting->message ?? translate('shared_files') }}</div>
                                </div>
                                <div class="text-muted small flex-shrink-0">{{ $chatting->created_at->diffForHumans() }}</div>
                            </a>
                        @endif
                    @empty
                        <div class="fl-empty-state">
                            <i class="fi fi-sr-comment"></i>
                            {{ translate('no_conversations_yet') }}
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
@endsection
